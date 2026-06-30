<?php
/**
 * HorizontalTimelineItem::render_callback()
 *
 * @package DIPI\Modules\HorizontalTimelineItem
 * @since ??
 */

namespace DIPI\Modules\HorizontalTimelineItem;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;

trait RenderCallbackTrait
{
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

        $image_value = $attrs['image']['innerContent']['desktop']['value'] ?? '';
        $image = is_array($image_value) ? ($image_value['src'] ?? '') : ($image_value ?? '');
        $image_alt = is_array($image_value) ? ($image_value['alt'] ?? '') : '';
        $image_alt = $image_alt ? $image_alt : static::dipi_get_image_alt_by_url($image);

        $use_icon = $attrs['use_icon']['innerContent']['desktop']['value'] ?? 'off';
        $font_icon = $attrs['font_icon']['innerContent']['desktop']['value'] ?? '';
        $use_circle = $attrs['use_circle']['innerContent']['desktop']['value'] ?? 'off';
        $use_circle_border = $attrs['use_circle_border']['innerContent']['desktop']['value'] ?? 'off';
        $title = $attrs['title']['innerContent']['desktop']['value'] ?? '';
        $desc_text = $attrs['desc_text']['innerContent']['desktop']['value'] ?? '';
        $show_button = $attrs['show_button']['innerContent']['desktop']['value'] ?? 'off';
        $use_timeline_icon = $attrs['use_timeline_icon']['innerContent']['desktop']['value'] ?? 'on';
        $timeline_icon = $attrs['timeline_icon']['innerContent']['desktop']['value'] ?? '';
        $icon_placement = $attrs['icon_placement']['innerContent']['desktop']['value'] ?? 'top';
        $custom_card_arrow = $attrs['custom_card_arrow']['innerContent']['desktop']['value'] ?? 'off';

        $header_level = $attrs['title_font']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? 'h4';

        // Render timeline icon
        $timeline_icon_render = '';
        if ('on' === $use_timeline_icon && !empty($timeline_icon)) {
            $ribbon_use_circle = $attrs['ribbon_use_circle']['innerContent']['desktop']['value'] ?? 'on';
            $ribbon_use_circle_border = $attrs['ribbon_use_circle_border']['innerContent']['desktop']['value'] ?? 'off';
            
            $timeline_icon_classes = ['ribbon-ico'];
            if ('on' === $ribbon_use_circle) {
                $timeline_icon_classes[] = 'ribbon-ico-circle';
            }
            if ('on' === $ribbon_use_circle && 'on' === $ribbon_use_circle_border) {
                $timeline_icon_classes[] = 'ribbon-ico-circle-border';
            }
            
            $timeline_icon_processed = Utils::process_font_icon($timeline_icon);
            $timeline_icon_render = sprintf(
                '<div class="et_pb_image_wrap ribbon-ico-wrap"><span class="%1$s" data-icon="%2$s"></span></div>',
                implode(' ', $timeline_icon_classes),
                esc_attr($timeline_icon_processed)
            );
        }

        // Render ribbon (always output wrapper to match VB structure; use elements->render for dynamic content)
        $ribbon_render = sprintf(
            '<div class="dipi_htl_ribbon-wrapper"><div class="dipi_timeline_ribbon"><span class="dipi_timeline_ribbon_text">%1$s</span></div></div>',
            $elements->render(['attrName' => 'ribbon'])
        );

