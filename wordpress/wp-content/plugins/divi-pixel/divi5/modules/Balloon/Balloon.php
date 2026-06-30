<?php
namespace DIPI\Modules\Balloon;
 
if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

require_once(__DIR__ . '/BalloonModuleController.php');

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use DIPI\Traits\BaseModuleScriptDataTrait;

class Balloon implements DependencyInterface {
    use RenderCallbackTrait;
    use ModuleClassNamesTrait;
    use ModuleStylesTrait;
    use BaseModuleScriptDataTrait;

    public function load() {
        $module_json_folder_path = plugin_dir_path(__FILE__);
        wp_enqueue_script('dipi_balloon_public');
        add_action(
            'init',
            function() use ( $module_json_folder_path ) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [ Balloon::class, 'render_callback' ],
                    ]
                );
            }
        );
    }
}