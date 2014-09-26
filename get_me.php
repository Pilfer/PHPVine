<?php
/*
	Fetch your account info on Vine
	Disclaimer: I didn't test this at all. I hope you know what you're doing :)
*/
//Include Vine class
require_once("vine.php");

$vine_email = "vine@youremail.com";
$vine_pass = "password_here";

//Initialize new Vine object
$vine = new Vine();

//Login to Vine.
if($vine->login($vine_email,$vine_pass) == true){
	//Fetch info on currently logged in user
	$user_info = $vine->getMe();
	//Make sure that shit worked
	if($user_info != false){
		//Are we in a cavern? CUZ WE ECHOIN' LIKE A MOFO
		echo "Hey there, " . $user_info->data->username . "! You're logged in and your Vine userId is " . $user_info->data->userId . "!";
		/*
		//All other data available in the response is:
		$user_info->data->followerCount;
		$user_info->data->includePromoted;
		$user_info->data->userId;
		$user_info->data->private;
		$user_info->data->likeCount;
		$user_info->data->postCount;
		$user_info->data->explicitContent;
		$user_info->data->blocked;
		$user_info->data->verified;
		$user_info->data->avatarUrl;
		$user_info->data->authoredPostCount;
		$user_info->data->location;
		$user_info->data->facebookConnected;
		$user_info->data->email;
		$user_info->data->username;
		$user_info->data->description;
		$user_info->data->followingCount;
		$user_info->data->twitterConnected;
		$user_info->data->blocking;
		$user_info->data->twitterId;
		$user_info->data->following;
		$user_info->data->repostsEnabled;
		*/
	}else{
		echo "Couldn't fetch info your account, bro.";
	}
}else{
	echo "Could not login to Vine.";
}
