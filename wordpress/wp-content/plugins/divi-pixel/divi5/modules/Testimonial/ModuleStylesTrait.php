<?php
namespace DIPI\Modules\Testimonial;

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

        $container_class = "$order_class .swiper-container";
        $item_class = "$order_class .dipi-testimonial-item";
        $image_class = "$order_class .dipi-testimonial-img, .dipi-testimonial-review-popup-open $order_class-popup .dipi-testimonial-img";

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
            SpacingStyle::style([
                "selector" => $container_class,
                "attr" => static::getAttr($attrs, "container_padding"),
                "important" => true,
            ]),
            SpacingStyle::style([
                "selector" => $item_class,
                "attr" => static::getAttr($attrs, "item_padding"),
                "important" => true,
            ]),
            $elements->style([
                'attrName'   => 'link_font',
            ]),
            $elements->style([
                'attrName'   => 'ul_font',
            ]),
            $elements->style([
                'attrName'   => 'ol_font',
            ]),
            $elements->style([
                'attrName'   => 'quote_font',
            ]),
            $elements->style([
                'attrName'   => 'header_1',
            ]),
            $elements->style([
                'attrName'   => 'header_2',
            ]),
            $elements->style([
                'attrName'   => 'header_3',
            ]),
            $elements->style([
                'attrName'   => 'header_4',
            ]),
            $elements->style([
                'attrName'   => 'header_5',
            ]),
            $elements->style([
                'attrName'   => 'header_6',
            ]),
            $elements->style([
                'attrName'   => 'testimonial_name',
            ]),
            $elements->style([
                'attrName'   => 'testimonial_text',
            ]),
            $elements->style([
                'attrName'   => 'company_name',
            ]),
            $elements->style([
                'attrName'   => 'readmore',
            ]),
            $elements->style([
                'attrName'   => 'review_popup_name',
            ]),
            $elements->style([
                'attrName'   => 'review_popup_text',
            ]),
            $elements->style([
                'attrName'   => 'review_popup_company_name',
            ]),
            $elements->style([
                'attrName'   => 'item_bg',
            ]),
            $elements->style([
                'attrName'   => 'profile_image',
            ]),
            $elements->style([
                'attrName'   => 'profile_image_box_shadow',
            ]),
            $elements->style([
                'attrName'   => 'profile_image_filters',
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .dipi-testimonial-main .dipi-testimonial-rating, .dipi-testimonial-review-popup-open  $order_class-popup .dipi-testimonial-rating",
                'attr'                => static::getAttr($attrs, 'rating_size'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return "font-size: $attrValue !important;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .dipi-testimonial-main .dipi-testimonial-rating span:not(:last-of-type), .dipi-testimonial-review-popup-open  $order_class-popup .dipi-testimonial-rating span:not(:last-of-type)",
                'attr'                => static::getAttr($attrs, 'rating_spacing'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return "margin-right: $attrValue !important;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .dipi-testimonial-main .dipi-testimonial-rating .dipi-testimonial-star-rating, .dipi-testimonial-review-popup-open  $order_class-popup .dipi-testimonial-star-rating",
                'attr'                => static::getAttr($attrs, 'rating_color'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return "color: $attrValue !important;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .dipi-testimonial-main .dipi-testimonial-rating .dipi-testimonial-star-rating-o, .dipi-testimonial-review-popup-open  $order_class-popup .dipi-testimonial-star-rating-o",
                'attr'                => static::getAttr($attrs, 'empty_rating_color'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return "color: $attrValue !important;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .dipi-testimonial-item",
                'attr'                => static::getAttr($attrs, 'item_align'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return "text-align: $attrValue !important;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .dipi-testimonial-img",
                'attr'                => static::getAttr($attrs, 'item_align'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    if ($attrValue === "left") return "margin-right: auto !important;";
                    if ($attrValue === "right") return "margin-left: auto !important;";
                    return "margin: 10px auto !important;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $image_class,
                'attr'                => static::getAttr($attrs, 'img_width'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return "width: $attrValue !important; height: $attrValue !important;";
                }
            ]),
            CommonStyle::style([
                'selector'            => ".mfp-wrap .mfp-container $order_class-popup.dipi-review-popup-text button.mfp-close:hover",
                'attr'                => static::getAttr($attrs, 'close_icon_bg_color'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return "background: $attrValue !important;";
                }
            ]),
            CommonStyle::style([
                'selector'            => ".mfp-wrap.mfp-close-btn-in .mfp-container $order_class-popup button.mfp-close:hover",
                'attr'                => static::getAttr($attrs, 'close_icon_color'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return "color: $attrValue !important;";
                }
            ]),
        ];

        $swiperStyles = static::swiper_module_styles($args);
        $styles = array_merge($styles, $swiperStyles);

        Style::add([
            'id'            => $args['id'],
            'name'          => $args['name'],
            'orderIndex'    => $args['orderIndex'],
            'storeInstance' => $args['storeInstance'],
            'styles'        => $styles,
        ]);
    }
}
