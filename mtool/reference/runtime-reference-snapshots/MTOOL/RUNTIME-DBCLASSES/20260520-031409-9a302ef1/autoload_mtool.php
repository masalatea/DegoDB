<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR AUTOLOAD TOP ==
// == END OF EDITABLE AREA FOR AUTOLOAD TOP ==

$mtooldb = NULL;

function connect_error_for_mtooldb($error_message)
{
	error_log($error_message);
	if (function_exists("DB_connect_error_event")) {
		DB_connect_error_event($error_message);
	} else if (php_sapi_name() != "cli") {
		header('HTTP/1.0 503 Service Temporarily Unavailable');
	}
}
function connect_mtooldb_if_not_yet()
{
	global $mtooldb;
	global $CustomMySQLDBServerNameFormtooldb;
	global $MySQLDBSSLConnectionServerKeyFormtooldb;
	global $MySQLDBSSLConnectionServerCertFormtooldb;
	global $MySQLDBSSLConnectionCaCertFormtooldb;
	
	if ($mtooldb) {
		// Already Connected
		return;
	}
	
	$MySQLDBServerName = "localhost";		// If you want to custom Server Name, set to $CustomMySQLDBServerNameFormtooldb beforehand
	if (isset($CustomMySQLDBServerNameFormtooldb)) {
		$MySQLDBServerName = $CustomMySQLDBServerNameFormtooldb;
	}
	$mtooldb = mysqli_init();
	if (isset($MySQLDBSSLConnectionServerKeyFormtooldb) && isset($MySQLDBSSLConnectionServerCertFormtooldb) && isset($MySQLDBSSLConnectionCaCertFormtooldb)) {
		$mtooldb->ssl_set($MySQLDBSSLConnectionServerKeyFormtooldb, $MySQLDBSSLConnectionServerCertFormtooldb, $MySQLDBSSLConnectionCaCertFormtooldb, NULL, NULL);
	}
	$mtooldb->real_connect($MySQLDBServerName, "???", "???", "mtool");
	if (!$mtooldb) {
		connect_error_for_mtooldb("error! Failed to connect Database: mtool from $MySQLDBServerName by ???");
		exit();
	}
	if ($mtooldb->connect_errno) {
		connect_error_for_mtooldb("Connect failed: " . $mtooldb->connect_error);
		exit();
	}
	if (!$mtooldb->set_charset("utf8mb4")) {
		connect_error_for_mtooldb("Error loading character set utf8: " . $mtooldb->error);
		exit();
	}
}
function reconnect_mtooldb_if_necessary()
{
	global $mtooldb;
	global $time_for_reconnect_mtooldb_if_necessary;
	
	$THRESHOLD_TIMEOUT_SEC = 10;
	
	if (abs(time() - $time_for_reconnect_mtooldb_if_necessary) > $THRESHOLD_TIMEOUT_SEC) {
		$mtooldb->ping();
		$time_for_reconnect_mtooldb_if_necessary = time();
	}
}
$time_for_reconnect_mtooldb_if_necessary = time();

