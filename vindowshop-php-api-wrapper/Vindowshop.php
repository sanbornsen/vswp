<?php 

/**
* THis is a wrapper class of Vindowshop.
*/
class Vindowshop
{
	//define version
	const version = 0.01;

	//define author
	const author = 'sanborn';	

	//define Vindowshop api baseurl
	const API_URL = "http://vindowshop.com:9999/";


	//app_id and and api key provided by Vindowshop
	protected $app_id = null;
	protected $api_key = null;

	//This token will issued from vindowshop server while authorising the api
	protected $app_token = null;

    /**
	    * Default constructor
	    * @param string $appId for Vindowshop application
	    * @param string $apiKey for vindowshop application
    */

    function __construct($appId, $apiKey){
    	$this->setAppId($appId);
    	$this->setApiKey($apiKey);
    }

	/**
		* Get the version of the API wrapper.
		* @return string Version of the API wrapper.
	*/
		public function getVersion(){
			return self::version;
		}

	/**
		* Initializing user app ID
		* @param string $appId for Vindowshop application
	*/

		private function setAppId($appId){
			$this->app_id = (string)$appId;
		}

	/**
		* Initializing user api Key
		* @param string $apiKey for Vindowshop application
	*/

		private function setApiKey($apiKey){
			$this->api_key = (string)$apiKey;
		}


	/**
		* Authenticate the application.
		* @return array PHP array of the JSON response.
	*/
		public function apiAuth(){
			$response = $this->apiRequest('auth/', $data = array('appId' => $this->app_id, 'apiKey' => $this->api_key));
			$array = json_decode($response, true);
			if(!$array['error']){
				$this->app_token = $array['token'];
			}
			else{
				throw new Exception($array['error']);
			}
		}

	/**
		* This method helps to get the authorization information
		* @return array of appId and appToken
	*/
		protected function getAuthInfo(){
			if($this->app_token)
				return array('appId'=>$this->app_id, 'appToken'=>$this->app_token);
			else
				return null;
		}

	/**
		* Extracting and Sending image for processing from a post
		* @param string $string The post where the images will be extracted from
	*/
		public function sendImages($string){
			$imgs = $this->getImagesUrls($string);
			foreach($imgs as $image){
				$images[] = rtrim($image,'\\');
			}
			$data = array('from_wrapper',$images);			
			$response = $this->apiRequest('',$data);
		}
			
	/**
		* Getting all image urls from the string
		* @param string $string The post where the image urls will be extracted from
		* @return array $matches will return the all the image urls containing in a string
	*/

		private function getImagesUrls($string){
			$imageTags = $this->getImageTags($string);
			$imageTags = implode(" ", $imageTags);
			$regex = '/https?\:\/\/[^\" ]+/i';
			preg_match_all($regex, $imageTags, $matches);
			return ($matches[0]);
		}

		
	/**
		* Getting all image tags in an array from the string
		* @param string $string The post where the image tagss will be extracted from
		* @return array $matches will give all the image tags in a string i.e. <img> tags 
	*/

		private function getImageTags($string){
			$regex = '/<img\s+.*?src=[\"\']?([^\"\' >]*)[\"\']?[^>]*>/i';
			preg_match_all($regex, $string, $matches);
			return ($matches[0]);
		}

	/**
		* Create the absolute path for the request.
		* @param string $url The base URL (Here it is used by API_URL)
		* @param string $path The relative path.
		* @return string $url.$path the entire path to send request
	*/
		private function buildPath($url, $path){
			return $url . $path;
		}


	/**
		* This method send an image url and required parameters
		* recieve the matches found
		* @param string $url of the image
		* @return Response
	*/
		public function getMatches($url){
			$data = array($url,'Women Topwear',292,438,136,189,64,64);
			$response = $this->apiRequest('fetchprod',$data);
			die(var_dump($response));
		}

	/**
		* This method helps users to get images uploaded by them only
		* @param None
		* @return $response json encoded string containing all the urls o the immages uploaded by current appId
	*/

		public function getMyImages(){
			$blank_array = array();
			$response = $this->apiRequest('getmyimages/',$blank_array); 
			return $response;
		}

	/**
		* This methos helps user to create supprotng DOM of vindowshop on their website
		* @return $html the DOM to be added
	*/

		public function createJS(){
			$html = '<div id="basic-modal-content"></div>';
			$html .= '<script src="http://code.jquery.com/jquery-1.10.2.min.js"></script>';
			$html .= '<script type="text/javascript" src="https://dl.dropboxusercontent.com/u/107817493/vindowshop/js/jquery.js"></script>';
			$html .= '<script type="text/javascript" src="https://dl.dropboxusercontent.com/u/107817493/vindowshop/js/jquery.simplemodal.js"></script>';
			$html .= '<script type="text/javascript" src="https://dl.dropboxusercontent.com/u/107817493/vindowshop/js/vindowshop.js"></script>';
			$html .= '<script type="text/javascript" >';
			$html .= 'var img_array = '.str_replace('"', "'", $this->getMyImages()).';';
			$html .= 'var img = document.body.getElementsByTagName("img");';
			$html .= 'var i = 0;var image_url=[];';
			$html .= 'while (i < img.length) { var pos = inArray(img[i].src, img_array);image_url.push(\'"\'+img[i].src+\'"\');';
			$html .= 'if(pos != \'not found\'){var new_html = "<a href=\'javascript:void(0)\'><img onmouseover=\'javascript:lights_in(this)\' onclick=\'javascript:select_gender(this,"+addquote(img[i].src)+")\' onmouseout=\'javascript:lights_out(this)\' style=\'opacity: 0.4; position: absolute; z-index: 1; top: 15px; right: 30px; max-height:40px\' src=\'http://www.f6s.com/pictures/profiles/17/1641/164049_th2.jpg\'></a>";';
			$html .= 'img[i].parentNode.setAttribute(\'style\',\'display: inline-block;position: relative;\');';
			$html .= 'img[i].parentNode.innerHTML = img[i].parentNode.innerHTML+new_html;';
			$html .= '}i++;} function addquote(str){return \'"\'+str+\'"\'}</script>';
			$html .= '<link rel="stylesheet" href="https://dl.dropboxusercontent.com/u/107817493/vindowshop/css/basic.css" type="text/css" media="all" />';
			$html .= '<link rel="stylesheet" href="https://dl.dropboxusercontent.com/u/107817493/vindowshop/css/VindowShop.css" type="text/css" media="all" />';
			return $html;
		}


	/**
		* Send request via this method
		* @param string $path The path to send the request
		* @param array $data Data to send to the api
		* @return json $result Json the reply from the request
	*/

		private function apiRequest($path, array $data = null){
			$path = (string) $path;
			$data = (array) $data;
			$data[] = $this->getAuthInfo();
			$url = 	$this->buildPath(self::API_URL,$path);
			$params = json_encode($data);
			
			$ch = curl_init($url);

			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);

			curl_close($ch);

			return $result;
		}
		
	}


// Testing

	$instance = new Vindowshop('123456789','987654321');
	$instance->apiAuth();
//$instance->sendImages($string); // String is the entire post including image urls, just passing the entire post will make it work
$instance->getMyImages(); // Get all the image of appId 123456789
$htm = $instance->createJS(); // Creating DOM via this function (Normally this function should be called in the footer section)

?>
