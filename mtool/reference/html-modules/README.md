## HTML Module Sources

This tree is reserved for curated HTML module source trees that will replace
legacy snapshot and placeholder inputs over time.

Resolution order for `catalog://html-module/{project_key}/{source_output_key}`:

1. `mtool/reference/html-modules/{project_slug}/{source_output_key}/current`
2. `mtool/reference/legacy-source-snapshots/{project_slug}/html/{source_output_key}`
3. `mtool/reference/legacy-source-placeholders/{project_slug}/html/{source_output_key}`

Current status:

- `MTOOL` has `21` canonical HTML module roots under `mtool/reference/html-modules/mtool/*/current`
- current breakdown is `13` snapshot-backed roots and `8` placeholder-backed scaffold roots
- remaining fallback usage is `legacy-html-snapshot=0`, `legacy-html-placeholder=0`
- paired `project_source_outputs.artifact_strategy` for these modules is `html-module-catalog`
- generator dispatch for these modules lives in `app/project_output_html_module_generator.php`
- `scripts/bootstrap_html_module_roots.php` bootstraps fallback sources into canonical roots
- future canonical HTML source edits should happen under `.../current`, not under legacy snapshot roots
- new runtime/generator must not read `original-codes/` directly
