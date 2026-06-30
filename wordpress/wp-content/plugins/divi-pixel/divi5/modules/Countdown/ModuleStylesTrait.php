<?php
namespace DIPI\Modules\Countdown;

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

        $style = [
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
                'attrName' => 'clock_font',
            ]),
            $elements->style([
                'attrName' => 'labels_font',
            ]),
            CommonStyle::style([
                'selector' => $order_class . ' .flip_clock div.time span.count, '.$order_class.' .block_clock div.face',
                'attr' => static::getAttr($attrs, 'clock_background', ''),
                'property' => "background"
            ])
        ];

        $clock_style = $attrs['style']['innerContent']['desktop']['value']?? 'flip_clock';
        if($clock_style == 'flip_clock') {
            $flip_clock_style = [
                CommonStyle::style([
                    'selector' => $order_class . ' .flip_clock div.time span.count.top',
                    'attr' => static::getAttr($attrs, 'flip_clock_top_border', ''),
                    'property' => "border-top-color"
                ]),
                CommonStyle::style([
                    'selector' => $order_class . ' .flip_clock div.time span.count.top',
                    'attr' => static::getAttr($attrs, 'flip_clock_separator_top_border', ''),
                    'property' => "border-bottom-color"
                ]),
                CommonStyle::style([
                    'selector' => $order_class . ' .flip_clock div.time span.count.bottom',
                    'attr' => static::getAttr($attrs, 'flip_clock_separator_bottom_border', ''),
                    'property' => "border-top-color"
                ]),
                CommonStyle::style([
                    'selector' => $order_class . ' .flip_clock div.time span.count.bottom',
                    'attr' => static::getAttr($attrs, 'flip_clock_bottom_border', ''),
                    'property' => "border-bottom-color"
                ]),
                CommonStyle::style([
                    'selector' => $order_class . ' .flip_clock .face .time',
                    'attr' => static::getAttr($attrs, 'clock_face_width', ''),
                    'property' => "width"
                ]),
                CommonStyle::style([
                    'selector' => $order_class . ' .flip_clock .face .time',
                    'attr' => static::getAttr($attrs, 'clock_face_height', ''),
                    'property' => "height"
                ]),
                SpacingStyle::style( [
                    'selector' => $order_class . " .flip_clock .face",
                    'attr' => static::getAttr($attrs, 'clock_face_margin', ''),
                ])
            ];
            $style = array_merge($style, $flip_clock_style);
        } else if($clock_style == 'block_clock') {
            $block_clock_style = [
                SpacingStyle::style( [
                    'selector' => $order_class . " .block_clock .face",
                    'attr' => static::getAttr($attrs, 'clock_face_margin', ''),
                ]),
                SpacingStyle::style( [
                    'selector' => $order_class . " .block_clock .face",
                    'attr' => static::getAttr($attrs, 'clock_face_padding', ''),
                ]),
            ];
            $block_clock_equalize_width = $attrs['block_clock_equalize_width']['innerContent']['desktop']['value'] ?? 'on';
            if($block_clock_equalize_width == "on") {
                $block_clock_style[] = CommonStyle::style([
                    'selector' => $order_class . ' .block_clock .face',
                    'attr' => static::getAttr($attrs, 'flex', ''),
                    'declarationFunction' => function ( array $args ) {
                        return  "flex: 1;";
                    }
                ]);
            } else {
                $block_clock_style[] = CommonStyle::style([
                    'selector' => $order_class . ' .block_clock .face_wrapper.face_wrapper',
                    'attr' => static::getAttr($attrs, 'block_clock_face_alignment', ''),
                    'property' => "justify-content"
                ]);
            }
            $style = array_merge($style, $block_clock_style);
        }

        Style::add(
			[
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => $style
			]
		);
    }
}
