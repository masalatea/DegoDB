<?php

// Initialize TestPatternList
function InitializeTestPatternList($ProjectPID, $TestGroupPID, $TestPID)
{
	$DATestPattern = new TestPatternDBAccess();
	$DATestPatternSelection = new TestPatternSelectionDBAccess();
	
	$TestPatternList = $DATestPattern->GetTestPatternList($ProjectPID, $TestGroupPID, $TestPID);
	if ($TestPatternList != NULL) {
		for ($i = 0 ; $i < count($TestPatternList); $i++) {
			$TestPattern = $TestPatternList[$i];
			$TestPattern->TestPatternSelectionList = $DATestPatternSelection->GetTestPatternSelectionList(
								$ProjectPID, $TestGroupPID, $TestPID, $TestPattern->PID);
		}
	}
	return $TestPatternList;
}
function GetCorrespondingTestPattern($TestPatternList, $AllSelectionList)
{
	$correspondingTestPattern = NULL;
	
	if ($TestPatternList != NULL) {
		$AllMatched = true;
		for ($j = 0 ; $j < count($TestPatternList); $j++) {
			$TestPattern = $TestPatternList[$j];
			
			$AllMatched = true;
			for($k = 0 ; $k < count($TestPattern->TestPatternSelectionList); $k++) {
				$TestPatternSelection = $TestPattern->TestPatternSelectionList[$k];
				
				$thisMatched = false;
				$ValueMatchFlagHT = array();
				for($l = 0 ; $l < count($AllSelectionList) ; $l++) {
					$thisSelectionObj = $AllSelectionList[$l];
					
					if (array_key_exists($thisSelectionObj->PID, $ValueMatchFlagHT)) {
						// Already matched. One item can match to only one item. So, this item is skipped.
					} else {
						if ($TestPatternSelection->Selection == $thisSelectionObj->Selection) {
							$thisMatched = true;
							$ValueMatchFlagHT[$thisSelectionObj->PID] = true;
							break;
						}
					}
				}
				if (!$thisMatched) {
					$AllMatched = false;
					break;
				}
			}
			if ($AllMatched) {
				$correspondingTestPattern = $TestPattern;
				break;
			}
		}
	}
	return $correspondingTestPattern;
}

function GetCorrespondingTestPatternExecuteResult($TestPatternExecuteResultList, $correspondingTestPattern)
{
	$correspondingTestPatternExecuteResult = NULL;
	if ($TestPatternExecuteResultList != NULL && $correspondingTestPattern != NULL) {
		for($i = 0 ; $i < count($TestPatternExecuteResultList); $i++) {
			$TestPatternExecuteResult = $TestPatternExecuteResultList[$i];
			if ($TestPatternExecuteResult->ProjectPID     == $correspondingTestPattern->ProjectPID   &&
				$TestPatternExecuteResult->TestGroupPID   == $correspondingTestPattern->TestGroupPID &&
				$TestPatternExecuteResult->TestPID        == $correspondingTestPattern->TestPID      &&
				$TestPatternExecuteResult->TestPatternPID == $correspondingTestPattern->PID) {
				$correspondingTestPatternExecuteResult = $TestPatternExecuteResult;
			}
		}
	}
	return $correspondingTestPatternExecuteResult;
}

class TestConditionSelectionContainerData
{
	public $SelectionList;
	public $Index = 0;
	public $Index2nd = 0;
	public $PatternCountUntilThis = 0;
	public $secondCount = 0;
	
	public function Calculate2ndCount($AllPatternCount)
	{
		$this->secondCount = $AllPatternCount / $this->PatternCountUntilThis;
	}
}

function InitializeTestConditionSelectionContainerList($ProjectPID, $TestGroupPID, $TestPID, $TestConditionList, &$AllPatternCount, $IncludeOldResult)
{
	$DATestConditionSelection = new TestConditionSelectionDBAccess();
	
	$TestConditionSelectionContainerList = array();
	
	$AllPatternCount = 0;
	for($i = 0 ; $i < count($TestConditionList); $i++) {
		$TestCondition = $TestConditionList[$i];
		
		$thisSelectionList = NULL;
		if ($IncludeOldResult) {
			$thisSelectionList = $DATestConditionSelection->GetNewestOrHasResultTestConditionSelectionList(
										$ProjectPID, $TestGroupPID, $TestPID, $TestCondition->PID);
		} else {
			$thisSelectionList = $DATestConditionSelection->GetNewestTestConditionSelectionList(
										$ProjectPID, $TestGroupPID, $TestPID, $TestCondition->PID);
		}
		$containerData = new TestConditionSelectionContainerData();
		if ($thisSelectionList != NULL) {
			$containerData->SelectionList = $thisSelectionList;
			$containerData->Index = 0;
			$containerData->Index2nd = 0;
		} else {
			$containerData->SelectionList = array();
			$containerData->Index = -1;
			$containerData->Index2nd = -1;
		}
		if (count($thisSelectionList) > 0) {
			if ($AllPatternCount == 0) {
				$AllPatternCount = 1;
			}
			$AllPatternCount *= count($thisSelectionList);
		}
		$containerData->PatternCountUntilThis = $AllPatternCount;
		array_push($TestConditionSelectionContainerList, $containerData);
	}
	for($j = 0 ; $j < count($TestConditionSelectionContainerList); $j++) {
		$SelCont = $TestConditionSelectionContainerList[$j];
		$SelCont->Calculate2ndCount($AllPatternCount);
	}
	return $TestConditionSelectionContainerList;
}

function InitializeAllSelectionListOfList($AllPatternCount, $TestConditionSelectionContainerList)
{
	$AllSelectionListOfList = array();
	if ($AllPatternCount > 0) {
		for($i = 0 ; $i < $AllPatternCount; $i++) {
			$AllSelectionList = array();
			
			$thisResultPID = "";
			$thisRowSelectionList = array();
			for($j = 0 ; $j < count($TestConditionSelectionContainerList); $j++) {
				$SelCont = $TestConditionSelectionContainerList[$j];
				
				if (count($SelCont->SelectionList) > 0) {
					if ($SelCont->Index >= 0 && $SelCont->Index < count($SelCont->SelectionList)) {
						array_push($AllSelectionList, $SelCont->SelectionList[$SelCont->Index]);
					}
					$SelCont->Index2nd++;
					if ($SelCont->Index2nd >= $SelCont->secondCount) {
						$SelCont->Index2nd = 0;
						$SelCont->Index++;
						
						if ($SelCont->Index >= count($SelCont->SelectionList)) {
							$SelCont->Index = 0;
						}
					}
				}
			}
			array_push($AllSelectionListOfList, $AllSelectionList);
		}
	}
	return $AllSelectionListOfList;
}

?>
