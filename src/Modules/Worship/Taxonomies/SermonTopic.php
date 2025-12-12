<?php

namespace atc\SDG\Modules\Worship\Taxonomies;

use atc\WXC\Taxonomies\TaxonomyHandler;

class SermonTopic extends TaxonomyHandler
{
    public function __construct(\WP_Term|null $term = null)
    {
        parent::__construct([
            'slug'         => 'sermon_topic',
            'plural_slug'  => 'sermon_topics',
            'object_types' => ['sermon'],
            'hierarchical' => false,
        ], $term);
    }
}
