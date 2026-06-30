<?php
namespace DIPI\Modules\AdvancedTabs;

if (!defined("ABSPATH")) {
    die("Direct access forbidden.");
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Border\BorderStyle;
use ET\Builder\Packages\Module\Options\Spacing\SpacingStyle;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Framework\Breakpoint\Breakpoint;

use DIPI\Traits\BaseRenderTrait;

trait ModuleStylesTrait
{
    use CustomCssTrait;
    use StyleDeclarationTrait;
    use BaseRenderTrait;

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
        // Output base + TB variants so the same CSS works on normal pages and in Theme Builder (body/header/footer) templates.
        $order_class_base = preg_replace('/_tb_(body|header|footer)$/', '', $args['orderClass'] ?? '');
        $tb_suffixes = ['_tb_body', '_tb_header', '_tb_footer'];
        $sel = function ($suffix) use ($order_class_base, $tb_suffixes) {
            $selectors = [$order_class_base . $suffix];
            foreach ($tb_suffixes as $tb) {
                $selectors[] = $order_class_base . $tb . $suffix;
            }
            return implode(', ', $selectors);
        };

        $use_tabs_fullwidth = static::getPropValue($attrs, 'use_tabs_fullwidth') ?? 'on';
        $tabs_placement = static::getPropValue($attrs, 'tabs_placement') ?? 'column';
        $use_active_arrow = static::getPropValue($attrs, 'use_active_arrow') ?? 'off';
        // Check if tabs slider is enabled for any viewport
        $enable_ts_desktop = $attrs['enable_ts']['innerContent']['desktop']['value'] ?? 'off';
        $enable_ts_tablet = $attrs['enable_ts']['innerContent']['tablet']['value'] ?? $enable_ts_desktop;
        $enable_ts_phone = $attrs['enable_ts']['innerContent']['phone']['value'] ?? $enable_ts_tablet;
        $enable_tabs_slider = ($enable_ts_desktop === 'on' || $enable_ts_tablet === 'on' || $enable_ts_phone === 'on') ? 'on' : 'off';
        $navigation_prev_icon_yn = static::getPropValue($attrs, 'navigation_prev_icon_yn') ?? 'on';
        $navigation_next_icon_yn = static::getPropValue($attrs, 'navigation_next_icon_yn') ?? 'on';
        $navigation_circle = static::getPropValue($attrs, 'navigation_circle') ?? 'off';

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
                    CssStyle::style([
                        'selector' => $sel(''),
                        'attr' => $attrs['css'] ?? [],
                        'cssFields' => static::custom_css(),
                    ]),
                    $elements->style([
                        'attrName' => 'tabs_wrapper',
                    ]),
                    $elements->style([
                        'attrName' => 'content_wrapper',
                    ]),
                    $elements->style([
                        'attrName' => 'tabs_item',
                    ]),
                    $elements->style([
                        'attrName' => 'tabs_item_active',
                    ]),
                    $elements->style([
                        'attrName' => 'tabs_icon',
                    ]),
                    $elements->style([
                        'attrName' => 'tabs_text_spacing',
                    ]),
                    $elements->style([
                        'attrName' => 'tabs_text_spacing_active',
                    ]),
                    $elements->style([
                        'attrName' => 'tabs_icon_active',
                    ]),
                    $elements->style([
                        'attrName' => 'header',
                    ]),
                    $elements->style([
                        'attrName' => 'header_2',
                    ]),
                    $elements->style([
                        'attrName' => 'header_3',
                    ]),
                    $elements->style([
                        'attrName' => 'header_4',
                    ]),
                    $elements->style([
                        'attrName' => 'header_5',
                    ]),
                    $elements->style([
                        'attrName' => 'header_6',
                    ]),
                    $elements->style([
                        'attrName' => 'body_font',
                    ]),
                    $elements->style([
                        'attrName' => 'link_font',
                    ]),
                    $elements->style([
                        'attrName' => 'ul_font',
                    ]),
                    $elements->style([
                        'attrName' => 'quote_font',
                    ]),
                    $elements->style([
                        'attrName' => 'title',
                    ]),
                    $elements->style([
                        'attrName' => 'title_active',
                    ]),
                    $elements->style([
                        'attrName' => 'subtitle',
                    ]),
                    $elements->style([
                        'attrName' => 'subtitle_active',
                    ]),
                    $elements->style([
                        'attrName' => 'button',
                    ]),
                    CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tabs'),
                        'attr' => $attrs['use_tabs_fullwidth']['innerContent'],
                        'declarationFunction' => function (array $args) {
                            if ($args['attrValue'] === 'on') {
                                return "flex-wrap: nowrap;";
                            } else {
                                return "flex-wrap: wrap;";
                            }
                        }
                    ]),
                    CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tab'),
                        'attr' => $attrs['use_tabs_fullwidth']['innerContent'],
                        'declarationFunction' => function (array $args) {
                            if ($args['attrValue'] === 'on') {
                                return "width: 100%";
                            } else {
                                return "";
                            }
                        }
                    ]),
                    CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tabs'),
                        'attr' => $attrs['tabs_placement']['innerContent'],
                        'declarationFunction' => function (array $args) {
                            if ($args['attrValue'] === 'row' || $args['attrValue'] === 'row-reverse') {
                                return "flex-direction: column;";
                            } else {
                                return "flex-direction: row;";
                            }
                        }
                    ]),
                    CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tabs'),
                        'attr' => $attrs['tabs_align']['innerContent'],
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args['attrValue'];
                            return "align-self: {$attrValue}; justify-content: {$attrValue};";
                        }
                    ]),
                    CommonStyle::style([
                        'selector' => $sel(' .dipi-at-container'),
                        'attr' => $attrs['tabs_placement']['innerContent'],
                        'declarationFunction' => function (array $args) {
                            return "flex-direction: {$args['attrValue']};";
                        }
                    ]),
                    CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tab'),
                        'attr' => $attrs['tabs_min_width']['innerContent'],
                        'declarationFunction' => function (array $args) use ($attrs) {
                            $state = $args['state'] ?? 'value';
                            $breakpoint = $args['breakpoint'] ?? 'desktop';

                            $use_tabs_fullwidth = ModuleUtils::get_attr_value(
                                [
                                    'attr' => $attrs['use_tabs_fullwidth']['innerContent'],
                                    'breakpoint' => $breakpoint,
                                    'state' => $state,
                                    'mode' => 'getAndInheritAll',
                                    'defaultValue' => [],
                                    'breakpointNames' => Breakpoint::get_all_breakpoint_names(),
                                    'baseBreakpoint' => 'desktop',
                                ]
                            );

                            if ($use_tabs_fullwidth === 'off') {
                                return "min-width: {$args['attrValue']};";
                            } else {
                                return "min-width: unset;";
                            }
                        }
                    ]),
                    CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tab'),
                        'attr' => $attrs['tabs_max_width']['innerContent'],
                        'declarationFunction' => function (array $args) use ($attrs) {
                            $state = $args['state'] ?? 'value';
                            $breakpoint = $args['breakpoint'] ?? 'desktop';

                            $use_tabs_fullwidth = ModuleUtils::get_attr_value(
                                [
                                    'attr' => $attrs['use_tabs_fullwidth']['innerContent'],
                                    'breakpoint' => $breakpoint,
                                    'state' => $state,
                                    'mode' => 'getAndInheritAll',
                                    'defaultValue' => [],
                                    'breakpointNames' => Breakpoint::get_all_breakpoint_names(),
                                    'baseBreakpoint' => 'desktop',
                                ]
                            );

                            if ($use_tabs_fullwidth === 'off') {
                                return "max-width: {$args['attrValue']};";
                            } else {
                                return "max-width: unset;";
                            }
                        }
                    ]),
                    CommonStyle::style([
                        'selector' => $sel(' .dipi-at-panels'),
                        'attr' => $attrs['tabs_container_width']['innerContent'],
                        'declarationFunction' => function (array $args) use ($attrs) {
                            $state = $args['state'] ?? 'value';
                            $breakpoint = $args['breakpoint'] ?? 'desktop';

                            $tabs_placement = ModuleUtils::get_attr_value(
                                [
                                    'attr' => $attrs['tabs_placement']['innerContent'],
                                    'breakpoint' => $breakpoint,
                                    'state' => $state,
                                    'mode' => 'getAndInheritAll',
                                    'defaultValue' => [],
                                    'breakpointNames' => Breakpoint::get_all_breakpoint_names(),
                                    'baseBreakpoint' => 'desktop',
                                ]
                            );

                            if ($tabs_placement === 'row' || $tabs_placement === 'row-reverse') {
                                return "width: calc(100% - {$args['attrValue']});";
                            } else {
                                return "width: auto;";
                            }
                        }
                    ]),
                    CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tabs'),
                        'attr' => $attrs['tabs_container_width']['innerContent'],
                        'declarationFunction' => function (array $args) use ($attrs) {
                            $state = $args['state'] ?? 'value';
                            $breakpoint = $args['breakpoint'] ?? 'desktop';

                            $tabs_placement = ModuleUtils::get_attr_value(
                                [
                                    'attr' => $attrs['tabs_placement']['innerContent'],
                                    'breakpoint' => $breakpoint,
                                    'state' => $state,
                                    'mode' => 'getAndInheritAll',
                                    'defaultValue' => [],
                                    'breakpointNames' => Breakpoint::get_all_breakpoint_names(),
                                    'baseBreakpoint' => 'desktop',
                                ]
                            );

                            //FIXME: should tabs_container_width also be applied when tabs_placement is column? Maybe hide the tabs_container_width setting when tabs_placement is column?
                            if ($tabs_placement === 'row' || $tabs_placement === 'row-reverse') {
                                return "width: {$args['attrValue']};";
                            } else {
                                return "width: auto;";
                            }
                        }
                    ]),










                    CommonStyle::style([
                        'selector' => $sel(' .dipi-at-panel-content[data-imgplacement-desktop*="row"]'),
                        'attr' => static::getAttr($attrs, 'content_vertical_align', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args['attrValue'];
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "desktop" ? "align-items: {$attrValue};" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector' => $sel(' .dipi-at-panel-content[data-imgplacement-desktop*="col"]'),
                        'attr' => static::getAttr($attrs, 'content_vertical_align', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args['attrValue'];
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "desktop" ? "justify-content: {$attrValue};" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector' => $sel(' .dipi-at-panel-content[data-imgplacement-tablet*="row"]'),
                        'attr' => static::getAttr($attrs, 'content_vertical_align', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args['attrValue'];
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "tablet" ? "align-items: {$attrValue};" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector' => $sel(' .dipi-at-panel-content[data-imgplacement-tablet*="col"]'),
                        'attr' => static::getAttr($attrs, 'content_vertical_align', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args['attrValue'];
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "tablet" ? "justify-content: {$attrValue};" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector' => $sel(' .dipi-at-panel-content[data-imgplacement-phone*="row"]'),
                        'attr' => static::getAttr($attrs, 'content_vertical_align', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args['attrValue'];
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "phone" ? "align-items: {$attrValue};" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector' => $sel(' .dipi-at-panel-content[data-imgplacement-phone*="col"]'),
                        'attr' => static::getAttr($attrs, 'content_vertical_align', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args['attrValue'];
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "phone" ? "justify-content: {$attrValue};" : "";
                        }
                    ]),
                    $use_active_arrow === "on" ? CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tabs .dipi-at-tab svg'),
                        'attr' => static::getAttr($attrs, 'active_arrow_color', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args['attrValue'];
                            return "fill: {$attrValue};";
                        }
                    ]) : null,
                    $use_active_arrow === "on" ? CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tabs.has-arrow') . ', ' . $sel(' .dipi-at-tabs.has-arrow .dipi-at-tab.dipi-at-tab--active'),
                        'attr' => static::getAttr($attrs, 'active_arrow_color', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args['attrValue'];
                            return "overflow: visible;";
                        }
                    ]) : null,
                    $tabs_placement === "row" || $tabs_placement === "row-reverse" ? CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tabs .dipi-at-tab svg'),
                        'attr' => static::getAttr($attrs, 'active_arrow_size', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args['attrValue'];
                            return "height: {$attrValue} !important; width: auto;";
                        }
                    ]) : null,
                    $tabs_placement !== "row" && $tabs_placement !== "row-reverse" ? CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tabs .dipi-at-tab svg'),
                        'attr' => static::getAttr($attrs, 'active_arrow_size', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args['attrValue'];
                            return "width: {$attrValue}; height: auto;";
                        }
                    ]) : null,
                    $enable_tabs_slider === "on" ? CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tabs-prev:not(.sticky)') . ', ' . $sel(' .dipi-at-tabs-next:not(.sticky)'),
                        'attr' => static::getAttr($attrs, 'ts_navigation_vertical_position', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args['attrValue'];
                            return "top: {$attrValue}px !important;";
                        }
                    ]) : null,
                    $enable_tabs_slider === "on" ? CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tabs-prev:not(.sticky)'),
                        'attr' => static::getAttr($attrs, 'ts_navigation_horizontal_position', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args['attrValue'];
                            return "left: {$attrValue}px !important;";
                        }
                    ]) : null,
                    $enable_tabs_slider === "on" ? CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tabs-next'),
                        'attr' => static::getAttr($attrs, 'ts_navigation_horizontal_position', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args['attrValue'];
                            return "right: {$attrValue}px !important;";
                        }
                    ]) : null,
                    $enable_tabs_slider === "on" && $navigation_prev_icon_yn === "on" ? CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tabs-prev:after'),
                        'attr' => static::getAttr($attrs, 'navigation_prev_icon', ''),
                        'declarationFunction' => [static::class, 'icon_font_declaration'],
                    ]) : null,
                    $enable_tabs_slider === "on" && $navigation_next_icon_yn === "on" ? CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tabs-next:after'),
                        'attr' => static::getAttr($attrs, 'navigation_next_icon', ''),
                        'declarationFunction' => [static::class, 'icon_font_declaration'],
                    ]) : null,
                    $enable_tabs_slider === "on" ? CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tabs-prev') . ', ' . $sel(' .dipi-at-tabs-next'),
                        'attr' => static::getAttr($attrs, 'navigation_size', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args['attrValue'];
                            return "font-size: {$attrValue} !important;";
                        }
                    ]) : null,
                    $enable_tabs_slider === "on" ? CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tabs-prev') . ', ' . $sel(' .dipi-at-tabs-next'),
                        'attr' => static::getAttr($attrs, 'navigation_padding', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args['attrValue'];
                            return "padding: {$attrValue} !important;";
                        }
                    ]) : null,
                    $enable_tabs_slider === "on" ? CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tabs-prev') . ', ' . $sel(' .dipi-at-tabs-next'),
                        'attr' => static::getAttr($attrs, 'navigation_color', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args['attrValue'];
                            return "color: {$attrValue} !important;";
                        }
                    ]) : null,
                    $enable_tabs_slider === "on" ? CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tabs-prev') . ', ' . $sel(' .dipi-at-tabs-next'),
                        'attr' => static::getAttr($attrs, 'navigation_bg_color', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args['attrValue'];
                            return "background-color: {$attrValue} !important;";
                        }
                    ]) : null,
                    $enable_tabs_slider === "on" && $navigation_circle === "on" ? CommonStyle::style([
                        'selector' => $sel(' .dipi-at-tabs-prev') . ', ' . $sel(' .dipi-at-tabs-next'),
                        'attr' => static::getAttr($attrs, 'navigation_circle', ''),
                        'declarationFunction' => function (array $args) {
                            $attrValue = $args['attrValue'];
                            return "border-radius: 50% !important;";
                        }
                    ]) : null,
                ],
            ]
        );
    }
}
