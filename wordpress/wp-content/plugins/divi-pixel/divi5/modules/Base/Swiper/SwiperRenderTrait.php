<?php
/**
 *
 * @package DIPI\Modules\Base\Swiper
 * @since ??
 * 
 */

namespace DIPI\Modules\Base\Swiper;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Framework\Breakpoint\Breakpoint;
use DIPI\Traits\BaseRenderTrait;

trait SwiperRenderTrait {
    use BaseRenderTrait;

    private static $breakpoint_settings_cache = null;
    
    private static $breakpoint_widths_cache = null;
    
    private static $enabled_breakpoint_names_cache = null;
    
    private static $fallback_order_cache = [];

    private static function get_enabled_breakpoint_names()
    {
        if (self::$enabled_breakpoint_names_cache !== null) {
            return self::$enabled_breakpoint_names_cache;
        }
        
        self::$enabled_breakpoint_names_cache = [];
        
        if (class_exists('\ET\Builder\Framework\Breakpoint\Breakpoint')) {
            try {
                $enabled_names = Breakpoint::get_enabled_breakpoint_names();
                if (!empty($enabled_names) && is_array($enabled_names)) {
                    self::$enabled_breakpoint_names_cache = $enabled_names;
                    return self::$enabled_breakpoint_names_cache;
                }
            } catch (\Exception $e) {
            }
        }
        
        self::$enabled_breakpoint_names_cache = ['desktop', 'tablet', 'phone'];
        
        return self::$enabled_breakpoint_names_cache;
    }

    private static function get_breakpoint_settings()
    {
        if (self::$breakpoint_settings_cache !== null) {
            return self::$breakpoint_settings_cache;
        }
        
        self::$breakpoint_settings_cache = [];
        
        if (class_exists('\ET\Builder\Framework\Breakpoint\Breakpoint')) {
            try {
                $breakpoints = Breakpoint::get_enabled_breakpoints();
                if (!empty($breakpoints) && is_array($breakpoints)) {
                    self::$breakpoint_settings_cache = $breakpoints;
                    return self::$breakpoint_settings_cache;
                }
            } catch (\Exception $e) {
            }
        }
        
        return self::$breakpoint_settings_cache;
    }
    
    /**
     * Returns the min-width (in px) at which each breakpoint's settings apply.
     * Swiper uses min-width breakpoints: key N means "when viewport >= N, use these settings".
     * Divi uses: maxWidth for phone/phone_wide/tablet/tablet_wide (< that px), minWidth for widescreen/ultra_wide (> that px).
     * So we convert maxWidth breakpoints to min-width by ordering: smallest max = 0, next = prev_max+1, etc.
     */
    private static function get_breakpoint_widths()
    {
        if (self::$breakpoint_widths_cache !== null) {
            return self::$breakpoint_widths_cache;
        }
        
        self::$breakpoint_widths_cache = [];
        $breakpoints = static::get_breakpoint_settings();
        
        $max_width_breakpoints = [];
        $min_width_breakpoints = [];
        
        foreach ($breakpoints as $breakpoint) {
            $name = $breakpoint['name'] ?? '';
            if (empty($name)) {
                continue;
            }
            
            if (isset($breakpoint['maxWidth']['value'])) {
                $max = intval($breakpoint['maxWidth']['value']);
                $max_width_breakpoints[] = ['name' => $name, 'max' => $max];
            } elseif (isset($breakpoint['minWidth']['value'])) {
                $min = intval($breakpoint['minWidth']['value']);
                $min_width_breakpoints[] = ['name' => $name, 'min' => $min];
            }
        }
        
        usort($max_width_breakpoints, function ($a, $b) {
            return $a['max'] - $b['max'];
        });
        
        $prev_max = -1;
        foreach ($max_width_breakpoints as $bp) {
            $min_width = $prev_max < 0 ? 0 : $prev_max + 1;
            $prev_max = $bp['max'];
            static::set_breakpoint_width_cache($bp['name'], $min_width);
        }
        
        foreach ($min_width_breakpoints as $bp) {
            static::set_breakpoint_width_cache($bp['name'], $bp['min']);
        }
        
        if (!isset(self::$breakpoint_widths_cache['desktop'])) {
            $largest_max = $prev_max >= 0 ? $prev_max + 1 : 1024;
            static::set_breakpoint_width_cache('desktop', $largest_max);
        }
        
        return self::$breakpoint_widths_cache;
    }
    
