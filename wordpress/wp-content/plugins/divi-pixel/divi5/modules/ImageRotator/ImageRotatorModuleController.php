<?php
//namespace D5TUTSimpleQuickModule;
namespace DIPI\Modules\ImageRotator;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Route\RESTRoute;
use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use WP_REST_Request;
use WP_REST_Response;

class ImageRotatorModuleGetPluginsUrlController extends RESTController {

    /**
     * Return unordered list of recent posts.
     */
    public static function index( WP_REST_Request $request ): WP_REST_Response {
        $response = plugins_url('/', __FILE__);
        return self::response_success( $response );
    }

    /**
     * Index action arguments.
     * Endpoint arguments as used in `register_rest_route()`.
     */
    public static function index_args(): array {
        return [
            'args' => [],
        ];
    }

    /**
     * Index action permission.
     * Endpoint permission callback as used in `register_rest_route()`.
     */
    public static function index_permission(): bool {
        return UserRole::can_current_user_use_visual_builder();
    }
}

/**
 * Class for registering REST API Endpoint.
 */
class ImageRotatorModuleRenderImagesController extends RESTController {

    /**
     * Return unordered list of recent posts.
     */
    public static function index( WP_REST_Request $request ): WP_REST_Response {
        $args = $request->get_param( 'args' );
        $post_id = $request->get_param( 'postId' );
        
        $current_page = [
            "url" => get_permalink(),
            "permalink" => get_permalink(),
            "id" =>  $post_id  ,
            "title" => get_the_title(),
            "thumbnailUrl" => "",
            "thumbnailId"=> 0,
            "authorName" => ""
        ];
        $conditional_tags = array();
        $response = [
            'html' => ImageRotator::render_images(
                json_decode(json_encode($args), true),
                $conditional_tags,
                $current_page
            ),
        ];
        return self::response_success( $response );
    }

    /**
     * Index action arguments.
     * Endpoint arguments as used in `register_rest_route()`.
     */
    public static function index_args(): array {
        return [
            'args' => [
                'type'              => 'object',
                'required'          => true,
                'sanitize_callback' => function( $data ) {

                    return json_decode ( $data );
                },
                'validate_callback' => 'esc_html',
            ],
            'postId' => [
                'type'              => 'number',
                'required'          => true,
                'sanitize_callback' => function( $number ) {

                    return intval( $number );
                },
                'validate_callback' => 'esc_html',
            ],
        ];
    }

    /**
     * Index action permission.
     * Endpoint permission callback as used in `register_rest_route()`.
     */
    public static function index_permission(): bool {
        return UserRole::can_current_user_use_visual_builder();
    }
}

/**
 * Register "Simple Quick Module"'s REST API Route and endpoint.
 */
add_action(
    'init',
    function() {
        $route = new RESTRoute( 'dipi/v1' );

        $route->prefix( '/module-data' )->group(
            function( $router ) {
                $router->get( '/image-rotator/get_plugins_url', ImageRotatorModuleGetPluginsUrlController::class );
                $router->get( '/image-rotator/render_images', ImageRotatorModuleRenderImagesController::class );
            }
        );
    }
);