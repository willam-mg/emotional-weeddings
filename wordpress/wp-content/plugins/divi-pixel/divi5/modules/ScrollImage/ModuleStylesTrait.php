<?php
namespace DIPI\Modules\ScrollImage;

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

        $use_overlay = $attrs['use_overlay']['innerContent']['desktop']['value'] ?? 'off';
        $use_icon_circle = $attrs['use_icon_circle']['innerContent']['desktop']['value'] ?? 'off';
        $use_icon_circle_border = $attrs['use_icon_circle_border']['innerContent']['desktop']['value'] ?? 'off';
        $icon_color = $attrs['icon_color']['innerContent']['desktop']['value'] ?? '';
        $icon_color_hover = $attrs['icon_color']['innerContent']['desktop']['hover'] ?? '';
        $icon_circle_color = $attrs['icon_circle_color']['innerContent']['desktop']['value'] ?? '';
        $icon_circle_color_hover = $attrs['icon_circle_color']['innerContent']['desktop']['hover'] ?? '';
        
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
                        'attrName'   => 'direction_img',
                    ]),
                    $use_overlay === 'on' ? $elements->style([
                        'attrName'   => 'overlay_bg',
                    ]) : null,
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi-image-scroll-icon",
                        'attr'                => static::getAttr($attrs, 'direction_icon'),
                        'declarationFunction' => [ static::class, 'icon_font_declaration' ],
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi-image-scroll-icon",
                        'attr'                => static::getAttr($attrs, 'icon_color'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "color: $attrValue !important;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class:hover .dipi-image-scroll-icon",
                        'attr'                => static::getAttr($attrs, 'icon_color'),
                        'declarationFunction' => function ( array $args ) use ($icon_color_hover) {
                            return  "color: $icon_color_hover !important;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi-image-scroll-icon",
                        'attr'                => static::getAttr($attrs, 'icon_size'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "font-size: $attrValue !important;";
                        }
                    ]),
                    $use_icon_circle === 'on' ? CommonStyle::style([
                        'selector'            => "$order_class .dipi-image-scroll-icon",
                        'attr'                => static::getAttr($attrs, 'icon_circle_color'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "padding: 25px; border-radius: 100%; background-color: $attrValue !important;";
                        }
                    ]) : null,
                    $use_icon_circle === 'on' ? CommonStyle::style([
                        'selector'            => "$order_class:hover .dipi-image-scroll-icon",
                        'attr'                => static::getAttr($attrs, 'icon_circle_color'),
                        'declarationFunction' => function ( array $args ) use ($icon_circle_color_hover) {
                            return  "background-color: $icon_circle_color_hover !important;";
                        }
                    ]) : null,
                    $use_icon_circle === 'on' && $use_icon_circle_border === 'on' ? CommonStyle::style([
                        'selector'            => "$order_class .dipi-image-scroll-icon",
                        'attr'                => static::getAttr($attrs, 'icon_border_color'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "border: 3px solid $attrValue;";
                        }
                    ]) : null,
                    $use_icon_circle === 'on' && $use_icon_circle_border === 'on' ? CommonStyle::style([
                        'selector'            => "$order_class:hover .dipi-image-scroll-icon",
                        'attr'                => static::getAttr($attrs, 'icon_border_color'),
                        'declarationFunction' => function ( array $args ) use ($icon_border_color_hover) {
                            return  "border-color: $icon_border_color_hover !important;";
                        }
                    ]) : null,
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi-image-scroll-content > img",
                        'attr'                => static::getAttr($attrs, 'direction_image_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "width: $attrValue !important;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi-image-scroll-container",
                        'attr'                => static::getAttr($attrs, 'scroll_image_height'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "height: $attrValue !important;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class:hover .dipi-image-scroll-image img",
                        'attr'                => static::getAttr($attrs, 'scroll_speed'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  $attrValue !== "" ? "transition: all {$attrValue}s !important;" : "";                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi-image-scroll-image img",
                        'attr'                => static::getAttr($attrs, 'back_scroll_speed'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  $attrValue !== "" ? "transition: all {$attrValue}s !important;" : "";
                        }
                    ]),
				],
			]
		);
    }
}
