<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-ChattopicBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-Chattopic.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-Chattopic.php` and extend `ChattopicDataBase` for project-specific customizations.

    class ChattopicData extends ChattopicDataBase
    {
    }
}

?>