    private static function set_breakpoint_width_cache($name, $width)
    {
        self::$breakpoint_widths_cache[$name] = $width;
        $normalized = strtolower(str_replace(['_', ' '], '', $name));
        if ($normalized !== $name) {
            self::$breakpoint_widths_cache[$normalized] = $width;
        }
        $underscore_variant = str_replace(' ', '_', strtolower($name));
        if ($underscore_variant !== $name && $underscore_variant !== $normalized) {
            self::$breakpoint_widths_cache[$underscore_variant] = $width;
        }
    }
    
    public static function get_breakpoint_width($breakpoint_name)
    {
        $bp_lower = strtolower($breakpoint_name);
        $normalized = str_replace(['_', ' '], '', $bp_lower);
        
        if ($normalized === 'phone') {
            return 0;
        }
        
        $breakpoint_widths = static::get_breakpoint_widths();
        
        if (isset($breakpoint_widths[$breakpoint_name])) {
            return $breakpoint_widths[$breakpoint_name];
        }
        
        if (isset($breakpoint_widths[$normalized])) {
            return $breakpoint_widths[$normalized];
        }
        
        $camelCase = ucfirst($normalized);
        if (isset($breakpoint_widths[$camelCase])) {
            return $breakpoint_widths[$camelCase];
        }
        
        foreach ($breakpoint_widths as $name => $width) {
            $name_normalized = strtolower(str_replace(['_', ' '], '', $name));
            if ($name_normalized === $normalized || 
                strpos($name_normalized, $normalized) !== false || 
                strpos($normalized, $name_normalized) !== false) {
                return $width;
            }
        }
        
        return null;
    }
    
    /**
     * Fallback min-width (px) when Breakpoint API doesn't provide one.
     * Matches Divi semantics: phone < 767, phone_wide < 860, tablet < 980, tablet_wide < 1024, desktop base, widescreen > 1500, ultra_wide > 1800.
     */
    private static function calculate_fallback_width($breakpoint, $sorted_breakpoints)
    {
        $bp_normalized = strtolower(str_replace(['_', ' '], '', $breakpoint));
        
        $index = array_search($breakpoint, $sorted_breakpoints);
        
        if ($index === false || $index === 0) {
            return $bp_normalized === 'phone' ? 0 : 768;
        }
        
        $prev_bp = $sorted_breakpoints[$index - 1] ?? null;
        if ($prev_bp) {
            $prev_width = static::get_breakpoint_width($prev_bp);
            if ($prev_width !== null && $prev_width > 0) {
                return $prev_width + 1;
            }
        }
        
        if (strpos($bp_normalized, 'ultra') !== false) {
            return 1921;
        } elseif ($bp_normalized === 'widescreen') {
            return 1281;
        } elseif (strpos($bp_normalized, 'tablet') !== false) {
            return strpos($bp_normalized, 'wide') !== false ? 980 : 860;
        } elseif (strpos($bp_normalized, 'phone') !== false) {
            return strpos($bp_normalized, 'wide') !== false ? 767 : 0;
        } elseif ($bp_normalized === 'desktop') {
            return 1024;
        }
        
        return 768;
    }

    private static function get_breakpoint_priority($breakpoint)
    {
        $bp_lower = strtolower($breakpoint);
        
        if (strpos($bp_lower, 'ultra') !== false) return 1;
        if ($bp_lower === 'widescreen') return 2;
        if ($bp_lower === 'desktop') return 3;
        if (strpos($bp_lower, 'tablet') !== false) {
            return strpos($bp_lower, 'wide') !== false ? 4 : 5;
        }
        if (strpos($bp_lower, 'phone') !== false) {
            return strpos($bp_lower, 'wide') !== false ? 6 : 7;
        }
        
        return 99;
    }

    private static function get_fallback_order($all_breakpoints)
    {
        $cache_key = implode('|', $all_breakpoints);
        if (isset(self::$fallback_order_cache[$cache_key])) {
            return self::$fallback_order_cache[$cache_key];
        }
        
        $breakpoint_priority = [];
        foreach ($all_breakpoints as $bp) {
            $breakpoint_priority[$bp] = static::get_breakpoint_priority($bp);
        }
        
        asort($breakpoint_priority);
        self::$fallback_order_cache[$cache_key] = array_keys($breakpoint_priority);
        
        return self::$fallback_order_cache[$cache_key];
    }

