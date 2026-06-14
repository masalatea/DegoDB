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

// == START OF GENERATED RUNTIME AUTOLOAD ==
$__mtoolRuntimeAutoloadRoot = __DIR__;
$__mtoolRuntimePreloadFiles = array (
  0 => '_runtime_loader.php',
  1 => 'data-BuildSourceFuncCache.php',
  2 => 'data-CompareOutput.php',
  3 => 'data-DBConnection.php',
  4 => 'data-Project.php',
  5 => 'data-ProjectGroup.php',
  6 => 'data-ProjectGroupTemplate.php',
  7 => 'data-ProjectSourceOutput.php',
  8 => 'data-ProjectUser.php',
  9 => 'data-Req.php',
  10 => 'data-SpecContent.php',
  11 => 'data-daCustomProxyFunc.php',
  12 => 'data-dafunc.php',
  13 => 'data-dafuncselecthaving.php',
  14 => 'data-dafuncselectwhere.php',
  15 => 'data-htmlTemplate.php',
  16 => 'data-htmlTemplateParameter.php',
);
foreach ($__mtoolRuntimePreloadFiles as $__mtoolRuntimePreloadFile) {
    require_once $__mtoolRuntimeAutoloadRoot . '/' . $__mtoolRuntimePreloadFile;
}

if (!isset($GLOBALS['__mtool_runtime_classmap_maps'])) {
    $GLOBALS['__mtool_runtime_classmap_maps'] = [];
}
if (!isset($GLOBALS['__mtool_runtime_classmap_registered_roots'])) {
    $GLOBALS['__mtool_runtime_classmap_registered_roots'] = [];
}

