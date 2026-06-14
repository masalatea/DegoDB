# 2026-05-14 Progress Snapshot

## 前提

- この進捗率は file 数ではなく、機能マイルストーン重みで見た概算である。
- `Project 1 = MTOOL` の parity 完了度と、広い意味での「旧 `dev web/db` 全体の再構築完了度」は分けて扱う。
- sample / directory 整理は別 workstream として切り分ける。
- 完了の基準は、`新画面で全設定が可能`、`current canonical data が正本`、`そこから出る Output が新旧一致`、`旧画面が日常運用で不要` の 4 点を満たすこととする。
- 計画は `Phase 1. 機能移植完了` と `Phase 2. self-host / runtime 置換完了` に分ける。詳細は `2026-0514-functional-migration-vs-self-host-plan.md` を参照する。

## 完了判定テーブル

| 観点 | 完了条件 | 現在の判定 |
| --- | --- | --- |
| 設定画面 parity | 旧設定画面でできた設定を current route で全て設定できる。 | partial |
| current source of truth | current canonical schema / file だけで運用できる。 | partial |
| Output parity | current metadata から生成した Output が旧実装と一致する。 | partial |
| 旧画面不要化 | 日常の設定変更・build・比較・検証を current route だけで回せる。 | partial |

補足:

- `Project 1 = MTOOL` の `36/36 success` は Output parity の強い材料だが、まだ broad scope 全体の完了そのものではない。
- route が開いただけの module は `available` であっても `migration done` とは限らない。

## フェーズの読み方

| フェーズ | 意味 | 現在の位置 |
| --- | --- | --- |
| Phase 1. 機能移植完了 | 旧設定機能を current route / canonical metadata で網羅し、Output parity まで到達する。 | main track |
| Phase 2. self-host / runtime 置換完了 | Mtool 自身の generated Output を Runtime 本体へ差し替え、再編集なしで動かす。 | 初期段階 |

補足:

- 現在の broad scope `%` は主に Phase 1 の進捗として読む。
- `RUNTIME-DBCLASSES` の partial self-generation は Phase 2 の準備にはなるが、Phase 1 完了の必須条件ではない。

## 計画更新

- 2026-05-14 時点では、主系は Phase 1 のまま据え置く。
- broad scope `%` は引き続き「旧設定機能を current route / canonical data へ移し切る進捗」として読む。
- self-host / runtime 置換の作業は止めないが、主目的は parity 維持、bridge debt 圧縮、置換準備に限る。
- Phase 2 を主系へ上げるのは、残件の中心が missing settings ではなく runtime / loader / generated contract 調整へ移ってからにする。

## 概算進捗

- broad scope 全体
  - 約 `77%`
  - 根拠: これは主に Phase 1 の進捗である。admin/lab の主要 route 群、metadata schema、build/compare/source-output 基盤、Project 1 parity、directory/policy 整理に加え、`Project Security / Host Assignment` は users first slice だけでなく page security / host assignment の current landing zone まで入った。さらに `HTML` は source binding と live `html` / `htmlParameter` row に加えて、global `html_templates` / `html_template_parameters` も canonical table へ寄せた。`LanguageResource` も copied legacy reference の read-first route から一段進み、current canonical table と group/resource CRUD に加えて move と duplicate flow まで current admin に入った。ただし完了定義を「全設定可能 + current canonical を正本 + Output 新旧一致 + 旧画面不要化」と置くと、`LanguageResource` の additional group / auto-translate、page security の route policy 連携、host assignment の infra split、sample 固有 output 実装がまだ残る。
- `Project 1 = MTOOL` parity / bridge scope
  - 約 `91%`
  - 根拠: `36/36 success` の build/publish parity は到達済みで、wrapper/current route handoff も広く入った。さらに `HTML` は live row だけでなく template metadata も canonical table 化し、global settings の current CRUD と smoke test まで通った。`LanguageResource` も group/resource の current CRUD に加えて move と duplicate flow が入り、旧画面依存はもう一段減ったが、generator/runtime 側にはまだ bootstrap / fallback 依存が残る。
- `Project 1 = MTOOL` canonical replacement scope
  - 約 `63%`
  - 根拠: parity は出ているが、`36` output のうち canonical / self-generated と言えるものはまだ限定的で、`LanguageResource` には additional group / auto-translate など未移植機能が残る。ただし `HTML` は live row に加えて global template metadata も `html_templates` / `html_template_parameters` へ寄せ、`LanguageResource` も current canonical table と CRUD + move/duplicate flow を持ったため、旧画面依存はさらに減った。
