<?php
//namespace D5TUTSimpleQuickModule;
namespace DIPI\Modules\Testimonial;

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
class TestimonialModuleGetTestimonialsController extends RESTController {

    /**
     * Return unordered list of recent posts.
     */
    public static function index( WP_REST_Request $request ): WP_REST_Response {
        $args = $request->get_param( 'args' );

        $response = [
            'html' => Testimonial::get_testimonial(
                json_decode(json_encode($args), true)
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

class TestimonialModuleGetCategoriesController extends RESTController {

    /**
     * Return unordered list of recent posts.
     */
    public static function index( WP_REST_Request $request ): WP_REST_Response {
        $response = Testimonial::get_testimonial_categories();
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
                $router->get( '/testimonial/get_testimonial', TestimonialModuleGetTestimonialsController::class );
                $router->get( '/testimonial/get_testimonial_categories', TestimonialModuleGetCategoriesController::class );
            }
        );
    }
);