
<?php
global $wpdb;


// Last line of metadata, where id is 9999

$wpdb->insert( 
	'wp_postmeta', 
	array( 
		'meta_id' => 9999, 
		'post_id' => 9999,
		'meta_key' => '_readability',
		'meta_value' => 'www.google.com;1;proov;proov"'
		),
	array( 
		'%d', 
		'%d',
		'%s',
		'%s'
		
	) 
);
$results = $wpdb->get_results( 'SELECT meta_value FROM wp_postmeta where meta_id=9999', OBJECT );
$table = explode(";", $results[0]->meta_value);

?>
<div class="wrap">
    <?php    echo "<h2>" . __( 'Readability Parser', 'feed-trdom' ) . "</h2>"; ?>
     
    <form name="oscimp_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">

        <?php    echo "<h4>" . __( 'Readability Parser Settings', 'feed_trdom' ) . "</h4>"; ?>
        <p><?php _e("Feed URL: " ); ?><input type="text" value="<?php echo $table[0]; ?>" name="url" size="20"><?php _e(" ex: http://www.readability.com/YourUserName/latest/feed" ); ?></p>
        <p><?php _e("Update interval: " ); ?><input type="text" value="<?php echo $table[1]; ?>" name="interval" size="20"><?php _e(" ex: 1 eqals every minute 60 equals every hour" ); ?></p>
        <p><?php _e("WP user: " ); ?><input type="text" value="<?php echo $table[2]; ?>" name="user"  size="20"><?php _e("Your username" ); ?></p>
        <p><?php _e("WP pass: " ); ?><input type="password" value="<?php echo $table[3]; ?>" name="pass"  size="20"><?php _e("Your password" ); ?></p>
         
     
        <p class="submit">
        <input type="submit" name="Submit" value="<?php _e('Update Options', 'feed_trdom' ) ?>" />
        </p>
    </form>
</div>

<?php 
$results = $wpdb->get_results( 'SELECT * FROM wp_readability', OBJECT );
if(isSet($_POST['url']) and isSet($_POST['interval']) and isSet($_POST['user']) and isSet($_POST['pass'])){
	$uued = array($_POST['url'],$_POST['interval'],$_POST['user'],$_POST['pass']);
	$semi = implode(";", $uued);
	
	
	//echo $_POST['url']; 
	//Update table... 
	$wpdb->update( 
	'wp_postmeta', 
	array( 
		'meta_value' => $semi,	// string
	), 
	array( 'meta_id' => 9999 ), 
	array( 
		'%s'
	), 
	array( '%d' ) 
);
	?>
	<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
	<?php
}

add_action('my_hourly_event', 'do_this_hourly');
my_activation();
function my_activation() {
	$string=false;
	if ( !wp_next_scheduled( 'my_hourly_event' ) ) {
		global $wpdb;
		$results = $wpdb->get_results( 'SELECT meta_value FROM wp_postmeta where meta_id=9999', OBJECT );
		$table = explode(";", $results[0]->meta_value);
		$refresh_time = $table[1];
		$minut = 60;
		$timer = $minut * $refresh_time;
		//echo $timer;
		wp_schedule_event( time() + $timer, 'minute', 'my_hourly_event'); //siin hetkel on iga minuti tagant  võtab hetke aja milrefresh tehti ja lisab sellele 60 sekki juurde
       // echo $striing = 'gotit';
		do_this_hourly(); 
			
        
	}else{
		$timestamp = wp_next_scheduled( 'my_hourly_event' ); 
		//print_r( $schedule);
		if(time()> $timestamp ){
				 // siit võtab selle schedle  aja   
			$timestamp = wp_next_scheduled( 'my_hourly_event' ); 
			wp_unschedule_event( $timestamp,'my_hourly_event' );
			my_activation();
		}else{
			
			echo '<div class="error"><p><strong>It will check for new posts if the CronJob has finished (update interval).</strong></p></div>';
		}
    }

	
}


function do_this_hourly() {
			global $wpdb;
			$results = $wpdb->get_results( 'SELECT * FROM wp_readability', OBJECT );
			
			
			echo "<div class='updated'><p><strong>Posts added (if there were any). Cronjob has started</strong></p></div>";
			global $wpdb;
			$results = $wpdb->get_results( 'SELECT * FROM wp_readability', OBJECT );
			//print_r($results);
			
			$results = $wpdb->get_results( 'SELECT meta_value FROM wp_postmeta where meta_id=9999', OBJECT );
			$table = explode(";", $results[0]->meta_value);
			addposts($table[2], $table[3], $table[0]);
}

function addposts($USER, $PASS,$LINK){
	
    libxml_disable_entity_loader(false);
	libxml_use_internal_errors(true);
	ini_set('max_execution_time', 500);
	
	$abi=true;

	
	require('IXR_Library.php');  
    
	$client = new IXR_Client(get_site_url().'/xmlrpc.php');
    $USER = $USER;
    $PASS = $PASS;
	
	
	$juur=simplexml_load_file($LINK);	
	
	$all_posts = 1000;
	$params = array(0,$USER,$PASS, $all_posts);
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
				
					$url= 'https://www.readability.com/api/content/v1/parser?url='.$pieces[1].'&token=06c5ceff0071abbdae231cfb9b5b1ba6d8b2976e';

					if($title != $uudis->title){
						$checker=true;
						$finalurl = $url;

						
					}else{

						$link =  $uudis->link;
						$pieces = explode("url=", $link);
						if ($pieces[1] != 'http://zite.to/1BuUo2R'){	
							$url= 'https://www.readability.com/api/content/v1/parser?url='.$pieces[1].'&token=06c5ceff0071abbdae231cfb9b5b1ba6d8b2976e';
							addnewpost($url,$USER,$PASS);

						}

					}
		}
	}
	}else{
		echo 'uusi postitusi pole';
	}
	return true;

}
//Adding a new post 
function addnewpost($finaleurl,$username,$password){

				$client = new IXR_Client(get_site_url().'/xmlrpc.php');
					
						//If username and password match, then it will continue.
						$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
						$html = file_get_contents($finaleurl,false,$context);
						$obj = json_decode($html);
						$content['title'] = $obj->title;
						$content['authot'] = $obj->author;
						$content['categories'] = array("NewCategory","Nothing");

						$string = $obj->content;
						
						$siteurl = get_site_url();
						
						$string = preg_replace('|src="(.+)/(.+).jpg"|', 'src="'.$siteurl.'/wp-content/uploads/$2.jpg"', $string);
						$string =  preg_replace('|src="(.+)/(.+).png"|', 'src="'.$siteurl.'/wp-content/uploads/$2.png"', $string);
						$string =  preg_replace('|src="(.+)/(.+).gif"|', 'src="'.$siteurl.'/wp-content/uploads/$2.gif"', $string);

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
	?>