$last_sql_command_for_mtooldb = "";
include_once("data-ApacheSetting.php");
include_once("data-BuildLog.php");
include_once("data-BuildSourceCache.php");
include_once("data-BuildSourceFuncCache.php");
include_once("data-BuildToken.php");
include_once("data-BuildTokenCompletedItem.php");
include_once("data-BuildTokenProjectSourceOutput.php");
include_once("data-BuildTokenTemplateCache.php");
include_once("data-chattopic.php");
include_once("data-chattopicAttachment.php");
include_once("data-chattopic_and_Project.php");
include_once("data-CompareOutput.php");
include_once("data-CompareOutputAdditionalPath.php");
include_once("data-CompareOutputSearchCache.php");
include_once("data-CompareOutputSearchCacheHint.php");
include_once("data-da.php");
include_once("data-daCustomProxy.php");
include_once("data-daCustomProxyFunc.php");
include_once("data-daCustomProxyFunc_leftouterjoin_dafunc_and_da.php");
include_once("data-daCustomProxySourceOutputTarget.php");
include_once("data-dafunc.php");
include_once("data-dafuncinserttargetfields.php");
include_once("data-dafuncselecthaving.php");
include_once("data-dafuncselecthaving_leftouterjoin_targetfields.php");
include_once("data-dafuncselecttargetfields.php");
include_once("data-dafuncselectwhere.php");
include_once("data-dafuncSimpleProxySourceOutputTarget.php");
include_once("data-dafuncupdatedeletewhere.php");
include_once("data-dafuncupdatetargetfields.php");
include_once("data-dataclass.php");
include_once("data-dataclassfields.php");
include_once("data-DBBackup.php");
include_once("data-DBBackupUser.php");
include_once("data-DBConnection.php");
include_once("data-dbtable.php");
include_once("data-dbtablecolumns.php");
include_once("data-DBUser.php");
include_once("data-DBUserClientHost.php");
include_once("data-DropboxBaseFolder.php");
include_once("data-DropboxBaseFolderUser.php");
include_once("data-DropboxOauth2StatusHash.php");
include_once("data-DropboxSetting.php");
include_once("data-DropboxUploadToken.php");
include_once("data-html.php");
include_once("data-htmlParameter.php");
include_once("data-htmlTemplate.php");
include_once("data-htmlTemplateParameter.php");
include_once("data-htmlTemplateParameter_leftouterjoin_AnotherHtmlTemplate.php");
include_once("data-htmlTemplate_leftouterjoin_ParentHtmlTemplate.php");
include_once("data-html_leftouterjoin_htmlTemplate.php");
include_once("data-InternalUser.php");
include_once("data-LanguageResource.php");
include_once("data-LanguageResourceAdditionalGroupAssignment.php");
include_once("data-LanguageResourceCaption.php");
include_once("data-LanguageResourceGroup.php");
include_once("data-LanguageResourceGroupLang.php");
include_once("data-LanguageResourceGroupProjectSourceOutput.php");
include_once("data-LanguageResourceLang.php");
include_once("data-LastBuild.php");
include_once("data-LiveCheckResult.php");
include_once("data-LiveCheckResultSummaryForEachHour.php");
include_once("data-LiveCheckTarget.php");
include_once("data-minutes.php");
include_once("data-minutes_and_RelatedTables.php");
include_once("data-MySQLShowColumn.php");
include_once("data-PaypalSubscription.php");
include_once("data-Project.php");
include_once("data-ProjectGroup.php");
include_once("data-ProjectGroupTemplate.php");
include_once("data-ProjectHostSetting.php");
include_once("data-ProjectSecurityForEachPage.php");
include_once("data-ProjectSecurityForEachPageDetails.php");
include_once("data-ProjectSourceOutput.php");
include_once("data-ProjectSourceOutputSavedFiles.php");
include_once("data-ProjectUser.php");
include_once("data-Req.php");
include_once("data-Req_and_Project.php");
include_once("data-Server.php");
include_once("data-SettingGroup.php");
include_once("data-SettingGroupUser.php");
include_once("data-Spec.php");
include_once("data-SpecContent.php");
include_once("data-SpecialHoliday.php");
include_once("data-Test.php");
include_once("data-TestCondition.php");
include_once("data-TestConditionSelection.php");
include_once("data-TestGroup.php");
include_once("data-TestGroup_leftouterjoin_Project.php");
include_once("data-TestPattern.php");
include_once("data-TestPatternExecuteResult.php");
include_once("data-TestPatternSelection.php");
include_once("data-Test_leftouterjoin_Project.php");
include_once("data-UploadDropboxPathCache.php");
include_once("data-UploadDropboxPathCacheItems.php");
include_once("data-UploadGroup.php");
include_once("data-UploadGroupAssignedServerPath.php");
include_once("data-UploadGroupAssignedUser.php");
include_once("data-UploadServer.php");
include_once("data-UploadServerPath.php");
include_once("dbaccess-ApacheSetting.php");
include_once("dbaccess-BuildLog.php");
include_once("dbaccess-BuildSourceCache.php");
include_once("dbaccess-BuildSourceFuncCache.php");
include_once("dbaccess-BuildToken.php");
include_once("dbaccess-BuildTokenCompletedItem.php");
include_once("dbaccess-BuildTokenProjectSourceOutput.php");
include_once("dbaccess-BuildTokenTemplateCache.php");
include_once("dbaccess-chattopic.php");
include_once("dbaccess-chattopicAttachment.php");
include_once("dbaccess-chattopic_and_Project.php");
include_once("dbaccess-CompareOutput.php");
include_once("dbaccess-CompareOutputAdditionalPath.php");
include_once("dbaccess-CompareOutputSearchCache.php");
include_once("dbaccess-CompareOutputSearchCacheHint.php");
include_once("dbaccess-da.php");
include_once("dbaccess-daCustomProxy.php");
include_once("dbaccess-daCustomProxyFunc.php");
include_once("dbaccess-daCustomProxyFunc_leftouterjoin_dafunc_and_da.php");
include_once("dbaccess-daCustomProxySourceOutputTarget.php");
include_once("dbaccess-dafunc.php");
include_once("dbaccess-dafuncinserttargetfields.php");
include_once("dbaccess-dafuncselecthaving.php");
include_once("dbaccess-dafuncselecthaving_leftouterjoin_targetfields.php");
include_once("dbaccess-dafuncselecttargetfields.php");
include_once("dbaccess-dafuncselectwhere.php");
include_once("dbaccess-dafuncSimpleProxySourceOutputTarget.php");
include_once("dbaccess-dafuncupdatedeletewhere.php");
include_once("dbaccess-dafuncupdatetargetfields.php");
include_once("dbaccess-dataclass.php");
include_once("dbaccess-dataclassfields.php");
include_once("dbaccess-DBBackup.php");
include_once("dbaccess-DBBackupUser.php");
include_once("dbaccess-DBConnection.php");
include_once("dbaccess-dbtable.php");
include_once("dbaccess-dbtablecolumns.php");
include_once("dbaccess-DBUser.php");
include_once("dbaccess-DBUserClientHost.php");
include_once("dbaccess-DropboxBaseFolder.php");
include_once("dbaccess-DropboxBaseFolderUser.php");
include_once("dbaccess-DropboxOauth2StatusHash.php");
include_once("dbaccess-DropboxSetting.php");
include_once("dbaccess-DropboxUploadToken.php");
include_once("dbaccess-html.php");
include_once("dbaccess-htmlParameter.php");
include_once("dbaccess-htmlTemplate.php");
include_once("dbaccess-htmlTemplateParameter.php");
include_once("dbaccess-htmlTemplateParameter_leftouterjoin_AnotherHtmlTemplate.php");
include_once("dbaccess-htmlTemplate_leftouterjoin_ParentHtmlTemplate.php");
include_once("dbaccess-html_leftouterjoin_htmlTemplate.php");
include_once("dbaccess-InternalUser.php");
include_once("dbaccess-LanguageResource.php");
include_once("dbaccess-LanguageResourceAdditionalGroupAssignment.php");
include_once("dbaccess-LanguageResourceCaption.php");
include_once("dbaccess-LanguageResourceGroup.php");
include_once("dbaccess-LanguageResourceGroupLang.php");
include_once("dbaccess-LanguageResourceGroupProjectSourceOutput.php");
include_once("dbaccess-LanguageResourceLang.php");
include_once("dbaccess-LastBuild.php");
include_once("dbaccess-LiveCheckResult.php");
include_once("dbaccess-LiveCheckResultSummaryForEachHour.php");
include_once("dbaccess-LiveCheckTarget.php");
include_once("dbaccess-minutes.php");
include_once("dbaccess-minutes_and_RelatedTables.php");
include_once("dbaccess-MySQLShowColumn.php");
include_once("dbaccess-PaypalSubscription.php");
include_once("dbaccess-Project.php");
include_once("dbaccess-ProjectGroup.php");
include_once("dbaccess-ProjectGroupTemplate.php");
include_once("dbaccess-ProjectHostSetting.php");
include_once("dbaccess-ProjectSecurityForEachPage.php");
include_once("dbaccess-ProjectSecurityForEachPageDetails.php");
include_once("dbaccess-ProjectSourceOutput.php");
include_once("dbaccess-ProjectSourceOutputSavedFiles.php");
include_once("dbaccess-ProjectUser.php");
include_once("dbaccess-Req.php");
include_once("dbaccess-Req_and_Project.php");
include_once("dbaccess-Server.php");
include_once("dbaccess-SettingGroup.php");
include_once("dbaccess-SettingGroupUser.php");
include_once("dbaccess-Spec.php");
include_once("dbaccess-SpecContent.php");
include_once("dbaccess-SpecialHoliday.php");
include_once("dbaccess-Test.php");
include_once("dbaccess-TestCondition.php");
include_once("dbaccess-TestConditionSelection.php");
include_once("dbaccess-TestGroup.php");
include_once("dbaccess-TestGroup_leftouterjoin_Project.php");
include_once("dbaccess-TestPattern.php");
include_once("dbaccess-TestPatternExecuteResult.php");
include_once("dbaccess-TestPatternSelection.php");
include_once("dbaccess-Test_leftouterjoin_Project.php");
include_once("dbaccess-UploadDropboxPathCache.php");
include_once("dbaccess-UploadDropboxPathCacheItems.php");
include_once("dbaccess-UploadGroup.php");
include_once("dbaccess-UploadGroupAssignedServerPath.php");
include_once("dbaccess-UploadGroupAssignedUser.php");
include_once("dbaccess-UploadServer.php");
include_once("dbaccess-UploadServerPath.php");

// == START OF EDITABLE AREA FOR AUTOLOAD BOTTOM ==
// == END OF EDITABLE AREA FOR AUTOLOAD BOTTOM ==

?>
