<?php
/**
 * ContentSlider::render_callback()
 *
 * @package DIPI\Modules\ContentSlider
 * @since ??
 */

namespace DIPI\Modules\ContentSlider;

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
		global $dipi_cs_selectors_string;
		$dipi_cs_selectors_string = "";

		$children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];

		$dipi_cs_selectors = [];
        $dipi_active_id = -1;
		$dipi_active_order_num = -1;

		$label_position = static::getPropValue($attrs, 'label_position');
        $show_active_selector_only_builder =static::getPropValue($attrs, 'show_active_selector_only_builder');
        $content_animation = static::getPropValue($attrs, 'content_animation');
        $move_slider_with_pin = static::getPropValue($attrs, 'move_slider_with_pin');
        $move_slider_with_progress_line = static::getPropValue($attrs, 'move_slider_with_progress_line');
        $move_slider_with_label = static::getPropValue($attrs, 'move_slider_with_label');
        $navigation_on_hover = static::getPropValue($attrs, 'navigation_on_hover');
		$data_next_icon = static::getPropValue($attrs, 'navigation_next_icon');
        $data_prev_icon = static::getPropValue($attrs, 'navigation_prev_icon');
        $data_next_icon = sprintf('data-icon="%1$s"', Utils::process_font_icon($data_next_icon));
        $data_prev_icon = sprintf('data-icon="%1$s"', Utils::process_font_icon($data_prev_icon));
        $next_icon_render = 'on' === static::getPropValue($attrs, 'navigation_next_icon_yn') ? $data_next_icon : 'data-icon="9"';
        $prev_icon_render = 'on' === static::getPropValue($attrs, 'navigation_prev_icon_yn') ? $data_prev_icon : 'data-icon="8"';
		$navigation = sprintf(
            '<div class="dipi-navigation">
                <div class="dipi-nav-button dipi-prev-button  %3$s" %1$s></div>
                <div class="dipi-nav-button dipi-next-button  %3$s" %2$s></div>
            </div>
                ',
            $prev_icon_render,
            $next_icon_render,
            $navigation_on_hover === "on" ? "show_on_hover" : ""
        );

		$active_item_indices = [];
		for ($i = 0; $i < count($block->parsed_block['innerBlocks']); $i ++) { 
			$child = $block->parsed_block['innerBlocks'][$i];
			$selector = $child['attrs']['selector']['innerContent']['desktop']['value'] ?? "";
			$active_item = $child['attrs']['active_item']['innerContent']['desktop']['value'] ?? "";
			if ($active_item === "on") {
				$active_item_indices[] = $i;
			}
			if ($dipi_active_id < 0 && $active_item === "on") {
				$dipi_active_id = $i;
				$dipi_active_order_num = $child["id"];
				// $dipi_active_order_num = child.props.shortcode_index;
			}
			if ($selector) {
				$dipi_cs_selectors[] = str_replace(";", "", $selector);
			} else {
				$dipi_cs_selectors[] = "";
			}
		}

        $dipi_cs_selectors_string = implode(",", array_filter($dipi_cs_selectors, function($var) { return $var && strlen(trim($var)); }));

		// Only "on" (string) means show only active in VB; anything else (null, "", etc.) = show all. Front-end JS ignores this.
		$show_only_active_value = ( is_string( $show_active_selector_only_builder ) && trim( $show_active_selector_only_builder ) === 'on' ) ? 'on' : 'off';

		$config = [
            'selectors' => implode(";", $dipi_cs_selectors),
            'child_count' => count($dipi_cs_selectors),
            'active_id' => $dipi_active_id,
            'active_order_num' => $dipi_active_order_num,
            'show_only_active' => $show_only_active_value,
            'active_item_indices' => $active_item_indices,
            'content_animation' => $content_animation,
            'move_slider_with_pin' => $move_slider_with_pin,
            'move_slider_with_progress_line' => $move_slider_with_progress_line,
            'move_slider_with_label' => $move_slider_with_label
        ];

		$extra_classes = "";
        if ($move_slider_with_pin === "on") {
            $extra_classes .= " slider_with_pin";
        }
        if ($move_slider_with_progress_line !== "disable") {
            $extra_classes .= " slider_with_line";
        }
        if ($move_slider_with_label !== "disable") {
            $extra_classes .= " slider_with_label";
        }
        if ($label_position !== "bottom") {
            $extra_classes .= " label-$label_position";
        }

		$render_html = sprintf(
            '<div class="dipi-content-slider %5$s" data-config="%2$s" data-active-id="%3$s" data-active-order-num="%4$s">
                <div class="dipi-progress-line">
                    <div class="dipi-progress-line-active"></div>
                    <div class="dipi-progress-line-event-placeholder"></div>
                    <span class="dipi-slider-pin"></span>
                </div>
                %6$s
                <div class="dipi-content-slider-items">%1$s</div>
            </div>',
            $content,
            esc_attr(htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8')) ,
            $dipi_active_id,
            $dipi_active_order_num,
            $extra_classes, #5,
            $navigation
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
				'classnamesFunction'  => [ ContentSlider::class, 'module_classnames' ],
				'stylesComponent'     => [ ContentSlider::class, 'module_styles' ],
				'scriptDataComponent' => [ ContentSlider::class, 'module_script_data' ],
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
				'childrenIds'         => $children_ids,
			]
		);
	}
}
