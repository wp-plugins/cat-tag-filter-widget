<?php
/*
Plugin Name: Cat + Tag Filter
Plugin URI: 
Description: This plugin adds a widget to your WordPress site that allows your visitors to filter posts by category and tag.
Author: Ajay Verma
Version: 0.4.2
Author URI: http://traveliving.org/
*/
/*  Copyright 2011  Verma Ajay  (email : ajayverma1986@gmail.com)
    
*/

if ($_POST["ctf_submit"] == 1) {
	$cat = '';
	$tag = '';
	$tag_prefix = '?tag=';
	if ($_POST["tag_logic"] == 1) $tag_logic = '+';
	else $tag_logic = ',';
	if ($_POST["cat"] != -1) {
		$cat = '/?category_name=' . $_POST['cat'];
		$tag_prefix = '&tag=';
	} 

	if ($_POST["tag"] && $_POST["tag"][0] != -1) {
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
if ($args[tag]){ 
$args[tag] = str_replace(" ", ",", $args[tag]);

$args[tag] = explode(",", $args[tag]);
$current_tag = $args[tag];}

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
  global $ctf_options, $categories;
  if ($ctf_options['exclude_cats'] != '') $args = '&exclude=' . $ctf_options['exclude_cats']; 
  else $args = '';
  $categories =  get_categories('pad_counts=1' . $args);
  return child_cats_list(0, 0);
}
function tag_options($type){
  global $ctf_options, $current_tag;
  if ($ctf_options['exclude_tags'] != '') $args = 'exclude=' . $ctf_options['exclude_tags']; 
  else $args = '';
  $tags = get_tags($args);
  if ($type == 1){
   $options .= '<ul>';
  foreach ($tags as $tag) {
   $options .= '<li>'; 
    $options .= '<input type="checkbox" name="';
	$options .= "tag[]";
	$options .= '" value="' . $tag->slug . '"';
	if (is_array($current_tag)) {if (in_array($tag->slug, $current_tag)) $options .= ' checked ';}
	$options .= '>';
    $options .= $tag->name;
    if ($ctf_options['tags_count'] == 1) $options .= ' (' . $tag->count . ')';
    $options .= '</li>';    
  }
  $options .= '</ul>';
  }
  else {  
 
 $options .= '<select name="' . "tag[]" . '" id="ctf-tag-select" ><option value="-1">';
  if ($ctf_options['all_tags_text'] != '') $options .= $ctf_options['all_tags_text']; else $options .=__('Any tag', 'cat-tag-filter');
  $options .= '</option>'; 
  foreach ($tags as $tag) {
    $options .= '<option value="' . $tag->slug . '"';
	if (is_array($current_tag)) {if (in_array($tag->slug, $current_tag)) $options .= ' selected="selected" ';}
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
	  $defaults = array( 'title' => __('Filter', 'cat-tag-filter'), 'button_title' => __('Show posts', 'cat-tag-filter'), 'cat_list_label' => __('Show posts from:', 'cat-tag-filter'), 'tag_list_label' => __('With tag:', 'cat-tag-filter'), 'all_cats_text' => __('Any category', 'cat-tag-filter'), 'all_tags_text' => __('Any tag', 'cat-tag-filter'), 'cats_count' => 1, 'tags_count' => 0, 'tag_logic' => 1, 'tag_type' => 0, 'exclude_tags' => '', 'exclude_cats' => '');
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
	$ctf_options['exclude_tags'] = $instance['exclude_tags'];
	$ctf_options['exclude_cats'] = $instance['exclude_cats'];
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
	$instance['exclude_tags'] = $new_instance['exclude_tags'];
	$instance['exclude_cats'] = $new_instance['exclude_cats'];
    return $instance;
  }
  /** @see WP_Widget::form */
  function form($instance) {   
    $defaults = array( 'title' => __('Filter', 'cat-tag-filter'), 'button_title' => __('Show posts', 'cat-tag-filter'), 'cat_list_label' => __('Show posts from:', 'cat-tag-filter'), 'tag_list_label' => __('With tag:', 'cat-tag-filter'), 'all_cats_text' => __('Any category', 'cat-tag-filter'), 'all_tags_text' => __('Any tag', 'cat-tag-filter'), 'cats_count' => 1, 'tags_count' => 0, 'tag_logic' => 1, 'tag_type' => 0, 'exclude_tags' => '', 'exclude_cats' => '' );
  	$instance = wp_parse_args( (array) $instance, $defaults ); 				
    ?>   
	<p>
		<strong>Please consider donating 10 cents if you like this plugin! :)</strong>	</p>
		<p>
	<a href="http://goo.gl/SCdKg"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" alt="donate" /></a>    
	</p>
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
      <label for="ctf-exclude_cats">
        <?php _e('Coma separated category id\'s to exclude', 'cat-tag-filter'); ?>: 
      </label>    
      <input type="text" id="ctf-exclude_cats" name="<?php echo $this->get_field_name('exclude_cats'); ?>" value="<?php echo esc_attr($instance['exclude_cats']);?>" />   
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
	<label for="ctf-tag-type">
	<?php _e('How to show tags:', 'cat-tag-filter'); ?> 
	</label>
	<select name="<?php echo $this->get_field_name('tag_type'); ?>" id="ctf-tag-type" >   
      <option value="0" <?php if ($instance['tag_type'] == 0) echo 'selected="selected" ';?> >
      <?php _e('Dropdown list', 'cat-tag-filter'); ?>
      </option>   
	  <option value="1" <?php if ($instance['tag_type'] == 1) echo 'selected="selected" ';?> >
      <?php _e('Checkboxes', 'cat-tag-filter'); ?>
      </option>
    </select>
	</p>
	<p>
	<label for="ctf-tag-logic">
	<?php _e('Logic operator for multiple tags:', 'cat-tag-filter'); ?> 
	</label>
	<select name="<?php echo $this->get_field_name('tag_logic'); ?>" id="ctf-tag-logic" >   
      <option value="0" <?php if ($instance['tag_logic'] == 0) echo 'selected="selected" ';?> >
      <?php _e('OR', 'cat-tag-filter'); ?>
      </option>   
	  <option value="1" <?php if ($instance['tag_logic'] == 1) echo 'selected="selected" ';?> >
      <?php _e('AND', 'cat-tag-filter'); ?>
      </option>
    </select>
	</p>
    <p>        
      <input type="checkbox" id="ctf-tags-count" name="<?php echo $this->get_field_name('tags_count'); ?>" value="1" <?php if ($instance['tags_count'] == 1) echo "checked ";?>/>
      <label for="ctf-tags-count">
        <?php _e('Show tags post count', 'cat-tag-filter'); ?> 
      </label>   
    </p>  
	<p>    
      <label for="ctf-exclude_tags">
        <?php _e('Coma separated tag id\'s to exclude', 'cat-tag-filter'); ?>: 
      </label>    
      <input type="text" id="ctf-exclude_tags" name="<?php echo $this->get_field_name('exclude_tags'); ?>" value="<?php echo esc_attr($instance['exclude_tags']);?>" />   
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