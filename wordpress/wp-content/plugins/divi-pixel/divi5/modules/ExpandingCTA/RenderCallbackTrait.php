<?php
/**
 * ExpandingCTA::render_callback()
 *
 * @package DIPI\Modules\ExpandingCTA
 * @since ??
 */

namespace DIPI\Modules\ExpandingCTA;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Packages\Module\Module;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use ET\Builder\Packages\Module\Options\Background\BackgroundComponentParallax;

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

    public static function _render_content($attrs, $elements, $id)
	{
		$url = static::getPropValue($attrs, 'url');
        $url_new_window = static::getPropValue($attrs, 'url_new_window');
        $parallax_image_background = BackgroundComponentParallax::component([
			'backgroundAttr' => $attrs['main_bg']['decoration']['background'] ?? null,
			'moduleId'       => $id
		]);

        $content_image_icon = '';
        $content_icon_selector = ".dipi_expanding_cta_$id.dipi_expanding_cta .dipi_expanding_cta_container .dipi_expanding_cta-content-wrapper .dipi-content-image-icon-wrap .et-pb-icon";
		$use_content_icon = static::getPropValue($attrs, 'use_content_icon');
        if ('on' == $use_content_icon) {
            $content_icon = static::getPropValue($attrs, 'content_icon');
            $content_icon = Utils::process_font_icon($content_icon);
            $content_image_icon = sprintf(
                '<div class="dipi-content-image-icon-wrap dipi-icon-wrap">
                    <span class="et-pb-icon et-pb-font-icon dipi-content-icon">%1$s</span>
                </div>',
                esc_attr($content_icon)
            );
        } 

		$content_image = static::getPropValue($attrs, 'content_image');
		// Handle both structured array {src, alt} and legacy string format
		$content_image_src = '';
		if (is_array($content_image) && isset($content_image['src'])) {
			$content_image_src = $content_image['src'];
		} elseif (is_string($content_image)) {
			$content_image_src = $content_image;
		}
		
		if ('on' !== $use_content_icon && $content_image_src && $content_image_src !== '') {
            $content_image_icon = $elements->render([
				'attrName' => 'content_image',
			]);
			if ($content_image_icon) {
				$content_image_icon = sprintf(
					'<div class="dipi-content-image-icon-wrap dipi-image-wrapper">%1$s</div>',
					$content_image_icon
				);
			}
        }

        $content_title_level = $attrs["content_title"]["decoration"]["font"]["font"]["headingLevel"] ?? 'h2';
        $content_title = '';
        if ('' !== static::getPropValue($attrs, 'content_title', "")) {
            $content_title = sprintf(
                '<%2$s class="dipi-content-heading">
                    %1$s
                </%2$s>',
                esc_attr(static::getPropValue($attrs, 'content_title')),
                esc_attr($content_title_level)
            );
        }

        $content_description = '';
        if (static::getPropValue($attrs, 'content_desc', "") !== "") {
            $content_description = sprintf(
                '<div class="dipi-desc">%1$s</div>',
                static::process_content(static::getPropValue($attrs, 'content_desc'))
            );
        }

        $show_content_button = static::getPropValue($attrs, 'show_content_button');
        $content_button = $elements->render([
			'attrName' => 'content_button',
		]);

        $show_second_button = static::getPropValue($attrs, 'show_second_button');
        $second_button = $elements->render([
			'attrName' => 'second_button',
		]);

        $content_html = sprintf(
            '%1$s
            <div class="dipi-content-text">
                %2$s
                %3$s
            </div>
            <div class="dipi-button-wrapper">
                %4$s %5$s
            </div>
          ',
            $content_image_icon,
            $content_title,
            $content_description,
            ($show_content_button === 'on') ? $content_button : '',
            ($show_second_button === 'on') ? $second_button : ''
        );
        if (!empty($url)) {
            $target = ('on' === $url_new_window) ? 'target="_blank"' : '';
            $content_html = sprintf(
                '<a href="%1$s" %2$s
              class="content_link dipi_expanding_cta-content">
              %3$s
            </a>',
                esc_url($url),
                et_core_intentionally_unescaped($target, 'fixed_string'),
                et_core_esc_previously($content_html)
            );
        } else {
            $content_html = sprintf(
                '<div
              class="dipi_expanding_cta-content">
              %1$s
            </div>',
                $content_html
            );
        }
        $content_html = sprintf(
            '%1$s
            <div class="dipi_expanding_cta-content-wrapper">
                %2$s
            </div>
            ',
            $parallax_image_background,
            $content_html
        );

        return $content_html;
	}

	public static function _render_overlay($attrs, $id)
    {
        $overlay_parallax_bg = '';
		$overlay_bg_parallax = $attrs['overlay_bg']['decoration']['background']['desktop']['value']['image']['parallax']['enabled'] ?? 'off';
        if ('on' == $overlay_bg_parallax) {
            $overlay_parallax_bg = BackgroundComponentParallax::component([
				'backgroundAttr' => $attrs['overlay_bg']['decoration']['background'] ?? null,
				'moduleId'       => $id
			]);
        }

        $overlay_html = sprintf(
            '<div class="dipi_extending_cta-overlay">
            %1$s
            </div>
            '
            ,
            $overlay_parallax_bg
        );
        return $overlay_html;
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
        $id = $block->parsed_block['id'];

		$hide_header = static::getPropValue($attrs, 'hide_header');
		$hide_backtotop = static::getPropValue($attrs, 'hide_backtotop');

        $config = [
			"hide_header" => $hide_header,
			"hide_backtotop" => $hide_backtotop,
			"dipi_expanding_cta_class" => "dipi_expanding_cta_$id",
			"dipi_expanding_cta_order_number" => $id
        ];

		$overlay_above_background = static::getPropValue($attrs, 'overlay_above_background');
        $overlay_html = static::_render_overlay($attrs, $id);

		$module_custom_classes = '';
        $render_html = sprintf(
            '<div class="dipi_expanding_cta_container %1$s"
                data-config="%5$s"
            >
                <div class="dipi_expanding_cta_container-background">
                    <span class="et_pb_background_pattern"></span>
					<span class="et_pb_background_mask"></span>
                </div>
                %2$s
                %3$s
            </div>
            %4$s
            ',
            $module_custom_classes,
            static::_render_content($attrs, $elements, $id),
            $overlay_above_background === 'on' ? $overlay_html : '',
            $overlay_above_background === 'on' ? '' : $overlay_html,
            esc_attr(htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8')) #5
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
				'classnamesFunction'  => [ ExpandingCTA::class, 'module_classnames' ],
				'stylesComponent'     => [ ExpandingCTA::class, 'module_styles' ],
				'scriptDataComponent' => [ ExpandingCTA::class, 'module_script_data' ],
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
