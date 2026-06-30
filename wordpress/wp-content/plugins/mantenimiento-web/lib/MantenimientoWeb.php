<?php

/* ======================================================================================
   @author     Carlos Doral Pérez (https://webartesanal.com)
   @version    0.4
   @copyright  Copyright &copy; 2018 Carlos Doral Pérez, All Rights Reserved
               License: GPLv2 or later
   ====================================================================================== */

namespace cdp_mweb;

/**
 *
 */
class MantenimientoWeb
{
    /**
     * Cadenas con las vars a almacenar en tabla wp_options
     */
    const WP_OPTION_CLAVE = 'cdp_mantenimiento_web_key';
    const WP_OPTION_ACTIVO = 'cdp_mantenimiento_web_activo';
    const WP_OPTION_MENSAJES = 'cdp_mantenimiento_web_mensajes';
    
	/**
	 *
	 */
	static function activar_plugin()
	{
	    Mensajes::borrar();
 		 ModoMantenimiento::desactivar();
	    RedirigirAConfiguracion::crear();
	}

	/**
	 *
	 */
	static function desactivar_plugin()
	{
		ModoMantenimiento::desactivar();
	}

	/**
	 *
	 */
	static function desinstalar_plugin()
	{
	}
}

?>