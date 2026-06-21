<?PHP

function GetMtoolOneConstDefinition($BuildToken, $project, $ProjectSourceOutput, $TemplateName, $enumName, $enumValue, $const_index, $comma)
{
	$template_one_def = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, $TemplateName);
	$template_one_def = ReplaceOutputSourceInfoByKeyValue($template_one_def, array(
			array("KEY"=>"__CONST_NAME__", "VALUE"=>$enumName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__CONST_VALUE__", "VALUE"=>$enumValue, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__CONST_VALUE_STRING__", "VALUE"=>GetValueOfDBAccessClassEscapeFixedString($project, $ProjectSourceOutput, $enumValue), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
	
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			$template_one_def = ReplaceOutputSourceInfoByKeyValue($template_one_def, array(
					array("KEY"=>"__CONST_INDEX__", "VALUE"=>$const_index, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
					array("KEY"=>"__COMMA__", "VALUE"=>$comma, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
				));
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
	return $template_one_def;
}

?>
