<?php
namespace DIPI\Modules\TimelineItem;

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

    /**
     * Top/bottom margin on .dipi_timeline_item_card-wrap (module Design spacing + card_spacing). Module wins per side when set.
     *
     * @param array<string,mixed> $attrs
     * @return array{0:string,1:string}
     */
    private static function timeline_item_wrap_mt_mb_at_bp($attrs, $bp)
    {
        $pick = function ($try) use ($attrs) {
            if (! $try) {
                return null;
            }
            $mt       = '';
            $mb       = '';
            $mod_slot = ( $attrs['module']['decoration']['spacing'] ?? [] )[ $try ] ?? [];
            $mod_m    = $mod_slot['value']['margin'] ?? null;
            if (is_array($mod_m)) {
                $mt = isset($mod_m['top']) ? trim((string) $mod_m['top']) : '';
                $mb = isset($mod_m['bottom']) ? trim((string) $mod_m['bottom']) : '';
            }
            $cs_slot = ( $attrs['card_spacing']['innerContent'] ?? [] )[ $try ] ?? [];
            $cs_m    = $cs_slot['value']['margin'] ?? null;
            if (is_array($cs_m)) {
                $cs_t = isset($cs_m['top']) ? trim((string) $cs_m['top']) : '';
                $cs_b = isset($cs_m['bottom']) ? trim((string) $cs_m['bottom']) : '';
                if ('' === $mt && '' !== $cs_t) {
                    $mt = $cs_t;
                }
                if ('' === $mb && '' !== $cs_b) {
                    $mb = $cs_b;
                }
            }
            if ('' === $mt && '' === $mb) {
                return null;
            }

            return [ '' !== $mt ? $mt : '0px', '' !== $mb ? $mb : '0px' ];
        };

        $first = $pick($bp);
        if ('tablet' === $bp) {
            return $first ?? $pick('desktop') ?? [ '0px', '0px' ];
        }
        if ('phone' === $bp) {
            return $first ?? $pick('tablet') ?? $pick('desktop') ?? [ '0px', '0px' ];
        }

        return $first ?? [ '0px', '0px' ];
    }

    /**
     * @param array<string,mixed> $attrs
     * @return array<string,mixed>|null
     */
    private static function timeline_item_card_spacing_padding_only_inner($attrs)
    {
        $inner = ($attrs['card_spacing'] ?? [])['innerContent'] ?? null;
        if (empty($inner) || ! is_array($inner)) {
            return null;
        }
        $inner = json_decode(wp_json_encode($inner), true);
        foreach (['desktop', 'tablet', 'phone'] as $bp) {
            if (isset($inner[$bp]['value']['margin'])) {
                $inner[$bp]['value']['margin'] = [
                    'top'    => '',
                    'right'  => '',
                    'bottom' => '',
                    'left'   => '',
                ];
            }
        }
        return $inner;
    }

    /**
     * @param array<string,mixed> $attrs
     * @return array<string,mixed>|null
     */
    private static function timeline_item_card_spacing_wrap_margins_inner($attrs)
    {
        $inner = ($attrs['card_spacing'] ?? [])['innerContent'] ?? null;
        if (empty($inner) || ! is_array($inner)) {
            return null;
        }
        $inner = json_decode(wp_json_encode($inner), true);
        foreach (['desktop', 'tablet', 'phone'] as $bp) {
            if (! isset($inner[$bp]['value'])) {
                continue;
            }
            if (isset($inner[$bp]['value']['padding'])) {
                $inner[$bp]['value']['padding'] = [
                    'top'    => '',
                    'right'  => '',
                    'bottom' => '',
                    'left'   => '',
                ];
            }
            if (isset($inner[$bp]['value']['margin'])) {
                $m = $inner[$bp]['value']['margin'];
                $inner[$bp]['value']['margin'] = [
                    'top'    => $m['top'] ?? '',
                    'right'  => '',
                    'bottom' => $m['bottom'] ?? '',
                    'left'   => '',
                ];
            }
        }
        return $inner;
    }

    /**
     * @param array<string,mixed> $attrs
     * @return array<int,mixed>
     */
    private static function timeline_item_card_spacing_style_entries($order_class, $attrs)
    {
        $card_inner = static::timeline_item_card_spacing_padding_only_inner($attrs);
        $out        = [
            SpacingStyle::style([
                'selector' => "{$order_class} .dipi_timeline_item_card",
                'attr'     => null !== $card_inner ? $card_inner : static::getAttr($attrs, 'card_spacing'),
            ]),
        ];
        $wrap_inner = static::timeline_item_card_spacing_wrap_margins_inner($attrs);
        if (null !== $wrap_inner) {
            $out[] = SpacingStyle::style([
                'selector' => "{$order_class} .dipi_timeline_item_card-wrap",
                'attr'     => $wrap_inner,
            ]);
        }
        return $out;
    }

    public static function module_styles($args)
    {
        $attrs    = $args['attrs'] ?? [];
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class  = $args['orderClass'] ?? '';
        $order_number = preg_replace('/[^0-9]/', '', $order_class);


        $icon_selector = $order_class . ' .dipi_timeline_font_icon';
        $timeline_icon_selector = $order_class . ' .ribbon-icon';
        $timeline_icon_image_selector = $order_class . ' .ribbon-icon.ribbon-icon-image';
        $timeline_icon_circle_selector = $order_class . ' .ribbon-icon.ribbon-icon-circle';
        $icon_alignment_selector = $order_class . ' .dipi_timeline_item_image';
        $image_alignment_selector = $order_class . ' .dipi_timeline_item_image .et_pb_image_wrap';
        $timeline_icon_hover_selector = $order_class . ':hover .ribbon-icon, ' . $order_class . ' .ribbon-icon.active';
        $timeline_icon_circle_hover_selector = $order_class . ':hover .ribbon-icon.ribbon-icon-circle, ' . $order_class . ' .ribbon-icon.ribbon-icon-circle.active';
        
        $use_icon = static::getAttrByMode($attrs, 'use_icon', 'off');
        $use_icon_font_size = static::getAttrByMode($attrs, 'use_icon_font_size', 'off');
        $use_circle = static::getAttrByMode($attrs, 'use_circle', 'off');
        $use_circle_border = static::getAttrByMode($attrs, 'use_circle_border', 'off');
        $ribbon_use_icon_font_size = static::getAttrByMode($attrs, 'ribbon_use_icon_font_size', 'off');
        $ribbon_use_circle = static::getAttrByMode($attrs, 'ribbon_use_circle', 'on');
        $ribbon_use_circle_border = static::getAttrByMode($attrs, 'ribbon_use_circle_border', 'off');
        $ribbon_circle_color_hover = isset($attrs['ribbon_circle_color']['innerContent']['desktop']['hover']) ? $attrs['ribbon_circle_color']['innerContent']['desktop']['hover'] : '';
        $ribbon_circle_border_color_hover = isset($attrs['ribbon_circle_border_color']['innerContent']['desktop']['hover']) ? $attrs['ribbon_circle_border_color']['innerContent']['desktop']['hover'] : '';
        $timeline_icon_color_hover = isset($attrs['timeline_icon_color']['innerContent']['desktop']['hover']) ? $attrs['timeline_icon_color']['innerContent']['desktop']['hover'] : '';
        $ribbon_text_align = isset($attrs['ribbon_text']['decoration']['font']['font']['desktop']['value']['textAlign']) ? $attrs['ribbon_text']['decoration']['font']['font']['desktop']['value']['textAlign'] : '';
        $ribbon_text_align_tablet = isset($attrs['ribbon_text']['decoration']['font']['font']['tablet']['value']['textAlign']) ? $attrs['ribbon_text']['decoration']['font']['font']['tablet']['value']['textAlign'] : $ribbon_text_align;
        $ribbon_text_align_phone = isset($attrs['ribbon_text']['decoration']['font']['font']['phone']['value']['textAlign']) ? $attrs['ribbon_text']['decoration']['font']['font']['phone']['value']['textAlign'] : $ribbon_text_align_tablet;

        Style::add([
            'id'            => $args['id'],
            'name'          => $args['name'],
            'orderIndex'    => $args['orderIndex'],
            'storeInstance' => $args['storeInstance'],
            'styles'        => array_merge([
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
                    'attrName'   => 'button',
                ]),
                $elements->style([
                    'attrName'   => 'image',
                ]),
                $elements->style([
                    'attrName'   => 'header_font',
                ]),
                $elements->style([
                    'attrName'   => 'body_font',
                ]),
                $elements->style([
                    'attrName'   => 'ribbon_text',
                ]),
                SpacingStyle::style([
                    "selector" => "$order_class .ribbon-icon",
                    "attr" => static::getAttr($attrs, "timeline_icon_spacing"),
                ]),
                SpacingStyle::style([
                    "selector" => "$order_class .et-pb-icon",
                    "attr" => static::getAttr($attrs, "circle_icon_padding"),
                ]),
            ],
            static::timeline_item_card_spacing_style_entries($order_class, $attrs),
            [
                CommonStyle::style([
                    'selector'            => "{$order_class} .dipi_timeline_item_card-wrap",
                    'attr'                => static::getAttr($attrs, 'card_arrow_size'),
                    'declarationFunction' => function ( array $args ) use ( $attrs ) {
                        if ('desktop' !== $args['breakpoint']) {
                            return '';
                        }
                        list($mt, $mb) = static::timeline_item_wrap_mt_mb_at_bp($attrs, 'desktop');

                        return '--dipi-timeline-wrap-mt: ' . esc_attr($mt) . '; --dipi-timeline-wrap-mb: ' . esc_attr($mb) . ';';
                    },
                ]),
                CommonStyle::style([
                    'selector'            => "{$order_class} .dipi_timeline_item_card-wrap",
                    'attr'                => static::getAttr($attrs, 'card_arrow_size'),
                    'declarationFunction' => function ( array $args ) use ( $attrs ) {
                        if ('tablet' !== $args['breakpoint']) {
                            return '';
                        }
                        list($mt, $mb) = static::timeline_item_wrap_mt_mb_at_bp($attrs, 'tablet');

                        return '--dipi-timeline-wrap-mt: ' . esc_attr($mt) . '; --dipi-timeline-wrap-mb: ' . esc_attr($mb) . ';';
                    },
                ]),
                CommonStyle::style([
                    'selector'            => "{$order_class} .dipi_timeline_item_card-wrap",
                    'attr'                => static::getAttr($attrs, 'card_arrow_size'),
                    'declarationFunction' => function ( array $args ) use ( $attrs ) {
                        if ('phone' !== $args['breakpoint']) {
                            return '';
                        }
                        list($mt, $mb) = static::timeline_item_wrap_mt_mb_at_bp($attrs, 'phone');

                        return '--dipi-timeline-wrap-mt: ' . esc_attr($mt) . '; --dipi-timeline-wrap-mb: ' . esc_attr($mb) . ';';
                    },
                ]),
                SpacingStyle::style([
                    "selector" => "$order_class .dipi_timeline_item_card .dipi_timeline_item_content",
                    "attr" => static::getAttr($attrs, "card_content_padding"),
                ]),
                CommonStyle::style([
                    'selector'            => "$order_class .dipi_timeline_item_card",
                    'attr'                => static::getAttr($attrs, 'card_width'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return "width: $attrValue;";
                    }
                ]),
                CommonStyle::style([
                    "selector" => "$order_class .ribbon-icon",
                    "attr" => static::getAttr($attrs, "timeline_icon"),
                    "declarationFunction" => [static::class, 'icon_font_declaration'],
                ]),
                CommonStyle::style([
                    "selector" => "$order_class .dipi_timeline_font_icon",
                    "attr" => static::getAttr($attrs, "font_icon"),
                    "declarationFunction" => [static::class, 'icon_font_declaration'],
                ]),
                CommonStyle::style([
                    'selector'            => $icon_alignment_selector,
                    'attr'                => static::getAttr($attrs, 'icon_alignment'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        if($attrValue === "") {
                            return "";
                        }
                        return "text-align: $attrValue;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => $image_alignment_selector,
                    'attr'                => static::getAttr($attrs, 'icon_alignment'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        if($attrValue === "") {
                            return "";
                        }
                        if ($attrValue === "left") {
                            return "margin: auto auto auto 0;";
                        } else if ($attrValue === "right") {
                            return "margin: auto 0 auto auto;";
                        }
                        return "margin: auto;";
                    }
                ]),
                $use_icon_font_size === "on" ? CommonStyle::style([
                    'selector'            => $icon_selector,
                    'attr'                => static::getAttr($attrs, 'icon_font_size'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return "font-size: $attrValue;";
                    }
                ]) : null,
                CommonStyle::style([
                    'selector'            => $order_class . ' .dipi_timeline_item_image .et_pb_image_wrap',
                    'attr'                => static::getAttr($attrs, 'image_max_width'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return "max-width: $attrValue;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => $order_class . ' .dipi_timeline_item_card',
                    'attr'                => static::getAttr($attrs, 'card_max_width'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return "max-width: $attrValue;";
                    }
                ]),
                $use_icon === "on" ? CommonStyle::style([
                    'selector'            => $icon_selector,
                    'attr'                => static::getAttr($attrs, 'icon_color'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return "color: $attrValue;";
                    }
                ]) : null,
                $use_icon === "on" && $use_circle === "on" ? CommonStyle::style([
                    'selector'            => $icon_selector,
                    'attr'                => static::getAttr($attrs, 'circle_color'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return "background-color: $attrValue;";
                    }
                ]) : null,
                $use_icon === "on" && $use_circle === "on" && $use_circle_border === "on" ? CommonStyle::style([
                    'selector'            => $icon_selector,
                    'attr'                => static::getAttr($attrs, 'circle_border_color'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return "border-color: $attrValue!important;";
                    }
                ]) : null,
                $ribbon_use_icon_font_size === "on" ? CommonStyle::style([
                    'selector'            => $timeline_icon_selector,
                    'attr'                => static::getAttr($attrs, 'timeline_icon_font_size'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return "font-size: $attrValue;";
                    }
                ]) : null,
                $ribbon_use_icon_font_size === "on" ? CommonStyle::style([
                    'selector'            => $timeline_icon_image_selector,
                    'attr'                => static::getAttr($attrs, 'timeline_icon_font_size'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return "width: $attrValue;";
                    }
                ]) : null,
                CommonStyle::style([
                    'selector'            => $timeline_icon_selector,
                    'attr'                => static::getAttr($attrs, 'timeline_icon_color'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return "color: $attrValue;";
                    }
                ]),
                $timeline_icon_color_hover !== "" ? CommonStyle::style([
                    'selector'            => $timeline_icon_hover_selector,
                    'attr'                => static::getAttr($attrs, 'timeline_icon_color'),
                    'declarationFunction' => function ( array $args ) use ($timeline_icon_color_hover) {
                        return "color: $timeline_icon_color_hover!important;";
                    }
                ]) : null,
                $ribbon_use_circle === "on" ? CommonStyle::style([
                    'selector'            => $timeline_icon_circle_selector,
                    'attr'                => static::getAttr($attrs, 'ribbon_circle_color'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return "background-color: $attrValue;";
                    }
                ]) : null,
                $ribbon_use_circle === "on" && $ribbon_circle_color_hover !== "" ? CommonStyle::style([
                    'selector'            => $timeline_icon_circle_hover_selector,
                    'attr'                => static::getAttr($attrs, 'ribbon_circle_color'),
                    'declarationFunction' => function ( array $args ) use ($ribbon_circle_color_hover) {
                        return "background-color: $ribbon_circle_color_hover!important;";
                    }
                ]) : null,
                $ribbon_use_circle === "on" && $ribbon_use_circle_border === "on" ? CommonStyle::style([
                    'selector'            => $timeline_icon_circle_selector,
                    'attr'                => static::getAttr($attrs, 'ribbon_circle_border_color'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return "border-color: $attrValue;";
                    }
                ]) : null,
                $ribbon_use_circle === "on" && $ribbon_use_circle_border === "on" && $ribbon_circle_border_color_hover !== "" ? CommonStyle::style([
                    'selector'            => $timeline_icon_circle_hover_selector,
                    'attr'                => static::getAttr($attrs, 'ribbon_circle_border_color'),
                    'declarationFunction' => function ( array $args ) use ($ribbon_circle_border_color_hover) {
                        return "border-color: $ribbon_circle_border_color_hover!important;";
                    }
                ]) : null,
                CommonStyle::style([
                    'selector'            => ".dipi_timeline_layout_right .dipi_timeline_container .dipi-timeline-items {$order_class}.dipi_timeline_item .dipi_timeline_item_custom-card-arrow  .dipi_timeline_item_container .dipi_timeline_item_card:after,
                    .dipi_timeline_layout_mixed.startpos-right .dipi_timeline_container .dipi-timeline-items {$order_class}.dipi_timeline_item:nth-child(odd) .dipi_timeline_item_custom-card-arrow .dipi_timeline_item_container .dipi_timeline_item_card:after,
                    .dipi_timeline_layout_mixed.startpos-left .dipi_timeline_container .dipi-timeline-items {$order_class}.dipi_timeline_item:nth-child(even) .dipi_timeline_item_custom-card-arrow .dipi_timeline_item_container .dipi_timeline_item_card:after",
                    'attr'                => static::getAttr($attrs, 'card_arrow_size'),
                    'declarationFunction' => function ( array $args ) use ( $attrs ) {
                        $attrValue = $args['attrValue'];
                        $breakpoint = $args['breakpoint'];
                        if ( $breakpoint !== "desktop" ) {
                            return '';
                        }
                        $color = static::getAttrByMode( $attrs, 'card_arrow_color', '#F2F3F3', 'desktop' );
                        return "left: -$attrValue; border-width: $attrValue; border-right-color: $color; border-left-color: transparent;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => ".dipi_timeline .dipi_timeline_layout_right_tablet .dipi_timeline_container .dipi-timeline-items:nth-child(odd) $order_class.dipi_timeline_item .dipi_timeline_item_custom-card-arrow  .dipi_timeline_item_container .dipi_timeline_item_card:after,
                    .dipi_timeline .dipi_timeline_layout_right_tablet .dipi_timeline_container .dipi-timeline-items:nth-child(even) $order_class.dipi_timeline_item .dipi_timeline_item_custom-card-arrow  .dipi_timeline_item_container .dipi_timeline_item_card:after,
                    .dipi_timeline .dipi_timeline_layout_mixed_tablet.startpos-right .dipi_timeline_container .dipi-timeline-items $order_class.dipi_timeline_item:nth-child(odd) .dipi_timeline_item_custom-card-arrow .dipi_timeline_item_container .dipi_timeline_item_card:after,
                    .dipi_timeline .dipi_timeline_layout_mixed_tablet.startpos-left .dipi_timeline_container .dipi-timeline-items $order_class.dipi_timeline_item:nth-child(even) .dipi_timeline_item_custom-card-arrow .dipi_timeline_item_container .dipi_timeline_item_card:after",
                    'attr'                => static::getAttr($attrs, 'card_arrow_size'),
                    'declarationFunction' => function ( array $args ) use ( $attrs ) {
                        $attrValue = $args['attrValue'];
                        $breakpoint = $args['breakpoint'];
                        if ( $breakpoint !== "tablet" ) {
                            return '';
                        }
                        $color = static::getAttrByMode( $attrs, 'card_arrow_color', '#F2F3F3', 'tablet' );
                        return "left: -$attrValue; border-width: $attrValue; border-right-color: $color; border-left-color: transparent;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => ".dipi_timeline .dipi_timeline_layout_right_phone .dipi_timeline_container .dipi-timeline-items:nth-child(odd) $order_class.dipi_timeline_item .dipi_timeline_item_custom-card-arrow  .dipi_timeline_item_container .dipi_timeline_item_card:after,
                    .dipi_timeline .dipi_timeline_layout_right_phone .dipi_timeline_container .dipi-timeline-items:nth-child(even) $order_class.dipi_timeline_item .dipi_timeline_item_custom-card-arrow  .dipi_timeline_item_container .dipi_timeline_item_card:after,
                    .dipi_timeline .dipi_timeline_layout_mixed_phone.startpos-right .dipi_timeline_container .dipi-timeline-items $order_class.dipi_timeline_item:nth-child(odd) .dipi_timeline_item_custom-card-arrow .dipi_timeline_item_container .dipi_timeline_item_card:after,
                    .dipi_timeline .dipi_timeline_layout_mixed_phone.startpos-left .dipi_timeline_container .dipi-timeline-items $order_class.dipi_timeline_item:nth-child(even) .dipi_timeline_item_custom-card-arrow .dipi_timeline_item_container .dipi_timeline_item_card:after",
                    'attr'                => static::getAttr($attrs, 'card_arrow_size'),
                    'declarationFunction' => function ( array $args ) use ( $attrs ) {
                        $attrValue = $args['attrValue'];
                        $breakpoint = $args['breakpoint'];
                        if ( $breakpoint !== "phone" ) {
                            return '';
                        }
                        $color = static::getAttrByMode( $attrs, 'card_arrow_color', '#F2F3F3', 'phone' );
                        return "left: -$attrValue; border-width: $attrValue; border-right-color: $color; border-left-color: transparent;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => ".dipi_timeline_layout_left .dipi_timeline_container .dipi-timeline-items $order_class.dipi_timeline_item .dipi_timeline_item_custom-card-arrow .dipi_timeline_item_container .dipi_timeline_item_card:after,
                    .dipi_timeline_layout_mixed.startpos-right .dipi_timeline_container .dipi-timeline-items $order_class.dipi_timeline_item:nth-child(even) .dipi_timeline_item_custom-card-arrow .dipi_timeline_item_container .dipi_timeline_item_card:after,
                    .dipi_timeline_layout_mixed.startpos-left .dipi_timeline_container .dipi-timeline-items $order_class.dipi_timeline_item:nth-child(odd) .dipi_timeline_item_custom-card-arrow .dipi_timeline_item_container .dipi_timeline_item_card:after",
                    'attr'                => static::getAttr($attrs, 'card_arrow_size'),
                    'declarationFunction' => function ( array $args ) use ( $attrs ) {
                        $attrValue = $args['attrValue'];
                        $breakpoint = $args['breakpoint'];
                        if ( $breakpoint !== "desktop" ) {
                            return '';
                        }
                        $color = static::getAttrByMode( $attrs, 'card_arrow_color', '#F2F3F3', 'desktop' );
                        return "right: -$attrValue; border-width: $attrValue; border-left-color: $color; border-right-color: transparent;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => ".dipi_timeline .dipi_timeline_layout_left_tablet .dipi_timeline_container .dipi-timeline-items $order_class.dipi_timeline_item:nth-child(odd) .dipi_timeline_item_custom-card-arrow .dipi_timeline_item_container .dipi_timeline_item_card:after,
                    .dipi_timeline .dipi_timeline_layout_left_tablet .dipi_timeline_container .dipi-timeline-items $order_class.dipi_timeline_item:nth-child(even) .dipi_timeline_item_custom-card-arrow .dipi_timeline_item_container .dipi_timeline_item_card:after,
                    .dipi_timeline .dipi_timeline_layout_mixed_tablet.startpos-right .dipi_timeline_container .dipi-timeline-items $order_class.dipi_timeline_item:nth-child(even) .dipi_timeline_item_custom-card-arrow .dipi_timeline_item_container .dipi_timeline_item_card:after,
                    .dipi_timeline .dipi_timeline_layout_mixed_tablet.startpos-left .dipi_timeline_container .dipi-timeline-items $order_class.dipi_timeline_item:nth-child(odd) .dipi_timeline_item_custom-card-arrow .dipi_timeline_item_container .dipi_timeline_item_card:after",
                    'attr'                => static::getAttr($attrs, 'card_arrow_size'),
                    'declarationFunction' => function ( array $args ) use ( $attrs ) {
                        $attrValue = $args['attrValue'];
                        $breakpoint = $args['breakpoint'];
                        if ( $breakpoint !== "tablet" ) {
                            return '';
                        }
                        $color = static::getAttrByMode( $attrs, 'card_arrow_color', '#F2F3F3', 'tablet' );
                        return "right: -$attrValue; border-width: $attrValue; border-left-color: $color; border-right-color: transparent;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => "div.et_pb_module.dipi_timeline .dipi_timeline_layout_left_phone .dipi_timeline_container .dipi-timeline-items $order_class.dipi_timeline_item:nth-child(even) .dipi_timeline_item_custom-card-arrow .dipi_timeline_item_container .dipi_timeline_item_card:after,
                    div.et_pb_module.dipi_timeline .dipi_timeline_layout_left_phone .dipi_timeline_container .dipi-timeline-items $order_class.dipi_timeline_item:nth-child(odd) .dipi_timeline_item_custom-card-arrow .dipi_timeline_item_container .dipi_timeline_item_card:after,
                    div.et_pb_module.dipi_timeline .dipi_timeline_layout_mixed_phone.startpos-right .dipi_timeline_container .dipi-timeline-items $order_class.dipi_timeline_item:nth-child(even) .dipi_timeline_item_custom-card-arrow .dipi_timeline_item_container .dipi_timeline_item_card:after,
                    div.et_pb_module.dipi_timeline .dipi_timeline_layout_mixed_phone.startpos-left .dipi_timeline_container .dipi-timeline-items $order_class.dipi_timeline_item:nth-child(odd) .dipi_timeline_item_custom-card-arrow .dipi_timeline_item_container .dipi_timeline_item_card:after",
                    'attr'                => static::getAttr($attrs, 'card_arrow_size'),
                    'declarationFunction' => function ( array $args ) use ( $attrs ) {
                        $attrValue = $args['attrValue'];
                        $breakpoint = $args['breakpoint'];
                        if ( $breakpoint !== "phone" ) {
                            return '';
                        }
                        $color = static::getAttrByMode( $attrs, 'card_arrow_color', '#F2F3F3', 'phone' );
                        return "right: -$attrValue; border-width: $attrValue; border-left-color: $color; border-right-color: transparent;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => ".dipi_timeline $order_class.dipi_timeline_item .dipi_timeline_item_card .dipi_timeline_ribbon",
                    'attr'                => static::getAttr($attrs, 'card_arrow_size'),
                    'declarationFunction' => function ( array $args ) use ($ribbon_text_align, $ribbon_text_align_tablet, $ribbon_text_align_phone) {
                        $breakpoint = $args['breakpoint'];
                        $ribbon_text_align_value = $ribbon_text_align;
                        if($breakpoint === "tablet") {
                            $ribbon_text_align_value = $ribbon_text_align_tablet;
                        } else if($breakpoint === "phone") {
                            $ribbon_text_align_value = $ribbon_text_align_phone;
                        }
                        if($ribbon_text_align_value === "") return "";
                        $alignValue = "center";
                        if ($ribbon_text_align_value === "left") $alignValue = "flex-start";
                        else if ($ribbon_text_align_value === "right") $alignValue = "flex-end";
                        return "justify-content: $alignValue;";
                    }
                ]),
            ]
            ),
		]);
    }
}
