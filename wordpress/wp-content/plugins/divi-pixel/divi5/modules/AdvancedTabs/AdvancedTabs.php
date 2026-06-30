<?php
namespace DIPI\Modules\AdvancedTabs;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use DIPI\Traits\BaseModuleScriptDataTrait;

class AdvancedTabs implements DependencyInterface
{
    use RenderCallbackTrait;
    use ModuleClassNamesTrait;
    use ModuleStylesTrait;
    use BaseModuleScriptDataTrait;

    public function load()
    {
        $module_json_folder_path = plugin_dir_path(__FILE__);
        add_action(
            'init',
            function () use ($module_json_folder_path) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [AdvancedTabs::class, 'render_callback'],
                    ]
                );
            }
        );
    }
}

add_filter(
    'divi.conversion.postConvertAttrs',
    function (array $converted_attrs, string $module_name, array $original_attrs, bool $is_preset_conversion): array {
        if ('dipi/advanced-tabs' !== $module_name || $is_preset_conversion) {
            return $converted_attrs;
        }

        /** 
         * Convert enable_tabs_slider and individual attributes into a combined, responsive attribute 
         */
        //Desktop
        if (
            isset($original_attrs['enable_tabs_slider']) && $original_attrs['enable_tabs_slider'] === 'on' &&
            isset($original_attrs['enable_ts_on_wide']) && $original_attrs['enable_ts_on_wide'] === 'on'
        ) {
            $converted_attrs['enable_ts']['innerContent']['desktop']['value'] = 'on';
        }

        //Tablet
        if (
            isset($original_attrs['enable_tabs_slider']) && $original_attrs['enable_tabs_slider'] === 'on' &&
            isset($original_attrs['enable_ts_on_tab']) && $original_attrs['enable_ts_on_tab'] === 'on'
        ) {
            $converted_attrs['enable_ts']['innerContent']['tablet']['value'] = 'on';
        }

        // Phone
        if (
            isset($original_attrs['enable_tabs_slider']) && $original_attrs['enable_tabs_slider'] === 'on' &&
            isset($original_attrs['enable_ts_on_pho']) && $original_attrs['enable_ts_on_pho'] === 'on'
        ) {
            $converted_attrs['enable_ts']['innerContent']['phone']['value'] = 'on';
        }

        //Clean up unknownAttributes to prevent backwards compatibility mode
        if (isset($converted_attrs['unknownAttributes']['enable_tabs_slider'])) {
            unset($converted_attrs['unknownAttributes']['enable_tabs_slider']);
        }

        if (isset($converted_attrs['unknownAttributes']['enable_ts_on_wide'])) {
            unset($converted_attrs['unknownAttributes']['enable_ts_on_wide']);
        }

        if (isset($converted_attrs['unknownAttributes']['enable_ts_on_tab'])) {
            unset($converted_attrs['unknownAttributes']['enable_ts_on_tab']);
        }

        if (isset($converted_attrs['unknownAttributes']['enable_ts_on_pho'])) {
            unset($converted_attrs['unknownAttributes']['enable_ts_on_pho']);
        }

        if (isset($converted_attrs['unknownAttributes']) && empty($converted_attrs['unknownAttributes'])) {
            unset($converted_attrs['unknownAttributes']);
        }

        /**
         * Convert use_tabs_fullwidth as it's not properly converting when only tablet/phone are disbaled
         */
        if (isset($original_attrs['use_tabs_fullwidth']) && $original_attrs['use_tabs_fullwidth'] === 'off') {
            $converted_attrs['use_tabs_fullwidth']['innerContent']['desktop']['value'] = 'off';
        }
        if (isset($original_attrs['use_tabs_fullwidth_tablet']) && $original_attrs['use_tabs_fullwidth_tablet'] === 'off') {
            $converted_attrs['use_tabs_fullwidth']['innerContent']['tablet']['value'] = 'off';
        }
        if (isset($original_attrs['use_tabs_fullwidth_phone']) && $original_attrs['use_tabs_fullwidth_phone'] === 'off') {
            $converted_attrs['use_tabs_fullwidth']['innerContent']['phone']['value'] = 'off';
        }

        /**
         * Convert tabs_min_width as it's not properly converting using conversion-outline
         */
        if (isset($original_attrs['tabs_min_width'])) {
            $converted_attrs['tabs_min_width']['innerContent']['desktop']['value'] = $original_attrs['tabs_min_width'];
        }
        if (isset($original_attrs['tabs_min_width_tablet'])) {
            $converted_attrs['tabs_min_width']['innerContent']['tablet']['value'] = $original_attrs['tabs_min_width_tablet'];
        }
        if (isset($original_attrs['tabs_min_width_phone'])) {
            $converted_attrs['tabs_min_width']['innerContent']['phone']['value'] = $original_attrs['tabs_min_width_phone'];
        }

        /**
         * Convert tabs_max_width as it's not properly converting using conversion-outline
         */
        if (isset($original_attrs['tabs_max_width'])) {
            $converted_attrs['tabs_max_width']['innerContent']['desktop']['value'] = $original_attrs['tabs_max_width'];
        }
        if (isset($original_attrs['tabs_max_width_tablet'])) {
            $converted_attrs['tabs_max_width']['innerContent']['tablet']['value'] = $original_attrs['tabs_max_width_tablet'];
        }
        if (isset($original_attrs['tabs_max_width_phone'])) {
            $converted_attrs['tabs_max_width']['innerContent']['phone']['value'] = $original_attrs['tabs_max_width_phone'];
        }


        /**
         * Fix wrongly converted box shadows for Demo 9
        */
        if (isset($converted_attrs['tabs_item']['decoration']['boxShadow']['tablet']['value']['vertical']) && "" === $converted_attrs['tabs_item_active']['decoration']['boxShadow']['tablet']['value']['vertical']) {
            unset($converted_attrs['tabs_item']['decoration']['boxShadow']['tablet']['value']['vertical']);
        }
        if (isset($converted_attrs['tabs_item']['decoration']['boxShadow']['tablet']['value']['blur']) && "" === $converted_attrs['tabs_item_active']['decoration']['boxShadow']['tablet']['value']['blur']) {
            unset($converted_attrs['tabs_item']['decoration']['boxShadow']['tablet']['value']['blur']);
        }
        if (empty($converted_attrs['tabs_item']['decoration']['boxShadow']['tablet']['value'])) {
            unset($converted_attrs['tabs_item']['decoration']['boxShadow']['tablet']['value']);
        }
        if (empty($converted_attrs['tabs_item']['decoration']['boxShadow']['tablet'])) {
            unset($converted_attrs['tabs_item']['decoration']['boxShadow']['tablet']);
        }
        if (isset($converted_attrs['tabs_item_active']['decoration']['boxShadow']['tablet']['value']['vertical']) && "" === $converted_attrs['tabs_item_active']['decoration']['boxShadow']['tablet']['value']['vertical']) {
            unset($converted_attrs['tabs_item_active']['decoration']['boxShadow']['tablet']['value']['vertical']);
        }
        if (isset($converted_attrs['tabs_item_active']['decoration']['boxShadow']['tablet']['value']['blur']) && "" === $converted_attrs['tabs_item_active']['decoration']['boxShadow']['tablet']['value']['blur']) {
            unset($converted_attrs['tabs_item_active']['decoration']['boxShadow']['tablet']['value']['blur']);
        }
        if (empty($converted_attrs['tabs_item_active']['decoration']['boxShadow']['tablet']['value'])) {
            unset($converted_attrs['tabs_item_active']['decoration']['boxShadow']['tablet']['value']);
        }
        if (empty($converted_attrs['tabs_item_active']['decoration']['boxShadow']['tablet'])) {
            unset($converted_attrs['tabs_item_active']['decoration']['boxShadow']['tablet']);
        }

        return $converted_attrs;
    },
    10,
    4
);