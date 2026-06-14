# Internal Documentation Index / 内部ドキュメント索引

English companion:
This index is the one-step inward lane for contributor-facing internals. Keep the top-level `docs/` surface focused on external users who want to try building with Mtool, and use `docs/internal/` only when you need implementation contracts, architecture, or migration/reference maps.

`docs/internal/` は、実装内部、architecture、migration map、AI / contributor contract をまとめる 1 段内側の索引です。  
表側の `docs/` は「Mtool を使って開発してみたい外部ユーザ」の導線を優先し、この配下は内部判断や実装変更が必要な時だけ使います。

## internal docs に入る前に

1. [DegoDB](../../README.md)
2. [Documentation Index / 文書索引](../README.md)
3. [Start Here / 最初の入口](../start-here.md)
4. [Choose Your Path / 目的別の読み方](../choose-your-path.md)
5. [Existing DB To Output / 既存 DB から出力まで](../existing-db-to-output.md)

top-level `docs/` の入口から個別 internal doc へ直接飛ぶのではなく、まずこの索引を 1 段はさみます。

## 役割

- top-level `docs/` は external / user-facing guide
- `docs/internal/` は contributor / maintainer / AI operator 向け reference
- `docs/reports/` は履歴、handoff、resume prompt の保存先

## Contributor Contracts

- [AI Operator Contract / AI operator contract](ai-operator-contract.md)
  - AI / contributor の再開順、checkpoint、handoff payload を固定する
- [Repo Boundaries / repository 境界](repo-boundaries.md)
  - root directory ごとの durable / disposable / host-side reference boundary を整理する

## Architecture

- [Runtime Architecture / ランタイム構成](runtime-architecture.md)
  - `admin` / `lab` / Docker / path boundary の技術構成
- [Generated Code Strategy / 生成コード方針](generated-code-strategy.md)
  - generated runtime、runtime reference、promote / restore の扱い
- [Site Boundaries / サイト境界](site-boundaries.md)
  - `admin` / `lab` / `app` / `reference` の責務分離
- [Source Output Path Policy / source output path 方針](source-output-path-policy.md)
  - `mtool/`、`mtool/extensions/`、`sample/`、`work/` の役割分担
- [Auth Architecture / 認証構成](auth-architecture.md)
  - 認証まわりの current ルール
- [Data Model / 最小データモデル](data-model.md)
  - canonical metadata と関連 entity の見取り図

## Migration And Reference Maps

- [Mtool Admin Roadmap / Mtool Admin 再構築ロードマップ](mtool-admin-roadmap.md)
  - 旧画面 / 旧 file 群と新 route の対応表
- [HTML-DB Rewrite Map / HTML-DB rewrite map](html-db-rewrite-map.md)
  - `HTML-DB` rewrite inventory と current route mapping
- [Legacy/New DB Mapping / 旧・新 DB 対応リスト](legacy-new-db-mapping.md)
  - 旧 DB / 新 DB の対応表
- [LanguageResource Separation / LanguageResource 分離方針](language-resource-separation.md)
  - `LanguageResource` の source of truth と分離方針

## Update Rules

- top-level `docs/` は外部ユーザ向け導線を優先し、個別 internal doc はこの索引から辿る
- 恒久文書は日本語本文を正本にしつつ、冒頭に英語 companion を添えて日英併記で維持する
- `docs/reports/` 配下の progress / handoff / resume prompt / slice report は日本語のみ運用でよい
- report で確定した stable rule は、必要に応じて日付なしの恒久文書へ移す
- `original-codes/docs/` は旧実装の調査資料であり、新実装 docs の source of truth ではない
