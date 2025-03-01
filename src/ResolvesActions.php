<?php

namespace Laravel\Nova;

use Laravel\Nova\Actions\ActionCollection;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Http\Requests\NovaRequest;

trait ResolvesActions
{
    /**
     * Get the actions that are available for the given request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Actions\ActionCollection<int, \Laravel\Nova\Actions\Action>
     */
    public function availableActions(NovaRequest $request)
    {
        $resource = $this->resource;

        if (method_exists($resource, 'getKey')) {
            $request->mergeIfMissing(array_filter([
                'resourceId' => $resource->getKey(),
            ]));
        }

        $actions = $this->resolveActions($request)
                    ->filter->authorizedToSee($request);

        if (optional($resource)->exists === true) {
            return $actions->withAuthorizedToRun($request, $resource)->values();
        }

        if (! is_null($resources = $request->selectedResources())) {
            $resources->each(function ($resource) use ($request, $actions) {
                $actions->withAuthorizedToRun($request, $resource);
            });

            return $actions->values();
        }

        return $actions->values();
    }

    /**
     * Get the actions that are available for the given index request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Actions\ActionCollection<int, \Laravel\Nova\Actions\Action>
     */
    public function availableActionsOnIndex(NovaRequest $request)
    {
        $resource = $this->resource;

        $actions = $this->resolveActions($request)
                    ->authorizedToSeeOnIndex($request);

        if (optional($resource)->exists === true) {
            return $actions->withAuthorizedToRun($request, $resource)->values();
        }

        if (! is_null($resources = $request->selectedResources())) {
            $resources->each(function ($resource) use ($request, $actions) {
                $actions->withAuthorizedToRun($request, $resource);
            });

            return $actions->values();
        }

        return $actions->values();
    }

    /**
     * Get the actions that are available for the given detail request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Actions\ActionCollection<int, \Laravel\Nova\Actions\Action>
     */
    public function availableActionsOnDetail(NovaRequest $request)
    {
        return $this->resolveActions($request)
                    ->authorizedToSeeOnDetail($request)
                    ->withAuthorizedToRun($request, $this->resource)
                    ->values();
    }

    /**
     * Get the resource table row actions that are available for the given index request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Actions\ActionCollection<int, \Laravel\Nova\Actions\Action>
     */
    public function availableActionsOnTableRow(NovaRequest $request)
    {
        return $this->resolveActions($request)
                    ->authorizedToSeeOnTableRow($request)
                    ->withAuthorizedToRun($request, $this->resource)
                    ->values();
    }

    /**
     * Get the actions for the given request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Actions\ActionCollection<int, \Laravel\Nova\Actions\Action>
     */
    public function resolveActions(NovaRequest $request)
    {
        return ActionCollection::make(
            $this->filter($this->actions($request))
        );
    }

    /**
     * Get the "pivot" actions that are available for the given request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Actions\ActionCollection<int, \Laravel\Nova\Actions\Action>
     */
    public function availablePivotActions(NovaRequest $request)
    {
        return $this->resolvePivotActions($request)
                    ->authorizedToSeeOnIndex($request)
                    ->values();
    }

    /**
     * Get the "pivot" actions for the given request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Actions\ActionCollection<int, \Laravel\Nova\Actions\Action>
     */
    public function resolvePivotActions(NovaRequest $request)
    {
        if ($request->viaRelationship()) {
            return ActionCollection::make(
                array_values($this->filter($this->getPivotActions($request)))
            )->each->showOnIndex();
        }

        return ActionCollection::make();
    }

    /**
     * Get the "pivot" actions for the given request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, \Laravel\Nova\Actions\Action>
     */
    protected function getPivotActions(NovaRequest $request)
    {
        $resource = Nova::resourceInstanceForKey($request->viaResource);

        $field = $resource->availableFields($request)->first(function ($field) use ($request) {
            return isset($field->resourceName) &&
                   $field->resourceName == $request->resource &&
                   ($field instanceof BelongsToMany || $field instanceof MorphToMany) &&
                   $field->manyToManyRelationship === $request->viaRelationship;
        });

        if ($field && isset($field->actionsCallback)) {
            return array_values(call_user_func($field->actionsCallback, $request));
        }

        return [];
    }

    /**
     * Merge the default actions with the given actions.
     *
     * @param  array  $actions
     * @return array<int, \Laravel\Nova\Actions\Action>
     */
    public static function defaultsWith(array $actions)
    {
        return array_merge(static::defaultActions(), $actions);
    }

    /**
     * Return the default actions.
     *
     * @return array<int, \Laravel\Nova\Actions\Action>
     */
    public static function defaultActions()
    {
        return [
            //
        ];
    }

    /**
     * Get the actions available on the entity.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return static::defaultsWith([
            //
        ]);
    }
}
