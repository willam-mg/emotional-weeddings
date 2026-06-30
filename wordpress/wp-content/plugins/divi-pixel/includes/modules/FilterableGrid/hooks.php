<?php
namespace DiviPixel;

add_action('wp_ajax_posts_by_page', __NAMESPACE__ . '\\get_posts_by_page');
add_action('wp_ajax_nopriv_posts_by_page', __NAMESPACE__ . '\\get_posts_by_page');

function get_featured_image_url($post_id, $image_size, $fallback_url)
{
    $attachment = get_the_post_thumbnail_url($post_id, $image_size);
    if ($attachment) {
        return $attachment;
    } else {
        return $fallback_url;
    }
}

function get_posts_by_page() {
    $grid_info = $_POST["grid_info"]; //phpcs:ignore 
    $post_ids = $grid_info["post_ids"];
    $page = $grid_info["page_index"];
    $args = $grid_info["args"];

    $link_elements = explode('|', $args['link_elements']);

    $link_element_title = $args['use_post_link'] === 'on' ? $link_elements[0] : 'off';
    $link_element_excerpt = $args['use_post_link'] === 'on' ? $link_elements[1] : 'off';
    $link_element_image = $args['use_post_link'] === 'on' ? $link_elements[2] : 'off';

    $items = [
        '<div class="grid-sizer"></div>',
        '<div class="gutter-sizer"></div>',
    ];

    $overlay_icon_classes[] = 'dipi-filterable-grid-icon';
            
    if ('on' === $args['overlay_icon_use_circle']) {
        $overlay_icon_classes[] = 'dipi-filterable-grid-icon-circle';
    }

    if ('on' === $args['overlay_icon_use_circle'] && 'on' === $args['overlay_icon_use_circle_border']) {
        $overlay_icon_classes[] = 'dipi-filterable-grid-icon-circle-border';
    }

    $data_icon = '' !== $args['hover_icon'] ? sprintf(
        ' data-icon="%1$s"',
        esc_attr($args['hover_icon'])
    ) : 'data-no-icon';

    $post_link_target = $args['post_link_target'];

    foreach ($post_ids as $post_index=>$post_id) {
        $post = get_post($post_id);
        $attachment = get_the_post_thumbnail_url($post_id, "full");
        $img_id = get_post_thumbnail_id($post_id);
        $image = $attachment;
        $image_desktop_url = get_featured_image_url($post_id, $args['image_size_desktop'], $image);
        $image_tablet_url = get_featured_image_url($post_id, $args['image_size_tablet'], $image);
        $image_phone_url = get_featured_image_url($post_id, $args['image_size_phone'], $image);
        $image_alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
        $post_title = get_the_title($post_id);
        $a_open_tag = '';
        $a_close_tag = '';
        $img_a_open_tag = '';
        $img_a_close_tag = '';
        $lightbox_and_link_icon_html = '';

        if ($args['use_post_link'] === 'on') {
            $post_link_url = get_permalink($post_id);
            $a_open_tag =  sprintf('<a href="%1$s" target="%2$s" aria-label="%3$s">',
                $post_link_url,
                $post_link_target === '_self' ? '_self' : '_blank',
                esc_html($post_title)
            );
            $a_close_tag = '</a>';

            if ($args['use_overlay'] === 'on'
                && $args['show_lightbox_link_icon'] === 'on'
            ) {
                if (!empty(trim($image))) {
                    $lightbox_icon_html = sprintf(
                        '<a href="%1$s" class="et-pb-icon et_pb_inline_icon %2$s animated %3$s lightbox-icon" data-icon="&#x55;" aria-label="%4$s"></a>',
                        esc_url($image),
                        implode(' ', $overlay_icon_classes),
                        $args['icon_animation'],
                        esc_attr__('Open image in lightbox', 'dipi-divi-pixel')
                    );
                } else {
                    $lightbox_icon_html = sprintf(
                        '<div class="et-pb-icon et_pb_inline_icon %1$s animated %2$s" data-icon="&#x55;"></div>',
                        implode(' ', $overlay_icon_classes),
                        $args['icon_animation']
                    );
                }

                $link_icon_html = sprintf(
                    '<a href="%3$s" target="%4$s">
                        <div class="et-pb-icon et_pb_inline_icon %1$s animated %2$s link-icon" data-icon="&#xe02c;"></div>
                    </a>',
                    implode(' ', $overlay_icon_classes),
                    $args['icon_animation'],
                    $post_link_url,
                    $post_link_target
                );

                
                $lightbox_and_link_icon_html =  sprintf(
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
        if ('on' === $args['icon_in_overlay']) {
            $icon_html = sprintf(
                '<div class="et-pb-icon %1$s %3$s animated %4$s"%2$s></div>',
                ('' !== $args['hover_icon'] ? ' et_pb_inline_icon' : ''),
                'on' === $args['icon_in_overlay'] ? $data_icon : '',
                implode(' ', $overlay_icon_classes),
                $args['icon_animation']
            );
        }

        $name_html = '';
        $header_level = $args['header_level'];
        if ('on' === $args['title_in_overlay'] && '' !== $post_title) {
            $name_html = sprintf(
                '<%3$s class="dipi-filterable-grid-title animated %2$s">
                    %1$s
                </%3$s>',
                $post_title,
                $args['title_animation'],
                $header_level
            );
        }

        $excerpt = '';
        if (has_excerpt($post_id)) {
            $excerpt = get_the_excerpt($post_id);
        }
        
        // Render HTML of Excerpt
        $raw_html_excerpt = $excerpt;
        if ($args['enable_html_on_grid'] === 'on' || $args['enable_html_in_overlay'] === 'on') {
            if (!has_excerpt($post_id )) {
                $raw_html_excerpt =  get_the_content(null, false, $post_id);
                // Remove HTML Comment tags
                $raw_html_excerpt = preg_replace('/<!--(.*?)-->/', '', $raw_html_excerpt);// phpcs:ignore
            }
        }
        // Render short code of post content, but this is having performance issue
        $raw_shortcode_excerpt = $raw_html_excerpt;
        if ($args['enable_shortcode_on_grid'] === 'on' || $args['enable_shortcode_in_overlay'] === 'on') {
            $shortcode_excerpt = do_shortcode($raw_html_excerpt);
            $raw_shortcode_excerpt = $shortcode_excerpt;
        }
        
        $excerpt = preg_replace( '@\[caption[^\]]*?\].*?\[\/caption]@si', '', $excerpt );
        $excerpt = preg_replace( '@\[et_pb_post_nav[^\]]*?\].*?\[\/et_pb_post_nav]@si', '', $excerpt );
        $excerpt = preg_replace( '@\[audio[^\]]*?\].*?\[\/audio]@si', '', $excerpt );
        $excerpt = preg_replace( '@\[embed[^\]]*?\].*?\[\/embed]@si', '', $excerpt );
        $excerpt = wp_strip_all_tags( $excerpt );
        
        // Safely handle shortcodes
        if (function_exists('et_strip_shortcodes')) {
            $excerpt = et_strip_shortcodes($excerpt);
        } else {
            // Fallback: Remove common shortcode patterns
            $excerpt = strip_shortcodes($excerpt);
            $excerpt = preg_replace('/\[.*?\]/s', '', $excerpt); // Remove any remaining shortcode-like patterns
        }
        
        // Safely handle dynamic content
        if (class_exists('ET_Builder_Value')) {
            $excerpt = et_builder_strip_dynamic_content($excerpt);
        } else {
            // Fallback: Remove any @ET-DC@ markers that might be present
            $excerpt = preg_replace('/@ET-DC@.*?@/', '', $excerpt);
        }
        
        $excerpt = apply_filters( 'et_truncate_post', $excerpt, get_the_ID() );
        $excerpt_html = '';
        if ('on' === $args['excerpt_in_overlay'] && '' !== $excerpt) {
            $limit_excerpt = '';
            if ($args['enable_html_in_overlay'] === "on") {
                if ($args['enable_shortcode_in_overlay'] === "on") {
                    $limit_excerpt = dipi_limit_length_text_of_html( $raw_shortcode_excerpt, $args['excerpt_length_in_overlay']);
                } else {
                    $limit_excerpt = dipi_limit_length_of_html( $raw_html_excerpt, $args['excerpt_length_in_overlay']) ['text'];
                }
            } else {
                $limit_excerpt = dipi_limit_length_letters_of_string($excerpt, $args['excerpt_length_in_overlay']);
            }
            $excerpt_html = sprintf(
                '<div class="dipi-filterable-grid-excerpt animated %2$s">
                    %1$s
                </div>',
                $limit_excerpt,
                $args['excerpt_animation']
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

        $item_class = 'page-'.$page;
        $data_page = 'data-page='.$page;

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
        if ('on' === $args['show_custom_taxonomy']) {
            $item_category_terms = get_the_terms($post_id, $args['select_custom_tax']);
            if ($args['show_taxonomy_link'] === "on") {
                $item_category_term_name =  array_map(function($term) {
                    return sprintf('<a href="%1$s" rel="tag" class="dipi-grid-item-category">%2$s</a>', get_term_link($term), $term->name);
                }, $item_category_terms);
                $grid_item_category_html = implode(", ", $item_category_term_name);

            } else {
                $item_category_term_name = array_map(function ($term)
                {
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
        if ('on' === $args['show_post_title'] && '' !== $post_title) {
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
        if ('on' === $args['show_post_excerpt'] && '' !== $excerpt) {
            $limit_excerpt = '';
            if ($args['enable_html_on_grid'] === "on") {
                if ($args['enable_shortcode_on_grid'] === "on") {
                    $limit_excerpt = dipi_limit_length_text_of_html( $raw_shortcode_excerpt, $args['excerpt_length']);
                } else {
                    $limit_excerpt = dipi_limit_length_of_html( $raw_html_excerpt, $args['excerpt_length']) ['text'];
                }
            } else {
                $limit_excerpt = dipi_limit_length_letters_of_string($excerpt, $args['excerpt_length']);
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
        $author_avatar_html = $args['show_author_avatar'] === 'on' ? sprintf (
            '<img src=" %1$s" />',
            esc_url(get_avatar_url($author_id))
        ) : '';
        $author_html = 'on' === $args['show_author'] ? sprintf(
            '<span class="dipi-author-prefix">%4$s </span>
            <span class="dipi-author">
                
                %1$s
                <a href="%2$s"> %3$s</a>
            </span>
            ',
            $author_avatar_html,
            get_author_posts_url($author_id),
            $author_name,
            $args['author_prefix']
        ) : '';
        // Date
        $date_html = 'on' === $args['show_date']
            ? et_get_safe_localization( sprintf( __( '%s', 'et_builder' ), '<span class="post-date">' . esc_html( get_the_date( str_replace( '\\\\', '\\', $args['meta_date'] ), $post_id ) ) . '</span>' ) )
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
            //if ($args['use_post_link'] === 'off') {
                $post_link_url = get_permalink($post_id);
                $btn_open_tag = sprintf('a href="%1$s"', $post_link_url);
                $btn_close_tag = 'a';
            //}
            $button_use_icon = $args['read_more_use_icon'];
            $button_icon     = $args['read_more_icon'];
            $read_more_link_target = $args['read_more_link_target'];
            $readmore_data_icon       = '$';
            $readmore_data_icon_class = '';
            if('on' === $button_use_icon) {
                $readmore_data_icon       = $button_icon !== '' ? $button_icon : '$';
                $readmore_data_icon_class = 'et_pb_custom_button_icon';
            }
            $grid_item_more = sprintf(
                '<div class="dipi-fg-readmore-button-wrapper">
                    <%5$s
                        class="et_pb_button dipi-fg-readmore-button %3$s"
                        target="%4$s"
                        data-icon="%2$s">%1$s
                    </%6$s>
                </div>',
                $args['read_more_text'],
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
            $post_meta_html = sprintf('<div class="dipi-post-meta">%1$s</div>',
                implode('<span class="dipi-post-meta-separator"> | </span>', $post_meta));
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
            $args['enable_html_on_grid'] === "on" ? '' : ($link_element_excerpt === "on" ? $a_open_tag : ""), // If raw html is enabled, need to keep link of raw HTML. So don't need to set link of excerpt to post.
            $args['enable_html_on_grid'] === "on" ? '' : ($link_element_excerpt === "on" ? $a_close_tag : ""), // If raw html is enabled, need to keep link of raw HTML. So don't need to set link of excerpt to post.
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
            'on' === $args['title_in_lightbox'] ? " data-title='$post_title'" : '',
            'on' === $args['excerpt_in_lightbox'] ? " data-excerpt='" . get_the_excerpt($post_id) . "'" : '', #5
            $args['image_animation'], #5
            $image_desktop_url,
            $image_tablet_url,
            $image_phone_url,
            $args['fix_lazy'] === 'on' ? esc_attr("eager") : esc_attr("lazy")
            
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
            'on' === $args['title_in_lightbox'] ? " data-title='$post_title'" : '',
            'on' === $args['excerpt_in_lightbox'] ? " data-excerpt='" . get_the_excerpt($post_id) . "'" : '', #5
            et_core_esc_previously($overlay_output),
            $image_desktop_url,
            $image_tablet_url,
            $image_phone_url,
            $link_element_image === 'on' ? $img_a_open_tag : "", #10
            $link_element_image === 'on' ? $img_a_close_tag : "",
            $args['image_animation'],
            $grid_content_html,
            $item_class,
            $a_open_tag, #15
            $a_close_tag,
            $data_page,
            !empty(trim($image)) ? $img_html : "",
            !empty(trim($image)) ? "href='$image'" : ""
        );
    }

    wp_send_json_success(implode("", $items));

    wp_die();
}
