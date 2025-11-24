<?php

namespace atc\SDG\Modules\Worship\PostTypes;

use atc\WXC\Core\PostTypeHandler;

class Sermon extends PostTypeHandler
{
	public function __construct(?\WP_Post $post = null)
	{
		$config = [
			'slug'        => 'sermon',
			'menu_icon'   => 'dashicons-megaphone',
			'taxonomies'   => [ 'sermon_topic', 'admin_tag' ],
			'supports' => ['title', 'author', 'thumbnail', 'editor', 'excerpt', 'revisions'],
			//'capability_type' => ['sermon', 'sermons'],
			//'map_meta_cap'       => true,
		];

        parent::__construct( $config, $post );
    }

    public function boot(): void
    {
        parent::boot(); // Optional if you add shared logic later
    }
}
