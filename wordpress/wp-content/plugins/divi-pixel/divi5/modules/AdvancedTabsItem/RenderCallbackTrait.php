<?php
/**
 * AdvancedTabsItem::render_callback()
 *
 * @package DIPI\Modules\AdvancedTabsItem
 * @since ??
 */

namespace DIPI\Modules\AdvancedTabsItem;

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
        $parent       = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
		$parent_attrs = $parent->attrs ?? [];

		$render_html = "";

		$use_library_content = static::getPropValue($attrs, 'use_library_content') ?? "off";
		if($use_library_content === "on") {
			$divi_library = static::getPropValue($attrs, 'divi_library');
			$divi_library_shortcode = static::render_library_layout($divi_library);
			$render_html = sprintf('<div class="dipi-at-panel-content dipi-at-panel-content--lib">%1$s</div>', $divi_library_shortcode);
		} else {
			$use_button = static::getPropValue($attrs, 'use_button') ?? "off";
			$button = $use_button === "on" ? $elements->render([
				'attrName' => 'button'
			]) : "";
			$image_link_target = static::getPropValue($attrs, 'image_link_target') ?? "self";
			$image_link_target = $image_link_target === "blank" ? 'target="_blank"' : '';
			$image_value = static::getPropValue($attrs, 'image') ?? "";
			// Support both URL string and object { src, alt } (e.g. from dynamic content).
			$image = is_array($image_value) && isset($image_value['src']) ? $image_value['src'] : $image_value;
			$alt = static::getPropValue($attrs, 'alt') ?? "";
			if ( is_array($image_value) && isset($image_value['alt']) && (string) $image_value['alt'] !== '' ) {
				$alt = $image_value['alt'];
			}
			$image_render = "";
			if ( $image !== "" ) {
				$image_link = static::getPropValue($attrs, 'image_link') ?? "";
				$image_link_yes = static::getPropValue($attrs, 'image_link_yes') ?? "off";
				if ( $image_link_yes === "on" ) {
					$image_render = sprintf( '<a href="%1$s" class="dipi-at-panel-image-link dipi-at-panel-image" %2$s><img src="%3$s" alt="%4$s" /></a>', esc_url( $image_link ), $image_link_target, esc_url( $image ), esc_attr( $alt ) );
				} else {
					$image_render = sprintf( '<img class="dipi-at-panel-image" src="%1$s" alt="%2$s" />', esc_url( $image ), esc_attr( $alt ) );
				}
			}
			$image_placement = $attrs['img_placement']['innerContent']['desktop']['value'] ?? "column";
			$image_placement_tablet = $attrs['img_placement']['innerContent']['tablet']['value'] ?? $image_placement;
			$image_placement_phone = $attrs['img_placement']['innerContent']['phone']['value'] ?? $image_placement_tablet;
			$content = static::getPropValue($attrs, 'content') ?? "";
			$render_html = sprintf('<div class="dipi-at-panel-content" data-imgplacement-desktop="%4$s" data-imgplacement-tablet="%5$s" data-imgplacement-phone="%6$s">
					%1$s
					<div class="dipi-at-panel-text">
						%2$s
						<div class="dipi-at-btn-wrap">%3$s</div>
					</div>
				</div>', 
				$image_render, 
				$content, 
				$button, 
				$image_placement, 
				$image_placement_tablet, 
				$image_placement_phone
			);
		}

		$render_html = sprintf(
			'<div class="et_pb_module_inner">
				<div class="dipi-at-panel">%1$s</div>
			</div>',
			$render_html
		);

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
				'classnamesFunction'  => [ AdvancedTabsItem::class, 'module_classnames' ],
				'stylesComponent'     => [ AdvancedTabsItem::class, 'module_styles' ],
				'scriptDataComponent' => [ AdvancedTabsItem::class, 'module_script_data' ],
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
