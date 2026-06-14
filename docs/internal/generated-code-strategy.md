# Generated Code Strategy / 生成コード方針

English companion:
This document explains why generated runtime code remains part of the architecture, what the current wrapper/base contract looks like, and where the boundary sits between the completed functional-migration phase and the later self-host cutover phase. Read it when you need the current truth for `RUNTIME-DBCLASSES`.

## 目的

- 新バージョンでも、「ツール自身が使う runtime code の一部をツール自身で生成する」前提を明文化する。
- 旧 `original-codes/mtool_lib/dbclasses/` を、単なる参照コードではなく legacy recovery 用 generated artifact として扱う。
- DB 設計データ Export がまだ無い期間の暫定運用と、後で自己生成へ戻す方針を分けて整理する。

## 対象

まず対象にするのは、旧 `original-codes/mtool_lib/dbclasses/` である。

- 総ファイル数 204
- `data-*.php` 101
- `dbaccess-*.php` 101
- `autoload_mtool.php` 1
- `index.html` 1

この構成自体が、旧 mtool における runtime dependency の一部だった。

## 基本認識

- 旧 `dbclasses` は hand-written library ではない。
- DB 設計 metadata から生成された出力物であり、その出力物を mtool 自身が runtime で利用していた。
- したがって新バージョンでも、最終的には「新システムが新システム用の generated runtime code を自分で作る」構造に戻す。

## フェーズ分割

generated runtime の扱いは、次の 2 フェーズに分けて考える。

### Phase 1. 機能移植完了

- 目的は、旧設定機能を current route / canonical metadata で置き換え、Output parity まで到達すること。
- この段階では、runtime 本体がまだ legacy recovery copy、partial self-generation、legacy delegate を含んでいてもよい。
- 重要なのは、機能の source of truth が current 側へ移り、旧画面に戻らずに設定と出力が回ることである。

### Phase 2. self-host / runtime 置換完了

- 目的は、Mtool 自身が生成した Output を Runtime 本体へそのまま差し替え、再編集なしで動かすこと。
- この段階では、generated runtime が authoritative source になり、app 自身がその出力物を読んで継続運用できる必要がある。
- Phase 2 は Phase 1 の後段であり、機能網羅の途中で self-host 都合に current 実装を引きずられすぎない。

## API 公開モデルの原則

- generated runtime の再構築と、API をどう公開するかは分けて考える。
- API 公開モデルとしては、新実装も `single-function proxy` と `custom proxy` を別物として持つ。

### `single-function proxy`

- 基本の公開モデル
- `1 dafunc = 1 endpoint`
- naming、request、response、auth を function 直結で保つ
- generator の都合で `custom` に畳み込まない

### `custom proxy`

- 上位の構成モデル
- 複数 function を束ねる use-case endpoint
- transaction、failure policy、step 構成を扱う
- `single` で素直に表現できない場合だけ使う

つまり、新実装で内部の runtime helper や endpoint base class を共有しても、
metadata、source output binding、UI、artifact strategy の意味まで統合しない。
`single` を first-class model として残し、`custom` は advanced path として設計する。

## 当面の方針

### 1. legacy recovery copy を許容する

- DB 設計データ Export が未投入の間は、旧 `dbclasses` のコピー利用を許容する。
- ここでのコピーは恒久解ではなく、generator 再建までの recovery / bootstrap 導線とみなす。
- この段階では、生成ロジックよりも runtime 依存関係の切り出しと loader 設計を優先する。
- current runtime reference path は `mtool/reference/dbclasses/` とする。
- `bootstrap_dbclasses.sh` は archived historical helper として current mainline から外した。current supported runtime/tool workflow では実行せず、`make bootstrap-dbclasses` も archive 済み helper を案内して fail fast するだけにした。
- authoritative runtime reference `mtool/reference/dbclasses/` の repair / rollback は、`make restore-runtime-reference-snapshot ARTIFACT_KEY=...` または `php mtool/scripts/restore_runtime_reference_snapshot.php --artifact-key=...` の snapshot-backed recovery に限定する。旧 `make bootstrap-dbclasses-runtime-reference` と archive 済み `bootstrap_dbclasses.sh --apply-to-runtime-reference` は retired guidance として fail fast する。
- current durable promote は `make promote-runtime-reference` または `php mtool/scripts/promote_runtime_reference.php --artifact-key=...` で行い、verified self-generated artifact を `mtool/reference/dbclasses/` へ昇格する。
- promote 時には同じ tree を `mtool/reference/runtime-reference-snapshots/MTOOL/RUNTIME-DBCLASSES/{artifact_key}/` にも保存し、`make restore-runtime-reference-snapshot ARTIFACT_KEY=...` または `php mtool/scripts/restore_runtime_reference_snapshot.php --artifact-key=...` で `work/` 消失後も restore できる。
- `php mtool/scripts/show_runtime_reference_status.php` または `make mtool-runtime-reference-status REQUIRE_CURRENT=1` を使うと、latest artifact と promoted runtime reference の `artifact_key` 一致に加えて `durable_recovery_ready` も機械的に確認できる。
- `make test` / `make mtool-self-loop-check` のように new artifact を作る verification run は、default authoritative reference を進める promote candidate run と分けて扱う。`status=stale-reference` は `latest artifact` が未採用であることを示す運用状態であり、green verification 自体の failure とは別に読む。default baseline を更新したい verified artifact にだけ promote を行う。
- このため current mainline には host-side staged legacy copy helper を残さない。もし host-side quarantined full-tree legacy copy が再び必要になった場合だけ、archive から archive 済み helper を明示的に復帰させて使う。
- `export_legacy_*_reference.php` 群も host-side export helper として扱う。`dataclass` / `dbtable` / `db_access` / `html` / `language_resource` は `--host-side --sql-dump=original-codes/mtool.sql` のように host filesystem 上の dump path を明示して実行し、`table_schema` だけは `--host-side` と temporary imported legacy schema に対する `--dsn` / `--schema-name` を使う。
- current live host-only helper inventory は、`export_legacy_*_reference.php` / `export_legacy_table_schema_reference.php` / `export_mtool_db_access_seed.php` の `explicit export` と、`source_dump_path` / `bootstrap-reference` の `provenance metadata` に分けて読む。旧 `last-resort staging` lane は archived historical lane であり、current durable input refresh の導線には含めない。
- 既存の `projects` / `lab_experiments` CRUD は schema が旧 generated class と一致しないため、repository driver は `pdo` のまま維持する。
- ただし page 層は adapter の上に載せ、後で generated 実装へ差し替えられるようにする。

