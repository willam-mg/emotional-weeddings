<?php
namespace DIPI\Modules\Counter;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Module;
use DIPI\Traits\BaseRenderTrait;

trait RenderCallbackTrait {
  use BaseRenderTrait;
 

  static function get_easy_pie_chart_data($attrs, $count_to_value, $count_from_value){

    $default_circle_track_color = 'rgba(0,0,0,0.1)';
    $counter_advanced  = $attrs['counter']['advanced'];
    $counter_decoration = $attrs['counter']['decoration'];
    $easy_pie_chart_data = array();
    $easy_pie_chart_data[] = "data-count-to='{$count_to_value}'";
    $easy_pie_chart_data[] = "data-count-from='{$count_from_value}'";

    $count_duration = static::getPropValue($counter_advanced, 'count_duration');
    $easy_pie_chart_data[] = "data-count-duration='{$count_duration}'";

    $count_animation_delay = static::getPropValue($counter_advanced, 'count_animation_delay');
    $easy_pie_chart_data[] = "data-count-animation-delay='{$count_animation_delay}'";

    $force_decimal_places = 'on' === static::getPropValue($counter_advanced, 'force_decimal_places');
    $easy_pie_chart_data[] = "data-force-decimal-places='{$force_decimal_places}'";

    $count_number_decimals = static::getPropValue($counter_advanced, 'count_number_decimals');
    $easy_pie_chart_data[] = "data-decimal-places='{$count_number_decimals}'";

    $counter_type = static::getPropValue($counter_advanced, 'counter_type');
    $easy_pie_chart_data[] = "data-counter-type='{$counter_type}'";

    $count_circle_percent = static::getPropValue($counter_advanced, 'count_circle_percent');
    $easy_pie_chart_data[] = "data-circle-percent='{$count_circle_percent}'";

    $circle_bar_color = static::getPropValue($counter_decoration, 'circle_bar_color');
    $circle_bar_color = isset($circle_bar_color) && '' !== $circle_bar_color ? $circle_bar_color : et_builder_accent_color();
    $easy_pie_chart_data[] = "data-circle-bar-color='{$circle_bar_color}'";

    $circle_track_color = static::getPropValue($counter_decoration, 'circle_track_color');
    $circle_track_color = isset($circle_track_color) && '' !== $circle_track_color ? $circle_track_color : $default_circle_track_color;
    $easy_pie_chart_data[] = "data-circle-track-color='{$circle_track_color}'";

    $circle_line_width = static::getPropValue($counter_decoration, 'circle_line_width');
    $easy_pie_chart_data[] = "data-circle-line-width='{$circle_line_width}'";

    $circle_line_cap = static::getPropValue($counter_advanced, 'circle_line_cap');
    $easy_pie_chart_data[] = "data-circle-line-cap='{$circle_line_cap}'";

    $circle_size = static::getPropValue($counter_advanced, 'circle_size');
    if (isset($circle_size) && '' !== $circle_size) {
        $easy_pie_chart_data[] = "data-circle-size='{$circle_size}'";
    }

    $circle_use_scale = static::getPropValue($counter_advanced, 'circle_use_scale');
    if (isset($circle_use_scale) && 'on' === $circle_use_scale) {
        $circle_scale_length = static::getPropValue($counter_advanced, 'circle_scale_length');
        $circle_scale_color = static::getPropValue($counter_advanced, 'circle_scale_color');
        $circle_scale_color = isset($circle_scale_color) && '' !== $circle_scale_color ? $circle_scale_color : 'rgba(0,0,0,0.1)';
        $easy_pie_chart_data[] = "data-circle-use-scale='true'";
        $easy_pie_chart_data[] = "data-circle-scale-color='{$circle_scale_color}'";
        $easy_pie_chart_data[] = "data-circle-scale-length='{$circle_scale_length}'";
    }

    $circle_rotate = static::getPropValue($counter_advanced, 'circle_rotate');
    $easy_pie_chart_data[] = "data-circle-rotate='{$circle_rotate}'";

    $count_number_thousands_separator = static::getPropValue($counter_advanced, 'count_number_thousands_separator');
    $easy_pie_chart_data[] = "data-number-separator='{$count_number_thousands_separator}'";

    $count_number_decimal_separator = '' !== static::getPropValue($counter_advanced, 'count_number_decimal_separator') ? static::getPropValue($counter_advanced, 'count_number_decimal_separator') : localeconv()['decimal_point'];
    $easy_pie_chart_data[] = "data-number-decimal-separator='{$count_number_decimal_separator}'";
    return implode(' ', $easy_pie_chart_data);
  }

