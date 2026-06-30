<?php
/**
 * Timeline::render_callback()
 *
 * @package DIPI\Modules\Timeline
 * @since ??
 */

namespace DIPI\Modules\Timeline;

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

	static function getPropValue($attrs, $prop) {
        $attr = $attrs[$prop] ?? null;
        if(!$attr) return null;
        return is_array($attr) && array_key_exists('innerContent', $attr) ? (isset($attr['innerContent']['desktop']['value']) ? $attr['innerContent']['desktop']['value'] : null) : (isset($attr['desktop']['value'])? $attr['desktop']['value']: null);
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

		$thisProps = [];
        foreach ($attrs as $key => $value) {
            $thisProps[$key] = static::getPropValue($attrs, $key);
        }

		$layout = $thisProps['layout'];
		$start_position = $thisProps['start_position'];
		$use_active_line = $thisProps['use_active_line'];
		$line_area_size = $thisProps['line_area_size'];
		$show_card_arrow = $thisProps['show_card_arrow'];
		$timline_line_html = '<div class="dipi-timeline-line"></div>';

		if ($use_active_line == "on") {
			$timline_line_html .='<div class="dipi-timeline-line__active"></div>';
		}
		
		$layout_tablet = isset($attrs['layout']['innerContent']['tablet']['value']) ? $attrs['layout']['innerContent']['tablet']['value'] : $layout;
		$layout_phone = isset($attrs['layout']['innerContent']['phone']['value']) ? $attrs['layout']['innerContent']['phone']['value'] : $layout_tablet;
		
		if ( is_rtl() && 'left' === $layout ) {
			$layout = 'right';
		}

		if ( is_rtl() && 'left' === $layout_tablet ) {
			$layout_tablet = 'right';
		}

		if ( is_rtl() && 'left' === $layout_phone ) {
			$layout_phone = 'right';
		}

		$module_custom_classes = 'dipi_timeline_custom_classes';
		$module_custom_classes .= sprintf( ' dipi_timeline_layout_%1$s', esc_attr( $layout ) );

		if ( ! empty( $layout_tablet ) ) {
			$module_custom_classes .=  " dipi_timeline_layout_{$layout_tablet}_tablet" ;
		} else {
			$module_custom_classes .=  " dipi_timeline_layout_right_tablet" ;
		}

		if ( ! empty( $layout_phone ) ) {
			$module_custom_classes .=  " dipi_timeline_layout_{$layout_phone}_phone" ;
		} else {
			$module_custom_classes .=  " dipi_timeline_layout_right_phone" ;
		}
		
		if ( ! empty( $start_position ) ) {
				$module_custom_classes .=  " startpos-{$start_position}" ;
		}
		
		if ($show_card_arrow == 'on') {
			$module_custom_classes .= " dipi_timeline_show-card-arrow" ;
		}

        $render_html = sprintf(
			'<div class="%3$s">
				<div class="dipi_timeline_container">
				<div class="dipi-timeline-items">%1$s</div>
				%2$s
				</div>
			</div>
			',
			$content,
			$timline_line_html,
			$module_custom_classes
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
				'classnamesFunction'  => [ Timeline::class, 'module_classnames' ],
				'stylesComponent'     => [ Timeline::class, 'module_styles' ],
				'scriptDataComponent' => [ Timeline::class, 'module_script_data' ],
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
