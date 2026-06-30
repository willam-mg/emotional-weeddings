<?php
namespace DIPI\Modules\Timeline;

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

        $text_align = isset($attrs['module']['advanced']['text']['text']['desktop']['value']['orientation']) ? $attrs['module']['advanced']['text']['text']['desktop']['value']['orientation'] : 'left';
        $text_align_tablet = isset($attrs['module']['advanced']['text']['text']['tablet']['value']['orientation']) ? $attrs['module']['advanced']['text']['text']['tablet']['value']['orientation'] : $text_align;
        $text_align_phone = isset($attrs['module']['advanced']['text']['text']['phone']['value']['orientation']) ? $attrs['module']['advanced']['text']['text']['phone']['value']['orientation'] : $text_align_tablet;
        $icon_alignment_selector = "$order_class .dipi_timeline_item_image";
        $image_alignment_selector = "$order_class .dipi_timeline_item_image .et_pb_image_wrap";
    
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
                        'selector'            => "$order_class .dipi_timeline_item .ribbon-icon-wrap, $order_class .dipi_timeline_layout_right .dipi_timeline_item .ribbon-icon-wrap, $order_class.dipi_timeline .dipi_timeline_layout_left .dipi_timeline_item .ribbon-icon-wrap",
                        'attr'                => static::getAttr($attrs, 'line_area_size'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "width: $attrValue;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_item .ribbon-icon-wrap, $order_class .dipi_timeline_layout_right .dipi_timeline_item .ribbon-icon-wrap, $order_class.dipi_timeline .dipi_timeline_layout_left .dipi_timeline_item .ribbon-icon-wrap",
                        'attr'                => static::getAttr($attrs, 'line_area_size'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "max-width: $attrValue;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_right .dipi-timeline-line__active, $order_class .dipi_timeline_layout_right .dipi-timeline-line",
                        'attr'                => static::getAttr($attrs, 'line_area_size'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "desktop" ? "left: calc(1rem + {$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_right_tablet .dipi-timeline-line__active, $order_class .dipi_timeline_layout_right_tablet .dipi-timeline-line",
                        'attr'                => static::getAttr($attrs, 'line_area_size'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "tablet" ? "left: calc(1rem + {$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_right_phone .dipi-timeline-line__active, $order_class .dipi_timeline_layout_right_phone .dipi-timeline-line",
                        'attr'                => static::getAttr($attrs, 'line_area_size'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "phone" ? "left: calc(1rem + {$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_left .dipi-timeline-line__active, $order_class .dipi_timeline_layout_left .dipi-timeline-line",
                        'attr'                => static::getAttr($attrs, 'line_area_size'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "desktop" ? "right: calc(1rem + {$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_left_tablet .dipi-timeline-line__active, $order_class .dipi_timeline_layout_left_tablet .dipi-timeline-line",
                        'attr'                => static::getAttr($attrs, 'line_area_size'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "tablet" ? "right: calc(1rem + {$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_left_phone .dipi-timeline-line__active, $order_class .dipi_timeline_layout_left_phone .dipi-timeline-line",
                        'attr'                => static::getAttr($attrs, 'line_area_size'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "phone" ? "right: calc(1rem + {$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_left .dipi-timeline-line",
                        'attr'                => static::getAttr($attrs, 'timeline_line_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "desktop" ? "transform: translateX({$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_left_tablet .dipi-timeline-line",
                        'attr'                => static::getAttr($attrs, 'timeline_line_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "tablet" ? "transform: translateX({$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_left_phone .dipi-timeline-line",
                        'attr'                => static::getAttr($attrs, 'timeline_line_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "phone" ? "transform: translateX({$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_right .dipi-timeline-line",
                        'attr'                => static::getAttr($attrs, 'timeline_line_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "desktop" ? "transform: translateX({$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_right_tablet .dipi-timeline-line",
                        'attr'                => static::getAttr($attrs, 'timeline_line_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "tablet" ? "transform: translateX({$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_right_phone .dipi-timeline-line",
                        'attr'                => static::getAttr($attrs, 'timeline_line_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "phone" ? "transform: translateX({$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi-timeline-line",
                        'attr'                => static::getAttr($attrs, 'timeline_line_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return "border-width: {$attrValue};";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi-timeline-line",
                        'attr'                => static::getAttr($attrs, 'timeline_line_color'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return "border-color: {$attrValue};";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi-timeline-line",
                        'attr'                => static::getAttr($attrs, 'timeline_line_style'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return "border-style: {$attrValue};";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi-timeline-line__active",
                        'attr'                => static::getAttr($attrs, 'timeline_active_line_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return "border-width: {$attrValue};";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi-timeline-line__active",
                        'attr'                => static::getAttr($attrs, 'timeline_active_line_color'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return "border-color: {$attrValue};";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi-timeline-line__active",
                        'attr'                => static::getAttr($attrs, 'timeline_active_line_style'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return "border-style: {$attrValue};";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_left .dipi-timeline-line__active",
                        'attr'                => static::getAttr($attrs, 'timeline_active_line_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "desktop" ? "transform: translateX({$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_left_tablet .dipi-timeline-line__active",
                        'attr'                => static::getAttr($attrs, 'timeline_active_line_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "tablet" ? "transform: translateX({$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_left_phone .dipi-timeline-line__active",
                        'attr'                => static::getAttr($attrs, 'timeline_active_line_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "phone" ? "transform: translateX({$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_right .dipi-timeline-line",
                        'attr'                => static::getAttr($attrs, 'timeline_line_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "desktop" ? "transform: translateX(-{$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_right_tablet .dipi-timeline-line",
                        'attr'                => static::getAttr($attrs, 'timeline_line_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "tablet" ? "transform: translateX(-{$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_right_phone .dipi-timeline-line",
                        'attr'                => static::getAttr($attrs, 'timeline_line_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "phone" ? "transform: translateX(-{$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_right .dipi-timeline-line__active",
                        'attr'                => static::getAttr($attrs, 'timeline_active_line_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "desktop" ? "transform: translateX(-{$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_right_tablet .dipi-timeline-line__active",
                        'attr'                => static::getAttr($attrs, 'timeline_active_line_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "tablet" ? "transform: translateX(-{$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_right_phone .dipi-timeline-line__active",
                        'attr'                => static::getAttr($attrs, 'timeline_active_line_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'] / 2;
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "phone" ? "transform: translateX(-{$attrValue}px);" : "";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_right .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item .dipi_timeline_item_container .dipi_timeline_item_card:after,
                        $order_class .dipi_timeline_layout_mixed.startpos-right .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(odd) .dipi_timeline_item_container .dipi_timeline_item_card:after,
                        $order_class .dipi_timeline_layout_mixed.startpos-left .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(even) .dipi_timeline_item_container .dipi_timeline_item_card:after",
                        'attr'                => static::getAttr($attrs, 'card_arrow_size'),
                        'declarationFunction' => function ( array $args ) use ( $attrs ) {
                            $attrValue = $args['attrValue'];
                            $breakpoint = $args['breakpoint'];
                            if ( $breakpoint !== "desktop" ) {
                                return "";
                            }
                            $color = static::getAttrByMode( $attrs, 'card_arrow_color', '#F2F3F3', 'desktop' );
                            return "left: -{$attrValue}; border-width: {$attrValue}; border-right-color: {$color}; border-left-color: transparent;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class.dipi_timeline .dipi_timeline_layout_right_tablet .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(odd) .dipi_timeline_item_container .dipi_timeline_item_card:after,
                        $order_class.dipi_timeline .dipi_timeline_layout_right_tablet .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(even) .dipi_timeline_item_container .dipi_timeline_item_card:after,
                        $order_class .dipi_timeline_layout_mixed_tablet.startpos-right .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(odd) .dipi_timeline_item_container .dipi_timeline_item_card:after,
                        $order_class .dipi_timeline_layout_mixed_tablet.startpos-left .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(even) .dipi_timeline_item_container .dipi_timeline_item_card:after",
                        'attr'                => static::getAttr($attrs, 'card_arrow_size'),
                        'declarationFunction' => function ( array $args ) use ( $attrs ) {
                            $attrValue = $args['attrValue'];
                            $breakpoint = $args['breakpoint'];
                            if ( $breakpoint !== "tablet" ) {
                                return "";
                            }
                            $color = static::getAttrByMode( $attrs, 'card_arrow_color', '#F2F3F3', 'tablet' );
                            return "left: -{$attrValue}; border-width: {$attrValue}; border-right-color: {$color}; border-left-color: transparent;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class.et_pb_module.dipi_timeline .dipi_timeline_layout_right_phone .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(odd) .dipi_timeline_item_container .dipi_timeline_item_card:after,
                        $order_class.et_pb_module.dipi_timeline .dipi_timeline_layout_right_phone .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(even) .dipi_timeline_item_container .dipi_timeline_item_card:after,
                        $order_class.et_pb_module .dipi_timeline_layout_mixed_phone.startpos-right .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(odd) .dipi_timeline_item_container .dipi_timeline_item_card:after,
                        $order_class.et_pb_module .dipi_timeline_layout_mixed_phone.startpos-left .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(even) .dipi_timeline_item_container .dipi_timeline_item_card:after",
                        'attr'                => static::getAttr($attrs, 'card_arrow_size'),
                        'declarationFunction' => function ( array $args ) use ( $attrs ) {
                            $attrValue = $args['attrValue'];
                            $breakpoint = $args['breakpoint'];
                            if ( $breakpoint !== "phone" ) {
                                return "";
                            }
                            $color = static::getAttrByMode( $attrs, 'card_arrow_color', '#F2F3F3', 'phone' );
                            return "left: -{$attrValue}; border-width: {$attrValue}; border-right-color: {$color}; border-left-color: transparent;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_layout_left .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item .dipi_timeline_item_container .dipi_timeline_item_card:after,
                        $order_class .dipi_timeline_layout_mixed.startpos-right .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(even) .dipi_timeline_item_container .dipi_timeline_item_card:after,
                        $order_class .dipi_timeline_layout_mixed.startpos-left .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(odd) .dipi_timeline_item_container .dipi_timeline_item_card:after",
                        'attr'                => static::getAttr($attrs, 'card_arrow_size'),
                        'declarationFunction' => function ( array $args ) use ( $attrs ) {
                            $attrValue = $args['attrValue'];
                            $breakpoint = $args['breakpoint'];
                            if ( $breakpoint !== "desktop" ) {
                                return "";
                            }
                            $color = static::getAttrByMode( $attrs, 'card_arrow_color', '#F2F3F3', 'desktop' );
                            return "right: -{$attrValue}; border-width: {$attrValue}; border-left-color: {$color}; border-right-color: transparent;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class.dipi_timeline .dipi_timeline_layout_left_tablet .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(even) .dipi_timeline_item_container .dipi_timeline_item_card:after,
                        $order_class.dipi_timeline .dipi_timeline_layout_left_tablet .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(odd) .dipi_timeline_item_container .dipi_timeline_item_card:after,
                        $order_class .dipi_timeline_layout_mixed_tablet.startpos-right .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(even) .dipi_timeline_item_container .dipi_timeline_item_card:after,
                        $order_class .dipi_timeline_layout_mixed_tablet.startpos-left .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(odd) .dipi_timeline_item_container .dipi_timeline_item_card:after",
                        'attr'                => static::getAttr($attrs, 'card_arrow_size'),
                        'declarationFunction' => function ( array $args ) use ( $attrs ) {
                            $attrValue = $args['attrValue'];
                            $breakpoint = $args['breakpoint'];
                            if ( $breakpoint !== "tablet" ) {
                                return "";
                            }
                            $color = static::getAttrByMode( $attrs, 'card_arrow_color', '#F2F3F3', 'tablet' );
                            return "right: -{$attrValue}; border-width: {$attrValue}; border-left-color: {$color}; border-right-color: transparent;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class.et_pb_module.dipi_timeline .dipi_timeline_layout_left_phone .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(even) .dipi_timeline_item_container .dipi_timeline_item_card:after,
                        $order_class.et_pb_module.dipi_timeline .dipi_timeline_layout_left_phone .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(odd) .dipi_timeline_item_container .dipi_timeline_item_card:after,
                        $order_class.et_pb_module .dipi_timeline_layout_mixed_phone.startpos-right .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(even) .dipi_timeline_item_container .dipi_timeline_item_card:after,
                        $order_class.et_pb_module .dipi_timeline_layout_mixed_phone.startpos-left .dipi_timeline_container .dipi-timeline-items .dipi_timeline_item:nth-child(odd) .dipi_timeline_item_container .dipi_timeline_item_card:after",
                        'attr'                => static::getAttr($attrs, 'card_arrow_size'),
                        'declarationFunction' => function ( array $args ) use ( $attrs ) {
                            $attrValue = $args['attrValue'];
                            $breakpoint = $args['breakpoint'];
                            if ( $breakpoint !== "phone" ) {
                                return "";
                            }
                            $color = static::getAttrByMode( $attrs, 'card_arrow_color', '#F2F3F3', 'phone' );
                            return "right: -{$attrValue}; border-width: {$attrValue}; border-left-color: {$color}; border-right-color: transparent;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_timeline_item .dipi_timeline_item_card .dipi_timeline_ribbon",
                        'attr'                => static::getAttr($attrs, 'card_arrow_size'),
                        'declarationFunction' => function ( array $args ) use ( $text_align, $text_align_tablet, $text_align_phone ) {
                            $breakpoint = $args['breakpoint'];
                            $ribbon_text_align_value = $text_align;
                            if($breakpoint === "tablet") {
                                $ribbon_text_align_value = $text_align_tablet;
                            } else if($breakpoint === "phone") {
                                $ribbon_text_align_value = $text_align_phone;
                            }
                            $alignValue = "center";
                            if ($ribbon_text_align_value === "left") $alignValue = "flex-start";
                            else if ($ribbon_text_align_value === "right") $alignValue = "flex-end";
                            return "justify-content: $alignValue;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => $order_class,
                        'attr'                => static::getAttr($attrs, 'card_arrow_size'),
                        'declarationFunction' => function ( array $args ) use ( $text_align, $text_align_tablet, $text_align_phone ) {
                            $breakpoint = $args['breakpoint'];
                            $text_align_value = $text_align;
                            if($breakpoint === "tablet") {
                                $text_align_value = $text_align_tablet;
                            } else if($breakpoint === "phone") {
                                $text_align_value = $text_align_phone;
                            }
                            return "text-align: $text_align_value;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => $icon_alignment_selector,
                        'attr'                => static::getAttr($attrs, 'image_icon_alignment'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return "text-align: $attrValue;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => $image_alignment_selector,
                        'attr'                => static::getAttr($attrs, 'image_icon_alignment'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            if($attrValue === "center") {
                                return "margin: auto;";
                            } else if($attrValue === "right") {
                                return "margin: auto 0 auto auto;";
                            }
                            return "margin: auto auto auto 0;";
                        }
                    ]),
				],
			]
		);
    }
}