  static function count_posts($attrs = array())
  {
        $args = $attrs['counter']['advanced']['count_post']['desktop']['value'];
        $count = 0;
        foreach (static::get_post_types_to_count() as $post_type => $post_type_name) {
            //Check if we should count this post type
            if(!isset($args["count_{$post_type}"]) || $args["count_{$post_type}"] !== 'on'){
                continue;
            }
            //Get all taxonomies for this post type
            $taxnomoy_objects = get_object_taxonomies($post_type, 'objects');
            //If we have taxonomies, we need to count based on them. If there are no taxonomies 
            //on this post type, we simply count all posts of this post type
            if($taxnomoy_objects && count($taxnomoy_objects) > 0) {

                //Build a WP_Query with Tax Query to count based on the selected terms
                $query_args = array(
                    'post_type'     => $post_type, 
                    'post_status'   => 'publish', //TODO: Maybe configurable?
                    'posts_per_page' => -1,
                    'tax_query' => array(
                      'relation' => 'OR',
                    )
                );

                foreach($taxnomoy_objects as $taxonomy) {
                    
                    //Skip taxonomies for which we don't show settings
                    if(!$taxonomy->show_ui || !$taxonomy->show_in_menu || !$taxonomy->public){
                        continue;
                    }

                    if($args["count_{$post_type}_{$taxonomy->name}_all_terms"] !== 'off'){
                        $query_args['tax_query'][] = [
                            'taxonomy' => $taxonomy->name,
                            'operator' => 'EXISTS'
                        ];
                    } else {
                        $selected_terms = $args["{$post_type}_{$taxonomy->name}"];
                        if(isset($selected_terms) && '' !== $selected_terms){
                            $term_ids = $selected_terms;
                        } else {
                            $term_ids = [];
                        }
                        
                        $query_args['tax_query'][] = [
                            'taxonomy' => $taxonomy->name,
                            'field' => 'id',
                            'terms' => $term_ids
                        ];
                    }

                    if($args["count_{$post_type}_{$taxonomy->name}_without_terms"] !== 'off'){
                        $query_args['tax_query'][] = [
                            'taxonomy' => $taxonomy->name,
                            'operator' => 'NOT EXISTS'
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

    static function get_post_types_to_count()
    {
        global $wp_post_types;
        $post_types = array(
            'post' => $wp_post_types['post']->labels->name,
            'page' => $wp_post_types['page']->labels->name,
        );

        foreach (get_post_types(array('public' => true, '_builtin' => false), 'objects', 'and') as $post_type) {
            $post_types[$post_type->name] = $post_type->labels->name;
        }

        return $post_types;
    }
   static function render_counter($attrs, $id) {
    $counter_advanced = $attrs['counter']['advanced'];

   
      
      $counter_type = static::getPropValue($counter_advanced, 'counter_type');
      $count_duration = static::getPropValue($counter_advanced, 'count_duration');
      $count_type = static::getPropValue($counter_advanced, 'count_type');
      $count_date_type = static::getPropValue($counter_advanced, 'count_date_type');
      $count_to_type = static::getPropValue($counter_advanced, 'count_to_type');
      $count_to_number = static::getPropValue($counter_advanced, 'count_to_number') ?? 0;
      $count_to_date = static::getPropValue($counter_advanced, 'count_to_date') ?? "";
      $count_to_include_date = static::getPropValue($counter_advanced, 'count_to_include_date');
      $count_from_type = static::getPropValue($counter_advanced, 'count_from_type');
      $count_from_number = static::getPropValue($counter_advanced, 'count_from_number') ?? 0;
      $count_from_date = static::getPropValue($counter_advanced, 'count_from_date') ?? "";
      $count_from_include_date = static::getPropValue($counter_advanced, 'count_from_include_date');
      $count_animation_delay = static::getPropValue($counter_advanced, 'count_animation_delay');
      $halfcircle_label = static::getPropValue($counter_advanced, 'halfcircle_label');
      $prefix = static::getPropValue($attrs, 'prefix');
      $suffix = static::getPropValue($attrs, 'suffix');

      $count_to_value = 0;
      $count_from_value = 0;
      

      if ('number' == $count_to_type) {
          $count_to_value = $count_to_number;
          $count_from_value = $count_from_number;
      } else if ('date' == $count_to_type) {

          $count_to_date = '' !== $count_to_date ? strtotime($count_to_date) : current_time('timestamp');
          $count_from_date = '' !== $count_from_date ? strtotime($count_from_date) : current_time('timestamp');

          if ('on' === $count_to_include_date) {
              switch ($count_date_type) {
                  case 'seconds':
                      $count_to_date += 1;
                      break;
                  case 'minutes':
                      $count_to_date += 60;
                      break;
                  case 'hours':
                      $count_to_date += 60 * 60;
                      break;
                  case 'weeks':
                      $count_to_date += 60 * 60 * 24 * 7;
                      break;
                  case 'month':
                      $count_to_date += 60 * 60 * 24 * (365 / 12);
                      break;
                  case 'years':
                      $count_to_date += 60 * 60 * 24 * 365;
                      break;
                  default: //Default is days
                      $count_to_date += 60 * 60 * 24;
              }
          }

          if ('on' === $count_from_include_date) {
              switch ($count_date_type) {
                  case 'seconds':
                      $count_from_date += 1;
                      break;
                  case 'minutes':
                      $count_from_date += 60;
                      break;
                  case 'hours':
                      $count_from_date += 60 * 60;
                      break;
                  case 'weeks':
                      $count_from_date += 60 * 60 * 24 * 7;
                      break;
                  case 'month':
                      $count_from_date += 60 * 60 * 24 * (365 / 12);
                      break;
                  case 'years':
                      $count_from_date += 60 * 60 * 24 * 365;
                      break;
                  default: //Default is days
                      $count_from_date += 60 * 60 * 24;
              }
          }

          $difference_in_seconds = $count_to_date - $count_from_date;

          switch ($count_date_type) {
              case 'seconds':
                  $count_to_value = sprintf('%1$s', floor($difference_in_seconds));
                  break;
              case 'minutes':
                  $count_to_value = sprintf('%1$s', floor($difference_in_seconds / 60));
                  break;
              case 'hours':
                  $count_to_value = sprintf('%1$s', floor($difference_in_seconds / (60 * 60)));
                  break;
              case 'weeks':
                  $count_to_value = sprintf('%1$s', floor($difference_in_seconds / (60 * 60 * 24 * 7)));
                  break;
              case 'month':
                  $count_to_value = sprintf('%1$s', floor($difference_in_seconds / (60 * 60 * 24 * (365 / 12))));
                  break;
              case 'years':
                  $count_to_value = sprintf('%1$s', floor($difference_in_seconds / (60 * 60 * 24 * 365)));
                  break;
              default:
                  $count_to_value = sprintf('%1$s', floor($difference_in_seconds / (60 * 60 * 24)));
          }
      } else if ('post' == $count_to_type) {
          $count_to_value = static::count_posts($attrs);
      }

      $start_label = "";
      $end_label = "";
      if ($halfcircle_label === 'presuffix') {
          $start_label = sprintf('<span class="dipi_label dipi_start_label">%1$s</span>',
              $prefix
          );
          $end_label = sprintf('<span class="dipi_label dipi_end_label">%1$s</span>',
              $suffix
          );
      } else if ($halfcircle_label === 'fromto') {
          $start_label = sprintf('<span class="dipi_label dipi_start_label">%1$s</span>',
              $count_from_value
          );
          $end_label = sprintf('<span class="dipi_label dipi_end_label">%1$s</span>',
              $count_to_value
          );
      }


      return sprintf(
        '<div class="dipi-counter-container" style="width: 100%%">
          <div id="%6$s_wrapper" data-id="%6$s_wrapper" class="dipi_counter_number_wrapper %5$s" %1$s>
            %7$s
            <div class="dipi_counter_number">
                <span class="dipi_counter_number_prefix">%2$s</span><span class="dipi_counter_number_number">%3$s</span><span class="dipi_counter_number_suffix">%4$s</span>
            </div>
            %8$s
          </div></div>',
        static::get_easy_pie_chart_data($attrs, $count_to_value, $count_from_value),
        $prefix,
        $count_from_value,
        $suffix,
        $counter_type, #5
        $id,
        $start_label,
        $end_label
    );

    
  }
   
  public static function render_callback( $attrs, $content, $block, $elements ) {
    
    $id = $block->parsed_block['id'];
    $parent = BlockParserStore::get_parent( $id, $block->parsed_block['storeInstance'] );
   

    $content = static::render_counter($attrs, $id);
    
    return Module::render(
      [
        // FE only.
        'orderIndex'          => $block->parsed_block['orderIndex'],
        'storeInstance'       => $block->parsed_block['storeInstance'],

        // VB equivalent.
        'attrs'               => $attrs,
        'elements'            => $elements,
        'id'                  => $id,
        'moduleClassName'     => '',
        'name'                => $block->block_type->name,
        'classnamesFunction'  => [ Counter::class, 'module_classnames' ],
        'moduleCategory'      => $block->block_type->category,
        'stylesComponent'     => [ Counter::class, 'module_styles' ],
        'scriptDataComponent' => [ Counter::class, 'module_script_data' ],
        'parentAttrs'         => $parent->attrs ?? [],
        'parentId'            => $parent->id ?? '',
        'parentName'          => $parent->blockName ?? '',
        'children'            => 
                  // $elements->style_components(['attrName' => 'module']).
                  // $elements->style_components(['attrName' => 'content']).
                  // $elements->style_components(['attrName' => 'image']).
                  $content 
      ]
    );
  }
}