### 2. basename compatibility を優先する

- directory path は後で調整してよい。
- ただし basename は、旧実装とおおむね揃える方針を取る。
- 最低限維持する対象は以下。
  - `data-<Entity>.php`
  - `dbaccess-<Entity>.php`
  - `autoload_mtool.php`

これは loader 差し替え時の影響範囲を小さくするためである。

### 2.1 namespace / PSR-4 は後段で整える

- 最終的には PHP generated/runtime code も PSR-4 対応の namespace と directory layout に寄せる想定で進める。
- ただし current migration / parity phase では、PSR-4 整形を先行条件にはしない。
- 当面は namespace や path の美しさよりも、parity、current route への吸収、legacy 依存の切り離しを優先する。
- そのため現段階では、file は作業を止めない pragmatic な場所に置いてよく、後で PSR-4 へ寄せやすいように loader と依存境界だけを局所化しておく。
- つまり今は basename / 振る舞い互換を優先し、PSR-4 は最終整理タスクとして別 TODO で扱う。

### 3. 完全上書き前提にはしない

- 旧実装の build pipeline は、既存ファイル中の編集領域を保持しながら生成し直す設計を持っていた。
- ただし新バージョンでは、同一 generated file の中に editable region を残す方式は新規採用しない。
- 新バージョンでは、「generated layer は再生成してよい」「custom layer は生成対象の外に置く」を基本原則にする。
- したがって「完全上書き前提にはしない」の意味は、旧方式のように generated file 内の手編集を救済することではなく、generator が custom layer を触らない構造を採ることを指す。

### 3.1 カスタマイズ境界の基本方針

- generated code は disposable artifact として扱う。
- 手書きコードは generated file の editable region へ戻さず、別クラス・別ファイル・別 template override として保持する。
- 生成側は `Base` 系クラスまたはそれに相当する安定した生成物を出し、custom 側は継承または collaborator 差し替えで拡張する。
- Compare Output の template asset / ignore asset override のように、生成テキスト全体を差し替える要求は file-level override として扱う。
- Source Output / runtime dbclasses も、将来的にはこの境界に揃える。

### 3.2 hook を置く判定基準

- hook は、「行単位差分」ではなく「責務として名前を付けられる可変点」にだけ置く。
- 具体的には、以下を満たす場合だけ hook 候補とみなす。
  - 呼び出しタイミングが明確である。
  - 入出力が狭く、generator や runtime の広い内部状態へ依存しない。
  - デフォルト実装が no-op でも標準動作が成立する。
  - 同種の拡張が複数 project / 複数 entity で再利用される見込みがある。
  - hook 単体または hook を含む境界のテストを書きやすい。
- 上記を満たさないものは hook にせず、file override、collaborator 差し替え、または個別実装として扱う。

### 3.3 hook を置きやすい層と置きにくい層

- hook を置きやすい層
  - `dbaccess-*`
  - build / output service
  - endpoint / proxy generation の組み立て処理
- hook を置きにくい層
  - `data-*` のような DTO / value object
  - 生成 SQL 全文や生成ソース全文のように、差分が構造ではなく全文置換になりやすい領域
- DTO 系は薄く保ち、必要なら helper や mapper 側で吸収する。

### 3.4 DTO の責務境界

- DTO / value object は、原則として「値を保持して受け渡す」責務に留める。
- 新実装で DTO に残してよい責務は、次のような mechanical なものに限る。
  - generated field / property の保持
  - generator / serializer が一律に必要とする単純な配列化・復元
  - 型変換や default 補完が runtime 共通規約として機械的に定義できる場合の、ごく薄い補助
- 一方で、次の責務は DTO から外へ出す。
  - 業務意味を持つ判定
  - 画面表示用 caption や label の整形
  - 他 entity や repository を引く補助
  - build / output / proxy の分岐ロジック
  - hydrating 後の補正や保存前の検証
- 旧実装の helper を新方針へ写すと、例えば次のようになる。
  - `ProjectData::IsMySQL()` は DTO ではなく、DB 種別を扱う helper / policy へ寄せる
  - `ProjectSourceOutputData::IsProxyServer()` や `IsProxyClient()` は、source output classifier や service へ寄せる
  - `GetProjectStorageTypeCaption()` のような caption 生成は formatter / presenter へ寄せる
  - `LanguageResourceCaptionDBAccess::GetCaptionBasedOnResouceKey()` のような cross-entity lookup は service または repository collaboration に寄せる
- つまり、旧 editable region に入っていた helper の受け皿は、まず DTO 継承ではなく custom layer 側の helper / mapper / service / collaborator と考える。

### 3.5 旧 hook の実態

