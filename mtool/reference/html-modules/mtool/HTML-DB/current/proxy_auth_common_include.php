<?php
// Please assume dafuncSingleProxy_AuthTypeEnum and daCustomProxyAuthTypeEnum is same

include_once("/srv/legacy/www/mtool_lib/lib_mtool_project_source_output.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");

$is_include_DBaaS_proxy = false;
$is_include_non_DBaaS_proxy = false;
mtool_check_if_project_source_output_include_DBaaS($ProjectPID, $is_include_DBaaS_proxy, $is_include_non_DBaaS_proxy);

function GetSingleGetFuncSelectionList($ProjectPID)
{
	$SingleGetFuncSelectionList = array();
	$DAdafunc = new dafuncDBAccess();
	$DAda = new daDBAccess();
	$dalist = $DAda->GetdaList($ProjectPID);
	if ($dalist) {
		for($i = 0 ; $i < count($dalist); $i++) {
			$da = $dalist[$i];
			
			$dafunclist = $DAdafunc->GetdafuncList($ProjectPID, $da->PID);
			if ($dafunclist) {
				for($j = 0 ; $j < count($dafunclist); $j++) {
					$dafunc = $dafunclist[$j];
					
					switch($dafunc->ActionType) {
						case dafuncActionTypeEnum::$SELECTSINGLE:
							array_push($SingleGetFuncSelectionList, array("VALUE"=>$dafunc->PID, "CAPTION"=>$da->name . ": " . $dafunc->name . " : " . GetFunctionNameFromFunctionActionType($dafunc->name, $dafunc->ActionType)));
							break;
						case dafuncActionTypeEnum::$SELECTLIST:
						case dafuncActionTypeEnum::$INSERT:
						case dafuncActionTypeEnum::$UPDATE:
						case dafuncActionTypeEnum::$DELETE:
							break;
						default:
							print "INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType . "\n";
							break;
					}
				}
			}
		}
	}
	return $SingleGetFuncSelectionList;
}

if ($is_include_DBaaS_proxy) {
	
	mtoolCommonFormSelect($FormKeyNameForAuthType, $AuthType,
		array($LANG_ENGLISH=>"Authentication Type (for DBaaS)", $LANG_JAPANESE=>"認証種類 (DBaaS向け)"),
		array($LANG_ENGLISH=>"Please select Authentication Type for DBaaS", $LANG_JAPANESE=>"認証種類を選択して下さい(DBaaS向け)"), 
		array(
			array("VALUE"=>dafuncSingleProxy_AuthTypeEnum::$PROJECTTOKEN,          "CAPTION"=>GetSingleProxyAuthTypeCaption(dafuncSingleProxy_AuthTypeEnum::$PROJECTTOKEN)),
			array("VALUE"=>dafuncSingleProxy_AuthTypeEnum::$GETFUNC,               "CAPTION"=>GetSingleProxyAuthTypeCaption(dafuncSingleProxy_AuthTypeEnum::$GETFUNC)),
			array("VALUE"=>dafuncSingleProxy_AuthTypeEnum::$PROJECTTOKENORGETFUNC, "CAPTION"=>GetSingleProxyAuthTypeCaption(dafuncSingleProxy_AuthTypeEnum::$PROJECTTOKENORGETFUNC)),
			array("VALUE"=>dafuncSingleProxy_AuthTypeEnum::$NOSECURITY,            "CAPTION"=>GetSingleProxyAuthTypeCaption(dafuncSingleProxy_AuthTypeEnum::$NOSECURITY)),
			array("VALUE"=>dafuncSingleProxy_AuthTypeEnum::$MANUAL,                "CAPTION"=>GetSingleProxyAuthTypeCaption(dafuncSingleProxy_AuthTypeEnum::$MANUAL)),
			array("VALUE"=>dafuncSingleProxy_AuthTypeEnum::$LOGINCOOKIETOKEN,      "CAPTION"=>GetSingleProxyAuthTypeCaption(dafuncSingleProxy_AuthTypeEnum::$LOGINCOOKIETOKEN))			
		), array(
			array("VALUE"=>dafuncSingleProxy_AuthTypeEnum::$GETFUNC . "," . dafuncSingleProxy_AuthTypeEnum::$PROJECTTOKENORGETFUNC, "SHOW"=>"GetFuncArea,GetFuncWarningArea")
		), "");
	
	mtoolCommonFormSelect($FormKeyNameForSingleGetFuncPID, $SingleGetFuncPID,
		array($LANG_ENGLISH=>"Get(single) function", $LANG_JAPANESE=>"認証用Get(single)関数"),
		array($LANG_ENGLISH=>"Please select Get(single) function for Authentication", $LANG_JAPANESE=>"認証用Get(single)関数を選択して下さい"), 
		GetSingleGetFuncSelectionList($ProjectPID)
		, array(), "GetFuncArea");
	if (is_numeric($thisPID)) {
		if ($SingleGetFuncPID <= 0) {
			// Not Selected. Show Warning
			$thisCaption = array($LANG_ENGLISH=>"WARNING! Please select Get(single) function for Authentication.", $LANG_JAPANESE=>"WARNING! 認証用Get(single)関数を選択して下さい");
			?>
			<div class="row" id="GetFuncWarningArea" style="display:none">
				<label class="col-md-3 control-label" for="inputtext"></label>
				<div class="col-md-9">
					<font color="red">
					<?php print htmlspecialchars($thisCaption[$lang]); ?>
					</font>
				</div>
			</div>
			<?php
		}
	}
} else {
	?>
	<p>[FYI] DBaaS Proxy is not include. Some setting is only for DBaaS.</p>
    <?php
}

?>