<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-BuildTokenBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-BuildToken.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-BuildToken.php` and extend `BuildTokenDataBase` for project-specific customizations.

    class BuildTokenData extends BuildTokenDataBase
    {
    }
}

?>
