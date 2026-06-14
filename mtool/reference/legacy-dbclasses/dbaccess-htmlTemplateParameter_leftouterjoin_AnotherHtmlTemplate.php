<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class htmlTemplateParameter_leftouterjoin_AnotherHtmlTemplateDBAccess
{
	public function __construct() {
	}
	
	public function GethtmlTemplateParameterList($param_htmlTemplateParameter_htmlTemplatePID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GethtmlTemplateParameterList ==
		// == END OF EDITABLE AREA FOR FUNCTION GethtmlTemplateParameterList ==
		
		$last_sql_command_for_mtooldb = "select htmlTemplateParameter.htmlTemplatePID, htmlTemplateParameter.PID, htmlTemplateParameter.ParameterName, htmlTemplateParameter.TargetValueType, htmlTemplateParameter.TargetVariableOrClassObject, htmlTemplateParameter.TargetPropertyOfClassObject, htmlTemplateParameter.AnotherTemplatePID, htmlTemplateParameter.TrimLastSpace, htmlTemplateParameter.TrimLastReturn, htmlTemplateParameter.DataType, htmlTemplate.name from htmlTemplateParameter LEFT OUTER JOIN htmlTemplate ON htmlTemplateParameter.AnotherTemplatePID = htmlTemplate.PID where htmlTemplateParameter.htmlTemplatePID = '" . $mtooldb->real_escape_string($param_htmlTemplateParameter_htmlTemplatePID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new htmlTemplateParameter_leftouterjoin_AnotherHtmlTemplateData();
			$thisresult->htmlTemplatePID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->ParameterName = $thisline[2];
			$thisresult->TargetValueType = $thisline[3];
			$thisresult->TargetVariableOrClassObject = $thisline[4];
			$thisresult->TargetPropertyOfClassObject = $thisline[5];
			$thisresult->AnotherTemplatePID = $thisline[6];
			$thisresult->TrimLastSpace = $thisline[7];
			$thisresult->TrimLastReturn = $thisline[8];
			$thisresult->DataType = $thisline[9];
			$thisresult->htmlTemplatename = $thisline[10];
			array_push($result, $thisresult);
		}
		return $result;
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	
	public function GethtmlTemplateParameterListMostDeep($param_htmlTemplateParameter_htmlTemplatePID_where)
	{
		$htmlTemplateParameterList = array();
		$this->GethtmlTemplateParameterListMostDeepSub($htmlTemplateParameterList, $param_htmlTemplateParameter_htmlTemplatePID_where);
		return $htmlTemplateParameterList;
	}
	private function GethtmlTemplateParameterListMostDeepSub(&$htmlTemplateParameterList, $htmlTemplatePID)
	{
		$thisHtmlTemplateParameterList = $this->GethtmlTemplateParameterList($htmlTemplatePID);
		
		for($i = 0 ; $i < count($thisHtmlTemplateParameterList) ; $i++) {
			$thisHtmlTemplateParameter = $thisHtmlTemplateParameterList[$i];
			array_push($htmlTemplateParameterList, $thisHtmlTemplateParameter);
			
			switch($thisHtmlTemplateParameter->TargetValueType)
			{
				case htmlTemplateParameterTargetValueTypeEnum::$EACHHTML:
					break;
				case htmlTemplateParameterTargetValueTypeEnum::$CODE:
					break;
				case htmlTemplateParameterTargetValueTypeEnum::$ANOTHERTEMPLATE:
					
					$isAlreadyExist = $this->CheckhtmlTemplateParameterIsAlreadyExist($htmlTemplateParameterList, $thisHtmlTemplateParameter->AnotherTemplatePID);
					if (!$isAlreadyExist) {
						$this->GethtmlTemplateParameterListMostDeepSub($htmlTemplateParameterList, $thisHtmlTemplateParameter->AnotherTemplatePID);
					}
					break;
			}
		}
	}
	private function CheckhtmlTemplateParameterIsAlreadyExist($htmlTemplateParameterList, $templatePID)
	{
		$isAlreadyExist = false;
		for($j = 0 ; $j < count($htmlTemplateParameterList) ; $j++) {
			$htmlTemplateParameter = $htmlTemplateParameterList[$j];
			if ($htmlTemplateParameter->PID == $templatePID) {
				$isAlreadyExist = true;
				break;
			}
		}
		return $isAlreadyExist;
	}
	
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>