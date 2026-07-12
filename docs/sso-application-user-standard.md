# SSO Application User Standard / SSOアプリケーションユーザ標準

English companion:
This document is the stable design standard for Mtool projects that authenticate users through SSO. Keep the verified external identity `(issuer, subject)` separate from the application's stable `app_user_id`; never use email as an identity key; persist only allowlisted profile fields; and keep exceptional lifecycle rules at an explicit custom boundary.

この文書は、SSOで認証するapplicationをMtoolまたはAIが設計するときの標準方向です。特別な要件がなければこの形を提案し、要件がある場合は差分と理由を明示します。

## 目標

利用者がSSOを選んだとき、次の一般的な処理を毎回ゼロから設計しなくてよい状態を目指します。

- 検証済みSSO principalから同じapplication userを安定して認識する
- 初回ログイン時にuserを作成し、再ログイン時に同じuserを復元する
- SSO由来profileとapplication固有profile・業務dataを適切に分離する
- user所有dataをIdPから独立した識別子へ紐付ける
- token、password、secret、raw claimsをuser dataへ保存しない
- 標準外のaccount lifecycleやbusiness ruleはcustom境界として残す

MtoolはIdPにはなりません。OIDCなどで認証済みのprincipalを、application userへ安全に対応付ける部分を標準化します。

## 識別子の標準

### 外部identity

OIDCの標準外部identity keyは次です。

```text
(normalized issuer, subject)
```

- `issuer` は検証済みtokenまたは固定されたprovider設定から得る
- issuerは末尾slashなど、provider contractで許可した範囲だけ正規化する
- `subject` は文字列として不透明に扱い、意味を推測しない
- `email`、`preferred_username`、display nameは外部identity keyにしない
- audience、tenant、organizationが追加境界になる場合はprovider policyとして明示する

`provider_key` を設定参照用に持つことはできますが、issuer・subjectの検証を省略するためには使いません。

### Application user

applicationはIdPから独立した不透明かつ不変な `app_user_id` を所有します。

- 業務tableのowner、creator、assigneeなどは `app_user_id` を参照する
- IdP移行時はexternal identity mappingを変更し、業務dataのuser IDは変更しない
- 一人のapp userへ複数external identityを紐付ける場合は、明示的で検証済みのlink処理を使う
- email一致だけで別identityを自動linkしない
- `app_user_id` は連番、UUIDなどを採用できるが、外部subjectをそのまま主keyにしない

### App-local identity

`app-local-user-identity-v0` の `local_user_id` は、安全なlocal cache・offline handoff用の識別子です。server側のcanonical `app_user_id` とは役割が異なります。

- server解決前は `local_user_id` をlocal cache keyとして利用できる
- server解決後の業務dataは `app_user_id` を使う
- sync actorは必要に応じて両方を持てるが、client値だけでserver authorizationを確定しない

## 標準data model

物理table名はproject規約に合わせて変更できますが、責務は分離します。

| Logical record | 主なfield | 責務 |
| --- | --- | --- |
| `app_user` | `app_user_id`, status, created/updated timestamps | application userの不変identityとlifecycle |
| `app_user_external_identity` | `app_user_id`, provider key, issuer, subject, first/last authenticated timestamps | SSO identityからapp userへのmapping |
| `app_user_profile` | `app_user_id`, allowlisted profile fields, ownership/source metadata | safe profile cacheとapplication profile |
| domain records | owner/creator等の`app_user_id` | application固有の保存data |
| roles/memberships | `app_user_id`, scope, role | server-authoritative authorization |

必須invariant:

- `(normalized_issuer, subject)` は一意
- external identityは同時に複数app userへ紐付かない
- domain recordsは外部subjectやemailではなく `app_user_id` を参照
- requiredなJIT作成は同一DB connection・一transactionで行う

小規模applicationではprofile fieldを `app_user` に同居させても構いません。ただし、field ownershipと外部identity mappingの分離は維持します。

## Profile field ownership

保存する各fieldを次のいずれかに分類します。

| Class | 更新主体 | 例 | 標準動作 |
| --- | --- | --- | --- |
| `sso_managed` | 検証済みSSO principal | display name, email, locale | allowlistにあるfieldだけlogin時にrefresh |
| `application_managed` | applicationまたはuser操作 | nickname, preference, onboarding state | SSO refreshで上書きしない |
| `server_authoritative` | server policy | role, membership, account status | local cacheを最終判断に使わない |
| `forbidden` | 保存不可 | token, password, client secret, raw claims | rejectまたは除外し、永続化しない |

