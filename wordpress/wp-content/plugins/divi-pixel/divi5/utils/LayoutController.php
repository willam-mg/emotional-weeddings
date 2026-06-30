<?php
namespace DIPI\Utils;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use ET\Builder\Framework\UserRole\UserRole;
use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;

class LayoutController
{
    private static $instance = null;

    /**
     * Get the singleton instance.
     *
     * @return LayoutController
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct()
    {
        $this->register_hooks();
    }

    /**
     * Register WordPress hooks.
     */
    private function register_hooks()
    {
        add_action('rest_api_init', [self::class, 'rest_api_init']);
    }

    /**
     * Replace order numbers in class names and IDs with post-ID-prefixed versions.
     * 
     * @param string $content The HTML or CSS content to process.
     * @param int $post_id The post ID to inject into class names.
     * @return string The content with replaced order numbers.
     */
    private static function replace_order_numbers($content, $post_id, $replace_order_numbers)
    {
        if (!$replace_order_numbers) {
            return $content;
        }

        // Match Divi class names that start with et_pb_ or dipi_
        // and end with exactly one "_{number}" suffix.
        // Examples matched: et_pb_row_15, dipi_module_3
        // Examples ignored: my_class_18, et_pb_column_1_2
        $pattern = '/\b((?:et_pb|dipi)_(?![A-Za-z0-9_-]*_\d+_)[A-Za-z0-9_-]+)_(\d+)\b/';
        $replacement = '$1_' . $post_id . '_$2';

        return preg_replace($pattern, $replacement, $content);
    }

