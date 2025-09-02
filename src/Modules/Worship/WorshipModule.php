<?php

namespace atc\SDG\Modules\XXX;

use atc\WHx4\Core\Module as BaseModule;
//
use atc\SDG\Modules\XXX\PostTypes\XXX;
use atc\SDG\Modules\XXX\PostTypes\XXX;

// Define the module class
final class XXXModule extends BaseModule
{
    public function boot(): void
    {
        $this->registerDefaultViewRoot();
        parent::boot();
    }

    public function getPostTypeHandlerClasses(): array
    {
        return [
            XXX::class,
            XXX::class,
        ];
    }
}
