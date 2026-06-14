# DegoDB Background / DegoDB の背景

## What Is DegoDB? / DegoDB とは何か

DegoDB is a metadata-driven development workbench that starts from an existing schema and maintains a canonical model from which code, APIs, and other artifacts can be generated consistently.  
DegoDB は、既存のスキーマを起点に、コード、API、その他の成果物を一貫して生成できる canonical model を管理する、メタデータ駆動の開発ワークベンチです。

DegoDB is not a database design tool.  
DegoDB はデータベース設計ツールではありません。

Database design is considered an external concern.  
データベース設計は DegoDB の外側で扱う関心事とします。

DegoDB begins after a schema already exists and focuses on reducing the distance between:  
DegoDB は schema がすでに存在する地点から始まり、次の要素間の距離を縮めることに注力します。

- Database schemas / データベーススキーマ
- Metadata / メタデータ
- Generated code / 生成コード
- APIs / API
- Runtime artifacts / runtime 成果物
- Developer workflows / 開発ワークフロー

The goal is not simply code generation.  
目的は単なるコード生成ではありません。

The goal is to reduce drift between related artifacts and make development workflows more explicit and reproducible.  
関連する成果物同士の drift を減らし、開発ワークフローをより明示的かつ再現可能にすることが目的です。

## Why DegoDB Exists / DegoDB が存在する理由

Modern projects often suffer from metadata fragmentation.  
現代的なプロジェクトでは、メタデータの分断がしばしば問題になります。

Database schemas, generated code, API definitions, documentation, and runtime behavior gradually drift apart.  
データベーススキーマ、生成コード、API 定義、ドキュメント、runtime behavior は、時間とともに少しずつ乖離していきます。

As systems evolve, developers spend increasing amounts of time answering questions such as:  
システムが進化するにつれて、開発者は次のような問いに時間を使うようになります。

- Which artifact is authoritative? / どの成果物が authoritative なのか
- Which file should be edited? / どのファイルを編集すべきなのか
- What needs regeneration? / 何を再生成する必要があるのか
- Which workflow is currently supported? / 現在 support されている workflow はどれなのか
- What is the source of truth? / source of truth は何なのか

DegoDB was created to reduce this drift.  
DegoDB は、この drift を減らすために作られました。

Rather than treating generated artifacts as independent assets, DegoDB treats metadata as the canonical model and generates downstream artifacts from that model.  
DegoDB は生成物を独立した資産として扱うのではなく、メタデータを canonical model として扱い、そこから downstream artifacts を生成します。

## Problem Statement / 問題設定

Many development environments contain multiple representations of the same information.  
多くの開発環境には、同じ情報を表す複数の表現が存在します。

For example:  
例:

Schema -> Data Class -> DB Access -> API -> Documentation  
Schema -> Data Class -> DB Access -> API -> Documentation という流れ

Over time these representations diverge.  
時間が経つにつれて、これらの表現は乖離します。

When divergence occurs:  
乖離が起きると、次の問題が発生します。

- Maintenance costs increase. / 保守コストが増える。
- Onboarding becomes difficult. / onboarding が難しくなる。
- Automation becomes unreliable. / automation の信頼性が下がる。
- AI-assisted development becomes harder. / AI-assisted development が難しくなる。

DegoDB attempts to make relationships between these layers explicit and reproducible.  
DegoDB は、これらの layer 間の関係を明示的かつ再現可能にしようとします。

## Design Principles / 設計原則

### Canonical Source / 正本となる source

There should be a clearly identifiable source of truth.  
明確に識別できる source of truth があるべきです。

Developers should not have to guess which artifact is authoritative.  
開発者が、どの成果物が authoritative なのかを推測しなくて済むべきです。

### Mainline First / mainline 優先

Many paths may exist.  
複数の path が存在しても構いません。

Only one path should be obvious.  
ただし、明らかに辿るべき path は 1 つであるべきです。

DegoDB intentionally promotes a mainline workflow that minimizes cognitive load.  
DegoDB は cognitive load を最小化するため、意図的に mainline workflow を前面に出します。

### Explicit Over Implicit / 暗黙より明示

Relationships should be visible.  
関係性は見えるべきです。

Generation steps, metadata ownership, and workflow boundaries should be documented and discoverable.  
生成手順、metadata ownership、workflow boundaries は文書化され、発見可能であるべきです。

### Reproducible Generation / 再現可能な生成

Generated artifacts are disposable.  
生成物は disposable です。