- 旧実装の拡張方式は、実際には次の 2 系統に分かれていた。
  - generated file 内の editable region
  - `daCustomProxy` 系のような、DB metadata を generator / build が読む構造化拡張
- `original-codes/mtool_lib/dbclasses/` を確認すると、非空の editable region は主に `ADDITIONAL CLASS DEFINITION` と `BOTTOM` に偏っていた。
  - `ADDITIONAL` が 25
  - `BOTTOM` が 19
  - 大半は `data-*` 側で、helper 関数、caption 関数、enum 補助、真偽値判定関数の追加に使われていた
- 一方で、`FUNCTION` editable region に常用の実装を差し込んでいる形跡は確認できなかった。
- つまり旧 `dbclasses` の editable region は、「CRUD 本文へ独自ロジックを差し込む hook」というより、「generated data class に helper を足す逃がし先」として使われていたと整理できる。
- 旧 `dev web/db/*_include.php` 側には、`Input Parameter`、`Insert Data`、`Update Data`、`Get Data` などの名前付き editable region が大量に存在した。
  - 確認できた範囲では 637 箇所
  - これは class hook ではなく、生成済み手続きの途中に人が処理片を差し込むための枠である
- このため、旧実装の「hook」は単一の設計原則ではなく、generated file 内 helper 追加と、手続きテンプレート内 patch point が混在した状態だった。

### 3.6 旧 `Custom Proxy` との比較

- 旧 `Custom Proxy` は、editable region と比べると、より構造化された拡張方式だった。
- `daCustomProxy`、`daCustomProxyFunc`、`daCustomProxySourceOutputTarget` などの metadata を UI から編集し、build 側がそれを読んで endpoint や proxy source を構成していた。
- ここでは「どの関数を呼ぶか」「どの出力対象へ出すか」「indent をどう扱うか」などが metadata として管理され、generated file への直接手書きは前提になっていなかった。
- ただし、これは `single-function proxy` を不要にするという意味ではない。
- 旧実装では `dafuncSimpleProxySourceOutputTarget` と `dafunc.SingleProxy_*` が別に存在し、単純公開と構成公開は分離されていた。
- したがって、新実装が引き継ぐべきなのは editable region そのものではなく、`Custom Proxy` 側に見られる「意味のある可変点を metadata として管理し、generator が明示的に組み立てる」考え方である。
- 例えば、次のような metadata は引き継ぐ価値がある。
  - 認証方式
    - 旧: `daCustomProxy.AuthType`、`SingleGetFuncPID`
    - 新: `auth_strategy=project_token|login_cookie|no_security|get_func`
    - 出力先: `AuthPolicyInterface` collaborator、または `BaseProxyHandler::authPolicy()` の override
  - 呼び出し順序と呼び出し種別
    - 旧: `daCustomProxyFunc.dafuncPID`、`IsList`、`FunctionListOrder`
    - 新: `steps[] = [{dafunc_key, returns, order}]`
    - 出力先: `buildSteps()` が返す `ProxyStep` 配列、または `ProxyStepProvider` collaborator
  - 出力対象
    - 旧: `daCustomProxySourceOutputTarget.ProjectSourceOutputPID`
    - 新: `targets[] = ["php-server", "cs-client"]`
    - 出力先: target ごとの renderer / emitter collaborator
  - 実行ポリシー
    - 旧: `InTransaction`、`ContinueEvenIfFailedToInsert`
    - 新: `transaction_mode`、`failure_policy`
    - 出力先: `TransactionPolicy`、`FailurePolicy` collaborator
- 逆に、次のような metadata は新実装へは引き継がない。
  - `STEP n` という番号付きテンプレート差し込み前提
  - `AddIndentCount`
  - `AddIndentType`
- これらは「何をしたいか」ではなく「生成テキストをどう整形するか」を表す情報であり、責務名で説明できる可変点ではない。
- 新実装では、step 番号やインデント差し込みではなく、構造化された `steps[]` や policy metadata から `Base/Custom` または collaborator 境界を生成する。

### 3.7 新方針との差分

- 新実装では、旧 `dbclasses` のように `data-*` へ helper を直接追加する設計は基本にしない。
- これは、旧 editable region をそのまま DTO 継承へ置き換えるという意味ではない。
- DTO / value object は薄く保ち、helper は custom layer、mapper、service、collaborator へ寄せる。
- 継承や hook の主対象は `dbaccess-*`、build / output service、proxy / endpoint 組み立て層であり、DTO 自体は極力プレーンな generated object として保つ。
- 旧 `*_include.php` の名前付き editable region のような、手続き途中の patch point は新規採用しない。
- 一方で、旧 `Custom Proxy` のように、拡張対象を metadata として明示し、generator / build がそれを読んで組み立てる設計は新方針と整合する。
- したがって新実装では、以下のように整理する。
  - inline editable region は採用しない
  - 意味のある可変点だけ hook または collaborator 境界として定義する
  - 生成テキスト全体の差し替えは file-level override で扱う
  - プロジェクト固有の構成差分は metadata と custom layer に逃がす

### 3.8 旧実例から見た次の hook 候補

