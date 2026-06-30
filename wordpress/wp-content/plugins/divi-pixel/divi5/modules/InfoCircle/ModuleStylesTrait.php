<?php
namespace DIPI\Modules\InfoCircle;

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

        $icon_selector = "$order_class.dipi_info_circle .dipi_info_circle-small .dipi-info-image-icon-wrap .et-pb-icon";
        $image_icon_width_selector = "$order_class.dipi_info_circle .dipi-info-image-icon-wrap.dipi-image-wrapper";
    
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
                        'attrName'  => 'image_icon',
                    ]),
                    $elements->style([
                        'attrName'  => 'circle_list',
                    ]),
                    CommonStyle::style([
                        'selector'            => $icon_selector,
                        'attr'                => static::getAttr($attrs, 'icon_color'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "color: $attrValue;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => $icon_selector,
                        'attr'                => static::getAttr($attrs, 'image_icon_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "font-size: $attrValue;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => $image_icon_width_selector,
                        'attr'                => static::getAttr($attrs, 'image_icon_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "width: $attrValue;height: $attrValue;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class.dipi_info_circle .dipi_info_circle-in",
                        'attr'                => static::getAttr($attrs, 'icon_area_offset'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "top: calc(50% + " . ((float) $attrValue) . "px) !important;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class.dipi_info_circle .dipi-info-circle-out",
                        'attr'                => static::getAttr($attrs, 'icon_area_offset'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "top: calc(50% + " . ((float) $attrValue) . "px) !important;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class.dipi_info_circle .dipi_info_circle_container",
                        'attr'                => static::getAttr($attrs, 'circle_list_size'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "width: $attrValue;height: $attrValue;";
                        }
                    ]),
				],
			]
		);
    }
}
