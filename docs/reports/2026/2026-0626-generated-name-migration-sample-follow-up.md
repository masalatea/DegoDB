# 2026-06-26 Generated Name Migration Sample Follow-Up

## Status

- status: `SAMPLE_WIDE_COMMENT_AND_SEED_DOCS_SLICE_APPLIED`
- scope: canonical DataClass base template, tutorial sample generated DataClass base references, current sample seed descriptions, and current docs wording
- purpose: continue the generated name migration lane without changing legacy references

## Summary

The boundary-aware keyword map was validated against all tutorial sample references before code changes.

Validation result:

- sample count: `26`
- error count: `0`
- path change count: `0`
- text occurrence count: `187`
- warning count: `702`

The sample-wide transform/compare showed no file, path, or symbol rename conflicts. The representative sample differences were limited to DataClass base file comments that still used the old `dataclass / dataclassfields` wording.

## Applied Slice

Updated the canonical DataClass base template wording:

- from `canonical data class metadata`
- from `dataclass / dataclassfields`
- to `canonical DataClass metadata`
- to `DataClass / DataClass fields`

Then updated the generated tutorial sample DataClass base references with the same exact comment replacement.

Representative runtime generation was checked first on:

- `sample10-dbaccess-mini-crud-flow`
- `sample08-dbaccess-join-read-model`

The representative runtime output changed only the DataClass base comment lines. The same conflict-free replacement was then applied to the remaining tutorial sample generated DataClass base references.

Changed generated reference files:

- `48` tutorial sample reference files under `sample/tutorials/*/reference`

No legacy reference files were changed.

## Seed And Docs Classification

The remaining current seed/docs wording occurrences were classified separately from generated references.

Updated:

- tutorial sample project/source-output seed descriptions that described canonical DataClass output or metadata
- current non-historical docs that described DataClass metadata

Left unchanged:

- physical config DB table names such as `dataclass` and `dataclassfields`
- sample directory names that intentionally include `dataclass`
- historical reports and legacy/runtime reference symbols

## Verification

Passed:

```sh
make sample10-pack-runtime-test
make sample08-pack-runtime-test
make test
```

`make test` result: `249` tests, `8380` assertions, `1` skipped.

Also checked that these legacy reference paths have no diff:

```sh
git diff --name-only -- mtool/reference/legacy-dbclasses mtool/reference/legacy-mtool-build mtool/reference/legacy-mtool-templates
```

## Remaining Work

- Plan the physical/logical sample naming migration as a separate slice.
- Keep intentional historical/report references unchanged unless the document is being actively rewritten.
