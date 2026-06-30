<?php
namespace DIPI\Modules\TableOfContent;

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

    public static function getAttrByMode($attrs, $attr, $default = null, $mode = null)
    {
        return (((($attrs ?? [])[$attr] ?? [])['innerContent'] ?? [])[$mode ?? 'desktop'] ?? [])['value'] ?? $default ?? '';
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
        $attrs = $args['attrs'] ?? [];
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class = $args['orderClass'] ?? '';
        $order_number = preg_replace('/[^0-9]/', '', $order_class);


        Style::add(
            [
                'id' => $args['id'],
                'name' => $args['name'],
                'orderIndex' => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles' => [
                    // Module.
                    $elements->style([
                        'attrName' => 'module',
                        'styleProps' => [
                            'disabledOn' => [
                                'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                            ],
                        ],
                    ]),
                    CssStyle::style(
                        [
                            'selector' => $args['orderClass'],
                            'attr' => $attrs['css'] ?? [],
                            'cssFields' => static::custom_css(),
                        ]
                    ),
                    $elements->style([
                        'attrName' => 'table_title',
                    ]),
                    $elements->style([
                        'attrName' => 'table_title_font',
                    ]),
                    $elements->style([
                        'attrName' => 'header_icon',
                    ]),
                    $elements->style([
                        'attrName' => 'content',
                    ]),
                    $elements->style([
                        'attrName' => 'list_custom_1',
                    ]),
                    $elements->style([
                        'attrName' => 'list_custom_2',
                    ]),
                    $elements->style([
                        'attrName' => 'list_custom_3',
                    ]),
                    $elements->style([
                        'attrName' => 'list_custom_4',
                    ]),
                    $elements->style([
                        'attrName' => 'list_custom_5',
                    ]),
                    $elements->style([
                        'attrName' => 'list_custom_6',
                    ]),
                    $elements->style([
                        'attrName' => 'content_list_font',
                    ]),
                    $elements->style([
                        'attrName' => 'h1_font',
                    ]),
                    $elements->style([
                        'attrName' => 'h2_font',
                    ]),
                    $elements->style([
                        'attrName' => 'h3_font',
                    ]),
                    $elements->style([
                        'attrName' => 'h4_font',
                    ]),
                    $elements->style([
                        'attrName' => 'h5_font',
                    ]),
                    $elements->style([
                        'attrName' => 'h6_font',
                    ]),
                    $elements->style([
                        'attrName' => 'h1',
                    ]),
                    $elements->style([
                        'attrName' => 'h2',
                    ]),
                    $elements->style([
                        'attrName' => 'h3',
                    ]),
                    $elements->style([
                        'attrName' => 'h4',
                    ]),
                    $elements->style([
                        'attrName' => 'h5',
                    ]),
                    $elements->style([
                        'attrName' => 'h6',
                    ]),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-toc__title',
                        'attr' => static::getAttr($attrs, 'show_table_title', 'on'),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args["attrValue"];
                            $display = ($attrValue === 'on') ? 'block' : 'none';
                            return "display: {$display} !important;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-toc__icon',
                        'attr' => static::getAttr($attrs, 'item_bullet'),
                        'declarationFunction' => [static::class, 'icon_font_declaration'],
                        'important' => true
                    ]),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-toc__icon',
                        'attr' => static::getAttr($attrs, 'bullet_size'),
                        'property' => 'font-size',
                        'important' => true
                    ]),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-toc__icon',
                        'attr' => static::getAttr($attrs, 'bullet_spacing'),
                        'property' => 'padding-right',
                        'important' => true
                    ]),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-toc_header-icon',
                        'property' => 'color',
                        'attr' => static::getAttr($attrs, 'header_icon_color', ''),
                        'important' => true
                    ]),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-toc_header-icon',
                        'property' => 'font-size',
                        'attr' => static::getAttr($attrs, 'header_icon_size', '20px')
                    ]),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-toc-header-icon-open',
                        'attr' => static::getAttr($attrs, 'header_icon'),
                        'declarationFunction' => [static::class, 'icon_font_declaration'],
                        'important' => true
                    ]),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-toc-header-icon-closed',
                        'attr' => static::getAttr($attrs, 'header_icon_closed'),
                        'declarationFunction' => [static::class, 'icon_font_declaration'],
                        'important' => true
                    ]),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-toc__icon',
                        'attr' => static::getAttr($attrs, 'item_bullet'),
                        'declarationFunction' => [static::class, 'icon_font_declaration'],
                        'important' => true
                    ]),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-toc__sublist--lvl-1 li',
                        'property' => 'margin-top',
                        'attr' => static::getAttr($attrs, 'list_space_between_1', '5px')
                    ]),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-toc__sublist--lvl-2 li',
                        'property' => 'margin-top',
                        'attr' => static::getAttr($attrs, 'list_space_between_2', '5px')
                    ]),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-toc__sublist--lvl-3 li',
                        'property' => 'margin-top',
                        'attr' => static::getAttr($attrs, 'list_space_between_3', '5px')
                    ]),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-toc__sublist--lvl-4 li',
                        'property' => 'margin-top',
                        'attr' => static::getAttr($attrs, 'list_space_between_4', '5px')
                    ]),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-toc__sublist--lvl-5 li',
                        'property' => 'margin-top',
                        'attr' => static::getAttr($attrs, 'list_space_between_5', '5px')
                    ]),
                    CommonStyle::style([
                        'selector' => $order_class . ' .dipi-toc__sublist--lvl-6 li',
                        'property' => 'margin-top',
                        'attr' => static::getAttr($attrs, 'list_space_between_6', '5px')
                    ]),
                ],
            ]
        );
    }
}
