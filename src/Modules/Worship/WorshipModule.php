<?php

namespace SDG\Modules\Worship;

use WXC\Core\Module as BaseModule;
use WXC\Core\Query\PostQuery;
use WXC\Core\Shortcodes\ShortcodeManager;
//
use SDG\Modules\Worship\PostTypes\Sermon;
#use SDG\Modules\Worship\PostTypes\SermonSeries;

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
