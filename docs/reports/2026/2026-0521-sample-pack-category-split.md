# 2026-05-21 Sample Pack Category Split

## 概要

- active sample pack を flat な `sample/` 直下から、役割別の 2 系統へ再配置した。
- `sample/patterns/`
  - `sample1-simple-table` と `sample9-22`
- `sample/legacy-projects/`
  - `sample2-8`
- sample 番号は historical ID として維持し、現在の役割は directory category で読む方針に切り替えた。

## 意図

- flat な `sample1-22` は、最小 pattern sample と Original Code 由来の representative project sample が混在しており、番号だけでは役割が読めなかった。
- 先に category を分けることで、番号の全面再設計をしなくても閲覧・運用の混乱を減らせる。
- `Sample1` は runtime/import/output の最小 functional sample、`Sample9-22` は complex/new form の migration gate、`Sample2-8` は legacy project representative という読み分けを path で表現する。

## 実施内容

- active pack directory を `sample/patterns/` / `sample/legacy-projects/` へ移動した。
- unreferenced historical leftover `sample1-sql-server` は active catalog から外し、`sample/old/` へ退避した。
- sample pack 共通 runner の置き場は、曖昧な `sample/_shared/` ではなく `sample/_pack-support/` へ rename し、script 名も `sample-pack-runner.sh` に揃えた。
- `Makefile`、sample runner、compose override、sample README、integration README を new path に更新した。
- sample reference root を見る check script / helper を new path に更新した。
- `LanguageResource` file catalog の sample root も `sample/legacy-projects/<pack>/resources/` 前提に更新した。
- `apply_config_sample_seed.sh` は new nested layout を既定探索しつつ、flat layout しか無い環境では fallback できるようにした。

## 文書整理の扱い

- stable doc / current README は new category path を正本に更新する。
- 過去の dated report は履歴として残し、旧 flat path 記述もそのまま保持する。

## 次段の考え方

- 今回は category split までとし、sample 番号の全面 rename や test class rename は後段に回す。
- 番号の再設計が必要になった場合でも、まず `patterns` / `legacy-projects` の 2 系統で整理された状態を前提に検討する。