The canonical model is the long-term asset.  
長期的な資産は canonical model です。

Outputs should be reproducible from metadata.  
出力は metadata から再現可能であるべきです。

### Cognitive Load Reduction / cognitive load の削減

The primary objective is not code generation.  
主目的はコード生成ではありません。

The primary objective is reducing cognitive load.  
主目的は cognitive load を下げることです。

Developers should not need to remember:  
開発者は次のことを覚えておく必要がない状態であるべきです。

- Where metadata lives. / metadata がどこにあるか。
- Which files are authoritative. / どのファイルが authoritative か。
- What must be regenerated. / 何を再生成すべきか。
- Which workflow is currently supported. / どの workflow が現在 support されているか。

These relationships should be explicit.  
これらの関係性は明示されているべきです。

## Mainline Philosophy / mainline の考え方

DegoDB intentionally promotes a mainline.  
DegoDB は意図的に mainline を前面に出します。

Many development tools expose every possible path equally.  
多くの開発ツールは、可能な path をすべて同じ重みで露出します。

DegoDB favors a guided workflow:  
DegoDB は guided workflow を重視します。

Existing Schema -> Import -> Metadata -> Generation -> Experimentation  
Existing Schema -> Import -> Metadata -> Generation -> Experimentation という導線

The mainline is not the only path.  
mainline は唯一の path ではありません。

It is the path optimized for understanding, maintainability, and onboarding.  
それは understanding、maintainability、onboarding のために最適化された path です。

## Human and AI Development / 人間と AI による開発

DegoDB assumes that both humans and AI agents participate in development.  
DegoDB は、人間と AI agent の両方が開発に参加することを前提にします。

For this reason:  
そのため、次の性質を重視します。

- Workflows should be explicit. / workflow は明示的であるべきです。
- Documentation should be discoverable. / documentation は発見可能であるべきです。
- Metadata should be canonical. / metadata は canonical であるべきです。
- Generated artifacts should be reproducible. / generated artifacts は再現可能であるべきです。
- Boundaries and responsibilities should be visible. / boundaries と responsibilities は見えるべきです。

A repository should be understandable not only by its authors, but also by future maintainers and automated agents.  
repository は、作成者だけでなく、将来の maintainer や automated agent にも理解できるべきです。

## Scope / 対象範囲

DegoDB focuses on:  
DegoDB は次のことに注力します。

- Importing existing structures. / 既存構造の取り込み。
- Maintaining canonical metadata. / canonical metadata の維持。
- Generating repeatable artifacts. / 再現可能な成果物の生成。
- Supporting API experimentation. / API experimentation の支援。
- Reducing workflow ambiguity. / workflow ambiguity の削減。
- Improving discoverability of project structure. / project structure の発見可能性向上。

## Non-Goals / 非目標

DegoDB is not:  
DegoDB は次のものではありません。

- A database design tool. / データベース設計ツール。
- A no-code platform. / no-code platform。
- A generic ORM framework. / 汎用 ORM framework。
- A complete application generator. / complete application generator。
- A replacement for architectural decision making. / architectural decision making の代替。

DegoDB assumes that design decisions have already been made elsewhere and focuses on making those decisions easier to operationalize.  
DegoDB は design decision が別の場所ですでに行われていることを前提にし、それらを operationalize しやすくすることに注力します。

## Project History / プロジェクト履歴

DegoDB began as a database-oriented code generation tool.  
DegoDB は database-oriented code generation tool として始まりました。

Over time, the focus expanded beyond database access generation toward:  
時間とともに、焦点は database access generation から次の領域へ広がりました。

- Metadata management / metadata の管理
- Workflow definition / workflow の定義
- Reproducible generation / 再現可能な生成
- Documentation structure / documentation 構造
- Human and AI collaboration / 人間と AI の協働

The historical name DegoDB remains as a reminder of the project's origins.  
DegoDB という歴史的な名前は、project の出自を示すものとして残っています。

While the scope has evolved, the original objective remains the same:  
scope は進化しましたが、最初の目的は変わっていません。

Reduce friction between system definition and system implementation.  
system definition と system implementation の間の friction を減らすことです。

## One-Sentence Summary / 一文要約

DegoDB is a metadata-driven development workbench that transforms existing schemas into a canonical model from which code, APIs, and other development artifacts can be generated consistently and reproducibly.  
DegoDB は、既存 schema を canonical model に変換し、そこから code、API、その他の development artifacts を一貫して再現可能に生成する metadata-driven development workbench です。
