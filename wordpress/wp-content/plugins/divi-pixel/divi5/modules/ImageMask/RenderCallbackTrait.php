<?php
/**
 * ImageMask::render_callback()
 *
 * @package DIPI\Modules\ImageMask
 * @since ??
 */

namespace DIPI\Modules\ImageMask;

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

require_once plugin_dir_path(__FILE__) . 'php_utils/Decorations.php';
require_once plugin_dir_path(__FILE__) . 'php_utils/Shapes.php';

use DIPI_SVG_Decorations_D5;
use DIPI_SVG_Shapes_D5;

trait RenderCallbackTrait {
	use BaseRenderTrait;

	private static $props = [];
    private static $layerID = 0;

	static function getLayerId($prefix = 'SVG_')
    {
        static::$layerID++;
        return $prefix . static::$layerID;
    }

	static function dipi_get_image_alt_by_url($image_url) {
		if (is_array($image_url)) {
			$image_url = $image_url['src'] ?? '';
		}
		if (!is_string($image_url) || $image_url === '') {
			return '';
		}

		$attachment_id = attachment_url_to_postid($image_url);
		if ($attachment_id) {
			$alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
			return $alt_text;
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

        $parent       = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
		$parent_attrs = $parent->attrs ?? [];

		$decorations = new DIPI_SVG_Decorations_D5();
        $shapes = new DIPI_SVG_Shapes_D5();

		$layerZeroGradient = '';
        $layerOneGradient = '';

        $gradId2 = static::getLayerId('GRAD_');

        $layer_1_enable = static::getPropValue($attrs, 'layer_1_enable');
        $layer_1_background_type = static::getPropValue($attrs, 'layer_1_background_type');
        $layer_1_gradient_color_start = static::getPropValue($attrs, 'layer_1_gradient_color_start');
        $layer_1_gradient_color_end = static::getPropValue($attrs, 'layer_1_gradient_color_end');

        if ($layer_1_enable === 'on' && $layer_1_background_type === 'Gradient') {
            $layerOneGradient = sprintf('
			<defs>
			<linearGradient id="%3$s" x1="0%%" y1="0%%" x2="100%%" y2="0%%">
				<stop offset="0%%" style="stop-color: %1$s;stop-opacity: 1" />
				<stop offset="100%%" style="stop-color: %2$s;stop-opacity: 1" />
			</linearGradient>
			</defs>',
                $layer_1_gradient_color_start,
                $layer_1_gradient_color_end,
                $gradId2
            );
        }

        $style = '';
        $deco_1 = '';
        $deco_2 = '';
        $bottom_layers = '';
        $top_layers = '';

        $layer_2_enable = static::getPropValue($attrs, 'layer_2_enable');
        $docration_element_1 = static::getPropValue($attrs, 'docration_element_1');
        $layer_2_horz = static::getPropValue($attrs, 'layer_2_horz');
        $layer_2_vert = static::getPropValue($attrs, 'layer_2_vert');
        $layer_2_scale = static::getPropValue($attrs, 'layer_2_scale');
        $layer_2_rotate = static::getPropValue($attrs, 'layer_2_rotate');
        if ($layer_2_enable === 'on') {
            $deco_1 = $decorations->decoration($docration_element_1, "s02", $layer_2_horz, $layer_2_vert, $layer_2_scale, $layer_2_rotate);
        }

        $layer_3_enable = static::getPropValue($attrs, 'layer_3_enable');
        $docration_element_2 = static::getPropValue($attrs, 'docration_element_2');
        $layer_3_horz = static::getPropValue($attrs, 'layer_3_horz');
        $layer_3_vert = static::getPropValue($attrs, 'layer_3_vert');
        $layer_3_scale = static::getPropValue($attrs, 'layer_3_scale');
        $layer_3_rotate = static::getPropValue($attrs, 'layer_3_rotate');
        if ($layer_3_enable === 'on') {
            $deco_2 = $decorations->decoration($docration_element_2, "s03", $layer_3_horz, $layer_3_vert, $layer_3_scale, $layer_3_rotate);
        }

        $layer_2_above_image = static::getPropValue($attrs, 'layer_2_above_image');
        if ($layer_2_above_image === 'on') {
            $top_layers .= $deco_1;
        } else {
            $bottom_layers .= $deco_1;
        }

        $layer_3_above_image = static::getPropValue($attrs, 'layer_3_above_image');
        if ($layer_3_above_image === 'on') {
            $top_layers .= $deco_2;
        } else {
            $bottom_layers .= $deco_2;
        }

        $shape = static::getPropValue($attrs, 'shape');
        $image = static::getPropValue($attrs, 'image');
        $image_src = is_array($image) ? ($image['src'] ?? '') : $image;
        $image_alt_from_attr = is_array($image) ? ($image['alt'] ?? '') : null;
        $image_width = static::getPropValue($attrs, 'image_width');
        $image_horz = static::getPropValue($attrs, 'image_horz');
        $image_vert = static::getPropValue($attrs, 'image_vert');
        $shape_rotate = static::getPropValue($attrs, 'shape_rotate');
        $shape_scale_x = static::getPropValue($attrs, 'shape_scale_x');
        $shape_scale_y = static::getPropValue($attrs, 'shape_scale_y');
        $shape_flip = static::getPropValue($attrs, 'shape_flip');
        $use_custom_mask = static::getPropValue($attrs, 'use_custom_mask');
        $custom_mask = static::getPropValue($attrs, 'custom_mask');
        $custom_mask_src = is_array($custom_mask) ? ($custom_mask['src'] ?? '') : $custom_mask;
        $main_layer = $shapes->shape(
            $shape,
            [
                'image' => $image_src,
                'image_width' => $image_width,
                'image_horz' => $image_horz,
                'image_vert' => $image_vert,
                'shape_rotate' => $shape_rotate,
                'shape_scale_x' => $shape_scale_x,
                'shape_scale_y' => $shape_scale_y,
                'shape_flip' => $shape_flip,
                'use_custom_mask' => $use_custom_mask,
                'custom_mask' => $custom_mask_src,
            ],
            $layer_1_enable,
            $gradId2
        );

        $title_id = 'alt-text-' . uniqid();
        $viewbox_width = static::getPropValue($attrs, 'viewbox_width') ?? 1000;
        $viewbox_height = static::getPropValue($attrs, 'viewbox_height') ?? 1000;
        $viewbox_x = static::getPropValue($attrs, 'viewbox_x') ?? 0;
        $viewbox_y = static::getPropValue($attrs, 'viewbox_y') ?? 0;
        $img_alt = static::getPropValue($attrs, 'img_alt');
        if (!$img_alt) {
            $img_alt = $image_alt_from_attr ?: static::dipi_get_image_alt_by_url($image_src);
        }
        $render_html = sprintf(
            '<div class="dipi-image-mask--mask">
                <svg width="100%%" height="100%%"  style="overflow:visible" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="%8$s %9$s %10$s %11$s" aria-labelledby="%7$s" role="img">
                    <title id="%7$s">%6$s</title>
                    %1$s
                    <style>%2$s</style>
                    %3$s
                    %4$s
                    %5$s
                </svg>
            </div>',
            $layerOneGradient, // #1
            $style,
            $bottom_layers,
            $main_layer,
            $top_layers, // #5
            esc_attr($img_alt),
            $title_id,
            $viewbox_x,
            $viewbox_y,
            $viewbox_width,
            $viewbox_height
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
				'classnamesFunction'  => [ ImageMask::class, 'module_classnames' ],
				'stylesComponent'     => [ ImageMask::class, 'module_styles' ],
				'scriptDataComponent' => [ ImageMask::class, 'module_script_data' ],
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
