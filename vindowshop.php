<?php 

/*
Plugin Name: vindowshop
Description: Extracting images and send it to vindowshop server.
Version: 1.0
Author: Sanborn
License: GPL2
*/

function send_image_to_vindowshop() {
	$req_url = "http://vindowshop.com:8080";
	create_table_if_not_exist();	
	global $wpdb;
	$str = $_POST['content'];
	$post_id = $_POST['post_ID'];
	$urls = getUrls($str); // getting image url from the string
 	$unique_urls = array();
 	foreach($urls as $url){
 		$unique_url = substr($url,0,-1);
 		$sql = "SELECT * FROM vindowshop WHERE img_url LIKE '".$unique_url."'";
 		$results = $wpdb->get_results($sql); // checking whether the image is already taken or not
 		if(!sizeof($results)){
 			$rows_affected = $wpdb->insert( 'vindowshop', array('img_url' => $unique_url, 'post_id'=> $post_id));
    		$unique_urls[] = $unique_url;
    	}
    }

    // Sending request to the vindowshop server
    if(sizeof($unique_urls)){
    	$send_req = array('1234	',$unique_urls);
    	$params = json_encode($send_req);
   		$ch = curl_init($req_url);

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch); // recieving the result from the server

		curl_close($ch);
		$result_array = json_decode($result);
		/*
		This portion will be changed when I'll recieving a subset of the urls I am sending at $result. It'll be something like

		for($i = 0; $i<sizeof($result_array); $i++){
			$wpdb->query('UPDATE vindowshop SET status = 1 WHERE img_url LIKE "'.$result_array[$i].'"');
		} 
		*/

		for($i=0; $i<sizeof($unique_urls); $i++){
			// Updating the redirect url for corresponding image
			$wpdb->query('UPDATE vindowshop SET redirect_url = "'.$result_array[$i].'" WHERE img_url LIKE "'.$unique_urls[$i].'"'); 
		}
	}
    
}

/* Getting image tag from the post content*/
function getimgTag($string){
	$regex = '/<img\s+.*?src=[\"\']?([^\"\' >]*)[\"\']?[^>]*>/i';
	preg_match_all($regex, $string, $matches);
	return ($matches[0]);
}

/* Getting image src from the post content*/
function getUrls($string){
	$string = getimgTag($string); // getting all the <img> in the string
	$string = implode(" ", $string);
    $regex = '/https?\:\/\/[^\" ]+/i';
    preg_match_all($regex, $string, $matches);
    return ($matches[0]);
}
/* This is a function to create a table if it's not exist*/
function create_table_if_not_exist(){
	$sql = "CREATE TABLE IF NOT EXISTS `vindowshop` (
  			`img_id` int(255) NOT NULL AUTO_INCREMENT,
  			`img_url` text NOT NULL,
  			`redirect_url` text NOT NULL,
  			`post_id` int(225) NOT NULL,
  			`status` int(11) NOT NULL,
  			PRIMARY KEY (`img_id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

add_action( 'save_post', 'send_image_to_vindowshop' );



function my_action_javascript() {
	global $wpdb;
	/* Lateron this code will be 
		$images = $wpdb->get_results("SELECT img_url FROM vindowshop WHERE status = 1");
	*/
	$images = $wpdb->get_results("SELECT img_url,redirect_url FROM vindowshop");
?>
<!-- Modal content goes here -->
<div id="basic-modal-content"></div>
<script src="http://code.jquery.com/jquery-1.10.2.min.js"></script>
<script type='text/javascript' src='<?=plugin_dir_url(__FILE__)?>js/jquery.js'></script>
<script type='text/javascript' src='<?=plugin_dir_url(__FILE__)?>js/jquery.simplemodal.js'></script>
<script type='text/javascript' src='<?=plugin_dir_url(__FILE__)?>js/vindowshop.js'></script>
<script type="text/javascript" >
var img_array = [<?php 
						foreach($images as $image){
							echo "'".$image->img_url."',";
						}
						echo "'x'";
					?>];

/*var redirect_url_array = [<?php 
						foreach($images as $image){
							echo "'".$image->redirect_url."',";
						}
						echo "'x'";
					?>];*/
var img = document.body.getElementsByTagName("img");
var i = 0;
while (i < img.length) {
	var pos = inArray(img[i].src, img_array);
	
    if(pos){
    	var new_html = "<a href='javascript:void(0)'><img onmouseover='javascript:lights_in(this)' onclick='javascript:select_gender(this,\""+img[i].src+"\")' onmouseout='javascript:lights_out(this)' style='opacity: 0.4; position: absolute; z-index: 1; top: 15px; right: 30px; max-height:40px' src='http://www.f6s.com/pictures/profiles/17/1641/164049_th2.jpg'></a>";
    	img[i].parentNode.setAttribute('style','display: inline-block;position: relative;');
    	img[i].parentNode.innerHTML = img[i].parentNode.innerHTML+new_html;
    }
    i++;
}
</script>

<?php
}
wp_register_style( 'namespace', plugin_dir_url(__FILE__).'css/VindowShop.css' ); 
wp_enqueue_style('namespace');
wp_register_style( 'modal', plugin_dir_url(__FILE__).'css/basic.css' ); 
wp_enqueue_style('modal');
add_action( 'wp_footer', 'my_action_javascript' );
?>	