        // Render image or icon
        $image_render = '';
        if ('off' === $use_icon) {
            if (!empty($image)) {
                $image_render = sprintf(
                    '<div class="dipi_htl_item_image"><span class="et_pb_image_wrap dipi_htl_item_image_popup" href="%2$s"><img src="%1$s" alt="%3$s" /></span></div>',
                    esc_url($image),
                    esc_url($image),
                    esc_attr($image_alt)
                );
            }
        } else {
            if (!empty($font_icon)) {
                $icon_classes = ['et-pb-icon', 'dipi_timeline_font_icon'];
                if ('on' === $use_circle) {
                    $icon_classes[] = 'et-pb-icon-circle';
                }
                if ('on' === $use_circle && 'on' === $use_circle_border) {
                    $icon_classes[] = 'et-pb-icon-circle-border';
                }
                
                $icon_processed = Utils::process_font_icon($font_icon);
                $image_render = sprintf(
                    '<div class="dipi_htl_item_image"><span class="et_pb_image_wrap"><span class="%1$s">%2$s</span></span></div>',
                    implode(' ', $icon_classes),
                    esc_attr($icon_processed)
                );
            }
        }

        // Render title
        $title_render = '';
        if (!empty($title)) {
            $title_render = sprintf(
                '<%1$s class="dipi_htl_item_header">%2$s</%1$s>',
                esc_attr($header_level),
                esc_html($title)
            );
        }

        // Render description
        $desc_render = '';
        if (!empty($desc_text)) {
            $desc_render = sprintf(
                '<div class="dipi_htl_item_description">%1$s</div>',
                static::process_content($desc_text)
            );
        }

        // Render button
        $button_render = '';
        if ('on' === $show_button) {
            $button_render = $elements->render([
                'attrName' => 'button',
            ]);
            $button_render = sprintf('<div class="et_pb_button_wrapper">%1$s</div>', $button_render);
        }

        // Build module custom classes
        $module_custom_classes = 'dipi_htl_item_custom_classes';
        $module_custom_classes .= sprintf(' dipi_htl_item_position_%1$s', esc_attr($icon_placement));
        
        $icon_placement_tablet = $attrs['icon_placement']['innerContent']['tablet']['value'] ?? $icon_placement;
        $icon_placement_phone = $attrs['icon_placement']['innerContent']['phone']['value'] ?? $icon_placement_tablet;
        
        if (!empty($icon_placement_tablet)) {
            $module_custom_classes .= sprintf(' dipi_htl_item_position_%1$s_tablet', esc_attr($icon_placement_tablet));
        }
        if (!empty($icon_placement_phone)) {
            $module_custom_classes .= sprintf(' dipi_htl_item_position_%1$s_phone', esc_attr($icon_placement_phone));
        }

        if ($custom_card_arrow === 'on') {
            $module_custom_classes .= ' dipi_htl_item_custom-card-arrow';
        }

        // Build HTML structure - must match Divi 4 structure exactly
        $video_background = '';
        $parallax_image_background = '';
        $data_background_layout = '';
        
        $render_html = sprintf(
            '<div class="%12$s">
                <div class="dipi_htl_item_container">
                    %9$s
                    %11$s
                    <div class="dipi_htl_item_card-wrap">
                        <div class="dipi_htl_item_card">
                            %7$s
                            <span class="et_pb_background_pattern"></span>
                            <span class="et_pb_background_mask"></span>
                            %6$s
                            %2$s
                            <div class="dipi_htl_item_content">
                                <div class="dipi_htl_item_content_text">
                                    %3$s
                                    %1$s
                                </div>
                                %10$s
                            </div>
                        </div>
                    </div>
                </div>
            </div>',
            $desc_render,              // %1$s - description
            $image_render,             // %2$s - image
            $title_render,             // %3$s - title
            '',                        // %4$s
            '',                        // %5$s
            $video_background,         // %6$s
            $parallax_image_background, // %7$s
            $data_background_layout,   // %8$s
            $ribbon_render,            // %9$s
            $button_render,            // %10$s
            $timeline_icon_render,     // %11$s
            $module_custom_classes     // %12$s
        );

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
                'classnamesFunction' => [HorizontalTimelineItem::class, 'module_classnames'],
                'stylesComponent' => [HorizontalTimelineItem::class, 'module_styles'],
                'scriptDataComponent' => [HorizontalTimelineItem::class, 'module_script_data'],
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

