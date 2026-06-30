<?php

/*  Copyright 2022 Carlos Doral Pérez

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

    $url_css = CDP_MANTENIMIENTO_URL_FRONTAL_FX . '/css/burbujas.css';
    $url_js = CDP_MANTENIMIENTO_URL_FRONTAL_FX . '/js/burbujas.js';
    $site_name = get_bloginfo( 'name' );
    $site_description = get_bloginfo( 'description' );
    $mensaje_seguro = \cdp_mweb\ModoMantenimiento::sanear_mensaje_html( $mensaje );

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>" />
    <title><?php echo esc_html( $site_name ); ?></title>
    <?php
    if (function_exists('wp_site_icon')) {
        wp_site_icon();
    }
    ?>
    <meta name="viewport" content="width=device-width, maximum-scale=1, initial-scale=1, minimum-scale=1">
    <meta name="description" content="<?php echo esc_attr( $site_description ); ?>"/>
    <meta http-equiv="X-UA-Compatible" content="" />
    <meta property="og:site_name" content="<?php echo esc_attr( $site_name . ' - ' . $site_description ); ?>"/>
    <meta property="og:title" content="<?php echo esc_attr( $site_name ); ?>"/>
    <meta property="og:type" content="Maintenance"/>
    <meta property="og:url" content="<?php echo esc_url( site_url() ); ?>"/>
    <meta property="og:description" content="<?php echo esc_attr( $site_description ); ?>"/>
    <?php if( !empty( $logo ) ) { ?>
        <meta property="og:image" content="<?php echo esc_url( $logo ); ?>" />
        <meta property="og:image:url" content="<?php echo esc_url( $logo ); ?>"/>
        <meta property="og:image:secure_url" content="<?php echo esc_url( $logo ); ?>"/>
        <meta property="og:image:type" content="<?php echo esc_attr( $logo_ext ); ?>"/>
    <?php } ?>
    <link rel="profile" href="http://gmpg.org/xfn/11" />
    <link rel="pingback" href="<?php echo esc_url( get_bloginfo( 'pingback_url' ) ); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo esc_url( $url_css ); ?>" media="all"/>
    <script src="<?php echo esc_url( CDP_MANTENIMIENTO_URL_FRONTAL_FX . '/js/matter.0.18.min.js' ); ?>"></script>
    <script src="<?php echo esc_url( CDP_MANTENIMIENTO_URL_FRONTAL_FX . '/js/jquery-1.7.1.min.js' ); ?>"></script>
    <script>
        var url = <?php echo wp_json_encode( esc_url_raw( CDP_MANTENIMIENTO_URL_FRONTAL_FX ) ); ?>;
    </script>
    <script src="<?php echo esc_url( $url_js ); ?>"></script>
    <?php 
    $id_ga = \cdp_mweb\ModoMantenimiento::dame_id_google_analytics();
    if( $id_ga )
    {
        ?>
        <script type="text/javascript">
            (function(i,s,o,g,r,a,m){
                i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                    m=s.getElementsByTagName(o)[0];
                a.async=1;
                a.src=g;
                m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
            ga('create', '<?php echo esc_attr( $id_ga )?>', 'auto');
            ga('send', 'pageview');
        </script>
        <?php
    }
    ?>
</head>
<body>
    <div id="cdp-texto">
        <?php echo $mensaje_seguro; ?>
    </div>
</div>
</body>
</html>
