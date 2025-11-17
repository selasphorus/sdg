<?php

namespace atc\SDG\Modules\Worship;

use atc\BhWP\Core\Module as BaseModule;
use atc\BhWP\Core\Query\PostQuery;
use atc\BhWP\Core\Shortcodes\ShortcodeManager;
//
use atc\SDG\Modules\Worship\PostTypes\Sermon;
#use atc\SDG\Modules\Worship\PostTypes\SermonSeries;

// Define the module class
final class WorshipModule extends BaseModule
{
    public function boot(): void
    {
        $this->registerDefaultViewRoot();
        parent::boot();
    }

    public function getPostTypeHandlerClasses(): array
    {
        return [
            Sermon::class,
            //XXX::class,
        ];
    }
}
