<?php
/**
 * Balloon::render_callback()
 *
 * @package DIPI\Modules\Balloon
 * @since ??
 */

namespace DIPI\Modules\Balloon;

if (!defined('ABSPATH')) {
	die('Direct access forbidden.');
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Packages\Module\Module;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use DIPI\Traits\BaseRenderTrait;
use DIPI\Utils\LayoutController;

trait RenderCallbackTrait
{
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

	static function dipi_get_image_alt_by_url($image_url)
	{
		$attachment_id = attachment_url_to_postid($image_url);
		if ($attachment_id) {
			$alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
			return $alt_text;
		}
		return '';
	}
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
	public static function render_callback($attrs, $content, $block, $elements)
	{

		$order_number = $block->parsed_block['orderIndex'];
		$content_type = static::getPropValue($attrs, 'content_type');
		$use_balloon_icon = static::getPropValue($attrs, 'use_balloon_icon');
		$balloon_icon = Utils::process_font_icon(static::getPropValue($attrs, 'balloon_icon'));
		$balloon_img = static::getPropValue($attrs, 'balloon_img');
		$img_alt = static::getPropValue($attrs, 'img_alt');
		$img_alt = isset($img_alt) ? $img_alt : self::dipi_get_image_alt_by_url($balloon_img);
		$balloon_title = static::getPropValue($attrs, 'balloon_title');
		$balloon_description = static::getPropValue($attrs, 'balloon_description');
		$interactive = static::getPropValue($attrs, 'interactive') === 'on' ? 'true' : 'false';
		$trigger = static::getPropValue($attrs, 'trigger_on_click') === 'on' ? 'click' : 'mouseenter focus';
		$append_to = static::getPropValue($attrs, 'append_to');
		$use_balloon_arrow = static::getPropValue($attrs, 'use_balloon_arrow') === 'on' ? 'true' : 'false';
		
        $is_tb_header = isset($block->parsed_block['layout_type']) && $block->parsed_block['layout_type'] === 'et_header_layout';
        $is_tb_footer = isset($block->parsed_block['layout_type']) && $block->parsed_block['layout_type'] === 'et_footer_layout';
        if($is_tb_header){
            $order_class = "dipi_balloon_{$order_number}_tb_header";
        }else if($is_tb_footer){
            $order_class = "dipi_balloon_{$order_number}_tb_footer";
        } else {
            $order_class = "dipi_balloon_" . $order_number;
        }
        
		$content_alignment = static::getPropValue($attrs, 'content_alignment');
		$use_cta = static::getPropValue($attrs, 'use_cta');

		$ballon_animation = static::getPropValue($attrs, 'ballon_animation');

		if ($ballon_animation == 'fade') {
			$ballon_animation_animation = $ballon_animation;
		} else {
			$ballon_animation_animation = 'dipi-' . $ballon_animation;
		}

		// Custom animation
		$ballon_animation_class = '';
		if ($ballon_animation == 'fadeInLeft') {
			$ballon_animation_class = 'fadeInLeft';
		} elseif ($ballon_animation == 'fadeInRight') {
			$ballon_animation_class = 'fadeInRight';
		} elseif ($ballon_animation == 'fadeInUp') {
			$ballon_animation_class = 'fadeInUp';
		} elseif ($ballon_animation == 'fadeInDown') {
			$ballon_animation_class = 'fadeInDown';
		}

		$balloon_icon_image = '';
		if ('on' === $use_balloon_icon) {
			$balloon_icon_image = sprintf('
                <div class="dipi-balloon-image-icon">
                    <div class="et-pb-icon dipi-balloon-icon">%1$s</div>
                </div>',
				esc_attr($balloon_icon)
			);
		} else if ($balloon_img !== '') {
			$balloon_icon_image = sprintf('
                <div class="dipi-balloon-image-icon">
                    <img src="%1$s" class="dipi-balloon-image" alt="%2$s">
                </div>',
				esc_url($balloon_img['src']),
				esc_attr($img_alt)
			);
		}

		$title = '';

		$balloon_title_level = isset($attrs['balloon_title']['decoration']['font']['font']['desktop']['value']['headingLevel']) ? $attrs['balloon_title']['decoration']['font']['font']['desktop']['value']['headingLevel'] : 'h2';

		if ($balloon_title !== '') {
			$title = sprintf('
                <%2$s class="dipi-balloon-title">
                    %1$s
                </%2$s>',
				esc_html($balloon_title),
				esc_attr($balloon_title_level)
			);
		}

		$description = '';
		if ($balloon_description !== '') {
			$description = sprintf('
                <div class="dipi-balloon-description"> %1$s </div>',
				self::process_content($balloon_description)
			);
		}

		$button = '';
		if ('on' === $use_cta) {
			$button = $elements->render([
				'attrName' => 'button',
				'attributes' => [
					'class' => 'dipi-balloon-cta',
				]
			]);
		}
		if ('' !== $button) {
			$button = sprintf('
                <div class="dipi-balloon-cta-wrap">
                    %1$s
                </div>',
				$button
			);
		}

		$selector = static::getPropValue($attrs, 'selector');

		$ballon_placement = isset($attrs['ballon_placement']['innerContent']['desktop']['value'])
			? $attrs['ballon_placement']['innerContent']['desktop']['value'] : '';
		$ballon_placement_tablet = isset($attrs['ballon_placement']['innerContent']['tablet']['value'])
			&& !empty($attrs['ballon_placement']['innerContent']['tablet']['value'])
			? $attrs['ballon_placement']['innerContent']['tablet']['value'] : $ballon_placement;
		$ballon_placement_phone = isset($attrs['ballon_placement']['innerContent']['phone']['value'])
			&& !empty($attrs['ballon_placement']['innerContent']['phone']['value'])
			? $attrs['ballon_placement']['innerContent']['phone']['value'] : $ballon_placement_tablet;

		$content = '';
		if ('manual' === $content_type) {
			$content = sprintf('
                <div class="%5$s">
                    <div class="dipi-balloon-wrap dipi-alignment-%6$s">
                        %1$s
                        %2$s
                        %3$s
                        %4$s
                    </div>
                </div>',
				$balloon_icon_image,
				$title,
				$description,
				$button,
				"dipi-balloon $order_class",
				$content_alignment
			);
		} else {
			$divi_library = static::getPropValue($attrs, 'divi_library');
			// $divi_library_shortcode = !empty($divi_library) ? do_shortcode('[et_pb_section global_module="' . $divi_library . '"][/et_pb_section]') : "";
            $layout_content = !empty($divi_library) ? LayoutController::render_divi_layout($divi_library, false) : '';
			$content = sprintf('
                <div class="%2$s">
                    <div class="dipi-balloon-wrap dipi-alignment-%3$s">
                        %1$s
                    </div>
                </div>
                ',
				$layout_content,
				"dipi-balloon $order_class",
				$content_alignment
			);
		}

		$tippy_box_class = sprintf(
			'.dipi-balloon-open-%1$s .tippy-box',
			$order_number
		);

		$render_html = sprintf('
            <div class="dipi_balloon_inner" style="position:absolute;opacity:0;left:-99999px; top:-9999px;"
            
                data-order_number="%2$s"
                data-selector="%3$s"
                data-animation="%4$s"
                data-append_to="%5$s"
                data-ballon_placement_desktop="%6$s"
                data-ballon_placement_tablet="%7$s"
                data-ballon_placement_phone="%8$s"
                data-interactive="%9$s"
                data-trigger="%10$s"
                data-use_arrow="%11$s"
                data-d5_tb_header_class="%12$s"
                data-d5_tb_footer_class="%13$s"
            >
                <div class="et_pb_section dipi_balloon-inner">
                    %1$s
                </div>
            </div>',
			$content, #1
			esc_attr($order_number), #2
			esc_attr($selector), #3
			esc_attr($ballon_animation_animation), #4
			esc_attr($append_to), #5
			esc_attr($ballon_placement), #6
			esc_attr($ballon_placement_tablet), #7
			esc_attr($ballon_placement_phone), #8
			esc_attr($interactive),#9
			esc_attr($trigger), #10
			esc_attr($use_balloon_arrow), #11
            $is_tb_header ? 'dipi_is_tb_header' : '',
            $is_tb_footer ? 'dipi_is_tb_footer' : ''
		);

		$parent = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);
		$parent_attrs = $parent->attrs ?? [];

		return Module::render(
			[
				// FE only.
				'orderIndex' => $block->parsed_block['orderIndex'],
				'storeInstance' => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'attrs' => $attrs,
				'elements' => $elements,
				'id' => $block->parsed_block['id'],
				'name' => $block->block_type->name,
				'moduleCategory' => $block->block_type->category,
				'classnamesFunction' => [Balloon::class, 'module_classnames'],
				'stylesComponent' => [Balloon::class, 'module_styles'],
				'scriptDataComponent' => [Balloon::class, 'module_script_data'],
				'parentAttrs' => $parent_attrs,
				'parentId' => $parent->id ?? '',
				'parentName' => $parent->blockName ?? '',
				'children' => ElementComponents::component(
					[
						'attrs' => $attrs['module']['decoration'] ?? [],
						'id' => $block->parsed_block['id'],

						// FE only.
						'orderIndex' => $block->parsed_block['orderIndex'],
						'storeInstance' => $block->parsed_block['storeInstance'],
					]
				) . $render_html,
			]
		);
	}
}
