<?php

namespace atc\SDG\Modules\Lectionary;

use atc\WXC\Core\Module as BaseModule;
// PostTypes still WIP! Maybe these will actually be subtypes of Library plugin core types?
use atc\SDG\Modules\Lectionary\PostTypes\BibleBook;
use atc\SDG\Modules\Lectionary\PostTypes\Verse;

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
