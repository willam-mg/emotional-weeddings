<?php
namespace DIPI\Modules\HorizontalTimelineItem;

if (!defined("ABSPATH")) {
    die("Direct access forbidden.");
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Border\BorderStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
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

    /**
     * Normalize spacing field to a valid CSS length (e.g. "15" -> "15px").
     */
    private static function normalize_spacing_css_value( $value ) {
        $value = trim( (string) $value );
        if ( $value === '' ) {
            return '0px';
        }
        if ( preg_match( '/^\d+(\.\d+)?$/', $value ) ) {
            return $value . 'px';
        }
        return $value;
    }

    /**
     * Effective timeline icon padding for one side at a breakpoint (inherits tablet/desktop when empty).
     *
     * @param array  $spacing  timeline_icon_wrapper.decoration.spacing.
     * @param string $breakpoint desktop|tablet|phone.
     * @param string $side left|right.
     */
    private static function resolve_timeline_icon_padding_side( array $spacing, $breakpoint, $side ) {
        $pick = function ( $mode ) use ( $spacing, $side ) {
            $block = $spacing[ $mode ] ?? null;
            if ( ! is_array( $block ) ) {
                return '';
            }
            $padding = $block['value']['padding'] ?? null;
            if ( ! is_array( $padding ) || ! isset( $padding[ $side ] ) ) {
                return '';
            }
            $v = $padding[ $side ];
            return is_string( $v ) ? trim( $v ) : '';
        };
        if ( 'phone' === $breakpoint ) {
            $p = $pick( 'phone' );
            if ( $p !== '' ) {
                return static::normalize_spacing_css_value( $p );
            }
            $p = $pick( 'tablet' );
            if ( $p !== '' ) {
                return static::normalize_spacing_css_value( $p );
            }
            $p = $pick( 'desktop' );
            return $p !== '' ? static::normalize_spacing_css_value( $p ) : '15px';
        }
        if ( 'tablet' === $breakpoint ) {
            $p = $pick( 'tablet' );
            if ( $p !== '' ) {
                return static::normalize_spacing_css_value( $p );
            }
            $p = $pick( 'desktop' );
            return $p !== '' ? static::normalize_spacing_css_value( $p ) : '15px';
        }
        $p = $pick( 'desktop' );
        return $p !== '' ? static::normalize_spacing_css_value( $p ) : '15px';
    }

    public static function module_styles($args)
    {
        $attrs    = $args['attrs'] ?? [];
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class  = $args['orderClass'] ?? '';
        $order_number = preg_replace('/[^0-9]/', '', $order_class);
        $timeline_icon_spacing = ( ( $attrs['timeline_icon_wrapper'] ?? [] )['decoration'] ?? [] )['spacing'] ?? [];

        $use_icon = static::getAttrByMode($attrs, "use_icon", "off");
        $use_circle = static::getAttrByMode($attrs, "use_circle", "off");
        $use_circle_border = static::getAttrByMode($attrs, "use_circle_border", "off");
        $use_icon_font_size = static::getAttrByMode($attrs, "use_icon_font_size", "off");
        $use_timeline_icon = static::getAttrByMode($attrs, "ribbon_use_circle", "off");
        $custom_card_arrow = static::getAttrByMode($attrs, "custom_card_arrow", "off");
        $ribbon_use_circle = static::getAttrByMode($attrs, "ribbon_use_circle", "on");
        $ribbon_use_circle_border = static::getAttrByMode($attrs, "ribbon_use_circle_border", "off");
        $ribbon_use_icon_font_size = static::getAttrByMode($attrs, "ribbon_use_icon_font_size", "off");
        $timeline_icon_color_hover = isset($attrs['timeline_icon_color']['innerContent']['desktop']['hover']) ? $attrs['timeline_icon_color']['innerContent']['desktop']['hover'] : '';
        $ribbon_circle_color_hover = isset($attrs['ribbon_circle_color']['innerContent']['desktop']['hover']) ? $attrs['ribbon_circle_color']['innerContent']['desktop']['hover'] : '';
        $ribbon_circle_border_color_hover = isset($attrs['ribbon_circle_border_color']['innerContent']['desktop']['hover']) ? $attrs['ribbon_circle_border_color']['innerContent']['desktop']['hover'] : '';

        $icon_selector = ".dipi_horizontal_timeline $order_class .et-pb-icon";
        $timeline_icon_selector = ".dipi_horizontal_timeline $order_class .ribbon-ico";
        $timeline_icon_hover_selector = ".dipi_horizontal_timeline $order_class:hover .ribbon-ico,.dipi_horizontal_timeline $order_class .ribbon-ico.active";
        $timeline_icon_circle_selector = ".dipi_horizontal_timeline $order_class .ribbon-ico.ribbon-ico-circle";
        $timeline_icon_circle_hover_selector = ".dipi_horizontal_timeline $order_class:hover .ribbon-ico.ribbon-ico-circle,.dipi_horizontal_timeline $order_class .ribbon-ico.ribbon-ico-circle.active";
        $ribbon_text_selector = ".dipi_horizontal_timeline $order_class .dipi_timeline_ribbon .dipi_timeline_ribbon_text";
        $img_wrapper_selector = ".dipi_horizontal_timeline $order_class .dipi_htl_item_image .et_pb_image_wrap";

        $styles = [
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
                'attrName'   => 'title_font',
            ]),
            $elements->style([
                'attrName'   => 'desc_font',
            ]),
            $elements->style([
                'attrName'   => 'button',
            ]),
            $elements->style([
                'attrName'   => 'image',
            ]),
            $elements->style([
                'attrName'   => 'ribbon_text',
            ]),
            // Card margin and padding
            SpacingStyle::style([
                "selector" => ".dipi_horizontal_timeline $order_class .dipi_htl_item_card",
                "attr" => static::getAttr($attrs, "card_margin_padding"),
                "important" => true,
            ]),
            SpacingStyle::style([
                "selector" => ".dipi_horizontal_timeline $order_class .dipi_htl_item_card .dipi_htl_item_content",
                "attr" => static::getAttr($attrs, "card_content_padding"),
                "important" => true,
            ]),
            // Card width and max-width
            CommonStyle::style([
                'selector'            => ".dipi_horizontal_timeline $order_class .dipi_htl_item_card",
                'attr'                => static::getAttr($attrs, 'card_width', '100%'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "width: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => ".dipi_horizontal_timeline $order_class .dipi_htl_item_card",
                'attr'                => static::getAttr($attrs, 'card_max_width', '550px'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "max-width: $attrValue;";
                }
            ]),
            // Icon styles
            $use_icon_font_size === "on" ? CommonStyle::style([
                'selector'            => $icon_selector,
                'attr'                => static::getAttr($attrs, 'icon_font_size', '96px'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "font-size: $attrValue !important;";
                }
            ]) : null,
            CommonStyle::style([
                'selector'            => $icon_selector,
                'attr'                => static::getAttr($attrs, 'icon_color', '#000'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "color: $attrValue;";
                }
            ]),
            $use_circle === "on" ? CommonStyle::style([
                'selector'            => $icon_selector,
                'attr'                => static::getAttr($attrs, 'circle_color', '#eee'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "background-color: $attrValue;";
                }
            ]) : null,
            $use_circle === "on" ? CommonStyle::style([
                'selector'            => ".dipi_horizontal_timeline $order_class:hover .et-pb-icon",
                'attr'                => static::getAttr($attrs, 'icon_color', '#000'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return "color: $attrValue;";
                }
            ]) : null,
            $use_circle === "on" && $use_circle_border === "on" ? CommonStyle::style([
                'selector'            => $icon_selector,
                'attr'                => static::getAttr($attrs, 'circle_border_color', '#000'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "border-color: $attrValue;";
                }
            ]) : null,
            // Timeline icon styles
            $ribbon_use_icon_font_size === "on" ? CommonStyle::style([
                'selector'            => $timeline_icon_selector,
                'attr'                => static::getAttr($attrs, 'timeline_icon_font_size', '96px'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "font-size: $attrValue;";
                }
            ]) : null,
            CommonStyle::style([
                'selector'            => $timeline_icon_selector,
                'attr'                => static::getAttr($attrs, 'timeline_icon_color', '#2C3D49'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return "color: $attrValue;";
                }
            ]),
            CommonStyle::style([
                'selector'            => $timeline_icon_hover_selector,
                'attr'                => static::getAttr($attrs, 'timeline_icon_color'),
                'declarationFunction' => function ( array $args ) use ( $timeline_icon_color_hover ) {
                    return "color: $timeline_icon_color_hover;";
                }
            ]),
            $ribbon_use_circle === "on" ? CommonStyle::style([
                'selector'            => $timeline_icon_circle_selector,
                'attr'                => static::getAttr($attrs, 'ribbon_circle_color', '#F2F3F3'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return "background-color: $attrValue;";
                }
            ]) : null,
            $ribbon_use_circle === "on" && $ribbon_circle_color_hover !== '' ? CommonStyle::style([
                'selector'            => $timeline_icon_circle_hover_selector,
                'attr'                => static::getAttr($attrs, 'ribbon_circle_color'),
                'declarationFunction' => function ( array $args ) use ( $ribbon_circle_color_hover ) {
                    return  "background-color: {$ribbon_circle_color_hover}!important;";
                }
            ]) : null,
            $ribbon_use_circle === "on" && $ribbon_use_circle_border === "on" ? CommonStyle::style([
                'selector'            => $timeline_icon_circle_selector,
                'attr'                => static::getAttr($attrs, 'ribbon_circle_border_color', '#000'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "border-color: $attrValue;";
                }
            ]) : null,
            $ribbon_use_circle === "on" && $ribbon_use_circle_border === "on" && $ribbon_circle_border_color_hover !== '' ? CommonStyle::style([
                'selector'            => $timeline_icon_circle_hover_selector,
                'attr'                => static::getAttr($attrs, 'ribbon_circle_border_color'),
                'declarationFunction' => function ( array $args ) use ( $ribbon_circle_border_color_hover ) {
                    return  "border-color: {$ribbon_circle_border_color_hover}!important;";
                }
            ]) : null,
            // Ribbon text styles
            CommonStyle::style([
                'selector'            => $ribbon_text_selector,
                'attr'                => static::getAttr($attrs, 'ribbon_text_bgcolor', 'transparent'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "background-color: $attrValue;";
                }
            ]),
            // Timeline icon decoration spacing
            $elements->style([
                'attrName' => 'timeline_icon_wrapper',
            ]),
            // Spacing for image/icon wrapper
            SpacingStyle::style([
                "selector" => $img_wrapper_selector,
                "attr" => static::getAttr($attrs, "icon_image_margin_padding", null), //normal values won't have any effect here as this is a nested attribute
                "important" => true,
            ]),
            // Circle Icon Padding - explicit so it overrides static CSS (content icon .et-pb-icon only, not ribbon icon)
            isset($attrs['icon_settings']['decoration']['spacing']) ? SpacingStyle::style([
                "selector" => $icon_selector,
                "attr" => $attrs['icon_settings']['decoration']['spacing'],
                "important" => true,
            ]) : null,
            $elements->style([
                'attrName' => 'icon_settings',
            ]),
            BorderStyle::style([
                'selector' => $ribbon_text_selector,
                'attr'     => static::getAttr($attrs, 'ribbon_text'),
                'important' => true,
            ]),
            // Image max width
            CommonStyle::style([
                'selector'            => ".dipi_horizontal_timeline $order_class .dipi_htl_item_image .et_pb_image_wrap",
                'attr'                => static::getAttr($attrs, 'image_max_width', '100%'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    $is_px = strpos($attrValue, 'px') !== false;
                    $property = $is_px ? 'width' : 'max-width';
                    return  "$property: $attrValue;";
                }
            ]),
            // Icon font declarations
            CommonStyle::style([
                'selector'            => ".dipi_horizontal_timeline $order_class .dipi_timeline_font_icon",
                'attr'                => static::getAttr($attrs, 'font_icon'),
                'declarationFunction' => [ static::class, 'icon_font_declaration' ],
            ]),
            $custom_card_arrow === "on" ? CommonStyle::style([
                'selector'            => ".dipi_horizontal_timeline $order_class.dipi_htl_item_custom-card-arrow .dipi_htl_item_card-wrap:after",
                'attr'                => static::getAttr($attrs, 'card_arrow_color', '#eaebec'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return "border-top-color: $attrValue; border-bottom-color: $attrValue;";
                }
            ]) : null,
            $custom_card_arrow === "on" ? CommonStyle::style([
                'selector'            => ".dipi_horizontal_timeline $order_class.dipi_htl_item_custom-card-arrow .dipi_htl_item_card-wrap:after",
                'attr'                => static::getAttr($attrs, 'card_arrow_size', '12px'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return "border-width: $attrValue !important;";
                }
            ]) : null,
            // Timeline icon font (at end to match module-styles.tsx order)
            CommonStyle::style([
                'selector'            => $timeline_icon_selector,
                'attr'                => static::getAttr($attrs, 'timeline_icon'),
                'declarationFunction' => [ static::class, 'icon_font_declaration' ],
            ]),
            // Card arrow horizontal offset vs timeline icon padding (parent card_arrow_align start/end).
            CommonStyle::style([
                'selector'            => ".dipi_horizontal_timeline .dipi_timeline_card_arrow_start $order_class .dipi_htl_item_card-wrap:after",
                'attr'                => $timeline_icon_spacing,
                'declarationFunction' => function ( array $args ) use ( $timeline_icon_spacing ) {
                    if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'desktop' ) {
                        return '';
                    }
                    $left = static::resolve_timeline_icon_padding_side( $timeline_icon_spacing, 'desktop', 'left' );
                    return "left: {$left}; right: unset; transform: unset;";
                },
            ]),
            CommonStyle::style([
                'selector'            => ".et_pb_module.dipi_horizontal_timeline .dipi_timeline_card_arrow_start_tablet $order_class .dipi_htl_item_card-wrap:after",
                'attr'                => $timeline_icon_spacing,
                'declarationFunction' => function ( array $args ) use ( $timeline_icon_spacing ) {
                    if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'tablet' ) {
                        return '';
                    }
                    $left = static::resolve_timeline_icon_padding_side( $timeline_icon_spacing, 'tablet', 'left' );
                    return "left: {$left}; right: unset; transform: unset;";
                },
            ]),
            CommonStyle::style([
                'selector'            => "div.et_pb_module.dipi_horizontal_timeline .dipi_timeline_card_arrow_start_phone $order_class .dipi_htl_item_card-wrap:after",
                'attr'                => $timeline_icon_spacing,
                'declarationFunction' => function ( array $args ) use ( $timeline_icon_spacing ) {
                    if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'phone' ) {
                        return '';
                    }
                    $left = static::resolve_timeline_icon_padding_side( $timeline_icon_spacing, 'phone', 'left' );
                    return "left: {$left}; right: unset; transform: unset;";
                },
            ]),
            CommonStyle::style([
                'selector'            => ".dipi_horizontal_timeline .dipi_timeline_card_arrow_end $order_class .dipi_htl_item_card-wrap:after",
                'attr'                => $timeline_icon_spacing,
                'declarationFunction' => function ( array $args ) use ( $timeline_icon_spacing ) {
                    if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'desktop' ) {
                        return '';
                    }
                    $right = static::resolve_timeline_icon_padding_side( $timeline_icon_spacing, 'desktop', 'right' );
                    return "right: {$right}; left: unset; transform: unset;";
                },
            ]),
            CommonStyle::style([
                'selector'            => ".et_pb_module.dipi_horizontal_timeline .dipi_timeline_card_arrow_end_tablet $order_class .dipi_htl_item_card-wrap:after",
                'attr'                => $timeline_icon_spacing,
                'declarationFunction' => function ( array $args ) use ( $timeline_icon_spacing ) {
                    if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'tablet' ) {
                        return '';
                    }
                    $right = static::resolve_timeline_icon_padding_side( $timeline_icon_spacing, 'tablet', 'right' );
                    return "right: {$right}; left: unset; transform: unset;";
                },
            ]),
            CommonStyle::style([
                'selector'            => "div.et_pb_module.dipi_horizontal_timeline .dipi_timeline_card_arrow_end_phone $order_class .dipi_htl_item_card-wrap:after",
                'attr'                => $timeline_icon_spacing,
                'declarationFunction' => function ( array $args ) use ( $timeline_icon_spacing ) {
                    if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'phone' ) {
                        return '';
                    }
                    $right = static::resolve_timeline_icon_padding_side( $timeline_icon_spacing, 'phone', 'right' );
                    return "right: {$right}; left: unset; transform: unset;";
                },
            ]),
        ];

        Style::add(
            [
                'id'            => $args['id'],
                'name'          => $args['name'],
                'orderIndex'    => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles'        => array_filter($styles),
            ]
        );
    }
}

