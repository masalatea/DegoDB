# DegoDB Background / DegoDB の背景

## What Is DegoDB? / DegoDB とは何か

DegoDB is a metadata-driven development workbench that starts from an existing schema and maintains a canonical model from which code, APIs, and other artifacts can be generated consistently.  
DegoDB は、既存のスキーマを起点に、コード、API、その他の成果物を一貫して生成できる canonical model を管理する、メタデータ駆動の開発ワークベンチです。

DegoDB is not a database design tool.  
DegoDB はデータベース設計ツールではありません。

Database design is considered an external concern.  
データベース設計は DegoDB の外側で扱う関心事とします。

However, the original concept also includes an educational and AI-assisted entrance before that boundary.  
ただし、初期構想には、その境界の手前に置く教育的かつ AI-assisted な入口も含まれています。

Some users do not begin with a database schema.  
利用者の中には、database schema から始められない人もいます。

They may begin with JSON files, JSON API responses, or JSON-based configuration that already work in a small system.  
そのような利用者は、小さな system の中で既に動いている JSON file、JSON API response、JSON based configuration から始まることがあります。

For those users, DegoDB keeps an optional conceptual entrance that helps an AI or engineer translate the JSON shape and current processing rules into a database design draft.  
そのような利用者に対して、DegoDB は JSON の形と現在の処理規則を AI や技術者が database design draft へ翻訳するための optional な概念入口を初期構想として保持しています。

This is a guidance layer, not a runtime feature.  
これは guidance layer であり、runtime feature ではありません。

It does not change the DegoDB mainline.  
これは DegoDB の mainline を変更しません。

DegoDB begins after a schema already exists and focuses on reducing the distance between:  
DegoDB は schema がすでに存在する地点から始まり、次の要素間の距離を縮めることに注力します。

- Database schemas / データベーススキーマ
- Metadata / メタデータ
- Generated code / 生成コード
- APIs / API
- Runtime artifacts / runtime 成果物
- Developer workflows / 開発ワークフロー

The optional JSON-to-DB entrance exists before this point.  
optional な JSON-to-DB entrance は、この地点より前に存在します。

Its output should be a reviewed schema or schema draft that can enter the normal DegoDB flow.  
その出力は、通常の DegoDB flow に入れる reviewed schema または schema draft であるべきです。

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

## JSON-First Entrance as a Philosophy / 思想としての JSON-first entrance

The JSON-first entrance is a conceptual and instructional part of the original DegoDB idea, not a product feature promise.  
JSON-first entrance は DegoDB の初期構想に含まれる概念および指示の layer であり、product feature の約束ではありません。

It exists because many users understand their data before they understand databases.  
これは、多くの利用者が database を理解する前に、自分の data を理解しているからです。

A user may not know normalization, transaction boundaries, or persistence strategy.  
利用者は、normalization、transaction boundary、persistence strategy を知らないかもしれません。

But the same user may be able to show:  
しかし同じ利用者でも、次のことは示せる場合があります。

- What JSON exists. / どの JSON が存在するか。
- Which keys matter. / どの key が重要か。
- When the JSON is read. / いつ JSON を読むか。
- When the JSON is written. / いつ JSON を書くか。
- Which records are updated together. / どの record 群が一緒に更新されるか。
- Which lists, searches, or API responses are needed. / どの list、search、API response が必要か。

The philosophical role of the JSON-first entrance is to turn this practical knowledge into a database design conversation.  
JSON-first entrance の思想的役割は、この実践的な知識を database design の会話へ変換することです。

AI can act as a translator in that conversation.  
AI はその会話の中で翻訳者として働けます。

The AI does not replace architectural judgment.  
AI は architectural judgment を置き換えません。

It produces a reviewable draft: candidate entities, columns, relationships, lifecycle notes, transaction boundaries, and DegoDB targets.  
AI が作るのは、entity 候補、column 候補、relationship、lifecycle notes、transaction boundary、DegoDB target を含む reviewable draft です。

That draft is then reviewed, corrected, and turned into the schema or metadata that DegoDB can actually use.  
その draft は人間によって review / correction され、その後 DegoDB が実際に扱える schema または metadata へ変換されます。

### What this is / これは何か