- 旧 `dbclasses` の非空 editable region を確認すると、主な中身は 20 件で、ほとんどが caption 関数、enum 補助、真偽値 helper、cross-entity lookup だった。
- つまり、次に hook 化を検討すべき対象は「旧 editable region に何か入っていた場所」そのものではなく、旧 build / proxy metadata が表していた構造化された責務である。
- 現時点で、次段の semantic hook / collaborator 候補は次の通り。
  - `AuthPolicy`
    - 根拠: `daCustomProxy.AuthType`、`SingleGetFuncPID`、`dafunc.SingleProxy_AuthType`
    - 旧 build では `InitializeTopLevelSecurityCheckFormatAndExampleList()` と `MakeproxyserverSourceWriteToFile()` が認証分岐を持っていた
    - 新実装では endpoint / proxy generator が `auth_strategy` metadata から policy を引く形にする
    - first-pass では generated handler base に `singleGetFunctionName()`、`authorizeByGetFunction()`、`authorizeByLoginCookieToken()` hook を出す
  - `ProxyStepProvider`
    - 根拠: `daCustomProxyFunc.dafuncPID`、`IsList`、`FunctionListOrder`
    - 旧 build では custom proxy の step 列を順番に舐めて source を組み立てていた
    - 新実装では `steps[]` から `ProxyStep` を返す collaborator に寄せる
  - `TransactionPolicy`
    - 根拠: `daCustomProxy.InTransaction`
    - 旧 build では custom proxy 全体の transaction 有無を別分岐で持っていた
    - 新実装では endpoint / use-case 単位の transaction policy として持つ
  - `FailurePolicy`
    - 根拠: `daCustomProxy.ContinueEvenIfFailedToInsert`
    - 旧 build では insert action のときだけ `check_function_result` を切り替えていた
    - 新実装では insert failure 時の継続可否を policy として持つ
  - `SourceOutputTargetSelector`
    - 根拠: `daCustomProxySourceOutputTarget.ProjectSourceOutputPID`、`dafuncSimpleProxySourceOutputTarget`
    - 旧 build では output 対象かどうかを毎回チェックしていた
    - 新実装では target list / emitter selector として切り出す
  - `ProxyIncludePolicy`
    - 根拠: `CheckIfIncludeInsertFunctionInProxy()`、`CheckIfLoginByLoginCookieTokenFunctionInProxy()`
    - 旧 build では include file と補助 source の有無を action / auth から決めていた
    - 新実装では auth / action / step 構成から必要 dependency を返す policy に寄せる
- 一方で、次のものは hook 候補ではなく helper / classifier / formatter として扱う。
  - `ProjectSourceOutputData::IsProxyServer()`、`IsProxyClient()`、`IsDBaaSProxy()`
    - `SourceOutputClassifier`
  - `ProjectSourceOutputData::GetCSNameSpaceByConsideringDefault()`、`GetJavaPackageNameByConsideringDefault()`、`GetTargtServerPSOProxyBaseURLWithLastSlush()`
    - default resolver / formatter
  - `dafuncData::GetBaseDataClassName()`、`IsInsertFunction()`、`IsLoginByLoginCookieToken()`
    - `DAFuncClassifier` または `DAFuncPolicy`
  - `GetProjectDBTypeCaption()`、`GetSingleProxyAuthTypeCaption()` などの caption 関数
    - formatter / presenter
  - `LanguageResourceCaptionDBAccess::GetCaptionBasedOnResouceKey()`
    - cross-entity lookup service
- 逆に、次の metadata は今後も hook にしない。
  - `AddIndentCount`
  - `AddIndentType`
  - `STEP n` 番号付き差し込み前提
- 理由は、これらが domain 上の責務ではなく、生成テキストをどう並べるかという layout 情報だからである。

### 3.9 直近の実装優先順

- まず実装価値が高かったのは、`single-function proxy` を first-class model として戻すことだった。
  - function detail 配下の target / auth UI
  - `project_db_access_function_source_output_targets` を読む generator
  - `single-function-proxy` 用 source output strategy
- この first slice は 2026-05-11 に実装済みで、`SAMPLE-SINGLE-PROXY-SERVER` / `SAMPLE-SINGLE-PROXY-CLIENT` を使った end-to-end build まで確認できている。
- 2026-05-12 には default core seed 側にも `PAYPAL-PROXY-SERVER` / `UPLOADER-PROXY-SERVER` を追加し、Project 1 legacy simple proxy row の non-`ApacheHostSetting` remap を actual build まで通す。
- その次に詰めるのは、`Custom Proxy` 相当の generator / build 側で使う次の 3 つである。
  - `AuthPolicy`
  - `ProxyStepProvider`
  - `TransactionPolicy` / `FailurePolicy`
- 次に、target ごとの出力分岐を安定化するために `SourceOutputTargetSelector` を置く。
- DTO helper の置き換えは、その後に `SourceOutputClassifier`、`DAFuncClassifier`、formatter 群として薄く整理すればよい。
- つまり、次段で優先すべきは DTO 置換ではなく、次の 2 点である。
  - `single` を `single` として公開できる経路を戻すこと
  - その上で proxy / build 組み立ての可変点を metadata から読める境界へ変えること

## 現在の実装段階

- `db-config.project_source_outputs` を Source Output の canonical definition table として追加済み。
- default の core seed は `MTOOL / RUNTIME-DBCLASSES` に加え、Custom Proxy target 用の `MTOOL / DBIMPORT-PROXY-SERVER` / `DBIMPORT-PROXY-CLIENT`、Project 1 legacy simple proxy remap 用の `MTOOL / PAYPAL-PROXY-SERVER` / `UPLOADER-PROXY-SERVER`、さらに current verified Swagger lane 用の `MTOOL / DBTABLE-PROXY-SERVER` / `OPENAPI-JSON` を持つ。
- sample/test 用の `SAMPLE-SINGLE-PROXY-SERVER` / `SAMPLE-SINGLE-PROXY-CLIENT` は `tests/scenarios/mtool-single-proxy/seed/` 配下で別管理し、default initdb には含めない。
- `project_source_outputs.target_binding_type` を追加し、source output の用途区分を explicit metadata として持てるようにした。
- default `MTOOL` row は `RUNTIME-DBCLASSES=runtime`、`DBIMPORT-PROXY-SERVER/CLIENT=custom-proxy`、`PAYPAL-PROXY-SERVER/UPLOADER-PROXY-SERVER/DBTABLE-PROXY-SERVER/OPENAPI-JSON=single-function-proxy`
  - 旧 row や未設定 row だけは `artifact_strategy` / `class_type` から effective scope を fallback 判定する
