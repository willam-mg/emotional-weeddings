<?php

namespace DIPI\Modules;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use DIPI\Modules\AdvancedTabs\AdvancedTabs;
use DIPI\Modules\AdvancedTabsItem\AdvancedTabsItem;
use DIPI\Modules\Balloon\Balloon;
use DIPI\Modules\BeforeAfterSlider\BeforeAfterSlider;
use DIPI\Modules\BlogSlider\BlogSlider;
use DIPI\Modules\Breadcrumbs\Breadcrumbs;
use DIPI\Modules\ButtonGrid\ButtonGrid;
use DIPI\Modules\ButtonGridChild\ButtonGridChild;
use DIPI\Modules\Carousel\Carousel;
use DIPI\Modules\CarouselItem\CarouselItem;
use DIPI\Modules\ContentSlider\ContentSlider;
use DIPI\Modules\ContentSliderChild\ContentSliderChild;
use DIPI\Modules\ContentToggle\ContentToggle;
use DIPI\Modules\Countdown\Countdown;
use DIPI\Modules\Counter\Counter;
use DIPI\Modules\Divider\Divider;
use DIPI\Modules\DualHeading\DualHeading;
use DIPI\Modules\ExpandingCTA\ExpandingCTA;
use DIPI\Modules\FancyText\FancyText;
use DIPI\Modules\FancyTextChild\FancyTextChild;
use DIPI\Modules\FAQ\FAQ;
use DIPI\Modules\FilterableGallery\FilterableGallery;
use DIPI\Modules\FilterableGrid\FilterableGrid;
use DIPI\Modules\FlipBox\FlipBox;
use DIPI\Modules\FloatingMultiImages\FloatingMultiImages;
use DIPI\Modules\FloatingMultiImagesChild\FloatingMultiImagesChild;
use DIPI\Modules\GravityFormsStyler\GravityFormsStyler;
use DIPI\Modules\HorizontalTimeline\HorizontalTimeline;
use DIPI\Modules\HorizontalTimelineItem\HorizontalTimelineItem;
use DIPI\Modules\HoverBox\HoverBox;
use DIPI\Modules\HoverGallery\HoverGallery;
use DIPI\Modules\HoverGalleryItem\HoverGalleryItem;
use DIPI\Modules\ImageAccordion\ImageAccordion;
use DIPI\Modules\ImageAccordionChild\ImageAccordionChild;
use DIPI\Modules\ImageHotspot\ImageHotspot;
use DIPI\Modules\ImageHotspotChild\ImageHotspotChild;
use DIPI\Modules\ImageMagnifier\ImageMagnifier;
use DIPI\Modules\ImageMask\ImageMask;
use DIPI\Modules\ImageRotator\ImageRotator;
use DIPI\Modules\ImageShowcase\ImageShowcase;
use DIPI\Modules\ImageShowcaseChild\ImageShowcaseChild;
use DIPI\Modules\InfoCircle\InfoCircle;
use DIPI\Modules\InfoCircleItem\InfoCircleItem;
use DIPI\Modules\LottieIcon\LottieIcon;
use DIPI\Modules\MasonryGallery\MasonryGallery;
use DIPI\Modules\Panorama\Panorama;
use DIPI\Modules\ParallaxImages\ParallaxImages;
use DIPI\Modules\ParallaxImagesItem\ParallaxImagesItem;
use DIPI\Modules\PriceList\PriceList;
use DIPI\Modules\PriceListItem\PriceListItem;
use DIPI\Modules\PricingTable\PricingTable;
use DIPI\Modules\PricingTableItem\PricingTableItem;
use DIPI\Modules\ReadingProgressBar\ReadingProgressBar;
use DIPI\Modules\Reveal\Reveal;
use DIPI\Modules\ScrollImage\ScrollImage;
use DIPI\Modules\StarRating\StarRating;
use DIPI\Modules\SVGAnimator\SVGAnimator;
use DIPI\Modules\TableOfContent\TableOfContent;
use DIPI\Modules\Testimonial\Testimonial;
use DIPI\Modules\TextHighlighter\TextHighlighter;
use DIPI\Modules\TileScroll\TileScroll;
use DIPI\Modules\TileScrollItem\TileScrollItem;
use DIPI\Modules\TiltImage\TiltImage;
use DIPI\Modules\Timeline\Timeline;
use DIPI\Modules\TimelineItem\TimelineItem;
use DIPI\Modules\TypingText\TypingText;
use DiviPixel\DIPI_Settings;

