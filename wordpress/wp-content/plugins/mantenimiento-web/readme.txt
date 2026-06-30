=== Mantenimiento web ===
Contributors: Carlos Doral.
Tags: mantenimiento web, mantenimiento wordpress, modo mantenimiento, página en construcción
Requires at least: 3.5
Tested up to: 7.0
Stable tag: 0.15
License: GPLv2 or later 

Este plugin permite poner tu página en modo mantenimiento con el típico mensaje "Página en construcción" o "Página en mantenimiento". El mensaje se puede personalizar y se incorporan varias plantillas para que no tengas que diseñar nada.

== Description ==

**NUEVO:** Ahora se incluyen plantillas animadas.

**Para más información sobre [mantenimiento web de sitios Wordpress](https://webartesanal.com/mantenimiento-web-wordpress/)**

Características del plugin:

* Poner tu página en construcción para que ningún visitante pueda ver el contenido real.

Agradecimientos a [BRM.IO](https://brm.io/matter-js/) por su magnífica librería de física.

== Screenshots ==

1. Ésta es la página de configuración del plugin

2. Ésta es una de las plantillas personalizables que incluye animación e interacción Javascript

== Installation ==

1. Descargue el plugin, descomprímalo y súbalo al directorio /wp-content/plugins/
2. Vaya al apartado plugins y active "Mantenimiento Web".
3. El plugin le redigirá a la configuración del mismo. Si no lo hiciese vaya al menú Herramientas, Mantenimiento Web.
4. Antes de activar el modo mantenimiento debe hacer click en el check "Servicio activo", elegir el diseño que más le guste, personalizar el mensaje a mostrar al usuario.
5. Para finalizar pulse Guardar y si dispone de algún plugin de caché asegúrese de borrar todos los archivos caché.
6. Cuande desee volver a publicar su sitio web en modo abierto y navegable, vuelva a la página de configuración del plugin, desactive el check "Servicio activo", guarde los cambios y vuelva a borrar la caché.

Si lo desea, como método alternativo de instalación puede ir a la sección Plugins y hacer lo siguiente:

1. Pulse 'Añadir nuevo'.
2. En el buscador escriba 'mantenimiento web'.
3. Haga click en 'Instalar' y luego 'Activar'.
4. Ahora siga desde el paso 3 de la sección anterior.

== Changelog ==

= 0.15 =
* Mejoras en seguridad, nada funcional y probado en WP 7.0

= 0.14 =
* Actualización de seguridad. Agradecimientos a Patchstack.

= 0.13 =
* Actualización de seguridad. Agradecimientos a Patchstack.

= 0.12 =
* problema al instalar

= 0.11 =
* problemas con la subida de archivos

= 0.10 =
* Incoporación de plantilla animada con físicas JS

= 0.9 =
* Actualización de seguridad. Agradecimientos a Patchstack.

= 0.8 =
* Desactivo servicio monitor web hasta que lo depure al 100%

= 0.7 =
* Bug corregido, faltaba una clase, no estaba subida al repositorio

= 0.6 =
* Bug corregido al detectar la clase WP_Error

= 0.5 =
* Añadimos funcionalidad para poner tu WordPress en modo mantenimiento con el típico mensaje "página en construcción"

= 0.4 =
* Bug no dejaba cambiar el email para las alertas
* Mejoras en búsqueda de texto para detectar caída de página

= 0.3 =
* Mejora a la hora de mostrar errores en la comunicación entre servidores.
* Mejora en la detección de caída de página. Ahora se genera un literal dentro del código fuente del sitio web que es buscado por el rastreador.
* Se muestra la versión del plugin en una nueva ruta rest.

= 0.2 =
* La alerta de web caída la envía una sóla vez en lugar de cada 5 minutos.
* Ahora envía una alerta cuando la web se pone en marcha.
* Se muestran más detalles si se produce un error

= 0.1 =
* Versión inicial.

== Troubleshooting ==

* Si al activar o desactivar el modo mantenimiento del plugin tu sitio no aplica los cambios es muy posible que se deba a que tienes un plugin de caché. Borra la caché del plugin y todo debería funcionar correctamente. También es posible que tengas que borrar la caché de tu navegador.

**[Si necesitas un servicio de mantenimiento web Wordpress contacta con nosotros](https://webartesanal.com/servicio-mantenimiento-wordpress/)**



