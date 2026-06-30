<?php
namespace DIPI\Modules\FancyText;

if (!defined("ABSPATH")) {
    die("Direct access forbidden.");
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Border\BorderStyle;
use ET\Builder\Packages\Module\Options\Spacing\SpacingStyle;
use DIPI\Traits\BaseRenderTrait;

trait ModuleStylesTrait
{
    use CustomCssTrait;
    use StyleDeclarationTrait;
	use BaseRenderTrait;

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

        $sp_direction = static::getPropValue($attrs, 'sp_direction');

        $style = [
            // Module.
            $elements->style(
                [
                    'attrName'   => 'module',
                    'styleProps' => [
                        'disabledOn' => [
                            'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                        ],
                    ],
                ]
            ),
            CssStyle::style(
                [
                    'selector'  => $args['orderClass'],
                    'attr'      => $attrs['css'] ?? [],
                    'cssFields' => static::custom_css(),
                ]
            ),
            $elements->style(
                [
                    'attrName'   => 'all_font',
                ]
            ),
            $elements->style(
                [
                    'attrName'   => 'prefix_font',
                ]
            ),
            $elements->style(
                [
                    'attrName'   => 'text_font',
                ]
            ),
            $elements->style(
                [
                    'attrName'   => 'suffix_font',
                ]
            ),
            CommonStyle::style([
                'selector' => $order_class . ' .fancy-text-wrap .animated',
                'attr' => static::getAttr($attrs, 'duration', ''),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "animation-duration: {$attrValue}ms !important;";
                }
            ]),
            SpacingStyle::style( [
                'selector' => $order_class . " .fancy-text-wrap",
                'attr' => static::getAttr($attrs, 'text_spacing', ''),
            ]),
        ];

        if($sp_direction === "horizontally"){
            $style[] = CommonStyle::style([
                'selector' => $order_class . ' .fancy-text-suffix, ' . $order_class . ' .fancy-text-wrap , ' . $order_class .  ' .fancy-text-prefix',
                'attr' => static::getAttr($attrs, 'display', ''),
                'declarationFunction' => function ( array $args ) {
                    return  "display: inline-block !important;";
                }
            ]);
        } else {
            $style[] = CommonStyle::style([
                'selector' => $order_class . ' .fancy-text-suffix, ' . $order_class . ' .fancy-text-wrap , ' . $order_class .  ' .fancy-text-prefix',
                'attr' => static::getAttr($attrs, 'display', ''),
                'declarationFunction' => function ( array $args ) {
                    return  "display: block !important;";
                }
            ]);
        }
        

        Style::add(
			[
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => $style
			]
		);
    }
}