    public static function bak_render_divi_layout($layout_id, $replace_order_numbers = true)
    {
        // Validate the layout ID.
        if (empty($layout_id) || !is_numeric($layout_id)) {
            return '';
        }

        // Get the post data.
        $layout_post = get_post((int) $layout_id);

        // Check if the post exists and has content.
        if (!$layout_post || empty($layout_post->post_content)) {
            return '';
        }

        // Check if the post is published or if we have proper permissions.
        if ('publish' !== $layout_post->post_status && !current_user_can('read_private_posts')) {
            return '';
        }


        global $dipi_global_colors_style_tag_output;
        $global_colors_style_tag = '';
        if (!$dipi_global_colors_style_tag_output && !\ET\Builder\Framework\Utility\Conditions::is_vb_enabled()) {
            $dipi_global_colors_style_tag_output = true;
            // Get all the global colors CSS variable.
            $global_colors_style = \ET\Builder\FrontEnd\Module\Style::get_global_colors_style();

            if (!empty($global_colors_style)) {
                $global_colors_style_tag = '<style class="et-vb-global-data et-vb-global-colors">' . et_core_esc_previously($global_colors_style) . '</style>';
            }
        }




        // D5 CSS Capture with do_blocks
        try {
            // Capture current Style state before rendering
            $styles_before = Style::render('default', 'module');

            // Render the content using do_blocks
            $rendered_content = do_blocks($layout_post->post_content);

            // Capture Style state after rendering
            $styles_after = Style::render('default', 'module');

            // Get the new CSS that was generated during do_blocks
            $new_css_length = strlen($styles_after) - strlen($styles_before);

            if ($new_css_length > 0 && !empty($rendered_content)) {
                // Extract only the newly generated CSS
                $generated_css = substr($styles_after, strlen($styles_before));

                // Replace order numbers in rendered content and CSS to make them unique per post
                $rendered_content = self::replace_order_numbers($rendered_content, (int) $layout_id, $replace_order_numbers);
                $generated_css = self::replace_order_numbers($generated_css, (int) $layout_id, $replace_order_numbers);

                // IMPORTANT:
                // Wrap the rendered layout with Divi's expected DOM structure.
                // This is required so contextual selectors like:
                // .et-l--post > .et_builder_inner_content > .et_pb_section
                // continue to work correctly outside of the normal page flow.
                // $wrapped_content =
                //     '<div class="et-l et-l--post">' .
                //         '<div class="et_builder_inner_content">' .
                //             $rendered_content .
                //         '</div>' .
                //     '</div>';

                $wrapped_content = et_builder_get_layout_opening_wrapper() . $rendered_content . et_builder_get_layout_closing_wrapper();

                // Combine CSS and HTML content
                $complete_content =
                    '<style>' . $generated_css . '</style>' .
                    $wrapped_content .
                    $global_colors_style_tag;

                // Return content with CSS
                return HTMLUtility::render(
                    [
                        'tag' => 'div',
                        'attributes' => [
                            'class' => 'divi-library-layout-content',
                            'data-layout-id' => (int) $layout_id,
                        ],
                        'childrenSanitizer' => 'et_core_esc_previously',
                        'children' => $complete_content,
                    ]
                );
            } elseif (!empty($rendered_content)) {
                // Replace order numbers in rendered content to make them unique per post
                $rendered_content = self::replace_order_numbers($rendered_content, (int) $layout_id, $replace_order_numbers);

                // Content rendered but no new CSS
                // Still wrap with Divi layout containers to preserve layout behavior
                // $wrapped_content =
                //     '<div class="et-l et-l--post">' .
                //     '<div class="et_builder_inner_content">' .
                //     $rendered_content .
                //     '</div>' .
                //     '</div>';

                $wrapped_content = et_builder_get_layout_opening_wrapper() . $rendered_content . et_builder_get_layout_closing_wrapper();

                return HTMLUtility::render(
                    [
                        'tag' => 'div',
                        'attributes' => [
                            'class' => 'divi-library-layout-content',
                            'data-layout-id' => (int) $layout_id,
                        ],
                        'childrenSanitizer' => 'et_core_esc_previously',
                        'children' => $wrapped_content . $global_colors_style_tag,
                    ]
                );
            }
        } catch (\Throwable $e) {
            // CSS capture failed - fallback to basic rendering
        }

        // Fallback: Try apply_filters('the_content')
        try {
            $rendered_content = apply_filters('the_content', $layout_post->post_content);

            if (!empty($rendered_content)) {
                // Replace order numbers in rendered content to make them unique per post
                $rendered_content = self::replace_order_numbers($rendered_content, (int) $layout_id, $replace_order_numbers);

                // Even in fallback, wrap with Divi layout containers
                // so section-level contextual CSS continues to apply.
                $wrapped_content =
                    '<div class="et-l et-l--post">' .
                    '<div class="et_builder_inner_content">' .
                    $rendered_content .
                    '</div>' .
                    '</div>';

                return HTMLUtility::render(
                    [
                        'tag' => 'div',
                        'attributes' => [
                            'class' => 'divi-library-layout-content',
                            'data-layout-id' => (int) $layout_id,
                        ],
                        'childrenSanitizer' => 'et_core_esc_previously',
                        'children' => $wrapped_content . $global_colors_style_tag,
                    ]
                );
            }
        } catch (\Throwable $e) {
            // Fallback also failed
        }

        return '';
    }

    public static function rest_get_layout_content($request)
    {
        $layout_id = $request->get_param('layoutId');

        // Render the layout.
        $content = self::render_divi_layout($layout_id);

        if (empty($content)) {
            return new \WP_Error(
                'layout_not_found',
                sprintf('Layout with ID %d not found or has no content.', $layout_id),
                ['status' => 404]
            );
        }

        return [
            'content' => $content,
            'layoutId' => (int) $layout_id,
        ];
    }

    /**
     * Index action permission.
     * Endpoint permission callback as used in `register_rest_route()`.
     */
    public static function rest_permission_check(): bool
    {
        return UserRole::can_current_user_use_visual_builder();
    }

    public static function rest_api_init()
    {
        register_rest_route(
            'dipi/v1',
            '/layout',
            [
                'methods' => 'GET',
                'callback' => [LayoutController::class, 'rest_get_layout_content'],
                'permission_callback' => [LayoutController::class, 'rest_permission_check'],
                'args' => [
                    'layoutId' => [
                        'required' => true,
                        'validate_callback' => function ($param) {
                            return is_numeric($param);
                        },
                    ],
                ],
            ]
        );
    }