- 現行 generator は `mtool/reference/dbclasses/` を runtime reference source にして、`work/artifacts/source-outputs/{project_key}/{artifact_key}/` 配下へ artifact を出力する。
- artifact から materialize した current raw output は全 project で `work/source-outputs/{project_key}/{source_output_key}` を使う。repo に残す durable sample asset が必要な場合だけ、対応する pack の `sample/<category>/<pack>/reference/<source_output_key>/` に別保存する。
- `canonical-dbaccess-php` は root `dbaccess-*.php` wrapper と `base/*Base.php` を current raw output へ出し、sample baseline もその actual output を `reference/DBACCESS-PHP/` に置く。
- `canonical-dataclass-php` は root `data-*.php` wrapper と `base/*Base.php` を current raw output へ出し、sample baseline もその actual output を `reference/DATACLASS-PHP/` に置く。
- `RUNTIME-DBCLASSES` では `project_output_runtime_generator.php` が staging tree を作り、sync 済み `project_db_access_classes` / `project_db_access_functions` を使って root `dbaccess-*` を canonical wrapper へ差し替える。
- 現在の runtime generation mode は `canonical-dbaccess-partial-sql-regenerated` である。
- canonical metadata が十分な simple CRUD / first-pass joined select は SQL 本体まで再生成する。未対応の関数が残る場合だけ `_support/legacy-dbaccess/` の legacy compatibility class を使うが、current promoted `MTOOL` runtime reference では `legacy_delegate_function_count=0` のため generated DBAccess base は legacy 親を持たない。
- `html` bridge は `catalog://html-module/{project_key}/{source_output_key}` ref を `source_template_dir` に持てる。resolver は `mtool/reference/html-modules/` の canonical source tree を優先し、未着手 slice だけ `legacy-source-snapshots/` または `legacy-source-placeholders/` に fallback する。
- artifact には `manifest.json` schema version 3 と `tar.gz` archive を含める。
- source output artifact には、`mtool/extensions/{project_key}/{source_output_key}` 規約の custom layer も同梱する。
- `mtool/extensions/{project_key}/{source_output_key}` は人手/Codex 側の companion layer であり、current raw output と混ぜない。
  - workspace 側に custom layer があればそのまま copy する
  - 未作成なら artifact bundle 内に strategy 別 scaffold を生成する
    - PHP runtime / proxy server: `README.md` と `bootstrap.php`
    - C# proxy client: `README.md` と `ClientExtensions.cs`
- `DBIMPORT-PROXY-SERVER` / `DBIMPORT-PROXY-CLIENT` は `custom-proxy-server` / `custom-proxy-client` strategy で actual artifact を生成する。
  - `build-plan.json` と canonical custom proxy metadata を入力にして、runtime dbclasses reference から必要な source だけを集める
  - server 側は `proxyserver-*.php`、`_base/handlers/*.php`、`_wrappers/handlers/*.php`、`_support/` を出力する
  - client 側は `*ProxyClientBase.cs`、`*ProxyClient.cs`、request/result/DTO class を出力する
- `single-function-proxy` の source output strategy は core legacy remap output と optional sample/test source output の両方に対して実装済みである。
  - `project_db_access_function_source_output_targets` を読み、function 直結の naming / request / response / auth を保つ別 generator として build する
  - server 側は `single_proxy_loader.php` / `single_proxy_runtime.php` を使い、client 側は direct request/result DTO を出力する
  - `project_db_access_function_source_output_targets` はそのための canonical binding table として維持する
- current emitted `MTOOL / RUNTIME-DBCLASSES` file contract の source of truth は、この節の記述と `mtool/reference/dbclasses/` の promoted tree とする。
- promoted / emitted runtime tree の visible layout は次に固定する。
  - top-level `data-*.php` / `dbaccess-*.php`
  - `base/data-*Base.php` / `base/dbaccess-*Base.php`
  - `autoload_mtool.php`
  - `_runtime_loader.php`
  - `_support/legacy-dbaccess/`
  - `_support/runtime-generation-manifest.json`
