<?php
/**
 * SVGAnimator::render_callback()
 *
 * @package DIPI\Modules\SVGAnimator
 * @since ??
 */

namespace DIPI\Modules\SVGAnimator;

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

	private static function isUrlValid($url) {
        // Create a stream context with "ignore_errors" set to true
        $context = stream_context_create(['http' => ['ignore_errors' => true]]);
        // Fetch the URL headers
        $headers = get_headers($url, 0, $context); // phpcs:ignore
        // Check if the response code contains "404"
        if (strpos($headers[0], '404') !== false) {
           return false; // URL is invalid or returns a 404 error
        }
        return true; // URL is valid
    }

    private static function dipi_file_get_contents($src)
    {
        if (!static::isUrlValid($src)) return '';
        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) {
            $options = ["ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ]];

            $svg_content = file_get_contents($src, false, stream_context_create($options));
        } else {
            $svg_content = file_get_contents($src);
        }
        if (!$svg_content) {
            $svg_content = static::dipi_curl_get_contents($src);
        }
        return $svg_content;
    }

    private static function dipi_curl_get_contents($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        $html = curl_exec($ch);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    private static function dipi_curl_file_get_contents($src)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $src);
        $contents = curl_exec($curl);
        curl_close($curl);

        if ($contents) {
            return $contents;
        } else {
            return FALSE;
        } 
    }

    public static function dipi_get_url_content($src) {
        if (!$src) {
            return '';
        }
        $url_content = '';
        if (ini_get('allow_url_fopen')) {
            $url_content = static::dipi_file_get_contents($src);
        } else {
            // $svg_content = '<p>' . esc_html__('Your server has disabled \'allow_url_fopen\'. You must use SVG from your media library or paste link from same domain as - ', 'dipi-divi-pixel') . $_SERVER['HTTP_HOST'] . '</p>';
            $url_content = static::dipi_curl_file_get_contents($src);
        }
        return $url_content;
    }

	public static function add_prefix_svg_selector($svg_html, $order_class)
    {
        $stylecode_count = preg_match_all('/<style (.*?)>(.*?)<\/style>/s', $svg_html, $style_codes);
        $orderClassName = $order_class;
        if (!$orderClassName) {
            $orderClassName="dipi_svg_animator_unknown";
        }
        $prefix = ".".$orderClassName." ";
        if ($stylecode_count) {
            foreach($style_codes[2] as $style_code) {
                $stylecode_prefix = "";
                preg_match_all( '/(?ims)([a-z0-9\s\.\:#_\-@,]+)\{([^\}]*)\}/', $style_code, $arr);
                foreach ($arr[0] as $i => $x){
                    $selector = trim($arr[1][$i]);
                    $rules = trim($arr[2][$i]);
                    
                    $selectors = explode(',', trim($selector));                    
                    $selectors_withprefix = [];
                    foreach ($selectors as $strSel){
                        $selectors_withprefix[] = $prefix.$strSel;
                    }
                    $selector_withprefix = implode(',', $selectors_withprefix);
                    $stylecode_prefix .= $selector_withprefix."{".$rules."}\r\n";
                }
                
                $svg_html = str_replace($style_code, $stylecode_prefix, $svg_html);
            }
        }

        return $svg_html;
    }
    public static function render_svg_animator($args, $has_config = true)
    {
        $defaults = [
            'src' => '',
            'svg_color' => '',
            'svg_weight' => '',
            'align' => '',
            'anim_type' => 'delayed',
            'anim_dur' => '100',
            'path_timing_func' => 'LINEAR',
            'anim_timing_func' => 'LINEAR',
            'anim_start' => 'autostart',
            'align' => 'center',
            'replay_on_click' => 'off',
        ];
        $args = wp_parse_args($args, $defaults);

        $src = $args['src'];
        $svg_color = $args['svg_color'];
        $svg_weight = $args['svg_weight'];
        $animation_type = $args['anim_type'];
        $animation_duration = $args['anim_dur'];
        $path_timing_function = $args['path_timing_func'];
        $anim_timing_function = $args['anim_timing_func'];
        $anim_start = $args['anim_start'];
        $replay_on_click = $args['replay_on_click'];

        $sa_svg_id = $args['order_class'];
        $svg_content = '';
        $config = [];
        if ($has_config && $animation_type != 'none') {
            $config = [
                "svg_id" => $sa_svg_id,
                "type" => ($animation_type != '' ? $animation_type : 'delayed'),
                "duration" => ($animation_duration != '' ? $animation_duration : '200'),
                "start" => ($anim_start != '' ? $anim_start : 'autostart'),
                "pathTimingFunction" => ($path_timing_function != '' ? $path_timing_function : 'linear'),
                "animTimingFunction" => ($anim_timing_function != '' ? $anim_timing_function : 'linear'),
                "replay_on_click" => ($replay_on_click != '' ? $replay_on_click : 'off'),
            ];
        }
        $svg_color = ($svg_color != '' && $svg_color != '#' ? $svg_color : '#000000');
        $svg_content = "";
        if($src == "")
        {
            $svg_content = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 1080 540" style="enable-background:new 0 0 1080 540;" xml:space="preserve">
                <style type="text/css">
                    .st0{fill-rule:evenodd;clip-rule:evenodd;fill:none;stroke:#DDDDDD;stroke-width:4;stroke-miterlimit:10;}
                    .st1{fill:none;stroke:#DDDDDD;stroke-width:4;stroke-miterlimit:10;}
                </style>
                <rect x="3.1" y="2.5" class="st0" width="1073.4" height="534.1"/>
                <circle class="st0" cx="370.6" cy="143.8" r="79.5"/>
                <path class="st0" d="M3.8,440.7l175-145.9c16.8-14,41.3-13.7,57.7,0.7l145.8,127.7c13.1,11.5,32.9,11.1,45.5-1.1l293.4-274.8
                    c17.2-16.6,44.5-16.6,61.7,0l293.7,283.6"/>
                <path class="st1" d="M3.1,506.4l182.4-152.2c11.9-9.6,28.9-9.3,40.4,0.6l144.9,124.8c20.4,17.6,50.9,16.9,70.4-1.7l291.8-276.6
                    c10.2-9.7,26.3-9.7,36.5-0.1l306.8,289.2"/>
                </svg>
            ';
        } else {
            $svg_content = static::dipi_get_url_content($src);
        }

        $validators = array(
            '<svg' => '<svg id="svg-' . $sa_svg_id . '"',
            '<ellipse' => '<ellipse fill="none" stroke-width="' . $svg_weight . '" stroke="' . $svg_color . '"',
            '<rect' => '<rect fill="none" stroke-width="' . $svg_weight . '" stroke="' . $svg_color . '"',
            '<circle' => '<circle fill="none" stroke-width="' . $svg_weight . '" stroke="' . $svg_color . '"',
            '<polygon' => '<polygon fill="none" stroke-width="' . $svg_weight . '" stroke="' . $svg_color . '"',
            '<polyline' => '<polyline fill="none" stroke-width="' . $svg_weight . '" stroke="' . $svg_color . '"',
            '<defs' => '<defs fill="none" stroke-width="' . $svg_weight . '" stroke="' . $svg_color . '"',
            '<linearGradient' => '<linearGradient fill="none" stroke-width="' . $svg_weight . '" stroke="' . $svg_color . '"',
            '<path' => '<path fill="none" stroke-width="' . $svg_weight . '" stroke="' . $svg_color . '"',
            'style="' => 'style="fill:none!important;',
        );
        foreach ($validators as $key => $value) {
            $svg_content = str_replace($key, $value, $svg_content);
        }
        
        $svg_content = static::add_prefix_svg_selector($svg_content, $sa_svg_id);
        $svg_animator = sprintf(
            '<div class="dipi-svg-animator-inner-wrapper" data-config="%1$s">
                %2$s
            </div>',
            esc_attr(htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8')),
            $svg_content
        );

        return $svg_animator;
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
        $thisProps['order_class'] = "dipi_svg_animator_" . $order_number;

        $svg_animator = static::render_svg_animator($thisProps);

        $render_html = sprintf(
            '<div class="dipi-svg-animator-container preloading" style="opacity: 0;">
                %1$s
            </div>',
            $svg_animator
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
				'classnamesFunction'  => [ SVGAnimator::class, 'module_classnames' ],
				'stylesComponent'     => [ SVGAnimator::class, 'module_styles' ],
				'scriptDataComponent' => [ SVGAnimator::class, 'module_script_data' ],
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