- sample / directory reorganization scope
  - 約 `85%`
  - 根拠: layout、命名、`work/` / `tmp/` / `mtool/` / `sample/` の責務、sample-only initdb policy、`sample1..7` の runtime 実検証までは完了した。未了は各 sample の project-specific metadata 増強と、必要な sample だけに buildable output を個別実装する段階である。

## できていること

- `Project 1 = MTOOL` の output parity は build/publish とも `36/36 success` に到達している。
- `Project`、`DB Table`、`Data Class`、`DB Access class/function`、`Proxy`、`Source Output`、`Compare Output`、`Endpoint Test` の current route は first slice が実用段階に入っている。
- `Project Security / Host Assignment` は first slice current route が入り、`/security`、`/security/users`、`/security/pages`、`/host-assignments` を current admin site で開ける。
- `LanguageResource` は copied legacy catalog を `mtool/reference/mtool-legacy-language-resource-catalog.json` として保持しつつ、`project_language_resource_*` current canonical table へ bootstrap できる。
- `LanguageResource` の `list / groups / detail` current route は `MTOOL` の `1007` resources / `7` groups / `20250` captions / `51` languages を current canonical source として表示でき、group/resource CRUD に加えて base group move と duplicate prefill/create も current admin から実行できる。
- `security/users` は `project_memberships` を canonical source of truth にして `owner / admin / member` を更新できる。
- `security/pages` は `project_page_security_policies` + `project_page_security_policy_capabilities` を current landing zone にして、`SERVER_NAME + SCRIPT_NAME + SecurityType` を normalized capability list として編集できる。
- `host-assignments` は `project_host_assignments` を current landing zone にして、旧画面の visible 4 列を denormalized row として編集できる。
- `HTML` は `project_html_source_bindings` を current landing zone にして、legacy `ProjectSourceOutputPID` ごとの current `source_output_key` / `module_source_ref` / `refresh_policy` を `admin:/projects/{project_key}/html` で編集できる。
- `HTML` の live row も `project_html_definitions` / `project_html_parameters` を current canonical table にし、MTOOL は copied legacy reference から 66 html / 145 parameter を bootstrap できる。
- `HTML` template metadata も `html_templates` / `html_template_parameters` を current canonical table にし、legacy table が無い `config_app` でも copied MTOOL reference から 349 template / 7 template parameter を bootstrap できる。
- `HTML` template settings は `/settings/html-templates` 系 route で current CRUD を持ち、project HTML parameter audit も canonical template metadata を優先して組み立てる。
- `RUNTIME-DBCLASSES`、`DBIMPORT-PROXY-SERVER`、`DBIMPORT-PROXY-CLIENT` は artifact 生成の current path が通っている。
- `dbtable` / `dataclass` / `project_source_outputs` / compare/proxy 関連の canonical metadata schema と repository/service は入っている。
- `build` / `compare-output` / `endpoint test` は `lab` 側の run/job history まで current 化された。
- root layout は `docker/` base-only、`mtool/` current runtime、`sample/` durable sample pack、`tests/scenarios/` verification scenario へ整理された。
- `sample/*/compose.yaml` は `APP_WORK_ROOT=sample/<pack>/output` に統一された。
- sample pack の `db-config` initdb は「共通 schema + その pack の seed のみ」に修正済みで、`mtool/docker/mariadb/config-seed/` を混ぜない方針へ直した。
- `sample1..7` は fresh initdb で再検証し、各 pack が sample 自身の `projects` / `project_source_outputs` だけで立ち上がることを確認済みである。
- 上記 runtime 検証では、全 pack で `output/` 配下に自動生成物は生じず、`metadata-only` seed の期待どおり空のままであることも確認済みである。

## 進行中

