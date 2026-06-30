<?php
/**
 * TileScrollItem::render_callback()
 *
 * @package DIPI\Modules\TileScrollItem
 * @since ??
 */

namespace DIPI\Modules\TileScrollItem;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Packages\Module\Module;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use DIPI\Traits\BaseRenderTrait;

trait RenderCallbackTrait {
	use BaseRenderTrait;

	private static $props = [];

	public static function render_images($attrs = array())
	{
		$gallery_ids = self::getPropValue($attrs, "gallery");
		$attachment_ids = explode(",", $gallery_ids);
		$items = "";
		foreach ($attachment_ids as $attachment_id) {
			$attachment = wp_get_attachment_image_src($attachment_id, "full");
			if (!$attachment) {
				continue;
			}
			$image = $attachment[0];
			$image_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
			$items .= sprintf(
				'<div
					class="dipi-tile-scroll__line-img"
					style="background-image:url(%1$s)"
					loading="lazy"
				>
				</div>'
				,
				$image,
				$image_alt
			);
		}
		return $items;
	}
	
	/**
	 * Static module render callback which outputs server side rendered HTML on the Front-End.
	 *
	 * @since ??
	 * @param array          $attrs    Block attributes that were saved by VB.
	 * @param string         $content  Block content.
	 * @param WP_Block       $block    Parsed block object that being rendered.
	 * @param ModuleElements $elements ModuleElements instance.
	 *
	 * @return string HTML rendered of Static module.
	 */
	public static function render_callback( $attrs, $content, $block, $elements ) {
		$order_number = $block->parsed_block['orderIndex'];
		$images = self::render_images($attrs);

		$render_html = sprintf(
			'<div class="dipi_tile_scroll_item_container %2$s">
			  %1$s
			</div>
			',
			$images,
			$order_number % 2 === 0 ? 'even' : 'odd'
		  );

        $parent       = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
		$parent_attrs = $parent->attrs ?? [];

		return Module::render(
			[
				// FE only.
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'attrs'               => $attrs,
				'elements'            => $elements,
				'id'                  => $block->parsed_block['id'],
				'name'                => $block->block_type->name,
				'moduleCategory'      => $block->block_type->category,
				'classnamesFunction'  => [ TileScrollItem::class, 'module_classnames' ],
				'stylesComponent'     => [ TileScrollItem::class, 'module_styles' ],
				'scriptDataComponent' => [ TileScrollItem::class, 'module_script_data' ],
				'parentAttrs'         => $parent_attrs,
				'parentId'            => $parent->id ?? '',
				'parentName'          => $parent->blockName ?? '',
				'children'            => ElementComponents::component(
					[
						'attrs'         => $attrs['module']['decoration'] ?? [],
						'id'            => $block->parsed_block['id'],

						// FE only.
						'orderIndex'    => $block->parsed_block['orderIndex'],
						'storeInstance' => $block->parsed_block['storeInstance'],
					]
				) . $render_html,
			]
		);
	}
}
