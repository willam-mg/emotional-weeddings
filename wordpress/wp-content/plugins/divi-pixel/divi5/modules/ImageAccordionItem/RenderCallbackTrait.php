<?php
/**
 * ImageAccordionItem::render_callback()
 *
 * @package DIPI\Modules\ImageAccordionItem
 * @since ??
 */

namespace DIPI\Modules\ImageAccordionItem;

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
        $parent       = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
		$parent_attrs = $parent->attrs ?? [];

		$use_accordion_icon = static::getPropValue($attrs, 'use_accordion_icon');
		$accordion_icon_value = Utils::process_font_icon(static::getPropValue($attrs, 'accordion_icon'));
		$use_onload_active = $attrs['use_onload_active']['innerContent']['desktop']['value'] ?? "off";
		$use_onload_active_tablet = $attrs['use_onload_active']['innerContent']['tablet']['value'] ?? $use_onload_active;
		$use_onload_active_phone = $attrs['use_onload_active']['innerContent']['phone']['value'] ?? $use_onload_active_tablet;

		// Icon
        $accordion_icon = $accordion_icon_value !== '' ? sprintf(
            '<div class="dipi-accordion-image-icon">
                <span class="et-pb-icon et-pb-font-icon dipi-accordion-icon">
                    %1$s
                </span>
            </div>',
            esc_attr($accordion_icon_value)
        ) : '';

		// Image
		$accordion_image = '';
		$accordion_image_data = static::getPropValue($attrs, 'accordion_image');
		
		if (!empty($accordion_image_data)) {
			if (is_array($accordion_image_data) && isset($accordion_image_data['src']) && $accordion_image_data['src'] !== '') {
				$accordion_image_url = $accordion_image_data['src'];
				$img_alt = isset($accordion_image_data['alt']) ? $accordion_image_data['alt'] : '';
			} else if (is_string($accordion_image_data) && $accordion_image_data !== '') {
				$accordion_image_url = $accordion_image_data;
				$img_alt = static::getPropValue($attrs, 'img_alt');
			} else {
				$accordion_image_url = '';
				$img_alt = '';
			}
			
			if ($accordion_image_url) {
				$accordion_image = sprintf(
					'<div class="dipi-accordion-image-icon"><img src="%1$s" class="dipi-accordion-image" alt="%2$s"></div>',
					esc_url($accordion_image_url),
					esc_attr($img_alt)
				);
			}
		}

		// Condition checking for icon and image
        $accordion_image_icon = 'on' === $use_accordion_icon ? $accordion_icon : $accordion_image;

		// Title
        $accordion_title_level = $attrs["accordion_title"]["decoration"]["font"]["font"]["desktop"]["value"]["headingLevel"] ?? "h3";
		$accordion_title = '';
		if (isset($attrs['accordion_title']['innerContent'])) {
			$accordion_title = sprintf(
				'<%2$s class="dipi-accordion-title">%1$s</%2$s>',
				$elements->render(['attrName' => 'accordion_title']),
				esc_attr($accordion_title_level)
			);
		}

		// Description
		$accordion_description = '';
		if (isset($attrs['accordion_description']['innerContent'])) {
			$accordion_description = sprintf(
				'<div class="dipi-accordion-description">%1$s</div>',
				$elements->render(['attrName' => 'accordion_description'])
			);
		}

		$show_accordion_button = static::getPropValue($attrs, 'show_accordion_button');
		$accordion_button  = 'on' === $show_accordion_button ? sprintf(
            '<div class="dipi-accordion-button-wrap">%1$s</div>',
            $elements->render([
				'attrName' => 'accordion_button',
			])
        ) : '';

		$bg_img_html = $elements->render(['attrName' => 'bg_img']);
		$bg_img_wrapper = $bg_img_html ? sprintf(
			'<div class="dipi-bg-img-wrapper" style="position: absolute; top: 0; left: 0; width: 100%%; height: 100%%; z-index: 0; overflow: hidden;">
				%1$s
			</div>',
			$bg_img_html
		) : '';

		$render_html = sprintf(
            '<div class="dipi-ia-image-bg" style="position: relative;">
                %11$s
                <div class="dipi_image_accordion_bg %8$s"></div>
                <div class="dipi_image_accordion_bg_hover"></div>
                <div class="dipi_image_accordion_child_content_wrapper dipi-align-horizontal-%6$s dipi-align-vertical-%7$s">
                    <div class="dipi-accordion-content" data-active-on-load="%5$s" data-active-on-load-tablet="%9$s" data-active-on-load-phone="%10$s">
                        %1$s
                        %2$s
                        %3$s
                        %4$s
                    </div>
                </div>
            </div>',
            $accordion_image_icon,
            $accordion_title,
            $accordion_description,
            $accordion_button,
            ('on' === $use_onload_active), //#5
            static::getPropValue($attrs, 'accordion_align_horizontal'),
            static::getPropValue($attrs, 'accordion_align_vertical'),
            'dipi_hide_on_hover',
            ('on' === $use_onload_active_tablet), 
            ('on' === $use_onload_active_phone), //#10
            $bg_img_wrapper //#11
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
				'classnamesFunction'  => [ ImageAccordionItem::class, 'module_classnames' ],
				'stylesComponent'     => [ ImageAccordionItem::class, 'module_styles' ],
				'scriptDataComponent' => [ ImageAccordionItem::class, 'module_script_data' ],
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
