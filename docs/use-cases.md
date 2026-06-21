# Use Cases / ユースケース

English companion:
This page groups the practical use cases DegoDB can describe today and separates current support from future compatibility tracks.

DegoDB is a database-first and existing-database-first development toolkit. / DegoDB は、データベース起点・既存データベース起点の開発ツールキットです。

It turns database schemas into canonical metadata, generated code, API surfaces, and verified source output artifacts. / データベーススキーマから、正規化されたメタデータ、生成コード、API の外部仕様、検証済み Source Output 成果物を作ります。

This page organizes practical use cases for product positioning, examples, and consulting conversations. / このページでは、製品説明、例、相談案件で使う実用シナリオを整理します。

## Core Capabilities / 基本機能

- Import database schemas into canonical metadata / データベーススキーマを正規化されたメタデータへ取り込む
- Generate Data Classes / Data Class を生成する
- Generate DB Access layers / DB Access 層を生成する
- Generate Source Output artifacts / Source Output 成果物を生成する
- Generate OpenAPI / API surfaces / OpenAPI や API の外部仕様を生成する
- Export and import project metadata bundles / プロジェクトメタデータ一式を書き出し、取り込む
- Keep generated code, API definitions, and documentation aligned / 生成コード、API 定義、ドキュメントの整合性を保つ
- Support repeatable verification through tutorial samples and reference outputs / チュートリアルサンプルと参照出力で繰り返し検証できるようにする

## Database-First Development / データベース起点の開発

- Start a new application from a database-first schema / データベース起点のスキーマから新規アプリケーションを始める
- Generate application code from an existing production database / 既存の本番データベースからアプリケーションコードを生成する
- Generate API surfaces from database-backed metadata / データベース由来メタデータから API の外部仕様を生成する
- Build internal tools around existing business data / 既存の業務データを中心に社内ツールを作る
- Keep schema, generated code, and documentation moving together / スキーマ、生成コード、ドキュメントを一緒に更新していく

## Legacy Modernization / レガシー現代化

- Modernize a legacy database-backed application / データベースに支えられたレガシーアプリケーションを現代化する
- Document an undocumented or messy database / 文書化されていない、または整理されていないデータベースを文書化する
- Understand schema relationships before changing code / コード変更前にスキーマ関係を理解する
- Compare generated artifacts during migration work / 移行作業中に生成成果物を比較する
- Preserve behavior while replacing fragile handwritten data access code / 壊れやすい手書きのデータベースアクセスコードを置き換えながら振る舞いを保つ

## Consulting And Implementation Support / コンサルティングと実装支援

- Prepare technical handoff material for consulting or implementation projects / コンサルティングや実装案件向けの技術引き継ぎ資料を準備する
- Create repeatable schema documentation for client systems / 顧客システム向けに再現可能なスキーマドキュメントを作る
- Produce before-and-after artifacts for modernization proposals / 現代化提案用に変更前後の成果物を作る
- Separate proven current features from future compatibility tracks / 現行機能と将来対応候補を分けて説明する
- Discuss SaaS localization, billing, compliance, and enterprise readiness as implementation support topics, not as automatic domain expertise / SaaS のローカライズ、請求、コンプライアンス、エンタープライズ対応は、自動的に分かっている専門領域ではなく、実装支援の論点として扱う

## Current And Future Compatibility / 現行対応と将来対応

| Area / 領域 | Current refactored implementation / 現行リファクタ版 | Legacy reference / 旧実装参照 | Positioning / 位置づけ |
| --- | --- | --- | --- |
| Database / データベース | MySQL / MariaDB, SQLite, PostgreSQL as main verified targets / MySQL / MariaDB、SQLite、PostgreSQL を主な検証対象とする | SQL Server metadata hooks existed / SQL Server 向けメタデータ導線が存在した | Oracle and SQL Server are future enterprise compatibility tracks / Oracle と SQL Server は、エンタープライズ向けの将来対応候補 |
| Output language / 出力言語 | PHP-focused / PHP 主対象 | Data Class / DB Access could be generated for PHP, C#, Java, Objective-C, and Swift / Data Class / DB Access は PHP、C#、Java、Objective-C、Swift を実際に生成できていた | Do not claim non-PHP output as current support yet / PHP 以外の出力は現行対応済みとはまだ書かない |
| Proxy / API | PHP-focused / PHP 主対象 | PHP-oriented legacy path / PHP 中心の旧実装経路 | Keep C# / Java / mobile language proxy claims conservative / C#、Java、モバイル向け言語の proxy 対応主張は控えめにする |

## Related Examples / 関連 example

- [Examples / 例の索引](../examples/README.md) / シナリオ型の例の計画と一覧
- [Compatibility And Output Support / 対応範囲と出力サポート](compatibility-and-output-support.md) / 現行対応、旧実装参照、将来候補の安全な説明
- [Existing DB To Output / 既存 DB から出力まで](existing-db-to-output.md) / 既存 DB から生成出力までの現行主導線
- [Sample Tutorial Roadmap / sample 学習導線](sample-tutorial-roadmap.md) / 現行チュートリアルサンプルの学習導線
