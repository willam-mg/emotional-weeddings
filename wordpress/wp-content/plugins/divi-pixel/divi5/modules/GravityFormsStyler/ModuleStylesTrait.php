<?php
namespace DIPI\Modules\GravityFormsStyler;

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

        $use_custom_select_arrow = static::getAttrByMode($attrs, "use_custom_select_arrow", "off");
        $select_arrow_use_icon = static::getAttrByMode($attrs, "select_arrow_use_icon", "off");

        $form_title_selector = "$order_class .gform_title";
        $form_desc_selector = "$order_class .gform_description";
        $address_country_selector = "$order_class .gform_wrapper .ginput_address_country select, $order_class .gform_wrapper  .ginput_address_state select";
        $address_country_arrow_selector = "$order_class .gform_wrapper .ginput_address_country:after, $order_class .gform_wrapper .ginput_address_state:after";
        $select_field_selector = $address_country_selector.", $order_class .gform_wrapper .gfield .ginput_container_select select.gfield_select";
        $select_arrow_selector = $address_country_arrow_selector.", $order_class .gform_wrapper .gfield .ginput_container_select:after";
        $consent_field_button_selector = "$order_class .gform_wrapper .gfield .ginput_container_consent input[type=checkbox]";
        $checkbox_radio_button_selector = $consent_field_button_selector.", "."$order_class .gform_wrapper .gfield .gchoice .gfield-choice-input";
        $address_country_option_selector = "$order_class .gform_wrapper .ginput_address_country select option, $order_class .gform_wrapper .ginput_address_state select option";
        $select_option_selector = $address_country_option_selector . ", $order_class .gform_wrapper .gfield select.gfield_select option";
        $consent_field_label_selector = "$order_class .gform_wrapper .gfield .ginput_container_consent .gfield_consent_label";
        $checkbox_radio_label_selector = $consent_field_label_selector.", "."$order_class .gform_wrapper .gfield .gchoice > label";
        $input_field_selector = "$order_class :is(.gform_wrapper .ginput_container_time.gfield_time_ampm select, .gform_wrapper .gfield .ginput_container input:not(.gfield-choice-input))";
        $textarea_field_selector = "$order_class :is(.gform_wrapper .ginput_container_multiselect select, .gform_wrapper .gfield textarea.textarea)";

        $input_box_shadow_style = $attrs['input_field']['decoration']['boxShadow']['desktop']['value']['style'] ?? "none";
        $textarea_box_shadow_style = $attrs['textarea_field']['decoration']['boxShadow']['desktop']['value']['style'] ?? "none";
        $select_box_shadow_style = $attrs['select_field']['decoration']['boxShadow']['desktop']['value']['style'] ?? "none";

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
						'selector'            => $form_title_selector,
						'attr'                => static::getAttr($attrs, 'form_title', 'gf_title'),
						'declarationFunction' => function ( array $args ) {
                            $form_title = $args["attrValue"] === "hide" ? "none" : "block";
                            return "display: $form_title;";
                        }
					]),
                    CommonStyle::style([
						'selector'            => $form_desc_selector,
						'attr'                => static::getAttr($attrs, 'form_desc', 'gf_desc'),
						'declarationFunction' => function ( array $args ) {
                            $form_desc = $args["attrValue"] === "hide" ? "none" : "block";
                            return "display: $form_desc;";
                        }
					]),
                    $elements->style([
						'attrName'   => 'form_title',
					]),
                    $elements->style([
						'attrName'   => 'form_desc',
					]),
                    $elements->style([
						'attrName'   => 'progress_bar',
					]),
                    $elements->style([
						'attrName'   => 'progress_active_bar',
					]),
                    $elements->style([
						'attrName'   => 'field_container',
					]),
                    $elements->style([
						'attrName'   => 'field_description',
					]),
                    $elements->style([
						'attrName'   => 'input_field',
					]),
                    $elements->style([
						'attrName'   => 'textarea_field',
					]),
                    $elements->style([
						'attrName'   => 'select_field',
					]),
                    $use_custom_select_arrow === "on" ? CommonStyle::style([
						'selector'            => $select_field_selector,
						'attr'                => static::getAttr($attrs, 'use_custom_select_arrow', ''),
						'declarationFunction' => function ( array $args ) {
                            return "-webkit-appearance: none;-moz-appearance: none;appearance: none;background-image: none;";
                        }
					]) : null,
                    $use_custom_select_arrow === "on" ? $elements->style([
						'attrName'   => 'select_arrow',
					]) : null,
                    ($use_custom_select_arrow === "on" && $select_arrow_use_icon === "on") ? CommonStyle::style([
						'selector'            => $select_arrow_selector,
						'attr'                => static::getAttr($attrs, 'select_arrow_icon'),
						'declarationFunction' => [static::class, "icon_font_declaration"],
					]) : null,
                    ($use_custom_select_arrow === "on" && $select_arrow_use_icon === "on") ? CommonStyle::style([
						'selector'            => $select_arrow_selector,
						'attr'                => static::getAttr($attrs, 'select_arrow_icon_color', ''),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "color: $attrValue;";
                        }
					]) : null,
                    ($use_custom_select_arrow === "on" && $select_arrow_use_icon === "on") ? CommonStyle::style([
						'selector'            => $select_arrow_selector,
						'attr'                => static::getAttr($attrs, 'select_arrow_icon_size', ''),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "font-size: $attrValue;";
                        }
					]) : null,
                    $use_custom_select_arrow !== "on" ? CommonStyle::style([
						'selector'            => $select_arrow_selector,
						'attr'                => static::getAttr($attrs, 'use_custom_select_arrow', ''),
						'declarationFunction' => function ( array $args ) {
                            return "background-image:none";
                        }
					]) : null,
                    CommonStyle::style([
						'selector'      => $select_option_selector,
						'attr'          => static::getAttr($attrs, 'select_option_bg_color', ''),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "background-color: $attrValue;";
                        }
					]),
                    $elements->style([
						'attrName'   => 'checkbox_radio_container',
					]),
                    $elements->style([
						'attrName'   => 'checkbox_radio_one_option',
					]),
                    CommonStyle::style([
						'selector'      => $checkbox_radio_button_selector,
						'attr'          => static::getAttr($attrs, 'checkbox_radio_button_size', ''),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "width: $attrValue;";
                        }
					]),
                    CommonStyle::style([
						'selector'      => $checkbox_radio_button_selector,
						'attr'          => static::getAttr($attrs, 'checkbox_radio_button_size', ''),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "height: $attrValue;";
                        }
					]),
                    CommonStyle::style([
						'selector'      => $checkbox_radio_button_selector,
						'attr'          => static::getAttr($attrs, 'checkbox_radio_button_color', ''),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "accent-color: $attrValue!important;";
                        }
					]),
                    $elements->style([
						'attrName'   => 'checkbox_radio_label',
                        'styleProps' => [
                            'selector' => $checkbox_radio_label_selector,
                        ]
					]),
                    $elements->style([
						'attrName'   => 'section_field',
					]),
                    $elements->style([
						'attrName'   => 'html_field',
					]),
                    $elements->style([
						'attrName'   => 'consent_field',
					]),
                    CommonStyle::style([
						'selector'      => $consent_field_button_selector,
						'attr'          => static::getAttr($attrs, 'consent_field_button_size', ''),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "width: $attrValue;";
                        }
					]),
                    CommonStyle::style([
						'selector'      => $consent_field_button_selector,
						'attr'          => static::getAttr($attrs, 'consent_field_button_size', ''),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "height: $attrValue;";
                        }
					]),
                    CommonStyle::style([
						'selector'      => $consent_field_button_selector,
						'attr'          => static::getAttr($attrs, 'consent_field_button_color', ''),
						'declarationFunction' => function ( array $args ) {
                            $attrValue = $args["attrValue"];
                            return "accent-color: $attrValue!important;";
                        }
					]),
                    $elements->style([
						'attrName'   => 'consent_field_label',
					]),
                    $elements->style([
						'attrName'   => 'confirm_msg',
					]),
                    $elements->style([
						'attrName'   => 'text',
					]),
                    $elements->style([
						'attrName'   => 'pagination_title',
					]),
                    $elements->style([
						'attrName'   => 'pagination_cur_page',
					]),
                    $elements->style([
						'attrName'   => 'pagination_count',
					]),
                    $elements->style([
						'attrName'   => 'progressbar_label',
					]),
                    $elements->style([
						'attrName'   => 'field_label',
					]),
                    $elements->style([
						'attrName'   => 'required_field_indicator',
					]),
                    $elements->style([
						'attrName'   => 'field_description',
					]),
                    $elements->style([
						'attrName'   => 'input_field',
					]),
                    $elements->style([
						'attrName'   => 'textarea_field',
					]),
                    $elements->style([
						'attrName'   => 'select_dropdown_field',
					]),
                    $elements->style([
						'attrName'   => 'section_title',
					]),
                    $elements->style([
						'attrName'   => 'section_desc',
					]),
                    $elements->style([
						'attrName'   => 'html_field',
					]),
                    $elements->style([
						'attrName'   => 'header',
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
						'attrName'   => 'focus_field',
					]),
                    $elements->style([
						'attrName'   => 'input_field_focus',
					]),
                    $elements->style([
						'attrName'   => 'all',
					]),
                    $elements->style([
						'attrName'   => 'submit',
					]),
                    $elements->style([
						'attrName'   => 'prev',
					]),
                    $elements->style([
						'attrName'   => 'next',
					]),
                    $input_box_shadow_style === "none" ? CommonStyle::style([
                        'selector'            => $input_field_selector,
                        'attr'                => static::getAttr($attrs, 'input_field', ''),
                        'declarationFunction' => function ( array $args ) {
                            return "box-shadow: none!important;";
                        }
                    ]) : null,
                    $textarea_box_shadow_style === "none" ? CommonStyle::style([
                        'selector'            => $textarea_field_selector,
                        'attr'                => static::getAttr($attrs, 'textarea_field', ''),
                        'declarationFunction' => function ( array $args ) {
                            return "box-shadow: none!important;";
                        }
                    ]) : null,
                    $select_box_shadow_style === "none" ? CommonStyle::style([
                        'selector'            => $select_field_selector,
                        'attr'                => static::getAttr($attrs, 'select_field', ''),
                        'declarationFunction' => function ( array $args ) {
                            return "box-shadow: none!important;";
                        }
                    ]) : null,
				],
			]
		);
    }
}
