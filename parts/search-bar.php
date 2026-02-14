<form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="js-live-search-form">
    <label for="search-field">
        <span class="screen-reader-text"><?php echo esc_html_x('Search for:', 'label', 'cigno-zen'); ?></span>
    </label>
    <div class="search-input-shell">
        <input
            type="search"
            id="search-field"
            class="search-field js-live-search-input"
            placeholder="<?php echo esc_attr_x('Cerca...', 'placeholder', 'cigno-zen'); ?>"
            value="<?php echo get_search_query(); ?>"
            name="s"
            autocomplete="off"
            role="combobox"
            aria-autocomplete="list"
            aria-expanded="false"
            aria-controls="search-suggestions"
        >
        <button type="submit" class="search-submit">
            <?php get_template_part( 'parts/svg/search-icon'); ?>
            <span class="screen-reader-text"><?php echo esc_html_x('Cerca', 'submit button', 'cigno-zen'); ?></span>
        </button>
    </div>
    <div id="search-suggestions" class="search-suggestions" role="listbox" hidden></div>
</form>
