<?php 

/*
Plugin Name: vindowshop
Description: Extracting images and send it to vindowshop server.
Version: 1.0
Author: Sanborn
License: GPL2
*/

require_once('vindowshop-php-api-wrapper/Vindowshop.php');

function send_image_to_vindowshop() {
	$str = $_POST['content'];
	$post_id = $_POST['post_ID'];
	$instance = new Vindowshop('123456789','987654321');
	$instance->apiAuth();
	$instance->sendImages($str);	
}

add_action( 'save_post', 'send_image_to_vindowshop' );

function my_action_javascript() {
	$instance = new Vindowshop('123456789','987654321');
	$instance->apiAuth(); 
	echo $instance->createJS();
}

add_action( 'wp_footer', 'my_action_javascript' );
?>	
