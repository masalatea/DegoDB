<?php

// Generated wrapper entry migrated from a legacy data class.
// Keep custom properties, helper methods, and top-level helpers here.

require_once __DIR__ . '/base/data-ProjectBase.php';

class ProjectData extends ProjectDataBase
{
	public function Getoption_automatically_create_simple_proxy()
	{
		return ($this->option_automatically_create_simple_proxy == 1);
	}
	public function Getoption_automatically_create_custom_proxy()
	{
		return ($this->option_automatically_create_custom_proxy == 1);
	}
	public function Getoption_show_proxy_link()
	{
		return ($this->option_show_proxy_link == 1);
	}
	public function Getoption_auto_upload_after_build()
	{
		return ($this->option_auto_upload_after_build == 1);
	}
	public function Getoption_show_source()
	{
		return ($this->option_show_source == 1);
	}
	public function Getoption_show_detail()
	{
		return ($this->option_show_detail == 1);
	}
	public function Getoption_show_recommended_column_warning()
	{
		return ($this->option_show_recommended_column_warning == 1);
	}
	public function Getoption_all_source_include()
	{
		return ($this->option_all_source_include == 1);
	}
	public function Getoption_user_can_change_da_func_order()
	{
		return ($this->option_user_can_change_da_func_order == 1);
	}
	public function Getoption_build_dataclass_for_proxy_client_only_if_proxy_exist()
	{
		return ($this->option_build_dataclass_for_proxy_client_only_if_proxy_exist == 1);
	}
	public function IsMySQL()
	{
		return ($this->DBType == ProjectDBTypeEnum::$MYSQLONCLOUD);
	}
}
function GetProjectStorageTypeCaption($storagetype)
{
	switch($storagetype)
	{
		case ProjectStorageTypeEnum::$DROPBOX:
			return "DropBox";
	}
	return $storagetype;
}

function GetProjectDBTypeCaption($dbtype)
{
	switch($dbtype)
	{
		case ProjectDBTypeEnum::$DEFAULT:
			return "Default";
		case ProjectDBTypeEnum::$MYSQLONCLOUD:
			return "MySQL on Cloud";
		case ProjectDBTypeEnum::$SQLSERVER:
			return "SQL Server";
	}
	return $dbtype;
}

?>