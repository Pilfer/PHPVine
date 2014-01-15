<?php

//include Amazon S3 library bucket for upload
//Found here but modified slightly to return the versionId in the response headers: https://github.com/tpyo/amazon-s3-php-class
require_once("S3.php");

class Vine{
	
	public $endpoint = "https://api.vineapp.com/";
	public $useragent = "iphone/106 (iPhone; iOS 7.0.4; Scale/2.00)";
	
	//for bucket uploads
	public $s3_key = "AKIAJL2SSORTZ5AK6D4A";
	public $s3_secret = "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";
	
	public $s3_bucket = "vines";
	public $s3_url = "http://vines.s3.amazonaws.com/";
	
	//POST headers
	public $post_headers = array(
				"Accept: */*",
				"Accept-Language: en;q=1, fr;q=0.9, de;q=0.8, zh-Hans;q=0.7, zh-Hant;q=0.6, ja;q=0.5",
				"Connection: keep-alive",
				"X-Vine-Client: ios/1.4.7",
				"Content-Type: application/json"
			);
			
	public $upload_headers = array(
				"Accept: */*",
				"Accept-Language: en;q=1, fr;q=0.9, de;q=0.8, zh-Hans;q=0.7, zh-Hant;q=0.6, ja;q=0.5",
				"Connection: keep-alive",
				"X-Vine-Client: ios/1.4.7",
				"Content-Type: application/json"
			);
	
	//GET headers
	public $get_headers = array(
				"Host: api.vineapp.com",
				"Proxy-Connection: keep-alive",
				"Accept: */*",
				"Accept-Language: en;q=1, fr;q=0.9, de;q=0.8, zh-Hans;q=0.7, zh-Hant;q=0.6, ja;q=0.5",
				"Connection: keep-alive",
			);
	
	//Session ID for Vine headers (pretty much Vine's gay access_token if they used oAuth)
	public $session_id;
	public $username;
	public $userId;
	public $email;
	public $password;
	
	
	//Just a string that the GET/POST functions use if you decide to enable a proxy. ip:port is the format you'll use.
	public $proxy;
	
