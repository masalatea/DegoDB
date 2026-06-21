<?PHP
__HTTP_HEADER__

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

__INCLUDE_FILES__

// Please connect database to $__DB_OBJECT__ here. Please include DBAccess Class
// [JP] 以下の編集領域でmysqliのデータベース接続を行って $__DB_OBJECT__ オブジェクトに格納して下さい。
// [JP] 以下の編集領域でDBAccessクラスのIncludeを行って下さい。

// == START OF EDITABLE AREA FOR INITIALIZE ==
// == END OF EDITABLE AREA FOR INITIALIZE ==

$stdin = file_get_contents('php://input');
$data = json_decode($stdin);

$SecurityCheckResult = false;

// Please perform Security Check and set true to $SecurityCheckResult
// [JP] 以下の編集領域でセキュリティチェックを行い、$SecurityCheckResult を true に設定して下さい。

// == START OF EDITABLE AREA FOR SECURITY CHECK ==
// == END OF EDITABLE AREA FOR SECURITY CHECK ==

__RESULT_INITIALIZE__
if (!$SecurityCheckResult) {
	// Security Error
	print json_encode(array(
__RESULT_PARAM__
		"_status"=>"NGinServer",
		"Message"=>"Security Error"
	));
	
} else {
	// Security Check Passed
	// == START OF EDITABLE AREA FOR CUSTOM ACTION AFTER SECURITY CHECK ==
	// == END OF EDITABLE AREA FOR CUSTOM ACTION AFTER SECURITY CHECK ==
__BEGIN_TRANSACTION__
__FUNCTION_CALL__
__COMMIT_TRANSACTION__
	$json_result = array(
__RESULT_PARAM__
		"_status"=>"OK",
		"Message"=>"Successfully called"
	);
	// == START OF EDITABLE AREA FOR CUSTOM ACTION AFTER SUCCESS ==
	// == END OF EDITABLE AREA FOR CUSTOM ACTION AFTER SUCCESS ==
__INSERT_ID__
__RETURN_SQL__
	print json_encode($json_result);
}
// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>
