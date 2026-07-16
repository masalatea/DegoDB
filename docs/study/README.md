# Study Guide / sample で学ぶ

English companion:
This study guide explains how to learn DegoDB by walking through the tutorial sample packs. Use it after the Quickstart, when you want to understand what each sample teaches and what files to inspect.

この directory は、Quickstart の次に読む study guide です。  
`sample/tutorials/` の sample を、簡単なものから複雑なものへ順番に触りながら、DegoDB の使い方を学ぶために置きます。

## 読む順番

1. [sample01-first-run.md](sample01-first-run.md)
   - 1 table の最小 end-to-end を触る
2. [data-class-lane.md](data-class-lane.md)
   - `sample02` から `sample04` で Data Class の読み方を学ぶ
3. [db-access-lane.md](db-access-lane.md)
   - `sample05` から `sample09` で DB Access metadata と出力を学ぶ
4. [mini-crud-flow.md](mini-crud-flow.md)
   - `sample10` で小さな CRUD flow としてまとめて見る
5. [html-source-output.md](html-source-output.md)
   - `sample11` で HTML template / HTML Source Output の最小 publish flow を見る
6. [external-db-source-import.md](external-db-source-import.md)
   - `sample12` で external named source から import / sync / publish へ進む流れを見る
7. [openapi-api-surface.md](openapi-api-surface.md)
   - `sample13` で single-function proxy target metadata から OpenAPI artifact を publish する流れを見る
8. [custom-proxy-runtime.md](custom-proxy-runtime.md)
   - `sample14` で custom proxy metadata から proxy server artifact を publish する流れを見る
9. [project-metadata-export-import.md](project-metadata-export-import.md)
   - `sample15` で project metadata bundle の export / preview / apply を見る
10. [authenticated-proxy.md](authenticated-proxy.md)
   - `sample16` で static bearer authenticated proxy と fail-closed behavior を見る
11. [multi-output-capstone.md](multi-output-capstone.md)
   - `sample17` で同じ project から複数 Source Output を publish する流れを見る
12. [sample18 README](../../sample/tutorials/sample18-mini-task-board-demo/README.md)
   - `sample18` で AI が整理した prompt から動く TaskCard demo を作り、`web-lab` の sample page まで触る流れを見る
13. [ebook-cms-lane.md](ebook-cms-lane.md)
   - `sample19` から `sample26` で JSON-first idea から ebook / headless CMS 風 artifact へ進む流れを見る
14. [sample27 README](../../sample/tutorials/sample27-app-local-persistence-demo/README.md) から [sample32 README](../../sample/tutorials/sample32-no-code-ui-test-lab/README.md)
   - App-local persistence、generated no-code runtime、sync、domain repeatability、UI contract lab を順に見る
15. [sample33 README](../../sample/tutorials/sample33-sqlite-to-mysql-promotion/README.md) と [sample34 README](../../sample/tutorials/sample34-sqlite-to-firebird-promotion/README.md)
   - SQLite-first state の一方向 promotion artifact/checkpoint を見る
16. [sample35 README](../../sample/tutorials/sample35-capacitor-artifact-import/README.md) から [sample37 README](../../sample/tutorials/sample37-shared-state-sync-client-input/README.md)
   - external app、sync server、sync client owner へ渡す artifact/packet boundary を見る
17. [sample38 README](../../sample/tutorials/sample38-shared-state-sync-node-runtime/README.md) から [sample44 README](../../sample/tutorials/sample44-raycast-fps-line-demo/README.md)
   - shared-state runtime reference を chat、whiteboard、room game へ適用する流れを見る
18. [sample45 README](../../sample/tutorials/sample45-map-info-popup-site/README.md) から [sample47 README](../../sample/tutorials/sample47-open-world-rpg-demo/README.md)
   - static-first information/adventure demo と room RPG の domain packet 接続を見る

## study の基本姿勢

各 sample は、まず test で green を確認し、その後に seed、reference、generated output を読みます。  
最初から全 SQL や全 PHP を理解しようとせず、次の 4 点だけを追います。

- 何の table を seed しているか
- どの canonical metadata が seed され、どれが import / sync で作られるか
- どの source output が生成されるか
- reference と actual output が何を保証しているか

sample README に載っている manual flow は、仕組みを分解して追うための optional path です。
初回は `make sampleNN-pack-runtime-test` だけで構いません。test が通った後に、manual flow の各 command が checker 内で何をしているかを確認します。

## sample11-18 の読み方

`sample11` から `sample17` は Source Output lane です。`sample18` は、それらを使って AI が整理した prompt から小さな動作 demo を作る instruction-driven demo lane です。

