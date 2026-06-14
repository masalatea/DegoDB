# 2026-05-21 Bootstrap DBClasses Staged Copy Use Inventory

## 結論

- `work/legacy-recovery/dbclasses` は current live runtime / generator / test の入力ではない。current repo 上の参照は `bootstrap_dbclasses.sh`、`make bootstrap-dbclasses`、および docs 説明にほぼ限られる。
- したがって `bootstrap_dbclasses.sh` を残す理由は、以前まとめて書いていた `diff / inspection / emergency preparation` 全部ではない。
- current inventory では、`runtime reference repair / rollback`、`latest artifact の採用`、`sample / migration test 入力` はすでに staged copy 非依存へ移っている。
- この helper をまだ残すなら、現在の keep reason は `host-side で quarantined された writable full-tree legacy copy を使う emergency preparation` だけである。read-only diff / inspection は staged copy の独立理由から外してよい。
- 逆に言うと、current repo-supported workflow で `bootstrap_dbclasses.sh` を必須にする具体例は、現時点では docs 上に残っていない。

## 1. すでに staged copy 非依存へ移ったもの

### runtime reference repair / rollback

- authoritative runtime reference の修復は、`make restore-runtime-reference-snapshot ARTIFACT_KEY=...` または `php mtool/scripts/restore_runtime_reference_snapshot.php --artifact-key=...` が主系である。
- `bootstrap_dbclasses.sh --apply-to-runtime-reference` と `make bootstrap-dbclasses-runtime-reference` は retired しており、legacy copy の direct overwrite は current guidance から外れている。

### latest artifact の採用

- verified artifact を default authoritative reference へ進める作業は、`make promote-runtime-reference` または `php mtool/scripts/promote_runtime_reference.php --artifact-key=...` で扱う。
- これは `work/legacy-recovery/dbclasses` を経由しない。

### sample / migration test 入力

- sample / migration test は `tests/fixtures/legacy-dbclasses/` の curated copy を使う。
- `original-codes/` も `work/legacy-recovery/dbclasses` も test input mainline ではない。

### current live code / test dependency

- `rg -n "legacy-recovery/dbclasses|bootstrap_dbclasses|bootstrap-dbclasses" mtool tests` の結果、live code / test 側で staged copy root を読む導線は見当たらない。
- current で残っているのは `bootstrap_dbclasses.sh` 自身、Make target、artifact strategy 名、docs 説明である。

## 2. staged copy の独立理由から外してよいもの

### read-only diff

- `AGENTS.md` でも `original-codes/` は host-side reference として残してよい前提になっている。
- したがって read-only diff は `original-codes/` と `mtool/reference/dbclasses/`、または promoted snapshot を直接比較すればよく、staged copy を理由に残す必要は薄い。

### read-only inspection

- full-tree の閲覧や spot check も `original-codes/` の host-side reference、`mtool/reference/dbclasses/`、`mtool/reference/runtime-reference-snapshots/...` で足りる。
- `work/legacy-recovery/dbclasses` は inspection のための必須入力ではなく、mutable sandbox を欲しい時の convenience に近い。

### `generated-bootstrap-dbclasses` という名前

- `generated-bootstrap-dbclasses` は source output / artifact strategy 名であり、`bootstrap_dbclasses.sh` 依存を意味しない。
- runtime bundle 生成や source output 定義に `bootstrap` という語が残っていても、last-resort staged copy helper を repo に残す理由にはならない。

## 3. まだ残る lane

### host-side quarantined full-tree emergency preparation

- `bootstrap_dbclasses.sh` が今も提供している独自価値は、`original-codes/mtool_lib/dbclasses` をそのまま触らず、`work/legacy-recovery/dbclasses` に mutable な full-tree copy を作れることだけである。
- これは runtime reference repair ではなく、host-side で一時的に legacy full tree を隔離して emergency preparation をしたい時の補助導線である。
- current docs では、この lane に対する同等の host-side 代替手順はまだ固定していない。

## 4. tightened keep / archive rule

- keep reason
  - `bootstrap_dbclasses.sh` を残すなら、理由は `host-side quarantined full-tree emergency preparation` のみと読む。
- reason から外すもの
  - read-only diff
  - read-only inspection
  - runtime reference repair / rollback
  - sample / migration test input
- archive condition
  - 上記 emergency preparation lane 自体を不要化するか、同等の host-side 代替手順を docs 化できた時。

## 判断

- ユーザー理解どおり、`staged legacy copy を使う diff / inspection / emergency preparation` のうち、前 2 つはどんどん消す方向でよい。
- 以後 `bootstrap_dbclasses.sh` を残す理由を書く時は、`diff / inspection` を並べず、`host-side quarantined full-tree emergency preparation` に絞って表現する。

## 検証

- `rg -n "work/legacy-recovery/dbclasses|legacy-recovery/dbclasses|bootstrap_dbclasses\\.sh|bootstrap-dbclasses|last-resort staging|staged legacy copy|ACKNOWLEDGE_LAST_RESORT|--last-resort" docs mtool tests Makefile`
- `rg -n "legacy-recovery/dbclasses|bootstrap_dbclasses|bootstrap-dbclasses" mtool tests`
- `rg -n "tests/fixtures/legacy-dbclasses|restore_runtime_reference_snapshot|promote_runtime_reference" docs mtool tests`

## 参照

- `AGENTS.md`
- `docs/reports/2026/2026-0519-original-codes-host-only-enforcement.md`
- `docs/reports/2026/2026-0520-bootstrap-dbclasses-archive-readiness.md`
- `docs/reports/2026/2026-0520-bootstrap-dbclasses-runtime-reference-retirement.md`
- `docs/reports/2026/2026-0520-runtime-reference-durable-snapshot-recovery.md`
- `docs/reports/2026/2026-0521-host-only-helper-lane-classification.md`
