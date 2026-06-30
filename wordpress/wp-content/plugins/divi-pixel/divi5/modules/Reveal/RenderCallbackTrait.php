<?php
/**
 * Reveal::render_callback()
 *
 * @package DIPI\Modules\Reveal
 * @since ??
 */

namespace DIPI\Modules\Reveal;

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
        $order_number = $block->parsed_block['orderIndex'];

		$default_show_status = $attrs['default_show_status']['innerContent']['desktop']['value'] ?? "collapsed";
		$container_selector = $attrs['container_selector']['innerContent']['desktop']['value'] ?? "";
		$use_overlay = $attrs['use_overlay']['innerContent']['desktop']['value'] ?? "off";
		$overlay_as = $attrs['overlay_as']['innerContent']['desktop']['value'] ?? "css";
		$append_to = $attrs['append_to']['innerContent']['desktop']['value'] ?? "element";

		$less_container_height = $attrs['less_container_height']['innerContent']['desktop']['value'] ?? "100px";
        $less_container_height_tablet = isset($attrs['less_container_height']['innerContent']['tablet']['value']) ? $attrs['less_container_height']['innerContent']['tablet']['value']: $less_container_height;
        $less_container_height_phone = isset($attrs['less_container_height']['innerContent']['phone']['value']) ? $attrs['less_container_height']['innerContent']['phone']['value'] : $less_container_height_tablet;
        
        $show_less_button_text   = $attrs['show_less_button']['innerContent']['desktop']['value']['text'] ?? "Show Less";
        $show_less_button_output = $elements->render(
			[
                'attrName'      => 'show_less_button',
			]
		);
		$show_less_button_custom = $attrs['show_less_button']['decoration']['button']['desktop']['value']['enable'] ?? "off";
        $show_less_button_custom_icon = isset( $attrs['show_less_button']['decoration']['button']['desktop']['value']['icon']['settings']['unicode'] ) ? $attrs['show_less_button']['decoration']['button']['desktop']['value']['icon']['settings']['unicode'] : '';
        $show_less_button_custom_icon_tablet = isset( $attrs['show_less_button']['decoration']['button']['tablet']['value']['icon']['settings']['unicode'] ) ? $attrs['show_less_button']['decoration']['button']['tablet']['value']['icon']['settings']['unicode'] : $show_less_button_custom_icon;
        $show_less_button_custom_icon_phone = isset( $attrs['show_less_button']['decoration']['button']['phone']['value']['icon']['settings']['unicode'] ) ? $attrs['show_less_button']['decoration']['button']['phone']['value']['icon']['settings']['unicode'] : $show_less_button_custom_icon_tablet;

        $show_more_button_text   = $attrs['show_more_button']['innerContent']['desktop']['value']['text'] ?? "Show More";
        $show_more_button_output = $elements->render(
			[
                'attrName'      => 'show_more_button',
			]
		);
		$show_more_button_custom = $attrs['show_more_button']['decoration']['button']['desktop']['value']['enable'] ?? "off";
        $show_more_button_custom_icon = isset( $attrs['show_more_button']['decoration']['button']['desktop']['value']['icon']['settings']['unicode'] ) ? $attrs['show_more_button']['decoration']['button']['desktop']['value']['icon']['settings']['unicode'] : '';
        $show_more_button_custom_icon_tablet = isset( $attrs['show_more_button']['decoration']['button']['tablet']['value']['icon']['settings']['unicode'] ) ? $attrs['show_more_button']['decoration']['button']['tablet']['value']['icon']['settings']['unicode'] : $show_more_button_custom_icon;
        $show_more_button_custom_icon_phone = isset( $attrs['show_more_button']['decoration']['button']['phone']['value']['icon']['settings']['unicode'] ) ? $attrs['show_more_button']['decoration']['button']['phone']['value']['icon']['settings']['unicode'] : $show_more_button_custom_icon_tablet;

        $button_output = $default_show_status === "collapsed" ? $show_more_button_output : $show_less_button_output;
		
        $config = [
            'container_selector' => $container_selector,
            'default_show_status' => $default_show_status,
            'order_number' => $order_number,
            'use_overlay' => $use_overlay,
            'show_less_button_text' => $show_less_button_text,
            'show_more_button_text' => $show_more_button_text,
            'less_container_height' => $less_container_height,
            'less_container_height_tablet' => $less_container_height_tablet,
            'less_container_height_phone' => $less_container_height_phone,
            'overlay_as' => $overlay_as,
            'append_to' => $append_to,
        ];

		$render_html = sprintf(
			'<div
                class="dipi-reveal"
                data-config="%1$s"
				data-less-icon="%3$s"
                data-less-icon-tablet="%4$s"
                data-less-icon-phone="%5$s"
                data-more-icon="%6$s"
                data-more-icon-tablet="%7$s"
                data-more-icon-phone="%8$s"
            >
                %2$s
            </div>',
			esc_attr(htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8')),
            $button_output,
			$show_less_button_custom === "on" ? $show_less_button_custom_icon : "5",
            $show_less_button_custom === "on" ? $show_less_button_custom_icon_tablet : "5",
            $show_less_button_custom === "on" ? $show_less_button_custom_icon_phone : "5",
            $show_more_button_custom === "on" ? $show_more_button_custom_icon : "5",
            $show_more_button_custom === "on" ? $show_more_button_custom_icon_tablet : "5",
            $show_more_button_custom === "on" ? $show_more_button_custom_icon_phone : "5"
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
				'classnamesFunction'  => [ Reveal::class, 'module_classnames' ],
				'stylesComponent'     => [ Reveal::class, 'module_styles' ],
				'scriptDataComponent' => [ Reveal::class, 'module_script_data' ],
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
