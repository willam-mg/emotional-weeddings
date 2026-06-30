<?php
namespace DIPI\Modules\Breadcrumbs;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Spacing\SpacingStyle;

trait ModuleStylesTrait
{

    use CustomCssTrait;
    use StyleDeclarationTrait;
    public static function getAttr($attrs, $attr, $default = null, $zoom = '', $unit = '', $wrap_func = '')
    {
        $AttrValue = (($attrs ?? [])[$attr] ?? [])['innerContent'] ?? ['desktop' => ['value' => $default ?? '']];
        return $AttrValue;
    }
    public static function getDipiAttr($attrs, $attr, $default = null, $zoom = '', $unit = '', $wrap_func = '')
    {
        $beforeAttrValue = (($attrs ?? [])[$attr] ?? [])['innerContent'] ?? ['desktop' => ['value' => $default ?? '']];
        $afterAttrValue = $beforeAttrValue;
        if (empty($afterAttrValue['tablet'])) {
            $afterAttrValue['tablet'] = $afterAttrValue['desktop'];
        }
        if (empty($afterAttrValue['phone'])) {
            $afterAttrValue['phone'] = $afterAttrValue['tablet'];
        }
        $slug_value = $afterAttrValue['desktop']['value'] ?? $default;
        $slug_value_tablet = $afterAttrValue['tablet']['value'];
        $slug_value_phone = $afterAttrValue['phone']['value'];
        if ($zoom === '') {
            $slug_value = $slug_value . $unit;
            $slug_value_tablet = $slug_value_tablet . $unit;
            $slug_value_phone = $slug_value_phone . $unit;
        } else {
            $slug_value = ((float) $slug_value * $zoom) . $unit;
            $slug_value_tablet = ((float) $slug_value_tablet * $zoom) . $unit;
            $slug_value_phone = ((float) $slug_value_phone * $zoom) . $unit;
        }
        if ($wrap_func !== '') {
            $slug_value = "$wrap_func($slug_value)";
            $slug_value_tablet = "$wrap_func($slug_value_tablet)";
            $slug_value_phone = "$wrap_func($slug_value_phone)";
        }
        $afterAttrValue['desktop']['value'] = $slug_value;
        if (isset($beforeAttrValue['tablet'])) {
            $afterAttrValue['tablet']['value'] = $slug_value_tablet;
        }
        if (isset($beforeAttrValue['phone'])) {
            $afterAttrValue['phone']['value'] = $slug_value_phone;
        }
        return $afterAttrValue;
    }
    public static function getAttrNumber($attrs, $attr, $default = null, $delta = 0)
    {
        $beforeAttrValue = (($attrs ?? [])[$attr] ?? [])['innerContent'] ?? ['desktop' => ['value' => $default ?? '']];
        $afterAttrValue = $beforeAttrValue;
        $afterAttrValue['desktop']['value'] = (float) $beforeAttrValue['desktop']['value'] + (float) $delta;
        if (isset($beforeAttrValue['tablet'])) {
            $afterAttrValu['tablet']['value'] = (float) $beforeAttrValue['tablet']['value'] + (float) $delta;
        }
        if (isset($beforeAttrValue['phone'])) {
            $afterAttrValue['phone']['value'] = (float) $beforeAttrValue['phone']['value'] + (float) $delta;
        }
        return $afterAttrValue;
    }

