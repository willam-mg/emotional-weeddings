<?php
namespace DIPI\Modules\FilterableGrid;

if (!defined("ABSPATH")) {
    die("Direct access forbidden.");
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Border\BorderStyle;
use ET\Builder\Packages\Module\Options\Spacing\SpacingStyle;
use ET\Builder\Packages\StyleLibrary\Declarations\Background\GradientBackgroundStyleDeclarationTrait;

trait ModuleStylesTrait
{
    use CustomCssTrait;
    use StyleDeclarationTrait;
    use GradientBackgroundStyleDeclarationTrait;

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

    public static function getDipiAttr(
        $attrs,
        $attr,
        $default = null,
        $zoom = "",
        $unit = "",
        $wrap_func = ""
    ) {
        $beforeAttrValue = (($attrs ?? [])[$attr] ?? [])["innerContent"] ?? [
            "desktop" => ["value" => $default ?? ""],
        ];
        $afterAttrValue = $beforeAttrValue;
        if (empty($afterAttrValue["tablet"])) {
            $afterAttrValue["tablet"] = $afterAttrValue["desktop"];
        }
        if (empty($afterAttrValue["phone"])) {
            $afterAttrValue["phone"] = $afterAttrValue["tablet"];
        }
        $slug_value = $afterAttrValue["desktop"]["value"] ?? $default;
        $slug_value_tablet = $afterAttrValue["tablet"]["value"];
        $slug_value_phone = $afterAttrValue["phone"]["value"];
        if ($zoom === "") {
            $slug_value = $slug_value . $unit;
            $slug_value_tablet = $slug_value_tablet . $unit;
            $slug_value_phone = $slug_value_phone . $unit;
        } else {
            $slug_value = (float) $slug_value * $zoom . $unit;
            $slug_value_tablet = (float) $slug_value_tablet * $zoom . $unit;
            $slug_value_phone = (float) $slug_value_phone * $zoom . $unit;
        }
        if ($wrap_func !== "") {
            $slug_value = "$wrap_func($slug_value)";
            $slug_value_tablet = "$wrap_func($slug_value_tablet)";
            $slug_value_phone = "$wrap_func($slug_value_phone)";
        }
        $afterAttrValue["desktop"]["value"] = $slug_value;
        if (isset($beforeAttrValue["tablet"])) {
            $afterAttrValue["tablet"]["value"] = $slug_value_tablet;
        }
        if (isset($beforeAttrValue["phone"])) {
            $afterAttrValue["phone"]["value"] = $slug_value_phone;
        }
        return $afterAttrValue;
    }
    public static function getDipiAttrNumber(
        $attrs,
        $attr,
        $default = null,
        $delta = 0
    ) {
        $beforeAttrValue = (($attrs ?? [])[$attr] ?? [])["innerContent"] ?? [
            "desktop" => ["value" => $default ?? ""],
        ];
        $afterAttrValue = $beforeAttrValue;
        $afterAttrValue["desktop"]["value"] =
            (float) $beforeAttrValue["desktop"]["value"] + (float) $delta;
        if (isset($beforeAttrValue["tablet"])) {
            $afterAttrValu["tablet"]["value"] =
                (float) $beforeAttrValue["tablet"]["value"] + (float) $delta;
        }
        if (isset($beforeAttrValue["phone"])) {
            $afterAttrValue["phone"]["value"] =
                (float) $beforeAttrValue["phone"]["value"] + (float) $delta;
        }
        return $afterAttrValue;
    }

    public static function module_styles($args)
    {
        $attrs    = $args['attrs'] ?? [];
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class  = $args['orderClass'] ?? '';
        $order_number = preg_replace('/[^0-9]/', '', $order_class);

        $filter_bar_selector = "{$order_class} .dipi-filter-bar";
        $filter_bar_item_selector = "{$order_class} .dipi-filter-bar .dipi-filter-bar-item";
        $filter_bar_item_title_selector = "{$order_class} .dipi-filter-bar .dipi-filter-bar-item .dipi-filter-bar-item-title";
        $filter_bar_item_hover_selector = "{$order_class} .dipi-filter-bar .dipi-filter-bar-item:hover";
        $filter_bar_item_active_selector = "{$order_class} .dipi-filter-bar .dipi-filter-bar-item.active";
        $filter_bar_item_active_hover_selector = "{$order_class} .dipi-filter-bar .dipi-filter-bar-item.active:hover";
        $pagination_selector = "{$order_class} .dipi-pagination";
        $grid_item_grid_selector = "{$order_class} .dipi-filtered-grid-container .dipi-filtered-grid-item, {$order_class} .dipi-filtered-grid-container .dipi-filtered-grid-item .grid";
        $grid_item_selector = "{$order_class} .dipi-filtered-posts-container .dipi-filtered-posts-item .grid-item";
        $overlay_icon_selector = "{$order_class}.dipi_filterable_grid .grid .grid-item .dipi_filterable_grid_overlay .dipi-filterable-grid-icon";
        $overlay_icon_hover_selector = "{$order_class}.dipi_filterable_grid .grid .grid-item .dipi_filterable_grid_overlay .dipi-filterable-grid-icon:hover";
        $overlay_icon_circle_selector = "{$order_class}.dipi_filterable_grid .grid .grid-item .dipi_filterable_grid_overlay .dipi-filterable-grid-icon.dipi-filterable-grid-icon-circle";
        $overlay_icon_circle_hover_selector = "{$order_class}.dipi_filterable_grid .grid .grid-item .dipi_filterable_grid_overlay .dipi-filterable-grid-icon.dipi-filterable-grid-icon-circle:hover";
        $overlay_selector = "{$order_class}.dipi_filterable_grid .grid .grid-item .dipi_filterable_grid_overlay.content";
        $hover_icon_selector = "{$order_class}.dipi_filterable_grid .grid .grid-item:hover .dipi_filterable_grid_overlay .dipi-filterable-grid-icon";
        $hover_title_selector = "{$order_class}.dipi_filterable_grid .grid .grid-item:hover .dipi_filterable_grid_overlay .dipi-filterable-grid-title";
        $hover_excerpt_selector = "{$order_class}.dipi_filterable_grid .grid .grid-item:hover .dipi_filterable_grid_overlay .dipi-filterable-grid-excerpt";
        $filter_bar_item_text_selector = "{$order_class} .dipi-filter-bar .dipi-filter-bar-item .dipi-filter-bar-item-title, {$order_class} .dipi-filter-bar .dipi-filter-bar-item .dipi-filter-bar-item-desc";
        $filter_bar_item_active_text_selector = "{$order_class} .dipi-filter-bar .dipi-filter-bar-item.active .dipi-filter-bar-item-title, {$order_class} .dipi-filter-bar .dipi-filter-bar-item.active .dipi-filter-bar-item-desc";
        
        $use_overlay = static::getAttrByMode($attrs, 'use_overlay', 'off');
        $overlay_icon_use_icon_font_size = static::getAttrByMode($attrs, 'overlay_icon_use_icon_font_size', 'on');
        $overlay_icon_use_circle = static::getAttrByMode($attrs, 'overlay_icon_use_circle', 'on');
        $overlay_icon_use_circle_border = static::getAttrByMode($attrs, 'overlay_icon_use_circle_border', 'off');
        
        $filter_bar_normal_text_align = $attrs['filter_bar_item']['decoration']['font']['font']['desktop']['value']['textAlign'] ?? '';
        $filter_bar_active_text_align = $attrs['filter_bar_item_active']['decoration']['font']['font']['desktop']['value']['textAlign'] ?? '';

        $background_color = $attrs["overlay_bg"]["decoration"]["background"]["desktop"]["value"]["color"] ?? null;
        $hover_background_color = $attrs["overlay_bg"]["decoration"]["background"]["desktop"]["hover"]["color"] ?? null;
        $background_gradient = $attrs["overlay_bg"]["decoration"]["background"]["desktop"]["value"]["gradient"] ?? null;
        $hover_background_gradient = $attrs["overlay_bg"]["decoration"]["background"]["desktop"]["hover"]["gradient"] ?? null;

        if($background_gradient && !isset($background_gradient["stops"])) 
            $background_gradient = null;
        if($background_gradient) {
            if(!isset($background_gradient['type']))
            {
                $background_gradient['type'] = 'linear';
            }
            if(!isset($background_gradient['direction']))
            {
                $background_gradient['direction'] = '180deg';
            }
        }

        if($hover_background_gradient && !isset($hover_background_gradient["stops"])) 
            $hover_background_gradient = null;
        if($hover_background_gradient) {
            if(!isset($hover_background_gradient['type']))
            {
                $hover_background_gradient['type'] = 'linear';
            }
            if(!isset($hover_background_gradient['direction']))
            {
                $hover_background_gradient['direction'] = '180deg';
            }
        }

        $style = [
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
            CssStyle::style([
                'selector'  => "{$args['orderClass']} .dipi_filterable_grid_wrapper .grid .grid-item",
                'attr'      => $attrs['css'] ?? [],
                'cssFields' => static::custom_css_for_item(),
            ]),
            $elements->style([
                'attrName'   => 'pagination_btn',
            ]),
            $elements->style([
                'attrName'   => 'pagination_btn_active',
            ]),
            $elements->style([
                'attrName'   => 'load_more',
            ]),
            CommonStyle::style([
                'selector'            => $pagination_selector,
                'attr'                => static::getAttr($attrs, 'load_more_alignment'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "justify-content: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $filter_bar_selector,
                'attr'                => static::getAttr($attrs, 'sticky_filter_bar_top'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "top: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $filter_bar_selector,
                'attr'                => static::getAttr($attrs, 'space_tabs'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "gap: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $filter_bar_selector,
                'attr'                => static::getAttr($attrs, 'filter_bar_max_width'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "max-width: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $filter_bar_item_title_selector,
                'attr'                => static::getAttr($attrs, 'space_tab_number'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "gap: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $filter_bar_selector,
                'attr'                => static::getAttr($attrs, 'filter_bar_layout'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "flex-direction: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $filter_bar_selector,
                'attr'                => static::getAttr($attrs, 'filter_tab_alignment'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "place-content: $attrValue;";
                }
            ]),
            $elements->style([
                'attrName'   => 'filter_bar',
            ]),
            $elements->style([
                'attrName'   => 'filter_bar_item',
            ]),
            $elements->style([
                'attrName'   => 'filter_bar_item_active',
            ]),
            CommonStyle::style([
                'selector'            => $filter_bar_item_text_selector,
                'attr'                => static::getAttr($attrs, 'filter_bar_item_text_align'),
                'declarationFunction' => function ( array $args ) use ($filter_bar_normal_text_align) {
                    return  "justify-content: $filter_bar_normal_text_align;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $filter_bar_item_active_text_selector,
                'attr'                => static::getAttr($attrs, 'filter_bar_item_text_align_active'),
                'declarationFunction' => function ( array $args ) use ($filter_bar_active_text_align) {
                    return  "justify-content: $filter_bar_active_text_align;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $filter_bar_item_selector,
                'attr'                => static::getAttr($attrs, 'filter_bar_item_width'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "width: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $filter_bar_item_hover_selector,
                'attr'                => static::getAttr($attrs, 'filter_bar_item_width'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "width: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $filter_bar_item_selector,
                'attr'                => static::getAttr($attrs, 'filter_bar_item_height'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "height: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $filter_bar_item_hover_selector,
                'attr'                => static::getAttr($attrs, 'filter_bar_item_height'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "height: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $filter_bar_item_active_selector,
                'attr'                => static::getAttr($attrs, 'filter_bar_item_width_active'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "width: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $filter_bar_item_active_hover_selector,
                'attr'                => static::getAttr($attrs, 'filter_bar_item_width_active'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "width: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $filter_bar_item_active_selector,
                'attr'                => static::getAttr($attrs, 'filter_bar_item_height_active'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "height: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $filter_bar_item_active_hover_selector,
                'attr'                => static::getAttr($attrs, 'filter_bar_item_height_active'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "height: $attrValue;";
                }
            ]),
            $elements->style([
                'attrName'   => 'grid',
            ]),
            $elements->style([
                'attrName'   => 'grid_item',
            ]),
            CommonStyle::style([
                'selector'            => $grid_item_grid_selector,
                'attr'                => static::getAttr($attrs, 'grid_animation_speed'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "animation-duration: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $grid_item_grid_selector,
                'attr'                => static::getAttr($attrs, 'grid_animation_delay'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "animation-delay: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $grid_item_selector,
                'attr'                => static::getAttr($attrs, 'grid_animation_speed'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "animation-duration: $attrValue;";
                }
            ]),
            $elements->style([
                'attrName'   => 'grid_item_title',
            ]),
            $elements->style([
                'attrName'   => 'grid_item_excerpt',
            ]),
            $elements->style([
                'attrName'   => 'grid_item_category',
            ]),
            CommonStyle::style([
                'selector'            => "{$order_class} .img-container.dipi-fg-animation:hover img",
                'attr'                => static::getAttr($attrs, 'image_animation_speed'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = (int)$args['attrValue'] / 1000;
                    return  "transition-duration: {$attrValue}s;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "{$order_class} .grid-sizer, {$order_class} .grid-item",
                'attr'                => static::getAttr($attrs, 'columns_gutter'),
                'declarationFunction' => function ( array $args ) {
                    if(!isset($args['attrValue']['columns'])){
                        return "";
                    }
                    $columns = $args['attrValue']['columns'];
                    $gutter = isset($args['attrValue']['gutter']) ? $args['attrValue']['gutter'] : 10;
                    return  "width: calc((100% - (({$columns} - 1) * {$gutter}px)) / {$columns});";
                }
            ]),
            CommonStyle::style([
                'selector'            => "{$order_class}.dipi_filterable_grid .dipi_filterable_grid_wrapper.layout_grid .grid .img-container, {$order_class}.dipi_filterable_grid .dipi_filterable_grid_wrapper.layout_grid .grid .img-container img",
                'attr'                => static::getAttr($attrs, 'row_height'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "height: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "{$order_class} .grid-item",
                'attr'                => static::getAttr($attrs, 'columns_gutter'),
                'declarationFunction' => function ( array $args ) {
                    $gutter = isset($args['attrValue']['gutter']) ? $args['attrValue']['gutter'] : 10;
                    return  "margin-bottom: {$gutter}px;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "{$order_class}.dipi_filterable_grid .dipi_filterable_grid_wrapper.layout_grid .grid",
                'attr'                => static::getAttr($attrs, 'columns_gutter'),
                'declarationFunction' => function ( array $args ) {
                    $gutter = isset($args['attrValue']['gutter']) ? $args['attrValue']['gutter'] : 10;;
                    return  "column-gap: {$gutter}px;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "{$order_class} .gutter-sizer",
                'attr'                => static::getAttr($attrs, 'columns_gutter'),
                'declarationFunction' => function ( array $args ) {
                    $gutter = isset($args['attrValue']['gutter']) ? $args['attrValue']['gutter'] : 10;
                    return  "width: {$gutter}px;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "{$order_class} .grid",
                'attr'                => static::getAttr($attrs, 'columns_gutter'),
                'declarationFunction' => function ( array $args ) {
                    $gutter = isset($args['attrValue']['gutter']) ? $args['attrValue']['gutter'] : 10;
                    return  "margin-bottom: {$gutter}px;";
                }
            ]),
            CommonStyle::style([
                'selector'            => "{$order_class}.dipi_filterable_grid, {$order_class}.dipi_filterable_grid .grid-item",
                'attr'                => static::getAttr($attrs, 'show_overflow'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  $attrValue === "on" ? "overflow: visible !important;" : "";
                }
            ]),
            CommonStyle::style([
                'selector'            => $overlay_icon_selector,
                'attr'                => static::getAttr($attrs, 'overlay_icon_color'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "color: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $overlay_icon_circle_selector,
                'attr'                => static::getAttr($attrs, 'overlay_icon_circle_color'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "background-color: $attrValue;";
                }
            ]),
            $elements->style([
                'attrName'   => 'read_more_button',
                'styleProps' => [
                    'button' => [
                        'propertySelectors' => [
                            'desktop' => [
                                'value' => [
                                    'text-align'=> "{$order_class} .dipi-fg-readmore-button-wrapper",
                                ]
                            ]
                        ]
                    ]
                ]
            ]),
            $elements->style([
                'attrName'   => 'link_font',
            ]),
            $elements->style([
                'attrName'   => 'ul_font',
            ]),
            $elements->style([
                'attrName'   => 'ol_font',
            ]),
            $elements->style([
                'attrName'   => 'quote_font',
            ]),
            $elements->style([
                'attrName'   => 'meta_font',
            ]),
            $elements->style([
                'attrName'   => 'meta_author_font',
            ]),
            $elements->style([
                'attrName'   => 'meta_date_font',
            ]),
            $elements->style([
                'attrName'   => 'header_1',
            ]),
            $elements->style([
                'attrName'   => 'header_2',
            ]),
            $elements->style([
                'attrName'   => 'header_3',
            ]),
            $elements->style([
                'attrName'   => 'header_4',
            ]),
            $elements->style([
                'attrName'   => 'header_5',
            ]),
            $elements->style([
                'attrName'   => 'header_6',
            ]),
            $elements->style([
                'attrName'   => 'header_font',
            ]),
            $elements->style([
                'attrName'   => 'excerpt_font',
            ]),
            $elements->style([
                'attrName'   => 'filter_bar_name_font',
            ]),
            $elements->style([
                'attrName'   => 'filter_bar_desc_font',
            ]),
        ];

        if($use_overlay === "on") {
            if($background_gradient)
            {
                $style[] = CommonStyle::style([
                    'selector'            => "$order_class .grid .grid-item .dipi_filterable_grid_overlay.background",
                    'attr'                => static::getAttr($attrs, 'icon_speed'),
                    'declarationFunction' => function ( array $args ) use ($background_gradient) {
                        $gradient_string = static::gradient_background_style_declaration($background_gradient);
                        return  "background-image: $gradient_string;";
                    }
                ]);
            }
            else if($background_color)
            {
                $style[] = CommonStyle::style([
                    'selector'            => "$order_class .grid .grid-item .dipi_filterable_grid_overlay.background",
                    'attr'                => static::getAttr($attrs, 'icon_speed'),
                    'declarationFunction' => function ( array $args ) use ($background_color) {
                        return  "background-color: $background_color;";
                    }
                ]);
            }
            if($hover_background_gradient)
            {
                $style[] = CommonStyle::style([
                    'selector'            => "$order_class .grid .grid-item .dipi_filterable_grid_overlay.background-hover",
                    'attr'                => static::getAttr($attrs, 'icon_speed'),
                    'declarationFunction' => function ( array $args ) use ($hover_background_gradient) {
                        $gradient_string = static::gradient_background_style_declaration($hover_background_gradient);
                        return  "background-image: $gradient_string;";
                    }
                ]);
            }
            else if($hover_background_color)
            {
                $style[] = CommonStyle::style([
                    'selector'            => "$order_class .grid .grid-item .dipi_filterable_grid_overlay.background-hover",
                    'attr'                => static::getAttr($attrs, 'icon_speed'),
                    'declarationFunction' => function ( array $args ) use ($hover_background_color) {
                        return  "background-color: $hover_background_color;";
                    }
                ]);
            }
            $style[] = CommonStyle::style([
                'selector'            => $hover_icon_selector,
                'attr'                => static::getAttr($attrs, 'icon_speed'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "animation-duration: $attrValue!important;";
                }
            ]);
            $style[] = CommonStyle::style([
                'selector'            => $hover_icon_selector,
                'attr'                => static::getAttr($attrs, 'icon_delay'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "animation-delay: $attrValue!important;";
                }
            ]);
            $style[] = CommonStyle::style([
                'selector'            => $hover_title_selector,
                'attr'                => static::getAttr($attrs, 'title_speed'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "animation-duration: $attrValue!important;";
                }
            ]);
            $style[] = CommonStyle::style([
                'selector'            => $hover_title_selector,
                'attr'                => static::getAttr($attrs, 'title_delay'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "animation-delay: $attrValue!important;";
                }
            ]);
            $style[] = CommonStyle::style([
                'selector'            => $hover_excerpt_selector,
                'attr'                => static::getAttr($attrs, 'excerpt_speed'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "animation-duration: $attrValue!important;";
                }
            ]);
            $style[] = CommonStyle::style([
                'selector'            => $hover_excerpt_selector,
                'attr'                => static::getAttr($attrs, 'excerpt_delay'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "animation-delay: $attrValue!important;";
                }
            ]);
            $style[] = SpacingStyle::style([
                'selector'            => $overlay_selector,
                'attr'                => static::getAttr($attrs, 'overlay_padding'),
            ]);
            $style[] = CommonStyle::style([
                'selector'            => $overlay_selector,
                'attr'                => static::getAttr($attrs, 'overlay_align_horizontal'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];    
                    $align = $attrValue === "flex-start" ? "left" : ($attrValue === "flex-end" ? "right" : "center");
                    return  "text-align: $align;";
                }
            ]);
            $style[] = CommonStyle::style([
                'selector'            => $overlay_selector,
                'attr'                => static::getAttr($attrs, 'overlay_align_horizontal'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "align-items: $attrValue;";
                }
            ]);
            $style[] = CommonStyle::style([
                'selector'            => $overlay_selector,
                'attr'                => static::getAttr($attrs, 'overlay_align_vertical'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "justify-content: $attrValue;";
                }
            ]);
            if($overlay_icon_use_icon_font_size === "on") {
                $style[] = CommonStyle::style([
                    'selector'            => $overlay_icon_selector,
                    'attr'                => static::getAttr($attrs, 'overlay_icon_font_size', '15px'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "font-size: $attrValue;";
                    }
                ]);
                $style[] = CommonStyle::style([
                    'selector'            => $overlay_icon_hover_selector,
                    'attr'                => static::getAttr($attrs, 'overlay_icon_font_size', '15px'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "font-size: $attrValue;";
                    }
                ]);
            }
            if($overlay_icon_use_circle === "on") {
                $style[] = SpacingStyle::style([
                    'selector'            => $overlay_icon_circle_selector,
                    'attr'                => static::getAttr($attrs, 'overlay_icon_circle_padding'),
                ]);
                if($overlay_icon_use_circle_border === "on") {
                    $style[] = CommonStyle::style([
                        'selector'            => $overlay_icon_circle_selector,
                        'attr'                => static::getAttr($attrs, 'overlay_icon_circle_border_color'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "border-color: $attrValue;";
                        }
                    ]);
                }
                $style[] = CommonStyle::style([
                    'selector'            => $overlay_icon_selector,
                    'attr'                => static::getAttr($attrs, 'hover_icon'),
                    'declarationFunction' => [ static::class, 'icon_font_declaration' ],
                ]);
            }
        }

        Style::add([
            'id'            => $args['id'],
            'name'          => $args['name'],
            'orderIndex'    => $args['orderIndex'],
            'storeInstance' => $args['storeInstance'],
            'styles'        => $style
		]);
    }
}
