<?php
/*
	Fetch Vine popular page example.
	Disclaimer: I didn't test this at all. I hope you know what you're doing :)
*/
//Include Vine class
require_once("vine.php");

$vine_email = "vine@youremail.com";
$vine_pass = "password_here";

//Initialize new Vine object
$vine = new Vine();

if($vine->login($vine_email,$vine_pass) == true){
	//Logged in to Vine. Let's fetch the popular page
	$posts = array();//create an empty array to store the posts in
	
	//Loop through the 5 pages of the popular page
	for($i=0;$i<5;$i++){
		$popular_response = $vine->getPopular($i);
		if($popular_response != false){
			//Dump the Vine posts into the array to iterate through later
			$posts[] = $popular_response->data->records;
		}else{
			//Couldn't fetch that page. Uh oh. Let's just pretend it didn't happen because this is PHP...
		}
	}
	
	//Just make sure we actually have some posts in the array
	if(count($posts) >= 1){
		foreach($posts as $post){
			echo $post->thumbnailUrl . "\n";//Print out the thumnailUrl for the Vine
			echo $post->shareUrl . "\n";//Print out the http://vine.co/v/* url for the Vine
		}
	}else{
		//Shit..why is this array empty? =[
	}
	
}else{
	echo "Could not login to Vine.";
}
?>