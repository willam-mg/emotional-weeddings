<?php
/**
 * Breadcrumbs::render_callback()
 *
 * @package DIPI\Modules\Breadcrumbs
 * @since ??
 */

namespace DIPI\Modules\Breadcrumbs;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Packages\Module\Module;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;

trait RenderCallbackTrait
{
    private static $props = [];
    public static $separator = '';
    public static $schema_item_list = '';
    public static $schema_item_list_element = '';
    public static $schema_item = '';
    public static $schema_item_name = '';
    public static $schema_item_position = '';
    public static $schema_item_position_content = 1;
    public static $rendering = false;
    public static function dipi_breadcrumbs($attrs)
    {
        return [
            "before_image" => $attrs["before_image"]['innerContent']['desktop']['value']['src'],
            "before_image_alt" => $attrs["before_image_alt"]['innerContent']['desktop']['value'],
            "before_label" => $attrs["before_label"]['innerContent']['desktop']['value'],
            "after_image" => $attrs["after_image"]['innerContent']['desktop']['value']['src'],
            "after_image_alt" => $attrs["after_image_alt"]['innerContent']['desktop']['value'],
            "after_label" => $attrs["after_label"]['innerContent']['desktop']['value'],
            "offset" => $attrs["offset"]['innerContent']['desktop']['value'],
            "direction" => $attrs["direction"]['innerContent']['desktop']['value'],
            "move_slider" => $attrs["move_slider"]['innerContent']['desktop']['value']
        ];
    }
    public static function home_icon($args)
    {
        $bc_home_icon = (isset($args['bc_home_icon'])) ? $args['bc_home_icon'] : '';
        if ($bc_home_icon == 'on') {
            echo sprintf('<span class="et-pb-icon dipi-home-icon"></span>');
        } else {
            echo '';
        }
    }
    public static function separator_func($args)
    {
        $bc_separator = (isset($args['bc_separator'])) ? $args['bc_separator'] : 'icon';
        $bc_separator_icon = (isset($args['bc_separator_icon'])) ? $args['bc_separator_icon'] : '';
        $bc_separator_sysmbol = (isset($args['bc_separator_sysmbol'])) ? $args['bc_separator_sysmbol'] : '';

        if ($bc_separator == 'icon') {
            echo sprintf(
                '<li class="dipi-breadcrumb-separator">
                    <span class="et-pb-icon dipi-separator-icon">%1$s</span>
                </li>',

                //Fix Me esc_attr(et_pb_process_font_icon($bc_separator_icon))
                esc_attr(Utils::process_font_icon($bc_separator_icon))
            );
        } else {
            echo sprintf(
                '<li class="dipi-breadcrumb-separator">
                    <span class="dipi-separator-symbol">%1$s</span>
                </li>',
                esc_attr($bc_separator_sysmbol)
            );
        }


    }
    public static function get_post_types_and_taxonomies()
    {
        $post_types = get_post_types(array(
            'public' => true,
        ), 'objects');
        $post_types_options = [];
        $taxonomies_options = [];
        foreach ($post_types as $post_type) {
            $post_types_options[$post_type->name] = ['label' => esc_html($post_type->label)];
            $taxonomies = get_object_taxonomies($post_type->name, 'objects');
            $taxonomies_options_by_post_types = [];
            foreach ($taxonomies as $taxonomy) {
                if ($taxonomy->name == 'post_format') {
                    continue;
                }

                $taxonomies_options_by_post_types[$taxonomy->name] = ['label' => $taxonomy->label];
            }
            $taxonomies_options[$post_type->name] = $taxonomies_options_by_post_types;
        }

        return json_encode([
            'post_types_options' => $post_types_options,
            'taxonomies_options' => $taxonomies_options
        ]);
    }
    public static function render_breadcrumbs($args = array(), $conditional_tags = array(), $current_page = array())
    {
        $hide_home = (isset($args['hide_home'])) ? $args['hide_home'] : 'off';
        $bc_custom_home = (isset($args['bc_custom_home'])) ? $args['bc_custom_home'] : 'off';
        $bc_home_text = (isset($args['bc_home_text'])) ? $args['bc_home_text'] : '';
        $bc_home_url = (isset($args['bc_home_url'])) ? $args['bc_home_url'] : '';
        $bc_separator = (isset($args['bc_separator'])) ? $args['bc_separator'] : 'icon';
        $bc_separator_icon = (isset($args['bc_separator_icon'])) ? $args['bc_separator_icon'] : '';
        $bc_separator_sysmbol = (isset($args['bc_separator_sysmbol'])) ? $args['bc_separator_sysmbol'] : '';
        $is_home = et_fb_conditional_tag('is_home', $conditional_tags);
        $is_front_page = et_fb_conditional_tag('is_front_page', $conditional_tags);
        $is_single = et_fb_conditional_tag('is_single', $conditional_tags);

        $post_id = isset($current_page['id']) ? (int) $current_page['id'] : 0;
        $page_object = get_post($post_id);
        $is_page = isset($page_object->post_type) && 'page' === $page_object->post_type;
        $_post = get_post($post_id);
        $parent_id = get_post($_post->post_parent);
        $bc_home_icon = (isset($args['bc_home_icon'])) ? $args['bc_home_icon'] : '';

        if ($bc_separator == 'icon') {
            $separator = sprintf(
                '<li class="dipi-breadcrumb-separator">
                    <span class="et-pb-icon dipi-separator-icon">%1$s</span>
                </li>',
                // Fix Me esc_attr(et_pb_process_font_icon($bc_separator_icon))
                esc_attr(Utils::process_font_icon($bc_separator_icon))
            );
        } else {
            $separator = sprintf(
                '<li class="dipi-breadcrumb-separator">
                    <span class="dipi-separator-symbol">%1$s</span>
                </li>',
                esc_attr($bc_separator_sysmbol)
            );
        }

        if (static::$rendering) {
            return '';
        }

        static::$rendering = true;

        $home_icon = $bc_home_icon == 'on' ? '<span class="et-pb-icon dipi-home-icon"></span>' : '';

        ob_start();

        ?>

        <?php if ($is_home || $is_front_page): ?>
            <?php if ($hide_home !== 'on'): ?>
                <li class="dipi-breadcrumb-item dipi-breadcrumb-home">
                    <?php if ($bc_custom_home == 'on'): ?>
                        <a href="<?php echo esc_url($bc_home_url); ?>">
                            <span>
                                <?php static::home_icon($args); ?>
                                <?php echo esc_html($bc_home_text); ?>
                            </span>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo esc_url(get_home_url()); ?>">
                            <span>
                                <?php static::home_icon($args); ?>
                                <?php echo esc_html(bloginfo('name')); ?>
                            </span>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endif; ?>
        <?php

        else:

            $position = 0;

            ?>
            <?php if ($hide_home !== 'on'): ?>
                <li class="dipi-breadcrumb-item dipi-breadcrumb-home">
                    <?php if ($bc_custom_home == 'on'): ?>
                        <a href="<?php echo esc_url($bc_home_url); ?>">
                            <span>
                                <?php static::home_icon($args); ?>
                                <?php echo esc_html($bc_home_text); ?>
                            </span>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo esc_url(get_home_url()); ?>">
                            <span>
                                <?php static::home_icon($args); ?>
                                <?php echo esc_html(bloginfo('name')); ?>
                            </span>
                        </a>
                    <?php endif; ?>
                </li>
                <?php static::separator_func($args); ?>
            <?php endif; ?>
            <?php if ($is_page && !$parent_id): ?>
                <li class="dipi-breadcrumb-item dipi-breadcrumb-current">
                    <span><?php echo esc_html(get_the_title($post_id)); ?></span>
                </li>

            <?php elseif ($is_page && $parent_id):

                $parents = get_post_ancestors($post_id);

                foreach (array_reverse($parents) as $pageID):

                    $position += 1;

                    // if($position > 2) echo $separator;

                    ?>

                    <li class="dipi-breadcrumb-item">
                        <span>
                            <a href="<?php esc_url(the_permalink($pageID)); ?>">
                                <?php echo esc_html(get_the_title($pageID)); ?>
                            </a>
                        </span>
                    </li>

                    <?php static::separator_func($args); ?>
                <?php endforeach; ?>

                <li class="dipi-breadcrumb-item dipi-breadcrumb-current">
                    <span>
                        <?php echo esc_html(get_the_title($post_id)); ?>
                    </span>
                </li>

            <?php else: ?>

                <li class="dipi-breadcrumb-item dipi-breadcrumb-current">
                    <span>
                        <?php echo esc_html(get_the_title($post_id)); ?>
                    </span>
                </li>

                <?php

            endif;

        endif;

        $breadcrumb = ob_get_contents();

        ob_end_clean();

        static::$rendering = false;

        $output = sprintf(
            '<ul> %1$s </ul>',
            $breadcrumb
        );

        return $output;

    }