    public static function get_all_breakpoints($attrs, $prop_name)
    {
        $breakpoints = [];
        if (isset($attrs[$prop_name]['innerContent']) && is_array($attrs[$prop_name]['innerContent'])) {
            $breakpoints = array_keys($attrs[$prop_name]['innerContent']);
        }
        return $breakpoints;
    }

    public static function get_breakpoint_value($attrs, $prop_name, $breakpoint, $fallback_value = null)
    {
        if (isset($attrs[$prop_name]['innerContent'][$breakpoint]['value'])) {
            return $attrs[$prop_name]['innerContent'][$breakpoint]['value'];
        }
        return $fallback_value;
    }

    public static function get_responsive_value($attrs, $prop_name, $breakpoint, $default_value = null)
    {
        $fallback_order = ['ultra_wide', 'widescreen', 'desktop', 'tablet_wide', 'tablet', 'phone_wide', 'phone'];
        $current_index = array_search($breakpoint, $fallback_order);

        if (isset($attrs[$prop_name]['innerContent'][$breakpoint]['value'])) {
            return $attrs[$prop_name]['innerContent'][$breakpoint]['value'];
        }

        if ($current_index !== false) {
            for ($i = $current_index - 1; $i >= 0; $i--) {
                $fallback_breakpoint = $fallback_order[$i];
                if (isset($attrs[$prop_name]['innerContent'][$fallback_breakpoint]['value'])) {
                    return $attrs[$prop_name]['innerContent'][$fallback_breakpoint]['value'];
                }
            }
        }

        if (isset($attrs[$prop_name]['innerContent']['desktop']['value'])) {
            return $attrs[$prop_name]['innerContent']['desktop']['value'];
        }

        return $default_value;
    }

    public static function get_swiper_default_values()
    {
        $values = [
            'columns' => "4",
            'space_between' => "50",
            'effect' => "slide",
            'slide_shadows' => "on",
            'shadow_overlay_color' => "",
            'rotate' => "50",
            'speed' => "500",
            'loop' => "off",
            'autoplay' => "off",
            'autoplay_speed' => "5000",
            'pause_on_hover' => "on",
            'continues' => "off",
            'autoplay_reverse' => "off",
            'navigation' => "off",
            'navigation_on_hover' => "off",
            'pagination' => "off",
            'dynamic_bullets' => "on",
            'centered' => "off",
            'navigation_prev_icon_yn' => "off",
            'navigation_prev_icon' => array('unicode' => "&#x38;", 'type' => "divi", 'weight' => "400"),
            'navigation_next_icon_yn' => "off",
            'navigation_next_icon' => array('unicode' => "&#x39;", 'type' => "divi", 'weight' => "400"),
            'navigation_size' => "50",
            'navigation_padding' => "10",
            'navigation_color' => "#007aff",
            'navigation_bg_color' => "",
            'navigation_circle' => "off",
            'navigation_position_left' => "-66px",
            'navigation_position_right' => "-66px",
            'pagination_position' => "-40",
            'pagination_color' => "#d8d8d8",
            'pagination_active_color' => "",
        ];
        return $values;
    }