const DIVI5_MODULE_SETTING_MAP = [
    'md_accordion_image' => [ImageAccordion::class, ImageAccordionChild::class],
    'md_advanced_divider' => [Divider::class],
    'md_advanced_tabs' => [AdvancedTabs::class, AdvancedTabsItem::class],
    'md_balloon' => [Balloon::class],
    'md_before_after_slider' => [BeforeAfterSlider::class],
    'md_blog_slider' => [BlogSlider::class],
    'md_breadcrumbs' => [Breadcrumbs::class],
    'md_button_grid' => [ButtonGrid::class, ButtonGridChild::class],
    'md_carousel' => [Carousel::class, CarouselItem::class],
    'md_content_slider' => [ContentSlider::class, ContentSliderChild::class],
    'md_content_toggle' => [ContentToggle::class],
    'md_countdown' => [Countdown::class],
    'md_counter' => [Counter::class],
    'md_dual_heading' => [DualHeading::class],
    'md_expanding_cta' => [ExpandingCTA::class],
    'md_fancy_text' => [FancyText::class, FancyTextChild::class],
    'md_faq' => [FAQ::class],
    'md_filterable_gallery' => [FilterableGallery::class],
    'md_filterable_grid' => [FilterableGrid::class],
    'md_flip_box' => [FlipBox::class],
    'md_floating_multi_images' => [FloatingMultiImages::class, FloatingMultiImagesChild::class],
    'md_gravity_styler' => [GravityFormsStyler::class],
    'md_horizontal_timeline' => [HorizontalTimeline::class, HorizontalTimelineItem::class],
    'md_hover_box' => [HoverBox::class],
    'md_hover_gallery' => [HoverGallery::class, HoverGalleryItem::class],
    'md_image_hotspot' => [ImageHotspot::class, ImageHotspotChild::class],
    'md_image_magnifier' => [ImageMagnifier::class],
    'md_image_mask' => [ImageMask::class],
    'md_image_rotator' => [ImageRotator::class],
    'md_image_showcase' => [ImageShowcase::class, ImageShowcaseChild::class],
    'md_info_circle' => [InfoCircle::class, InfoCircleItem::class],
    'md_lottie_icon' => [LottieIcon::class],
    'md_masonry_gallery' => [MasonryGallery::class],
    'md_panorama' => [Panorama::class],
    'md_parallax_images' => [ParallaxImages::class, ParallaxImagesItem::class],
    'md_pricelist' => [PriceList::class, PriceListItem::class],
    'md_pricing_table' => [PricingTable::class, PricingTableItem::class],
    'md_reading_progress_bar' => [ReadingProgressBar::class],
    'md_reveal' => [Reveal::class],
    'md_scroll_image' => [ScrollImage::class],
    'md_star_rating' => [StarRating::class],
    'md_svg_animator' => [SVGAnimator::class],
    'md_table_of_contents' => [TableOfContent::class],
    'md_testimonial' => [Testimonial::class],
    'md_text_highlighter' => [TextHighlighter::class],
    'md_tile_scroll' => [TileScroll::class, TileScrollItem::class],
    'md_tilt_image' => [TiltImage::class],
    'md_timeline' => [Timeline::class, TimelineItem::class],
    'md_typing_text' => [TypingText::class],
    // 'md_gallery_slider' => [ImageSlider::class],
    // 'md_table_maker' => [TableMaker::class],
];

add_action(
    'divi_module_library_modules_dependency_tree',
    function ($dependency_tree) {
        foreach (DIVI5_MODULE_SETTING_MAP as $setting => $module_classes) {
            if (DIPI_Settings::get_option($setting)) {
                continue;
            }

            foreach ((array) $module_classes as $module_class) {
                $dependency_tree->add_dependency(new $module_class());
            }
        }
    }
);