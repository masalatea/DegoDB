# Documentation Index / 文書索引

English companion:
This index explains how to read the permanent documentation under `docs/`. The top-level surface is for external users who want to try building with Mtool, while contributor-only architecture and migration references now live one step inward under `docs/internal/`.

`docs/` は current implementation の恒久文書を置く場所です。  
最初に history を掘るのではなく、まずここにある日付なし文書を source of truth として読みます。  
top-level は外部ユーザ向け導線を優先し、内部向け文書は [Internal Documentation Index / 内部ドキュメント索引](internal/README.md) 配下へ 1 段下げます。

## 3 層の読み方

1. 入口 layer
   - [Quickstart / まず動かしてみる](quickstart.md)
   - [Start Here / 最初の入口](start-here.md)
   - [Choose Your Path / 目的別の読み方](choose-your-path.md)
2. golden path layer
   - [Existing DB To Output / 既存 DB から出力まで](existing-db-to-output.md)
   - [Common Tasks / よく使う作業](common-tasks.md)
   - [Current Supported Workflow / 現在サポートするワークフロー](current-supported-workflow.md)
   - [Troubleshooting / トラブルシューティング](troubleshooting.md)
3. detail layer
   - [Concept Overview / 概念概要](overview.md)
   - [Storage And State Model / 保存先と状態モデル](storage-and-state-model.md)
   - [Project Metadata Bundle / プロジェクトメタデータ bundle](project-metadata-bundle.md)
   - [Config DB Externalization / config DB 外部化](config-db-externalization.md)
   - [Glossary / 用語集](glossary.md)
   - [Sample Tutorial Roadmap / sample 学習導線](sample-tutorial-roadmap.md)
   - [Study Guide / sample で学ぶ](study/README.md)
   - [Internal Documentation Index / 内部ドキュメント索引](internal/README.md)

入口 layer で読む順番を決め、golden path layer で実行の流れを掴み、その後に detail layer へ降ります。  
detail doc だけを読んで mainline を再構成するのは current reading model ではありません。

## 最初の入口

- [DegoDB](../README.md)
  - public / repo top の入口
- [Quickstart / まず動かしてみる](quickstart.md)
  - clone 直後に local stack と sample01 を 1 周だけ動かす文書
- [Start Here / 最初の入口](start-here.md)
  - 最初の 5 分で repo の current な読み方を掴む文書
- [Choose Your Path / 目的別の読み方](choose-your-path.md)
  - 目的別に current doc と最初のコマンドを逆引きする文書
- [Existing DB To Output / 既存 DB から出力まで](existing-db-to-output.md)
  - existing DB 接続から canonical metadata 永続化、設計、output verify までの primary journey
- [Common Tasks / よく使う作業](common-tasks.md)
  - 起動、sample、test、bundle、config DB backup / durable env、runtime reference 確認の最短手順
- [Current Supported Workflow / 現在サポートするワークフロー](current-supported-workflow.md)
  - current mainline と archived helper の境界
- [Storage And State Model / 保存先と状態モデル](storage-and-state-model.md)
  - `config_db`、existing DB、artifact、`work/` の役割を 1 枚で見る companion
- [Sample Tutorial Roadmap / sample 学習導線](sample-tutorial-roadmap.md)
  - tutorial sample の学習順
- [Study Guide / sample で学ぶ](study/README.md)
  - tutorial sample を教材として読む hands-on guide
- [Internal Documentation Index / 内部ドキュメント索引](internal/README.md)
  - contributor / maintainer 向け内部 reference の索引
- [Test Guide / テストガイド](../tests/README.md)
  - test gate と scenario の案内

## Core Docs

- [Concept Overview / 概念概要](overview.md)
  - ツール本来の `DB 構造 -> import -> Data Class -> DB Access -> Source Output` の概念モデル
- [Existing DB To Output / 既存 DB から出力まで](existing-db-to-output.md)
  - existing DB 接続から output verify までの primary journey
- [Storage And State Model / 保存先と状態モデル](storage-and-state-model.md)
  - `config_db`、artifact、`work/` の state map
- [Current Supported Workflow / 現在サポートするワークフロー](current-supported-workflow.md)
  - current mainline と archived 導線の切り分け
- [Common Tasks / よく使う作業](common-tasks.md)
  - 起動、sample、test、config DB backup / durable env、runtime reference 操作の最短手順
- [Troubleshooting / トラブルシューティング](troubleshooting.md)
  - current supported lane で踏みやすい warning / error の切り分け
- [Glossary / 用語集](glossary.md)
  - repo 内の主要用語を短く揃える語彙集
- [Sample Tutorial Roadmap / sample 学習導線](sample-tutorial-roadmap.md)
  - tutorial sample を simple-to-complex に並べた正本
- [Study Guide / sample で学ぶ](study/README.md)
  - `sample01` から `sample17` を学習順に触るための guide

## Task And Reference Guides

- [Project Metadata Bundle / プロジェクトメタデータ bundle](project-metadata-bundle.md)
  - canonical metadata export / import preview / secret separation の正本
- [Config DB Externalization / config DB 外部化](config-db-externalization.md)
  - `config_db` の local overlay / external lane / preflight / migrate の正本
- [Sample Packs / sample pack 一覧](../sample/README.md)
  - sample pack 全体の案内
- [Sample Tutorial Packs / tutorial sample 一覧](../sample/tutorials/README.md)
  - tutorial lane の pack 一覧
- [Internal Documentation Index / 内部ドキュメント索引](internal/README.md)
  - 実装内部、architecture、migration map、AI / contributor contract

## Reports

- [2026 Reports / 2026 年の履歴](reports/2026/README.md)
  - 2026 年の report 索引

report は履歴、判断経緯、handoff、resume prompt のために残します。  
current supported workflow や stable rule を調べるときは、まず date-less な恒久文書を優先してください。

## Update Rules

- 恒久文書は日付なしファイル名で更新する
- 恒久文書は日本語本文を正本にしつつ、冒頭に英語 companion を添えて日英併記で維持する
- top-level `docs/` は外部ユーザ向け導線を優先し、個別 internal doc は [Internal Documentation Index / 内部ドキュメント索引](internal/README.md) から辿る
- 履歴として残す文書は `YYYY-MMDD-<slug>.md` を使う
- `docs/reports/` 配下の progress / handoff / resume prompt / slice report は日本語のみ運用でよい
- report で確定した stable rule は、必要に応じて日付なしの恒久文書へ移す
- `original-codes/docs/` は旧実装の調査資料であり、新実装 docs の source of truth ではない
