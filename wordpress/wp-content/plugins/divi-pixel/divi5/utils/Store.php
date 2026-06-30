<?php
namespace DIPI\Store;

if (!defined('ABSPATH')) {
    exit;
}

class Store
{
    public function __construct()
    {
        $ajax_actions = [
            'wp_ajax_dipi_action_get_image_sizes' => 'actionGetImageSizes',
            'wp_ajax_dipi_action_get_post_types' => 'actionGetPostTypes',
            'wp_ajax_dipi_action_get_posts_count' => 'actionGetPostsCount',
            'wp_ajax_dipi_action_get_dipi_media_category' => 'actionGetDipiMediaCategory',
            'wp_ajax_dipi_action_get_dipi_faq_category' => 'actionGetDipiFaqCategory',
            'wp_ajax_dipi_action_get_post_types_and_taxonomies' => 'actionGetPostTypesAndTaxonomies',
            'wp_ajax_dipi_action_get_testimonial_category_options' => 'actionGetTestimonialCategoryOptions',
            'wp_ajax_dipi_action_get_gravity_forms' => 'actionGetGravityForms',
            'wp_ajax_dipi_action_get_divi_library_layouts' => 'actionGetDiviLibraryLayouts',
        ];

        foreach ($ajax_actions as $hook => $method) {
            $this->registerAjaxActionIfAvailable($hook, $method);
        }
    }

    private function registerAjaxActionIfAvailable($hook, $method)
    {
        if (has_action($hook) !== false) {
            return;
        }

        add_action($hook, [$this, $method]);
    }

    public function actionGetImageSizes()
    {
        wp_send_json_success($this->getImageSizes());
        wp_die();
    }

    public function actionGetPostTypes()
    {
        wp_send_json_success($this->getPostsTypesWithTerms());
        wp_die();
    }

    public function actionGetPostsCount()
    {
        $posted_data = isset($_GET['options']) ? sanitize_text_field($_GET['options']) : ''; // phpcs:ignore
        $temp_data = str_replace("\\", "", $posted_data);
        $clean_data = (array) json_decode($temp_data);

        wp_send_json_success([
            'count' => $this->countPosts($clean_data),
        ]);
        wp_die();
    }

    public function actionGetDipiMediaCategory()
    {
        wp_send_json_success(get_categories(['taxonomy' => 'dipi_media_category']));
        wp_die();
    }

    public function actionGetDipiFaqCategory()
    {
        wp_send_json_success(get_categories(['taxonomy' => 'dipi_faq_category']));
        wp_die();
    }

    public function actionGetPostTypesAndTaxonomies()
    {
        $registered_post_types = et_get_registered_post_type_options(false, false);
        $excluded_post_types = ['Media', 'Taxonomies', 'Popup Maker'];
        $taxonomies_object = get_taxonomies([], 'objects');

        $taxonomies_object = array_filter($taxonomies_object, function ($object) {
            $exclude_taxonomies = [
                'dipi_media_category',
                'layout_pack',
                'layout_type',
                'layout_category',
                'layout_tag',
                'scope',
                'module_width',
                'nav_menu',
                'link_category',
                'post_format',
                'wp_template_part_area',
                'wp_theme',
            ];

            return !in_array($object->name, $exclude_taxonomies, true);
        });

        $taxonomies_options = array_map(function ($object) {
            return [$object->name => $object->label];
        }, $taxonomies_object);

        $taxonomies_options = array_merge(
            $taxonomies_options,
            ['post_category' => (object) ['post_category' => 'Category']]
        );

        $post_types = array_diff($registered_post_types, $excluded_post_types);

        wp_send_json_success([
            'post_types_options' => $post_types,
            'taxonomies_options' => $taxonomies_options,
        ]);
        wp_die();
    }

    public function actionGetTestimonialCategoryOptions()
    {
        $testimonial_categories = get_terms([
            'taxonomy' => 'testimonial_cat',
            'hide_empty' => false,
        ]);

        wp_send_json_success([
            'testimonial_categories' => $testimonial_categories,
        ]);
        wp_die();
    }

    public function actionGetGravityForms()
    {
        $forms_list = [
            0 => esc_html__('Please enable Gravity Forms Styler Module on Divi Pixel settings.', 'dipi-divi-pixel'),
        ];

        if (function_exists('dipi_get_gravity_forms')) {
            $forms_list = dipi_get_gravity_forms();
        }

        wp_send_json_success($forms_list);
        wp_die();
    }

    public function actionGetDiviLibraryLayouts()
    {
        global $wpdb;
        $layouts = $wpdb->get_results($wpdb->prepare(
            "SELECT ID,post_title FROM $wpdb->posts WHERE post_type=%s",
            sanitize_text_field('et_pb_layout')
        ));

        $layouts_list = [
            '0' => ['label' => __('-- select a layout --', 'dipi-divi-pixel')],
        ];

        if (count($layouts)) {
            foreach ($layouts as $layout) {
                $layouts_list[$layout->ID] = ['label' => $layout->post_title];
            }
        }

        wp_send_json_success($layouts_list);
        wp_die();
    }