    public static function module_styles($args)
    {
        $attrs = $args['attrs'] ?? [];
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class = $args['orderClass'] ?? '';

        $styles = [
            // Module.
            $elements->style(
                [
                    'attrName' => 'module',
                    'styleProps' => [
                        'disabledOn' => [
                            'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                        ],
                    ],
                ]
            ),
            $elements->style([
                'attrName' => 'advanced_items',
            ]),
            $elements->style([
                'attrName' => 'advanced_hover',
            ]),
            $elements->style([
                'attrName' => 'advanced_active',
            ]),
            TextStyle::style(
                [
                    'selector' => "{$args['orderClass']} .example_flip_box__content-container",
                    'attr' => $attrs['module']['advanced']['text'] ?? [],
                ]
            ),
            CssStyle::style(
                [
                    'selector' => $args['orderClass'],
                    'attr' => $attrs['css'] ?? [],
                    'cssFields' => static::custom_css(),
                ]
            ),
            CommonStyle::style([
                'selector' => "$order_class .dipi-separator-icon, $order_class .dipi-separator-symbol",
                'property' => 'font-size',
                'attr' => static::getAttr($attrs, 'bc_separator_size', '')
            ]),

            CommonStyle::style([
                'selector' => "$order_class .dipi-separator-icon, $order_class .dipi-separator-symbol",
                'property' => 'color',
                'attr' => static::getAttr($attrs, 'bc_separator_color', '')
            ]),
            CommonStyle::style([
                'selector' => "$order_class .dipi-separator-icon, $order_class .dipi-separator-symbol",
                'attr' => static::getAttr($attrs, 'bc_separator_space', ''),
                'declarationFunction' => function (array $args) {
                    $attrValue = $args['attrValue'];
                    return "margin-right: $attrValue; margin-left: $attrValue";
                }
            ]),

            CommonStyle::style([
                'selector' => "$order_class .dipi-breadcrumb-home .dipi-home-icon",
                'property' => 'font-size',
                'attr' => static::getAttr($attrs, 'bc_home_size', '')
            ]),

            // Set the home icon content (::before pseudo-element)
            CommonStyle::style([
                'selector' => "$order_class .dipi-breadcrumb-home .dipi-home-icon:before",
                'attr' => ['desktop' => ['value' => '']],
                'declarationFunction' => function (array $args) {
                    return 'content: "\e074";';
                }
            ]),

            CommonStyle::style([
                'selector' => "$order_class .dipi-home-icon, $order_class .dipi-home-icon:before",
                'property' => 'color',
                'attr' => static::getAttr($attrs, 'bc_home_color', ''),
                'important' => true
            ]),

            CommonStyle::style([
                'selector' => "$order_class .dipi-breadcrumb-home:hover .dipi-home-icon, $order_class .dipi-breadcrumb-home:hover .dipi-home-icon:before",
                'property' => 'color',
                'attr' => static::getAttr($attrs, 'bc_hover_home_color', ''),
                'important' => true
            ]),

            CommonStyle::style([
                'selector' => "$order_class .dipi-breadcrumb-item a",
                'property' => 'background-color',
                'attr' => static::getAttr($attrs, 'bc_item_bg_color', '')
            ]),
            CommonStyle::style([
                'selector' => "$order_class .dipi-breadcrumb-item:hover a",
                'property' => 'background-color',
                'attr' => static::getAttr($attrs, 'bc_hover_item_bg_color', ''),
                'important' => true
            ]),

            CommonStyle::style([
                'selector' => "$order_class .dipi-breadcrumb-current",
                'property' => 'background-color',
                'attr' => static::getAttr($attrs, 'bc_active_item_color', ''),
                'important' => true
            ]),

            // Base breadcrumbs list styles - remove bullets and make items inline
            CommonStyle::style([
                'selector' => "$order_class .dipi-breadcrumbs > ul",
                'attr' => static::getAttr($attrs, 'bc_items_alignment', ''),
                'declarationFunction' => function (array $args) {
                    return 'list-style: none !important; display: flex !important; flex-wrap: wrap; align-items: center; margin: 0; padding: 0;';
                }
            ]),
            CommonStyle::style([
                'selector' => "$order_class .dipi-breadcrumbs > ul > li",
                'attr' => static::getAttr($attrs, 'bc_items_alignment', ''),
                'declarationFunction' => function (array $args) {
                    return 'display: inline-block; margin: 0; padding: 0;';
                }
            ]),

            // Make border color inherit from text color when border color is empty (matches Divi 4 behavior)
            CommonStyle::style([
                'selector' => "$order_class .dipi-breadcrumb-item a",
                'attr' => static::getAttr($attrs, 'bc_items_alignment', ''),
                'declarationFunction' => function (array $args) {
                    return 'border-color: currentColor;';
                }
            ]),
            CommonStyle::style([
                'selector' => "$order_class .dipi-breadcrumb-item:hover a",
                'attr' => static::getAttr($attrs, 'bc_items_alignment', ''),
                'declarationFunction' => function (array $args) {
                    return 'border-color: currentColor;';
                }
            ]),
            CommonStyle::style([
                'selector' => "$order_class .dipi-breadcrumb-current",
                'attr' => static::getAttr($attrs, 'bc_items_alignment', ''),
                'declarationFunction' => function (array $args) {
                    return 'border-color: currentColor;';
                }
            ])
        ];

        $attr_bc_item_padding = static::getAttr($attrs, 'bc_item_padding', '')['desktop']['value'] ?? '';
        if (!empty($attr_bc_item_padding)) {
            $styles[] = SpacingStyle::style([
                'selector' => "$order_class .dipi-breadcrumb-item:not(.dipi-breadcrumb-current) a",
                'attr' => static::getAttr($attrs, 'bc_item_padding', ''),
            ]);
        }
        
        $attr_bc_active_item_padding = static::getAttr($attrs, 'bc_active_item_padding', '')['desktop']['value'] ?? '';
        if (!empty($attr_bc_active_item_padding)) {
            $styles[] = SpacingStyle::style([
                'selector' => "$order_class .dipi-breadcrumbs > ul > li.dipi-breadcrumb-current",
                'attr' => static::getAttr($attrs, 'bc_active_item_padding', ''),
                'important' => true
            ]);
        }

        $attr_bc_separator_icon = static::getAttr($attrs, 'bc_separator_icon', '');
        $has_separator_icon = false;
        if (!empty($attr_bc_separator_icon)) {
            foreach (['desktop', 'tablet', 'phone'] as $breakpoint) {
                $icon_value = $attr_bc_separator_icon[$breakpoint]['value'] ?? null;
                if (!empty($icon_value) && is_array($icon_value)) {
                    $has_separator_icon = true;
                    break;
                }
            }
        }
        if ($has_separator_icon) {
            $styles[] = CommonStyle::style([
                'selector' => "$order_class .et-pb-icon.dipi-separator-icon",
                'attr' => ['desktop' => ['value' => '']],
                'declarationFunction' => function (array $args) {
                    return 'font-size: 0 !important; line-height: 0 !important;';
                }
            ]);
            $styles[] = CommonStyle::style([
                'selector' => "$order_class .et-pb-icon.dipi-separator-icon:before",
                'attr' => $attr_bc_separator_icon,
                'declarationFunction' => [ static::class, 'icon_font_declaration' ],
            ]);
            $styles[] = CommonStyle::style([
                'selector' => "$order_class .et-pb-icon.dipi-separator-icon:before",
                'property' => 'font-size',
                'attr' => static::getAttr($attrs, 'bc_separator_size', '')
            ]);
            $styles[] = CommonStyle::style([
                'selector' => "$order_class .et-pb-icon.dipi-separator-icon:before",
                'property' => 'color',
                'attr' => static::getAttr($attrs, 'bc_separator_color', '')
            ]);
            $styles[] = CommonStyle::style([
                'selector' => "$order_class .et-pb-icon.dipi-separator-icon:before",
                'attr' => ['desktop' => ['value' => '']],
                'declarationFunction' => function (array $args) {
                    return 'display: inline-block; line-height: 1;';
                }
            ]);
        }

        // Add alignment style for breadcrumbs items
        $attr_bc_items_alignment = static::getAttr($attrs, 'bc_items_alignment', '')['desktop']['value'] ?? '';
        if (!empty($attr_bc_items_alignment)) {
            $styles[] = CommonStyle::style([
                'selector' => "$order_class .dipi-breadcrumbs > ul",
                'attr' => static::getAttr($attrs, 'bc_items_alignment', ''),
                'declarationFunction' => function (array $args) {
                    $attrValue = $args['attrValue'] ?? '';
                    $flex_align = 'flex-start';
                    switch ($attrValue) {
                        case 'dipi-bc-left':
                            $flex_align = 'flex-start';
                            break;
                        case 'dipi-bc-center':
                            $flex_align = 'center';
                            break;
                        case 'dipi-bc-right':
                            $flex_align = 'flex-end';
                            break;
                        default:
                            $flex_align = 'flex-start';
                            break;
                    }
                    return "justify-content: $flex_align !important;";
                }
            ]);
        }

        Style::add(
            [
                'id' => $args['id'],
                'name' => $args['name'],
                'orderIndex' => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles' => $styles
            ]
        );
    }
}