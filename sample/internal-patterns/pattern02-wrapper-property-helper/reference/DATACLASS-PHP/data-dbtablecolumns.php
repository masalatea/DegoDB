<?php

// Generated wrapper entry migrated from a legacy data class.
// Keep custom properties, helper methods, and top-level helpers here.

require_once __DIR__ . '/base/data-dbtablecolumnsBase.php';

class dbtablecolumnsData extends dbtablecolumnsDataBase
{
	public $ColumnListOrderSupposedToBe;
	
	function IsAutoIncrement()
	{
		$ThisIsAutoIncrement = false;
		if (preg_match("/auto.*inc/i", $this->Extra)) {
			$ThisIsAutoIncrement = true;
		}
		return $ThisIsAutoIncrement;
	}
	
	function NotSupportedDataTypeForInsertOrUpdateBasedOnDBType($project)
	{
		switch($project->DBType)
		{
			case ProjectDBTypeEnum::$DEFAULT:
			case ProjectDBTypeEnum::$MYSQLONCLOUD:
				return false;
			case ProjectDBTypeEnum::$SQLSERVER:
				
				if (
				    preg_match("/timestamp/i", $this->datatype)
					) {
					return true;
				}
				break;
		}
		return false;
	}
}
?>