<?php

/**
 * Funzionalità di Jetpack
 */
function jetpackme_remove_rp() {
	if (class_exists('Jetpack_RelatedPosts')) {
		$jprp = Jetpack_RelatedPosts::init();
		$callback = array($jprp, 'filter_add_target_to_dom');
		remove_filter('the_content', $callback, 40);
	}
}
add_filter('wp', 'jetpackme_remove_rp', 20);

function jetpackme_custom_related() {
	if (class_exists('Jetpack_RelatedPosts') && method_exists('Jetpack_RelatedPosts', 'init_raw')) {
		$related = Jetpack_RelatedPosts::init_raw()
			->set_query_name('edent-related-shortcode')
			->get_for_post_id(get_the_ID(), array('size' => 4));

		if ($related) {
			$output = "<h2 id='related-posts'>Altri Articoli:</h2>";
			$output .= "<ul class='related-posts'>";

			foreach ($related as $result) {
				$related_post_id = $result['id'];
				$related_post = get_post($related_post_id);
				$related_post_title = $related_post->post_title;
				$related_post_link = get_permalink($related_post_id);

				$output .= '<li class="related-post">';
				$output .= "<a href='{$related_post_link}'>{$related_post_title}</a>";
				$output .= "</li>";
			}
			$output .= "</ul>";
		}
		echo $output;
	}
}
add_shortcode('jprel', 'jetpackme_custom_related');
