<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-TestLeftouterjoinProjectBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-TestLeftouterjoinProject.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-TestLeftouterjoinProject.php` and extend `TestLeftouterjoinProjectDataBase` for project-specific customizations.

    class TestLeftouterjoinProjectData extends TestLeftouterjoinProjectDataBase
    {
    }
}

?>
