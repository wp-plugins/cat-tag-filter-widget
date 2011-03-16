<?php
/*
Plugin Name: Cat + Tag Filter
Plugin URI: http://wordpress.org/extend/plugins/cat-tag-filter-widget/
Description: This plugin adds a widget to your WordPress site that allows your visitors to filter posts by category and tag.
Author: Ajay Verma
Version: 0.2.1
Author URI: http://traveliving.org/
License: GPL2
*/
/*  Copyright 2011  Verma Ajay  (email : ajayverma1986@gmail.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ($_POST["ctf_submit"] == 1) {
if ($_POST["cat"] != -1) {$cat = '/?category_name=' . $_POST['cat'];
if ($_POST["tag"] != -1) $tag = '&tag=' . $_POST["tag"];}
else {if ($_POST["tag"] != -1) $tag = '/?tag=' . $_POST["tag"];}
$url = $_POST["home_url"] . $cat . $tag; 
header('Location: ' . $url);
}
else{
if (basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__)) exit('Please do not load this page directly');}

function child_cats_list($parent, $level){
  global $ctf_options;
  global $categories;
  foreach ($categories as $category) 
    { 
      if ($category->parent == $parent)
        {
          $options .= '<option value="' . $category->category_nicename . '">';
          for ($i=0;$i<$level;$i++)
            { 
            $options .="&nbsp;&nbsp;";
            }
          $options .=" "; 
          $options .= $category->cat_name;
          if ($ctf_options['cats_count'] == 1) $options .= ' ('.$category->category_count.')';
          $options .= '</option>';    
          $options .= child_cats_list($category->cat_ID, $level+1);    
        }     
    }
  return $options;
} 
function cat_options(){
  global $categories;
  $categories =  get_categories('pad_counts=1');
  return child_cats_list(0, 0);
}
function tag_options(){
  global $ctf_options; 
  $tags = get_tags();
  foreach ($tags as $tag) {
    $options .= '<option value="'.$tag->slug.'">';
    $options .= $tag->name;
    if ($ctf_options['tags_count'] == 1) $options .= ' (' . $tag->count . ')';
    $options .= '</option>';    
  }
  return $options;
}
function ctf_widget(){
  global $ctf_options; ?>
  <form action="<?php echo get_bloginfo('wpurl') . '/' . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)) . '/cat-tag-filter.php'; ?>" method="post">  
    <?php if ($ctf_options['cat_list_label']) echo '<label for="ctf-cat-select">' . $ctf_options['cat_list_label'] . '</label>'; ?> 
    <select name="cat" id="ctf-cat-select" >   
      <option value="-1">
      <?php if ($ctf_options['all_cats_text'] != '') echo $ctf_options['all_cats_text']; else _e('Any category', 'cat-tag-filter'); ?>
      </option>   
      <?php echo cat_options(); ?> 
    </select><br />
    <?php if ($ctf_options['tag_list_label']) echo '<label for="ctf-tag-select">' . $ctf_options['tag_list_label'] . '</label>'; ?>
    <select name="tag" id="ctf-tag-select" >   
      <option value="-1">
      <?php if ($ctf_options['all_tags_text'] != '') echo $ctf_options['all_tags_text']; else _e('Any tag', 'cat-tag-filter'); ?>
      </option>   
      <?php echo tag_options(); ?> 
    </select>          
    <input type="hidden" name="ctf_submit" value="1" />
    <input type="hidden" name="home_url" value="<?php bloginfo('url'); ?>" />    
    <input id="ctf-submit" class="button" type="submit"  value="<?php echo $ctf_options['button_title']; ?>"/>  
  </form>  
  <?php
}
/**
 * Cat + Tag Filter Class
 */
  
