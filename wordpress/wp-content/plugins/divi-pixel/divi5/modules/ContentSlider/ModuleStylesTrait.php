<?php
namespace DIPI\Modules\ContentSlider;

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
		global $dipi_cs_selectors_string;

        $attrs    = $args['attrs'] ?? [];
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class  = $args['orderClass'] ?? '';
        $order_number = preg_replace('/[^0-9]/', '', $order_class);

        $progress_line_selector = "$order_class  .dipi-content-slider .dipi-progress-line";
        $progress_active_line_selector = "$order_class  .dipi-content-slider .dipi-progress-line-active";
        $gradations_selector = "$order_class .content-slider-gradations";
        $gradations_wrapper_selector = "$order_class .content-slider-gradations-wrapper";
        $gradations_active_selector = "$order_class .content-slider-item.active .content-slider-gradations";
        $gradations_active_wrapper_selector = "$order_class .content-slider-item.active .content-slider-gradations-wrapper";
        $slider_pin_selector = "$order_class.dipi_content_slider .dipi-slider-pin";
        $navigation_container_class = "$order_class .dipi-navigation";
        $navigation_class = "$order_class  .dipi-nav-button";
        $navigation_position_left_class = "$order_class .dipi-prev-button, $order_class:hover .dipi-prev-button.dipi-nav-button.show_on_hover";
        $navigation_position_right_class = "$order_class .dipi-next-button, $order_class:hover .dipi-next-button.dipi-nav-button.show_on_hover";
        $navigation_position_left_area_class = "$order_class .dipi-prev-button.dipi-nav-button.show_on_hover:before";
        $navigation_position_right_area_class = "$order_class .dipi-next-button.dipi-nav-button.show_on_hover:before";
        $navigation_hover_selector = "$order_class .dipi-nav-button:hover:after";
        $navigation_hover_bg_selector = "$order_class .dipi-nav-button:hover";

        $navigation_position_left = static::getAttrByMode($attrs, "navigation_position_left", "");
        $navigation_position_right = static::getAttrByMode($attrs, "navigation_position_right", "");
        $navigation_circle = static::getAttrByMode($attrs, "navigation_circle", "off");

        Style::add(
			[
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
                        'attrName'   => 'label',
					]),
                    $elements->style([
                        'attrName'   => 'active_label',
					]),
                    $elements->style([
                        'attrName'   => 'desc',
					]),
                    $elements->style([
                        'attrName'   => 'active_desc',
					]),
                    $elements->style([
                        'attrName'   => 'progress_line',
					]),
                    $elements->style([
                        'attrName'   => 'progress_active_line',
					]),
                    CommonStyle::style([
                        'selector' => $navigation_hover_selector,
                        'attr' => static::getAttr($attrs, 'navigation_color', ''),
                        'property' => "color",
                        'important' => true
                    ]),
                    CommonStyle::style([
                        'selector' => $navigation_class,
                        'attr' => static::getDipiAttr($attrs, 'navigation_color', ''),
                        'property' => "color",
                        'important' => true
                    ]),
                    CommonStyle::style([
                        'selector' => $navigation_class,
                        'attr' => static::getAttr($attrs, 'navigation_bg_color', ''),
                        'property' => "background",
                        'important' => true
                    ]),
                    CommonStyle::style([
                        'selector' => $navigation_hover_bg_selector,
                        'attr' => static::getAttr($attrs, 'navigation_bg_color', ''),
                        'property' => "background",
                        'important' => true
                    ]),
                    CommonStyle::style([
						'selector' => $navigation_class,
						'attr' => static::getAttr($attrs, 'navigation_size', ''),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "width: {$attrValue}px!important;";
                        }
					]),
                    CommonStyle::style([
						'selector' => $navigation_class,
						'attr' => static::getAttr($attrs, 'navigation_size', ''),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "height: {$attrValue}px!important;";
                        }
					]),
                    CommonStyle::style([
						'selector' => "$order_class .dipi-next-button:after, $order_class .dipi-next-button:before, $order_class .dipi-prev-button:after, $order_class .dipi-prev-button:before",
						'attr' => static::getAttr($attrs, 'navigation_size', ''),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "font-size: {$attrValue}px!important;";
                        }
					]),
                    CommonStyle::style([
						'selector'            => "$order_class .dipi-next-button, $order_class .dipi-prev-button",
						'attr'                => static::getAttr($attrs, 'navigation_padding', ''),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "padding: {$attrValue}px!important;";
                        }
					]),
                    $navigation_circle === "on" ? CommonStyle::style([
						'selector'            => "$order_class .dipi-next-button, $order_class .dipi-prev-button",
						'attr'                => static::getAttr($attrs, 'navigation_circle', ''),
						'declarationFunction' => function ( array $args ) {
                            return "border-radius: 50% !important;";
                        }
					]) : null,
                    CommonStyle::style([
						'selector'            => $navigation_position_left_class,
						'attr'                => static::getAttr($attrs, 'navigation_position_left', ''),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return "left: $attrValue !important;";
                        }
					]),
                    CommonStyle::style([
						'selector'            => $navigation_position_left_area_class,
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
						'selector'            => $navigation_position_right_class,
						'attr'                => static::getAttr($attrs, 'navigation_position_right', ''),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return "right: $attrValue !important;";
                        }
					]),
                    CommonStyle::style([
						'selector'            => $navigation_position_right_area_class,
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
						'selector'            => $navigation_container_class,
						'attr'                => static::getAttr($attrs, 'navigation_position_vertical', ''),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return "top: $attrValue !important;";
                        }
					]),
                    CommonStyle::style([
                        'selector' => "$order_class .dipi-next-button:after, $order_class .dipi-next-button:before, $order_class .dipi-prev-button:after, $order_class .dipi-prev-button:before",
                        'attr' => static::getAttr($attrs, 'navigation_color', ''),
                        'property' => "color",
                        'important' => true
                    ]),
                    CommonStyle::style([
						'selector'            => $navigation_class,
						'attr'                => static::getAttr($attrs, 'navigation', ''),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = ($args['attrValue'] === "on" ? "flex" : "none");
                            return "display: $attrValue !important;";
                        }
					]),
                    CommonStyle::style([
                        'selector' => $progress_line_selector,
                        'attr' => static::getAttr($attrs, 'progress_line_color', ''),
                        'property' => "background",
                    ]),
                    CommonStyle::style([
                        'selector' => $progress_line_selector,
                        'attr' => static::getAttr($attrs, 'progress_line_weight', ''),
                        'property' => "height",
                    ]),
                    SpacingStyle::style([
                        "selector" => $progress_line_selector,
                        "attr" => static::getAttr($attrs, "progress_line_margin"),
                    ]),
                    CommonStyle::style([
                        'selector' => $progress_active_line_selector,
                        'attr' => static::getAttr($attrs, 'progress_active_line_color', ''),
                        'property' => "background",
                    ]),
                    CommonStyle::style([
                        'selector' => $progress_active_line_selector,
                        'attr' => static::getAttr($attrs, 'progress_active_line_weight', ''),
                        'property' => "height",
                    ]),
                    SpacingStyle::style([
                        "selector" => $progress_active_line_selector,
                        "attr" => static::getAttr($attrs, "progress_active_line_margin"),
                    ]),
                    CommonStyle::style([
                        'selector' => $gradations_selector,
                        'attr' => static::getAttr($attrs, 'gradations_width', ''),
                        'property' => "width",
                    ]),
                    CommonStyle::style([
                        'selector' => $gradations_selector,
                        'attr' => static::getAttr($attrs, 'gradations_height', ''),
                        'property' => "height",
                    ]),
                    CommonStyle::style([
                        'selector' => $gradations_selector,
                        'attr' => static::getAttr($attrs, 'gradations_color', ''),
                        'property' => "background",
                    ]),
                    SpacingStyle::style([
                        "selector" => $gradations_wrapper_selector,
                        "attr" => static::getAttr($attrs, "gradations_margin"),
                    ]),
                    CommonStyle::style([
                        'selector' => $gradations_active_selector,
                        'attr' => static::getAttr($attrs, 'active_gradations_width', ''),
                        'property' => "width",
                    ]),
                    CommonStyle::style([
                        'selector' => $gradations_active_selector,
                        'attr' => static::getAttr($attrs, 'active_gradations_height', ''),
                        'property' => "height",
                    ]),
                    CommonStyle::style([
                        'selector' => $gradations_active_selector,
                        'attr' => static::getAttr($attrs, 'active_gradations_color', ''),
                        'property' => "background",
                    ]),
                    SpacingStyle::style([
                        "selector" => $gradations_active_wrapper_selector,
                        "attr" => static::getAttr($attrs, "active_gradations_margin"),
                    ]),
                    CommonStyle::style([
                        'selector' => $slider_pin_selector,
                        'property' => 'width',
                        'attr'     => static::getAttr($attrs, 'circle_size', ''),
                    ]),
                    CommonStyle::style([
                        'selector' => $slider_pin_selector,
                        'property' => 'height',
                        'attr'     => static::getAttr($attrs, 'circle_size', ''),
                    ]),
                    CommonStyle::style([
                        'selector' => $slider_pin_selector,
                        'property' => 'background',
                        'attr'     => static::getAttr($attrs, 'circle_bg_color', ''),
                    ]),
                    $elements->style([
                        'attrName'  => 'circle_border',
                        'styleProps' => [
                            "selector" => $slider_pin_selector
                        ]
                    ]),
                    CommonStyle::style([
                        'selector' => $dipi_cs_selectors_string,
                        'property' => 'animation-delay',
                        'attr'     => static::getAttr($attrs, 'content_delay', ''),
                    ]),
                    CommonStyle::style([
                        'selector' => $dipi_cs_selectors_string,
                        'property' => 'animation-duration',
                        'attr'     => static::getAttr($attrs, 'content_speed', ''),
                    ]),
				],
			]
		);
    }
}