| sample | まず見るもの | 迷った時の見方 |
| --- | --- | --- |
| `sample11` | `HTML-PAGE` | HTML template authoring ではなく、curated HTML module tree を Source Output として publish する例 |
| `sample12` | `named-live-schema:sample12_lab` | config DB seed ではなく、外部 DB 相当の `db-lab` から table metadata を取り込む例 |
| `sample13` | `OPENAPI-JSON` | proxy 実行ではなく、DBAccess function から OpenAPI artifact を作る例 |
| `sample14` | `CUSTOM-PROXY-SERVER` | 複数 DBAccess step を custom proxy metadata で束ね、PHP server artifact を作る例 |
| `sample15` | `PROJECT-METADATA-BUNDLE` | generated code ではなく、設計 metadata を export / import する例 |
| `sample16` | `AUTH-PROXY-SERVER` | static bearer auth が fail-closed になることを generated artifact で確認する例 |
| `sample17` | four outputs | 同じ project から DataClass / DBAccess / HTML / OpenAPI を publish する final capstone |
| `sample18` | cleaned prompt | TaskCard の CRUD DBAccess、HTML / OpenAPI、web-lab sample page を含む instruction-driven demo |

## sample19-26 の読み方

`sample19` から `sample26` は ebook CMS lane です。production CMS を完成させるためではなく、JSON-first idea を Mtool metadata に解釈し、DataClass / DBAccess / HTML / OpenAPI / auth proxy / metadata bundle を段階的に読むための教材として扱います。

詳しくは [ebook-cms-lane.md](ebook-cms-lane.md) を参照します。

## sample27-47 の読み方

`sample27-32` は App-local / no-code、`sample33-34` は DB promotion、`sample35-37` は external handoff、`sample38-47` は dependency-free shared-state / domain demo lane です。後半は Docker runtime pack とは限らないため、各 README の focused validator と production boundary を先に確認します。

`sample38-47` は動く reference/demo ですが、authentication、moderation、production persistence、public deployment、real WebSocket infrastructure、anti-cheat などを暗黙に含みません。

## Quickstart との関係

Quickstart は「local stack が動くこと」を確認するための入口です。  
study guide は「sample を教材として使えること」を確認するための入口です。

```bash
make env
make up-mtool
make sample01-pack-runtime-test
```

ここまで通ったら、study guide 側では sample ごとの README と `seed/`、`reference/` を読みます。

## 実行前提

- コマンドは repository root で実行する
- Docker Desktop / Docker daemon は起動済みとする
- Quickstart の MTOOL stack は起動したままでもよい
- `make sampleNN-pack-runtime-test` は sample ごとの isolated Docker stack を作り、終わったら片付ける
- sample test は同じ host port を使うため、複数 sample を並列実行しない

各 study page に載せている `make sampleNN-pack-runtime-test` は、そのまま実行できます。  
初回は Docker image build / pull が入るため、数分かかることがあります。

## まとめて green を確認する

Study 全体の test gate を先に確認したい場合は、次を repository root から実行します。

```bash
make sample02-pack-runtime-test sample03-pack-runtime-test sample04-pack-runtime-test sample05-pack-runtime-test sample06-pack-runtime-test sample07-pack-runtime-test sample08-pack-runtime-test sample09-pack-runtime-test sample10-pack-runtime-test sample11-pack-runtime-test sample12-pack-runtime-test sample13-pack-runtime-test sample14-pack-runtime-test sample15-pack-runtime-test sample16-pack-runtime-test sample17-pack-runtime-test sample18-pack-runtime-test sample19-pack-runtime-test sample20-pack-runtime-test sample21-pack-runtime-test sample22-pack-runtime-test sample23-pack-runtime-test sample24-pack-runtime-test sample25-pack-runtime-test sample26-pack-runtime-test
```

`sample18` の画面まで含めて確認する場合:

```bash
make sample18-http-runtime-smoke
```

後続 lane の代表的な focused gate:

```bash
make sample28-no-code-runtime-ui-smoke
php mtool/scripts/validate_sample33_promotion.php
php mtool/scripts/validate_sample34_firebird_promotion.php
node sample/tutorials/sample35-capacitor-artifact-import/scripts/validate-sample.mjs
node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-sample.mjs
node sample/tutorials/sample47-open-world-rpg-demo/scripts/validate-sample.mjs
node sample/tutorials/sample47-open-world-rpg-demo/scripts/validate-mtool-artifact-linkage.mjs
```

`sample01` は Quickstart で実行済みの前提です。もう一度含めても問題ありません。

## 正本

- tutorial sample の設計・一覧: [../sample-tutorial-roadmap.md](../sample-tutorial-roadmap.md)
- tutorial pack の実体: [../../sample/tutorials/README.md](../../sample/tutorials/README.md)
- clone 直後の実行手順: [../quickstart.md](../quickstart.md)
