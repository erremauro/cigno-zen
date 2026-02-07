# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Fix
- Add support for 'target' property for the shortcode [maestro]

## [1.11.0] 2026-01-07
### Add
- Add the shortcode '[maestro]' to create a link to a master
- Add the shortcode '[autore]' to create a link to an author
### Change
- Move 'author' link to '/autore/'
## Fix
- Missing birth or death date now shows '?' label
- Fix a bug that prevented to show the predecessor if a primary teacher was specified
### Polish
- Add Logout icon

## [1.10.1] 2026-01-30
### Fix
- Move the 'cr=disabled' query param at the end of the URL in the LS shortcut.

## [1.10.0] 2026-01-30
### Add
- Add the [LS] shortcode to reference the Lotus Sutra in the Shobogenzo
### Change
- Add 'has-no-indent' css style

## [1.9.1] 2026-01-28
### Change
- Add 'hide_from_list' option for Articles to hide an article from the article list and in the home
### Fix
- Fix footnotes popup pagination

## [1.9.0] 2026-01-26
### Changed
- Remove "utilità" from the footer
- Update "remember me" authentication session to 14 days with refreshing sessions
- Update site-logo to multi-colored
- Update "Login" button color scheme
- Add `dictionary` in the `page-utilita.php`
### Fixed
- Fix Login Redirect to 
- Fix search-icon width
- Fix an issue with bottom space in the sidebar

## [1.8.1] 2025-12-31
### Added
- Dictionary Page
### Changed
- Dictionary's lemmas now support `show_all=1` URL param to show the whole
definition
 
## [1.8.0] 2025-12-31
### Changed
- Replace the header with a brand-new top navigation bar!

## [1.7.4] 2025-12-29
### Changed
- Footnote's text is now paginated.
- Update styles adding poem's verse identation
- Update `patterns/template-featured-posts.php` to show up to three featured posts.
- Support `featured=1` on `/articoli/` to list only posts with `is_featured`, ordered by `featured_order`.

## [1.7.3]
### Added
- Add the page `genealogia-dei-maestri`
- Add the page `studio-dei-sutra`
- Add the page `utilita`
### Changed
- Updates `footer.php` adding links to Instagram and the page "Utilità".
- Updates some master's information label related to predecessors and heirs.
### Fixed
- Fix date format
- Fix honorific name margin

## [1.7.2] 2025-10-14
### Changed
- If Master's floruit dates are provided in place of birth/death dates, show
those dates and years of activity.
### Fixed
- Master's successor listing has been restored.

## [1.7.1] 2025-10-13
### Fixed
- Fix auto-save value for master properties like `uuid ` and `name_latin`

## [1.7.0] 2025-10-13
### Changed
- Update `patterns/template-single-maestro.php` to reflect the new ACF fields.
- Add `inc/masters.php` to add custom columns to the Master post-type list.
- Theme style is now managed by the time of the day and toggle override last
  only for the day.
- Update kanji style serif font.
- Update styles for `post-tag-list` and `related-articles`
- Update styles for tag-pills

## [1.6.2] 2025-09-13
### Changed
- Custom message created for Masters not found in `404.php`

## [1.6.1] 2025-09-13
### Fixed
- Fix tag-cloud pill box-shadow for light theme

## [1.6.0] 2025-09-13
### Added
- Add a Tag Cloud to the home page.
### Changed
- Quotes are now showed event to non logged-in users
- Master's Information style has been updated
- Update poem/verse style
- H3 title top and bottom margin increased
- Change `figure` (image) style  
### Fixed
- Strip shortcodes from search results and fix paragraph formatting.
- Footnotes' popup content is now scrollable if the content is too long. 

## [1.5.0] 2025-09-10
### Added
- Add [references] short code to show linked posts
### Fixed
- Fix list style in tag-definitions.
- Fix php tag opening in `parts/cta-title-link`
- Fix `cta-title-link` label `font-size`

