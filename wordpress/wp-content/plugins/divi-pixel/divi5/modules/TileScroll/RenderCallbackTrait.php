<?php
/**
 * TileScroll::render_callback()
 *
 * @package DIPI\Modules\TileScroll
 * @since ??
 */

namespace DIPI\Modules\TileScroll;

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
		$content = self::sanitize_content($content);
		$content = str_replace(["&#91;", "&#93;"], ["[", "]"], $content);
		$content = do_shortcode($content);
		$content = str_replace(
			["<p><div", "</div></p>", "</div> <!-- .et_pb_section --></p>"],
			["<div", "</div>", "</div>"],
			$content
		);
		return $content;
	}

	static function dipi_get_image_alt_by_url($image_url) {
		$attachment_id = attachment_url_to_postid($image_url);
		if ($attachment_id) {
			$alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
			return $alt_text;
		}
		return '';
	}

	static function _render_content($attrs, $elements)
    {
        $parallax_image_background = "";
        $content_circle_icon = self::getPropValue($attrs, 'content_circle_icon');
        $content_image_icon = '';
        $content_image_icon_classes[] = '';
        if ('on' === $content_circle_icon) {
            $content_image_icon_classes[] = 'content-ico-circle';
        }
        $content_icon_selector = '%%order_class%% .dipi-tile-scroll-content-wrapper .dipi-content-image-icon-wrap .et-pb-icon';
		$content_icon = self::getPropValue($attrs, 'content_icon');
		$use_content_icon = $content_icon["useIcon"];

		if ('on' == $use_content_icon) {
            $content_image_icon = sprintf(
                '<div class="dipi-content-image-icon-wrap dipi-icon-wrap">
                    <span class="et-pb-icon et-pb-font-icon dipi-content-icon %2$s">%1$s</span>
                </div>',
                Utils::process_font_icon($content_icon["icon"]),
                implode(' ', $content_image_icon_classes)
            );
        } else if ('on' !== $use_content_icon && isset($content_icon['src']) && $content_icon['src'] !== '') {
            $content_img_alt = isset($content_icon['alt']) ? $content_icon['alt'] : self::dipi_get_image_alt_by_url($content_icon['src']);
            $content_image_icon = sprintf(
                '<div class="dipi-content-image-icon-wrap dipi-image-wrapper">
                    <div class="dipi-content-image">
                        <img src="%1$s" alt="%2$s">
                    </div>
                </div>',
                esc_attr($content_icon['src']),
                esc_attr($content_img_alt)
            );
        }
  
		$content_title_level = $attrs['content_title']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? 'h2';
		$content_title_text = self::getPropValue($attrs, 'content_title');
        $content_title = '';
        if ($content_title_text && '' !== $content_title_text) {
            $content_title = sprintf(
                '<%2$s class="dipi-content-heading">
                    %1$s
                </%2$s>',
                esc_attr($content_title_text),
                esc_attr($content_title_level)
            );
        }
  
        $content_description = '';
		$content_description_text = self::getPropValue($attrs, 'content_description');
        if ($content_description_text && !empty($content_description_text)) {
            $content_description = sprintf(
                '<div class="dipi-desc">%1$s</div>',
                self::process_content($content_description_text)
            );
        }
  
		$show_content_button = $attrs['content_button']['advanced']['use_button']["desktop"]["value"];
        $content_button = $elements->render(
			[
                'attrName' => 'content_button',
			]
		);

        $content_html = '';
        if ($content_image_icon || $content_title || $content_description || $show_content_button === 'on') {
            $content_html = sprintf(
            '%1$s
            <div class="dipi-content-text">
                %2$s
                %3$s
            </div>
            <div class="dipi-button-wrapper">
                %4$s
            </div>
            ',
            $content_image_icon,
            $content_title,
            $content_description,
            ($show_content_button === 'on') ? $content_button : ''
            );
            $content_html = sprintf(
            '<div
                class="dipi-tile-scroll-content-wrapper">
                %1$s
            </div>',
            $content_html
            );
    
            $content_html = sprintf(
                '<div class="dipi-tile-scroll-content">
                    %1$s
                    %2$s
                </div>',
                $parallax_image_background,
                $content_html
            );
        }
        return $content_html;
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

        $anim_direction = self::getPropValue($attrs, 'anim_direction') ?? "row";
        $start_row_direction = self::getPropValue($attrs, 'start_row_direction') ?? "left";
        $start_col_direction = self::getPropValue($attrs, 'start_col_direction') ?? "top";
        $rotate_angle = self::getPropValue($attrs, 'rotate_angle') ?? "0";
        $move_amount = self::getPropValue($attrs, 'move_amount') ?? "20px";
        $use_overlay = self::getPropValue($attrs, 'use_overlay') ?? "off";

        $module_custom_classes = '';
       
        $config = [
            'items_count' => count($children_ids),
            'anim_direction' => $anim_direction,
            'start_row_direction' => $start_row_direction,
            'start_col_direction' => $start_col_direction,
            'move_amount' => $move_amount,
            'rotate_angle'=> $rotate_angle,
        ];
       
        $module_custom_classes = '';
        $module_custom_classes .= " anim_direct_{$anim_direction}";
        $start_direction = '';
        if ($anim_direction === 'row') {
            $start_direction = $start_row_direction;
        } else {
            $start_direction = $start_col_direction;
        }
        $module_custom_classes .= " anim_start_{$start_direction}";
        $overlay_html = $use_overlay === "on" ? '<div class="dipi-tile-scroll-overlay"></div>' : '';
        $render_html = sprintf(
            '<div class="dipi_tile_scroll_container %2$s" data-config="%3$s">
                <div class="dipi-tile-scroll-items">%1$s</div>    
                %5$s
                %4$s
            </div>
            ',
            $content,
            $module_custom_classes,
            esc_attr(htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8')),
            self::_render_content($attrs, $elements),
            $overlay_html #5
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
				'classnamesFunction'  => [ TileScroll::class, 'module_classnames' ],
				'stylesComponent'     => [ TileScroll::class, 'module_styles' ],
				'scriptDataComponent' => [ TileScroll::class, 'module_script_data' ],
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
