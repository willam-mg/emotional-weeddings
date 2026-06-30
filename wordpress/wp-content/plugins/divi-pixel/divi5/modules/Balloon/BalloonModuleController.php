<?php
//namespace D5TUTSimpleQuickModule;
namespace DIPI\Modules\Balloon;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Route\RESTRoute;
use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class for registering REST API Endpoint.
 */
class BalloonModuleRenderLibraryLayoutController extends RESTController {
    /**
     * Return unordered list of recent posts.
     */
    public static function index( WP_REST_Request $request ): WP_REST_Response {
        $library_id = $request->get_param( 'library_id' );
        
        $response = [
            'html' => Balloon::render_library_layout($library_id),
        ];
        return self::response_success( $response );
    }

    /**
     * Index action arguments.
     * Endpoint arguments as used in `register_rest_route()`.
     */
    public static function index_args(): array {
        return [
            'library_id' => [
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


class BalloonModuleGetDiviLayoutsController extends RESTController {

    /**
     * Return unordered list of recent posts.
     */
    public static function index( WP_REST_Request $request ): WP_REST_Response {
        /*$current_page     = isset( $_POST['current_page'] ) ? $_POST['current_page'] : array();
        $current_page     = array_intersect_key( $current_page, et_fb_current_page_params() );
        $current_page     = $utils->sanitize_text_fields( $current_page );*/

        //$conditional_tags = isset( $_POST['conditional_tags'] ) ? $_POST['conditional_tags'] : array();
        //$conditional_tags = array_intersect_key( $conditional_tags, et_fb_conditional_tag_params() );
        // sanitize values.
        //$conditional_tags = $utils->sanitize_text_fields( $conditional_tags );
        $conditional_tags = array();
        $response = Balloon::get_divi_layouts();
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
 * Register "Simple Quick Module"'s REST API Route and endpoint.
 */
add_action(
    'init',
    function() {
        $route = new RESTRoute( 'dipi/v1' );

        $route->prefix( '/module-data' )->group(
            function( $router ) {
                $router->get( '/balloon/get_divi_layouts', BalloonModuleGetDiviLayoutsController::class );
            }
        );
        $route->prefix( '/module-data' )->group(
            function( $router ) {
                $router->get( '/balloon/render_library_layout', BalloonModuleRenderLibraryLayoutController::class );
            }
        );
    }
);