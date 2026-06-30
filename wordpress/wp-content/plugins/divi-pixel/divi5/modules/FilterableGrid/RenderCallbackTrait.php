<?php
/**
 * FilterableGrid::render_callback()
 *
 * @package DIPI\Modules\FilterableGrid
 * @since ??
 */

namespace DIPI\Modules\FilterableGrid;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Packages\Module\Module;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use DIPI\Traits\BaseRenderTrait;

trait RenderCallbackTrait
{
    use BaseRenderTrait;
    private static $props = [];
    private static $vendor_prefix = 'dipi';

    private static function get_featured_image_url($post_id, $image_size, $fallback_url)
    {
        $attachment = get_the_post_thumbnail_url($post_id, $image_size);
        if ($attachment) {
            return $attachment;
        } else {
            return $fallback_url;
        }
    }

    public static function filter_invalid_term_ids($term_ids, $taxonomy)
    {
        $valid_term_ids = array();

        foreach ($term_ids as $term_id) {
            $term_id = intval($term_id);
            $term = term_exists($term_id, $taxonomy);
            if (!empty($term)) {
                $valid_term_ids[] = $term_id;
            }
        }

        return $valid_term_ids;
    }

    protected static function filter_meta_categories($categories, $post_id = 0, $taxonomy = 'category')
    {
        $raw_term_ids = is_array($categories) ? $categories : explode(',', $categories);

        if (in_array('all', $raw_term_ids, true)) {
            // If "All Categories" is selected return an empty array so it works for all terms
            // even ones created after the module was last updated.
            return array();
        }

        $term_ids = array();

        foreach ($raw_term_ids as $value) {
            if ('current' === $value) {
                if ($post_id > 0) {
                    $post_terms = wp_get_object_terms($post_id, $taxonomy);

                    if (is_wp_error($post_terms)) {
                        continue;
                    }

                    $term_ids = array_merge($term_ids, wp_list_pluck($post_terms, 'term_id'));
                } else {
                    $is_category = 'category' === $taxonomy && is_category();
                    $is_tag = !$is_category && 'post_tag' === $taxonomy && is_tag();
                    $is_tax = !$is_category && !$is_tag && is_tax($taxonomy);

                    if ($is_category || $is_tag || $is_tax) {
                        $term_ids[] = get_queried_object()->term_id;
                    }
                }

                continue;
            }
            $term_ids[] = (int) $value;
        }

        $term_ids = static::filter_invalid_term_ids(array_unique(array_filter($term_ids)), $taxonomy);

        return $term_ids;
    }

    protected static function filter_include_categories($include_categories, $post_id = 0, $taxonomy = 'category')
    {
        $categories = array();

        if (!empty($include_categories)) {
            // wp_doing_ajax() covers VB usage when fetching computed values where we always have a post.
            if (is_singular() || wp_doing_ajax()) {
                $post_id = $post_id > 0 ? $post_id : 0;//static::get_current_post_id_reverse();
                $categories = static::filter_meta_categories($include_categories, $post_id, $taxonomy);
            } else {
                $categories = static::filter_meta_categories($include_categories, 0, $taxonomy);
            }
        }

        return $categories;
    }

