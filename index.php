<?
// Get the permissions for user_location and friends_location for the first time.

$appId = '243859085640463';
$page = 'http://apps.facebook.com/friends-visit/';
$auth_url = "http://www.facebook.com/dialog/oauth?client_id=" . $appId . "&redirect_uri=" . urlencode($page). "&scope=user_location,friends_location";

$signed_request = $_REQUEST["signed_request"];
list($encoded_sig, $payload) = explode('.', $signed_request, 2); 

$data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
if (empty($data["user_id"])) {
	echo("<script> top.location.href='" . $auth_url . "'</script>");
} else {
	include('process_user.php');
}

?>
