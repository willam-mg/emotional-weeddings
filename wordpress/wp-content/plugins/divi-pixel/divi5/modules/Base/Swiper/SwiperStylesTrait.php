<?php
namespace DIPI\Modules\Base\Swiper;

if (!defined("ABSPATH")) {
    die("Direct access forbidden.");
}

use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;

trait SwiperStylesTrait
{
    use SwiperRenderTrait;
    /**
	 * Icon Font declaration.
	 *
	 * This function will declare icon font for Child module.
	 *
	 * @param array $params {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  If set to true, the CSS will be added with !important.
	 *     @type string     $returnType This is the type of value that the function will return. Can be either string or key_value_pair.
	 * }
	 *
	 * @return string
	 * @since ??
	 */
	static function swiper_icon_font_declaration( $params ) {
		$icon_attr = $params['attrValue'] ?? [];

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => [
					'font-family' => true,
					'content'     => true,
				],
			]
		);

		if ( ! empty( $icon_attr ) ) {
			$style_declarations->add( 'content', '"' . Utils::process_font_icon( $icon_attr ) . '"' );
			$font_family = isset( $icon_attr['type'] ) && 'fa' === $icon_attr['type'] ? 'FontAwesome' : 'ETmodules';
			$font_weight = $icon_attr['weight'];
			$style_declarations->add( 'font-family', $font_family );
			$style_declarations->add( 'font-weight', $font_weight );
		}

		return $style_declarations->value() ?? "";
	}

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

    public static function swiper_module_styles($args)
    {
        $attrs    = $args['attrs'] ?? [];
        $order_class  = $args['orderClass'] ?? '';

        $swiper_default_values = static::get_swiper_default_values();

        $slide_shadows = static::getAttrByMode($attrs, "slide_shadows", $swiper_default_values['slide_shadows']);
        $navigation_circle = static::getAttrByMode($attrs, "navigation_circle", $swiper_default_values['navigation_circle']);
        $navigation_prev_icon_yn = static::getAttrByMode($attrs, "navigation_prev_icon_yn", $swiper_default_values['navigation_prev_icon_yn']);
        $navigation_next_icon_yn = static::getAttrByMode($attrs, "navigation_next_icon_yn", $swiper_default_values['navigation_next_icon_yn']);

        $styles = [
            $navigation_prev_icon_yn === "on" ? CommonStyle::style([
                'selector'            => "$order_class .swiper-button-prev:after",
                'attr'                => static::getAttr($attrs, 'navigation_prev_icon', $swiper_default_values['navigation_prev_icon']),
                'declarationFunction' => [ static::class, 'swiper_icon_font_declaration' ],
            ]) : null,
            $navigation_next_icon_yn === "on" ? CommonStyle::style([
                'selector'            => "$order_class .swiper-button-next:after",
                'attr'                => static::getAttr($attrs, 'navigation_next_icon', $swiper_default_values['navigation_next_icon']),
                'declarationFunction' => [ static::class, 'swiper_icon_font_declaration' ],
            ]) : null,
            $slide_shadows === "on" ? CommonStyle::style([
                'selector'            => "$order_class .swiper-3d .swiper-slide-shadow-left",
                'attr'                => static::getAttr($attrs, 'shadow_overlay_color', $swiper_default_values['shadow_overlay_color']),
                'declarationFunction' => function ( array $args ) {
                    $shadow_overlay_color = $args["attrValue"];
                    return "background-image: -webkit-gradient(linear, right top, left top, from($shadow_overlay_color), to(rgba(0, 0, 0, 0))); background-image: -webkit-linear-gradient(right, $shadow_overlay_color, rgba(0, 0, 0, 0)); background-image: -o-linear-gradient(right, $shadow_overlay_color, rgba(0, 0, 0, 0)); background-image: linear-gradient(to left, $shadow_overlay_color, rgba(0, 0, 0, 0));";
                }
            ]) : null,
            $slide_shadows === "on" ? CommonStyle::style([
                'selector'            => "$order_class .swiper-3d .swiper-slide-shadow-right",
                'attr'                => static::getAttr($attrs, 'shadow_overlay_color', $swiper_default_values['shadow_overlay_color']),
                'declarationFunction' => function ( array $args ) {
                    $shadow_overlay_color = $args["attrValue"];
                    return "background-image: -webkit-gradient(linear, left top, right top, from($shadow_overlay_color), to(rgba(0, 0, 0, 0))); background-image: -webkit-linear-gradient(left, $shadow_overlay_color, rgba(0, 0, 0, 0));background-image: -o-linear-gradient(left, $shadow_overlay_color, rgba(0, 0, 0, 0)); background-image: linear-gradient(to right, $shadow_overlay_color, rgba(0, 0, 0, 0));";
                }
            ]) : null,
            $slide_shadows === "on" ? CommonStyle::style([
                'selector'            => "$order_class .swiper-3d .swiper-slide-shadow-top",
                'attr'                => static::getAttr($attrs, 'shadow_overlay_color', $swiper_default_values['shadow_overlay_color']),
                'declarationFunction' => function ( array $args ) {
                    $shadow_overlay_color = $args["attrValue"];
                    return "background-image: -webkit-gradient(linear, left bottom, left top, from($shadow_overlay_color), to(rgba(0, 0, 0, 0))); background-image: -webkit-linear-gradient(bottom, $shadow_overlay_color, rgba(0, 0, 0, 0)); background-image: -o-linear-gradient(bottom, $shadow_overlay_color, rgba(0, 0, 0, 0)); background-image: linear-gradient(to top, $shadow_overlay_color, rgba(0, 0, 0, 0));";
                }
            ]) : null,
            $slide_shadows === "on" ? CommonStyle::style([
                'selector'            => "$order_class .swiper-3d .swiper-slide-shadow-bottom",
                'attr'                => static::getAttr($attrs, 'shadow_overlay_color', $swiper_default_values['shadow_overlay_color']),
                'declarationFunction' => function ( array $args ) {
                    $shadow_overlay_color = $args["attrValue"];
                    return "background-image: -webkit-gradient(linear, left top, left bottom, from($shadow_overlay_color), to(rgba(0, 0, 0, 0))); background-image: -webkit-linear-gradient(top, $shadow_overlay_color, rgba(0, 0, 0, 0)); background-image: -o-linear-gradient(top, $shadow_overlay_color, rgba(0, 0, 0, 0));background-image: linear-gradient(to bottom, $shadow_overlay_color, rgba(0, 0, 0, 0));";
                }
            ]) : null,
            CommonStyle::style([
                'selector'            => "$order_class .swiper-button-prev, $order_class:hover .swiper-button-prev.swiper-arrow-button.show_on_hover",
                'attr'                => static::getAttr($attrs, 'navigation_position_left', $swiper_default_values['navigation_position_left']),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return "left: $attrValue !important;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-button-prev.swiper-arrow-button.show_on_hover:before",
                'attr'                => static::getAttr($attrs, 'navigation_position_left', $swiper_default_values['navigation_position_left']),
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
                'attr'                => static::getAttr($attrs, 'navigation_position_right', $swiper_default_values['navigation_position_right']),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return "right: $attrValue !important;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-button-next.swiper-arrow-button.show_on_hover:before",
                'attr'                => static::getAttr($attrs, 'navigation_position_right', $swiper_default_values['navigation_position_right']),
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
                'attr' => static::getAttr($attrs, 'navigation_color', $swiper_default_values['navigation_color']),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args["attrValue"];
                    return "color: {$attrValue}!important;";
                }
            ]),
            CommonStyle::style([
                'selector' => "$order_class .swiper-button-next, $order_class .swiper-button-prev",
                'attr' => static::getAttr($attrs, 'navigation_bg_color', $swiper_default_values['navigation_bg_color']),
                'property' => "background"
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-button-next, $order_class .swiper-button-prev",
                'attr'                => static::getAttr($attrs, 'navigation_size', $swiper_default_values['navigation_size']),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args["attrValue"];
                    return "width: {$attrValue}px!important;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-button-next, $order_class .swiper-button-prev",
                'attr'                => static::getAttr($attrs, 'navigation_size', $swiper_default_values['navigation_size']),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args["attrValue"];
                    return "height: {$attrValue}px!important;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-button-next:after, $order_class .swiper-button-next:before, $order_class .swiper-button-prev:after, $order_class .swiper-button-prev:before",
                'attr'                => static::getAttr($attrs, 'navigation_size', $swiper_default_values['navigation_size']),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args["attrValue"];
                    return "font-size: {$attrValue}px!important;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-button-next, $order_class .swiper-button-prev",
                'attr'                => static::getAttr($attrs, 'navigation_padding', $swiper_default_values['navigation_padding']),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args["attrValue"];
                    return "padding: {$attrValue}px!important;";
                }
            ]),
            $navigation_circle === "on" ? CommonStyle::style([
                'selector'            => "$order_class .swiper-button-next, $order_class .swiper-button-prev",
                'attr'                => static::getAttr($attrs, 'navigation_circle', $swiper_default_values['navigation_circle']),
                'declarationFunction' => function ( array $args ) {
                    return "border-radius: 50% !important;";
                }
            ]) : null,
            CommonStyle::style([
                'selector'            => "$order_class .swiper-pagination-bullet",
                'attr'                => static::getAttr($attrs, 'pagination_color', $swiper_default_values['pagination_color']),
                "property" => "background",
                "important" => true
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-pagination-bullet.swiper-pagination-bullet-active",
                'attr'                => static::getAttr($attrs, 'pagination_active_color', $swiper_default_values['pagination_active_color']),
                "property" => "background",
                "important" => true
            ]),
            CommonStyle::style([
                'selector'            => "$order_class .swiper-container-horizontal > .swiper-pagination-bullets, $order_class .swiper-pagination-fraction, $order_class .swiper-pagination-custom",
                'attr'                => static::getAttr($attrs, 'pagination_position', $swiper_default_values['pagination_position']),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args["attrValue"];
                    return "bottom: {$attrValue}px !important;";
                }
            ]),
        ];

        return $styles;
    }
}
