<?php
namespace DIPI\Modules\CarouselItem;

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

        $use_icon = $attrs['use_icon']['innerContent']['desktop']['value'] ?? "off";
        $use_icon_circle = $attrs['use_icon_circle']['innerContent']['desktop']['value'] ?? "off";
        $use_icon_circle_border = $attrs['use_icon_circle_border']['innerContent']['desktop']['value'] ?? "off";
        $use_icon_font_size = $attrs['use_icon_font_size']['innerContent']['desktop']['value'] ?? "off";
        $use_icon_font_size_tablet = $attrs['use_icon_font_size']['innerContent']['tablet']['value'] ?? $use_icon_font_size;
        $use_icon_font_size_phone = $attrs['use_icon_font_size']['innerContent']['phone']['value'] ?? $use_icon_font_size_tablet;
        $carousel_image_align = $attrs['carousel_image_align']['innerContent']['desktop']['value'] ?? "";
        $carousel_icon_align = $attrs['carousel_icon_align']['innerContent']['desktop']['value'] ?? "";
        $button_align = $attrs['carousel_button']['decoration']['button']['desktop']['value']['alignment'] ?? "";

        $icon_selector = $order_class . ' .dipi-carousel-icon';
        $icon_hover_selector = $order_class . '.dipi_carousel_child:hover .dipi-carousel-icon';

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
                    'attrName'   => 'carousel_button',
                ]),
                $elements->style([
                    'attrName'   => 'title_font',
                ]),
                $elements->style([
                    'attrName'   => 'desc_font',
                ]),
                $elements->style([
                    'attrName'   => 'image',
                ]),
                SpacingStyle::style([
                    "selector" => "$order_class .dipi-image-wrap .dipi-carousel-icon, $order_class .dipi-image-wrap .dipi-carousel-image",
                    "attr" => static::getAttr($attrs, "item_icon_spacing"),
                    "important" => true,
                ]),
                CommonStyle::style([
                    'selector'  => $order_class . " .dipi-carousel-image",
                    'attr'      => static::getAttr($attrs, 'img_width'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"];
                        return "max-width: $attrValue !important;";
                    }
                ]),
                CommonStyle::style([
                    'selector'  => $icon_selector . ',' . $icon_hover_selector,
                    'attr'      => static::getAttr($attrs, 'icon_color'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"];
                        return "color: $attrValue !important;";
                    }
                ]),
                $use_icon_circle == "on" ? CommonStyle::style([
                    'selector'  => $icon_selector . ',' . $icon_hover_selector,
                    'attr'      => static::getAttr($attrs, 'icon_circle_color'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"];
                        return "background-color: $attrValue !important;";
                    }
                ]) : null,
                $use_icon_circle == "on" && $use_icon_circle_border === "on" ? CommonStyle::style([
                    'selector'  => $icon_selector . ',' . $icon_hover_selector,
                    'attr'      => static::getAttr($attrs, 'icon_circle_border_color'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"];
                        return "border-color: $attrValue !important;";
                    }
                ]) : null,
                CommonStyle::style([
                    'selector'            => $icon_selector,
                    'attr'                => static::getAttr($attrs, 'carousel_icon'),
                    'declarationFunction' => [ static::class, 'icon_font_declaration' ],
                ]),
                $use_icon_font_size === "on" ? CommonStyle::style([
                    'selector'  => $icon_selector . ',' . $icon_hover_selector,
                    'attr'      => static::getAttr($attrs, 'icon_font_size'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"];
                        $breakpoint = $args["breakpoint"];
                        return $breakpoint === "desktop" ? "font-size: $attrValue !important;" : "";
                    }
                ]) : null,
                $use_icon_font_size_tablet === "on" ? CommonStyle::style([
                    'selector'  => $icon_selector . ',' . $icon_hover_selector,
                    'attr'      => static::getAttr($attrs, 'icon_font_size'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"];
                        $breakpoint = $args["breakpoint"];
                        return $breakpoint === "tablet" ? "font-size: $attrValue !important;" : "";
                    }
                ]) : null,
                $use_icon_font_size_phone === "on" ? CommonStyle::style([
                    'selector'  => $icon_selector . ',' . $icon_hover_selector,
                    'attr'      => static::getAttr($attrs, 'icon_font_size'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"];
                        $breakpoint = $args["breakpoint"];
                        return $breakpoint === "phone" ? "font-size: $attrValue !important;" : "";
                    }
                ]) : null,
                (($use_icon === "on" && $carousel_icon_align !== "") ? CommonStyle::style([
                    'selector'  => '.dipi_carousel .swiper-container ' . $order_class . '.dipi_carousel_child .dipi-image-wrap',
                    'attr'      => static::getAttr($attrs, 'carousel_icon_align'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"];
                        return "justify-content: $attrValue !important;";
                    }
                ]) : (($use_icon === "off" && $carousel_image_align !== "") ? CommonStyle::style([
                    'selector'  => '.dipi_carousel .swiper-container ' . $order_class . '.dipi_carousel_child .dipi-image-wrap',
                    'attr'      => static::getAttr($attrs, 'carousel_image_align'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"];
                        return "justify-content: $attrValue !important;";
                    }
                ]) : CommonStyle::style([
                    'selector'  => '.dipi_carousel .swiper-container ' . $order_class . '.dipi_carousel_child .dipi-image-wrap',
                    'attr'      => $attrs['module']['advanced']['text']['text'] ?? [],
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"]["orientation"] ?? "";
                        return $attrValue !== "" ? "justify-content: $attrValue !important;" : "";
                    }
                ]))),
                CommonStyle::style([
                    'selector'  => '.dipi_carousel .swiper-container ' . $order_class . '.dipi_carousel_child',
                    'attr'      => $attrs['module']['advanced']['text']['text'] ?? [],
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"]["orientation"] ?? "";
                        return $attrValue !== "" ? "text-align: $attrValue !important;" : "";
                    }
                ]),
                $button_align !== "" ? CommonStyle::style([
                    'selector'  => '.dipi_carousel .swiper-container ' . $order_class . '.dipi_carousel_child .dipi-carousel-button-wrapper',
                    'attr'      => $attrs['carousel_button']['decoration']['button'] ?? [],
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"]["alignment"] ?? "center";
                        return "text-align: $attrValue !important;";
                    }
                ]) : null
            ],
		]);
    }
}
