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

## study の基本姿勢

各 sample は、まず test で green を確認し、その後に seed、reference、generated output を読みます。  
最初から全 SQL や全 PHP を理解しようとせず、次の 4 点だけを追います。

- 何の table を seed しているか
- どの canonical metadata が seed され、どれが import / sync で作られるか
- どの source output が生成されるか
- reference と actual output が何を保証しているか

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
make sample02-pack-runtime-test sample03-pack-runtime-test sample04-pack-runtime-test sample05-pack-runtime-test sample06-pack-runtime-test sample07-pack-runtime-test sample08-pack-runtime-test sample09-pack-runtime-test sample10-pack-runtime-test
```

`sample01` は Quickstart で実行済みの前提です。もう一度含めても問題ありません。

## 正本

- tutorial sample の設計・一覧: [../sample-tutorial-roadmap.md](../sample-tutorial-roadmap.md)
- tutorial pack の実体: [../../sample/tutorials/README.md](../../sample/tutorials/README.md)
- clone 直後の実行手順: [../quickstart.md](../quickstart.md)
