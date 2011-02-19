=== Plugin Name ===
Contributors: ajayver
Donate link: 
Tags: filter, tags, categories, widget
Requires at least: 2.8
Tested up to: 3.0.5
Stable tag: 0.1

This plugin adds a widget to your WordPress site that gives your visitors an ability to filter all your posts by a category or/and tag.

== Description ==

If you were searching for an easy way to let your WordPress site visitors to filter your content by a category and a tag in the same time, this plugin will help you a lot. It will add a widget to your widgets admin page, where you can edit the settings and put it in any "widgetized" place on your website.

This plugin will be very useful for websites with hundreds of categories and tags. I wrote it for my travel blog where we have categories for places that we'd been and tags for topics like video, photo, useful, mountains, beaches e.t.c.
So I wanted to give my visitors an ability to easily filter content by any category plus tag, like **category India + tag Video**. WordPress has the ability to show such pages, we just need to pass the proper address to it. So this what is done by this small plugin - it gives you two selectors - one for your categories and one for tags - you can choose any combination of them and view the filtered content.

In the future I'm planning to add a second tag feature, so people would be able to filter posts like **category India + tag Motorcycles + tag Useful**. 
Please send me your ideas of implementation, the PHP code is almost ready - I just don't know how to add it without ruining the simplicity of the widget. 

If your WordPress template doesn't support widgets, please see the `Installation` tab, there are some instructions on how to manually add this widget in your template files. 

== Installation ==

1. Go to your Wordpress admin dashboard -> Plugins -> Add new, then search for **Cat + Tag Fliter** and agree to install it.

If it didn't work, try this:

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


== Screenshots ==

1. This is how the plugin looks in twentyten, almost no styling.
2. This is how it looks on my blog
3. These are the widget options

== Frequently Asked Questions ==

None.

== Changelog ==

= 0.1 =
* Plugins first publication


== Upgrade Notice ==

None.