- current emitted runtime tree には `mtool/dbclasses/_base/` / `_wrappers/` を含めない。これらは historical self-generated bundle input を `generated_catalog.php`、runtime build-plan、migration helper が読むための compatibility layout としてのみ扱う。
- current runtime artifact は、legacy basename 互換を保ちつつ visible contract を wrapper/base に固定して束ねる。
  - `mtool/dbclasses/dbaccess-*.php` は generated wrapper entry として残し、`mtool/dbclasses/base/dbaccess-*Base.php` に generated DBAccess base class を置く
  - `mtool/dbclasses/data-*.php` は root entry file として残す
  - `mtool/dbclasses/autoload_mtool.php` は compatibility entry として残すが、final runtime artifact では top-level function を持つ file だけを preload し、その他の class-like symbol は generated classmap で lazy load する
  - `project_output_runtime_generator.php` は internal staging tree では regenerated `data-*` / `dbaccess-*` を root へ書く
  - `project_output_service.php` がその staging tree を final runtime bundle へ変換する
  - DBAccess は sample1 に寄せて root wrapper + `base/` へ出力する
  - data も final artifact では全 `99` class を root wrapper + `base/data-*Base.php` へ出力する
  - 旧 PHP `FOR FUNCTION` editable area は実使用が確認できなかったため再現しない。DBAccess の custom 入口は wrapper 継承とクラス単位 helper 追加に限定する
  - plain DTO だけでなく、default property / method-only / wrapper-property / method+enum / top-level declaration を含む non-plain `data-*` も wrapper/base lane へ吸収済みである。親 DTO の raw property は non-plain class からも導出し、set 一致で順序だけ異なる場合は runtime reference 宣言順で出力する
  - final runtime artifact では `mtool/dbclasses/_base/` / `_wrappers/` を出力しない。`generated_catalog.php` と runtime build-plan は historical self-generated bundle 入力として残る `base/` / `_base/` / `_wrappers/` を logical source として解釈できる
  - `mtool/dbclasses/_support/legacy-dbaccess/` は legacy delegate が残る場合の copied support か、delegate 不要時の compatibility placeholder を置く
  - `mtool/dbclasses/_support/runtime-generation-manifest.json` に mode / counts / warnings と `data_generation_items` を残す
  - runtime-generation-manifest には source artifact provenance として `artifact_key` も残し、promote 後の `mtool/reference/dbclasses/` から最後に上げた verified self-loop を追えるようにする
  - `mtool/dbclasses/_runtime_loader.php` で custom layer の `bootstrap.php` を解決する
  - `ApacheHostSetting` / `ApacheHostSettingTemplate` は旧実装でも Apache config template 出力と host assignment infra にしか使っていないため、runtime artifact scope からは明示除外する
- `create_project_output.php` は artifact を `work/artifacts/source-outputs/{project_key}/{artifact_key}/bundle/...` に生成する。
- `work/source-outputs/{project_key}/{source_output_key}` は `publish` した artifact を current raw output として materialize したときだけ更新する。
- custom layer の entry point は strategy ごとに分ける。
  - runtime dbclasses: `bootstrap.php`、`data-*.php`、`dbaccess-*.php`
  - proxy server: `bootstrap.php`、`handlers/*.php`
  - proxy client: `ClientExtensions.cs` と companion collaborator source
- custom wrapper は root basename をそのまま使い、対応する `*Base` class を継承する。
  - 例: `data-Project.php` で `class ProjectData extends ProjectDataBase`
- artifact manifest の `customization_model` は `base-custom-wrapper-layer` とする。
- UI は `/projects/{project_key}/source-outputs`、`/{source_output_key}`、`/{source_output_key}/edit` から同じ service を呼ぶ。
- CLI は `mtool/scripts/create_project_output.php` を使う。
- compare output file 生成は `mtool/scripts/create_compare_output.php` を使う。
- compare output の初期 seed は `MTOOL / MAIN` と `MTOOL / CLIENTCOMMON` を持ち、旧 `Project 1` の CompareOutput PID 1/2 と additional path PID 6/9 を local placeholder path 向け canonical metadata として取り込んでいる。
  - `--source-output-key` 未指定時は DB なしの local default definition を使う。
  - canonical definition を読む場合は `web-admin` container から実行するか、DB 接続先を明示する。
- custom proxy の canonical metadata table と admin UI も追加済みである。
  - `project_custom_proxies`
  - `project_custom_proxy_steps`
  - `project_custom_proxy_source_output_targets`
- `admin:/projects/{project_key}/proxy/custom`、`/{custom_proxy_key}`、`/{custom_proxy_key}/functions` で metadata と step を編集できる。
- `Project 1 (Mtool)` 由来の主要 custom proxy は legacy seed 済みで、target source output も `DBIMPORT-PROXY-SERVER` / `DBIMPORT-PROXY-CLIENT` へ再マップ済みである。
- `Project 1 (Mtool)` 由来の DB Access canonical baseline も `019_project_db_access_class_function_seed.sql` / `020_project_db_access_designer_seed.sql` / `022_backfill_runtime_legacy_selectlist_sort_order_columns.sql` として seed/backfill 化済みである。
- `mtool/scripts/export_mtool_db_access_seed.php` は current config DB と legacy metadata からこの 3 file を再生成するための dev-time tool であり、legacy 側は temporary imported `legacy_seed_tmp` または host から明示指定した `--host-side --sql-dump=original-codes/mtool.sql` で与える。base Docker runtime には `original-codes/` を mount しない。runtime は `original-codes` を読まない。
- `admin:/projects/{project_key}/source-outputs/{source_output_key}` と `mtool/scripts/show_source_output_build_plan.php` は、canonical custom proxy metadata を source output 単位の build plan preview として読める。
- build 側では、この metadata を消費して first-pass multi-step proxy source を server/client ともに組み立てる。
- `GetFunc` / `ProjectTokenOrGetFunc` / `LoginCookieToken` については generated wrapper handler の auth hook を通せるようにした。
- ただし project 固有の auth 実装本体、より意味のある hook 粒度、runtime 自体の canonical self-generation はまだ次段である。

## source of truth の切り替え

### 現在

- 調査資料
- 旧 `dbclasses`
- 旧 `dev web/db/` の設定画面読解
- `db-config.project_source_outputs` に保持した canonical definition
- `db-config.project_custom_proxies` / `project_custom_proxy_steps` に保持した canonical proxy metadata

これらを暫定の source of truth として扱う。

### Export 投入後

- Export された DB 設計 metadata
- 新 `db-config` 側の canonical metadata
- 新しい generator / `ProjectSourceOutput`

こちらへ source of truth を移す。

