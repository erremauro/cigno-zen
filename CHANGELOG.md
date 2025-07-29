# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- Add Volume's download link to `patterns/template-taxonomu-volumes.php`
### Changed
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

[unreleased]: https://github.com/erremauro/cigno-zen/compare/v0.4.0...HEAD
[0.4.0]: https://github.com/erremauro/cigno-zen/releases/tag/v0.4.0
[0.3.0]: https://github.com/erremauro/cigno-zen/releases/tag/v0.3.0
[0.2.1]: https://github.com/erremauro/cigno-zen/releases/tag/v0.2.1
[0.2.0]: https://github.com/erremauro/cigno-zen/releases/tag/v0.2.0
[0.1.0]: https://github.com/erremauro/cigno-zen/releases/tag/v0.1.0
[0.0.1]: https://github.com/erremauro/cigno-zen/releases/tag/v0.0.1
