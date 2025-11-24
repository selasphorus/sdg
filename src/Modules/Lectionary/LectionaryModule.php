<?php

namespace SDG\Modules\Lectionary;

use WXC\Core\Module as BaseModule;
// PostTypes still WIP! Maybe these will actually be subtypes of Library plugin core types?
use SDG\Modules\Lectionary\PostTypes\BibleBook;
use SDG\Modules\Lectionary\PostTypes\Verse;

// Define the module class
final class LectionaryModule extends BaseModule
{
    public function boot(): void
    {
        $this->registerDefaultViewRoot();
        parent::boot();
    }

    public function getPostTypeHandlerClasses(): array
    {
        return [
            BibleBook::class,
            Verse::class,
        ];
    }
}