## 新実装への含意

- `ProjectSourceOutput` は、単に外部向けの生成物を出すだけではなく、「ツール自身が使う runtime layer を出力する」責務も持つ前提で再設計する。
- `dbtable` -> `dataclass` -> `da` -> generated runtime artifact という連鎖は、新実装でも維持する。
- `admin` 側の設定画面は、この generator を動かすための canonical metadata 編集 UI になる。
- `lab` 側の build / compare は、生成結果の検証 UI になる。
- ただし現時点の generator はまだ `dbclasses` runtime を固める最小実装であり、template 展開や複数 strategy は次段で拡張する。
- 将来の Source Output generator は、generated/custom 分離を前提にし、generated file の内部へ editable region を新設しない。
- その代わりに、意味のある可変点だけ hook または collaborator 境界として generator から明示的に出力する。
- current bootstrap 実装では、まず「class 単位の Base/Custom 境界」と `bootstrap.php` だけを導入し、旧 editable region の helper をそのまま復活させない。
- つまり現在の custom は、「必要な class だけ wrapper override する」「helper / service / policy を `bootstrap.php` 経由で読む」形であり、semantic hook の粒度は今後拡張する。
- `SingleProxy_*` の単体 function auth policy については、2026-05-08 時点で shared resolver を導入し、blank を legacy default の `ProjectToken` として detail / endpoint preview から共通解釈できる。
- 同じ enum は custom proxy 側の `AuthType` / `SingleGetFuncPID` にも使うが、source of truth は分ける。
  - 単体 function proxy / endpoint preview: `project_db_access_functions.single_proxy_*`
  - 単体 function proxy の target source output: `project_db_access_function_source_output_targets`
  - multi-step custom proxy build plan / artifact: `project_custom_proxies.auth_type` / `single_get_function_name`
  - multi-step custom proxy の target source output: `project_custom_proxy_source_output_targets`
- `Custom Proxy` については canonical metadata / step UI / Project 1 seed / target key 再マップに加え、source output detail / CLI の build plan preview と first-pass actual source output build まで入った。
- single-function proxy target assignment は canonical table / UI / artifact build まで first slice 実装済みである。2026-05-25 時点の default `MTOOL` source output catalog は `runtime=1` / `custom-proxy=2` / `single-function-proxy=4` で、この 4 件は `PAYPAL-PROXY-SERVER` / `UPLOADER-PROXY-SERVER` / `DBTABLE-PROXY-SERVER` / `OPENAPI-JSON` の core definition として扱う。optional sample/test seed を適用した場合だけ、ここに `SAMPLE-SINGLE-PROXY-*` が追加されて合計 6 件になる。function detail では `DBIMPORT-PROXY-*` を single-function target 候補へ出さず、逆に custom proxy detail では `RUNTIME-DBCLASSES` を target 候補へ出さない。boundary は UI / normalize helper に加えて `project_source_outputs.target_binding_type` の explicit metadata でも固定した。
- `sync_project_db_access.php` は canonical-bootstrap function を初回 insert するとき、generic single-function outputs が存在すれば `DBTABLE-PROXY-SERVER` / `OPENAPI-JSON` へ default target assignment する。これにより `lab-live-schema -> import -> Data Class sync -> DB Access sync -> publish -> Lab Swagger` の current lane を seed だけで再現できる。
- `GetFunc` 系 auth と `LoginCookieToken` は generated wrapper handler の hook 境界までは入った。
- ただし project 固有の auth 実装、より意味のある hook 粒度、transaction / failure policy の高度化はまだ次段である。
- compare output definition 自体は `project_compare_outputs` / `project_compare_output_additional_paths` に保存し、local filesystem 向けの compare output generator から参照し始めている。
- compare 実行 job と結果レビュー UI は `lab` 側へ接続済みであり、template asset / ignore rule asset の編集 UI も `admin` 側へ接続済みである。
- compare output まわりで残る次段は、job history を DB 化したい場合の境界設計と、Build 実行系との足並み合わせである。

## 移行ステップ

### Phase 1 側

1. 旧 `dbclasses` の naming rule を新実装 docs に固定する。
2. legacy recovery copy 先と loader 位置を決める。
3. DB 設計データ Export を投入する。
4. new metadata からの generator を実装する。
5. current route / canonical metadata で機能と Output parity を揃える。

### Phase 2 側

6. legacy recovery copy への依存を段階的に外し、runtime self-generation を主系へ寄せる。
7. generated runtime を authoritative source として app 自身が読めるようにする。
8. Mtool 自身が出力した Runtime へ差し替え、再編集なしの継続運用を確認する。

## 現時点の結論

- 新バージョンでも、`dbclasses` 相当は自己生成・自己利用する前提で進める。
- current default は promoted self-generated reference を使い、旧生成物コピーは recovery 用に残す。
- file basename は旧実装へ寄せる。
- current runtime reference path は `mtool/reference/dbclasses/` とする。
- path と loader の最終形は、generator 再建時に再整理する。

## 自己置換の判断基準

- これは Phase 1 の完了条件ではなく、Phase 2 に入れるかどうかの判断基準である。
- 「自身の出力コードに自分を置き換える」とは、単に artifact を export できることではない。
- 少なくとも次の 3 条件が揃って初めて、自己置換フェーズへ入ったと見なす。
  - canonical metadata から runtime layer を再生成でき、`legacy-copy-bootstrap` に依存しない
  - proxy 系 source output も build plan preview ではなく actual source として生成できる
  - app 自身がその generated runtime を authoritative source として読める
