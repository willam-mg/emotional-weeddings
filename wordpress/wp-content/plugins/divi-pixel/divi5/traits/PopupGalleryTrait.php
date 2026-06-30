<?php 

namespace DIPI\Traits;
if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

trait PopupGalleryTrait {
    public static function popup_gallery_items($attachment_ids) {
        $items = [];
        foreach ($attachment_ids as $attachment_id) {
          $attachment = wp_get_attachment_image_src($attachment_id, "full");
          if(!$attachment){
              continue;
          }
          $image = $attachment[0];
          $image_title = get_the_title($attachment_id);
          $items[] = sprintf(
              '<div class="dipi-gallery-item" href="%1$s"%2$s%3$s></div>',
              $image,
              " data-title='$image_title'" ,
              " data-caption='" . htmlspecialchars(wp_get_attachment_caption($attachment_id)) . "'"
          );
        }
        return implode("", $items);
    }

    public static function popup_gallery($attachment_ids) {
        $items = self::popup_gallery_items($attachment_ids);
        return sprintf(
            '<div class="dipi-gallery">%1$s</div>',
            $items
        );
    }
}