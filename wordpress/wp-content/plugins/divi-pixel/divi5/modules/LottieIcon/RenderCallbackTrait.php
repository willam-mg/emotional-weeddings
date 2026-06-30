<?php
namespace DIPI\Modules\LottieIcon;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use DIPI\Traits\BaseRenderTrait;

trait RenderCallbackTrait {
    use BaseRenderTrait;

    static function sanitize_content($content)
	{
		return preg_replace('/^<\/p>(.*)<p>/s', '$1', $content);
	}

	static function process_content($content)
	{
		$content = static::sanitize_content($content);
		$content = str_replace(["&#91;", "&#93;"], ["[", "]"], $content);
		$content = do_shortcode($content);
		$content = str_replace(
			["<p><div", "</div></p>", "</div> <!-- .et_pb_section --></p>"],
			["<div", "</div>", "</div>"],
			$content
		);
		return $content;
	}

    public static function render_callback( $attrs, $content, $block, $elements ) {
        $order_number = $block->parsed_block['orderIndex'];
        $thisProps = [];
        foreach ($attrs as $key => $value) {
            $thisProps[$key] = isset($attrs[$key]['innerContent']) ? static::getPropValue($attrs, $key) : '';
        }

        $path = $thisProps['json_file'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $use_json_code = $thisProps['use_json_code'];
        $json_code = $thisProps['json_code'];
        $loop = $thisProps['loop'];
        $autoplay = $thisProps['autoplay'];
        $anim_delay = $thisProps['anim_delay'];
        $anim_start = $thisProps['anim_start'];
        $anim_start_viewport = $thisProps['anim_start_viewport'];
        $direction = $thisProps['direction'];
        $speed = $thisProps['speed'];
        $play_on_hover = $thisProps['play_on_hover'];
        $stop_on_hover = $thisProps['stop_on_hover'];
        $start_frame = $thisProps['start_frame'];
        //Animate on scroll
        $animate_on_scroll = $thisProps['animate_on_scroll'];
        $visibility_start = $thisProps['visibility_start'];
        $visibility_end = $thisProps['visibility_end'];
        $frame_start = $thisProps['frame_start'];
        $frame_end = $thisProps['frame_end'];
        $lottie_placement = $thisProps['lottie_placement'];
        $lottie_placement_tablet = $attrs['lottie_placement']['innerContent']['tablet']['value'] ?? $lottie_placement;
        $lottie_placement_phone = $attrs['lottie_placement']['innerContent']['phone']['value'] ?? $lottie_placement;
        $lottie_title_level = $attrs['title']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? "h2";

        if (is_rtl() && 'left' === $lottie_placement) {
            $lottie_placement = 'right';
        }

        if (is_rtl() && 'left' === $lottie_placement_tablet) {
                $lottie_placement_tablet = 'right';
        }

        if (is_rtl() && 'left' === $lottie_placement_phone) {
                $lottie_placement_phone = 'right';
        }
        
        $lottie_title = '';
        if ('' !== $thisProps['lottie_title']) {
            $lottie_title = sprintf(
                '<%2$s class="dipi-lottie-title">%1$s</%2$s>',
                esc_attr($thisProps['lottie_title']),
                esc_attr($lottie_title_level)
            );
        }

        $lottie_content = '';
        if ('' !== $thisProps['lottie_content']) {
            $lottie_content = sprintf(
                '<div class="dipi-lottie-desc">%1$s</div>',
                static::process_content($thisProps['lottie_content'])
            );
        }

        $lottie_button = '';
        if ('on' === ($attrs['button']['advanced']['use_button']['desktop']['value'] ?? 'off')) {
            $lottie_button = $elements->render([
                'attrName' => 'button',
            ]);
        }

        $options = [];

        if($use_json_code === "off") {
            $options['path'] = esc_attr($path);
        } else {
            $json_data = json_encode(json_decode($json_code));
            $options['path'] = 'data:application/json,' . rawurlencode($json_data);
        }
        $options['ext'] = $use_json_code === 'on' ? 'json' : $extension;
        $options['loop'] = $loop === 'on' ? true : false;
        $options['autoplay'] = $autoplay === 'on' ? true : false;
        $options['anim_delay'] = $anim_delay;
        $options['anim_start'] = $anim_start;
        $options['anim_start_viewport'] = $anim_start_viewport;
        $options['start_frame'] = $start_frame;

        $options['speed'] = esc_attr($speed);
        $options['direction'] = esc_attr($direction);
        $options['play_on_hover'] = esc_attr($play_on_hover);
        $options['stop_on_hover'] = esc_attr($stop_on_hover);

        $options['animate_on_scroll'] = esc_attr($animate_on_scroll);
        $options['visibility_start'] = esc_attr($visibility_start);
        $options['visibility_end'] = esc_attr($visibility_end);
        $options['frame_start'] = esc_attr($frame_start);
        $options['frame_end'] = esc_attr($frame_end);

        $lottie_icon = '';
        if ($use_json_code === 'off' && $extension === "lottie") {
            $lottie_icon = sprintf('
                <dotlottie-wc
                    id="lottiePlayer"
                    class="dipi-lottie-icon"
                    src="%1$s"
                    data-options="%2$s"
                    %3$s
                    %4$s
                    speed="%5$s"
                    ></dotlottie-wc>
                ',
                $options['path'],
                esc_attr(wp_json_encode($options)),
                $animate_on_scroll !== 'on' && $autoplay === 'on' ? 'autoplay' : '',
                $animate_on_scroll !== 'on' && $loop === 'on' ? 'loop' : '',
                $speed #5
            );
        } else {
            if ($animate_on_scroll == 'on') {
                $lottie_icon = sprintf( 
                    '<lottie-player src="%2$s"  class="dipi-lottie-icon" data-options="%1$s"></lottie-player>
                    ', 
                    esc_attr(wp_json_encode($options)),
                    $options['path']
                );
        
            } else {
                $lottie_icon = sprintf( 
                    '<div class="dipi-lottie-icon" data-options="%1$s"></div>
                    ', 
                    esc_attr(wp_json_encode($options))
                );
            }
        }
        

        $render_html = '';
        $module_custom_classes = 'dipi-lottie-wrapper';
        $module_custom_classes .= sprintf(' dipi_lottie_placement_%1$s', esc_attr($lottie_placement));
        if (!empty($lottie_placement_tablet)) {
            $module_custom_classes .= " dipi_lottie_placement_{$lottie_placement_tablet}_tablet";
        }

        if (!empty($lottie_placement_phone)) {
            $module_custom_classes .= " dipi_lottie_placement_{$lottie_placement_phone}_phone";
        }

        if( $lottie_button != '' || $lottie_content != '' || $lottie_title != '' ) {

            $render_html = sprintf(
                '<div class="%5$s">
                    %1$s
                    <div class="dipi-lottie-content">
                        %2$s
                        %3$s
                        %4$s
                    </div>
                </div>',
                $lottie_icon,
                $lottie_title,
                $lottie_content,
                $lottie_button,
                $module_custom_classes #5
            );

        } else {
            $render_html = sprintf(
                '<div class="%2$s">
                    %1$s
                </div>',
                $lottie_icon,
                $module_custom_classes
            );
        }

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
				'classnamesFunction'  => [ LottieIcon::class, 'module_classnames' ],
				'stylesComponent'     => [ LottieIcon::class, 'module_styles' ],
				'scriptDataComponent' => [ LottieIcon::class, 'module_script_data' ],
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