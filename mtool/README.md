# Mtool Current Implementation / Mtool 現行実装

English companion:
`mtool/` is the source of truth for the current DegoDB runtime, metadata application, generators, scripts, and curated compatibility references. Start here for directory ownership; use `docs/internal/README.md` for architecture and migration details.

`mtool/` は、DegoDB の現行 runtime、metadata application、generator、script、整理済み compatibility reference の正本です。directory の責務はこの文書、architecture と migration の詳細は [Internal Documentation Index](../docs/internal/README.md) から確認します。

## Directory map / directory 地図

| Path | Role / 役割 |
| --- | --- |
| `app/` | admin / lab が共有する current application code。詳細は [app README](app/README.md)。 |
| `admin/` | Admin surface の front controller と public web root。 |
| `lab/` | Lab / verification surface の front controller と public web root。 |
| `shared/` | app/runtime 間で共有する小さな contract。新しい汎用 application logic は `app/` を優先する。 |
| `scripts/` | Current CLI、validator、migration/debug helper。恒久手順は root Make target または `docs/common-tasks.md` を優先する。 |
| `docker/` | Mtool compose override、config schema/seed、container asset。 |
| `extensions/` | 明示的に optional な extension/module。 |
| `resources/` | MTOOL LanguageResource の file-based source of truth。詳細は [resource README](resources/README.md)。 |
| `reference/` | 整理済み legacy reference、current runtime reference、source template。現行 runtime の直接入力に legacy reference を使わない。 |
| `archive/` | current mainline ではない historical implementation asset。 |

## Current boundaries / 現在の境界

- Current runtime / generator / Docker container は、`reference/legacy-*` を直接の実行入力にしません。
- Admin / lab の共通 application logic は `app/` に置き、front controller 固有の wiring だけを `admin/` / `lab/` に置きます。
- 新しい CLI や validator は `scripts/` に置き、debug/migration 専用 entrypoint は用途が分かる subdirectory に分けます。
- generated artifact、compare workspace、test output は `mtool/` に保存せず、`work/` または各 sample の disposable path を使います。

## Main references / 主な参照先

- [Current Supported Workflow](../docs/current-supported-workflow.md)
- [Runtime Architecture](../docs/internal/runtime-architecture.md)
- [Generated Code Strategy](../docs/internal/generated-code-strategy.md)
- [Repository Boundaries](../docs/internal/repo-boundaries.md)
- [Common Tasks](../docs/common-tasks.md)
