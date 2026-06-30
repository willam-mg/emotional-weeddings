<?php
namespace DiviPixel;
add_filter('et_late_global_assets_list', function ($assets, $assets_args, $et_dynamic_assets) {
    if (!isset($assets['et_icons_fa'])) {
        $assets_prefix = et_get_dynamic_assets_path();
        $assets['et_icons_fa'] = array(
            'css' => "{$assets_prefix}/css/icons_fa_all.css",
        );
    }
    if (!isset($assets['et_icons_all'])) {
        $assets_prefix = et_get_dynamic_assets_path();
        $assets['et_icons_all'] = array(
            'css' => "{$assets_prefix}/css/icons_all.css",
        );
    }
    return $assets;
}, 100, 3);

$breakpoint_mobile = DIPI_Settings::get_mobile_menu_breakpoint();
$collapse_submenu_prevent_parent_opening = DIPI_Settings::get_option('collapse_submenu_prevent_parent_opening');

$mobile_menu_font_weight = DIPI_Customizer::get_option('mobile_menu_font_weight');
$mobile_submenu_icon_on_collapse = DIPI_Customizer::get_option('mobile_submenu_icon_on_collapse');
$mobile_submenu_icon_on_collapse_border_radius = DIPI_Customizer::get_option('mobile_submenu_icon_on_collapse_border_radius');
$mobile_submenu_icon_on_collapse_color = DIPI_Customizer::get_option('mobile_submenu_icon_on_collapse_color');
$mobile_submenu_icon_on_collapse_background_color = DIPI_Customizer::get_option('mobile_submenu_icon_on_collapse_background_color');
$mobile_submenu_icon_on_expand = DIPI_Customizer::get_option('mobile_submenu_icon_on_expand');
$mobile_submenu_icon_on_expand_border_radius = DIPI_Customizer::get_option('mobile_submenu_icon_on_expand_border_radius');
$mobile_submenu_icon_on_expand_color = DIPI_Customizer::get_option('mobile_submenu_icon_on_expand_color');
$mobile_submenu_icon_on_expand_background_color = DIPI_Customizer::get_option('mobile_submenu_icon_on_expand_background_color');
?>

