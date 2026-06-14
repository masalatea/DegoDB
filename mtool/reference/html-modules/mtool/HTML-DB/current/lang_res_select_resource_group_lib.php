<?php
		$TargetLanguageGroupList = array();
		$DALanguageResourceGroup = new LanguageResourceGroupDBAccess();
		$LanguageResourceGroupList = $DALanguageResourceGroup->GetLanguageResourceGroupList($LanguageResource->ProjectPID);
		for($i = 0 ; $i < count($LanguageResourceGroupList); $i++) {
			$LanguageResourceGroup = $LanguageResourceGroupList[$i];
			
			array_push($TargetLanguageGroupList,
				array("VALUE"=>$LanguageResourceGroup->PID, "CAPTION"=>$LanguageResourceGroup->Name));
		}
		mtoolCommonFormSelect("LanguageResourceGroupPID", $LanguageResource->LanguageResourceGroupPID,
			array($LANG_ENGLISH=>"Language Resource Group", $LANG_JAPANESE=>"言語リソースグループ"),
			array($LANG_ENGLISH=>"Please select Language Resource Group", $LANG_JAPANESE=>"言語リソースグループを選択して下さい"), 
			$TargetLanguageGroupList, array(), "");
?>
