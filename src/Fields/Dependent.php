<?php

namespace Laravel\Nova\Fields;

use Illuminate\Support\Arr;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @internal
 *
 * @phpstan-type TDependentResolver (callable(static, \Laravel\Nova\Http\Requests\NovaRequest, \Laravel\Nova\Fields\FormData):(void))|class-string
 */
class Dependent
{
    /**
     * The dependent context.
     *
     * @var array<int, string>
     */
    public array $context = ['create', 'update'];

    /**
     * The dependent attributes.
     *
     * @var array<int, string|\Laravel\Nova\Fields\Field>
     */
    public array $attributes = [];

    /**
     * The dependent resolved FormData.
     */
    public ?FormData $formData = null;

    /**
     * Create a new dependent object.
     *
     * @param  \Laravel\Nova\Fields\Field|array<int, string|\Laravel\Nova\Fields\Field>|string  $attributes
     * @param  callable|string  $resolver
     * @param  array<int, string>|string|null  $context
     *
     * @phpstan-param TDependentResolver $resolver
     */
    public function __construct(
        Field|array|string $attributes,
        public $resolver,
        array|string|null $context = null
    ) {
        $this->context = Arr::wrap($context ?? $this->context);

        $this->attributes = collect(Arr::wrap($attributes))->map(static function ($item) {
            /** @var string|\Laravel\Nova\Fields\Field $item */
            if ($item instanceof MorphTo) {
                return [$item->attribute, "{$item->attribute}_type"];
            }

            return $item instanceof Field ? $item->attribute : $item;
        })->flatten()->all();
    }

    /**
     * Handle the dependencies for request.
     *
     * @return $this
     */
    public function handle(Field $field, NovaRequest $request)
    {
        /** @var TDependentResolver|null $resolver */
        $resolver = (
            ($request->isCreateOrAttachRequest() && ! in_array('create', $this->context))
            || ($request->isUpdateOrUpdateAttachedRequest() && ! in_array('update', $this->context))
        ) ? null : $this->resolver;

        $this->formData = FormData::onlyFrom($request, array_merge($this->attributes, [$field->attribute]));

        if (is_string($resolver) && class_exists($resolver)) {
            $resolver = new $resolver;
        }

        if (is_callable($resolver)) {
            call_user_func($resolver, $field, $request, $this->formData);
        }

        return $this;
    }

    /**
     * Get depedent attributes.
     *
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return collect($this->attributes)->mapWithKeys(function ($attribute) {
            return [$attribute => optional($this->formData)->get($attribute)];
        })->all();
    }
}
