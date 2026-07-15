# Capacitor artifact import sample first slice / Capacitor artifact import sample first slice

Date: 2026-07-14

## Summary / summary

Added `sample/tutorials/sample35-capacitor-artifact-import` as a Capacitor-ready React sample that directly imports checked-in Mtool-style artifacts.

`sample/tutorials/sample35-capacitor-artifact-import` を追加し、Capacitor-ready React sample が checked-in の Mtool-style artifact を直接 import できることを確認する first slice とした。

## Completed scope / 完了scope

This first slice covers the agreed Mtool-intended operation surface:

- artifact import and artifact index review;
- project/app identity display;
- list/detail/form screen rendering;
- readonly/editable field rendering;
- local form draft state;
- required-field validation;
- `no-code-runtime-action-intent-v0` draft creation;
- mock/disabled submit handoff boundary;
- blocked/error state display;
- ownership boundary display.

この first slice は、合意した Mtool 想定操作面を一通り sample 上で表現する。

## Added files / 追加ファイル

- `sample/tutorials/sample35-capacitor-artifact-import/README.md`
- `sample/tutorials/sample35-capacitor-artifact-import/package.json`
- `sample/tutorials/sample35-capacitor-artifact-import/capacitor.config.ts`
- `sample/tutorials/sample35-capacitor-artifact-import/src/App.tsx`
- `sample/tutorials/sample35-capacitor-artifact-import/src/mtoolNoCodeBridge.ts`
- `sample/tutorials/sample35-capacitor-artifact-import/src/mtoolArtifacts.ts`
- `sample/tutorials/sample35-capacitor-artifact-import/src/MtoolArtifactSummary.tsx`
- `sample/tutorials/sample35-capacitor-artifact-import/src/MtoolScreenRenderer.tsx`
- `sample/tutorials/sample35-capacitor-artifact-import/src/MtoolActionIntentPanel.tsx`
- `sample/tutorials/sample35-capacitor-artifact-import/src/mtool-artifacts/*.sample.json`
- `sample/tutorials/sample35-capacitor-artifact-import/scripts/validate-sample.mjs`

## Catalog boundary / catalog boundary

`sample35` is registered as `app-wrapper-tutorial-sample`.

It is intentionally not a runtime pack and does not contain:

- `compose.yaml`;
- `run.sh`;
- `seed/`;
- `ios/`;
- `android/`.

`sample35` は `app-wrapper-tutorial-sample` として登録した。runtime pack ではなく、Docker runtime sample と native project sample のどちらにも寄せない。

## Validation / validation

Passed:

```bash
node sample/tutorials/sample35-capacitor-artifact-import/scripts/validate-sample.mjs
php -l mtool/app/sample_pack_catalog.php
php -r 'require "mtool/app/sample_pack_catalog.php"; if (!in_array("sample35-capacitor-artifact-import", app_sample_pack_category_map()["tutorials"], true)) { fwrite(STDERR, "missing sample35\n"); exit(1); } if (app_sample_pack_structure_type("sample35-capacitor-artifact-import") !== "app-wrapper-tutorial-sample") { fwrite(STDERR, "wrong structure\n"); exit(1); } if (in_array("sample35-capacitor-artifact-import", app_sample_pack_runtime_pack_names(), true)) { fwrite(STDERR, "sample35 must not be runtime pack\n"); exit(1); } echo "sample35 catalog ok\n";'
python3 - <<'PY'
from pathlib import Path
roots=[Path('docs'),Path('docs/internal'),Path('docs/study')]
missing=[]
for root in roots:
    for p in root.glob('*.md'):
        if 'English companion:' not in p.read_text(errors='replace'):
            missing.append(str(p))
print('\n'.join(sorted(missing)) if missing else 'all permanent docs have English companion')
PY
git diff --check
make test
```

Final `make test` result:

```text
OK, but incomplete, skipped, or risky tests!
Tests: 678, Assertions: 15761, Skipped: 6.
```

Note: direct local `./vendor/bin/phpunit` was not available in this checkout, so PHPUnit verification was performed through the existing Docker-backed `make test` route.

## Non-claims / 非claim

This first slice does not claim:

- dependency installation;
- `npm run build`;
- `npx cap sync`;
- native project generation;
- simulator/device QA;
- app signing or store submission.

Those checks remain external-owner or later optional gates.

## Status / status

Status: `FIRST_SLICE_DONE`.