<style type="text/css" id="mobile-menu-collapse-submenu-css">
@media all and (max-width: <?php echo esc_html(intval($breakpoint_mobile)); ?>px) {
    body.dipi-collapse-submenu-mobile .et-l--header .et_mobile_menu li .sub-menu,
    body.dipi-collapse-submenu-mobile #main-header .et_mobile_menu li .sub-menu {
        width: 100%;
        overflow: hidden;
        max-height: 0;
        visibility: hidden !important;
    }

    body.dipi-collapse-submenu-mobile .et-l--header .et_mobile_menu li .dipi-collapse-closed,
    body.dipi-collapse-submenu-mobile #main-header .et_mobile_menu li .dipi-collapse-closed {
        width: 100%;
        max-height: 0px;
        display: none !important;
    }
    body.dipi-collapse-submenu-mobile .et-l--header .et_mobile_menu li .dipi-collapse-animating,
    body.dipi-collapse-submenu-mobile #main-header .et_mobile_menu li .dipi-collapse-animating {
        display: block !important;
    }

    body.dipi-collapse-submenu-mobile .et-l--header .et_mobile_menu li .dipi-collapse-opened,
    body.dipi-collapse-submenu-mobile #main-header .et_mobile_menu li .dipi-collapse-opened {
        width: 100%;
        max-height: 3000px;
        display: block !important;
        visibility: visible !important;

    }
    body.dipi-collapse-submenu-mobile .et-l--header .et_mobile_menu li ul.sub-menu,
    body.dipi-collapse-submenu-mobile #main-header .et_mobile_menu li ul.sub-menu{
        -webkit-transition: all 800ms ease-in-out;
        -moz-transition: all 800ms ease-in-out;
        -o-transition: all 800ms ease-in-out;
        transition: all 800ms ease-in-out;
    }

    body.dipi-collapse-submenu-mobile .et_mobile_menu li li {
        padding-left: 0 !important;
    }

    body.dipi-collapse-submenu-mobile .et-l--header .et_mobile_menu li.menu-item-has-children > a,
    body.dipi-collapse-submenu-mobile #main-header .et_mobile_menu li.menu-item-has-children > a {
        cursor: pointer;
        font-weight: <?php echo esc_html($mobile_menu_font_weight); ?> !important;
        position: relative;
    }

    body.dipi-collapse-submenu-mobile .et-l--header .et_mobile_menu li.menu-item-has-children ul li a,
    body.dipi-collapse-submenu-mobile #main-header .et_mobile_menu li.menu-item-has-children ul li a {
        font-weight: <?php echo esc_html($mobile_menu_font_weight); ?> !important;
    }


    body.dipi-collapse-submenu-mobile .et-l--header .et_mobile_menu li.menu-item-has-children>a:before,
    body.dipi-collapse-submenu-mobile #main-header .et_mobile_menu li.menu-item-has-children>a:before,
    body.dipi-collapse-submenu-mobile .et-l--header .et_mobile_menu li.menu-item-has-children>a:after,
    body.dipi-collapse-submenu-mobile #main-header .et_mobile_menu li.menu-item-has-children>a:after  {
        font-size: 18px;
        margin-right: 10px;
        display: inline-block;
        position: absolute;
        right: 5px;
        z-index: 10;
        cursor: pointer;
        font-family: "ETmodules";
        transition-timing-function: ease-in-out;
        transition-property: all;
        transition-duration: .4s;
        width: 1.6rem;
        height: 1.6rem;
        line-height: 1.6rem;
        text-align: center;
        vertical-align: middle;
    }

    /* Submenu closed */
    body.dipi-collapse-submenu-mobile .et-l--header .et_mobile_menu li.menu-item-has-children>a:before,
    body.dipi-collapse-submenu-mobile #main-header .et_mobile_menu li.menu-item-has-children>a:before {
        content: '<?php
            // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped 
            echo htmlspecialchars_decode($mobile_submenu_icon_on_collapse);
            // phpcs:enable
            ?>';
        color: <?php echo esc_html($mobile_submenu_icon_on_collapse_color) ?>;
        background-color: <?php echo esc_html($mobile_submenu_icon_on_collapse_background_color) ?>;
        border-radius: <?php echo esc_html($mobile_submenu_icon_on_collapse_border_radius); ?>%;
    }


    body.dipi-collapse-submenu-mobile .et-l--header .et_mobile_menu li.menu-item-has-children>a:after,
    body.dipi-collapse-submenu-mobile #main-header .et_mobile_menu li.menu-item-has-children>a:after{
        content: '<?php echo esc_html($mobile_submenu_icon_on_expand) ?>';
        color: <?php echo esc_html($mobile_submenu_icon_on_expand_color) ?>;
        background-color: <?php echo esc_html($mobile_submenu_icon_on_expand_background_color) ?>;
        border-radius: <?php echo esc_html($mobile_submenu_icon_on_expand_border_radius); ?>%;
        transform: rotate(-90deg);
        opacity: 0;
    }

    /* Submenu opened */
    body.dipi-collapse-submenu-mobile .et-l--header .et_mobile_menu li.menu-item-has-children>a.dipi-collapse-menu:before,
    body.dipi-collapse-submenu-mobile #main-header .et_mobile_menu li.menu-item-has-children>a.dipi-collapse-menu:before {
        transform: rotate(90deg);
        opacity: 0;
    }
    body.dipi-collapse-submenu-mobile .et-l--header .et_mobile_menu li.menu-item-has-children>a.dipi-collapse-menu:after,
    body.dipi-collapse-submenu-mobile #main-header .et_mobile_menu li.menu-item-has-children>a.dipi-collapse-menu:after {
        transform: rotate(0deg);
        opacity: 1;
    }

    /* body.dipi-collapse-submenu-mobile .et-l--header .et_mobile_menu li.menu-item-has-children>a:before,
    body.dipi-collapse-submenu-mobile #main-header .et_mobile_menu li.menu-item-has-children>a:before */


}
</style>