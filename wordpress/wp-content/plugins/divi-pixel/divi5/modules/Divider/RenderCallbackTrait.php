<?php
/**
 * Divider::render_callback()
 *
 * @package DIPI\Modules\Divider
 * @since ??
 */

namespace DIPI\Modules\Divider;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Packages\Module\Module;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;

trait RenderCallbackTrait {
	private static $props = [];
	public static $classname_content = 'dipi-td-content';
	public static $classname_first_decoration = 'dipi-td-first';
    public static $classname_second_decoration = 'dipi-td-second';

	private static function render_content_text() {
        $content_text_tag = static::$props["content_text_tag"];
        return sprintf('<%1$s class="%2$s">%3$s</%1$s>', $content_text_tag, static::$classname_content, static::$props["content_text"]);
    }

    private static function render_content_image() {
        return sprintf('
            <img class="%1$s" src="%2$s" />',
            static::$classname_content,
            static::$props["content_image"]['src']
        );
    }

    private static function render_content_icon() {
		$content_icon = Utils::process_font_icon( static::$props['content_icon'] ?? [] );
        return sprintf(
            '<div class="%1$s"><span class="et-pb-icon">%2$s</span></div>',
                static::$classname_content,
				esc_html($content_icon)
        );
    }

    private static function render_content_lottie() {
        $lottie_file = static::$props["content_lottie"];
        $lottie_code_enable = static::$props["content_lottie_code_enable"];
        $lottie_code = static::$props["content_lottie_code"];
        $extension = pathinfo($lottie_file, PATHINFO_EXTENSION);

        if ($lottie_code_enable === "off" && $extension === "lottie") {
            return sprintf('
                <div class="%1$s">
                    <dotlottie-player id="lottiePlayer" autoplay loop speed="1" src="%2$s"/>
                </div>',
                static::$classname_content,
                $lottie_file
            );
        }
        
        // If lottie_code is provided, use it directly
        if ($lottie_code_enable === "on") {
            // Decode and re-encode to ensure valid JSON
            $json_data = json_encode(json_decode($lottie_code));
            // Use data:application/json to properly set JSON data
            $src = 'data:application/json,' . rawurlencode($json_data);
        } else {
            // Use the file URL if no code is provided
            $src = $lottie_file;
        }
        
        return sprintf('
            <div class="%1$s">
                <lottie-player autoplay loop mode="normal" src="%2$s" style="width: 100%%">
                </lottie-player>
            </div>',
            static::$classname_content,
            $src
        );
    }

    private static function render_first_decoration() {
        return static::render_decoration(static::$classname_first_decoration, true);
    }

    private static function render_second_decoration () {
        $use_custom_second_decoration = static::$props["use_custom_second_decoration"];
        $isFirst = $use_custom_second_decoration !== 'on';
        return static::render_decoration(static::$classname_second_decoration, $isFirst);
    }

    private static function render_decoration ($classname, $isFirst) {
        $decoration_style = $isFirst ? static::$props['first_decoration_style'] : static::$props['second_decoration_style'];
 
        $classnames = sprintf('dipi-td-decoration  dipi-td-decoration-%1$s %2$s', $decoration_style, $classname);
        $render = '';
      
        $render .= $decoration_style === 'line' ? static::render_line_decoration($classnames, $isFirst) : '';
        $render .= $decoration_style === 'image' ? static::render_image_decoration($classnames, $isFirst) : '';
        $render .= $decoration_style === 'icon' ? static::render_icon_decoration($classnames, $isFirst) : '';
        $render .= $decoration_style === 'lottie' ? static::render_lottie_decoration($classnames, $isFirst) : '';
        $render .= $decoration_style === 'empty' ? static::render_line_decoration($classnames, $isFirst) : '';
       
        return $render;
    }

    private static function render_line_decoration($classname, $isFirst = true){
        return sprintf('<div class="%1$s"></div>', $classname);
    }

    private static function render_image_decoration($classname, $isFirst = true){
        $decoration_image = $isFirst ? static::$props['decoration_first_image'] : static::$props['decoration_second_image'];
        return sprintf('<div class="%1$s"><img src="%2$s" /></div>', $classname, $decoration_image['src']);
    }