    public static function render_divi_layout($layout_id)
    {
        // Bail early if layout ID is invalid.
        $layout_id = absint($layout_id);
        if (0 === $layout_id) {
            return '';
        }

        // Bail early if post is not found or content is empty.
        $layout_post = get_post($layout_id);
        if (!$layout_post || empty($layout_post->post_content)) {
            return '';
        }

        // Render block markup using BlockParserStore::render_inner_content().
        $rendered_content = et_core_intentionally_unescaped(BlockParserStore::render_inner_content($layout_post->post_content), 'html');

        if (!empty($rendered_content)) {
            //The children we will render using the HTMLUtility 
            $children = [$rendered_content];

            //FIXME: this will always output the global colors, even if they are not used. However, it's 
            //       better than having a switch solution in the settings a user has to manually activate 
            //       and we currently have no better way of telling if global colors are needed.
            //Render Divis global colos, in case they were used on a layout that is being rendered. 
            global $dipi_global_colors_style_tag_output;
            if (!$dipi_global_colors_style_tag_output && !Conditions::is_vb_enabled()) {
                //Only output global colors once
                $dipi_global_colors_style_tag_output = true;

                //Get all the global colors CSS variable.
                $global_colors_style = Style::get_global_colors_style();

                //Only render them, if there are global styles
                if (!empty($global_colors_style)) {
                    $children[] = [
                        'tag' => 'style',
                        'attributes' => [
                            'class' => 'dipi-global-colors',
                            'data-layout-id' => $layout_id,
                        ],
                        'childrenSanitizer' => 'et_core_esc_previously',
                        'children' => et_core_esc_previously($global_colors_style),
                    ];
                }
            }



            //Divi Core animations don't run properly when not displayed on page load, e.g. on advanced tabs. This code should fix it, though it causes certain animations not to run at all
            $children[] = [
                'tag' => 'style',
                'attributes' => [
                    'class' => 'dipi-fix-divi-animations-in-renderd-layouts',
                    'data-layout-id' => $layout_id,
                ],
                'childrenSanitizer' => 'et_core_esc_previously',
                'children' => sprintf('
                    div.dipi-layout-content[data-layout-id="%1$d"] .et-waypoint:not(.et_pb_counters),
                    div.dipi-layout-content[data-layout-id="%1$d"] .et_animated { 
                        opacity: 1; 
                    }',
                    $layout_id
                ),
            ];

            // Merge captured CSS with rendered content.
            $rendered_styles = Style::render('default', 'presetNested')
                . Style::render('default', 'preset')
                . Style::render('default', 'presetGroup')
                . Style::render('default', 'module');

            if (!empty($rendered_styles)) {
                $children[] = [
                    'tag' => 'style',
                    'attributes' => [
                        'class' => 'dipi-layout-content-styles',
                        'data-layout-id' => $layout_id,
                    ],
                    'childrenSanitizer' => 'et_core_esc_previously',
                    'children' => $rendered_styles,
                ];
            }


            //Whenever a divi layout is rendered, we enqueue font awesome as a caution since we don't know whether or not font awesome might be used on that layout
            //FIXME: maybe we should scan the $rendered_content
            global $dipi_enqueue_font_awesome;
            if(str_contains($rendered_styles, "FontAwesome")){
                $dipi_enqueue_font_awesome = true;
            }


            return HTMLUtility::render(
                [
                    'tag' => 'div',
                    'attributes' => [
                        'class' => 'dipi-layout-content',
                        'data-layout-id' => $layout_id,
                    ],
                    'childrenSanitizer' => 'et_core_esc_previously',
                    'children' => $children,
                ]
            );
        }

        return '';
    }

}

// Initialize the singleton instance
LayoutController::instance();