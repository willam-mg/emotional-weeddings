<?php
/**
 * CarouselItem::render_callback()
 *
 * @package DIPI\Modules\CarouselItem
 * @since ??
 */

namespace DIPI\Modules\CarouselItem;

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
use DIPI\Utils\LayoutController;

trait RenderCallbackTrait
{
    use BaseRenderTrait;

    private static $props = [];

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

    static function dipi_get_image_alt_by_url($image_url)
    {
        $attachment_id = attachment_url_to_postid($image_url);
        if ($attachment_id) {
            $alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
            return $alt_text;
        }
        return '';
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
        $parent = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);
        $parent_attrs = $parent->attrs ?? [];

        $order_number = $block->parsed_block['orderIndex'];

        $thisProps = [];
        foreach ($attrs as $key => $value) {
            $thisProps[$key] = isset($attrs[$key]['innerContent']) ? static::getPropValue($attrs, $key) : '';
        }

        $img_src = is_array($thisProps['img_src']) ? ($thisProps['img_src']['src'] ?? '') : ($thisProps['img_src'] ?? '');
        $img_alt = is_array($thisProps['img_src']) ? ($thisProps['img_src']['alt'] ?? '') : '';
        $img_alt = $img_alt ? $img_alt : static::dipi_get_image_alt_by_url($img_src);

        $carousel_icon = $thisProps['carousel_icon'];
        $use_icon = $thisProps['use_icon'];
        $use_icon_circle = $thisProps['use_icon_circle'];
        $use_icon_circle_border = $thisProps['use_icon_circle_border'];
        $use_icon_font_size = $thisProps['use_icon_font_size'];
        $title = $thisProps['title'];
        $desc_text = $thisProps['desc_text'];
        $show_button = $thisProps['show_button'];
        $img_animation = $thisProps['image_animation'];

        $show_light_box = static::getPropValue($parent_attrs, 'show_light_box') ?? 'on';
        $title_in_lightbox = static::getPropValue($parent_attrs, 'title_in_lightbox') ?? 'off';
        $desc_in_lightbox = static::getPropValue($parent_attrs, 'desc_in_lightbox') ?? 'off';

        $button_render = '';
        if ('on' === $show_button) {
            $button_render = $elements->render([
                'attrName' => 'carousel_button',
            ]);
            $button_render = sprintf('<div class="dipi-carousel-button-wrapper">%1$s</div>', $button_render);
        }

        if ('off' === $use_icon) {
            $image_animation = (!empty($img_animation) && $img_animation !== 'none') ? 'dipi-' . $img_animation : '';
            $image_classes = [];

            if (!empty($img_src)) {
                $image = sprintf(
                    '<img src="%1$s" alt="%2$s" class="%3$s" />',
                    $img_src,
                    $img_alt,
                    ($image_classes ? implode(' ', $image_classes) : '') . ' dipi-c-img'
                );
                $img_src_hover = $thisProps['img_src__hover'] ?? '';
                $image_hover = (isset($thisProps['img_src__hover_enabled']) && $thisProps['img_src__hover_enabled'] === 'on|hover') ? sprintf(
                    '<img src="%1$s" alt="%2$s" class="%3$s" />',
                    $img_src_hover,
                    $img_alt,
                    ($image_classes ? implode(' ', $image_classes) : '') . ' dipi-c-hover-img'
                ) : '';
                $image_extra_classes = (isset($thisProps['img_src__hover_enabled']) && $thisProps['img_src__hover_enabled'] === 'on|hover') ? ' dipi-c-has-hover' : '';
                $img_href = "";
                if ($show_light_box != "off") {
                    $img_href = sprintf(
                        'href="%1$s"',
                        esc_attr($img_src)
                    );
                }
                $image_render = sprintf(
                    '<span class="dipi-carousel-image %3$s %5$s" %1$s %6$s %7$s>
                        %2$s
                        %4$s
                    </span>',
                    $img_href,
                    $image,
                    $image_animation,
                    $image_hover,
                    $image_extra_classes, #5
                    'on' === $title_in_lightbox ? " data-title='$title'" : '',
                    'on' === $desc_in_lightbox ? " data-caption='" . $desc_text . "'" : ''

                );
            } else {
                $image_render = '';
            }
        } else {
            $carousel_icon_classes[] = 'et-pb-icon dipi-carousel-icon';

            if ('on' === $use_icon_circle) {
                $carousel_icon_classes[] = 'dipi-carousel-icon-circle';
            }

            if ('on' === $use_icon_circle && 'on' === $use_icon_circle_border) {
                $carousel_icon_classes[] = 'dipi-carousel-icon-circle-border';
            }

            $carousel_icon_circle_class = "on" === $thisProps["use_icon_circle"] ? "dipi-carousel-icon-circle" : "";
            $carousel_icon_circle_border_class =
                "on" === $thisProps["use_icon_circle"] && "on" === $thisProps["use_icon_circle_border"]
                ? "dipi-carousel-icon-circle-border"
                : "";
            $carousel_icon =
                "on" === $thisProps["use_icon"] && $thisProps["carousel_icon"]
                ? Utils::process_font_icon($carousel_icon)
                : "1";

            $image_render = sprintf(
                '<span class="%1$s">%2$s</span>',
                implode(' ', $carousel_icon_classes),
                esc_attr($carousel_icon)
            );
        }

