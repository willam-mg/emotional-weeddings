<?php
namespace DIPI\Modules\ContentSliderChild;

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
        $slider_pin_selector = ".dipi-content-slider[data-active-order-num='{$order_number}'] .dipi-progress-line .dipi-slider-pin";

        Style::add([
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
                    'attrName'  => 'title',
                ]),
                $elements->style([
                    'attrName'  => 'active_label',
                ]),
                $elements->style([
                    'attrName'  => 'desc',
                ]),
                $elements->style([
                    'attrName'  => 'active_desc',
                ]),
                $elements->style([
                    'attrName'  => 'circle_border',
                    'styleProps' => [
                        "selector" => $slider_pin_selector
                    ]
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
            ],
		]);
    }
}
