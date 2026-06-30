<?php
/**
 * ParallaxImagesItem::render_callback()
 *
 * @package DIPI\Modules\ParallaxImagesItem
 * @since ??
 */

namespace DIPI\Modules\ParallaxImagesItem;

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

	static function sanitize_content($content)
	{
		return preg_replace('/^<\/p>(.*)<p>/s', '$1', $content);
	}

	static function process_content($content)
	{
		$content = static::sanitize_content($content);
		$content = str_replace(["&#91;", "&#93;"], ["[", "]"], $content);
		$content = do_shortcode($content);
		$content = str_replace(
			["<p><div", "</div></p>", "</div> <!-- .et_pb_section --></p>"],
			["<div", "</div>", "</div>"],
			$content
		);
		return $content;
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

		$content_type = static::getPropValue($attrs, 'content_type');
		$image = static::getPropValue($attrs, 'image');
		$alt = static::getPropValue($attrs, 'alt');
		$text_content = static::getPropValue($attrs, 'text_content');

		$content = '';
        if($content_type === 'Image') {
            $content = sprintf('<img src="%1$s" alt="%2$s" />', $image, $alt);
        }

		if($content_type === 'Text') {
			$use_button = static::getPropValue($attrs, 'use_button');
			$content_button = '';

			if ( $use_button === 'on' ) {
				$content_button = sprintf(
					'<div class="dipi-pi-button-wrapper">%1$s</div>',
					$elements->render(
						[
							'attrName' => 'content_button',
						]
					)
				);
			}

			$content = sprintf('<div class="dipi-pi-content-text">%1$s%2$s</div>',
				static::process_content($text_content),
			 	$content_button
			);
		}

		$depth_x = $attrs['depth_x']['innerContent']['desktop']['value'] ?? 0.2;
		$depth_x_tablet = $attrs['depth_x']['innerContent']['tablet']['value'] ?? $depth_x;
		$depth_x_phone = $attrs['depth_x']['innerContent']['phone']['value'] ?? $depth_x_tablet;

		$depth_y = $attrs['depth_y']['innerContent']['desktop']['value'] ?? 0.2;
		$depth_y_tablet = $attrs['depth_y']['innerContent']['tablet']['value'] ?? $depth_y;
		$depth_y_phone = $attrs['depth_y']['innerContent']['phone']['value'] ?? $depth_y_tablet;

		$render_html = sprintf('
            <div class="dipi-pi-item-image" 
                data-depth-x="%2$s" data-depth-x-tablet="%3$s" data-depth-x-phone="%4$s"
                data-depth-y="%5$s" data-depth-y-tablet="%6$s" data-depth-y-phone="%7$s"
            >
                %1$s
            </div>', 
            $content,
            $depth_x,
            $depth_x_tablet,
            $depth_x_phone,
            $depth_y,
            $depth_y_tablet,
            $depth_y_phone
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
				'classnamesFunction'  => [ ParallaxImagesItem::class, 'module_classnames' ],
				'stylesComponent'     => [ ParallaxImagesItem::class, 'module_styles' ],
				'scriptDataComponent' => [ ParallaxImagesItem::class, 'module_script_data' ],
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
