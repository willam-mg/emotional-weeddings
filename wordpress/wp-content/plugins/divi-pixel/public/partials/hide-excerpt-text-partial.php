<?php
namespace DiviPixel;

if (DIPI_Settings::get_option('custom_archive_page')):
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$("body.archive #main-content article.et_pb_post, body.blog #main-content article.et_pb_post")
		.contents()
		.filter(function() {
			return this.nodeType === 3;
		})
		.remove();
});
</script>

<?php else: ?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$("body.archive #main-content article.et_pb_post .dipi-post-content, body.blog #main-content article.et_pb_post .dipi-post-content").remove();
});
</script>
<?php endif;?>