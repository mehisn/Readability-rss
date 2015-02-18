
<?php require("wp-config.php")
?>
<?php
global $wpdb;

$results = $wpdb->get_results( 'SELECT * FROM wp_readability', OBJECT );?>
<div class="wrap">
    <?php    echo "<h2>" . __( 'Readability Parser', 'feed-trdom' ) . "</h2>"; ?>
     
    <form name="oscimp_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">

        <?php    echo "<h4>" . __( 'Readability Parser Settings', 'feed_trdom' ) . "</h4>"; ?>
        <p><?php _e("Feed URL: " ); ?><input type="text" value="<?php echo $results[last-query]->url; ?>" name="url" size="20"><?php _e(" ex: http://www.readability.com/YourUserName/latest/feed" ); ?></p>
        <p><?php _e("Update interval: " ); ?><input type="text" value="<?php echo $results[last-query]->interval; ?>" name="interval" size="20"><?php _e(" ex: 1 eqals every minute 60 equals every hour" ); ?></p>
        <p><?php _e("WP user: " ); ?><input type="text" value="<?php echo $results[last-query]->user; ?>" name="user"  size="20"><?php _e("Your username" ); ?></p>
        <p><?php _e("WP pass: " ); ?><input type="password" value="<?php echo $results[last-query]->pass; ?>" name="pass"  size="20"><?php _e("Your password" ); ?></p>
         
     
        <p class="submit">
        <input type="submit" name="Submit" value="<?php _e('Update Options', 'feed_trdom' ) ?>" />
        </p>
    </form>
</div>

<?php 
$results = $wpdb->get_results( 'SELECT * FROM wp_readability', OBJECT );
if(isSet($_POST['url']) and isSet($_POST['interval']) and isSet($_POST['user']) and isSet($_POST['pass'])){
	//echo $_POST['url']; 
	$wpdb->update( 
	'wp_readability', 
	array( 
		'url' => $_POST['url'],	// string
		'interval' => $_POST['interval'],	// int) 
		'user' => $_POST['user'],	// string 
		'pass' => $_POST['pass']	// string 
	), 
	array( 'ID' => 1 ), 
	array( 
		'%s',	// value1
		'%d',	// value2
		'%s',	// value3
		'%s'	// value4
	), 
	array( '%d' ) 
);
	?>
	<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>

	
	
	<?php
	
	
	
	//addposts();
}

function callActivation(){
	my_activation();
}




add_action('my_hourly_event', 'do_this_hourly');
my_activation();
function my_activation() {
	$string=false;
	if ( !wp_next_scheduled( 'my_hourly_event' ) ) {
		global $wpdb;
		$results = $wpdb->get_results( 'SELECT * FROM wp_readability', OBJECT );
		$refresh_time = $results[last-query]->interval;
		$minut = 60;
		$timer = $minut * $refresh_time;
		//echo $timer;
		wp_schedule_event( time() + $timer, 'minute', 'my_hourly_event'); //siin hetkel on iga minuti tagant  võtab hetke aja milrefresh tehti ja lisab sellele 60 sekki juurde
       // echo $striing = 'gotit';
		do_this_hourly(); 
			
        
	}else{
		
		echo '<div class="error"><p><strong>It will check for new posts if the CronJob has finished(update interval)</strong></p></div>';
		//my_activation();
    }

	$timestamp = wp_next_scheduled( 'my_hourly_event' ); 
	//print_r( $schedule);
    if($string and time()> $timestamp ){
		     // siit võtab selle schedle  aja    
        wp_unschedule_event( $timestamp,'my_hourly_event' );
		my_activation();
	}
}


function do_this_hourly() {
			global $wpdb;
			$results = $wpdb->get_results( 'SELECT * FROM wp_readability', OBJECT );
			
			$to_time = time() + 600;
			$from_time = time();
			echo round(abs($to_time - $from_time) / 60,2). " minute";
			
			echo "<div class='updated'><p><strong>Posts added. Cronjob has started</strong></p></div>";
			global $wpdb;
			$results = $wpdb->get_results( 'SELECT * FROM wp_readability', OBJECT );
			//print_r($results);
			addposts($results[last-query]->user, $results[last-query]->pass, $results[last-query]->url);
		
}




