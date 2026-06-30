<?php
/**
 * Testimonial::render_callback()
 *
 * @package DIPI\Modules\Testimonial
 * @since ??
 */

namespace DIPI\Modules\Testimonial;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Packages\Module\Module;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use DIPI\Traits\BaseRenderTrait;
use DIPI\Modules\Base\Swiper\SwiperRenderTrait;

trait RenderCallbackTrait {
    use BaseRenderTrait;
	use SwiperRenderTrait;

	private static $props = [];

	static function get_testimonial_categories() {
		$testimonial_categories = get_terms(array(
			'taxonomy'   => 'testimonial_cat',
			'hide_empty' => false,
		));
		return 	json_encode([
			'testimonial_categories' => $testimonial_categories
		]);
	}

	static function closing_tags($html) {
        preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
        $openedtags = $result[1];
        preg_match_all('#</([a-z]+)>#iU', $html, $result);

        $closedtags = $result[1];
        $len_opened = count($openedtags);

        if (count($closedtags) == $len_opened) {
            return $html;
        }
        $openedtags = array_reverse($openedtags);
        for ($i=0; $i < $len_opened; $i++) {
            if (!in_array($openedtags[$i], $closedtags)) {
                $html .= '</'.$openedtags[$i].'>';
            } else {
                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }
        return $html;
    }

	static function get_testimonial($args = array()) 
    {
        $defaults = [
			'order_number' => 0,
			'order_class' => '',
            'remove_empty_html' => 'off',
            'use_show_popup_rating' => 'on',
            'use_show_popup_review' => 'on',
            'use_show_popup_company' => 'on',
            'use_show_popup_name' => 'on',
            'use_show_popup_image' => 'on',
            'popup_color' => '#fff',
            'popup_size' => '620px',
            'close_icon_bg_color' => '#000',
            'close_icon_color' => '#fff',
        ];
        $args = wp_parse_args($args, $defaults);
		$order_number = $args['order_number'];
		$order_class = $args['order_class'];
        $total_testimonial = isset($args['total_testimonial']) ? $args['total_testimonial'] : 10;
        $review_length = isset($args['review_length']) ? $args['review_length'] : 15;
        $remove_empty_html = isset($args['remove_empty_html']) ? $args['remove_empty_html']: 'off';
        $use_hide_img = isset($args['use_hide_img']) ? $args['use_hide_img'] : 'off';
        $use_hide_name = isset($args['use_hide_name']) ? $args['use_hide_name'] : 'off';
        $use_hide_review = isset($args['use_hide_review']) ? $args['use_hide_review'] : 'off';
        $use_hide_company = isset($args['use_hide_company']) ? $args['use_hide_company'] : 'off';
        $use_hide_company_link = isset($args['use_hide_company_link']) ? $args['use_hide_company_link'] : 'off';
        $use_hide_rating = isset($args['use_hide_rating']) ? $args['use_hide_rating'] : 'off';
        $review_type = !empty($args['review_type']) ? $args['review_type'] : [];
        $use_hide_readmore = isset($args['use_hide_readmore']) ? $args['use_hide_readmore'] : 'off';
        $readmore_text = isset($args['readmore_text']) ? $args['readmore_text'] : '';
        $review_type_arr = [];
        $testimonial_categories = !empty($args['testimonial_categories']) && count($args['testimonial_categories']) > 0 ? $args['testimonial_categories'] : [];
        $testimonial_suppress_filters = !empty($args['testimonial_suppress_filters']) ? $args['testimonial_suppress_filters'] : 'on';
        $use_show_popup_rating = $args['use_show_popup_rating'];
        $use_show_popup_date = isset($args['use_show_popup_date']) ? $args['use_show_popup_date'] : 'on';
        $use_show_popup_title = isset($args['use_show_popup_title']) ? $args['use_show_popup_title'] : 'on';
        $use_show_popup_review = $args['use_show_popup_review'] ?? 'on';
        $use_show_popup_company = $args['use_show_popup_company'] ?? 'on';
        $use_show_popup_name = $args['use_show_popup_name'] ?? 'on';
        $use_show_popup_image = $args['use_show_popup_image'] ?? 'on';
        $excluded_post_ids = $args['excluded_post_ids'] ?? '';
        $included_post_ids = $args['included_post_ids'] ?? '';
        $filter_by_stars = isset($args['filter_by_stars']) ? $args['filter_by_stars'] : 0;

        if(isset($review_type[2]) && 'on' == $review_type[0]) {
            $review_type_arr[0] = 'default';
        } else

        if(isset($review_type[2]) && 'on' == $review_type[1]) {
            $review_type_arr[1] = 'facebook';
        }

        if(isset($review_type[2]) && 'on' == $review_type[2]) {
            $review_type_arr[2] = 'google';
        }

        if(isset($review_type[2]) && 'on' == $review_type[3]) {
            $review_type_arr[3] = 'woo';
        }

        $testimonials_array = [];

        $cpt_args = [
            'post_type' => 'dipi_testimonial',
            'post_status' => 'publish',
            'posts_per_page' =>  -1,
        ];

        if(!empty($testimonial_categories)){
            $cpt_args['tax_query'] = [[
                'taxonomy' => 'testimonial_cat',
                'field' => 'term_id',
                'terms' => $testimonial_categories,
                'operator' => 'IN'
            ]];
        }

        $order_by = $args['orderby'] ?? "date_desc";

        switch ( $order_by ) {
            case 'date_asc':
                $cpt_args['orderby'] = 'date';
                $cpt_args['order']   = 'ASC';
                break;
            case 'title_asc':
                $cpt_args['orderby'] = 'title';
                $cpt_args['order']   = 'ASC';
                break;
            case 'title_desc':
                $cpt_args['orderby'] = 'title';
                $cpt_args['order']   = 'DESC';
                break;
            case 'rand':
                $cpt_args['orderby'] = 'rand';
                break;
            default:
                $cpt_args['orderby'] = 'date';
                $cpt_args['order']   = 'DESC';
                break;
        }

        if ('' !== $excluded_post_ids) {
            $cpt_args['post__not_in'] = explode(",", $excluded_post_ids);
        }

       
        $all_ids =  explode(",", $included_post_ids);
        
        if(count($all_ids) > 0 && !empty($args['included_post_ids'])){
            $total_testimonial = intval($total_testimonial) + count($all_ids);
        }
        
        $included_ids = [];
        foreach($all_ids as $id){
            $included_ids[] = intval(trim($id));
        }
        
        $cpt_args['suppress_filters'] = ($testimonial_suppress_filters === 'on');
     
        $included_testimonials = get_posts([
                'post_type' => 'dipi_testimonial',
                'post__in' =>  $included_ids,
                'orderby' => 'post__in'
            ]
        );
        
        $testimonials = (intval($total_testimonial) !== 0)? get_posts($cpt_args): [];
        
        foreach($included_testimonials as $testimonial) {    
            $testimonials[] = $testimonial;
        }
         
        foreach($testimonials as $testimonial) {
            $feature_image = '';
            if (has_post_thumbnail($testimonial->ID)) {
                $image_src = wp_get_attachment_image_src(get_post_thumbnail_id($testimonial->ID), 'full');
                if ($image_src && is_array($image_src)) {
                    $feature_image = $image_src[0];
                }
            }
            $review_type = get_post_meta($testimonial->ID, 'testimonial_type' , true);
            
            if(count($review_type_arr) !== 0) {
                if(!in_array($testimonial->ID, $included_ids) && !in_array($review_type, $review_type_arr)) {
                    continue;
                }
            }

            $testimonial_star = get_post_meta($testimonial->ID, 'testimonial_star' , true);
            if(!$testimonial_star || '' === $testimonial_star){
                $testimonial_star = 0;
            }
            if($testimonial_star < $filter_by_stars) {
                continue;
            }
            
            $testimonials_array[] = [
                'title' => $testimonial->post_title,
                'content' => $testimonial->post_content,
                'profile_image' => get_post_meta($testimonial->ID, 'profile_image' , true),
                'feature_image' => $feature_image,
                'testimonial_email' => '',
                'testimonial_name' => get_post_meta($testimonial->ID, 'testimonial_name' , true),
                'company_name' => get_post_meta($testimonial->ID, 'company_name' , true),
                'company_link' => get_post_meta($testimonial->ID, 'company_link' , true),
                'testimonial_star' => $testimonial_star,
                'testimonial_type' => get_post_meta($testimonial->ID, 'testimonial_type' , true),
                'facebook_id' => get_post_meta($testimonial->ID, 'facebook_id' , true)
            ];
        }

        $woo_args = [
            'type' => 'review'
        ];

        $woo_reviews = get_comments($woo_args);

        foreach($woo_reviews as $woo_review) {
            
            if(count($review_type_arr) !== 0 && !in_array('woo', $review_type_arr)) {
                continue;
            }

            if(!empty($testimonial_categories) || count($testimonial_categories) < 1) continue;

            $testimonial_star = get_comment_meta($woo_review->comment_ID, 'rating', true );
            if($testimonial_star < $filter_by_stars) {
                continue;
            }

            $testimonials_array[] = [
                'title' => '',
                'content' => $woo_review->comment_content,
                'profile_image' => get_avatar_url($woo_review->comment_author_email),
                'testimonial_email' => $woo_review->comment_author_email,
                'testimonial_name' => $woo_review->comment_author,
                'testimonial_star' => $testimonial_star,
                'company_name' => '',
                'company_link' => '',
                'testimonial_type' => 'woo',
                'facebook_id' => ''
            ];
        }
          
        ob_start();

        if(is_array($testimonials_array)){

            $loop = 1;

            foreach($testimonials_array as $testimonial_item ){

                $default_image_url = plugins_url('/avatar.png', __FILE__);
                
                $profile_image_url = (!empty($testimonial_item['feature_image'])) ? $testimonial_item['feature_image'] : $default_image_url;
                $profile_image_url = (!empty($testimonial_item['profile_image']) && filter_var($testimonial_item['profile_image'], FILTER_VALIDATE_URL)) ? $testimonial_item['profile_image'] : $profile_image_url;
            
            ?> 

                <div class="dipi-testimonial-item">

                    <?php if('off' == $use_hide_img && !($remove_empty_html === 'on' && empty($profile_image_url))) : ?>
                    <div class="dipi-testimonial-img">
                        <img 
                            src="<?php echo esc_url($profile_image_url); ?>"
                            alt="<?php echo esc_attr($testimonial_item['title']); ?>"
                        >
                    </div>
                    <?php endif; ?>

                    <?php if('off' == $use_hide_rating ) : ?>
                    <div class="dipi-testimonial-rating">
                        <?php 
                            for ( $i = 1; $i <= 5; ++$i ) :
                            if ( $i <= $testimonial_item['testimonial_star'] ) {
                                echo '<span class="dipi-testimonial-star-rating">★</span>';
                            } else {
                                echo '<span class="dipi-testimonial-star-rating-o">☆</span>';
                            }
                        endfor;
                        ?>
                    </div>
                    <?php endif; ?>

                    <?php if('off' == $use_hide_review && !($remove_empty_html === 'on' && empty($testimonial_item['content']))) : ?>
                        <div class="dipi-testimonial-text">
                            <div><?php

                                $review_text = $testimonial_item['content'];
                                 
                                $dipi_limit_html = dipi_limit_length_of_html($review_text, $review_length);
                                $review_text = $dipi_limit_html['text'];
                                $overflow_review = $dipi_limit_html['overflowed'];
                                
                               echo wp_kses_post($review_text);
                            ?></div>
                            <?php if('off' == $use_hide_readmore && $overflow_review) : ?>
                            <a href="#" data-mfp-src="#dipi-review-popup-<?php echo esc_attr($order_number); ?>-<?php echo esc_attr($loop); ?>" class="dipi-open-popup-link"><?php echo esc_html($readmore_text); ?></a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if('off' == $use_hide_name && !($remove_empty_html === 'on' && empty($testimonial_item['testimonial_name']))) : ?>
                    <div class="dipi-testimonial-name">
                        <?php echo esc_html($testimonial_item['testimonial_name']); ?>
                    </div>
                    <?php endif; ?>

                    <?php if('off' == $use_hide_company && !($remove_empty_html === 'on' && empty($testimonial_item['company_name']))) : ?>
                    <div class="dipi-company-name">
                        <?php if('on' !== $use_hide_company_link && !empty($testimonial_item['company_link'])) : ?> 
                        <a target="_blank" href="<?php echo esc_url($testimonial_item['company_link']); ?>">
                        <?php endif; ?>
                        <?php echo esc_html($testimonial_item['company_name']); ?>
                        <?php if('on' !== $use_hide_company_link && !empty($testimonial_item['company_link'])) : ?> 
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php

                    $popup_color = $args['popup_color'];
                    $popup_size = $args['popup_size'];
                    $close_icon_bg_color = $args['close_icon_bg_color'];
                    $close_icon_color = $args['close_icon_color'];

                    $popup_styles = 'background:'.$popup_color.'; width:'.$popup_size.';';
                    $popup_close_button_styles = 'background:'.$close_icon_bg_color.'; color:'.$close_icon_color.';';

                    ?>

                    <!-- Swiper has problems in Safari, when MagnificPopup injects the popup back to where it was so the workaround is to wrap the whole popup content in another hidden div -->
                    <div style="display:none">

                        <div id="dipi-review-popup-<?php echo esc_attr($order_number); ?>-<?php echo esc_attr($loop); ?>" style="<?php echo esc_attr($popup_styles); ?>" class="mfp-hide dipi-review-popup-text <?php echo esc_attr($order_class)."-popup"?>">

                        <?php if('on' == $use_show_popup_rating ) : ?>
                            <div class="dipi-testimonial-rating">
                                <?php 
                                    for ( $i = 1; $i <= 5; ++$i ) :
                                        if ( $i <= $testimonial_item['testimonial_star'] ) {
                                            echo '<span class="dipi-testimonial-star-rating">★</span>';
                                        } else {
                                            echo '<span class="dipi-testimonial-star-rating-o">☆</span>';
                                        }
                                    endfor;
                                ?>
                            </div>
                        <?php endif; ?>

                        <?php if('on' == $use_show_popup_review && !($remove_empty_html === 'on' && empty($testimonial_item['content']))) : ?>
                        <div class="dipi-testimonial-text">
                            <?php echo wp_kses_post(static::closing_tags($testimonial_item['content'])); ?>
                        </div>
                        <?php endif; ?>

                        <div class="dipi-review-popup-bottom">

                            <?php if('on' == $use_show_popup_image && !($remove_empty_html === 'on' && empty($profile_image_url))) : ?>
                                <div class="dipi-testimonial-img">
                                    <img  src="<?php echo esc_url($profile_image_url); ?>" alt="<?php echo esc_html($testimonial_item['title']); ?>" />
                                </div>
                            <?php endif; ?>
                            <div class="dipi-profile-info">
                                <?php if('on' == $use_show_popup_name && !($remove_empty_html === 'on' && empty($testimonial_item['testimonial_name']))) : ?>
                                    <div class="dipi-testimonial-name">
                                        <?php echo esc_html($testimonial_item['testimonial_name']); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if('on' == $use_show_popup_company && !($remove_empty_html === 'on' && empty($testimonial_item['company_name']))) : ?>
                                    <div class="dipi-company-name">
                                    <?php if('on' !== $use_hide_company_link && !empty($testimonial_item['company_link'])) : ?> 
                                    <a target="_blank" href="<?php echo esc_url($testimonial_item['company_link']); ?>">
                                    <?php endif; ?>
                                    <?php echo esc_html($testimonial_item['company_name']); ?>
                                    <?php if('on' !== $use_hide_company_link && !empty($testimonial_item['company_link'])) : ?> 
                                    </a>
                                    <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <button title="Close (Esc)" style="<?php echo et_core_intentionally_unescaped(stripslashes($popup_close_button_styles), 'html'); ?>" type="button" class="mfp-close">×</button>
                        </div>
                    </div>
            <?php
            if ($loop++ == $total_testimonial) break;
            }
        } else {
            echo "<div class='dipi-error'>No Testimonial Found!</div>";
        }
                
        wp_reset_postdata();

        $output = ob_get_contents();

        ob_end_clean();

        return $output;
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
	public static function render_callback( $attrs, $content, $block, $elements ) {
        $order_number = $block->parsed_block['orderIndex'];
        $computed_depends_on = [
			"total_testimonial",
            "excluded_post_ids",
            "included_post_ids",
            "use_hide_img",
            "use_hide_name",
            "use_hide_review",
            "review_length",
            "use_hide_company",
            "use_hide_company_link",
            "use_hide_rating",
            "use_hide_readmore",
            "readmore_text",
            "review_type",
            "orderby",
            "testimonial_suppress_filters",
            "filter_by_stars",
            "remove_empty_html",
            "use_show_popup_rating",
            "use_show_popup_review",
            "use_show_popup_company",
            "use_show_popup_name",
            "use_show_popup_image",
            "popup_color",
            "popup_size",
            "close_icon_bg_color",
            "close_icon_color",
		];
		$thisProps = [];
        foreach ($computed_depends_on as $key => $value) {
            $thisProps[$value] = static::getPropValue($attrs, $value);
        }
        $thisProps['order_number'] = $order_number;
        $thisProps['order_class'] = "dipi_testimonial_" . $order_number;
        $thisProps['testimonial_categories'] = isset($attrs['testimonial_categories']['innerContent']['desktop']['value']) ? $attrs['testimonial_categories']['innerContent']['desktop']['value'] : [];

        $testimonial_content = static::get_testimonial($thisProps);
        $render_html = static::render_swiper(
            $attrs, 
            $testimonial_content, 
            $order_number, 
            "dipi-testimonial-main", 
            "dipi-testimonial-wrapper", 
            "dipi-testimonial-item"
        );

        $parent       = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
		$parent_attrs = $parent->attrs ?? [];

		return Module::render(
			[
				// FE only.
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'attrs'               => $attrs,
				'elements'            => $elements,
				'id'                  => $block->parsed_block['id'],
				'name'                => $block->block_type->name,
				'moduleCategory'      => $block->block_type->category,
				'classnamesFunction'  => [ Testimonial::class, 'module_classnames' ],
				'stylesComponent'     => [ Testimonial::class, 'module_styles' ],
				'scriptDataComponent' => [ Testimonial::class, 'module_script_data' ],
				'parentAttrs'         => $parent_attrs,
				'parentId'            => $parent->id ?? '',
				'parentName'          => $parent->blockName ?? '',
				'children'            => ElementComponents::component(
					[
						'attrs'         => $attrs['module']['decoration'] ?? [],
						'id'            => $block->parsed_block['id'],

						// FE only.
						'orderIndex'    => $block->parsed_block['orderIndex'],
						'storeInstance' => $block->parsed_block['storeInstance'],
					]
				) . $render_html,
			]
		);
	}
}
