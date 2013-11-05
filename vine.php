<?php
class Vine{
	
	public $endpoint = "https://api.vineapp.com/";
	public $useragent = "com.vine.iphone/1.1 (unknown, iPhone OS 6.1.3, iPad, Scale/1.000000)";
	
	//POST headers
	public $post_headers = array(
				"Host: api.vineapp.com",
				"Proxy-Connection: keep-alive",
				"Accept: */*",
				"Accept-Language: nb, en, fr, de, ja, nl, it, es, pt, pt-PT, da, fi, sv, ko, zh-Hans, zh-Hant, ru, pl, tr, uk, ar, hr, cs, el, he, ro, sk, th, id, ms, en-GB, ca, hu, vi, en-us;q=0.8",
				"Connection: keep-alive",
			);
	
	//GET headers
	public $get_headers = array(
				"Host: api.vineapp.com",
				"Proxy-Connection: keep-alive",
				"Accept: */*",
				"Accept-Language: nb, en, fr, de, ja, nl, it, es, pt, pt-PT, da, fi, sv, ko, zh-Hans, zh-Hant, ru, pl, tr, uk, ar, hr, cs, el, he, ro, sk, th, id, ms, en-GB, ca, hu, vi, en-us;q=0.8",
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
		$search_response = $this->api("users/search/" . urlencode($username) . "?size=100&page=" . trim($page),"GET");
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
		//var_dump($feed_response);
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
	
}//End of Class
?>