- `HTML` は current route / bridge に加え、`project_html_source_bindings`、`project_html_definitions`、`project_html_parameters`、`html_templates`、`html_template_parameters` を current DB に保持できるようになった。残りは generator/runtime 側の template bootstrap dependency と self-host 置換の前提整理である。
- `LanguageResource` は current canonical table / bootstrap / CRUD + move/duplicate flow まで入った。残りは additional group / auto-translate と optional module / code-native source of truth への分離である。
- `Source Output` は list/create/update/reorder と artifact build が通り、legacy-only field の `notes` structured block 退避、`HTML-DB` 再 publish、`36/36 success` 再確認まで完了した。
- `Project Security / Host Assignment` は landing zone まで current 化されたが、page security の route policy 連携と、host assignment の infra split は未了である。
- `RUNTIME-DBCLASSES` は partial self-generation までで、bootstrap / legacy delegate をまだ完全には外していない。
- sample pack は layout/policy は固まったが、project-specific metadata export の追加と、必要な sample のみ buildable output を持たせる設計が残っている。

## まだできていないこと

- `LanguageResource` の additional group assignment / auto-translate currentization、および optional module 化。
- page security landing zone から current route / service policy へどう再投影するかの固定。
- host assignment landing zone を system / infra settings slice へどう split するかの固定。
- ここまでが Phase 1 の主な残りである。
- `RUNTIME-DBCLASSES` の full self-generation と、app 自身を self-generated runtime へ切り替える段階。
- これは主に Phase 2 の課題である。
- DB import connector の一般化。現状は `MTOOL` first slice 前提で、外部 DB への汎化は未了。
- `LanguageResource` bridge の本格置換と、HTML generator/runtime 側の bootstrap dependency 圧縮。
- sample ごとの「sample 自身の seed だけを対象にした実 buildable output」の実装。
- PSR-4 指向 namespace/layout への最終寄せ。

## 当面の優先順

- `Project 1` では `HTML generator/runtime` / `source-output` 周辺の残り bootstrap dependency を少しずつ削る。
- `LanguageResource` は current CRUD + move/duplicate flow の次段として additional group / auto-translate を入れ、その後に optional module / code-native source of truth の境界を詰める。
- `Project Security / Host Assignment` の次段として、landing zone から route policy / infra split への接続を固める。
- sample は `metadata-only` の representative seed を増やすか、または sample 固有 metadata が揃ったものだけ buildable output を個別追加する。
- Phase 2 の self-host / runtime 置換は、Phase 1 の一区切り後に主系へ上げる。

## 次の実行リスト

1. `HTML generator/runtime` 側の template bootstrap dependency を詰める
   - 完了条件: `project_html_definitions` / `project_html_parameters` / `html_templates` / `html_template_parameters` を前提に、generator が current canonical metadata を正本として読む境界を固定する。
2. `LanguageResource` の残 current route を埋める
   - 完了条件: additional group assignment / auto-translate の current route を入れ、legacy `lang_res*.php` の未置換操作を縮める。
3. `LanguageResource` を core scope から切り離した実装境界を固定する
   - 完了条件: optional module 方針、seed/default state、sample/test での扱い、将来の AI/Git friendly source of truth を文書として確定する。
4. `Project Security / Host Assignment` の landing zone を route policy / infra split へ接続する
   - 完了条件: page security の current policy 投影先を決め、host assignment の infra catalog への分離境界を固定する。
5. sample 用 metadata を必要最小限だけ厚くする
   - 完了条件: representative `metadata-only` seed を追加するか、または sample 固有 metadata が揃った pack にだけ buildable output definition を個別実装する。

## 実行順の意図

- sample policy の runtime 実検証は `sample1..7` まで完了したので、次は `Source Output` の bridge debt を減らし、`Project 1` parity の信頼性を維持する。
- `Project Security / Host Assignment` は landing zone currentization まで終わったので、次は route policy / infra split の接続を詰める。
- `HTML` は binding landing zone、live row、global template metadata まで canonical 化できたので、次は generator 側の bootstrap dependency を削る。`LanguageResource` は current CRUD + move/duplicate flow が入ったため、その次は remaining routes と optional module 境界の整理を主対象とする。
- sample 側は、ここから先は「全部 buildable にする」のではなく、必要な pack だけを選んで metadata を厚くする。
- self-host / runtime 置換は重要だが、現時点では Phase 1 の機能網羅を崩さないよう、後段フェーズとして分離して扱う。

## いまの読み方

- 「動くところまで来ているか」で見ると、current admin/lab と Project 1 parity はかなり進んでいる。
- 「旧実装を bridge なしで新実装へ置き換え切ったか」で見ると、まだ中盤である。
- したがって broad scope の感覚値は `約77%`、Project 1 parity の感覚値は `約91%` と読むのが妥当である。