    private static function render_icon_decoration($classname, $isFirst = true){
        $decoration_icon = $isFirst ? Utils::process_font_icon( static::$props['decoration_first_icon']?? [] ) :
			Utils::process_font_icon( static::$props['decoration_second_icon']?? [] );
        
        return sprintf('<div class="%1$s"><span class="et-pb-icon">%2$s</span></div>',
			$classname, 
		    $decoration_icon
		);
    }

    private static function render_lottie_decoration($classname, $isFirst = true){
        $decoration_lottie = $isFirst ? static::$props['decoration_first_lottie_file'] : static::$props['decoration_second_lottie_file'];
        $decoration_lottie_code_enable = $isFirst ? static::$props["decoration_first_lottie_code_enable"] : static::$props["decoration_second_lottie_code_enable"];
        $decoration_lottie_code = $isFirst ? static::$props["decoration_first_lottie_code"] : static::$props["decoration_second_lottie_code"];
        $extension = pathinfo($decoration_lottie, PATHINFO_EXTENSION);

        if ($decoration_lottie_code_enable === "off" && $extension === "lottie") {
            return sprintf('
                <div class="%1$s">
                    <dotlottie-player id="lottiePlayer_%3$s" autoplay loop speed="1" src="%2$s"/>
                </div>',
                $classname,
                $decoration_lottie,
                $isFirst ? "first" : "second"
            );
        }
        
        // If lottie_code is provided, use it directly
        if ($decoration_lottie_code_enable === "on") {
            // Decode and re-encode to ensure valid JSON
            $json_data = json_encode(json_decode($decoration_lottie_code));
            // Use data:application/json to properly set JSON data
            $src = 'data:application/json,' . rawurlencode($json_data);
        } else {
            // Use the file URL if no code is provided
            $src = $decoration_lottie;
        }
        
        return sprintf('
            <div class="%1$s">
                <lottie-player autoplay loop mode="normal" src="%2$s" style="width: 100%%">
                </lottie-player>
            </div>',
            $classname,
            $src
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
	public static function render_callback( $attrs, $content, $block, $elements ) {
		static::$props = array_map(function($attr) {
			return is_array($attr) && array_key_exists('innerContent', $attr) ? $attr['innerContent']['desktop']['value'] : $attr;
		}, $attrs); 

		$content_type = static::$props["content_type"];
        $hide_first_element = static::$props["hide_first_element"];
        $hide_second_element = static::$props["hide_second_element"];
        $use_custom_second_decoration = static::$props["use_custom_second_decoration"];

        $className = sprintf('dipi-pixel-divider dipi-pixel-divider-%1$s dipi-pixel-divider-%2$s', 
            static::$props["divider_layout"], 
            $content_type
        );   
        $className .= $hide_first_element === 'on' ? ' dipi-pixel-divider-hide-first' : '';
        $className .= $hide_second_element === 'on' ? ' dipi-pixel-divider-hide-second' : '';
        $className .= $use_custom_second_decoration !== 'on' ? ' dipi-pixel-divider-mirror' : '';

        $content  = '';
        $content = ($content_type === 'text') ? static::render_content_text() : $content;
        $content = ($content_type === 'image') ? static::render_content_image() : $content;
        $content = ($content_type === 'icon') ? static::render_content_icon() : $content;
        $content = ($content_type === 'lottie') ? static::render_content_lottie() : $content;
      
        $first_decoration = $hide_first_element !== 'on'? static::render_first_decoration() : '';
        $second_decoration = $hide_second_element !== 'on'? static::render_second_decoration() : '';

		$render_html =  sprintf('
		<div class="%1$s">%3$s%2$s%4$s</div>',
			$className,
			$content,
			$first_decoration,
			$second_decoration

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
				'classnamesFunction'  => [ Divider::class, 'module_classnames' ],
				'stylesComponent'     => [ Divider::class, 'module_styles' ],
				'scriptDataComponent' => [ Divider::class, 'module_script_data' ],
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
