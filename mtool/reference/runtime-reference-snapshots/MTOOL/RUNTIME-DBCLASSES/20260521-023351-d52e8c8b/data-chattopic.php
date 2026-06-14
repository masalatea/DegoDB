<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-chattopicBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-chattopic.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-chattopic.php` and extend `chattopicDataBase` for project-specific customizations.

    class chattopicData extends chattopicDataBase
    {
    }
}

?>
