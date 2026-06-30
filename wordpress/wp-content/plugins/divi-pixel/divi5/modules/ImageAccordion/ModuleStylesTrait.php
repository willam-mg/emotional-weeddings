<?php
namespace DIPI\Modules\ImageAccordion;

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

        $always_visible_desktop = $attrs['always_visible']['innerContent']['desktop']['value'] ?? "off";
        $always_visible_tablet = $attrs['always_visible']['innerContent']['tablet']['value'] ?? $always_visible_desktop;
        $always_visible_phone = $attrs['always_visible']['innerContent']['phone']['value'] ?? $always_visible_tablet;
        $always_visible_fields_desktop = $attrs['always_visible_fields']['innerContent']['desktop']['value'];
        $always_visible_fields_tablet = $attrs['always_visible_fields']['innerContent']['tablet']['value'] ?? $always_visible_fields_desktop;
        $always_visible_fields_phone = $attrs['always_visible_fields']['innerContent']['phone']['value'] ?? $always_visible_fields_tablet;

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
                        'selector'            => "$order_class .dipi_image_accordion_wrapper",
                        'attr'                => static::getAttr($attrs, 'accordion_direction'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            if ($attrValue === "horizontal") {
                                return "flex-direction: row;";
                            } else {
                                return "flex-direction: column;";
                            }
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi-active",
                        'attr'                => static::getAttr($attrs, 'active_image_relative_width'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return "flex: $attrValue 0 auto !important;";
                        }
                    ]),
                    CommonStyle::style([
                        'selector'            => "$order_class .dipi_image_accordion_wrapper",
                        'attr'                => static::getAttr($attrs, 'accordion_height'),
                        'declarationFunction' => function ( array $args ) {
                            $attrValue = $args['attrValue'];
                            return "height: $attrValue !important;";
                        }
                    ]),
                    $always_visible_desktop === "on" ? CommonStyle::style([
                        'selector'            => "$order_class .dipi_image_accordion_child .dipi-accordion-content",
                        'attr'                => static::getAttr($attrs, 'always_visible'),
                        'declarationFunction' => function ( array $args ) {
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "desktop" ? "opacity: 1;" : "";
                        }
                    ]) : null,
                    $always_visible_desktop === "on" && in_array("icon_image", $always_visible_fields_desktop) ? CommonStyle::style([
                        'selector'            => "$order_class .dipi_image_accordion_child .dipi-accordion-image-icon",
                        'attr'                => static::getAttr($attrs, 'always_visible'),
                        'declarationFunction' => function ( array $args ) {
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "desktop" ? "opacity: 1;" : "";
                        }
                    ]) : null,
                    $always_visible_desktop === "on" && in_array("title", $always_visible_fields_desktop) ? CommonStyle::style([
                        'selector'            => "$order_class .dipi_image_accordion_child .dipi-accordion-title",
                        'attr'                => static::getAttr($attrs, 'always_visible'),
                        'declarationFunction' => function ( array $args ) {
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "desktop" ? "opacity: 1;" : "";
                        }
                    ]) : null,
                    $always_visible_desktop === "on" && in_array("description", $always_visible_fields_desktop) ? CommonStyle::style([
                        'selector'            => "$order_class .dipi_image_accordion_child .dipi-accordion-description",
                        'attr'                => static::getAttr($attrs, 'always_visible'),
                        'declarationFunction' => function ( array $args ) {
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "desktop" ? "opacity: 1;" : "";
                        }
                    ]) : null,
                    $always_visible_desktop === "on" && in_array("button", $always_visible_fields_desktop) ? CommonStyle::style([
                        'selector'            => "$order_class .dipi_image_accordion_child .dipi-accordion-button-wrap",
                        'attr'                => static::getAttr($attrs, 'always_visible'),
                        'declarationFunction' => function ( array $args ) {
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "desktop" ? "opacity: 1;" : "";
                        }
                    ]) : null,
                    $always_visible_tablet === "on" ? CommonStyle::style([
                        'selector'            => "$order_class .dipi_image_accordion_child .dipi-accordion-content",
                        'attr'                => static::getAttr($attrs, 'always_visible'),
                        'declarationFunction' => function ( array $args ) {
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "tablet" ? "opacity: 1;" : "";
                        }
                    ]) : null,
                    $always_visible_tablet === "on" && in_array("icon_image", $always_visible_fields_tablet) ? CommonStyle::style([
                        'selector'            => "$order_class .dipi_image_accordion_child .dipi-accordion-image-icon",
                        'attr'                => static::getAttr($attrs, 'always_visible'),
                        'declarationFunction' => function ( array $args ) {
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "tablet" ? "opacity: 1;" : "";
                        }
                    ]) : null,
                    $always_visible_tablet === "on" && in_array("title", $always_visible_fields_tablet) ? CommonStyle::style([
                        'selector'            => "$order_class .dipi_image_accordion_child .dipi-accordion-title",
                        'attr'                => static::getAttr($attrs, 'always_visible'),
                        'declarationFunction' => function ( array $args ) {
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "tablet" ? "opacity: 1;" : "";
                        }
                    ]) : null,
                    $always_visible_tablet === "on" && in_array("description", $always_visible_fields_tablet) ? CommonStyle::style([
                        'selector'            => "$order_class .dipi_image_accordion_child .dipi-accordion-description",
                        'attr'                => static::getAttr($attrs, 'always_visible'),
                        'declarationFunction' => function ( array $args ) {
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "tablet" ? "opacity: 1;" : "";
                        }
                    ]) : null,
                    $always_visible_tablet === "on" && in_array("button", $always_visible_fields_tablet) ? CommonStyle::style([
                        'selector'            => "$order_class .dipi_image_accordion_child .dipi-accordion-button-wrap",
                        'attr'                => static::getAttr($attrs, 'always_visible'),
                        'declarationFunction' => function ( array $args ) {
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "tablet" ? "opacity: 1;" : "";
                        }
                    ]) : null,
                    $always_visible_phone === "on" ? CommonStyle::style([
                        'selector'            => "$order_class .dipi_image_accordion_child .dipi-accordion-content",
                        'attr'                => static::getAttr($attrs, 'always_visible'),
                        'declarationFunction' => function ( array $args ) {
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "phone" ? "opacity: 1;" : "";
                        }
                    ]) : null,
                    $always_visible_phone === "on" && in_array("icon_image", $always_visible_fields_phone) ? CommonStyle::style([
                        'selector'            => "$order_class .dipi_image_accordion_child .dipi-accordion-image-icon",
                        'attr'                => static::getAttr($attrs, 'always_visible'),
                        'declarationFunction' => function ( array $args ) {
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "phone" ? "opacity: 1;" : "";
                        }
                    ]) : null,
                    $always_visible_phone === "on" && in_array("title", $always_visible_fields_phone) ? CommonStyle::style([
                        'selector'            => "$order_class .dipi_image_accordion_child .dipi-accordion-title",
                        'attr'                => static::getAttr($attrs, 'always_visible'),
                        'declarationFunction' => function ( array $args ) {
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "phone" ? "opacity: 1;" : "";
                        }
                    ]) : null,
                    $always_visible_phone === "on" && in_array("description", $always_visible_fields_phone) ? CommonStyle::style([
                        'selector'            => "$order_class .dipi_image_accordion_child .dipi-accordion-description",
                        'attr'                => static::getAttr($attrs, 'always_visible'),
                        'declarationFunction' => function ( array $args ) {
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "phone" ? "opacity: 1;" : "";
                        }
                    ]) : null,
                    $always_visible_phone === "on" && in_array("button", $always_visible_fields_phone) ? CommonStyle::style([
                        'selector'            => "$order_class .dipi_image_accordion_child .dipi-accordion-button-wrap",
                        'attr'                => static::getAttr($attrs, 'always_visible'),
                        'declarationFunction' => function ( array $args ) {
                            $breakpoint = $args['breakpoint'];
                            return $breakpoint === "phone" ? "opacity: 1;" : "";
                        }
                    ]) : null,
				],
			]
		);
    }
}