    private function getImageSizes()
    {
        global $_wp_additional_image_sizes;

        $sizes = [];
        $intermediate_image_sizes = get_intermediate_image_sizes();
        foreach ($intermediate_image_sizes as $size_name) {
            if (in_array($size_name, ['thumbnail', 'medium', 'large'], true)) {
                $sizes[$size_name]['width'] = get_option($size_name . '_size_w');
                $sizes[$size_name]['height'] = get_option($size_name . '_size_h');
                $sizes[$size_name]['crop'] = (bool) get_option($size_name . '_crop');
            } elseif (isset($_wp_additional_image_sizes[$size_name])) {
                $sizes[$size_name] = [
                    'width' => $_wp_additional_image_sizes[$size_name]['width'],
                    'height' => $_wp_additional_image_sizes[$size_name]['height'],
                    'crop' => $_wp_additional_image_sizes[$size_name]['crop'],
                ];
            }
        }

        $image_sizes = [
            'full' => esc_html__('Full Size', 'dipi-divi-pixel'),
        ];

        foreach ($sizes as $size_key => $size_value) {
            $image_sizes[$size_key] = sprintf(
                '%1$s (%2$s x %3$s,%4$s cropped)',
                $size_key,
                $size_value['width'],
                $size_value['height'],
                $size_value['crop'] === false ? ' not' : ''
            );
        }

        return $image_sizes;
    }

    private function getPostsTypesWithTerms()
    {
        global $wp_post_types;
        $post_types = [
            'post' => $wp_post_types['post']->labels->name,
            'page' => $wp_post_types['page']->labels->name,
        ];

        foreach (get_post_types(['public' => true, '_builtin' => false], 'objects', 'and') as $post_type) {
            $post_types[$post_type->name] = $post_type->labels->name;
        }

        $taxonomies = [];
        foreach ($post_types as $key => $value) {
            foreach (get_object_taxonomies($key, 'objects') as $tax_key => $tax_value) {
                if ($tax_value->public == 1 && $tax_value->show_ui == 1 && $tax_value->show_in_menu == 1) {
                    $taxonomies[$tax_key] = [
                        'label' => $tax_value->label,
                    ];
                    $terms = get_terms([
                        'taxonomy' => $tax_key,
                        'hide_empty' => true,
                    ]);
                    foreach ($terms as $term) {
                        $taxonomies[$tax_key]['terms'][] = [
                            'id' => $term->term_id,
                            'name' => $term->name,
                            'slug' => $term->slug,
                        ];
                    }
                }
            }
            $post_types[$key] = [
                'label' => $value,
                'taxonomies' => $taxonomies,
            ];
        }

        return $post_types;
    }

    private function countPosts($args)
    {
        $count = 0;
        $post_types = $this->getPostsTypesWithTerms();

        foreach ($post_types as $post_type => $post_type_name) {
            if ($args["count_{$post_type}"] !== 'on') {
                continue;
            }

            $taxonomy_objects = get_object_taxonomies($post_type, 'objects');
            if ($taxonomy_objects && count($taxonomy_objects) > 0) {
                $query_args = [
                    'post_type' => $post_type,
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'tax_query' => [
                        'relation' => 'OR',
                    ],
                ];

                foreach ($taxonomy_objects as $taxonomy) {
                    if (!$taxonomy->show_ui || !$taxonomy->show_in_menu || !$taxonomy->public) {
                        continue;
                    }

                    if ($args["count_{$post_type}_{$taxonomy->name}_all_terms"] === 'on') {
                        $query_args['tax_query'][] = [
                            'taxonomy' => $taxonomy->name,
                            'operator' => 'EXISTS',
                        ];
                    } else {
                        $selected_terms = $args["{$post_type}_{$taxonomy->name}"];
                        $term_ids = isset($selected_terms) && count($selected_terms) > 0 ? $selected_terms : [];

                        $query_args['tax_query'][] = [
                            'taxonomy' => $taxonomy->name,
                            'field' => 'id',
                            'terms' => $term_ids,
                        ];
                    }

                    if ($args["count_{$post_type}_{$taxonomy->name}_without_terms"] === 'on') {
                        $query_args['tax_query'][] = [
                            'taxonomy' => $taxonomy->name,
                            'operator' => 'NOT EXISTS',
                        ];
                    }
                }

                $query = new \WP_Query($query_args);
                $count += $query->post_count;
            } else {
                $count += wp_count_posts($post_type)->publish;
            }
        }

        return $count;
    }
}
new Store();