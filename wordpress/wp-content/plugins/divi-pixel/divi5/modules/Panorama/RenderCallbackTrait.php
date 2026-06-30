<?php
/**
 * Panorama::render_callback()
 *
 * @package DIPI\Modules\Panorama
 * @since ??
 */

namespace DIPI\Modules\Panorama;

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

	public static function isYoutube($url)
    {
        if (strpos($url, 'youtube') > 0) {
            return true;
        }

        return false;
    }
    public static function getYoutubeID($url)
    {
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
        return $match[1];
    }

	public static function render_panorama_video($thisProps)
    {

        if (static::isYoutube($thisProps['videomp4'])) {
            return sprintf('
				<div class="dipi-panorma-wrapper wrapper" style="height:100%%">
					<div class="dipi-panorama-overlay">
						<div class="dipi-panorama-overlay-content">
							<div class="dipi-panorama-icon dipi-scroll-icon"></div><br />
							<div class="dipi-panorama-icon dipi-hand-icon"></div>
						</div>
					</div>
					<iframe height="100%%" width="100%%" src="https://www.youtube.com/embed/%1$s?controls=0" frameBorder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowFullScreen></iframe>
				</div>',
                static::getYoutubeID($thisProps['videomp4'])
            );
        }

        $videomp4 = '';
        if (!empty($thisProps['videomp4'])) {
            $videomp4 = sprintf('<source src="%1$s" type="video/mp4"/>', $thisProps['videomp4']);
        }

        $videowebm = '';
        if (!empty($thisProps['videowebm'])) {
            $videowebm = sprintf('<source src="%1$s" type="video/webm"/>', $thisProps['videowebm']);
        }

        return sprintf('
			<div class="dipi-panorma-wrapper wrapper" style="height:100%%">
				<div class="dipi-panorama-overlay">
					<div class="dipi-panorama-overlay-content">
						<div class="dipi-panorama-icon dipi-scroll-icon"></div><br />
						<div class="dipi-panorama-icon dipi-hand-icon"></div>
					</div>
				</div>
				<video class="dipi-panorama-video video-js vjs-default-skin vjs-big-play-centered"
					controls
					preload="none"
					style="width:100%%;height:100%%"
					poster="%1$s"
					data-is-autoload="%4$s"
					crossOrigin="anonymous">
					%2$s
					%3$s
					<p class="vjs-no-js">
						To view this video please enable JavaScript, and consider upgrading to
						a web browser that supports HTML5 video
					</p>
				</video>
			</div>',
            $thisProps['poster'], // #1
            $videomp4, // #2
            $videowebm, // #3
            $thisProps['is_autoload']// #4
        );
    }

	public static function render_panorama_image2d($thisProps, $rs_height)
    {
        $img_src = $thisProps['image2d'];
        $img_size = getimagesize($thisProps['image2d']);
       
        return sprintf(
            '<div class="dipi-panorma-wrapper wrapper loaded dipi-panorma-image2d" style="height:100%%;">
				<div class="dipi-panorama-overlay">
					<div class="dipi-panorama-overlay-content">
 						<div class="dipi-panorama-icon dipi-scroll-icon"></div><br />
						<div class="dipi-panorama-icon dipi-hand-icon"></div>
					</div>
				</div>
				<div class="dipi-img-drag" data-direction="%3$s" data-repeat="%4$s" 
                    data-module-height="%2$s" 
                    data-module-height-tablet="%7$s" 
                    data-module-height-phone="%8$s" 
                    data-image-width="%5$s" 
                    data-image-height="%6$s">
				</div>
			</div>',
            $thisProps['image2d'], // #1
            $rs_height['desktop'], // #2
            $thisProps['direction'], // #3
            $thisProps['repeat'], // #4
            $img_size[0], // #5
            $img_size[1], // #6
            $rs_height['tablet'], // #7
            $rs_height['phone'] // #8
        );
    }

	public static function render_panorama_image($thisProps, $height, $module_id)
    {
        $images = '';
        if ($thisProps['type'] == 'Equirectangular') {
            $images = sprintf('data-image="%1$s"', $thisProps['image']);
        } else if ($thisProps['type'] == 'Cube Map') {
            $images = sprintf('
				data-image0="%1$s"
				data-image1="%2$s"
				data-image2="%3$s"
				data-image3="%4$s"
				data-image4="%5$s"
				data-image5="%6$s"',
                $thisProps['image0'],
                $thisProps['image1'],
                $thisProps['image2'],
                $thisProps['image3'],
                $thisProps['image4'],
                $thisProps['image5']
            );
        }
        return sprintf(
            '<div class="dipi-panorma-wrapper wrapper" style="height:100%%">
				<div class="dipi-panorama-overlay">
					<div class="dipi-panorama-overlay-content">
						<div class="dipi-panorama-icon dipi-scroll-icon"></div><br />
						<div class="dipi-panorama-icon dipi-hand-icon"></div>
					</div>
				</div>
				<div id="%5$s" class="dipi-panorama"
				data-type="%1$s"
				data-is-autoload="%6$s"
				%2$s
				style="width:%3$s; height: %4$s"></div>
			</div>',
            $thisProps['type'], // #1
            $images, // #2
            '100%', // #3
            $height, // #4
            $module_id, // #5
            $thisProps['is_autoload']// #6
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
        $parent       = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
		$parent_attrs = $parent->attrs ?? [];

		$thisProps = [];
        foreach ($attrs as $key => $value) {
            $thisProps[$key] = static::getPropValue($attrs, $key);
        }

		$rs_height = [];
		$rs_height['desktop'] = $attrs["module"]["decoration"]["sizing"]["desktop"]["value"]["height"] ?? "500px";
		$rs_height['tablet'] = $attrs["module"]["decoration"]["sizing"]["tablet"]["value"]["height"] ?? $rs_height['desktop'];
		$rs_height['phone'] = $attrs["module"]["decoration"]["sizing"]["phone"]["value"]["height"] ?? $rs_height['tablet'];

		$render_html = "";

		if ($thisProps['type'] == "Video") {
			$render_html = static::render_panorama_video($thisProps);
		}
		else if ($thisProps['type'] == "2D Image") {
			$render_html = static::render_panorama_image2d($thisProps, $rs_height);
		} else {
			$render_html = static::render_panorama_image($thisProps, $rs_height["desktop"], $block->parsed_block['id']);
		}

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
				'classnamesFunction'  => [ Panorama::class, 'module_classnames' ],
				'stylesComponent'     => [ Panorama::class, 'module_styles' ],
				'scriptDataComponent' => [ Panorama::class, 'module_script_data' ],
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
