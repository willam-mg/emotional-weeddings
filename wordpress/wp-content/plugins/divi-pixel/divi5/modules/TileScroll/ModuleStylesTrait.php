<?php
namespace DIPI\Modules\TileScroll;

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

        $use_overlay = self::getAttrByMode($attrs, 'use_overlay');
        $use_icon = self::getAttrByMode($attrs, 'content_icon')["useIcon"];

        $content_container_class = $order_class . " .dipi-tile-scroll-content-wrapper";
        $scroll_container_class = $order_class . " .dipi_tile_scroll_container";
        $content_image_icon_wrapper_selector = $order_class. " .dipi-tile-scroll-content-wrapper .dipi-content-image-icon-wrap";
        $content_icon_selector = $order_class. " .dipi-content-image-icon-wrap .et-pb-icon";
        $content_image_icon_selector = $order_class. " .dipi_tile_scroll_container .dipi-content-image," . $order_class . ".dipi_tile_scroll_container .dipi-icon-wrap .et-pb-icon";
        $content_image_icon_size_selector = $order_class . " .dipi_tile_scroll_container .dipi-content-image img";

        Style::add(
			[
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => [
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
                            'cssFields' => self::custom_css(),
                        ]
                    ),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-tile-scroll-items',
                        'attr' => self::getAttr($attrs, 'space_lines', ''),
                        'property' => "gap"
                    ]),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-tile-scroll-items .dipi_tile_scroll_item_container',
                        'attr' => self::getAttr($attrs, 'space_items', ''),
                        'property' => "gap"
                    ]),
                    $elements->style([
                        'attrName' => 'content_container',
                    ]),
                    $use_overlay === "on" ? $elements->style([
                        'attrName' => 'overlay_bg',
                    ]) : null,
                    CommonStyle::style([
                        'selector' => $content_container_class,
                        'attr' => self::getDipiAttr($attrs, 'cotent_bg_blur', '0', 1, "px", "blur"),
                        'property' => "backdrop-filter"
                    ]),
                    CommonStyle::style([
                        'selector' => $content_container_class,
                        'attr' => self::getDipiAttr($attrs, 'cotent_bg_blur', '0', 1, "px", "blur"),
                        'property' => "-webkit-backdrop-filter"
                    ]),
                    $use_icon === "on" ?  CommonStyle::style([
                        'selector' => $content_icon_selector,
                        'attr' => $attrs['content_icon']['innerContent'] ?? [],
                        'declarationFunction' => function ( array $args ) {
                            $icon = $args['attrValue']['icon'];
                            return self::icon_font_declaration($icon);
                        }
                    ]) : null,
                    $elements->style([
                        'attrName' => 'content_icon',
                    ]),
                    CommonStyle::style([
                        'selector' => $content_icon_selector,
                        'attr' => $attrs["content_icon"]["decoration"]["color"] ?? [],
                        'property' => "color"
                    ]),
                    CommonStyle::style([
                        'selector' => $content_image_icon_selector,
                        'attr' => $attrs['content_icon']['decoration']['backgroundColor'] ?? [],
                        'property' => "background-color"
                    ]),
                    CommonStyle::style([
                        'selector' => $content_icon_selector,
                        'attr' => $attrs['content_icon']['decoration']['iconSize'] ?? [],
                        'property' => "font-size"
                    ]),
                    CommonStyle::style([
                        'selector' => $content_image_icon_size_selector,
                        'attr' => $attrs['content_icon']['decoration']['iconSize'] ?? [],
                        'property' => "width"
                    ]),
                    CommonStyle::style([
                        'selector' => $content_image_icon_size_selector,
                        'attr' => $attrs['content_icon']['decoration']['iconSize'] ?? [],
                        'property' => "height"
                    ]),
                    CommonStyle::style([
                        'selector' => $scroll_container_class,
                        'attr' => self::getAttr($attrs, "scroll_container_width", ""),
                        'property' => "width"
                    ]),
                    CommonStyle::style([
                        'selector' => $scroll_container_class,
                        'attr' => self::getAttr($attrs, "scroll_container_height", ""),
                        'property' => "height"
                    ]),
                    CommonStyle::style([
                        'selector' => $content_container_class,
                        'attr' => self::getAttr($attrs, "content_width", ""),
                        'property' => "width"
                    ]),
                    CommonStyle::style([
                        'selector' => $content_container_class,
                        'attr' => self::getAttr($attrs, "content_height", ""),
                        'property' => "height"
                    ]),
                    CommonStyle::style([
                        'selector' => $content_image_icon_wrapper_selector,
                        'attr' => self::getAttr($attrs, "icon_alignment", ""),
                        'property' => "text-align"
                    ]),
                    $elements->style([
                        'attrName' => 'content_title',
                    ]),
                    $elements->style([
                        'attrName' => 'content_description',
                    ]),
                    $elements->style([
                        'attrName' => 'content_button',
                    ]),
				],
			]
		);
    }
}
