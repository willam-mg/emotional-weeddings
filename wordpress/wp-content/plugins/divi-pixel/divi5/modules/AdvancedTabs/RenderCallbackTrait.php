<?php
/**
 * AdvancedTabs::render_callback()
 *
 * @package DIPI\Modules\AdvancedTabs
 * @since ??
 */

namespace DIPI\Modules\AdvancedTabs;

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

	public static function render_tabs($attrs, $content, $block, $elements) {
		$tabs_placement = static::getPropValue($attrs, 'tabs_placement') ?? "column";
		$arrows = array(
            'column'         => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 255 127.5" width="30px"><g><polygon points="0 0 127.5 127.5 255 0 0 0"/></g></svg>',
            'column-reverse' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 255 127.5" width="30px"><g><polygon points="255 127.5 127.5 0 0 127.5 255 127.5"/></g></svg>',
            'row'            => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 127.5 255" height="30px"><g><polygon points="0 255 127.5 127.5 0 0 0 255"/></g></svg>',
            'row-reverse'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 127.5 255" height="30px"><g><polygon points="127.5 0 0 127.5 127.5 255 127.5 0"/></g></svg>'
        );
		$arrow = static::getPropValue($attrs, 'use_active_arrow') === 'on' ? $arrows[$tabs_placement] : '';
		
		$tabs_html = "";

		for ($i = 0; $i < count($block->parsed_block['innerBlocks']); $i ++) { 
			$child = $block->parsed_block['innerBlocks'][$i];
			$classname = "dipi_advanced_tabs_item_" . $child['orderIndex'];
			$tab = [];
			$child_attrs = $child['attrs'];
			foreach ($child_attrs as $key => $value) {
				$tab[$key] = isset($child_attrs[$key]['innerContent']) ? static::getPropValue($child_attrs, $key) : '';
			}
			$tab_text = '';
            $media = '';
            $extra_class = '';
			if(!empty($tab['title']) || !empty($tab['subtitle'])){
				$title_html = sprintf('<div class="dipi-at-tab-title">%1$s</div>', isset($tab['title']) ? esc_html($tab['title']) : '');
				$subtitle_html = sprintf('<div class="dipi-at-tab-subtitle">%1$s</div>', isset($tab['subtitle']) ? esc_html($tab['subtitle']) : '');
				$tab_text = sprintf('<div class="dipi-at-tab-container">%1$s %2$s</div>', $title_html, $subtitle_html);
			}

			if(isset($tab['tab_media']) && $tab['tab_media'] === 'icon') {
                $font_icon = "";
				if(isset($tab['font_icon']) && !empty($tab['font_icon'])){
					$font_icon = $tab['font_icon']['unicode'];
				}
				if(isset($tab['use_active_tab_icon']) && $tab['use_active_tab_icon'] === 'on'){
                	$font_icon_active = "";
					if(isset($tab['font_icon_active']) && !empty($tab['font_icon_active'])){
						$font_icon_active = $tab['font_icon_active']['unicode'];
					}
				} else {
					$font_icon_active = $font_icon;
				}
                $media = sprintf(
					'<span class="at-media-wrap">
                    	<span class="et-pb-icon dipi-tab-media dipi-tab-media--normal">%1$s</span>
                    	<span class="et-pb-icon dipi-tab-media dipi-tab-media--active">%2$s</span>
                    </span>',
                    esc_html( $font_icon ),
                    esc_html( $font_icon_active )
                );
            }

            if(isset($tab['tab_media']) && $tab['tab_media'] === 'image') {
                $tab_image_placement = $tab['tab_image_placement'] ?? 'top';
				$tab_image = (isset( $tab['tab_image']) && !empty( $tab['tab_image'])) ?  $tab['tab_image'] : '';
				$use_active_tab_image = $tab['use_active_tab_image'] ?? 'off';
				$tab_image_active = (isset( $tab['tab_image_active']) && !empty( $tab['tab_image_active']) && $use_active_tab_image === 'on') ?  $tab['tab_image_active'] : $tab_image;
				$media = sprintf('
                    <span class="at-media-wrap ">
                        <span class=" dipi-tab-media dipi-tab-media--normal">
                            <img src="%1$s"   />
                        </span>
                        <span class=" dipi-tab-media dipi-tab-media--active">
                            <img src="%2$s"   />
                        </span>
                    </span>', 
                    esc_url( $tab_image ),
                    esc_url( $tab_image_active )
                );
				if($tab_image_placement === 'left' || $tab_image_placement === 'right'){
					$extra_class .= ' dipi-at-horz-media';
				}
			}

			$activate_selector = $tab['activate_tab_selector'] ?? '';
			$scroll_tab_offset = $tab['scroll_tab_offset'] ?? '100px';

		$activate_selector_data = (isset($activate_selector) && !empty($activate_selector)) ? sprintf('data-activate-selector="%1$s"', esc_attr( $activate_selector )) : '';
		$scroll_tab_offset_data = (isset($scroll_tab_offset) && !empty($scroll_tab_offset)) ? sprintf('data-tab-scroll-off="%1$s"', esc_attr( $scroll_tab_offset )) : '';

			$tabs_html .= sprintf(
				'<div class="dipi-at-tab %3$s %6$s" data-panel="%3$s" %7$s %8$s> 
                    %4$s   
                    %1$s
                    %5$s
                </div>',
				$tab_text, // #1
				'',  // #2
				esc_attr($classname), // #3 
				$media, // #4
				$arrow, // #5
				$extra_class,
				$activate_selector_data,
				$scroll_tab_offset_data
			);
		}

		return $tabs_html;
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
		$children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];

        $parent       = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
		$parent_attrs = $parent->attrs ?? [];

		$tb_slider = '';
        $json_slider_settings = '';
        $next_icon_render = '';
        $prev_icon_render = '';

		// Check if tabs slider is enabled for any viewport
		$enable_ts_desktop = $attrs['enable_ts']['innerContent']['desktop']['value'] ?? "off";
		$enable_ts_tablet = $attrs['enable_ts']['innerContent']['tablet']['value'] ?? $enable_ts_desktop;
		$enable_ts_phone = $attrs['enable_ts']['innerContent']['phone']['value'] ?? $enable_ts_tablet;
		
		$is_slider_enabled = $enable_ts_desktop === "on" || $enable_ts_tablet === "on" || $enable_ts_phone === "on";

		if($is_slider_enabled) {
			$tb_slider = 'dipi-at-slider';
			$slider_settings = [
				'allow_touch_move' => static::getPropValue($attrs, 'allow_touch_move') ?? "on",
				'enable_ts_on_wide' => $enable_ts_desktop,
				'tabs_per_view_wide' => $attrs['tabs_per_view']['innerContent']['desktop']['value'] ?? 2,
				'ts_navigation_wide' => $attrs['ts_navigation']['innerContent']['desktop']['value'] ?? "off",
				'enable_ts_on_tab' => $enable_ts_tablet,
				'tabs_per_view_tab' => $attrs['tabs_per_view']['innerContent']['tablet']['value'] ?? 2,
				'ts_navigation_tab' => $attrs['ts_navigation']['innerContent']['tablet']['value'] ?? "off",
				'enable_ts_on_pho' => $enable_ts_phone,
				'tabs_per_view_pho' => $attrs['tabs_per_view']['innerContent']['phone']['value'] ?? 2,
				'ts_navigation_pho' => $attrs['ts_navigation']['innerContent']['phone']['value'] ?? "off",
			];
			$json_slider_settings = wp_json_encode($slider_settings);

			$data_next_icon = static::getPropValue($attrs, 'navigation_next_icon');
			$data_prev_icon = static::getPropValue($attrs, 'navigation_prev_icon');
			$next_icon_render = 'data-next-icon="9"';
			$navigation_next_icon_yn = static::getPropValue($attrs, 'navigation_next_icon_yn') ?? "off";
			if($navigation_next_icon_yn === "on") {
				$next_icon_render = sprintf('data-next-icon="%1$s"', esc_attr(Utils::process_font_icon($data_next_icon)));
			}
			$prev_icon_render = 'data-prev-icon="8"';
			$navigation_prev_icon_yn = static::getPropValue($attrs, 'navigation_prev_icon_yn') ?? "off";
			if($navigation_prev_icon_yn === "on") {
				$prev_icon_render = sprintf('data-prev-icon="%1$s"', esc_attr(Utils::process_font_icon($data_prev_icon)));
			}
		}

		$placement = array(
            'column'         => 'top',
            'column-reverse' => 'bottom',
            'row'            => 'left',
            'row-reverse'    => 'right'
        );

		$tabs_placement = static::getPropValue($attrs, 'tabs_placement') ?? "column";
		$arrow_class = 'has-arrow arrow-place-' . $placement[$tabs_placement];
		$arrow_align = static::getPropValue($attrs, 'arrow_align') ?? "center";
		$arrow_class .= " arrow-allign-" . $arrow_align;

		$tabs_html = static::render_tabs($attrs, $content, $block, $elements);

		$sticky_distance = $attrs['sticky_tabs_distance']['innerContent']['desktop']['value'] ?? "55px";
		$sticky_distance_tablet = $attrs['sticky_tabs_distance_tablet']['innerContent']['tablet']['value'] ?? $sticky_distance;
		$sticky_distance_phone = $attrs['sticky_tabs_distance_phone']['innerContent']['phone']['value'] ?? $sticky_distance_tablet;

		$ts_navigation_vertical_position_wide = $attrs['ts_navigation_vertical_position']['innerContent']['desktop']['value'] ?? "50";
		$ts_navigation_vertical_position_tab = $attrs['ts_navigation_vertical_position']['innerContent']['tablet']['value'] ?? $ts_navigation_vertical_position_wide;
		$ts_navigation_vertical_position_pho = $attrs['ts_navigation_vertical_position']['innerContent']['phone']['value'] ?? $ts_navigation_vertical_position_tab;
		$ts_navigation_horizontal_position_wide = $attrs['ts_navigation_horizontal_position']['innerContent']['desktop']['value'] ?? "0";
		$ts_navigation_horizontal_position_tab = $attrs['ts_navigation_horizontal_position']['innerContent']['tablet']['value'] ?? $ts_navigation_horizontal_position_wide;
		$ts_navigation_horizontal_position_pho = $attrs['ts_navigation_horizontal_position']['innerContent']['phone']['value'] ?? $ts_navigation_horizontal_position_tab;
		$use_scroll_to_content = $attrs['use_scroll_to_content']['innerContent']['desktop']['value'] ?? "off";
		$use_scroll_to_content_tablet = $attrs['use_scroll_to_content']['innerContent']['tablet']['value'] ?? $use_scroll_to_content;
		$use_scroll_to_content_phone = $attrs['use_scroll_to_content']['innerContent']['phone']['value'] ?? $use_scroll_to_content_tablet;
		$scroll_to_content_offset = $attrs['scroll_to_content_offset']['innerContent']['desktop']['value'] ?? "0";
		$scroll_to_content_offset_tablet = $attrs['scroll_to_content_offset']['innerContent']['tablet']['value'] ?? $scroll_to_content_offset;
		$scroll_to_content_offset_phone = $attrs['scroll_to_content_offset']['innerContent']['phone']['value'] ?? $scroll_to_content_offset_tablet;

		$order_number = $block->parsed_block['orderIndex'];
		$order_class = "dipi_advanced_tabs_" . $order_number;
		$default_tab = 0;

		for ($i = 0; $i < count($block->parsed_block['innerBlocks']); $i ++) { 
			$child = $block->parsed_block['innerBlocks'][$i];
			$is_default_tab = $child['attrs']['is_default_tab']['innerContent']['desktop']['value'] ?? "off";
			if($is_default_tab === "on") {
				$default_tab = $i;
				break;
			}
		}

		$data = [
			'tab_animation' => static::getPropValue($attrs, 'tab_animation') ?? "slide",
			'animation_duration' => static::getPropValue($attrs, 'dipi_animation_duration') ?? "500",
			'activate_on_hover' => static::getPropValue($attrs, 'activate_on_hover') ?? "off",
			'activate_first_tab_as_placeholder' => static::getPropValue($attrs, 'activate_first_tab_as_placeholder') ?? "off",
			'use_sticky_tabs'        => static::getPropValue($attrs, 'use_sticky_tabs') ?? "off",
			'use_scroll_to_content'  => htmlspecialchars(json_encode([
				'desktop' => $use_scroll_to_content,
				'tablet' => $use_scroll_to_content_tablet,
				'phone' => $use_scroll_to_content_phone,
			]), ENT_QUOTES, 'UTF-8'),
			'scroll_to_content_offset'  => htmlspecialchars(json_encode([
				'desktop' => $scroll_to_content_offset,
				'tablet' => $scroll_to_content_offset_tablet,
				'phone' => $scroll_to_content_offset_phone,
			]), ENT_QUOTES, 'UTF-8'),
			'ts_navigation_vertical_position'  => htmlspecialchars(json_encode([
				'desktop' => $ts_navigation_vertical_position_wide,
				'tablet' => $ts_navigation_vertical_position_tab,
				'phone' => $ts_navigation_vertical_position_pho
			]), ENT_QUOTES, 'UTF-8'),
			'ts_navigation_horizontal_position'  => htmlspecialchars(json_encode([
				'desktop' => $ts_navigation_horizontal_position_wide,
				'tablet' => $ts_navigation_horizontal_position_tab,
				'phone' => $ts_navigation_horizontal_position_pho
			]), ENT_QUOTES, 'UTF-8'),
			'sticky_distance'        => $sticky_distance,
			'sticky_distance_tablet' => $sticky_distance_tablet,
			'sticky_distance_phone'  => $sticky_distance_phone,
			'admin_bar_space'        => is_admin_bar_showing() ? true : false,
			'turn_off_sticky'        => static::getPropValue($attrs, 'turn_off_sticky') ?? "off",
			'module_class'           => $order_class,
			'default_tab'           => $default_tab
		];

		$dataset = '';
        foreach($data as $key => $value){
            $dataset .= ' data-' . $key . '="' . esc_attr($value) . '"';
        }

        $render_html = sprintf(
                '<div class="dipi-advanced-tabs dipi-at-container dipi-advanced-tabs-front" %4$s>
                    <div class="dipi-at-tabs-container %5$s" data-slider=\'%6$s\' %7$s %8$s >
                        <div class="dipi-at-tabs %3$s">%2$s</div>
                    </div>
                    <div class="dipi-at-panels">%1$s</div>
                </div>',
            $content,
            $tabs_html,
            $arrow_class,
            $dataset,
            $tb_slider, // #5
            $json_slider_settings,
            $next_icon_render,
            $prev_icon_render
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
				'classnamesFunction'  => [ AdvancedTabs::class, 'module_classnames' ],
				'stylesComponent'     => [ AdvancedTabs::class, 'module_styles' ],
				'scriptDataComponent' => [ AdvancedTabs::class, 'module_script_data' ],
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
