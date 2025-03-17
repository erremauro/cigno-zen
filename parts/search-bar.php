<form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
    <label for="search-field">
        <span class="screen-reader-text"><?php echo esc_html_x('Search for:', 'label', 'cigno-zen'); ?></span>
    </label>
    <input type="search" id="search-field" class="search-field" placeholder="<?php echo esc_attr_x('Cerca...', 'placeholder', 'cigno-zen'); ?>" value="<?php echo get_search_query(); ?>" name="s">
    <button type="submit" class="search-submit">
        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/search-icon.svg'); ?>" width="32" height="auto">
            <span class="screen-reader-text"><?php echo esc_html_x('Cerca', 'submit button', 'cigno-zen'); ?></span>
        </button>
    </button>
</form>