- A documentation and instruction layer. / documentation と instruction の layer。
- A way to document the original broader entrance to DegoDB. / DegoDB が初期構想として持っていた広い入口を文書化する考え方。
- A bridge from practical JSON knowledge to database design language. / 実践的な JSON 知識を database design language へつなぐ橋。
- A format that AI agents can read and execute. / AI agent が読んで実行できる format。
- A way to separate raw JSON, canonical tables, migration, and runtime writes. / raw JSON、canonical table、migration、runtime write を分ける考え方。

### What this is not / これは何ではないか

- Not a JSON auto-import feature. / JSON auto-import 機能ではない。
- Not a guarantee that arbitrary JSON can become a good schema automatically. / 任意の JSON が自動で良い schema になる保証ではない。
- Not a replacement for database design review. / database design review の代替ではない。
- Not a change to the DegoDB runtime input model. / DegoDB runtime input model の変更ではない。
- Not a promise that DegoDB will directly parse application JSON as canonical metadata. / DegoDB が application JSON を canonical metadata として直接 parse する約束ではない。

This distinction is essential.  
この区別は重要です。

If the JSON-first entrance is described as a feature, users may expect automatic conversion.  
JSON-first entrance を機能として説明すると、利用者は自動変換を期待してしまいます。

If it is described as an instruction layer, users and AI agents understand that it prepares a design draft for review.  
instruction layer として説明すれば、利用者と AI agent は、それが review 用の design draft を準備するものだと理解できます。

## Feature Boundary / 機能境界

DegoDB features begin where canonical metadata can be imported, stored, synchronized, generated, and verified.  
DegoDB の機能は、canonical metadata を import / store / synchronize / generate / verify できる地点から始まります。

The JSON-first entrance happens before that boundary.  
JSON-first entrance は、その境界より前で起きます。

Its deliverable is not a generated artifact.  
その成果物は generated artifact ではありません。

Its deliverable is a design draft that can be converted into one of the normal inputs:  
その成果物は、通常の input のいずれかへ変換可能な design draft です。

- A database schema that can be imported. / import 可能な database schema。
- A Lab DB schema to experiment with. / 実験用の Lab DB schema。
- A DegoDB metadata design task. / DegoDB metadata design task。
- A migration plan that separates raw JSON archive and canonical tables. / raw JSON archive と canonical table を分ける migration plan。

Therefore, documentation should use careful wording:  
そのため、documentation では次のように注意して書くべきです。

- Say "JSON to DB design draft", not "JSON to DB auto-conversion". / 「JSON to DB design draft」と書き、「JSON to DB auto-conversion」とは書かない。
- Say "AI-assisted translation", not "automatic schema generation". / 「AI-assisted translation」と書き、「automatic schema generation」とは書かない。
- Say "optional entrance", not "new mainline". / 「optional entrance」と書き、「new mainline」とは書かない。
- Say "reviewable draft", not "final schema". / 「reviewable draft」と書き、「final schema」とは書かない。

This keeps the philosophy useful without overstating current product behavior.  
これにより、現在の product behavior を過大に見せずに、この思想を有効に保てます。

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

This also applies before the database exists.  
これは database がまだ存在しない前段にも当てはまります。

When a system currently uses JSON, the implicit assumptions hidden in that JSON should be made explicit before they become database metadata.  
system が現在 JSON を使っている場合、その JSON に隠れた暗黙の前提は、database metadata になる前に明示されるべきです。

For example: identity, ownership, lifecycle, relationship, transaction boundary, and retention policy.  
たとえば identity、ownership、lifecycle、relationship、transaction boundary、retention policy です。

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

The JSON-first entrance follows the same principle.  
JSON-first entrance も同じ原則に従います。

It reduces the cognitive load for users who can explain their data in JSON terms but cannot yet express it as a normalized schema.  
それは、自分の data を JSON の言葉では説明できるが、normalized schema としてはまだ表現できない利用者の cognitive load を下げます。

The AI-facing contract exists so that this translation is repeatable instead of ad hoc.  
AI-facing contract は、この翻訳を ad hoc ではなく repeatable にするために存在します。

## Mainline Philosophy / mainline の考え方

