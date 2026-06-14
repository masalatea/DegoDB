<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-TestBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-Test.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-Test.php` and extend `TestDataBase` for project-specific customizations.

    class TestData extends TestDataBase
    {
    }
}

?>