## [1.4.0] 2025-09-08
### Added
- Add singing bowl at the bottom of the page
- Add a filter that strips shortcodes from search risults
- Add `[footnotes]`, `[fn]` and `[fndef]` short-code for writing consistent footnotes.
- Add `[collapsable]` short-code.
- Add JSON-LD information for Masters
### Changed
- cta link now supports a description that has been implemented.
- Homepage now show 1 featured article and three latest articles. If the screen is too narrow (i.e. mobile) the article are shown as a browsable carousel.
- Tags now prefer the alternate name when rendered (via the `show_as` ACF custom field) and related tags now create a JSON-LD schema for comma separated tag synonyms.
- Homepage reading list is now hidden for everyone if there a no articles in the queue.
- Update the cta-link in homepage to use an SVG chevron
- The footnotes plugin in `script.js` has been updated to support the `[footnotes]` short-code
- Master's school property has been converted to a taxonomy
### Fixed
- Fix highlight color for searches
- Fix form checkbox's checkmark color for light theme.
### Polish
- sun/moon icons have been moved to `parts/svg`
- The "Dhamma Gift" message has been moved to `parts/dhamma-gift.php`
- `page-articles.php` moved to `__page-articles.php` as deprecated. (wasn't used)

## [1.3.0] 2025-09-02
### Added
- Add single-post template for `maestro` post-type
- Add `browser-sync` with command `npm run dev` to support live-reload
### Changed
- `templates/template-single-post-footer.php` moved to `parts/single-post-footer.php`

## [1.2.2] 2025-08-30
### Fixed
- Fix missing cursor pointer on site log and sun/moon

## [1.2.1] 2025-08-30
### Changed
- All colors in the theme color scheme converted to rgba
- Add sun/moon animation
### Fixed
- Fix missing post-tags light theme color

## [1.2.0] 2025-08-29
### Added
- Add support for dark/light theme
### Changed
- Site logo and search icon have been moved to `parts/svg` as php template files

## [1.1.0] 2025-08-28
### Added
- Add styles for poems
### Changed
- Update the contact form success message appearance

## [1.0.0] 2025-08-27
### Added
- Add `pages/privacy-policy.php` template
- Add Privacy Policy link to footer
- Add `parts/home-welcome` to manage homepage's welcome messages based on user logged-in states. When the user is logged in we show a quotation of the day using the `[zen_quotes]` (from the cz-quotes plugin) shortcode.
- Add Login/Logout/Register buttons in header's menu
### Changed
- Style and scripts are now minified
- Updates the ebook download link look for taxonomy volumes.
- Increase `padding-bottom` in footer for singular posts.
- Registration Page Title has been updated to reflect it's naming to "Registrati"

## [0.9.0] 2025-08-21
### Added
- Add `404.php` page
- Add `inc/authentication.php` to manage user registration email verification, hide the WordPress top bar and disallow accessing the /dashboard to non admin users.
### Changed
- Update Home Page to support the CZ Continue Reading Plugin
- Now users can register and are immediately logged in
### Fixed
- Fix successfull login redirect
- Fix `.success-message` appearance
- Fix a CSS issue that prevented the correct styling of #footnotes-toggle 

## [0.8.0] 2025-08-18
### Changed
- Update Note's title style
- Update related articles appearance.
- Update `/assets/js/scripts.js` to implement pop-up footnotes.

## [0.7.1] 2025-08-17
### Changed
- Update tag readings appearance to collapsable pills.
- Add a `.no-article-found` style to center the message.
- Update the `parts/tag-header.php` definition appearance
- Update `template/tempalte-query-loop` to show "Articoli Correlati" as a title for "tags" query loops.
### Fixed
- Fix author's bio menu toggle appearance
- Fix `.tag-title` appearance
- Fix `.post-tags` style and link clickability

## [0.7.0] 2025-08-16
### Added
- Add a `tag.php` template to show tag definitions and related articles.
- Add custom functions (`inc/tags.php`) to render JSON-LD data for tags.
- Add a `template_redirect` action to open random articles from the homepage.
- Add a tags section to `template-single-post.php`
- Add `inc/shortcodes.php` to `functions.php`
- Add the `[sentence]` shortcode.
- Add `[separator]` shortcode
### Changed
- Refactor the `more-link-button` as a function in `inc/more-link.php` and updates `assets/scripts.js` accordingly.
- Improve the related articles appearance.
- Change category "poesie" to "poemi" in `patterns/template-home-categories.php`
- Update the author's biography "read more" label to "LEGGI TUTTO"
### Fixed
- Fix an issue with the rendering of post-pagination in the `custom_post_pagination` function in `inc/posts.php`
- Fix the author's biography read more chevron button animation
### Removed
- `parts/separator.php` has been replaced by the `[separator]` shortcode.

## [0.6.0] 2025-08-13
### Added
- Add an `author.php` page to show the author's biography.
### Changed
- Add `parts/cta-title-link.php` for the home page section titles.
- Swapped default `p.more-text` tag for articles to `cta-title-link` template-part.
- Restyle home-page's author list
- Add a Welcome message to `page-home.php`
- `page-home.php` has been refactored to `patterns/template-home-*`
- `parts/header.php` now has the option to hide the menu
- `page-login.php` and `page-registrazione.php` has been updated to use the new
option in `parts-header.php` to make the pages more consistent.
### Fixed
- Fix header menu button clickability and styles.
- Site Logo fill color has been set to transparent.

## [0.5.0] 2025-08-06
### Added
- Add Landing Page
- Add User Account's registration, login and logout pages.
- Add Volume's download link to `patterns/template-taxonomy-volumes.php`
### Changed
- `functions.php` has been refactored by separating grouped functionalities
in separate files placed inside the `./inc/` directory.
- Page title is more SEO friendly
- Removed autofocus on search field when the menu is opened in `script.js`

## [0.4.0] 2025-07-13
### Added
- Add `question` style for interview-like post
- Add site's favicon(s)
- Add Category List page
- Add a "MENU" label
### Changed
- All pages were moved to `./pages/`
- `page.php` is now a template that get templates parts in `.pages/` by
the slug's name
- A visual separator was added to `page-volumi.php` for separating titles
- Chevron animation has been removed
- Add a timestamp version to the styles and scripts URI to force the reload on changes
### Fixed
- Fix typo in `page-volumi.php`

## [0.3.0] 2025-07-10
### Added
- Adds Volumes and Author's List pages
- Updates the Header adding a Context Menu to browser content by Volumes and Authors
- Displays the serie's Author
### Changed
- Taxonomy "series" has been renamed "volumes"
- Hides the pagination box in `patterns/template-query-loop.php` when no pages are available
- `more-link` bottom margin changed for better post spacing

## [0.2.1] - 2025-07-09
### Fixed
- `theme.json` has been removed

## [0.2.0] - 2025-07-09
### Added
- Post subtitle

### Changed
- Add "next" and "prev" pagination control to paginated posts.
- Search now shows the paragraph where the searched term (highlighted) was found in the text.

### Fixed
- Bold font weight


## [0.1.0] - 2025-03-17
- Defines the basic structure of the site with `index`, `single`, `page`, `taxonomy-series` and `search`.
- Defines `parts` folder for storing template parts (i.e. header and footer) and `patterns` for storing template patterns like loops
- Define the `styles.css` for the website
- Add sites assets like website logo and javascript for supporting the search field drop down action
- Add the `screenshot.png` theme preview
- Add development support files like `.editorconfig`

## [0.0.1] - 2025-03-07

### Added
- Initial `index.php`, `style.css`, and `functions.php`
- This Changelog

[Unreleased]: https://github.com/erremauro/cigno-zen/compare/v1.11.0...HEAD
[1.11.0]: https://github.com/erremauro/cigno-zen/releases/tag/v1.11.0
[1.10.1]: https://github.com/erremauro/cigno-zen/releases/tag/v1.10.1
[1.10.0]: https://github.com/erremauro/cigno-zen/releases/tag/v1.10.0
[1.9.1]: https://github.com/erremauro/cigno-zen/releases/tag/v1.9.1
[1.9.0]: https://github.com/erremauro/cigno-zen/releases/tag/v1.9.0
[1.8.1]: https://github.com/erremauro/cigno-zen/releases/tag/v1.8.1
[1.8.0]: https://github.com/erremauro/cigno-zen/releases/tag/v1.8.0
[1.7.4]: https://github.com/erremauro/cigno-zen/releases/tag/v1.7.4
[1.7.3]: https://github.com/erremauro/cigno-zen/releases/tag/v1.7.3
[1.7.2]: https://github.com/erremauro/cigno-zen/releases/tag/v1.7.2
[1.7.1]: https://github.com/erremauro/cigno-zen/releases/tag/v1.7.1
[1.7.0]: https://github.com/erremauro/cigno-zen/releases/tag/v1.7.0
[1.6.2]: https://github.com/erremauro/cigno-zen/releases/tag/v1.6.2
[1.6.1]: https://github.com/erremauro/cigno-zen/releases/tag/v1.6.1
[1.6.0]: https://github.com/erremauro/cigno-zen/releases/tag/v1.6.0
[1.5.0]: https://github.com/erremauro/cigno-zen/releases/tag/v1.5.0
[1.4.0]: https://github.com/erremauro/cigno-zen/releases/tag/v1.4.0
[1.3.0]: https://github.com/erremauro/cigno-zen/releases/tag/v1.3.0
[1.2.2]: https://github.com/erremauro/cigno-zen/releases/tag/v1.2.2
[1.2.1]: https://github.com/erremauro/cigno-zen/releases/tag/v1.2.1
[1.2.0]: https://github.com/erremauro/cigno-zen/releases/tag/v1.2.0
[1.1.0]: https://github.com/erremauro/cigno-zen/releases/tag/v1.1.0
[1.0.0]: https://github.com/erremauro/cigno-zen/releases/tag/v1.0.0
[0.9.0]: https://github.com/erremauro/cigno-zen/releases/tag/v0.9.0
[0.8.0]: https://github.com/erremauro/cigno-zen/releases/tag/v0.8.0
[0.7.1]: https://github.com/erremauro/cigno-zen/releases/tag/v0.7.1
[0.7.0]: https://github.com/erremauro/cigno-zen/releases/tag/v0.7.0
[0.6.0]: https://github.com/erremauro/cigno-zen/releases/tag/v0.6.0
[0.5.0]: https://github.com/erremauro/cigno-zen/releases/tag/v0.5.0
[0.4.0]: https://github.com/erremauro/cigno-zen/releases/tag/v0.4.0
[0.3.0]: https://github.com/erremauro/cigno-zen/releases/tag/v0.3.0
[0.2.1]: https://github.com/erremauro/cigno-zen/releases/tag/v0.2.1
[0.2.0]: https://github.com/erremauro/cigno-zen/releases/tag/v0.2.0
[0.1.0]: https://github.com/erremauro/cigno-zen/releases/tag/v0.1.0
[0.0.1]: https://github.com/erremauro/cigno-zen/releases/tag/v0.0.1
