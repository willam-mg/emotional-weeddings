<?php
namespace DIPI\Traits;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

trait BaseModuleScriptDataTrait
{
    public static function module_script_data($args)
    {
        $elements = $args['elements'];

        // Element Script Data Options.
        $elements->script_data(
            [
                'attrName' => 'module',
            ]
        );
    }
}