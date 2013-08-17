=== IntelliWidget Per Page Featured Posts and Menus ===
Contributors: lilaeamedia
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DE4W9KW7HQJNA
Tags: featured posts, events, page menu, plugin, textwidget, widget, custom post types, custom sidebar
Requires at least: 3.5
Tested up to: 3.6
Stable tag: 1.3.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display featured posts, custom menus, html content and more within a single dynamic sidebar that can be customized on a per-page or site-wide basis.

== Description ==

Why use IntelliWidget as your Featured Post plugin?

* Powerful and extensible, does the work of multiple plugins
* No new sidebars necessary–uses the sidebars you already have
* Displays custom page settings if they exist, main widget if they don’t
* Supports Custom Post Types and Custom Nav Menus
* Doubles as a Text Widget–customizable to any page
* Use as many different instances on a single page as you wish
* Saves hours setting up and maintaining your WordPress site
* Supports shortcodes so you can put the power of Intelliwidget anywhere in your site
* Set up one page and reuse settings on other pages
* No new database tables
* Clean uninstall
* Lets you keep using all your other widgets
* Shows or hides content by date and time
* Improves performance by retrieving Post data using a single query instead of multiple meta data function calls.

IntelliWidget eliminates the need for multiple sidebars to accommodate page-specific content. Instead, you use one set of dynamic sidebars, load a few IntelliWidgets and then customize on a per-page basis. If you don’t customize a page, the default widget (the one you configured on the widgets page) displays instead.

IntelliWidgets can include but are not limited to: custom page menus, featured posts, slider lists, arbitrary text/html (textwidgets), calendars, testimonials, categories and more.

IntelliWidget now supports Wordpress Custom Menus! Use the Nav Menu template option and select any Nav Menu to use on specific pages. Standard IntelliWidget Menus continue to work as before.

Expire posts on a specific date/time using the new date features. Also, you can combine active posts and expired posts to show only events going on currently.

Because the lists are generated using active titles and permalinks, your site is always current with the latest versions of your content.

Combine with custom post types to create unlimited CMS-style content blocks. Combine with your favorite jQuery Slider plugin to display animated slideshows. You can even use the settings from an existing page. Now you don't have to add a gazillion sidebars to your widgets admin to have unlimited page-specific content.

IntelliWidget is flexible and versatile. Please read the documentation to see more ideas how to use it to its full potential.
 
== Installation ==

1. Download the IntelliWidget plugin archive and unzip it.

2. Upload the 'intelliwidget-per-page-featured-posts-and-menus' directory to `/wp-content/plugins/` directory

3. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How do I get started? =

See the "Getting Started" section.

= Where can I find documentation? =

Docs can be found at http://www.lilaeamedia.com/plugins/intelliwidget/

== Screenshots ==

1. Example of the Widgets Admin Panel.
2. Example of the Page Admin Panel.
3. Example of the Main Settings Panel.
4. Example of the Advanced Settings Panel.
5. Example of the Custom Data Fields Panel.

== Changelog ==

= 1.3.2 =
* Test if $post is object before attempted to get ID attribute to prevent error notice.

= 1.3.1 =
* Changed method signature of Walker_IntelliWidget::start_el() to match Walker::start_el() to avoid Strict Standards notice in WP 3.6

= 1.3.0 =
* Added intelliwidget-current-menu-item style for items linked to current page
* Added "Override copied settings" feature. You can now keep some or all of the settings sections from a page and copy the rest
* Added "events" template and corresponding styles to intelliwidget.css

= 1.2.6 =
* Fixed bug in "skip current post" (thanks Markus)
* Section Settings Specific Posts Menu now refreshes on save

= 1.2.5 =
* Fixed random sort order bug (thanks Joshua)
* Strip tags from title attribute text on links
* Fixed conditional in skip_expired query

= 1.2.4 =
* Fixed SQL bug that incorrectly joined taxonomies. (thanks AMoy)
* Cleaned up _get_the_intelliwidget_excerpt to strip all but text.

= 1.2.3 =
* Fixed SQL bug that caused duplicate results in the post data. (thanks MNolte)
* Cleaned up i18l functions and created new .pot file.

