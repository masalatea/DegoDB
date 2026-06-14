<?php

include_once("/srv/legacy/www/mtool_lib/lib_mtool_dbtable_import_core.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dbtable_import.php");

$ProjectPID = trim(GetParam("ProjectPID"));

$NoError = true;

if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}

$importMySQL = NULL;
if ($NoError) {
	$importMySQL = connect_to_synchronize_target_db($ProjectPID);
}

$project = NULL;
if ($NoError) {
	$DAProject = new ProjectDBAccess();
	$project = $DAProject->GetProject($ProjectPID);
}

$show_recommended_column_warning = false;
if ($NoError) {
	$show_recommended_column_warning = $project->Getoption_show_recommended_column_warning();
}

?>
