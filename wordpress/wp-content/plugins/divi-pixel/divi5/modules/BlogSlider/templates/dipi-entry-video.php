<?php
if( $first_video = et_get_first_video() ) {
    $thumb          = '';
    $width          = (int) apply_filters( 'et_pb_blog_image_width', 1080 );
    $height         = ($args['use_thumbnail_height'] === 'on' && $args['thumbnail_height']) ? $args['thumbnail_height'] : 768 ;
    $height         = (int) apply_filters( 'et_pb_blog_image_height', $height );
    $classtext      = '';
    $titletext      = get_the_title();
    $alttext        = get_post_meta( get_post_thumbnail_id(), '_wp_attachment_image_alt', true );
    $thumbnail      = get_thumbnail( $width, $height, $classtext, $alttext, $titletext, false, 'Blogimage' );
	$thumb          = $thumbnail['thumb'];
    $video_overlay = has_post_thumbnail() ? sprintf(
        '<div class="et_pb_video_overlay" style="background-image: url(%1$s); background-size: cover;">
            <div class="et_pb_video_overlay_hover">
                <a href="#" class="et_pb_video_play"></a>
            </div>
        </div>',
        $thumb
    ) : '';
}
?>
<div class="dipi_video_container dipi-entry-featured-image-url">
<?php if ('off' === $args['card_clickable'] && 'on' === $args['image_clickable']): ?>
	<a    
        class="dipi-blog-post-overlay-link"
        href="<?php echo esc_url(get_permalink()); ?>"
        target="<?php echo ('on' === $args['url_new_window']) ? '_blank' : ''; ?>"></a>
	<?php endif;?>

    <div class="dipi-blog-post-overlay">
        <?php printf('%1$s', et_core_esc_wp($author));?>
    </div>
    <?php
        printf(
            '
                %1$s
                %2$s
            ',
            et_core_esc_previously( $video_overlay ),
            et_core_esc_previously( $first_video )
        ); 
    ?>
</div>