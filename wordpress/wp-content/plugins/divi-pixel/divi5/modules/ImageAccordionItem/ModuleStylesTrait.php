<?php
namespace DIPI\Modules\ImageAccordionItem;

if (!defined("ABSPATH")) {
    die("Direct access forbidden.");
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Background\BackgroundStyle;

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

        $use_accordion_icon = static::getAttrByMode($attrs, "use_accordion_icon", "off");
        $use_accordion_icon_circle = static::getAttrByMode($attrs, "use_accordion_icon_circle", "off");
        $use_accordion_icon_circle_border = static::getAttrByMode($attrs, "use_accordion_icon_circle_border", "off");
        $use_accordion_icon_font_size = static::getAttrByMode($attrs, "use_accordion_icon_font_size", "off");
        $image_bg_hover = $attrs['image_bg']['decoration']['background']['desktop']['hover'] ?? '';

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
                    'attrName'   => 'accordion_image',
                ]),
                $elements->style([
                    'attrName'   => 'accordion_title',
                ]),
                $elements->style([
                    'attrName'   => 'accordion_description',
                ]),
                $elements->style([
                    'attrName'   => 'accordion_button',
                ]),
                $elements->style([
                    'attrName'   => 'image_bg',
                ]),
                CommonStyle::style([
                    'selector'            => "$order_class .dipi-accordion-icon",
                    'attr'                => static::getAttr($attrs, 'accordion_icon'),
                    'declarationFunction' => [ static::class, 'icon_font_declaration' ],
                ]),
                CommonStyle::style([
                    'selector'            => "$order_class .dipi-bg-img-wrapper img",
                    'attr'                => static::getAttr($attrs, 'bg_img'),
                    'declarationFunction' => function ( array $args ) {
                        return "width: 100% !important; height: 100% !important; object-fit: cover !important; display: block !important;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => "$order_class .dipi-accordion-image",
                    'attr'                => static::getAttr($attrs, 'accordion_image_width'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"];
                        return  "width: {$attrValue} !important;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => "$order_class .dipi-accordion-content",
                    'attr'                => static::getAttr($attrs, 'content_width'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"];
                        return  "max-width: {$attrValue} !important;";
                    }
                ]),
                $use_accordion_icon === "on" ? CommonStyle::style([
                    'selector'            => "$order_class .dipi-accordion-icon",
                    'attr'                => static::getAttr($attrs, 'accordion_icon_color'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"];
                        return  "color: {$attrValue} !important;";
                    }
                ]) : null,
                $use_accordion_icon === "on" && $use_accordion_icon_circle === "on" ? CommonStyle::style([
                    'selector'            => "$order_class .dipi-accordion-icon",
                    'attr'                => static::getAttr($attrs, 'accordion_icon_circle_color'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"];
                        return  "padding: 25px; border-radius: 100%; background-color: {$attrValue} !important;";
                    }
                ]) : null,
                $use_accordion_icon === "on" && $use_accordion_icon_circle === "on" && $use_accordion_icon_circle_border === "on" ? CommonStyle::style([
                    'selector'            => "$order_class .dipi-accordion-icon",
                    'attr'                => static::getAttr($attrs, 'accordion_icon_circle_border_color'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"];
                        return  "border: 3px solid {$attrValue};";
                    }
                ]) : null,
                $use_accordion_icon === "on" && $use_accordion_icon_font_size === "on" ? CommonStyle::style([
                    'selector'            => "$order_class .dipi-accordion-icon",
                    'attr'                => static::getAttr($attrs, 'accordion_icon_font_size'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args["attrValue"];
                        return  "font-size: {$attrValue} !important;";
                    }
                ]) : null,
                BackgroundStyle::style([
                    'selector'            => "$order_class .dipi_image_accordion_bg_hover",
                    'attr'                => [
                        'desktop' => [
                            'value' => $image_bg_hover,
                        ],
                    ],
                ]),
            ],
		]);
    }
}
