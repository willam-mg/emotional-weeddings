<?php
namespace DIPI\Modules\StarRating;

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
						'attrName'   => 'title',
					]),
                    $elements->style([
						'attrName'   => 'description',
					]),
                    CommonStyle::style([
						'selector'            => "$order_class .dipi-wrapper .dipi-star-rating",
						'attr'                => static::getAttr($attrs, 'alignment'),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "text-align: $attrValue;";
                        }
					]),
                    CommonStyle::style([
						'selector'            => "$order_class .dipi-wrapper .dipi-star-rating",
						'attr'                => static::getAttr($attrs, 'star_rating_icon_size'),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "font-size: $attrValue;";
                        }
					]),
                    CommonStyle::style([
						'selector'            => "$order_class .dipi-wrapper .dipi-star-rating span:not(:last-of-type)",
						'attr'                => static::getAttr($attrs, 'star_rating_icon_spacing'),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "margin-right: $attrValue;";
                        }
					]),
                    CommonStyle::style([
						'selector'            => "$order_class .dipi-wrapper .dipi-star-rating span.dipi-star-full:before",
						'attr'                => static::getAttr($attrs, 'active_rating_icon_color'),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "color: $attrValue;";
                        }
					]),
                    CommonStyle::style([
						'selector'            => "$order_class .dipi-wrapper span.dipi-star-full",
						'attr'                => static::getAttr($attrs, 'active_rating_icon_color'),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "color: $attrValue;";
                        }
					]),
                    CommonStyle::style([
						'selector'            => "$order_class .dipi-wrapper .dipi-star-rating span.dipi-star-empty",
						'attr'                => static::getAttr($attrs, 'inactive_rating_icon_color'),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "color: $attrValue;";
                        }
					]),
                    CommonStyle::style([
						'selector'            => "$order_class .dipi-wrapper .dipi-star-rating .dipi-star-rating-number",
						'attr'                => static::getAttr($attrs, 'star_rating_number_color'),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "color: $attrValue;";
                        }
					]),
                    CommonStyle::style([
						'selector'            => "$order_class .dipi-wrapper .dipi-star-rating .dipi-star-rating-number",
						'attr'                => static::getAttr($attrs, 'star_rating_number_size'),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return  "font-size: $attrValue;";
                        }
					]),
				],
			]
		);
    }
}
