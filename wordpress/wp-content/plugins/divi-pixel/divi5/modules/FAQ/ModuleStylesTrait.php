<?php
namespace DIPI\Modules\FAQ;

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

        $faq_layout = static::getAttrByMode($attrs, 'faq_layout', 'toggles');
        $icon_closed_bg_color = static::getAttrByMode($attrs, 'icon_closed_bg_color', '');
        $icon_open_bg_color = static::getAttrByMode($attrs, 'icon_open_bg_color', '');
        $icon_animate = static::getAttrByMode($attrs, 'icon_animate', 'off');
        $icon_animate_delay = static::getAttrByMode($attrs, 'icon_animate_delay', 'off');
        $title_closed_text_align = $attrs['title_closed_font']['decoration']['font']['font']['desktop']['value']['textAlign'] ?? 'left';
        $title_open_text_align = $attrs['title_open_font']['decoration']['font']['font']['desktop']['value']['textAlign'] ?? 'left';

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
                    $elements->style([
                        'attrName'   => 'icon_open_padding',
                    ]),
                    $elements->style([
                        'attrName'   => 'icon_closed_padding',
                    ]),
                    $elements->style([
                        'attrName'   => 'entry_closed_spacing',
                    ]),
                    $elements->style([
                        'attrName'   => 'entry_open_spacing',
                    ]),
                    $elements->style([
                        'attrName'   => 'title_padding_closed',
                    ]),
                    $elements->style([
                        'attrName'   => 'title_padding_open',
                    ]),
                    $elements->style([
                        'attrName'   => 'content_padding_closed',
                    ]),
                    $elements->style([
                        'attrName'   => 'content_padding_open',
                    ]),
                    $elements->style([
                        'attrName'   => 'title_closed_font',
                    ]),
                    $elements->style([
                        'attrName'   => 'title_closed_border',
                    ]),
                    $elements->style([
                        'attrName'   => 'title_open_font',
                    ]),
                    $elements->style([
                        'attrName'   => 'title_open_border',
                    ]),
                    $elements->style([
                        'attrName'   => 'content_closed',
                    ]),
                    $elements->style([
                        'attrName'   => 'content_open',
                    ]),
                    $elements->style([
                        'attrName'   => 'content_link_font',
                    ]),
                    $elements->style([
                        'attrName'   => 'entries_closed',
                    ]),
                    $elements->style([
                        'attrName'   => 'entries_open',
                    ]),
                    $faq_layout !== "plain" ? CommonStyle::style([
                        'selector'            => $order_class . ' .dipi-faq-icon-open',
                        'attr'                => static::getAttr($attrs, 'icon_open'),
                        'declarationFunction' => [ static::class, 'icon_font_declaration' ],
                    ]) : null,
                    $faq_layout !== "plain" ? CommonStyle::style([
                        'selector'            => $order_class . ' .dipi-faq-icon-closed',
                        'attr'                => static::getAttr($attrs, 'icon_closed'),
                        'declarationFunction' => [ static::class, 'icon_font_declaration' ],
                    ]) : null,
                    $faq_layout !== "plain" ? CommonStyle::style([
                        'selector'  => $order_class . ' .dipi-faq-title .dipi-faq-icon-closed',
                        'attr'      => static::getAttr($attrs, 'icon_closed_color'),
                        'declarationFunction' => function ( array $args ) use ($icon_closed_bg_color) {
                            $attrValue = $args["attrValue"];
                            return "color: $attrValue; background: $icon_closed_bg_color;";
                        }
                    ]) : null,
                    $faq_layout !== "plain" ? CommonStyle::style([
                        'selector'  => $order_class . ' .dipi-faq-title .dipi-faq-icon-closed',
                        'attr'      => static::getAttr($attrs, 'icon_closed_font_size'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "font-size: $attrValue;";
                        }
                    ]) : null,
                    $faq_layout !== "plain" ? CommonStyle::style([
                        'selector'  => $order_class . ' .dipi-faq-title .dipi-faq-icon-closed',
                        'attr'      => static::getAttr($attrs, 'icon_closed_border_radius'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "border-radius: $attrValue;";
                        }
                    ]) : null,
                    $faq_layout !== "plain" ? CommonStyle::style([
                        'selector'  => $order_class . ' .dipi-faq-title .dipi-faq-icon-open',
                        'attr'      => static::getAttr($attrs, 'icon_open_color'),
                        'declarationFunction' => function ( array $args ) use ($icon_open_bg_color) {
                            $attrValue = $args["attrValue"];
                            return "color: $attrValue; background: $icon_open_bg_color;";
                        }
                    ]) : null,
                    $faq_layout !== "plain" ? CommonStyle::style([
                        'selector'  => $order_class . ' .dipi-faq-title .dipi-faq-icon-open',
                        'attr'      => static::getAttr($attrs, 'icon_open_font_size'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "font-size: $attrValue;";
                        }
                    ]) : null,
                    $faq_layout !== "plain" ? CommonStyle::style([
                        'selector'  => $order_class . ' .dipi-faq-title .dipi-faq-icon-open',
                        'attr'      => static::getAttr($attrs, 'icon_open_border_radius'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "border-radius: $attrValue;";
                        }
                    ]) : null,
                    $faq_layout !== "plain" && $icon_animate === "on" ? CommonStyle::style([
                        'selector'  => $order_class . ' .open .dipi-faq-title .dipi-faq-icon-closed',
                        'attr'      => static::getAttr($attrs, 'icon_animate'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "transform: rotate(90deg);";
                        }
                    ]) : null,
                    $faq_layout !== "plain" && $icon_animate === "on" ? CommonStyle::style([
                        'selector'  => $order_class . ' .closed .dipi-faq-title .dipi-faq-icon-open',
                        'attr'      => static::getAttr($attrs, 'icon_animate'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "transform: rotate(-90deg);";
                        }
                    ]) : null,
                    $faq_layout !== "plain" && $icon_animate_delay === "on" && $icon_animate === "on" ? CommonStyle::style([
                        'selector'  => $order_class . ' .dipi-faq-title .dipi-faq-icon-open, ' . $order_class . ' .dipi-faq-title .dipi-faq-icon-closed',
                        'attr'      => static::getAttr($attrs, 'icon_animate_delay'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "transition-delay: 0.3s;";
                        }
                    ]) : null,
                    CommonStyle::style([
                        'selector'  => $order_class . ' .dipi-faq-entry',
                        'attr'      => static::getAttr($attrs, 'entry_background_closed'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "background-color: $attrValue;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'  => $order_class . ' .dipi-faq-entry.open',
                        'attr'      => static::getAttr($attrs, 'entry_background_open'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "background-color: $attrValue;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'  => $order_class . ' .dipi-faq-entry .dipi-faq-title',
                        'attr'      => static::getAttr($attrs, 'title_background_closed'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "background-color: $attrValue;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'  => $order_class . ' .dipi-faq-entry.open .dipi-faq-title',
                        'attr'      => static::getAttr($attrs, 'title_background_open'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "background-color: $attrValue;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'  => $order_class . ' .dipi-faq-entry .dipi-faq-title span',
                        'attr'      => static::getAttr($attrs, 'title_background_closed'),
                        'declarationFunction' => function ( array $args ) use ($title_closed_text_align) {
                            $attrValue = $args["attrValue"];
                            return "justify-content: $title_closed_text_align;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'  => $order_class . ' .dipi-faq-entry.open .dipi-faq-title span',
                        'attr'      => static::getAttr($attrs, 'title_background_open'),
                        'declarationFunction' => function ( array $args ) use ($title_open_text_align) {
                            $attrValue = $args["attrValue"];
                            return "justify-content: $title_open_text_align;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'  => $order_class . ' .dipi-faq-entry .dipi-faq-content',
                        'attr'      => static::getAttr($attrs, 'content_background_closed'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "background-color: $attrValue;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'  => $order_class . ' .dipi-faq-entry.open .dipi-faq-content',
                        'attr'      => static::getAttr($attrs, 'content_background_open'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "background-color: $attrValue;";
                        }
                    ]),
				],
			]
		);
    }
}