- 2026-05-19 時点では、`RUNTIME-DBCLASSES` は `canonical-dbaccess-partial-sql-regenerated` により `sql_regenerated_dbaccess_count=98` / `sql_regenerated_function_count=505` / `canonical_helper_function_count=7` / `canonical_data_class_count=99` / `data_entity_count=99` / `plain_data_candidate_count=63` / `non_plain_data_candidate_count=36` / `bootstrap_data_class_count=0` / `legacy_delegate_function_count=0` まで到達した。zero-arg の fixed/raw cleanup DELETE に加え、same-table OR group を含む select/update/delete、helper-style method、親 DTO の raw property 導出と順序差吸収、upstream import / sync 済み `dbtable` / `dataclass` metadata と stale `sync-bootstrap` field filter を使った plain DTO `data-*` の canonical 化、さらに `TestPattern` / `TestConditionSelection` の default-property migration、`da` / `dataclass` の method-only `ADDITIONAL CLASS DEFINITION` migration、`dbtablecolumns` の wrapper-property + helper-method migration、`Project` / `ProjectSourceOutput` / `Req` / `da*` / `htmlTemplateParameter` の method+enum migration、`ProjectUser` / `SpecContent` / `htmlTemplate` の top-level declaration migration まで入っている。legacy input `dbaccess-*` の constructor は全件 empty だったため、generated class 側で no-op constructor を直接持つようにし、dbaccess delegate は残していない。final runtime bundle では全 `data-*` が `root data-*.php wrapper + base/data-*Base.php` へ移行済みであり、transition-state `_base/` / `_wrappers/` layout は出力に残らない。custom proxy auth の source of truth を `project_custom_proxies` 側へ寄せたため、`daCustomProxyFunc_leftouterjoin_dafunc_and_da` では legacy `AuthType` / `SingleGetFuncPID` を保持しない。`ApacheHostSetting` / `ApacheHostSettingTemplate` は Apache config template / host assignment infra 用のため runtime bundle scope から除外している。`sync_project_db_access.php` は method catalog に無い `sync-bootstrap` function row を prune するため、warning は `0` 件である。full self-loop は self-generated artifact 入力と promoted default reference 入力の両方で通過し、current default mode は `config.php` が `self-generated-reference:canonical-dbaccess-partial-sql-regenerated` を自動判定する。
- 現 current baseline では `bootstrap_data_class_count=0`、`fallback_dbaccess_count=0`、`legacy_delegate_function_count=0` であり、runtime dbclasses 本体に bootstrap copy / legacy delegate 残件はない。ただし future に見つかる helper-heavy `data-*`、未知の class/file contract、file/blob parameter を含む query は別である。legacy blob target は bare `?` だけではなく `prepare()` + `bind_param("b")` + `send_long_data()` 契約を持つため、current generator は `is_blob_target=1` を regenerate せず delegate する。admin / sync / bridge から canonical metadata を保存する際も repository 層で同じ contract を再検証し、unsupported な `IsBlobTarget=1` や `parameter_data_type=file` を流し込めないようにしている。さらに `export_mtool_db_access_seed.php` も current runtime reference に対して blob/file preflight を走らせるため、direct SQL seed/export でも unsupported metadata を持ち出さない。`select_having` は canonical `select_target_fields` を参照する argument / fixed / field 比較まで current generator で扱え、`parameter_type=anotherfield` を含む joined select も non-empty `or_group` / `or_group_type=andorand` を含む join ON grouping まで current generator で扱える。
- runtime 置換の運用は 2 段で進める。plain DTO、simple CRUD、既に manifest/self-loop で generated と確認できる単純形は direct replacement 対象とし、`MTOOL` runtime へそのまま広げてよい。一方で non-plain `data-*`、helper-heavy class、複数 declaration、未知の class/file contract は sample gate を先に通す。current complex lane の gate / coverage は `tests/Integration/Sample9TestPatternDefaultPropertyOutputTest.php`、`Sample10CompareOutputCompanionDeclarationsOutputTest.php`、`Sample11DaDataclassMethodOnlyOutputTest.php`、`Sample12DbtablecolumnsWrapperPropertyOutputTest.php`、`Sample13ReqMethodAndEnumOutputTest.php`、`Sample14BuildSourceFuncCacheCompanionDeclarationsOutputTest.php`、`Sample15BuildLogCompanionDeclarationsOutputTest.php`、`Sample16LiveCheckResultCompanionDeclarationsOutputTest.php`、`Sample17SpecContentTopLevelDeclarationOutputTest.php`、`Sample18ProjectUserTopLevelDeclarationOutputTest.php`、`Sample19HtmlTemplateTopLevelDeclarationOutputTest.php`、`Sample20DaCustomProxyMethodAndEnumOutputTest.php`、`Sample21ProjectMethodAndEnumOutputTest.php`、`Sample22ProjectSourceOutputMethodAndEnumOutputTest.php`、`LegacyTopLevelDeclarationMigrationTest.php` である。
- したがって現状は自己置換フェーズの入口を超えており、Step 2 / Phase 2 の進捗は約 `70-75%` とみなす。ただしここで追う `full replacement` は historical contract の literal `100%` ではなく、current supported runtime scope を self-host / authoritative runtime として閉じる `bounded full replacement` と読む。主な残りは、`bounded full replacement` と dbclass/runtime output zero-copy goal の境界明文化である。`mtool` 実処理コードの historical copy はこの goal の対象外とし、sample/test 側 curated legacy fixture も migration gate input として別枠に置く。`file/blob` contract は current live metadata に無いため optional unsupported track として別扱いにし、current inventory で `unclassified_non_plain_items=0` の complex lane は future に新形が出た時だけ sample gate を追加する。
