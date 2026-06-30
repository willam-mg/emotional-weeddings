<?php
namespace DIPI\Modules\Carousel;

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
    
    private static $props = [];

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

    public static function module_styles($args)
    {
        $attrs    = $args['attrs'] ?? [];
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class  = $args['orderClass'] ?? '';
        $order_number = preg_replace('/[^0-9]/', '', $order_class);

        $use_thumbnail_height = static::getAttrByMode($attrs, "use_thumbnail_height", "off");
        $carousel_icon_align = static::getAttrByMode($attrs, "carousel_icon_align", "");
        $button_align = $attrs['button']['decoration']['button']['desktop']['value']['alignment'] ?? "";

        $styles = [
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
                'attrName'   => 'title_font',
            ]),
            $elements->style([
                'attrName'   => 'desc_font',
            ]),
            $elements->style([
                'attrName'   => 'item',
            ]),
            $elements->style([
                'attrName'   => 'button',
            ]),
            SpacingStyle::style([
                "selector" => "$order_class .swiper-container",
                "attr" => static::getAttr($attrs, "container_padding"),
                "important" => true,
            ]),
            $use_thumbnail_height === "on" ? CommonStyle::style([
                'selector'            => "$order_class .dipi-carousel-image img",
                'attr'                => static::getAttr($attrs, 'thumbnail_height'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "height: $attrValue;";
                }
            ]) : null,
            $use_thumbnail_height === "on" ? CommonStyle::style([
                'selector'            => "$order_class .dipi-carousel-image img",
                'attr'                => static::getAttr($attrs, 'thumbnail_fit'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "object-fit: $attrValue;";
                }
            ]) : null,
            $carousel_icon_align !== "" ? CommonStyle::style([
                'selector'            => "$order_class .dipi_carousel_child .dipi-image-wrap",
                'attr'                => static::getAttr($attrs, 'carousel_icon_align'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "justify-content: $attrValue;";
                }
            ]) : CommonStyle::style([
                'selector'            => "$order_class .dipi_carousel_child .dipi-image-wrap",
                'attr'                => $attrs['module']['advanced']['text']['text'] ?? [],
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args["attrValue"]["orientation"] ?? "center";
                    return  "justify-content: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "$order_class.dipi_carousel .swiper-container .dipi_carousel_child",
                'attr'                => $attrs['module']['advanced']['text']['text'] ?? [],
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args["attrValue"]["orientation"] ?? "center";
                    return  "text-align: $attrValue;";
                }
            ]),
            $button_align !== "" ? CommonStyle::style([
                'selector'            => "$order_class.dipi_carousel .swiper-container .dipi_carousel_child .dipi-carousel-button-wrapper",
                'attr'                => $attrs['button']['decoration']['button'] ?? [],
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args["attrValue"]["alignment"] ?? "center";
                    return  "text-align: $attrValue;";
                }
            ]) : null,
            CommonStyle::style([
                'selector'            => "$order_class .dipi-carousel-wrapper",
                'attr'                => static::getAttr($attrs, 'continues', 'off'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  $attrValue === "on" ? "transition-timing-function: linear;" : "";
                }
            ])
        ];

        $swiperStyles = static::swiper_module_styles($args);
        $styles = array_merge($styles, $swiperStyles);

        Style::add(
			[
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => $styles
			]
		);
    }
}
