<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-DBConnectionBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-DBConnection.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-DBConnection.php` and extend `DBConnectionDataBase` for project-specific customizations.

    class DBConnectionData extends DBConnectionDataBase
    {
    }
}
function GetDBConnectionDBServerTypeCaption($value)
{
	switch($value)
	{
		case DBConnectionDBServerTypeEnum::$DEFAULT:
			return "Default";
		case DBConnectionDBServerTypeEnum::$MYSQL:
			return "MySQL";
	}
	return $value;
}


?>
