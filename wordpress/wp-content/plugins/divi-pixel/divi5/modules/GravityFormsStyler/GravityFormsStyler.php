<?php
namespace DIPI\Modules\GravityFormsStyler;
 
if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

require_once(__DIR__ . '/GravityFormsStylerModuleController.php');

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use DIPI\Traits\BaseModuleScriptDataTrait;
 
class GravityFormsStyler implements DependencyInterface
{
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
                        'render_callback' => [ GravityFormsStyler::class, 'render_callback' ],
                    ]
                );
            }
        );
    }
}