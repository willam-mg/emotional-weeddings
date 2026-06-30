/*
Plugin Name: Mantenimiento web
Plugin URI: https://webartesanal.com/mantenimiento-web/
Description: Pon tu WordPress en modo mantenimiento para hacer mejoras o reparaciones. Tu página mostrará un aviso "Sitio en construcción" y sólo tú podrás navegar por ella.
Version: 0.9
Requires at least: 3.5
Tested up to: 6.0
Author: Carlos Doral
Author URI: https://webartesanal.com/mantenimiento-web/
License: GPLv2 or later
*/ 

/*  Copyright 2018 Carlos Doral Pérez

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

// Globales
var Engine = Matter.Engine,
    Render = Matter.Render,
    Runner = Matter.Runner,
    Bodies = Matter.Bodies,
    Composite = Matter.Composite,
    MouseConstraint = Matter.MouseConstraint,
    Mouse = Matter.Mouse
    ;
var engine, world;

//
var bubbleList = [];
var width = window.innerWidth;
var height = window.innerHeight;
var interval;
var maxBubbles = 30;

//
function initAnimation()
{
	// Burbujas
	bubbleList = [];
	maxBubbles = width * height / 33333;

    // Creo motor
    engine = Engine.create();

    // Creo renderizador
    var render = Render.create({
        element: document.body,
        engine: engine,
        options : {
            width: width,
            height : height,
			showAngleIndicator: false,
            wireframes: false,
			background: '#fff'
		}
    });
    world = engine.world;

    // Desactivo gravedad
    engine.world.gravity.y = 0;

    // Muros
    var opt = { isStatic: true, render : { visible : false } };
    var wall1 = Bodies.rectangle( 0, height/2, 20, height, opt );
    var wall2 = Bodies.rectangle( width, height/2, 20, height, opt );
    var wall3 = Bodies.rectangle( width/2, 0, width, 20, opt );
    var wall4 = Bodies.rectangle( width/2, height, width, 20, opt );
	Matter.World.add( world, [ wall1, wall2, wall3, wall4 ] );
	
	// Bloque central texto
	var x = $( '#cdp-texto' ).position().left;
	var y = $( '#cdp-texto' ).position().top;
	var w = $( '#cdp-texto' ).outerWidth();
	var h = $( '#cdp-texto' ).outerHeight();
	y = ( height - h ) / 2;
	x = ( width - w ) / 2;
	$( '#cdp-texto' ).css( 'left', x + 'px' );
	$( '#cdp-texto' ).css( 'top', y + 'px' );
	var textBlock = Bodies.rectangle( 
		x + w / 2,
		y + h / 2,
		w,
		h,
		opt
	);
	Matter.World.add( world, [ textBlock ] );

	// Raton
	var mouse = Mouse.create(render.canvas),
			mouseConstraint = MouseConstraint.create(engine, {
				mouse: mouse,
				constraint: {
					stiffness: 0.2,
					render: {
						visible: false
					}
				}
			});
    Composite.add(world, mouseConstraint);
    render.mouse = mouse;
	
    // Teclado
    document.addEventListener( "keydown", function( e ) 
    {
        if( e.keyCode == 37 || e.keyCode == 65 )
        {
        }
    } );

    // Ejecuto renderizador
    Render.run(render);

    // Creo runner y ejecuto motor
    var runner = Runner.create();
    Runner.run( runner, engine );
    Render.lookAt(render, {
		min: { x: 0, y: 0 },
		max: { x: width, y: height }
	});

    // Temporizador
    interval = 
	    setInterval( 
	    	function() 
		    {
		    	// Creo burbujas
		    	if( bubbleList.length < maxBubbles )
		    	{
			    	var bubble = createBubble();
					bubbleList.push( bubble );
				}
			},
			250 
		);
}

//
var texIndex = 0;

//
function createBubble()
{
	//
	var textureList = [
		'burbuja-amarilla.png',
		'burbuja-roja.png',
		'burbuja-azul.png',
		'burbuja-celeste.png',
		'burbuja-morada.png',
		'burbuja-verde.png',
		'burbuja-naranja.png',
	];
	var textureFile = url + '/img/' + textureList[texIndex++];
	texIndex %= textureList.length;
	
	// Calculo radio
	var radius = 30 + Math.random() * 40;

	// Creo burbuja
	var bubble = 
		Bodies.circle( 
			Math.max( radius, Math.random() * ( width - radius ) ),
			Math.max( radius, Math.random() * ( height - radius ) ),
			radius, 
			{
				friction: 0,
				frictionAir: 0.0005,
				frictionStatic : 0,
				restitution: 0.8,
				density: 0.5,
				mass: 1,
				velocity : { x : -0.1, y : 0.2 },
				render: {
					sprite: {
						texture : textureFile,
						xScale : radius * 2 / 256,
						yScale : radius * 2 / 256
					}
				}
			}
		);

	// La animo
	var force = { 
		x : ( Math.random() - 0.5 ) * 5,
		y : ( Math.random() - 0.5 ) * 5 
	};
	Matter.World.add( world, [ bubble ] );
	Matter.Body.setVelocity( bubble, force );

	// Devuelvo obj
	return bubble;
}

//
//
//	
$( document ).ready( function() {

	// Resize
	$( window ).resize( function() {
		clearInterval( interval );
		width = window.innerWidth;
		height = window.innerHeight;
		$( 'body canvas' ).detach();
	    initAnimation();
	} );

	// inicio
	initAnimation();
	
} );
