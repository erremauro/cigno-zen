<?php

function cigno_zen_setup() {
    load_theme_textdomain('cigno-zen', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'cigno_zen_setup');

function cigno_zen_scripts() {
	wp_enqueue_style('cigno-zen-style', get_stylesheet_uri());
	wp_enqueue_script('cigno-zen-script', get_template_directory_uri() . '/assets/js/script.js', array(), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'cigno_zen_scripts');

function carica_google_fonts() {
    wp_enqueue_style('libre-baskerville', 'https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap', false);
}
add_action('wp_enqueue_scripts', 'carica_google_fonts');

// Disable JetPack's Related Posts Automatic Placement
function jetpackme_remove_rp() {
    if ( class_exists( 'Jetpack_RelatedPosts' ) ) {
        $jprp = Jetpack_RelatedPosts::init();
        $callback = array( $jprp, 'filter_add_target_to_dom' );
        remove_filter( 'the_content', $callback, 40 );
    }
}
add_filter( 'wp', 'jetpackme_remove_rp', 20 );

// Create JetPack's Related Posts Automatic Placement
function jetpackme_custom_related() {
	//  Check that JetPack Related Posts exists
	if (
			class_exists( 'Jetpack_RelatedPosts' )
			&& method_exists( 'Jetpack_RelatedPosts', 'init_raw' )
	) {
			//  Get the related posts
			$related = Jetpack_RelatedPosts::init_raw()
				->set_query_name( 'edent-related-shortcode' )
				->get_for_post_id(
					get_the_ID(),   //  ID of the post
					array( 'size' => 4 )//  How many related items to fetch
				);
			if ( $related ) {
				//  Set the container for the related posts
				$output = "<h2 id='related-posts'>Altri Articoli:</h2>";
				$output .=   "<ul class='related-posts'>";

				foreach ( $related as $result ) {
					$related_post_id = $result['id'];

					// Get the related post
					$related_post = get_post( $related_post_id );

					//  Get the attributes
					$related_post_title = $related_post->post_title;
					$related_post_link  = get_permalink( $related_post_id );

					//  Create the HTML for the related post
					$output .= '<li class="related-post">';
					$output .=    "<a href='{$related_post_link}'>";
					$output .=       "{$related_post_title}</a>";
					$output .= "</li>";
				}
				//  Finish the related posts container
				$output .="</ul>";
			}
		//  Display the related posts
		echo $output;
	}
}
add_shortcode( 'jprel', 'jetpackme_custom_related' );

/**
 * Display all suggested terms.
 *
 * @param string $html Original HTML output.
 * @param array $terms Array of suggested terms.
 * @param WP_Query $query WP_Query object.
 *
 * @return string
 */
add_filter(
	'ep_suggestion_html',
	function( $html, $terms, $query )  {
		$valid_terms = array_filter($terms, function($term) {
            return !empty(trim($term['text']));
        });

        if (!empty($valid_terms)) {
			$html  = '<div class="ep-suggestions">';
			$html .= '<p class="ep-suggestion-item">' . esc_html__( 'Forse intendevi', 'elasticpress' );
			foreach( $terms as $term ) {
				$html .= ' <a href="' . esc_url( get_search_link( $term['text'] ) ) . '">' . esc_html( $term['text'] ) . '</a>';
			}
			$html .= '?</p>';
			$html .= '</div>';
		}

		return $html;
	},
	10,
	3
);
