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

    $url_img = CDP_MANTENIMIENTO_URL_FRONTAL . '/img';
    $class = 'fondo' . absint( $plantilla );
    $site_name = get_bloginfo( 'name' );
    $site_description = get_bloginfo( 'description' );
    $mensaje_seguro = \cdp_mweb\ModoMantenimiento::sanear_mensaje_html( $mensaje );

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <!-- cdp_mantenimiento_web -->
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
    <style type="text/css">

        /* layout */
        .cdp-contenedor-ppal {
        }
        .cdp-contenido {
            width:100%;
            max-width: 800px;
            margin: 0 auto;
            min-height: 600px;
        }
        .cdp-texto {
        }

        /* estilo fondo 1 */
        body.fondo1 {
            background-color: #00b59b;
        }
        body.fondo1 .cdp-contenido {
            background: url("<?php echo esc_url( $url_img . '/fondo-mantenimiento-1.jpg' ); ?>") top right no-repeat #00b59b;
        }
        body.fondo1 .cdp-texto {
            padding: 100px 20px 0 20px;
            width: 450px;
            color:#ffffff;
            text-shadow: 0px 0px 3px #000000;
        }
        body.fondo1 h1, h2, h3, h4, h5, p, div { 
            padding: 1% 0;
            margin: 1% 0;
            font-family: "Trebuchet MS", Helvetica, sans-serif;
            line-height: 1em;
        }

        /* estilo fondo 2 */
        body.fondo2 {
            background-color: #ffffff;
        }
        body.fondo2 .cdp-contenido {
            background: url("<?php echo esc_url( $url_img . '/fondo-mantenimiento-2.jpg' ); ?>") top center no-repeat #ffffff;
        }
        body.fondo2 .cdp-texto {
            text-align: center;
            padding: 280px 20px 0 20px;
            color:#444444;
        }
        body.fondo2 h1, h2, h3, h4, h5, p { 
            padding: 1% 0;
            margin: 1% 0;
            font-family: "Trebuchet MS", Helvetica, sans-serif;
            line-height: 1em;
        }

        /* estilo fondo 2 */
        body.fondo2 {
            background-color: #ffffff;
        }
        body.fondo2 .cdp-contenido {
            background: url("<?php echo esc_url( $url_img . '/fondo-mantenimiento-2.jpg' ); ?>") top center no-repeat #ffffff;
        }
        body.fondo2 .cdp-texto {
            text-align: center;
            padding: 280px 20px 0 20px;
            color:#444444;
        }
        body.fondo2 h1, h2, h3, h4, h5, p { 
            padding: 1% 0;
            margin: 1% 0;
            font-family: Verdana, Geneva, sans-serif;
            line-height: 1em;
        }

        /* estilo fondo 3 */
        body.fondo3 {
            background-color: #80b1ec;
        }
        body.fondo3 .cdp-contenido {
            background: url("<?php echo esc_url( $url_img . '/fondo-mantenimiento-3.jpg' ); ?>") top center no-repeat #80b1ec;
        }
        body.fondo3 .cdp-texto {
            text-align: center;
            padding: 530px 20px 0 20px;
            color:#444444;
            color:#ffffff;
            text-shadow: 2px 2px 1px #444;
        }
        body.fondo3 h1, h2, h3, h4, h5, p { 
            padding: 2% 0;
            margin: 2% 0;
            font-family: Verdana, Geneva, sans-serif;
            line-height: 1.2em;
        }

        /* estilos comunes */
        h1 { font-size: 60px; }
        h2 { font-size: 50px; }
        h3 { font-size: 40px; }
        h4 { font-size: 30px; }
        h5 { font-size: 25px; }
        p { font-size: 20px; }

        /* media queries */
        @media only screen and (max-width: 780px) {
            h1 { font-size: 45px; }
            h2 { font-size: 40px; }
            h3 { font-size: 35px; }
            h4 { font-size: 30px; }
            h5 { font-size: 25px; }
            p { font-size: 20px; }
        }
        @media only screen and (max-width: 480px) {
            h1 { font-size: 36px; }
            h2 { font-size: 32px; }
            h3 { font-size: 28px; }
            h4 { font-size: 24px; }
            h5 { font-size: 20px; }
            p { font-size: 16px; }
        }
    </style>
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
<body class="<?php echo esc_attr( $class ); ?>">
<div class="cdp-contenedor-ppal">
    <div class="cdp-contenido">
        <div class="cdp-texto">
            <?php echo $mensaje_seguro; ?>
        </div>
    </div>
</div>
</body>
</html>
