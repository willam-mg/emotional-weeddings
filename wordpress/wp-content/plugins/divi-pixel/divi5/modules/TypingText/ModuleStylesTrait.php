<?php
namespace DIPI\Modules\TypingText;

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
                'attrName'  => 'all_font',
            ]),
            $elements->style([
                'attrName'  => 'typing_prefix',
            ]),
            $elements->style([
                'attrName'  => 'typing_text',
            ]),
            $elements->style([
                'attrName'  => 'typing_suffix',
            ]),
            $elements->style([
                'attrName'  => 'cursor_char',
            ]),
            CommonStyle::style([
                'selector' => $order_class . ' .dipi-typing',
                'attr' => $attrs['all_font']['decoration']['font']['font'] ?? [],
                'declarationFunction' => function ( array $args ) {
                  $attrValue = $args['attrValue']['textAlign'] ?? 'left';
                  $gTextAlign = "";
                  if($attrValue === "left" )
                    $gTextAlign = "flex-start";
                  else if($attrValue === "center")
                    $gTextAlign = "center";
                  else if($attrValue === "right")
                    $gTextAlign = "flex-end";
                  return "justify-content :{$gTextAlign}!important;";
                }
            ]),
            CommonStyle::style([
                'selector' => $order_class . ' .dipi-typing',
                'attr' => static::getAttr($attrs,'flex_direction', 'row'),
                'property' => "flex-direction",
                'important' => true
            ])
        ];

        $showCursor = static::getAttrByMode($attrs,'show_cursor', 'on');
        if($showCursor !== 'on') {
            $typingPadding = $attrs['typing_text']['decoration']['spacing']['desktop']['value']['padding'] ?? '';
            $typingPaddingSum = 0;
            if($typingPadding && $typingPadding != "")
                $typingPaddingSum = (int)$typingPadding['top'] + (int)$typingPadding['bottom'];
            $styles[] = CommonStyle::style([
                'selector' => $order_class . ' .dipi-typing-wrap',
                'attr' => $attrs['typing_text']['decoration']['font']['font'] ?? [],
                'declarationFunction' => function ( array $args ) use ($typingPaddingSum) {
                  $typingFontSize = isset($args['attrValue']['size']) ? (int)$args['attrValue']['size'] : 14;
                  $typingFontLineHeight = isset($args['attrValue']['lineHeight']) ? (float)$args['attrValue']['lineHeight'] : 1.7;
                  $typingTextHeight = $typingFontSize * $typingFontLineHeight + $typingPaddingSum;
                  return "height: {$typingTextHeight}px !important;";
                }
            ]);
        }

        Style::add(
			[
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => $styles,
			]
		);
    }
}
