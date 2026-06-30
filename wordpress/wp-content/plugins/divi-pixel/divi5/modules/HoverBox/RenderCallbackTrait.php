<?php
/**
 * HoverBox::render_callback()
 *
 * @package DIPI\Modules\HoverBox
 * @since ??
 */

namespace DIPI\Modules\HoverBox;

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
	private static function _render_content($attrs, $content, $block, $elements)
    {
        $content_icon_image = '';
        $attr_use_content_icon = $attrs['use_content_icon']['innerContent']['desktop']['value'];
        $attr_content_circle_icon = $attrs['content_circle_icon']['innerContent']['desktop']['value'];
        $attr_content_circle_border = $attrs['content_circle_border']['innerContent']['desktop']['value'];
        $attr_content_icon = $attrs['content_icon']['innerContent']['desktop']['value'];
        $attr_content_image = $attrs['content_image']['innerContent']['desktop']['value']['src'];
        $attr_content_image_alt = $attrs['content_image']['innerContent']['desktop']['value']['alt'];

        if ('on' == $attr_use_content_icon) {

            $content_circle_icon_class = ('on' === $attr_content_circle_icon) ? ' dipi-content-icon-circle' : '';
            $content_border_icon_class = ('on' === $attr_content_circle_border) ? ' dipi-content-icon-border' : '';
            $content_icon = Utils::process_font_icon($attr_content_icon);
            $content_icon_image = sprintf(
                '<div class="dipi-content-image-icon-wrap dipi-icon-wrap">
                    <span class="et-pb-icon et-pb-font-icon dipi-hover-box-content-icon%2$s%3$s">%1$s</span>
                </div>',
                esc_attr($content_icon),
                $content_circle_icon_class,
                $content_border_icon_class
            );

            // $this->dipi_generate_font_icon_styles($render_slug, 'content_icon', '%%order_class%% .dipi-hover-box-content .dipi-hover-box-content-icon');
        } else if ('on' !== $attr_use_content_icon && $attr_content_image !== '') {
            $content_icon_image = sprintf(
                '<div class="dipi-content-image-icon-wrap dipi-image-wrap">
                    <img src="%1$s" alt="%2$s">
                </div>',
                esc_attr($attr_content_image),
                $attr_content_image_alt
            );
        }
        
        // Title.
		$content_title = $elements->render(
			[
				'attrName' => 'content_title',
			]
		);

		// Content.
		$body_text = $elements->render(
			[
				'attrName' => 'body_text',
			]
		);
        

        $content_parallax_bg = '';

        return sprintf(
            '<div class="dipi-hover-box-content">
              %1$s
              <div class="dipi-hover-box-content-innner">
                    %2$s
                    <div class="dipi-text">
                        %3$s
                        %4$s
                    </div>
              </div>
            </div>
            ',
            $content_parallax_bg,
            $content_icon_image,
            $content_title,
            $body_text
        );
        
    }
	private static function _render_content_hover($attrs, $content, $block, $elements)
    {
        $content_hover_icon_image = '';
        $attr_use_content_hover_icon = $attrs['use_content_hover_icon']['innerContent']['desktop']['value'];
        $attr_content_hover_circle_icon = $attrs['content_hover_circle_icon']['innerContent']['desktop']['value'];
        $attr_content_hover_circle_border = $attrs['content_hover_circle_border']['innerContent']['desktop']['value'];
        $attr_content_hover_icon = $attrs['content_hover_icon']['innerContent']['desktop']['value'];
        $attr_content_hover_image = $attrs['content_hover_image']['innerContent']['desktop']['value']['src'];
        $attr_content_hover_image_alt = $attrs['content_hover_image']['innerContent']['desktop']['value']['alt'];
        $attr_use_content_hover_button = $attrs['use_content_hover_button']['innerContent']['desktop']['value'];
        if ('on' == $attr_use_content_hover_icon) {

            $hover_circle_icon_class = ('on' === $attr_content_hover_circle_icon) ? ' dipi-hover-icon-circle' : '';
            $hover_border_icon_class = ('on' === $attr_content_hover_circle_border) ? ' dipi-hover-icon-border' : '';
            $icon_hover = ($attr_content_hover_icon === '%&quot;%%' || $attr_content_hover_icon === '%"%%') ? '%%22%%' : $attr_content_hover_icon;
            $content_hover_icon = Utils::process_font_icon($attr_content_hover_icon);
            
            $content_hover_icon_image = sprintf(
                '<div class="dipi-hover-image-icon-wrap dipi-icon-wrap">
                    <span class="et-pb-icon et-pb-font-icon dipi-hover-box-hover-icon %2$s%3$s">
                        %1$s
                    </span>
                </div>',
                esc_attr($content_hover_icon),
                $hover_circle_icon_class,
                $hover_border_icon_class
            );

            // Font Icon Styles since Divi 4.13
            // $this->dipi_generate_font_icon_styles($render_slug, 'content_hover_icon', '%%order_class%% .dipi-hover-box-hover .dipi-hover-box-hover-icon');
        } else if ('on' !== $attr_use_content_hover_icon && $attr_content_hover_image !== '') {
            $content_hover_icon_image = sprintf(
                '<div class="dipi-hover-image-icon-wrap dipi-image-wrap">
                    <img class="dipi-hover-box-hover-imge" src="%1$s" alt="%2$s">
                </div>',
                $attr_content_hover_image,
                $attr_content_hover_image_alt
            );
        }

        
        // Title.
		$content_hover_title = $elements->render(
			[
				'attrName' => 'content_hover_title',
			]
		);

		// Content.
		$content_hover_content = $elements->render(
			[
				'attrName' => 'content_hover_content',
			]
		);

        $content_hover_button = '';
        if ('on' === $attr_use_content_hover_button) {

            $content_hover_button = $elements->render(
				[
					'attrName' => 'content_hover_button',
				]
				);
				 ;
        }

        $content_hover_parallax_bg = '';

        return sprintf(
            '<div class="dipi-hover-box-hover">
                %1$s
                <div class="dipi-hover-box-hover-innner">
                    %2$s
                    <div class="dipi-text">
                        %3$s
                        %4$s
                    </div>
                    %5$s
                </div>
            </div>',
            $content_hover_parallax_bg,
            $content_hover_icon_image,
            $content_hover_title,
            $content_hover_content,
            $content_hover_button
        );
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
        $hover_box_animation = 'dipi-hover-box-slide-top';
        $attr_hover_type = $attrs['hover_type']['innerContent']['desktop']['value'];
        $attr_hover_direction = $attrs['hover_direction']['innerContent']['desktop']['value'];
        $attr_hover_box_align_front = $attrs['hover_box_align_front']['innerContent']['desktop']['value'];
        $attr_hover_box_align_back = $attrs['hover_box_align_back']['innerContent']['desktop']['value'];
        $attr_use_force_square = $attrs['use_force_square']['innerContent']['desktop']['value'];
        if ('slide' === $attr_hover_type) {
            $hover_box_animation = "dipi-hover-box-slide-{$attr_hover_direction}";
        } else if ('fade' === $attr_hover_type) {
            $hover_box_animation = 'dipi-hover-box-fade';
        } else if ('zoom' === $attr_hover_type) {
            $hover_box_animation = 'dipi-hover-box-zoom';
        }

        $hover_box_align_front_class = sprintf(
            'hover_box_align_front_%1$s',
            $attr_hover_box_align_front
        );

        $hover_box_align_back_class = sprintf(
            'hover_box_align_back_%1$s',
            $attr_hover_box_align_back
        );

        $render_html =  sprintf(
            '<div class="dipi-hover-box-container %3$s %4$s %5$s" data-force_square="%6$s">
                <div class="dipi-hover-box-inner-wrapper">
                    %1$s
                    %2$s
                </div>
            </div>',
            static::_render_content($attrs, $content, $block, $elements),
            static::_render_content_hover($attrs, $content, $block, $elements),
            $hover_box_animation,
            $hover_box_align_front_class,
            $hover_box_align_back_class,
            $attr_use_force_square
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
				'classnamesFunction'  => [ HoverBox::class, 'module_classnames' ],
				'stylesComponent'     => [ HoverBox::class, 'module_styles' ],
				'scriptDataComponent' => [ HoverBox::class, 'module_script_data' ],
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
