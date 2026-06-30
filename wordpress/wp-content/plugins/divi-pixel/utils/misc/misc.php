<?php
namespace DiviPixel;
/**
 */

if (!class_exists('DIPI_Misc')) {
    class DIPI_Misc
    {
        private static $instance = null;

        private function __construct()
        {
        }

        public static function instance()
        {
            if (self::$instance === null) {
                self::$instance = new self();
                self::$instance->init();
            }
            return self::$instance;
        }

        public static function is_vb()
        {
            return function_exists('et_core_is_fb_enabled') && et_core_is_fb_enabled();
        }

        private function init()
        {
            $this->fix_wordpress_6_7_auto_size_images();
        }

        private function fix_wordpress_6_7_auto_size_images()
        {
            add_filter('wp_content_img_tag', [$this, 'wp_content_img_tag']);
            add_filter('wp_get_attachment_image_attributes', [$this, 'wp_get_attachment_image_attributes']);
        }

        public function wp_content_img_tag($image)
        {
            return str_replace(' sizes="auto, ', ' sizes="', $image);
        }

        public function wp_get_attachment_image_attributes($attr)
        {
            if (isset($attr['sizes'])) {
                $attr['sizes'] = preg_replace('/^auto, /', '', $attr['sizes']);
            }
            return $attr;
        }
    }

    DIPI_Misc::instance();
}