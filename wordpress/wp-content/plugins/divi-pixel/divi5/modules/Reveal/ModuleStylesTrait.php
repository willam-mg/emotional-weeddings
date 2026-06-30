<?php
namespace DIPI\Modules\Reveal;

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

        $container_selector = static::getAttrByMode($attrs, 'container_selector', '');
        $container_overlay_selector = "$container_selector.dipi-reveal-oa-html ~ .dipi-reveal-overlay, $container_selector.dipi-reveal-oa-html .dipi-reveal-overlay, $container_selector.dipi-reveal-oa-css:after";
        $collapsed_container_selector = ".dipi-reveal-container-collapsed";
        $expanded_container_selector = ".dipi-reveal-container-expanded";
        $dipi_reveal_container_selector = ".dipi-reveal-container-$order_number";
        $collapsed_module_selector = "$order_class .dipi-reveal.collapsed";
        $expanded_module_selector = "$order_class .dipi-reveal.expanded";

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
                'attrName' => 'show_less_button',
            ]),
            $elements->style([
                'attrName' => 'show_more_button',
            ]),
            CommonStyle::style([
                'selector'      => "$order_class .dipi-reveal",
                'property'      => 'text-align',
                'attr'          => static::getAttr($attrs, 'reveal_button_align', '')
            ]),
            CommonStyle::style([
                'selector'      => $collapsed_module_selector,
                'property'      => 'margin-top',
                'attr'          => static::getDipiAttr($attrs, 'more_v_offset', '', '')
            ]),
            CommonStyle::style([
                'selector'      => $collapsed_module_selector,
                'property'      => 'margin-bottom',
                'attr'          => static::getDipiAttr($attrs, 'more_v_offset', '', -1, 'px')
            ]),
            CommonStyle::style([
                'selector'      => $expanded_module_selector,
                'property'      => 'margin-top',
                'attr'          => static::getDipiAttr($attrs, 'less_v_offset', '', '')
            ]),
            CommonStyle::style([
                'selector'      => $expanded_module_selector,
                'property'      => 'margin-bottom',
                'attr'          => static::getDipiAttr($attrs, 'less_v_offset', '', -1, 'px')
            ]),
            CommonStyle::style([
                'selector'      => $dipi_reveal_container_selector.$collapsed_container_selector,
                'property'      => 'transition-duration',
                'attr'          => static::getAttr($attrs, 'less_animation_time', '')
            ]),	
            CommonStyle::style([
                'selector'      => $dipi_reveal_container_selector.$expanded_container_selector,
                'property'      => 'transition-duration',
                'attr'          => static::getAttr($attrs, 'more_animation_time', '')
            ]),	
            CommonStyle::style([
                'selector'      => $container_overlay_selector,
                'property'      => 'content',
                'attr'          => ''
            ]),		
            CommonStyle::style([
                'selector'      => $container_overlay_selector,
                'property'      => 'z-index',
                'attr'          => static::getAttr($attrs, 'overlay_z_index', '')
            ]),	
            
        ];

        $use_overlay = static::getAttrByMode($attrs, 'use_overlay', '');

        if($use_overlay === "on") {
            $styles[] = $elements->style([
                'attrName' => 'overlay_bg',
                'styleProps' => [
                    'selector' => $container_overlay_selector,
                ]
            ]);
        }

        Style::add([
            'id'            => $args['id'],
            'name'          => $args['name'],
            'orderIndex'    => $args['orderIndex'],
            'storeInstance' => $args['storeInstance'],
            'styles'        => $styles
		]);
    }
}
