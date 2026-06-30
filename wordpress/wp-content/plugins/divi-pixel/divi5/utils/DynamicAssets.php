<?php
namespace DIPI\Utils;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use ET\Builder\FrontEnd\Assets\DynamicAssetsUtils;

class DynamicAssets
{
    private static $instance = null;

    /**
     * Get the singleton instance.
     *
     * @return DynamicAssets
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct()
    {
        $this->register_filters();
    }

    /**
     * Register WordPress filters.
     */
    private function register_filters()
    {
        add_filter(
            'divi_frontend_assets_dynamic_assets_global_assets_list',
            [$this, 'divi_frontend_assets_dynamic_assets_global_assets_list'],
            10,
            3
        );
        
        add_filter(
            'divi_frontend_assets_dynamic_assets_late_global_assets_list',
            [$this, 'divi_frontend_assets_dynamic_assets_late_global_assets_list'],
            10,
            3
        );
    }

    /**
     * Check if the current post content contains a specific font type.
     *
     * @param string $type The font type to check for (e.g., 'fa', 'divi').
     * @return bool True if post content contains the specified type, false otherwise.
     */
    private function should_add_font_with_type($type)
    {
        //If at some point errors we have issues with the Theme Builder, we can try to use ET_Post_Stack::get_main_post_id() to get the actual post ID. get_the_ID could potentially be the wrong one
        $post_id = get_the_ID();

        if (!$post_id) {
            return false;
        }

        $post = get_post($post_id);
        if (!$post || empty($post->post_content)) {
            return false;
        }

        return strpos($post->post_content, '"type":"' . $type . '"') !== false;
    }

    /**
     * Filter callback for divi_frontend_assets_dynamic_assets_global_assets_list.
     *
     * @param array $early_global_asset_list The early global asset list.
     * @param array $assets_args The assets arguments.
     * @param mixed $dynamic_assets_instance The dynamic assets instance.
     * @return array Modified asset list.
     */
    public function divi_frontend_assets_dynamic_assets_global_assets_list($early_global_asset_list, $assets_args, $dynamic_assets_instance)
    {
        //Get assets prefix from `$assets_args` parameter or from utility.
        $assets_prefix = $assets_args['assets_prefix'] ?? DynamicAssetsUtils::get_dynamic_assets_path();

        // Add FontAwesome icons if post content contains "type":"fa"
        if ($this->should_add_font_with_type('fa')) {
            $early_global_asset_list['et_icons_fa'] = [
                'css' => "{$assets_prefix}/css/icons_fa_all.css",
            ];
        }

        // Add Divi icons if post content contains "type":"divi"
        if ($this->should_add_font_with_type('divi')) {
            $early_global_asset_list['et_icons_base'] = [
                'css' => "{$assets_prefix}/css/icons_base.css",
            ];
        }

        return $early_global_asset_list;
    }

    public function divi_frontend_assets_dynamic_assets_late_global_assets_list($late_global_asset_list, $assets_args, $dynamic_assets_instance){
        //If at any point we decided to enqueue font_awesome, then we should enqueue it
        global $dipi_enqueue_font_awesome;
        if($dipi_enqueue_font_awesome) {
            $assets_prefix = $assets_args['assets_prefix'] ?? DynamicAssetsUtils::get_dynamic_assets_path();

            $late_global_asset_list['et_icons_fa'] = [
                'css' => "{$assets_prefix}/css/icons_fa_all.css",
            ];
        }

        return $late_global_asset_list;
    }
}

// Initialize the singleton instance
DynamicAssets::instance();
