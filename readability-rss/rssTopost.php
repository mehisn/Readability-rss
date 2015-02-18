<?php 
    /*
    Plugin Name: Readability RSS Feed parser
    Plugin URI: http://www.noulik.ee
    Description: Plugin for parsing Readability RSS Feed and adding those articles to your Wordpress posts(images are included)
    Author: M. Noulik
    Version: 1.0
    Author URI: http://www.noulik.ee
    */
	
	
?>
<?php
function feed_admin(){

	include('functions.php');
}

function feed_admin_actions(){
	add_options_page("Readability Feed", "Readability Feed", "administrator", "feed_display_main", "feed_admin");
	
}

add_action('admin_menu', 'feed_admin_actions');

// -----------------WIDGET----------------------

class Readability extends WP_Widget
{
  function Readability()
  {
    $widget_ops = array('classname' => 'Readability', 'description' => 'Updates Posts. Update interval is set by Admin' );
    $this->WP_Widget('Readability', 'Readabilit API Feed', $widget_ops);
  }
 
  function form($instance)
  {
    $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
    $title = $instance['title'];
?>
  <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
<?php
  }
 
  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    return $instance;
  }
 
  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);
 
    echo $before_widget;
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
 
    if (!empty($title))
      echo $before_title . $title . $after_title;;
 
    // WIDGET CODE GOES HERE
		//echo '<div class="hidden">';
		include('tere.php');
		
		//echo '</div>';
    echo $after_widget;
  }
 
}
// -----------------WIDGET----------------------
add_action( 'widgets_init', create_function('', 'return register_widget("Readability");') );?>