<?php
namespace DIPI\Modules\ReadingProgressBar;

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

        $bar_animation = static::getAttrByMode($attrs, 'bar_animation', 'no');

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
                    CommonStyle::style([
						'selector' => "$order_class .dipi-reading-progress, $order_class .dipi-reading-progress-fill",
						'attr' => static::getAttr($attrs, 'bar_size'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "height: {$attrValue}!important;";
                        }
					]),
                    $bar_animation === 'striped' ? CommonStyle::style([
                        'selector' => "$order_class .dipi-striped-color",
                        'attr' => static::getAttr($attrs, 'bar_striped_color'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "background-image: linear-gradient(135deg, {$attrValue} 25%, transparent 25%, transparent 50%, {$attrValue} 50%, {$attrValue} 75%, transparent 75%, transparent);";
                        }
                    ]) : null,
				],
			]
		);
    }
}
