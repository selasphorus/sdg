<?php

namespace atc\SDG\Modules\Worship\PostTypes;

use atc\WHx4\Core\PostTypeHandler;

class Sermon extends PostTypeHandler
{
	public function __construct(?\WP_Post $post = null)
	{
		$config = [
			'slug'        => 'sermon',
			//'plural_slug' => 'sermons',
			'labels'      => [
				//'add_new_item' => 'Summon New Monster',
				//'not_found'    => 'No monsters lurking nearby',
			],
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

	    // Apply Title Args -- this modifies front-end display only
	    // TODO: consider alternative approaches to allow for more customization? e.g. different args as with old SDG getPersonDisplayName method
		/*$this->applyTitleArgs( $this->getSlug(), [
			'line_breaks'    => true,
			'show_subtitle'  => true,
			'hlevel_sub'     => 4,
			'called_by'      => 'Sermon::boot',
			'append'         => ' {Amen!}',
		]);*/
	}
}

