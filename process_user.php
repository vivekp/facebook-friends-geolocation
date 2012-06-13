<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>Schedule Visit to friends | Facebook App</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" type="text/css" media="all" href="style.css" />
    <script src="http://maps.google.com/maps?file=api&v=2&key=ABQIAAAAcFO8x0TN6e3Xw96lF6J_lxQIu6Cqdu4lKKxoe7OOJi78eqEEHxQfWz2kMVD0KlolUwYxS36IDll3PQ" type="text/javascript"></script> 
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
    <script src="utils.js" type="text/javascript"></script>
  </head>
  <body onload="bootstrap(false);">	<!-- false is mandatory to bootstarp only specific sections. -->


<?php
require 'src/facebook.php';

// App information and keys.
$appId = '243859085640463';
$appSecret = 'fa59a03922813ddaad47d4b686f175fe';
$appUrl = 'http://apps.facebook.com/friends-visit/';

// Create our Application instance.
$facebook = new Facebook(array(
  'appId'  => $appId,
  'secret' => $appSecret,
));

// Get User ID.
$user = $facebook->getUser();

// Get access token for this app.
$access_token = "";
session_start();

$code = $_REQUEST["code"];
if(empty($code)) {
     echo "Loading... <br /> ";
     echo "Creaing Application instance... Please be patient. <br/>";

     $_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
     $dialog_url = "http://www.facebook.com/dialog/oauth?client_id=" 
       . $appId . "&redirect_uri=" . urlencode($appUrl) . "&state="
       . $_SESSION['state'];
     echo("<script> top.location.href='" . $dialog_url . "'</script>");
     die();
}

$token_url = "https://graph.facebook.com/oauth/access_token?"
       . "client_id=" . $appId . "&redirect_uri=" . urlencode($appUrl)
       . "&client_secret=" . $appSecret . "&code=" . $code;

$response = file_get_contents($token_url);
$params = null;
parse_str($response, $params);
$access_token = $params['access_token'];

// Extract all the data from the user using Graph API calls.
if ($user) {
	try {
		// Proceed knowing we have a logged in user who's authenticated.

		$graph_url = "https://graph.facebook.com/me?access_token=".$access_token;
		$user_profile = json_decode(file_get_contents($graph_url));	
		echo "<div style='font-size:20px;color:blue;'>Welcome ".$user_profile->name." ! </div>";
		echo "<br/>";

		$graph_url = "https://graph.facebook.com/me/friends?access_token=".$access_token;
		$friends = json_decode(file_get_contents($graph_url));	
		$friends = $friends->data;

		echo "Total friends: ".sizeof($friends);
		if(sizeof($friends) > 50) {
			echo "<script>alert('You have too many friends. This app may not give results for you due to latency issues in retrieving data. Trying for 20 friends...');</script>";	
			$friends = array_slice($friends, 0, 20);	
		}

		$name = array($user_profile->name);
		$location = array($user_profile->location->name);
?>
		<p>You live at <?=$location[0]?>.</p>
		<div id="locations">	
			<div id="name0" style="display:none"><?=$name[0]?></div><div id="location0" style="display:none"><?=$location[0]?></div>
			<p>Your friends live in following cities: </p>
			<table border="0">
<?		

		$i = 1;
		foreach($friends as $friend) {
			$id = $friend->id;
			$graph_url = "https://graph.facebook.com/".$id."?access_token=".$access_token;
			$profile = json_decode(file_get_contents($graph_url));	
			$city = $profile->location->name;
			if(!empty($city)) {
				array_push($name, $profile->name);
				array_push($location, $city);
?>		
				<tr>
					<td><div id="name<?=$i?>"><?=($profile->name)?></div></td>
					<td>-></td>
					<td><div id="location<?=$i?>"><?=$city?></div></td>
				</tr>
<?				$i++;
			}
		}
?>
			</table>
		</div> <!--locations-->
<?		
	} catch (FacebookApiException $e) {
		error_log($e);
		$user = null;
	}
}

// Login or logout url will be needed depending on current user state.
if ($user) {
  $logoutUrl = $facebook->getLogoutUrl();
} else {
  $loginUrl = $facebook->getLoginUrl();
}

?>


    <?php if ($user): ?>
<!--	  <button type="button" value="visit" name="visit" onclick="bootstrap(true);">Schedule Visit</button>-->
	  <button type="button" value="map" name="map" onclick="bootstrap(true);initialize_map();">Show friends on map</button>
	  <div id= "message"></div>
<!--	  <div id="map_directions"></div> -->
	  <center><div id="map_canvas" style="width:520px; height:400px;"></div></center>
    <?php else: ?>
      <div>
        <a href="<?php echo $loginUrl; ?>">Login with Facebook</a>
      </div>
    <?php endif ?>

  </body>
</html>
