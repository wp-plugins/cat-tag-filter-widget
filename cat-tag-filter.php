<?php
/* 
Plugin Name: Cat + Tag Filter
Plugin URI: http://wordpress.org/extend/plugins/cat-tag-filter-widget/
Description: This plugin adds a widget to your WordPress site that allows your visitors to filter posts by category and tag.
Author: Ajay Verma
Version: 0.3
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
	$tag_prefix = '?tag=';
	if ($_POST["tag_logic"] == "AND") $tag_logic = '+';
	else $tag_logic = ',';
	if ($_POST["cat"] != -1) {
		$cat = '/?category_name=' . $_POST['cat'];
		$tag_prefix = '&tag=';
	} 

	if ($_POST["tag"] != -1) {
		$tag .= $tag_prefix;
		$i = 0;
		foreach ($_POST["tag"] as $tags){	
			if ($i > 0) $tag .= $tag_logic;
			$tag .= $tags;	
			$i++;
		}
	}	

	$url = $_POST["home_url"] . $cat . $tag; 
	header('Location: ' . $url);
	exit;
	
}
else {
	if (basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__)) exit('Please do not load this page directly');
	}
	
$str = $_SERVER['QUERY_STRING'];
parse_str($str, $args);
if ($args[category_name] !='') $current_cat = $args[category_name];
if ($args[tag] !='') $current_tag = $args[tag];

function child_cats_list($parent, $level){
  
  global $ctf_options, $categories, $current_cat;
  foreach ($categories as $category) 
    { 
      if ($category->parent == $parent)
        {
          $options .= '<option value="' . $category->category_nicename . '"';
		  if (is_category($category->cat_name) || $category->category_nicename == $current_cat) $options .= ' selected="selected" ';
		  $options .= '>';
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
function tag_options($type){
  global $ctf_options, $current_tag; 
  $tags = get_tags();
  if ($type == 'checkboxes'){
   $options .= '<ul>';
  foreach ($tags as $tag) {
   $options .= '<li>'; 
    $options .= '<input type="checkbox" name="';
	$options .= "tag[]";
	$options .= '" value="' . $tag->slug . '"';
	if (is_tag($tag->slug) || $tag->slug == $current_tag) $options .= ' checked ';
	$options .= '>';
    $options .= $tag->name;
    if ($ctf_options['tags_count'] == 1) $options .= ' (' . $tag->count . ')';
    $options .= '</li>';    
  }
  $options .= '</ul>';
  }
  else {  
 
 $options .= '<select name="tag" id="ctf-tag-select" ><option value="-1">';
  if ($ctf_options['all_tags_text'] != '') $options .= $ctf_options['all_tags_text']; else $options .=__('Any tag', 'cat-tag-filter');
  $options .= '</option>'; 
  foreach ($tags as $tag) {
    $options .= '<option value="' . $tag->slug . '"';
	if (is_tag($tag->slug) || $tag->slug == $current_tag) $options .= ' selected="selected" ';
	$options .= '>';
    $options .= $tag->name;
    if ($ctf_options['tags_count'] == 1) $options .= ' (' . $tag->count . ')';
    $options .= '</option>';    
  }
  $options .= '</select>'; }
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
      <?php 
	  
	  echo tag_options($ctf_options['tag_type']); 
	  
	   ?>        
    <input type="hidden" name="ctf_submit" value="1" />
    <input type="hidden" name="home_url" value="<?php bloginfo('url'); ?>" /> 
	<input type="hidden" name="tag_logic" value="<?php echo $ctf_options['tag_logic'] ?>" />	
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
	  $defaults = array( 'title' => __('Filter', 'cat-tag-filter'), 'button_title' => __('Show posts', 'cat-tag-filter'), 'cat_list_label' => __('Show posts from:', 'cat-tag-filter'), 'tag_list_label' => __('With tag:', 'cat-tag-filter'), 'all_cats_text' => __('Any category', 'cat-tag-filter'), 'all_tags_text' => __('Any tag', 'cat-tag-filter'), 'cats_count' => 1, 'tags_count' => 0, 'tag_logic' => 'AND', 'tag_type' => 'dropdown' );
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
	$ctf_options['tag_logic'] =  $instance['tag_logic'];
	$ctf_options['tag_type'] =  $instance['tag_type'];
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
	$instance['tag_logic'] = $new_instance['tag_logic'];
	$instance['tag_type'] = $new_instance['tag_type'];
    return $instance;
  }
  /** @see WP_Widget::form */
  function form($instance) {   
    $defaults = array( 'title' => __('Filter', 'cat-tag-filter'), 'button_title' => __('Show posts', 'cat-tag-filter'), 'cat_list_label' => __('Show posts from:', 'cat-tag-filter'), 'tag_list_label' => __('With tag:', 'cat-tag-filter'), 'all_cats_text' => __('Any category', 'cat-tag-filter'), 'all_tags_text' => __('Any tag', 'cat-tag-filter'), 'cats_count' => 1, 'tags_count' => 0, 'tag_logic' => 'AND', 'tag_type' => 'dropdown' );
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
      <input type="checkbox" id="ctf-tag-type" name="<?php echo $this->get_field_name('tag_type'); ?>" value="checkboxes" <?php if ($instance['tag_type'] == 'checkboxes') echo "checked ";?>/>
      <label for="ctf-tag-type">
        <?php _e('Allow selecting multiple tags with checkboxes', 'cat-tag-filter'); ?> 
      </label>   
    </p>
	<p>        
      <input type="checkbox" id="ctf-tag-logic" name="<?php echo $this->get_field_name('tag_logic'); ?>" value="AND" <?php if ($instance['tag_logic'] == 'AND') echo "checked ";?>/>
      <label for="ctf-tag-logic">
        <?php _e('Use AND logic for multiple tags instead of OR', 'cat-tag-filter'); ?> 
      </label>   
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
	<p>
	Please consider donating 10 cents if you like this plugin! :)
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
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