<?php 
 
if(!function_exists('dipi_get_image_sizes')) {
    function dipi_get_image_sizes() {
        global $_wp_additional_image_sizes;
        $sizes = array();
        $get_intermediate_image_sizes = get_intermediate_image_sizes();
        foreach ($get_intermediate_image_sizes as $_size) {
            if (in_array($_size, array('thumbnail', 'medium', 'large'))) {
                $sizes[$_size]['width'] = get_option($_size . '_size_w');
                $sizes[$_size]['height'] = get_option($_size . '_size_h');
                $sizes[$_size]['crop'] = (bool) get_option($_size . '_crop');
            } elseif (isset($_wp_additional_image_sizes[$_size])) {
                $sizes[$_size] = array(
                    'width' => $_wp_additional_image_sizes[$_size]['width'],
                    'height' => $_wp_additional_image_sizes[$_size]['height'],
                    'crop' => $_wp_additional_image_sizes[$_size]['crop'],
                );
            }
        }

        $image_sizes = array(
            'full' => esc_html__('Full Size', 'dipi-divi-pixel'),
        );
        foreach ($sizes as $sizeKey => $sizeValue) {
            $image_sizes[$sizeKey] = sprintf(
                '%1$s (%2$s x %3$s,%4$s cropped)',
                $sizeKey,
                $sizeValue["width"],
                $sizeValue["height"],
                ($sizeValue["crop"] == false ? ' not' : '')

            );
        }
        return $image_sizes;
    }
}

 
if(!function_exists('dipi_get_posts_types_with_terms')) {
    function dipi_get_posts_types_with_terms() {
        global $wp_post_types;
        $post_types = array(
            'post' => $wp_post_types['post']->labels->name,
            'page' => $wp_post_types['page']->labels->name,
        );
        foreach (get_post_types(array('public' => true, '_builtin' => false), 'objects', 'and') as $post_type) {
            $post_types[$post_type->name] = $post_type->labels->name;
        }
        $tax = [];  
        foreach ($post_types as $key => $value) {
            foreach (get_object_taxonomies($key, 'objects') as $tax_key => $tax_value) {
                if($tax_value->public == 1 && $tax_value->show_ui == 1 && $tax_value->show_in_menu == 1){
        
                    $tax[$tax_key] = [
                        'label' => $tax_value->label,
                    ];
                    $terms = get_terms([
                        'taxonomy' => $tax_key,
                        'hide_empty' => true
                    ]);
                    foreach($terms as $term){
                        $tax[$tax_key]['terms'][] = [
                            'id' => $term->term_id,
                            'name' => $term->name,
                            'slug' => $term->slug,
                        ];
                    }
                    
                }
            }
            $post_types[$key] = [
                'label' => $value,
                'taxonomies' => $tax
            ];
        }
        return $post_types;
    }
}