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
	$images = $wpdb->get_results("SELECT img_url, redirect_url FROM vindowshop");
?>
<script src="http://code.jquery.com/jquery-1.10.2.min.js"></script>
<script type="text/javascript" >


var img_array = [<?php 
						foreach($images as $image){
							echo "'".$image->img_url."',";
						}
						echo "'x'";
					?>];

var redirect_url_array = [<?php 
						foreach($images as $image){
							echo "'".$image->redirect_url."',";
						}
						echo "'x'";
					?>];

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

function select_gender(el,link){
	var prev_html = el.parentNode.innerHTML;
	var new_html = "<a onclick='javascript:get_result(this.innerHTML,\""+link+"\")'>Men Topwear</a>&nbsp&nbsp<a onclick='javascript:get_result(this.innerHTML,\""+link+"\")'>Women Topwear</a>";
	el.parentNode.innerHTML = prev_html + "<br>" + new_html + "<div id='vindowshop_result'></div>";
}

function get_result(val,link){
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    var data = jQuery.parseJSON(xmlhttp.responseText);
    var new_html = "";
    for(var i=0;i<data.length;i++){
    	new_html += "<img style='padding:5px;max-height:150px; max-width:100px' src='http://www.beta.vindowshop.com"+data[i]+"'>";
    }
    alert(new_html);
    document.getElementById("vindowshop_result").innerHTML=new_html;
    }
  }
xmlhttp.open("POST","http://vindowshop.com:8080/fetchprod",true);
xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
xmlhttp.send('["'+link+'","'+val+'",600,600,0,0,100,100]');
}


function lights_in(el){
	el.setAttribute('style','opacity:1.0;position: absolute; z-index: 1; top: 15px; right: 30px; max-height:40px');
}

function lights_out(el){
	el.setAttribute('style','opacity:0.4;position: absolute; z-index: 1; top: 15px; right: 30px; max-height:40px');
}


function inArray(needle, haystack) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle) return i;
    }
    return false;
}
</script>
<?php
}
add_action( 'wp_footer', 'my_action_javascript' );
?>	