DegoDB intentionally promotes a mainline.  
DegoDB は意図的に mainline を前面に出します。

Many development tools expose every possible path equally.  
多くの開発ツールは、可能な path をすべて同じ重みで露出します。

DegoDB favors a guided workflow:  
DegoDB は guided workflow を重視します。

Existing Schema -> Import -> Metadata -> Generation -> Experimentation  
Existing Schema -> Import -> Metadata -> Generation -> Experimentation という導線

When a user starts from JSON, the initial conceptual workflow is:  
利用者が JSON から始める場合、初期構想に含まれる概念 workflow は次のようになります。

JSON Shape -> AI-assisted Design Draft -> Reviewed Schema -> Import -> Metadata -> Generation -> Experimentation  
JSON Shape -> AI-assisted Design Draft -> Reviewed Schema -> Import -> Metadata -> Generation -> Experimentation という導線

Only the second half is DegoDB's functional mainline.  
この後半だけが DegoDB の機能的 mainline です。

The first half is an optional thinking and instruction layer.  
前半は optional な思考および指示の layer です。

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

The JSON-first entrance expresses this AI collaboration model in its original pre-design form.  
JSON-first entrance は、この AI collaboration model を初期構想に含まれていた pre-design の形で表します。

It gives an AI agent a bounded task: read JSON and current processing, produce a reviewable DB design draft, and clearly separate assumptions from blocking questions.  
それは AI agent に、JSON と現在の処理を読み、reviewable な DB design draft を作り、assumptions と blocking questions を明確に分ける、という境界のある task を与えます。

The AI is not asked to silently invent a final schema.  
AI は final schema を黙って作り上げることを求められていません。

It is asked to expose the reasoning that lets humans and DegoDB continue safely.  
人間と DegoDB が安全に続けられるよう、その reasoning を露出することが求められています。

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

DegoDB documentation may also support:
DegoDB documentation は、次の支援も行えます。

- Helping users describe existing JSON-based workflows. / 既存の JSON-based workflow を利用者が説明できるようにすること。
- Helping AI agents translate JSON shapes into reviewable schema drafts. / AI agent が JSON shape を reviewable schema draft へ翻訳できるようにすること。
- Clarifying where conceptual preparation ends and DegoDB features begin. / 概念的な準備がどこで終わり、DegoDB の機能がどこから始まるかを明確にすること。

## Non-Goals / 非目標

DegoDB is not:  
DegoDB は次のものではありません。

- A database design tool. / データベース設計ツール。
- A JSON-to-database automatic conversion engine. / JSON から database への自動変換 engine。
- A no-code platform. / no-code platform。
- A generic ORM framework. / 汎用 ORM framework。
- A complete application generator. / complete application generator。
- A replacement for architectural decision making. / architectural decision making の代替。

DegoDB assumes that design decisions have already been made elsewhere and focuses on making those decisions easier to operationalize.  
DegoDB は design decision が別の場所ですでに行われていることを前提にし、それらを operationalize しやすくすることに注力します。

For JSON-first users, "elsewhere" may include an AI-assisted preparation step.  
JSON-first の利用者にとって、この「別の場所」には AI-assisted な準備 step が含まれる場合があります。

That step can be documented by DegoDB, but it remains outside the functional runtime/generator boundary.  
その step は DegoDB の documentation で支援できますが、functional runtime / generator boundary の外側に留まります。

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
- Optional conceptual entrances for users who begin outside database design / database design の外側から始める利用者のための、初期構想に含まれる optional conceptual entrance

The historical name DegoDB remains as a reminder of the project's origins.  
DegoDB という歴史的な名前は、project の出自を示すものとして残っています。

While the scope has evolved, the original objective remains the same:  
scope は進化しましたが、最初の目的は変わっていません。

Reduce friction between system definition and system implementation.  
system definition と system implementation の間の friction を減らすことです。

## One-Sentence Summary / 一文要約

DegoDB is a metadata-driven development workbench that transforms existing schemas into a canonical model from which code, APIs, and other development artifacts can be generated consistently and reproducibly.  
DegoDB は、既存 schema を canonical model に変換し、そこから code、API、その他の development artifacts を一貫して再現可能に生成する metadata-driven development workbench です。
