<?php

namespace DiviPixel;

/**
 * Shared map marker geometry for Divi Pixel custom markers, and Divi 5 runtime override data.
 */
class DIPI_Custom_Map_Marker
{
    /**
     * @param int    $width  Image width in pixels.
     * @param int    $height Image height in pixels.
     * @param string $anchor Anchor preset key (e.g. bottom_center).
     * @return array{anchorX:int,anchorY:int,anchorPointX:int,anchorPointY:int}
     */
    public static function compute_anchor_points($width, $height, $anchor)
    {
        if (!$anchor) {
            $anchor = 'bottom_center';
        }

        switch ($anchor) {
            case 'top_left':
                $anchorX = 0;
                $anchorY = 0;
                $anchorPointX = intval($width / 2);
                $anchorPointY = 0;
                break;
            case 'top_center':
                $anchorX = intval($width / 2);
                $anchorY = 0;
                $anchorPointX = 0;
                $anchorPointY = 0;
                break;
            case 'top_right':
                $anchorX = $width;
                $anchorY = 0;
                $anchorPointX = -intval($width / 2);
                $anchorPointY = 0;
                break;
            case 'center_left':
                $anchorX = 0;
                $anchorY = intval($height / 2);
                $anchorPointX = intval($width / 2);
                $anchorPointY = -intval($height / 2);
                break;
            case 'center_center':
                $anchorX = intval($width / 2);
                $anchorY = intval($height / 2);
                $anchorPointX = 0;
                $anchorPointY = -intval($height / 2);
                break;
            case 'center_right':
                $anchorX = $width;
                $anchorY = intval($height / 2);
                $anchorPointX = -intval($width / 2);
                $anchorPointY = -intval($height / 2);
                break;
            case 'bottom_left':
                $anchorX = 0;
                $anchorY = $height;
                $anchorPointX = intval($width / 2);
                $anchorPointY = -$height;
                break;
            case 'bottom_center':
                $anchorX = intval($width / 2);
                $anchorY = $height;
                $anchorPointX = 0;
                $anchorPointY = -$height;
                break;
            case 'bottom_right':
                $anchorX = $width;
                $anchorY = $height;
                $anchorPointX = -intval($width / 2);
                $anchorPointY = -$height;
                break;
            default:
                $anchorX = 0;
                $anchorY = 0;
                $anchorPointX = 0;
                $anchorPointY = 0;
        }

        return [
            'anchorX' => $anchorX,
            'anchorY' => $anchorY,
            'anchorPointX' => $anchorPointX,
            'anchorPointY' => $anchorPointY,
        ];
    }

    /**
     * @param string      $image_url  Marker image URL (same as stored in settings).
     * @param string|null $anchor_key Anchor preset or null for default.
     * @return ?array{width:int,height:int,anchorX:int,anchorY:int,anchorPointX:int,anchorPointY:int}
     */
    public static function get_geometry_for_marker_image($image_url, $anchor_key)
    {
        if (!isset($image_url) || '' === $image_url) {
            return null;
        }

        $image_size = @getimagesize($image_url);
        if (!$image_size || !is_array($image_size)) {
            return null;
        }

        $width = $image_size[0];
        $height = $image_size[1];
        $anchor = $anchor_key ?: 'bottom_center';
        $points = self::compute_anchor_points($width, $height, $anchor);

        return array_merge(
            ['width' => $width, 'height' => $height],
            $points
        );
    }

    /**
     * True only when Divi 5 is active, "Add Custom Map Marker" is enabled, and an image URL is set.
     */
    public static function should_enqueue_divi5_marker_script()
    {
        if (!function_exists('et_builder_d5_enabled') || !et_builder_d5_enabled()) {
            return false;
        }
        if (!DIPI_Settings::get_option('custom_map_marker')) {
            return false;
        }
        $url = DIPI_Settings::get_option('upload_custom_marker');
        return !empty($url);
    }

    /**
     * Data for wp_localize_script; null if the script should not run.
     *
     * @return ?array{iconUrl:string,size:array{w:int,h:int},anchor:array{x:int,y:int},anchorPoint:array{x:int,y:int}}
     */
    public static function get_frontend_localized_config()
    {
        if (!self::should_enqueue_divi5_marker_script()) {
            return null;
        }

        $image_url = DIPI_Settings::get_option('upload_custom_marker');
        $anchor_key = DIPI_Settings::get_option('custom_map_marker_anchor');
        $geom = self::get_geometry_for_marker_image($image_url, $anchor_key);
        if (!$geom) {
            return null;
        }

        return [
            'iconUrl' => esc_url_raw($image_url),
            'size' => ['w' => (int) $geom['width'], 'h' => (int) $geom['height']],
            'anchor' => ['x' => (int) $geom['anchorX'], 'y' => (int) $geom['anchorY']],
            'anchorPoint' => ['x' => (int) $geom['anchorPointX'], 'y' => (int) $geom['anchorPointY']],
        ];
    }
}
