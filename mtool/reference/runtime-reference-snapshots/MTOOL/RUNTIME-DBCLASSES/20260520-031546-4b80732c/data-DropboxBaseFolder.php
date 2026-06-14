<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-DropboxBaseFolderBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-DropboxBaseFolder.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-DropboxBaseFolder.php` and extend `DropboxBaseFolderDataBase` for project-specific customizations.

    class DropboxBaseFolderData extends DropboxBaseFolderDataBase
    {
    }
}

?>