class cat_tag_filter extends WP_Widget {
  /** constructor */
  function cat_tag_filter() {
    $widget_ops = array('classname' => 'cat-tag-filter', 'description' => __('Filter posts by category and tag', 'cat_tag_filter') );
    parent::WP_Widget(false, $name = 'Cat + Tag Filter', $widget_ops);	
  }
  /** @see WP_Widget::widget */
  function widget($args, $instance) {	
	  $defaults = array( 'title' => __('Filter', 'cat-tag-filter'), 'button_title' => __('Show posts', 'cat-tag-filter'), 'cat_list_label' => __('Show posts from:', 'cat-tag-filter'), 'tag_list_label' => __('With tag:', 'cat-tag-filter'), 'all_cats_text' => __('Any category', 'cat-tag-filter'), 'all_tags_text' => __('Any tag', 'cat-tag-filter'), 'cats_count' => 1, 'tags_count' => 0 );
    $instance = wp_parse_args( (array) $instance, $defaults );
    extract( $args );
    global $ctf_options;
    $ctf_options['title'] = apply_filters('widget_title', $instance['title']);
    $ctf_options['button_title'] = apply_filters('widget_title', $instance['button_title']);
    $ctf_options['cat_list_label'] = apply_filters('widget_title', $instance['cat_list_label']);
    $ctf_options['tag_list_label'] = apply_filters('widget_title', $instance['tag_list_label']);
    $ctf_options['all_cats_text'] = apply_filters('widget_title', $instance['all_cats_text']);
    $ctf_options['all_tags_text'] = apply_filters('widget_title', $instance['all_tags_text']);
    $ctf_options['cats_count'] =  $instance['cats_count'];
    $ctf_options['tags_count'] =  $instance['tags_count'];
    echo $before_widget; 
    if ( $ctf_options['title'] ) echo $before_title . $ctf_options['title'] . $after_title; 
    ctf_widget();
    echo $after_widget; 
  }
    /** @see WP_Widget::update */
  function update($new_instance, $old_instance) {				
  	$instance = $old_instance;
  	$instance['title'] = strip_tags($new_instance['title']);
  	$instance['button_title'] = strip_tags($new_instance['button_title']);
  	$instance['cat_list_label'] = strip_tags($new_instance['cat_list_label']);
  	$instance['tag_list_label'] = strip_tags($new_instance['tag_list_label']);
  	$instance['all_cats_text'] = strip_tags($new_instance['all_cats_text']);
  	$instance['all_tags_text'] = strip_tags($new_instance['all_tags_text']);
  	$instance['cats_count'] = $new_instance['cats_count'];
  	$instance['tags_count'] = $new_instance['tags_count'];
    return $instance;
  }
  /** @see WP_Widget::form */
  function form($instance) {   
    $defaults = array( 'title' => __('Filter', 'cat-tag-filter'), 'button_title' => __('Show posts', 'cat-tag-filter'), 'cat_list_label' => __('Show posts from:', 'cat-tag-filter'), 'tag_list_label' => __('With tag:', 'cat-tag-filter'), 'all_cats_text' => __('Any category', 'cat-tag-filter'), 'all_tags_text' => __('Any tag', 'cat-tag-filter'), 'cats_count' => 1, 'tags_count' => 0 );
  	$instance = wp_parse_args( (array) $instance, $defaults ); 				
    ?>          
    <p>    
      <label for="ctf-widget-title">
        <?php _e('Widget title', 'cat-tag-filter'); ?>: 
      </label>    
      <input type="text" id="ctf-widget-title" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($instance['title']);?>" />   
    </p>        
    <p>    
      <label for="ctf-cat-list-title">
        <?php _e('Categories dropdown label', 'cat-tag-filter'); ?>: 
      </label>    
      <input type="text" id="ctf-cat-list-title" name="<?php echo $this->get_field_name('cat_list_label'); ?>" value="<?php echo esc_attr($instance['cat_list_label']);?>" />   
    </p>  
    <p>    
      <label for="ctf-all-cats-text">
        <?php _e('All categories option text', 'cat-tag-filter'); ?>: 
      </label>    
      <input type="text" id="ctf-all-cats-text" name="<?php echo $this->get_field_name('all_cats_text'); ?>" value="<?php echo esc_attr($instance['all_cats_text']);?>" />   
    </p>  
    <p>        
      <input type="checkbox" id="ctf-cats-count" name="<?php echo $this->get_field_name('cats_count'); ?>" value="1" <?php if ($instance['cats_count'] == 1) echo "checked ";?>/>
      <label for="ctf-cats-count">
        <?php _e('Show categories post count', 'cat-tag-filter'); ?> 
      </label>   
    </p>  
    <p>    
      <label for="ctf-tag-list-title">
        <?php _e('Tags dropdown label', 'cat-tag-filter'); ?>: 
      </label>    
      <input type="text" id="ctf-tag-list-title" name="<?php echo $this->get_field_name('tag_list_label'); ?>" value="<?php echo esc_attr($instance['tag_list_label'])?>" />   
    </p>  
    <p>    
      <label for="ctf-all-tags-text">
        <?php _e('All tags option text', 'cat-tag-filter'); ?>: 
      </label>    
      <input type="text" id="ctf-all-tags-text" name="<?php echo $this->get_field_name('all_tags_text'); ?>" value="<?php echo esc_attr($instance['all_tags_text']);?>" />   
    </p>  
    <p>        
      <input type="checkbox" id="ctf-tags-count" name="<?php echo $this->get_field_name('tags_count'); ?>" value="1" <?php if ($instance['tags_count'] == 1) echo "checked ";?>/>
      <label for="ctf-tags-count">
        <?php _e('Show tags post count', 'cat-tag-filter'); ?> 
      </label>   
    </p>  
    <p>    
      <label for="ctf-button-title">
        <?php _e('Button title', 'cat-tag-filter'); ?>: 
      </label>    
      <input type="text" id="ctf-button-title" name="<?php echo $this->get_field_name('button_title'); ?>" value="<?php echo esc_attr($instance['button_title']);?>" />   
    </p>        
  <?php 
  }
} // class Cat + Tag Filter
// register Cat + Tag Filter widget
	if (function_exists('load_plugin_textdomain'))
	{
		load_plugin_textdomain('cat-tag-filter', '/' .PLUGINDIR. '/' .dirname(plugin_basename(__FILE__)) . '/languages/' );
	}
add_action('widgets_init', create_function('', 'return register_widget("cat_tag_filter");')); 
?>