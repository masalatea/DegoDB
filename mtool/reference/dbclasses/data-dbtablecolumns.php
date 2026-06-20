<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-DbtablecolumnsBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-Dbtablecolumns.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-Dbtablecolumns.php` and extend `DbtablecolumnsDataBase` for project-specific customizations.

    class DbtablecolumnsData extends DbtablecolumnsDataBase
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
