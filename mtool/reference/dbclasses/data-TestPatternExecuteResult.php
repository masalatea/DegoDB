<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-TestPatternExecuteResultBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-TestPatternExecuteResult.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-TestPatternExecuteResult.php` and extend `TestPatternExecuteResultDataBase` for project-specific customizations.

    class TestPatternExecuteResultData extends TestPatternExecuteResultDataBase
    {
    }
}

?>
