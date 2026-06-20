<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-DafuncBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-Dafunc.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-Dafunc.php` and extend `DafuncDataBase` for project-specific customizations.

    class DafuncData extends DafuncDataBase
    {
	function IsInsertUpdateDeleteTargetVal()
	{
		return ($this->InsertUpdateDeleteParamType == DafuncInsertUpdateDeleteParamTypeEnum::$VAL);
	}
	function IsInsertUpdateDeleteTargetClassObject()
	{
		return ($this->InsertUpdateDeleteParamType == DafuncInsertUpdateDeleteParamTypeEnum::$CLASSOBJECT);
	}
	function IsInsertUpdateDeleteTargetSetByClassObjectAndWhereByValForUpdate()
	{
		return ($this->InsertUpdateDeleteParamType == DafuncInsertUpdateDeleteParamTypeEnum::$SETBYCLASSOBJECTANDWHEREBYVALFORUPDATE);
	}
	
	function GetBaseDataClassName()
	{
		switch($this->ActionType) {
			case DafuncActionTypeEnum::$SELECTSINGLE:
			case DafuncActionTypeEnum::$SELECTLIST:
				if ($this->DataClassBaseNameForSelectAction != "") {
					return $this->DataClassBaseNameForSelectAction;
				} else if ($this->name != "") {
					return $this->name;
				}
				break;
			case DafuncActionTypeEnum::$INSERT:
			case DafuncActionTypeEnum::$UPDATE:
			case DafuncActionTypeEnum::$DELETE:
				if ($this->InsertUpdateDeleteTargetTable != "") {
					return $this->InsertUpdateDeleteTargetTable;
				} else if ($this->name != "") {
					// This is default
					return $this->name;
				}
				break;
			default:
				print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
				break;
		}
		return "";
	}
	function GetInsertUpdateDeleteTargetTable()
	{
		switch($this->ActionType) {
			case DafuncActionTypeEnum::$SELECTSINGLE:
			case DafuncActionTypeEnum::$SELECTLIST:
				break;
			case DafuncActionTypeEnum::$INSERT:
			case DafuncActionTypeEnum::$UPDATE:
			case DafuncActionTypeEnum::$DELETE:
				if ($this->InsertUpdateDeleteTargetTable != "") {
					return $this->InsertUpdateDeleteTargetTable;
				} else if ($this->name != "") {
					// This is default
					return $this->name;
				}
				break;
			default:
				print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
				break;
		}
		return "";
	}
	
	function IsInsertFunction()
	{
		switch($this->ActionType) {
			case DafuncActionTypeEnum::$SELECTSINGLE:
			case DafuncActionTypeEnum::$SELECTLIST:
			case DafuncActionTypeEnum::$UPDATE:
			case DafuncActionTypeEnum::$DELETE:
				break;
			case DafuncActionTypeEnum::$INSERT:
				return true;
			default:
				print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
				break;
		}
		return false;
	}
	
	function IsLoginByLoginCookieToken()
	{
		switch($this->SingleProxy_AuthType) {
			case DafuncSingleProxy_AuthTypeEnum::$DEFAULT:
			case DafuncSingleProxy_AuthTypeEnum::$PROJECTTOKEN:
			case DafuncSingleProxy_AuthTypeEnum::$GETFUNC:
			case DafuncSingleProxy_AuthTypeEnum::$PROJECTTOKENORGETFUNC:
			case DafuncSingleProxy_AuthTypeEnum::$NOSECURITY:
			case DafuncSingleProxy_AuthTypeEnum::$MANUAL:
				break;
			case DafuncSingleProxy_AuthTypeEnum::$LOGINCOOKIETOKEN:
				return true;
			default:
				print "INTERNAL ERROR! Unknown Auth Type: " . $this->SingleProxy_AuthType . "\n";
		}
		return false;
	}
    }
}
function GetDafuncInsertUpdateDeleteParamTypeCaption($paramType)
{
	switch($paramType)
	{
		case DafuncInsertUpdateDeleteParamTypeEnum::$DEFAULT:
			return "Not Selected";
		case DafuncInsertUpdateDeleteParamTypeEnum::$VAL:
			return "Value";
		case DafuncInsertUpdateDeleteParamTypeEnum::$CLASSOBJECT:
			return "Class Object";
		case DafuncInsertUpdateDeleteParamTypeEnum::$SETBYCLASSOBJECTANDWHEREBYVALFORUPDATE:
			return "Class Object for Set Target / Value for Where";
	}
	return $paramType;
}

function GetDafuncORGroupTypeCaption($paramType)
{
	switch($paramType)
	{
		case DafuncORGroupTypeEnum::$DEFAULT:
			return "Not Selected";
		case DafuncORGroupTypeEnum::$ORANDOR:
			return "(.. or ..) and (.. or ..)";
		case DafuncORGroupTypeEnum::$ANDORAND:
			return "(.. and ..) or (.. and ..)";
	}
	return $paramType;
}
function GetWorkingORGroupType($paramType)
{
	switch($paramType)
	{
		case DafuncORGroupTypeEnum::$DEFAULT:
			return DafuncORGroupTypeEnum::$ORANDOR;
		case DafuncORGroupTypeEnum::$ORANDOR:
			return $paramType;
		case DafuncORGroupTypeEnum::$ANDORAND:
			return $paramType;
	}
	return $paramType;
}

function GetDAFuncActionTypeCaption($ActionType)
{
	switch($ActionType) {
		case DafuncActionTypeEnum::$SELECTSINGLE:
			return "Select(Single)";
		case DafuncActionTypeEnum::$SELECTLIST:
			return "Select(List)";
		case DafuncActionTypeEnum::$INSERT:
			return "Insert";
		case DafuncActionTypeEnum::$UPDATE:
			return "Update";
		case DafuncActionTypeEnum::$DELETE:
			return "Delete";
		default:
			print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
			return "";
	}
}

function GetSingleProxyAuthTypeCaption($value)
{
	switch($value) {
		case DafuncSingleProxy_AuthTypeEnum::$DEFAULT:
			return "Default";
		case DafuncSingleProxy_AuthTypeEnum::$PROJECTTOKEN:
			return "Project's Token (default)";
		case DafuncSingleProxy_AuthTypeEnum::$GETFUNC:
			return "Get Function";
		case DafuncSingleProxy_AuthTypeEnum::$PROJECTTOKENORGETFUNC:
			return "Project's Token or Get Function";
		case DafuncSingleProxy_AuthTypeEnum::$NOSECURITY:
			return "No Security";
		case DafuncSingleProxy_AuthTypeEnum::$MANUAL:
			return "Manual";
		case DafuncSingleProxy_AuthTypeEnum::$LOGINCOOKIETOKEN:
			return "Login Cookie Token";
		default:
			print "INTERNAL ERROR! Unknown Auth Type: " . $value . "\n";
			return "";
	}
}


?>
