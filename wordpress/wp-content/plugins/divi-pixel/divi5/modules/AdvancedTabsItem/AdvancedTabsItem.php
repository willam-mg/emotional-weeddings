<?php
 

 namespace DIPI\Modules\AdvancedTabsItem;
 
if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use DIPI\Traits\BaseModuleScriptDataTrait;

class AdvancedTabsItem implements DependencyInterface {
    use RenderCallbackTrait;
    use ModuleClassNamesTrait;
    use ModuleStylesTrait;
    use BaseModuleScriptDataTrait;

    public function load() {
        $module_json_folder_path = plugin_dir_path(__FILE__);
        add_action(
            'init',
            function() use ( $module_json_folder_path ) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [ AdvancedTabsItem::class, 'render_callback' ],
                    ]
                );
            }
        );
    }
}


add_filter(
    'divi.conversion.postConvertAttrs',
    function (array $converted_attrs, string $module_name, array $original_attrs, bool $is_preset_conversion): array {
        if ('dipi/advanced-tabs-item' !== $module_name || $is_preset_conversion) {
            return $converted_attrs;
        }
        
        /**
         * Convert tab_icon_placement as it's not properly converting using conversion-outline
         */
        if(isset($original_attrs['tab_icon_placement'])){
            $converted_attrs['tab_icon_placement']['innerContent']['desktop']['value'] = $original_attrs['tab_icon_placement'];
        }
        if(isset($original_attrs['tab_icon_placement_tablet'])){
            $converted_attrs['tab_icon_placement']['innerContent']['tablet']['value'] = $original_attrs['tab_icon_placement_tablet'];
        }
        if(isset($original_attrs['tab_icon_placement_phone'])){
            $converted_attrs['tab_icon_placement']['innerContent']['phone']['value'] = $original_attrs['tab_icon_placement_phone'];
        }


        return $converted_attrs;
    },
    10,
    4
);