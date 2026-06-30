<?php
/**
 * ImageShowcaseChild::render_callback()
 *
 * @package DIPI\Modules\ImageShowcaseChild
 * @since ??
 */

namespace DIPI\Modules\ImageShowcaseChild;

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
    
    private static function extract_image_src( $value ) {
        if ( empty( $value ) ) {
            return '';
        }

        if ( is_string( $value ) ) {
            if ( false !== strpos( $value, '$variable(' ) ) {
                return '';
            }

            return $value;
        }

        if ( is_array( $value ) ) {
            if ( isset( $value['src'] ) && is_string( $value['src'] ) ) {
                if ( false !== strpos( $value['src'], '$variable(' ) ) {
                    return '';
                }

                return $value['src'];
            }

            if ( isset( $value['url'] ) && is_string( $value['url'] ) ) {
                if ( false !== strpos( $value['url'], '$variable(' ) ) {
                    return '';
                }

                return $value['url'];
            }

            if ( isset( $value['sizes'] ) && is_array( $value['sizes'] ) ) {
                $selected_size = '';

                if ( isset( $value['size'] ) && is_string( $value['size'] ) ) {
                    $selected_size = $value['size'];
                } elseif ( isset( $value['selectedSize'] ) && is_string( $value['selectedSize'] ) ) {
                    $selected_size = $value['selectedSize'];
                } elseif ( isset( $value['sizeSlug'] ) && is_string( $value['sizeSlug'] ) ) {
                    $selected_size = $value['sizeSlug'];
                }

                if ( $selected_size && isset( $value['sizes'][ $selected_size ]['url'] ) ) {
                    $selected_url = $value['sizes'][ $selected_size ]['url'];

                    if ( is_string( $selected_url ) && false === strpos( $selected_url, '$variable(' ) ) {
                        return $selected_url;
                    }
                }

                if ( isset( $value['sizes']['full']['url'] ) && is_string( $value['sizes']['full']['url'] ) ) {
                    $full_url = $value['sizes']['full']['url'];

                    if ( false === strpos( $full_url, '$variable(' ) ) {
                        return $full_url;
                    }
                }
            }

            if ( isset( $value['value'] ) ) {
                return static::extract_image_src( $value['value'] );
            }
        }

        return '';
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

        $img_alt = static::getPropValue($attrs, 'img_alt');
        $img_alt_is_dynamic = is_string($img_alt) && false !== strpos($img_alt, '$variable(');

        $bg_img_html = $elements->render([
            'attrName' => 'bg_img',
        ]);

        if ($bg_img_html && $img_alt && ! $img_alt_is_dynamic) {
            $escaped_alt = esc_attr($img_alt);

            if (false !== stripos($bg_img_html, ' alt=')) {
                $replaced = preg_replace(
                    '/\balt=(["\']).*?\1/i',
                    sprintf('alt="%s"', $escaped_alt),
                    $bg_img_html,
                    1
                );

                if (null !== $replaced) {
                    $bg_img_html = $replaced;
                }
            } else {
                $replaced = preg_replace(
                    '/<img\b(?![^>]*\balt=)([^>]*?)(\/?)>/i',
                    sprintf('<img$1 alt="%s"$2>', $escaped_alt),
                    $bg_img_html,
                    1
                );

                if (null !== $replaced) {
                    $bg_img_html = $replaced;
                }
            }
        }

        if (! $bg_img_html) {
            $bg_img_value = static::getPropValue($attrs, 'bg_img') ?? '';

            if (is_array($bg_img_value)) {
                $bg_img_value = static::extract_image_src($bg_img_value);
            }

            if (is_string($bg_img_value) && $bg_img_value !== '') {
                if (false === strpos($bg_img_value, '$variable(')) {
                    $bg_img_html = sprintf(
                        '<img src="%1$s" alt="%2$s" />',
                        esc_url($bg_img_value),
                        esc_attr($img_alt ?? '')
                    );
                } else {
                    $bg_img_html = '';
                }
            } else {
                $bg_img_html = '';
            }
        }

		$render_html = sprintf(
			'<div class="et_pb_module_inner">%1$s</div>',
            $bg_img_html
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
				'classnamesFunction'  => [ ImageShowcaseChild::class, 'module_classnames' ],
				'stylesComponent'     => [ ImageShowcaseChild::class, 'module_styles' ],
				'scriptDataComponent' => [ ImageShowcaseChild::class, 'module_script_data' ],
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
