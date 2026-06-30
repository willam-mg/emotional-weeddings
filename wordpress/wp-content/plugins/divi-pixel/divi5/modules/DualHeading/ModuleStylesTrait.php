<?php
namespace DIPI\Modules\DualHeading;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
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

    public static function getAttr($attrs, $attr, $default = null)
    {
        $AttrValue = (($attrs ?? [])[$attr] ?? [])["innerContent"] ?? [
            "desktop" => ["value" => $default ?? ""],
        ];
        return $AttrValue;
    }

    public static function getResponsiveAttr($attrs, $attr, $default = null)
    {
        $value = static::getAttr($attrs, $attr, $default);
        $result = [];
        foreach (['desktop', 'tablet', 'phone'] as $breakpoint) {
            $result[$breakpoint] = ($value[$breakpoint]['value'] ?? $default ?? '');
        }
        return $result;
    }

    public static function module_styles($args)
    {
        $attrs = $args['attrs'] ?? [];
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class = $args['orderClass'] ?? '';
        $order_number = preg_replace('/[^0-9]/', '', $order_class);

        $fh_selector = "$order_class .dipi-dual-heading .dipi-dh-first-heading";
        $sh_selector = "$order_class .dipi-dual-heading .dipi-dh-second-heading";
        $bt_selector = "$order_class .dipi-dual-heading .dipi-dh-main::before";
        $main_selector = "$order_class .dipi-dual-heading .dipi-dh-main";

        // Get props
        $fh_style = static::getAttrByMode($attrs, 'fh_style', 'none');
        $sh_style = static::getAttrByMode($attrs, 'sh_style', 'none');
        $bt_style = static::getAttrByMode($attrs, 'bt_style', 'none');
        $fh_background_style = static::getAttrByMode($attrs, 'fh_background_style', 'normal');
        $sh_background_style = static::getAttrByMode($attrs, 'sh_background_style', 'normal');
        $bt_background_style = static::getAttrByMode($attrs, 'bt_background_style', 'normal');
        $use_reveal_effect = static::getAttrByMode($attrs, 'use_reveal_effect', 'off');
        $use_background_text = static::getAttrByMode($attrs, 'use_background_text', 'off');
        $heading_display = static::getResponsiveAttr($attrs, 'heading_display', 'row');
        $fh_background_animation = static::getAttrByMode($attrs, 'fh_background_animation', 'off');
        $sh_background_animation = static::getAttrByMode($attrs, 'sh_background_animation', 'off');
        $fh_reveal_effect_animation = static::getAttrByMode($attrs, 'fh_reveal_effect_animation', 'dipiDHFadeOut');
        $fh_reveal_effect_animation_speed = static::getAttrByMode($attrs, 'fh_reveal_effect_animation_speed', '0.6');
        $fh_reveal_effect_animation_delay = static::getAttrByMode($attrs, 'fh_reveal_effect_animation_delay', '0.2');
        $sh_reveal_effect_animation = static::getAttrByMode($attrs, 'sh_reveal_effect_animation', 'dipiDHFadeOut');
        $sh_reveal_effect_animation_speed = static::getAttrByMode($attrs, 'sh_reveal_effect_animation_speed', '0.6');
        $sh_reveal_effect_animation_delay = static::getAttrByMode($attrs, 'sh_reveal_effect_animation_delay', '0.4');
        
        $styles = [
            // Module
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
            TextStyle::style(
                [
                    'selector' => $main_selector,
                    'attr' => $attrs['module']['advanced']['text'] ?? [],
                ]
            ),
            CssStyle::style([
                'selector' => $args['orderClass'],
                'attr' => $attrs['css'] ?? [],
                'cssFields' => static::custom_css(),
            ]),
            // First Heading Typography
            $elements->style([
                'attrName' => 'first_heading',
            ]),
            // Second Heading Typography
            $elements->style([
                'attrName' => 'second_heading',
            ]),
            // Background Text Typography
            $use_background_text === 'on' ? $elements->style([
                'attrName' => 'background_text',
            ]) : null,
            // Reveal Effect Styles
            $use_reveal_effect === 'on' ? $elements->style([
                'attrName' => 'fh_reveal_effect',
            ]) : null,
            $use_reveal_effect === 'on' ? $elements->style([
                'attrName' => 'sh_reveal_effect',
            ]) : null,
            // Heading Display (flex-direction)
            CommonStyle::style([
                'selector' => $main_selector,
                'attr' => static::getAttr($attrs, 'heading_display', 'row'),
                'declarationFunction' => function (array $args) {
                    $attrValue = $args['attrValue'];
                    $value = is_array($attrValue) ? ($attrValue['desktop']['value'] ?? 'row') : $attrValue;
                    return "flex-direction: $value;";
                }
            ]),
            CommonStyle::style([
                'selector' => $main_selector,
                'attr' => $attrs['module']['advanced']['text']['text'] ?? [],
                'declarationFunction' => function (array $args) {
                    $textOrientation = '';
                    if (is_array($args['attrValue'])) {
                        // Get value for current breakpoint (handled by CommonStyle)
                        $breakpoint = $args['breakpoint'] ?? 'desktop';
                        $textOrientation = $args['attrValue']['orientation'] ?? 
                                          ($args['attrValue']['orientation'] ?? 'center');
                    }
                    if ($textOrientation === '') return '';

                    $flexValue = $textOrientation;
                    if ($textOrientation === 'left') $flexValue = 'flex-start';
                    if ($textOrientation === 'right') $flexValue = 'flex-end';

                    return "justify-content: $flexValue; align-items: $flexValue;";
                }
            ]),
            CommonStyle::style([
                'selector' => $main_selector,
                'attr' => $attrs['module']['advanced']['text']['text'] ?? [],
                'declarationFunction' => function (array $args) {
                    $textOrientation = '';
                    if (is_array($args['attrValue'])) {
                        $breakpoint = $args['breakpoint'] ?? 'desktop';
                        $textOrientation = $args['attrValue']['orientation'] ?? 
                                          ($args['attrValue']['orientation'] ?? 'center');
                    }
                    return $textOrientation !== '' ? "text-align: $textOrientation;" : '';
                }
            ]),
            // First Heading Stroke
            $fh_style === 'stroke' ? CommonStyle::style([
                'selector' => $fh_selector,
                'attr' => static::getAttr($attrs, 'fh_stroke_color', 'rgba(255,255,255)'),
                'declarationFunction' => function (array $args) use ($attrs) {
                    $stroke_color = is_array($args['attrValue']) ? ($args['attrValue']['desktop']['value'] ?? 'rgba(255,255,255)') : $args['attrValue'];
                    $stroke_fill = static::getAttrByMode($attrs, 'fh_stroke_fill', 'rgba(0,0,0)');
                    $stroke_width = static::getAttrByMode($attrs, 'fh_stroke_width', '1');
                    return sprintf(
                        '-webkit-text-stroke-color: %1$s;
                        -webkit-text-fill-color: %2$s;
                        -webkit-text-stroke-width: %3$spx;
                        paint-order: stroke fill;',
                        $stroke_color,
                        $stroke_fill,
                        $stroke_width
                    );
                }
            ]) : null,
            // Second Heading Stroke
            $sh_style === 'stroke' ? CommonStyle::style([
                'selector' => $sh_selector,
                'attr' => static::getAttr($attrs, 'sh_stroke_color', 'rgba(255,255,255)'),
                'declarationFunction' => function (array $args) use ($attrs) {
                    $stroke_color = is_array($args['attrValue']) ? ($args['attrValue']['desktop']['value'] ?? 'rgba(255,255,255)') : $args['attrValue'];
                    $stroke_fill = static::getAttrByMode($attrs, 'sh_stroke_fill', 'rgba(0,0,0)');
                    $stroke_width = static::getAttrByMode($attrs, 'sh_stroke_width', '1');
                    return sprintf(
                        '-webkit-text-stroke-color: %1$s;
                        -webkit-text-fill-color: %2$s;
                        -webkit-text-stroke-width: %3$spx;
                        paint-order: stroke fill;',
                        $stroke_color,
                        $stroke_fill,
                        $stroke_width
                    );
                }
            ]) : null,
                // Background Text Stroke
            ($use_background_text === 'on' && $bt_style === 'stroke') ? CommonStyle::style([
                'selector' => $bt_selector,
                'attr' => static::getAttr($attrs, 'bt_stroke_color', 'rgba(255,255,255)'),
                'declarationFunction' => function (array $args) use ($attrs) {
                    $stroke_color = is_array($args['attrValue']) ? ($args['attrValue']['desktop']['value'] ?? 'rgba(255,255,255)') : $args['attrValue'];
                    $stroke_fill = static::getAttrByMode($attrs, 'bt_stroke_fill', 'rgba(0,0,0)');
                    $stroke_width = static::getAttrByMode($attrs, 'bt_stroke_width', '1');
                    return sprintf(
                        '-webkit-text-stroke-color: %1$s;
                        -webkit-text-fill-color: %2$s;
                        -webkit-text-stroke-width: %3$spx;
                        paint-order: stroke fill;',
                        $stroke_color,
                        $stroke_fill,
                        $stroke_width
                    );
                }
            ]) : null,
            // Background Text Position
            $use_background_text === 'on' ? CommonStyle::style([
                'selector' => $bt_selector,
                'attr' => static::getAttr($attrs, 'bt_position_horizontal', '50%'),
                'declarationFunction' => function (array $args) {
                    $value = is_array($args['attrValue']) ? ($args['attrValue']['desktop']['value'] ?? '50%') : $args['attrValue'];
                    return "left: $value;";
                }
            ]) : null,
            $use_background_text === 'on' ? CommonStyle::style([
                'selector' => $bt_selector,
                'attr' => static::getAttr($attrs, 'bt_position_vertical', '50%'),
                'declarationFunction' => function (array $args) {
                    $value = is_array($args['attrValue']) ? ($args['attrValue']['desktop']['value'] ?? '50%') : $args['attrValue'];
                    return "top: $value;";
                }
            ]) : null,
            // Background Text Content
            $use_background_text === 'on' ? CommonStyle::style([
                'selector' => $bt_selector,
                'attr' => static::getAttr($attrs, 'background_text', 'Background Text'),
                'declarationFunction' => function (array $args) {
                    $value = is_array($args['attrValue']) ? ($args['attrValue']['desktop']['value'] ?? '') : $args['attrValue'];
                    return sprintf('content: "%1$s";', esc_attr($value));
                }
            ]) : null,
            // Reveal Effect Animation
            $use_reveal_effect === 'on' ? CommonStyle::style([
                'selector' => "$fh_selector::before, $sh_selector::before",
                'attr' => static::getAttr($attrs, 'use_reveal_effect', 'off'),
                'declarationFunction' => function () {
                    return "content:'';";
                }
            ]) : null,
                // First Heading Background Animation
            ($fh_style === 'background' && $fh_background_animation === 'on') ? CommonStyle::style([
                'selector' => $fh_selector . " .dipi-dh-bg-container",
                'attr' => static::getAttr($attrs, 'fh_background_animation', 'off'),
                'declarationFunction' => function () use ($attrs) {
                    $animation_direction = static::getAttrByMode($attrs, 'fh_background_animation_direction', 'dipiBGLeftToRight');
                    $animation_speed = static::getAttrByMode($attrs, 'fh_background_animation_speed', '30');
                    $animation_function = static::getAttrByMode($attrs, 'fh_background_animation_function', 'linear');
                    return sprintf(
                        'background-repeat:repeat !important;
                        animation-name: %1$s;
                        animation-duration: %2$ss;
                        animation-iteration-count: infinite;
                        animation-timing-function: %3$s;',
                        $animation_direction,
                        $animation_speed,
                        $animation_function
                    );
                }
            ]) : null,
                // Second Heading Background Animation
            ($sh_style === 'background' && $sh_background_animation === 'on') ? CommonStyle::style([
                'selector' => $sh_selector . " .dipi-dh-bg-container",
                'attr' => static::getAttr($attrs, 'sh_background_animation', 'off'),
                'declarationFunction' => function () use ($attrs) {
                    $animation_direction = static::getAttrByMode($attrs, 'sh_background_animation_direction', 'dipiBGLeftToRight');
                    $animation_speed = static::getAttrByMode($attrs, 'sh_background_animation_speed', '30');
                    $animation_function = static::getAttrByMode($attrs, 'sh_background_animation_function', 'linear');
                    return sprintf(
                        'background-repeat:repeat !important;
                        animation-name: %1$s;
                        animation-duration: %2$ss;
                        animation-iteration-count: infinite;
                        animation-timing-function: %3$s;',
                        $animation_direction,
                        $animation_speed,
                        $animation_function
                    );
                }
            ]) : null,
                // First Heading Reveal Effect Animation
            ($use_reveal_effect === 'on') ? CommonStyle::style([
                'selector' => "$order_class .dipi-dual-heading.dipi-go-animation .dipi-dh-first-heading::before",
                'attr' => static::getAttr($attrs, 'use_reveal_effect', 'off'),
                'declarationFunction' => function () use ($fh_reveal_effect_animation, $fh_reveal_effect_animation_speed, $fh_reveal_effect_animation_delay) {
                    return sprintf(
                        'animation-name: %1$s;
                        animation-duration: %2$ss;
                        animation-delay: %3$ss;
                        animation-timing-function: ease;
                        animation-fill-mode: forwards;',
                        $fh_reveal_effect_animation,
                        $fh_reveal_effect_animation_speed,
                        $fh_reveal_effect_animation_delay
                    );
                }
            ]) : null,
            ($use_reveal_effect === 'on') ? CommonStyle::style([
                'selector' => "$order_class .dipi-dual-heading.dipi-go-animation.dipi-dh-waypoint .dipi-dh-first-heading .dipi-dh-animation-container",
                'attr' => static::getAttr($attrs, 'use_reveal_effect', 'off'),
                'declarationFunction' => function () use ($fh_reveal_effect_animation_speed, $fh_reveal_effect_animation_delay) {
                    return sprintf(
                        'animation-name:dipiDHreveal;
                        animation-duration: %1$ss;
                        animation-delay: %2$ss;
                        animation-fill-mode: forwards;',
                        $fh_reveal_effect_animation_speed / 2,
                        $fh_reveal_effect_animation_delay
                    );
                }
            ]) : null,
                // Second Heading Reveal Effect Animation
            ($use_reveal_effect === 'on') ? CommonStyle::style([
                'selector' => "$order_class .dipi-dual-heading.dipi-go-animation .dipi-dh-second-heading::before",
                'attr' => static::getAttr($attrs, 'use_reveal_effect', 'off'),
                'declarationFunction' => function () use ($sh_reveal_effect_animation, $sh_reveal_effect_animation_speed, $sh_reveal_effect_animation_delay) {
                    return sprintf(
                        'animation-name: %1$s;
                        animation-duration: %2$ss;
                        animation-delay: %3$ss;
                        animation-timing-function: ease;
                        animation-fill-mode: forwards;',
                        $sh_reveal_effect_animation,
                        $sh_reveal_effect_animation_speed,
                        $sh_reveal_effect_animation_delay
                    );
                }
            ]) : null,
            $use_reveal_effect === 'on' ? CommonStyle::style([
                'selector' => "$order_class .dipi-dual-heading.dipi-go-animation.dipi-dh-waypoint .dipi-dh-second-heading .dipi-dh-animation-container",
                'attr' => static::getAttr($attrs, 'use_reveal_effect', 'off'),
                'declarationFunction' => function () use ($sh_reveal_effect_animation_speed, $sh_reveal_effect_animation_delay) {
                    return sprintf(
                        'animation-name:dipiDHreveal;
                        animation-duration: %1$ss;
                        animation-delay: %2$ss;
                        animation-fill-mode: forwards;',
                        $sh_reveal_effect_animation_speed / 2,
                        $sh_reveal_effect_animation_delay
                    );
                }
            ]) : null,
            ($fh_style === 'background' && $fh_background_style === 'clipped') ? CommonStyle::style([
                'selector' => "$fh_selector .dipi-dh-bg-container",
                'attr' => static::getAttr($attrs, 'fh_background_style', 'clipped'),
                'declarationFunction' => function (array $args) {
                    return "background-clip: text !important;-webkit-background-clip: text !important;color: transparent !important;-webkit-text-fill-color: transparent !important;";
                }
            ]) : null,
            ($sh_style === 'background' && $sh_background_style === 'clipped') ? CommonStyle::style([
                'selector' => "$sh_selector .dipi-dh-bg-container",
                'attr' => static::getAttr($attrs, 'sh_background_style', 'clipped'),
                'declarationFunction' => function (array $args) {
                    return "background-clip: text !important;-webkit-background-clip: text !important;color: transparent !important;-webkit-text-fill-color: transparent !important;";
                }
            ]) : null,
                // Background Text Clipped Style
            ($use_background_text === 'on' && $bt_background_style === 'clipped') ? CommonStyle::style([
                'selector' => $bt_selector,
                'attr' => static::getAttr($attrs, 'bt_background_style', 'clipped'),
                'declarationFunction' => function (array $args) {
                    return "background-clip: text !important;-webkit-background-clip: text !important;color: transparent !important;-webkit-text-fill-color: transparent !important;";
                }
            ]) : null,
        ];

        // Filter out null values
        // $styles = array_filter($styles);

        Style::add([
            'id' => $args['id'],
            'name' => $args['name'],
            'orderIndex' => $args['orderIndex'],
            'storeInstance' => $args['storeInstance'],
            'styles' => $styles,
        ]);
    }
}