function addposts($USER, $PASS,$LINK){
	
//echo 'tere';
    libxml_disable_entity_loader(false);
	libxml_use_internal_errors(true);
	ini_set('max_execution_time', 500);
	
	$abi=true;

	
	require('IXR_Library.php');  
    
	$client = new IXR_Client(get_site_url().'/xmlrpc.php');
    //echo get_site_url().'/wordpress/xmlrpc.php';
    $USER = $USER;
    $PASS = $PASS;
	
	
	$juur=simplexml_load_file($LINK);	
	
	
	$params = array(0,$USER,$PASS,8);
	if (!$client->query('metaWeblog.getRecentPosts', $params)) { 
	  die('Something went wrong - '.$client->getErrorCode().' : '.$client->getErrorMessage()); 
	} 
	$myresponse = $client->getResponse();
	
	$array1=array();
	$array2=array();
	foreach ($juur->xpath("channel/item[contains(title, '$abi')]") as $uudis) { 
		array_push($array1,$uudis->title);
	}
	foreach($myresponse as $res){
		if($res['post_status']!="draft"){ 
			array_push($array2,$res['title']);
		}
	}
	$result = array_diff($array1, $array2); // siin käib kontroll kas on uusi

	//print_r($result);
	//echo '<br>';
	//echo '<br>';
	if($result){
	foreach ($result as $title) { 
		foreach ($juur->xpath("channel/item[contains(title, '$abi')]") as $uudis) { 
		 //$times = new IXR_Date();
			
			//echo $link.'   ';
			
				
					$url= 'https://www.readability.com/api/content/v1/parser?url='.$pieces[1].'&token=06c5ceff0071abbdae231cfb9b5b1ba6d8b2976e';
					
					
					
					if($title != $uudis->title){
						$checker=true;
						$finalurl = $url;
						//echo "There were no new posts";
						//echo $res['title']." ";
						/*echo "</br>    ";
						echo $uudis->title;
						echo "</br>";
						echo "</br>"; */
						
					}else{
						//echo "No news added to WP.";
						$link =  $uudis->link;
						$pieces = explode("url=", $link);
						if ($pieces[1] != 'http://zite.to/1BuUo2R'){	
							$url= 'https://www.readability.com/api/content/v1/parser?url='.$pieces[1].'&token=06c5ceff0071abbdae231cfb9b5b1ba6d8b2976e';
							//echo $uudis->title.' ';
							addnewpost($url,$USER,$PASS);
							//echo '<br>';
							//echo '<br>';
						}
						//addnewpost($url);
					//echo $uudis->title;
					
					}
				
			}
		}
	}else{
		echo 'uusi postitusi pole';
	}
		


	return true;

}
function addnewpost($finaleurl,$username,$password){
		//echo $finaleurl;
				$client = new IXR_Client(get_site_url().'/xmlrpc.php');
					
						
						echo $obj->title;
					
						//echo $username;
						//echo $password;
						$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
						$html = file_get_contents($finaleurl,false,$context);
						$obj = json_decode($html);
						$content['title'] = $obj->title;
						$content['authot'] = $obj->author;
						$content['categories'] = array("NewCategory","Nothing");
						//$content['description'] = $obj->content;
						
						$string = $obj->content;
						
						$siteurl = get_site_url();
						
						$string = preg_replace('|src="(.+)/(.+).jpg"|', 'src="'.$siteurl.'/wp-content/uploads/$2.jpg"', $string);
						$string =  preg_replace('|src="(.+)/(.+).png"|', 'src="'.$siteurl.'/wp-content/uploads/$2.png"', $string);
						$string =  preg_replace('|src="(.+)/(.+).gif"|', 'src="'.$siteurl.'/wp-content/uploads/$2.gif"', $string);
						//echo get_site_url();
					
						$string =str_replace('\"', '', $string);
						$content['description'] =$string;
						$content['custom_fields'] = array( array('key' => 'my_custom_fied','value'=>'yes') );
						$content['mt_keywords'] = array();
						if (!$client->query('metaWeblog.newPost','', $username,$password, $content, true)) 
						{
							die( 'Error while creating a new post' . $client->getErrorCode() ." : ". $client->getErrorMessage());  
						}
						$ID =  $client->getResponse(); 
						
						$url2=$finaleurl;
						$html = file_get_contents($url2);
						$doc = new DOMDocument();
						$doc->loadHTML($html);
						$tags = $doc->getElementsByTagName('img');
						foreach ($tags as $tag) {
							 
							$string = $tag->getAttribute('src');
							$string =str_replace('\"', '', $string);
							
								$string2 = preg_replace('|(.+)/(.+).jpg|', '$2.jpg',$string);
								$string2 =  preg_replace('|(.+)/(.+).png|', '$2.png', $string2);
								$string2 =  preg_replace('|(.+)/(.+).gif|', '$2.gif', $string2);
								
							    $img = rand().'.jpg';
								file_put_contents($img, file_get_contents($string));
								$myFile = $img;
								
								
								$fh = fopen($myFile, 'r');
								$fs = filesize($myFile);
								$theData = fread($fh, $fs);
								fclose($fh);  
								
								
								$nimed = explode(".com", $string);
								
								$params = array('name' => $string2, 'type' => 'image/jpg', 'bits' => new IXR_Base64($theData), 'overwrite' => false);
								$res = $client->query('wp.uploadFile',1, $username, $password, $params); 
										
								unlink($img);
							
							
						}
 
	
		
			
	}

	
	
	
	// Lets Create the WIDGET!!!
	class wp_my_plugin extends WP_Widget {

	// constructor
	function wp_my_plugin() {
		parent::WP_Widget(false, $name = __('My Widget', 'wp_widget_plugin') );

	}

	// widget form creation
	// widget form creation
	function form($instance) {

	// Check values
	if( $instance) {
		 $title = esc_attr($instance['title']);
		 $text = esc_attr($instance['text']);
		 $textarea = esc_textarea($instance['textarea']);
	} else {
		 $title = '';
		 $text = '';
		 $textarea = '';
	}
	?>

	<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'wp_widget_plugin'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Text:', 'wp_widget_plugin'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>" type="text" value="<?php echo $text; ?>" />
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('textarea'); ?>"><?php _e('Textarea:', 'wp_widget_plugin'); ?></label>
	<textarea class="widefat" id="<?php echo $this->get_field_id('textarea'); ?>" name="<?php echo $this->get_field_name('textarea'); ?>"><?php echo $textarea; ?></textarea>
	</p>
	<?php
	}

	// update widget
	function update($new_instance, $old_instance) {
		  $instance = $old_instance;
		  // Fields
		  $instance['title'] = strip_tags($new_instance['title']);
		  $instance['text'] = strip_tags($new_instance['text']);
		  $instance['textarea'] = strip_tags($new_instance['textarea']);
		 return $instance;
	}

	// display widget
	function widget($args, $instance) {
	   extract( $args );
	   // these are the widget options
	   $title = apply_filters('widget_title', $instance['title']);
	   $text = $instance['text'];
	   $textarea = $instance['textarea'];
	   echo $before_widget;
	   // Display the widget
	   echo '<div class="widget-text wp_widget_plugin_box">';

	   // Check if title is set
	   if ( $title ) {
		  echo $before_title . $title . $after_title;
	   }

	   // Check if text is set
	   if( $text ) {
		  echo '<p class="wp_widget_plugin_text">'.$text.'</p>';
	   }
	   // Check if textarea is set
	   if( $textarea ) {
		 echo '<p class="wp_widget_plugin_textarea">'.$textarea.'</p>';
	   }
	   echo '</div>';
	   echo $after_widget;
	}
}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("wp_my_plugin");'));
	
	
	
	
	
?>