emailが変わっても同じ `(issuer, subject)` なら同じ `app_user_id` を復元し、allowlist policyに従ってemailを更新します。emailが同じでもexternal identityが異なれば、既存userへ自動統合しません。

## Login and provisioning flow

### 共通flow

1. SSO protocol validationを完了する
2. trusted principalからissuer・subjectを正規化する
3. external identity mappingを検索する
4. mappingがあれば同じ `app_user_id` を復元する
5. allowlisted SSO-managed profileをrefreshする
6. account statusとserver authorizationを評価する
7. application-owned user dataを別途loadする

### JIT provisioning

unknown identityを許可するprojectでは、次を一transactionで行います。

1. `app_user` を作る
2. `app_user_external_identity` を作る
3. 必要な初期profileを作る
4. 全て成功した場合だけcommitする

unique競合が発生した場合は、重複userを作らずrollbackし、既存mappingを安全に再読込します。失敗を部分成功として扱いません。

### Invitation-only

unknown identityを自動作成しません。validなSSO loginであっても、事前許可mappingまたは明示enrollmentがなければfail closedにします。

projectは `jit` または `invitation-only` を明示的に選択します。選択がなければMtool/AIは確認し、production向け生成を推測で続行しません。

## Lifecycle boundary

標準で扱う範囲:

- first login createまたはdeny
- repeat login restore
- allowlisted profile refresh
- enabled/disabled status確認
- last authenticated timestamp
- application dataへの `app_user_id` handoff

projectごとの明示設計が必要な範囲:

- identity link/unlink、account merge
- invitation、organization/tenant membership
- IdP移行とsubject再割当
- SCIM provisioning/deprovisioning
- retention、export、erasure、anonymization
- account recoveryとsupport operator操作

IdPでloginできなくなったことを理由にapplication dataを自動削除しません。

## Security rules

- access token、refresh token、ID token、password、client secretをprofile、App-local identity、audit metadataへ保存しない
- full/raw claimsを保存せず、用途が決まったallowlisted fieldだけ抽出する
- clientから送られたissuer、subject、role、`app_user_id`だけでserver authorizationしない
- serverは検証済みsession/tokenからprincipalを確定し、mappingとaccount statusを再確認する
- role・membershipはserver-authoritative dataとして扱う
- login auditには必要最小限のidentifierと結果を残し、secretや過剰な個人情報を含めない
- 同一identityの同時JIT作成はunique constraintとtransactionで収束させる

## Mtool・AIが最初に確認する事項

SSO利用が判明したら、標準を提示したうえで、結果を変える次の事項だけ確認します。

1. unknown SSO userは `jit` で作成するか、`invitation-only` にするか
2. SSOから保存・refreshするprofile fieldは何か
3. application側でuserが編集するprofile fieldは何か
4. userが所有・作成・担当する業務dataは何か
5. organization/tenant境界があるか
6. disabled、retention、erasure、identity linkに標準外要件があるか

特別な回答がなければ、`issuer + subject` mapping、opaque `app_user_id`、email非識別子、credential非保存を標準として使います。

## Design validation checklist

生成または設計案を承認する前に確認します。

- [ ] 外部identity keyがissuerとsubjectを含む
- [ ] emailやusernameをuser identity keyにしていない
- [ ] application-owned `app_user_id` がある
- [ ] 業務dataが `app_user_id` を参照する
- [ ] provisioning modeが明示されている
- [ ] JITのrequired writeが一transactionである
- [ ] profile field ownershipが分類されている
- [ ] token、secret、raw claimsが保存対象外である
- [ ] client cacheをserver authorizationの根拠にしていない
- [ ] account link、disable、retentionなどcustom lifecycle境界が明示されている
- [ ] first login、repeat login、email変更、required write失敗がtest対象である

## 現在の実装対応状況

現在は、OIDC principal検証・safe field handoff、App-local identity保存/復元、sync actor伝搬、生成DBAccess、Transaction Full基盤まで実証済みです。

canonical server `app_user_id`、external identity mapping、JIT resolver、profile ownership contractのruntime/generator実装は、この標準に従う次phaseです。したがって、この文書は現在の全機能対応をclaimするものではなく、設計の正本です。
