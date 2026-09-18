# Scripts Manager

A [Marketers Delight](https://marketersdelight.com/) drop-in for adding custom code to the `<head>` and the end of the `<body>`, sitewide or on specific posts, archives and terms. It can also add body classes to individual pages, and turn off plugin scripts and styles on pages that don't need them.

## Features

- Header scripts, printed in `<head>`
- Footer scripts, printed before `</body>`
- Sitewide scripts, plus scripts for single posts and pages, post type archives and the blog page, and category and taxonomy archives. Page-specific scripts are added after the sitewide ones.
- Custom body classes, sitewide or on individual posts, pages and terms
- Scripts Optimization: turn off selected plugin scripts and styles on single posts and on category and taxonomy archives. The list of plugins comes from the `md_filter_dequeue_scripts` filter, and the option only shows when something is registered.
- Prints the Google Analytics (gtag.js) tag when a Google Analytics ID is set in MD's integration settings

## Settings

**Sitewide** (MD settings dashboard → Scripts):

- **Body classes**
- **Header Scripts**
- **Footer Scripts**

**Post type archives**: the same three fields in each post type's settings, for its archive page (and the blog page for posts).

**Per page**: in the Page Settings box on posts and pages, and on category and taxonomy term screens:

- **Body classes**
- **Header Scripts**
- **Footer Scripts**
- **Scripts Optimization**: checkboxes for each registered plugin (only shows when a plugin is registered)

## Developers

- `md_filter_dequeue_scripts`: register plugins whose assets can be turned off per page. Return an array keyed by an ID, where each item holds `label`, and optionally `scripts` and `styles` (arrays of registered handles).

```php
add_filter( 'md_filter_dequeue_scripts', function( $plugins ) {
	$plugins['contact-form'] = array(
		'label' => 'Contact Form',
		'scripts' => array( 'contact-form-js' ),
		'styles' => array( 'contact-form-css' )
	);

	return $plugins;
} );
```

## Requirements

- Marketers Delight 6.0 or later
- WordPress 6.6 or later
- PHP 7.4 or later

## Install

1. Download the latest `scripts-x.y.z.zip` from the [Releases page](https://github.com/MarketersDelight/scripts/releases).
2. In WordPress, go to MD's Drop-ins screen, click **Add new**, and upload the zip.
3. Activate Scripts Manager, then add your scripts in MD's settings or on any post.

Don't use **Code → Download ZIP**. That zip includes development files and a folder name that MD won't recognize.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
