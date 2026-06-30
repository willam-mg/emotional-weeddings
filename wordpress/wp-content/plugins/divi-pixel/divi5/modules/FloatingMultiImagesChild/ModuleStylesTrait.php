<?php
namespace DIPI\Modules\FloatingMultiImagesChild;

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

        $use_icon = static::getAttrByMode($attrs, "use_icon", "off");
		$image = static::getAttrByMode($attrs, 'image', array());
        $img_pathinfo = pathinfo($image["src"] ?? "");
        $is_img_svg = isset($img_pathinfo['extension']) ? 'svg' === $img_pathinfo['extension'] : false;

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
                    'attrName'   => 'image',
                ]),
                CommonStyle::style([
                    'selector' => $order_class,
                    'attr' => static::getAttr($attrs, 'horizontal_position', '0%'),
                    'property' => "left",
                    'important' => true
                ]),
                CommonStyle::style([
                    'selector' => $order_class,
                    'attr' => static::getAttr($attrs, 'vertical_position', '0%'),
                    'property' => "top",
                    'important' => true
                ]),
                CommonStyle::style([
                    'selector' => $order_class,
                    'attr' => static::getAttr($attrs, 'vertical_position', '0%'),
                    'declarationFunction' => function ( array $args ) {
                        return "position: absolute !important;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => $order_class,
                    'attr'                => static::getAttr($attrs, 'fmi_effect', 'updown'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"];
                        return "animation-name: dipi-{$attrValue}-effect !important;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => $order_class,
                    'attr'                => static::getAttr($attrs, 'fmi_speed', '5000ms'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"];
                        return "animation-duration: {$attrValue} !important;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => $order_class,
                    'attr'                => static::getAttr($attrs, 'fmi_delay', '0ms'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"];
                        return "animation-delay: {$attrValue} !important;";
                    }
                ]),
                $use_icon === "on" ? CommonStyle::style([
                    'selector' => "$order_class .et-pb-icon.dipi-fi-icon",
                    'attr' => static::getAttr($attrs, 'icon_size', '32px'),
                    'property' => "font-size",
                ]) : null,
                $use_icon === "on" ? CommonStyle::style([
                    'selector' => "$order_class .et-pb-icon.dipi-fi-icon, $order_class .et-pb-icon.dipi-fi-icon:hover",
                    'attr' => static::getAttr($attrs, 'icon_color', ''),
                    'property' => "color",
                    'important' => true
                ]) : null,
                $is_img_svg ? CommonStyle::style([
                    'selector' => $order_class,
                    'attr' => static::getAttr($attrs, 'image', ''),
                    'declarationFunction' => function ( array $args ) {
                        return "width: 100%;";
                    }
                ]) : null,
                CommonStyle::style([
                    'selector'            => "$order_class .dipi-fi-icon",
                    'attr'                => static::getAttr($attrs, 'icon', ''),
                    'declarationFunction' => [ static::class, 'icon_font_declaration' ],
                ]),
            ],
		]);
    }
}
