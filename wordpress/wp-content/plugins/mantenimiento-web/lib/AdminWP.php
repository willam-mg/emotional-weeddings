<?php

/* ======================================================================================
   @author     Carlos Doral Pérez (https://webartesanal.com)
   @copyright  Copyright &copy; 2022 Carlos Doral Pérez, All Rights Reserved
               License: GPLv2 or later
   ====================================================================================== */

namespace cdp_mweb;

/**
 *
 */
class AdminWP
{
    /**
     * 
     */
    private static $nombre_plugin;
    
	/**
	 *
	 */
	static function init()
	{
	    AdminWP::crear_menu();
	    add_filter( 'plugin_action_links', [ __CLASS__, 'enlace_configuracion' ], 10, 2 );
	}

	/**
	 *
	 */
	static function crear_menu()
	{
		wp_enqueue_style( 
			'admin-estilos', 
			CDP_MANTENIMIENTO_URL_RAIZ . '/css/admin.css', 
			false 
		);
		add_submenu_page
		(
			'tools.php',
			'Mantenimiento Web',
			'Mantenimiento Web',
			'manage_options',
			'cdp_mantenimiento_web',
			array( __CLASS__, 'vista_configuracion' )
		);
	}

	/**
	 * 
	 */
	static function enlace_configuracion( $enlaces, $archivo )
	{
	    // Sólo añado enlaces a mi plugin
	    if( !self::$nombre_plugin )
	        self::$nombre_plugin = 
	           plugin_basename( 
	               CDP_MANTENIMIENTO_DIR_RAIZ . '/mantenimiento-web.php' 
	           );
        if( $archivo != self::$nombre_plugin )
            return $enlaces;
        
        // Procedo
        $enlace = [
            sprintf(
                "<a href=\"%s\">%s</a>",
                esc_url( admin_url( 'tools.php?page=cdp_mantenimiento_web' ) ),
                esc_html__( 'Configuración', 'cdp_mweb' )
            ) ];
        return array_merge( $enlace, $enlaces );
	}
	
