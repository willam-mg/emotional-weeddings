<?php
namespace DIPI\Modules\ExpandingCTA;

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
        $attrs    = $args['attrs'] ?? [];
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class  = $args['orderClass'] ?? '';
        $order_number = preg_replace('/[^0-9]/', '', $order_class);

        $content_selector = "$order_class.dipi_expanding_cta .dipi_expanding_cta_container .dipi_expanding_cta-content-wrapper .dipi_expanding_cta-content";
        $overlay_class = "$order_class.dipi_expanding_cta .dipi_extending_cta-overlay";
        $content_icon_selector = "$order_class.dipi_expanding_cta .dipi_expanding_cta_container .dipi_expanding_cta-content-wrapper .dipi-content-image-icon-wrap .et-pb-icon";
        $content_image_icon_selector = "$order_class.dipi_expanding_cta .dipi_expanding_cta_container .dipi_expanding_cta-content-wrapper .dipi-content-image-icon-wrap";
        $content_image_icon_width_selector = "$order_class.dipi_expanding_cta .dipi_expanding_cta_container .dipi_expanding_cta-content-wrapper .dipi-content-image-icon-wrap.dipi-image-wrapper";
        $zoom_in_padding_selector = "$order_class.dipi_expanding_cta.dipi-expanded-cta .dipi_expanding_cta_container .dipi_expanding_cta_container-background,
                    $order_class.dipi_expanding_cta.dipi-expanded-cta .dipi_expanding_cta_container .dipi_expanding_cta-content-wrapper,
					$order_class.dipi_expanding_cta.dipi-expanded-cta .dipi_expanding_cta_container > .et_parallax_bg_wrap";
        $content_alignment_margins = array(
            'left' => 'auto auto auto 0',
            'center' => 'auto',
            'right' => 'auto 0 auto auto',
        );

        $use_content_icon = static::getAttrByMode($attrs, 'use_content_icon', 'off');

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
                    CommonStyle::style([
						'selector'  => $content_icon_selector,
						'attr'      => static::getAttr($attrs, 'content_icon_color', ''),
						"property"  => "color",
					]),
                    CommonStyle::style([
						'selector'  => $content_image_icon_selector,
						'attr'      => static::getAttr($attrs, 'content_image_icon_background_color', ''),
						"property"  => "background-color",
					]),
                    CommonStyle::style([
						'selector'  => $content_icon_selector,
						'attr'      => static::getAttr($attrs, 'content_image_icon_width', ''),
						"property"  => "font-size",
					]),
                    CommonStyle::style([
						'selector'  => $content_image_icon_width_selector,
						'attr'      => static::getAttr($attrs, 'content_image_icon_width', ''),
						"property"  => "width",
					]),
                    $use_content_icon === "on" ? CommonStyle::style([
						'selector'  => $content_image_icon_width_selector,
						'attr'      => static::getAttr($attrs, 'content_image_icon_width', ''),
						"property"  => "height",
					]) : CommonStyle::style([
						'selector'  => $content_image_icon_width_selector,
						'attr'      => static::getAttr($attrs, 'content_image_icon_width', ''),
						'declarationFunction' => function ( array $args ) {
                            return "line-height: 0;";
                        }
					]),
                    CommonStyle::style([
						'selector'  => $content_selector,
						'attr'      => static::getAttr($attrs, 'content_width', ''),
						"property"  => "max-width",
					]),
                    CommonStyle::style([
						'selector'  => $content_selector,
						'attr'      => static::getAttr($attrs, 'content_alignment', ''),
						'declarationFunction' => function ( array $args ) use ($content_alignment_margins) {
                            $attrValue = $args["attrValue"] ?? "center";
                            $marginValue = $content_alignment_margins[$attrValue];
                            return "margin: $marginValue !important;";
                        }
					]),
                    CommonStyle::style([
						'selector'  => $content_image_icon_selector,
						'attr'      => static::getAttr($attrs, 'icon_alignment', ''),
						'declarationFunction' => function ( array $args ) use ($content_alignment_margins) {
                            $attrValue = $args["attrValue"] ?? "center";
                            $marginValue = $content_alignment_margins[$attrValue];
                            return "margin: $marginValue !important;";
                        }
					]),
                    CommonStyle::style([
						'selector'  => $zoom_in_padding_selector,
						'attr'      => static::getAttr($attrs, 'zoom_in_expanding', ''),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "transform: scale($attrValue);";
                        }
					]),
                    CommonStyle::style([
						'selector'            => "$order_class .dipi_expanding_cta_container .dipi-content-image-icon-wrap .dipi-content-icon",
						'attr'                => static::getAttr($attrs, 'content_icon', ''),
						'declarationFunction' => [ static::class, 'icon_font_declaration' ],
					]),
                    $elements->style([
						'attrName'   => 'container',
					]),
                    $elements->style([
						'attrName'   => 'content_title',
					]),
                    $elements->style([
						'attrName'   => 'content_desc',
					]),
                    $elements->style([
						'attrName'   => 'overlay_bg',
                        'styleProps' => [
                            'selector' => $overlay_class
                        ]
					]),
                    $elements->style([
						'attrName'   => 'main_bg',
					]),
                    $elements->style([
						'attrName'   => 'main_selector',
					]),
                    $elements->style([
						'attrName'   => 'header',
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
						'attrName'   => 'content_button',
					]),
                    $elements->style([
						'attrName'   => 'second_button',
					]),
                    $elements->style([
						'attrName'   => 'content_image',
					])
				],
			]
		);
    }
}
