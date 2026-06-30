<?php
/**
 * ImageMagnifier::render_callback()
 *
 * @package DIPI\Modules\ImageMagnifier
 * @since ??
 */

namespace DIPI\Modules\ImageMagnifier;

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
		$main_image = static::normalize_media_value( static::getPropValue( $attrs, 'main_image' ) );
		if ( '' === $main_image ) {
            return '';
        };

		$img_alt = static::normalize_dynamic_value( static::getPropValue( $attrs, 'img_alt' ) ) ?? "";
		$speed = static::getPropValue($attrs, 'speed') ?? "300";
		$touch_bottom_offset = static::getPropValue($attrs, 'touch_bottom_offset') ?? "0";

        $options = [
			"data-speed" => $speed,
			"data-touchbottomoffset" => esc_attr($touch_bottom_offset)
        ];

		$options = implode(
            " ", 
            array_map(
                function($k, $v){
                    return "{$k}='{$v}'";
                }, 
                array_keys($options),
                $options
            )
        );

		$attachment_id = attachment_url_to_postid( $main_image );
		$image_title    = $attachment_id ? get_the_title( $attachment_id ) : '';
		$srcset         = $attachment_id ? wp_get_attachment_image_srcset( $attachment_id, 'full' ) : '';
		$render_html = sprintf(
			'<div class="dipi-image-magnifier" %5$s>
				<img data-magnify-src="%1$s" src="%1$s" srcset="%2$s" alt="%3$s" title="%4$s"/>
			</div>',
			esc_attr($main_image),
			esc_attr($srcset),
			esc_attr($img_alt),
			$image_title,
			$options
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
				'classnamesFunction'  => [ ImageMagnifier::class, 'module_classnames' ],
				'stylesComponent'     => [ ImageMagnifier::class, 'module_styles' ],
				'scriptDataComponent' => [ ImageMagnifier::class, 'module_script_data' ],
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

	private static function normalize_dynamic_value( $value ) {
		if ( is_null( $value ) ) {
			return '';
		}

		if ( is_string( $value ) ) {
			if ( false !== strpos( $value, '<' ) ) {
				if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $value, $matches ) && ! empty( $matches[1] ) ) {
					return $matches[1];
				}

				$stripped = trim( wp_strip_all_tags( $value ) );
				if ( '' !== $stripped ) {
					return $stripped;
				}
			}
			return $value;
		}

		if ( is_scalar( $value ) ) {
			return strval( $value );
		}

		if ( is_array( $value ) ) {
			$preferred_keys = [ 'src', 'url', 'value', 'rendered', 'content', 'preview', 'html' ];

			foreach ( $preferred_keys as $key ) {
				if ( isset( $value[ $key ] ) && '' !== $value[ $key ] ) {
					$normalized = self::normalize_dynamic_value( $value[ $key ] );
					if ( '' !== $normalized ) {
						return $normalized;
					}
				}
			}

			foreach ( $value as $nested_value ) {
				$normalized = self::normalize_dynamic_value( $nested_value );
				if ( '' !== $normalized ) {
					return $normalized;
				}
			}
		}

		return '';
	}

	private static function normalize_media_value( $value ) {
		$normalized = self::normalize_dynamic_value( $value );

		return is_string( $normalized ) ? $normalized : '';
	}
}
