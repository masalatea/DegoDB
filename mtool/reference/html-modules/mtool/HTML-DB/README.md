## HTML-DB Canonical Module Root

`current/` is the preferred canonical source root for `catalog://html-module/MTOOL/HTML-DB`.

Current status:

- bootstrapped from `mtool/reference/legacy-source-snapshots/mtool/html/HTML-DB`
- kept inside the new resolver path so future edits no longer require DB seed changes
- intended to diverge incrementally from the legacy snapshot as `HTML-DB` is rewritten

Notes:

- runtime/generator must continue to avoid direct reads from `original-codes/`
- edits for the new canonical source should happen under `current/`
- once stable, `work/source-outputs/MTOOL/HTML-DB` should be reproducible from this root alone, and later integrated into an appropriate PSR-4-oriented path under `mtool/`
- rewrite inventory and current-route mapping live in `docs/internal/html-db-rewrite-map.md`
- cluster inventory can be regenerated with `php scripts/show_html_db_rewrite_map.php`
- current first rewrite seam is `project_source_output.php` and `project_source_output_edit.php`, which are staged as generated wrappers with `_legacy/` fallbacks
- `change_order` / `*_include.php` helper files may remain on the bridge temporarily until current route side absorbs them
