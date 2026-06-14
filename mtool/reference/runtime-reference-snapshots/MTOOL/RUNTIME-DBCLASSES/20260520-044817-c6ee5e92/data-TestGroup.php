<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-TestGroupBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-TestGroup.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-TestGroup.php` and extend `TestGroupDataBase` for project-specific customizations.

    class TestGroupData extends TestGroupDataBase
    {
    }
}

?>
