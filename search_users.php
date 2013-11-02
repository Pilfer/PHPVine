<?php
/*
	Search for users on Vine
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
    //Simple example on scraping usernames based on username keyword.
    $username = "John";//Username to search for
    $users[] = array();//An array to store all the usernames in
    $pages = 5;//Number of pages to fetch
    $total_count = NULL;//This will hold the total count of search results
    
    //Loop through pages
    for($i=0;$i<=$pages;$i++){
        $results = $vine->searchUser($username,$i);
        if($results != false){
            $total_count = $results->data->count;
            foreach($results->data->records as $user){
                $users[] = $user;
            }
        }else{
            //The search function failed...weird...
        }
    }
    echo "\n<br/>Search results for username: <b>" . $username . "</b>";
    echo "\n<br/>Count of users we fetched: " . count($users);
    echo "\n<br/>Total count of search results available to us: " . $total_count . "<br/>Results:<hr/>\n\n";
    
    foreach($users as $user){
		//Some examples of user response object
        //$user->username
        //$user->userId
        //$user->avatarUrl
		
        //A good way to check to see if the user is "active" is if they've got a real display pic or the default one
        if($user->avatarUrl != "https://s3.amazonaws.com/vines/avatars/default.png"){
            echo $user->userId . " has a real profile picture.<br/>\n";
        }else{
            echo $user->userId . " has a default profile picture.<br/>\n";
        }
        
    }
}else{
	echo "Couldn't login to Vine.";
}