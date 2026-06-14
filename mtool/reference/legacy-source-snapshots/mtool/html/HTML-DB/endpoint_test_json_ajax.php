<?php
require '/srv/legacy/composer/vendor/autoload.php';

$base_dir = __DIR__ . "/";
$MAX_INCLUDE_CONFIG_TRY = 10;
for($try_index = 0 ; $try_index <= $MAX_INCLUDE_CONFIG_TRY ; $try_index++) {
	$site_configfile = $base_dir . "config.php";
	if (is_file($site_configfile)) {
		include_once($site_configfile);
		break;
	}
	$base_dir .= "../";
}
include_once("/srv/legacy/www/mtool_lib/lib_commonheader.php");
include_once("/srv/legacy/www/mtool_lib/dbclasses/autoload_mtool.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_db.php");
include_once("/srv/legacy/www/mtool_lib/lib_path_on_top.php");

class EndpointTestResult
{
	public $Result = NULL;
	public $_status = "NG";
	public $Message = "";
}
$result = new EndpointTestResult();

if ($matsuesoft_login_token_id == "") {
	?>
	<h3><?php print getres("NOTICE_PLEAE_LOGIN_BEFORE_USE"); ?></h3>
	<?php
} else {
	if ($matsuesoft_login_user_email_verified == false) {
		?>
		<h3><?php print getres("NOTICE_PLEAE_AUTHENTICATE_EMAIL_BEFORE_USE"); ?></h3>
		<?php
		
		include_once("/srv/legacy/www/mtool_lib/email_verification_include.php");
		
		show_message_about_email_verification(true);
		
	} else {
		$ProjectPID = trim(GetParam("ProjectPID"));
		if (CheckIfLoginUserIsUserAndOutputMessage($ProjectPID, $matsuesoft_login_token_id)) {
			// ここから上はTemplateと共通
			
			$ProxyURL = trim(GetParam("ProxyURL"));
			$POST_JSON = trim(GetParam("POST_JSON"));
			
			$post_json_obj = json_decode($POST_JSON);
			if ($post_json_obj == NULL) {
				$result->_status = "NG";
				$result->Message = "Failed to decode JSON";
				
			} else {
				$ch = curl_init();
				curl_setopt_array($ch, array(
					CURLOPT_URL => $ProxyURL,
					CURLOPT_POST => true,
					CURLOPT_HTTPHEADER => array(
						'Content-Type: application/json',
						'Matsuesoft-SQL-Output: Yes'
						),
					CURLOPT_POSTFIELDS => $POST_JSON,
					CURLOPT_RETURNTRANSFER => true
				));
				$response = curl_exec($ch);
				
				$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				if ($http_code != 200) {
					// Error Occured
					$result->_status = "NG";
					$result->Message = "Error Occured while request. Http Code is: " . $http_code;
					error_log($result->Message . " in " . __FILE__ . " on line " . __LINE__);
				} else {
					$result->Result = $response;
					$result->_status = "OK";
					$result->Message = "Successfully Called";
				}
			}
			// ここから下はTemplateと共通
		}
	}
}

if ($result->_status == "OK") {
	?>
    <p>Original Result:</p>
    <pre><?php print htmlspecialchars($result->Result); ?></pre>
    
    <?php
	$json_obj = json_decode($result->Result);
    if ($json_obj) {
		?>
		<p>Result with Format:</p>
		<pre><?php print json_encode($json_obj, JSON_PRETTY_PRINT); ?></pre>
		<?php
	}
} else {
	?>
    <p>Result: <?php print htmlspecialchars($result->_status); ?></p>
    <p><?php print htmlspecialchars($result->Message); ?></p>
    <?php
}

?>
