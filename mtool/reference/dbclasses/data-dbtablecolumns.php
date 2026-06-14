<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-dbtablecolumnsBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-dbtablecolumns.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-dbtablecolumns.php` and extend `dbtablecolumnsDataBase` for project-specific customizations.

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
}

?>