$GLOBALS['__mtool_runtime_classmap_maps'][$__mtoolRuntimeAutoloadRoot] = array (
  'ApacheSettingDBAccessBase' => 'base/dbaccess-ApacheSettingBase.php',
  'ApacheSettingDataBase' => 'base/data-ApacheSettingBase.php',
  'BuildLogDBAccessBase' => 'base/dbaccess-BuildLogBase.php',
  'BuildLogDataBase' => 'base/data-BuildLogBase.php',
  'BuildLogMessageTypeEnum' => 'base/data-BuildLogBase.php',
  'BuildSourceCacheDBAccessBase' => 'base/dbaccess-BuildSourceCacheBase.php',
  'BuildSourceCacheDataBase' => 'base/data-BuildSourceCacheBase.php',
  'BuildSourceCacheSourceTypeEnum' => 'base/data-BuildSourceCacheBase.php',
  'BuildSourceFuncCacheBuildTargetTypeEnum' => 'base/data-BuildSourceFuncCacheBase.php',
  'BuildSourceFuncCacheDBAccessBase' => 'base/dbaccess-BuildSourceFuncCacheBase.php',
  'BuildSourceFuncCacheDataBase' => 'base/data-BuildSourceFuncCacheBase.php',
  'BuildSourceFuncCacheReleaseTargetTypeEnum' => 'base/data-BuildSourceFuncCacheBase.php',
  'BuildTokenCompletedItemBuildTargetTypeEnum' => 'base/data-BuildTokenCompletedItemBase.php',
  'BuildTokenCompletedItemDBAccessBase' => 'base/dbaccess-BuildTokenCompletedItemBase.php',
  'BuildTokenCompletedItemDataBase' => 'base/data-BuildTokenCompletedItemBase.php',
  'BuildTokenDBAccessBase' => 'base/dbaccess-BuildTokenBase.php',
  'BuildTokenDataBase' => 'base/data-BuildTokenBase.php',
  'BuildTokenProjectSourceOutputBuildTargetTypeEnum' => 'base/data-BuildTokenProjectSourceOutputBase.php',
  'BuildTokenProjectSourceOutputDBAccessBase' => 'base/dbaccess-BuildTokenProjectSourceOutputBase.php',
  'BuildTokenProjectSourceOutputDataBase' => 'base/data-BuildTokenProjectSourceOutputBase.php',
  'BuildTokenTemplateCacheDBAccessBase' => 'base/dbaccess-BuildTokenTemplateCacheBase.php',
  'BuildTokenTemplateCacheDataBase' => 'base/data-BuildTokenTemplateCacheBase.php',
  'CompareOutputAdditionalPathDBAccessBase' => 'base/dbaccess-CompareOutputAdditionalPathBase.php',
  'CompareOutputAdditionalPathDataBase' => 'base/data-CompareOutputAdditionalPathBase.php',
  'CompareOutputDBAccessBase' => 'base/dbaccess-CompareOutputBase.php',
  'CompareOutputDataBase' => 'base/data-CompareOutputBase.php',
  'CompareOutputOutputFileTypeEnum' => 'base/data-CompareOutputBase.php',
  'CompareOutputSearchCacheDBAccessBase' => 'base/dbaccess-CompareOutputSearchCacheBase.php',
  'CompareOutputSearchCacheDataBase' => 'base/data-CompareOutputSearchCacheBase.php',
  'CompareOutputSearchCacheHintDBAccessBase' => 'base/dbaccess-CompareOutputSearchCacheHintBase.php',
  'CompareOutputSearchCacheHintDataBase' => 'base/data-CompareOutputSearchCacheHintBase.php',
  'DBBackupDBAccessBase' => 'base/dbaccess-DBBackupBase.php',
  'DBBackupDataBase' => 'base/data-DBBackupBase.php',
  'DBBackupUserDBAccessBase' => 'base/dbaccess-DBBackupUserBase.php',
  'DBBackupUserDataBase' => 'base/data-DBBackupUserBase.php',
  'DBConnectionDBAccessBase' => 'base/dbaccess-DBConnectionBase.php',
  'DBConnectionDBServerTypeEnum' => 'base/data-DBConnectionBase.php',
  'DBConnectionDataBase' => 'base/data-DBConnectionBase.php',
  'DBUserClientHostDBAccessBase' => 'base/dbaccess-DBUserClientHostBase.php',
  'DBUserClientHostDataBase' => 'base/data-DBUserClientHostBase.php',
  'DBUserDBAccessBase' => 'base/dbaccess-DBUserBase.php',
  'DBUserDataBase' => 'base/data-DBUserBase.php',
  'DropboxBaseFolderDBAccessBase' => 'base/dbaccess-DropboxBaseFolderBase.php',
  'DropboxBaseFolderDataBase' => 'base/data-DropboxBaseFolderBase.php',
  'DropboxBaseFolderUserDBAccessBase' => 'base/dbaccess-DropboxBaseFolderUserBase.php',
  'DropboxBaseFolderUserDataBase' => 'base/data-DropboxBaseFolderUserBase.php',
  'DropboxOauth2StatusHashDBAccessBase' => 'base/dbaccess-DropboxOauth2StatusHashBase.php',
  'DropboxOauth2StatusHashDataBase' => 'base/data-DropboxOauth2StatusHashBase.php',
  'DropboxSettingDBAccessBase' => 'base/dbaccess-DropboxSettingBase.php',
  'DropboxSettingDataBase' => 'base/data-DropboxSettingBase.php',
  'DropboxUploadTokenDBAccessBase' => 'base/dbaccess-DropboxUploadTokenBase.php',
  'DropboxUploadTokenDataBase' => 'base/data-DropboxUploadTokenBase.php',
  'InternalUserDBAccessBase' => 'base/dbaccess-InternalUserBase.php',
  'InternalUserDataBase' => 'base/data-InternalUserBase.php',
  'LanguageResourceAdditionalGroupAssignmentDBAccessBase' => 'base/dbaccess-LanguageResourceAdditionalGroupAssignmentBase.php',
  'LanguageResourceAdditionalGroupAssignmentDataBase' => 'base/data-LanguageResourceAdditionalGroupAssignmentBase.php',
  'LanguageResourceCaptionDBAccessBase' => 'base/dbaccess-LanguageResourceCaptionBase.php',
  'LanguageResourceCaptionDataBase' => 'base/data-LanguageResourceCaptionBase.php',
  'LanguageResourceDBAccessBase' => 'base/dbaccess-LanguageResourceBase.php',
  'LanguageResourceDataBase' => 'base/data-LanguageResourceBase.php',
  'LanguageResourceGroupDBAccessBase' => 'base/dbaccess-LanguageResourceGroupBase.php',
  'LanguageResourceGroupDataBase' => 'base/data-LanguageResourceGroupBase.php',
  'LanguageResourceGroupLangDBAccessBase' => 'base/dbaccess-LanguageResourceGroupLangBase.php',
  'LanguageResourceGroupLangDataBase' => 'base/data-LanguageResourceGroupLangBase.php',
  'LanguageResourceGroupProjectSourceOutputDBAccessBase' => 'base/dbaccess-LanguageResourceGroupProjectSourceOutputBase.php',
  'LanguageResourceGroupProjectSourceOutputDataBase' => 'base/data-LanguageResourceGroupProjectSourceOutputBase.php',
  'LanguageResourceLangDBAccessBase' => 'base/dbaccess-LanguageResourceLangBase.php',
  'LanguageResourceLangDataBase' => 'base/data-LanguageResourceLangBase.php',
  'LastBuildBuildClassTypeEnum' => 'base/data-LastBuildBase.php',
  'LastBuildDBAccessBase' => 'base/dbaccess-LastBuildBase.php',
  'LastBuildDataBase' => 'base/data-LastBuildBase.php',
  'LiveCheckResultDBAccessBase' => 'base/dbaccess-LiveCheckResultBase.php',
  'LiveCheckResultDataBase' => 'base/data-LiveCheckResultBase.php',
  'LiveCheckResultLiveCheckResultEnum' => 'base/data-LiveCheckResultBase.php',
  'LiveCheckResultLiveCheckTypeEnum' => 'base/data-LiveCheckResultBase.php',
  'LiveCheckResultSummaryForEachHourDBAccessBase' => 'base/dbaccess-LiveCheckResultSummaryForEachHourBase.php',
  'LiveCheckResultSummaryForEachHourDataBase' => 'base/data-LiveCheckResultSummaryForEachHourBase.php',
  'LiveCheckResultSummaryForEachHourLiveCheckResultEnum' => 'base/data-LiveCheckResultSummaryForEachHourBase.php',
  'LiveCheckResultSummaryForEachHourLiveCheckTypeEnum' => 'base/data-LiveCheckResultSummaryForEachHourBase.php',
  'LiveCheckTargetDBAccessBase' => 'base/dbaccess-LiveCheckTargetBase.php',
  'LiveCheckTargetDataBase' => 'base/data-LiveCheckTargetBase.php',
  'MySQLShowColumnDBAccessBase' => 'base/dbaccess-MySQLShowColumnBase.php',
  'MySQLShowColumnDataBase' => 'base/data-MySQLShowColumnBase.php',
  'PaypalSubscriptionDBAccessBase' => 'base/dbaccess-PaypalSubscriptionBase.php',
  'PaypalSubscriptionDataBase' => 'base/data-PaypalSubscriptionBase.php',
  'ProjectDBAccessBase' => 'base/dbaccess-ProjectBase.php',
  'ProjectDataBase' => 'base/data-ProjectBase.php',
  'ProjectGroupDBAccessBase' => 'base/dbaccess-ProjectGroupBase.php',
  'ProjectGroupDataBase' => 'base/data-ProjectGroupBase.php',
  'ProjectGroupProjectGroupTypeEnum' => 'base/data-ProjectGroupBase.php',
  'ProjectGroupTemplateDBAccessBase' => 'base/dbaccess-ProjectGroupTemplateBase.php',
  'ProjectGroupTemplateDataBase' => 'base/data-ProjectGroupTemplateBase.php',
  'ProjectGroupTemplateProjectGroupTypeEnum' => 'base/data-ProjectGroupTemplateBase.php',
  'ProjectHostSettingDBAccessBase' => 'base/dbaccess-ProjectHostSettingBase.php',
  'ProjectHostSettingDataBase' => 'base/data-ProjectHostSettingBase.php',
  'ProjectSecurityForEachPageDBAccessBase' => 'base/dbaccess-ProjectSecurityForEachPageBase.php',
  'ProjectSecurityForEachPageDataBase' => 'base/data-ProjectSecurityForEachPageBase.php',
  'ProjectSecurityForEachPageDetailsDBAccessBase' => 'base/dbaccess-ProjectSecurityForEachPageDetailsBase.php',
  'ProjectSecurityForEachPageDetailsDataBase' => 'base/data-ProjectSecurityForEachPageDetailsBase.php',
  'ProjectSourceOutputDBAccessBase' => 'base/dbaccess-ProjectSourceOutputBase.php',
  'ProjectSourceOutputDataBase' => 'base/data-ProjectSourceOutputBase.php',
  'ProjectSourceOutputSavedFilesDBAccessBase' => 'base/dbaccess-ProjectSourceOutputSavedFilesBase.php',
  'ProjectSourceOutputSavedFilesDataBase' => 'base/data-ProjectSourceOutputSavedFilesBase.php',
  'ProjectUserDBAccessBase' => 'base/dbaccess-ProjectUserBase.php',
  'ProjectUserDataBase' => 'base/data-ProjectUserBase.php',
  'ProjectUserIsOwnerEnum' => 'base/data-ProjectUserBase.php',
  'ProjectUserSerurityEnum' => 'base/data-ProjectUserBase.php',
  'ReqDBAccessBase' => 'base/dbaccess-ReqBase.php',
  'ReqDataBase' => 'base/data-ReqBase.php',
  'Req_and_ProjectDBAccessBase' => 'base/dbaccess-Req_and_ProjectBase.php',
  'Req_and_ProjectDataBase' => 'base/data-Req_and_ProjectBase.php',
  'ServerDBAccessBase' => 'base/dbaccess-ServerBase.php',
  'ServerDataBase' => 'base/data-ServerBase.php',
  'SettingGroupDBAccessBase' => 'base/dbaccess-SettingGroupBase.php',
  'SettingGroupDataBase' => 'base/data-SettingGroupBase.php',
  'SettingGroupUserDBAccessBase' => 'base/dbaccess-SettingGroupUserBase.php',
  'SettingGroupUserDataBase' => 'base/data-SettingGroupUserBase.php',
  'SortedhtmlTemplateDataContainer' => 'base/data-htmlTemplateBase.php',
  'SpecContentDBAccessBase' => 'base/dbaccess-SpecContentBase.php',
  'SpecContentDataBase' => 'base/data-SpecContentBase.php',
  'SpecDBAccessBase' => 'base/dbaccess-SpecBase.php',
  'SpecDataBase' => 'base/data-SpecBase.php',
  'SpecialHolidayDBAccessBase' => 'base/dbaccess-SpecialHolidayBase.php',
  'SpecialHolidayDataBase' => 'base/data-SpecialHolidayBase.php',
  'TestConditionDBAccessBase' => 'base/dbaccess-TestConditionBase.php',
  'TestConditionDataBase' => 'base/data-TestConditionBase.php',
  'TestConditionSelectionDBAccessBase' => 'base/dbaccess-TestConditionSelectionBase.php',
  'TestConditionSelectionDataBase' => 'base/data-TestConditionSelectionBase.php',
  'TestDBAccessBase' => 'base/dbaccess-TestBase.php',
  'TestDataBase' => 'base/data-TestBase.php',
  'TestGroupDBAccessBase' => 'base/dbaccess-TestGroupBase.php',
  'TestGroupDataBase' => 'base/data-TestGroupBase.php',
  'TestGroup_leftouterjoin_ProjectDBAccessBase' => 'base/dbaccess-TestGroup_leftouterjoin_ProjectBase.php',
  'TestGroup_leftouterjoin_ProjectDataBase' => 'base/data-TestGroup_leftouterjoin_ProjectBase.php',
  'TestPatternDBAccessBase' => 'base/dbaccess-TestPatternBase.php',
  'TestPatternDataBase' => 'base/data-TestPatternBase.php',
  'TestPatternExecuteResultDBAccessBase' => 'base/dbaccess-TestPatternExecuteResultBase.php',
  'TestPatternExecuteResultDataBase' => 'base/data-TestPatternExecuteResultBase.php',
  'TestPatternExecuteResultExecuteResultEnum' => 'base/data-TestPatternExecuteResultBase.php',
  'TestPatternSelectionDBAccessBase' => 'base/dbaccess-TestPatternSelectionBase.php',
  'TestPatternSelectionDataBase' => 'base/data-TestPatternSelectionBase.php',
  'Test_leftouterjoin_ProjectDBAccessBase' => 'base/dbaccess-Test_leftouterjoin_ProjectBase.php',
  'Test_leftouterjoin_ProjectDataBase' => 'base/data-Test_leftouterjoin_ProjectBase.php',
  'UploadDropboxPathCacheDBAccessBase' => 'base/dbaccess-UploadDropboxPathCacheBase.php',
  'UploadDropboxPathCacheDataBase' => 'base/data-UploadDropboxPathCacheBase.php',
  'UploadDropboxPathCacheItemsDBAccessBase' => 'base/dbaccess-UploadDropboxPathCacheItemsBase.php',
  'UploadDropboxPathCacheItemsDataBase' => 'base/data-UploadDropboxPathCacheItemsBase.php',
  'UploadGroupAssignedServerPathDBAccessBase' => 'base/dbaccess-UploadGroupAssignedServerPathBase.php',
  'UploadGroupAssignedServerPathDataBase' => 'base/data-UploadGroupAssignedServerPathBase.php',
  'UploadGroupAssignedUserDBAccessBase' => 'base/dbaccess-UploadGroupAssignedUserBase.php',
  'UploadGroupAssignedUserDataBase' => 'base/data-UploadGroupAssignedUserBase.php',
  'UploadGroupDBAccessBase' => 'base/dbaccess-UploadGroupBase.php',
  'UploadGroupDataBase' => 'base/data-UploadGroupBase.php',
  'UploadServerDBAccessBase' => 'base/dbaccess-UploadServerBase.php',
  'UploadServerDataBase' => 'base/data-UploadServerBase.php',
  'UploadServerPathDBAccessBase' => 'base/dbaccess-UploadServerPathBase.php',
  'UploadServerPathDataBase' => 'base/data-UploadServerPathBase.php',
  'chattopicAttachmentDBAccessBase' => 'base/dbaccess-chattopicAttachmentBase.php',
  'chattopicAttachmentDataBase' => 'base/data-chattopicAttachmentBase.php',
  'chattopicDBAccessBase' => 'base/dbaccess-chattopicBase.php',
  'chattopicDataBase' => 'base/data-chattopicBase.php',
  'chattopic_and_ProjectDBAccessBase' => 'base/dbaccess-chattopic_and_ProjectBase.php',
  'chattopic_and_ProjectDataBase' => 'base/data-chattopic_and_ProjectBase.php',
  'daCustomProxyDBAccessBase' => 'base/dbaccess-daCustomProxyBase.php',
  'daCustomProxyDataBase' => 'base/data-daCustomProxyBase.php',
  'daCustomProxyFuncDBAccessBase' => 'base/dbaccess-daCustomProxyFuncBase.php',
  'daCustomProxyFuncDataBase' => 'base/data-daCustomProxyFuncBase.php',
  'daCustomProxyFunc_leftouterjoin_dafunc_and_daDBAccessBase' => 'base/dbaccess-daCustomProxyFunc_leftouterjoin_dafunc_and_daBase.php',
  'daCustomProxyFunc_leftouterjoin_dafunc_and_daDataBase' => 'base/data-daCustomProxyFunc_leftouterjoin_dafunc_and_daBase.php',
  'daCustomProxySourceOutputTargetDBAccessBase' => 'base/dbaccess-daCustomProxySourceOutputTargetBase.php',
  'daCustomProxySourceOutputTargetDataBase' => 'base/data-daCustomProxySourceOutputTargetBase.php',
  'daDBAccessBase' => 'base/dbaccess-daBase.php',
  'daDataBase' => 'base/data-daBase.php',
  'dafuncDBAccessBase' => 'base/dbaccess-dafuncBase.php',
  'dafuncDataBase' => 'base/data-dafuncBase.php',
  'dafuncSimpleProxySourceOutputTargetDBAccessBase' => 'base/dbaccess-dafuncSimpleProxySourceOutputTargetBase.php',
  'dafuncSimpleProxySourceOutputTargetDataBase' => 'base/data-dafuncSimpleProxySourceOutputTargetBase.php',
  'dafuncinserttargetfieldsDBAccessBase' => 'base/dbaccess-dafuncinserttargetfieldsBase.php',
  'dafuncinserttargetfieldsDataBase' => 'base/data-dafuncinserttargetfieldsBase.php',
  'dafuncselecthavingDBAccessBase' => 'base/dbaccess-dafuncselecthavingBase.php',
  'dafuncselecthavingDataBase' => 'base/data-dafuncselecthavingBase.php',
  'dafuncselecthaving_leftouterjoin_targetfieldsDBAccessBase' => 'base/dbaccess-dafuncselecthaving_leftouterjoin_targetfieldsBase.php',
  'dafuncselecthaving_leftouterjoin_targetfieldsDataBase' => 'base/data-dafuncselecthaving_leftouterjoin_targetfieldsBase.php',
  'dafuncselecttargetfieldsDBAccessBase' => 'base/dbaccess-dafuncselecttargetfieldsBase.php',
  'dafuncselecttargetfieldsDataBase' => 'base/data-dafuncselecttargetfieldsBase.php',
  'dafuncselectwhereDBAccessBase' => 'base/dbaccess-dafuncselectwhereBase.php',
  'dafuncselectwhereDataBase' => 'base/data-dafuncselectwhereBase.php',
  'dafuncupdatedeletewhereDBAccessBase' => 'base/dbaccess-dafuncupdatedeletewhereBase.php',
  'dafuncupdatedeletewhereDataBase' => 'base/data-dafuncupdatedeletewhereBase.php',
  'dafuncupdatetargetfieldsDBAccessBase' => 'base/dbaccess-dafuncupdatetargetfieldsBase.php',
  'dafuncupdatetargetfieldsDataBase' => 'base/data-dafuncupdatetargetfieldsBase.php',
  'dataclassDBAccessBase' => 'base/dbaccess-dataclassBase.php',
  'dataclassDataBase' => 'base/data-dataclassBase.php',
  'dataclassfieldsDBAccessBase' => 'base/dbaccess-dataclassfieldsBase.php',
  'dataclassfieldsDataBase' => 'base/data-dataclassfieldsBase.php',
  'dbtableDBAccessBase' => 'base/dbaccess-dbtableBase.php',
  'dbtableDataBase' => 'base/data-dbtableBase.php',
  'dbtablecolumnsDBAccessBase' => 'base/dbaccess-dbtablecolumnsBase.php',
  'dbtablecolumnsDataBase' => 'base/data-dbtablecolumnsBase.php',
  'htmlDBAccessBase' => 'base/dbaccess-htmlBase.php',
  'htmlDataBase' => 'base/data-htmlBase.php',
  'htmlParameterDBAccessBase' => 'base/dbaccess-htmlParameterBase.php',
  'htmlParameterDataBase' => 'base/data-htmlParameterBase.php',
  'htmlTemplateDBAccessBase' => 'base/dbaccess-htmlTemplateBase.php',
  'htmlTemplateDataBase' => 'base/data-htmlTemplateBase.php',
  'htmlTemplateParameterDBAccessBase' => 'base/dbaccess-htmlTemplateParameterBase.php',
  'htmlTemplateParameterDataBase' => 'base/data-htmlTemplateParameterBase.php',
  'htmlTemplateParameter_leftouterjoin_AnotherHtmlTemplateDBAccessBase' => 'base/dbaccess-htmlTemplateParameter_leftouterjoin_AnotherHtmlTemplateBase.php',
  'htmlTemplateParameter_leftouterjoin_AnotherHtmlTemplateDataBase' => 'base/data-htmlTemplateParameter_leftouterjoin_AnotherHtmlTemplateBase.php',
  'htmlTemplateProgramLanguageEnum' => 'base/data-htmlTemplateBase.php',
  'htmlTemplateTargetTypeEnum' => 'base/data-htmlTemplateBase.php',
  'htmlTemplate_leftouterjoin_ParentHtmlTemplateDBAccessBase' => 'base/dbaccess-htmlTemplate_leftouterjoin_ParentHtmlTemplateBase.php',
  'htmlTemplate_leftouterjoin_ParentHtmlTemplateDataBase' => 'base/data-htmlTemplate_leftouterjoin_ParentHtmlTemplateBase.php',
  'html_leftouterjoin_htmlTemplateDBAccessBase' => 'base/dbaccess-html_leftouterjoin_htmlTemplateBase.php',
  'html_leftouterjoin_htmlTemplateDataBase' => 'base/data-html_leftouterjoin_htmlTemplateBase.php',
  'minutesDBAccessBase' => 'base/dbaccess-minutesBase.php',
  'minutesDataBase' => 'base/data-minutesBase.php',
  'minutes_and_RelatedTablesDBAccessBase' => 'base/dbaccess-minutes_and_RelatedTablesBase.php',
  'minutes_and_RelatedTablesDataBase' => 'base/data-minutes_and_RelatedTablesBase.php',
);
if (!isset($GLOBALS['__mtool_runtime_classmap_registered_roots'][$__mtoolRuntimeAutoloadRoot])) {
    spl_autoload_register(
        static function (string $class) use ($__mtoolRuntimeAutoloadRoot): void {
            $classMap = $GLOBALS['__mtool_runtime_classmap_maps'][$__mtoolRuntimeAutoloadRoot] ?? [];
            if (!isset($classMap[$class])) {
                return;
            }

            require_once $__mtoolRuntimeAutoloadRoot . '/' . $classMap[$class];
        },
    );
    $GLOBALS['__mtool_runtime_classmap_registered_roots'][$__mtoolRuntimeAutoloadRoot] = true;
}

unset($__mtoolRuntimePreloadFile, $__mtoolRuntimePreloadFiles, $__mtoolRuntimeAutoloadRoot);
// == END OF GENERATED RUNTIME AUTOLOAD ==

// == START OF EDITABLE AREA FOR AUTOLOAD BOTTOM ==
// == END OF EDITABLE AREA FOR AUTOLOAD BOTTOM ==

?>
