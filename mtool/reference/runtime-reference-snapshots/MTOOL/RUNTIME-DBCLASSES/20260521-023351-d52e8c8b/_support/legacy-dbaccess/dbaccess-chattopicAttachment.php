<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-chattopicAttachmentBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-chattopicAttachment.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-chattopicAttachment.php` and extend `chattopicAttachmentDBAccessBase` for project-specific customizations.

    class chattopicAttachmentDBAccessLegacy extends chattopicAttachmentDBAccessBase
    {
    }
}

?>
