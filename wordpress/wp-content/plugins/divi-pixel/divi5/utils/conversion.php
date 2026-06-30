<?php
namespace DIPI\Utils;

if (!defined('ABSPATH')) {
    exit; // Prevent direct access.
}

class Conversion
{
    public function __construct()
    {

        // add_filter('divi.moduleLibrary.conversion.moduleConversionOutline', [$this, 'filter_module_conversion_outline'], 10, 2);
        add_filter('divi.moduleLibrary.conversion.valueExpansionFunctionMap', [$this, 'valueExpansionFunctionMap']);
        add_filter('divi.conversion.legacyAttributeNames', [$this, 'legacyAttributeNames']);
    }


    /**
     * All here listed arguments will be ignored during module conversion, even if they would be present in the conversion-outline.json
     * @param mixed $attributes
     * @return array
     */
    public function legacyAttributeNames($attributes)
    {
        $plugin_attributes = [
            // "first_heading_color_gradient_type",
            // "second_heading_color_gradient_type",
            // "background_text_color_gradient_type",
            // "fh_reveal_effect_color_gradient_type",
            // "sh_reveal_effect_color_gradient_type",
        ];
        return array_merge($attributes, $plugin_attributes);
    }

    public function valueExpansionFunctionMap($valueExpansionFunctionMapping)
    {
        $valueExpansionFunctionMapping['dipiConvertLinkElements'] = 'DIPI\Utils\Conversion::dipiConvertLinkElements';
        $valueExpansionFunctionMapping['dipiConvertButtonIconEnable'] = 'DIPI\Utils\Conversion::dipiConvertButtonIconEnable';
        $valueExpansionFunctionMapping['dipiConvertImagePosition'] = 'DIPI\Utils\Conversion::dipiConvertImagePosition';
        $valueExpansionFunctionMapping['dipiConvertTermIds'] = 'DIPI\Utils\Conversion::dipiConvertTermIds';
        $valueExpansionFunctionMapping['dipiConvertVisibleFields'] = 'DIPI\Utils\Conversion::dipiConvertVisibleFields';
        $valueExpansionFunctionMapping['dipiConvertCircleSize'] = 'DIPI\Utils\Conversion::dipiConvertCircleSize';
        $valueExpansionFunctionMapping['dipiConvertMultipleCheckboxes'] = 'DIPI\Utils\Conversion::dipiConvertMultipleCheckboxes';
        return $valueExpansionFunctionMapping;
    }

    public static function dipiConvertLinkElements($value)
    {
        $oldValues = explode('|', $value);
        $defaultValues = ['title', 'excerpt', 'image'];

        if (count($oldValues) !== 3) {
            return $defaultValues;
        }

        $convertedValues = [];
        foreach ($defaultValues as $index => $defaultValue) {
            if (isset($oldValues[$index]) && $oldValues[$index] === 'on') {
                $convertedValues[] = $defaultValue;
            }
        }

        return $convertedValues;
    }

    public static function dipiConvertButtonIconEnable($value)
    {
        return $value === 'on' ? 'on' : null;
    }

    public static function dipiConvertImagePosition($value)
    {
        $words = explode('_', $value);
        if (count($words) > 1) {
            return $words[1] . ' ' . $words[0];
        }
        return $value;
    }

    public static function dipiConvertTermIds($value)
    {
        return explode(',', $value);
    }

    public static function dipiConvertVisibleFields($value)
    {
        $options = ['icon_image', 'title', 'description', 'button'];
        $oldValues = explode('|', $value);

        $result = [];
        foreach ($options as $index => $option) {
            if (isset($oldValues[$index]) && $oldValues[$index] === 'on') {
                $result[] = $option;
            }
        }

        return $result;
    }

    public static function dipiConvertCircleSize($value)
    {
        //FIXME: Can this in theory be a % or em value?
        return intval($value) . 'px';
    }

    public static function dipiConvertMultipleCheckboxes($value)
    {
        $options = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        $oldValues = explode('|', $value);

        $result = [];
        foreach ($options as $index => $option) {
            if (isset($oldValues[$index]) && $oldValues[$index] === 'on') {
                $result[] = $option;
            }
        }

        return $result;
    }
}

//Immediately initialize the class so it registers itself
new Conversion();
