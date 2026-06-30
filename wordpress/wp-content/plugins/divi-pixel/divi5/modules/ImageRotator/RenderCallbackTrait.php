<?php
/**
 * ImageRotator::render_callback()
 *
 * @package DIPI\Modules\ImageRotator
 * @since ??
 */

namespace DIPI\Modules\ImageRotator;

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

trait RenderCallbackTrait {
	use BaseRenderTrait;

	private static $props = [];

	private static function get_attachment_image($attachment_id, $image_size, $fallback_url)
    {
        $attachment = wp_get_attachment_image_src($attachment_id, $image_size);
        if ($attachment) {
            return $attachment[0];
        } else {
            return $fallback_url;
        }
    }

    public static function render_images($args = array(), $conditional_tags = array(), $current_page = array())
    {
        $defaults = [
            'gallery' => '',
            'autoplay' => false,
        ];

        $args = wp_parse_args($args, $defaults);

        $attachment_ids = explode(",", $args["gallery"]);
        $items = [];
        $att_ids = [];
        $gallery_orderby = explode('_', $args['gallery_orderby']);
        if($gallery_orderby[0] === 'none') {
            $att_ids = $attachment_ids;
        }
        if($gallery_orderby[0] !== 'none') {
            $query_args = array( 
                'post_type'      => 'attachment',
                'post_mime_type' => 'image',
                'post__in' =>  $attachment_ids,
                'posts_per_page' => '-1'
            );
            
            $query_args['orderby'] = $gallery_orderby[0];
            
            if(count($gallery_orderby) > 1) {
                $query_args['order'] = strtoupper($gallery_orderby[1]);
            }  
            
            $attachments_posts = get_posts($query_args);
            if ($attachments_posts) {
                foreach ( $attachments_posts as $attachment ) { 
                    $att_ids[] =  $attachment->ID;
                }
            }  
        }

        foreach ($att_ids as $index=>$attachment_id) {
            $attachment = wp_get_attachment_image_src($attachment_id, "full");
            if (!$attachment) {
                continue;
            }

            $image = $attachment[0];

            $image_desktop_url = (isset($args['image_size_desktop']) && !empty($args['image_size_desktop']))? static::get_attachment_image($attachment_id, $args['image_size_desktop'], $image) : $image;
            $image_tablet_url = (isset($args['image_size_tablet']) && !empty($args['image_size_tablet']))? static::get_attachment_image($attachment_id, $args['image_size_tablet'], $image): $image_desktop_url;
            $image_phone_url = (isset($args['image_size_phone']) && !empty($args['image_size_phone']))?static::get_attachment_image($attachment_id, $args['image_size_phone'], $image): $image_tablet_url;

            $image_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
            $image_title = get_the_title($attachment_id);


            $item_class = '';
            if ($index > 0) {
                $item_class = 'hidden';
            }
 
            $items[] = sprintf('
                <img src="%1$s"
                    data-index="%6$s"
                    decoding="sync"
                    srcset="%2$s 768w, %3$s 980w, %4$s 1024w"
                    sizes="(max-width: 768px) 768px, (max-width: 980px) 980px, 1024px"
                    class="dipi-img-rotator-item dipi-img-rotator-item-%6$s %5$s"
                />',
                $image,
                $image_phone_url,
                $image_tablet_url,
                $image_desktop_url,
                $item_class, #5
                $index
            );
        }
        return implode("", $items);
    }

    public static function render_preload($thisProps) {
        if($thisProps['use_preload'] !== 'on') return '';
        $preload_svg_color = $thisProps['preload_svg_color']? $thisProps['preload_svg_color'] : '#202020';
        $preload_image = '<svg enable-background="new 0 0 0 0" version="1.1" viewBox="0 0 100 100" xml:space="preserve" xmlns="http://www.w3.org/2000/svg"><path d="m73 50c0-12.7-10.3-23-23-23s-23 10.3-23 23m3.9 0c0-10.5 8.5-19.1 19.1-19.1s19.1 8.6 19.1 19.1" fill="'.$preload_svg_color.'"><animateTransform attributeName="transform" attributeType="XML" dur="1s" from="0 50 50" repeatCount="indefinite" to="360 50 50" type="rotate"/>  </path></svg>';
        if($thisProps['use_custom_preload_image'] === 'on' && !empty($thisProps['custom_preload_image'])){
            $preload_image = simplexml_load_file($thisProps['custom_preload_image']);
            if($preload_image) {
                $preload_image = $preload_image->asXML();
            }else{
                $preload_image = '<img src="'.$thisProps['custom_preload_image'].'" />';
            }
        }
        return sprintf('
            <div class="dipi-image-rotator-preload">
                %1$s
            </div>',
            $preload_image
        );
    }

    private static function get_indicator_icon_url(){
        $mockups_path = __DIR__ . '/360-icon.png';
        if(file_exists($mockups_path)) { // return whatever requested size if exists.
            return plugins_url('/360-icon.png', __FILE__);
        }
        
        return false;
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
        $thisProps = [];
        foreach ($attrs as $key => $value) {
            $thisProps[$key] = static::getPropValue($attrs, $key);
        }
        $items = static::render_images($thisProps);
        $rotate_on_drag = $thisProps['rotate_on_drag'];
        $rotate_on_wheel = $thisProps['rotate_on_wheel'];
        $show_playpause_buttons = $thisProps['show_playpause_buttons'];
        $button_v_alignment = $thisProps['button_v_alignment'];
        $playpause_btn_html = "";
        if( $show_playpause_buttons === 'on') {
            $play_button = $elements->render([
                'attrName' => 'play_button',
            ]);
            $pause_button = $elements->render([
                'attrName' => 'pause_button',
            ]);
            $playpause_btn_html = sprintf('
                <div class="dipi-image-rotator-playpause-buttons">
                    %1$s
                    %2$s
                </div>',
                $play_button,
                $pause_button
            );
        }
        $config = [
            "autoplay" => $thisProps["autoplay"],
            "play_speed" => $thisProps["play_speed"],
            "rotate_on_drag" => $rotate_on_drag,
            "drag_direction" => $thisProps["drag_direction"],
            "rotate_on_wheel" => $thisProps["rotate_on_wheel"],
            "invert_play" => $thisProps["invert_play"],
        ];
        $module_classes = [];
        $module_classes[] = "button-pos-$button_v_alignment";
        if ($rotate_on_drag === "on") {
            $module_classes[] = "rotate_on_drag";
        }
        if ($rotate_on_wheel === "on") {
            $module_classes[] ="rotate_on_wheel";
        }
        
        $preload = static::render_preload($thisProps);

        $icon_content = $thisProps["hide_360_icon"] === "off" ? sprintf('<img class="indicator-icon" src="%1$s" />',  static::get_indicator_icon_url()): "";
        
        $render_html = sprintf(
            '<div class="dipi-image-rotator %5$s" data-config="%3$s">
                %4$s
                <div class="dipi-image-rotator-inner">
                    %2$s
                    %6$s    
                    <div class="dipi-image-rotator-images" draggable="true" data-active-id="0">
                        %1$s
                    </div>
                </div>
            </div>',
            $items,
            $playpause_btn_html,
            esc_attr(htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8')), #5
            $icon_content,
            implode(" ", $module_classes),
            $preload
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
				'classnamesFunction'  => [ ImageRotator::class, 'module_classnames' ],
				'stylesComponent'     => [ ImageRotator::class, 'module_styles' ],
				'scriptDataComponent' => [ ImageRotator::class, 'module_script_data' ],
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
