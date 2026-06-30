<?php
/**
 * ScrollImage::render_callback()
 *
 * @package DIPI\Modules\ScrollImage
 * @since ??
 */

namespace DIPI\Modules\ScrollImage;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Packages\Module\Module;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;

trait RenderCallbackTrait {
	private static $props = [];
	
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
        $scroll_image = ScrollImage::getPropValue($attrs, 'scroll_image');
		$img_alt = ScrollImage::getPropValue($attrs, 'img_alt');
		$scroll_type = ScrollImage::getPropValue($attrs, 'scroll_type');
		$scroll_direction = ScrollImage::getPropValue($attrs, 'scroll_direction');
		$reverse = ScrollImage::getPropValue($attrs, 'reverse');
		$use_direction_icon = ScrollImage::getPropValue($attrs, 'use_direction_icon');
		$use_image = ScrollImage::getPropValue($attrs, 'use_image');
		$direction_image = ScrollImage::getPropValue($attrs, 'direction_image');
		$direction_img_alt = ScrollImage::getPropValue($attrs, 'direction_img_alt');
		$direction_icon = ScrollImage::getPropValue($attrs, 'direction_icon');
		$direction_icon = Utils::process_font_icon($direction_icon);
		$overlay_direction = ScrollImage::getPropValue($attrs, 'overlay_direction');
		$use_icon_animation = ScrollImage::getPropValue($attrs, 'use_icon_animation');

		$content_icon = $use_direction_icon === 'on' && $use_image === 'off' ? sprintf(
			'<div class="dipi-image-scroll-content">
				<span class="et-pb-icon et-pb-font-icon dipi-image-scroll-icon">%1$s</span>
			</div>',
			esc_attr($direction_icon)
		) : sprintf(
			'<div class="dipi-image-scroll-content">
				<img src="%1$s" alt="%2$s">
			</div>',
			esc_url($direction_image),
			esc_attr($direction_img_alt)
		);

		$container_class = '';
		if( $scroll_type === "on_mouse") {
			$container_class = "dipi-image-container-scroll";
		}

		$vertical_class = '';
		if( $scroll_direction === "vertical") {
			$vertical_class = 'dipi-image-scroll-vertical-active';
		}

		$icon_animation_class = "on" === $use_icon_animation ? 'dipi-icon-animate' : '';
		$scroll_direction_class = "vertical" === $scroll_direction ? 'dipi-image-scroll-vertical' : 'dipi-image-scroll-horizontal';
		$reverse_reset_class = "on" === $reverse ? 'dipi-container-scroll-anim-reset' : '';
		
		$render_html = sprintf(
			'<div class="dipi-scroll-image %10$s %11$s" data-type="%3$s" data-direction="%4$s" data-reverse="%9$s">
				<div class="dipi-image-scroll-container %6$s %12$s">
					%5$s
					<div class="dipi-image-scroll-image dipi-image-scroll-%1$s %7$s">
						<div class="dipi-image-scroll-overlay reveal %13$s"></div>
						<img src="%2$s" alt="%8$s">
					</div>
				</div>
			</div>',
			$scroll_direction,
			$scroll_image,
			$scroll_type,
			$scroll_direction,
			$content_icon,
			$container_class,
			$vertical_class,
			esc_attr($img_alt),
			$reverse,
			$icon_animation_class,
			$scroll_direction_class,
			$reverse_reset_class,
			$overlay_direction
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
				'classnamesFunction'  => [ ScrollImage::class, 'module_classnames' ],
				'stylesComponent'     => [ ScrollImage::class, 'module_styles' ],
				'scriptDataComponent' => [ ScrollImage::class, 'module_script_data' ],
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
