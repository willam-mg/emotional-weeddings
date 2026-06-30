<?php
namespace DIPI\Modules\Balloon;

if (!defined("ABSPATH")) {
    die("Direct access forbidden.");
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Border\BorderStyle;
use ET\Builder\Packages\Module\Options\Spacing\SpacingStyle;

trait ModuleStylesTrait
{
    use CustomCssTrait;
    use StyleDeclarationTrait;

    private static $props = [];

    public static function getAttrByMode($attrs, $attr, $default = null, $mode = null)
    {
        return (((($attrs ?? [])[$attr] ?? [])['innerContent'] ?? [])[$mode ?? 'desktop'] ?? [])['value'] ?? $default ?? '';
    }

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
        $tb_header_class = str_contains($args['orderClass'], '_tb_header') ? '.dipi_is_tb_header' : '';
        $tb_footer_class = str_contains($args['orderClass'], '_tb_footer') ? '.dipi_is_tb_footer' : '';

        $attrs = $args['attrs'] ?? [];
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class = $args['orderClass'] ?? '';
        $order_number = preg_replace('/[^0-9]/', '', $order_class);

        $use_icon_circle = static::getAttrByMode($attrs, "use_icon_circle", "off");
        $use_icon_circle_border = static::getAttrByMode($attrs, "use_icon_circle_border", "off");
        $use_icon_size = static::getAttrByMode($attrs, "use_icon_size", "off");
        $use_balloon_icon = static::getAttrByMode($attrs, "use_balloon_icon", "off");
        Style::add(
            [
                'id' => $args['id'],
                'name' => $args['name'],
                'orderIndex' => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles' => [
                    // Module.
                    $elements->style([
                        'attrName' => 'module',
                        'styleProps' => [
                            'disabledOn' => [
                                'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                            ],
                        ],
                    ]),
                    CssStyle::style(
                        [
                            'selector' => $args['orderClass'],
                            'attr' => $attrs['css'] ?? [],
                            'cssFields' => static::custom_css(),
                        ]
                    ),
                    $elements->style([
                        'attrName' => 'balloon_img',
                    ]),
                    $elements->style([
                        'attrName' => 'balloon_title',
                    ]),
                    $elements->style([
                        'attrName' => 'balloon_description',
                    ]),
                    $elements->style([
                        'attrName' => 'button',
                    ]),
                    // CommonStyle::style([
                    //     'selector' => $order_class,
                    //     'attr' => static::getAttr($attrs, 'width', '550px'),
                    //     'declarationFunction' => function (array $args) {
                    //         $attrValue = $args["attrValue"];
                    //         return "width:{$attrValue} !important";
                    //     }
                    // ]),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-balloon-image',
                        'attr' => static::getAttr($attrs, 'balloon_image_width', '100px'),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args["attrValue"];
                            return "width:{$attrValue} !important";
                        }
                    ]),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-balloon-icon',
                        'attr' => static::getAttr($attrs, 'icon_color', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args["attrValue"];
                            return "color:{$attrValue} !important";
                        }
                    ]),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-balloon-icon:hover',
                        'attr' => static::getAttr($attrs, 'icon_color', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args["attrValue"];
                            return "color:{$attrValue} !important";
                        }
                    ]),
                    $use_icon_circle === "on" ? CommonStyle::style([
                        'selector' => $order_class . ' .dipi-balloon-icon',
                        'attr' => static::getAttr($attrs, 'icon_circle_color', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args["attrValue"];
                            return "padding: 25px; border-radius: 100%; background-color: {$attrValue}";
                        }
                    ]) : null,


                    $use_icon_circle === "on" ? CommonStyle::style([
                        'selector' => $order_class . ' .dipi-balloon-icon:hover',
                        'attr' => static::getAttr($attrs, 'icon_circle_color', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args["attrValue"];
                            return "background-color: {$attrValue} !important;";
                        }
                    ]) : null,

                    $use_icon_circle_border === "on" ? CommonStyle::style([
                        'selector' => $order_class . ' .dipi-balloon-icon',
                        'attr' => static::getAttr($attrs, 'icon_circle_border_color', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args["attrValue"];
                            return "border: 3px solid {$attrValue}";
                        }
                    ]) : null,

                    $use_icon_circle_border === "on" ? CommonStyle::style([
                        'selector' => $order_class . ' .dipi-balloon-icon:hover',
                        'attr' => static::getAttr($attrs, 'icon_circle_border_color', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args["attrValue"];
                            return "border-color:  {$attrValue} !important;";
                        }
                    ]) : null,


                    $use_icon_size === "on" ? CommonStyle::style([
                        'selector' => $order_class . ' .dipi-balloon-icon',
                        'attr' => static::getAttr($attrs, 'icon_size', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args["attrValue"];
                            return "font-size: {$attrValue} !important;";
                        }
                    ]) : null,
                    $use_icon_size === "on" ? CommonStyle::style([
                        'selector' => $order_class . ' .dipi-balloon-icon:hover',
                        'attr' => static::getAttr($attrs, 'icon_size', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args["attrValue"];
                            return "font-size: {$attrValue} !important;";
                        }
                    ]) : null,

                    CommonStyle::style([
                        'selector' => " .dipi-ballon-on-top.dipi-balloon-zindex-{$order_number}{$tb_header_class}{$tb_footer_class}",
                        'attr' => static::getAttr($attrs, 'balloon_z_index', '9999'),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args["attrValue"];
                            return "z-index: {$attrValue} !important;";
                        }
                    ]),
                    SpacingStyle::style([
                        "selector" => ".dipi-balloon-open-{$order_number}{$tb_header_class}{$tb_footer_class} .tippy-box",
                        "attr" => static::getAttr($attrs, "custom_margin"),
                        "important" => true,
                    ]),
                    SpacingStyle::style([
                        'selector' => $order_class . ' .dipi-balloon-wrap',
                        "attr" => static::getAttr($attrs, "custom_padding"),
                        "important" => true,
                    ]),

                    CommonStyle::style([
                        'selector' => ".dipi-balloon-open-{$order_number}{$tb_header_class}{$tb_footer_class} .tippy-arrow",
                        'attr' => static::getAttr($attrs, 'balloon_arrow_color', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args["attrValue"];
                            return "color: {$attrValue} !important;";
                        }
                    ]),

                    // CommonStyle::style([
                    //     'selector' => ".dipi-balloon-open-{$order_number} .tippy-box",
                    //     'property' => 'max-width',
                    //     'attr' => static::getAttr($attrs, 'width', '550px')
                    // ]),
                    $use_balloon_icon === "on" ? CommonStyle::style([
                        'selector' => $order_class . ' .dipi-balloon-image-icon .et-pb-icon.dipi-balloon-icon',
                        'attr' => static::getAttr($attrs, 'balloon_icon'),
                        'declarationFunction' => [static::class, 'icon_font_declaration'],
                        'important' => true
                    ]) : null,
                ],
            ]
        );
    }
}
