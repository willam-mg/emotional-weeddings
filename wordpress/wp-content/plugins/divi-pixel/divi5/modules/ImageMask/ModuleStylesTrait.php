<?php
namespace DIPI\Modules\ImageMask;

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

    public static function module_styles($args)
    {
        $attrs    = $args['attrs'] ?? [];
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class  = $args['orderClass'] ?? '';
        $order_number = preg_replace('/[^0-9]/', '', $order_class);

        $layer_1_enable = static::getAttrByMode($attrs, 'layer_1_enable', 'off');
        $layer_1_background_type = static::getAttrByMode($attrs, 'layer_1_background_type', 'Solid Color');
        $layer_2_enable = static::getAttrByMode($attrs, 'layer_2_enable', 'off');
        $layer_3_enable = static::getAttrByMode($attrs, 'layer_3_enable', 'off');

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
                        'attrName'   => 'main_bg',
                    ]),
                    $layer_1_enable === "on" && $layer_1_background_type === "Solid Color" ? CommonStyle::style([
                        'selector'            => "$order_class .st1",
                        'attr'                => static::getAttr($attrs, 'layer_1_background_color'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "fill: $attrValue;";
                        }
                    ]) : null,
                    $layer_2_enable === "on" ? CommonStyle::style([
                        'selector'            => "$order_class .s02",
                        'attr'                => static::getAttr($attrs, 'layer_2_background_color'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "fill: $attrValue;";
                        }
                    ]) : null,
                    $layer_3_enable === "on" ? CommonStyle::style([
                        'selector'            => "$order_class .s03",
                        'attr'                => static::getAttr($attrs, 'layer_3_background_color'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "fill: $attrValue;";
                        }
                    ]) : null,
				],
			]
		);
    }
}