	/**
	 *
	 */
	static function vista_configuracion()
	{
	    // Sólo usuarios con permisos reales de administración pueden guardar cambios.
	    if( !is_admin() || !current_user_can( 'manage_options' ) )
	    {
	        wp_die( esc_html__( 'No tienes permisos para acceder a esta página.', 'cdp_mweb' ) );
	    }
	    
	    // Acciones MODO MANTENIMIENTO
	    $mensajes_modo_mantenimiento = [];
		
		// Solicitud de guardado
		$solicitud_guardar = false;
		if( Input::post( 'cdp_guardar_modo_mantenimiento' ) )
		{
			check_admin_referer( 'cdp_guardar_2231', 'nonce_guardar_2231' );
			$solicitud_guardar = true;
		}
		
		// Acción guardar después del chequeo de seguridad
		if( $solicitud_guardar )
		{
		    //
		    $algun_cambio = false;
		    $modo_activo = !empty( $_POST['modo_mantenimiento_activo'] );
		    $id_google_analytics = isset( $_POST['id_google_analytics'] )
		        ? sanitize_text_field( wp_unslash( $_POST['id_google_analytics'] ) )
		        : '';
		    $mensaje_texto = isset( $_POST['mensaje_texto'] )
		        ? wp_unslash( $_POST['mensaje_texto'] )
		        : '';
		    $plantilla_publicada = isset( $_POST['plantilla'] )
		        ? absint( wp_unslash( $_POST['plantilla'] ) )
		        : 0;

		    // Alta/baja modo mantenimiento
		    if( $modo_activo && ModoMantenimiento::esta_activo() )
		    {
		        ;
		    }
		    else
	        if( !$modo_activo && !ModoMantenimiento::esta_activo() )
	        {
	            ;
	        }
		    else
		    {
		        if( $modo_activo )
		            ModoMantenimiento::activar();
	            else
	                ModoMantenimiento::desactivar();
                $algun_cambio = true;
		    }
			
			// Guardo ID GA
			$id_ga = ModoMantenimiento::dame_id_google_analytics();
			if( $id_ga != $id_google_analytics )
			{
			    ModoMantenimiento::actualizar_id_google_analytics(
			        $id_google_analytics
		        );
			    $algun_cambio = true;
			}

			// Guardo mensaje
			$mensaje = ModoMantenimiento::dame_mensaje_texto();
			$mensaje_saneado = ModoMantenimiento::sanear_mensaje_html( $mensaje_texto );
			if( $mensaje != $mensaje_saneado )
			{
			    ModoMantenimiento::actualizar_mensaje_texto(
			        $mensaje_texto
		        );
			    $algun_cambio = true;
			}

			// Guardo plantilla
			$plantilla = ModoMantenimiento::dame_plantilla();
			if( $plantilla != $plantilla_publicada )
			{
			    ModoMantenimiento::actualizar_plantilla(
			        $plantilla_publicada
		        );
			    $algun_cambio = true;
			}

			// Ningún cambio
			if( !$algun_cambio )
    			Mensajes::aviso( __( "No se ha realizado ningún cambio", 'cdp_mweb' ) );

    		// Obtengo mensajes
    		$mensajes_modo_mantenimiento = Mensajes::dame();
		}

		// Url plugin
		$url = admin_url() . 'admin.php?page=cdp_mantenimiento_web';
		$plantilla = absint( ModoMantenimiento::dame_plantilla() );

?>
<div class="cdp-mweb-admin-contenedor">
<form method="post" action="<?php echo esc_url( $url ); ?>">
<?php wp_nonce_field( 'cdp_guardar_2231', 'nonce_guardar_2231' ); ?>
<h2><?php esc_html_e( 'Configuración del plugin Mantenimiento Web', 'cdp_mweb' )?></h2>
<p><?php esc_html_e( 'Este plugin pone tu web modo privado haciendo que sólo tú puedas verla. El visitante sólo podrá acceder a la home donde aparecerá el típico mensaje "Página en construcción".', 'cdp_mweb' )?></p>
	<table width="95%">
		<tr>
			<th width="30%"><label><?php esc_html_e( 'Servicio activo', 'cdp_mweb' )?>:</label></th>
			<td width="70%"><input type="checkbox" name="modo_mantenimiento_activo" value="1" <?php checked( ModoMantenimiento::esta_activo() ); ?>></td>
		</tr>
		<tr>
			<th><label><?php esc_html_e( 'Plantilla', 'cdp_mweb' )?>:</label></th>
			<td>
				<table class="cdp_plantilla">
					<tr>
						<td>
							<input type="radio" name="plantilla" id="pl_1" value="1" <?php checked( $plantilla, 1 ); ?>><br>
							<img src="<?php echo esc_url( CDP_MANTENIMIENTO_URL_FRONTAL . '/img/fondo-mantenimiento-1.jpg' ); ?>" width="100" height="auto" onclick="javascript:jQuery('#pl_1').prop('checked',true)">
						</td>
						<td>
							<input type="radio" name="plantilla" id="pl_2" value="2" <?php checked( $plantilla, 2 ); ?>><br>
							<img src="<?php echo esc_url( CDP_MANTENIMIENTO_URL_FRONTAL . '/img/fondo-mantenimiento-2.jpg' ); ?>" width="100" height="auto" onclick="javascript:jQuery('#pl_2').prop('checked',true)">
						</td>
						<td>
							<input type="radio" name="plantilla" id="pl_3" value="3" <?php checked( $plantilla, 3 ); ?>><br>
							<img src="<?php echo esc_url( CDP_MANTENIMIENTO_URL_FRONTAL . '/img/fondo-mantenimiento-3.jpg' ); ?>" width="100" height="auto" onclick="javascript:jQuery('#pl_3').prop('checked',true)">
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<th><label><?php esc_html_e( 'Plantilla FX', 'cdp_mweb' )?>:</label></th>
			<td>
				<table class="cdp_plantilla">
					<tr>
						<td>
							<input type="radio" name="plantilla" id="pl_4" value="4" <?php checked( $plantilla, 4 ); ?>><br>
							<img src="<?php echo esc_url( CDP_MANTENIMIENTO_URL_FRONTAL_FX . '/img/burbujas.gif' ); ?>" width="100" height="auto" onclick="javascript:jQuery('#pl_4').prop('checked',true)">
						</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<th><label><?php esc_html_e( 'Mensaje de texto', 'cdp_mweb' )?>:</label></th>
			<td><textarea name="mensaje_texto" rows="5"><?php 
			$txt = ModoMantenimiento::dame_mensaje_texto();
			if( $txt )
			{
				echo esc_textarea( $txt );
			}
			else
			{
				echo esc_textarea(
					sprintf( 
						"<h3>%s</h3>\n<p>%s</p>", 
						__( 'Página en construcción', 'cdp_mweb' ),
						__( 'Lamentamos las molestias', 'cdp_mweb' )
					)
				);
			}
			?></textarea></td>
		</tr>
		<tr>
			<th><label><?php esc_html_e( 'ID de Google Analytics', 'cdp_mweb' )?>:</label></th>
			<td><input type="text" name="id_google_analytics" value="<?php 
			echo esc_attr( ModoMantenimiento::dame_id_google_analytics() )?>"></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="cdp_guardar_modo_mantenimiento" id="cdp_guardar_modo_mantenimiento" class="button button-primary" value="<?php echo esc_attr__( 'Guardar', 'cdp_mweb' ); ?>"></td>
		</tr>
		<tr>
			<td></td>
			<td>
				<?php
				foreach( $mensajes_modo_mantenimiento as $msg )
				{
				    $class = $msg['tipo'] == 'aviso' ? 'cdp-aviso' : 'cdp-error';
				    ?><div class="cdp-mweb-admin-mensaje <?php echo esc_attr( $class ); ?>"><?php
					echo esc_html( $msg['texto'] );
					?></div><?php
				}
				?>
			</td>
		</tr>
	</table>
</form>
<h2><?php esc_html_e( '¿Necesitas ayuda con tu WordPress?', 'cdp_mweb' )?></h2>
<p><?php esc_html_e( 'Vigilamos y cuidamos tu sitio Wordpress', 'cdp_mweb' );
	echo ' ';
    printf( 
        '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
        esc_url( 'https://webartesanal.com/servicio-mantenimiento-wordpress/' ),
        esc_html__( 'Consulta nuestros planes de mantenimiento', 'cdp_mweb' )
    )?>.</p>
<em><?php esc_html_e( 'Realizado por Carlos Doral', 'cdp_mweb' )?>. <a href="<?php echo esc_url( 'https://webartesanal.com/' ); ?>" target="_blank" rel="noopener noreferrer">Web Artesanal</a></em>
<?php
	}
}

?>
