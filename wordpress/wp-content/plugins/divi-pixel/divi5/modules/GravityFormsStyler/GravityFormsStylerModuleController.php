<?php
//namespace D5TUTSimpleQuickModule;
namespace DIPI\Modules\GravityFormsStyler;

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
class GravityFormsStylerModuleGetGravityForm extends RESTController {
    use RenderCallbackTrait;

    /**
     * Return unordered list of recent posts.
     */
    public static function index( WP_REST_Request $request ): WP_REST_Response {
        $args = $request->get_param( 'args' );
        
        $current_page = array();
        $conditional_tags = array();
        $response = [
            'html' => self::get_gravity_form(
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
                $router->get( '/gravity-forms-styler/get_gravity_form', GravityFormsStylerModuleGetGravityForm::class );
            }
        );
    }
);