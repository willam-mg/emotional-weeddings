<?php
namespace DIPI\Modules\ImageHotspotChild;

if (!defined("ABSPATH")) {
    die("Direct access forbidden.");
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Border\BorderStyle;
use ET\Builder\Packages\Module\Options\Spacing\SpacingStyle;
use DIPI\Traits\BaseRenderTrait;

trait ModuleStylesTrait
{
    use CustomCssTrait;
    use StyleDeclarationTrait;
	use BaseRenderTrait;

    private static $props = [];

    public static function getAttr(
        $attrs,
        $attr,
        $default = null,
        $zoom = "",
        $unit = "",
        $wrap_func = ""
    ) {
        $AttrValue = (($attrs ?? [])[$attr] ?? [])["innerContent"] ?? [
            "desktop" => ["value" => $default ?? ""],
        ];
        return $AttrValue;
    }

    public static function module_styles($args)
    {
        $attrs    = $args['attrs'] ?? [];
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class  = $args['orderClass'] ?? '';
        $order_number = preg_replace('/[^0-9]/', '', $order_class);

        $hotspot_circle_icon = static::getPropValue($attrs, 'hotspot_circle_icon') ?? "off";
        $hotspot_circle_border = static::getPropValue($attrs, 'hotspot_circle_border') ?? "off";
        $use_hotspot_icon_font_size = static::getPropValue($attrs, 'use_hotspot_icon_font_size') ?? "off";
        $use_tooltip_icon_circle = static::getPropValue($attrs, 'use_tooltip_icon_circle') ?? "off";
        $tooltip_icon_circle_border = static::getPropValue($attrs, 'tooltip_icon_circle_border') ?? "off";
        $use_tooltip_icon_font_size = static::getPropValue($attrs, 'use_tooltip_icon_font_size') ?? "off";
        $hotspot_ripple_effect = static::getPropValue($attrs, 'hotspot_ripple_effect') ?? "off";
        $hotspot_ripple_effect_style = static::getPropValue($attrs, 'hotspot_ripple_effect_style') ?? 'style-1';
        $hotspot_ripple_effect_size = static::getPropValue($attrs, 'hotspot_ripple_effect_size') ?? '100px';

        Style::add([
            'id'            => $args['id'],
            'name'          => $args['name'],
            'orderIndex'    => $args['orderIndex'],
            'storeInstance' => $args['storeInstance'],
            'styles'        => [
                // Module.
                $elements->style([
                    'attrName'   => 'module',
                    'styleProps' => [
                        'disabledOn' => [
                            'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                        ],
                    ],
                ]),
                CssStyle::style([
                    'selector'  => $args['orderClass'],
                    'attr'      => $attrs['css'] ?? [],
                    'cssFields' => static::custom_css(),
                ]),
                $elements->style([
                    'attrName'   => 'hotspot_img',
                ]),
                $elements->style([
                    'attrName'   => 'tooltip_icon',
                ]),
                $elements->style([
                    'attrName'   => 'tooltip_title',
                ]),
                $elements->style([
                    'attrName'   => 'tooltip_desc',
                ]),
                $elements->style([
                    'attrName'   => 'tooltip_button',
                ]),
                $elements->style([
                    'attrName'   => 'tooltip_img',
                ]),
                $elements->style([
                    'attrName'   => 'tooltip_box',
                ]),
                SpacingStyle::style([
                    "selector" => "$order_class .dipi-hotspot-icon",
                    "attr" => static::getAttr($attrs, "hotspot_icon_padding"),
                    "important" => true,
                ]),
                SpacingStyle::style([
                    "selector" => "$order_class .dipi-tooltip-icon",
                    "attr" => static::getAttr($attrs, "tooltip_icon_padding"),
                    "important" => true,
                ]),
                CommonStyle::style([
                    'selector' => "$order_class .dipi-hotspot .dipi-hotspot-image",
                    'attr'     => static::getAttr($attrs, 'hotspot_image_width', '100px'),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];
                        return "width: $attrValue!important;";
                    },
                ]),
                CommonStyle::style([
                    'selector' => "$order_class .dipi-tooltip-image-icon img",
                    'attr'     => static::getAttr($attrs, 'tooltip_image_width', '100px'),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];
                        return "width: $attrValue!important;";
                    },
                ]),
                CommonStyle::style([
                    'selector' => "$order_class .dipi-tooltip-wrap",
                    'attr'     => static::getAttr($attrs, 'tooltip_width', '300px'),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];
                        return "width: $attrValue!important;";
                    },
                ]),
                CommonStyle::style([
                    'selector' => "$order_class .dipi-tooltip-wrap, $order_class .dipi-tooltip-button-wrap",
                    'attr'     => static::getAttr($attrs, 'tooltip_content_align', 'left'),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];
                        if ($attrValue === "center") {
                            return "text-align: center!important;";
                        } else if ($attrValue === "right") {
                            return "text-align: right!important;";
                        }
                        return "text-align: left!important;";
                    },
                ]),
                CommonStyle::style([
                    'selector' => "$order_class .dipi-tooltip-image-icon",
                    'attr'     => static::getAttr($attrs, 'tooltip_content_align', 'left'),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];
                        if ($attrValue === "center") {
                            return "margin-left: auto!important; margin-right: auto!important;";
                        } else if ($attrValue === "right") {
                            return "margin-right: 0!important; margin-left: auto!important;";
                        }
                        return "margin-left: 0!important; margin-right: auto!important;";
                    },
                ]),
                CommonStyle::style([
                    'selector' => "$order_class .dipi-hotspot-icon",
                    'attr'     => static::getAttr($attrs, 'hotspot_icon_color', '#7EBEC5'),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];
                        return "color: $attrValue!important;";
                    },
                ]),
                $hotspot_circle_icon === "on" ? CommonStyle::style([
                    'selector' => "$order_class .dipi-hotspot-icon",
                    'attr'     => static::getAttr($attrs, 'hotspot_circle_color', ''),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];
                        return "padding: 15px; border-radius: 100%; background-color: $attrValue;";
                    },
                ]) : null,
                $hotspot_circle_border === "on" ? CommonStyle::style([
                    'selector' => "$order_class .dipi-hotspot-icon",
                    'attr'     => static::getAttr($attrs, 'hotspot_circle_border_color', ''),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];
                        return "border: 3px solid $attrValue;";
                    },
                ]) : null,
                $use_hotspot_icon_font_size === "on" ? CommonStyle::style([
                    'selector' => "$order_class .dipi-hotspot-icon",
                    'attr'     => static::getAttr($attrs, 'hotspot_icon_size', ''),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];
                        return "font-size: $attrValue!important;";
                    },
                ]) : null,
                CommonStyle::style([
                    'selector' => "$order_class .dipi-tooltip-icon",
                    'attr'     => static::getAttr($attrs, 'tooltip_icon_color', ''),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];
                        return "color: $attrValue!important;";
                    },
                ]),
                $use_tooltip_icon_circle === "on" ? CommonStyle::style([
                    'selector' => "$order_class .dipi-tooltip-icon",
                    'attr'     => static::getAttr($attrs, 'tooltip_icon_circle_color', ''),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];
                        return "padding: 25px; border-radius: 100%; background-color: $attrValue;";
                    },
                ]) : null,
                $tooltip_icon_circle_border === "on" ? CommonStyle::style([
                    'selector' => "$order_class .dipi-tooltip-icon",
                    'attr'     => static::getAttr($attrs, 'tooltip_icon_circle_border_color', ''),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];
                        return "border: 3px solid $attrValue;";
                    },
                ]) : null,
                $use_tooltip_icon_font_size === "on" ? CommonStyle::style([
                    'selector' => "$order_class .dipi-tooltip-icon",
                    'attr'     => static::getAttr($attrs, 'tooltip_icon_font_size', '40px'),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];
                        return "font-size: $attrValue!important;";
                    },
                ]) : null,
                CommonStyle::style([
                    'selector' => "$order_class .dipi-tooltip-arrow-left::before",
                    'attr'     => static::getAttr($attrs, 'arrow_color', '#000'),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];
                        return "border-left-color: $attrValue!important;";
                    },
                ]),
                CommonStyle::style([
                    'selector' => "$order_class .dipi-tooltip-arrow-right::before",
                    'attr'     => static::getAttr($attrs, 'arrow_color', '#000'),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];
                        return "border-right-color: $attrValue!important;";
                    },
                ]),
                CommonStyle::style([
                    'selector' => "$order_class .dipi-tooltip-arrow-bottom::before",
                    'attr'     => static::getAttr($attrs, 'arrow_color', '#000'),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];
                        return "border-bottom-color: $attrValue!important;";
                    },
                ]),
                CommonStyle::style([
                    'selector' => "$order_class .dipi-tooltip-arrow-top::before",
                    'attr'     => static::getAttr($attrs, 'arrow_color', '#000'),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];
                        return "border-top-color: $attrValue!important;";
                    },
                ]),
                CommonStyle::style([
                    'selector' => "$order_class .dipi-sonar-circle",
                    'attr'     => static::getAttr($attrs, 'hotspot_ripple_effect_speed', '2.5'),
                    'declarationFunction' => function (array $args) use ($hotspot_ripple_effect_size) {
                        $attrValue = $args["attrValue"];
                        $ripple_effect_size = intval($hotspot_ripple_effect_size) / 3;
                        return "animation-duration: {$attrValue}s!important; width: {$ripple_effect_size}px!important; height: {$ripple_effect_size}px!important;";
                    },
                ]),
                CommonStyle::style([
                    'selector' => "$order_class .dipi-sonar-circle:nth-child(2)",
                    'attr'     => static::getAttr($attrs, 'hotspot_ripple_effect_speed', '2.5'),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];    
                        $animation_part_speed = floatval($attrValue) / 3;
                        return "animation-delay: {$animation_part_speed}s!important;";
                    },
                ]),
                CommonStyle::style([
                    'selector' => "$order_class .dipi-sonar-circle:nth-child(3)",
                    'attr'     => static::getAttr($attrs, 'hotspot_ripple_effect_speed', '2.5'),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];    
                        $animation_part_speed = floatval($attrValue) * 2 / 3;
                        return "animation-delay: {$animation_part_speed}s!important;";
                    },
                ]),
                CommonStyle::style([
                    'selector' => "$order_class .dipi-sonar-circle:nth-child(4)",
                    'attr'     => static::getAttr($attrs, 'hotspot_ripple_effect_speed', '2.5'),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];    
                        return "animation-delay: {$attrValue}s!important;";
                    },
                ]),
                CommonStyle::style([
                    'selector' => "$order_class",
                    'attr'     => static::getAttr($attrs, 'hotspot_position_vertical', '10%'),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];    
                        return "top: $attrValue!important;";
                    },
                ]),
                CommonStyle::style([
                    'selector' => "$order_class",
                    'attr'     => static::getAttr($attrs, 'hotspot_position_horizontal', '10%'),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];    
                        return "left: $attrValue!important;";
                    },
                ]),
                CommonStyle::style([
                    'selector' => "$order_class .dipi-tooltip-wrap",
                    'attr'     => static::getAttr($attrs, 'tooltip_bg', ''),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];    
                        return "background-color: $attrValue!important;";
                    },
                ]),
                ($hotspot_ripple_effect === "on" && $hotspot_ripple_effect_style === "style-2") ? CommonStyle::style([
                    'selector' => "$order_class .dipi-sonar-circle",
                    'attr'     => static::getAttr($attrs, 'hotspot_ripple_effect_color', ''),
                    'declarationFunction' => function (array $args) {
                        $attrValue = $args["attrValue"];
                        return "border-color: $attrValue!important;";
                    },
                ]) : null,
                CommonStyle::style([
                    'selector'            => "$order_class .dipi-hotspot-icon",
                    'attr'                => static::getAttr($attrs, 'hotspot_icon', ''),
                    'declarationFunction' => [ static::class, 'icon_font_declaration' ],
                ]),
                CommonStyle::style([
                    'selector'            => "$order_class .dipi-tooltip-icon",
                    'attr'                => static::getAttr($attrs, 'tooltip_icon', ''),
                    'declarationFunction' => [ static::class, 'icon_font_declaration' ],
                ]),
            ],
		]);
    }
}
