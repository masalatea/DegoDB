<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-DropboxOauth2StatusHashBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-DropboxOauth2StatusHash.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-DropboxOauth2StatusHash.php` and extend `DropboxOauth2StatusHashDataBase` for project-specific customizations.

    class DropboxOauth2StatusHashData extends DropboxOauth2StatusHashDataBase
    {
    }
}

?>
