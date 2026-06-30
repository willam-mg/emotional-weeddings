<?php
namespace DIPI\Modules\ParallaxImagesItem;

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

        $content_type = $attrs['content_type']['innerContent']['desktop']['value'] ?? 'Image';

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
                    'cssFields' => self::custom_css(),
                ]),
                $elements->style([
                    'attrName'   => 'content_button',
                ]),
                CommonStyle::style([
                    'selector'            => "$order_class.dipi_parallax_images_item",
                    'attr'                => static::getAttr($attrs, 'layer_max_width'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "max-width: $attrValue !important;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => "$order_class.dipi_parallax_images_item",
                    'attr'                => static::getAttr($attrs, 'position_x'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "left: $attrValue !important;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => "$order_class.dipi_parallax_images_item",
                    'attr'                => static::getAttr($attrs, 'position_y'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "top: $attrValue !important;";
                    }
                ]),
                $content_type === "Image" ? CommonStyle::style([
                    'selector'            => "$order_class .dipi-pi-item-image",
                    'attr'                => static::getAttr($attrs, 'layer_max_width'),
                    'declarationFunction' => function ( array $args ) {
                        return  "overflow: hidden;";
                    },
                ]) : null,
            ],
		]);
    }
}
