<?php
/**
 * TimelineItem::render_callback()
 *
 * @package DIPI\Modules\TimelineItem
 * @since ??
 */

namespace DIPI\Modules\TimelineItem;

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
		$order_number = $block->parsed_block['orderIndex'];
		$thisProps = [];
        foreach ($attrs as $key => $value) {
            $thisProps[$key] = static::getPropValue($attrs, $key);
        }

		$url = $thisProps['url'];
        $image = $thisProps['image'];
        $url_new_window = $thisProps['url_new_window'];

		$font_icon = $thisProps['font_icon'];
        $use_icon = $thisProps['use_icon'];
        $use_circle = $thisProps['use_circle'];
        $use_circle_border = $thisProps['use_circle_border'];
        $use_icon_font_size = $thisProps['use_icon_font_size'];
        $ribbon_use_circle = $thisProps['ribbon_use_circle'];
        $ribbon_use_circle_border = $thisProps['ribbon_use_circle_border'];
        $ribbon_use_icon_font_size = $thisProps['ribbon_use_icon_font_size'];
        $header_level = isset($attrs['header_font']['decoraton']['font']['font']['desktop']['value']['headingLevel']) ? $attrs['header_font']['decoraton']['font']['font']['desktop']['value']['headingLevel'] : 'h4';
		$icon_font_size = $thisProps['icon_font_size'];
		$timeline_icon_font_size = $thisProps['timeline_icon_font_size'];
		$image_max_width = $thisProps['image_max_width'];
		$card_max_width = $thisProps['card_max_width'];
		$custom_card_arrow = $thisProps['custom_card_arrow'];
		$child_animation = $thisProps['child_animation'];
		$anim_start_viewport = $thisProps['anim_start_viewport'];
		$icon_placement = $thisProps['icon_placement'];
		$icon_placement_tablet = isset($attrs['icon_placement']['innerContent']['tablet']['value']) ? $attrs['icon_placement']['innerContent']['tablet']['value'] : $icon_placement;
		$icon_placement_phone = isset($attrs['icon_placement']['innerContent']['phone']['value']) ? $attrs['icon_placement']['innerContent']['phone']['value'] : $icon_placement_tablet;
        $image_pathinfo = pathinfo($image);
		$is_image_svg = isset($image_pathinfo['extension']) ? 'svg' === $image_pathinfo['extension'] : false;
		$timeline_image = $thisProps['timeline_image'];
		$use_timeline_icon = $thisProps['use_timeline_icon'];
		$timeline_icon = $thisProps['timeline_icon'];

		$ribbon = sprintf(
            '<div class="dipi_timeline_ribbon"><span class="dipi_timeline_ribbon_text">%s</span></div>',
            $thisProps['ribbon']
        );

	$title_target = $url_new_window === "on" ? "_blank" : "_self";
	$title = $url ? (
		sprintf('<a href="%s" target="%s">%s</a>', esc_url($url), $title_target, $thisProps['title'])
	) : (
		sprintf('<span>%s</span>', $thisProps['title'])
	);

		$title = sprintf(
            '<%1$s class="dipi_timeline_item_header">%2$s</%1$s>',
            $header_level,
			$title
		);

		$timeline_icon_class = "ribbon-icon";
		if ("on" === $ribbon_use_circle) {
			$timeline_icon_class .= " ribbon-icon-circle";
		}

		if ("on" === $ribbon_use_circle && "on" === $ribbon_use_circle_border) {
			$timeline_icon_class .= " ribbon-icon-circle-border";
		}

		if ("on" === $use_timeline_icon) {
			$timeline_icon = sprintf(
				'<span class="%s" data-icon="%s"></span>',
				$timeline_icon_class,
				Utils::process_font_icon($timeline_icon)
			);
		} else if ($timeline_image !== "") {
			$timeline_icon_class .= " ribbon-icon-image";
			$timeline_icon = sprintf(
				'<div class="%s"><img src="%s" class="dipi-content-image"/></div>',
				$timeline_icon_class,
				$timeline_image
			);
		} else {
			$timeline_icon = "";
		}

		$timeline_icon = sprintf(
			'<div class="et_pb_image_wrap ribbon-icon-wrap">%s</div>',
			$timeline_icon
		);

		$image_classes = "";
		if ("on" === $use_icon) {
			$image_classes = "et-pb-icon dipi_timeline_font_icon";
			if ("on" === $use_circle) {
				$image_classes .= " et-pb-icon-circle";
			}
			if ("on" === $use_circle && "on" === $use_circle_border) {
				$image_classes .= " et-pb-icon-circle-border";
			}
		}

		if ("on" === $use_icon && $font_icon) {
			$image = sprintf(
				'<span class="%s">%s</span>',
				$image_classes,
				Utils::process_font_icon($font_icon)
			);
		} else if ($image) {
			$image = sprintf(
				'<img src="%s" class="dipi-content-image" alt="%s" />',
				$image,
				$thisProps['alt']
			);
		}

		if ($image) {
			$image = sprintf(
				'<span class="et_pb_image_wrap">%s</span>',
				$image
			);
			$image = sprintf(
				'<div class="dipi_timeline_item_image">%s</div>',
				$image
			);
		}

		$module_custom_classes = "dipi_timeline_item_custom_classes";
		$module_custom_classes .= " dipi_timeline_item_position_" . $icon_placement;

		if ($icon_placement_tablet) {
			$module_custom_classes .= " dipi_timeline_item_position_" . $icon_placement_tablet . "_tablet";
		}

		if ($icon_placement_phone) {
			$module_custom_classes .= " dipi_timeline_item_position_" . $icon_placement_phone . "_phone";
		}

		if ("on" === $custom_card_arrow) {
			$module_custom_classes .= " dipi_timeline_item_custom-card-arrow";
		}

		$button = "";
		if ("on" === $thisProps['show_button']) {
			$button = $elements->render([
				'attrName' => "button",
			]);
		}

		$content_html = sprintf(
			'<div class="dipi_timeline_item_description">%s</div>',
			$thisProps['content']
		);

		$config = [
            'anim_name' => $child_animation,
            'anim_start_viewport' => $anim_start_viewport,
        ];

		$render_html = sprintf(
			'<div class="%1$s" data-config="%9$s ">
				<div class="dipi_timeline_item_container">
					%2$s
					%3$s
					<div class="dipi_timeline_item_card-wrap %4$s">
						<div class="dipi_timeline_item_card">
							%5$s
							<div class="dipi_timeline_item_content">
								%2$s
								<div class="dipi_timeline_item_content_text">
									%6$s
									%7$s
								</div>
								<div class="dipi_timeline_item_button_wrapper">
									%8$s
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>',
			$module_custom_classes,
			$ribbon,
			$timeline_icon,
			$child_animation !== 'none' ? ' need_animation ' : '',
			$image,
			$title,
			$content_html,
			$button,
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
				'classnamesFunction'  => [ TimelineItem::class, 'module_classnames' ],
				'stylesComponent'     => [ TimelineItem::class, 'module_styles' ],
				'scriptDataComponent' => [ TimelineItem::class, 'module_script_data' ],
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
