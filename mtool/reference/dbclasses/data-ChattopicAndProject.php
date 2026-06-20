<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-ChattopicAndProjectBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-ChattopicAndProject.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-ChattopicAndProject.php` and extend `ChattopicAndProjectDataBase` for project-specific customizations.

    class ChattopicAndProjectData extends ChattopicAndProjectDataBase
    {
    }
}

?>
