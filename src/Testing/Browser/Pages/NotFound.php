<?php

namespace Laravel\Nova\Testing\Browser\Pages;

use Laravel\Dusk\Browser;

class NotFound extends Page
{
    /**
     * Create a new page instance.
     */
    public function __construct()
    {
        parent::__construct('/404');
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->whenAvailable('@404-error-page', static function (Browser $browser) {
            $browser->assertSee('404');
        });
    }
}
