<?php
namespace DIPI\Modules\AdvancedTabsItem;

if (!defined("ABSPATH")) {
    die("Direct access forbidden.");
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Framework\Breakpoint\Breakpoint;

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
        $sel_prefix = function ($prefix, $suffix) use ($order_class_base, $tb_suffixes) {
            $selectors = [$prefix . $order_class_base . $suffix];
            foreach ($tb_suffixes as $tb) {
                $selectors[] = $prefix . $order_class_base . $tb . $suffix;
            }
            return implode(', ', $selectors);
        };

        $use_active_tab_icon = static::getPropValue($attrs, 'use_active_tab_icon') ?? "off";
        $button_alignment = $attrs['button']['decoration']['button']['desktop']['value']['alignment'] ?? "left";
        $use_button = static::getPropValue($attrs, 'use_button') ?? "off";
        $use_library_content = static::getPropValue($attrs, 'use_library_content') ?? "off";
        $img_placement = static::getPropValue($attrs, 'img_placement') ?? "column";
        $img_placement_tablet = $attrs['img_placement']['innerContent']['tablet']['value'] ?? $img_placement;
        $img_placement_phone = $attrs['img_placement']['innerContent']['phone']['value'] ?? $img_placement_tablet;
        $image_margin = $attrs['image']['decoration']['spacing']['desktop']['value']['margin'] ?? "0px";
        $tab_media = static::getPropValue($attrs, 'tab_media') ?? "icon";

        Style::add([
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
                    'attrName' => 'title',
                ]),
                $elements->style([
                    'attrName' => 'subtitle',
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
                    'attrName' => 'text_font',
                ]),
                $elements->style([
                    'attrName' => 'link_font',
                ]),
                $elements->style([
                    'attrName' => 'ul_font',
                ]),
                $elements->style([
                    'attrName' => 'ol_font',
                ]),
                $elements->style([
                    'attrName' => 'quote_font',
                ]),
                $elements->style([
                    'attrName' => 'image',
                ]),
                $elements->style([
                    'attrName' => 'tab_image',
                ]),
                $elements->style([
                    'attrName' => 'tab_image_active',
                ]),
                $elements->style([
                    'attrName' => 'button',
                ]),
                $elements->style([
                    'attrName' => 'image_filters',
                ]),
                $tab_media === "icon" ? $elements->style([
                    'attrName' => 'tab_normal_icon_spacing',
                ]) : null,
                $tab_media === "icon" ? $elements->style([
                    'attrName' => 'tab_active_icon_spacing',
                ]) : null,
                $tab_media === "image" ? $elements->style([
                    'attrName' => 'tab_normal_image_spacing',
                ]) : null,
                $tab_media === "image" ? $elements->style([
                    'attrName' => 'tab_active_image_spacing',
                ]) : null,


                CommonStyle::style([
                    'selector' => $sel('.dipi-at-tab'),
                    'attr' => $attrs['tab_icon_placement']['innerContent'],
                    'declarationFunction' => function (array $args) use ($attrs) {
                        $state = $args['state'] ?? 'value';
                        $breakpoint = $args['breakpoint'] ?? 'desktop';

                        $tab_media = ModuleUtils::get_attr_value(
                            [
                                'attr' => $attrs['tab_media']['innerContent'],
                                'breakpoint' => $breakpoint,
                                'state' => $state,
                                'mode' => 'getAndInheritAll',
                            ]
                        );

                        if ($tab_media !== 'icon') {
                            return "";
                        }

                        switch ($args['attrValue']) {
                            case 'top':
                                return "flex-direction: column;";
                            case 'right':
                                return "flex-direction: row-reverse;";
                            case 'bottom':
                                return "flex-direction: column-reverse;";
                            case 'left':
                                return "flex-direction: row;";
                        }
                    }
                ]),
                CommonStyle::style([
                    'selector' => $sel('.dipi-at-tab'),
                    'attr' => $attrs['tab_image_placement']['innerContent'],
                    'declarationFunction' => function (array $args) use ($attrs) {
                        $state = $args['state'] ?? 'value';
                        $breakpoint = $args['breakpoint'] ?? 'desktop';

                        $tab_media = ModuleUtils::get_attr_value(
                            [
                                'attr' => $attrs['tab_media']['innerContent'],
                                'breakpoint' => $breakpoint,
                                'state' => $state,
                                'mode' => 'getAndInheritAll',
                            ]
                        );

                        if ($tab_media !== 'image') {
                            return "";
                        }

                        switch ($args['attrValue']) {
                            case 'top':
                                return "flex-direction: column;";
                            case 'right':
                                return "flex-direction: row-reverse;";
                            case 'bottom':
                                return "flex-direction: column-reverse;";
                            case 'left':
                                return "flex-direction: row;";
                        }
                    }
                ]),
                CommonStyle::style([
                    'selector' => $sel('.dipi-at-tab .at-media-wrap'),
                    'attr' => $attrs['tab_icon_alignment_horz1']['innerContent'],
                    'declarationFunction' => function (array $args) use ($attrs) {
                        $state = $args['state'] ?? 'value';
                        $breakpoint = $args['breakpoint'] ?? 'desktop';

                        //tab_icon_alignment_horz1 only applies to icon tabs. none/image tabs shall not get the css here
                        $tab_media = ModuleUtils::get_attr_value(
                            [
                                'attr' => $attrs['tab_media']['innerContent'],
                                'breakpoint' => $breakpoint,
                                'state' => $state,
                                'mode' => 'getAndInheritAll',
                            ]
                        );

                        if ($tab_media !== 'icon') {
                            return "";
                        }

                        $tab_icon_placement = ModuleUtils::get_attr_value(
                            [
                                'attr' => $attrs['tab_icon_placement']['innerContent'],
                                'breakpoint' => $breakpoint,
                                'state' => $state,
                                'mode' => 'getAndInheritAll',
                            ]
                        );

                        if ($tab_icon_placement === 'top') {
                            return "text-align: {$args['attrValue']};";
                        } else {
                            return "";
                        }
                    }
                ]),
                CommonStyle::style([
                    'selector' => $sel('.dipi-at-tab .at-media-wrap'),
                    'attr' => $attrs['tab_icon_alignment_horz2']['innerContent'],
                    'declarationFunction' => function (array $args) use ($attrs) {
                        $state = $args['state'] ?? 'value';
                        $breakpoint = $args['breakpoint'] ?? 'desktop';

                        //tab_icon_alignment_horz2 only applies to icon tabs. none/image tabs shall not get the css here
                        $tab_media = ModuleUtils::get_attr_value(
                            [
                                'attr' => $attrs['tab_media']['innerContent'],
                                'breakpoint' => $breakpoint,
                                'state' => $state,
                                'mode' => 'getAndInheritAll',
                            ]
                        );

                        if ($tab_media !== 'icon') {
                            return "";
                        }

                        $tab_icon_placement = ModuleUtils::get_attr_value(
                            [
                                'attr' => $attrs['tab_icon_placement']['innerContent'],
                                'breakpoint' => $breakpoint,
                                'state' => $state,
                                'mode' => 'getAndInheritAll',
                            ]
                        );

                        if ($tab_icon_placement === 'bottom') {
                            return "text-align: {$args['attrValue']};";
                        } else {
                            return "";
                        }
                    }
                ]),
                CommonStyle::style([
                    'selector' => $sel('.dipi-at-tab .at-media-wrap'),
                    'attr' => $attrs['tab_image_alignment_horz1']['innerContent'],
                    'declarationFunction' => function (array $args) use ($attrs) {
                        $state = $args['state'] ?? 'value';
                        $breakpoint = $args['breakpoint'] ?? 'desktop';

                        //tab_image_alignment_horz1 only applies to image tabs. none/icon tabs shall not get the css here
                        $tab_media = ModuleUtils::get_attr_value(
                            [
                                'attr' => $attrs['tab_media']['innerContent'],
                                'breakpoint' => $breakpoint,
                                'state' => $state,
                                'mode' => 'getAndInheritAll',
                            ]
                        );

                        if ($tab_media !== 'image') {
                            return "";
                        }

                        $tab_icon_placement = ModuleUtils::get_attr_value(
                            [
                                'attr' => $attrs['tab_image_placement']['innerContent'],
                                'breakpoint' => $breakpoint,
                                'state' => $state,
                                'mode' => 'getAndInheritAll',
                            ]
                        );

                        if ($tab_icon_placement === 'top') {
                            return "text-align: {$args['attrValue']};";
                        } else {
                            return "";
                        }
                    }
                ]),
                CommonStyle::style([
                    'selector' => $sel('.dipi-at-tab .at-media-wrap'),
                    'attr' => $attrs['tab_image_alignment_horz2']['innerContent'],
                    'declarationFunction' => function (array $args) use ($attrs) {
                        $state = $args['state'] ?? 'value';
                        $breakpoint = $args['breakpoint'] ?? 'desktop';

                        //tab_image_alignment_horz2 only applies to image tabs. none/icon tabs shall not get the css here
                        $tab_media = ModuleUtils::get_attr_value(
                            [
                                'attr' => $attrs['tab_media']['innerContent'],
                                'breakpoint' => $breakpoint,
                                'state' => $state,
                                'mode' => 'getAndInheritAll',
                            ]
                        );

                        if ($tab_media !== 'image') {
                            return "";
                        }

                        $tab_icon_placement = ModuleUtils::get_attr_value(
                            [
                                'attr' => $attrs['tab_image_placement']['innerContent'],
                                'breakpoint' => $breakpoint,
                                'state' => $state,
                                'mode' => 'getAndInheritAll',
                            ]
                        );

                        if ($tab_icon_placement === 'bottom') {
                            return "text-align: {$args['attrValue']};";
                        } else {
                            return "";
                        }
                    }
                ]),




















                CommonStyle::style([
                    'selector' => $sel(' .dipi-at-panel-content .dipi-at-panel-image'),
                    'attr' => $attrs['img_container_width']['innerContent'],
                    'declarationFunction' => function (array $args) use ($attrs) {

                        $attrValue = $args['attrValue'];
                        if (empty($attrValue) || trim($attrValue) === '') {
                            return '';
                        }


                        $state = $args['state'] ?? 'value';
                        $breakpoint = $args['breakpoint'] ?? 'desktop';


                        //Only apply image width for non-library layouts
                        $use_library_content = ModuleUtils::get_attr_value(
                            [
                                'attr' => $attrs['use_library_content']['innerContent'],
                                'breakpoint' => $breakpoint,
                                'state' => $state,
                                'mode' => 'getAndInheritAll',
                                'defaultValue' => [],
                                'breakpointNames' => Breakpoint::get_all_breakpoint_names(),
                                'baseBreakpoint' => 'desktop',
                            ]
                        );
                        if ($use_library_content === 'on') {
                            return "";
                        }

                        //Only apply imag ewidth for row/row-reverse layout
                        $img_placement = ModuleUtils::get_attr_value(
                            [
                                'attr' => $attrs['img_placement']['innerContent'],
                                'breakpoint' => $breakpoint,
                                'state' => $state,
                                'mode' => 'getAndInheritAll',
                                'defaultValue' => [],
                                'breakpointNames' => Breakpoint::get_all_breakpoint_names(),
                                'baseBreakpoint' => 'desktop',
                            ]
                        );
                        if ($img_placement !== 'row' && $img_placement !== 'row-reverse') {
                            return "";
                        }

                        return "width: {$attrValue};";
                    }
                ]),
                CommonStyle::style([
                    'selector' => $sel(' .dipi-at-panel-content .dipi-at-panel-text'),
                    'attr' => $attrs['img_container_width']['innerContent'],
                    'declarationFunction' => function (array $args) use ($attrs) {

                        $attrValue = $args['attrValue'];
                        if (empty($attrValue) || trim($attrValue) === '') {
                            return '';
                        }


                        $state = $args['state'] ?? 'value';
                        $breakpoint = $args['breakpoint'] ?? 'desktop';


                        //Only apply image width for non-library layouts
                        $use_library_content = ModuleUtils::get_attr_value(
                            [
                                'attr' => $attrs['use_library_content']['innerContent'],
                                'breakpoint' => $breakpoint,
                                'state' => $state,
                                'mode' => 'getAndInheritAll',
                                'defaultValue' => [],
                                'breakpointNames' => Breakpoint::get_all_breakpoint_names(),
                                'baseBreakpoint' => 'desktop',
                            ]
                        );
                        if ($use_library_content === 'on') {
                            return "";
                        }

                        //Only apply imag ewidth for row/row-reverse layout
                        $img_placement = ModuleUtils::get_attr_value(
                            [
                                'attr' => $attrs['img_placement']['innerContent'],
                                'breakpoint' => $breakpoint,
                                'state' => $state,
                                'mode' => 'getAndInheritAll',
                                'defaultValue' => [],
                                'breakpointNames' => Breakpoint::get_all_breakpoint_names(),
                                'baseBreakpoint' => 'desktop',
                            ]
                        );
                        if ($img_placement !== 'row' && $img_placement !== 'row-reverse') {
                            return "";
                        }

                        return "width: calc(100% - {$attrValue});";
                    }
                ]),
                CommonStyle::style([
                    'selector' => $sel(' .dipi-at-panel-content .dipi-at-panel-image') . ', ' . $sel(' .dipi-at-panel-content .dipi-at-panel-text'),
                    'attr' => $attrs['img_container_width']['innerContent'],
                    'declarationFunction' => function (array $args) use ($attrs) {

                        $attrValue = $args['attrValue'];
                        if (empty($attrValue) || trim($attrValue) === '') {
                            return '';
                        }


                        $state = $args['state'] ?? 'value';
                        $breakpoint = $args['breakpoint'] ?? 'desktop';


                        //Only apply image width for non-library layouts
                        $use_library_content = ModuleUtils::get_attr_value(
                            [
                                'attr' => $attrs['use_library_content']['innerContent'],
                                'breakpoint' => $breakpoint,
                                'state' => $state,
                                'mode' => 'getAndInheritAll',
                                'defaultValue' => [],
                                'breakpointNames' => Breakpoint::get_all_breakpoint_names(),
                                'baseBreakpoint' => 'desktop',
                            ]
                        );
                        if ($use_library_content === 'on') {
                            return "";
                        }

                        //Only apply imag ewidth for row/row-reverse layout
                        $img_placement = ModuleUtils::get_attr_value(
                            [
                                'attr' => $attrs['img_placement']['innerContent'],
                                'breakpoint' => $breakpoint,
                                'state' => $state,
                                'mode' => 'getAndInheritAll',
                                'defaultValue' => [],
                                'breakpointNames' => Breakpoint::get_all_breakpoint_names(),
                                'baseBreakpoint' => 'desktop',
                            ]
                        );
                        if ($img_placement !== 'column' && $img_placement !== 'column-reverse') {
                            return "";
                        }


                        $spacing = self::get_attr_value(
                            [
                                'attr' => $attrs['image']['decoration']['spacing'],
                                'breakpoint' => $breakpoint,
                                'state' => $state,
                                'mode' => 'getAndInheritAll',
                                'defaultValue' => [],
                                'breakpointNames' => Breakpoint::get_all_breakpoint_names(),
                                'baseBreakpoint' => 'desktop',
                            ]
                        );
                        $margin_left = $spacing['margin']['left'] ?? '0px';
                        $margin_right = $spacing['margin']['right'] ?? '0px';
                        return "width: calc(100% - {$margin_left} - {$margin_right});";
                    }
                ]),







                CommonStyle::style([
                    'selector' => $sel_prefix('.dipi_advanced_tabs .dipi-at-panels ', ' .dipi-at-panel-content ul'),
                    'attr' => static::getAttr($attrs, 'ul_type'),
                    'declarationFunction' => function (array $args) {
                        return "list-style-type: {$args['attrValue']} !important;";
                    }
                ]),
                CommonStyle::style([
                    'selector' => $sel_prefix('.dipi_advanced_tabs .dipi-at-panels ', ' .dipi-at-panel-content ul'),
                    'attr' => static::getAttr($attrs, 'ul_position'),
                    'declarationFunction' => function (array $args) {
                        return "list-style-position: {$args['attrValue']} !important;";
                    }
                ]),
                CommonStyle::style([
                    'selector' => $sel_prefix('.dipi_advanced_tabs .dipi-at-panels ', ' .dipi-at-panel-content ul'),
                    'attr' => static::getAttr($attrs, 'ul_item_indent'),
                    'declarationFunction' => function (array $args) {
                        return "padding-left: {$args['attrValue']} !important;";
                    }
                ]),
                CommonStyle::style([
                    'selector' => $sel_prefix('.dipi_advanced_tabs .dipi-at-panels ', ' .dipi-at-panel-content ol'),
                    'attr' => static::getAttr($attrs, 'ol_type'),
                    'declarationFunction' => function (array $args) {
                        return "list-style-type: {$args['attrValue']} !important;";
                    }
                ]),
                CommonStyle::style([
                    'selector' => $sel_prefix('.dipi_advanced_tabs .dipi-at-panels ', ' .dipi-at-panel-content ol'),
                    'attr' => static::getAttr($attrs, 'ol_position'),
                    'declarationFunction' => function (array $args) {
                        return "list-style-position: {$args['attrValue']} !important;";
                    }
                ]),
                CommonStyle::style([
                    'selector' => $sel_prefix('.dipi_advanced_tabs .dipi-at-panels ', ' .dipi-at-panel-content ol'),
                    'attr' => static::getAttr($attrs, 'ol_item_indent'),
                    'declarationFunction' => function (array $args) {
                        return "padding-left: {$args['attrValue']} !important;";
                    }
                ]),










                $use_active_tab_icon === "on" ? CommonStyle::style([
                    'selector' => $sel_prefix('.dipi_advanced_tabs ', '.dipi-at-tab .dipi-tab-media--active'),
                    'attr' => static::getAttr($attrs, 'font_icon_active'),
                    'declarationFunction' => [static::class, 'icon_font_declaration'],
                ]) : null,
                $use_active_tab_icon !== "on" ? CommonStyle::style([
                    'selector' => $sel_prefix('.dipi_advanced_tabs ', '.dipi-at-tab .dipi-tab-media--normal') . ', ' . $sel_prefix('.dipi_advanced_tabs ', '.dipi-at-tab .dipi-tab-media--active'),
                    'attr' => static::getAttr($attrs, 'font_icon'),
                    'declarationFunction' => [static::class, 'icon_font_declaration'],
                ]) : null,
                $use_button === "on" ? CommonStyle::style([
                    'selector' => $sel_prefix('.dipi_advanced_tabs .dipi-at-panels ', ' .dipi-at-btn-wrap'),
                    'attr' => static::getAttr($attrs, 'button'),
                    'declarationFunction' => function (array $args) use ($button_alignment) {
                        if ($button_alignment === "center") {
                            return "justify-content: center!important;";
                        } else if ($button_alignment === "right") {
                            return "justify-content: flex-end!important;";
                        }
                        return "justify-content: flex-start!important;";
                    }
                ]) : null,
                CommonStyle::style([
                    'selector' => $sel('.dipi-at-tab .et-pb-icon'),
                    'attr' => static::getAttr($attrs, 'icon_color'),
                    'declarationFunction' => function (array $args) {
                        return "color: {$args['attrValue']}!important;";
                    }
                ]),
                CommonStyle::style([
                    'selector' => $sel('.dipi-at-tab img'),
                    'attr' => static::getAttr($attrs, 'tab_image_size'),
                    'declarationFunction' => function (array $args) {
                        return "width: {$args['attrValue']}!important;";
                    }
                ]),
                CommonStyle::style([
                    'selector' => $sel('.dipi-at-tab .et-pb-icon'),
                    'attr' => static::getAttr($attrs, 'icon_size'),
                    'declarationFunction' => function (array $args) {
                        return "font-size: {$args['attrValue']}!important;";
                    }
                ]),
                CommonStyle::style([
                    'selector' => $sel('.dipi-at-tab.dipi-at-tab--active .et-pb-icon.dipi-tab-media--active'),
                    'attr' => static::getAttr($attrs, 'icon_color_active'),
                    'declarationFunction' => function (array $args) {
                        return "color: {$args['attrValue']}!important;";
                    }
                ]),
                CommonStyle::style([
                    'selector' => $sel('.dipi-at-tab.dipi-at-tab--active .et-pb-icon.dipi-tab-media--active'),
                    'attr' => static::getAttr($attrs, 'icon_size_active'),
                    'declarationFunction' => function (array $args) {
                        return "font-size: {$args['attrValue']}!important;";
                    }
                ]),
                $use_library_content !== "on" ? CommonStyle::style([
                    'selector' => $sel(' .dipi-at-panel-content'),
                    'attr' => static::getAttr($attrs, 'img_placement'),
                    'declarationFunction' => function (array $args) {
                        return "flex-direction: {$args['attrValue']};";
                    }
                ]) : null,







                $use_library_content !== "on" && ($img_placement === "column" || $img_placement === "column-reverse") ? CommonStyle::style([
                    'selector' => $sel(' .dipi-at-panel-content .dipi-at-panel-image'),
                    'attr' => static::getAttr($attrs, 'content_image_align'),
                    'declarationFunction' => function (array $args) {
                        $breakpoint = $args["breakpoint"];
                        return $breakpoint === "phone" ? "align-self: {$args['attrValue']} !important;" : "";
                    }
                ]) : null,
                $use_library_content !== "on" ? CommonStyle::style([
                    'selector' => $sel(' .dipi-at-panel-content .dipi-at-panel-image'),
                    'attr' => static::getAttr($attrs, 'img_container_max_width'),
                    'declarationFunction' => function (array $args) {
                        $breakpoint = $args["breakpoint"];
                        return $breakpoint === "phone" ? "max-width: {$args['attrValue']} !important;" : "";
                    }
                ]) : null,
            ],
        ]);
    }



    /**
     * Copy of ModuleUtils::get_attr_value() since its not working for spacing arrays as we expect it so we just make it work to our liking 
     */
    public static function get_attr_value(array $args)
    {
        $args = wp_parse_args(
            $args,
            [
                'mode' => 'getOrInheritAll',
                'defaultValue' => null,
                'baseBreakpoint' => 'desktop',
                'breakpointNames' => Breakpoint::get_default_breakpoint_names(),
            ]
        );

        $attr = $args['attr'];
        $base_breakpoint = $args['baseBreakpoint'];
        $breakpoint = $args['breakpoint'];
        $breakpoint_names = $args['breakpointNames'];
        $state = $args['state'];
        $mode = $args['mode'];
        $default_value = $args['defaultValue'];

        // Get attribute value.
        $attr_value = isset($attr[$breakpoint][$state]) ? $attr[$breakpoint][$state] : null;

        // Get inherited value.
        $inherited_attr_value = null;

        switch ($mode) {
            case 'getAndInheritClosest':
            case 'getOrInheritClosest':
            case 'inheritClosest':
                $inherited_attr_value = ModuleUtils::inherit_attr_value(
                    [
                        'attr' => $attr,
                        'baseBreakpoint' => $base_breakpoint,
                        'breakpoint' => $breakpoint,
                        'breakpointNames' => $breakpoint_names,
                        'state' => $state,
                        'inheritMode' => 'closest',
                    ]
                );
                break;

            // Default is for *InheritAll mode:
            // - 'getAndInheritAll'
            // - 'getOrInheritAll'
            // - 'inheritAll'
            // - 'get'.
            default:
                $inherited_attr_value = ModuleUtils::inherit_attr_value(
                    [
                        'attr' => $attr,
                        'baseBreakpoint' => $base_breakpoint,
                        'breakpoint' => $breakpoint,
                        'breakpointNames' => $breakpoint_names,
                        'state' => $state,
                        'inheritMode' => 'all',
                    ]
                );
                break;
        }

        // Get returned value based on its mode.
        $returned_attr_value = null;
        switch ($mode) {
            case 'getAndInheritAll':
            case 'getAndInheritClosest':
                // Combine attrValue and inherited value.
                if (is_array($attr_value) && is_array($inherited_attr_value)) {
                    $returned_attr_value = self::array_replace_recursive_non_empty($inherited_attr_value, $attr_value);
                } else {
                    $returned_attr_value = null !== $attr_value ? $attr_value : $inherited_attr_value;
                }
                break;
            case 'getOrInheritAll':
            case 'getOrInheritClosest':
                $returned_attr_value = null !== $attr_value ? $attr_value : $inherited_attr_value;
                break;
            case 'inheritAll':
            case 'inheritClosest':
                $returned_attr_value = $inherited_attr_value;
                break;

            // Default stands for mode === 'get'.
            default:
                $returned_attr_value = $attr_value;
                break;
        }

        return null !== $returned_attr_value ? $returned_attr_value : $default_value;
    }



    public static function array_replace_recursive_non_empty(array $base, array $override)
    {
        foreach ($override as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = self::array_replace_recursive_non_empty($base[$key], $value);
            } else {
                // Only overwrite if value is not empty
                if ($value !== '' && $value !== null) {
                    $base[$key] = $value;
                }
            }
        }

        return $base;
    }
}