    private static function schema_item_position_meta()
    {
        echo sprintf(
            '<meta itemprop="position" content="%1$s"/>',
            esc_html(static::$schema_item_position_content)
        );
        static::$schema_item_position_content++;
    }

    private static function calc_separtator()
    {

        $bc_separator = static::$props['bc_separator'];
        $bc_separator_icon = static::$props['bc_separator_icon'];
        $bc_separator_sysmbol = static::$props['bc_separator_sysmbol'];
        $bc_separator_size = static::$props['bc_separator_size'];
        $bc_separator_color = static::$props['bc_separator_color'];
        $bc_separator_space = static::$props['bc_separator_space'];


        if ($bc_separator == 'icon') {
            static::$separator = sprintf(
                '<li class="dipi-breadcrumb-separator">
                <span class="et-pb-icon dipi-separator-icon">%1$s</span>
                </li>',
                esc_attr(Utils::process_font_icon($bc_separator_icon))
                // Fix Me esc_attr(et_pb_process_font_icon($bc_separator_icon))
            );
            // static::dipi_generate_font_icon_styles($render_slug, 'bc_separator_icon', "$order_class .et-pb-icon.dipi-separator-icon");
        } else {
            static::$separator = sprintf(
                '<li class="dipi-breadcrumb-separator">
                    <span class="dipi-separator-symbol">%1$s</span>
                </li>',
                esc_attr($bc_separator_sysmbol)
            );
        }
    }
    private static function get_post_primary_category($post_id, $term = 'category')
    {
        $return = array();

        if (class_exists('WPSEO_Primary_Term')) {
            // Show Primary category by Yoast if it is enabled & set
            $wpseo_primary_term = new WPSEO_Primary_Term($term, $post_id);
            $primary_term = get_term($wpseo_primary_term->get_primary_term());

            if (!is_wp_error($primary_term)) {
                $return['primary_category'] = $primary_term;
            }
        }

        if (empty($return['primary_category'])) {
            $categories_list = get_the_terms($post_id, $term);
            if (empty($return['primary_category']) && !empty($categories_list)) {
                $return['primary_category'] = $categories_list[0]; //get the first category
            } else {
                return null;
            }
        }

        return $return['primary_category'];
    }

