<?php
namespace DIPI\Modules\ImageShowcase;

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

        $enable_vertical_scroll = static::getAttrByMode($attrs, "enable_vertical_scroll", "off");

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
                    $enable_vertical_scroll === "on" ? CommonStyle::style([
                        'selector'  => "$order_class:hover .dipi-mockup-vs .dipi_image_showcase_child>.et_pb_module_inner img",
                        'attr'      => static::getAttr($attrs, "scroll_speed"),
                        'declarationFunction' => function( array $args ) {
                            $attrValue = $args['attrValue'];
                            return "transition: all {$attrValue}s !important;";
                        },
                    ]) : null,
                    $enable_vertical_scroll === "on" ? CommonStyle::style([
                        'selector'  => "$order_class .dipi-mockup-vs .dipi_image_showcase_child>.et_pb_module_inner img",
                        'attr'      => static::getAttr($attrs, "back_scroll_speed"),
                        'declarationFunction' => function( array $args ) {
                            $attrValue = $args['attrValue'];
                            return "transition: all {$attrValue}s !important;";
                        },
                    ]) : null,
				],
			]
		);
    }
}
