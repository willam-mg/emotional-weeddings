<?php
/**
 * InfoCircle::render_callback()
 *
 * @package DIPI\Modules\InfoCircle
 * @since ??
 */

namespace DIPI\Modules\InfoCircle;

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
		$children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];

		$icon_area_offset = $attrs['icon_area_offset']['innerContent']['desktop']['value'] ?? '0';
		$icon_area_offset_tablet = $attrs['icon_area_offset']['innerContent']['tablet']['value'] ?? $icon_area_offset;
		$icon_area_offset_phone = $attrs['icon_area_offset']['innerContent']['phone']['value'] ?? $icon_area_offset_tablet;

        $show_as_list = $attrs['show_as_list']['innerContent']['desktop']['value'] ?? 'off';
        $show_as_list_tablet = $attrs['show_as_list']['innerContent']['tablet']['value'] ?? $show_as_list;
        $show_as_list_phone = $attrs['show_as_list']['innerContent']['phone']['value'] ?? $show_as_list_tablet;
        
        $start_angle = $attrs['start_angle']['innerContent']['desktop']['value'] ?? '0';
        $start_angle_tablet = $attrs['start_angle']['innerContent']['tablet']['value'] ?? $start_angle;
        $start_angle_phone = $attrs['start_angle']['innerContent']['phone']['value'] ?? $start_angle_tablet;

		$thisProps = [];
        foreach ($attrs as $key => $value) {
            $thisProps[$key] = static::getPropValue($attrs, $key);
        }

		$info_image_icon_animation = $thisProps['info_image_icon_animation'];
        $select_event = $thisProps['select_event'];
        $content_animation = $thisProps['content_animation'];
        $auto_mode = $thisProps['auto_mode'];
        $auto_time = $thisProps['auto_time'];
        $auto_rotate_mode = $thisProps['auto_rotate_mode'];
        $reverse_anim_direction = $thisProps['reverse_anim_direction'];
        $auto_rotate_time = $thisProps['auto_rotate_time'];
        $auto_rotate_angle = $thisProps['auto_rotate_angle'];

        $config = [
            'animation' => $content_animation,
            'items_count' => count($block->parsed_block['innerBlocks']),
            'icon_area_offset' => (int)$icon_area_offset,
            'icon_area_offset_tablet' => (int)$icon_area_offset_tablet,
            'icon_area_offset_phone' => (int)$icon_area_offset_phone,
            'start_angle' => $start_angle,
            'start_angle_tablet' => $start_angle_tablet,
            'start_angle_phone' => $start_angle_phone,
        ];
        if ($auto_mode === 'on') {
            $config = array_merge(
                $config,
                [
                    'auto_mode' => $auto_mode,
                    'auto_time' => $auto_time,
                    'auto_rotate_mode' => $auto_rotate_mode,
                    'auto_rotate_time' => $auto_rotate_time,
                    'auto_rotate_angle' => $auto_rotate_angle,
                    'reverse_anim_direction' => $reverse_anim_direction,
                ]
            );
        }

        $module_custom_classes = '';

        if ($select_event == 'click') {
            $module_custom_classes .= " dipi-trigger_on_click";
        }
        if (!empty($show_as_list) && $show_as_list === 'on') {
            $module_custom_classes .= " dipi_info-circle_list";
        }

        if (!empty($show_as_list_tablet) && $show_as_list_tablet === 'on') {
            $module_custom_classes .= " dipi_info-circle_list_tablet";
        }

        if (!empty($show_as_list_phone) && $show_as_list_phone === 'on') {
            $module_custom_classes .= " dipi_info-circle_list_phone";
        }

        if ($info_image_icon_animation === 'on') {
            $module_custom_classes .= ' icon_ani';
        }

        $render_html = sprintf(
            '<div class="dipi_info_circle_container %2$s" data-config="%3$s">
                <div class="dipi-info-circle dipi-info-circle-out"></div>
                <div class="dipi-info-circle-items">%1$s</div>
            </div>
            ',
            $content,
            $module_custom_classes,
            esc_attr(htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8'))
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
				'classnamesFunction'  => [ InfoCircle::class, 'module_classnames' ],
				'stylesComponent'     => [ InfoCircle::class, 'module_styles' ],
				'scriptDataComponent' => [ InfoCircle::class, 'module_script_data' ],
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