    public static function render_swiper(
        $attrs,
        $slider_content = "",
        $order_number = 0,
        $containerClassName = "",
        $wrapperClassName = "",
        $slideClassName = "",
        $additional_options = "",
        $responsive_options = array()
    ) {
        $swiper_default_values = static::get_swiper_default_values();

        $attr_breakpoint_keys = static::get_all_breakpoints($attrs, 'columns');

        $enabled_breakpoint_names = static::get_enabled_breakpoint_names();

        $enabled_normalized = [];
        foreach ($enabled_breakpoint_names as $name) {
            $normalized = strtolower(str_replace(['_', ' '], '', $name));
            $enabled_normalized[$normalized] = $name;
            $enabled_normalized[$name] = $name;
        }

        if (!empty($enabled_breakpoint_names) && !empty($attr_breakpoint_keys)) {
            $filtered_breakpoints = [];
            foreach ($attr_breakpoint_keys as $bp) {
                $bp_normalized = strtolower(str_replace(['_', ' '], '', $bp));
                if (isset($enabled_normalized[$bp]) || isset($enabled_normalized[$bp_normalized])) {
                    $filtered_breakpoints[] = $bp;
                }
            }

            $attr_breakpoint_keys = !empty($filtered_breakpoints) ? $filtered_breakpoints : $enabled_breakpoint_names;
        }

        if (empty($attr_breakpoint_keys)) {
            $all_breakpoints = $enabled_breakpoint_names ?: ['desktop', 'tablet', 'phone'];
        } else {
            // Emit data-* / JSON for every enabled breakpoint, not only keys present in attrs
            // (attrs often had desktop-only before module.json defined tablet/phone defaults).
            $all_breakpoints = array_values(array_unique(array_merge(
                $enabled_breakpoint_names ?: ['desktop', 'tablet', 'phone'],
                $attr_breakpoint_keys
            )));
        }

        $columns_data = [];
        $default_columns = static::getPropValue($attrs, 'columns') ?? $swiper_default_values['columns'];
        foreach ($all_breakpoints as $bp) {
            $columns_data[$bp] = static::get_responsive_value($attrs, 'columns', $bp, $default_columns);
        }

        $columns_desktop = $columns_data['desktop'] ?? $default_columns;
        $columns_tablet = $columns_data['tablet'] ?? $columns_desktop;
        $columns_phone = $columns_data['phone'] ?? $columns_tablet;

        $space_between_data = [];
        $default_space_between = static::getPropValue($attrs, 'space_between') ?? $swiper_default_values['space_between'];
        foreach ($all_breakpoints as $bp) {
            $space_between_data[$bp] = static::get_responsive_value($attrs, 'space_between', $bp, $default_space_between);
        }

        $space_between = $space_between_data['desktop'] ?? $default_space_between;
        $space_between_tablet = $space_between_data['tablet'] ?? $space_between;
        $space_between_phone = $space_between_data['phone'] ?? $space_between_tablet;

        $speed = $attrs['speed']['innerContent']['desktop']['value'] ?? $swiper_default_values['speed'];
        if (in_array('speed', $responsive_options)) {
            $speed_tablet = $attrs['speed']['innerContent']['tablet']['value'] ?? $speed;
            $speed_phone = $attrs['speed']['innerContent']['phone']['value'] ?? $speed_tablet;
            $speed = $speed . '|' . $speed_tablet . '|' . $speed_phone;
        }

        $loop = static::getPropValue($attrs, 'loop') ?? $swiper_default_values['loop'];
        $centered = static::getPropValue($attrs, 'centered') ?? $swiper_default_values['centered'];
        $autoplay = static::getPropValue($attrs, 'autoplay') ?? $swiper_default_values['autoplay'];
        $autoplay_speed = static::getPropValue($attrs, 'autoplay_speed') ?? $swiper_default_values['autoplay_speed'];
        $pause_on_hover = static::getPropValue($attrs, 'pause_on_hover') ?? $swiper_default_values['pause_on_hover'];

        $navigation = $attrs['navigation']['innerContent']['desktop']['value'] ?? $swiper_default_values['navigation'];
        $navigation_tablet = $attrs['navigation']['innerContent']['tablet']['value'] ?? $navigation;
        $navigation_phone = $attrs['navigation']['innerContent']['phone']['value'] ?? $navigation_tablet;

        $navigation_on_hover = $attrs['navigation_on_hover']['innerContent']['desktop']['value'] ?? $swiper_default_values['navigation_on_hover'];
        $navigation_on_hover_t = $attrs['navigation_on_hover']['innerContent']['tablet']['value'] ?? $navigation_on_hover;
        $navigation_on_hover_p = $attrs['navigation_on_hover']['innerContent']['phone']['value'] ?? $navigation_on_hover_t;

        $pagination = $attrs['pagination']['innerContent']['desktop']['value'] ?? $swiper_default_values['pagination'];
        $pagination_tablet = $attrs['pagination']['innerContent']['tablet']['value'] ?? $pagination;
        $pagination_phone = $attrs['pagination']['innerContent']['phone']['value'] ?? $pagination_tablet;

        $effect = static::getPropValue($attrs, 'effect') ?? "slide";
        $rotate = static::getPropValue($attrs, 'rotate') ?? $swiper_default_values['rotate'];
        $dynamic_bullets = static::getPropValue($attrs, 'dynamic_bullets') ?? $swiper_default_values['dynamic_bullets'];
        $slide_shadows = ('on' === (static::getPropValue($attrs, 'slide_shadows') ?? $swiper_default_values['slide_shadows'])) ? esc_attr('true') : esc_attr('false');

        // $centered = $effect === "coverflow" ? "off" : $centered;

        $breakpoints_config = [];
        $base_breakpoint = 'phone';
        $base_priority = 99;
        
        foreach ($all_breakpoints as $bp) {
            $priority = static::get_breakpoint_priority($bp);
            if ($priority > $base_priority) {
                $base_breakpoint = $bp;
                $base_priority = $priority;
            }
        }

        $sorted_breakpoints = static::get_fallback_order($all_breakpoints);
        $sorted_breakpoints = array_reverse($sorted_breakpoints);
        
        $breakpoint_widths = static::get_breakpoint_widths();
        
        foreach ($all_breakpoints as $bp) {
            $width = static::get_breakpoint_width($bp);
            $bp_normalized = strtolower(str_replace(['_', ' '], '', $bp));
            
            if ($width === null || $width <= 0) {
                foreach ($breakpoint_widths as $name => $configured_width) {
                    $name_normalized = strtolower(str_replace(['_', ' '], '', $name));
                    if ($name_normalized === $bp_normalized) {
                        $width = $configured_width;
                        break;
                    }
                }
                
                if ($width === null || $width <= 0) {
                    $index = array_search($bp, $sorted_breakpoints);
                    if ($index !== false && $index > 0) {
                        $prev_bp = $sorted_breakpoints[$index - 1] ?? null;
                        if ($prev_bp) {
                            $prev_width = static::get_breakpoint_width($prev_bp);
                            if ($prev_width !== null && $prev_width > 0) {
                                $width = $prev_width + 100;
                            }
                        }
                    }
                    
                    if ($width === null || $width <= 0) {
                        if ($bp_normalized === 'phone') {
                            $width = 0;
                        } else {
                            $width = static::calculate_fallback_width($bp, $sorted_breakpoints);
                        }
                    }
                }
            }
            
            if ($width <= 0 && $bp_normalized === 'phone') {
                continue;
            }
            
            if ($width <= 0) {
                $width = static::calculate_fallback_width($bp, $sorted_breakpoints);
            }

            $breakpoints_config[$width] = [
                'columns' => $columns_data[$bp] ?? $columns_desktop,
                'spaceBetween' => $space_between_data[$bp] ?? $space_between,
            ];
        }
        
        ksort($breakpoints_config);

        $base_breakpoint_config = [
            'columns' => $columns_data[$base_breakpoint] ?? $columns_phone,
            'spaceBetween' => $space_between_data[$base_breakpoint] ?? $space_between_phone,
        ];

        $base_breakpoint_config = [
            'columns' => $columns_phone,
            'spaceBetween' => $space_between_phone,
        ];

        $breakpoints_json = wp_json_encode($breakpoints_config);
        $base_config_json = wp_json_encode($base_breakpoint_config);

        $options = sprintf(
            'data-columnsphone="%1$s"
		    data-columnstablet="%2$s"
		    data-columnsdesktop="%3$s"
		    data-spacebetween="%4$s"
		    data-loop="%5$s"
		    data-speed="%6$s"
		    data-navigation="on"
		    data-pagination="on"
		    data-autoplay="%9$s"
		    data-autoplayspeed="%10$s"
		    data-pauseonhover="%11$s"
		    data-effect="%12$s"
		    data-rotate="%13$s"
		    data-dynamicbullets="%14$s"
		    data-ordernumber="%15$s"
		    data-centered="%16$s"
		    data-spacebetween_tablet="%17$s"
		    data-spacebetween_phone="%18$s"
		    data-shadow="%19$s"
		    data-breakpoints="%20$s"
		    data-baseconfig="%21$s"
            %22$s
            data-navigation_t="%23$s"
            data-navigation_m="%24$s"
            data-pagination_t="%25$s"
            data-pagination_m="%26$s"',
            esc_attr($columns_phone),
            esc_attr($columns_tablet),
            esc_attr($columns_desktop),
            esc_attr($space_between),
            esc_attr($loop),
            esc_attr($speed),
            esc_attr($navigation),
            esc_attr($pagination),
            esc_attr($autoplay),
            esc_attr($autoplay_speed), #10
            esc_attr($pause_on_hover),
            esc_attr($effect),
            esc_attr($rotate),
            esc_attr($dynamic_bullets),
            esc_attr($order_number), #15
            esc_attr($centered),
            esc_attr($space_between_tablet),
            esc_attr($space_between_phone),
            esc_attr($slide_shadows),
            esc_attr($breakpoints_json),
            esc_attr($base_config_json),
            esc_attr($additional_options),
            esc_attr($navigation_tablet),
            esc_attr($navigation_phone),
            esc_attr($pagination_tablet),
            esc_attr($pagination_phone)
        );

        $data_next_icon = static::getPropValue($attrs, 'navigation_next_icon') ?? $swiper_default_values['navigation_next_icon'];
        $data_prev_icon = static::getPropValue($attrs, 'navigation_prev_icon') ?? $swiper_default_values['navigation_prev_icon'];
        $data_next_icon = sprintf('data-icon="%1$s"', Utils::process_font_icon($data_next_icon));
        $data_prev_icon = sprintf('data-icon="%1$s"', Utils::process_font_icon($data_prev_icon));
        $next_icon = 'on' === (static::getPropValue($attrs, 'navigation_next_icon_yn') ?? $swiper_default_values['navigation_next_icon_yn']) ? $data_next_icon : 'data-icon="&#x39;"';
        $prev_icon = 'on' === (static::getPropValue($attrs, 'navigation_prev_icon_yn') ?? $swiper_default_values['navigation_prev_icon_yn']) ? $data_prev_icon : 'data-icon="&#x38;"';

        $navigation_desktop = $navigation === "on" ? "show_on_desktop_flex" : "hide_on_desktop";
        $navigation_tablet = $navigation_tablet === "on" ? "show_on_tablet_flex" : "hide_on_tablet";
        $navigation_phone = $navigation_phone === "on" ? "show_on_phone_flex" : "hide_on_phone";
        $navigation_additional_class = $navigation_desktop . " " . $navigation_tablet . " " . $navigation_phone;

        $navigation_on_hover_additional_class = "";
        if ($navigation_on_hover === "on") {
            $navigation_on_hover_additional_class = "show_on_hover";
        }
        if ($navigation_on_hover_t === "on") {
            $navigation_on_hover_additional_class .= " show_on_hover_t";
        }
        if ($navigation_on_hover_p === "on") {
            $navigation_on_hover_additional_class .= " show_on_hover_p";
        }

        $render_navigation = sprintf(
            '<div class="swiper-button-next swiper-arrow-button dipi-sbn%1$s %4$s %5$s" %2$s></div>
		    <div class="swiper-button-prev swiper-arrow-button dipi-sbp%1$s %4$s %5$s" %3$s></div>',
            $order_number,
            $next_icon,
            $prev_icon,
            $navigation_on_hover_additional_class,
            $navigation_additional_class
        );

        $pagination_desktop = $pagination === "on" ? "show_on_desktop_block" : "hide_on_desktop";
        $pagination_tablet = $pagination_tablet === "on" ? "show_on_tablet_block" : "hide_on_tablet";
        $pagination_phone = $pagination_phone === "on" ? "show_on_phone_block" : "hide_on_phone";
        $pagination_additional_class = $pagination_desktop . " " . $pagination_tablet . " " . $pagination_phone;

        $render_pagination = sprintf(
            '<div class="swiper-pagination dipi-sp%1$s %2$s"></div>',
            $order_number,
            $pagination_additional_class
        );

        $render_html = sprintf('
		    <div class="%5$s preloading" %2$s>
		        <div class="swiper-container">
		            <div class="%6$s">
		                %1$s
		            </div>
		        </div>
		        %3$s
		        <div class="swiper-container-horizontal">
		            %4$s
		        </div>
		    </div>',
            $slider_content,
            $options,
            $render_navigation,
            $render_pagination,
            $containerClassName,
            $wrapperClassName
        );

        return $render_html;
    }
}
