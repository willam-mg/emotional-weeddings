<?php
namespace DIPI\Modules\HorizontalTimeline;

if (!defined("ABSPATH")) {
    die("Direct access forbidden.");
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Spacing\SpacingStyle;
use DIPI\Modules\Base\Swiper\SwiperStylesTrait;

trait ModuleStylesTrait
{
    use CustomCssTrait;
    use StyleDeclarationTrait;
    use SwiperStylesTrait;

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
     * Parse a value like "60px" or "12" to a numeric value for calculations.
     * Returns [ 'num' => float|false, 'raw' => string ] to match TSX behavior.
     */
    private static function parsePxValue($attrValue)
    {
        $raw = (string) ($attrValue ?? '');
        $cleaned = trim(str_replace('px', '', $raw));
        $num = $cleaned !== '' && is_numeric($cleaned) ? (float) $cleaned : false;
        return ['num' => $num, 'raw' => $raw];
    }

    public static function module_styles($args)
    {
        $attrs    = $args['attrs'] ?? [];
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class  = $args['orderClass'] ?? '';
        $order_number = preg_replace('/[^0-9]/', '', $order_class);

        $use_active_line = static::getAttrByMode($attrs, "use_active_line", "on");
        $show_card_arrow = static::getAttrByMode($attrs, "show_card_arrow", "on");

        $flex_alignments = [
            'start'  => 'flex-start',
            'center' => 'center',
            'end'    => 'flex-end',
        ];

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
            // Timeline item (card border & box shadow).
            $elements->style([
                'attrName' => 'item',
            ]),
            // Button (timeline item buttons).
            $elements->style([
                'attrName' => 'button',
            ]),
            // Timeline text (Design > Timeline Text): Title, Description, Ribbon.
            $elements->style([
                'attrName' => 'title_font',
            ]),
            $elements->style([
                'attrName' => 'desc_font',
            ]),
            $elements->style([
                'attrName' => 'ribbon_font',
            ]),
            CssStyle::style([
                'selector'  => $args['orderClass'],
                'attr'      => $attrs['css'] ?? [],
                'cssFields' => static::custom_css(),
            ]),
            SpacingStyle::style([
                "selector" => "$order_class .dipi_htl_container",
                "attr" => static::getAttr($attrs, "container_padding"),
                "important" => true,
            ]),
            // Card arrow alignment: positions timeline icon and card arrow (D4: .dipi_htl_item_container align-items)
            CommonStyle::style([
                'selector'            => "$order_class .dipi_horizontal_timeline_item .dipi_htl_item_container",
                'attr'                => static::getAttr($attrs, 'card_arrow_align', 'center'),
                'declarationFunction' => function ( array $args ) use ( $flex_alignments ) {
                    $attrValue = $args['attrValue'] ?? 'center';
                    $align = is_array($attrValue) && isset($attrValue['value']) ? $attrValue['value'] : $attrValue;
                    $align = $align ?: 'center';
                    $flex = isset($flex_alignments[ $align ]) ? $flex_alignments[ $align ] : $align;
                    return 'align-items: ' . $flex . ';';
                }
            ]),
            // Line area size - height
            CommonStyle::style([
                'selector'            => "$order_class .dipi_horizontal_timeline_item .ribbon-ico-wrap, $order_class .dipi_htl_layout_bottom .dipi_horizontal_timeline_item .ribbon-ico-wrap, $order_class .dipi_htl_layout_top .dipi_horizontal_timeline_item .ribbon-ico-wrap",
                'attr'                => static::getAttr($attrs, 'line_area_size', '60px'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "height: $attrValue;";
                }
            ]),
            // Timeline item - text alignment (module.advanced.text orientation)
            CommonStyle::style([
                'selector'            => "$order_class .dipi_horizontal_timeline_item",
                'attr'                => $attrs['module']['advanced']['text']['text'] ?? [],
                'declarationFunction' => function ( array $args ) {
                    $textOrientation = $args['attrValue']['orientation'] ?? 'center';
                    return "text-align: $textOrientation;";
                }
            ]),
            // Line area size - max-height
            CommonStyle::style([
                'selector'            => "$order_class .dipi_horizontal_timeline_item .ribbon-ico-wrap, $order_class .dipi_htl_layout_bottom .dipi_horizontal_timeline_item .ribbon-ico-wrap, $order_class .dipi_htl_layout_top .dipi_horizontal_timeline_item .ribbon-ico-wrap",
                'attr'                => static::getAttr($attrs, 'line_area_size', '60px'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "max-height: $attrValue !important;";
                }
            ]),
            // Line area size - top position for bottom layout (desktop only)
            CommonStyle::style([
                'selector'            => "$order_class .dipi_htl_layout_bottom .swiper-arrow-button, $order_class .dipi_htl_layout_bottom .dipi-htl-line__active, $order_class .dipi_htl_layout_bottom .dipi-htl-line",
                'attr'                => static::getAttr($attrs, 'line_area_size', '60px'),
                'declarationFunction' => function ( array $args ) {
                    if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'desktop' ) {
                        return '';
                    }
                    $parsed = static::parsePxValue($args['attrValue'] ?? '');
                    $value = $parsed['num'] !== false ? ($parsed['num'] * 0.5) . 'px' : $parsed['raw'];
                    return "top: $value;";
                }
            ]),
            // Line area size - top position for bottom layout (tablet only)
            CommonStyle::style([
                'selector'            => "$order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_bottom_tablet .swiper-arrow-button, $order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_bottom_tablet .dipi_htl_container .dipi-htl-line__active, $order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_bottom_tablet .dipi_htl_container .dipi-htl-line",
                'attr'                => static::getAttr($attrs, 'line_area_size', '60px'),
                'declarationFunction' => function ( array $args ) {
                    if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'tablet' ) {
                        return '';
                    }
                    $parsed = static::parsePxValue($args['attrValue'] ?? '');
                    $value = $parsed['num'] !== false ? ($parsed['num'] * 0.5) . 'px' : $parsed['raw'];
                    return "top: $value;";
                },
            ]),
            // Line area size - bottom position for top layout (desktop only)
            CommonStyle::style([
                'selector'            => "$order_class .dipi_htl_layout_top .swiper-arrow-button, $order_class .dipi_htl_layout_top .dipi-htl-line__active, $order_class .dipi_htl_layout_top .dipi-htl-line",
                'attr'                => static::getAttr($attrs, 'line_area_size', '60px'),
                'declarationFunction' => function ( array $args ) {
                    if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'desktop' ) {
                        return '';
                    }
                    $parsed = static::parsePxValue($args['attrValue'] ?? '');
                    $value = $parsed['num'] !== false ? ($parsed['num'] * 0.5) . 'px' : $parsed['raw'];
                    return "bottom: $value;";
                }
            ]),
            // Line area size - bottom position for top layout (tablet only)
            CommonStyle::style([
                'selector'            => "$order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_top_tablet .swiper-arrow-button, $order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_top_tablet .dipi_htl_container .dipi-htl-line__active, $order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_top_tablet .dipi_htl_container .dipi-htl-line",
                'attr'                => static::getAttr($attrs, 'line_area_size', '60px'),
                'declarationFunction' => function ( array $args ) {
                    if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'tablet' ) {
                        return '';
                    }
                    $parsed = static::parsePxValue($args['attrValue'] ?? '');
                    $value = $parsed['num'] !== false ? ($parsed['num'] * 0.5) . 'px' : $parsed['raw'];
                    return "bottom: $value;";
                },
            ]),
            // Line area size - top position for bottom layout (phone only)
            CommonStyle::style([
                'selector'            => "$order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_bottom_phone .swiper-arrow-button, $order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_bottom_phone .dipi_htl_container .dipi-htl-line__active, $order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_bottom_phone .dipi_htl_container .dipi-htl-line",
                'attr'                => static::getAttr($attrs, 'line_area_size', '60px'),
                'declarationFunction' => function ( array $args ) {
                    if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'phone' ) {
                        return '';
                    }
                    $parsed = static::parsePxValue($args['attrValue'] ?? '');
                    $value = $parsed['num'] !== false ? ($parsed['num'] * 0.5) . 'px' : $parsed['raw'];
                    return "top: $value;";
                },
            ]),
            // Line area size - bottom position for top layout (phone only)
            CommonStyle::style([
                'selector'            => "$order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_top_phone .swiper-arrow-button, $order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_top_phone .dipi_htl_container .dipi-htl-line__active, $order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_top_phone .dipi_htl_container .dipi-htl-line",
                'attr'                => static::getAttr($attrs, 'line_area_size', '60px'),
                'declarationFunction' => function ( array $args ) {
                    if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'phone' ) {
                        return '';
                    }
                    $parsed = static::parsePxValue($args['attrValue'] ?? '');
                    $value = $parsed['num'] !== false ? ($parsed['num'] * 0.5) . 'px' : $parsed['raw'];
                    return "bottom: $value;";
                },
            ]),
            // Timeline line - border width
            CommonStyle::style([
                'selector'            => "$order_class .dipi-htl-line",
                'attr'                => static::getAttr($attrs, 'timeline_line_width', '2px'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "border-width: $attrValue;";
                }
            ]),
            // Timeline line - border color
            CommonStyle::style([
                'selector'            => "$order_class .dipi-htl-line",
                'attr'                => static::getAttr($attrs, 'timeline_line_color', '#F2F3F3'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "border-color: $attrValue;";
                }
            ]),
            // Timeline line - border style
            CommonStyle::style([
                'selector'            => "$order_class .dipi-htl-line",
                'attr'                => static::getAttr($attrs, 'timeline_line_style', 'solid'),
                'declarationFunction' => function ( array $args ) {
                    $attrValue = $args['attrValue'];
                    return  "border-style: $attrValue;";
                }
            ]),
            // Timeline line - transform for top layout (desktop only)
            CommonStyle::style([
                'selector'            => "$order_class .dipi_htl_layout_top .dipi-htl-line",
                'attr'                => static::getAttr($attrs, 'timeline_line_width', '2px'),
                'declarationFunction' => function ( array $args ) {
                    if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'desktop' ) {
                        return '';
                    }
                    $parsed = static::parsePxValue($args['attrValue'] ?? '');
                    $value = $parsed['num'] !== false ? 'translateY(' . ($parsed['num'] * 0.5) . 'px)' : 'translateY(' . $parsed['raw'] . ')';
                    return "transform: $value;";
                }
            ]),
            // Timeline line - transform for top layout (tablet only)
            CommonStyle::style([
                'selector'            => "$order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_top_tablet .dipi-htl-line",
                'attr'                => static::getAttr($attrs, 'timeline_line_width', '2px'),
                'declarationFunction' => function ( array $args ) {
                    if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'tablet' ) {
                        return '';
                    }
                    $parsed = static::parsePxValue($args['attrValue'] ?? '');
                    $value = $parsed['num'] !== false ? 'translateY(' . ($parsed['num'] * 0.5) . 'px)' : 'translateY(' . $parsed['raw'] . ')';
                    return "transform: $value;";
                },
            ]),
            // Timeline line - transform for bottom layout (desktop only)
            CommonStyle::style([
                'selector'            => "$order_class .dipi_htl_layout_bottom .dipi-htl-line",
                'attr'                => static::getAttr($attrs, 'timeline_line_width', '2px'),
                'declarationFunction' => function ( array $args ) {
                    if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'desktop' ) {
                        return '';
                    }
                    $parsed = static::parsePxValue($args['attrValue'] ?? '');
                    $value = $parsed['num'] !== false ? 'translateY(-' . ($parsed['num'] * 0.5) . 'px)' : 'translateY(-' . $parsed['raw'] . ')';
                    return "transform: $value;";
                }
            ]),
            // Timeline line - transform for bottom layout (tablet only)
            CommonStyle::style([
                'selector'            => "$order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_bottom_tablet .dipi-htl-line",
                'attr'                => static::getAttr($attrs, 'timeline_line_width', '2px'),
                'declarationFunction' => function ( array $args ) {
                    if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'tablet' ) {
                        return '';
                    }
                    $parsed = static::parsePxValue($args['attrValue'] ?? '');
                    $value = $parsed['num'] !== false ? 'translateY(-' . ($parsed['num'] * 0.5) . 'px)' : 'translateY(-' . $parsed['raw'] . ')';
                    return "transform: $value;";
                },
            ]),
            // Timeline line - transform for top layout (phone only)
            CommonStyle::style([
                'selector'            => "$order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_top_phone .dipi-htl-line",
                'attr'                => static::getAttr($attrs, 'timeline_line_width', '2px'),
                'declarationFunction' => function ( array $args ) {
                    if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'phone' ) {
                        return '';
                    }
                    $parsed = static::parsePxValue($args['attrValue'] ?? '');
                    $value = $parsed['num'] !== false ? 'translateY(' . ($parsed['num'] * 0.5) . 'px)' : 'translateY(' . $parsed['raw'] . ')';
                    return "transform: $value;";
                },
            ]),
            // Timeline line - transform for bottom layout (phone only)
            CommonStyle::style([
                'selector'            => "$order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_bottom_phone .dipi-htl-line",
                'attr'                => static::getAttr($attrs, 'timeline_line_width', '2px'),
                'declarationFunction' => function ( array $args ) {
                    if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'phone' ) {
                        return '';
                    }
                    $parsed = static::parsePxValue($args['attrValue'] ?? '');
                    $value = $parsed['num'] !== false ? 'translateY(-' . ($parsed['num'] * 0.5) . 'px)' : 'translateY(-' . $parsed['raw'] . ')';
                    return "transform: $value;";
                },
            ]),
        ];

        // Active line styles (if enabled)
        if ($use_active_line === "on") {
            $active_line_styles = [
                // Active line - border width
                CommonStyle::style([
                    'selector'            => "$order_class .dipi-htl-line__active",
                    'attr'                => static::getAttr($attrs, 'timeline_active_line_width', '2px'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "border-width: $attrValue;";
                    }
                ]),
                // Active line - border color
                CommonStyle::style([
                    'selector'            => "$order_class .dipi-htl-line__active",
                    'attr'                => static::getAttr($attrs, 'timeline_active_line_color', '#2C3D49'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "border-color: $attrValue;";
                    }
                ]),
                // Active line - border style
                CommonStyle::style([
                    'selector'            => "$order_class .dipi-htl-line__active",
                    'attr'                => static::getAttr($attrs, 'timeline_active_line_style', 'solid'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "border-style: $attrValue;";
                    }
                ]),
                // Active line - transform for top layout (desktop only)
                CommonStyle::style([
                    'selector'            => "$order_class .dipi_htl_layout_top .dipi-htl-line__active",
                    'attr'                => static::getAttr($attrs, 'timeline_active_line_width', '2px'),
                    'declarationFunction' => function ( array $args ) {
                        if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'desktop' ) {
                            return '';
                        }
                        $parsed = static::parsePxValue($args['attrValue'] ?? '');
                        $value = $parsed['num'] !== false ? 'translateY(' . ($parsed['num'] * 0.5) . 'px)' : 'translateY(' . $parsed['raw'] . ')';
                        return "transform: $value;";
                    }
                ]),
                // Active line - transform for top layout (tablet only)
                CommonStyle::style([
                    'selector'            => "$order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_top_tablet .dipi-htl-line__active",
                    'attr'                => static::getAttr($attrs, 'timeline_active_line_width', '2px'),
                    'declarationFunction' => function ( array $args ) {
                        if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'tablet' ) {
                            return '';
                        }
                        $parsed = static::parsePxValue($args['attrValue'] ?? '');
                        $value = $parsed['num'] !== false ? 'translateY(' . ($parsed['num'] * 0.5) . 'px)' : 'translateY(' . $parsed['raw'] . ')';
                        return "transform: $value;";
                    },
                ]),
                // Active line - transform for bottom layout (desktop only)
                CommonStyle::style([
                    'selector'            => "$order_class .dipi_htl_layout_bottom .dipi-htl-line__active",
                    'attr'                => static::getAttr($attrs, 'timeline_active_line_width', '2px'),
                    'declarationFunction' => function ( array $args ) {
                        if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'desktop' ) {
                            return '';
                        }
                        $parsed = static::parsePxValue($args['attrValue'] ?? '');
                        $value = $parsed['num'] !== false ? 'translateY(-' . ($parsed['num'] * 0.5) . 'px)' : 'translateY(-' . $parsed['raw'] . ')';
                        return "transform: $value;";
                    }
                ]),
                // Active line - transform for bottom layout (tablet only)
                CommonStyle::style([
                    'selector'            => "$order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_bottom_tablet .dipi-htl-line__active",
                    'attr'                => static::getAttr($attrs, 'timeline_active_line_width', '2px'),
                    'declarationFunction' => function ( array $args ) {
                        if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'tablet' ) {
                            return '';
                        }
                        $parsed = static::parsePxValue($args['attrValue'] ?? '');
                        $value = $parsed['num'] !== false ? 'translateY(-' . ($parsed['num'] * 0.5) . 'px)' : 'translateY(-' . $parsed['raw'] . ')';
                        return "transform: $value;";
                    },
                ]),
                // Active line - transform for top layout (phone only)
                CommonStyle::style([
                    'selector'            => "$order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_top_phone .dipi-htl-line__active",
                    'attr'                => static::getAttr($attrs, 'timeline_active_line_width', '2px'),
                    'declarationFunction' => function ( array $args ) {
                        if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'phone' ) {
                            return '';
                        }
                        $parsed = static::parsePxValue($args['attrValue'] ?? '');
                        $value = $parsed['num'] !== false ? 'translateY(' . ($parsed['num'] * 0.5) . 'px)' : 'translateY(' . $parsed['raw'] . ')';
                        return "transform: $value;";
                    },
                ]),
                // Active line - transform for bottom layout (phone only)
                CommonStyle::style([
                    'selector'            => "$order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_layout_bottom_phone .dipi-htl-line__active",
                    'attr'                => static::getAttr($attrs, 'timeline_active_line_width', '2px'),
                    'declarationFunction' => function ( array $args ) {
                        if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'phone' ) {
                            return '';
                        }
                        $parsed = static::parsePxValue($args['attrValue'] ?? '');
                        $value = $parsed['num'] !== false ? 'translateY(-' . ($parsed['num'] * 0.5) . 'px)' : 'translateY(-' . $parsed['raw'] . ')';
                        return "transform: $value;";
                    },
                ]),
            ];
            $styles = array_merge($styles, $active_line_styles);
        }

        // Card arrow styles (if enabled)
        if ($show_card_arrow === "on") {
            $card_arrow_styles = [
                // Card arrow - border bottom color
                CommonStyle::style([
                    'selector'            => "$order_class .dipi_htl_item_card-wrap:after",
                    'attr'                => static::getAttr($attrs, 'card_arrow_color', '#F2F3F3'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "border-bottom-color: $attrValue;";
                    }
                ]),
                // Card arrow - border top color
                CommonStyle::style([
                    'selector'            => "$order_class .dipi_htl_item_card-wrap:after",
                    'attr'                => static::getAttr($attrs, 'card_arrow_color', '#F2F3F3'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "border-top-color: $attrValue;";
                    }
                ]),
                // Card arrow - border width
                CommonStyle::style([
                    'selector'            => "$order_class .dipi_htl_item_card-wrap:after",
                    'attr'                => static::getAttr($attrs, 'card_arrow_size', '12px'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return "border-width: $attrValue !important;";
                    }
                ]),
                // Card arrow - top position for bottom layout (desktop only)
                CommonStyle::style([
                    'selector'            => "$order_class .dipi_htl_layout_bottom .dipi_htl_container .dipi_horizontal_timeline_item .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class .dipi-carousel-main:not(.dipi_htl_loop_on) .dipi_htl_layout_mixed.startpos-bottom .dipi_htl_container .dipi_horizontal_timeline_item:nth-child(odd) .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class .dipi_htl_loop_on .dipi_htl_layout_mixed.startpos-bottom .dipi_htl_container .dipi_horizontal_timeline_item[data-htl-parity=\"even\"] .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class .dipi-carousel-main:not(.dipi_htl_loop_on) .dipi_htl_layout_mixed.startpos-top .dipi_htl_container .dipi_horizontal_timeline_item:nth-child(even) .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class .dipi_htl_loop_on .dipi_htl_layout_mixed.startpos-top .dipi_htl_container .dipi_horizontal_timeline_item[data-htl-parity=\"odd\"] .dipi_htl_item_container .dipi_htl_item_card-wrap:after",
                    'attr'                => static::getAttr($attrs, 'card_arrow_size', '12px'),
                    'declarationFunction' => function ( array $args ) {
                        if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'desktop' ) {
                            return '';
                        }
                        $parsed = static::parsePxValue($args['attrValue'] ?? '');
                        $value = $parsed['num'] !== false ? '-' . $parsed['num'] . 'px' : '-' . $parsed['raw'];
                        return "top: $value;";
                    }
                ]),
                // Card arrow - top position for bottom layout (tablet only)
                CommonStyle::style([
                    'selector'            => "$order_class.dipi_horizontal_timeline.et_pb_module .dipi-carousel-main:not(.dipi_htl_loop_on) .dipi_htl_layout_bottom_tablet .dipi_htl_container .dipi-htl-items .dipi_horizontal_timeline_item:nth-child(odd) .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_loop_on .dipi_htl_layout_bottom_tablet .dipi_htl_container .dipi-htl-items .dipi_horizontal_timeline_item[data-htl-parity=\"odd\"] .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class.dipi_horizontal_timeline.et_pb_module .dipi-carousel-main:not(.dipi_htl_loop_on) .dipi_htl_layout_bottom_tablet .dipi_htl_container .dipi-htl-items .dipi_horizontal_timeline_item:nth-child(even) .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_loop_on .dipi_htl_layout_bottom_tablet .dipi_htl_container .dipi-htl-items .dipi_horizontal_timeline_item[data-htl-parity=\"even\"] .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class.dipi_horizontal_timeline.et_pb_module .dipi-carousel-main:not(.dipi_htl_loop_on) .dipi_htl_layout_mixed_tablet.startpos-bottom .dipi_htl_container .dipi-htl-items .dipi_horizontal_timeline_item:nth-child(odd) .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_loop_on .dipi_htl_layout_mixed_tablet.startpos-bottom .dipi_htl_container .dipi-htl-items .dipi_horizontal_timeline_item[data-htl-parity=\"even\"] .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class.dipi_horizontal_timeline.et_pb_module .dipi-carousel-main:not(.dipi_htl_loop_on) .dipi_htl_layout_mixed_tablet.startpos-top .dipi_htl_container .dipi-htl-items .dipi_horizontal_timeline_item:nth-child(even) .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_loop_on .dipi_htl_layout_mixed_tablet.startpos-top .dipi_htl_container .dipi-htl-items .dipi_horizontal_timeline_item[data-htl-parity=\"odd\"] .dipi_htl_item_container .dipi_htl_item_card-wrap:after",
                    'attr'                => static::getAttr($attrs, 'card_arrow_size', '12px'),
                    'declarationFunction' => function ( array $args ) {
                        if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'tablet' ) {
                            return '';
                        }
                        $parsed = static::parsePxValue($args['attrValue'] ?? '');
                        $value = $parsed['num'] !== false ? '-' . $parsed['num'] . 'px' : '-' . $parsed['raw'];
                        return "top: $value;";
                    },
                ]),
                // Card arrow - bottom position for top layout (desktop only)
                CommonStyle::style([
                    'selector'            => "$order_class .dipi_htl_layout_top .dipi_htl_container .dipi_horizontal_timeline_item .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class .dipi-carousel-main:not(.dipi_htl_loop_on) .dipi_htl_layout_mixed.startpos-bottom .dipi_htl_container .dipi_horizontal_timeline_item:nth-child(even) .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class .dipi_htl_loop_on .dipi_htl_layout_mixed.startpos-bottom .dipi_htl_container .dipi_horizontal_timeline_item[data-htl-parity=\"odd\"] .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class .dipi-carousel-main:not(.dipi_htl_loop_on) .dipi_htl_layout_mixed.startpos-top .dipi_htl_container .dipi_horizontal_timeline_item:nth-child(odd) .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class .dipi_htl_loop_on .dipi_htl_layout_mixed.startpos-top .dipi_htl_container .dipi_horizontal_timeline_item[data-htl-parity=\"even\"] .dipi_htl_item_container .dipi_htl_item_card-wrap:after",
                    'attr'                => static::getAttr($attrs, 'card_arrow_size', '12px'),
                    'declarationFunction' => function ( array $args ) {
                        if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'desktop' ) {
                            return '';
                        }
                        $parsed = static::parsePxValue($args['attrValue'] ?? '');
                        $value = $parsed['num'] !== false ? '-' . $parsed['num'] . 'px' : '-' . $parsed['raw'];
                        return "bottom: $value;";
                    }
                ]),
                // Card arrow - bottom position for top layout (tablet only)
                CommonStyle::style([
                    'selector'            => "$order_class.dipi_horizontal_timeline.et_pb_module .dipi-carousel-main:not(.dipi_htl_loop_on) .dipi_htl_layout_top_tablet .dipi_htl_container .dipi-htl-items .dipi_horizontal_timeline_item:nth-child(even) .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_loop_on .dipi_htl_layout_top_tablet .dipi_htl_container .dipi-htl-items .dipi_horizontal_timeline_item[data-htl-parity=\"even\"] .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class.dipi_horizontal_timeline.et_pb_module .dipi-carousel-main:not(.dipi_htl_loop_on) .dipi_htl_layout_top_tablet .dipi_htl_container .dipi-htl-items .dipi_horizontal_timeline_item:nth-child(odd) .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_loop_on .dipi_htl_layout_top_tablet .dipi_htl_container .dipi-htl-items .dipi_horizontal_timeline_item[data-htl-parity=\"odd\"] .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class.dipi_horizontal_timeline.et_pb_module .dipi-carousel-main:not(.dipi_htl_loop_on) .dipi_htl_layout_mixed_tablet.startpos-bottom .dipi_htl_container .dipi-htl-items .dipi_horizontal_timeline_item:nth-child(even) .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_loop_on .dipi_htl_layout_mixed_tablet.startpos-bottom .dipi_htl_container .dipi-htl-items .dipi_horizontal_timeline_item[data-htl-parity=\"odd\"] .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class.dipi_horizontal_timeline.et_pb_module .dipi-carousel-main:not(.dipi_htl_loop_on) .dipi_htl_layout_mixed_tablet.startpos-top .dipi_htl_container .dipi-htl-items .dipi_horizontal_timeline_item:nth-child(odd) .dipi_htl_item_container .dipi_htl_item_card-wrap:after, $order_class.dipi_horizontal_timeline.et_pb_module .dipi_htl_loop_on .dipi_htl_layout_mixed_tablet.startpos-top .dipi_htl_container .dipi-htl-items .dipi_horizontal_timeline_item[data-htl-parity=\"even\"] .dipi_htl_item_container .dipi_htl_item_card-wrap:after",
                    'attr'                => static::getAttr($attrs, 'card_arrow_size', '12px'),
                    'declarationFunction' => function ( array $args ) {
                        if ( ( $args['breakpoint'] ?? 'desktop' ) !== 'tablet' ) {
                            return '';
                        }
                        $parsed = static::parsePxValue($args['attrValue'] ?? '');
                        $value = $parsed['num'] !== false ? '-' . $parsed['num'] . 'px' : '-' . $parsed['raw'];
                        return "bottom: $value;";
                    },
                ]),
            ];
            $styles = array_merge($styles, $card_arrow_styles);
        }

        // Add Swiper styles
        $swiperStyles = static::swiper_module_styles($args);
        $styles = array_merge($styles, $swiperStyles);

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

