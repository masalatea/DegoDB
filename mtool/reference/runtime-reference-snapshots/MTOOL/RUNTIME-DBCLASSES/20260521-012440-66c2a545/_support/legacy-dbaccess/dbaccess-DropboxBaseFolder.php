<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-DropboxBaseFolderBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-DropboxBaseFolder.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-DropboxBaseFolder.php` and extend `DropboxBaseFolderDBAccessBase` for project-specific customizations.

    class DropboxBaseFolderDBAccessLegacy extends DropboxBaseFolderDBAccessBase
    {
    }
}

?>
