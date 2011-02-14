=== Plugin Name ===
Contributors: ajayver
Donate link: 
Tags: filter, tags, categories, widget
Requires at least: 2.8
Tested up to: 3.0.5
Stable tag: 0.1

This plugin adds a widget to you WordPress site that gives yor visitors an opportunity to filter all your posts by a category or/and tag.

== Description ==

This plugin adds a widget to you WordPress site that gives yor visitors an opportunity to filter all your posts by a category or/and tag.
Basicly it will just generate a link like http://yourblog.com/category/some-category?tag=some-tag and redirect visitor to this address.

== Installation ==


1. Upload `cat-tag-filter` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to your widgets settings and ass Cat + Tag Filter widget to your sidebar.

If your theme doesn't support widgets, you can use this code:

`<?php the_widget('cat_tag_filter', $instance, $args); ?>`

Here is a full list of default $instance arguments:
`'title' => 'Filter'
'button_title' => 'Show posts'
'cat_list_label' => 'Show posts from:
'tag_list_label' => 'With tag:' 
'all_cats_text' => 'Any category'
'all_tags_text' => 'Any tag'
'cats_count' => 1 
'tags_count' => 0 `


If you want to override some settings, for example get rid of title and turn on the counter for tag list use this code:

`<?php the_widget('cat_tag_filter','title=&tags_count=1'); ?>`

If you want to get rid of div's that WordPress creates before and after all the widgets, use this code:

`<?php the_widget('cat_tag_filter','title=','before_widget=&after_widget='); ?>`

You can also override `before_title` and `after_title` the same way.



== Changelog ==

= 0.1 =
* Plugins first publication