= 1.2.2 =
* Fixed bug that created invalid path to the admin JavaScript include on Windows
* Thanks to Spokesrider on Wordpress.org for finding this!
* Added Page Menu as option for Nav Menu (to automatically generate menu from pages)
* Added ID field option to override default 'intelliwidget' id

= 1.2.1 =
* Fixed bug that horked the query generator when no specific posts are selected
* Renamed the url to array function to prevent JS namespace collisions

= 1.2.0 =
* Supports Custom Nav Menus as well as IntelliWidget menus
* Support for IntelliWidget Shortcode
* Support for Event Date and Expire Date on posts
* Hierarchical Select Menus
* Hide Expired post option
* Show only active posts option
* Changed the way IW treats event dates (now separate custom data field)
* Added Query Class separate from The Loop to retrieve all data in a single database call
* Reduced the number of database queries per widget instance
* Changed custom data field names so they don't conflict with existing data fields
* Refactored template tags to be more efficient
* Added Custom Data Fields meta box for easy editing of postmeta fields (especially dates)

= 1.1.0 =
* Improved the overall interface.
* Fixed bug in the way IW saves per-page widgets 

= 1.0.2 =
* Ajax submits on Edit Page
* Commented out &raquo; on read more link

= 1.0.1 =
* Form areas collapsible to save space

= 1.0.0 =
* Combined favorite functionality from different widgets into a single IntelliWidget.
* Packaged for public consumption


== Upgrade Notice ==

= 1.3.0 =
New Features! We added the long-needed intelliwidget-current-menu-item style, a generic "events" template, and the ability to keep some page sections and copy the rest using "Override Copied Settings."

= 1.2.4 =
This upgrade fixes an issue in the query class that incorrectly joined posts and taxonomy terms.

= 1.2.3 =
This upgrade fixes an issue in the query class that caused multiple rows to be returned for posts with duplicate thumbnail rows in the postmeta table. It also fixes a problem with the excerpts template showing debug output.

= 1.2.0 =
IntelliWidget now uses its own query class instead of WP_Query so it can get postmeta data in a single database call. Event Date and Expire Date fields have been added which replace using "future" post status for upcoming events. PLEASE READ http://www.lilaeamedia.com/plugins/intelliwidget/ and report any issues on the WP Forum.

= 1.1.0 =
Ajax submits and collapsible form areas greatly improve useability

== Getting Started ==

Here is a very simple example of how to use an IntelliWidget titled "My First IntelliWidget."

1. From Appearance > Widgets admin, drag the IntelliWidget over to one of your existing sidebars. 

2. Enter "My First IntelliWidget" in the Title input. Leave the "Link to Archive" box unchecked for now. Save the Widget.

3. Load a page from your site in a browser that uses the sidebar you just modified. You will see a menu of up to five links with the Title, "My First IntelliWidget." This is the default behavior: a menu of page links sorted by title. This is not very useful but it serves to understand the power of IntelliWidgets.

4. In the WordPress Admin, go to Pages and click to edit the page you just viewed. You will now see a new meta box labeled "IntelliWidget." Leave the "Use settings from" alone and click "Add new section."

5. When the page refreshes, you will see a new meta box with settings almost exactly like the ones in the Widgets Admin. Click the "Replaces" dropdown menu and you will see an option for the sidebar where you added the IntelliWidget. Select this option.

6. Give the section a title in the "Section Title" input. Type some text in the "Custom Text/HTML" textarea and select "Text Only-No Posts" option in the dropdown menu. Click "Save."

7. Now load the page you just edited in your browser. Instead of the menu from before, you now see the new title and the custom text you typed. If you go to any other page that uses the same sidebar, you will see the menu from before.

8. The point of this exercise is to demonstrate that any page can override the default widget with it's own custom section.

9. Lastly, you can select this page in the "Use Settings From..." menu from any other page to re-use these settings. This is useful if you have a sub-set of pages that need to re-use the same sidebar content. This feature alone can save hours of repetition wasted on many other "custom sidebar" plugins.

== Documentation ==

Can be found at http://www.lilaeamedia.com/plugins/intelliwidget/

