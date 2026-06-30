<?php
namespace DIPI\Modules\ImageMagnifier;

if (!defined("ABSPATH")) {
    die("Direct access forbidden.");
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;

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

        $lens_border_size = static::getAttrByMode($attrs, "lens_border_size", "7");
        $lens_border_color = static::getAttrByMode($attrs, "lens_border_color", "#fff");
        $use_inset_shadow = static::getAttrByMode($attrs, "use_inset_shadow", "off");
        $inset_shadow_size = static::getAttrByMode($attrs, "inset_shadow_size", "40");
        $inset_shadow_color = static::getAttrByMode($attrs, "inset_shadow_color", "#fff");

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
                        'attrName'   => 'main_image',
					]),
                    $use_inset_shadow !== "on" ? 
                    CommonStyle::style([
						'selector' => "$order_class .magnify .magnify-lens",
						'attr' => ["desktop" => ["value" => ""]],
						'declarationFunction' => function ( array $args ) use ($lens_border_size, $lens_border_color) {
                            $attrValue = $args["attrValue"];
                            return "box-shadow: 0 0 0 {$lens_border_size}px {$lens_border_color}, 0 0 {$lens_border_size}px {$lens_border_size}px rgba(0, 0, 0, 0.25), inset 0 0 0px 0px transparent;";
                        }
					]) : 
                    CommonStyle::style([
						'selector' => "$order_class .magnify .magnify-lens",
						'attr' => ["desktop" => ["value" => ""]],
						'declarationFunction' => function ( array $args ) use ($lens_border_size, $lens_border_color, $inset_shadow_size, $inset_shadow_color) {
                            $attrValue = $args["attrValue"];
                            return "box-shadow: 0 0 0 {$lens_border_size}px {$lens_border_color}, 0 0 {$lens_border_size}px {$lens_border_size}px rgba(0, 0, 0, 0.25), inset 0 0 {$inset_shadow_size}px 2px {$inset_shadow_color};";
                        }
					]),
                    CommonStyle::style([
						'selector' => "$order_class .magnify > .magnify-lens",
						'attr' => static::getAttr($attrs, 'lens_size', '200'),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "width: {$attrValue}px !important; height: {$attrValue}px !important;";
                        }
					]),
                    CommonStyle::style([
						'selector' => "$order_class .magnify .magnify-lens",
						'attr' => static::getAttr($attrs, 'touch_lens_h_offset', '0px'),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "margin-left: $attrValue;";
                        }
					]),
                    CommonStyle::style([
						'selector' => "$order_class .magnify .magnify-lens",
						'attr' => static::getAttr($attrs, 'touch_lens_v_offset', '0px'),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "margin-top: $attrValue;";
                        }
					]),
				],
			]
		);
    }
}