        $title_level = $attrs['title_font']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? 'h2';
        $image_render = $image_render ? sprintf('<div class="dipi-image-wrap">%1$s</div>', $image_render) : '';
        $title = !empty($title) ? sprintf('<%2$s class="dipi-carousel-item-title">%1$s</%2$s>', $title, esc_attr($title_level)) : '';

        $desc_text = !empty($desc_text) ? sprintf(
            '<div class="dipi-carousel-item-desc">%1$s</div>',
            static::process_content($desc_text)
        ) : '';

        $link_option_url = $attrs['module']['advanced']['link']['desktop']['value']['url'] ?? "";
        $link_option_url_new_window = $attrs['module']['advanced']['link']['desktop']['value']['target'] ?? "off";
        $link_taget = ($link_option_url_new_window === 'on') ? 'target="blank"' : '';
        $link_start = (!empty($link_option_url)) ? sprintf('<a href="%1$s" %2$s>', esc_url($link_option_url), $link_taget) : '';
        $link_end = (!empty($link_option_url)) ? sprintf('</a>') : '';
        $content_start_wrapper = '';
        $content_end_wrapper = '';
        if (strlen(trim($title)) || strlen(trim($desc_text)) || strlen(trim($button_render))) {
            $content_start_wrapper = '<div class="dipi-carousel-item-content">';
            $content_end_wrapper = '</div>';
        }
        $default_output = sprintf('
            <div class="dipi-carousel-child-wrapper">
                %5$s
                %1$s
                %6$s
                %7$s
                    %5$s
                    %2$s
                    %3$s
                    %6$s
                    %4$s
                %8$s
            </div>',
            $image_render,
            $title,
            $desc_text,
            $button_render,
            $link_start, #5
            $link_end,
            $content_start_wrapper,
            $content_end_wrapper
        );

        if ($thisProps['type'] == 'divi_library') {
            $libraryId = $thisProps['divi_library_id'];
            $layout_content = !empty($libraryId) ? LayoutController::render_divi_layout($libraryId, false) : '';

            $render_html = sprintf('
                <div class="%2$s">
                    %1$s
                </div>
                ',
                $layout_content,
                'dipi-carousel-child-wrapper'
            );
        } else {
            $render_html = $default_output;
        }

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
                'classnamesFunction' => [CarouselItem::class, 'module_classnames'],
                'stylesComponent' => [CarouselItem::class, 'module_styles'],
                'scriptDataComponent' => [CarouselItem::class, 'module_script_data'],
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
