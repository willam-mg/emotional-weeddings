<?php
namespace DIPI\Modules\TextHighlighter;

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
                        'attrName'   => 'all_text',
					]),
                    $elements->style([
                        'attrName'   => 'prefix_text',
					]),
                    $elements->style([
                        'attrName'   => 'highlighted_text',
					]),
                    $elements->style([
                        'attrName'   => 'suffix_text',
					]),
                    $elements->style([
                        'attrName'   => 'highlighted_text_spacing',
					]),
                    CommonStyle::style([
						'selector'            => "$order_class .dipi-highlight-text-wrapper .dipi-highlight-prefix-text, $order_class .dipi-highlight-text-wrapper .dipi-highlight-suffix-text",
						'attr'                => static::getAttr($attrs, 'text_direction'),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return "display: $attrValue;";
                        }
					]),
                    CommonStyle::style([
						'selector'            => "$order_class .dipi-text-highlight-text ~span svg",
						'attr'                => static::getAttr($attrs, 'highlight_z_index'),
						'declarationFunction' => function ( array $args ) {
                            $value = $args['attrValue'] === "above" ? "1" : "-1";
                            return "z-index: $value !important;";
                        }
					]),
                    CommonStyle::style([
						'selector'            => "$order_class .dipi-text-highlight-text ~span svg",
						'attr'                => static::getAttr($attrs, 'highlight_line_cap'),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return "stroke-linecap: $attrValue;";
                        }
					]),
                    CommonStyle::style([
						'selector'            => "$order_class .dipi-text-highlight-wrapper svg path",
						'attr'                => static::getAttr($attrs, 'stroke_width'),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return "stroke-width: $attrValue;";
                        }
					]),
                    CommonStyle::style([
						'selector'            => "$order_class .dipi-text-highlight-wrapper svg path",
						'attr'                => static::getAttr($attrs, 'stroke_color'),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return "stroke: $attrValue;";
                        }
					]),
                    CommonStyle::style([
						'selector'            => "$order_class .dipi-text-highlight-wrapper svg",
						'attr'                => static::getAttr($attrs, 'shape_v_offset'),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'];
                            if ($attrValue > 0) return "top: calc(50% + {$attrValue}px) !important;";
                            $attrValue = -$attrValue;
                            return "top: calc(50% - {$attrValue}px) !important;";
                        }
					]),
                    CommonStyle::style([
						'selector'            => "$order_class .dipi-text-highlight-wrapper svg",
						'attr'                => static::getAttr($attrs, 'shape_h_offset'),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'];
                            if ($attrValue > 0) return "left: calc(50% + {$attrValue}px) !important;";
                            $attrValue = -$attrValue;
                            return "left: calc(50% - {$attrValue}px) !important;";
                        }
					]),
                    CommonStyle::style([
						'selector'            => "$order_class .dipi-text-highlight-wrapper svg",
						'attr'                => static::getAttr($attrs, 'shape_width_delta', '20px'),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'];
                            if ($attrValue > 0) return "width: calc(100% + {$attrValue}px);";
                            $attrValue = -$attrValue;
                            return "width: calc(100% - {$attrValue}px);";
                        }
					]),
                    CommonStyle::style([
						'selector'            => "$order_class .dipi-text-highlight-wrapper svg",
						'attr'                => static::getAttr($attrs, 'shape_height_delta', '20px'),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = (float)$args['attrValue'];
                            if ($attrValue > 0) return "height: calc(100% + {$attrValue}px);";
                            $attrValue = -$attrValue;
                            return "height: calc(100% - {$attrValue}px);";
                        }
					]),
				],
			]
		);
    }
}