    public static function render_filter_bar($args = array(), $conditional_tags = array(), $current_page = array())
    {
        $filter_bar_html = '';
        $defaults = [
            'filter_bar_name_level' => 'div',
            'filter_bar_desc_level' => 'div',
        ];
        $args = wp_parse_args($args, $defaults);

        $show_filter_bar = $args['show_filter_bar'];
        if ($show_filter_bar == 'off') {
            return '';
        }

        $post_id = isset($current_page['id']) ? (int) $current_page['id'] : 0;
        $show_all_filter = $args['show_all_filter'];
        $all_filter_label = $args['all_filter_label'];
        $select_post_type = $args['select_post_type'];
        $select_custom_tax = $args['select_custom_tax'];
        $show_num_of_elements = $args['show_num_of_elements'];
        $hide_description = $args['hide_description'];
        $post_status = $args['post_status'];
        $filter_bar_name_level = $args['filter_bar_name_level'];
        $filter_bar_desc_level = $args['filter_bar_desc_level'];

        if (isset($args['filterable_grid_post']["include_term_ids_of_$select_custom_tax"]))
            $include_term_ids = $args['filterable_grid_post']["include_term_ids_of_$select_custom_tax"];
        else
            $include_term_ids = [];

        $include_term_ids = implode(',', static::filter_include_categories($include_term_ids, $post_id, $select_custom_tax));
        if (!$include_term_ids) {
            return sprintf('<div class="alert" data-items-count="0">
                    Please Select <strong>\'Included ' . $select_custom_tax . '\'</strong> to show in filter bar.
                    <br>
                    If you still didn\'t add terms of <strong>\'' . $select_custom_tax . '\'</strong>,
                    you can add new terms and assign.
                </div>');
        }
        $dipi_include_terms = $include_term_ids
            ? array_map(function ($term_id) {
                return get_term($term_id);
            }, explode(",", $include_term_ids))
            : [];
        $dipi_include_terms = array_filter($dipi_include_terms, function ($term_id) {
            return $term_id;
        });
        if ($show_all_filter == 'on') {
            $dipi_include_term_all = new \stdClass();
            $dipi_include_term_all->name = $all_filter_label;
            array_unshift($dipi_include_terms, $dipi_include_term_all);
        }
        foreach ($dipi_include_terms as $index => $dipi_include_term) {
            $name_html = "";
            $desc_html = "";
            if (!empty($dipi_include_term->name) || !empty($dipi_include_term->description)) {
                $name_html = sprintf(
                    '<%2$s class="dipi-filter-bar-name">%1$s</%2$s>',
                    !empty($dipi_include_term->name) ? $dipi_include_term->name : '',
                    $filter_bar_name_level
                );
                if ($hide_description === 'off') {
                    $desc_html = sprintf(
                        '<%2$s class="dipi-filter-bar-item-desc">%1$s</%2$s>',
                        !empty($dipi_include_term->description) ? $dipi_include_term->description : '',
                        $filter_bar_desc_level
                    );
                }
            }
            $count_html = '';
            if ($show_num_of_elements === 'on') {
                $query_images_args = [];
                $tax_query = [];
                if ($show_all_filter == 'on' && $index == 0) {
                    $tax_query = [
                        [
                            'taxonomy' => $select_custom_tax,
                            'field' => 'id',
                            'terms' => explode(",", $include_term_ids),
                        ]
                    ];
                } else {
                    $tax_query = [
                        [
                            'taxonomy' => $select_custom_tax,
                            'field' => 'slug',
                            'terms' => $dipi_include_term->slug,
                        ]
                    ];
                }
                $query_posts_args = array(
                    'post_type' => $select_post_type,
                    'post_status' => 'published',
                    'posts_per_page' => -1,
                    'tax_query' => $tax_query,
                    'fields' => 'ids',
                    'no_found_rows' => true,
                    'post_status' => $post_status
                );
                $query_posts = new \WP_Query($query_posts_args);
                $query_post_count = $query_posts->post_count;
                $count_html = sprintf('<div class="dipi-filter-bar-count">%1$s</div>', $query_post_count);
            }

            $filter_bar_html .= sprintf('
                <div class="dipi-filter-bar-item dipi-filter-bar-item-%3$s %5$s"
                    data-index="%3$s"
                    data-term="%4$s"
                >
                    <div class="dipi-filter-bar-item-title">
                        %1$s
                        %6$s
                    </div>
                    %2$s
                </div>
                ',
                $name_html,
                $desc_html,
                $index,
                $dipi_include_term->name,
                $index === 0 ? 'active' : '', #5
                $count_html
            );

        }
        return sprintf(
            '<div class="dipi-filter-bar" data-items-count="%2$s">
                %1$s
            </div>
           ',
            $filter_bar_html,
            count($dipi_include_terms)
        );
    }
    public static function render_posts($args = array(), $conditional_tags = array(), $current_page = array())
    {
        $defaults = [
            'select_post_type' => 'post',
            'select_custom_tax' => 'dipi_cpt_category',
            'posts' => '',
            'pagination_type' => 'none',
            'post_orderby' => '',
            'title_in_lightbox' => 'off',
            'excerpt_in_lightbox' => 'off',
            'icon_in_overlay' => 'off',
            'title_in_overlay' => 'off',
            'excerpt_in_overlay' => 'off',
            'use_post_link' => 'off',
            'link_elements' => 'on|on|on',
            'post_link_target' => '_blank',
            'use_overlay' => 'off',
            'use_overlay_tablet' => '',
            'use_overlay_phone' => '',
            'hover_icon' => '',
            'fix_lazy' => 'off',
            'image_size_desktop' => 'full',
            'image_size_tablet' => 'full',
            'image_size_phone' => 'full',
            'image_animation' => 'none',
            'grid_animation' => 'none',
            'show_lightbox_link_icon' => 'off',
            'post_count' => '-1',
            'posts_per_page' => '10',
            'load_more_text' => 'Load More',
            'prev_btn_text' => 'Prev',
            'next_btn_text' => 'Next',
            'post_status' => 'publish',
            'show_author' => 'off',
            'show_author_avatar' => 'off',
            'show_date' => 'off',
            'meta_date' => 'M j, Y',
            'read_more' => 'off',
            'read_more_text' => 'Read More',
            'show_custom_taxonomy' => 'off',
            'show_taxonomy_link' => 'off',
        ];
        $args = wp_parse_args($args, $defaults);
        if ($args['use_overlay_tablet'] === '' || $args['use_overlay_tablet'] === null) {
            $args['use_overlay_tablet'] = $args['use_overlay'];
        }
        if ($args['use_overlay_phone'] === '' || $args['use_overlay_phone'] === null) {
            $args['use_overlay_phone'] = $args['use_overlay_tablet'];
        }
        $post_id = isset($current_page['id']) ? (int) $current_page['id'] : 0;
        $show_all_filter = $args['show_all_filter'];
        $all_filter_label = $args['all_filter_label'];
        $select_post_type = $args['select_post_type'] ? $args['select_post_type'] : 'post';
        $select_custom_tax = $args['select_custom_tax'];
        $grid_animation = $args['grid_animation'];
        $show_lightbox_link_icon = $args['show_lightbox_link_icon'];
        $use_overlay = $args['use_overlay'];
        $use_overlay_tablet = $args['use_overlay_tablet'];
        $use_overlay_phone = $args['use_overlay_phone'];
        $grid_layout = $args['grid_layout'];
        $use_post_link = $args['use_post_link'];
        $post_link_target = $args['post_link_target'];
        $icon_animation = $args['icon_animation'];
        $title_animation = $args['title_animation'];
        $excerpt_animation = $args['excerpt_animation'];
        $post_status = $args['post_status'];
        $link_elements = explode('|', $args['link_elements']);
        $link_element_title = $use_post_link === 'on' ? $link_elements[0] : 'off';
        $link_element_excerpt = $use_post_link === 'on' ? $link_elements[1] : 'off';
        $link_element_image = $use_post_link === 'on' ? $link_elements[2] : 'off';

        $show_lightbox = $args['show_lightbox'];
        $show_lightbox_tablet = $args['show_lightbox_tablet'];
        $show_lightbox_phone = $args['show_lightbox_phone'];
        $post_orderby = $args['post_orderby'];
        $overlay_icon_use_circle = $args['overlay_icon_use_circle'];
        $overlay_icon_use_circle_border = $args['overlay_icon_use_circle_border'];
        $icon_in_overlay = $args["icon_in_overlay"];
        $hover_icon = $args['hover_icon'];
        $title_in_overlay = $args["title_in_overlay"];
        $excerpt_in_overlay = $args["excerpt_in_overlay"];
        $enable_html_in_overlay = $args["enable_html_in_overlay"];
        $enable_shortcode_in_overlay = $args["enable_shortcode_in_overlay"];
        $excerpt_length_in_overlay = $args["excerpt_length_in_overlay"];
        $show_custom_taxonomy = $args["show_custom_taxonomy"];
        $show_taxonomy_link = $args["show_taxonomy_link"];
        $show_post_title = $args["show_post_title"];
        $read_more = $args["read_more"];
        $read_more_text = $args["read_more_text"];
        $read_more_link_target = (isset($args["read_more_link_target"]) && !empty($args["read_more_link_target"])) ? $args["read_more_link_target"] : '_self';
        $show_author = $args["show_author"];
        $author_prefix = $args["author_prefix"];
        $show_author_avatar = $args["show_author_avatar"];
        $show_date = $args["show_date"];
        $meta_date = $args["meta_date"];
        $show_post_excerpt = $args["show_post_excerpt"];
        $excerpt_length = $args["excerpt_length"];
        $enable_html_on_grid = $args["enable_html_on_grid"];
        $enable_shortcode_on_grid = $args["enable_shortcode_on_grid"];
        $title_in_lightbox = $args["title_in_lightbox"];
        $excerpt_in_lightbox = $args["excerpt_in_lightbox"];
        $image_animation = $args["image_animation"];
        $fix_lazy = $args["fix_lazy"];
        $pagination_type = $args['pagination_type'];
        $post_count = $args['post_count'];
        $post_count_tablet = $args['post_count_tablet'];
        $post_count_phone = $args['post_count_phone'];
        $post_count_responsive_active = $post_count !== $post_count_tablet || $post_count !== $post_count_phone;
        $posts_per_page = $args['posts_per_page'];
        $load_more_text = $args['load_more_text'];
        $prev_btn_text = $args['prev_btn_text'];
        $next_btn_text = $args['next_btn_text'];
        $config = [
            'grid_layout' => $grid_layout,
        ];

        if (isset($args['filterable_grid_post']["include_term_ids_of_$select_custom_tax"]))
            $include_term_ids = $args['filterable_grid_post']["include_term_ids_of_$select_custom_tax"];
        else
            $include_term_ids = [];
        $include_term_ids = implode(',', static::filter_include_categories($include_term_ids, $post_id, $select_custom_tax));
        $dipi_include_terms = $include_term_ids
            ? array_map(function ($term_id) {
                return get_term($term_id);
            }, explode(",", $include_term_ids))
            : [];
        $dipi_include_terms = array_filter($dipi_include_terms, function ($term_id) {
            return $term_id;
        });
        if ($show_all_filter == 'on') {
            $dipi_include_term_all = new \stdClass();
            $dipi_include_term_all->name = $all_filter_label;
            array_unshift($dipi_include_terms, $dipi_include_term_all);
        }

        $show_overlay_classes = ($use_overlay === 'on') ? 'show_overlay' : 'hide_overlay';
        if (!empty($use_overlay_tablet)) {
            $show_overlay_classes .= ($use_overlay_tablet === 'on') ? ' show_overlay_tablet' : ' hide_overlay_tablet';
        }
        if (!empty($use_overlay_phone)) {
            $show_overlay_classes .= ($use_overlay_phone === 'on') ? ' show_overlay_phone' : ' hide_overlay_phone';
        }


        $show_lightboxclasses = "";
        $use_post_link_class = "";
        if ($use_post_link === "on") {
            if ($show_lightbox_link_icon === 'on') {
                $show_lightboxclasses = 'show_lightbox show_lightbox_tablet show_lightbox_phone';
            } else {
                $show_lightboxclasses = "hide_lightbox hide_lightbox_tablet hide_lightbox_phone";
            }
            $use_post_link_class = 'use_post_link';
        } else {
            $show_lightboxclasses = ($show_lightbox === 'on') ? 'show_lightbox' : 'hide_lightbox';
            if (!empty($show_lightbox_tablet)) {
                $show_lightboxclasses .= ($show_lightbox_tablet === 'on') ? ' show_lightbox_tablet' : ' hide_lightbox_tablet';
            }
            if (!empty($show_lightbox_phone)) {
                $show_lightboxclasses .= ($show_lightbox_phone === 'on') ? ' show_lightbox_phone' : ' hide_lightbox_phone';
            }
        }



        $posts_html = '';

        $query_posts_args = [
            'post_type' => $select_post_type,
            'post_status' => $post_status,
            'posts_per_page' => -1,
        ];
        switch ($post_orderby) {
            case 'date_asc':
                $query_posts_args['orderby'] = 'date';
                $query_posts_args['order'] = 'ASC';
                break;
            case 'title_asc':
                $query_posts_args['orderby'] = 'title';
                $query_posts_args['order'] = 'ASC';
                break;
            case 'title_desc':
                $query_posts_args['orderby'] = 'title';
                $query_posts_args['order'] = 'DESC';
                break;
            case 'rand':
                $query_posts_args['orderby'] = 'rand';
                break;
            case 'menu_asc':
                $query_posts_args['orderby'] = 'menu_order';
                $query_posts_args['order'] = 'ASC';
                break;
            case 'menu_desc':
                $query_posts_args['orderby'] = 'menu_order';
                $query_posts_args['order'] = 'DESC';
                break;
            case '':
            default:
                $query_posts_args['orderby'] = 'date';
                $query_posts_args['order'] = 'DESC';
                break;
        }

        foreach ($dipi_include_terms as $index => $dipi_include_term) {
            $items = [
                '<div class="grid-sizer"></div>',
                '<div class="gutter-sizer"></div>',
            ];
            $query_images_args = [];
            $orderby = 'date';
            $tax_query = [];
            if ($show_all_filter == 'on' && $index == 0) {
                $tax_query = [
                    [
                        'taxonomy' => $select_custom_tax,
                        'field' => 'id',
                        'terms' => explode(",", $include_term_ids),
                    ]
                ];
            } else {
                $tax_query = [
                    [
                        'taxonomy' => $select_custom_tax,
                        'field' => 'slug',
                        'terms' => $dipi_include_term->slug,
                    ]
                ];
            }
            $query_posts_args['tax_query'] = $tax_query;

            $query_posts = new \WP_Query($query_posts_args);
            $query_posts_count = $query_posts->post_count;
            $pages = (int) (($query_posts_count - 1) / $posts_per_page) + 1;
            $pagination_html = '';
            $pagination_pages = '';
            if (($pagination_type === 'numbered_pagination') && ((int) $pages > 1)) {
                $prev_pagination_html = "<span class='dipi-pagination-btn' data-page='prev'>$prev_btn_text</span>";
                $next_pagination_html = "<span class='dipi-pagination-btn' data-page='next'>$next_btn_text</span>";
                $pagination_html .= $prev_pagination_html;
                for ($pageIndex = 1; $pageIndex <= $pages; $pageIndex++) {
                    $one_pagination_html = sprintf(
                        '<span class="dipi-pagination-btn dipi-pagination-btn-%1$s %2$s" data-page="%1$s">
                            %1$s
                        </span>',
                        $pageIndex,
                        $pageIndex == 1 ? 'active' : ($pageIndex == 2 ? 'active-next' : '')
                    );
                    $pagination_html .= $one_pagination_html;
                }
                $pagination_html .= $next_pagination_html;
            }
            if ($pagination_type === 'load_more' && ((int) $pages > 1)) {
                $pagination_html = sprintf(
                    '<span class="dipi-loadmore-btn" data-page="1">
                        %1$s
                    </span>
                    ',
                    $load_more_text
                );
            }
            if ($pagination_type === 'infinite_scroll' && ((int) $pages > 1)) {
                $pagination_html = sprintf(
                    '<span class="dipi-loadmore-btn watch_end_of_grid" data-page="1">
                        %1$s
                    </span>
                    ',
                    $load_more_text
                );
            }
            if ($pagination_html) {
                $pagination_html = sprintf('
                    <div class="dipi-pagination" data-page-count="%2$s">
                        %1$s
                    </div>',
                    $pagination_html,
                    $pages
                );
            }

            $sticky_post_ids = array();
            $regular_post_ids = array();
            foreach ($query_posts->posts as $post) {
                if (is_sticky($post->ID)) {
                    $sticky_post_ids[] = $post->ID;
                } else {
                    $regular_post_ids[] = $post->ID;
                }
            }
            if ('rand' === $post_orderby) {
                if (!empty($sticky_post_ids)) {
                    shuffle($sticky_post_ids);
                }
                if (!empty($regular_post_ids)) {
                    shuffle($regular_post_ids);
                }
            }
            $post_ids = array_merge($sticky_post_ids, $regular_post_ids);

            $overlay_output = '';

            $overlay_icon_classes[] = 'dipi-filterable-grid-icon';

            if ('on' === $overlay_icon_use_circle) {
                $overlay_icon_classes[] = 'dipi-filterable-grid-icon-circle';
            }

            if ('on' === $overlay_icon_use_circle && 'on' === $overlay_icon_use_circle_border) {
                $overlay_icon_classes[] = 'dipi-filterable-grid-icon-circle-border';
            }

            $data_icon = '' !== $hover_icon ? sprintf(
                ' data-icon="%1$s"',
                esc_attr(Utils::process_font_icon($hover_icon))
            ) : 'data-no-icon';

            foreach ($post_ids as $post_index => $post_id) {
                $post = get_post($post_id);
                $attachment = get_the_post_thumbnail_url($post_id, "full");
                $img_id = get_post_thumbnail_id($post_id);
                $image = $attachment;
                $image_desktop_url = static::get_featured_image_url($post_id, $args['image_size_desktop'], $image);
                $image_tablet_url = static::get_featured_image_url($post_id, $args['image_size_tablet'], $image);
                $image_phone_url = static::get_featured_image_url($post_id, $args['image_size_phone'], $image);
                $image_alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
                $post_title = get_the_title($post_id);
                $a_open_tag = '';
                $a_close_tag = '';
                $img_a_open_tag = '';
                $img_a_close_tag = '';
                $lightbox_and_link_icon_html = '';

                if ($use_post_link === 'on') {
                    $post_link_url = get_permalink($post_id);
                    $a_open_tag = sprintf(
                        '<a href="%1$s" target="%2$s" aria-label="%3$s">',
                        $post_link_url,
                        $post_link_target === '_self' ? '_self' : '_blank',
                        esc_html($post_title)
                    );
                    $a_close_tag = '</a>';

                    if (
                        $use_overlay === 'on'
                        && $show_lightbox_link_icon === 'on'
                    ) {
                        if (!empty(trim($image))) {
                            $lightbox_icon_html = sprintf(
                                '<a href="%1$s" class="et-pb-icon et_pb_inline_icon %2$s animated %3$s lightbox-icon" data-icon="&#x55;" aria-label="%4$s"></a>',
                                esc_url($image),
                                implode(' ', $overlay_icon_classes),
                                $icon_animation,
                                esc_attr__('Open image in lightbox', 'dipi-divi-pixel')
                            );
                        } else {
                            $lightbox_icon_html = sprintf(
                                '<div class="et-pb-icon et_pb_inline_icon %1$s animated %2$s" data-icon="&#x55;"></div>',
                                implode(' ', $overlay_icon_classes),
                                $icon_animation
                            );
                        }

                        $link_icon_html = sprintf(
                            '<a href="%3$s" target="%4$s">
                                <div class="et-pb-icon et_pb_inline_icon %1$s animated %2$s link-icon" data-icon="&#xe02c;"></div>
                            </a>',
                            implode(' ', $overlay_icon_classes),
                            $icon_animation,
                            $post_link_url,
                            $post_link_target
                        );


                        $lightbox_and_link_icon_html = sprintf(
                            '<div class="dipi_lightbox_link_icon">
                                %1$s
                                %2$s
                            </div>',
                            $lightbox_icon_html,
                            $link_icon_html
                        );
                    } else {
                        $img_a_open_tag = $a_open_tag;
                        $img_a_close_tag = $a_close_tag;
                    }
                }

                $icon_html = '';
                if ('on' === $icon_in_overlay) {
                    $icon_html = sprintf(
                        '<div class="et-pb-icon %1$s %3$s animated %4$s"%2$s></div>',
                        ('' !== $hover_icon ? ' et_pb_inline_icon' : ''),
                        'on' === $icon_in_overlay ? $data_icon : '',
                        implode(' ', $overlay_icon_classes),
                        $icon_animation
                    );
                }

                $name_html = '';
                $header_level = $args['header_level'];
                if ('on' === $title_in_overlay && '' !== $post_title) {
                    $name_html = sprintf(
                        '<%3$s class="dipi-filterable-grid-title animated %2$s">
                            %1$s
                        </%3$s>',
                        $post_title,
                        $title_animation,
                        $header_level
                    );
                }

                $excerpt = get_the_excerpt($post_id);

                // Render HTML of Excerpt
                $raw_html_excerpt = $excerpt;
                if ($enable_html_on_grid === 'on' || $enable_html_in_overlay === 'on') {
                    if (!has_excerpt($post_id)) {
                        $raw_html_excerpt = get_the_content(null, false, $post_id);
                        // Remove HTML Comment tags
                        $raw_html_excerpt = preg_replace('/<!--(.*?)-->/', '', $raw_html_excerpt);// phpcs:ignore
                    }
                }
                // Render short code of post content, but this is having performance issue
                $raw_shortcode_excerpt = $raw_html_excerpt;
                if ($enable_shortcode_on_grid === 'on' || $enable_shortcode_in_overlay === 'on') {
                    $shortcode_excerpt = do_shortcode($raw_html_excerpt);
                    $raw_shortcode_excerpt = $shortcode_excerpt;
                }

                $excerpt = preg_replace('@\[caption[^\]]*?\].*?\[\/caption]@si', '', $excerpt);
                $excerpt = preg_replace('@\[et_pb_post_nav[^\]]*?\].*?\[\/et_pb_post_nav]@si', '', $excerpt);
                $excerpt = preg_replace('@\[audio[^\]]*?\].*?\[\/audio]@si', '', $excerpt);
                $excerpt = preg_replace('@\[embed[^\]]*?\].*?\[\/embed]@si', '', $excerpt);
                $excerpt = wp_strip_all_tags($excerpt);
                $excerpt = et_strip_shortcodes($excerpt);
                // $excerpt = et_builder_strip_dynamic_content( $excerpt );
                $excerpt = apply_filters('et_truncate_post', $excerpt, get_the_ID());
                $excerpt_html = '';
                if ('on' === $excerpt_in_overlay && '' !== $excerpt) {
                    $limit_excerpt = '';
                    if ($enable_html_in_overlay === "on") {
                        if ($enable_shortcode_in_overlay === "on") {
                            $limit_excerpt = dipi_limit_length_text_of_html($raw_shortcode_excerpt, $excerpt_length_in_overlay);
                        } else {
                            $limit_excerpt = dipi_limit_length_of_html($raw_html_excerpt, $excerpt_length_in_overlay)['text'];
                        }
                    } else {
                        $limit_excerpt = dipi_limit_length_letters_of_string($excerpt, $excerpt_length_in_overlay);
                    }
                    $excerpt_html = sprintf(
                        '<div class="dipi-filterable-grid-excerpt animated %2$s">
                            %1$s
                        </div>',
                        $limit_excerpt,
                        $excerpt_animation
                    );
                }

                $overlay_output = sprintf(
                    '<span class="dipi_filterable_grid_overlay background"></span>
                    <span class="dipi_filterable_grid_overlay background-hover"></span>
                    <span class="dipi_filterable_grid_overlay content" style="transition-duration: 0ms;">
                        %4$s
                        %1$s
                        %2$s
                        %3$s
                    </span>',
                    $icon_html,
                    $name_html,
                    $excerpt_html,
                    $lightbox_and_link_icon_html
                );

                $item_class = '';
                $data_page = '';
                $pagination_pages = '';
                if ($pagination_type === 'none') {
                    if ((int) $post_count >= 0 && $post_index >= (int) $post_count) {
                        $item_class = 'hidden';
                    }
                    if ($post_count_responsive_active) {
                        if ((int) $post_count_tablet >= 0 && $post_index >= (int) $post_count_tablet) {
                            $item_class .= ' tablet_hidden';
                        } else {
                            $item_class .= " tablet_show";
                        }
                        if ((int) $post_count_phone >= 0 && $post_index >= (int) $post_count_phone) {
                            $item_class .= ' phone_hidden';
                        } else {
                            $item_class .= " phone_show";
                        }
                    }
                } else {
                    $page = (int) ($post_index / $posts_per_page) + 1;
                    $item_class = 'page-' . $page;
                    if ($page !== 1) {
                        $item_class .= ' hidden';
                    }
                    $data_page = 'data-page=' . $page;
                    $pagination_pages = 'data-pages=' . $pages;
                }

                // Add taxonomy classes to grid items
                $taxonomy_classes = [];

                // Get categories
                $categories = get_the_category($post_id);
                if ($categories && !is_wp_error($categories)) {
                    foreach ($categories as $category) {
                        $taxonomy_classes[] = 'category-' . $category->slug;
                    }
                }

                // Get tags
                $tags = get_the_tags($post_id);
                if ($tags && !is_wp_error($tags)) {
                    foreach ($tags as $tag) {
                        $taxonomy_classes[] = 'post_tag-' . $tag->slug;
                    }
                }

                // Get custom taxonomies
                $custom_taxonomies = get_object_taxonomies($post->post_type, 'names');
                foreach ($custom_taxonomies as $taxonomy_name) {
                    // Skip built-in taxonomies we already handled
                    if ($taxonomy_name === 'category' || $taxonomy_name === 'post_tag') {
                        continue;
                    }

                    $terms = get_the_terms($post_id, $taxonomy_name);
                    if ($terms && !is_wp_error($terms)) {
                        foreach ($terms as $term) {
                            $taxonomy_classes[] = $taxonomy_name . '-' . $term->slug;
                        }
                    }
                }

                // Add taxonomy classes to item_class
                if (!empty($taxonomy_classes)) {
                    $item_class .= ' ' . implode(' ', $taxonomy_classes);
                }

                //Grid Content
                $grid_item_category_html = '';
                if ('on' === $show_custom_taxonomy && !empty($dipi_include_term->name)) {
                    $item_category_terms = get_the_terms($post_id, $select_custom_tax);
                    if ($show_taxonomy_link === "on") {
                        $item_category_term_name = array_map(function ($term) {
                            return sprintf('<a href="%1$s" rel="tag" class="dipi-grid-item-category">%2$s</a>', get_term_link($term), $term->name);
                        }, $item_category_terms);
                        $grid_item_category_html = implode(", ", $item_category_term_name);

                    } else {
                        $item_category_term_name = array_map(function ($term) {
                            return $term->name;
                        }, $item_category_terms);
                        $grid_item_category_html = sprintf(
                            '<span class="dipi-grid-item-category">
                                %1$s
                            </span>',
                            implode(", ", $item_category_term_name)
                        );
                    }
                }

                // Grid Item Title
                $dipi_filterable_grid_before_title = "";
                $dipi_filterable_grid_before_title = apply_filters('dipi_filterable_grid_before_title', $dipi_filterable_grid_before_title);
                $dipi_filterable_grid_before_title = apply_filters('dipi_filterable_grid_before_title_with_post', $dipi_filterable_grid_before_title, $post);

                $dipi_filterable_grid_after_title = "";
                $dipi_filterable_grid_after_title = apply_filters('dipi_filterable_grid_after_title', $dipi_filterable_grid_after_title);
                $dipi_filterable_grid_after_title = apply_filters('dipi_filterable_grid_after_title_with_post', $dipi_filterable_grid_after_title, $post);

                $grid_item_title_html = '';
                $grid_item_title_level = $args['grid_item_title_level'];
                if ('on' === $show_post_title && '' !== $post_title) {
                    $grid_item_title_html = sprintf(
                        '<%2$s class="dipi-grid-item-title">
                            %1$s
                        </%2$s>',
                        $post_title,
                        $grid_item_title_level
                    );
                }
                if ($dipi_filterable_grid_before_title) {
                    $dipi_filterable_grid_before_title = sprintf('
                        <div class="dipi-grid-item-before-title">
                            %1$s
                        </div>',
                        $dipi_filterable_grid_before_title
                    );
                }
                if ($dipi_filterable_grid_after_title) {
                    $dipi_filterable_grid_after_title = sprintf('
                        <div class="dipi-grid-item-after-title">
                            %1$s
                        </div>
                        ',
                        $dipi_filterable_grid_after_title
                    );
                }
                // Grid Item Excerpt
                $grid_item_excerpt_html = '';
                if ('on' === $show_post_excerpt && '' !== $excerpt) {
                    $limit_excerpt = '';
                    if ($enable_html_on_grid === "on") {
                        if ($enable_shortcode_on_grid === "on") {
                            $limit_excerpt = dipi_limit_length_text_of_html($raw_shortcode_excerpt, $excerpt_length);
                        } else {
                            $limit_excerpt = dipi_limit_length_of_html($raw_html_excerpt, $excerpt_length)['text'];
                        }
                    } else {
                        $limit_excerpt = dipi_limit_length_letters_of_string($excerpt, $excerpt_length);
                    }

                    $grid_item_excerpt_html = sprintf(
                        '<div class="dipi-grid-item-excerpt">
                            %1$s
                        </div>',
                        $limit_excerpt
                    );
                }
                // Author
                $author_id = get_post_field('post_author', $post_id);
                $author_info = get_userdata($author_id);
                $author_name = $author_info->display_name;
                $author_avatar_html = $show_author_avatar === 'on' ? sprintf(
                    '<img src=" %1$s" />',
                    esc_url(get_avatar_url($author_id))
                ) : '';
                $author_html = 'on' === $show_author ? sprintf(
                    '<span class="dipi-author-prefix">%4$s </span>
                    <span class="dipi-author">
                        
                        %1$s
                        <a href="%2$s"> %3$s</a>
                    </span>
                    ',
                    $author_avatar_html,
                    get_author_posts_url($author_id),
                    $author_name,
                    $author_prefix
                ) : '';
                // Date
                $date_html = 'on' === $args['show_date']
                    ? et_get_safe_localization(sprintf(__('%s', 'et_builder'), '<span class="post-date">' . esc_html(get_the_date(str_replace('\\\\', '\\', $args['meta_date']), $post_id)) . '</span>'))
                    : '';
                // Read More
                $dipi_filterable_grid_before_readmore = "";
                $dipi_filterable_grid_before_readmore = apply_filters('dipi_filterable_grid_before_readmore', $dipi_filterable_grid_before_readmore);
                $dipi_filterable_grid_before_readmore = apply_filters('dipi_filterable_grid_before_readmore_with_post', $dipi_filterable_grid_before_readmore, $post);

                $dipi_filterable_grid_after_readmore = "";
                $dipi_filterable_grid_after_readmore = apply_filters('dipi_filterable_grid_after_readmore', $dipi_filterable_grid_after_readmore);
                $dipi_filterable_grid_after_readmore = apply_filters('dipi_filterable_grid_after_readmore_with_post', $dipi_filterable_grid_after_readmore, $post);
                $grid_item_more = "";
                if ('on' === $args['read_more']) {
                    //$btn_open_tag = 'button';
                    //$btn_close_tag = 'button';
                    //if ($use_post_link === 'off') {
                    $post_link_url = get_permalink($post_id);
                    $btn_open_tag = sprintf('a href="%1$s"', $post_link_url);
                    $btn_close_tag = 'a';
                    //}
                    $button_use_icon = $args['read_more_use_icon'];
                    $button_icon = $args['read_more_icon'];
                    $read_more_link_target = $args['read_more_link_target'];
                    $readmore_data_icon = ' ';
                    $readmore_data_icon_class = '';
                    if ('on' === $button_use_icon) {
                        $readmore_data_icon = $button_icon ? Utils::process_font_icon($button_icon) : '$';
                        $readmore_data_icon_class = 'et_pb_custom_button_icon';
                    }
                    $grid_item_more = sprintf(
                        '<div class="dipi-fg-readmore-button-wrapper">
                            <%5$s
                                class="et_pb_button dipi-fg-readmore-button %3$s"
                                target="%4$s">%1$s
                            </%6$s>
                        </div>',
                        $read_more_text,
                        esc_attr($readmore_data_icon),
                        $readmore_data_icon_class,
                        $read_more_link_target,
                        $btn_open_tag,
                        $btn_close_tag
                    );
                }

                // Post Meta
                $dipi_filterable_grid_before_meta = "";
                $dipi_filterable_grid_before_meta = apply_filters('dipi_filterable_grid_before_meta', $dipi_filterable_grid_before_meta);
                $dipi_filterable_grid_before_meta = apply_filters('dipi_filterable_grid_before_meta_with_post', $dipi_filterable_grid_before_meta, $post);

                $dipi_filterable_grid_after_meta = "";
                $dipi_filterable_grid_after_meta = apply_filters('dipi_filterable_grid_after_meta', $dipi_filterable_grid_after_meta);
                $dipi_filterable_grid_after_meta = apply_filters('dipi_filterable_grid_after_meta_with_post', $dipi_filterable_grid_after_meta, $post);

                $dipi_filterable_grid_first_meta = "";
                $dipi_filterable_grid_first_meta = apply_filters('dipi_filterable_grid_first_meta', $dipi_filterable_grid_first_meta);
                $dipi_filterable_grid_first_meta = apply_filters('dipi_filterable_grid_first_meta_with_post', $dipi_filterable_grid_first_meta, $post);

                $dipi_filterable_grid_last_meta = "";
                $dipi_filterable_grid_last_meta = apply_filters('dipi_filterable_grid_last_meta', $dipi_filterable_grid_last_meta);
                $dipi_filterable_grid_last_meta = apply_filters('dipi_filterable_grid_last_meta_with_post', $dipi_filterable_grid_last_meta, $post);

                $post_meta = [];
                if ($dipi_filterable_grid_first_meta) {
                    $post_meta[] = $dipi_filterable_grid_first_meta;
                }
                if (!empty($author_html)) {
                    $post_meta[] = $author_html;
                }
                if (!empty($date_html)) {
                    $post_meta[] = $date_html;
                }
                if (!empty($grid_item_category_html)) {
                    $post_meta[] = $grid_item_category_html;
                }
                if ($dipi_filterable_grid_last_meta) {
                    $post_meta[] = $dipi_filterable_grid_last_meta;
                }
                $post_meta_html = "";
                if (!empty($post_meta)) {
                    $post_meta_html = sprintf(
                        '<div class="dipi-post-meta">%1$s</div>',
                        implode('<span class="dipi-post-meta-separator"> | </span>', $post_meta)
                    );
                }
                $grid_content_html = sprintf(
                    '<div class="dipi-grid-item-content">
                        %11$s
                        %1$s
                        %12$s
                        %9$s
                        %5$s
                            %2$s
                        %6$s
                        %10$s
                        %7$s
                            %3$s
                        %8$s
                        %13$s
                        %4$s
                        %14$s
                    </div>',
                    $post_meta_html,
                    $grid_item_title_html,
                    $grid_item_excerpt_html,
                    $grid_item_more,
                    $link_element_title === "on" ? $a_open_tag : "", #5
                    $link_element_title === "on" ? $a_close_tag : "",
                    $enable_html_on_grid === "on" ? '' : ($link_element_excerpt === "on" ? $a_open_tag : ""), // If raw html is enabled, need to keep link of raw HTML. So don't need to set link of excerpt to post.
                    $enable_html_on_grid === "on" ? '' : ($link_element_excerpt === "on" ? $a_close_tag : ""), // If raw html is enabled, need to keep link of raw HTML. So don't need to set link of excerpt to post.
                    $dipi_filterable_grid_before_title,
                    $dipi_filterable_grid_after_title, #10
                    $dipi_filterable_grid_before_meta,
                    $dipi_filterable_grid_after_meta,
                    $dipi_filterable_grid_before_readmore,
                    $dipi_filterable_grid_after_readmore
                );

                $img_html = sprintf('
                        <img src="%1$s"
                            loading="%9$s"
                            alt="%2$s"
                            srcset="%8$s 768w, %7$s 980w, %6$s 1024w"
                            sizes="(max-width: 768px) 768px, (max-width: 980px) 980px, 1024px"
                        />
                    ',
                    $image,
                    $image_alt,
                    'on' === $title_in_lightbox ? " data-title='$post_title'" : '',
                    'on' === $excerpt_in_lightbox ? " data-excerpt='" . get_the_excerpt($post_id) . "'" : '', #5
                    $image_animation, #5
                    $image_desktop_url,
                    $image_tablet_url,
                    $image_phone_url,
                    $fix_lazy === 'on' ? esc_attr("eager") : esc_attr("lazy")

                );
                $items[] = sprintf(
                    '<div class="grid-item %14$s" %17$s>
                        %10$s
                        <div class="img-container dipi-fg-animation dipi-fg-%12$s" %19$s %4$s%5$s>
                            %18$s
                            %6$s
                        </div>
                        %11$s
                        %13$s
                    </div>',
                    $image,
                    $image_alt,
                    $post_title,
                    'on' === $title_in_lightbox ? " data-title='$post_title'" : '',
                    'on' === $excerpt_in_lightbox ? " data-excerpt='" . get_the_excerpt($post_id) . "'" : '', #5
                    et_core_esc_previously($overlay_output),
                    $image_desktop_url,
                    $image_tablet_url,
                    $image_phone_url,
                    $link_element_image === 'on' ? $img_a_open_tag : "", #10
                    $link_element_image === 'on' ? $img_a_close_tag : "",
                    $image_animation,
                    $grid_content_html,
                    $item_class,
                    $a_open_tag, #15
                    $a_close_tag,
                    $data_page,
                    !empty(trim($image)) ? $img_html : "",
                    !empty(trim($image)) ? "href='$image'" : ""
                );
            }
            $posts_html .= sprintf('
                <div
                    class="
                        dipi-filtered-posts-item
                        dipi-filtered-posts-item-%6$s
                        %9$s
                        animated
                        %11$s
                    "
                    data-index="%6$s"
                    data-term="%7$s"
                    data-count="%8$s"
                    data-anim="%11$s"
                    %13$s
                >
                    <div class="grid %3$s %4$s %5$s" data-lazy="%2$s" data-config="%10$s">
                        %1$s
                    </div>
                    %12$s
                </div>',
                implode("", $items),
                $fix_lazy === 'on' ? esc_attr("true") : esc_attr("false"),
                $show_lightboxclasses,
                $show_overlay_classes,
                $use_post_link_class, #5
                $index,
                $dipi_include_term->name,
                $query_posts_count,
                $index === 0 ? 'active' : '',
                esc_attr(htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8')), #10
                $grid_animation,
                $pagination_html,
                $pagination_pages
            );
        }
        return sprintf(
            '<div
                class="dipi-filtered-posts-container"
                data-items-count="%2$s"
            >
                %1$s
             </div>',
            $posts_html,
            count($dipi_include_terms)
        );
    }

    public static function get_filterable_grid_data($args = array(), $conditional_tags = array(), $current_page = array())
    {
        $filter_bar_html = static::render_filter_bar($args, $conditional_tags, $current_page);
        $posts_html = static::render_posts($args, $conditional_tags, $current_page);

        return sprintf(
            '%1$s
            %2$s',
            $filter_bar_html,
            $posts_html
        );
    }

    /**
     * Static module render callback which outputs server side rendered HTML on the Front-End.
     *
     * @since ??
     * @param array          $attrs    Block attributes that were saved by VB.
     * @param string         $content  Block content.
     * @param WP_Block       $block    Parsed block object that being rendered.
     * @param ModuleElements $elements ModuleElements instance.
     *
     * @return string HTML rendered of Static module.
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $order_number = $block->parsed_block['orderIndex'];

        $link_elements = static::getPropValue($attrs, "link_elements");
        $title = in_array("title", $link_elements) ? "on" : "off";
        $excerpt = in_array("excerpt", $link_elements) ? "on" : "off";
        $image = in_array("image", $link_elements) ? "on" : "off";
        $link_elements = sprintf("%s|%s|%s", $title, $excerpt, $image);

        $use_overlay_desktop = isset($attrs['use_overlay']['innerContent']['desktop']['value']) ? $attrs['use_overlay']['innerContent']['desktop']['value'] : 'off';
        $use_overlay_tablet_val = isset($attrs['use_overlay']['innerContent']['tablet']['value']) ? $attrs['use_overlay']['innerContent']['tablet']['value'] : $use_overlay_desktop;
        $use_overlay_phone_val = isset($attrs['use_overlay']['innerContent']['phone']['value']) ? $attrs['use_overlay']['innerContent']['phone']['value'] : $use_overlay_tablet_val;

        $args = [
            "filterable_grid_post" => static::getPropValue($attrs, 'filterable_grid_post'),
            "select_post_type" => static::getPropValue($attrs, "select_post_type"),
            "select_custom_tax" => static::getPropValue($attrs, "select_custom_tax"),
            "pagination_type" => static::getPropValue($attrs, "pagination_type"),
            "post_count" => isset($attrs['post_count']['innerContent']['desktop']['value']) ? $attrs['post_count']['innerContent']['desktop']['value'] : -1,
            "post_count_tablet" => isset($attrs['post_count']['innerContent']['tablet']['value']) ? $attrs['post_count']['innerContent']['tablet']['value'] : -1,
            "post_count_phone" => isset($attrs['post_count']['innerContent']['phone']['value']) ? $attrs['post_count']['innerContent']['phone']['value'] : -1,
            "posts_per_page" => static::getPropValue($attrs, "posts_per_page"),
            "prev_btn_text" => static::getPropValue($attrs, "prev_btn_text"),
            "next_btn_text" => static::getPropValue($attrs, "next_btn_text"),
            "load_more_text" => static::getPropValue($attrs, "load_more"),
            "show_filter_bar" => static::getPropValue($attrs, "show_filter_bar"),
            "show_all_filter" => static::getPropValue($attrs, "show_all_filter"),
            "all_filter_label" => static::getPropValue($attrs, "all_filter_label"),
            "show_num_of_elements" => static::getPropValue($attrs, "show_num_of_elements"),
            "hide_description" => static::getPropValue($attrs, "hide_description"),
            "post_orderby" => static::getPropValue($attrs, "post_orderby"),
            "grid_layout" => static::getPropValue($attrs, "grid_layout"),
            "use_post_link" => static::getPropValue($attrs, "use_post_link"),
            'post_link_target' => static::getPropValue($attrs, "post_link_target"),
            "link_elements" => $link_elements,
            "show_lightbox_link_icon" => static::getPropValue($attrs, "show_lightbox_link_icon"),
            "show_lightbox" => isset($attrs["show_lightbox"]["innerContent"]["desktop"]["value"]) ? $attrs["show_lightbox"]["innerContent"]["desktop"]["value"] : "off",
            "show_lightbox_tablet" => isset($attrs["show_lightbox"]["innerContent"]["tablet"]["value"]) ? $attrs["show_lightbox"]["innerContent"]["tablet"]["value"] : "off",
            "show_lightbox_phone" => isset($attrs["show_lightbox"]["innerContent"]["phone"]["value"]) ? $attrs["show_lightbox"]["innerContent"]["phone"]["value"] : "off",
            "title_in_lightbox" => static::getPropValue($attrs, "title_in_lightbox"),
            "excerpt_in_lightbox" => static::getPropValue($attrs, "excerpt_in_lightbox"),
            "icon_in_overlay" => static::getPropValue($attrs, "icon_in_overlay"),
            "title_in_overlay" => static::getPropValue($attrs, "title_in_overlay"),
            "excerpt_in_overlay" => static::getPropValue($attrs, "excerpt_in_overlay"),
            "overlay_icon_use_circle" => static::getPropValue($attrs, "overlay_icon_use_circle"),
            "overlay_icon_use_circle_border" => static::getPropValue($attrs, "overlay_icon_use_circle_border"),
            "hover_icon" => static::getPropValue($attrs, "hover_icon"),
            "use_overlay" => $use_overlay_desktop,
            "use_overlay_tablet" => $use_overlay_tablet_val,
            "use_overlay_phone" => $use_overlay_phone_val,
            "icon_animation" => static::getPropValue($attrs, "icon_animation"),
            "title_animation" => static::getPropValue($attrs, "title_animation"),
            "excerpt_animation" => static::getPropValue($attrs, "excerpt_animation"),
            "image_animation" => static::getPropValue($attrs, "image_animation"),
            "grid_animation" => static::getPropValue($attrs, "grid_animation"),
            "grid_animation_delay" => static::getPropValue($attrs, "grid_animation_delay"),
            "grid_animation_speed" => static::getPropValue($attrs, "grid_animation_speed"),
            "show_post_excerpt" => static::getPropValue($attrs, "show_post_excerpt"),
            "excerpt_length" => static::getPropValue($attrs, "excerpt_length"),
            "show_post_title" => static::getPropValue($attrs, "show_post_title"),
            "read_more" => static::getPropValue($attrs, "read_more"),
            "read_more_text" => static::getPropValue($attrs, "read_more_button")["text"] ?? "Read More",
            "read_more_link_target" => static::getPropValue($attrs, "read_more_button")["linkTarget"] ?? "_self",
            "read_more_use_icon" => "off",
            "read_more_icon" => "",
            "show_author" => static::getPropValue($attrs, "show_author"),
            "author_prefix" => static::getPropValue($attrs, "author_prefix"),
            "show_author_avatar" => static::getPropValue($attrs, "show_author_avatar"),
            "show_date" => static::getPropValue($attrs, "show_date"),
            "meta_date" => static::getPropValue($attrs, "meta_date"),
            "show_custom_taxonomy" => static::getPropValue($attrs, "show_custom_taxonomy"),
            "show_taxonomy_link" => static::getPropValue($attrs, "show_taxonomy_link"),
            "post_status" => static::getPropValue($attrs, "post_status"),
            "enable_html_on_grid" => static::getPropValue($attrs, "enable_html_on_grid"),
            "enable_shortcode_on_grid" => static::getPropValue($attrs, "enable_shortcode_on_grid"),
            "enable_html_in_overlay" => static::getPropValue($attrs, "enable_html_in_overlay"),
            "enable_shortcode_in_overlay" => static::getPropValue($attrs, "enable_shortcode_in_overlay"),
            "excerpt_length_in_overlay" => static::getPropValue($attrs, "excerpt_length_in_overlay"),
            "grid_item_title_level" => isset($attrs['grid_item_title']['decoration']['font']['font']['desktop']['value']['headingLevel']) ? $attrs['grid_item_title']['decoration']['font']['font']['desktop']['value']['headingLevel'] : 'h4',
            "header_level" => isset($attrs['header_font']['decoraton']['font']['font']['desktop']['value']['headingLevel']) ? $attrs['header_font']['decoraton']['font']['font']['desktop']['value']['headingLevel'] : 'h4',
            "filter_bar_name_level" => isset($attrs['filter_bar_name_font']['decoraton']['font']['font']['desktop']['value']['headingLevel']) ? $attrs['filter_bar_name_font']['decoraton']['font']['font']['desktop']['value']['headingLevel'] : 'div',
            "filter_bar_desc_level" => isset($attrs['filter_bar_desc_font']['decoraton']['font']['font']['desktop']['value']['headingLevel']) ? $attrs['filter_bar_desc_font']['decoraton']['font']['font']['desktop']['value']['headingLevel'] : 'div',
        ];

        $filterable_grid_html = static::get_filterable_grid_data($args);

        $scroll_to_top = $attrs['scroll_to_top']['innerContent']['desktop']['value'] ?? 'off';
        $scroll_to_top_tablet = $attrs['scroll_to_top']['innerContent']['tablet']['value'] ?? $scroll_to_top;
        $scroll_to_top_phone = $attrs['scroll_to_top']['innerContent']['phone']['value'] ?? $scroll_to_top;

        $scroll_to_top_offset = $attrs['scroll_to_top_offset']['innerContent']['desktop']['value'] ?? '0';
        $scroll_to_top_offset_tablet = $attrs['scroll_to_top_offset']['innerContent']['tablet']['value'] ?? $scroll_to_top;
        $scroll_to_top_offset_phone = $attrs['scroll_to_top_offset']['innerContent']['phone']['value'] ?? $scroll_to_top;

        $sticky_filter_bar_top = $attrs['sticky_filter_bar_top']['innerContent']['desktop']['value'] ?? '0';
        $sticky_filter_bar_top_tablet = $attrs['sticky_filter_bar_top']['innerContent']['tablet']['value'] ?? $sticky_filter_bar_top;
        $sticky_filter_bar_top_phone = $attrs['sticky_filter_bar_top']['innerContent']['phone']['value'] ?? $sticky_filter_bar_top;

        $config = [
            "infinite_scroll_viewport" => static::getPropValue($attrs, 'infinite_scroll_viewport'),
            "scroll_to_top" => [
                "desktop" => $scroll_to_top,
                "tablet" => $scroll_to_top_tablet,
                "phone" => $scroll_to_top_phone,
            ],
            "scroll_to_top_offset" => [
                "desktop" => $scroll_to_top_offset,
                "tablet" => $scroll_to_top_offset_tablet,
                "phone" => $scroll_to_top_offset_phone,
            ],
            "sticky_filter_bar_top" => [
                "desktop" => $sticky_filter_bar_top,
                "tablet" => $sticky_filter_bar_top_tablet,
                "phone" => $sticky_filter_bar_top_phone,
            ],
            "grid_animation_delay" => static::getPropValue($attrs, "grid_animation_delay"),
        ];
        $grid_layout = static::getPropValue($attrs, 'grid_layout');
        $sticky_filter_bar = static::getPropValue($attrs, 'sticky_filter_bar');
        $sticky_filter_bar_tablet = isset($attrs['sticky_filter_bar']['innerContent']['tablet']['value']) ? $attrs['sticky_filter_bar']['innerContent']['tablet']['value'] : $sticky_filter_bar;
        $sticky_filter_bar_phone = isset($attrs['sticky_filter_bar']['innerContent']['phone']['value']) ? $attrs['sticky_filter_bar']['innerContent']['phone']['value'] : $sticky_filter_bar_tablet;
        $module_custom_classes = 'dipi_filterable_grid_wrapper';
        if ($grid_layout === 'grid') {
            $module_custom_classes .= " layout_grid";
        }
        if ($sticky_filter_bar === "on") {
            $module_custom_classes .= " sticky_filter_bar";
        }
        if ($sticky_filter_bar_tablet === "on") {
            $module_custom_classes .= " sticky_filter_bar_tablet";
        }
        if ($sticky_filter_bar_phone === "on") {
            $module_custom_classes .= " sticky_filter_bar_phone";
        }

        $render_html = sprintf(
            '<div class="%2$s" data-config="%3$s">
                %1$s
            </div>
           ',
            $filterable_grid_html,
            $module_custom_classes,
            esc_attr(htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8'))
        );

        $parent = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);
        $parent_attrs = $parent->attrs ?? [];

        return Module::render(
            [
                // FE only.
                'orderIndex' => $block->parsed_block['orderIndex'],
                'storeInstance' => $block->parsed_block['storeInstance'],

                // VB equivalent.
                'attrs' => $attrs,
                'elements' => $elements,
                'id' => $block->parsed_block['id'],
                'name' => $block->block_type->name,
                'moduleCategory' => $block->block_type->category,
                'classnamesFunction' => [FilterableGrid::class, 'module_classnames'],
                'stylesComponent' => [FilterableGrid::class, 'module_styles'],
                'scriptDataComponent' => [FilterableGrid::class, 'module_script_data'],
                'parentAttrs' => $parent_attrs,
                'parentId' => $parent->id ?? '',
                'parentName' => $parent->blockName ?? '',
                'children' => ElementComponents::component(
                    [
                        'attrs' => $attrs['module']['decoration'] ?? [],
                        'id' => $block->parsed_block['id'],

                        // FE only.
                        'orderIndex' => $block->parsed_block['orderIndex'],
                        'storeInstance' => $block->parsed_block['storeInstance'],
                    ]
                ) . $render_html,
            ]
        );
    }
}
