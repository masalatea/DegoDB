<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-ChattopicAttachmentBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-ChattopicAttachment.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-ChattopicAttachment.php` and extend `ChattopicAttachmentDBAccessBase` for project-specific customizations.

    class ChattopicAttachmentDBAccess extends ChattopicAttachmentDBAccessBase
    {
    }
}

?>
