<?PHP

function GetTargetDropboxBaseFolderPIDBasedOnProjectOrProjectSourceOutput($project, $ProjectSourceOutput)
{
	if ($ProjectSourceOutput->DropboxBaseFolderPID > 0) {
		return $ProjectSourceOutput->DropboxBaseFolderPID;
	}
	return $project->DropboxBaseFolderPID;
}

?>
