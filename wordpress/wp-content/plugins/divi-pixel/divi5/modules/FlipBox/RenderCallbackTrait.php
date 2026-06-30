<?php
/**
 * FlipBox::render_callback()
 *
 * @package DIPI\Modules\FlipBox
 * @since ??
 */

namespace DIPI\Modules\FlipBox;

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
	private static function _render_front_side($attrs, $content, $block, $elements)
    {
        $front_icon_image = '';
		$attr_use_front_icon =  $attrs['use_front_icon']['innerContent']['desktop']['value'];
		$attr_front_icon = $attrs['front_icon']['innerContent']['desktop']['value'];
		$attr_front_circle_icon = $attrs['front_circle_icon']['innerContent']['desktop']['value'];
		$attr_front_circle_border = $attrs['front_circle_border']['innerContent']['desktop']['value'];
		$attr_front_image = $attrs['front_image']['innerContent']['desktop']['value']['src'] ?? '';
		$attr_front_image_alt = $attrs['front_image_alt']['innerContent']['desktop']['value'] ?? '';
		$attr_use_front_button = $attrs['use_front_button']['innerContent']['desktop']['value'] ?? '';
        if ('on' == $attr_use_front_icon) {
            $front_icon = Utils::process_font_icon($attr_front_icon);
            $front_circle_icon_class = 'on' === $attr_front_circle_icon ? 'dipi-front-icon-circle' : '';
            $front_border_icon_class = 'on' === $attr_front_circle_border ? 'dipi-front-icon-border' : '';
            $front_icon_image = sprintf(
                '<div class="dipi-front-image-icon-wrap dipi-icon-wrap">
                    <span class="et-pb-icon et-pb-font-icon dipi-flip-box-front-icon %2$s %3$s">
                        %1$s
                    </span>
                </div>',
                esc_attr($front_icon),
                $front_circle_icon_class,
                $front_border_icon_class
            );

            //$this->dipi_generate_font_icon_styles($render_slug, 'front_icon', '%%order_class%% .dipi-front-image-icon-wrap .dipi-flip-box-front-icon');

        } else if (isset($attr_front_image) && '' !== $attr_front_image) {

            $front_image_alt = $attr_front_image_alt ;

            $front_icon_image = sprintf(
                '<div class="dipi-front-image-icon-wrap dipi-image-wrap">
                  <img src="%1$s" alt="%2$s">
                </div>',
                esc_attr($attr_front_image),
                $front_image_alt
            );
        }

        // Title.
		$front_title = $elements->render(
			[
				'attrName' => 'front_title',
			]
		);

		// Content.
		$front_content = $elements->render(
			[
				'attrName' => 'front_content',
			]
		);

        
        $front_button = '';
        if ('on' === $attr_use_front_button) {

            $front_button = $elements->render(
				[
					'attrName' => 'front_button',
				]
				);
				 ;
        }

        $front_parallax_bg = '';


        $front_content_render = '';
        if ('' !== $front_title || '' !== $front_content || '' !== $front_button) {
            $front_content_render = sprintf(
                '<div class="dipi-text">
                    %1$s
                    %2$s
                    %3$s
                </div>',
                $front_title,
                $front_content,
                $front_button
            );
        }

        return sprintf(
            '<div class="dipi-flip-box-front-side">
                <div class="dipi-flip-box-front-side-wrapper">
                    %1$s
                    <div class="dipi-flip-box-front-side-innner">
                        %2$s
                        %3$s
                    </div>
                </div>
            </div>
            ',
            $front_parallax_bg,
            $front_icon_image,
            $front_content_render
        );
    }
	private static function _render_back_side($attrs, $content, $block, $elements)
    {
        $back_icon_image = '';
		$attr_use_back_icon =  $attrs['use_back_icon']['innerContent']['desktop']['value'];
		$attr_back_icon = $attrs['back_icon']['innerContent']['desktop']['value'];
		$attr_back_circle_icon = $attrs['back_circle_icon']['innerContent']['desktop']['value'];
		$attr_back_circle_border = $attrs['back_circle_border']['innerContent']['desktop']['value'];
		$attr_back_image = $attrs['back_image']['innerContent']['desktop']['value']['src'] ?? '';
		$attr_back_image_alt = $attrs['back_image_alt']['innerContent']['desktop']['value'] ?? '';
		$attr_use_back_button = $attrs['use_back_button']['innerContent']['desktop']['value'] ?? '';
        if ('on' == $attr_use_back_icon) {
            $back_icon = Utils::process_font_icon($attr_back_icon);
            $back_circle_icon_class = 'on' === $attr_back_circle_icon ? 'dipi-back-icon-circle' : '';
            $back_border_icon_class = 'on' === $attr_back_circle_border ? 'dipi-back-icon-border' : '';
            $back_icon_image = sprintf(
                '<div class="dipi-back-image-icon-wrap dipi-icon-wrap">
                    <span class="et-pb-icon et-pb-font-icon dipi-flip-box-back-icon %2$s %3$s">
                        %1$s
                    </span>
                </div>',
                esc_attr($back_icon),
                $back_circle_icon_class,
                $back_border_icon_class
            );

            //$this->dipi_generate_font_icon_styles($render_slug, 'back_icon', '%%order_class%% .dipi-back-image-icon-wrap .dipi-flip-box-back-icon');

        } else if (isset($attr_back_image) && '' !== $attr_back_image) {

            $back_image_alt = $attr_back_image_alt ;

            $back_icon_image = sprintf(
                '<div class="dipi-back-image-icon-wrap dipi-image-wrap">
                  <img src="%1$s" alt="%2$s">
                </div>',
                esc_attr($attr_back_image),
                $back_image_alt
            );
        }

        // Title.
		$back_title = $elements->render(
			[
				'attrName' => 'back_title',
			]
		);

		// Content.
		$back_content = $elements->render(
			[
				'attrName' => 'back_content',
			]
		);

        
        $back_button = '';
        if ('on' === $attr_use_back_button) {

            $back_button = $elements->render(
				[
					'attrName' => 'back_button',
				]
				);
				 ;
        }

        $back_parallax_bg = '';


        $back_content_render = '';
        if ('' !== $back_title || '' !== $back_content || '' !== $back_button) {
            $back_content_render = sprintf(
                '<div class="dipi-text">
                    %1$s
                    %2$s
                    %3$s
                </div>',
                $back_title,
                $back_content,
                $back_button
            );
        }

        return sprintf(
            '<div class="dipi-flip-box-back-side">
                <div class="dipi-flip-box-back-side-wrapper">
                    %1$s
                    <div class="dipi-flip-box-back-side-innner">
                        %2$s
                        %3$s
                    </div>
                </div>
            </div>
            ',
            $back_parallax_bg,
            $back_icon_image,
            $back_content_render
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
		$flip_box_animation = '';
		$attr_flip_box_animation =  $attrs['flip_box_animation']['innerContent']['desktop']['value'];
		$attr_use_3d_flip_box = $attrs['use_3d_flip_box']['innerContent']['desktop']['value'];
		$attr_use_3d_effect = $attrs['use_3d_effect']['innerContent']['desktop']['value'];
		$attr_use_dynamic_height = $attrs['use_dynamic_height']['innerContent']['desktop']['value'];
		$attr_use_force_square = $attrs['use_force_square']['innerContent']['desktop']['value'];
        if ('flip_horizontally_ltr' == $attr_flip_box_animation && 'off' == $attr_use_3d_flip_box) {
            $flip_box_animation = 'dipi-flip-left-right';
        } elseif ('flip_horizontally_rtl' == $attr_flip_box_animation && 'off' == $attr_use_3d_flip_box) {
            $flip_box_animation = 'dipi-flip-right-left';
        } elseif ('flip_vertically_ttb' == $attr_flip_box_animation && 'off' == $attr_use_3d_flip_box) {
            $flip_box_animation = 'dipi-flip-top-bottom';
        } elseif ('flip_vertically_btt' == $attr_flip_box_animation && 'off' == $attr_use_3d_flip_box) {
            $flip_box_animation = 'dipi-flip-bottom-top';
        }

        if ('on' == $attr_use_3d_flip_box) {
            if ('flip_horizontally_ltr' == $attr_flip_box_animation) {
                $flip_box_animation = 'dipi-flip-box-3d-cube dipi-flip-ltr';
            } elseif ('flip_horizontally_rtl' == $attr_flip_box_animation) {
                $flip_box_animation = 'dipi-flip-box-3d-cube dipi-flip-rtl';
            } elseif ('flip_vertically_ttb' == $attr_flip_box_animation) {
                $flip_box_animation = 'dipi-flip-box-3d-cube dipi-flip-ttb';
            } elseif ('flip_vertically_btt' == $attr_flip_box_animation) {
                $flip_box_animation = 'dipi-flip-box-3d-cube dipi-flip-btt';
            }
        }

        $dipi_3d_flank = ('on' == $attr_use_3d_flip_box) ? '<div class="dipi-flip-box-3d-flank"></div>' : '';
        $use_3d_effect = ('on' == $attr_use_3d_effect) ? 'dipi-3d-flip-box' : '';

        $render_html = sprintf(
            '<div class="dipi-flip-box-container" data-dynamic_height="%6$s" data-force_square="%7$s">
                <div class="dipi-flip-box-inner %3$s %4$s">
                    <div class="dipi-flip-box-inner-wrapper">
                        %1$s
                        %2$s
                        %5$s
                    </div>
                </div>
           </div>',
           static::_render_front_side($attrs, $content, $block, $elements),
            static::_render_back_side($attrs, $content, $block, $elements),
            $flip_box_animation,
            $use_3d_effect,
            $dipi_3d_flank,
            $attr_use_dynamic_height,
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
				'classnamesFunction'  => [ FlipBox::class, 'module_classnames' ],
				'stylesComponent'     => [ FlipBox::class, 'module_styles' ],
				'scriptDataComponent' => [ FlipBox::class, 'module_script_data' ],
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