    private static function breadcrumbs_node($label, $link = '', $render_sep = false)
    {

        ?>

        <li <?php echo esc_attr(static::$schema_item_list_element); ?>
            class="dipi-breadcrumb-item <?php echo !$render_sep ? 'dipi-breadcrumb-current' : ''; ?>">

            <?php if (!empty($link)): ?>
                <a <?php echo esc_attr(static::$schema_item); ?> href="<?php echo esc_url($link); ?>">
                <?php endif; ?>

                <span <?php echo esc_attr(static::$schema_item_name); ?>>
                    <?php echo wp_kses_post($label); ?>
                </span>

                <?php if (!empty($link)): ?>
                </a>
            <?php endif; ?>

            <?php static::schema_item_position_meta(); ?>
        </li>
        <?php
        if ($render_sep) {
            static::separator_func(static::$props);
        }
    }

    private static function breadcrumbs_term_node($term, $taxonomy, $is_archive = false)
    {
        if (isset($term->parent) && !empty($term->parent)) {
            $parent_term = get_term_by('id', $term->parent, $taxonomy);
            static::breadcrumbs_term_node($parent_term, $taxonomy);
        }
        if ($is_archive) {
            static::breadcrumbs_node($term->name, '', false);
        } else {
            static::breadcrumbs_node($term->name, get_term_link($term->slug, $taxonomy), true);
        }

    }
    private static function dipi_bc_items_align($bc_align)
    {
        $flex_align = 'flex-start';
        switch ($bc_align) {
            case 'dipi-bc-left':
                $flex_align = 'flex-start';
                break;
            case 'dipi-bc-center':
                $flex_align = 'center';
                break;
            case 'dipi-bc-right':
                $flex_align = 'flex-end';
                break;
            default:
                $flex_align = 'flex-start';
                break;
        }
        return $flex_align;
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
        static::$props = array_map(function ($attr) {
            return is_array($attr) && array_key_exists('innerContent', $attr) ? $attr['innerContent']['desktop']['value'] ?? '' : $attr;
        }, $attrs);
        $hide_home = static::$props['hide_home'];
        $bc_custom_home = static::$props['bc_custom_home'];

        $bc_home_text = static::$props['bc_home_text'];
        $bc_home_url = static::$props['bc_home_url'];
        $bc_home_size = static::$props['bc_home_size'];
        $bc_home_color = static::$props['bc_home_color'];
        $bc_home_icon = static::$props['bc_home_icon'];
        $bc_hover_home_color = isset(static::$props['bc_home_color__hover']) ? static::$props['bc_home_color__hover'] : $bc_home_color;

        $bc_separator = static::$props['bc_separator'];
        $bc_separator_icon = static::$props['bc_separator_icon'];
        $bc_separator_sysmbol = static::$props['bc_separator_sysmbol'];
        $bc_separator_size = static::$props['bc_separator_size'];
        $bc_separator_color = static::$props['bc_separator_color'];
        $bc_separator_space = static::$props['bc_separator_space'];

        $bc_item_bg_color = static::$props['bc_item_bg_color'];
        $bc_hover_item_bg_color = static::$props['bc_hover_item_bg_color'];
        $bc_active_item_color = static::$props['bc_active_item_color'];
        $bc_schema = static::$props['bc_schema'];
        $bc_post_type = static::$props['bc_post_type'];
        $bc_post_type_label = static::$props['bc_post_type_label'];
        $bc_post_type_root = static::$props['bc_post_type_root'];
        $bc_post_taxonomy = static::$props['bc_' . $bc_post_type . '_taxonomy']['desktop']['value'] ?? null;
        $bc_is_post_type_root = static::$props['bc_is_post_type_root'];
        $bc_items_alignment = static::$props['bc_items_alignment'];
        static::calc_separtator();

        global $post;

        $actual_post;
        $post_id;
        if ( \ET_Theme_Builder_Layout::is_theme_builder_layout() ) {
            $post_id = \ET_Post_Stack::get_main_post_id();
            $actual_post = \ET_Post_Stack::get_main_post();
        } else {
            $post_id = get_the_ID();
            $actual_post = $post;
        }


        $parent_id = $actual_post ? $actual_post->post_parent : '';

        if (static::$rendering) {
            return '';
        }

        static::$rendering = true;
        if ('on' == $bc_schema):
            static::$schema_item_list = 'itemscope itemtype=https://schema.org/BreadcrumbList';
            static::$schema_item_list_element = 'itemprop=itemListElement itemscope itemtype=https://schema.org/ListItem';
            static::$schema_item = 'itemprop=item';
            static::$schema_item_name = 'itemprop=name';
            static::$schema_item_position = '<meta itemprop="position" content="%1$s"/>';
        endif;

        ob_start();

        ?>

        <?php if (is_home() || is_front_page()): ?>
            <?php if ($hide_home !== 'on'): ?>
                <li <?php echo esc_attr(static::$schema_item_list_element); ?> class="dipi-breadcrumb-item dipi-breadcrumb-home">

                    <?php if ($bc_custom_home == 'on'): ?>

                        <a <?php echo esc_attr(static::$schema_item); ?> href="<?php echo esc_url($bc_home_url); ?>">
                            <span <?php echo esc_attr(static::$schema_item_name); ?>>
                                <?php static::home_icon(static::$props); ?>
                                <?php echo esc_html($bc_home_text); ?>
                            </span>
                        </a>

                    <?php else: ?>

                        <a href="<?php echo esc_url(get_home_url()); ?>">
                            <span <?php echo esc_attr(static::$schema_item_name); ?>>
                                <?php static::home_icon(static::$props); ?>
                                <?php echo esc_html(bloginfo('name')); ?>
                            </span>
                        </a>

                    <?php endif; ?>
                    <?php static::schema_item_position_meta(); ?>
                </li>
            <?php endif; ?>

        <?php else: ?>
            <?php $position = 0; ?>
            <?php if ($hide_home !== 'on'): ?>
                <li <?php echo esc_attr(static::$schema_item_list_element); ?> class="dipi-breadcrumb-item dipi-breadcrumb-home">

                    <?php if ($bc_custom_home == 'on'): ?>

                        <a <?php echo esc_attr(static::$schema_item); ?> href="<?php echo esc_url($bc_home_url); ?>">
                            <span <?php echo esc_attr(static::$schema_item_name); ?>>
                                <?php static::home_icon(static::$props); ?>
                                <?php echo esc_html($bc_home_text); ?>
                            </span>
                        </a>

                    <?php else: ?>
                        <a <?php echo esc_attr(static::$schema_item); ?> href="<?php echo esc_url(get_home_url()); ?>">
                            <span <?php echo esc_attr(static::$schema_item_name); ?>>
                                <?php static::home_icon(static::$props); ?>
                                <?php echo bloginfo('name'); ?>
                            </span>
                        </a>

                    <?php endif; ?>
                    <?php static::schema_item_position_meta(); ?>
                </li>

                <?php static::separator_func(static::$props); ?>
            <?php endif; ?>
            <?php

            if (is_single($post_id) && $actual_post->post_type == $bc_post_type):

                if (isset($bc_post_type_root) && !empty($bc_post_type_root) && $bc_is_post_type_root == 'on') {
                    $archive_link = $bc_post_type_root;
                } else {
                    $archive_link = get_post_type_archive_link($bc_post_type);
                }

                if ($archive_link) {
                    $post_type = get_post_type_object($bc_post_type);
                    $label = $post_type->labels->name;
                    if (!empty($bc_post_type_label)) {
                        $label = $bc_post_type_label;
                    }

                    static::breadcrumbs_node($label, $archive_link, true);
                }

                if (isset($bc_post_taxonomy) && !empty($bc_post_taxonomy)) {
                    $post_term = static::get_post_primary_category($post_id, $bc_post_taxonomy);
                    if (isset($post_term) && !empty($post_term)) {
                        static::breadcrumbs_term_node($post_term, $bc_post_taxonomy);
                    }
                }
            endif;

            if (is_archive()) {
                $queried_object = get_queried_object();
                if ($queried_object instanceof WP_Term) {
                    $taxonomy = $queried_object->taxonomy;
                    if ($taxonomy == $bc_post_taxonomy) {

                        if (isset($bc_post_type_root) && !empty($bc_post_type_root) && $bc_is_post_type_root == 'on') {
                            $archive_link = $bc_post_type_root;
                        } else {
                            $archive_link = get_post_type_archive_link($bc_post_type);
                        }

                        if ($archive_link) {
                            $post_type = get_post_type_object($bc_post_type);
                            $label = $post_type->label;
                            if (!empty($bc_post_type_label)) {
                                $label = $bc_post_type_label;
                            }

                            static::breadcrumbs_node($label, $archive_link, true);
                        }
                    }
                    static::breadcrumbs_term_node($queried_object, $taxonomy, true);
                } else {
                    $title = $queried_object->name ?? $queried_object->labels->name ?? '';
                    static::breadcrumbs_node($title, '', false);
                }
            }

            if (is_page($post_id) && !$parent_id): // page without parent
                static::breadcrumbs_node(get_the_title($post_id), '', false);
            elseif (is_page($post_id) && $parent_id): // page with parent
                $parents = get_post_ancestors($post_id);
                foreach (array_reverse($parents) as $pageID):
                    $position += 1;
                    static::breadcrumbs_node(get_the_title($pageID), get_page_link($pageID), true);
                endforeach;

                static::breadcrumbs_node(get_the_title($post_id), '', false);
            elseif (is_single($post_id)):
                static::breadcrumbs_node(get_the_title($post_id), '', false);
            endif;

            if (is_404()) {
                static::breadcrumbs_node(wp_title('', false), '', false);
            }
        endif;

        $breadcrumb = ob_get_contents();

        ob_end_clean();

        static::$rendering = false;

        $output = sprintf(
            '<div class="dipi-breadcrumbs %3$s">
                <ul %2$s>
                    %1$s
                </ul>
            </div>',
            $breadcrumb,
            static::$schema_item_list,
            $bc_items_alignment
        );

        $parent = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);
        $parent_attrs = $parent->attrs ?? [];

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
                'classnamesFunction' => [Breadcrumbs::class, 'module_classnames'],
                'stylesComponent' => [Breadcrumbs::class, 'module_styles'],
                'scriptDataComponent' => [Breadcrumbs::class, 'module_script_data'],
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
                ) . $output,
            ]
        );
    }
}
