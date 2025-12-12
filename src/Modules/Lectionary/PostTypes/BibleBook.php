<?php

namespace atc\SDG\Modules\Lectionary\PostTypes;

use atc\WXC\PostTypes\PostTypeHandler;

class BibleBook extends PostTypeHandler
{
	public function __construct(?\WP_Post $post = null)
	{
		$config = [
			'slug'        => 'bible_book',
			//'menu_icon'   => 'dashicons-megaphone',
			//'taxonomies'   => [ 'sermon_topic', 'admin_tag' ],
			'supports' => ['title', 'author', 'thumbnail', 'editor', 'excerpt', 'revisions'],
			//'capability_type' => ['bible_book', 'bible_books'],
			//'map_meta_cap'       => true,
			//'rewrite'            => array( 'slug' => 'bible-books' ), // permalink structure slug
		];

        parent::__construct( $config, $post );
    }

    public function boot(): void
    {
        parent::boot(); // Optional if you add shared logic later
    }
}
