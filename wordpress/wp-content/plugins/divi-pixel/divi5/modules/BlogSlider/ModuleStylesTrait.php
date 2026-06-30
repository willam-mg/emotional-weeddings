<?php
namespace DIPI\Modules\BlogSlider;

if (!defined("ABSPATH")) {
    die("Direct access forbidden.");
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Border\BorderStyle;
use ET\Builder\Packages\Module\Options\Spacing\SpacingStyle;
use DIPI\Modules\Base\Swiper\SwiperStylesTrait;

trait ModuleStylesTrait
{
    use CustomCssTrait;
    use StyleDeclarationTrait;
    use SwiperStylesTrait;

    public static function getAttrByMode($attrs, $attr, $default = null, $mode = null) {
		return (((($attrs??[])[$attr]??[])['innerContent']??[])[$mode??'desktop']??[])['value']??$default??'';
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

    public static function getDipiAttr(
        $attrs,
        $attr,
        $default = null,
        $zoom = "",
        $unit = "",
        $wrap_func = ""
    ) {
        $beforeAttrValue = (($attrs ?? [])[$attr] ?? [])["innerContent"] ?? [
            "desktop" => ["value" => $default ?? ""],
        ];
        $afterAttrValue = $beforeAttrValue;
        if (empty($afterAttrValue["tablet"])) {
            $afterAttrValue["tablet"] = $afterAttrValue["desktop"];
        }
        if (empty($afterAttrValue["phone"])) {
            $afterAttrValue["phone"] = $afterAttrValue["tablet"];
        }
        $slug_value = $afterAttrValue["desktop"]["value"] ?? $default;
        $slug_value_tablet = $afterAttrValue["tablet"]["value"];
        $slug_value_phone = $afterAttrValue["phone"]["value"];
        if ($zoom === "") {
            $slug_value = $slug_value . $unit;
            $slug_value_tablet = $slug_value_tablet . $unit;
            $slug_value_phone = $slug_value_phone . $unit;
        } else {
            $slug_value = (float) $slug_value * $zoom . $unit;
            $slug_value_tablet = (float) $slug_value_tablet * $zoom . $unit;
            $slug_value_phone = (float) $slug_value_phone * $zoom . $unit;
        }
        if ($wrap_func !== "") {
            $slug_value = "$wrap_func($slug_value)";
            $slug_value_tablet = "$wrap_func($slug_value_tablet)";
            $slug_value_phone = "$wrap_func($slug_value_phone)";
        }
        $afterAttrValue["desktop"]["value"] = $slug_value;
        if (isset($beforeAttrValue["tablet"])) {
            $afterAttrValue["tablet"]["value"] = $slug_value_tablet;
        }
        if (isset($beforeAttrValue["phone"])) {
            $afterAttrValue["phone"]["value"] = $slug_value_phone;
        }
        return $afterAttrValue;
    }
    public static function getDipiAttrNumber(
        $attrs,
        $attr,
        $default = null,
        $delta = 0
    ) {
        $beforeAttrValue = (($attrs ?? [])[$attr] ?? [])["innerContent"] ?? [
            "desktop" => ["value" => $default ?? ""],
        ];
        $afterAttrValue = $beforeAttrValue;
        $afterAttrValue["desktop"]["value"] =
            (float) $beforeAttrValue["desktop"]["value"] + (float) $delta;
        if (isset($beforeAttrValue["tablet"])) {
            $afterAttrValu["tablet"]["value"] =
                (float) $beforeAttrValue["tablet"]["value"] + (float) $delta;
        }
        if (isset($beforeAttrValue["phone"])) {
            $afterAttrValue["phone"]["value"] =
                (float) $beforeAttrValue["phone"]["value"] + (float) $delta;
        }
        return $afterAttrValue;
    }

    public static function module_styles($args)
    {
        $attrs    = $args['attrs'] ?? [];
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class  = $args['orderClass'] ?? '';
        $order_number = preg_replace('/[^0-9]/', '', $order_class);

        $use_thumbnail_height = static::getAttrByMode($attrs, "use_thumbnail_height", "off");
        $show_more = static::getAttrByMode($attrs, "show_more", "off");
        $show_comments = static::getAttrByMode($attrs, "show_comments", "off");
        $slide_shadows = static::getAttrByMode($attrs, "use_thumbnail_height", "on");
        $navigation_circle = static::getAttrByMode($attrs, "navigation_circle", "off");
        $date_circle_icon = static::getAttrByMode($attrs, "date_circle_icon", "off");
        $date_circle_border = static::getAttrByMode($attrs, "date_circle_border", "off");
        $button_alignment = static::getAttrByMode($attrs, "button_alignment", "");

        $item_border_radius = $attrs['item']['decoration']['border']['desktop']['value']["radius"] ?? '';
        $item_has_radius = false;
        if($item_border_radius !== '')
        {
            $bottomLeft = $item_border_radius["bottomLeft"] ?? 0;
            $bottomRight = $item_border_radius["bottomRight"] ?? 0;
            $topLeft = $item_border_radius["topLeft"] ?? 0;
            $topRight = $item_border_radius["topRight"] ?? 0;
            $item_has_radius = (int)$bottomLeft !== 0 || (int)$bottomRight !== 0 || (int)$topLeft !== 0 || (int)$topRight !== 0;
        }

        $styles = [
            // Module.
            $elements->style(
                [
                    'attrName'   => 'module',
                    'styleProps' => [
                        'disabledOn' => [
                            'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                        ],
                    ],
                ]
            ),
            CssStyle::style(
                [
                    'selector'  => $args['orderClass'],
                    'attr'      => $attrs['css'] ?? [],
                    'cssFields' => static::custom_css(),
                ]
            ),
            $elements->style([
                'attrName'   => 'image',
            ]),
            $elements->style([
                'attrName'   => 'header_font',
            ]),
            $elements->style([
                'attrName'   => 'body_font',
            ]),
            $elements->style([
                'attrName'   => 'cat_font',
            ]),
            $elements->style([
                'attrName'   => 'author_font',
            ]),
            $elements->style([
                'attrName'   => 'date_font',
            ]),
            $elements->style([
                'attrName'   => 'overlay_bg',
                'styleProps' => [
                    'selector' => "$order_class .dipi-blog-post .dipi-blog-post-overlay, $order_class .dipi-blog-post:hover .dipi-blog-post-overlay",
                ]
            ]),
            $elements->style([
                'attrName'   => 'item',
            ]),
            $elements->style([
                'attrName'   => 'button',
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-button-prev:after",
                'attr'                => static::getAttr($attrs, 'navigation_prev_icon', ''),
                'declarationFunction' => [ static::class, 'icon_font_declaration' ],
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-button-next:after",
                'attr'                => static::getAttr($attrs, 'navigation_next_icon', ''),
                'declarationFunction' => [ static::class, 'icon_font_declaration' ],
            ]),
            $use_thumbnail_height === "on" ? CommonStyle::style([
                'selector'            => "$order_class img.wp-post-image",
                'attr'                => static::getAttr($attrs, 'thumbnail_height', ''),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "height: $attrValue !important;";
                }
            ]) : null,
            $show_more === "on" && $show_comments === "on" ? CommonStyle::style([
                'selector'            => "$order_class .dipi-comments",
                'attr'                => static::getAttr($attrs, 'show_more', ''),
                'declarationFunction' => function ( array $args ) {
                    return  "position: absolute; right: 0; bottom: 0; transform: translate(-50%, -40%);";
                }
            ]) : null,
            $show_more === "on" && $show_comments === "on" && $button_alignment === "right"? CommonStyle::style([
                'selector'            => "$order_class .dipi-bottom-content",
                'attr'                => static::getAttr($attrs, 'show_more', ''),
                'declarationFunction' => function ( array $args ) {
                    return  "padding-right: 65px;";
                }
            ]) : null,
            CommonStyle::style([
                'selector'            => "$order_class .dipi-bottom-content",
                'attr'                => static::getAttr($attrs, 'button_alignment', ''),
                'declarationFunction' => function ( array $args ) {
                    $button_alignment = $args["attrValue"];
                    $gButtonAlign = "";
                    if ($button_alignment === "left") $gButtonAlign = "flex-start";
                    else if ($button_alignment === "center") $gButtonAlign = "center";
                    else if ($button_alignment === "right") $gButtonAlign = "flex-end";
                    return  "justify-content: $gButtonAlign !important;";
                }
            ]),
            $slide_shadows === "on" ? CommonStyle::style([
                'selector'            => "$order_class .dipi-carousel-main .swiper-3d .swiper-slide-shadow-left",
                'attr'                => static::getAttr($attrs, 'shadow_overlay_color', ''),
                'declarationFunction' => function ( array $args ) {
                    $shadow_overlay_color = $args["attrValue"];
                    return "background-image: -webkit-gradient(linear, right top, left top, from($shadow_overlay_color), to(rgba(0, 0, 0, 0))); background-image: -webkit-linear-gradient(right, $shadow_overlay_color, rgba(0, 0, 0, 0)); background-image: -o-linear-gradient(right, $shadow_overlay_color, rgba(0, 0, 0, 0)); background-image: linear-gradient(to left, $shadow_overlay_color, rgba(0, 0, 0, 0));";
                }
            ]) : null,
            $slide_shadows === "on" ? CommonStyle::style([
                'selector'            => "$order_class .dipi-carousel-main .swiper-3d .swiper-slide-shadow-right",
                'attr'                => static::getAttr($attrs, 'shadow_overlay_color', ''),
                'declarationFunction' => function ( array $args ) {
                    $shadow_overlay_color = $args["attrValue"];
                    return "background-image: -webkit-gradient(linear, left top, right top, from($shadow_overlay_color), to(rgba(0, 0, 0, 0))); background-image: -webkit-linear-gradient(left, $shadow_overlay_color, rgba(0, 0, 0, 0));background-image: -o-linear-gradient(left, $shadow_overlay_color, rgba(0, 0, 0, 0)); background-image: linear-gradient(to right, $shadow_overlay_color, rgba(0, 0, 0, 0));";
                }
            ]) : null,
            $slide_shadows === "on" ? CommonStyle::style([
                'selector'            => "$order_class .dipi-carousel-main .swiper-3d .swiper-slide-shadow-top",
                'attr'                => static::getAttr($attrs, 'shadow_overlay_color', ''),
                'declarationFunction' => function ( array $args ) {
                    $shadow_overlay_color = $args["attrValue"];
                    return "background-image: -webkit-gradient(linear, left bottom, left top, from($shadow_overlay_color), to(rgba(0, 0, 0, 0))); background-image: -webkit-linear-gradient(bottom, $shadow_overlay_color, rgba(0, 0, 0, 0)); background-image: -o-linear-gradient(bottom, $shadow_overlay_color, rgba(0, 0, 0, 0)); background-image: linear-gradient(to top, $shadow_overlay_color, rgba(0, 0, 0, 0));";
                }
            ]) : null,
            $slide_shadows === "on" ? CommonStyle::style([
                'selector'            => "$order_class .dipi-carousel-main .swiper-3d .swiper-slide-shadow-bottom",
                'attr'                => static::getAttr($attrs, 'shadow_overlay_color', ''),
                'declarationFunction' => function ( array $args ) {
                    $shadow_overlay_color = $args["attrValue"];
                    return "background-image: -webkit-gradient(linear, left top, left bottom, from($shadow_overlay_color), to(rgba(0, 0, 0, 0))); background-image: -webkit-linear-gradient(top, $shadow_overlay_color, rgba(0, 0, 0, 0)); background-image: -o-linear-gradient(top, $shadow_overlay_color, rgba(0, 0, 0, 0));background-image: linear-gradient(to bottom, $shadow_overlay_color, rgba(0, 0, 0, 0));";
                }
            ]) : null,
            CommonStyle::style([
                'selector'            => "$order_class .dipi-blog-post .dipi-author",
                'attr'                => static::getAttr($attrs, 'author_align', ''),
                'declarationFunction' => function ( array $args ) {
                    $author_align = $args['attrValue'];
                    $gAuthorAlign = "";
                    if ($author_align === "left") $gAuthorAlign = "flex-start";
                    else if ($author_align === "center") $gAuthorAlign = "center";
                    else if ($author_align === "right") $gAuthorAlign = "flex-end";
                    return  "justify-content: $gAuthorAlign !important;";
                }
            ]),
            SpacingStyle::style([
                "selector" => "$order_class .swiper-container",
                "attr" => static::getAttr($attrs, "container_padding"),
                "important" => true,
            ]),
            SpacingStyle::style([
                "selector" => "$order_class .dipi-blog-post",
                "attr" => static::getAttr($attrs, "item_padding"),
                "important" => true,
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-button-prev, $order_class:hover .swiper-button-prev.swiper-arrow-button.show_on_hover",
                'attr'                => static::getAttr($attrs, 'navigation_position_left', ''),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return "left: $attrValue !important;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-button-prev.swiper-arrow-button.show_on_hover:before",
                'attr'                => static::getAttr($attrs, 'navigation_position_left', ''),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    $attrValue = (int)$attrValue;
                    if ($attrValue < 0) {
                        $attrValue = -$attrValue;
                        return "width: {$attrValue}px !important;";
                    }
                    return "";
                }
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-button-next, $order_class:hover .swiper-button-next.swiper-arrow-button.show_on_hover",
                'attr'                => static::getAttr($attrs, 'navigation_position_right', ''),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return "right: $attrValue !important;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-button-next.swiper-arrow-button.show_on_hover:before",
                'attr'                => static::getAttr($attrs, 'navigation_position_right', ''),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    $attrValue = (int)$attrValue;
                    if ($attrValue < 0) {
                        $attrValue = -$attrValue;
                        return "width: {$attrValue}px !important;";
                    }
                    return "";
                }
            ]),
            CommonStyle::style([
                'selector' => "$order_class .swiper-button-next:after, $order_class .swiper-button-next:before, $order_class .swiper-button-prev:after, $order_class .swiper-button-prev:before",
                'attr' => static::getAttr($attrs, 'navigation_color', ''),
                'property' => "color"
            ]),
            CommonStyle::style([
                'selector' => "$order_class .swiper-button-next, $order_class .swiper-button-prev",
                'attr' => static::getAttr($attrs, 'navigation_bg_color', ''),
                'property' => "background"
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-button-next, $order_class .swiper-button-prev",
                'attr'                => static::getAttr($attrs, 'navigation_size', ''),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args["attrValue"];
                    return "width: {$attrValue}px!important;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-button-next, $order_class .swiper-button-prev",
                'attr'                => static::getAttr($attrs, 'navigation_size', ''),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args["attrValue"];
                    return "height: {$attrValue}px!important;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-button-next:after, $order_class .swiper-button-next:before, $order_class .swiper-button-prev:after, $order_class .swiper-button-prev:before",
                'attr'                => static::getAttr($attrs, 'navigation_size', ''),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args["attrValue"];
                    return "font-size: {$attrValue}px!important;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-button-next, $order_class .swiper-button-prev",
                'attr'                => static::getAttr($attrs, 'navigation_padding', ''),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args["attrValue"];
                    return "padding: {$attrValue}px!important;";
                }
            ]),
            $navigation_circle === "on" ? CommonStyle::style([
                'selector'            => "$order_class .swiper-button-next, $order_class .swiper-button-prev",
                'attr'                => static::getAttr($attrs, 'navigation_circle', ''),
                'declarationFunction' => function ( array $args ) {
                    return "border-radius: 50% !important;";
                }
            ]) : null,
            CommonStyle::style([
                'selector'            => "$order_class .swiper-pagination-bullet",
                'attr'                => static::getAttr($attrs, 'pagination_color', ''),
                "property" => "background",
                "important" => true
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-pagination-bullet.swiper-pagination-bullet-active",
                'attr'                => static::getAttr($attrs, 'pagination_active_color', ''),
                "property" => "background",
                "important" => true
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-container-horizontal > .swiper-pagination-bullets, $order_class .swiper-pagination-fraction, $order_class .swiper-pagination-custom",
                'attr'                => static::getAttr($attrs, 'pagination_position', ''),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args["attrValue"];
                    return "bottom: {$attrValue}px !important;";
                }
            ]),
            $date_circle_icon === "on" ? CommonStyle::style([
                'selector'            => "$order_class .dipi-date",
                'attr'                => static::getAttr($attrs, 'date_circle_icon', ''),
                'declarationFunction' => function ( array $args ) {
                    return "border-radius: 100px !important;";
                }
            ]) : null,
            CommonStyle::style([
                'selector'            => "$order_class .dipi-date",
                'attr'                => static::getAttr($attrs, 'date_circle_color', ''),
                "property" => "background-color",
                "important" => true
            ]),
            $date_circle_border === "on" ? CommonStyle::style([
                'selector'            => "$order_class .dipi-date",
                'attr'                => static::getAttr($attrs, 'date_circle_border_color', ''),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args["attrValue"];
                    return "border-width:3px; border-style:solid; border-color: $attrValue !important;";
                }
            ]) : null,
            CommonStyle::style([
                'selector'            => "$order_class .dipi-date",
                'attr'                => static::getAttr($attrs, 'date_right_space', ''),
                "property" => "right",
                "important" => true
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .dipi-date",
                'attr'                => static::getAttr($attrs, 'date_top_space', ''),
                "property" => "top",
                "important" => true
            ]),
            $item_has_radius === true ? CommonStyle::style([
                'selector'            => "$order_class .dipi-blog-post",
                'attr'                => static::getAttr($attrs, 'item', ''),
                'declarationFunction' => function ( array $args ) {
                    return "overflow: hidden;";
                }
            ]) : null,
        ];

        $swiperStyles = static::swiper_module_styles($args);
        $styles = array_merge($styles, $swiperStyles);

        Style::add([
            'id'            => $args['id'],
            'name'          => $args['name'],
            'orderIndex'    => $args['orderIndex'],
            'storeInstance' => $args['storeInstance'],
            'styles'        => $styles
		]);
    }
}
