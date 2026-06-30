<?php
/**
 * ImageHotspotChild::render_callback()
 *
 * @package DIPI\Modules\ImageHotspotChild
 * @since ??
 */

namespace DIPI\Modules\ImageHotspotChild;

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

	private static function hex2RGB($color, $opacity = false)
    {

        if ($color[0] == '#') {
            $color = substr($color, 1);
        }

        if (strlen($color) == 6) {
            $hex = [$color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]];
        } elseif (strlen($color) == 3) {
            $hex = [$color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]];
        }

        $rgb = array_map('hexdec', $hex);

        $output = 'rgba( ' . implode(",", $rgb) . ',' . $opacity . ' )';

        return $output;
    }

    private static function rgb_split($color, $alpha = true)
    {

        $pattern = '~^rgba?\((25[0-5]|2[0-4]\d|1\d{2}|\d\d?)\s*,\s*(25[0-5]|2[0-4]\d|1\d{2}|\d\d?)\s*,\s*(25[0-5]|2[0-4]\d|1\d{2}|\d\d?)\s*(?:,\s*([01]\.?\d*?))?\)$~';

        if (!preg_match($pattern, $color, $matches)) {
            return [];
        }

        return array_slice($matches, 1, $alpha ? 4 : 3);
    }

	private static function sonar_animation($attrs)
    {
		$hotspot_ripple_effect_style = static::getPropValue($attrs, 'hotspot_ripple_effect_style') ?? 'style-1';
		$hotspot_ripple_effect_color = static::getPropValue($attrs, 'hotspot_ripple_effect_color') ?? '';
		$hotspot_ripple_effect_size = static::getPropValue($attrs, 'hotspot_ripple_effect_size') ?? '100px';

        if ($hotspot_ripple_effect_style !== 'style-2') {
            return '';
        }
        return sprintf(
            '<div class="dipi-svg-sonar-container" style="width:%2$s;height:%2$s;">
                <div class="dipi-sonar-circle"></div>
                <div class="dipi-sonar-circle"></div>
                <div class="dipi-sonar-circle"></div>
                <div class="dipi-sonar-circle"></div>
            </div>',
            $hotspot_ripple_effect_color,
            $hotspot_ripple_effect_size
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
		$order_number = $block->parsed_block['orderIndex'];
        $parent       = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
		$parent_attrs = $parent->attrs ?? [];

		$tooltip_animation = static::getPropValue($parent_attrs, 'tooltip_animation') ?? 'fadeIn';
		$hide_tooltip = static::getPropValue($parent_attrs, 'hide_tooltip') ?? 'off';

		/**
         * Hotspot element
         */
        $hotspot_image = static::getPropValue($attrs, 'hotspot_img')['src'] ?? "";
        $use_hotspot_icon = static::getPropValue($attrs, 'use_hotspot_icon') ?? 'off';
        $hotspot_icon = static::getPropValue($attrs, 'hotspot_icon') ?? "";

		$hotspot_ripple_effect = static::getPropValue($attrs, 'hotspot_ripple_effect') ?? 'off';
		$hotspot_ripple_effect_style = static::getPropValue($attrs, 'hotspot_ripple_effect_style') ?? 'style-1';
		$hotspot_ripple_effect_color = static::getPropValue($attrs, 'hotspot_ripple_effect_color');

		$hotspot_link = static::getPropValue($attrs, 'hotspot_link');
		$hotspot_link_target = static::getPropValue($attrs, 'hotspot_link_target') === 'new_window' ? '_blank' : '_self';

		$start_link_wrap = !empty($hotspot_link) ? sprintf(
            '<a href="%1$s" target="%2$s">',
            $hotspot_link,
            $hotspot_link_target
        ) : '';

		$end_link_wrap = !empty($hotspot_link) ? '</a>' : '';

		$key_uuid = 'key' . rand();

		$color1 = "rgba(0,0,0, .3)";
        $color2 = "rgba(0,0,0, .3)";
        $color3 = "rgba(0,0,0, 0)";
        $color4 = "rgba(0,0,0, .5)";
        $color5 = "rgba(0,0,0, 0)";
        $color6 = "rgba(0,0,0, 0)";
        $color7 = "rgba(0,0,0, 0)";
        $color8 = "rgba(0,0,0, 0)";

        if ($hotspot_ripple_effect_color !== 'undefined') {

            if (strpos($hotspot_ripple_effect_color, "#") === 0) {

                $color1 = static::hex2RGB($hotspot_ripple_effect_color, 0.3);
                $color2 = static::hex2RGB($hotspot_ripple_effect_color, 0.3);
                $color3 = static::hex2RGB($hotspot_ripple_effect_color, 0.0);
                $color4 = static::hex2RGB($hotspot_ripple_effect_color, 0.5);
                $color5 = static::hex2RGB($hotspot_ripple_effect_color, 0.0);
                $color6 = static::hex2RGB($hotspot_ripple_effect_color, 0.0);
                $color7 = static::hex2RGB($hotspot_ripple_effect_color, 0.0);
                $color8 = static::hex2RGB($hotspot_ripple_effect_color, 0.0);
            } else {

                $rgbaColor = $hotspot_ripple_effect_color;
                $rgba_arr = static::rgb_split($rgbaColor);

                $red = isset($rgba_arr[0]) ? $rgba_arr[0] : '';
                $green = isset($rgba_arr[1]) ? $rgba_arr[1] : '';
                $blue = isset($rgba_arr[2]) ? $rgba_arr[2] : '';

                $color1 = "rgba($red, $green, $blue, .3)";
                $color2 = "rgba($red, $green, $blue, .3)";
                $color3 = "rgba($red, $green, $blue, 0)";
                $color4 = "rgba($red, $green, $blue, .5)";
                $color5 = "rgba($red, $green, $blue, 0)";
                $color6 = "rgba($red, $green, $blue, 0)";
                $color7 = "rgba($red, $green, $blue, 0)";
                $color8 = "rgba($red, $green, $blue, 0)";
            }
        }

        // Keyframes
        $keyframes = ('on' === $hotspot_ripple_effect && $hotspot_ripple_effect_style === 'style-1') ? "<style>@keyframes pulse-$key_uuid {
            0% {box-shadow: 0 0 0 0 $color1, 0 0 0 0 $color2;}
            33% {box-shadow: 0 0 0 15px $color3, 0 0 0 0 $color4;}
            66% {box-shadow: 0 0 0 10px $color5, 0 0 0 10px $color6;}
            100% {box-shadow: 0 0 0 0 $color7, 0 0 0 15px $color8;}
            }</style>" : "";

        // Pulse style
        $pulse_style = ('on' === $hotspot_ripple_effect && $hotspot_ripple_effect_style === 'style-1') ? 'style="animation: pulse-' . $key_uuid . ' 3s linear infinite;"' : '';
        if(!empty($hotspot_icon)) {
            // Hotspot icon
            $hotspot_icon = sprintf(
                '
                <span %2$s class="et-pb-icon et-pb-font-icon dipi-hotspot-icon">
                    %1$s
                </span>',
                esc_attr(Utils::process_font_icon($hotspot_icon)),
                $pulse_style
            );
        }
        
        $img_alt = static::getPropValue($attrs, 'img_alt');

        if(!empty($hotspot_image)) {
            // Hotspot image
            $hotspot_image = sprintf(
                '<img style="animation: pulse-%2$s 3s linear infinite;" src="%1$s" class="dipi-hotspot-image" alt="%3$s">',
                $hotspot_image,
                $key_uuid,
                    esc_attr($img_alt)
            );
        }

        $ripple_element = static::sonar_animation($attrs);

        $hotspot_img_icon = $use_hotspot_icon === 'on' ? $hotspot_icon : $hotspot_image;

        // Hotspot output
        $hotspot = sprintf(
            '
            <div class="dipi-hotspot">
                %3$s
                    %1$s
                    %2$s
                    %5$s
                %4$s
            </div>',
            $hotspot_img_icon,
            $keyframes,
            $start_link_wrap,
            $end_link_wrap,
            $ripple_element
        );

		$tooltip_icon = static::getPropValue($attrs, 'tooltip_icon') ?? '';

        if(!empty($tooltip_icon)) {
            // Tooltip icon
            $tooltip_icon = sprintf(
                '
                <div class="dipi-tooltip-image-icon">
                    <span class="et-pb-icon et-pb-font-icon dipi-tooltip-icon">
                        %1$s
                    </span>
                </div>',
                esc_attr(Utils::process_font_icon($tooltip_icon))
            );
        }

		$tooltip_img_src = static::getPropValue($attrs, 'tooltip_img_src')['src'] ?? '';
        $tooltip_img_alt = static::getPropValue($attrs, 'tooltip_img_alt') ?? '';
        // Tooltip Image
        $tooltip_image = '';
        if (!empty($tooltip_img_src)) {
            $tooltip_image = sprintf(
                '
                <div class="dipi-tooltip-image-icon">
                    <img src="%1$s" class="dipi-tooltip-image" alt="%2$s">
                </div>',
                $tooltip_img_src,
                esc_attr($tooltip_img_alt)
            );
        }

        $tooltip_title_level = $attrs['tooltip_title']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? 'h2';
        $tooltip_title = static::getPropValue($attrs, 'tooltip_title') ?? '';
        $tooltip_title = $tooltip_title !== '' ? sprintf(
            '<%2$s class="dipi-tooltip-title">
                %1$s
            </%2$s>',
            $tooltip_title,
            esc_attr($tooltip_title_level)
        ) : '';

		$tooltip_desc = static::getPropValue($attrs, 'tooltip_desc') ?? '';
        // Tooltip Description
        $tooltip_desc = $tooltip_desc !== '' ? sprintf(
            '
            <div class="dipi-tooltip-desc">
                %1$s
            </div>',
            $tooltip_desc
        ) : '';

		$show_tooltip_button = static::getPropValue($attrs, 'show_tooltip_button') ?? 'off';

        $tooltip_button = $elements->render([
			'attrName' => 'tooltip_button',
		]);

		$use_tooltip_icon = static::getPropValue($attrs, 'use_tooltip_icon') ?? 'off';
        // Tooltip Icon/Image
        $tooltip_img_icon = 'on' === $use_tooltip_icon ? $tooltip_icon : $tooltip_image;

        // Tooltip button
        $tooltip_button = 'on' === $show_tooltip_button ? sprintf('<div class="dipi-tooltip-button-wrap">%1$s</div>', $tooltip_button) : '';

		

		$use_tooltip_arrow = static::getPropValue($attrs, 'use_tooltip_arrow') ?? 'off';
		$tooltip_position = static::getPropValue($attrs, 'tooltip_position') ?? 'left';
        // Tooltip Arrow
        $tooltip_arrow = 'on' === $use_tooltip_arrow ? 'dipi-tooltip-arrow dipi-tooltip-arrow-' . $tooltip_position : '';

        // Tooltip Position
        $tooltip_position_class = "dipi-tooltip-position-{$tooltip_position}";
        
        // Child Order Class
        $order_class = "dipi_image_hotspot_child_$order_number";

		$content_type = static::getPropValue($attrs, 'content_type') ?? 'default';
        // Tooltip element
        $tooltip = '';
        if ($hide_tooltip === 'off') {
            if ($content_type === 'library') {
        		// Tooltip Divi Libary Shortcode
				$library_id = static::getPropValue($attrs, 'library_id') ?? '';
				$tooltip_shortcode = do_shortcode('[et_pb_section global_module="' . $library_id . '"][/et_pb_section]');
                $tooltip = sprintf(
                    '
                    <div
                        class="dipi-tooltip-wrap %2$s %3$s animated %4$s"
                        data-order-number="%5$s"
                    >
                        %1$s
                    </div>',
                    $tooltip_shortcode,
                    $tooltip_position_class,
                    $tooltip_arrow,
                    $tooltip_animation,
                    $order_number #5

                );
            } else {

                $tooltip = sprintf(
                    '
                    <div
                        class="dipi-tooltip-wrap %5$s %6$s animated %7$s"
                        data-order-number="%8$s"
                    >
                        %1$s
                        %2$s
                        %3$s
                        %4$s
                    </div>',
                    $tooltip_img_icon,
                    $tooltip_title,
                    $tooltip_desc,
                    $tooltip_button,
                    $tooltip_position_class, #5
                    $tooltip_arrow,
                    $tooltip_animation,
                    $order_number
                );
            }
        }

        /**
         * Tooltip Output
         */
        $render_html = sprintf(
            '
            <div class="dipi-image-hotspot-child">
                %1$s
                %2$s
            </div>',
            $tooltip,
            $hotspot
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
				'classnamesFunction'  => [ ImageHotspotChild::class, 'module_classnames' ],
				'stylesComponent'     => [ ImageHotspotChild::class, 'module_styles' ],
				'scriptDataComponent' => [ ImageHotspotChild::class, 'module_script_data' ],
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
