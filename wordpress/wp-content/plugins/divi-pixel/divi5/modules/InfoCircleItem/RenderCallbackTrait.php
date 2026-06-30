<?php
/**
 * InfoCircleItem::render_callback()
 *
 * @package DIPI\Modules\InfoCircleItem
 * @since ??
 */

namespace DIPI\Modules\InfoCircleItem;

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

	static function dipi_get_image_alt_by_url($image_url) {
		$attachment_id = attachment_url_to_postid($image_url);
		if ($attachment_id) {
			$alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
			return $alt_text;
		}
		return '';
	}

	static function _render_info_image_icon($thisProps)
	{
		$info_image_icon = '';
		$info_icon_selector = '%%order_class%%.dipi_info_circle_item .dipi_info_circle_item_container .dipi_info_circle-small .dipi-info-image-icon-wrap .et-pb-icon';

		if ('on' == $thisProps['use_info_icon']) {
			$icon = ($thisProps['info_icon'] === '%&quot;%%' || $thisProps['info_icon'] === '%"%%') ? '%%22%%' : $thisProps['info_icon'];
			$info_icon = Utils::process_font_icon($icon);
			$info_image_icon = sprintf(
				'<div class="dipi-info-image-icon-wrap dipi-icon-wrapper">
					<span class="et-pb-icon et-pb-font-icon dipi-info-icon">%1$s</span>
				</div>',
				esc_attr($info_icon)
			);
		} else if ('on' !== $thisProps['use_info_icon'] && !empty($thisProps['info_image'])) {
			if (is_array($thisProps['info_image']) && isset($thisProps['info_image']['src']) && $thisProps['info_image']['src'] !== '') {
				$info_image_src = $thisProps['info_image']['src'];
				$info_image_alt = isset($thisProps['info_image']['alt']) ? $thisProps['info_image']['alt'] : '';
			} else if (is_string($thisProps['info_image']) && $thisProps['info_image'] !== '') {
				$info_image_src = $thisProps['info_image'];
				$info_image_alt = isset($thisProps['info_image_alt']) ? $thisProps['info_image_alt'] : '';
			} else {
				$info_image_src = '';
				$info_image_alt = '';
			}
			
			if ($info_image_src !== '') {
				$info_image_alt = $info_image_alt ? $info_image_alt : static::dipi_get_image_alt_by_url($info_image_src);
				$info_image_icon = sprintf(
					'<div class="dipi-info-image-icon-wrap dipi-image-wrapper">
						<img src="%1$s" class="dipi-info-image" alt="%2$s">
					</div>',
					esc_attr($info_image_src),
					esc_attr($info_image_alt)
				);
			}
		}
		return sprintf(
			'<div class="dipi_info_circle_item-info_image_icon-wrapper">
			%1$s
			</div>
			',
			$info_image_icon
		);
		return $info_image_icon;
	}

	static function _render_content($thisProps, $elements)
	{
		$parallax_image_background = ""; //$this->get_parallax_image_background();
		$content_image_icon = '';
		$content_icon_selector = '%%order_class%%.dipi_info_circle_item .dipi_info_circle_item_container .dipi_info_circle_item-content-wrapper .dipi-content-image-icon-wrap .et-pb-icon';
		if ('on' == $thisProps['use_content_icon']) {
			$icon = ($thisProps['content_icon'] === '%&quot;%%' || $thisProps['content_icon'] === '%"%%') ? '%%22%%' : $thisProps['content_icon'];
			$content_icon = Utils::process_font_icon($icon);
			$content_image_icon = sprintf(
				'<div class="dipi-content-image-icon-wrap dipi-icon-wrap">
					<span class="et-pb-icon et-pb-font-icon dipi-content-icon">%1$s</span>
				</div>',
				esc_attr($content_icon)
			);

		} else if ('on' !== $thisProps['use_content_icon'] && !empty($thisProps['content_image'])) {
			if (is_array($thisProps['content_image']) && isset($thisProps['content_image']['src']) && $thisProps['content_image']['src'] !== '') {
				$content_image_src = $thisProps['content_image']['src'];
				$content_image_alt = isset($thisProps['content_image']['alt']) ? $thisProps['content_image']['alt'] : '';
			} else if (is_string($thisProps['content_image']) && $thisProps['content_image'] !== '') {
				$content_image_src = $thisProps['content_image'];
				$content_image_alt = isset($thisProps['content_image_alt']) ? $thisProps['content_image_alt'] : '';
			} else {
				$content_image_src = '';
				$content_image_alt = '';
			}
			
			if ($content_image_src !== '') {
				$content_image_alt = $content_image_alt ? $content_image_alt : static::dipi_get_image_alt_by_url($content_image_src);
				$content_image_icon = sprintf(
					'<div class="dipi-content-image-icon-wrap dipi-image-wrapper">
						<img src="%1$s" class="dipi-content-image" alt="%2$s">
					</div>',
					esc_attr($content_image_src),
					esc_attr($content_image_alt) 
				);
			}
		}

		$content_title = '';
		if (isset($thisProps['content_title']) && '' !== $thisProps['content_title']) {
			$content_title = sprintf(
				'<%2$s class="dipi-content-heading">
					%1$s
				</%2$s>',
				esc_attr($thisProps['content_title']),
				esc_attr($thisProps['content_title_level'])
			);
		}

		$content_desc = '';
		if (isset($thisProps['content_desc']) && $thisProps['content_desc'] != '') {
			$content_desc = sprintf(
				'<div class="dipi-desc">%1$s</div>',
				static::process_content($thisProps['content_desc'])
			);
		}
		
		$content_html = sprintf(
			'%1$s
			<div class="dipi-content-text">
			%2$s
			%3$s
			</div>
			',
			$content_image_icon,
			$content_title,
			$content_desc
		);

		$show_content_button        = $thisProps['show_content_button'];
		if ($show_content_button === 'on') {
			$content_button = $elements->render([
				'attrName' => 'content_button',
			]);
			$content_html = sprintf(
				'%1$s
				%2$s
				',
				et_core_esc_previously( $content_html ),
				$content_button
			);
		}

		$content_html = sprintf(
			'%1$s
			<div
				class="dipi_info_circle_item-content-wrapper animated">
				%2$s
			</div>
			',
			$parallax_image_background,
			$content_html
		);

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
		$order_number = $block->parsed_block['orderIndex'];
		$beforeSiblings = BlockParserStore::get_siblings( $block->parsed_block['id'], "before");

		$thisProps = [];
        foreach ($attrs as $key => $value) {
            $thisProps[$key] = static::getPropValue($attrs, $key);
        }
		$thisProps['content_title_level'] = $attrs["content_title"]["decoration"]["font"]["font"]["desktop"]["value"]["headingLevel"] ?? "h2";

    	$module_custom_classes = '';
		$render_html = sprintf(
			'<div class="dipi_info_circle_item_container  %1$s dipi_info_circle_item-%4$s %5$s" data-index="%4$s">
				<div class="dipi-info-circle dipi_info_circle-small animated">
				%2$s
				</div>
				<div class="dipi-info-circle dipi_info_circle-in">
				%3$s
				</div>
			</div>',
			$module_custom_classes,
			static::_render_info_image_icon($thisProps),
			static::_render_content($thisProps, $elements),
			count($beforeSiblings),
			count($beforeSiblings) === 0 ? ' active' : ''
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
				'classnamesFunction'  => [ InfoCircleItem::class, 'module_classnames' ],
				'stylesComponent'     => [ InfoCircleItem::class, 'module_styles' ],
				'scriptDataComponent' => [ InfoCircleItem::class, 'module_script_data' ],
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