	public function __construct(){
		//echo "Initialized Vine Class";
	}
	
	
	//Generate a v4 UUID - should be used when uploading to Amazon S3
	public function genuuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0x0fff ) | 0x4000, mt_rand( 0, 0x3fff ) | 0x8000, mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ));
	}

	//Make a POST request
	public function post($url,$data=''){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if(!empty($this->session_id)){
			$headers = $this->post_headers;
			$headers[] = "vine-session-id: " . $this->session_id;
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}else{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->post_headers);
		}

		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		if(isset($this->proxy) && !empty($this->proxy)){
			curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
		}
		$result = curl_exec($ch);
		curl_close($ch);
		if($result){
			return $result;
		}else{
			return false;
		}
	}
	
	//Make a GET request
	public function get($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if(!empty($this->session_id)){
			$headers = $this->get_headers;
			$headers[] = "vine-session-id: " . $this->session_id;
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}else{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->get_headers);
		}
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
		if(isset($this->proxy) && !empty($this->proxy)){
			curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
		}
		$result = curl_exec($ch);
		curl_close($ch);
		if($result){
			return $result;
		}else{
			return false;
		}
	}
	
	//Simple Vine API function.
	public function api($path,$method,$data=''){
		switch($method){
			case "GET":
				$response = $this->get($this->endpoint . $path);
				if($response != false){
					$json_response = json_decode($response);
					if($json_response){
						return $json_response;
					}else{
						return false;
					}
				}
				break;
			case "POST":
				$response = $this->post($this->endpoint . $path,$data);
				if($response != false){
					$json_response = json_decode($response);
					if($json_response){
						return $json_response;
					}else{
						return false;
					}
				}
				break;
		}
	}
	
	
	//Login to Vine
	public function login($email,$password){
		$data = array(
			"username" => $email,
			"password" => $password
		);
		$login_response = $this->api("users/authenticate","POST",$data);
		if($login_response != false){
			if($login_response->success == true){
				$this->email = $email;
				$this->password = $password;
				$this->username = $login_response->data->username;
				$this->userId = $login_response->data->userId;
				$this->session_id = $login_response->data->key;
				return true;
			}else{
				return false;
			}	
		}
	}
	
	//Follow user
	public function follow($userid){
		$follow_response = $this->api("users/" . trim($userid) . "/followers","POST");
		if($follow_response != false){
			if($follow_response->success == true){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	//Likes a specific Vine
	public function like($postId){
		$like_response = $this->api("posts/" . trim($postId) . "/likes","POST");
		if($like_response != false){
			if($like_response->success == true){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	//Revines a specific Vine
	public function revine($postId){
		$revine_response = $this->api("posts/" . trim($postId) . "/repost","POST");
		if($revine_response != false){
			if($revine_response->success == true){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	//Returns info on the current logged-in user.
	//Will return false if failed or if user isn't logged in.
	public function getMe(){
		$me_response = $this->api("users/me","GET");
		if($me_response != false){
			if($me_response->success == true){
				return $me_response;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	//Fetches popular page. Note: Pages go from 1-5.
	public function getPopular($page){
		$popular_response = $this->api("timelines/popular?size=100&page=" . trim($page),"GET");
		if($popular_response != false){
			if($popular_response->success == true){
				return $popular_response;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	//Searches username
	public function searchUser($username,$page){
		$search_response = $this->api("users/search/" . urlencode(trim($username)) . "?size=100&page=" . trim($page),"GET");
		if($search_response != false){
			if($search_response->success == true){
				return $search_response;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	//Search for tags
	public function searchTags($tag,$page){
		$tag_response = $this->api("timelines/tags/" . urlencode(trim($tag)) . "?size=100&page=" . trim($page),"GET");
		if($tag_response != false){
			if($tag_response->success == true){
				return $tag_response;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	//Fetch users' feed
	public function getUserFeed($uid,$page){
		$feed_response = $this->api("timelines/users/" . trim($uid) . "?page=" . trim($page),"GET");
		if($feed_response != false){
			if($feed_response->success == true){
				return $feed_response;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	//Fetch a user
	public function getUser($uid){
		$user_response = $this->api("users/profiles/" . trim($uid),"GET");
		if($user_response != false){
			if($user_response->success == true){
				return $user_response;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	//uploads an avatar to amazon s3
	//full path to file, name of file on s3 .jpg
	//200x201
	public function uploadAvatar($file,$remote_filename){
		$s3 = new S3($this->s3_key,$this->s3_secret);
		$k = $s3->putObjectFile($file, $this->s3_bucket, "avatars/" . $remote_filename);
		if($k != false){
			$version_id = $k;
			$url = $this->s3_url . "avatars/" . $remote_filename . "?versionId=" . $version_id;
			return $url;
		}else{
			return false;
		}
	}
	
	//uploads thumbnail to amazon s3
	//full path to file, name of file on s3 + .mp4.jpg
	//480x480
	public function uploadThumbnail($file,$remote_filename){
		$s3 = new S3($this->s3_key,$this->s3_secret);
		$k = $s3->putObjectFile($file, $this->s3_bucket, "thumbs/" . $remote_filename);
		if($k != false){
			$version_id = $k;
			$url = $this->s3_url . "thumbs/" . $remote_filename . "?versionId=" . $version_id;
			return $url;
		}else{
			return false;
		}
	}
	
	//uploads video to amazon s3
	//full path to file, name of file on s3 + .mp4
	//480x480
	public function uploadVideo($file,$remote_filename){
		$s3 = new S3($this->s3_key,$this->s3_secret);
		$k = $s3->putObjectFile($file, $this->s3_bucket, "videos/" . $remote_filename);
		if($k != false){
			$version_id = $k;
			$url = $this->s3_url . "videos/" . $remote_filename . "?versionId=" . $version_id;
			return $url;
		}else{
			return false;
		}
	}
	
	
	//configure the vine - parameters below
	/*
	$params = array(
		"thumbnailUrl" => $this->uploadThumnail(..),
		"videoUrl" => $this->uploadVideo(..),
		"description" => "Caption Here!",
		"entities" => "[]"
	);
	
	NOTE: It must be properly formatted and indented JSON when you post it.
	Use JSON_PRETTY_PRINT or the equivelent function if you don't have PHP 5.5
	Like so: $params = json_encode($params,JSON_PRETTY_PRINT);
	*/
	public function configureVine($params){
		$config_response = $this->api("posts","POST", $params);
		if($config_response != false){
			if($config_response->success == true){
				return $config_response;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	
}//End of Class
?>
