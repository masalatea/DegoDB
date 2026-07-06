# Current Plans / 現在の計画

English companion:
This page lists only unfinished or deferred plans. Completed work is kept in dated reports.

This page is the active plan index for DegoDB. / このページは、DegoDB の現在有効な計画索引です。

Use this page before searching historical reports. / 履歴 report を探す前に、まずこのページを見ます。

## Quick Plan List / 計画リスト

When someone asks for "the plan list", answer from this section first. / 「計画リスト」と聞かれたら、まずこの section から答えます。

Answer the main plan first, then mention auxiliary later-review items only when the question asks for all parked / deferred work too. / まず主計画を答え、補助・後日検討項目は parked / deferred も含めて聞かれた場合だけ補足します。

### Main Plan To Minimum / minimum までの主計画

This is the mainline for reaching the next minimum product-facing no-code milestone. / これは次の minimum な product-facing no-code milestone へ到達するための主線です。

Current baseline: the first-slice no-code Web interface already exists as generated `runtime-preview.html` / `runtime-preview.json` plus operator/admin inspection surfaces. React + TypeScript is now the default first adapter direction, the first React bridge artifact slice is complete as `no-code-react-bridge`, the generated React bridge build/browser smokes are complete for sample28, React bridge display/form state shaping is complete for the first slice, React bridge artifact contract hardening is complete for the first slice, editable React bridge form state is complete for the first slice, React bridge validation hint display is complete for the first slice, React bridge action feedback display is complete for the first slice, JSON Forms / rjsf transform probe is complete for the first slice, schema-form runtime smoke and consumer notes are complete for the first slice, generated runtime visual/accessibility/keyboard-action polish is complete for the first slice, adapter handoff docs are complete for the first slice, retry audit trail/display is complete for the first slice, operator/admin no-code workflow polish is complete for the first slice, the no-code minimum closure report is complete for the first slice, worktree closure commit-hygiene notes are complete for the first slice, commit group execution is complete without pushing, validation parity/polish is complete for the first slice, no-code product surface boundary inventory is complete for the first slice, published no-code runtime artifact selection is complete for the first slice, approval/revision history boundary inventory is complete for the first slice, publish candidate revision record schema contract is complete for the first slice, approval transition state model is complete for the first slice, approval action UI contract is complete for the first slice, approval route/test implementation planning is complete for the first slice, publish candidate persistence implementation checklist is complete for the first slice, publish candidate migration/source-contract checklist is complete for the first slice, publish candidate repository/API contract test matrix is complete for the first slice, Docker-backed verification rerun is complete, minimal publish candidate persistence is complete for the first slice, approval transition persistence is complete for the first slice, guarded publish candidate detail UI is complete for the first slice, approved candidate package exposure is complete for the first slice, public runtime preview artifact-key route is complete for the first slice, public runtime current alias route is complete for the first slice, candidate event display polish is complete for the first slice, public runtime cache/version policy is complete for the first slice, current public revision visibility is complete for the first slice, explicit current public revision selection is complete for the first slice, custom public alias key storage is complete for the first slice, public alias delete workflow is complete for the first slice, rollback workflow polish is complete for the first slice, public delivery browser smoke is complete for the first slice, alias lifecycle audit trail is complete for the first slice, public delivery hardening closure is complete, public delivery commit cleanup is complete without pushing, post-public-delivery-commit replan is complete, local app packaging boundary inventory is complete for the first slice, post-local-app-packaging-boundary-inventory replan is complete, App-local package manifest is complete for the first slice, post-App-local-package-manifest replan is complete, App-local package archive smoke is complete for the first slice, post-App-local-package-archive-smoke replan is complete, App-local package operator readiness display is complete for the first slice, post-App-local-package-operator-readiness-display replan is complete, local app packaging closure is complete, post-local-app-packaging-closure replan is complete, the no-code product milestone update after public delivery and local packaging is complete, the second-domain runtime submit/processing confidence stack has been pushed, the #179-#182 no-code next phase is complete locally, #183 pre-push stack review is complete, and #186 closes the third-domain runtime submit/processing confidence boundary. The next natural step is #188 local commit stack review before push or another large behavior lane. / 現在の baseline として、first-slice の no-code Web interface は generated `runtime-preview.html` / `runtime-preview.json` と operator / admin inspection surface として既にあります。React + TypeScript は first adapter の基本方向になり、最初の React bridge artifact slice は `no-code-react-bridge` として完了し、sample28 の generated React bridge build / browser smoke も完了し、React bridge display / form state shaping も first slice として完了し、React bridge artifact contract hardening も first slice として完了し、editable React bridge form state も first slice として完了し、React bridge validation hint display も first slice として完了し、React bridge action feedback display も first slice として完了し、JSON Forms / rjsf transform probe も first slice として完了し、schema-form runtime smoke と consumer notes も first slice として完了し、generated runtime visual / accessibility / keyboard-action polish も first slice として完了し、adapter handoff docs も first slice として完了し、retry audit trail / display も first slice として完了し、operator / admin no-code workflow polish も first slice として完了し、no-code minimum closure report も first slice として完了し、worktree closure commit-hygiene notes も first slice として完了し、commit group execution も push なしで完了し、validation parity / polish、no-code product surface boundary inventory、published no-code runtime artifact selection、approval / revision history boundary inventory、publish candidate revision record schema contract、approval transition state model、approval action UI contract、approval route / test implementation planning、publish candidate persistence implementation checklist、publish candidate migration / source-contract checklist、publish candidate repository / API contract test matrix、Docker-backed verification rerun、minimal publish candidate persistence first slice、approval transition persistence first slice、guarded publish candidate detail UI first slice、approved candidate package exposure first slice、public runtime preview artifact-key route first slice、public runtime current alias route first slice、candidate event display polish first slice、public runtime cache/version policy first slice、current public revision visibility first slice、explicit current public revision selection first slice、custom public alias key storage first slice、public alias delete workflow first slice、rollback workflow polish first slice、public delivery browser smoke first slice、alias lifecycle audit trail first slice、public delivery hardening closure、public delivery commit cleanup、post-public-delivery-commit replan、local app packaging boundary inventory first slice、post-local-app-packaging-boundary-inventory replan、App-local package manifest first slice、post-App-local-package-manifest replan、App-local package archive smoke first slice、post-App-local-package-archive-smoke replan、App-local package operator readiness display first slice、post-App-local-package-operator-readiness-display replan、local app packaging closure、post-local-app-packaging-closure replan、public delivery と local packaging 後の no-code product milestone update、second-domain runtime submit / processing confidence stack の push、#179-#182 no-code next phase の local 完了、#183 pre-push stack review、#186 third-domain runtime submit / processing confidence closure まで進みました。次の自然な作業は push または大きな behavior lane の前の #188 local commit stack review です。

Latest update: no-code tryout-ready milestone is complete through #67, #69/#70 verification is pushed, #71 tryout UX polish is pushed, and #72 runtime action intent draft is pushed. #73 adds draft readiness checks so the local `Action Intent Draft` explains why a draft is blocked, for example disabled action policy or missing required key/input fields, while still avoiding server mutation. #74 adds a short human-readable draft summary above the JSON so blocked previews call out checks without requiring JSON parsing. #75 extends that summary with policy failed checks such as `principal.missing`, keeping the JSON detail and non-mutating preview boundary unchanged. #76 adds a copy affordance for the current draft JSON so tryout users can carry the local intent draft into notes or follow-up debugging. #77 adds a compact action metadata row so the draft panel shows action key, operation key, and operation type without opening the JSON. #78 adds a payload count row so key/input/filter field counts are visible without reading the JSON. #79 adds a visible draft state badge that mirrors ready/blocked/empty state. #80 chose a field summary row as the next small runtime intent-draft polish. #81 adds that field summary so key/input/filter field names are visible without opening the JSON. #82 chose collapsible draft JSON as the next readability slice after the summary rows were in place. #83 wraps detailed draft JSON in a `Draft JSON` disclosure while keeping copy behavior and the non-mutating preview boundary unchanged. #84 chose a closure report before starting another lane. #85 closes the runtime action intent draft polish lane and records accepted capability / remaining candidates. #86 chose local commit stack review before another product implementation lane. #87 records the 10-commit local stack boundary and latest verification baseline. #88 keeps push/history rewrite out of scope and chooses required-field guidance as the next implementation lane. #89 adds inline required badges and hints to generated runtime forms. Focused checks, sample28 runtime UI smoke, and full Integration PHPUnit passed on clean buildless stacks. Push was not performed for #73/#74/#75/#76/#77/#78/#79/#80/#81/#82/#83/#84/#85/#86/#87/#88/#89. / 最新更新: no-code tryout-ready milestone は #67 まで完了し、#69/#70 verification、#71 tryout UX polish、#72 runtime action intent draft は push 済みです。#73 では draft readiness checks を追加し、local の `Action Intent Draft` が disabled action policy や required key/input field 不足など、draft が blocked になる理由を説明できるようにしました。server mutation は引き続き実行しません。#74 では JSON の上に短い人間向け draft summary を追加し、blocked preview が JSON parsing なしで checks を見せられるようにしました。#75 では summary に `principal.missing` などの policy failed checks も表示し、JSON detail と non-mutating preview boundary は維持しています。#76 では current draft JSON の copy affordance を追加し、tryout user が local intent draft をメモや follow-up debugging に持ち出しやすくしました。#77 では compact action metadata row を追加し、JSON を開かなくても action key、operation key、operation type が分かるようにしました。#78 では payload count row を追加し、key / input / filter field count を JSON なしで確認できるようにしました。#79 では ready / blocked / empty state を反映する visible draft state badge を追加しました。#80 では次の小さな runtime intent-draft polish として field summary row を選びました。#81 では key / input / filter の field name を JSON なしで確認できる field summary を追加しました。#82 では summary row が揃った後の次の readability slice として collapsible draft JSON を選びました。#83 では detailed draft JSON を `Draft JSON` disclosure に包み、copy behavior と non-mutating preview boundary は維持します。#84 では次 lane に入る前に closure report を選びました。#85 では runtime action intent draft polish lane を closure し、accepted capability / remaining candidates を記録しました。#86 では次の product implementation lane の前に local commit stack review を選びました。#87 では 10-commit local stack boundary と latest verification baseline を記録しました。#88 では push / history rewrite を scope 外に保ち、次 implementation lane として required-field guidance を選びました。#89 では generated runtime form に inline required badge / hint を追加しました。focused checks、sample28 runtime UI smoke、full Integration PHPUnit は clean buildless stack 上で通過しました。#73/#74/#75/#76/#77/#78/#79/#80/#81/#82/#83/#84/#85/#86/#87/#88/#89 は push していません。

Latest continuation after #89: #90 records the post-required-field-guidance replan and chooses closure before starting another implementation lane. #91 closes the runtime required field guidance lane with accepted capability, verification baseline, and remaining candidates. Push was not performed for #90/#91. / #89 後の続き: #90 では required-field guidance 後の再計画を記録し、次の implementation lane に入る前に closure を選びました。#91 では runtime required field guidance lane を closure し、accepted capability、verification baseline、remaining candidates を記録しました。#90/#91 は push していません。

Latest continuation after #91: #92 chooses runtime required-field live hints as the next small implementation lane. #93 updates required-field hints in generated runtime forms so they switch between present and missing state as a user edits, while preserving the non-mutating action-intent preview boundary. Focused `NoCodeRuntimeTest`, sample28 runtime UI smoke, and full `make test` passed after resetting the sample01 stack to remove stale lab DB state. Push was not performed for #92/#93. / #91 後の続き: #92 では次の小さな implementation lane として runtime required-field live hints を選びました。#93 では generated runtime form の required-field hint が user editing に追従して present / missing state を切り替えるようにし、non-mutating action-intent preview boundary は維持しました。focused `NoCodeRuntimeTest`、sample28 runtime UI smoke、sample01 stack reset 後の full `make test` は通過しました。#92/#93 は push していません。

Latest continuation after #93: #94 chooses a closure report before starting server-backed execution, richer validation wording, another sample, or commit/push cleanup. #95 closes the runtime required-field live hints lane and records the accepted browser-local present/missing feedback capability. Push was not performed for #94/#95. / #93 後の続き: #94 では server-backed execution、より詳しい validation wording、別 sample、commit / push cleanup に入る前に closure report を選びました。#95 では runtime required-field live hints lane を closure し、browser-local の present / missing feedback capability を accepted capability として記録しました。#94/#95 は push していません。

Latest continuation after #95: #96 chooses richer required-field validation wording as the next small implementation lane. #97 updates generated runtime required hints so live present/missing feedback includes the action field role and rendered field label, while keeping the non-mutating action-intent preview boundary. Focused `NoCodeRuntimeTest`, sample28 runtime UI smoke, and full `make test` passed. Push was not performed for #96/#97. / #95 後の続き: #96 では次の小さな implementation lane として required-field validation wording の強化を選びました。#97 では generated runtime required hint の live present / missing feedback に action field role と rendered field label を含め、non-mutating action-intent preview boundary は維持しました。focused `NoCodeRuntimeTest`、sample28 runtime UI smoke、full `make test` は通過しました。#96/#97 は push していません。

Latest continuation after #97: #98 chooses a closure report before starting server-backed execution, another sample, or commit/push cleanup. #99 closes the runtime required-field validation wording lane and records the accepted action-role / rendered-label feedback capability. Push was not performed for #98/#99. / #97 後の続き: #98 では server-backed execution、別 sample、commit / push cleanup に入る前に closure report を選びました。#99 では runtime required-field validation wording lane を closure し、action role / rendered label を含む feedback capability を accepted capability として記録しました。#98/#99 は push していません。

Latest continuation after #99: #100 chooses a local commit stack review before server-backed execution, another sample, or push cleanup. #101 records the 17-commit local stack boundary, grouped review shape, and latest verification baseline without pushing. / #99 後の続き: #100 では server-backed execution、別 sample、push cleanup の前に local commit stack review を選びました。#101 では 17 commits の local stack boundary、review grouping、latest verification baseline を push せずに記録しました。

Latest continuation after #101: #102 chooses a server-backed runtime execution boundary inventory before adding a user-facing mutation path. #103 records that backend dispatch helpers and generated DBAccess execution coverage already exist, while generated runtime preview remains browser-local until auth, policy, CSRF, target binding, and result-refresh boundaries are explicit. Push was not performed for #102/#103. / #101 後の続き: #102 では user-facing mutation path を追加する前に server-backed runtime execution boundary inventory を選びました。#103 では backend dispatch helper と generated DBAccess execution coverage は既にある一方で、auth、policy、CSRF、target binding、result refresh の境界が明示されるまで generated runtime preview は browser-local のままにする、と記録しました。#102/#103 は push していません。

Latest continuation after #103: #104 chooses a runtime execution request contract before adding a public mutation endpoint. #105 adds a fail-closed helper that normalizes POST request data into action key, input, and runtime artifact binding after POST, CSRF, project, and artifact checks. PHP lint, focused `NoCodeRuntimeTest`, sample28 runtime UI smoke, `git diff --check`, and full `make test` passed. Push was not performed for #104/#105. / #103 後の続き: #104 では public mutation endpoint を追加する前に runtime execution request contract を選びました。#105 では POST、CSRF、project、artifact checks の後に POST request data を action key、input、runtime artifact binding へ正規化する fail-closed helper を追加しました。PHP lint、focused `NoCodeRuntimeTest`、sample28 runtime UI smoke、`git diff --check`、full `make test` は通過しました。#104/#105 は push していません。

Latest continuation after #105: #106 chooses a server-backed dispatch entrypoint helper before adding a public mutation route or generated preview submission wiring. #107 combines the request contract with existing dispatch in a fail-closed helper so tests can verify that invalid requests never reach the dispatcher. PHP lint, focused `NoCodeRuntimeTest`, sample28 runtime UI smoke, `git diff --check`, and full `make test` passed. Push was not performed for #106/#107. / #105 後の続き: #106 では public mutation route や generated preview submission wiring の前に server-backed dispatch entrypoint helper を選びました。#107 では request contract と既存 dispatch を fail-closed helper で合成し、不正 request が dispatcher に届かないことを test で固定しました。PHP lint、focused `NoCodeRuntimeTest`、sample28 runtime UI smoke、`git diff --check`、full `make test` は通過しました。#106/#107 は push していません。

Latest continuation after #107: #108 chooses a runtime execution endpoint response contract before adding route wiring or generated preview submission. #109 adds an internal endpoint-response helper that maps server-backed execution results to HTTP status and JSON payload shape without sending headers or registering a public mutation route. PHP lint, focused `NoCodeRuntimeTest`, sample28 runtime UI smoke, `git diff --check`, and full `make test` passed. Push was not performed for #108/#109. / #107 後の続き: #108 では route wiring や generated preview submission の前に runtime execution endpoint response contract を選びました。#109 では server-backed execution result を HTTP status と JSON payload shape に写す internal endpoint-response helper を追加し、header 送信や public mutation route 登録は行っていません。PHP lint、focused `NoCodeRuntimeTest`、sample28 runtime UI smoke、`git diff --check`、full `make test` は通過しました。#108/#109 は push していません。

Latest continuation after #109: #110 chooses an authenticated artifact-key execution route before current/alias execution routes or generated preview submission wiring. #111 registers the `/runs/no-code/{project}/{artifact}/execute.json` route, keeps the preview HTML public, requires auth for the mutation endpoint, loads the approved candidate screen definition, and connects the existing request/dispatch/endpoint response helpers to managed-operation outbox enqueue. PHP lint, focused route/runtime PHPUnit, sample28 runtime UI smoke, `git diff --check`, and full `make test` passed. Push was not performed for #110/#111. / #109 後の続き: #110 では current / alias execution route や generated preview submission wiring の前に、authenticated artifact-key execution route を選びました。#111 では `/runs/no-code/{project}/{artifact}/execute.json` route を登録し、preview HTML は public のまま、mutation endpoint は auth required にし、approved candidate の screen definition を読み、既存の request / dispatch / endpoint response helper を managed-operation outbox enqueue へ接続しました。PHP lint、focused route / runtime PHPUnit、sample28 runtime UI smoke、`git diff --check`、full `make test` は通過しました。#110/#111 は push していません。

Latest continuation after #111: #112 chooses authenticated current/alias execution route variants before generated preview submission wiring. #113 registers `/runs/no-code/{project}/current/execute.json` and `/runs/no-code/{project}/alias/{alias}/execute.json`, keeps preview HTML public, requires auth for both mutation endpoints, resolves the same approved candidates as the public preview routes, and dispatches through the existing runtime execution helper. PHP lint, focused `OpenApiSourceOutputContractTest`, sample28 runtime UI smoke, `git diff --check`, and full `make test` passed. Push was not performed for #112/#113. / #111 後の続き: #112 では generated preview submission wiring の前に、authenticated current / alias execution route variant を選びました。#113 では `/runs/no-code/{project}/current/execute.json` と `/runs/no-code/{project}/alias/{alias}/execute.json` を登録し、preview HTML は public のまま、両 mutation endpoint は auth required にし、public preview route と同じ approved candidate を解決して既存 runtime execution helper へ dispatch します。PHP lint、focused `OpenApiSourceOutputContractTest`、sample28 runtime UI smoke、`git diff --check`、full `make test` は通過しました。#112/#113 は push していません。

Latest continuation after #113: #114 chooses generated preview submission wiring now that artifact/current/alias execution routes exist. #115 adds a `Submit to server` control, injects current/alias execution binding at no-store preview response time, preserves immutable artifact preview caching, and keeps blocked drafts from submitting. PHP lint, focused runtime/public-route PHPUnit, sample28 runtime UI smoke, and direct current/alias browser smokes passed. Push was not performed for #114/#115. / #113 後の続き: #114 では artifact / current / alias の execution route が揃ったため、generated preview submission wiring を選びました。#115 では `Submit to server` control を追加し、no-store の current / alias preview response 時に execution binding を注入し、immutable artifact preview cache は維持し、blocked draft は submit できないままにしました。PHP lint、focused runtime / public-route PHPUnit、sample28 runtime UI smoke、direct current / alias browser smoke は通過しました。#114/#115 は push していません。

Latest continuation after #115: #116 chooses public runtime execution binding smoke hardening before moving to enabled-action execution or another sample. #117 makes the browser smoke require no execution binding for immutable artifact previews and require matching current/alias execution binding URLs for no-store previews. `make sample28-no-code-public-runtime-browser-smoke` passed. Push was not performed for #116/#117. / #115 後の続き: #116 では enabled-action execution や別 sample に進む前に public runtime execution binding smoke hardening を選びました。#117 では browser smoke が immutable artifact preview では execution binding なし、no-store の current / alias preview では一致する execution binding URL あり、を必須確認するようにしました。`make sample28-no-code-public-runtime-browser-smoke` は通過しました。#116/#117 は push していません。

Latest continuation after #117: #118 chooses a browser-side enabled submit payload smoke before real server mutation behavior or another sample. #119 adds a fetch-stub submit probe to the sample28 public runtime browser smoke: artifact previews still skip execution, while current/alias previews force one generated action ready in the browser, click `Submit to server`, and verify POST URL, same-origin credentials, CSRF, action binding, key, and input payload shape without mutating the server. `make sample28-no-code-public-runtime-browser-smoke` passed. Push was not performed for #118/#119. / #117 後の続き: #118 では実 server mutation behavior や別 sample の前に browser-side enabled submit payload smoke を選びました。#119 では sample28 public runtime browser smoke に fetch-stub submit probe を追加し、artifact preview は execution skip のまま、current / alias preview では browser 内で generated action 1 件を ready にして `Submit to server` をクリックし、POST URL、same-origin credentials、CSRF、action binding、key、input payload shape を server mutation なしで確認します。`make sample28-no-code-public-runtime-browser-smoke` は通過しました。#118/#119 は push していません。

Latest continuation after #119: #120 chooses an authenticated direct execution endpoint disabled-policy smoke before enabling real generated preview mutation behavior. #121 adds a PHP HTTP smoke that logs in through stub admin auth, reads the current/alias execution binding from the rendered preview, POSTs valid CSRF/project/artifact/action/input data to each `execute.json`, and verifies the request contract passes while the generated disabled action fails closed with 422 JSON. `make sample28-no-code-public-runtime-browser-smoke` passed. Push was not performed for #120/#121. / #119 後の続き: #120 では real generated preview mutation behavior を有効にする前に、authenticated direct execution endpoint disabled-policy smoke を選びました。#121 では PHP HTTP smoke を追加し、stub admin auth でログインし、rendered preview から current / alias execution binding を読み、valid な CSRF / project / artifact / action / input data を各 `execute.json` に POST し、request contract は通る一方で generated disabled action が 422 JSON として fail-closed することを確認します。`make sample28-no-code-public-runtime-browser-smoke` は通過しました。#120/#121 は push していません。

Latest continuation after #121: #122 chooses a principal-aware action policy overlay contract before wiring real endpoint mutation. #123 adds a pure runtime helper that keeps a stored runtime definition intact while overlaying action availability / policy from a separately evaluated principal-aware definition. Focused `NoCodeRuntimeTest` passed in Docker. Push was not performed for #122/#123. / #121 後の続き: #122 では real endpoint mutation を route に接続する前に、principal-aware action policy overlay contract を選びました。#123 では stored runtime definition は保ったまま、別途 principal 付きで評価した definition から action availability / policy だけを重ねる pure runtime helper を追加しました。Docker 上の focused `NoCodeRuntimeTest` は通過しました。#122/#123 は push していません。

Latest continuation after #123: #124 chooses route-level principal action policy overlay wiring before changing sample policy or enabling successful mutation. #125 connects current authenticated principal evaluation into the runtime execution response path, overlays action policy onto the approved stored runtime definition, and keeps the sample28 direct endpoint smoke fail-closed because the current stub admin principal still does not satisfy the sample operation's editor / write-scope policy. Focused `OpenApiSourceOutputContractTest` and `make sample28-no-code-public-runtime-browser-smoke` passed. Push was not performed for #124/#125. / #123 後の続き: #124 では sample policy 変更や successful mutation を有効にする前に、route-level principal action policy overlay wiring を選びました。#125 では current authenticated principal evaluation を runtime execution response path に接続し、approved stored runtime definition へ action policy を overlay します。sample28 direct endpoint smoke は、現在の stub admin principal が sample operation の editor / write-scope policy をまだ満たさないため、引き続き fail-closed です。focused `OpenApiSourceOutputContractTest` と `make sample28-no-code-public-runtime-browser-smoke` は通過しました。#124/#125 は push していません。

Latest continuation after #125: #126 chooses a sample28 successful endpoint tryout before UI result refresh or direct business-row mutation. #127 allows the local stub admin principal to execute the sample28 no-code tryout action, treats public runtime as a valid managed-operation sync origin, and updates the direct endpoint smoke so current/alias POSTs return 200 and enqueue pending managed-operation sync intents. `make sample28-no-code-public-runtime-browser-smoke` passed. Push was not performed for #126/#127. / #125 後の続き: #126 では UI result refresh や business row への直接 mutation の前に、sample28 successful endpoint tryout を選びました。#127 では local stub admin principal が sample28 no-code tryout action を実行できるようにし、public runtime を managed-operation sync origin として許可し、direct endpoint smoke で current / alias POST が 200 を返して pending managed-operation sync intent を enqueue することを確認します。`make sample28-no-code-public-runtime-browser-smoke` は通過しました。#126/#127 は push していません。

Latest continuation after #127: #128 chooses an authenticated browser real-submit smoke before UI result refresh, direct business-row mutation, or outbox processing. #129 extends the no-code runtime Playwright smoke with an `enabled-real-fetch` mode that logs in as the local stub admin, clicks the generated `Submit to server` control, and verifies the real current/alias endpoint response returns 200 with a pending managed-operation sync intent. `make sample28-no-code-public-runtime-browser-smoke` passed. Push was not performed for #128/#129. / #127 後の続き: #128 では UI result refresh、business row への直接 mutation、outbox processing の前に、authenticated browser real-submit smoke を選びました。#129 では no-code runtime Playwright smoke に `enabled-real-fetch` mode を追加し、local stub admin でログインして generated `Submit to server` control をクリックし、real current / alias endpoint response が 200 と pending managed-operation sync intent を返すことを確認します。`make sample28-no-code-public-runtime-browser-smoke` は通過しました。#128/#129 は push していません。

Latest continuation after #129: #130 chooses minimal runtime submit result feedback before outbox processing or direct business-row mutation. #131 updates generated runtime submit success messaging so a real endpoint response that enqueues sync work shows `Sync outbox status: pending` in both the runtime execute status and action feedback. The sample28 real-submit browser smoke now asserts that pending sync status is visible after current/alias submit. `make sample28-no-code-public-runtime-browser-smoke` passed. Push was not performed for #130/#131. / #129 後の続き: #130 では outbox processing や business row への直接 mutation の前に、最小の runtime submit result feedback を選びました。#131 では generated runtime の submit success messaging を更新し、real endpoint response が sync work を enqueue した場合に runtime execute status と action feedback の両方へ `Sync outbox status: pending` を表示します。sample28 real-submit browser smoke は current / alias submit 後に pending sync status が見えることを確認します。`make sample28-no-code-public-runtime-browser-smoke` は通過しました。#130/#131 は push していません。

Latest continuation after #131: #132 chooses a sample28 outbox processing smoke before adding live UI result refresh or changing the public runtime submit endpoint to process synchronously. #133 adds a sample28-specific smoke that processes pending runtime execution outbox items through the existing generated server DBAccess handler against an isolated SQLite row, proving the queued direct endpoint payload can become a `done` sync item and update data through generated DBAccess. `make sample28-no-code-public-runtime-browser-smoke` passed. Push was not performed for #132/#133. / #131 後の続き: #132 では live UI result refresh や public runtime submit endpoint の synchronous processing 化の前に、sample28 outbox processing smoke を選びました。#133 では sample28 専用 smoke を追加し、pending runtime execution outbox item を既存の generated server DBAccess handler で isolated SQLite row に処理します。queued direct endpoint payload が `done` sync item になり、generated DBAccess 経由で data を更新できることを確認します。`make sample28-no-code-public-runtime-browser-smoke` は通過しました。#132/#133 は push していません。

Latest continuation after #133: #134 chooses runtime submit outbox trace feedback before live UI result refresh or synchronous endpoint processing. #135 adds generated runtime feedback and smoke assertions for the accepted outbox item id and operation key, so a tryout user can connect the browser submit result to operator sync outbox inspection. Push was not performed for #134/#135. / #133 後の続き: #134 では live UI result refresh や synchronous endpoint processing の前に、runtime submit outbox trace feedback を選びました。#135 では accepted outbox item id と operation key を generated runtime feedback と smoke assertion に追加し、tryout user が browser submit result を operator sync outbox inspection につなげられるようにしました。#134/#135 は push していません。

Latest continuation after #135: #136 chooses runtime submit operator outbox detail path feedback before live UI result refresh, synchronous endpoint processing, or retry mutation. #137 adds the existing operator sync outbox detail path to generated runtime submit feedback when the endpoint returns a dedupe key, and sample28 smokes verify the path. Push was not performed for #136/#137. / #135 後の続き: #136 では live UI result refresh、synchronous endpoint processing、retry mutation の前に、runtime submit operator outbox detail path feedback を選びました。#137 では endpoint が dedupe key を返す場合に、既存の operator sync outbox detail path を generated runtime submit feedback へ表示し、sample28 smoke でその path を確認します。#136/#137 は push していません。

Latest continuation after #137: #138 chooses operator sync outbox detail processing handoff before live UI result refresh, synchronous endpoint processing, or runtime retry mutation. #139 adds a read-only Processing Handoff section to the sync outbox detail page so pending items reached from runtime submit are explained as queued for the existing processor, while failed items still point to retry eligibility. Push was not performed for #138/#139. / #137 後の続き: #138 では live UI result refresh、synchronous endpoint processing、runtime retry mutation の前に、operator sync outbox detail processing handoff を選びました。#139 では sync outbox detail page に read-only の Processing Handoff section を追加し、runtime submit から辿った pending item が existing processor 待ちであることを説明します。failed item は従来どおり retry eligibility に誘導します。#138/#139 は push していません。

Latest continuation after #139: #140 chooses a closure report before adding live UI result refresh, synchronous endpoint processing, runtime retry mutation, or another sample. #141 closes the server-backed runtime submit/outbox handoff lane: generated runtime submit can POST to the authenticated current/alias endpoint, enqueue pending sync work, show pending status, expose item id / operation / operator detail path, prove processing through generated server DBAccess in sample28 smoke, and explain pending/running/done/failed states on the operator detail page. Push was not performed for #140/#141. / #139 後の続き: #140 では live UI result refresh、synchronous endpoint processing、runtime retry mutation、別 sample の前に closure report を選びました。#141 では server-backed runtime submit / outbox handoff lane を closure します。generated runtime submit は authenticated current / alias endpoint へ POST し、pending sync work を enqueue し、pending status、item id、operation、operator detail path を表示し、sample28 smoke で generated server DBAccess 経由の処理を確認し、operator detail page で pending / running / done / failed state を説明できます。#140/#141 は push していません。

Latest continuation after #141: #142 chooses runtime result follow-up guidance as the smallest next user-facing slice before polling, synchronous processing, retry mutation, another sample, or commit/push cleanup. #143 adds submit success guidance for pending/running outbox work: process the sync outbox item, then refresh the runtime preview to see updated data. Push was not performed for #142/#143. / #141 後の続き: #142 では polling、synchronous processing、retry mutation、別 sample、commit / push cleanup の前に、最小の user-facing slice として runtime result follow-up guidance を選びました。#143 では pending / running の outbox work に対して、sync outbox item を処理してから runtime preview を refresh して更新結果を見る、という submit success guidance を追加しました。#142/#143 は push していません。

Latest continuation after #143: #144 chooses a closure report before adding live polling, anchor/link affordance polish, synchronous processing, runtime retry mutation, another sample, or commit/push cleanup. #145 closes the runtime submit result follow-up guidance lane and records the accepted manual result-check behavior: submit remains outbox-based, and the user is told to process the sync outbox item and refresh the runtime preview. Push was not performed for #144/#145. / #143 後の続き: #144 では live polling、anchor / link affordance polish、synchronous processing、runtime retry mutation、別 sample、commit / push cleanup の前に closure report を選びました。#145 では runtime submit result follow-up guidance lane を closure し、accepted な manual result-check behavior を記録します。submit は outbox-based のまま保ち、user には sync outbox item を処理して runtime preview を refresh するよう案内します。#144/#145 は push していません。

Latest continuation after #145: #146 chooses runtime outbox detail path affordance groundwork before full anchor/link rendering, live polling, synchronous processing, runtime retry mutation, another sample, or commit/push cleanup. #147 exposes the accepted operator sync outbox detail path as `data-runtime-outbox-detail-path` on the runtime execute status and action feedback after successful submit, keeping visible wording unchanged while making later click/copy affordance polish safer. Push was not performed for #146/#147. / #145 後の続き: #146 では full anchor / link rendering、live polling、synchronous processing、runtime retry mutation、別 sample、commit / push cleanup の前に、runtime outbox detail path affordance groundwork を選びました。#147 では submit 成功後の runtime execute status と action feedback に accepted operator sync outbox detail path を `data-runtime-outbox-detail-path` として公開します。表示文言は変えず、後続の click / copy affordance polish を安全にします。#146/#147 は push していません。

Latest continuation after #147: #148 chooses runtime outbox detail path copy affordance before full anchor/link rendering, live polling, synchronous processing, runtime retry mutation, another sample, or commit/push cleanup. #149 adds a disabled-by-default `Copy outbox path` control that becomes available after successful submit when an operator sync outbox detail path exists, copies that path, and resets when local draft/action state changes. Push was not performed for #148/#149. / #147 後の続き: #148 では full anchor / link rendering、live polling、synchronous processing、runtime retry mutation、別 sample、commit / push cleanup の前に、runtime outbox detail path copy affordance を選びました。#149 では disabled-by-default の `Copy outbox path` control を追加し、submit 成功後に operator sync outbox detail path がある場合だけ利用可能にして path を copy できるようにします。local draft / action state が変わると reset します。#148/#149 は push していません。

Latest continuation after #149: #150 chooses a closure report before full link rendering, live polling, synchronous processing, runtime retry mutation, another sample, or commit/push cleanup. #151 closes the runtime outbox detail path copy affordance lane and records the accepted behavior: the runtime keeps submit outbox-based, shows the accepted operator path, and lets the user copy it after successful submit. Push was not performed for #150/#151. / #149 後の続き: #150 では full link rendering、live polling、synchronous processing、runtime retry mutation、別 sample、commit / push cleanup の前に closure report を選びました。#151 では runtime outbox detail path copy affordance lane を closure し、accepted behavior を記録します。runtime は submit を outbox-based のまま保ち、accepted operator path を表示し、submit 成功後に user がその path を copy できます。#150/#151 は push していません。

Latest continuation after #151: #152 chooses local commit stack review before full link rendering, live polling, synchronous processing, runtime retry mutation, another sample, or push cleanup. #153 records the current 43-commit local stack boundary from intent-draft polish through server-backed runtime submit/outbox handoff and result-follow-up affordances. No history rewrite and no push were performed. / #151 後の続き: #152 では full link rendering、live polling、synchronous processing、runtime retry mutation、別 sample、push cleanup の前に local commit stack review を選びました。#153 では intent-draft polish から server-backed runtime submit / outbox handoff と result-follow-up affordance までの、現在の 43 commits local stack boundary を記録しました。history rewrite と push は行っていません。

Latest continuation after #153: #154 chooses runtime outbox detail open link affordance before live polling, synchronous processing, runtime retry mutation, another sample, or push cleanup. #155 adds a hidden-by-default `Open outbox detail` link next to the copy control; after successful submit it points to the accepted operator sync outbox detail path while the copy affordance remains available. Push was not performed for #154/#155. / #153 後の続き: #154 では live polling、synchronous processing、runtime retry mutation、別 sample、push cleanup の前に、runtime outbox detail open link affordance を選びました。#155 では copy control の横に hidden-by-default の `Open outbox detail` link を追加します。submit 成功後は accepted operator sync outbox detail path を指し、copy affordance も維持します。#154/#155 は push していません。

Latest continuation after #155: #156 chooses a manual runtime result refresh button before live polling, synchronous processing, retry mutation, another sample, or push cleanup. #157 adds a disabled-by-default `Refresh preview` control that becomes available after successful submit and preserves current screen form values through reload using browser session storage. Push was not performed for #156/#157. / #155 後の続き: #156 では live polling、synchronous processing、retry mutation、別 sample、push cleanup の前に、manual runtime result refresh button を選びました。#157 では disabled-by-default の `Refresh preview` control を追加し、submit 成功後に利用可能にして、browser session storage により reload 前後の current screen form value を保持します。#156/#157 は push していません。

Latest continuation after #157: #158 chooses runtime result refresh guidance wording before live polling, synchronous processing, retry mutation, another sample, or push cleanup. #159 adds a visible refresh status message and aligns submit success guidance so users see the two-step handoff: process the sync outbox item, then use `Refresh preview` to reload the current screen. Push was not performed for #158/#159. / #157 後の続き: #158 では live polling、synchronous processing、retry mutation、別 sample、push cleanup の前に、runtime result refresh guidance wording を選びました。#159 では visible refresh status message を追加し、submit success guidance と揃えて、sync outbox item を処理してから `Refresh preview` で current screen を reload する、という二段階 handoff を見えるようにしました。#158/#159 は push していません。

Latest continuation after #159: #160 chooses a closure report before live polling, another sample, synchronous demo processing, retry mutation, or push cleanup. #161 closes the manual runtime result refresh lane with accepted capability, latest verification baseline, and next candidates. Push was not performed for #160/#161. / #159 後の続き: #160 では live polling、別 sample、synchronous demo processing、retry mutation、push cleanup の前に closure report を選びました。#161 では manual runtime result refresh lane を closure し、accepted capability、latest verification baseline、next candidates を記録しました。#160/#161 は push していません。

Latest continuation after #161: #162 chooses a local commit stack review before live polling, another sample, synchronous demo processing, retry mutation, or push cleanup. #163 records the current 48-commit local stack boundary, review grouping, latest verification baseline, and no-push/no-rewrite boundary. Push was not performed for #162/#163. / #161 後の続き: #162 では live polling、別 sample、synchronous demo processing、retry mutation、push cleanup の前に local commit stack review を選びました。#163 では現在の 48 commits local stack boundary、review grouping、latest verification baseline、no-push / no-rewrite boundary を記録しました。#162/#163 は push していません。

Latest continuation after #163: #164 chooses a second-sample public runtime submit handoff smoke before live polling, synchronous demo processing, retry mutation, or push cleanup. #165 adds a sample29 public runtime browser smoke target that proves current/alias submit reaches the real endpoint and exposes the same pending outbox copy/open/manual-refresh handoff at the browser level. Push was not performed for #164/#165. / #163 後の続き: #164 では live polling、synchronous demo processing、retry mutation、push cleanup の前に、second-sample public runtime submit handoff smoke を選びました。#165 では sample29 public runtime browser smoke target を追加し、current / alias submit が real endpoint に到達し、同じ pending outbox copy / open / manual-refresh handoff を browser level で確認します。#164/#165 は push していません。

Latest continuation after #165: #166 chooses a closure report before live polling, synchronous demo processing, retry mutation, generic smoke extraction, or push cleanup. #167 closes the second-sample public runtime submit handoff lane and records sample28 full baseline plus sample29 second-domain browser proof as accepted capability. Push was not performed for #166/#167. / #165 後の続き: #166 では live polling、synchronous demo processing、retry mutation、generic smoke extraction、push cleanup の前に closure report を選びました。#167 では second-sample public runtime submit handoff lane を closure し、sample28 full baseline と sample29 second-domain browser proof を accepted capability として記録しました。#166/#167 は push していません。

Latest continuation after #167: #168 chooses a local commit stack review before live polling, synchronous demo processing, retry mutation, generic smoke extraction, or push cleanup. #169 records the current 51-commit local stack boundary after the sample29 second-domain public runtime submit handoff proof. Push was not performed for #168/#169. / #167 後の続き: #168 では live polling、synchronous demo processing、retry mutation、generic smoke extraction、push cleanup の前に local commit stack review を選びました。#169 では sample29 second-domain public runtime submit handoff proof 後の現在の 51 commits local stack boundary を記録しました。#168/#169 は push していません。

Latest continuation after #169: #170 chooses generic multi-profile endpoint smoke extraction before sample29 outbox processing, live polling, synchronous demo processing, retry mutation, or push cleanup. #171 parameterizes the direct runtime execution endpoint smoke for sample28/sample29 and enables it in the sample29 public runtime smoke, proving current/alias direct endpoint enqueue for `update_support_case` while keeping sample29 outbox processing out of scope. Push was not performed for #170/#171. / #169 後の続き: #170 では sample29 outbox processing、live polling、synchronous demo processing、retry mutation、push cleanup の前に generic multi-profile endpoint smoke extraction を選びました。#171 では direct runtime execution endpoint smoke を sample28 / sample29 対応にし、sample29 public runtime smoke で有効化します。これにより `update_support_case` の current / alias direct endpoint enqueue を確認しつつ、sample29 outbox processing は scope 外に保ちます。#170/#171 は push していません。

Latest continuation after #171: #172 chooses sample29 outbox processing smoke before live polling, synchronous demo processing, retry mutation, or push cleanup. #173 parameterizes the runtime outbox processing smoke for sample28/sample29 and enables sample29 outbox processing by default, proving direct endpoint `update_support_case` work reaches the existing server DBAccess processor and updates an isolated SQLite `support_case` row. Push was not performed for #172/#173. / #171 後の続き: #172 では live polling、synchronous demo processing、retry mutation、push cleanup の前に sample29 outbox processing smoke を選びました。#173 では runtime outbox processing smoke を sample28 / sample29 対応にし、sample29 outbox processing を default で有効化します。これにより direct endpoint の `update_support_case` work が既存 server DBAccess processor に届き、isolated SQLite の `support_case` row を更新できることを確認します。#172/#173 は push していません。

Latest continuation after #173: #174 chooses a closure report before live polling, synchronous demo processing, runtime retry mutation, or push cleanup. #175 closes the second-domain runtime submit/processing confidence lane: sample28 and sample29 both prove public runtime browser submit, direct current/alias endpoint enqueue, operator outbox handoff affordances, and generated server DBAccess outbox processing against isolated SQLite rows. Push was not performed for #174/#175. / #173 後の続き: #174 では live polling、synchronous demo processing、runtime retry mutation、push cleanup の前に closure report を選びました。#175 では second-domain runtime submit / processing confidence lane を closure します。sample28 と sample29 はどちらも public runtime browser submit、direct current / alias endpoint enqueue、operator outbox handoff affordance、isolated SQLite row に対する generated server DBAccess outbox processing を確認済みです。#174/#175 は push していません。

Latest continuation after #175: #176 records the pre-push commit cleanup plan after the second-domain runtime confidence closure. #177 rewrites only the local unpushed `develop` stack from 55 small commits into 6 reviewable grouped functional commits after creating backup branch `codex/backup-develop-pre-push-cleanup-20260705-517756b`, then adds one docs-only cleanup record commit. Tree parity with the backup branch was confirmed before the docs-only record; push was not performed. / #175 後の続き: #176 では second-domain runtime confidence closure 後の push 前 commit cleanup plan を記録しました。#177 では backup branch `codex/backup-develop-pre-push-cleanup-20260705-517756b` を作成したうえで、local の未 push `develop` stack だけを 55 個の小 commit から review しやすい 6 grouped functional commits に整理し、その後 docs-only の cleanup record commit を 1 つ追加しました。docs-only record の前に backup branch との tree parity は確認済みです。push はしていません。

Latest continuation after #177: #178 records the next direction after the grouped stack was pushed. The next no-code phase treats all major candidates as necessary, but sequences them as one mainline: #179 practical runtime flow polish first, #180 synchronous demo processing second, #181 product narrative docs third, and #182 next domain/sample expansion fourth. / #177 後の続き: #178 では grouped stack push 後の次方向を記録しました。次の no-code phase では主要候補をすべて必要なものとして扱いますが、主線としては #179 practical runtime flow polish、#180 synchronous demo processing、#181 product narrative docs、#182 next domain / sample expansion の順に進めます。

Latest continuation after #178: #179 adds a practical runtime flow indicator to generated no-code runtime panels. The runtime now groups the submit, outbox tracking, and refresh steps into a visible flow state, and sample28/sample29 public runtime browser smokes verify the accepted flow after real submit. Push was not performed for #179. / #178 後の続き: #179 では generated no-code runtime panel に practical runtime flow indicator を追加しました。runtime は submit、outbox tracking、refresh の各 step を visible flow state としてまとめ、sample28 / sample29 public runtime browser smoke で real submit 後の accepted flow を確認します。#179 は push していません。

Latest continuation after #179: #180 adds a fail-closed synchronous demo processing gate. The production default remains async outbox handoff; demo processing is advertised only when `MTOOL_NO_CODE_RUNTIME_SYNC_DEMO` is truthy and `MTOOL_RUNTIME_SQLITE_PATH` is set, and the endpoint still requires explicit `runtime_demo_process=1` before processing one pending outbox item through the existing server DBAccess processor. Push was not performed for #180. / #179 後の続き: #180 では fail-closed な synchronous demo processing gate を追加しました。production default は async outbox handoff のままです。demo processing は `MTOOL_NO_CODE_RUNTIME_SYNC_DEMO` が truthy かつ `MTOOL_RUNTIME_SQLITE_PATH` が設定されている時だけ runtime binding に表示され、endpoint は explicit な `runtime_demo_process=1` がある場合だけ既存 server DBAccess processor で pending outbox item を 1 件処理します。#180 は push していません。

Latest continuation after #180: #181 updates the permanent product-facing docs so DegoDB keeps its database-first identity while explaining no-code as an upper layer on canonical metadata, generated artifacts, managed operations, Source Output review, and approval records. Push was not performed for #181. / #180 後の続き: #181 では恒久的な product-facing docs を更新し、DegoDB の database-first identity を保ったまま、no-code を canonical metadata、generated artifacts、managed operations、Source Output review、approval records の上に載る upper layer として説明しました。#181 は push していません。

Latest continuation after #181: #182 adds `sample31-no-code-inventory-request-demo` as a third data-first no-code domain sample. It proves generated `NO-CODE-RUNTIME` artifact generation and browser-local runtime behavior for an inventory request domain beyond tickets and support cases. Push was not performed for #182. / #181 後の続き: #182 では 3 つ目の data-first no-code domain sample として `sample31-no-code-inventory-request-demo` を追加しました。ticket / support case 以外の inventory request domain で generated `NO-CODE-RUNTIME` artifact generation と browser-local runtime behavior を確認します。#182 は push していません。

Latest continuation after #182: #183 records the pre-push stack review. The current 5 local commits are already separated into natural review units, so no local squash / history rewrite is recommended. Push was not performed for #183. / #182 後の続き: #183 では push 前 stack review を記録しました。現在の 5 local commits は自然な review unit に分かれているため、local squash / history rewrite は不要と判断しました。#183 は push していません。

Latest continuation after #183: #184 chooses sample31 public runtime submit/processing confidence as the next product-facing lane. #185 adds a sample31 public runtime browser smoke for current/alias submit, direct endpoint enqueue, and generated server DBAccess outbox processing. Push was not performed for #184/#185. / #183 後の続き: #184 では次の product-facing lane として sample31 public runtime submit / processing confidence を選びました。#185 では sample31 public runtime browser smoke を追加し、current / alias submit、direct endpoint enqueue、generated server DBAccess outbox processing を確認します。#184/#185 は push していません。

Latest continuation after #185: #186 closes the third-domain runtime submit/processing confidence lane across sample28, sample29, and sample31. #187 chooses local commit stack review before live polling, runtime retry mutation, visual authoring, a fourth domain sample, or push cleanup. Push was not performed for #186/#187. / #185 後の続き: #186 では sample28、sample29、sample31 にまたがる third-domain runtime submit / processing confidence lane を closure しました。#187 では live polling、runtime retry mutation、visual authoring、4 つ目の domain sample、push cleanup の前に local commit stack review を選びました。#186/#187 は push していません。

Latest continuation after #187: #188 records the local commit stack review after third-domain confidence. The 8 local commits are already separated by product meaning, so no local squash / history rewrite is recommended before an explicit push. Push was not performed for #188. / #187 後の続き: #188 では third-domain confidence 後の local commit stack review を記録しました。8 local commits は product meaning ごとに分かれているため、明示 push 前の local squash / history rewrite は不要と判断しました。#188 は push していません。

Latest continuation after #188: #189 adds a read-only sync outbox status JSON route as the first live-polling boundary. It reuses the existing admin/config and project permission guard, returns status / handoff / retry summary without exposing the stored intent body, and leaves generated runtime auto-polling for the next slice. Push was not performed for #189. / #188 後の続き: #189 では live polling の最初の境界として read-only sync outbox status JSON route を追加しました。既存の admin / config role と project permission guard を再利用し、stored intent body は返さずに status / handoff / retry summary だけを返します。generated runtime auto-polling は次 slice に残しています。#189 は push していません。

Latest continuation after #189: #190 wires generated runtime submit results to the read-only sync outbox status JSON route. The runtime now checks the outbox status after an accepted submit and updates the flow / feedback with pending, running, done, or review-needed state without processing or retrying work inline. Push was not performed for #190. / #189 後の続き: #190 では generated runtime の submit 結果を read-only sync outbox status JSON route へつなぎました。accepted submit 後に outbox status を確認し、pending / running / done / review-needed state を flow / feedback に反映します。inline processing や retry は行いません。#190 は push していません。

Latest continuation after #190: #191 turns the single live outbox status check into bounded polling. Pending/running outbox states are checked up to three times, the displayed message is updated in place, and the runtime stops with a timeout state instead of looping indefinitely. Push was not performed for #191. / #190 後の続き: #191 では single live outbox status check を bounded polling にしました。pending / running の outbox state は最大 3 回まで確認し、画面メッセージは上書き更新し、無限 loop ではなく timeout state で停止します。#191 は push していません。

Latest continuation after #191: #192 adds bounded polling timeout guidance to the generated runtime. When the outbox is still queued or processing after the capped checks, the runtime now says the live check stopped and points users to Refresh preview or the outbox detail instead of leaving the flow in a generic checking state. Push was not performed for #192. / #191 後の続き: #192 では generated runtime に bounded polling timeout guidance を追加しました。上限回数後も outbox が queued / processing の場合、runtime は live check が停止したことを表示し、generic な checking state のままにせず Refresh preview または outbox detail へ誘導します。#192 は push していません。

Latest continuation after #192: #193 adds deterministic browser smoke coverage for the terminal `done` outbox status branch. The smoke keeps the real pending/timeout endpoint path and adds a stubbed status JSON response that proves the generated runtime switches to the complete flow without processing or retrying work inline. Push was not performed for #193. / #192 後の続き: #193 では terminal `done` outbox status branch の deterministic browser smoke coverage を追加しました。real endpoint の pending / timeout 経路は維持しつつ、stubbed status JSON response で generated runtime が inline processing / retry なしに complete flow へ切り替わることを確認します。#193 は push していません。

Latest continuation after #193: #194 adds deterministic browser smoke coverage for the terminal `failed` / `needs_review` outbox status branch. The smoke keeps retry mutation and inline processing out of the runtime, and proves the generated runtime moves to an operator-review flow after one status JSON check. Push was not performed for #194. / #193 後の続き: #194 では terminal `failed` / `needs_review` outbox status branch の deterministic browser smoke coverage を追加しました。retry mutation と inline processing は runtime に入れず、status JSON check 1 回で generated runtime が operator-review flow へ移ることを確認します。#194 は push していません。

Latest continuation after #194: #195 closes the runtime live outbox status polling lane. The accepted boundary is authenticated read-only status JSON, generated runtime submit-time polling, bounded pending/queued timeout guidance, deterministic `done` complete-flow proof, and deterministic `failed` operator-review proof. Push was not performed for #195. / #194 後の続き: #195 では runtime live outbox status polling lane を closure しました。accepted boundary は authenticated read-only status JSON、generated runtime の submit-time polling、pending / queued の bounded timeout guidance、deterministic な `done` complete-flow proof、deterministic な `failed` operator-review proof です。#195 は push していません。

Latest continuation after #195: #196 records the local 16-commit stack review after the live polling closure. The stack is reviewable as-is in four groups: next-phase direction, runtime flow and demo processing, multi-domain confidence, and live polling. No squash, history rewrite, or push was performed. / #195 後の続き: #196 では live polling closure 後の local 16-commit stack review を記録しました。stack は next-phase direction、runtime flow と demo processing、multi-domain confidence、live polling の 4 group としてこのまま review 可能です。squash、history rewrite、push は行っていません。

Latest continuation after #196: #197 verifies that the terminal outbox status branch smoke coverage inherited by the shared sample28 public runtime browser smoke also passes for sample29 and sample31. This confirms the `done` and `failed` / `needs_review` branch proof is multi-profile without adding more sample-specific code. Push was not performed for #197. / #196 後の続き: #197 では shared sample28 public runtime browser smoke を継承する terminal outbox status branch smoke coverage が sample29 と sample31 でも通ることを確認しました。これにより、追加の sample-specific code なしで `done` と `failed` / `needs_review` branch proof が multi-profile であることを確認しています。#197 は push していません。

Latest continuation after #197: #198 chooses a manual result refresh boundary inventory before adding more refresh behavior. The key question is whether `Refresh preview` should simply reload the generated static artifact, request a fresh runtime JSON/data snapshot, or require a regenerate/publish step after processing. Push was not performed for #198. / #197 後の続き: #198 では refresh behavior を追加する前に manual result refresh boundary inventory を選びました。重要な問いは、`Refresh preview` が単に generated static artifact を reload するのか、新しい runtime JSON / data snapshot を取得するのか、processing 後の regenerate / publish step を必要とするのかです。#198 は push していません。

Latest continuation after #198: #199 records the manual result refresh boundary inventory. Current `Refresh preview` preserves form values and reloads the same generated public runtime artifact; it does not fetch fresh DB rows or regenerate/publish a new runtime preview. The recommended next implementation is wording/affordance clarification before any fresh-data endpoint or regenerate/publish workflow. Push was not performed for #199. / #198 後の続き: #199 では manual result refresh boundary inventory を記録しました。現在の `Refresh preview` は form value を保持して同じ generated public runtime artifact を reload します。fresh DB row の取得や runtime preview の regenerate / publish は行いません。推奨される次の実装は、fresh-data endpoint や regenerate / publish workflow の前に wording / affordance clarification です。#199 は push していません。

Latest continuation after #199: #200 clarifies generated runtime refresh wording so `Refresh preview` is presented as reloading the current generated preview artifact, not fetching fresh business data. The submit follow-up now points users to process the outbox item, reload the artifact, or open the outbox detail. Push was not performed for #200. / #199 後の続き: #200 では generated runtime refresh wording を明確化し、`Refresh preview` が fresh business data を取得するものではなく current generated preview artifact を reload するものだと表示します。submit follow-up は outbox item の処理、artifact reload、または outbox detail の確認へ誘導します。#200 は push していません。

Latest continuation after #200: #201 closes the manual result refresh boundary/wording lane. Accepted boundary: refresh preserves form values and reloads the current generated preview artifact; fresh DB data requires a future explicit data endpoint or regenerate/publish/current workflow. Push was not performed for #201. / #200 後の続き: #201 では manual result refresh boundary / wording lane を closure しました。accepted boundary は、refresh が form value を保持して current generated preview artifact を reload すること、fresh DB data は将来の明示的な data endpoint または regenerate / publish / current workflow を必要とすることです。#201 は push していません。

Latest continuation after #201: #202 records the local 22-commit stack review after the refresh boundary closure. The stack is reviewable as-is across next-phase direction, runtime flow/demo processing, multi-domain confidence, live polling, and refresh boundary groups. No squash, history rewrite, or push was performed. / #201 後の続き: #202 では refresh boundary closure 後の local 22-commit stack review を記録しました。stack は next-phase direction、runtime flow / demo processing、multi-domain confidence、live polling、refresh boundary の group としてこのまま review 可能です。squash、history rewrite、push は行っていません。

Latest continuation after #202: #203 chooses a fresh runtime data endpoint boundary inventory before implementing any live data reload behavior. The inventory must settle data source, auth, cache, read-model shape, and stale-artifact messaging boundaries first. Push was not performed for #203. / #202 後の続き: #203 では live data reload behavior を実装する前に fresh runtime data endpoint boundary inventory を選びました。inventory では data source、auth、cache、read-model shape、stale-artifact messaging の境界を先に確定します。#203 は push していません。

| Order | Work unit / 作業の塊 | Commit unit / コミット単位 | Status | Rough effort / 目安 |
| --- | --- | --- | --- | --- |
| 1 | Post-verification-closure no-code product goal replan / verification closure 後の no-code product goal 再計画 | Chose minimal candidate persistence as the smallest code slice after Docker-backed verification passed | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 2 | Post-minimal-candidate-persistence no-code product goal replan / minimal candidate persistence 後の no-code product goal 再計画 | Chose approval transition persistence after durable draft candidate revisions landed | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 3 | Approval transition persistence first slice / approval transition persistence first slice | Added append-only transition events and repository helper for request-review / approve / reject | `DONE` | 1 - 2 days / 1 - 2 日 |
| 4 | Post-approval-transition-persistence no-code product goal replan / approval transition persistence 後の no-code product goal 再計画 | Chose guarded publish candidate detail UI after repository transition persistence landed | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 5 | Guarded publish candidate detail UI first slice / guarded publish candidate detail UI first slice | Added NO-CODE-RUNTIME detail-page candidate creation, candidate history, and guarded transition actions | `DONE` | 1 - 2 days / 1 - 2 日 |
| 6 | Post-guarded-candidate-UI no-code product goal replan / guarded candidate UI 後の no-code product goal 再計画 | Chose approved candidate package exposure after candidate UI controls landed | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 7 | Approved candidate package exposure first slice / approved candidate package exposure first slice | Exposed existing artifact detail/download links only for approved candidates while keeping public URL deferred | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 8 | Post-approved-candidate-package-exposure no-code product goal replan / approved candidate package exposure 後の no-code product goal 再計画 | Chose public runtime preview artifact-key route as the first public delivery continuation | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 9 | Public runtime preview artifact-key route first slice / public runtime preview artifact-key route first slice | Expose approved `NO-CODE-RUNTIME` `runtime-preview.html` through an artifact-key public route without alias semantics | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 10 | Post-public-runtime-preview no-code product goal replan / public runtime preview 後の no-code product goal 再計画 | Chose public runtime current alias route after artifact-key public serving landed | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 11 | Public runtime current alias route first slice / public runtime current alias route first slice | Expose latest approved `NO-CODE-RUNTIME` `runtime-preview.html` through `/runs/no-code/{project_key}/current/runtime-preview.html` | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 12 | Post-current-alias no-code product goal replan / current alias 後の no-code product goal 再計画 | Chose candidate event display polish after current alias landed | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 13 | Candidate event display polish first slice / candidate event display polish first slice | Shows existing publish candidate transition events on the NO-CODE-RUNTIME detail page | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 14 | Post-candidate-event-display no-code product goal replan / candidate event display 後の no-code product goal 再計画 | Chose public runtime cache/version policy after candidate event display landed | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 15 | Public runtime cache/version policy first slice / public runtime cache/version policy first slice | Artifact-key preview uses immutable public caching while current alias keeps no-store | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 16 | Post-cache-version-policy no-code product goal replan / cache/version policy 後の no-code product goal 再計画 | Chose current public revision visibility before explicit rollback/selection storage | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 17 | Current public revision visibility first slice / current public revision visibility first slice | Shows which approved candidate backs the project-level current public runtime preview | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 18 | Post-current-public-revision-visibility no-code product goal replan / current public revision visibility 後の no-code product goal 再計画 | Chose explicit current public revision selection before custom alias storage | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 19 | Explicit current public revision selection first slice / explicit current public revision selection first slice | Stores selected current approved candidate and lets operator/admin set current revision | `DONE` | 1 - 2 days / 1 - 2 日 |
| 20 | Post-explicit-current-public-revision-selection no-code product goal replan / explicit current public revision selection 後の no-code product goal 再計画 | Chose custom public alias key storage before rollback workflow polish | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 21 | Custom public alias key storage first slice / custom public alias key storage first slice | Stores stable public alias keys and serves approved candidates through alias runtime preview routes | `DONE` | 1 - 2 days / 1 - 2 日 |
| 22 | Post-custom-public-alias no-code product goal replan / custom public alias 後の no-code product goal 再計画 | Chose public alias delete workflow before broader rollback polish | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 23 | Public alias delete workflow first slice / public alias delete workflow first slice | Lists configured public aliases and lets operator/admin delete an alias | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 24 | Post-public-alias-delete no-code product goal replan / public alias delete 後の no-code product goal 再計画 | Chose public delivery closure before broader rollback workflow polish | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 25 | Public delivery closure first slice / public delivery closure first slice | Records the completed public runtime delivery capability boundary and remaining follow-up candidates | `DONE` | 0.5 day / 半日 |
| 26 | Post-public-delivery-closure no-code product goal replan / public delivery closure 後の no-code product goal 再計画 | Chose rollback workflow polish before alias lifecycle audit trail or browser smoke | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 27 | Rollback workflow polish first slice / rollback workflow polish first slice | Clarifies current rollback semantics and alias non-follow behavior in the operator/admin UI | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 28 | Post-rollback-workflow-polish no-code product goal replan / rollback workflow polish 後の no-code product goal 再計画 | Chose public delivery browser smoke before alias lifecycle audit trail | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 29 | Public delivery browser smoke first slice / public delivery browser smoke first slice | Verifies artifact-key/current/alias public runtime preview URLs in headless Chrome | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 30 | Post-public-delivery-browser-smoke no-code product goal replan / public delivery browser smoke 後の no-code product goal 再計画 | Chose alias lifecycle audit trail before leaving public delivery | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 31 | Alias lifecycle audit trail first slice / alias lifecycle audit trail first slice | Records alias create/update/delete events and displays recent lifecycle events in operator/admin UI | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 32 | Post-alias-lifecycle-audit-trail no-code product goal replan / alias lifecycle audit trail 後の no-code product goal 再計画 | Chose public delivery hardening closure before leaving this lane | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 33 | Public delivery hardening closure report / public delivery hardening closure report | Records the completed post-hardening public delivery boundary and remaining parked candidates | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 34 | Commit cleanup / review grouping / commit cleanup・review grouping | Organized accumulated public delivery worktree into local commit `e2c5d7e`, still without push | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 35 | Post-public-delivery-commit no-code product goal replan / public delivery commit 後の no-code product goal 再計画 | Chose local app packaging boundary inventory as the next product-facing lane | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 36 | Local app packaging boundary inventory first slice / local app packaging boundary inventory first slice | Defines the first app-local packaging boundary before implementation | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 37 | Post-local-app-packaging-boundary-inventory no-code product goal replan / local app packaging boundary inventory 後の no-code product goal 再計画 | Chose App-local package manifest first slice | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 38 | App-local package manifest first slice / App-local package manifest first slice | Adds `app-local-package-manifest` Source Output strategy and focused manifest coverage | `DONE` | 0.5 - 1.5 days / 半日 - 1.5 日 |
| 39 | Post-App-local-package-manifest no-code product goal replan / App-local package manifest 後の no-code product goal 再計画 | Chose package archive smoke after manifest shape landed | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 40 | App-local package archive smoke first slice / App-local package archive smoke first slice | Verifies generated package archive list/extract behavior and expected manifest files | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 41 | Post-App-local-package-archive-smoke no-code product goal replan / App-local package archive smoke 後の no-code product goal 再計画 | Chose operator readiness display after archive confidence landed | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 42 | App-local package operator readiness display first slice / App-local package operator readiness display first slice | Shows latest package artifact/archive/output-root/manifest/summary readiness and blockers in Source Output detail UI | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 43 | Post-App-local-package-operator-readiness-display no-code product goal replan / App-local package operator readiness display 後の no-code product goal 再計画 | Chose local app packaging closure after readiness display landed | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 44 | Local app packaging closure report / local app packaging closure report | Records the completed local package boundary and remaining parked packaging candidates | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 45 | Post-local-app-packaging-closure no-code product goal replan / local app packaging closure 後の no-code product goal 再計画 | Chose milestone update before starting another implementation lane | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 46 | No-code product milestone update after public delivery and local packaging / public delivery・local packaging 後の no-code product milestone update | Records the current completed no-code milestone, accepted capabilities, parked candidates, and next decision boundary | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 47 | Fresh no-code product-goal replan / fresh no-code product-goal replan | Chose local commit stack review before another implementation lane | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 48 | Local commit stack review after no-code milestone / no-code milestone 後の local commit stack review | Records ahead-count, recent milestone commits, verification baseline, and no-push boundary | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 49 | Fresh priority decision for next product-facing lane / 次の product-facing lane の fresh priority decision | Chose commit stack consolidation plan before another implementation lane | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 50 | No-code commit stack consolidation plan / no-code commit stack consolidation plan | Groups the 50 ahead commits into reviewable meaning units without rewrite or push | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 51 | Explicit next action decision / 次 action の明示判断 | Chose next implementation lane without local history rewrite or push | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 52 | Operator delivery overview first slice / operator delivery overview first slice | Shows public runtime and app-local package readiness together in the no-code Source Outputs inspection card | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 53 | Post-operator-delivery-overview no-code product goal replan / operator delivery overview 後の no-code product goal 再計画 | Chose no-code delivery milestone closure before additional implementation | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 54 | No-code delivery milestone closure report / no-code delivery milestone closure report | Records public delivery, local packaging, and operator delivery overview as the current product-facing milestone boundary | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 55 | Commit cleanup / review grouping after delivery milestone / delivery milestone 後の commit cleanup・review grouping | Grouped the 53 local ahead commits into reviewable meaning units without push or history rewrite | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 56 | Commit cleanup execution decision / commit cleanup execution 判断 | Squashed local ahead stack into grouped commits after creating a backup branch; push was not performed | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 57 | Post-cleanup verification / cleanup 後の verification | Ran final verification after local history cleanup | `DONE` | 0.25 day / 0.25 日 |
| 58 | Post-cleanup next action decision / cleanup 後の次 action 判断 | Chose a Docker-backed no-code user trial after cleanup/push | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 59 | No-code Docker trial scenario first pass / no-code Docker trial scenario first pass | Tried sample28 as a first-time local Docker user and recorded the preview/onboarding path | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 60 | User-facing no-code tryout guide first slice / user-facing no-code tryout guide first slice | Added the short Docker/browser tryout guide and linked it from README/sample28 | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 61 | No-code onboarding polish decision / no-code onboarding polish 判断 | Chose seeded preview data before wording polish or one-click tryout | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 62 | sample28 seeded preview data / sample28 seeded preview data | Added realistic ticket rows so the generated List/Detail/Form preview feels alive | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 63 | preview data smoke / preview data smoke | Verified seeded ticket rows appear in the runtime preview | `DONE` | 0.5 day / 半日 |
| 64 | operator wording polish / operator wording polish | Added first-time operator wording around Source Output, Publish Candidate, and Current Public Revision | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 65 | one-click tryout action design / one-click tryout action design | Designed a sample-scoped shortcut without weakening the normal approval path | `DONE` | 0.5 day / 半日 |
| 66 | one-click tryout implementation / one-click tryout implementation | Added the sample28 demo tryout approval shortcut with candidate/review/approve/current/alias steps | `DONE` | 1 - 2 days / 1 - 2 日 |
| 67 | README/docs information architecture split / README・docs 情報設計の分離整理 | Presented the two-layer value clearly: database-first tooling remains the foundation, and no-code runs as a strong upper layer on canonical metadata/artifacts/approval workflow | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 68 | Local tryout-ready commit regrouping / tryout-ready local commit 整理 | Replaced the unpushed six-commit local stack with two local commits: implementation/tryout readiness and documentation/positioning. No remote history rewrite and no push. | `DONE` | 0.25 day / 0.25 日 |
| 69 | Post-tryout-ready product goal replan / tryout-ready 後の product goal 再計画 | Choose pre-next-push verification before opening another implementation lane. Next product candidates are tryout UX polish 2, runtime real interaction, docs chapter hardening, and next scenario/sample. | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 70 | Pre-next-push verification / 次 push 前 verification | Reran focused sample28 no-code runtime smoke and full `make test` before pushing the #69/#70 planning/verification record | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 71 | Tryout UX polish 2 / tryout UX polish 2 | Added first-time `Tryout Next Steps` guidance on `NO-CODE-RUNTIME`, clarified Web preview vs App-local package delivery tracks, and guarded the wording with focused contract coverage | `DONE` | 0.5 day / 半日 |
| 72 | Runtime action intent draft first slice / runtime action intent draft first slice | Added a local `Action Intent Draft` panel to generated runtime previews so form edits update visible no-code action-intent JSON without executing server updates or bypassing disabled policy guards | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 73 | Runtime action intent draft readiness checks / runtime action intent draft readiness checks | Added `draft_checks` and `policy_failed_checks` to the local intent draft so blocked previews explain disabled action policy and missing required key/input fields | `DONE` | 0.5 day / 半日 |
| 74 | Runtime action intent draft summary / runtime action intent draft summary | Adds a compact summary line above the local intent draft JSON so ready/blocked previews are readable without opening the detailed JSON | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 75 | Runtime action intent policy summary / runtime action intent policy summary | Extends the compact draft summary with policy failed checks such as `principal.missing` so disabled previews show both draft blockers and policy blockers | `DONE` | 0.25 day / 0.25 日 |
| 76 | Runtime action intent draft copy affordance / runtime action intent draft copy affordance | Adds a copy control for the current local intent draft JSON, with visible copy status and smoke coverage that the copied text matches the edited draft | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 77 | Runtime action intent metadata summary / runtime action intent metadata summary | Adds a compact action metadata row to the local intent draft panel so action key, operation key, and operation type are visible without opening JSON | `DONE` | 0.25 day / 0.25 日 |
| 78 | Runtime action intent payload summary / runtime action intent payload summary | Adds a compact payload count row to the local intent draft panel so key/input/filter field counts are visible without opening JSON | `DONE` | 0.25 day / 0.25 日 |
| 79 | Runtime action intent state badge / runtime action intent state badge | Adds a visible state badge to the local intent draft panel so ready/blocked/empty state is visible as a stable UI marker | `DONE` | 0.25 day / 0.25 日 |
| 80 | Post-runtime action intent state badge replan / runtime action intent state badge 後の再計画 | Chose a field summary row as the next small intent-draft readability slice before larger real-interaction work | `DONE` | 0.25 day / 0.25 日 |
| 81 | Runtime action intent field summary / runtime action intent field summary | Adds a compact field summary row to the local intent draft panel so key/input/filter field names are visible without opening JSON | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 82 | Post-runtime action intent field summary replan / runtime action intent field summary 後の再計画 | Chose collapsible draft JSON as the next small readability slice after the summary rows became sufficient for normal scanning | `DONE` | 0.25 day / 0.25 日 |
| 83 | Runtime action intent JSON disclosure / runtime action intent JSON disclosure | Wraps the detailed local draft JSON in a `Draft JSON` disclosure so the panel stays readable while preserving copy and detailed inspection | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 84 | Post-runtime action intent JSON disclosure replan / runtime action intent JSON disclosure 後の再計画 | Chose a closure report before starting real execution, richer validation UI, next scenario/sample, or commit grouping | `DONE` | 0.25 day / 0.25 日 |
| 85 | Runtime action intent draft polish closure / runtime action intent draft polish closure | Records the accepted non-mutating local draft capability and remaining candidates after the readability lane reached a coherent boundary | `DONE` | 0.25 day / 0.25 日 |
| 86 | Post-intent-draft-polish no-code product goal replan / intent draft polish 後の no-code product goal 再計画 | Chose local commit stack review before another product implementation lane | `DONE` | 0.25 day / 0.25 日 |
| 87 | Local commit stack review after intent draft polish / intent draft polish 後の local commit stack review | Records the 10-commit local stack boundary, latest verification baseline, and push/squash options without changing history | `DONE` | 0.25 day / 0.25 日 |
| 88 | Post-commit-stack-review no-code product goal replan / commit stack review 後の no-code product goal 再計画 | Keeps push/history rewrite out of scope and chooses runtime required-field guidance as the next low-risk implementation lane | `DONE` | 0.25 day / 0.25 日 |
| 89 | Runtime required field guidance first slice / runtime required field guidance first slice | Adds inline required badges and hints to generated runtime form fields so required input contract is visible before reading the draft summary or JSON | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 90 | Post-runtime required field guidance replan / runtime required field guidance 後の再計画 | Chose a closure report before starting live validation, server-backed execution, another sample, or commit/push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 91 | Runtime required field guidance closure / runtime required field guidance closure | Records accepted inline required badge/hint capability, latest verification baseline, and remaining candidates after the required guidance slice | `DONE` | 0.25 day / 0.25 日 |
| 92 | Post-required-field-guidance-closure no-code product goal replan / required-field guidance closure 後の no-code product goal 再計画 | Chose runtime required-field live hints as the smallest user-facing continuation before server-backed execution or another sample | `DONE` | 0.25 day / 0.25 日 |
| 93 | Runtime required field live hints first slice / runtime required field live hints first slice | Updates generated required-field hints between present and missing state as form edits change the local action-intent draft checks | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 94 | Post-runtime required field live hints replan / runtime required field live hints 後の再計画 | Chose a closure report before starting server-backed execution, richer validation wording, another sample, or commit/push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 95 | Runtime required field live hints closure / runtime required field live hints closure | Records accepted browser-local present/missing required-field feedback and the latest verification baseline after the live hints slice | `DONE` | 0.25 day / 0.25 日 |
| 96 | Post-required-field-live-hints no-code product goal replan / required-field live hints 後の no-code product goal 再計画 | Chose richer required-field validation wording before server-backed execution, another sample, or commit/push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 97 | Runtime required field validation wording first slice / runtime required field validation wording first slice | Adds action field role and rendered field label to generated runtime required hint present/missing messages | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 98 | Post-required-field-validation-wording no-code product goal replan / required-field validation wording 後の no-code product goal 再計画 | Chose a closure report before server-backed execution, another sample, or commit/push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 99 | Runtime required field validation wording closure / runtime required field validation wording closure | Records accepted action-role/rendered-label required feedback and the latest verification baseline after the wording slice | `DONE` | 0.25 day / 0.25 日 |
| 100 | Post-required-field-validation-wording-closure no-code product goal replan / required-field validation wording closure 後の no-code product goal 再計画 | Chose local commit stack review before larger server-backed execution, another sample, or push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 101 | Local commit stack review after required-field validation wording / required-field validation wording 後の local commit stack review | Records the 17-commit local stack boundary, latest verification baseline, and push/squash options without changing history | `DONE` | 0.25 day / 0.25 日 |
| 102 | Post-local-stack-review no-code product goal replan / local stack review 後の no-code product goal 再計画 | Chose server-backed runtime execution boundary inventory before adding a user-facing mutation path | `DONE` | 0.25 day / 0.25 日 |
| 103 | Server-backed runtime execution boundary inventory / server-backed runtime execution boundary inventory | Records the existing backend dispatch capability and the remaining product/security boundaries before generated runtime preview can execute mutations | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 104 | Post-runtime-execution-boundary no-code product goal replan / runtime execution boundary 後の no-code product goal 再計画 | Chose request contract hardening before adding a public mutation endpoint | `DONE` | 0.25 day / 0.25 日 |
| 105 | Runtime execution request contract first slice / runtime execution request contract first slice | Adds a fail-closed POST request contract helper for action key, input, CSRF, project binding, and runtime artifact binding | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 106 | Post-runtime-execution-request-contract no-code product goal replan / runtime execution request contract 後の no-code product goal 再計画 | Chose a server-backed dispatch entrypoint helper before public mutation routing or generated preview submission wiring | `DONE` | 0.25 day / 0.25 日 |
| 107 | Runtime execution dispatch entrypoint first slice / runtime execution dispatch entrypoint first slice | Combines the POST request contract with existing dispatch so invalid requests fail before dispatcher invocation and valid requests return request/intent/result shape | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 108 | Post-runtime-execution-dispatch-entrypoint no-code product goal replan / runtime execution dispatch entrypoint 後の no-code product goal 再計画 | Chose endpoint response contract before public mutation route wiring or generated preview submission wiring | `DONE` | 0.25 day / 0.25 日 |
| 109 | Runtime execution endpoint response contract first slice / runtime execution endpoint response contract first slice | Maps execution results to endpoint-ready HTTP status and JSON payload shape without sending headers or registering a public route | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 110 | Post-runtime-execution-endpoint-response no-code product goal replan / endpoint response 後の no-code product goal 再計画 | Chose an authenticated artifact-key execution route before current/alias route variants or generated preview submission wiring | `DONE` | 0.25 day / 0.25 日 |
| 111 | Runtime execution artifact route first slice / runtime execution artifact route first slice | Registers the artifact-key execution JSON route, requires auth for mutation, loads the approved candidate screen definition, and dispatches through the existing managed-operation outbox path | `DONE` | 0.5 day / 半日 |
| 112 | Post-runtime-execution-artifact-route no-code product goal replan / artifact route 後の no-code product goal 再計画 | Chose authenticated current/alias execution route variants before generated preview submission wiring | `DONE` | 0.25 day / 0.25 日 |
| 113 | Runtime execution current/alias routes first slice / runtime execution current/alias routes first slice | Registers authenticated current and custom-alias execution JSON routes that resolve the same approved candidates as the public preview URLs | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 114 | Post-runtime-execution-current-alias-routes no-code product goal replan / current-alias routes 後の no-code product goal 再計画 | Chose generated preview submission wiring after artifact/current/alias execution routes were addressable | `DONE` | 0.25 day / 0.25 日 |
| 115 | Runtime execution preview submit wiring first slice / runtime execution preview submit wiring first slice | Adds a generated `Submit to server` control and current/alias execution binding injection while keeping immutable artifact previews static and blocked drafts fail-closed | `DONE` | 0.5 day / 半日 |
| 116 | Post-preview-submit-wiring no-code product goal replan / preview submit wiring 後の no-code product goal 再計画 | Chose public runtime execution binding smoke hardening before enabled-action execution or another sample | `DONE` | 0.25 day / 0.25 日 |
| 117 | Public runtime execution binding smoke hardening / public runtime execution binding smoke hardening | Makes public runtime browser smoke assert artifact previews have no execution binding while current/alias previews expose the matching execution endpoint binding | `DONE` | 0.25 day / 0.25 日 |
| 118 | Post-execution-binding-smoke-hardening no-code product goal replan / execution binding smoke hardening 後の no-code product goal 再計画 | Chose browser-side enabled submit payload probing before real server mutation behavior or another sample | `DONE` | 0.25 day / 0.25 日 |
| 119 | Runtime preview enabled submit payload smoke / runtime preview enabled submit payload smoke | Adds a fetch-stub browser probe that forces a generated action ready, clicks `Submit to server`, and verifies POST URL, credentials, CSRF/action binding, key, and input payload shape without mutating the server | `DONE` | 0.25 day / 0.25 日 |
| 120 | Post-enabled-submit-payload-smoke no-code product goal replan / enabled submit payload smoke 後の no-code product goal 再計画 | Chose authenticated direct endpoint disabled-policy smoke before enabling real generated preview mutation behavior | `DONE` | 0.25 day / 0.25 日 |
| 121 | Runtime execution endpoint disabled-policy smoke / runtime execution endpoint disabled-policy smoke | Adds an HTTP smoke that logs in, POSTs valid current/alias execution requests, and verifies request binding passes while generated disabled actions fail closed with 422 JSON | `DONE` | 0.25 day / 0.25 日 |
| 122 | Post-endpoint-disabled-policy-smoke no-code product goal replan / endpoint disabled-policy smoke 後の no-code product goal 再計画 | Chose principal-aware action policy overlay contract before wiring real endpoint mutation behavior | `DONE` | 0.25 day / 0.25 日 |
| 123 | Runtime principal action policy overlay contract / runtime principal action policy overlay contract | Adds a pure helper that overlays action availability and policy from a principal-aware definition onto a stored runtime definition without changing artifact structure or data | `DONE` | 0.25 day / 0.25 日 |
| 124 | Post-principal-policy-overlay-contract no-code product goal replan / principal policy overlay contract 後の no-code product goal 再計画 | Chose route-level principal action policy overlay wiring before changing sample policy or enabling successful mutation | `DONE` | 0.25 day / 0.25 日 |
| 125 | Runtime execution route principal policy overlay wiring / runtime execution route principal policy overlay wiring | Connects authenticated principal policy evaluation into execution response generation, overlaying action policy onto the approved stored runtime definition before dispatch | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 126 | Post-route-principal-policy-overlay no-code product goal replan / route principal policy overlay 後の no-code product goal 再計画 | Chose sample28 successful endpoint tryout before UI result refresh or direct business-row mutation | `DONE` | 0.25 day / 0.25 日 |
| 127 | Sample28 runtime execution success smoke / sample28 runtime execution success smoke | Allows local stub admin to execute the sample28 no-code tryout action and verifies current/alias endpoint POSTs enqueue pending managed-operation sync intents | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 128 | Post-sample28-endpoint-success no-code product goal replan / sample28 endpoint success 後の no-code product goal 再計画 | Chose authenticated browser real-submit smoke before UI result refresh, direct business-row mutation, or outbox processing | `DONE` | 0.25 day / 0.25 日 |
| 129 | Sample28 authenticated browser real-submit smoke / sample28 authenticated browser real-submit smoke | Makes the Playwright tryout smoke log in and verify the generated submit control reaches the real current/alias endpoint and receives a pending sync intent response | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 130 | Post-browser-real-submit no-code product goal replan / browser real-submit 後の no-code product goal 再計画 | Chose minimal runtime submit result feedback before outbox processing or direct business-row mutation | `DONE` | 0.25 day / 0.25 日 |
| 131 | Runtime submit sync-status feedback / runtime submit sync-status feedback | Shows pending sync outbox status in generated runtime submit success feedback and verifies it through sample28 current/alias real-submit smoke | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 132 | Post-runtime-submit-sync-status-feedback no-code product goal replan / runtime submit sync-status feedback 後の no-code product goal 再計画 | Chose sample28 outbox processing smoke before live UI result refresh or synchronous endpoint processing | `DONE` | 0.25 day / 0.25 日 |
| 133 | Sample28 runtime outbox processing smoke / sample28 runtime outbox processing smoke | Processes pending sample28 runtime execution outbox items through generated server DBAccess against isolated SQLite and verifies the row update | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 134 | Post-outbox-processing-smoke no-code product goal replan / outbox processing smoke 後の no-code product goal 再計画 | Chose runtime submit outbox trace feedback before live UI result refresh or synchronous endpoint processing | `DONE` | 0.25 day / 0.25 日 |
| 135 | Runtime submit outbox trace feedback / runtime submit outbox trace feedback | Shows accepted sync outbox item id and operation key in generated runtime submit feedback and verifies it through sample28 real-submit smokes | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 136 | Post-runtime-submit-outbox-trace no-code product goal replan / runtime submit outbox trace 後の no-code product goal 再計画 | Chose operator outbox detail path feedback before live UI result refresh, synchronous endpoint processing, or retry mutation | `DONE` | 0.25 day / 0.25 日 |
| 137 | Runtime submit operator outbox detail path feedback / runtime submit operator outbox detail path feedback | Shows the existing operator sync outbox detail path in generated runtime submit feedback when a dedupe key is returned | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 138 | Post-runtime-submit-detail-path no-code product goal replan / runtime submit detail path 後の no-code product goal 再計画 | Chose operator sync outbox detail processing handoff before live UI result refresh, synchronous endpoint processing, or runtime retry mutation | `DONE` | 0.25 day / 0.25 日 |
| 139 | Operator sync outbox detail processing handoff / operator sync outbox detail processing handoff | Adds a read-only Processing Handoff section that explains pending/running/done/failed outbox states and keeps inline processing out of the detail page | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 140 | Post-operator-processing-handoff no-code product goal replan / operator processing handoff 後の no-code product goal 再計画 | Chose a lane closure report before live UI result refresh, synchronous endpoint processing, runtime retry mutation, or another sample | `DONE` | 0.25 day / 0.25 日 |
| 141 | Runtime submit/outbox handoff closure / runtime submit・outbox handoff closure | Closes the server-backed runtime submit handoff lane with accepted capabilities, verification baseline, and remaining candidates | `DONE` | 0.25 day / 0.25 日 |
| 142 | Post-runtime-submit/outbox-handoff-closure no-code product goal replan / runtime submit・outbox handoff closure 後の no-code product goal 再計画 | Chose runtime result follow-up guidance before polling, synchronous processing, retry mutation, another sample, or commit/push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 143 | Runtime submit result follow-up guidance / runtime submit result follow-up guidance | Adds pending/running submit guidance that tells the tryout user to process the sync outbox item and refresh the runtime preview for updated data | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 144 | Post-runtime-submit-result-follow-up no-code product goal replan / runtime submit result follow-up 後の no-code product goal 再計画 | Chose a closure report before polling, anchor/link affordance polish, synchronous processing, retry mutation, another sample, or commit/push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 145 | Runtime submit result follow-up closure / runtime submit result follow-up closure | Closes the manual result-check guidance lane with accepted behavior, verification baseline, and next candidates | `DONE` | 0.25 day / 0.25 日 |
| 146 | Post-result-follow-up-closure no-code product goal replan / result follow-up closure 後の no-code product goal 再計画 | Chose runtime outbox detail path affordance groundwork before full anchor/link rendering, live polling, synchronous processing, retry mutation, another sample, or commit/push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 147 | Runtime outbox detail path affordance groundwork / runtime outbox detail path affordance groundwork | Exposes the accepted operator sync outbox detail path as a data attribute on submit success status/feedback while keeping visible wording unchanged | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 148 | Post-outbox-detail-path-groundwork no-code product goal replan / outbox detail path groundwork 後の no-code product goal 再計画 | Chose runtime outbox detail path copy affordance before full link rendering, live polling, synchronous processing, retry mutation, another sample, or commit/push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 149 | Runtime outbox detail path copy affordance / runtime outbox detail path copy affordance | Adds a submit-success-only copy control for the accepted operator sync outbox detail path and verifies it through sample28 browser smoke | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 150 | Post-runtime-outbox-path-copy no-code product goal replan / runtime outbox path copy 後の no-code product goal 再計画 | Chose a closure report before full link rendering, live polling, synchronous processing, retry mutation, another sample, or commit/push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 151 | Runtime outbox detail path copy closure / runtime outbox detail path copy closure | Closes the copy affordance lane with accepted behavior, verification baseline, and next candidates | `DONE` | 0.25 day / 0.25 日 |
| 152 | Post-outbox-path-copy-closure no-code product goal replan / outbox path copy closure 後の no-code product goal 再計画 | Chose local commit stack review before full link rendering, live polling, synchronous processing, retry mutation, another sample, or push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 153 | Local commit stack review after runtime submit affordances / runtime submit affordance 後の local commit stack review | Records the 43-commit local stack boundary, review grouping, verification baseline, and no-push/no-rewrite boundary | `DONE` | 0.25 day / 0.25 日 |
| 154 | Post-local-stack-review no-code product goal replan / local stack review 後の no-code product goal 再計画 | Chose runtime outbox detail open link affordance before live polling, synchronous processing, retry mutation, another sample, or push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 155 | Runtime outbox detail open link affordance / runtime outbox detail open link affordance | Adds a submit-success-only `Open outbox detail` link using the accepted operator sync outbox detail path | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 156 | Post-outbox-detail-open-link no-code product goal replan / outbox detail open link 後の no-code product goal 再計画 | Chose manual runtime result refresh button before live polling, synchronous processing, retry mutation, another sample, or push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 157 | Runtime result refresh button first slice / runtime result refresh button first slice | Adds a submit-success-only `Refresh preview` control that preserves current screen form values through reload | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 158 | Post-result-refresh-button no-code product goal replan / result refresh button 後の no-code product goal 再計画 | Chose result refresh guidance wording before live polling, synchronous processing, retry mutation, another sample, or push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 159 | Runtime result refresh guidance wording first slice / runtime result refresh guidance wording first slice | Adds visible refresh status wording and aligns submit success guidance around the process-outbox-then-refresh handoff | `DONE` | 0.25 day / 0.25 日 |
| 160 | Post-result-refresh-guidance no-code product goal replan / result refresh guidance 後の no-code product goal 再計画 | Chose a closure report before live polling, another sample, synchronous demo processing, retry mutation, or push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 161 | Runtime manual result refresh closure / runtime manual result refresh closure | Closes the manual refresh lane with accepted capability, latest verification baseline, and next candidates | `DONE` | 0.25 day / 0.25 日 |
| 162 | Post-manual-refresh-closure no-code product goal replan / manual refresh closure 後の no-code product goal 再計画 | Chose local commit stack review before live polling, another sample, synchronous demo processing, retry mutation, or push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 163 | Local commit stack review after manual refresh / manual refresh 後の local commit stack review | Records the 48-commit local stack boundary, review grouping, latest verification baseline, and no-push/no-rewrite boundary | `DONE` | 0.25 day / 0.25 日 |
| 164 | Post-stack-review no-code product goal replan / stack review 後の no-code product goal 再計画 | Chose sample29 public runtime submit handoff smoke before live polling, synchronous demo processing, retry mutation, or push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 165 | Sample29 public runtime submit handoff smoke / sample29 public runtime submit handoff smoke | Adds a sample29 public runtime browser smoke for current/alias real-submit outbox copy/open/manual-refresh handoff | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 166 | Post-sample29 public runtime submit handoff replan / sample29 public runtime submit handoff 後の再計画 | Chose a closure report before live polling, synchronous demo processing, retry mutation, generic smoke extraction, or push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 167 | Sample29 public runtime submit handoff closure / sample29 public runtime submit handoff closure | Closes the second-sample submit handoff lane with accepted sample28/sample29 capability and next candidates | `DONE` | 0.25 day / 0.25 日 |
| 168 | Post-sample29-handoff-closure no-code product goal replan / sample29 handoff closure 後の no-code product goal 再計画 | Chose local commit stack review before live polling, synchronous demo processing, retry mutation, generic smoke extraction, or push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 169 | Local commit stack review after sample29 handoff / sample29 handoff 後の local commit stack review | Records the 51-commit local stack boundary, review grouping, latest verification baseline, and no-push/no-rewrite boundary | `DONE` | 0.25 day / 0.25 日 |
| 170 | Post-sample29 stack review no-code product goal replan / sample29 stack review 後の no-code product goal 再計画 | Chose generic multi-profile endpoint smoke extraction before sample29 outbox processing or larger runtime behavior changes | `DONE` | 0.25 day / 0.25 日 |
| 171 | Multi-profile runtime execution endpoint smoke / multi-profile runtime execution endpoint smoke | Parameterizes the direct endpoint smoke for sample28/sample29 and enables sample29 current/alias enqueue proof for `update_support_case` | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 172 | Post-multi-profile endpoint smoke no-code product goal replan / multi-profile endpoint smoke 後の no-code product goal 再計画 | Chose sample29 outbox processing smoke before larger runtime behavior changes | `DONE` | 0.25 day / 0.25 日 |
| 173 | Sample29 runtime outbox processing smoke / sample29 runtime outbox processing smoke | Parameterizes the outbox processing smoke for sample28/sample29 and proves sample29 direct endpoint payload processing through generated server DBAccess | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 174 | Post-sample29 processing no-code product goal replan / sample29 processing 後の no-code product goal 再計画 | Chose a closure report before live polling, synchronous demo processing, runtime retry mutation, or push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 175 | Second-domain runtime submit/processing confidence closure / second-domain runtime submit・processing confidence closure | Closes sample28/sample29 submit, endpoint enqueue, handoff affordance, and outbox processing proof as the current async runtime confidence boundary | `DONE` | 0.25 day / 0.25 日 |
| 176 | Pre-push commit cleanup plan / push 前 commit cleanup plan | Plans local-only regrouping after second-domain runtime confidence closure, with backup branch and no push | `DONE` | 0.25 day / 0.25 日 |
| 177 | Pre-push grouped commit cleanup execution / push 前 grouped commit cleanup 実行 | Rewrites the local unpushed functional stack into 6 grouped commits, confirms tree parity with the pre-cleanup backup branch, then adds a docs-only cleanup record | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 178 | Post-push next no-code direction replan / push 後の次 no-code 方向性再計画 | Promotes the remaining major candidates into a sequenced mainline instead of choosing only one; detailed phase plan is recorded in [2026-0705 no-code next phase detailed plan](reports/2026/2026-0705-no-code-next-phase-detailed-plan.md) | `DONE` | 0.25 day / 0.25 日 |
| 179 | No-code practical runtime flow polish / no-code 実利用 runtime flow polish | Adds a visible Submit / Track / Refresh runtime flow indicator and verifies accepted flow for sample28/sample29 real-submit smokes | `DONE` | 0.5 - 1.5 days / 半日 - 1.5 日 |
| 180 | Synchronous demo processing first slice / synchronous demo processing first slice | Adds a fail-closed demo gate where submit can request one immediate outbox process only when env/SQLite binding and explicit POST flag are present, while preserving async outbox as the production foundation | `DONE` | 1 - 2 days / 1 - 2 日 |
| 181 | Database-first plus no-code product narrative docs / database-first + no-code product narrative docs | Updates README/docs to explain that no-code sits on the database-first metadata, artifact, approval, and operation foundation | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 182 | Next domain/sample expansion / 次 domain・sample 展開 | Adds sample31 inventory request as a third data-first no-code sample and verifies generated runtime artifact plus browser-local runtime smoke | `DONE` | 2 - 5 days / 2 - 5 日 |
| 183 | Pre-push stack review after sample31 / sample31 後の push 前 stack review | Confirms the 5 local commits are already reviewable as-is; no squash/history rewrite and no push | `DONE` | 0.25 day / 0.25 日 |
| 184 | Post-sample31 stack review no-code product goal replan / sample31 stack review 後の no-code product goal 再計画 | Chose sample31 public runtime submit/processing confidence before live polling, retry mutation, or another domain | `DONE` | 0.25 day / 0.25 日 |
| 185 | Sample31 public runtime submit/processing smoke / sample31 public runtime submit・processing smoke | Adds sample31 current/alias public runtime submit, direct endpoint enqueue, and generated server DBAccess outbox processing smoke | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 186 | Third-domain runtime submit/processing confidence closure / third-domain runtime submit・processing confidence closure | Closes sample28/sample29/sample31 public runtime submit, endpoint enqueue, and generated server DBAccess processing as the current async runtime confidence boundary | `DONE` | 0.25 day / 0.25 日 |
| 187 | Post-third-domain confidence no-code product goal replan / third-domain confidence 後の no-code product goal 再計画 | Chose local commit stack review before live polling, retry mutation, visual authoring, another domain, or push cleanup | `DONE` | 0.25 day / 0.25 日 |
| 188 | Local commit stack review after third-domain confidence / third-domain confidence 後の local commit stack review | Records the 8-commit local stack boundary and confirms no squash/history rewrite is recommended before explicit push | `DONE` | 0.25 day / 0.25 日 |
| 189 | Sync outbox status JSON for live polling / live polling 用 sync outbox status JSON | Adds an authenticated read-only status endpoint for sync outbox tracking without exposing intent payloads or processing inline | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 190 | Runtime submit live outbox status UI / runtime submit live outbox status UI | Polls the read-only status JSON after accepted submit and updates runtime flow/feedback without inline processing or retry | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 191 | Runtime live outbox bounded polling / runtime live outbox bounded polling | Repeats pending/running status checks up to three times, updates status text in place, and stops without an infinite loop | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 192 | Runtime bounded polling timeout guidance / runtime bounded polling timeout guidance | Shows a clear timeout message and refresh/detail next step when queued/processing status remains after capped checks | `DONE` | 0.25 day / 0.25 日 |
| 193 | Runtime terminal done status branch smoke / runtime terminal done status branch smoke | Adds deterministic browser smoke coverage for the status JSON `done` branch and complete runtime flow | `DONE` | 0.25 day / 0.25 日 |
| 194 | Runtime terminal failed status branch smoke / runtime terminal failed status branch smoke | Adds deterministic browser smoke coverage for the status JSON `failed` / `needs_review` branch and operator-review runtime flow | `DONE` | 0.25 day / 0.25 日 |
| 195 | Runtime live outbox status polling closure / runtime live outbox status polling closure | Records the accepted live polling boundary, verification baseline, and remaining next candidates | `DONE` | 0.25 day / 0.25 日 |
| 196 | Local commit stack review after live polling / live polling 後の local commit stack review | Records the 16-commit local stack boundary, grouping, verification baseline, and no-push/no-rewrite recommendation | `DONE` | 0.25 day / 0.25 日 |
| 197 | Multi-profile terminal status branch verification / multi-profile terminal status branch verification | Verifies sample29 and sample31 public runtime browser smokes inherit the terminal `done` and `failed` / `needs_review` branch checks | `DONE` | 0.25 day / 0.25 日 |
| 198 | Post-live-polling refresh boundary replan / live polling 後の refresh boundary 再計画 | Chooses manual result refresh boundary inventory before adding refresh behavior or stronger visual reload proof | `DONE` | 0.25 day / 0.25 日 |
| 199 | Manual result refresh boundary inventory / manual result refresh boundary inventory | Records current refresh mechanics, artifact/data boundaries, and recommends wording/affordance clarification before fresh-data behavior | `DONE` | 0.25 day / 0.25 日 |
| 200 | Runtime refresh artifact wording clarification / runtime refresh artifact wording clarification | Clarifies refresh copy so reload means current generated preview artifact reload, not fresh business-data fetch | `DONE` | 0.25 day / 0.25 日 |
| 201 | Manual result refresh boundary closure / manual result refresh boundary closure | Closes the refresh boundary/wording lane and records accepted behavior plus next candidates | `DONE` | 0.25 day / 0.25 日 |
| 202 | Local commit stack review after refresh boundary / refresh boundary 後の local commit stack review | Records the 22-commit local stack boundary, grouping, latest verification baseline, and no-push/no-rewrite recommendation | `DONE` | 0.25 day / 0.25 日 |
| 203 | Fresh runtime data endpoint boundary replan / fresh runtime data endpoint boundary 再計画 | Chooses a boundary inventory before implementing live data reload behavior | `DONE` | 0.25 day / 0.25 日 |
| 204 | Fresh runtime data endpoint boundary inventory / fresh runtime data endpoint boundary inventory | Records route, cache, auth, data-source, response-shape, and non-goal boundaries for future live data reload behavior | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 205 | Fresh runtime data endpoint contract plan / fresh runtime data endpoint contract plan | Fixes the read-only `runtime-data.json` response contract, route scope, failure semantics, and smoke matrix before implementation | `DONE` | 0.25 day / 0.25 日 |
| 206 | Fresh runtime data endpoint route contract first slice / fresh runtime data endpoint route contract first slice | Adds authenticated current/alias `runtime-data.json` routes, versioned JSON contract, generated DBAccess read attempt, fail-closed response, and sample28 public smoke coverage | `DONE` | 0.5 day / 0.5 日 |
| 207 | Runtime data web DB binding replan / runtime data web DB binding 再計画 | Chooses a deterministic web-request runtime DB binding plan before upgrading `runtime-data.json` from fail-closed to successful live row reads | `DONE` | 0.25 day / 0.25 日 |
| 208 | Sample28 runtime data live row read / sample28 runtime data live row read | Binds generated DBAccess runtime env for public web requests so sample28 current/alias `runtime-data.json` returns live seeded rows instead of fail-closed DB connection errors | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 209 | Multi-profile runtime data live row smoke / multi-profile runtime data live row smoke | Verifies sample29 and sample31 current/alias `runtime-data.json` return live seeded rows through the same generated DBAccess web binding while submit/outbox processing remains passing | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 210 | Fresh runtime data endpoint first milestone closure / fresh runtime data endpoint first milestone closure | Closes the first fresh runtime data endpoint milestone with accepted boundary, verification baseline, and remaining next candidates | `DONE` | 0.25 day / 0.25 日 |
| 211 | Post-fresh-runtime-data endpoint replan / fresh runtime data endpoint 後の再計画 | Chooses current/alias UI consumption of `runtime-data.json` as the next small product-facing slice, using injected public runtime binding rather than changing immutable artifacts | `DONE` | 0.25 day / 0.25 日 |
| 212 | Runtime data refresh UI consumption first slice / runtime data refresh UI consumption first slice | Injects current/alias `runtime_data_url` binding and lets Refresh fetch `runtime-data.json`, merge live data into existing generated screen metadata, and render live rows in public runtime previews | `DONE` | 0.5 - 1 day / 0.5 - 1 日 |
| 213 | Runtime data refresh key preservation / runtime data refresh key preservation | Preserves action key values as hidden controls when live `runtime-data.json` refresh re-renders form bodies whose generated action key is not a visible field | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 214 | Runtime data terminal done auto refresh / runtime data terminal done auto refresh | Automatically fetches current/alias `runtime-data.json` when live outbox polling reaches terminal `done`, while leaving pending timeout, failed review, and artifact-key preview behavior unchanged | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 215 | Runtime data refresh wording polish / runtime data refresh wording polish | Clarifies Refresh copy so artifact-key previews stay static while current/alias previews fetch read-only live runtime data, and makes runtime-data failure copy explicitly non-mutating | `DONE` | 0.25 day / 0.25 日 |
| 216 | Runtime data post-processing proof / runtime data post-processing proof | Extends the outbox processing smoke so the same processed runtime DB row is also visible through generated runtime-data list/detail/form screens | `DONE` | 0.25 day / 0.25 日 |
| 217 | Multi-profile runtime data post-processing proof / multi-profile runtime data post-processing proof | Verifies sample29 and sample31 public runtime smokes inherit the processed-row runtime-data list/detail/form proof through the shared outbox processing smoke | `DONE` | 0.25 day / 0.25 日 |
| 218 | Runtime data read-model shape boundary inventory / runtime data read-model shape boundary inventory | Records the current `runtime-data.json` shape and chooses additive screen metadata before query-driven pagination/filter/detail selection or form-default behavior | `DONE` | 0.25 day / 0.25 日 |
| 219 | Runtime data screen metadata first slice / runtime data screen metadata first slice | Adds additive `runtime-data.json` screen metadata for row count, selected key, and live-read freshness, with endpoint and post-processing smoke assertions | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 220 | Multi-profile runtime data metadata verification / multi-profile runtime data metadata verification | Verifies sample29 and sample31 public runtime smokes expose and assert runtime-data row-count and selected-key metadata for current/alias and post-processing proof paths | `DONE` | 0.25 day / 0.25 日 |
| 221 | Runtime data selected-key query first slice / runtime data selected-key query first slice | Adds optional `selected_key` query support for current/alias `runtime-data.json` so detail/form screens can render a selected row while list rows remain intact and missing keys fail closed | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 222 | Multi-profile runtime data selected-key verification / multi-profile runtime data selected-key verification | Verifies sample29 and sample31 public runtime smokes inherit selected-key query handling, query echo, and missing-key fail-closed behavior | `DONE` | 0.25 day / 0.25 日 |
| 223 | Runtime data list row selection first slice / runtime data list row selection first slice | Adds current/alias list-row Select affordance that fetches `runtime-data.json?selected_key=...` and refreshes detail/form from the selected row while artifact-key previews remain static | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 224 | Multi-profile runtime data row selection fixtures / multi-profile runtime data row selection fixtures | Adds second seeded rows to sample29/sample31 and verifies row-selection UI smoke selects non-first rows across the other product-facing profiles | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 225 | Runtime data row selection milestone closure / runtime data row selection milestone closure | Closes the first current/alias live data row-selection lane and records accepted capability plus next read-model candidates | `DONE` | 0.25 day / 0.25 日 |
| 226 | Runtime data pagination contract plan / runtime data pagination contract plan | Fixes the first pagination/page-size boundary before implementation, keeping detail/form selected-row semantics separate from list slicing | `DONE` | 0.25 day / 0.25 日 |
| 227 | Runtime data pagination endpoint first slice / runtime data pagination endpoint first slice | Adds optional current/alias `runtime-data.json` `page` and `page_size` query handling, list-only slicing, pagination metadata, and fail-closed invalid-input smoke coverage | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 228 | Runtime data browser pagination controls plan / runtime data browser pagination controls plan | Fixes the first browser control boundary for paginated live runtime data without changing no-query full-list behavior | `DONE` | 0.25 day / 0.25 日 |
| 229 | Runtime data browser pagination controls first slice / runtime data browser pagination controls first slice | Adds current/alias live runtime data page-size entry plus Previous/Next controls from pagination metadata while preserving no-query Refresh and selected-key row selection | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 230 | Post-pagination-controls no-code product goal replan / pagination controls 後の no-code product goal 再計画 | Chose compact pagination total-count visibility before arbitrary page-size input, filter/search, or larger navigation controls | `DONE` | 0.25 day / 0.25 日 |
| 231 | Runtime data pagination total-count label / runtime data pagination total-count label | Shows `total_rows` in the generated runtime pagination controls and verifies the label/DOM attribute against returned pagination metadata | `DONE` | 0.25 day / 0.25 日 |
| 232 | Post-pagination-total-count no-code product goal replan / pagination total-count 後の no-code product goal 再計画 | Chose user-entered page-size controls before filter/search or direct page navigation because endpoint `page_size` support already exists | `DONE` | 0.25 day / 0.25 日 |
| 233 | Runtime data page-size input first slice / runtime data page-size input first slice | Replaces the fixed page-size entry with a bounded numeric page-size input and Apply control while keeping Previous/Next metadata-driven | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 234 | Post-page-size-input no-code product goal replan / page-size input 後の no-code product goal 再計画 | Chooses direct page navigation before filter/search because current/alias `runtime-data.json` already accepts bounded `page` values | `DONE` | 0.25 day / 0.25 日 |
| 235 | Runtime data direct page input first slice / runtime data direct page input first slice | Adds a bounded active pagination page input and Go control while preserving page-size Apply, Previous/Next metadata, no-query Refresh, and selected-key behavior | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 236 | Post-direct-page-input no-code product goal replan / direct page input 後の no-code product goal 再計画 | Chooses endpoint search query support before browser search UI so the read-only data contract is fixed first | `DONE` | 0.25 day / 0.25 日 |
| 237 | Runtime data search query endpoint first slice / runtime data search query endpoint first slice | Adds bounded `q` search for current/alias `runtime-data.json`, filtering generated DBAccess rows before pagination and default detail/form selection | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 238 | Post-search-query-endpoint no-code product goal replan / search query endpoint 後の no-code product goal 再計画 | Chooses browser search controls now that current/alias `runtime-data.json` has a bounded `q` contract | `DONE` | 0.25 day / 0.25 日 |
| 239 | Runtime data browser search controls first slice / runtime data browser search controls first slice | Adds explicit current/alias runtime-data Search controls that request `q` while preserving no-query Refresh, pagination, and selected-key behavior | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 240 | Runtime-data query controls milestone closure replan / runtime-data query controls milestone closure 再計画 | Chooses closure before field-specific filters, sort controls, or another read-model lane | `DONE` | 0.25 day / 0.25 日 |
| 241 | Runtime-data query controls milestone closure / runtime-data query controls milestone closure | Records accepted endpoint/browser query controls, verification baseline, remaining candidates, and no-push boundary | `DONE` | 0.25 day / 0.25 日 |
| 242 | Post-query-controls field filter replan / query controls 後の field filter 再計画 | Chooses endpoint field-specific filters before browser filter UI or sort controls so the contract is explicit first | `DONE` | 0.25 day / 0.25 日 |
| 243 | Runtime data field filter endpoint first slice / runtime data field filter endpoint first slice | Adds bounded `filter[field]=value` query support for current/alias `runtime-data.json`, filtering generated DBAccess rows before pagination and default detail/form selection | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 244 | Post-field-filter-endpoint no-code product goal replan / field filter endpoint 後の no-code product goal 再計画 | Chooses browser field filter controls now that current/alias `runtime-data.json` has a bounded field-filter contract | `DONE` | 0.25 day / 0.25 日 |
| 245 | Runtime data browser field filter controls first slice / runtime data browser field filter controls first slice | Adds explicit current/alias runtime-data field filter controls that request `filter[field]=value` while preserving no-query Refresh, search, pagination, and selected-key behavior | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 246 | Runtime-data field filter controls closure replan / runtime-data field filter controls closure 再計画 | Chooses closure before sort controls, persisted query state, or broader read-model polish | `DONE` | 0.25 day / 0.25 日 |
| 247 | Runtime-data field filter controls closure / runtime-data field filter controls closure | Records accepted endpoint/browser field-filter capability, verification baseline, remaining candidates, and no-push boundary | `DONE` | 0.25 day / 0.25 日 |
| 248 | Post-field-filter-controls no-code product goal replan / field filter controls 後の no-code product goal 再計画 | Chooses endpoint sort query support before browser sort controls so the read-only data contract is explicit first | `DONE` | 0.25 day / 0.25 日 |
| 249 | Runtime data sort query endpoint first slice / runtime data sort query endpoint first slice | Adds bounded one-field `sort[field]=asc|desc` query support for current/alias `runtime-data.json`, sorting generated DBAccess rows before pagination and default detail/form selection | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 250 | Post-sort-query-endpoint no-code product goal replan / sort query endpoint 後の no-code product goal 再計画 | Chooses browser sort controls now that current/alias `runtime-data.json` has a bounded one-field sort contract | `DONE` | 0.25 day / 0.25 日 |
| 251 | Runtime data browser sort controls first slice / runtime data browser sort controls first slice | Adds explicit current/alias runtime-data sort controls that request `sort[field]=asc|desc` while preserving no-query Refresh, search, filters, pagination, and selected-key behavior | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 252 | Runtime-data controls closure replan / runtime-data controls closure 再計画 | Chooses closure now that row selection, pagination, search, field filters, and sort controls are all present for the first slice | `DONE` | 0.25 day / 0.25 日 |
| 253 | Runtime-data controls closure / runtime-data controls closure | Records accepted current/alias live runtime-data controls capability, verification baseline, remaining candidates, and no-push boundary | `DONE` | 0.25 day / 0.25 日 |
| 254 | Post-runtime-data-controls no-code product goal replan / runtime-data controls 後の no-code product goal 再計画 | Chooses layout/accessibility polish before persisted query state or combined-query behavior | `DONE` | 0.25 day / 0.25 日 |
| 255 | Runtime-data controls layout/accessibility first slice / runtime-data controls layout/accessibility first slice | Adds stable runtime-data controls grouping semantics and smoke coverage while preserving existing read-only query behavior | `DONE` | 0.25 day / 0.25 日 |
| 256 | Post-controls-accessibility no-code product goal replan / controls accessibility 後の no-code product goal 再計画 | Chooses persisted query-control state before combined-query behavior so the dense controls remain understandable after each live data fetch | `DONE` | 0.25 day / 0.25 日 |
| 257 | Runtime-data query control state persistence first slice / runtime-data query control state persistence first slice | Restores search/filter/sort/page-size control values from `runtime-data.json` query metadata after generated screen re-render, without changing request behavior | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 258 | Post-query-control-state no-code product goal replan / query-control state 後の no-code product goal 再計画 | Chooses combined query behavior now that generated controls retain active query state after live data fetches | `DONE` | 0.25 day / 0.25 日 |
| 259 | Runtime-data combined query controls first slice / runtime-data combined query controls first slice | Lets generated search/filter/sort/pagination controls preserve one another's active values in a single read-only `runtime-data.json` request, while row selection remains explicit | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 260 | Runtime-data query controls final closure replan / runtime-data query controls final closure 再計画 | Chooses closure now that selection, pagination, search, filter, sort, accessibility grouping, retained state, and combined browser requests are all present | `DONE` | 0.25 day / 0.25 日 |
| 261 | Runtime-data query controls final closure / runtime-data query controls final closure | Records accepted current/alias read-only query-control capability, latest verification baseline, remaining candidates, and no-push boundary | `DONE` | 0.25 day / 0.25 日 |
| 262 | Post-query-controls-closure no-code product goal replan / query controls closure 後の no-code product goal 再計画 | Chooses form/detail default selection semantics before query reset, URL/history persistence, or broader read-model shape | `DONE` | 0.25 day / 0.25 日 |
| 263 | Runtime-data detail/form selection basis metadata first slice / runtime-data detail/form selection basis metadata first slice | Adds additive `selection_basis` metadata so runtime-data payloads explain whether detail/form values came from an explicit selected key, query-result first row, default first row, or empty result | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 264 | Post-selection-basis no-code product goal replan / selection basis 後の no-code product goal 再計画 | Chooses an explicit query reset affordance before URL/history persistence, visual density polish, richer filter/sort models, or broader read-model shape | `DONE` | 0.25 day / 0.25 日 |
| 265 | Runtime-data query reset affordance first slice / runtime-data query reset affordance first slice | Adds an explicit generated Clear control that fetches no-query current/alias runtime data and clears retained search/filter/sort/page controls without changing endpoint contracts | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 266 | Post-query-reset no-code product goal replan / query reset 後の no-code product goal 再計画 | Chooses visual density polish before URL/history persistence, richer filter/sort models, or broader read-model shape because the runtime-data control row is now feature-complete and dense | `DONE` | 0.25 day / 0.25 日 |
| 267 | Runtime-data controls visual density first slice / runtime-data controls visual density first slice | Tightens generated runtime-data control spacing, labels, input widths, and small-screen wrapping without changing read-only query behavior | `DONE` | 0.25 day / 0.25 日 |
| 268 | Post-visual-density no-code product goal replan / visual density 後の no-code product goal 再計画 | Chooses URL query mirroring before richer filter/sort models or broader read-model shape so current/alias read-only exploration is shareable without changing endpoint contracts | `DONE` | 0.25 day / 0.25 日 |
| 269 | Runtime-data browser URL query mirror first slice / runtime-data browser URL query mirror first slice | Mirrors successful current/alias runtime-data query operations into the browser URL via `history.replaceState`, and clears those params on Clear, without initial-load replay or artifact-key behavior changes | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |

Latest continuation after #203: #204 records that current/alias runtime routes are no-store artifact delivery paths, not dynamic business-data reads. A future fresh-data endpoint should be read-only, current/alias scoped, no-store, sourced from generated DBAccess/read-model materialization, and versioned separately from `runtime-preview.json`; immutable artifact-key preview remains static and should not process outbox, retry mutation, regenerate artifacts, or switch current revision inline. Push was not performed for #204. / #203 後の続き: #204 では current / alias runtime route が no-store の artifact delivery path であり、dynamic business-data read ではないことを記録しました。将来の fresh-data endpoint は read-only、current / alias scoped、no-store、generated DBAccess / read-model materialization 由来、かつ `runtime-preview.json` とは別 version の contract とするのが自然です。immutable artifact-key preview は static のままとし、outbox processing、mutation retry、artifact regenerate、current revision 切替を inline では行いません。#204 は push していません。

Latest continuation after #204: #205 fixes the first fresh runtime data endpoint contract plan. The planned endpoint remains current/alias scoped, read-only, `GET` only, `no-store`, and versioned as `no-code-runtime-data-v0`; the response should identify project/artifact/revision, expose screen rows/detail/form values derived from generated DBAccess/read-model materialization, and fail closed when the selected artifact or generated read binding is unavailable. Push was not performed for #205. / #204 後の続き: #205 では最初の fresh runtime data endpoint contract plan を固定しました。予定する endpoint は current / alias scoped、read-only、`GET` only、`no-store`、`no-code-runtime-data-v0` version とし、response は project / artifact / revision を識別し、generated DBAccess / read-model materialization 由来の screen rows / detail / form values を返し、選択 artifact や generated read binding が使えない場合は fail closed します。#205 は push していません。

Latest continuation after #205: #206 adds the first route contract slice for fresh runtime data. Current and alias `runtime-data.json` routes are authenticated, `GET` only, `no-store`, and return `no-code-runtime-data-v0` JSON. The handler attempts generated DBAccess list reads and returns rendered screen data on success; in the default sample28 public web stack, runtime DB connection is not yet bound into Apache, so the smoke records the expected fail-closed 422 JSON while preserving existing submit/enqueue/outbox processing behavior. Push was not performed for #206. / #205 後の続き: #206 では fresh runtime data の最初の route contract slice を追加しました。current / alias `runtime-data.json` route は authenticated、`GET` only、`no-store`、`no-code-runtime-data-v0` JSON を返します。handler は generated DBAccess list read を試み、成功時は rendered screen data を返します。default の sample28 public web stack では runtime DB connection が Apache 側にまだ binding されていないため、smoke は expected fail-closed 422 JSON を記録し、既存の submit / enqueue / outbox processing behavior は維持します。#206 は push していません。

Latest continuation after #206: #207 chooses the next implementation direction for successful live row reads. The next slice should bind runtime DB access for public web requests through the existing sample/tutorial runtime environment first, not by weakening `runtime-data.json` into static preview fallback. The success smoke should begin with sample28 current/alias reads after seeded business rows are reachable, then expand to sample29/sample31. Push was not performed for #207. / #206 後の続き: #207 では successful live row read の次 implementation direction を選びました。次 slice は `runtime-data.json` を static preview fallback に弱めるのではなく、まず既存 sample / tutorial runtime environment を通じて public web request 用 runtime DB access を binding します。success smoke は seeded business rows が読める状態の sample28 current / alias read から始め、その後 sample29 / sample31 に広げます。#207 は push していません。

Latest continuation after #207: #208 binds the generated DBAccess runtime environment around public runtime data reads, using the existing config DB binding for the sample/admin runtime. Sample28 public smoke now proves current and alias `runtime-data.json` return status 200, `no-code-runtime-data-v0`, three screens, and seeded first row key `1001`; submit/enqueue/outbox processing coverage still passes. Push was not performed for #208. / #207 後の続き: #208 では public runtime data read の間だけ generated DBAccess runtime environment を binding し、sample / admin runtime の既存 config DB binding を使います。sample28 public smoke は current / alias `runtime-data.json` が status 200、`no-code-runtime-data-v0`、3 screens、seeded first row key `1001` を返すことを確認し、submit / enqueue / outbox processing coverage も引き続き通過します。#208 は push していません。

Latest continuation after #208: #209 promotes the successful runtime-data read smoke across the other product-facing sample profiles. Sample29 current/alias `runtime-data.json` returns status 200, `no-code-runtime-data-v0`, three screens, and seeded first row key `2001`; sample31 current/alias returns status 200, `no-code-runtime-data-v0`, three screens, and seeded first row key `3101`. Existing current/alias submit enqueue checks and generated DBAccess outbox processing smokes still pass. Push was not performed for #209. / #208 後の続き: #209 では successful runtime-data read smoke を他の product-facing sample profile に広げました。sample29 current / alias `runtime-data.json` は status 200、`no-code-runtime-data-v0`、3 screens、seeded first row key `2001` を返し、sample31 current / alias も status 200、`no-code-runtime-data-v0`、3 screens、seeded first row key `3101` を返します。既存の current / alias submit enqueue check と generated DBAccess outbox processing smoke も引き続き通過します。#209 は push していません。

Latest continuation after #209: #210 closes the first fresh runtime data endpoint milestone. Accepted capability: current/alias public runtime selections can expose authenticated, read-only, no-store, versioned live data snapshots through generated DBAccess across sample28, sample29, and sample31, while immutable artifact-key preview remains static and submit/outbox processing remains separate. Remaining candidates are UI consumption of `runtime-data.json`, post-submit fresh data reload behavior, broader read model shape, and cache/error wording polish. Push was not performed for #210. / #209 後の続き: #210 では first fresh runtime data endpoint milestone を closure しました。accepted capability は、current / alias public runtime selection が authenticated、read-only、no-store、versioned な live data snapshot を generated DBAccess 経由で sample28、sample29、sample31 に公開できることです。immutable artifact-key preview は static のまま、submit / outbox processing は別経路のままです。残る候補は UI 側での `runtime-data.json` consumption、post-submit fresh data reload behavior、より広い read model shape、cache / error wording polish です。#210 は push していません。

Latest continuation after #210: #211 chooses current/alias UI consumption of `runtime-data.json` as the next small product-facing slice. The implementation should keep artifact-key preview immutable, inject a public runtime data binding only for current/alias pages, and let Refresh fetch the read-only data endpoint before any broader post-submit auto-refresh behavior. Push was not performed for #211. / #210 後の続き: #211 では次の小さな product-facing slice として current / alias UI による `runtime-data.json` consumption を選びました。implementation は artifact-key preview を immutable のままにし、current / alias page にだけ public runtime data binding を注入し、より広い post-submit auto-refresh behavior の前に Refresh が read-only data endpoint を fetch できるようにします。#211 は push していません。

Latest continuation after #211: #212 implements the first runtime data UI consumption slice. Current/alias public runtime pages now inject `runtime_data_url`; Refresh fetches `runtime-data.json`, validates `no-code-runtime-data-v0`, merges live data into the existing generated screen metadata, and re-renders list/detail/form bodies. Artifact-key preview remains immutable and keeps reload behavior. sample28, sample29, and sample31 public runtime browser smokes now verify real current/alias runtime-data fetch and live row rendering. Push was not performed for #212. / #211 後の続き: #212 では最初の runtime data UI consumption slice を実装しました。current / alias public runtime page は `runtime_data_url` を注入し、Refresh は `runtime-data.json` を fetch して `no-code-runtime-data-v0` を検証し、既存の generated screen metadata に live data を重ねて list / detail / form body を再描画します。artifact-key preview は immutable のままで reload behavior を維持します。sample28、sample29、sample31 の public runtime browser smoke は real current / alias runtime-data fetch と live row rendering を検証するようになりました。#212 は push していません。

Latest continuation after #212: #213 preserves generated action key values after a live `runtime-data.json` Refresh. When the refreshed form does not render an action key as a visible field, the runtime now carries that key as a hidden control so the local action intent draft does not regress to `key.missing:<field>` after live data is applied. Push was not performed for #213. / #212 後の続き: #213 では live `runtime-data.json` Refresh 後の generated action key を保持しました。refresh 後の form が action key を visible field として描画しない場合も、runtime が hidden control として key を持つため、live data 適用後に local action intent draft が `key.missing:<field>` へ戻りません。#213 は push していません。

Latest continuation after #213: #214 adds terminal-done auto refresh for current/alias public runtime previews. When live outbox polling reaches terminal `done` and a `runtime_data_url` binding exists, the runtime fetches fresh `runtime-data.json` automatically through the same read-only path used by manual Refresh. Pending timeout, failed / needs-review status, and immutable artifact-key preview behavior remain unchanged. Push was not performed for #214. / #213 後の続き: #214 では current / alias public runtime preview に terminal done auto refresh を追加しました。live outbox polling が terminal `done` に到達し、`runtime_data_url` binding がある場合、runtime は manual Refresh と同じ read-only path で fresh `runtime-data.json` を自動取得します。pending timeout、failed / needs-review status、immutable artifact-key preview の behavior は変更していません。#214 は push していません。

Latest continuation after #214: #215 clarifies runtime-data Refresh wording. The public runtime now says artifact-key previews stay static, current/alias previews can fetch read-only live runtime data, and runtime-data fetch failures leave current preview data unchanged. Behavior is unchanged from #214; this is a product-surface wording and smoke expectation polish. Push was not performed for #215. / #214 後の続き: #215 では runtime-data Refresh の文言を明確化しました。public runtime は artifact-key preview が static のままであること、current / alias preview は read-only live runtime data を取得できること、runtime-data fetch failure では現在の preview data を変更しないことを表示します。behavior は #214 から変更せず、product-surface wording と smoke expectation の polish です。#215 は push していません。

Latest continuation after #215: #216 adds a post-processing runtime-data proof to the existing outbox processing smoke. After generated server DBAccess processes the pending no-code sync outbox item against the runtime SQLite row, the smoke now also renders generated runtime-data screens from the same runtime DB environment and verifies the processed value appears in list, detail, and form data. Push was not performed for #216. / #215 後の続き: #216 では既存 outbox processing smoke に post-processing runtime-data proof を追加しました。generated server DBAccess が pending no-code sync outbox item を runtime SQLite row に反映した後、同じ runtime DB environment から generated runtime-data screens を描画し、処理後の値が list / detail / form data に現れることを確認します。#216 は push していません。

Latest continuation after #216: #217 promotes the post-processing runtime-data proof across sample29 and sample31 public runtime smokes. sample29 verifies processed `next_action` and sample31 verifies processed `fulfillment_note` in generated runtime-data list, detail, and form data after generated server DBAccess outbox processing. Push was not performed for #217. / #216 後の続き: #217 では post-processing runtime-data proof を sample29 / sample31 の public runtime smoke へ広げました。sample29 は processed `next_action`、sample31 は processed `fulfillment_note` が generated server DBAccess outbox processing 後の generated runtime-data list / detail / form data に出ることを確認します。#217 は push していません。

Latest continuation after #217: #218 records the runtime-data read-model shape boundary. Current `runtime-data.json` returns current/alias scoped, read-only, no-store screen renders; list/detail/form are rendered from generated DBAccess rows, with detail/form using the first row. The next recommended implementation step is additive screen-level metadata such as row count, selected key, source, and freshness hints before introducing query-driven pagination, filtering, detail selection, or form-default semantics. Push was not performed for #218. / #217 後の続き: #218 では runtime-data read-model shape boundary を記録しました。現在の `runtime-data.json` は current / alias scoped、read-only、no-store の screen render を返し、list / detail / form は generated DBAccess rows から描画され、detail / form は先頭 row を使います。次の推奨実装は、query-driven pagination、filtering、detail selection、form-default semantics の前に、row count、selected key、source、freshness hint などの additive screen-level metadata を足すことです。#218 は push していません。

Latest continuation after #218: #219 adds the first additive runtime-data screen metadata slice. Each current/alias `runtime-data.json` screen now includes `metadata.row_count`, `metadata.selected_key`, and `metadata.freshness: live-read` while preserving the existing `data` and `source` shape. Endpoint and post-processing smokes assert the metadata, and sample28 public runtime plus full integration tests passed. Push was not performed for #219. / #218 後の続き: #219 では最初の additive runtime-data screen metadata slice を追加しました。各 current / alias `runtime-data.json` screen は既存の `data` と `source` shape を維持したまま、`metadata.row_count`、`metadata.selected_key`、`metadata.freshness: live-read` を含みます。endpoint と post-processing smoke は metadata を検証し、sample28 public runtime と full integration test は通過しました。#219 は push していません。

Latest continuation after #219: #220 promotes the runtime-data metadata verification across sample29 and sample31. Both public runtime smokes now prove current/alias `runtime-data.json` reports row-count and selected-key metadata, and the generated server DBAccess outbox processing proof reports the same metadata after the processed runtime DB row is rendered. Push was not performed for #220. / #219 後の続き: #220 では runtime-data metadata verification を sample29 / sample31 へ広げました。両方の public runtime smoke で current / alias `runtime-data.json` が row-count と selected-key metadata を返すことを確認し、generated server DBAccess outbox processing proof でも処理後 runtime DB row の render 後に同じ metadata が出ることを確認しました。#220 は push していません。

Latest continuation after #220: #221 adds the first query-driven detail/form selection slice for `runtime-data.json`. Current/alias data routes now accept optional `selected_key`; without it they keep first-row behavior, with it they render detail/form from the matching generated action key row while preserving list rows, and missing keys fail closed with JSON 422. sample28 smoke proves `selected_key=1002` selects a non-first row and missing selected key fails closed; full integration tests passed. Push was not performed for #221. / #220 後の続き: #221 では `runtime-data.json` の最初の query-driven detail / form selection slice を追加しました。current / alias data route は任意の `selected_key` を受け、指定なしでは従来どおり先頭 row、指定ありでは list rows を維持したまま matching generated action key row から detail / form を描画し、存在しない key は JSON 422 で fail closed します。sample28 smoke は `selected_key=1002` が先頭以外の row を選ぶことと、missing selected key が fail closed することを確認し、full integration test も通過しました。#221 は push していません。

Latest continuation after #221: #222 promotes selected-key query verification across sample29 and sample31. Both public runtime smokes now prove normal current/alias `runtime-data.json` keep first-row behavior, `current-selected` echoes `query.selected_key` and renders the selected key, missing selected keys fail closed with 422, and existing submit/outbox processing plus runtime-data post-processing proof still pass. Push was not performed for #222. / #221 後の続き: #222 では selected-key query verification を sample29 / sample31 へ広げました。両方の public runtime smoke で通常の current / alias `runtime-data.json` は first-row behavior を保ち、`current-selected` は `query.selected_key` を返して selected key を描画し、missing selected key は 422 で fail closed し、既存の submit / outbox processing と runtime-data post-processing proof も引き続き通過します。#222 は push していません。

Latest continuation after #222: #223 adds the first browser UI affordance for selected runtime data rows. Current/alias runtime previews re-render list rows with `Select` buttons when a live `runtime_data_url` binding exists; clicking a row fetches `runtime-data.json?selected_key=...`, highlights the selected row, and refreshes detail/form screens plus hidden action key values from that selected row. Artifact-key previews remain static. sample28 public runtime smoke proves current and alias row selection for key `1002`. Push was not performed for #223. / #222 後の続き: #223 では selected runtime data row の最初の browser UI affordance を追加しました。current / alias runtime preview は live `runtime_data_url` binding がある場合に list row を `Select` button 付きで再描画し、row click で `runtime-data.json?selected_key=...` を取得し、selected row を highlight し、detail / form screen と hidden action key value をその selected row から更新します。artifact-key preview は static のままです。sample28 public runtime smoke は key `1002` の current / alias row selection を確認しました。#223 は push していません。

Latest continuation after #223: #224 promotes the runtime data row-selection UI proof across sample29 and sample31 by adding second seeded rows and asserting non-first row selection in their public runtime smokes. sample29 now verifies selection of key `2002`, sample31 verifies selection of key `3102`, current/alias live runtime data reports two rows, and the existing submit/outbox processing proof remains passing. Push was not performed for #224. / #223 後の続き: #224 では sample29 と sample31 に 2 件目の seed row を追加し、public runtime smoke で先頭以外の row selection を確認することで、runtime data row-selection UI proof を他の product-facing profile へ広げました。sample29 は key `2002`、sample31 は key `3102` の選択を確認し、current / alias live runtime data は 2 rows を返し、既存の submit / outbox processing proof も引き続き通過します。#224 は push していません。

Latest continuation after #224: #225 closes the first runtime-data row-selection milestone. Accepted capability: current/alias public runtime previews can fetch read-only live runtime data, render list/detail/form from generated DBAccess rows, select non-first rows by generated action key, keep immutable artifact-key previews static, and preserve submit/outbox processing as a separate mutation path across sample28, sample29, and sample31. Remaining candidates are query-driven pagination/page-size, filter parameters, form default semantics, and operator/admin wording for live runtime data selection boundaries. Push was not performed for #225. / #224 後の続き: #225 では最初の runtime-data row-selection milestone を closure しました。accepted capability は、current / alias public runtime preview が read-only live runtime data を取得し、generated DBAccess rows から list / detail / form を描画し、generated action key で先頭以外の row を選択し、immutable artifact-key preview は static に保ち、sample28 / sample29 / sample31 全体で submit / outbox processing を別の mutation path として維持できることです。残る候補は query-driven pagination / page-size、filter parameters、form default semantics、operator / admin wording for live runtime data selection boundaries です。#225 は push していません。

Latest continuation after #225: #226 fixes the first runtime-data pagination contract plan before implementation. The planned first slice should add optional `page` and `page_size` query parameters for current/alias `runtime-data.json`, slice list-screen rows only, expose additive pagination metadata, keep no-query behavior unchanged, keep `selected_key` as the detail/form selection mechanism, fail closed on invalid pagination input, and leave immutable artifact-key previews plus submit/outbox mutation behavior unchanged. Push was not performed for #226. / #225 後の続き: #226 では実装前に最初の runtime-data pagination contract plan を固定しました。予定する first slice は current / alias `runtime-data.json` に任意の `page` と `page_size` query parameter を追加し、list screen rows だけを slice し、additive pagination metadata を返し、query なしの挙動は維持し、detail / form selection は `selected_key` の責務として維持し、不正な pagination input は fail closed にし、immutable artifact-key preview と submit / outbox mutation behavior は変更しません。#226 は push していません。

Latest continuation after #226: #227 implements the first runtime-data pagination endpoint slice. Current/alias `runtime-data.json` accepts optional `page` and `page_size`, applies pagination only to list screen rows, returns additive list pagination metadata, preserves no-query and selected-key detail/form behavior, and fails closed for invalid pagination input. sample28, sample29, and sample31 public runtime smokes prove `page=2&page_size=1` and invalid `page=0` behavior. Push was not performed for #227. / #226 後の続き: #227 では最初の runtime-data pagination endpoint slice を実装しました。current / alias `runtime-data.json` は任意の `page` と `page_size` を受け取り、pagination は list screen rows のみに適用し、list pagination metadata を additive に返し、query なしの挙動と selected-key detail / form behavior を維持し、不正な pagination input は fail closed にします。sample28、sample29、sample31 の public runtime smoke は `page=2&page_size=1` と invalid `page=0` behavior を確認しました。#227 は push していません。

Latest continuation after #227: #228 fixes the first browser pagination controls plan. The first UI slice should preserve no-query full-list Refresh behavior, add an explicit page-size entry control for current/alias live runtime data, fetch `runtime-data.json?page=1&page_size=...` only after the user opts into pagination, then show Previous/Next controls from returned pagination metadata. Detail/form selection remains governed by `selected_key`, and artifact-key previews remain static. Push was not performed for #228. / #227 後の続き: #228 では最初の browser pagination controls plan を固定しました。first UI slice は query なしの full-list Refresh behavior を維持し、current / alias live runtime data に明示的な page-size entry control を追加し、user が pagination を選んだ後だけ `runtime-data.json?page=1&page_size=...` を取得し、その後は返却された pagination metadata から Previous / Next controls を表示します。detail / form selection は引き続き `selected_key` が担い、artifact-key preview は static のままです。#228 は push していません。

Latest continuation after #228: #229 implements the first browser pagination controls slice. Current/alias list screens now expose an explicit `Page size 1` entry control, then render Previous/Next controls from `runtime-data.json` pagination metadata after the user opts in. Plain Refresh still fetches the no-query full-list runtime data, selected-key row selection still uses `selected_key`, and artifact-key previews remain static. sample28, sample29, and sample31 public runtime browser smokes verify `page=1&page_size=1` entry, `page=2&page_size=1` next navigation, one-row paginated rendering, pagination metadata, and selected-row refresh behavior. Push was not performed for #229. / #228 後の続き: #229 では最初の browser pagination controls slice を実装しました。current / alias の list screen は明示的な `Page size 1` entry control を表示し、user が選択した後は `runtime-data.json` の pagination metadata から Previous / Next controls を表示します。通常の Refresh は引き続き query なしの full-list runtime data を取得し、selected-key row selection は引き続き `selected_key` を使い、artifact-key preview は static のままです。sample28、sample29、sample31 の public runtime browser smoke で `page=1&page_size=1` entry、`page=2&page_size=1` next navigation、1-row paginated rendering、pagination metadata、selected-row refresh behavior を確認しました。#229 は push していません。

Latest continuation after #229: #230 chooses compact pagination total-count visibility as the next smallest product-facing continuation after fixed-size pagination controls. #231 adds the `total_rows` count to generated runtime pagination controls as `Page X of Y (N total rows)` plus a DOM data attribute, and extends the browser smoke to verify the displayed count matches returned pagination metadata. Arbitrary page-size input, filter/search, and larger navigation controls remain deferred. Push was not performed for #230/#231. / #229 後の続き: #230 では fixed-size pagination controls 後の次の最小 product-facing continuation として compact pagination total-count visibility を選びました。#231 では generated runtime pagination controls に `total_rows` を `Page X of Y (N total rows)` と DOM data attribute として表示し、browser smoke で表示件数が返却された pagination metadata と一致することを確認します。任意 page-size input、filter / search、より大きな navigation controls は deferred のままです。#230/#231 は push していません。

Latest continuation after #231: #232 chooses user-entered page-size controls as the next small continuation because current/alias `runtime-data.json` already accepts bounded `page_size` values. #233 replaces the fixed `Page size 1` button with a numeric page-size input and Apply control, keeps Apply on page 1, clamps browser input to the existing 1..100 endpoint boundary, and leaves Previous/Next driven by returned pagination metadata. Filter/search and direct page navigation remain deferred. Push was not performed for #232/#233. / #231 後の続き: #232 では current / alias `runtime-data.json` が既に bounded `page_size` を受けられるため、次の小さな continuation として user-entered page-size controls を選びました。#233 では固定 `Page size 1` button を numeric page-size input と Apply control に置き換え、Apply は page 1 から開始し、browser input は既存 endpoint boundary の 1..100 に clamp し、Previous / Next は返却された pagination metadata のままにします。filter / search と direct page navigation は deferred です。#232/#233 は push していません。

Latest continuation after #233: #234 chooses direct page navigation as the next small continuation because current/alias `runtime-data.json` already accepts bounded `page` values and the active pagination control already displays page count. #235 adds a numeric active page input and `Go` control, clamps browser input to the returned `page_count`, keeps page-size Apply starting at page 1, and leaves Previous/Next driven by returned pagination metadata. Filter/search remains deferred. Push was not performed for #234/#235. / #233 後の続き: #234 では current / alias `runtime-data.json` が既に bounded `page` を受け取り、active pagination control が page count を表示しているため、次の小さな continuation として direct page navigation を選びました。#235 では active pagination に numeric page input と `Go` control を追加し、browser input は返却された `page_count` に clamp し、page-size Apply は page 1 開始のまま、Previous / Next は返却された pagination metadata のままにします。filter / search は deferred です。#234/#235 は push していません。

Latest continuation after #235: #236 chooses endpoint search query support before browser search UI so the read-only data contract is fixed first. #237 adds a bounded `q` query for current/alias `runtime-data.json`, filters generated DBAccess rows by rendered display value before pagination and default detail/form selection, echoes the query, and keeps selected-key, pagination, immutable artifact preview, and submit/outbox mutation behavior separate. Browser search UI remains deferred. Push was not performed for #236/#237. / #235 後の続き: #236 では browser search UI の前に endpoint search query support を選び、read-only data contract を先に固定しました。#237 では current / alias `runtime-data.json` に bounded `q` query を追加し、generated DBAccess rows を表示値で pagination と default detail / form selection の前に絞り込み、query を echo し、selected-key、pagination、immutable artifact preview、submit / outbox mutation behavior は別責務のままにします。Browser search UI は deferred です。#236/#237 は push していません。

Latest continuation after #237: #238 chooses browser search controls now that current/alias `runtime-data.json` has a bounded `q` contract. #239 adds explicit Search input/buttons to current/alias runtime-data controls, requests `q` through the read-only endpoint, keeps normal Refresh as no-query full-list reload, and leaves pagination plus selected-key behavior separate. Push was not performed for #238/#239. / #237 後の続き: #238 では current / alias `runtime-data.json` に bounded `q` contract が入ったため、browser search controls を選びました。#239 では current / alias runtime-data controls に明示的な Search input / button を追加し、read-only endpoint へ `q` を投げ、通常 Refresh は no-query full-list reload のまま、pagination と selected-key behavior は別責務のままにします。#238/#239 は push していません。

Latest continuation after #239: #240 chooses a runtime-data query controls milestone closure before field-specific filters, sort controls, or another read-model lane. #241 closes the lane with accepted capability: current/alias public runtime previews can fetch read-only live generated DBAccess rows, select rows, page through list rows, adjust page size, jump to a page, and search with `q`, while immutable artifact-key previews and submit/outbox mutation behavior remain separate. Push was not performed for #240/#241. / #239 後の続き: #240 では field-specific filters、sort controls、別 read-model lane の前に runtime-data query controls milestone closure を選びました。#241 では accepted capability として、current / alias public runtime preview が read-only live generated DBAccess rows を取得し、row selection、list pagination、page size 調整、direct page jump、`q` search を行える一方で、immutable artifact-key preview と submit / outbox mutation behavior は別責務のままであることを記録して lane を閉じます。#240/#241 は push していません。

Latest continuation after #241: #242 chooses endpoint field-specific filters before browser filter UI or sort controls so the read-only data contract is explicit first. #243 adds bounded `filter[field]=value` support for current/alias `runtime-data.json`, applies filters to generated DBAccess rows after global `q` search and before pagination/default detail-form selection, echoes `query.filter`, and fails closed for invalid or unknown filter fields. Push was not performed for #242/#243. / #241 後の続き: #242 では browser filter UI や sort controls の前に endpoint field-specific filters を選び、read-only data contract を先に明示します。#243 では current / alias `runtime-data.json` に bounded `filter[field]=value` support を追加し、global `q` search 後、pagination / default detail-form selection 前に generated DBAccess rows を絞り込み、`query.filter` を echo し、不正または未知の filter field は fail closed にします。#242/#243 は push していません。

Latest continuation after #243: #244 chooses browser field filter controls now that the current/alias `runtime-data.json` endpoint has an explicit bounded `filter[field]=value` contract. #245 adds generated runtime field filter controls to the same read-only runtime-data control row, keeps normal Refresh as no-query full-list reload, and keeps search, pagination, selected-key row selection, immutable artifact-key previews, and submit/outbox mutation behavior separate. Push was not performed for #244/#245. / #243 後の続き: #244 では current / alias `runtime-data.json` endpoint に bounded `filter[field]=value` contract が入ったため、browser field filter controls を選びました。#245 では generated runtime の同じ read-only runtime-data control row に field filter controls を追加し、通常 Refresh は no-query full-list reload のまま、search、pagination、selected-key row selection、immutable artifact-key preview、submit / outbox mutation behavior は別責務のままにします。#244/#245 は push していません。

Latest continuation after #245: #246 chooses a field filter controls closure before sort controls, persisted query state, or broader read-model polish. #247 closes the field-filter lane with accepted capability: current/alias public runtime previews can filter read-only live generated DBAccess rows through bounded `filter[field]=value` endpoint queries and explicit browser controls while preserving no-query Refresh, search, pagination, selected-key selection, immutable artifact-key previews, and submit/outbox mutation separation. Push was not performed for #246/#247. / #245 後の続き: #246 では sort controls、persisted query state、より広い read-model polish の前に field filter controls closure を選びました。#247 では accepted capability として、current / alias public runtime preview が bounded `filter[field]=value` endpoint query と明示 browser controls によって read-only live generated DBAccess rows を field filter でき、通常 Refresh、search、pagination、selected-key selection、immutable artifact-key preview、submit / outbox mutation separation を維持することを記録して lane を閉じます。#246/#247 は push していません。

Latest continuation after #247: #248 chooses endpoint sort query support before browser sort controls so the read-only data contract is explicit first. #249 adds bounded one-field `sort[field]=asc|desc` support for current/alias `runtime-data.json`, applies sorting after search/filter and before pagination/default detail-form selection, echoes `query.sort`, and fails closed for invalid sort direction or missing sort field. Browser sort UI remains deferred. Push was not performed for #248/#249. / #247 後の続き: #248 では browser sort controls の前に endpoint sort query support を選び、read-only data contract を先に固定します。#249 では current / alias `runtime-data.json` に bounded one-field `sort[field]=asc|desc` support を追加し、search / filter 後、pagination / default detail-form selection 前に sorting を適用し、`query.sort` を echo し、不正な sort direction または存在しない sort field は fail closed にします。Browser sort UI は deferred です。#248/#249 は push していません。

Latest continuation after #249: #250 chooses browser sort controls now that current/alias `runtime-data.json` has a bounded one-field sort contract. #251 adds generated runtime sort controls to the read-only runtime-data control row, requests `sort[field]=asc|desc`, keeps normal Refresh as no-query full-list reload, and keeps search, field filters, pagination, selected-key row selection, immutable artifact-key previews, and submit/outbox mutation behavior separate. Push was not performed for #250/#251. / #249 後の続き: #250 では current / alias `runtime-data.json` に bounded one-field sort contract が入ったため、browser sort controls を選びました。#251 では generated runtime の read-only runtime-data control row に sort controls を追加し、`sort[field]=asc|desc` を request し、通常 Refresh は no-query full-list reload のまま、search、field filter、pagination、selected-key row selection、immutable artifact-key preview、submit / outbox mutation behavior は別責務のままにします。#250/#251 は push していません。

Latest continuation after #251: #252 chooses a closure report before persisted query state, combined query controls, layout/accessibility polish, form default semantics, or broader read-model work. #253 closes the runtime-data controls lane with accepted capability: current/alias public runtime previews can fetch read-only live generated DBAccess rows, select rows, page and jump through list rows, change page size, search with `q`, filter with `filter[field]=value`, and sort with `sort[field]=asc|desc`, while normal Refresh remains a no-query full-list reload and immutable artifact-key previews plus submit/outbox mutation behavior remain separate. Push was not performed for #252/#253. / #251 後の続き: #252 では persisted query state、combined query controls、layout / accessibility polish、form default semantics、より広い read-model work の前に closure report を選びました。#253 では accepted capability として、current / alias public runtime preview が read-only live generated DBAccess rows を取得し、row selection、list pagination / jump、page size 変更、`q` search、`filter[field]=value` filter、`sort[field]=asc|desc` sort を行える一方で、通常 Refresh は no-query full-list reload のまま、immutable artifact-key preview と submit / outbox mutation behavior は別責務のままであることを記録して lane を閉じます。#252/#253 は push していません。

Latest continuation after #253: #254 chooses layout/accessibility polish before persisted query state or combined-query behavior because the runtime-data control row is now dense. #255 adds stable `data-runtime-data-controls` grouping semantics plus `role="group"` / `aria-label="Runtime data controls"` to generated current/alias runtime-data controls, and extends browser/PHP coverage without changing request behavior. Push was not performed for #254/#255. / #253 後の続き: #254 では runtime-data control row が密になったため、persisted query state や combined-query behavior の前に layout / accessibility polish を選びました。#255 では generated current / alias runtime-data controls に stable `data-runtime-data-controls` grouping semantics と `role="group"` / `aria-label="Runtime data controls"` を追加し、request behavior は変えずに browser / PHP coverage を拡張します。#254/#255 は push していません。

Latest continuation after #255: #256 chooses persisted query-control state before combined-query behavior because search/filter/sort controls currently re-render after each live data fetch. #257 restores generated search/filter/sort/page-size control values from returned `runtime-data.json` `query` / `pagination` metadata after screen re-render, while keeping request URL construction, no-query Refresh, selected-key row selection, immutable artifact-key previews, and submit/outbox mutation behavior unchanged. Push was not performed for #256/#257. / #255 後の続き: #256 では search / filter / sort controls が live data fetch 後に再描画されるため、combined-query behavior の前に persisted query-control state を選びました。#257 では returned `runtime-data.json` の `query` / `pagination` metadata から generated search / filter / sort / page-size control values を screen re-render 後に復元し、request URL construction、no-query Refresh、selected-key row selection、immutable artifact-key preview、submit / outbox mutation behavior は変更しません。#256/#257 は push していません。

Latest continuation after #257: #258 chooses combined query behavior now that generated controls retain active query state after live data fetches. #259 lets generated Search, Filter, Sort, and pagination/page-size operations preserve one another's current values in a single read-only `runtime-data.json` request, while selected-key row selection remains an explicit row-selection query and no-query Refresh remains a full-list reload. Push was not performed for #258/#259. / #257 後の続き: #258 では live data fetch 後に generated controls が active query state を保持できるようになったため、combined query behavior を選びました。#259 では generated Search、Filter、Sort、pagination / page-size 操作が互いの current values を 1 つの read-only `runtime-data.json` request に保持するようにし、selected-key row selection は明示的な row-selection query のまま、no-query Refresh は full-list reload のままにします。#258/#259 は push していません。

Latest continuation after #259: #260 chooses closure now that the current/alias read-only runtime-data control surface includes row selection, pagination/page-size, direct page jumps, search, field filter, sort, accessibility grouping, retained active control state, and combined browser requests. #261 closes the runtime-data query controls lane with accepted capability, latest verification baseline, remaining candidates, and no-push boundary. Push was not performed for #260/#261. / #259 後の続き: #260 では current / alias の read-only runtime-data control surface に row selection、pagination / page-size、direct page jump、search、field filter、sort、accessibility grouping、active control state retention、combined browser request が揃ったため closure を選びました。#261 では runtime-data query controls lane を accepted capability、latest verification baseline、remaining candidates、no-push boundary とともに閉じます。#260/#261 は push していません。

Latest continuation after #261: #262 chooses form/detail default selection semantics before query reset affordance, URL/history persistence, visual density polish, richer filter/sort models, or broader read-model shape. #263 adds additive `selection_basis` metadata to runtime-data screen metadata so generated payloads explain whether detail/form values came from an explicit `selected_key`, the first row of a search/filter/sort result, the default first row, or an empty result. Push was not performed for #262/#263. / #261 後の続き: #262 では query reset affordance、URL / history persistence、visual density polish、より豊かな filter / sort model、broader read-model shape の前に form / detail default selection semantics を選びました。#263 では runtime-data screen metadata に additive な `selection_basis` metadata を追加し、detail / form values が明示 `selected_key`、search / filter / sort result の先頭行、default first row、empty result のどれに由来するかを generated payload が説明できるようにします。#262/#263 は push していません。

Latest continuation after #263: #264 chooses an explicit query reset affordance because the read-only runtime-data control surface now preserves combined search/filter/sort/page state. #265 adds a generated `Clear` control that calls current/alias `runtime-data.json` without query parameters, resets retained controls from returned metadata, and keeps immutable artifact previews plus submit/outbox mutation behavior separate. Push was not performed for #264/#265. / #263 後の続き: #264 では read-only runtime-data control surface が search / filter / sort / page state を combined に保持するようになったため、明示的な query reset affordance を選びました。#265 では generated `Clear` control を追加し、current / alias `runtime-data.json` を query parameter なしで呼び、返却 metadata から retained controls をリセットし、immutable artifact preview と submit / outbox mutation behavior は分離したままにします。#264/#265 は push していません。

Latest continuation after #265: #266 chooses visual density polish before URL/history persistence, richer filter/sort models, or broader read-model shape because the runtime-data control row now includes selection, pagination, search, filter, sort, retained combined state, and Clear. #267 tightens generated control spacing, label emphasis, input/select widths, and small-screen wrapping without changing request construction, endpoint contracts, immutable artifact previews, or submit/outbox mutation behavior. Push was not performed for #266/#267. / #265 後の続き: #266 では runtime-data control row が selection、pagination、search、filter、sort、combined state retention、Clear まで含むようになったため、URL / history persistence、より豊かな filter / sort model、broader read-model shape の前に visual density polish を選びました。#267 では request construction、endpoint contract、immutable artifact preview、submit / outbox mutation behavior を変えずに、generated controls の spacing、label emphasis、input / select width、小画面折り返しを締めます。#266/#267 は push していません。

Latest continuation after #267: #268 chooses URL query mirroring before richer filter/sort models or broader read-model shape. #269 mirrors successful current/alias read-only runtime-data operations into the browser URL with `history.replaceState`, clears known runtime-data query parameters on `Clear`, and keeps initial-load replay, endpoint contracts, immutable artifact-key previews, and submit/outbox mutation behavior out of scope. Push was not performed for #268/#269. / #267 後の続き: #268 ではより豊かな filter / sort model や broader read-model shape の前に URL query mirror を選びました。#269 では successful な current / alias の read-only runtime-data 操作を `history.replaceState` で browser URL に反映し、`Clear` で既知の runtime-data query parameter を消します。initial-load replay、endpoint contract、immutable artifact-key preview、submit / outbox mutation behavior は対象外です。#268/#269 は push していません。

Latest continuation after #269: #270 chooses initial URL query replay because #269 made successful current/alias runtime-data exploration visible in the browser URL but did not consume those parameters on page load. #271 replays known runtime-data URL parameters once on initial preview load through the existing read-only `runtime-data.json` refresh path, while keeping back/forward handling, endpoint contracts, richer filter/sort models, immutable artifact-key previews, and submit/outbox mutation behavior out of scope. Push was not performed for #270/#271. / #269 後の続き: #270 では #269 で current / alias の runtime-data exploration を browser URL に反映できるようになった一方、page load 時の消費はまだだったため initial URL query replay を選びました。#271 では既知の runtime-data URL parameter を初期 preview load で一度だけ既存の read-only `runtime-data.json` refresh 経路に流し、back / forward handling、endpoint contract、より豊かな filter / sort model、immutable artifact-key preview、submit / outbox mutation behavior は対象外のままにします。#270/#271 は push していません。

Latest continuation after #271: #272 chooses closure for the URL persistence lane before starting richer filter/sort models or broader read-model shape. #273 closes the lane with accepted capability: current/alias runtime-data exploration can be mirrored into the browser URL and replayed on initial load, while immutable artifact-key previews, endpoint contracts, mutation behavior, and browser back/forward replay remain separate future decisions. Push was not performed for #272/#273. / #271 後の続き: #272 ではより豊かな filter / sort model や broader read-model shape に入る前に URL persistence lane の closure を選びました。#273 では accepted capability として、current / alias の runtime-data exploration を browser URL に反映し、初期 load で再生できる一方、immutable artifact-key preview、endpoint contract、mutation behavior、browser back / forward replay は将来判断として分離したままにして lane を閉じます。#272/#273 は push していません。

Latest continuation after #273: #274 chooses direct endpoint multi-filter coverage before changing generated browser controls because the endpoint already accepts bounded multi-field filters. #275 adds public runtime endpoint smoke coverage for two simultaneous `filter[field]=value` clauses across the no-code sample profiles, while keeping generated browser UI, URL mirror/replay, sort behavior, endpoint contracts, immutable artifact-key previews, and submit/outbox mutation behavior unchanged. Push was not performed for #274/#275. / #273 後の続き: #274 では endpoint がすでに bounded multi-field filter を受けるため、generated browser controls を変える前に direct endpoint multi-filter coverage を選びました。#275 では no-code sample profiles に対して 2 つ同時の `filter[field]=value` 条件を public runtime endpoint smoke で固定し、generated browser UI、URL mirror / replay、sort behavior、endpoint contract、immutable artifact-key preview、submit / outbox mutation behavior は変更しません。#274/#275 は push していません。

Latest continuation after #275: #276 chooses the multi-filter upper-bound smoke before generated multi-filter browser UI because the endpoint contract allows up to 8 fields. #277 adds fail-closed public runtime endpoint smoke coverage for 9 simultaneous `filter[field]=value` clauses, while keeping generated browser UI, URL mirror/replay, sort behavior, endpoint contracts, immutable artifact-key previews, and submit/outbox mutation behavior unchanged. Push was not performed for #276/#277. / #275 後の続き: #276 では endpoint contract が最大 8 fields を許可するため、generated multi-filter browser UI の前に multi-filter upper-bound smoke を選びました。#277 では 9 つ同時の `filter[field]=value` 条件を fail-closed にする public runtime endpoint smoke coverage を追加し、generated browser UI、URL mirror / replay、sort behavior、endpoint contract、immutable artifact-key preview、submit / outbox mutation behavior は変更しません。#276/#277 は push していません。

Latest continuation after #277: #278 chooses closure for the endpoint multi-filter contract lane before changing generated browser controls. #279 closes the lane with accepted capability: current/alias `runtime-data.json` accepts bounded multi-field `filter[field]=value` queries, returns matching rows with query-result selection semantics, and fails closed above the 8-field limit. Generated browser UI, URL mirror/replay multi-filter handling, typed operators, multi-column sort, endpoint contract changes, immutable artifact-key previews, and submit/outbox mutation behavior remain separate future decisions. Push was not performed for #278/#279. / #277 後の続き: #278 では generated browser controls を変える前に endpoint multi-filter contract lane の closure を選びました。#279 では accepted capability として、current / alias `runtime-data.json` が bounded multi-field `filter[field]=value` query を受け、query-result selection semantics で一致 rows を返し、8-field limit 超過を fail-closed にすることを記録して lane を閉じます。Generated browser UI、URL mirror / replay の multi-filter handling、typed operators、multi-column sort、endpoint contract 変更、immutable artifact-key preview、submit / outbox mutation behavior は将来判断として分離します。#278/#279 は push していません。

Latest continuation after #279: #280 chooses generated browser two-filter controls as the next small UI slice after the endpoint multi-filter contract closure. #281 adds a secondary generated filter field/value control, sends up to two bounded `filter[field]=value` clauses through the existing read-only `runtime-data.json` refresh path, and extends browser/HTML coverage while keeping endpoint contracts, URL replay multi-filter handling, typed operators, multi-column sort, immutable artifact-key previews, and submit/outbox mutation behavior unchanged. Push was not performed for #280/#281. / #279 後の続き: #280 では endpoint multi-filter contract closure 後の次の小さな UI slice として generated browser two-filter controls を選びました。#281 では secondary generated filter field / value control を追加し、既存の read-only `runtime-data.json` refresh 経路で最大 2 つの bounded `filter[field]=value` 条件を送れるようにし、browser / HTML coverage を拡張します。endpoint contract、URL replay の multi-filter handling、typed operators、multi-column sort、immutable artifact-key preview、submit / outbox mutation behavior は変更しません。#280/#281 は push していません。

### Auxiliary Later Review / 補助・後日検討

These are useful candidates, but they are not part of the main minimum path unless a fresh priority decision promotes them. / これらは有用な候補ですが、新しい優先判断で昇格するまでは minimum までの主計画には含めません。

| Order | Work unit / 作業の塊 | Commit unit / コミット単位 | Status | Rough effort / 目安 |
| --- | --- | --- | --- | --- |
| A1 | Mtool implementation namespace cleanup / Mtool 実装 namespace cleanup | Boundary inventory is recorded; no implementation is recommended until a specific helper cluster or maintenance goal is chosen | `PARKED_REPLAN` | Replan first; likely 1 - 3 days after scope is narrowed / まず再計画。scope を絞った後に 1 - 3 日程度 |
| A2 | Mtool admin/lab route authorization hardening / admin・lab route authorization 強化 | Replan only when a concrete deployment need or route cluster is ready | `PARKED_REPLAN` | Re-estimate after route cluster and audit/test scope are chosen / route cluster と audit/test scope 決定後に再見積もり |
| A3 | Mtool config store PostgreSQL support / Mtool config store PostgreSQL 対応 | Treat separately from user DB/generated output PostgreSQL support | `PARKED` | Re-estimate as a config-store portability project / config-store portability project として再見積もり |
| A4 | SQL Server / Oracle current support / SQL Server・Oracle 現行対応 | Enterprise support-scope decision is required first | `PARKED` | Re-estimate after explicit enterprise need is confirmed / 明示的な enterprise need 確認後に再見積もり |
| A5 | Japanese invoice / billing / compliance sample / 日本向け請求・インボイス sample | Domain review is required first | `PARKED` | Re-estimate after domain review / domain review 後に再見積もり |
| A6 | Approval workflow, rollback / revision history, local app packaging / 承認 workflow、rollback・revision、local app packaging | Reopen as separate product/foundation plans after current generated-output and docs lanes settle | `PARKED` | Re-estimate as separate plans when promoted / 昇格時に個別 plan として再見積もり |

Public delivery now covers approved candidate package exposure, artifact-key preview, current preview, explicit current selection, custom alias storage, alias deletion, cache/version policy, rollback wording for the operator/admin surface, browser smoke coverage for artifact/current/alias preview URLs, alias lifecycle audit visibility, a hardening closure report, and local commit cleanup. The next lane is local app packaging; its boundary inventory and first package manifest slice are complete. / public delivery は approved candidate package exposure、artifact-key preview、current preview、explicit current selection、custom public alias storage、alias deletion、cache / version policy、operator / admin surface の rollback wording、artifact / current / alias preview URL の browser smoke coverage、alias lifecycle audit visibility、hardening closure report、local commit cleanup まで到達しました。次の lane は local app packaging で、boundary inventory と最初の package manifest slice は完了済みです。

Latest update: no-code tryout-ready is complete through #67. The tryout-ready commits and status clarification were pushed to `origin/develop`; #69/#70 now records the post-tryout-ready replan and verification before the next push. / 最新更新: no-code tryout-ready は #67 まで完了しています。tryout-ready commits と status clarification は `origin/develop` へ push 済みで、#69/#70 では次 push 前の post-tryout-ready replan と verification を記録します。

## Priority Rationale / 優先理由

The OSS / consulting readiness docs package has been completed as the documentation-first step. / OSS・導入支援 readiness 資料 package は、ドキュメント先行の作業として完了しました。

AI context source output is implemented, verified across all tutorial samples, and available as an implicit default companion documentation output for current/new projects. Mtool self-output verification publishes AI context documentation for Mtool itself. / AI 文脈 Source Output は全 tutorial sample に展開済みで、現在・新規 project へ暗黙 default companion documentation output として提供します。Mtool 自身についても AI 文脈ドキュメントを self-output して検証しています。

The physical/logical sample naming migration, PostgreSQL Input / Output support, and generated PHP output namespace support are complete for their intended support boundaries. Generated PHP output namespace support adds an optional project-level namespace setting for DataClass / DBAccess output, keeps the default namespace-free, and verifies mixed sample coverage. Broader Mtool implementation namespace cleanup remains deferred. / physical / logical sample 命名移行、PostgreSQL Input / Output 対応、generated PHP output namespace 対応は、意図した support boundary では完了です。generated PHP output namespace 対応では、DataClass / DBAccess output 向けの任意 project-level namespace 設定を追加し、default は namespace なしのまま、sample coverage は混在で検証しました。Mtool 実装全体の namespace cleanup は後回しです。

App local DB / sync / no-code app roadmap and feasibility study catalog are drafted as dated reports. During planning, auth foundation was identified as useful beyond that roadmap, so it was promoted out of the FS group into a normal first-slice feature/foundation plan. Mtool auth foundation first slice is complete. Gate 0 core feasibility studies are also complete: Shared Contract Manifest Spike, App Local SQLite Schema Spike, and DTO Save/Read Spike. The FS result supports the original design assumption that generated DataClass and shared contract should be separate artifacts: DataClass remains implementation-facing, while shared contract metadata carries persistence / sync / no-code semantics. Shared Contract Core Vocabulary and Shared DataClass contract foundation are complete: manifest v0 vocabulary, validator, explicit contract metadata tables, DataClass + table metadata manifest generation, DataClass shape compare, `shared-contract-json`, and first `shared-contract-typescript` DTO output are implemented. App-local persistence first demo and Source Output artifact slices are complete through sample27: server row -> DTO -> App-local SQLite save -> App-local SQLite read is verified, and `app-local-persistence-php` now emits schema / manifest / summary / PHP wrapper artifacts. Managed data operation layer first-slice spine is complete through sample07 coverage: canonical operation / operation-field tables, PDO repository, fail-closed policy evaluator, `managed-operation-docs-md` Source Output artifact, plan-only execution adapter, sync intent skeleton, sync outbox lifecycle, App-local executor / handler, server DBAccess executor / handler, project catalog binding, real generated DBAccess coverage, and sample07 managed operation coverage are in place. No-code screen definition and runtime MVP is complete for the minimal steps 1-8 path: `no-code-screen-definition-v0`, the first `no-code-runtime-v0` render/dispatch adapter, `no-code-runtime-json` artifact publishing, sample07 artifact generation/publish verification, persisted operation flow via generated sample07 DBAccess, minimal HTML preview rendering, basic UI smoke, and browser/headless update dispatch smoke are implemented. The first user-facing no-code app MVP sample, `sample28-no-code-data-app-mvp`, is also complete through generated list/detail/form smoke and pack verification. The post-sample28 product-goal replan chose generated runtime UX polish as the next small product-facing slice before broader domain, sync, or operator workflow expansion. That lane is now complete through readable generated titles/subtitles, empty-state copy, browser action feedback, runtime/screen/action state badges, working/success/error feedback states, and refreshed sample07/sample28 smokes. The second data-first no-code domain sample, `sample29-no-code-support-case-demo`, is complete as a first slice: it applies the same generated runtime path to a support-case domain with read-model context fields, no-code metadata, pack/runtime smoke, and browser UI smoke. The first sync-backed no-code demonstration, `sample30-no-code-app-local-sync-demo`, is also complete: generated no-code action intent becomes a managed operation sync intent, enters the sync outbox, and is processed by the App-local SQLite handler. The server-side sync processing follow-up first slice is also complete inside sample30: a second sync outbox item is processed by generated server DBAccess and updates a server SQLite row without adding transport or conflict resolution. / App 内 DB・同期・no-code app roadmap と feasibility study catalog は日付付き report として作成済みです。検討の中で auth 基盤はその roadmap に限定されず通常機能としても有用だと分かったため、FS 群から外して正式な first-slice 計画へ格上げしました。Mtool auth 基盤 first slice は完了済みです。Gate 0 core FS も Shared Contract Manifest Spike、App Local SQLite Schema Spike、DTO Save/Read Spike まで完了し、generated DataClass と shared contract は別 artifact として扱うべき、という元の見立てを支持しました。DataClass は implementation-facing、shared contract metadata は persistence / sync / no-code semantics の正本として扱います。Shared Contract Core Vocabulary と Shared DataClass contract 基盤は完了し、manifest v0 語彙、validator、明示 contract metadata table、DataClass + table metadata からの manifest 生成、DataClass shape compare、`shared-contract-json`、最初の `shared-contract-typescript` DTO output を実装済みです。App-local persistence first demo と Source Output artifact slice は sample27 まで完了し、server row -> DTO -> App-local SQLite save -> App-local SQLite read を検証済み、`app-local-persistence-php` は schema / manifest / summary / PHP wrapper artifact を出力します。Managed data operation layer first-slice spine は sample07 coverage まで完了し、canonical operation / operation-field table、PDO repository、fail-closed policy evaluator、`managed-operation-docs-md` Source Output artifact、plan-only execution adapter、sync intent skeleton、sync outbox lifecycle、App-local executor / handler、server DBAccess executor / handler、project catalog binding、real generated DBAccess coverage、sample07 managed operation coverage を追加済みです。No-code screen definition・runtime MVP は minimal steps 1-8 path として完了し、`no-code-screen-definition-v0`、最初の `no-code-runtime-v0` render/dispatch adapter、`no-code-runtime-json` artifact publishing、sample07 artifact 生成 / publish 検証、generated sample07 DBAccess 経由の persisted operation flow、最小 HTML preview rendering、basic UI smoke、browser/headless update dispatch smoke を実装済みです。最初の user-facing no-code app MVP sample である `sample28-no-code-data-app-mvp` も、generated list/detail/form smoke と pack verification まで完了しました。sample28 後の product-goal replan では、より広い domain / sync / operator workflow 拡張の前に、小さな product-facing slice として generated runtime UX polish を選びました。その lane は readable な generated title/subtitle、empty-state copy、browser action feedback、runtime / screen / action state badge、working / success / error feedback state、sample07/sample28 smoke 更新まで完了しました。2 つ目の data-first no-code domain sample である `sample29-no-code-support-case-demo` は first slice として完了し、read-model context field を持つ support-case domain、no-code metadata、pack/runtime smoke、browser UI smoke で同じ generated runtime path を検証しました。最初の sync-backed no-code demonstration である `sample30-no-code-app-local-sync-demo` も完了し、generated no-code action intent が managed operation sync intent になり、sync outbox に入り、App-local SQLite handler で処理されることを確認しました。server-side sync processing follow-up first slice も sample30 内で完了し、2 件目の sync outbox item を generated server DBAccess で処理して server SQLite row を更新しました。transport や conflict resolution は追加していません。

## Rough Effort Notes / 作業量メモ

These are planning estimates, not deadlines. / これは計画用の目安であり、期限ではありません。

| Order | Work unit / 作業の塊 | Rough effort / 目安 | Note |
| --- | --- | --- | --- |
| 1 | AI context standard rollout / AI 文脈出力の標準展開 | Completed / 完了 | Added AI context output definitions across tutorial samples, regenerated affected references, and locked cross-sample publish coverage in `ZzzAiContextStandardOutputTest`. |
| 2 | AI context default-output transition / AI 文脈出力の default 化 | Completed / 完了 | `AI-CONTEXT-MD` remains the compatibility key, and missing project rows are supplied as an implicit default companion documentation output. |
| 3 | Mtool self-output verification / Mtool 自身の self-output 検証 | Completed / 完了 | Mtool outputs AI context documentation for Mtool itself; the generated documentation is reviewed by test contract as AI-reader context. |
| 4 | Modernization audit MVP generator / 現代化診断 MVP generator | Completed / 完了 | Added deterministic `modernization-audit-md` generation and sample17 `MODERNIZATION-AUDIT-MD` reference coverage. |
| 5 | Goal-based help and wrapper CLI roadmap / 目的別 help と wrapper CLI roadmap | Completed / 完了 | Added `goal-based-help-and-wrapper-cli-roadmap.md` as the design doc for goal help groups and future wrapper CLI command shape. |
| 6 | Physical/logical sample naming migration / physical・logical sample 命名移行 | Completed / 完了 | `sample01`-`sample10` and `sample12`-`sample26` tutorial samples are committed/applied with snake_case physical DB/source names while generated PHP/OpenAPI/proxy class/file names stay stable under `physical-logical-v1`. `sample14` covers the custom proxy step-source boundary; `sample11` has no DB physical-name target. `SamplePhysicalLogicalNamingContractTest` guards tutorial seed SQL schema identifiers, seed physical-name columns, checker physical-name constants, generated reference DBAccess SQL, reference JSON physical-name fields, generated reference and tutorial documentation physical-name text mentions, migrated PHPUnit / CLI check-script policy opt-ins, and sample-run entrypoint classification against regressions. Current tutorial/study docs distinguish physical `snake_case` names from generated surfaces. Do not hand-patch generated artifacts or legacy references. |
| 7 | PostgreSQL Input / Output support / PostgreSQL Input・Output 対応 | Completed / 完了 | PostgreSQL input is covered by live schema import, PostgreSQL output is covered by generated DBAccess / user DB contract comparison, and both are verified through `make postgresql-user-db-test-local`. Mtool config store PostgreSQL support is outside this scope. |
| 8 | PHP output namespace support / generated PHP output namespace 対応 | Completed / 完了 | Added optional project-level PHP namespace support for generated DataClass / DBAccess output. Default output remains namespace-free; sample04 and sample10 cover namespaced output; sample01, sample15, and sample26 cover namespace-free defaults / metadata bundles. Mtool self-output namespace application remains outside this completed lane. Tracking memo: [2026-0627 PHP output namespace support plan](reports/2026/2026-0627-php-output-namespace-plan.md). |
| 9 | Mtool auth foundation first slice / Mtool auth 基盤 first slice | Completed / 完了 | Added `mtool/app/auth_foundation.php` with old `ProjectUser` read/write bit inventory, role-based permission keys, normalized principal shape, and all-pass / fail-closed authorization evaluator. Covered by `AuthFoundationContractTest`. |
| 10 | Gate 0 feasibility studies / Gate 0 FS 群 | Completed / 完了 | Completed Shared Contract Manifest Spike, App Local SQLite Schema Spike, and DTO Save/Read Spike. Result: DataClass can describe generated DTO shape, but shared contract metadata must separately carry nullable / default / key / persistence / sync semantics. |
| 11 | Shared Contract Core Vocabulary / shared contract 最小語彙 | Completed / 完了 | Added shared contract manifest v0 vocabulary, validator, local metadata collision policy, and sample02/task fixture test. |
| 12 | Shared DataClass contract foundation / shared DataClass contract 基盤 | Completed / 完了 | Added explicit shared contract metadata tables/repository, language-neutral contract manifest builder from DataClass + table metadata, DataClass shape compare, `shared-contract-json` source output, and first `shared-contract-typescript` DTO output. |
| 13 | App-local persistence first demo / App-local persistence first demo | Completed / 完了 | Added App-local SQLite schema generator/apply, generic DTO save/read DBAccess helper, and sample27 `server read -> DTO -> app save -> app read` PDO harness. |
| 14 | App-local persistence source output artifacts / App-local persistence Source Output artifacts | Completed / 完了 | Added `app-local-persistence-php` Source Output artifact generation for schema / manifest / summary / PHP wrappers, plus sample27 source output seed and artifact verification. |
| 15 | Managed data operation layer / managed data operation layer | Completed for first-slice spine / first-slice spine 完了 | Operation / operation-field metadata, repository, policy evaluator, generated operation docs artifact, plan-only execution adapter, sync intent skeleton, sync outbox lifecycle, App-local executor / handler, server DBAccess executor / handler, server DBAccess binding discovery, candidate selection, project catalog wiring, real generated DBAccess coverage, and sample07 managed operation coverage are in place. |
| 16 | No-code screen definition and runtime MVP / no-code screen definition・runtime MVP | Completed for minimal steps 1-8 path / minimal steps 1-8 path 完了 | `no-code-screen-definition-v0`, first `no-code-runtime-v0` render/dispatch adapter, `no-code-runtime-json` artifact publishing with HTML preview, sample07 artifact generation/publish verification, a persisted operation flow, basic UI smoke, and browser/headless update dispatch smoke are in place. |
| 17 | No-code app sample / no-code app sample | Completed for first MVP / first MVP 完了 | `sample28-no-code-data-app-mvp` proves the first data-first no-code behavior path through generated list/detail/form smoke and pack verification. |
| 18 | Mtool implementation namespace cleanup / Mtool 実装 namespace cleanup | Replan only / 再計画のみ | Boundary inventory recorded 365 PHP files, about 3152 top-level functions, and about 1238 include lines across the implementation surface. Do not start repo-wide migration without a scoped helper cluster and compatibility shim policy. |
| 19 | Next no-code product goal replan / 次の no-code product goal 再計画 | Completed / 完了 | Chose generated runtime UX polish as the next product-facing no-code goal and promoted it into Quick Plan. Decision report: [2026-0630 Next No-Code Product Goal Replan](reports/2026/2026-0630-next-no-code-product-goal-replan.md). |
| 20 | Generated no-code runtime UX polish first slice / generated no-code runtime UX polish first slice | Completed / 完了 | Generated runtime preview now has readable titles/subtitles, deterministic empty-state copy, browser action feedback, and refreshed sample07/sample28 smoke coverage. Report: [2026-0630 Generated No-Code Runtime UX Polish First Slice](reports/2026/2026-0630-generated-no-code-runtime-ux-polish-first-slice.md). |
| 21 | Generated no-code runtime state polish follow-up / generated no-code runtime state polish follow-up | Completed / 完了 | Runtime preview now exposes ready/error preview state, ready/empty screen state, idle/working/success/error action feedback state, and smoke coverage for generated state attributes. Report: [2026-0630 Generated No-Code Runtime State Polish Follow-Up](reports/2026/2026-0630-generated-no-code-runtime-state-polish-follow-up.md). |
| 22 | Next no-code product goal replan after runtime polish / runtime polish 後の次 no-code product goal 再計画 | Completed / 完了 | Chose Data-first no-code domain sample 2 as the next product-facing implementation. Decision report: [2026-0630 Next No-Code Product Goal After Runtime Polish](reports/2026/2026-0630-next-no-code-product-goal-after-runtime-polish.md). |
| 23 | Data-first no-code domain sample 2 first slice / data-first no-code domain sample 2 first slice | Completed / 完了 | Added `sample29-no-code-support-case-demo` with support-case read-model context, no-code metadata, pack/runtime smoke, and browser UI smoke. Report: [2026-0630 Sample29 No-Code Support Case First Slice](reports/2026/2026-0630-sample29-no-code-support-case-first-slice.md). |
| 24 | Post-sample29 no-code product goal replan / sample29 後の no-code product goal 再計画 | Completed / 完了 | Chose App-local sync no-code demonstration as the next product-facing implementation. Decision report: [2026-0630 Post-Sample29 No-Code Product Goal Replan](reports/2026/2026-0630-post-sample29-no-code-product-goal-replan.md). |
| 25 | App-local sync no-code demonstration first slice / App-local sync no-code demonstration first slice | Completed / 完了 | Added `sample30-no-code-app-local-sync-demo` connecting generated no-code action intent to managed operation sync outbox and App-local SQLite handler. Report: [2026-0630 Sample30 No-Code App-local Sync First Slice](reports/2026/2026-0630-sample30-no-code-app-local-sync-first-slice.md). |
| 26 | Post-sample30 no-code product goal replan / sample30 後の no-code product goal 再計画 | Completed / 完了 | Chose Server-side sync processing follow-up as the next product-facing implementation. Decision report: [2026-0630 Post-Sample30 No-Code Product Goal Replan](reports/2026/2026-0630-post-sample30-no-code-product-goal-replan.md). |
| 27 | Server-side sync processing follow-up first slice / server-side sync processing follow-up first slice | Completed / 完了 | Extended sample30 with generated server DBAccess materialization, binding fallback from the generated method catalog, server outbox handler processing, and server SQLite row verification. Report: [2026-0630 Server-Side Sync Processing Follow-Up First Slice](reports/2026/2026-0630-server-side-sync-processing-follow-up-first-slice.md). |
| 28 | Post-server-side sync no-code product goal replan / server-side sync 後の no-code product goal 再計画 | Completed / 完了 | Chose Reusable partial-update server merge policy as the next product-facing implementation. Decision report: [2026-0630 Post-Server-Side Sync No-Code Product Goal Replan](reports/2026/2026-0630-post-server-side-sync-no-code-product-goal-replan.md). |
| 29 | Reusable partial-update server merge policy first slice / reusable partial-update server merge policy first slice | Completed / 完了 | Added generated server DBAccess partial update merge in the shared executor, removed sample30's sample-specific full-row payload completion, and verified sample30 plus direct server DBAccess coverage. Report: [2026-0630 Reusable Partial-Update Server Merge Policy First Slice](reports/2026/2026-0630-reusable-partial-update-server-merge-policy-first-slice.md). |
| 30 | Post-partial-update merge no-code product goal replan / partial-update merge 後の no-code product goal 再計画 | Completed / 完了 | Chose Sync handoff visibility polish as the next product-facing implementation. Decision report: [2026-0630 Post-Partial-Update Merge No-Code Product Goal Replan](reports/2026/2026-0630-post-partial-update-merge-no-code-product-goal-replan.md). |
| 31 | Sync handoff visibility polish first slice / sync handoff visibility polish first slice | Completed / 完了 | Added generated runtime sync-status hint badges, sample30 App-local/server handoff visibility summary, and focused/full verification. Report: [2026-0630 Sync Handoff Visibility Polish First Slice](reports/2026/2026-0630-sync-handoff-visibility-polish-first-slice.md). |
| 32 | Post-sync handoff visibility no-code product goal replan / sync handoff visibility 後の no-code product goal 再計画 | Completed / 完了 | Chose Operator/admin no-code workflow as the next product-facing implementation. Decision report: [2026-0630 Post-Sync Handoff Visibility No-Code Product Goal Replan](reports/2026/2026-0630-post-sync-handoff-visibility-no-code-product-goal-replan.md). |
| 33 | Operator/admin no-code workflow first slice / operator/admin no-code workflow first slice | Completed / 完了 | Added an inspection-only `NO-CODE-RUNTIME` summary to the existing Source Outputs admin page, backed by a reusable inspection helper and focused PHPUnit coverage. Report: [2026-0630 Operator/Admin No-Code Workflow First Slice](reports/2026/2026-0630-operator-admin-no-code-workflow-first-slice.md). |
| 34 | Post-operator/admin no-code product goal replan / operator/admin no-code 後の product goal 再計画 | Completed / 完了 | Chose Operator preview health/detail links as the next product-facing implementation. Decision report: [2026-0630 Post-Operator/Admin No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-admin-no-code-product-goal-replan.md). |
| 35 | Operator preview health/detail links first slice / operator preview health・detail link first slice | Completed / 完了 | Added health summary and direct definition/detail/download/preview path affordances around existing generated no-code runtime artifacts. Report: [2026-0630 Operator Preview Health Detail Links First Slice](reports/2026/2026-0630-operator-preview-health-detail-links-first-slice.md). |
| 36 | Post-operator preview health no-code product goal replan / operator preview health 後の product goal 再計画 | Completed / 完了 | Chose Operator source-output artifact detail as the next product-facing implementation. Decision report: [2026-0630 Post-Operator Preview Health No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-preview-health-no-code-product-goal-replan.md). |
| 37 | Operator source-output artifact detail first slice / operator source-output artifact detail first slice | Completed / 完了 | Added a read-only artifact detail route/page that summarizes manifest, archive, bundle, runtime source, and download affordance. Report: [2026-0630 Operator Source-Output Artifact Detail First Slice](reports/2026/2026-0630-operator-source-output-artifact-detail-first-slice.md). |
| 38 | Post-operator artifact detail no-code product goal replan / operator artifact detail 後の product goal 再計画 | Completed / 完了 | Chose Sync error-state visibility as the next product-facing implementation. Decision report: [2026-0630 Post-Operator Artifact Detail No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-artifact-detail-no-code-product-goal-replan.md). |
| 39 | Sync error-state visibility first slice / sync error-state visibility first slice | Completed / 完了 | Added one deterministic sample30 failed outbox path using existing status, attempts, and last_error fields. Report: [2026-0630 Sync Error-State Visibility First Slice](reports/2026/2026-0630-sync-error-state-visibility-first-slice.md). |
| 40 | Post-sync error-state visibility no-code product goal replan / sync error-state visibility 後の product goal 再計画 | Completed / 完了 | Chose Operator failed-sync inspection as the next product-facing implementation. Decision report: [2026-0630 Post-Sync Error-State Visibility No-Code Product Goal Replan](reports/2026/2026-0630-post-sync-error-state-visibility-no-code-product-goal-replan.md). |
| 41 | Operator failed-sync inspection first slice / operator failed-sync inspection first slice | Completed / 完了 | Added read-only failed sync outbox inspection to Source Outputs admin using existing status, attempts, and last_error fields. Report: [2026-0630 Operator Failed-Sync Inspection First Slice](reports/2026/2026-0630-operator-failed-sync-inspection-first-slice.md). |
| 42 | Post-operator failed-sync inspection no-code product goal replan / operator failed-sync inspection 後の product goal 再計画 | Completed / 完了 | Chose Operator sync outbox detail as the next product-facing implementation. Decision report: [2026-0630 Post-Operator Failed-Sync Inspection No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-failed-sync-inspection-no-code-product-goal-replan.md). |
| 43 | Operator sync outbox detail first slice / operator sync outbox detail first slice | Completed / 完了 | Added a read-only project-scoped sync outbox item detail page with intent payload and list links. Report: [2026-0630 Operator Sync Outbox Detail First Slice](reports/2026/2026-0630-operator-sync-outbox-detail-first-slice.md). |
| 44 | Post-operator sync outbox detail no-code product goal replan / operator sync outbox detail 後の product goal 再計画 | Completed / 完了 | Chose Sync retry eligibility guard as the next product-facing implementation. Decision report: [2026-0630 Post-Operator Sync Outbox Detail No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-sync-outbox-detail-no-code-product-goal-replan.md). |
| 45 | Sync retry eligibility guard first slice / sync retry eligibility guard first slice | Completed / 完了 | Added a fail-closed retry eligibility helper and exposed its read-only decision in operator detail. Report: [2026-0630 Sync Retry Eligibility Guard First Slice](reports/2026/2026-0630-sync-retry-eligibility-guard-first-slice.md). |
| 46 | Post-sync retry eligibility guard no-code product goal replan / sync retry eligibility guard 後の product goal 再計画 | Completed / 完了 | Chose Operator sync retry action as the next product-facing implementation. Decision report: [2026-0630 Post-Sync Retry Eligibility Guard No-Code Product Goal Replan](reports/2026/2026-0630-post-sync-retry-eligibility-guard-no-code-product-goal-replan.md). |
| 47 | Operator sync retry action first slice / operator sync retry action first slice | Completed / 完了 | Added a narrow operator POST action that requeues eligible failed sync outbox items to pending without processing them inline. Report: [2026-0630 Operator Sync Retry Action First Slice](reports/2026/2026-0630-operator-sync-retry-action-first-slice.md). |
| 48 | Post-operator sync retry action no-code product goal replan / operator sync retry action 後の product goal 再計画 | Completed / 完了 | Chose Retry processing smoke as the next product-facing confidence step. Decision report: [2026-0630 Post-Operator Sync Retry Action No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-sync-retry-action-no-code-product-goal-replan.md). |
| 49 | Retry processing smoke first slice / retry processing smoke first slice | Completed / 完了 | Proved a requeued pending item is picked up by the existing processor path without adding scheduler, transport, or conflict resolution. Report: [2026-0630 Retry Processing Smoke First Slice](reports/2026/2026-0630-retry-processing-smoke-first-slice.md). |
| 50 | Post-retry processing smoke no-code product goal replan / retry processing smoke 後の product goal 再計画 | Completed / 完了 | Chose Operator retry feedback polish as the next small product-facing implementation. Decision report: [2026-0630 Post-Retry Processing Smoke No-Code Product Goal Replan](reports/2026/2026-0630-post-retry-processing-smoke-no-code-product-goal-replan.md). |
| 51 | Operator retry feedback polish first slice / operator retry feedback polish first slice | Completed / 完了 | Made the post-requeue operator result and next processor step clearer without adding scheduler, transport, or audit tables. Report: [2026-0630 Operator Retry Feedback Polish First Slice](reports/2026/2026-0630-operator-retry-feedback-polish-first-slice.md). |
| 52 | Post-operator retry feedback polish no-code product goal replan / operator retry feedback polish 後の product goal 再計画 | Completed / 完了 | Chose No-code runtime error/retry visibility as the next product-facing implementation. Decision report: [2026-0630 Post-Operator Retry Feedback Polish No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-retry-feedback-polish-no-code-product-goal-replan.md). |
| 53 | No-code runtime error/retry visibility first slice / no-code runtime error/retry visibility first slice | Completed / 完了 | Surfaced failed/retryable sync state in generated runtime artifacts without adding retry mutation there. Report: [2026-0630 No-Code Runtime Error/Retry Visibility First Slice](reports/2026/2026-0630-no-code-runtime-error-retry-visibility-first-slice.md). |
| 54 | React-first no-code Web framework bridge FS / React-first no-code Web framework bridge FS | Completed / 完了 | Chose React + TypeScript as the first adapter direction, with Vue / Svelte as comparison references. FS plan: [2026-0630 React-first no-code Web framework bridge FS plan](reports/2026/2026-0630-react-first-no-code-web-framework-bridge-fs-plan.md). |
| 55 | React-first no-code Web framework bridge first slice / React-first no-code Web framework bridge first slice | Completed / 完了 | Added `no-code-react-bridge`, `NoCodeReactBridge`, sample28 `NO-CODE-REACT-BRIDGE`, and focused/sample28 verification. Report: [2026-0630 React-first No-Code Web Framework Bridge First Slice](reports/2026/2026-0630-react-first-no-code-web-framework-bridge-first-slice.md). |
| 56 | React bridge build smoke first slice / React bridge build smoke first slice | Completed / 完了 | Added `make sample28-no-code-react-bridge-build-smoke` and a reusable Node smoke that installs/builds the generated React bridge scaffold in `work/tmp`. Report: [2026-0630 React Bridge Build Smoke First Slice](reports/2026/2026-0630-react-bridge-build-smoke-first-slice.md). |
| 57 | Post-React bridge build smoke no-code product goal replan / React bridge build smoke 後の no-code product goal 再計画 | Completed / 完了 | Chose React bridge browser smoke as the next smallest confidence step after build smoke. |
| 58 | React bridge browser smoke first slice / React bridge browser smoke first slice | Completed / 完了 | Added `make sample28-no-code-react-bridge-browser-smoke` and a reusable Node/Playwright smoke that renders the generated React bridge in headless Chrome. Report: [2026-0630 React Bridge Browser Smoke First Slice](reports/2026/2026-0630-react-bridge-browser-smoke-first-slice.md). |
| 59 | Post-React bridge browser smoke no-code product goal replan / React bridge browser smoke 後の no-code product goal 再計画 | Completed / 完了 | Chose React bridge display/form state shaping after browser smoke exposed raw runtime cell display text. |
| 60 | React bridge display/form state shaping first slice / React bridge display/form state shaping first slice | Completed / 完了 | Added generated React bridge helpers for runtime cell display and action-intent input normalization. Report: [2026-0701 React Bridge Display/Form State Shaping First Slice](reports/2026/2026-0701-react-bridge-display-form-state-shaping-first-slice.md). |
| 61 | Post-React bridge display/form state shaping no-code product goal replan / React bridge display/form state shaping 後の no-code product goal 再計画 | Completed / 完了 | Chose React bridge artifact contract hardening as the next confidence step after render/input helper behavior stabilized. |
| 62 | React bridge artifact contract hardening first slice / React bridge artifact contract hardening first slice | Completed / 完了 | Added `contract_schema_version`, `contract_invariants`, and build/browser/PHP coverage for React bridge artifact invariants. Report: [2026-0701 React Bridge Artifact Contract Hardening First Slice](reports/2026/2026-0701-react-bridge-artifact-contract-hardening-first-slice.md). |
| 63 | Post-React bridge artifact contract hardening no-code product goal replan / React bridge artifact contract hardening 後の no-code product goal 再計画 | Completed / 完了 | Chose Editable React bridge form state first slice as the next product-facing continuation. Report: [2026-0701 Post-React Bridge Artifact Contract Hardening No-Code Product Goal Replan](reports/2026/2026-0701-post-react-bridge-artifact-contract-hardening-no-code-product-goal-replan.md). |
| 64 | Editable React bridge form state first slice / editable React bridge form state first slice | Completed / 完了 | Generated React bridge inputs now manage local edit state and emit changed scalar values in action intents. Report: [2026-0701 Editable React Bridge Form State First Slice](reports/2026/2026-0701-editable-react-bridge-form-state-first-slice.md). |
| 65 | Post-editable React bridge form state no-code product goal replan / editable React bridge form state 後の no-code product goal 再計画 | Completed / 完了 | Chose React bridge validation hint display as the next small product-facing continuation. Report: [2026-0701 Post-Editable React Bridge Form State No-Code Product Goal Replan](reports/2026/2026-0701-post-editable-react-bridge-form-state-no-code-product-goal-replan.md). |
| 66 | React bridge validation hint display first slice / React bridge validation hint display first slice | Completed / 完了 | Generated React bridge form fields now display existing required/readonly metadata as lightweight hints. Report: [2026-0701 React Bridge Validation Hint Display First Slice](reports/2026/2026-0701-react-bridge-validation-hint-display-first-slice.md). |
| 67 | Post-React bridge validation hint display no-code product goal replan / React bridge validation hint display 後の no-code product goal 再計画 | Completed / 完了 | Chose React bridge action feedback display as the next small product-facing continuation. Report: [2026-0701 Post-React Bridge Validation Hint Display No-Code Product Goal Replan](reports/2026/2026-0701-post-react-bridge-validation-hint-display-no-code-product-goal-replan.md). |
| 68 | React bridge action feedback display first slice / React bridge action feedback display first slice | Completed / 完了 | Generated React bridge now displays local last-intent feedback after an action intent is created. Report: [2026-0701 React Bridge Action Feedback Display First Slice](reports/2026/2026-0701-react-bridge-action-feedback-display-first-slice.md). |
| 69 | Post-React bridge action feedback display no-code product goal replan / React bridge action feedback display 後の no-code product goal 再計画 | Completed / 完了 | Chose JSON Forms / rjsf transform probe as the next comparison step after the custom bridge proved display/edit/metadata/feedback. Report: [2026-0701 Post-React Bridge Action Feedback Display No-Code Product Goal Replan](reports/2026/2026-0701-post-react-bridge-action-feedback-display-no-code-product-goal-replan.md). |
| 70 | JSON Forms / rjsf transform probe first slice / JSON Forms・rjsf transform probe first slice | Completed / 完了 | Added `no-code-json-forms-probe`, sample28 `NO-CODE-JSON-FORMS-PROBE`, schema-form contract, JSON Schema, UI Schema, checker/foundation coverage. Report: [2026-0701 JSON Forms / rjsf Transform Probe First Slice](reports/2026/2026-0701-json-forms-rjsf-transform-probe-first-slice.md). |
| 71 | Post-JSON Forms / rjsf transform probe no-code product goal replan / JSON Forms・rjsf transform probe 後の no-code product goal 再計画 | Completed / 完了 | Chose React bridge contract documentation polish as the next small product-facing cleanup after custom React and schema-form comparison artifacts both existed. |
| 72 | React bridge contract documentation polish first slice / React bridge contract documentation polish first slice | Completed / 完了 | Added generated `CONSUMER-NOTES.md`, structured `consumer_notes`, required-file invariant coverage, and sample28/foundation assertions. Report: [2026-0701 React Bridge Contract Documentation Polish First Slice](reports/2026/2026-0701-react-bridge-contract-documentation-polish-first-slice.md). |
| 73 | Post-React bridge contract documentation polish no-code product goal replan / React bridge contract documentation polish 後の no-code product goal 再計画 | Completed / 完了 | Chose Schema-form probe hardening as the next continuation after generated consumer boundaries were documented. |
| 74 | Schema-form probe hardening first slice / schema-form probe hardening first slice | Completed / 完了 | Added Mtool extension metadata to JSON Schema properties, UI Schema options, field mappings, and contract invariants. Report: [2026-0701 Schema-Form Probe Hardening First Slice](reports/2026/2026-0701-schema-form-probe-hardening-first-slice.md). |
| 75 | Post-schema-form probe hardening no-code product goal replan / schema-form probe hardening 後の no-code product goal 再計画 | Completed / 完了 | Chose Schema-form runtime smoke as the next confidence slice after schema-form probe hardening. Report: [2026-0701 Post-Schema-Form Probe Hardening No-Code Product Goal Replan](reports/2026/2026-0701-post-schema-form-probe-hardening-no-code-product-goal-replan.md). |
| 76 | Schema-form runtime smoke first slice / schema-form runtime smoke first slice | Completed / 完了 | Added a focused rjsf SSR smoke for sample28 schema-form probe artifacts. Report: [2026-0701 Schema-Form Runtime Smoke First Slice](reports/2026/2026-0701-schema-form-runtime-smoke-first-slice.md). |
| 77 | Post-schema-form runtime smoke no-code product goal replan / schema-form runtime smoke 後の no-code product goal 再計画 | Completed / 完了 | Chose Schema-form consumer notes as the next handoff slice after schema-form runtime smoke. Report: [2026-0701 Post-Schema-Form Runtime Smoke No-Code Product Goal Replan](reports/2026/2026-0701-post-schema-form-runtime-smoke-no-code-product-goal-replan.md). |
| 78 | Schema-form consumer notes first slice / schema-form consumer notes first slice | Completed / 完了 | Added generated `CONSUMER-NOTES.md`, structured `consumer_notes`, required-file invariant coverage, and sample28/foundation assertions. Report: [2026-0701 Schema-Form Consumer Notes First Slice](reports/2026/2026-0701-schema-form-consumer-notes-first-slice.md). |
| 79 | Post-schema-form consumer notes no-code product goal replan / schema-form consumer notes 後の no-code product goal 再計画 | Completed / 完了 | Chose Generated runtime visual polish follow-up after adapter confidence and handoff docs stabilized. Report: [2026-0701 Post-Schema-Form Consumer Notes No-Code Product Goal Replan](reports/2026/2026-0701-post-schema-form-consumer-notes-no-code-product-goal-replan.md). |
| 80 | Generated runtime visual polish follow-up first slice / generated runtime visual polish follow-up first slice | Completed / 完了 | Added compact field/action/screen-key summaries to generated runtime preview screens. Report: [2026-0701 Generated Runtime Visual Polish Follow-Up First Slice](reports/2026/2026-0701-generated-runtime-visual-polish-follow-up-first-slice.md). |
| 81 | Post-generated runtime visual polish follow-up no-code product goal replan / generated runtime visual polish follow-up 後の no-code product goal 再計画 | Completed / 完了 | Chose Runtime preview accessibility polish after visible runtime scanability was improved. Report: [2026-0701 Post-Generated Runtime Visual Polish Follow-Up No-Code Product Goal Replan](reports/2026/2026-0701-post-generated-runtime-visual-polish-follow-up-no-code-product-goal-replan.md). |
| 82 | Runtime preview accessibility polish first slice / runtime preview accessibility polish first slice | Completed / 完了 | Added generated preview landmarks, labelled screen regions, action nav labels, and list table captions. Report: [2026-0701 Runtime Preview Accessibility Polish First Slice](reports/2026/2026-0701-runtime-preview-accessibility-polish-first-slice.md). |
| 83 | Post-runtime preview accessibility polish no-code product goal replan / runtime preview accessibility polish 後の no-code product goal 再計画 | Completed / 完了 | Chose React bridge/schema-form artifact parity notes after runtime accessibility polish. Report: [2026-0701 Post-Runtime Preview Accessibility Polish No-Code Product Goal Replan](reports/2026/2026-0701-post-runtime-preview-accessibility-polish-no-code-product-goal-replan.md). |
| 84 | React bridge/schema-form artifact parity notes first slice / React bridge・schema-form artifact parity notes first slice | Completed / 完了 | Added generated parity notes to React bridge and JSON Forms/rjsf probe consumer notes/contracts. Report: [2026-0701 React Bridge Schema-Form Artifact Parity Notes First Slice](reports/2026/2026-0701-react-bridge-schema-form-artifact-parity-notes-first-slice.md). |
| 85 | Post-artifact parity notes no-code product goal replan / artifact parity notes 後の no-code product goal 再計画 | Completed / 完了 | Chose Adapter artifact checklist note as the next handoff clarity continuation. Report: [2026-0701 Post-Artifact Parity Notes No-Code Product Goal Replan](reports/2026/2026-0701-post-artifact-parity-notes-no-code-product-goal-replan.md). |
| 86 | Adapter artifact checklist notes first slice / adapter artifact checklist notes first slice | Completed / 完了 | Added generated handoff checklists with required files, stable markers, and smoke commands to React bridge and JSON Forms/rjsf probe consumer notes/contracts. Report: [2026-0701 Adapter Artifact Checklist Notes First Slice](reports/2026/2026-0701-adapter-artifact-checklist-notes-first-slice.md). |
| 87 | Post-adapter checklist notes no-code product goal replan / adapter checklist notes 後の no-code product goal 再計画 | Completed / 完了 | Chose Adapter artifact troubleshooting notes as the next handoff clarity continuation. Report: [2026-0701 Post-Adapter Checklist Notes No-Code Product Goal Replan](reports/2026/2026-0701-post-adapter-checklist-notes-no-code-product-goal-replan.md). |
| 88 | Adapter artifact troubleshooting notes first slice / adapter artifact troubleshooting notes first slice | Completed / 完了 | Added generated troubleshooting notes for common React bridge and JSON Forms/rjsf probe handoff failures. Report: [2026-0701 Adapter Artifact Troubleshooting Notes First Slice](reports/2026/2026-0701-adapter-artifact-troubleshooting-notes-first-slice.md). |
| 89 | Post-adapter troubleshooting notes no-code product goal replan / adapter troubleshooting notes 後の no-code product goal 再計画 | Completed / 完了 | Chose Adapter consumer doc index note as the next handoff docs finalization slice. Report: [2026-0701 Post-Adapter Troubleshooting Notes No-Code Product Goal Replan](reports/2026/2026-0701-post-adapter-troubleshooting-notes-no-code-product-goal-replan.md). |
| 90 | Adapter consumer doc index notes first slice / adapter consumer doc index notes first slice | Completed / 完了 | Added generated documentation index notes linking parity, checklist, troubleshooting, and contract sections. Report: [2026-0701 Adapter Consumer Doc Index Notes First Slice](reports/2026/2026-0701-adapter-consumer-doc-index-notes-first-slice.md). |
| 91 | Post-adapter doc index notes no-code product goal replan / adapter doc index notes 後の no-code product goal 再計画 | Completed / 完了 | Chose Adapter docs completion report as the short closure step after adapter doc index notes. Report: [2026-0701 Post-Adapter Doc Index Notes No-Code Product Goal Replan](reports/2026/2026-0701-post-adapter-doc-index-notes-no-code-product-goal-replan.md). |
| 92 | Adapter docs completion report first slice / adapter docs completion report first slice | Completed / 完了 | Recorded the completed adapter handoff docs package and remaining boundaries. Report: [2026-0701 Adapter Docs Completion Report First Slice](reports/2026/2026-0701-adapter-docs-completion-report-first-slice.md). |
| 93 | Runtime preview keyboard/action affordance polish first slice / runtime preview keyboard・action affordance polish first slice | Completed / 完了 | Added generated action affordance markers, keyboard activation hints, disabled action reasons, and smoke/checker coverage. Report: [2026-0701 Runtime Preview Keyboard/Action Affordance Polish First Slice](reports/2026/2026-0701-runtime-preview-keyboard-action-affordance-polish-first-slice.md). |
| 94 | Post-runtime preview keyboard/action affordance polish no-code product goal replan / runtime preview keyboard・action affordance polish 後の no-code product goal 再計画 | Completed / 完了 | Chose Retry audit trail as the next accountability slice. Report: [2026-0701 Post-Runtime Preview Keyboard/Action Affordance Polish No-Code Product Goal Replan](reports/2026/2026-0701-post-runtime-preview-keyboard-action-affordance-polish-no-code-product-goal-replan.md). |
| 95 | Retry audit trail first slice / retry audit trail first slice | Completed / 完了 | Added `sync_outbox.retry_requeued` audit event for successful operator retry requeue. Report: [2026-0701 Retry Audit Trail First Slice](reports/2026/2026-0701-retry-audit-trail-first-slice.md). |
| 96 | Post-retry audit trail no-code product goal replan / retry audit trail 後の no-code product goal 再計画 | Completed / 完了 | Chose Retry audit display follow-up as the next visibility slice. Report: [2026-0701 Post-Retry Audit Trail No-Code Product Goal Replan](reports/2026/2026-0701-post-retry-audit-trail-no-code-product-goal-replan.md). |
| 97 | Retry audit display follow-up first slice / retry audit display follow-up first slice | Completed / 完了 | Surfaced recent retry audit events on the sync outbox detail page. Report: [2026-0701 Retry Audit Display Follow-Up First Slice](reports/2026/2026-0701-retry-audit-display-follow-up-first-slice.md). |
| 98 | Operator/admin no-code workflow polish first slice / operator・admin no-code workflow polish first slice | Completed / 完了 | Added Operator Workflow Checklist to the `NO-CODE-RUNTIME` source-output inspection summary. Report: [2026-0701 Operator/Admin No-Code Workflow Polish First Slice](reports/2026/2026-0701-operator-admin-no-code-workflow-polish-first-slice.md). |
| 99 | Post-operator/admin workflow no-code product goal replan / operator・admin workflow 後の no-code product goal 再計画 | Completed / 完了 | Chose No-code minimum closure report as the next mainline step. Report: [2026-0701 Post-Operator/Admin Workflow No-Code Product Goal Replan](reports/2026/2026-0701-post-operator-admin-workflow-no-code-product-goal-replan.md). |
| 100 | No-code minimum closure report first slice / no-code minimum closure report first slice | Completed / 完了 | Recorded the generated runtime, adapter, sync/retry, and operator/admin inspection surfaces as the current minimum milestone. Report: [2026-0701 No-Code Minimum Closure Report First Slice](reports/2026/2026-0701-no-code-minimum-closure-report-first-slice.md). |
| 101 | Post-no-code minimum closure product goal replan / no-code minimum closure 後の product goal 再計画 | Completed / 完了 | Chose Commit hygiene / worktree closure before starting another broad implementation lane. Report: [2026-0701 Post-No-Code Minimum Closure Product Goal Replan](reports/2026/2026-0701-post-no-code-minimum-closure-product-goal-replan.md). |
| 102 | Worktree closure commit hygiene first slice / worktree closure commit hygiene first slice | Completed / 完了 | Reviewed the accumulated worktree and prepared meaning-sized commit group recommendations without staging, committing, or pushing. Report: [2026-0701 Worktree Closure Commit Hygiene First Slice](reports/2026/2026-0701-worktree-closure-commit-hygiene-first-slice.md). |
| 103 | Commit group execution decision / commit group 実行判断 | Completed / 完了 | Created two local commits after `make test`; no push was performed. Report: [2026-0701 Commit Group Execution Decision](reports/2026/2026-0701-commit-group-execution-decision.md). |
| 104 | Post-commit no-code product goal replan / commit 後の no-code product goal 再計画 | Completed / 完了 | Chose deeper runtime capability, starting with generated required validation enforcement. Report: [2026-0701 Post-Commit No-Code Product Goal Replan](reports/2026/2026-0701-post-commit-no-code-product-goal-replan.md). |
| 105 | Generated required validation enforcement first slice / generated required validation enforcement first slice | Completed / 完了 | Treat blank required values as missing in PHP/browser action-intent builders and cover sample28 browser smoke. Report: [2026-0701 Generated Required Validation Enforcement First Slice](reports/2026/2026-0701-generated-required-validation-enforcement-first-slice.md). |
| 106 | Post-required-validation no-code product goal replan / required validation 後の no-code product goal 再計画 | Completed / 完了 | Chose React bridge required enforcement parity after generated runtime required enforcement. Report: [2026-0701 Post-Required-Validation No-Code Product Goal Replan](reports/2026/2026-0701-post-required-validation-no-code-product-goal-replan.md). |
| 107 | React bridge required enforcement parity first slice / React bridge required enforcement parity first slice | Completed / 完了 | Added blank required fail-close behavior to generated React bridge action-intent helper and browser smoke. Report: [2026-0701 React Bridge Required Enforcement Parity First Slice](reports/2026/2026-0701-react-bridge-required-enforcement-parity-first-slice.md). |
| 108 | Post-React-bridge-required-enforcement no-code product goal replan / React bridge required enforcement 後の no-code product goal 再計画 | Completed / 完了 | Chose validation feedback polish after React bridge required enforcement parity. Report: [2026-0701 Post-React-Bridge Required Enforcement No-Code Product Goal Replan](reports/2026/2026-0701-post-react-bridge-required-enforcement-no-code-product-goal-replan.md). |
| 109 | Validation feedback polish first slice / validation feedback polish first slice | Completed / 完了 | Added display-ready validation messages while preserving raw error codes. Report: [2026-0701 Validation Feedback Polish First Slice](reports/2026/2026-0701-validation-feedback-polish-first-slice.md). |
| 110 | Post-validation-feedback no-code product goal replan / validation feedback 後の no-code product goal 再計画 | Completed / 完了 | Chose schema-form validation parity check after validation feedback polish. Report: [2026-0701 Post-Validation-Feedback No-Code Product Goal Replan](reports/2026/2026-0701-post-validation-feedback-no-code-product-goal-replan.md). |
| 111 | Schema-form validation parity check first slice / schema-form validation parity check first slice | Completed with verification gap / 検証 gap 付き完了 | Added blank-required string metadata and rjsf temporary-probe smoke coverage; Docker-backed sample smoke/full test blocked by unavailable Docker daemon. Report: [2026-0701 Schema-Form Validation Parity Check First Slice](reports/2026/2026-0701-schema-form-validation-parity-check-first-slice.md). |
| 112 | Post-schema-form-validation-parity no-code product goal replan / schema-form validation parity 後の no-code product goal 再計画 | Completed / 完了 | Docker-backed verification rerun remains blocked; chose no-code product surface boundary inventory as a docs-only continuation. Report: [2026-0701 Post-Schema-Form Validation Parity No-Code Product Goal Replan](reports/2026/2026-0701-post-schema-form-validation-parity-no-code-product-goal-replan.md). |
| 113 | No-code product surface boundary inventory first slice / no-code product surface boundary inventory first slice | Completed / 完了 | Narrowed the larger product-surface lane to published no-code runtime artifact selection. Report: [2026-0701 No-Code Product Surface Boundary Inventory First Slice](reports/2026/2026-0701-no-code-product-surface-boundary-inventory-first-slice.md). |
| 114 | Published no-code runtime artifact selection first slice / published no-code runtime artifact selection first slice | Completed with verification gap / 検証 gap 付き完了 | Added read-only `publish_readiness` to no-code inspection and Source Outputs display. Docker-backed focused/full verification blocked by unavailable Docker daemon. Report: [2026-0701 Published No-Code Runtime Artifact Selection First Slice](reports/2026/2026-0701-published-no-code-runtime-artifact-selection-first-slice.md). |
| 115 | Post-published-no-code-runtime-artifact-selection no-code product goal replan / published no-code runtime artifact selection 後の no-code product goal 再計画 | Completed / 完了 | Docker-backed verification rerun remains blocked; chose approval / revision history boundary inventory as a docs-only continuation. Report: [2026-0701 Post-Published No-Code Runtime Artifact Selection Product Goal Replan](reports/2026/2026-0701-post-published-no-code-runtime-artifact-selection-no-code-product-goal-replan.md). |
| 116 | Approval / revision history boundary inventory first slice / approval・revision history boundary inventory first slice | Completed / 完了 | Narrowed the first mutation-capable product surface to publish candidate revision records. Report: [2026-0701 Approval / Revision History Boundary Inventory First Slice](reports/2026/2026-0701-approval-revision-history-boundary-inventory-first-slice.md). |
| 117 | Publish candidate revision record replan / publish candidate revision record 再計画 | Completed / 完了 | Docker-backed verification rerun remains blocked; chose revision record schema/docs only before mutation code. Report: [2026-0701 Publish Candidate Revision Record Replan](reports/2026/2026-0701-publish-candidate-revision-record-replan.md). |
| 118 | Publish candidate revision record schema contract first slice / publish candidate revision record schema contract first slice | Completed / 完了 | Defined the durable candidate revision record, repository boundary, UI boundary, and verification requirements without code changes. Report: [2026-0701 Publish Candidate Revision Record Schema Contract First Slice](reports/2026/2026-0701-publish-candidate-revision-record-schema-contract-first-slice.md). |
| 119 | Post-publish-candidate-revision-schema no-code product goal replan / publish candidate revision schema 後の no-code product goal 再計画 | Completed / 完了 | Docker rerun remains blocked; chose approval transition planning as a docs-only continuation. Report: [2026-0701 Post-Publish-Candidate-Revision Schema No-Code Product Goal Replan](reports/2026/2026-0701-post-publish-candidate-revision-schema-no-code-product-goal-replan.md). |
| 120 | Approval transition state model first slice / approval transition state model first slice | Completed / 完了 | Defined candidate approval states, allowed/blocked transitions, transition event contract, and UI/verification boundaries. Report: [2026-0701 Approval Transition State Model First Slice](reports/2026/2026-0701-approval-transition-state-model-first-slice.md). |
| 121 | Post-approval-transition-state-model no-code product goal replan / approval transition state model 後の no-code product goal 再計画 | Completed / 完了 | Docker-backed verification rerun remains blocked; chose approval action UI contract as a docs-only continuation. Report: [2026-0701 Post-Approval Transition State Model No-Code Product Goal Replan](reports/2026/2026-0701-post-approval-transition-state-model-no-code-product-goal-replan.md). |
| 122 | Approval action UI contract first slice / approval action UI contract first slice | Completed / 完了 | Defined future request-review / approve / reject / supersede UI actions, availability inputs, blocked reasons, and request contract without code changes. Report: [2026-0701 Approval Action UI Contract First Slice](reports/2026/2026-0701-approval-action-ui-contract-first-slice.md). |
| 123 | Post-approval-action-UI-contract no-code product goal replan / approval action UI contract 後の no-code product goal 再計画 | Completed / 完了 | Docker-backed verification remains blocked; chose approval route/test implementation plan as a docs-only continuation. Report: [2026-0701 Post-Approval-Action-UI-Contract No-Code Product Goal Replan](reports/2026/2026-0701-post-approval-action-ui-contract-no-code-product-goal-replan.md). |
| 124 | Approval route/test implementation plan first slice / approval route・test implementation plan first slice | Completed / 完了 | Defined route names, request shapes, repository boundaries, focused tests, route/source-contract tests, and verification gate before code. Report: [2026-0701 Approval Route/Test Implementation Plan First Slice](reports/2026/2026-0701-approval-route-test-implementation-plan-first-slice.md). |
| 125 | Post-approval-route/test-plan no-code product goal replan / approval route・test plan 後の no-code product goal 再計画 | Completed / 完了 | Docker-backed verification rerun remains blocked; chose publish candidate persistence implementation checklist as a docs-only continuation. Report: [2026-0701 Post-Approval Route/Test Plan No-Code Product Goal Replan](reports/2026/2026-0701-post-approval-route-test-plan-no-code-product-goal-replan.md). |
| 126 | Publish candidate persistence implementation checklist first slice / publish candidate persistence implementation checklist first slice | Completed / 完了 | Defined helper signatures, storage fields, focused tests, route/source-contract tests, and verification gate before code. Report: [2026-0701 Publish Candidate Persistence Implementation Checklist First Slice](reports/2026/2026-0701-publish-candidate-persistence-implementation-checklist-first-slice.md). |
| 127 | Post-candidate-persistence-checklist no-code product goal replan / candidate persistence checklist 後の no-code product goal 再計画 | Completed / 完了 | Docker-backed verification rerun remains blocked; chose docs-only migration/source-contract checklist. Report: [2026-0702 Post-Candidate-Persistence-Checklist No-Code Product Goal Replan](reports/2026/2026-0702-post-candidate-persistence-checklist-no-code-product-goal-replan.md). |
| 128 | Publish candidate migration/source-contract checklist first slice / publish candidate migration・source-contract checklist first slice | Completed / 完了 | Defined table columns, indexes, helper contracts, source-contract checks, and verification gate before code. Report: [2026-0702 Publish Candidate Migration Source-Contract Checklist First Slice](reports/2026/2026-0702-publish-candidate-migration-source-contract-checklist-first-slice.md). |
| 129 | Post-candidate-migration-checklist no-code product goal replan / candidate migration checklist 後の no-code product goal 再計画 | Completed / 完了 | Chose repository/API contract test matrix as the final docs-only pre-implementation slice while Docker verification was still blocked. Report: [2026-0702 Post-Candidate-Migration-Checklist No-Code Product Goal Replan](reports/2026/2026-0702-post-candidate-migration-checklist-no-code-product-goal-replan.md). |
| 130 | Publish candidate repository/API contract test matrix first slice / publish candidate repository・API contract test matrix first slice | Completed / 完了 | Defined repository create/list/find tests, fail-closed cases, Source Outputs gates, fixtures, and verification gate. Report: [2026-0702 Publish Candidate Repository API Contract Test Matrix First Slice](reports/2026/2026-0702-publish-candidate-repository-api-contract-test-matrix-first-slice.md). |
| 131 | Docker-backed verification rerun closure / Docker-backed verification rerun closure | Completed / 完了 | Reran schema-form sample smoke and full `make test` after Docker restart. Report: [2026-0702 Docker-Backed Verification Rerun Closure](reports/2026/2026-0702-docker-backed-verification-rerun-closure.md). |
| 132 | Post-verification-closure no-code product goal replan / verification closure 後の no-code product goal 再計画 | Completed / 完了 | Chose minimal candidate persistence as the smallest code slice after Docker-backed verification passed. |
| 133 | Minimal publish candidate persistence first slice / minimal publish candidate persistence first slice | Completed / 完了 | Added durable draft candidate revisions from publishable `NO-CODE-RUNTIME` readiness snapshots with scoped list/find repository tests. Report: [2026-0702 Minimal Publish Candidate Persistence First Slice](reports/2026/2026-0702-minimal-publish-candidate-persistence-first-slice.md). |
| 134 | Post-minimal-candidate-persistence no-code product goal replan / minimal candidate persistence 後の no-code product goal 再計画 | Completed / 完了 | Chose approval transition persistence as the next repository-tested product-surface continuation. Report: [2026-0702 Post-Minimal-Candidate-Persistence No-Code Product Goal Replan](reports/2026/2026-0702-post-minimal-candidate-persistence-no-code-product-goal-replan.md). |
| 135 | Approval transition persistence first slice / approval transition persistence first slice | Completed / 完了 | Added append-only transition events and repository helper for `request_review`, `approve`, and `reject`, with Docker-backed SQLite coverage. Report: [2026-0702 Approval Transition Persistence First Slice](reports/2026/2026-0702-approval-transition-persistence-first-slice.md). |
| 136 | Post-approval-transition-persistence no-code product goal replan / approval transition persistence 後の no-code product goal 再計画 | Completed / 完了 | Chose guarded publish candidate detail UI as the next product-surface continuation. Report: [2026-0702 Post-Approval-Transition-Persistence No-Code Product Goal Replan](reports/2026/2026-0702-post-approval-transition-persistence-no-code-product-goal-replan.md). |
| 137 | Guarded publish candidate detail UI first slice / guarded publish candidate detail UI first slice | Completed / 完了 | Added NO-CODE-RUNTIME detail-page candidate creation, candidate history, and guarded transition actions. Report: [2026-0702 Guarded Publish Candidate Detail UI First Slice](reports/2026/2026-0702-guarded-publish-candidate-detail-ui-first-slice.md). |
| 138 | Post-guarded-candidate-UI no-code product goal replan / guarded candidate UI 後の no-code product goal 再計画 | Completed / 完了 | Chose approved candidate package exposure as the next narrow product-surface continuation. Report: [2026-0702 Post-Guarded-Candidate-UI No-Code Product Goal Replan](reports/2026/2026-0702-post-guarded-candidate-ui-no-code-product-goal-replan.md). |
| 139 | Approved candidate package exposure first slice / approved candidate package exposure first slice | Completed / 完了 | Exposed existing artifact detail/download links only for approved candidates and kept public runtime URL deferred. Report: [2026-0702 Approved Candidate Package Exposure First Slice](reports/2026/2026-0702-approved-candidate-package-exposure-first-slice.md). |
| 140 | Post-approved-candidate-package-exposure no-code product goal replan / approved candidate package exposure 後の no-code product goal 再計画 | Completed / 完了 | Chose public runtime preview artifact-key route as the first public delivery continuation. Report: [2026-0702 Post-Approved-Candidate-Package-Exposure No-Code Product Goal Replan](reports/2026/2026-0702-post-approved-candidate-package-exposure-no-code-product-goal-replan.md). |
| 141 | Public runtime preview artifact-key route first slice / public runtime preview artifact-key route first slice | Completed / 完了 | Exposed approved `NO-CODE-RUNTIME` `runtime-preview.html` through an artifact-key public route while keeping public alias semantics deferred. Report: [2026-0702 Public Runtime Preview Artifact-Key Route First Slice](reports/2026/2026-0702-public-runtime-preview-artifact-key-route-first-slice.md). |
| 142 | Post-public-runtime-preview no-code product goal replan / public runtime preview 後の no-code product goal 再計画 | Completed / 完了 | Chose public runtime current alias route as the next public delivery continuation. Report: [2026-0702 Post-Public-Runtime-Preview No-Code Product Goal Replan](reports/2026/2026-0702-post-public-runtime-preview-no-code-product-goal-replan.md). |
| 143 | Public runtime current alias route first slice / public runtime current alias route first slice | Completed / 完了 | Added `/runs/no-code/{project_key}/current/runtime-preview.html`, resolving to the latest approved `NO-CODE-RUNTIME` publish candidate. Report: [2026-0702 Public Runtime Current Alias Route First Slice](reports/2026/2026-0702-public-runtime-current-alias-route-first-slice.md). |
| 144 | Post-current-alias no-code product goal replan / current alias 後の no-code product goal 再計画 | 0.25 - 0.5 day / 0.25 - 0.5 日 | Active planning step. Choose cache/version policy, revision selection/rollback boundary, custom alias key storage, or candidate event display polish. |

## Post-Minimal-Candidate-Persistence No-Code Product Goal Replan / minimal candidate persistence 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Minimal-Candidate-Persistence No-Code Product Goal Replan](reports/2026/2026-0702-post-minimal-candidate-persistence-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Minimal-Candidate-Persistence No-Code Product Goal Replan](reports/2026/2026-0702-post-minimal-candidate-persistence-no-code-product-goal-replan.md)。

This planning item selected Approval transition persistence first slice as the next small no-code product-facing implementation after durable draft candidate revisions landed. / この planning item では durable draft candidate revision 実装後の次の小さな no-code product-facing implementation として Approval transition persistence first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Approval transition persistence first slice | Persist candidate approval transitions after draft candidate storage exists. | 1 - 2 days / 1 - 2 日 | Selected. This turns the prior approval state-model planning into repository-tested behavior. |
| Candidate create/list/detail route surface | Add operator/admin route controls around candidate records. | 1 - 3 days / 1 - 3 日 | Deferred until transition helper behavior is proven. |
| Public runtime URL/package exposure | Expose approved runtime artifacts publicly. | 2 - 5 days / 2 - 5 日 | Deferred. Approval state should exist before delivery semantics. |
| Rollback/revision history public selection | Select/restore published revisions. | 2 - 5 days / 2 - 5 日 | Deferred until published revision semantics exist. |

## Approval Transition Persistence First Slice / approval transition persistence first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Approval Transition Persistence First Slice](reports/2026/2026-0702-approval-transition-persistence-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Approval Transition Persistence First Slice](reports/2026/2026-0702-approval-transition-persistence-first-slice.md)。

This implementation work was selected by the post-minimal-candidate-persistence replan and is complete for the first slice. / これは minimal candidate persistence 後の replan で選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| ATP1 | Transition event schema / transition event schema | `DONE` | 0.5 day / 半日 | Added append-only `no_code_publish_candidate_transition_events` and bootstrap preflight coverage. |
| ATP2 | Repository transition helper / repository transition helper | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added `app_pdo_transition_no_code_publish_candidate(...)` for request-review, approve, and reject. |
| ATP3 | Fail-closed guards / fail-closed guard | `DONE` | 0.5 day / 半日 | Added source-output, actor, expected-status, invalid-transition, and reject-reason guards. |
| ATP4 | Focused verification / focused verification | `DONE` | 0.5 day / 半日 | Extended Docker-backed SQLite integration coverage to 7 tests / 92 assertions. |

Boundary / 境界:

- In scope: repository-level transition persistence, append-only event records, `request_review`, `approve`, `reject`, expected status and actor guards. / 対象: repository-level transition persistence、append-only event record、`request_review`、`approve`、`reject`、expected status と actor guard。
- Out of scope: candidate route actions, approval UI buttons, public runtime URL, artifact packaging, rollback, published revision selection. / 対象外: candidate route action、approval UI button、public runtime URL、artifact packaging、rollback、published revision selection。
- Verification: focused Docker-backed PHPUnit for `NoCodePublishCandidateRepositorySqliteTest`. / 検証: `NoCodePublishCandidateRepositorySqliteTest` の focused Docker-backed PHPUnit。

## Post-Approval-Transition-Persistence No-Code Product Goal Replan / approval transition persistence 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Approval-Transition-Persistence No-Code Product Goal Replan](reports/2026/2026-0702-post-approval-transition-persistence-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Approval-Transition-Persistence No-Code Product Goal Replan](reports/2026/2026-0702-post-approval-transition-persistence-no-code-product-goal-replan.md)。

This planning item selected Guarded publish candidate detail UI first slice as the next small no-code product-facing implementation after repository transition persistence landed. / この planning item では repository transition persistence 実装後の次の小さな no-code product-facing implementation として Guarded publish candidate detail UI first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Guarded publish candidate detail UI | Surface candidate creation, history, and transition controls on the existing NO-CODE-RUNTIME detail page. | 1 - 2 days / 1 - 2 日 | Selected and completed. Reuses the repository helpers without adding public delivery semantics. |
| Separate candidate list/detail routes | Add dedicated candidate routes. | 1 - 3 days / 1 - 3 日 | Deferred. Existing source-output detail is enough for the first operator surface. |
| Public runtime URL/package exposure | Expose approved runtime artifacts publicly. | 2 - 5 days / 2 - 5 日 | Deferred until approval UI controls are visible. |
| Rollback/revision history public selection | Select/restore published revisions. | 2 - 5 days / 2 - 5 日 | Deferred until published revision semantics exist. |

## Guarded Publish Candidate Detail UI First Slice / guarded publish candidate detail UI first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Guarded Publish Candidate Detail UI First Slice](reports/2026/2026-0702-guarded-publish-candidate-detail-ui-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Guarded Publish Candidate Detail UI First Slice](reports/2026/2026-0702-guarded-publish-candidate-detail-ui-first-slice.md)。

This implementation work was selected by the post-approval-transition-persistence replan and is complete for the first slice. / これは approval transition persistence 後の replan で選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| GUI1 | Candidate readiness action / candidate readiness action | `DONE` | 0.5 day / 半日 | NO-CODE-RUNTIME detail page can create a draft publish candidate from publishable readiness. |
| GUI2 | Candidate history / candidate history | `DONE` | 0.5 day / 半日 | Detail page lists saved candidate revisions with artifact, readiness, status, and creator context. |
| GUI3 | Guarded transitions / guarded transitions | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Detail page exposes request-review, approve, and reject actions through the repository transition helper. |
| GUI4 | Contract coverage and docs / contract coverage・docs | `DONE` | 0.5 day / 半日 | Added static contract assertions, report, and current-plan updates. |

Boundary / 境界:

- In scope: existing Source Output detail route for `NO-CODE-RUNTIME`, create/list/history display, request-review / approve / reject actions, CSRF-protected POSTs, repository guard reuse. / 対象: 既存 Source Output detail route の `NO-CODE-RUNTIME`、create / list / history 表示、request-review / approve / reject action、CSRF 付き POST、repository guard 再利用。
- Out of scope: public runtime URL, artifact package exposure, rollback, dedicated candidate route set, new approval workflow tables. / 対象外: public runtime URL、artifact package exposure、rollback、専用 candidate route set、新しい approval workflow table。
- Verification: PHP lint, static route/UI contract assertions, focused Docker-backed repository tests, and full `make test`. / 検証: PHP lint、static route / UI contract assertion、focused Docker-backed repository test、full `make test`。

## Post-Guarded-Candidate-UI No-Code Product Goal Replan / guarded candidate UI 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Guarded-Candidate-UI No-Code Product Goal Replan](reports/2026/2026-0702-post-guarded-candidate-ui-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Guarded-Candidate-UI No-Code Product Goal Replan](reports/2026/2026-0702-post-guarded-candidate-ui-no-code-product-goal-replan.md)。

This planning item selected Approved candidate package exposure first slice as the next small no-code product-facing implementation after guarded candidate controls landed. / この planning item では guarded candidate control 実装後の次の小さな no-code product-facing implementation として Approved candidate package exposure first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Approved candidate package exposure | Expose package handoff links only after candidate approval. | 0.5 - 1 day / 半日 - 1 日 | Selected. Reuses existing artifact detail/download routes and does not create public delivery semantics. |
| Public runtime URL route | Add public runtime URL / alias route for approved candidates. | 2 - 5 days / 2 - 5 日 | Deferred. Needs separate public alias and security semantics. |
| Dedicated candidate list/detail routes | Add dedicated candidate route set. | 1 - 3 days / 1 - 3 日 | Deferred. Existing Source Output detail page remains sufficient for first workflow. |
| Rollback/revision history public selection | Select/restore published revisions. | 2 - 5 days / 2 - 5 日 | Deferred until published revision semantics exist. |

## Approved Candidate Package Exposure First Slice / approved candidate package exposure first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Approved Candidate Package Exposure First Slice](reports/2026/2026-0702-approved-candidate-package-exposure-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Approved Candidate Package Exposure First Slice](reports/2026/2026-0702-approved-candidate-package-exposure-first-slice.md)。

This implementation work was selected by the post-guarded-candidate-UI replan and is complete for the first slice. / これは guarded candidate UI 後の replan で選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| ACPE1 | Approved-only package affordance / approved-only package affordance | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Candidate history shows artifact detail/download links only for approved candidates. |
| ACPE2 | Non-approved guard copy / 未承認 guard copy | `DONE` | 0.25 day / 0.25 日 | Draft/review/rejected candidates show that package exposure is guarded until approval. |
| ACPE3 | Contract coverage and docs / contract coverage・docs | `DONE` | 0.25 day / 0.25 日 | Added static contract assertions, completion report, and full verification. |

Boundary / 境界:

- In scope: existing Source Output detail route for `NO-CODE-RUNTIME`, approved candidate artifact detail/download links, non-approved guard text, static contract coverage. / 対象: 既存 Source Output detail route の `NO-CODE-RUNTIME`、approved candidate の artifact detail / download link、未承認 guard text、static contract coverage。
- Out of scope: public runtime URL, public alias route, package copying, new storage table, rollback, dedicated candidate route set. / 対象外: public runtime URL、public alias route、package copy、新 storage table、rollback、専用 candidate route set。
- Verification: PHP lint, focused Docker-backed contract/repository tests, and full `make test` passed. / 検証: PHP lint、focused Docker-backed contract / repository test、full `make test` は通過。

## Post-Approved-Candidate-Package-Exposure No-Code Product Goal Replan / approved candidate package exposure 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Approved-Candidate-Package-Exposure No-Code Product Goal Replan](reports/2026/2026-0702-post-approved-candidate-package-exposure-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Approved-Candidate-Package-Exposure No-Code Product Goal Replan](reports/2026/2026-0702-post-approved-candidate-package-exposure-no-code-product-goal-replan.md)。

This planning step selected Public runtime preview artifact-key route first slice as the next small public delivery continuation. / この planning step では次の小さな public delivery continuation として Public runtime preview artifact-key route first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Public runtime preview artifact-key route first slice | Expose approved runtime preview through a guarded public artifact-key route. | 0.5 - 1 day / 半日 - 1 日 | Selected and completed. Keeps alias semantics deferred. |
| Public alias route planning | Define stable alias, cache, and revision semantics after artifact-key serving lands. | 0.5 - 1 day / 半日 - 1 日 | Deferred. |
| Candidate event display polish | Show transition event history alongside candidate rows. | 0.5 - 1 day / 半日 - 1 日 | Candidate. Useful accountability polish before public delivery. |
| Rollback/revision history public selection | Select/restore published revisions. | 2 - 5 days / 2 - 5 日 | Deferred until public published revision semantics are defined. |

## Public Runtime Preview Artifact-Key Route First Slice / public runtime preview artifact-key route first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Public Runtime Preview Artifact-Key Route First Slice](reports/2026/2026-0702-public-runtime-preview-artifact-key-route-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Public Runtime Preview Artifact-Key Route First Slice](reports/2026/2026-0702-public-runtime-preview-artifact-key-route-first-slice.md)。

This implementation work was selected by the post-approved-candidate-package-exposure replan and is complete for the first slice. / これは approved candidate package exposure 後の replan で選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| PRP1 | Approved artifact lookup / approved artifact lookup | `DONE` | 0.25 day / 0.25 日 | Repository can find an approved `NO-CODE-RUNTIME` candidate by project/artifact key and rejects draft/non-matching artifacts. |
| PRP2 | Public runtime preview route / public runtime preview route | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added `/runs/no-code/{project_key}/{artifact_key}/runtime-preview.html`, serving only the generated preview file from the existing artifact bundle. |
| PRP3 | Approved UI link and contracts / approved UI link・contract | `DONE` | 0.25 day / 0.25 日 | Approved candidates show the public preview link; route/auth and repository tests cover the boundary. |

Boundary / 境界:

- In scope: approved candidate gate, artifact-key public preview route, existing artifact manifest/bundle storage, route/auth contract coverage, repository lookup coverage. / 対象: approved candidate gate、artifact-key public preview route、既存 artifact manifest / bundle storage、route / auth contract coverage、repository lookup coverage。
- Out of scope: public alias, stable published slug, new storage table, rollback/revision selection, package copy, broad static file serving. / 対象外: public alias、stable published slug、新 storage table、rollback / revision selection、package copy、広い static file serving。
- Verification: PHP lint, focused Docker-backed contract/repository tests, and full `make test`. / 検証: PHP lint、focused Docker-backed contract / repository test、full `make test`。

## Post-Public-Runtime-Preview No-Code Product Goal Replan / public runtime preview 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Public-Runtime-Preview No-Code Product Goal Replan](reports/2026/2026-0702-post-public-runtime-preview-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Public-Runtime-Preview No-Code Product Goal Replan](reports/2026/2026-0702-post-public-runtime-preview-no-code-product-goal-replan.md)。

This planning step selected Public runtime current alias route first slice as the next public delivery continuation after artifact-key serving landed. / この planning step では artifact-key serving 実装後の次の public delivery continuation として Public runtime current alias route first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Public runtime current alias route first slice | Add a stable project-level current preview URL on top of approved artifact-key serving. | 0.5 - 1 day / 半日 - 1 日 | Selected and completed. Keeps custom alias storage deferred. |
| Cache/version policy | Decide cache headers and immutable/current URL behavior. | 0.5 - 1 day / 半日 - 1 日 | Deferred. Existing `no-store` remains for this first alias slice. |
| Revision selection / rollback boundary | Define how an approved candidate becomes the current public revision and how rollback works. | 1 - 3 days / 1 - 3 日 | Deferred until current alias behavior is visible. |
| Candidate event display polish | Show transition event history in admin UI. | 0.5 - 1 day / 半日 - 1 日 | Deferred. Useful auditability polish but less public-delivery-facing than the alias route. |

## Public Runtime Current Alias Route First Slice / public runtime current alias route first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Public Runtime Current Alias Route First Slice](reports/2026/2026-0702-public-runtime-current-alias-route-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Public Runtime Current Alias Route First Slice](reports/2026/2026-0702-public-runtime-current-alias-route-first-slice.md)。

This implementation work was selected by the post-public-runtime-preview replan and is complete for the first slice. / これは public runtime preview 後の replan で選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| PCA1 | Current approved candidate lookup / current approved candidate lookup | `DONE` | 0.25 day / 0.25 日 | Repository resolves the latest approved `NO-CODE-RUNTIME` publish candidate for a project. |
| PCA2 | Current public preview route / current public preview route | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added `/runs/no-code/{project_key}/current/runtime-preview.html` and dispatches it before artifact-key matching. |
| PCA3 | Approved UI link and contracts / approved UI link・contract | `DONE` | 0.25 day / 0.25 日 | Approved candidates show the current preview link; route/auth and repository tests cover the boundary. |

Boundary / 境界:

- In scope: latest approved candidate gate, project-level current alias route, existing artifact manifest/bundle storage, route/auth contract coverage, repository lookup coverage. / 対象: latest approved candidate gate、project-level current alias route、既存 artifact manifest / bundle storage、route / auth contract coverage、repository lookup coverage。
- Out of scope: custom alias key route, stable slug storage, explicit published revision table, rollback/revision selection, package copy, broad static file serving. / 対象外: custom alias key route、stable slug storage、explicit published revision table、rollback / revision selection、package copy、広い static file serving。
- Verification: PHP lint, focused Docker-backed contract/repository tests, `git diff --check`, and full `make test`. / 検証: PHP lint、focused Docker-backed contract / repository test、`git diff --check`、full `make test`。

## Post-Current-Alias No-Code Product Goal Replan / current alias 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Current-Alias No-Code Product Goal Replan](reports/2026/2026-0702-post-current-alias-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Current-Alias No-Code Product Goal Replan](reports/2026/2026-0702-post-current-alias-no-code-product-goal-replan.md)。

This planning item selected Candidate event display polish first slice as the next small no-code product-facing implementation after the current public runtime alias landed. / この planning item では current public runtime alias 実装後の次の小さな no-code product-facing implementation として Candidate event display polish first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Candidate event display polish | Show transition event history in admin UI. | 0.5 - 1 day / 半日 - 1 日 | Selected and completed. Reuses existing transition events and improves operator auditability without changing public URL semantics. |
| Cache/version policy | Decide no-store/current and future immutable artifact URL behavior. | 0.5 - 1 day / 半日 - 1 日 | Deferred. Useful before long-lived public use. |
| Revision selection / rollback boundary | Define explicit current published revision semantics and rollback behavior. | 1 - 3 days / 1 - 3 日 | Deferred. Needed before current alias becomes operationally authoritative. |
| Custom public alias key storage | Let a project expose a configured public slug beyond `current`. | 1 - 3 days / 1 - 3 日 | Deferred until current/cache/revision semantics are clear. |

## Candidate Event Display Polish First Slice / candidate event display polish first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Candidate Event Display Polish First Slice](reports/2026/2026-0702-candidate-event-display-polish-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Candidate Event Display Polish First Slice](reports/2026/2026-0702-candidate-event-display-polish-first-slice.md)。

This implementation work was selected by the post-current-alias replan and is complete for the first slice. / これは current alias 後の replan で選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| CED1 | Transition event read helper / transition event read helper | `DONE` | 0.25 day / 0.25 日 | Repository reads existing append-only transition events by project/source output/revision. |
| CED2 | Candidate history event display / candidate history event display | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | NO-CODE-RUNTIME detail page shows transition, from/to status, actor, timestamp, and reject reason when present. |
| CED3 | Contract coverage and docs / contract coverage・docs | `DONE` | 0.25 day / 0.25 日 | Added focused repository/static UI contract coverage, report, and current-plan updates. |

Boundary / 境界:

- In scope: existing transition event read path, existing Source Output detail page display, focused repository/static contract coverage. / 対象: 既存 transition event read path、既存 Source Output detail page display、focused repository / static contract coverage。
- Out of scope: new transition storage, new approval workflow states, public cache/version policy, revision rollback/selection, custom alias storage. / 対象外: 新 transition storage、新 approval workflow state、public cache / version policy、revision rollback / selection、custom alias storage。
- Verification: PHP lint, focused Docker-backed contract/repository tests, `git diff --check`, and full `make test`. / 検証: PHP lint、focused Docker-backed contract / repository test、`git diff --check`、full `make test`。

## Post-Candidate-Event-Display No-Code Product Goal Replan / candidate event display 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Candidate-Event-Display No-Code Product Goal Replan](reports/2026/2026-0702-post-candidate-event-display-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Candidate-Event-Display No-Code Product Goal Replan](reports/2026/2026-0702-post-candidate-event-display-no-code-product-goal-replan.md)。

This planning item selected Public runtime cache/version policy first slice as the next small no-code product-facing implementation after candidate transition event visibility landed. / この planning item では candidate transition event visibility 実装後の次の小さな no-code product-facing implementation として Public runtime cache/version policy first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Public runtime cache/version policy | Clarify cache semantics for artifact-key and current public runtime preview routes. | 0.5 - 1 day / 半日 - 1 日 | Selected and completed. |
| Revision selection / rollback boundary | Define explicit current published revision semantics and rollback behavior. | 1 - 3 days / 1 - 3 日 | Deferred until cache semantics are explicit. |
| Custom public alias key storage | Let a project expose a configured public slug beyond `current`. | 1 - 3 days / 1 - 3 日 | Deferred until current/cache/revision semantics are clear. |

## Public Runtime Cache/Version Policy First Slice / public runtime cache/version policy first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Public Runtime Cache/Version Policy First Slice](reports/2026/2026-0702-public-runtime-cache-version-policy-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Public Runtime Cache/Version Policy First Slice](reports/2026/2026-0702-public-runtime-cache-version-policy-first-slice.md)。

This implementation work was selected by the post-candidate-event-display replan and is complete for the first slice. / これは candidate event display 後の replan で選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| PCV1 | Cache policy helpers / cache policy helpers | `DONE` | 0.25 day / 0.25 日 | Added route-specific helpers for artifact-key and current public runtime preview cache policy. |
| PCV2 | Route-specific response headers / route-specific response headers | `DONE` | 0.25 day / 0.25 日 | Artifact-key preview uses `public, max-age=31536000, immutable`; current alias keeps `no-store`. |
| PCV3 | Contract coverage and docs / contract coverage・docs | `DONE` | 0.25 day / 0.25 日 | Added cache helper/static contract coverage, report, and current-plan updates. |

Boundary / 境界:

- In scope: response cache semantics for existing public runtime preview routes and contract coverage. / 対象: 既存 public runtime preview route の response cache semantics と contract coverage。
- Out of scope: explicit published revision selection, rollback, custom alias storage, new public URL shapes, package copy/static hosting. / 対象外: explicit published revision selection、rollback、custom alias storage、新 public URL shape、package copy / static hosting。
- Verification: PHP lint, focused Docker-backed contract test, `git diff --check`, and full `make test`. / 検証: PHP lint、focused Docker-backed contract test、`git diff --check`、full `make test`。

## Post-Cache-Version-Policy No-Code Product Goal Replan / cache/version policy 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Cache-Version-Policy No-Code Product Goal Replan](reports/2026/2026-0702-post-cache-version-policy-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Cache-Version-Policy No-Code Product Goal Replan](reports/2026/2026-0702-post-cache-version-policy-no-code-product-goal-replan.md)。

This planning item selected Current public revision visibility first slice as the next small no-code product-facing implementation after public runtime cache/version policy landed. / この planning item では public runtime cache/version policy 実装後の次の小さな no-code product-facing implementation として Current public revision visibility first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Current public revision visibility | Show which approved candidate backs the current public runtime preview. | 0.5 - 1 day / 半日 - 1 日 | Selected and completed. |
| Explicit revision selection / rollback implementation | Store and mutate the selected current public revision. | 1 - 3 days / 1 - 3 日 | Deferred until current visibility is explicit. |
| Custom public alias key storage | Let a project expose a configured public slug beyond `current`. | 1 - 3 days / 1 - 3 日 | Deferred until current/revision semantics are clearer. |

## Current Public Revision Visibility First Slice / current public revision visibility first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Current Public Revision Visibility First Slice](reports/2026/2026-0702-current-public-revision-visibility-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Current Public Revision Visibility First Slice](reports/2026/2026-0702-current-public-revision-visibility-first-slice.md)。

This implementation work was selected by the post-cache-version-policy replan and is complete for the first slice. / これは cache/version policy 後の replan で選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| CPR1 | Current candidate lookup reuse / current candidate lookup reuse | `DONE` | 0.25 day / 0.25 日 | Existing current approved candidate lookup is reused on the NO-CODE-RUNTIME detail page. |
| CPR2 | Current/non-current UI marker / current・non-current UI marker | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Approved candidates show whether they currently back `/runs/no-code/{project_key}/current/runtime-preview.html`. |
| CPR3 | Boundary copy and contracts / boundary copy・contract | `DONE` | 0.25 day / 0.25 日 | UI explains current/non-current behavior, with static contract coverage and docs. |

Boundary / 境界:

- In scope: current alias visibility on the existing operator/admin candidate history surface and static contract coverage. / 対象: 既存 operator / admin candidate history surface 上の current alias visibility と static contract coverage。
- Out of scope: rollback action, explicit current selection storage, custom alias storage, new public URL shapes, package copy/static hosting. / 対象外: rollback action、explicit current selection storage、custom alias storage、新 public URL shape、package copy / static hosting。
- Verification: PHP lint, focused Docker-backed contract test, `git diff --check`, and full `make test`. / 検証: PHP lint、focused Docker-backed contract test、`git diff --check`、full `make test`。

## Post-Current-Public-Revision-Visibility No-Code Product Goal Replan / current public revision visibility 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Current-Public-Revision-Visibility No-Code Product Goal Replan](reports/2026/2026-0702-post-current-public-revision-visibility-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Current-Public-Revision-Visibility No-Code Product Goal Replan](reports/2026/2026-0702-post-current-public-revision-visibility-no-code-product-goal-replan.md)。

This planning item selected Explicit current public revision selection first slice as the next small no-code product-facing implementation after current public revision visibility landed. / この planning item では current public revision visibility 実装後の次の小さな no-code product-facing implementation として Explicit current public revision selection first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Explicit current public revision selection first slice | Store and mutate the selected current public runtime revision. | 1 - 2 days / 1 - 2 日 | Selected and completed. |
| Custom public alias key storage | Let a project expose a configured public slug beyond `current`. | 1 - 3 days / 1 - 3 日 | Deferred until current selection is durable. |
| Broader rollback workflow polish | Add richer rollback copy/history around current selection. | 2 - 5 days / 2 - 5 日 | Deferred. The first slice treats rollback as selecting an older approved candidate. |

## Explicit Current Public Revision Selection First Slice / explicit current public revision selection first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Explicit Current Public Revision Selection First Slice](reports/2026/2026-0702-explicit-current-public-revision-selection-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Explicit Current Public Revision Selection First Slice](reports/2026/2026-0702-explicit-current-public-revision-selection-first-slice.md)。

This implementation work was selected by the post-current-public-revision-visibility replan and is complete for the first slice. / これは current public revision visibility 後の replan で選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| ECR1 | Current selection storage / current selection storage | `DONE` | 0.5 day / 半日 | Added `no_code_public_runtime_current_revisions` and bootstrap preflight coverage. |
| ECR2 | Repository and current lookup / repository・current lookup | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added `app_pdo_select_current_no_code_publish_candidate(...)`; current lookup prefers explicit selection before latest-approved fallback. |
| ECR3 | UI action, contracts, and docs / UI action・contract・docs | `DONE` | 0.5 day / 半日 | Added guarded `Set Current Public Revision` action, static contract checks, reports, and plan updates. |

Boundary / 境界:

- In scope: explicit current selection storage, operator/admin selection action, current route lookup behavior, focused/static coverage. / 対象: explicit current selection storage、operator / admin selection action、current route lookup behavior、focused / static coverage。
- Out of scope: custom alias key storage, separate rollback event stream, package copy/static hosting, new public URL shapes. / 対象外: custom alias key storage、separate rollback event stream、package copy / static hosting、新 public URL shape。
- Verification: PHP lint, focused Docker-backed repository and static contract tests, `git diff --check`, and full `make test`. / 検証: PHP lint、focused Docker-backed repository / static contract test、`git diff --check`、full `make test`。

## Post-Explicit-Current-Public-Revision-Selection No-Code Product Goal Replan / explicit current public revision selection 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Explicit-Current-Public-Revision-Selection No-Code Product Goal Replan](reports/2026/2026-0702-post-explicit-current-public-revision-selection-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Explicit-Current-Public-Revision-Selection No-Code Product Goal Replan](reports/2026/2026-0702-post-explicit-current-public-revision-selection-no-code-product-goal-replan.md)。

This planning item selected Custom public alias key storage first slice as the next small no-code product-facing implementation after explicit current public revision selection landed. / この planning item では explicit current public revision selection 実装後の次の小さな no-code product-facing implementation として Custom public alias key storage first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Custom public alias key storage first slice | Store stable public alias keys for approved runtime candidates. | 1 - 2 days / 1 - 2 日 | Selected and completed. |
| Broader rollback workflow polish | Add richer rollback copy/history around current/alias selection. | 1 - 3 days / 1 - 3 日 | Deferred until alias semantics are durable. |
| Public delivery closure report | Close the narrow public delivery lane with docs/status. | 0.5 - 1 day / 半日 - 1 日 | Deferred until alias route first slice lands. |

## Custom Public Alias Key Storage First Slice / custom public alias key storage first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Custom Public Alias Key Storage First Slice](reports/2026/2026-0702-custom-public-alias-key-storage-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Custom Public Alias Key Storage First Slice](reports/2026/2026-0702-custom-public-alias-key-storage-first-slice.md)。

This implementation work was selected by the post-explicit-current-public-revision-selection replan and is complete for the first slice. / これは explicit current public revision selection 後の replan で選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| CPA1 | Alias storage / alias storage | `DONE` | 0.5 day / 半日 | Added `no_code_public_runtime_aliases` and bootstrap preflight coverage. |
| CPA2 | Repository and alias route lookup / repository・alias route lookup | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added alias normalization/validation, set/lookup helpers, and `/alias/{alias_key}` runtime preview route. |
| CPA3 | UI action, contracts, and docs / UI action・contract・docs | `DONE` | 0.5 day / 半日 | Added guarded `Set Public Alias` action, static contract checks, reports, and plan updates. |

Boundary / 境界:

- In scope: alias storage, operator/admin alias assignment, alias route lookup behavior, focused/static coverage. / 対象: alias storage、operator / admin alias assignment、alias route lookup behavior、focused / static coverage。
- Out of scope: alias deletion/disable workflow, custom domain or CDN configuration, separate rollback event stream, package copy/static hosting. / 対象外: alias deletion / disable workflow、custom domain / CDN configuration、separate rollback event stream、package copy / static hosting。
- Verification: PHP lint, focused Docker-backed repository and static contract tests, `git diff --check`, and full `make test`. / 検証: PHP lint、focused Docker-backed repository / static contract test、`git diff --check`、full `make test`。

## Post-Custom-Public-Alias No-Code Product Goal Replan / custom public alias 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Custom-Public-Alias No-Code Product Goal Replan](reports/2026/2026-0702-post-custom-public-alias-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Custom-Public-Alias No-Code Product Goal Replan](reports/2026/2026-0702-post-custom-public-alias-no-code-product-goal-replan.md)。

This planning item selected Public alias delete workflow first slice as the next small no-code product-facing implementation after custom public alias key storage landed. / この planning item では custom public alias key storage 実装後の次の小さな no-code product-facing implementation として Public alias delete workflow first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Public alias delete workflow first slice | Let operator/admin withdraw stale or accidental public aliases. | 0.5 - 1 day / 半日 - 1 日 | Selected and completed. |
| Broader rollback workflow polish | Add richer rollback copy/history around current/alias selection. | 1 - 3 days / 1 - 3 日 | Deferred until alias lifecycle has a minimal removal path. |
| Public delivery closure report | Close the narrow public delivery lane with docs/status. | 0.5 - 1 day / 半日 - 1 日 | Deferred until alias deletion lands. |

## Public Alias Delete Workflow First Slice / public alias delete workflow first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Public Alias Delete Workflow First Slice](reports/2026/2026-0702-public-alias-delete-workflow-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Public Alias Delete Workflow First Slice](reports/2026/2026-0702-public-alias-delete-workflow-first-slice.md)。

This implementation work was selected by the post-custom-public-alias replan and is complete for the first slice. / これは custom public alias 後の replan で選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| PAD1 | Alias list helper / alias list helper | `DONE` | 0.25 day / 0.25 日 | Added repository list helper for configured public runtime aliases. |
| PAD2 | Alias delete helper / alias delete helper | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added guarded operator/admin deletion helper that removes the alias row. |
| PAD3 | UI action, contracts, and docs / UI action・contract・docs | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added `Public Runtime Aliases` list, `Delete Public Alias`, static contract checks, reports, and plan updates. |

Boundary / 境界:

- In scope: alias listing, operator/admin alias deletion, route deactivation via row deletion, focused/static coverage. / 対象: alias listing、operator / admin alias deletion、row deletion による route deactivation、focused / static coverage。
- Out of scope: soft-delete history, alias deletion event stream, custom domain or CDN configuration, package copy/static hosting. / 対象外: soft-delete history、alias deletion event stream、custom domain / CDN configuration、package copy / static hosting。
- Verification: PHP lint, focused Docker-backed repository and static contract tests, `git diff --check`, and full `make test`. / 検証: PHP lint、focused Docker-backed repository / static contract test、`git diff --check`、full `make test`。

## Post-Public-Alias-Delete No-Code Product Goal Replan / public alias delete 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Public-Alias-Delete No-Code Product Goal Replan](reports/2026/2026-0702-post-public-alias-delete-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Public-Alias-Delete No-Code Product Goal Replan](reports/2026/2026-0702-post-public-alias-delete-no-code-product-goal-replan.md)。

This planning item selected Public delivery closure first slice as the next small no-code product-facing step after public alias delete workflow landed. / この planning item では public alias delete workflow 実装後の次の小さな no-code product-facing step として Public delivery closure first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Public delivery closure first slice | Record the completed public runtime delivery lane and remaining follow-up candidates. | 0.5 day / 半日 | Selected and completed. |
| Broader rollback workflow polish | Add richer rollback copy/history around current and alias selection. | 1 - 3 days / 1 - 3 日 | Deferred until public delivery closure is recorded. |

## Public Delivery Closure First Slice / public delivery closure first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Public Delivery Closure First Slice](reports/2026/2026-0702-public-delivery-closure-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Public Delivery Closure First Slice](reports/2026/2026-0702-public-delivery-closure-first-slice.md)。

This docs-only closure was selected by the post-public-alias-delete replan and is complete for the first slice. / これは public alias delete 後の replan で選んだ docs-only closure で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| PDC1 | Capability boundary / capability boundary | `DONE` | 0.25 day / 0.25 日 | Recorded approved candidate, artifact-key, current, explicit current, alias, and alias deletion capabilities. |
| PDC2 | Remaining candidates / remaining candidates | `DONE` | 0.25 day / 0.25 日 | Recorded rollback polish, alias audit trail, public hardening, and browser smoke as follow-up candidates. |
| PDC3 | Plan index and report updates / plan index・report updates | `DONE` | 0.25 day / 0.25 日 | Added closure report and moved the active plan to post-public-delivery-closure replan. |

Boundary / 境界:

- In scope: docs closure, acceptance boundary, remaining follow-up candidates. / 対象: docs closure、acceptance boundary、remaining follow-up candidates。
- Out of scope: new code, new routes, custom domain/CDN configuration, package copy/static hosting, push. / 対象外: new code、新 route、custom domain / CDN configuration、package copy / static hosting、push。
- Verification: `git diff --check`. / 検証: `git diff --check`。

## Post-Public-Delivery-Closure No-Code Product Goal Replan / public delivery closure 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Public-Delivery-Closure No-Code Product Goal Replan](reports/2026/2026-0702-post-public-delivery-closure-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Public-Delivery-Closure No-Code Product Goal Replan](reports/2026/2026-0702-post-public-delivery-closure-no-code-product-goal-replan.md)。

This planning item selected Rollback workflow polish first slice as the next no-code product-facing step after public delivery closure landed. / この planning item では public delivery closure 実施後の次の no-code product-facing step として Rollback workflow polish first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Rollback workflow polish first slice | Clarify the existing current revision rollback path and alias behavior in operator/admin UI. | 0.5 - 1 day / 半日 - 1 日 | Selected and completed. |
| Alias lifecycle audit trail | Record alias create/update/delete lifecycle as explicit history. | 1 - 2 days / 1 - 2 日 | Deferred until auditability is promoted. |
| Public delivery browser smoke | Exercise public artifact/current/alias routes in a browser-level smoke. | 0.5 - 1 day / 半日 - 1 日 | Deferred until public route fixtures are stable smoke targets. |

## Rollback Workflow Polish First Slice / rollback workflow polish first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Rollback Workflow Polish First Slice](reports/2026/2026-0702-rollback-workflow-polish-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Rollback Workflow Polish First Slice](reports/2026/2026-0702-rollback-workflow-polish-first-slice.md)。

This first slice names the already-supported rollback behavior in the operator/admin `NO-CODE-RUNTIME` detail UI. Selecting an older approved candidate moves `current` back to that revision, while artifact-key URLs and alias rows remain stable unless explicitly updated. / この first slice では、operator / admin の `NO-CODE-RUNTIME` detail UI 上で既に可能な rollback behavior を明示しました。古い approved candidate を選ぶと `current` はその revision に戻り、artifact-key URL と alias row は明示更新しない限り固定です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RWP1 | Current/non-current rollback copy / current・non-current rollback 表示 | `DONE` | 0.25 day / 0.25 日 | Added current rollback target and older approved candidate rollback wording. |
| RWP2 | Alias follow-current warning / alias follow-current warning | `DONE` | 0.25 day / 0.25 日 | Added copy explaining alias routes do not automatically follow current rollback. |
| RWP3 | Static contract and docs / static contract・docs | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added Source Output contract assertions, report, and plan index updates. |

Boundary / 境界:

- In scope: UI wording, rollback semantics clarity, static coverage, docs. / 対象: UI wording、rollback semantics clarity、static coverage、docs。
- Out of scope: new rollback storage, rollback event stream, alias automatic follow-current mode, new public routes, push. / 対象外: new rollback storage、rollback event stream、alias automatic follow-current mode、新 public route、push。
- Verification: PHP lint, focused Docker-backed Source Output contract test, `git diff --check`, and full `make test`. / 検証: PHP lint、focused Docker-backed Source Output contract test、`git diff --check`、full `make test`。

## Post-Rollback-Workflow-Polish No-Code Product Goal Replan / rollback workflow polish 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Rollback-Workflow-Polish No-Code Product Goal Replan](reports/2026/2026-0702-post-rollback-workflow-polish-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Rollback-Workflow-Polish No-Code Product Goal Replan](reports/2026/2026-0702-post-rollback-workflow-polish-no-code-product-goal-replan.md)。

This planning item selected Public delivery browser smoke first slice as the next no-code product-facing step after rollback workflow polish landed. / この planning item では rollback workflow polish 実施後の次の no-code product-facing step として Public delivery browser smoke first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Public delivery browser smoke first slice | Exercise artifact/current/alias public preview URLs as browser-visible runtime pages. | 0.5 - 1 day / 半日 - 1 日 | Selected and completed. |
| Alias lifecycle audit trail | Record alias create/update/delete lifecycle as explicit history. | 1 - 2 days / 1 - 2 日 | Deferred until auditability is promoted. |
| New product-facing continuation outside public delivery | Move back to a broader no-code product surface after public delivery verification. | Replan first / まず再計画 | Deferred until public route browser coverage is closed. |

## Public Delivery Browser Smoke First Slice / public delivery browser smoke first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Public Delivery Browser Smoke First Slice](reports/2026/2026-0702-public-delivery-browser-smoke-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Public Delivery Browser Smoke First Slice](reports/2026/2026-0702-public-delivery-browser-smoke-first-slice.md)。

This first slice adds a sample28 browser smoke for public runtime delivery URLs. It generates and publishes a `NO-CODE-RUNTIME` artifact, seeds approved/current/alias public runtime rows, verifies cache headers, and runs the generated runtime browser smoke against artifact-key, current, and alias URLs. / この first slice では sample28 の public runtime delivery URL 向け browser smoke を追加しました。`NO-CODE-RUNTIME` artifact を生成・publish し、approved / current / alias の public runtime row を seed し、cache header を確認し、artifact-key / current / alias URL に対して generated runtime browser smoke を実行します。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| PDB1 | Browser smoke URL support / browser smoke URL support | `DONE` | 0.25 day / 0.25 日 | Added `--url=...` support to the existing no-code runtime browser smoke. |
| PDB2 | Public runtime smoke fixture / public runtime smoke fixture | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added CLI seed for approved/current/alias public runtime rows from a generated artifact. |
| PDB3 | sample28 public route smoke / sample28 public route smoke | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added `make sample28-no-code-public-runtime-browser-smoke` for artifact/current/alias URL and cache checks. |
| PDB4 | MySQL PDO route lookup hardening / MySQL PDO route lookup hardening | `DONE` | 0.25 day / 0.25 日 | Fixed duplicate named placeholders in current/alias approved candidate lookup queries uncovered by the MySQL-backed smoke. |

Boundary / 境界:

- In scope: sample28 smoke fixture, existing public runtime route behavior, cache header checks, browser checks for generated runtime semantics. / 対象: sample28 smoke fixture、既存 public runtime route behavior、cache header checks、generated runtime semantics の browser checks。
- Out of scope: new public route behavior, alias audit trail, new generated runtime UI behavior, push. / 対象外: new public route behavior、alias audit trail、新 generated runtime UI behavior、push。
- Verification: `make sample28-no-code-public-runtime-browser-smoke`, PHP lint, focused repository/static PHPUnit, `git diff --check`, and full `make test`. / 検証: `make sample28-no-code-public-runtime-browser-smoke`、PHP lint、focused repository / static PHPUnit、`git diff --check`、full `make test`。

## Post-Public-Delivery-Browser-Smoke No-Code Product Goal Replan / public delivery browser smoke 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Public-Delivery-Browser-Smoke No-Code Product Goal Replan](reports/2026/2026-0702-post-public-delivery-browser-smoke-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Public-Delivery-Browser-Smoke No-Code Product Goal Replan](reports/2026/2026-0702-post-public-delivery-browser-smoke-no-code-product-goal-replan.md)。

This planning item selected Alias lifecycle audit trail first slice as the next no-code product-facing step after public delivery browser smoke landed. / この planning item では public delivery browser smoke 実施後の次の no-code product-facing step として Alias lifecycle audit trail first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Alias lifecycle audit trail first slice | Make alias create/update/delete operations visible after the fact. | 0.5 - 1 day / 半日 - 1 日 | Selected and completed. |
| New product-facing continuation outside public delivery | Move back to a broader no-code product surface after public delivery auditability. | Replan first / まず再計画 | Deferred until alias lifecycle auditability is closed. |
| Broader public hardening | Add broader public delivery deployment hardening. | 1 - 3 days / 1 - 3 日 | Deferred. Browser smoke and cache policy are covered for the current lane. |

## Alias Lifecycle Audit Trail First Slice / alias lifecycle audit trail first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Alias Lifecycle Audit Trail First Slice](reports/2026/2026-0702-alias-lifecycle-audit-trail-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Alias Lifecycle Audit Trail First Slice](reports/2026/2026-0702-alias-lifecycle-audit-trail-first-slice.md)。

This first slice adds append-only events for public runtime alias create, update, and delete operations, then shows recent events in the operator/admin `NO-CODE-RUNTIME` detail UI. / この first slice では public runtime alias の create / update / delete 操作に append-only event を追加し、operator / admin の `NO-CODE-RUNTIME` detail UI に最近の event を表示します。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| ALT1 | Alias event schema / alias event schema | `DONE` | 0.25 day / 0.25 日 | Added `no_code_public_runtime_alias_events` and bootstrap preflight coverage. |
| ALT2 | Repository event recording / repository event recording | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Recorded `alias_created`, `alias_updated`, and `alias_deleted` events. |
| ALT3 | Operator/admin event display / operator/admin event display | `DONE` | 0.25 day / 0.25 日 | Added recent alias lifecycle events to the `Public Runtime Aliases` section. |
| ALT4 | Focused coverage and docs / focused coverage・docs | `DONE` | 0.25 day / 0.25 日 | Added repository/static assertions, reports, and plan index updates. |

Boundary / 境界:

- In scope: append-only alias lifecycle storage, create/update/delete event recording, recent UI display, focused coverage. / 対象: append-only alias lifecycle storage、create / update / delete event recording、recent UI display、focused coverage。
- Out of scope: broad audit search/export, automatic alias follow-current behavior, new public routes, push. / 対象外: broad audit search / export、automatic alias follow-current behavior、新 public route、push。
- Verification: public runtime browser smoke, PHP lint, focused repository/static PHPUnit, `git diff --check`, and full `make test`. / 検証: public runtime browser smoke、PHP lint、focused repository / static PHPUnit、`git diff --check`、full `make test`。

## Post-Alias-Lifecycle-Audit-Trail No-Code Product Goal Replan / alias lifecycle audit trail 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Alias-Lifecycle-Audit-Trail No-Code Product Goal Replan](reports/2026/2026-0702-post-alias-lifecycle-audit-trail-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Alias-Lifecycle-Audit-Trail No-Code Product Goal Replan](reports/2026/2026-0702-post-alias-lifecycle-audit-trail-no-code-product-goal-replan.md)。

This planning item selected Public delivery hardening closure report as the final no-code public delivery lane step after alias lifecycle audit trail landed. / この planning item では alias lifecycle audit trail 実施後の最後の no-code public delivery lane step として Public delivery hardening closure report を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Public delivery hardening closure report | Record the final public delivery boundary and remaining parked deployment hardening candidates. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Selected and completed. |
| Another public delivery hardening implementation | Add more public delivery behavior before leaving the lane. | 1 - 3 days / 1 - 3 日 | Deferred. No concrete minimum blocker remains after route/browser/audit coverage. |
| New product-facing continuation outside public delivery | Move to a broader no-code product surface. | Replan first / まず再計画 | Deferred until accumulated public delivery worktree is organized into reviewable commits. |

## Public Delivery Hardening Closure Report / public delivery hardening closure report

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Public Delivery Hardening Closure Report](reports/2026/2026-0702-public-delivery-hardening-closure-report.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Public Delivery Hardening Closure Report](reports/2026/2026-0702-public-delivery-hardening-closure-report.md)。

This closure report records public delivery as complete for the current minimum after post-closure hardening. / この closure report では post-closure hardening 後の public delivery を current minimum として完了扱いにしました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| PDHC1 | Capability boundary / capability boundary | `DONE` | 0.25 day / 0.25 日 | Recorded approved exposure, artifact/current/alias routes, cache policy, rollback/current selection, alias delete, browser smoke, and alias lifecycle audit. |
| PDHC2 | Remaining parked candidates / remaining parked candidates | `DONE` | 0.25 day / 0.25 日 | Parked custom domain/CDN/static copy, broad audit export, and automatic alias-follow-current mode until concrete requirements exist. |
| PDHC3 | Next work boundary / next work boundary | `DONE` | 0.25 day / 0.25 日 | Set commit cleanup / review grouping as next active step without push. |

Boundary / 境界:

- In scope: docs closure, accepted public delivery boundary, remaining parked candidates, next-step boundary. / 対象: docs closure、accepted public delivery boundary、remaining parked candidates、next-step boundary。
- Out of scope: new behavior, deployment infrastructure, commit, push. / 対象外: new behavior、deployment infrastructure、commit、push。
- Verification: no new code; previous alias lifecycle slice passed browser smoke, `git diff --check`, and full `make test`. / 検証: 新 code はなし。直前の alias lifecycle slice は browser smoke、`git diff --check`、full `make test` に通過。

## Public Delivery Commit Cleanup / public delivery commit cleanup

Status: `DONE`. Report: [2026-0702 Public Delivery Commit Cleanup](reports/2026/2026-0702-public-delivery-commit-cleanup.md). / Status: `DONE`。Report: [2026-0702 Public Delivery Commit Cleanup](reports/2026/2026-0702-public-delivery-commit-cleanup.md)。

This cleanup grouped the accumulated no-code public runtime delivery worktree into local commit `e2c5d7e` without pushing. / この cleanup では no-code public runtime delivery の累積 worktree を local commit `e2c5d7e` にまとめ、push はしていません。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| PDCC1 | Review diff / 差分確認 | `DONE` | 0.25 day / 0.25 日 | Confirmed the dirty worktree belonged to the public delivery lane. |
| PDCC2 | Local commit / local commit | `DONE` | 0.25 day / 0.25 日 | Created `e2c5d7e Add no-code public runtime delivery workflow`. |
| PDCC3 | Commit record / commit record | `DONE` | 0.25 day / 0.25 日 | Recorded commit scope and verification in the dated report and plan index. |

Boundary / 境界:

- In scope: local commit grouping, report/current-plan record. / 対象: local commit grouping、report / current-plan record。
- Out of scope: push, history rewrite, additional implementation. / 対象外: push、history rewrite、追加実装。
- Verification: reused the pre-commit browser smoke, focused PHPUnit, full `make test`, and `git diff --check` record. / 検証: commit 前の browser smoke、focused PHPUnit、full `make test`、`git diff --check` 記録を使用。

## Post-Public-Delivery-Commit No-Code Product Goal Replan / public delivery commit 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Public-Delivery-Commit No-Code Product Goal Replan](reports/2026/2026-0702-post-public-delivery-commit-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Public-Delivery-Commit No-Code Product Goal Replan](reports/2026/2026-0702-post-public-delivery-commit-no-code-product-goal-replan.md)。

This planning item selected Local app packaging boundary inventory first slice as the next no-code product-facing lane after public Web delivery was committed locally. / この planning item では public Web delivery を local commit にまとめた後の次の no-code product-facing lane として Local app packaging boundary inventory first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Local app packaging boundary inventory first slice | Define package/runtime/artifact boundaries before implementation. | 0.5 - 1 day / 半日 - 1 日 | Selected and completed. |
| Local app packaging implementation | Start package manifest/archive behavior immediately. | 2 - 5 days / 2 - 5 日 | Deferred until boundary inventory exists. |
| Remote sync transport | Prove transport after app-local sync foundations. | 2 - 5 days / 2 - 5 日 | Deferred. Packaging boundary should clarify whether transport is required for the first milestone. |
| Visual builder / full generated app shell | Broader generated app product. | 1 - 3 weeks or more / 1 - 3 週間以上 | Deferred. Current path still generates from canonical metadata and artifacts. |

## Local App Packaging Boundary Inventory First Slice / local app packaging boundary inventory first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Local App Packaging Boundary Inventory First Slice](reports/2026/2026-0702-local-app-packaging-boundary-inventory-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Local App Packaging Boundary Inventory First Slice](reports/2026/2026-0702-local-app-packaging-boundary-inventory-first-slice.md)。

This first slice defines the boundary for the next local app packaging lane. It recommends App-local package manifest first slice as the next implementation candidate. / この first slice では次の local app packaging lane の境界を定義しました。次の実装候補として App-local package manifest first slice を推奨します。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| LAPB1 | Current asset inventory / current asset inventory | `DONE` | 0.25 day / 0.25 日 | Recorded existing no-code runtime, React/schema-form, app-local persistence, sync, and public delivery assets. |
| LAPB2 | Minimum package boundary / minimum package boundary | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Defined deterministic package artifact, metadata, readiness display, and focused smoke boundaries. |
| LAPB3 | Out-of-scope boundary / out-of-scope boundary | `DONE` | 0.25 day / 0.25 日 | Deferred native, Flutter, signing, remote transport, conflict resolution, scheduler, and visual builder scope. |
| LAPB4 | Next implementation recommendation / next implementation recommendation | `DONE` | 0.25 day / 0.25 日 | Recommended App-local package manifest first slice as the next candidate. |

Boundary / 境界:

- In scope: inventory, minimum package boundary, next first-slice recommendation. / 対象: inventory、minimum package boundary、next first-slice recommendation。
- Out of scope: code changes, native target implementation, remote transport, push. / 対象外: code changes、native target implementation、remote transport、push。
- Verification: docs-only; run `git diff --check` before commit. / 検証: docs-only。commit 前に `git diff --check` を実行する。

## Post-Local-App-Packaging-Boundary-Inventory No-Code Product Goal Replan / local app packaging boundary inventory 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Local-App-Packaging-Boundary-Inventory No-Code Product Goal Replan](reports/2026/2026-0702-post-local-app-packaging-boundary-inventory-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Local-App-Packaging-Boundary-Inventory No-Code Product Goal Replan](reports/2026/2026-0702-post-local-app-packaging-boundary-inventory-no-code-product-goal-replan.md)。

This planning item selected App-local package manifest first slice as the next no-code product-facing implementation after local app packaging boundary inventory. / この planning item では local app packaging boundary inventory 後の次の no-code product-facing implementation として App-local package manifest first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| App-local package manifest first slice | Add a generated manifest/summary artifact around existing App-local persistence package boundaries. | 0.5 - 1.5 days / 半日 - 1.5 日 | Selected and completed. |
| Operator package readiness display | Show package readiness/blockers in operator/admin UI. | 0.5 - 1 day / 半日 - 1 日 | Deferred until manifest shape exists. |
| Package archive smoke | Verify a narrow package archive/unpack path. | 1 - 2 days / 1 - 2 日 | Deferred until manifest/readiness exists. |
| Remote sync transport smoke | Add a narrow transport proof. | 2 - 5 days / 2 - 5 日 | Deferred. Transport is not required for the first package boundary. |

## App-local Package Manifest First Slice / App-local package manifest first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 App-local Package Manifest First Slice](reports/2026/2026-0702-app-local-package-manifest-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 App-local Package Manifest First Slice](reports/2026/2026-0702-app-local-package-manifest-first-slice.md)。

This first slice adds a generated `app-local-package-manifest` Source Output strategy for package metadata/readiness summary files. / この first slice では package metadata / readiness summary files 用の generated `app-local-package-manifest` Source Output strategy を追加しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| ALPM1 | Strategy registry / strategy registry | `DONE` | 0.25 day / 0.25 日 | Added `AppLocalPackage` and `app-local-package-manifest` as generated Source Output options. |
| ALPM2 | Manifest generator / manifest generator | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Emits `app-local-package-manifest.json`, `app-local-package-summary.json`, and `README.md`. |
| ALPM3 | Focused coverage / focused coverage | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Verifies strategy registration, emitted files, manifest shape, artifact creation, and publish. |

Boundary / 境界:

- In scope: manifest/summary generation from existing shared contract and App-local persistence file boundary, focused tests. / 対象: existing shared contract と App-local persistence file boundary からの manifest / summary generation、focused tests。
- Out of scope: native installers, archive packaging beyond normal artifact publication, remote transport, conflict resolution, background scheduler, visual builder, push. / 対象外: native installer、normal artifact publication を超える archive packaging、remote transport、conflict resolution、background scheduler、visual builder、push。
- Verification: PHP lint and focused `SharedDataClassContractFoundationTest`; `git diff --check` and full `make test` before commit. / 検証: PHP lint と focused `SharedDataClassContractFoundationTest`。commit 前に `git diff --check` と full `make test`。

## Post-App-Local-Package-Manifest No-Code Product Goal Replan / App-local package manifest 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-App-Local-Package-Manifest No-Code Product Goal Replan](reports/2026/2026-0702-post-app-local-package-manifest-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-App-Local-Package-Manifest No-Code Product Goal Replan](reports/2026/2026-0702-post-app-local-package-manifest-no-code-product-goal-replan.md)。

This planning item selected App-local package archive smoke first slice after the package manifest shape landed. / この planning item では package manifest shape ができた後の次の step として App-local package archive smoke first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| App-local package archive smoke first slice | Verify the generated package archive can be listed and extracted with expected manifest files. | 0.5 - 1 day / 半日 - 1 日 | Selected and completed. |
| Operator package readiness display | Show package readiness/blockers in operator/admin UI. | 0.5 - 1 day / 半日 - 1 日 | Deferred until archive confidence exists. |
| Defer packaging lane | Leave local app packaging after the manifest slice. | Replan first / まず再計画 | Deferred because archive smoke is small and directly validates the just-added manifest boundary. |

## App-local Package Archive Smoke First Slice / App-local package archive smoke first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 App-local Package Archive Smoke First Slice](reports/2026/2026-0702-app-local-package-archive-smoke-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 App-local Package Archive Smoke First Slice](reports/2026/2026-0702-app-local-package-archive-smoke-first-slice.md)。

This first slice adds focused archive smoke coverage for the generated `app-local-package-manifest` artifact. / この first slice では generated `app-local-package-manifest` artifact 向けの focused archive smoke coverage を追加しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| ALPAS1 | Archive existence/listing / archive existence・listing | `DONE` | 0.25 day / 0.25 日 | Asserted the generated `.tar.gz` exists and contains expected manifest/summary entries. |
| ALPAS2 | Archive extraction / archive extraction | `DONE` | 0.25 day / 0.25 日 | Extracted the archive into a temporary directory and read the package manifest. |
| ALPAS3 | Focused verification and docs / focused verification・docs | `DONE` | 0.25 day / 0.25 日 | Updated focused PHPUnit coverage, report, and plan index. |

Boundary / 境界:

- In scope: focused archive list/extract smoke for the existing package manifest artifact path. / 対象: 既存 package manifest artifact path の focused archive list / extract smoke。
- Out of scope: native installers, new archive format, app shell packaging, operator/admin readiness UI, remote sync transport, push. / 対象外: native installer、新 archive format、app shell packaging、operator / admin readiness UI、remote sync transport、push。
- Verification: PHP lint and focused `SharedDataClassContractFoundationTest`. / 検証: PHP lint と focused `SharedDataClassContractFoundationTest`。

## Post-App-Local-Package-Archive-Smoke No-Code Product Goal Replan / App-local package archive smoke 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-App-Local-Package-Archive-Smoke No-Code Product Goal Replan](reports/2026/2026-0702-post-app-local-package-archive-smoke-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-App-Local-Package-Archive-Smoke No-Code Product Goal Replan](reports/2026/2026-0702-post-app-local-package-archive-smoke-no-code-product-goal-replan.md)。

This planning item selected App-local package operator readiness display first slice after archive confidence landed. / この planning item では archive confidence ができた後の次の step として App-local package operator readiness display first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| App-local package operator readiness display first slice | Make package readiness/blockers visible in the existing operator/admin Source Output detail UI. | 0.5 - 1 day / 半日 - 1 日 | Selected and completed. |
| Packaging closure report | Close the local packaging lane after manifest/archive confidence. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Deferred until readiness is visible. |
| Broader next no-code product goal | Move away from local packaging. | Replan first / まず再計画 | Deferred until the local packaging lane has an operator-visible readiness boundary. |

## App-local Package Operator Readiness Display First Slice / App-local package operator readiness display first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 App-local Package Operator Readiness Display First Slice](reports/2026/2026-0702-app-local-package-operator-readiness-display-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 App-local Package Operator Readiness Display First Slice](reports/2026/2026-0702-app-local-package-operator-readiness-display-first-slice.md)。

This first slice adds read-only readiness visibility for `app-local-package-manifest` Source Outputs. / この first slice では `app-local-package-manifest` Source Output 向けの read-only readiness visibility を追加しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| ALPORD1 | Readiness helper / readiness helper | `DONE` | 0.25 day / 0.25 日 | Computes latest artifact, archive, output root, manifest, summary, and blockers. |
| ALPORD2 | Operator/admin display / operator/admin display | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added `App-local Package Readiness` to Source Output detail for package manifest strategies. |
| ALPORD3 | Static contract coverage and docs / static contract coverage・docs | `DONE` | 0.25 day / 0.25 日 | Added static route/source assertions, report, and plan index updates. |

Boundary / 境界:

- In scope: operator/admin read-only readiness display for existing App-local package artifacts. / 対象: 既存 App-local package artifact の operator / admin read-only readiness display。
- Out of scope: new package generation behavior, native installers, new archive format, app shell packaging, remote sync transport, push. / 対象外: new package generation behavior、native installer、新 archive format、app shell packaging、remote sync transport、push。
- Verification: PHP lint and focused `OpenApiSourceOutputContractTest`; run `git diff --check` and full `make test` before commit. / 検証: PHP lint と focused `OpenApiSourceOutputContractTest`。commit 前に `git diff --check` と full `make test` を実行する。

## Post-App-Local-Package-Operator-Readiness-Display No-Code Product Goal Replan / App-local package operator readiness display 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-App-Local-Package-Operator-Readiness-Display No-Code Product Goal Replan](reports/2026/2026-0702-post-app-local-package-operator-readiness-display-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-App-Local-Package-Operator-Readiness-Display No-Code Product Goal Replan](reports/2026/2026-0702-post-app-local-package-operator-readiness-display-no-code-product-goal-replan.md)。

This planning item selected Local app packaging closure report after operator readiness display landed. / この planning item では operator readiness display 実施後の次の step として Local app packaging closure report を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Local app packaging closure report | Close the current package boundary and park larger packaging candidates. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Selected and completed. |
| Focused UI/browser smoke for readiness display | Add browser-visible confidence for the new readiness section. | 0.5 - 1 day / 半日 - 1 日 | Deferred. Static contract and full integration test passed; browser smoke can wait until the UI is more interactive. |
| Broader next no-code product goal | Move away from local packaging. | Replan first / まず再計画 | Deferred until the packaging lane is closed cleanly. |

## Local App Packaging Closure Report / local app packaging closure report

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Local App Packaging Closure Report](reports/2026/2026-0702-local-app-packaging-closure-report.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Local App Packaging Closure Report](reports/2026/2026-0702-local-app-packaging-closure-report.md)。

This closure report records the current local app packaging boundary as complete for the minimum lane. / この closure report では current local app packaging boundary を minimum lane として完了扱いにしました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| LAPC1 | Completed capability boundary / completed capability boundary | `DONE` | 0.25 day / 0.25 日 | Recorded manifest generation, archive smoke, and operator readiness display as the accepted minimum package boundary. |
| LAPC2 | Parked candidates / parked candidates | `DONE` | 0.25 day / 0.25 日 | Parked native packaging, Flutter, signing, app shell packaging, remote transport, conflict resolution, scheduler, and visual builder. |
| LAPC3 | Next-step boundary / next-step boundary | `DONE` | 0.25 day / 0.25 日 | Set broader no-code product-goal replan as the next active step. |

Boundary / 境界:

- In scope: docs closure, accepted local app package boundary, remaining parked candidates, next-step boundary. / 対象: docs closure、accepted local app package boundary、remaining parked candidates、next-step boundary。
- Out of scope: new behavior, native installers, signing, app shell packaging, remote sync transport, push. / 対象外: new behavior、native installer、signing、app shell packaging、remote sync transport、push。
- Verification: no new code; previous package readiness slice passed focused static contract, `git diff --check`, and full `make test`. / 検証: 新 code はなし。直前の package readiness slice は focused static contract、`git diff --check`、full `make test` に通過。

## Post-Local-App-Packaging-Closure No-Code Product Goal Replan / local app packaging closure 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Local-App-Packaging-Closure No-Code Product Goal Replan](reports/2026/2026-0702-post-local-app-packaging-closure-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Local-App-Packaging-Closure No-Code Product Goal Replan](reports/2026/2026-0702-post-local-app-packaging-closure-no-code-product-goal-replan.md)。

This planning item selected No-code product milestone update after public delivery and local packaging before starting another implementation lane. / この planning item では次の implementation lane に入る前に No-code product milestone update after public delivery and local packaging を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| No-code product milestone update after public delivery and local packaging | Record the current completed milestone and next decision boundary. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Selected and completed. |
| New implementation lane | Start the next product-facing lane immediately. | Replan first / まず再計画 | Deferred until the milestone state is recorded and a fresh priority is chosen. |
| Package readiness browser smoke | Add browser-visible coverage for readiness display. | 0.5 - 1 day / 半日 - 1 日 | Deferred unless the readiness UI becomes interactive or materially more complex. |

## No-Code Product Milestone Update After Public Delivery and Local Packaging / public delivery・local packaging 後の no-code product milestone update

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 No-Code Product Milestone Update After Public Delivery and Local Packaging](reports/2026/2026-0702-no-code-product-milestone-update-after-public-delivery-and-local-packaging.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 No-Code Product Milestone Update After Public Delivery and Local Packaging](reports/2026/2026-0702-no-code-product-milestone-update-after-public-delivery-and-local-packaging.md)。

This milestone update records the current completed no-code product state after public delivery and local app packaging. / この milestone update では public delivery と local app packaging 後の current completed no-code product state を記録しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| NPMU1 | Public delivery inventory / public delivery inventory | `DONE` | 0.25 day / 0.25 日 | Recorded approved package exposure, public routes, rollback/current/alias behavior, browser smoke, and audit visibility. |
| NPMU2 | Local packaging inventory / local packaging inventory | `DONE` | 0.25 day / 0.25 日 | Recorded package manifest, archive smoke, operator readiness display, and closure boundary. |
| NPMU3 | Parked candidate inventory / parked candidate inventory | `DONE` | 0.25 day / 0.25 日 | Parked native packaging, transport, conflict resolution, scheduler, visual builder, CDN/domain, and broader audit search/export. |
| NPMU4 | Next decision boundary / next decision boundary | `DONE` | 0.25 day / 0.25 日 | Set fresh no-code product-goal replan as the next active step. |

Boundary / 境界:

- In scope: docs milestone update, accepted capability inventory, parked candidates, next decision boundary. / 対象: docs milestone update、accepted capability inventory、parked candidates、next decision boundary。
- Out of scope: new implementation, commit rewrite, push. / 対象外: new implementation、commit rewrite、push。
- Verification: docs-only; previous implementation slices passed focused coverage, `git diff --check`, and full `make test`. / 検証: docs-only。直前までの implementation slice は focused coverage、`git diff --check`、full `make test` に通過。

## Fresh No-Code Product-Goal Replan / fresh no-code product-goal replan

Status: `DONE`. Report: [2026-0702 Fresh No-Code Product Goal Replan After Milestone Update](reports/2026/2026-0702-fresh-no-code-product-goal-replan-after-milestone-update.md). / Status: `DONE`。Report: [2026-0702 Fresh No-Code Product Goal Replan After Milestone Update](reports/2026/2026-0702-fresh-no-code-product-goal-replan-after-milestone-update.md)。

This planning item selected Local commit stack review before another implementation lane. / この planning item では次の implementation lane に入る前に Local commit stack review を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Local commit stack review before next product lane | Record ahead-count, recent milestone commits, verification baseline, and no-push boundary. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Selected and completed. |
| New product-facing implementation lane | Start another implementation lane immediately. | Replan first / まず再計画 | Deferred until commit stack state is recorded. |
| Additional public delivery/local packaging hardening | Add more hardening after both lanes are closed. | Replan first / まず再計画 | Deferred. Both lanes are closed for the current minimum boundary. |

## Local Commit Stack Review After No-Code Milestone / no-code milestone 後の local commit stack review

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Local Commit Stack Review After No-Code Milestone](reports/2026/2026-0702-local-commit-stack-review-after-no-code-milestone.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Local Commit Stack Review After No-Code Milestone](reports/2026/2026-0702-local-commit-stack-review-after-no-code-milestone.md)。

This review records that `develop` is 49 commits ahead of `origin/develop`, with worktree clean before the review report was added, and push still out of scope. / この review では、`develop` が `origin/develop` より 49 commits ahead であり、review report 追加前の worktree は clean、push は引き続き対象外であることを記録しました。

## Fresh Priority Decision For Next Product-Facing Lane / 次の product-facing lane の fresh priority decision

Status: `DONE`. Report: [2026-0702 Fresh Priority Decision After No-Code Commit Stack Review](reports/2026/2026-0702-fresh-priority-decision-after-no-code-commit-stack-review.md). / Status: `DONE`。Report: [2026-0702 Fresh Priority Decision After No-Code Commit Stack Review](reports/2026/2026-0702-fresh-priority-decision-after-no-code-commit-stack-review.md)。

This planning item selected No-code commit stack consolidation plan before another implementation lane. / この planning item では次の implementation lane の前に No-code commit stack consolidation plan を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| No-code commit stack consolidation plan | Group the 50 ahead commits into reviewable meaning units without rewrite or push. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Selected and completed. |
| New product-facing implementation lane | Start another implementation lane immediately. | Replan first / まず再計画 | Deferred until current local stack has a clear review grouping. |
| Immediate squash/rewrite | Clean local history now. | Approval required / 承認必須 | Deferred. Rewriting local history should be explicit. |
| Push | Share commits to remote. | Out of scope / 対象外 | Not selected. User explicitly said not to push. |

## No-Code Commit Stack Consolidation Plan / no-code commit stack consolidation plan

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 No-Code Commit Stack Consolidation Plan](reports/2026/2026-0702-no-code-commit-stack-consolidation-plan.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 No-Code Commit Stack Consolidation Plan](reports/2026/2026-0702-no-code-commit-stack-consolidation-plan.md)。

This plan groups the 50 local ahead commits into reviewable meaning units and preserves the no-push/no-rewrite boundary. / この plan では local 50 ahead commits を reviewable な意味単位へ整理し、push なし / rewrite なしの境界を維持しました。

## Explicit Next Action Decision / 次 action の明示判断

Status: `DONE`. Report: [2026-0702 Explicit Next Action Decision After Commit Stack Consolidation](reports/2026/2026-0702-explicit-next-action-decision-after-commit-stack-consolidation.md). / Status: `DONE`。Report: [2026-0702 Explicit Next Action Decision After Commit Stack Consolidation](reports/2026/2026-0702-explicit-next-action-decision-after-commit-stack-consolidation.md)。

This planning item selected Operator delivery overview first slice as the next implementation lane, while keeping push and local history rewrite out of scope. / この planning item では push と local history rewrite を対象外のまま、次の implementation lane として Operator delivery overview first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Operator delivery overview | Public runtime delivery and app-local packaging are both complete but visible in separate places; show both states together for operators. | 0.5 - 1 day / 半日 - 1 日 | Selected and completed. |
| Local history cleanup | Squash or rewrite the 51 local commits before any more product work. | Approval required / 承認必須 | Deferred. User has not requested rewrite, and push remains out of scope. |
| PR/review summary without push | Prepare a review-facing summary from the local stack. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Deferred until the user asks for commit/PR organization. |

## Operator Delivery Overview First Slice / operator delivery overview first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Operator Delivery Overview First Slice](reports/2026/2026-0702-operator-delivery-overview-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Operator Delivery Overview First Slice](reports/2026/2026-0702-operator-delivery-overview-first-slice.md)。

This implementation adds a combined delivery overview to the no-code Source Outputs inspection card, showing public runtime readiness and app-local package readiness together. / この implementation では no-code Source Outputs inspection card に combined delivery overview を追加し、public runtime readiness と app-local package readiness を一緒に表示しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| ODO1 | Delivery summary model / delivery summary model | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added `delivery_overview` to `app_no_code_operator_inspection_from_catalog()`. |
| ODO2 | App-local package readiness rollup / App-local package readiness rollup | `DONE` | 0.25 day / 0.25 日 | Summarizes package definition, latest artifact/archive, manifest, summary, and blockers. |
| ODO3 | Operator UI display / operator UI display | `DONE` | 0.25 day / 0.25 日 | Shows Delivery Overview on the Source Outputs page with a link to the App-local package definition. |
| ODO4 | Focused coverage / focused coverage | `DONE` | 0.25 day / 0.25 日 | Added inspection and static contract assertions. |

Boundary / 境界:

- In scope: read-only operator visibility for public runtime and app-local package delivery paths. / 対象: public runtime と app-local package delivery path の read-only operator visibility。
- Out of scope: new publish actions, native packaging, remote transport, commit history rewrite, push. / 対象外: new publish action、native packaging、remote transport、commit history rewrite、push。
- Verification: `php -l`, focused PHPUnit, `git diff --check`, and full `make test` before commit. / 検証: `php -l`、focused PHPUnit、`git diff --check`、commit 前の full `make test`。

## Post-Operator-Delivery-Overview No-Code Product Goal Replan / operator delivery overview 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0702 Post-Operator-Delivery-Overview No-Code Product Goal Replan](reports/2026/2026-0702-post-operator-delivery-overview-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0702 Post-Operator-Delivery-Overview No-Code Product Goal Replan](reports/2026/2026-0702-post-operator-delivery-overview-no-code-product-goal-replan.md)。

This planning item selected no-code delivery milestone closure before additional implementation, then commit cleanup / review grouping without push. / この planning item では追加実装の前に no-code delivery milestone closure を選び、その後 push なしの commit cleanup / review grouping へ進める判断にしました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| No-code delivery milestone closure | Public delivery, local packaging, and operator delivery overview now form a coherent product-facing boundary. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Selected and completed. |
| Continue implementation immediately | Add another operator action or runtime surface. | Replan first / まず再計画 | Deferred until the milestone and commit stack are easier to review. |
| Commit cleanup immediately | Start review grouping without a closure note. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Deferred until closure is recorded. |

## No-Code Delivery Milestone Closure Report / no-code delivery milestone closure report

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 No-Code Delivery Milestone Closure Report](reports/2026/2026-0702-no-code-delivery-milestone-closure-report.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 No-Code Delivery Milestone Closure Report](reports/2026/2026-0702-no-code-delivery-milestone-closure-report.md)。

This closure records public delivery, local app packaging, and operator delivery overview as the current product-facing milestone boundary. / この closure では public delivery、local app packaging、operator delivery overview を current product-facing milestone boundary として記録しました。

Boundary / 境界:

- In scope: closure record, accepted capabilities, parked follow-ups, next commit-review boundary. / 対象: closure record、accepted capabilities、parked follow-up、次の commit-review boundary。
- Out of scope: new implementation, local history rewrite, squash, push. / 対象外: new implementation、local history rewrite、squash、push。
- Verification: docs-only; the latest implementation slice passed focused PHPUnit, `git diff --check`, and full `make test`. / 検証: docs-only。直近 implementation slice は focused PHPUnit、`git diff --check`、full `make test` に通過。

## Commit Cleanup / Review Grouping After Delivery Milestone / delivery milestone 後の commit cleanup・review grouping

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Commit Cleanup Review Grouping After Delivery Milestone](reports/2026/2026-0702-commit-cleanup-review-grouping-after-delivery-milestone.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Commit Cleanup Review Grouping After Delivery Milestone](reports/2026/2026-0702-commit-cleanup-review-grouping-after-delivery-milestone.md)。

This review grouped the 53 local ahead commits into reviewable meaning units without push or history rewrite. / この review では 53 件の local ahead commit を、push や history rewrite なしで review しやすい意味単位へ整理しました。

| Group | Commit range / commits | Meaning |
| --- | --- | --- |
| 1 | `2c66774` through `1e48bd7` | Sync/operator visibility foundation and runtime retry visibility. |
| 2 | `afe9f01` through `297fd85` | No-code runtime adapter milestone, validation parity, product-surface planning, approval/revision planning, and verification closure. |
| 3 | `e699869`, `e2c5d7e`, `c86d70b` | Publish candidate persistence and public runtime delivery implementation. |
| 4 | `8d5172c` through `bf4fe6d` | Local app packaging lane. |
| 5 | `c39c7f9`, `04138c9`, `fe8f036` | Milestone update, stack review, and consolidation plan. |
| 6 | `0332438`, `e1b4eee` | Operator delivery overview and no-code delivery milestone closure. |

Boundary / 境界:

- In scope: commit stack review grouping, docs update, next execution decision boundary. / 対象: commit stack review grouping、docs update、次の execution decision boundary。
- Out of scope: push, squash, rebase, reset, force-push, PR creation. / 対象外: push、squash、rebase、reset、force-push、PR creation。
- Verification: docs-only; `git diff --check`. / 検証: docs-only。`git diff --check`。

## Commit Cleanup Execution Decision / commit cleanup execution 判断

Status: `DONE`. Report: [2026-0702 Commit Cleanup Execution Result](reports/2026/2026-0702-commit-cleanup-execution-result.md). / Status: `DONE`。Report: [2026-0702 Commit Cleanup Execution Result](reports/2026/2026-0702-commit-cleanup-execution-result.md)。

This execution selected explicit local history cleanup. A backup branch was created first, then the local ahead stack was rebuilt from `origin/develop` into six grouped commits. Push was not performed. / この execution では明示的な local history cleanup を選びました。先に backup branch を作り、その後 local ahead stack を `origin/develop` から 6 件の grouped commit として作り直しました。Push は未実行です。

| Step | Work / 作業 | Status | Output / 成果物 |
| --- | --- | --- | --- |
| CED1 | Backup branch / backup branch | `DONE` | `codex/backup-develop-pre-squash-20260702-ea60c8c` |
| CED2 | Rebuild grouped commits / grouped commit 再構成 | `DONE` | `fa80e5a`, `3bfcaf7`, `337b2b1`, `2c7ef11`, `12dbed6`, `1def520` |
| CED3 | Tree parity check / tree parity check | `DONE` | `git diff --stat codex/backup-develop-pre-squash-20260702-ea60c8c..HEAD` returned no diff before this report update. |
| CED4 | Push boundary / push 境界 | `DONE` | Push was not performed. |

## Post-Cleanup Verification / cleanup 後の verification

Status: `DONE`. / Status: `DONE`。

Final verification after local history cleanup passed. / local history cleanup 後の最終 verification に通過しました。

- `git diff --check`: passed.
- `make test`: `327 tests, 10786 assertions, skipped 1`.

## Post-Cleanup Next Action Decision / cleanup 後の次 action 判断

Status: `ACTIVE_NEXT`. / Status: `ACTIVE_NEXT`。

Choose whether to prepare a review summary without push, prepare for a later push, or return to the next product-facing lane. / push なしの review summary を作るか、後続の push 準備へ進むか、次の product-facing lane へ戻るかを選びます。

## Data-First No-Code Domain Sample 2 First Slice / data-first no-code domain sample 2 first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Sample29 No-Code Support Case First Slice](reports/2026/2026-0630-sample29-no-code-support-case-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Sample29 No-Code Support Case First Slice](reports/2026/2026-0630-sample29-no-code-support-case-first-slice.md)。

This implementation work was selected after runtime UX/state polish and is complete for the first slice. / これは runtime UX/state polish 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| D2-1 | Domain boundary / domain 境界 | `DONE` | 0.5 day / 半日 | Chose a support-case domain with read-model context fields (`case_number`, `customer_name`, `customer_tier`) and editable workflow fields. |
| D2-2 | Sample scaffold / sample scaffold | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added sample29 directory, catalog entry, compose/run files, README, and project/source-output seed skeleton. |
| D2-3 | Metadata wiring / metadata wiring | `DONE` | 1 - 2 days / 1 - 2 日 | Seeded shared contract, managed operation, and `NO-CODE-RUNTIME` metadata for `support_case` / `update_support_case`. |
| D2-4 | Runtime smoke / runtime smoke | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added sample29 pack/runtime test coverage and a browser UI smoke profile using the polished generated runtime. |
| D2-5 | Docs and verification / docs・verification | `DONE` | 0.5 day / 半日 | README, report, current-plan update, targeted sample29 verification, and full `make test` record. |

Boundary / 境界:

- In scope: one second data-first no-code sample, slightly richer domain shape, generated runtime artifact path, managed operation smoke, browser UI smoke. / 対象: 2 つ目の data-first no-code sample、少し豊かな domain shape、generated runtime artifact path、managed operation smoke、browser UI smoke。
- Out of scope: new visual builder, new metadata tables, broad relation engine, app-local sync product demo, operator/admin workflow, native/Flutter target. / 対象外: visual builder 追加、新 metadata table、広い relation engine、app-local sync product demo、operator/admin workflow、native / Flutter target。
- Verification: sample pack runtime test, no-code runtime UI smoke, focused PHPUnit, and `make test` if shared generator/runtime code changes. / 検証: sample pack runtime test、no-code runtime UI smoke、focused PHPUnit、shared generator/runtime code を触る場合は `make test`。

## Post-Sample29 No-Code Product Goal Replan / sample29 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Sample29 No-Code Product Goal Replan](reports/2026/2026-0630-post-sample29-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Sample29 No-Code Product Goal Replan](reports/2026/2026-0630-post-sample29-no-code-product-goal-replan.md)。

This planning item selected App-local sync no-code demonstration as the next active implementation item. / この planning item では App-local sync no-code demonstration を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| App-local sync demonstration | Connect no-code action intent more visibly to App-local persistence / sync concepts. | 2 - 5 days / 2 - 5 日 | Selected. sample29 completed the second Web/runtime domain proof, so the next product story can show the sync-backed data path. |
| Sample29 follow-up domain pressure | Use support-case results to add the smallest missing read-model or relation-shaped proof. | 1 - 3 days / 1 - 3 日 | Deferred. sample29 did not expose a concrete blocking runtime/metadata gap. |
| Operator/admin no-code workflow | Show how an operator chooses, publishes, or inspects no-code runtime artifacts. | 1 - 3 days / 1 - 3 日 | Deferred. Operator surface still needs clearer scope. |
| Targeted runtime polish from sample29 | Polish only presentation gaps found in the second domain sample. | 0.5 - 2 days / 半日 - 2 日 | Deferred until a concrete presentation gap is identified. |

## App-Local Sync No-Code Demonstration First Slice / App-local sync no-code demonstration first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Sample30 No-Code App-local Sync First Slice](reports/2026/2026-0630-sample30-no-code-app-local-sync-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Sample30 No-Code App-local Sync First Slice](reports/2026/2026-0630-sample30-no-code-app-local-sync-first-slice.md)。

This implementation work was selected after sample29 and is complete for the first slice. / これは sample29 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| SY1 | Boundary and target sample / 境界と対象 sample | `DONE` | 0.5 day / 半日 | Added a small sample30 rather than expanding sample29, and fixed one sync-backed no-code update action path. |
| SY2 | App-local fixture and artifact bridge / App-local fixture・artifact bridge | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Seeded shared contract / App-local persistence / no-code runtime Source Outputs for `sync_task`. |
| SY3 | No-code intent to sync handoff / no-code intent -> sync handoff | `DONE` | 1 - 2 days / 1 - 2 日 | Generated no-code action intent becomes a managed operation sync intent and is enqueued in the sync outbox. |
| SY4 | Runtime and smoke proof / runtime・smoke proof | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added focused PHPUnit / sample pack smoke proving App-local outbox handler updates SQLite DTO state. |
| SY5 | Docs and verification / docs・verification | `DONE` | 0.5 day / 半日 | README, report, current-plan update, targeted verification, and full `make test` record. |

Boundary / 境界:

- In scope: one small sync-backed no-code demonstration, existing shared contract / App-local persistence / managed operation foundations, sample-visible sync handoff, focused smoke. / 対象: 小さな sync-backed no-code demonstration 1 つ、既存 shared contract / App-local persistence / managed operation foundation、sample-visible な sync handoff、focused smoke。
- Out of scope: new visual builder, conflict resolution, full offline runtime, transport to a remote server, operator/admin publishing workflow, native/Flutter target. / 対象外: visual builder 追加、conflict resolution、完全 offline runtime、remote server transport、operator/admin publishing workflow、native / Flutter target。
- Verification: focused PHPUnit or sample pack runtime test first; add browser smoke only if the first slice exposes browser-visible state; run `make test` if shared runtime/foundation code changes. / 検証: まず focused PHPUnit または sample pack runtime test。browser-visible state を出す場合だけ browser smoke を追加し、shared runtime / foundation code を触る場合は `make test`。

## Post-Sample30 No-Code Product Goal Replan / sample30 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Sample30 No-Code Product Goal Replan](reports/2026/2026-0630-post-sample30-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Sample30 No-Code Product Goal Replan](reports/2026/2026-0630-post-sample30-no-code-product-goal-replan.md)。

This planning item selected Server-side sync processing follow-up as the next active implementation item. / この planning item では Server-side sync processing follow-up を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Server-side sync processing follow-up | Extend the sample-visible path from App-local outbox handling toward generated server DBAccess processing. | 1 - 3 days / 1 - 3 日 | Selected. This is the smallest continuation after sample30 and stays within the existing sync / DBAccess foundations. |
| Sync handoff visibility polish | Make the generated/runtime artifact show the sync handoff state more clearly. | 0.5 - 2 days / 半日 - 2 日 | Deferred. sample30 proved the handoff; presentation polish should follow a concrete visible gap. |
| Operator/admin no-code workflow | Show how an operator chooses, publishes, or inspects no-code runtime artifacts. | 1 - 3 days / 1 - 3 日 | Deferred. Operator surface still needs clearer scope. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

## Server-Side Sync Processing Follow-Up First Slice / server-side sync processing follow-up first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Server-Side Sync Processing Follow-Up First Slice](reports/2026/2026-0630-server-side-sync-processing-follow-up-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Server-Side Sync Processing Follow-Up First Slice](reports/2026/2026-0630-server-side-sync-processing-follow-up-first-slice.md)。

This implementation work was selected after sample30 and is complete for the first slice. / これは sample30 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| SV1 | Boundary and sample target / 境界と sample target | `DONE` | 0.5 day / 半日 | Extended sample30 rather than adding sample31, keeping transport and conflict resolution out of scope. |
| SV2 | Server binding setup / server binding setup | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Materialized generated server DBAccess for `sync_task` and used generated method catalog fallback for binding. |
| SV3 | Sync outbox server handler proof / sync outbox server handler proof | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Processed a second managed operation sync outbox item with the server DBAccess handler and verified the server row changed. |
| SV4 | Sample pack smoke / sample pack smoke | `DONE` | 0.5 day / 半日 | Extended `Sample30NoCodeAppLocalSyncDemoTest` and `make sample30-pack-runtime-test` coverage. |
| SV5 | Docs and verification / docs・verification | `DONE` | 0.5 day / 半日 | README/report/current-plan updates and targeted verification record. |

Boundary / 境界:

- In scope: one server-side processing proof for an existing managed operation sync intent, generated DBAccess binding, sample-visible server row update, focused smoke. / 対象: 既存 managed operation sync intent の server-side processing proof 1 つ、generated DBAccess binding、sample-visible な server row update、focused smoke。
- Out of scope: remote transport, conflict resolution, retry scheduling beyond existing outbox lifecycle, visual builder, native/Flutter target. / 対象外: remote transport、conflict resolution、既存 outbox lifecycle を超える retry scheduling、visual builder、native / Flutter target。
- Verification: focused PHPUnit / sample pack runtime test first; run `make test` if shared server DBAccess / sync foundation code changes. / 検証: まず focused PHPUnit / sample pack runtime test。shared server DBAccess / sync foundation code を触る場合は `make test`。

## Post-Server-Side Sync No-Code Product Goal Replan / server-side sync 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Server-Side Sync No-Code Product Goal Replan](reports/2026/2026-0630-post-server-side-sync-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Server-Side Sync No-Code Product Goal Replan](reports/2026/2026-0630-post-server-side-sync-no-code-product-goal-replan.md)。

This planning item selected Reusable partial-update server merge policy as the next active implementation item. / この planning item では Reusable partial-update server merge policy を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Reusable partial-update / server merge policy | Replace the sample-specific full-row payload completion with a reusable read/merge/write policy for generated server DBAccess updates. | 1 - 3 days / 1 - 3 日 | Selected. sample30 exposed this as the most concrete product-path gap after server-side processing. Keep conflict resolution out of scope. |
| Sync handoff visibility polish | Make generated/runtime artifacts show App-local/server processing state more clearly. | 0.5 - 2 days / 半日 - 2 日 | Deferred. The data behavior gap is more foundational than presentation polish. |
| Operator/admin no-code workflow | Show how an operator chooses, publishes, or inspects no-code runtime artifacts. | 1 - 3 days / 1 - 3 日 | Deferred. Operator surface still needs clearer scope. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

## Reusable Partial-Update Server Merge Policy First Slice / reusable partial-update server merge policy first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Reusable Partial-Update Server Merge Policy First Slice](reports/2026/2026-0630-reusable-partial-update-server-merge-policy-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Reusable Partial-Update Server Merge Policy First Slice](reports/2026/2026-0630-reusable-partial-update-server-merge-policy-first-slice.md)。

This implementation work was selected after the server-side sync processing follow-up and is complete for the first slice. / これは server-side sync processing follow-up 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| PU1 | Boundary and server read target / 境界と server read target | `DONE` | 0.5 day / 半日 | Scoped the reusable merge path to update intents that carry partial input and key fields. |
| PU2 | Existing row read adapter / existing row read adapter | `DONE` | 0.5 - 1 day / 半日 - 1 日 | The server DBAccess executor derives and calls the generated read method when a full DataClass payload is missing. |
| PU3 | Merge policy helper / merge policy helper | `DONE` | 0.5 day / 半日 | Existing row values are merged with key + partial input into a full generated DataClass payload without conflict resolution. |
| PU4 | Server handler integration / server handler integration | `DONE` | 0.5 - 1 day / 半日 - 1 日 | The reusable merge path runs inside `app_managed_operation_server_dbaccess_execute_intent` for update operations. |
| PU5 | Sample30 smoke and docs / sample30 smoke・docs | `DONE` | 0.5 day / 半日 | Removed sample30's sample-specific payload completion, verified sample30 server update, and updated report/current plan. |

Boundary / 境界:

- In scope: update operation only, existing generated server DBAccess read/update methods, one row keyed by the sync intent, deterministic merge of partial input over existing row, sample30 smoke. / 対象: update operation のみ、既存 generated server DBAccess read/update method、sync intent の key による 1 row、partial input を existing row に deterministic merge、sample30 smoke。
- Out of scope: conflict resolution, remote transport, retry scheduling, multi-row merge, delete/create semantics, visual builder, native/Flutter target. / 対象外: conflict resolution、remote transport、retry scheduling、multi-row merge、delete / create semantics、visual builder、native / Flutter target。
- Verification: focused PHPUnit / `make sample30-pack-runtime-test` first; run `make test` because shared server DBAccess executor behavior is likely to change. / 検証: まず focused PHPUnit / `make sample30-pack-runtime-test`。shared server DBAccess executor behavior を触る可能性が高いため `make test` も実行する。

## Post-Partial-Update Merge No-Code Product Goal Replan / partial-update merge 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Partial-Update Merge No-Code Product Goal Replan](reports/2026/2026-0630-post-partial-update-merge-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Partial-Update Merge No-Code Product Goal Replan](reports/2026/2026-0630-post-partial-update-merge-no-code-product-goal-replan.md)。

This planning item selected Sync handoff visibility polish as the next active implementation item. / この planning item では Sync handoff visibility polish を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Sync handoff visibility polish | Make generated/runtime artifacts show App-local/server processing state more clearly now that both processing paths work. | 0.5 - 2 days / 半日 - 2 日 | Selected. This is the smallest product-facing continuation after both App-local and server-side sync processing paths work. |
| Operator/admin no-code workflow | Show how an operator chooses, publishes, or inspects no-code runtime artifacts. | 1 - 3 days / 1 - 3 日 | Deferred. Operator surface still needs clearer scope. |
| Additional sync behavior pressure | Add a small retry/error-state or merge edge proof after partial update merge. | 1 - 3 days / 1 - 3 日 | Deferred. Data behavior is good enough for now; make the existing handoff visible first. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

## Sync Handoff Visibility Polish First Slice / sync handoff visibility polish first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Sync Handoff Visibility Polish First Slice](reports/2026/2026-0630-sync-handoff-visibility-polish-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Sync Handoff Visibility Polish First Slice](reports/2026/2026-0630-sync-handoff-visibility-polish-first-slice.md)。

This implementation work was selected after reusable partial-update server merge policy and is complete for the first slice. / これは reusable partial-update server merge policy 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| SH1 | Visibility boundary / visibility 境界 | `DONE` | 0.5 day / 半日 | Chose generated sync-status hint badges plus sample30 checker-visible App-local/server handoff summary. |
| SH2 | Runtime/artifact state model / runtime・artifact state model | `DONE` | 0.5 day / 半日 | `local-copy` contracts now expose sync status hints, and generated runtime HTML renders a sync status badge for list/detail screens. |
| SH3 | Generated preview/checker polish / generated preview・checker polish | `DONE` | 0.5 - 1 day / 半日 - 1 日 | sample30 checker now reports App-local processed and server processed handoff states. |
| SH4 | Smoke and docs / smoke・docs | `DONE` | 0.5 day / 半日 | Verified sample30/full test and updated README/report/current plan. |

Boundary / 境界:

- In scope: sample30-visible sync handoff status, existing managed operation outbox lifecycle, existing App-local and server-side handlers, generated/runtime artifact presentation or checker-visible state, focused smoke. / 対象: sample30 で見える sync handoff status、既存 managed operation outbox lifecycle、既存 App-local / server-side handler、generated/runtime artifact presentation または checker-visible state、focused smoke。
- Out of scope: remote transport, conflict resolution, retry scheduling changes, new operator/admin workflow, visual builder, native/Flutter target. / 対象外: remote transport、conflict resolution、retry scheduling 変更、新 operator/admin workflow、visual builder、native / Flutter target。
- Verification: focused sample30 smoke first; run `make test` if shared runtime/foundation behavior changes. / 検証: まず focused sample30 smoke。shared runtime / foundation behavior を触る場合は `make test`。

## Post-Sync Handoff Visibility No-Code Product Goal Replan / sync handoff visibility 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Sync Handoff Visibility No-Code Product Goal Replan](reports/2026/2026-0630-post-sync-handoff-visibility-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Sync Handoff Visibility No-Code Product Goal Replan](reports/2026/2026-0630-post-sync-handoff-visibility-no-code-product-goal-replan.md)。

This planning item selected Operator/admin no-code workflow as the next active implementation item. / この planning item では Operator/admin no-code workflow を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Operator/admin no-code workflow | Show how an operator chooses, publishes, or inspects generated no-code runtime artifacts. | 1 - 3 days / 1 - 3 日 | Selected. The data path and handoff state are now visible enough to expose an operator-facing inspection workflow. |
| Additional sync behavior pressure | Add a small retry/error-state or merge edge proof after handoff visibility. | 1 - 3 days / 1 - 3 日 | Deferred. Useful, but less product-facing than an operator/admin inspection path. |
| Another product-facing no-code sample/polish slice | Add another sample or polish only if sample30 visibility exposes a concrete gap. | 0.5 - 3 days / 半日 - 3 日 | Deferred. No concrete new domain/presentation blocker is identified. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

## Operator/Admin No-Code Workflow First Slice / operator/admin no-code workflow first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Operator/Admin No-Code Workflow First Slice](reports/2026/2026-0630-operator-admin-no-code-workflow-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Operator/Admin No-Code Workflow First Slice](reports/2026/2026-0630-operator-admin-no-code-workflow-first-slice.md)。

This implementation work was selected after sync handoff visibility polish and is complete for the first slice. / これは sync handoff visibility polish 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| OA1 | Operator surface boundary / operator surface 境界 | `DONE` | 0.5 day / 半日 | Chose the existing Source Outputs admin page as the smallest operator/admin inspection surface. |
| OA2 | No-code artifact inspection model / no-code artifact inspection model | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added a reusable inspection helper that summarizes `NO-CODE-RUNTIME`, latest artifact, preview paths, screen/action counts, and sync hints. |
| OA3 | Admin/operator view integration / admin/operator view integration | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added an inspection-only summary section to `/projects/{project}/source-outputs` without editing metadata or expanding publish workflow. |
| OA4 | Sample coverage and docs / sample coverage・docs | `DONE` | 0.5 day / 半日 | Added focused PHPUnit coverage and updated report/current plan. |

Boundary / 境界:

- In scope: inspection-only operator/admin surface, existing `NO-CODE-RUNTIME` Source Output artifacts, latest artifact/published preview metadata, generated screen/action summary, sync hint visibility. / 対象: inspection-only の operator/admin surface、既存 `NO-CODE-RUNTIME` Source Output artifact、latest artifact / published preview metadata、generated screen/action summary、sync hint visibility。
- Out of scope: visual builder, metadata editing workflow, publish approval workflow, remote transport, conflict resolution, native/Flutter target. / 対象外: visual builder、metadata 編集 workflow、publish approval workflow、remote transport、conflict resolution、native / Flutter target。
- Verification: focused PHPUnit for the inspection model first; run `make test` if shared source-output behavior changes. / 検証: まず inspection model の focused PHPUnit。shared source-output behavior を触る場合は `make test`。

## Post-Operator/Admin No-Code Product Goal Replan / operator/admin no-code 後の product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Operator/Admin No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-admin-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Operator/Admin No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-admin-no-code-product-goal-replan.md)。

This planning item selected Operator preview health/detail links as the next active implementation item. / この planning item では Operator preview health/detail links を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Operator inspection follow-up | Add the smallest missing operator affordance discovered from the first inspection surface, such as artifact detail linking or preview health. | 0.5 - 2 days / 半日 - 2 日 | Selected. The first surface shows counts, but operators still need a compact health signal and direct routes into the generated artifact. |
| No-code runtime product polish | Improve generated runtime behavior only if the operator inspection makes a concrete preview gap visible. | 0.5 - 2 days / 半日 - 2 日 | Deferred. No new generated runtime behavior gap is confirmed yet. |
| Sync/error-state pressure | Add a narrow retry/error/conflict visibility proof after the handoff and inspection path. | 1 - 3 days / 1 - 3 日 | Deferred. Useful, but the operator workflow should first make current artifact health clearer. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose the next small no-code product-facing implementation after generated artifact inspection became visible. / 対象: generated artifact inspection が見えるようになった後の次の小さな no-code product-facing implementation を選ぶ。
- Out of scope: broad visual builder, metadata editing workflow, publish approval workflow, native/Flutter target. / 対象外: 広い visual builder、metadata 編集 workflow、publish approval workflow、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## Operator Preview Health/Detail Links First Slice / operator preview health・detail link first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Operator Preview Health Detail Links First Slice](reports/2026/2026-0630-operator-preview-health-detail-links-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Operator Preview Health Detail Links First Slice](reports/2026/2026-0630-operator-preview-health-detail-links-first-slice.md)。

This implementation work was selected after the post-operator/admin no-code product-goal replan and is complete for the first slice. / これは operator/admin 後の no-code product-goal replan で選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| OH1 | Health model boundary / health model 境界 | `DONE` | 0.5 day / 半日 | Added `ready` / `warning` / `missing` health states from existing `NO-CODE-RUNTIME` definition/artifact/preview metadata. |
| OH2 | Detail-link affordances / detail link 導線 | `DONE` | 0.5 day / 半日 | Surfaced definition detail, latest artifact download when archive is available, and preview file paths without adding publish workflow. |
| OH3 | Operator page integration / operator page integration | `DONE` | 0.5 day / 半日 | Added compact health/detail affordances to the existing Source Outputs admin page. |
| OH4 | Focused coverage and docs / focused coverage・docs | `DONE` | 0.5 day / 半日 | Added focused PHPUnit coverage and updated report/current plan. |

Boundary / 境界:

- In scope: existing Source Outputs admin page, existing `NO-CODE-RUNTIME` Source Output artifacts, health derived from available definition/latest artifact/preview JSON/HTML, direct detail/download/path affordances. / 対象: 既存 Source Outputs admin page、既存 `NO-CODE-RUNTIME` Source Output artifact、definition / latest artifact / preview JSON / HTML から導く health、direct detail / download / path affordance。
- Out of scope: visual builder, metadata editing workflow, publish approval workflow, remote transport, conflict resolution, new generated runtime behavior. / 対象外: visual builder、metadata 編集 workflow、publish approval workflow、remote transport、conflict resolution、新しい generated runtime behavior。
- Verification: focused PHPUnit for health model first; run `make test` if shared source-output/operator page behavior changes. / 検証: まず health model の focused PHPUnit。shared source-output / operator page behavior を触る場合は `make test`。

## Post-Operator Preview Health No-Code Product Goal Replan / operator preview health 後の product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Operator Preview Health No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-preview-health-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Operator Preview Health No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-preview-health-no-code-product-goal-replan.md)。

This planning item selected Operator source-output artifact detail as the next active implementation item. / この planning item では Operator source-output artifact detail を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Operator artifact detail follow-up | Add the smallest artifact detail or preview accessibility improvement if health/linking exposes a concrete operator gap. | 0.5 - 2 days / 半日 - 2 日 | Selected. Health/detail links now point at artifact identity, but there is no read-only artifact detail page between list summary and archive download. |
| No-code runtime product polish | Improve generated runtime behavior only if operator health highlights a concrete preview/runtime issue. | 0.5 - 2 days / 半日 - 2 日 | Deferred. No generated runtime behavior gap is confirmed by the health surface. |
| Sync/error-state pressure | Add a narrow retry/error/conflict visibility proof after the handoff and operator inspection path. | 1 - 3 days / 1 - 3 日 | Deferred. Useful, but less directly tied to the current operator artifact inspection gap. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose the next small no-code product-facing implementation after operator artifact health became visible. / 対象: operator artifact health が見えるようになった後の次の小さな no-code product-facing implementation を選ぶ。
- Out of scope: broad visual builder, metadata editing workflow, publish approval workflow, native/Flutter target. / 対象外: 広い visual builder、metadata 編集 workflow、publish approval workflow、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## Operator Source-Output Artifact Detail First Slice / operator source-output artifact detail first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Operator Source-Output Artifact Detail First Slice](reports/2026/2026-0630-operator-source-output-artifact-detail-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Operator Source-Output Artifact Detail First Slice](reports/2026/2026-0630-operator-source-output-artifact-detail-first-slice.md)。

This implementation work was selected after the post-operator preview health no-code product-goal replan and is complete for the first slice. / これは operator preview health 後の no-code product-goal replan で選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| OD1 | Route and auth boundary / route・auth 境界 | `DONE` | 0.5 day / 半日 | Added a read-only artifact detail route using the existing project/source-output artifact authorization boundary. |
| OD2 | Artifact detail page / artifact detail page | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Rendered manifest, archive, bundle, runtime source, file counts, source output identity, and download affordance. |
| OD3 | Operator links / operator link | `DONE` | 0.5 day / 半日 | Linked latest/listed artifacts to the detail page from existing Source Outputs surfaces. |
| OD4 | Focused coverage and docs / focused coverage・docs | `DONE` | 0.5 day / 半日 | Added route/auth-focused coverage and updated report/current plan. |

Boundary / 境界:

- In scope: read-only artifact detail route/page, existing source-output artifact manifests, archive/download affordance, route/auth contract, existing Source Outputs surfaces. / 対象: read-only artifact detail route/page、既存 source-output artifact manifest、archive/download affordance、route/auth contract、既存 Source Outputs surface。
- Out of scope: artifact editing, publish approval workflow, visual builder, generated runtime behavior changes, remote transport, conflict resolution. / 対象外: artifact 編集、publish approval workflow、visual builder、generated runtime behavior 変更、remote transport、conflict resolution。
- Verification: route/auth focused PHPUnit first; run `make test` because routing/shared admin surface changes. / 検証: まず route/auth focused PHPUnit。routing / shared admin surface を触るため `make test` を実行。

## Post-Operator Artifact Detail No-Code Product Goal Replan / operator artifact detail 後の product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Operator Artifact Detail No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-artifact-detail-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Operator Artifact Detail No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-artifact-detail-no-code-product-goal-replan.md)。

This planning item selected Sync error-state visibility as the next active implementation item. / この planning item では Sync error-state visibility を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Operator artifact detail follow-up | Improve artifact detail only if this first page exposes a concrete missing inspection field. | 0.5 - 2 days / 半日 - 2 日 | Deferred. The first detail page closes the inspection gap without exposing an immediate missing field. |
| No-code runtime product polish | Improve generated runtime behavior only if artifact detail highlights a concrete preview/runtime issue. | 0.5 - 2 days / 半日 - 2 日 | Deferred. No runtime behavior gap is confirmed by artifact detail inspection. |
| Sync/error-state pressure | Add a narrow retry/error/conflict visibility proof after the handoff and operator inspection path. | 1 - 3 days / 1 - 3 日 | Selected. Success paths are now visible; the next product-facing gap is showing a failed sync/outbox state without adding transport or conflict resolution. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose the next small no-code product-facing implementation after artifact detail inspection became available. / 対象: artifact detail inspection が使えるようになった後の次の小さな no-code product-facing implementation を選ぶ。
- Out of scope: broad visual builder, metadata editing workflow, publish approval workflow, native/Flutter target. / 対象外: 広い visual builder、metadata 編集 workflow、publish approval workflow、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## Sync Error-State Visibility First Slice / sync error-state visibility first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Sync Error-State Visibility First Slice](reports/2026/2026-0630-sync-error-state-visibility-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Sync Error-State Visibility First Slice](reports/2026/2026-0630-sync-error-state-visibility-first-slice.md)。

This implementation work was selected after the post-operator artifact detail no-code product-goal replan and is complete for the first slice. / これは operator artifact detail 後の no-code product-goal replan で選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| SE1 | Error-state boundary / error-state 境界 | `DONE` | 0.5 day / 半日 | Chose a minimal failed sync/outbox state using existing outbox `failed` / `last_error` fields. |
| SE2 | Sample-visible failed state / sample-visible failed state | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Extended sample30 checker/result with one deterministic failed outbox processing path. |
| SE3 | Assertion coverage / assertion coverage | `DONE` | 0.5 day / 半日 | Asserted failed status, attempts, and last_error without changing success-path behavior. |
| SE4 | Docs and verification / docs・verification | `DONE` | 0.5 day / 半日 | Updated README/report/current plan and verified with sample30 plus full test. |

Boundary / 境界:

- In scope: sample30-visible failed sync/outbox status, existing outbox lifecycle fields, deterministic local/server handler failure, focused assertions. / 対象: sample30 で見える failed sync/outbox status、既存 outbox lifecycle field、deterministic な local/server handler failure、focused assertion。
- Out of scope: retry scheduler, remote transport, conflict resolution, broad operator dashboard, generated runtime behavior changes. / 対象外: retry scheduler、remote transport、conflict resolution、広い operator dashboard、generated runtime behavior 変更。
- Verification: sample30 pack runtime test first; run `make test` because sample checker behavior changes. / 検証: まず sample30 pack runtime test。sample checker behavior を触るため `make test` を実行。

## Post-Sync Error-State Visibility No-Code Product Goal Replan / sync error-state visibility 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Sync Error-State Visibility No-Code Product Goal Replan](reports/2026/2026-0630-post-sync-error-state-visibility-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Sync Error-State Visibility No-Code Product Goal Replan](reports/2026/2026-0630-post-sync-error-state-visibility-no-code-product-goal-replan.md)。

This planning item chose Operator failed-sync inspection as the next active implementation item. / この planning item では Operator failed-sync inspection を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Sync retry visibility | Add a narrow retry/requeue proof on top of the existing failed outbox state. | 1 - 3 days / 1 - 3 日 | Deferred. First make the failed state visible to an operator before adding behavior for retrying it. |
| Operator failed-sync inspection | Surface failed outbox state in an operator/admin page using existing status and last_error fields. | 1 - 3 days / 1 - 3 日 | Selected. This is the smallest product-facing continuation after sample30 made failed outbox state deterministic. |
| No-code runtime error feedback | Improve generated runtime feedback only if the failed outbox path exposes a concrete user-facing runtime gap. | 0.5 - 2 days / 半日 - 2 日 | Deferred. The confirmed gap is operator inspection, not generated runtime behavior. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose the next small no-code product-facing implementation after failed outbox visibility. / 対象: failed outbox visibility 後の次の小さな no-code product-facing implementation を選ぶ。
- Out of scope: broad visual builder, remote transport, full retry scheduler, conflict resolution, native/Flutter target. / 対象外: 広い visual builder、remote transport、full retry scheduler、conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## Operator Failed-Sync Inspection First Slice / operator failed-sync inspection first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Operator Failed-Sync Inspection First Slice](reports/2026/2026-0630-operator-failed-sync-inspection-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Operator Failed-Sync Inspection First Slice](reports/2026/2026-0630-operator-failed-sync-inspection-first-slice.md)。

This implementation work was selected after failed sync/outbox state became sample-visible and is complete for the first slice. / これは failed sync/outbox state が sample-visible になった後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| OF1 | Inspection boundary / inspection 境界 | `DONE` | 0.5 day / 半日 | Chose the existing Source Outputs admin page as the smallest read-only operator/admin surface. |
| OF2 | Outbox summary helper / outbox summary helper | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Reused existing outbox repository/status/last_error fields to produce a failed-item summary. |
| OF3 | Admin/operator view integration / admin/operator view integration | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Showed failed sync items without adding retry/edit behavior. |
| OF4 | Assertion coverage / assertion coverage | `DONE` | 0.5 day / 半日 | Added focused helper tests for failed item visibility and empty-state behavior. |
| OF5 | Docs and verification / docs・verification | `DONE` | 0.5 day / 半日 | Updated report/current plan and verified with PHP lint plus full `make test`. |

Boundary / 境界:

- In scope: read-only operator/admin failed sync inspection, existing outbox status / attempts / last_error fields, focused tests. / 対象: read-only の operator/admin failed sync inspection、既存 outbox status / attempts / last_error field、focused test。
- Out of scope: retry/requeue action, remote transport, conflict resolution, generated runtime behavior changes, broad dashboard. / 対象外: retry / requeue action、remote transport、conflict resolution、generated runtime behavior 変更、広い dashboard。
- Verification: focused PHP/PHPUnit first; run `make test` because the operator/admin surface changes. / 検証: まず focused PHP / PHPUnit。operator/admin surface を触るため `make test` を実行。

## Post-Operator Failed-Sync Inspection No-Code Product Goal Replan / operator failed-sync inspection 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Operator Failed-Sync Inspection No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-failed-sync-inspection-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Operator Failed-Sync Inspection No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-failed-sync-inspection-no-code-product-goal-replan.md)。

This planning item chose Operator sync outbox detail as the next active implementation item. / この planning item では Operator sync outbox detail を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Sync retry/requeue action | Add a narrow operator action for retrying failed outbox items. | 1 - 3 days / 1 - 3 日 | Deferred. Retry needs a safe read-only item detail surface first. |
| Operator sync outbox detail page | Add a read-only detail page for one outbox item before adding retry behavior. | 0.5 - 2 days / 半日 - 2 日 | Selected. This is the smallest operator-facing continuation after list-level failed sync inspection. |
| No-code runtime error feedback | Improve generated runtime feedback only if operator inspection exposes a runtime-facing gap. | 0.5 - 2 days / 半日 - 2 日 | Deferred. The confirmed gap remains operator diagnosis, not runtime behavior. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose the next small no-code product-facing implementation after read-only failed sync inspection. / 対象: read-only failed sync inspection 後の次の小さな no-code product-facing implementation を選ぶ。
- Out of scope: broad visual builder, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## Operator Sync Outbox Detail First Slice / operator sync outbox detail first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Operator Sync Outbox Detail First Slice](reports/2026/2026-0630-operator-sync-outbox-detail-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Operator Sync Outbox Detail First Slice](reports/2026/2026-0630-operator-sync-outbox-detail-first-slice.md)。

This implementation work was selected after read-only failed sync list inspection and is complete for the first slice. / これは read-only failed sync list inspection 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| OD1 | Detail boundary / detail 境界 | `DONE` | 0.5 day / 半日 | Added read-only detail before retry/requeue actions. |
| OD2 | Route and lookup / route・lookup | `DONE` | 0.5 day / 半日 | Added a project-scoped outbox detail route using existing dedupe_key lookup. |
| OD3 | Detail page / detail page | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Shows status, attempts, last_error, endpoints, operation metadata, dedupe key, timestamps, and intent payload. |
| OD4 | Admin list link / admin list link | `DONE` | 0.5 day / 半日 | Linked failed items from Source Outputs sync inspection to the detail page. |
| OD5 | Tests and docs / tests・docs | `DONE` | 0.5 day / 半日 | Added route/auth coverage, updated report/current plan, and verified with full `make test`. |

Boundary / 境界:

- In scope: read-only operator/admin outbox item detail, existing outbox fields, project-scoped routing, focused tests. / 対象: read-only operator/admin outbox item detail、既存 outbox field、project-scoped route、focused test。
- Out of scope: retry/requeue action, status mutation, remote transport, conflict resolution, broad dashboard. / 対象外: retry / requeue action、status mutation、remote transport、conflict resolution、広い dashboard。
- Verification: focused PHP/PHPUnit first; run `make test` if route/auth or shared surface changes. / 検証: まず focused PHP / PHPUnit。route / auth または shared surface を触る場合は `make test` を実行。

## Post-Operator Sync Outbox Detail No-Code Product Goal Replan / operator sync outbox detail 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Operator Sync Outbox Detail No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-sync-outbox-detail-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Operator Sync Outbox Detail No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-sync-outbox-detail-no-code-product-goal-replan.md)。

This planning item chose Sync retry eligibility guard as the next active implementation item. / この planning item では Sync retry eligibility guard を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Sync retry/requeue action | Add a narrow operator action for retrying failed outbox items. | 1 - 3 days / 1 - 3 日 | Deferred. Add a fail-closed eligibility decision first, then wire an action. |
| Retry eligibility guard | Add a small fail-closed eligibility helper before exposing an action button. | 0.5 - 2 days / 半日 - 2 日 | Selected. This is the smallest safe continuation before mutating failed outbox state. |
| No-code runtime error feedback | Improve generated runtime feedback only if operator detail exposes a runtime-facing gap. | 0.5 - 2 days / 半日 - 2 日 | Deferred. The confirmed gap remains operator retry readiness, not runtime behavior. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose the next small no-code product-facing implementation after read-only sync outbox detail. / 対象: read-only sync outbox detail 後の次の小さな no-code product-facing implementation を選ぶ。
- Out of scope: broad visual builder, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## Sync Retry Eligibility Guard First Slice / sync retry eligibility guard first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Sync Retry Eligibility Guard First Slice](reports/2026/2026-0630-sync-retry-eligibility-guard-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Sync Retry Eligibility Guard First Slice](reports/2026/2026-0630-sync-retry-eligibility-guard-first-slice.md)。

This implementation work was selected before adding retry/requeue actions and is complete for the first slice. / これは retry / requeue action を追加する前に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RG1 | Eligibility boundary / eligibility 境界 | `DONE` | 0.5 day / 半日 | Defined a fail-closed retry eligibility decision for sync outbox items. |
| RG2 | Helper contract / helper contract | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added a pure helper that returns allowed, reasons, and action label without mutating state. |
| RG3 | Operator detail visibility / operator detail visibility | `DONE` | 0.5 day / 半日 | Shows the read-only eligibility decision on the sync outbox detail page. |
| RG4 | Tests and docs / tests・docs | `DONE` | 0.5 day / 半日 | Added focused eligibility tests, updated report/current plan, and verified with full `make test`. |

Boundary / 境界:

- In scope: pure retry eligibility decision, failed outbox items, existing status / attempts / last_error fields, read-only operator visibility. / 対象: pure な retry eligibility decision、failed outbox item、既存 status / attempts / last_error field、read-only operator visibility。
- Out of scope: retry/requeue mutation, background scheduler, remote transport, conflict resolution, broad dashboard. / 対象外: retry / requeue mutation、background scheduler、remote transport、conflict resolution、広い dashboard。
- Verification: focused PHP/PHPUnit first; run `make test` if shared route/operator detail behavior changes. / 検証: まず focused PHP / PHPUnit。shared route / operator detail behavior を触る場合は `make test`。

## Post-Sync Retry Eligibility Guard No-Code Product Goal Replan / sync retry eligibility guard 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Sync Retry Eligibility Guard No-Code Product Goal Replan](reports/2026/2026-0630-post-sync-retry-eligibility-guard-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Sync Retry Eligibility Guard No-Code Product Goal Replan](reports/2026/2026-0630-post-sync-retry-eligibility-guard-no-code-product-goal-replan.md)。

This planning item chose Operator sync retry action as the next active implementation item. / この planning item では Operator sync retry action を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Sync retry/requeue action | Add a narrow operator action for retrying eligible failed outbox items. | 1 - 3 days / 1 - 3 日 | Selected. Eligibility guard is now in place, so the smallest product-facing mutation is requeue-to-pending. |
| Retry audit trail | Add a small audit note for retry attempts before or with mutation. | 0.5 - 2 days / 半日 - 2 日 | Deferred. Existing permission audit and updated_at are sufficient for this first mutation slice. |
| No-code runtime error feedback | Improve generated runtime feedback only if retry readiness exposes a runtime-facing gap. | 0.5 - 2 days / 半日 - 2 日 | Deferred. The confirmed gap remains operator retry action, not runtime behavior. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose the next small no-code product-facing implementation after retry eligibility guard. / 対象: retry eligibility guard 後の次の小さな no-code product-facing implementation を選ぶ。
- Out of scope: broad visual builder, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## Operator Sync Retry Action First Slice / operator sync retry action first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Operator Sync Retry Action First Slice](reports/2026/2026-0630-operator-sync-retry-action-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Operator Sync Retry Action First Slice](reports/2026/2026-0630-operator-sync-retry-action-first-slice.md)。

This implementation work was selected after the retry eligibility guard and is complete for the first slice. / これは retry eligibility guard 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RA1 | Mutation boundary / mutation 境界 | `DONE` | 0.5 day / 半日 | Requeues eligible failed items to pending and does not process inline. |
| RA2 | Repository wrapper / repository wrapper | `DONE` | 0.5 day / 半日 | Added a small retry/requeue wrapper around existing status update behavior. |
| RA3 | Operator action / operator action | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added CSRF-protected POST action on sync outbox detail. |
| RA4 | Tests and docs / tests・docs | `DONE` | 0.5 day / 半日 | Added focused repository/operator contract tests, updated report/current plan, and verified. |

Boundary / 境界:

- In scope: eligible failed item -> pending, clear last_error, keep attempts unchanged until processor claims it, project-scoped POST action, CSRF, focused tests. / 対象: eligible failed item -> pending、last_error clear、processor が claim するまでは attempts は増やさない、project-scoped POST action、CSRF、focused test。
- Out of scope: immediate processing, background scheduler, remote transport, conflict resolution, broad dashboard, retry audit table. / 対象外: immediate processing、background scheduler、remote transport、conflict resolution、広い dashboard、retry audit table。
- Verification: focused PHP/PHPUnit first; run `make test` because repository/operator mutation behavior changes. / 検証: まず focused PHP / PHPUnit。repository / operator mutation behavior を触るため `make test`。

## Post-Operator Sync Retry Action No-Code Product Goal Replan / operator sync retry action 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Operator Sync Retry Action No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-sync-retry-action-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Operator Sync Retry Action No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-sync-retry-action-no-code-product-goal-replan.md)。

This planning item selected Retry processing smoke as the next active implementation item. / この planning item では Retry processing smoke を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Operator retry feedback polish | Make the existing operator retry flow easier to understand after requeue. | 0.5 - 2 days / 半日 - 2 日 | Deferred. The operator action already has a narrow success message; product confidence needs processor proof first. |
| Retry processing smoke / retry processing smoke | Prove requeued items are picked up by the existing processor path in a focused sample or repository flow. | 0.5 - 2 days / 半日 - 2 日 | Selected. This closes the behavior loop after requeue without broadening retry UI or scheduling. |
| No-code runtime error/retry visibility | Surface retry-related state in generated/runtime artifacts. | 1 - 3 days / 1 - 3 日 | Deferred. Runtime-facing retry visibility should follow processor confidence. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose one next small product-facing implementation after retry action, based on visible gap and risk. / 対象: retry action 後の visible gap と risk に基づいて、次の小さな product-facing implementation を 1 つ選ぶ。
- Out of scope: broad visual builder, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## Retry Processing Smoke First Slice / retry processing smoke first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Retry Processing Smoke First Slice](reports/2026/2026-0630-retry-processing-smoke-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Retry Processing Smoke First Slice](reports/2026/2026-0630-retry-processing-smoke-first-slice.md)。

This implementation work was selected after operator sync retry action and is complete for the first slice. / これは operator sync retry action 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RS1 | Smoke boundary / smoke 境界 | `DONE` | 0.5 day / 半日 | Proved requeued `pending` item processing only; did not add scheduler, transport, or conflict resolution. |
| RS2 | Focused processor fixture / focused processor fixture | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Extended a focused repository/processor flow from failed -> requeued pending -> existing processor claim/handler. |
| RS3 | Assertions and docs / assertions・docs | `DONE` | 0.5 day / 半日 | Asserted final status, attempts behavior, cleared error, and existing processor result; updated report/current plan. |

Boundary / 境界:

- In scope: one deterministic retry processing smoke, existing outbox processor, existing handlers, existing requeue action semantics. / 対象: deterministic な retry processing smoke 1 つ、既存 outbox processor、既存 handler、既存 requeue action semantics。
- Out of scope: background scheduler, new retry UI, remote transport, conflict resolution, retry audit table, broad dashboard. / 対象外: background scheduler、新 retry UI、remote transport、conflict resolution、retry audit table、広い dashboard。
- Verification: focused PHPUnit/sample smoke first; run `make test` if shared processor or sample behavior changes. / 検証: まず focused PHPUnit / sample smoke。shared processor または sample behavior を触る場合は `make test`。

## Post-Retry Processing Smoke No-Code Product Goal Replan / retry processing smoke 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Retry Processing Smoke No-Code Product Goal Replan](reports/2026/2026-0630-post-retry-processing-smoke-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Retry Processing Smoke No-Code Product Goal Replan](reports/2026/2026-0630-post-retry-processing-smoke-no-code-product-goal-replan.md)。

This planning item selected Operator retry feedback polish as the next active implementation item. / この planning item では Operator retry feedback polish を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Operator retry feedback polish | Make the retry flow easier for operators to understand after the behavior loop is proven. | 0.5 - 2 days / 半日 - 2 日 | Selected. The behavior loop is proven, so the next smallest product gap is clarity after requeue. |
| No-code runtime error/retry visibility | Surface failed/retryable/requeued state in generated/runtime artifacts. | 1 - 3 days / 1 - 3 日 | Deferred. Runtime-facing state should wait until the operator flow is understandable. |
| Retry audit trail | Add a narrow audit note for operator retry mutation. | 0.5 - 2 days / 半日 - 2 日 | Deferred. Existing updated_at/status/attempts plus clear operator feedback are enough for the next slice. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose one next small product-facing implementation after retry processing smoke. / 対象: retry processing smoke 後の次の小さな product-facing implementation を 1 つ選ぶ。
- Out of scope: broad visual builder, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## Operator Retry Feedback Polish First Slice / operator retry feedback polish first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Operator Retry Feedback Polish First Slice](reports/2026/2026-0630-operator-retry-feedback-polish-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Operator Retry Feedback Polish First Slice](reports/2026/2026-0630-operator-retry-feedback-polish-first-slice.md)。

This implementation work was selected after retry processing smoke and is complete for the first slice. / これは retry processing smoke 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RF1 | Feedback boundary / feedback 境界 | `DONE` | 0.5 day / 半日 | Improved post-requeue operator clarity only; did not add scheduler, transport, conflict resolution, or audit tables. |
| RF2 | Detail page result copy/state / detail page result copy・state | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Success state explains that the item is pending and can be picked up by the existing processor. |
| RF3 | Contract and docs / contract・docs | `DONE` | 0.5 day / 半日 | Added focused page/source contract coverage, updated report/current plan, and verified. |

Boundary / 境界:

- In scope: operator detail feedback after retry, current status/attempts/last_error clarity, existing processor next-step wording. / 対象: retry 後の operator detail feedback、現在 status / attempts / last_error の分かりやすさ、既存 processor next-step wording。
- Out of scope: scheduler, transport, conflict resolution, retry audit table, broad dashboard, generated runtime UI. / 対象外: scheduler、transport、conflict resolution、retry audit table、広い dashboard、generated runtime UI。
- Verification: focused PHP/source contract first; run `make test` if shared route/operator behavior changes. / 検証: まず focused PHP / source contract。shared route / operator behavior を触る場合は `make test`。

## Post-Operator Retry Feedback Polish No-Code Product Goal Replan / operator retry feedback polish 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Operator Retry Feedback Polish No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-retry-feedback-polish-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Operator Retry Feedback Polish No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-retry-feedback-polish-no-code-product-goal-replan.md)。

This planning item selected No-code runtime error/retry visibility as the next active implementation item. / この planning item では No-code runtime error/retry visibility を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| No-code runtime error/retry visibility | Surface failed/retryable/requeued state in generated/runtime artifacts. | 1 - 3 days / 1 - 3 日 | Selected. Operator retry is understandable now; runtime-visible error/retry state is the next product gap. |
| Retry audit trail | Add a narrow audit note for operator retry mutation. | 0.5 - 2 days / 半日 - 2 日 | Deferred. Accountability is less visible to the product path than runtime error/retry state. |
| Another operator workflow polish slice | Polish the Source Outputs / sync outbox navigation only if review exposes a concrete operator workflow gap. | 0.5 - 2 days / 半日 - 2 日 | Deferred. No concrete new operator navigation gap is identified. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose one next small product-facing implementation after operator retry feedback polish. / 対象: operator retry feedback polish 後の次の小さな product-facing implementation を 1 つ選ぶ。
- Out of scope: broad visual builder, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## No-Code Runtime Error/Retry Visibility First Slice / no-code runtime error/retry visibility first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 No-Code Runtime Error/Retry Visibility First Slice](reports/2026/2026-0630-no-code-runtime-error-retry-visibility-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 No-Code Runtime Error/Retry Visibility First Slice](reports/2026/2026-0630-no-code-runtime-error-retry-visibility-first-slice.md)。

This implementation work was selected after operator retry feedback polish and is complete for the first slice. / これは operator retry feedback polish 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RV1 | Runtime visibility boundary / runtime visibility 境界 | `DONE` | 0.5 day / 半日 | Surfaced failed/retryable sync state only; retry mutation remains in operator/admin pages. |
| RV2 | Generated runtime state model / generated runtime state model | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added deterministic runtime artifact data and HTML hints for failed/retryable state. |
| RV3 | Sample smoke and docs / sample smoke・docs | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Updated focused runtime/sample smoke, report/current plan, and verification record. |

Boundary / 境界:

- In scope: generated/runtime-visible sync error or retryable state, read-only hints, existing sample/runtime smoke. / 対象: generated / runtime-visible な sync error または retryable state、read-only hint、既存 sample / runtime smoke。
- Out of scope: retry mutation in generated runtime, scheduler, transport, conflict resolution, retry audit table, broad dashboard. / 対象外: generated runtime 内の retry mutation、scheduler、transport、conflict resolution、retry audit table、広い dashboard。
- Verification: focused runtime/sample smoke first; run `make test` if shared generator/runtime behavior changes. / 検証: まず focused runtime / sample smoke。shared generator / runtime behavior を触る場合は `make test`。

## React-First No-Code Web Framework Bridge First Slice / React-first no-code Web framework bridge first slice

Status: `FIRST_SLICE_DONE`. FS plan: [2026-0630 React-first no-code Web framework bridge FS plan](reports/2026/2026-0630-react-first-no-code-web-framework-bridge-fs-plan.md). Report: [2026-0630 React-first no-code Web framework bridge first slice](reports/2026/2026-0630-react-first-no-code-web-framework-bridge-first-slice.md). / Status: `FIRST_SLICE_DONE`。FS plan: [2026-0630 React-first no-code Web framework bridge FS plan](reports/2026/2026-0630-react-first-no-code-web-framework-bridge-fs-plan.md)。Report: [2026-0630 React-first no-code Web framework bridge first slice](reports/2026/2026-0630-react-first-no-code-web-framework-bridge-first-slice.md)。

This first slice chose React + TypeScript as the first adapter direction and added a framework-facing artifact while keeping Mtool's ownership at the JSON/action-intent boundary. / この first slice では React + TypeScript を first adapter の方向として選び、Mtool の責務を JSON / action-intent 境界に保ったまま framework-facing artifact を追加しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RF1 | React-first FS decision / React-first FS 判断 | `DONE` | 0.5 day / 半日 | Chose a custom React + TypeScript bridge over immediate JSON Forms / rjsf conversion for the first slice, while keeping Vue / Svelte as comparison references. |
| RF2 | Source Output strategy / Source Output strategy | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added `no-code-react-bridge` and `NoCodeReactBridge`. |
| RF3 | Framework-facing artifact / framework-facing artifact | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Emits `bridge-contract.json`, React/TypeScript scaffold files, and action intent helper from existing no-code runtime payload. |
| RF4 | Sample and verification / sample・検証 | `DONE` | 0.5 day / 半日 | Added sample28 `NO-CODE-REACT-BRIDGE` seed and pack checker coverage. |

Boundary / 境界:

- In scope: React + TypeScript first adapter scaffold, framework-facing bridge contract, action intent helper, sample28 source output seed and smoke coverage. / 対象: React + TypeScript first adapter scaffold、framework-facing bridge contract、action intent helper、sample28 source output seed と smoke coverage。
- Out of scope: npm install / build proof, durable React component library ownership inside Mtool, JSON Forms / rjsf formal conversion, visual builder, full generated application shell, remote transport, full conflict resolution, native / Flutter target. / 対象外: npm install / build proof、Mtool 内で durable React component library を所有すること、JSON Forms / rjsf の正式変換、visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: focused shared contract integration test and `make sample28-pack-runtime-test`. / 検証: focused shared contract integration test と `make sample28-pack-runtime-test`。

## React Bridge Build Smoke First Slice / React bridge build smoke first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 React Bridge Build Smoke First Slice](reports/2026/2026-0630-react-bridge-build-smoke-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 React Bridge Build Smoke First Slice](reports/2026/2026-0630-react-bridge-build-smoke-first-slice.md)。

This first slice proves the generated React + TypeScript scaffold can install and build through Vite without leaving build byproducts in the source output directory. / この first slice では generated React + TypeScript scaffold が Vite で install / build でき、source output directory に build byproduct を残さないことを確認しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RB1 | Build smoke script / build smoke script | `DONE` | 0.5 day / 半日 | Added `check_no_code_react_bridge_build_smoke.js` to copy the generated bridge into `work/tmp`, run npm install/build, and verify the bridge summary. |
| RB2 | Make target / Make target | `DONE` | 0.5 day / 半日 | Added `make sample28-no-code-react-bridge-build-smoke`. |
| RB3 | Scaffold build fixes / scaffold build fixes | `DONE` | 0.5 day / 半日 | Added React type deps, fixed JSON import typing, and aligned TypeScript field/data types with actual runtime preview JSON. |

Boundary / 境界:

- In scope: sample28 generated React bridge install/build smoke, basic TypeScript/Vite buildability, and generated source output cleanliness. / 対象: sample28 generated React bridge の install / build smoke、basic TypeScript / Vite buildability、generated source output の clean さ。
- Out of scope: browser rendering smoke, visual polish, JSON Forms / rjsf transform, full generated application shell, npm package publishing. / 対象外: browser rendering smoke、visual polish、JSON Forms / rjsf transform、完全な generated application shell、npm package publishing。
- Verification: `make sample28-no-code-react-bridge-build-smoke`. / 検証: `make sample28-no-code-react-bridge-build-smoke`。

## Post-React Bridge Build Smoke No-Code Product Goal Replan / React bridge build smoke 後の no-code product goal 再計画

Status: `DONE`. / Status: `DONE`。

This planning item selected React bridge browser smoke as the next small no-code product-facing implementation after the React bridge build smoke. / この planning item では React bridge build smoke 後の次の小さな no-code product-facing implementation として React bridge browser smoke を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| React bridge browser smoke | Prove the built React bridge can render runtime screens and emit an action intent in a browser smoke. | 1 - 3 days / 1 - 3 日 | Selected. Build smoke proved package viability; browser confidence is the next smallest gap. |
| React bridge artifact contract hardening | Stabilize `bridge-contract.json` shape and add focused contract docs/tests before more runtime behavior. | 0.5 - 2 days / 半日 - 2 日 | Deferred until the browser smoke gives one more consumer-side proof. |
| JSON Forms / rjsf transform probe | Test whether Mtool screen fields should also emit JSON Schema / UI Schema for schema-form ecosystems. | 1 - 3 days / 1 - 3 日 | Deferred. Useful, but should not replace the custom bridge until the first adapter contract is stable. |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that visibility exists in both operator and runtime surfaces. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless accountability becomes the next concrete product gap. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after React bridge build smoke. / 対象: React bridge build smoke 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: implemented through React bridge browser smoke. / 検証: React bridge browser smoke として実装済み。

## React Bridge Browser Smoke First Slice / React bridge browser smoke first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 React Bridge Browser Smoke First Slice](reports/2026/2026-0630-react-bridge-browser-smoke-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 React Bridge Browser Smoke First Slice](reports/2026/2026-0630-react-bridge-browser-smoke-first-slice.md)。

This first slice proves the generated React + TypeScript scaffold can render the generated runtime screens in headless Chrome and expose the action-intent bridge for browser verification. / この first slice では generated React + TypeScript scaffold が generated runtime screen を headless Chrome で render でき、browser verification 用の action-intent bridge を公開できることを確認しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RBB1 | Browser smoke script / browser smoke script | `DONE` | 0.5 day / 半日 | Added `check_no_code_react_bridge_browser_smoke.js` to copy the generated bridge into `work/tmp`, run npm install/build, start Vite, and verify rendering with Playwright. |
| RBB2 | Make target / Make target | `DONE` | 0.5 day / 半日 | Added `make sample28-no-code-react-bridge-browser-smoke`. |
| RBB3 | Browser observability hooks / browser observability hooks | `DONE` | 0.5 day / 半日 | Added generated data attributes and browser-smoke globals for bridge contract and action-intent helper observation. |

Boundary / 境界:

- In scope: sample28 generated React bridge browser smoke, runtime screen rendering, disabled action state, operation metadata, and action-intent helper observation. / 対象: sample28 generated React bridge browser smoke、runtime screen rendering、disabled action state、operation metadata、action-intent helper observation。
- Out of scope: durable React component library ownership inside Mtool, visual polish, form input editing/client state management, JSON Forms / rjsf transform, full generated application shell, remote transport, full conflict resolution, native / Flutter target. / 対象外: Mtool 内で durable React component library を所有すること、visual polish、form input editing / client state management、JSON Forms / rjsf transform、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: `make sample28-no-code-react-bridge-browser-smoke`. / 検証: `make sample28-no-code-react-bridge-browser-smoke`。

## Post-React Bridge Browser Smoke No-Code Product Goal Replan / React bridge browser smoke 後の no-code product goal 再計画

Status: `DONE`. / Status: `DONE`。

This planning item selected React bridge display/form state shaping as the next small no-code product-facing implementation after the React bridge browser smoke. / この planning item では React bridge browser smoke 後の次の小さな no-code product-facing implementation として React bridge display / form state shaping を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| React bridge display/form state shaping | Replace raw nested runtime cell objects such as `{ value, display_value }` with cleaner display/input helpers for the React bridge. | 1 - 2 days / 1 - 2 日 | Selected. Browser smoke exposed `[object Object]` display text, so this is the most visible bridge quality gap. |
| React bridge artifact contract hardening | Stabilize `bridge-contract.json` shape and add focused contract docs/tests before more runtime behavior. | 0.5 - 2 days / 半日 - 2 日 | Deferred. Still useful after display/form shaping clarifies the consumer-facing helper boundary. |
| JSON Forms / rjsf transform probe | Test whether Mtool screen fields should also emit JSON Schema / UI Schema for schema-form ecosystems. | 1 - 3 days / 1 - 3 日 | Deferred. Useful, but should not replace the custom bridge until the first adapter contract is stable. |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that visibility exists in both operator and runtime surfaces. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless accountability becomes the next concrete product gap. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after React bridge browser smoke. / 対象: React bridge browser smoke 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: implemented through React bridge display/form state shaping. / 検証: React bridge display / form state shaping として実装済み。

## React Bridge Display/Form State Shaping First Slice / React bridge display/form state shaping first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 React Bridge Display/Form State Shaping First Slice](reports/2026/2026-0701-react-bridge-display-form-state-shaping-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 React Bridge Display/Form State Shaping First Slice](reports/2026/2026-0701-react-bridge-display-form-state-shaping-first-slice.md)。

This first slice makes the generated React bridge consume runtime cell objects intentionally: display uses `display_value` first, and action intent input uses scalar/null `value` output instead of raw cell objects. / この first slice では generated React bridge が runtime cell object を意図的に扱うようにしました。表示は `display_value` を優先し、action intent input は raw cell object ではなく scalar / null の `value` output に正規化します。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RDS1 | Runtime cell helper / runtime cell helper | `DONE` | 0.5 day / 半日 | Added `displayRuntimeValue`, `runtimeInputValue`, and shared `currentItem` helpers to the generated React bridge TypeScript helper. |
| RDS2 | React scaffold rendering / React scaffold rendering | `DONE` | 0.5 day / 半日 | Updated generated list/detail display and readonly form inputs to avoid raw `[object Object]` output. |
| RDS3 | Browser smoke coverage / browser smoke coverage | `DONE` | 0.5 day / 半日 | Extended React bridge browser smoke to verify form input rendering, no raw object display, and normalized action intent input. |

Boundary / 境界:

- In scope: generated React bridge runtime cell display helper, action-intent input normalization, readonly form field shaping, and sample28 browser smoke coverage. / 対象: generated React bridge runtime cell display helper、action-intent input normalization、readonly form field shaping、sample28 browser smoke coverage。
- Out of scope: editable form state / validation UX, visual styling polish, durable React component library ownership inside Mtool, JSON Forms / rjsf transform, full generated application shell. / 対象外: editable form state / validation UX、visual styling polish、Mtool 内で durable React component library を所有すること、JSON Forms / rjsf transform、完全な generated application shell。
- Verification: `make sample28-no-code-react-bridge-browser-smoke`. / 検証: `make sample28-no-code-react-bridge-browser-smoke`。

## Post-React Bridge Display/Form State Shaping No-Code Product Goal Replan / React bridge display/form state shaping 後の no-code product goal 再計画

Status: `DONE`. / Status: `DONE`。

This planning item selected React bridge artifact contract hardening as the next small no-code product-facing implementation after React bridge display/form state shaping. / この planning item では React bridge display / form state shaping 後の次の小さな no-code product-facing implementation として React bridge artifact contract hardening を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| React bridge artifact contract hardening | Stabilize `bridge-contract.json` shape and add focused contract docs/tests now that render/input helper behavior is clearer. | 0.5 - 2 days / 半日 - 2 日 | Selected. This is the strongest next confidence step before broader framework probes. |
| Editable React bridge form state first slice | Let generated React bridge inputs manage local edit state and emit changed scalar values. | 1 - 3 days / 1 - 3 日 | Deferred. Useful if product-facing interactivity should move after contract hardening. |
| JSON Forms / rjsf transform probe | Test whether Mtool screen fields should also emit JSON Schema / UI Schema for schema-form ecosystems. | 1 - 3 days / 1 - 3 日 | Candidate. Useful, but should not replace the custom bridge until the first adapter contract is stable. |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that visibility exists in both operator and runtime surfaces. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless accountability becomes the next concrete product gap. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after React bridge display/form state shaping. / 対象: React bridge display / form state shaping 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: implemented through React bridge artifact contract hardening. / 検証: React bridge artifact contract hardening として実装済み。

## React Bridge Artifact Contract Hardening First Slice / React bridge artifact contract hardening first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 React Bridge Artifact Contract Hardening First Slice](reports/2026/2026-0701-react-bridge-artifact-contract-hardening-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 React Bridge Artifact Contract Hardening First Slice](reports/2026/2026-0701-react-bridge-artifact-contract-hardening-first-slice.md)。

This first slice stabilizes the generated React bridge artifact contract with a schema marker and explicit invariants while keeping Mtool ownership at the JSON/action-intent boundary. / この first slice では schema marker と明示 invariant で generated React bridge artifact contract を安定化しました。Mtool の ownership は JSON / action-intent 境界に保っています。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RCH1 | Contract schema marker / contract schema marker | `DONE` | 0.5 day / 半日 | Added `contract_schema_version: no-code-react-bridge-contract-v0`. |
| RCH2 | Contract invariants / contract invariants | `DONE` | 0.5 day / 半日 | Added runtime/action-intent version, required file, required screen key, and runtime cell shape invariants. |
| RCH3 | Contract coverage / contract coverage | `DONE` | 0.5 day / 半日 | Extended build/browser smokes, sample28 checker, and shared foundation test coverage. |

Boundary / 境界:

- In scope: React bridge artifact schema marker, basic invariants, generated TypeScript contract type, sample28/browser/build/shared foundation verification. / 対象: React bridge artifact schema marker、basic invariant、generated TypeScript contract type、sample28 / browser / build / shared foundation verification。
- Out of scope: schema file publication, semantic version negotiation, durable React component library ownership inside Mtool, editable form UX, JSON Forms / rjsf transform. / 対象外: schema file publication、semantic version negotiation、Mtool 内で durable React component library を所有すること、editable form UX、JSON Forms / rjsf transform。
- Verification: `make sample28-no-code-react-bridge-browser-smoke`, `make sample28-no-code-react-bridge-build-smoke`, and `make test`. / 検証: `make sample28-no-code-react-bridge-browser-smoke`、`make sample28-no-code-react-bridge-build-smoke`、`make test`。

## Post-React Bridge Artifact Contract Hardening No-Code Product Goal Replan / React bridge artifact contract hardening 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0701 Post-React Bridge Artifact Contract Hardening No-Code Product Goal Replan](reports/2026/2026-0701-post-react-bridge-artifact-contract-hardening-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0701 Post-React Bridge Artifact Contract Hardening No-Code Product Goal Replan](reports/2026/2026-0701-post-react-bridge-artifact-contract-hardening-no-code-product-goal-replan.md)。

This planning item selected Editable React bridge form state first slice as the next small no-code product-facing implementation after React bridge artifact contract hardening. / この planning item では React bridge artifact contract hardening 後の次の小さな no-code product-facing implementation として Editable React bridge form state first slice を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Editable React bridge form state first slice | Let generated React bridge inputs manage local edit state and emit changed scalar values. | 1 - 3 days / 1 - 3 日 | Selected. This is the most direct product-facing continuation after display/input helpers and contract hardening. |
| JSON Forms / rjsf transform probe | Test whether Mtool screen fields should also emit JSON Schema / UI Schema for schema-form ecosystems. | 1 - 3 days / 1 - 3 日 | Deferred. Useful comparison, but the custom React bridge should prove editable behavior first. |
| React bridge contract documentation polish | Add human-readable consumer notes around the schema marker, invariants, and ownership boundary. | 0.5 - 1 day / 半日 - 1 日 | Deferred. Contract coverage exists; docs can catch up after the next behavior slice or if consumer docs become the blocker. |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that visibility exists in both operator and runtime surfaces. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless accountability becomes the next concrete product gap. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after React bridge artifact contract hardening. / 対象: React bridge artifact contract hardening 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only. / 検証: planning / report 更新のみ。

## Editable React Bridge Form State First Slice / editable React bridge form state first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Editable React Bridge Form State First Slice](reports/2026/2026-0701-editable-react-bridge-form-state-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Editable React Bridge Form State First Slice](reports/2026/2026-0701-editable-react-bridge-form-state-first-slice.md)。

This implementation work was selected by the post-contract-hardening replan and is complete for the first slice. / これは contract hardening 後の replan で選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| ERF1 | Form state boundary / form state boundary | `DONE` | 0.5 day / 半日 | Kept state local to generated React bridge preview; did not add persistence, transport, validation engine, or app shell. |
| ERF2 | Generated React input state / generated React input state | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Generated form inputs initialize from runtime cell display/value and keep edited scalar state. |
| ERF3 | Action intent changed values / action intent changed values | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Form action emits edited scalar values through `no-code-runtime-action-intent-v0`. |
| ERF4 | Browser smoke coverage / browser smoke coverage | `DONE` | 0.5 day / 半日 | Extended sample28 React bridge browser smoke to fill an input and verify the changed value in the observed intent. |
| ERF5 | Docs and verification / docs・verification | `DONE` | 0.5 day / 半日 | Updated report/current plan/sample README and ran focused browser/build smoke plus `make test`. |

Boundary / 境界:

- In scope: generated React bridge local form state, scalar changed-value action intent, sample28 browser smoke. / 対象: generated React bridge local form state、scalar changed-value action intent、sample28 browser smoke。
- Out of scope: visual styling polish, validation UX beyond existing metadata hints, durable React component library ownership inside Mtool, JSON Forms / rjsf transform, full generated application shell, remote transport, full conflict resolution. / 対象外: visual styling polish、既存 metadata hint を超える validation UX、Mtool 内で durable React component library を所有すること、JSON Forms / rjsf transform、完全な generated application shell、remote transport、full conflict resolution。
- Verification: `make sample28-no-code-react-bridge-browser-smoke`, `make sample28-no-code-react-bridge-build-smoke`, and `make test`. / 検証: `make sample28-no-code-react-bridge-browser-smoke`、`make sample28-no-code-react-bridge-build-smoke`、`make test`。

## Post-Editable React Bridge Form State No-Code Product Goal Replan / editable React bridge form state 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0701 Post-Editable React Bridge Form State No-Code Product Goal Replan](reports/2026/2026-0701-post-editable-react-bridge-form-state-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0701 Post-Editable React Bridge Form State No-Code Product Goal Replan](reports/2026/2026-0701-post-editable-react-bridge-form-state-no-code-product-goal-replan.md)。

This planning item selected React bridge validation hint display as the next small no-code product-facing implementation after editable React bridge form state. / この planning item では editable React bridge form state 後の次の小さな no-code product-facing implementation として React bridge validation hint display を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| JSON Forms / rjsf transform probe | Test whether Mtool screen fields should also emit JSON Schema / UI Schema for schema-form ecosystems after the custom React bridge proved editable behavior. | 1 - 3 days / 1 - 3 日 | Deferred. Useful comparison, but one more narrow custom-bridge UX proof should land first. |
| React bridge contract documentation polish | Add human-readable consumer notes around schema marker, invariants, form state, and ownership boundary. | 0.5 - 1 day / 半日 - 1 日 | Deferred. Useful, but generated behavior still had one small metadata gap. |
| React bridge validation hint display | Surface existing field required/readonly metadata in the generated React bridge without adding a validation engine. | 0.5 - 2 days / 半日 - 2 日 | Selected. Smallest product-facing continuation after editable form state. |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that visibility exists in both operator and runtime surfaces. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless accountability becomes the next concrete product gap. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after editable React bridge form state. / 対象: editable React bridge form state 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: implemented through React bridge validation hint display. / 検証: React bridge validation hint display として実装済み。

## React Bridge Validation Hint Display First Slice / React bridge validation hint display first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 React Bridge Validation Hint Display First Slice](reports/2026/2026-0701-react-bridge-validation-hint-display-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 React Bridge Validation Hint Display First Slice](reports/2026/2026-0701-react-bridge-validation-hint-display-first-slice.md)。

This first slice surfaces existing `required` / `readonly` field metadata in the generated React bridge as lightweight hints and input attributes without adding a validation engine. / この first slice では既存の `required` / `readonly` field metadata を、validation engine を追加せずに generated React bridge の lightweight hint と input attribute として表示しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RVH1 | Metadata display boundary / metadata display boundary | `DONE` | 0.5 day / 半日 | Kept scope to existing field metadata; no custom validation rules or server validation. |
| RVH2 | Generated hint rendering / generated hint rendering | `DONE` | 0.5 day / 半日 | Added required/readonly data attributes, input attributes, and lightweight field hints. |
| RVH3 | Browser smoke coverage / browser smoke coverage | `DONE` | 0.5 day / 半日 | Extended sample28 React bridge browser smoke to verify required metadata/hint while preserving edited intent coverage. |
| RVH4 | Docs and verification / docs・verification | `DONE` | 0.5 day / 半日 | Updated report/current plan/sample README and ran focused browser/build smoke plus `make test`. |

Boundary / 境界:

- In scope: generated React bridge required/readonly metadata display, input attributes, and sample28 browser smoke coverage. / 対象: generated React bridge required / readonly metadata display、input attribute、sample28 browser smoke coverage。
- Out of scope: custom validation rules, server-side validation, visual styling polish, JSON Forms / rjsf transform, full generated application shell. / 対象外: custom validation rule、server-side validation、visual styling polish、JSON Forms / rjsf transform、完全な generated application shell。
- Verification: `make sample28-no-code-react-bridge-browser-smoke`, `make sample28-no-code-react-bridge-build-smoke`, and `make test`. / 検証: `make sample28-no-code-react-bridge-browser-smoke`、`make sample28-no-code-react-bridge-build-smoke`、`make test`。

## Post-React Bridge Validation Hint Display No-Code Product Goal Replan / React bridge validation hint display 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0701 Post-React Bridge Validation Hint Display No-Code Product Goal Replan](reports/2026/2026-0701-post-react-bridge-validation-hint-display-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0701 Post-React Bridge Validation Hint Display No-Code Product Goal Replan](reports/2026/2026-0701-post-react-bridge-validation-hint-display-no-code-product-goal-replan.md)。

This planning item selected React bridge action feedback display as the next small no-code product-facing implementation after React bridge validation hint display. / この planning item では React bridge validation hint display 後の次の小さな no-code product-facing implementation として React bridge action feedback display を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| JSON Forms / rjsf transform probe | Test whether Mtool screen fields should also emit JSON Schema / UI Schema for schema-form ecosystems now that the custom React bridge proved display, edit state, and metadata hints. | 1 - 3 days / 1 - 3 日 | Deferred. Useful comparison, but the custom React bridge can absorb one more small UX proof first. |
| React bridge contract documentation polish | Add human-readable consumer notes around schema marker, invariants, form state, validation hints, and ownership boundary. | 0.5 - 1 day / 半日 - 1 日 | Deferred. Useful, but behavior remained the clearer next product-facing gap. |
| React bridge action feedback display | Surface local clicked/last-intent feedback in the generated React bridge, parallel to the HTML preview's action feedback. | 0.5 - 2 days / 半日 - 2 日 | Selected. It closes the loop from edited form input to visible generated intent feedback. |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that visibility exists in both operator and runtime surfaces. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless accountability becomes the next concrete product gap. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after React bridge validation hint display. / 対象: React bridge validation hint display 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: implemented through React bridge action feedback display. / 検証: React bridge action feedback display として実装済み。

## React Bridge Action Feedback Display First Slice / React bridge action feedback display first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 React Bridge Action Feedback Display First Slice](reports/2026/2026-0701-react-bridge-action-feedback-display-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 React Bridge Action Feedback Display First Slice](reports/2026/2026-0701-react-bridge-action-feedback-display-first-slice.md)。

This first slice displays local last-intent feedback in the generated React bridge after an action intent is created. / この first slice では action intent 作成後に generated React bridge 内で local last-intent feedback を表示します。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RAF1 | Feedback boundary / feedback boundary | `DONE` | 0.5 day / 半日 | Kept feedback local to generated React bridge preview; no persistence, transport, scheduler, or server execution. |
| RAF2 | Generated feedback rendering / generated feedback rendering | `DONE` | 0.5 day / 半日 | Added React state and feedback section with last action/screen summary. |
| RAF3 | Browser smoke coverage / browser smoke coverage | `DONE` | 0.5 day / 半日 | Extended sample28 React bridge browser smoke to verify success feedback state and action key. |

Boundary / 境界:

- In scope: local generated React bridge action feedback, last-intent display, and browser smoke coverage. / 対象: local generated React bridge action feedback、last-intent display、browser smoke coverage。
- Out of scope: server execution, persistence, transport, validation engine, visual styling polish, JSON Forms / rjsf transform. / 対象外: server execution、persistence、transport、validation engine、visual styling polish、JSON Forms / rjsf transform。
- Verification: `make sample28-no-code-react-bridge-browser-smoke`; run build/full tests after final verification pass. / 検証: `make sample28-no-code-react-bridge-browser-smoke`。最終確認で build / full test も実行する。

## Post-React Bridge Action Feedback Display No-Code Product Goal Replan / React bridge action feedback display 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0701 Post-React Bridge Action Feedback Display No-Code Product Goal Replan](reports/2026/2026-0701-post-react-bridge-action-feedback-display-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0701 Post-React Bridge Action Feedback Display No-Code Product Goal Replan](reports/2026/2026-0701-post-react-bridge-action-feedback-display-no-code-product-goal-replan.md)。

This planning item selected JSON Forms / rjsf transform probe as the next small no-code product-facing implementation after React bridge action feedback display. / この planning item では React bridge action feedback display 後の次の小さな no-code product-facing implementation として JSON Forms / rjsf transform probe を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| JSON Forms / rjsf transform probe | Test whether Mtool screen fields should also emit JSON Schema / UI Schema for schema-form ecosystems now that the custom React bridge proved display/edit/metadata/feedback. | 1 - 3 days / 1 - 3 日 | Selected. This is now the strongest comparison probe after the custom bridge path has enough behavior. |
| React bridge contract documentation polish | Add human-readable consumer notes around schema marker, invariants, form state, validation hints, feedback, and ownership boundary. | 0.5 - 1 day / 半日 - 1 日 | Deferred. Useful, but the next product-facing risk is whether Mtool's screen metadata can also map cleanly to schema-form ecosystems. |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that visibility exists in both operator and runtime surfaces. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless accountability becomes the next concrete product gap. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after React bridge action feedback display. / 対象: React bridge action feedback display 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: implemented through JSON Forms / rjsf transform probe first slice. / 検証: JSON Forms / rjsf transform probe first slice として実装する。

## JSON Forms / rjsf Transform Probe First Slice / JSON Forms・rjsf transform probe first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 JSON Forms / rjsf Transform Probe First Slice](reports/2026/2026-0701-json-forms-rjsf-transform-probe-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 JSON Forms / rjsf Transform Probe First Slice](reports/2026/2026-0701-json-forms-rjsf-transform-probe-first-slice.md)。

This first slice emits and verifies a small schema-form comparison artifact from existing no-code screen fields without replacing the custom React bridge. / この first slice では custom React bridge を置き換えず、既存 no-code screen field から小さな schema-form 比較 artifact を出力・検証しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| JF1 | Probe boundary / probe boundary | `DONE` | 0.5 day / 半日 | Kept the artifact comparison-only and scoped JSON Schema / UI Schema coverage to sample28 form metadata. |
| JF2 | Generated schema-form artifact / generated schema-form artifact | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added `no-code-json-forms-probe` Source Output strategy with `schema-form-contract.json`, `json-schema.json`, `ui-schema.json`, and README. |
| JF3 | Contract/checker coverage / contract・checker coverage | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Verified schema version, target markers, required fields, UI scopes, and sample28 source output publication. |
| JF4 | Docs and verification / docs・verification | `DONE` | 0.5 day / 半日 | Updated README/report/current plan and ran focused sample28 checks, React bridge build/browser smoke, and full tests. |

Boundary / 境界:

- In scope: one comparison artifact for schema-form ecosystems, sample28 form-field coverage, JSON Schema / UI Schema style metadata, focused verification. / 対象: schema-form ecosystem 向け比較 artifact 1 つ、sample28 form-field coverage、JSON Schema / UI Schema style metadata、focused verification。
- Out of scope: replacing the custom React bridge, installing JSON Forms / rjsf runtime UI, visual builder, full generated application shell, server execution, transport. / 対象外: custom React bridge の置き換え、JSON Forms / rjsf runtime UI 導入、visual builder、完全な generated application shell、server execution、transport。
- Verification: `php -l` on touched PHP files, `make sample28-pack-runtime-test`, `make sample28-no-code-react-bridge-build-smoke`, `make sample28-no-code-react-bridge-browser-smoke`, and `make test`. / 検証: 変更 PHP file の `php -l`、`make sample28-pack-runtime-test`、`make sample28-no-code-react-bridge-build-smoke`、`make sample28-no-code-react-bridge-browser-smoke`、`make test`。

## Post-JSON Forms / rjsf Transform Probe No-Code Product Goal Replan / JSON Forms・rjsf transform probe 後の no-code product goal 再計画

Status: `DONE`. / Status: `DONE`。

This planning item selected React bridge contract documentation polish after the schema-form comparison probe. / この planning item では schema-form 比較 probe 後の次の小さな no-code product-facing implementation として React bridge contract documentation polish を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| React bridge contract documentation polish | Add human-readable consumer notes now that custom React bridge and schema-form comparison artifacts both exist. | 0.5 - 1 day / 半日 - 1 日 | Selected. Useful to make the generated boundary readable before adding more behavior. |
| Schema-form probe hardening | Add richer field metadata mapping such as enum/options, format hints, or readonly inference after the first comparison artifact proved viable. | 1 - 3 days / 1 - 3 日 | Deferred. Useful after the generated bridge boundary notes are in place. |
| Generated runtime visual polish follow-up | Improve generated React/runtime presentation while keeping behavior stable. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless product-facing readability becomes the next priority. |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that visibility exists in both operator and runtime surfaces. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless accountability becomes the next concrete product gap. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after JSON Forms / rjsf transform probe. / 対象: JSON Forms / rjsf transform probe 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: replacing the custom React bridge, broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: custom React bridge の置き換え、広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: implemented through React bridge contract documentation polish first slice. / 検証: React bridge contract documentation polish first slice として実装済み。

## React Bridge Contract Documentation Polish First Slice / React bridge contract documentation polish first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 React Bridge Contract Documentation Polish First Slice](reports/2026/2026-0701-react-bridge-contract-documentation-polish-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 React Bridge Contract Documentation Polish First Slice](reports/2026/2026-0701-react-bridge-contract-documentation-polish-first-slice.md)。

This first slice adds generated consumer notes for the React bridge artifact after custom React and schema-form comparison outputs both exist. / この first slice では custom React と schema-form comparison output の両方が存在する状態で、React bridge artifact に生成 consumer notes を追加しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RCD1 | Documentation boundary / documentation boundary | `DONE` | 0.5 day / 半日 | Kept scope to generated consumer notes and structured contract metadata; no new runtime behavior. |
| RCD2 | Generated consumer notes / generated consumer notes | `DONE` | 0.5 day / 半日 | Added `CONSUMER-NOTES.md` explaining contract, scaffold, form state, schema-form probe, action intent, and editing boundaries. |
| RCD3 | Contract/readme coverage / contract・README coverage | `DONE` | 0.5 day / 半日 | Added structured `consumer_notes`, required-file invariant entry, README link, sample28 checker assertions, and foundation coverage. |

Boundary / 境界:

- In scope: generated React bridge consumer notes, structured contract notes, required-file invariant coverage, sample28/foundation verification. / 対象: generated React bridge consumer notes、structured contract notes、required-file invariant coverage、sample28 / foundation verification。
- Out of scope: new runtime behavior, schema-form hardening, visual builder, full generated application shell, server execution, transport. / 対象外: new runtime behavior、schema-form hardening、visual builder、完全な generated application shell、server execution、transport。
- Verification: `php -l`, script help checks, `make sample28-pack-runtime-test`, `make sample28-no-code-react-bridge-build-smoke`, `make sample28-no-code-react-bridge-browser-smoke`, and `make test`. / 検証: `php -l`、script help check、`make sample28-pack-runtime-test`、`make sample28-no-code-react-bridge-build-smoke`、`make sample28-no-code-react-bridge-browser-smoke`、`make test`。

## Post-React Bridge Contract Documentation Polish No-Code Product Goal Replan / React bridge contract documentation polish 後の no-code product goal 再計画

Status: `DONE`. / Status: `DONE`。

This planning item selected Schema-form probe hardening after React bridge contract documentation polish. / この planning item では React bridge contract documentation polish 後の次の小さな no-code product-facing implementation として Schema-form probe hardening を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Schema-form probe hardening | Add richer field metadata mapping such as enum/options, format hints, or readonly inference now that the consumer boundary is documented. | 1 - 3 days / 1 - 3 日 | Selected. This is the strongest continuation if schema-form compatibility should move from comparison-only toward useful metadata coverage. |
| Generated runtime visual polish follow-up | Improve generated React/runtime presentation while keeping behavior stable. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless product-facing readability is the next priority. |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that visibility exists in both operator and runtime surfaces. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless accountability becomes the next concrete product gap. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after React bridge contract documentation polish. / 対象: React bridge contract documentation polish 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: replacing the custom React bridge, broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: custom React bridge の置き換え、広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: implemented through Schema-form probe hardening first slice. / 検証: Schema-form probe hardening first slice として実装済み。

## Schema-Form Probe Hardening First Slice / schema-form probe hardening first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Schema-Form Probe Hardening First Slice](reports/2026/2026-0701-schema-form-probe-hardening-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Schema-Form Probe Hardening First Slice](reports/2026/2026-0701-schema-form-probe-hardening-first-slice.md)。

This first slice adds Mtool-aware metadata to the existing schema-form comparison artifact without adding a JSON Forms / rjsf runtime UI. / この first slice では JSON Forms / rjsf runtime UI は追加せず、既存 schema-form comparison artifact に Mtool-aware metadata を追加しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| SFH1 | Hardening boundary / hardening boundary | `DONE` | 0.5 day / 半日 | Kept scope to metadata enrichment; no runtime renderer, visual builder, or schema-form package install. |
| SFH2 | JSON Schema metadata / JSON Schema metadata | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added `description`, format mapping, and `x-mtool-*` field/action/client-write metadata to schema properties. |
| SFH3 | UI Schema and mapping metadata / UI Schema・mapping metadata | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added UI options and field mapping metadata for required/readonly/action role/client write/validation hints. |
| SFH4 | Checker coverage / checker coverage | `DONE` | 0.5 day / 半日 | Extended sample28 checker and foundation coverage for extension keys, action role, client-write, and UI validation hints. |

Boundary / 境界:

- In scope: Mtool extension metadata in schema-form probe artifacts, action field role/client-write hints, UI Schema options, focused checker coverage. / 対象: schema-form probe artifact 内の Mtool extension metadata、action field role / client-write hint、UI Schema options、focused checker coverage。
- Out of scope: JSON Forms / rjsf runtime UI, enum/options from new metadata tables, visual builder, server execution, transport. / 対象外: JSON Forms / rjsf runtime UI、新 metadata table 由来の enum / options、visual builder、server execution、transport。
- Verification: `php -l`, `make sample28-pack-runtime-test`, and `make test`. / 検証: `php -l`、`make sample28-pack-runtime-test`、`make test`。

## Post-Schema-Form Probe Hardening No-Code Product Goal Replan / schema-form probe hardening 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0701 Post-Schema-Form Probe Hardening No-Code Product Goal Replan](reports/2026/2026-0701-post-schema-form-probe-hardening-no-code-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0701 Post-Schema-Form Probe Hardening No-Code Product Goal Replan](reports/2026/2026-0701-post-schema-form-probe-hardening-no-code-product-goal-replan.md)。

This planning item selected Schema-form runtime smoke as the next small no-code product-facing implementation after schema-form probe hardening. / この planning item では schema-form probe hardening 後の次の小さな no-code product-facing implementation として Schema-form runtime smoke を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Generated runtime visual polish follow-up | Improve generated React/runtime presentation while keeping behavior stable now that artifact contracts are more readable. | 0.5 - 2 days / 半日 - 2 日 | Deferred. Useful, but less directly connected to the just-hardened schema-form probe. |
| Schema-form runtime smoke | Use the emitted JSON Schema / UI Schema in a tiny non-product smoke without adopting a schema-form UI as product code. | 1 - 3 days / 1 - 3 日 | Selected. This proves consumer viability while keeping the custom React bridge as the default product adapter. |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that visibility exists in both operator and runtime surfaces. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless accountability becomes the next concrete product gap. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after schema-form probe hardening. / 対象: schema-form probe hardening 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: replacing the custom React bridge, broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: custom React bridge の置き換え、広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning / report 更新のみ。

## Schema-Form Runtime Smoke First Slice / schema-form runtime smoke first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Schema-Form Runtime Smoke First Slice](reports/2026/2026-0701-schema-form-runtime-smoke-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Schema-Form Runtime Smoke First Slice](reports/2026/2026-0701-schema-form-runtime-smoke-first-slice.md)。

This implementation work was selected after schema-form probe hardening and is complete for the first slice. / これは schema-form probe hardening 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| SFRS1 | Smoke boundary / smoke boundary | `DONE` | 0.5 day / 半日 | Kept the smoke outside generated product code and used sample28 emitted `schema-form-contract.json`, `json-schema.json`, and `ui-schema.json`. |
| SFRS2 | Runtime smoke script / runtime smoke script | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added a small rjsf SSR smoke that validates generated metadata and renders the schema. |
| SFRS3 | Make/checker wiring / Make・checker wiring | `DONE` | 0.5 day / 半日 | Added `make sample28-no-code-schema-form-runtime-smoke`. |
| SFRS4 | Docs and verification / docs・verification | `DONE` | 0.5 day / 半日 | Updated report/current plan and ran focused smoke. |

Boundary / 境界:

- In scope: focused schema-form runtime smoke for sample28 probe artifacts, no product adoption of JSON Forms/rjsf, docs/report updates. / 対象: sample28 probe artifact 向け focused schema-form runtime smoke、JSON Forms / rjsf の product 採用なし、docs / report 更新。
- Out of scope: replacing custom React bridge, visual builder, full app shell, server execution, transport, sync behavior. / 対象外: custom React bridge の置き換え、visual builder、完全な app shell、server execution、transport、sync behavior。
- Verification: `node mtool/scripts/check_no_code_schema_form_runtime_smoke.js --help` and `make sample28-no-code-schema-form-runtime-smoke`. / 検証: `node mtool/scripts/check_no_code_schema_form_runtime_smoke.js --help` と `make sample28-no-code-schema-form-runtime-smoke`。

## Post-Schema-Form Runtime Smoke No-Code Product Goal Replan / schema-form runtime smoke 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0701 Post-Schema-Form Runtime Smoke No-Code Product Goal Replan](reports/2026/2026-0701-post-schema-form-runtime-smoke-no-code-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0701 Post-Schema-Form Runtime Smoke No-Code Product Goal Replan](reports/2026/2026-0701-post-schema-form-runtime-smoke-no-code-product-goal-replan.md)。

This planning item selected Schema-form consumer notes as the next small no-code product-facing implementation after schema-form runtime smoke. / この planning item では schema-form runtime smoke 後の次の小さな no-code product-facing implementation として Schema-form consumer notes を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Generated runtime visual polish follow-up | Improve generated React/runtime presentation after adapter confidence work stabilized. | 0.5 - 2 days / 半日 - 2 日 | Deferred. Still useful for visible product quality, but less directly connected to the schema-form consumer handoff. |
| Schema-form consumer notes | Add human-readable guidance to the generated schema-form probe artifact like the React bridge `CONSUMER-NOTES.md`. | 0.5 - 1 day / 半日 - 1 日 | Selected. Best continuation after the runtime smoke because consumer viability now needs generated handoff guidance. |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that visibility exists in both operator and runtime surfaces. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless accountability becomes the next concrete product gap. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after schema-form runtime smoke. / 対象: schema-form runtime smoke 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: replacing the custom React bridge, broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: custom React bridge の置き換え、広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning / report 更新のみ。

## Schema-Form Consumer Notes First Slice / schema-form consumer notes first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Schema-Form Consumer Notes First Slice](reports/2026/2026-0701-schema-form-consumer-notes-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Schema-Form Consumer Notes First Slice](reports/2026/2026-0701-schema-form-consumer-notes-first-slice.md)。

This implementation work was selected after schema-form runtime smoke and is complete for the first slice. / これは schema-form runtime smoke 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| SFCN1 | Consumer notes contract / consumer notes contract | `DONE` | 0.5 day / 半日 | Added structured `consumer_notes` to `schema-form-contract.json`. |
| SFCN2 | Generated notes file / generated notes file | `DONE` | 0.5 day / 半日 | Generated `CONSUMER-NOTES.md` with probe boundary, ownership split, runtime smoke boundary, stable markers, and editing guidance. |
| SFCN3 | Invariant and checker coverage / invariant・checker coverage | `DONE` | 0.5 day / 半日 | Added required-file invariant, sample28 checker coverage, and shared foundation coverage. |
| SFCN4 | Docs and verification / docs・verification | `DONE` | 0.5 day / 半日 | Updated sample README, report/current plan, and ran focused verification. |

Boundary / 境界:

- In scope: generated schema-form consumer notes, structured contract notes, required-file invariant, sample28/foundation coverage. / 対象: generated schema-form consumer notes、structured contract notes、required-file invariant、sample28 / foundation coverage。
- Out of scope: product adoption of JSON Forms/rjsf, replacing custom React bridge, visual builder, server execution, transport, sync behavior. / 対象外: JSON Forms / rjsf の product 採用、custom React bridge の置き換え、visual builder、server execution、transport、sync behavior。
- Verification: `php -l`, `make sample28-pack-runtime-test`, `make sample28-no-code-schema-form-runtime-smoke`, and `make test`. / 検証: `php -l`、`make sample28-pack-runtime-test`、`make sample28-no-code-schema-form-runtime-smoke`、`make test`。

## Post-Schema-Form Consumer Notes No-Code Product Goal Replan / schema-form consumer notes 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0701 Post-Schema-Form Consumer Notes No-Code Product Goal Replan](reports/2026/2026-0701-post-schema-form-consumer-notes-no-code-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0701 Post-Schema-Form Consumer Notes No-Code Product Goal Replan](reports/2026/2026-0701-post-schema-form-consumer-notes-no-code-product-goal-replan.md)。

This planning item selected Generated runtime visual polish follow-up as the next small no-code product-facing implementation after schema-form consumer notes. / この planning item では schema-form consumer notes 後の次の小さな no-code product-facing implementation として Generated runtime visual polish follow-up を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Generated runtime visual polish follow-up | Improve generated React/runtime presentation after adapter confidence and handoff docs stabilized. | 0.5 - 2 days / 半日 - 2 日 | Selected. Adapter confidence and handoff documentation are stable enough to return to generated runtime scanability. |
| React bridge/schema-form artifact parity notes | Add a compact comparison note explaining when to inspect React bridge vs schema-form probe artifacts. | 0.5 - 1 day / 半日 - 1 日 | Deferred. Recent notes already improved that boundary. |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that visibility exists in both operator and runtime surfaces. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless accountability becomes the next concrete product gap. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after schema-form consumer notes. / 対象: schema-form consumer notes 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: replacing the custom React bridge, broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: custom React bridge の置き換え、広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning / report 更新のみ。

## Generated Runtime Visual Polish Follow-Up First Slice / generated runtime visual polish follow-up first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Generated Runtime Visual Polish Follow-Up First Slice](reports/2026/2026-0701-generated-runtime-visual-polish-follow-up-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Generated Runtime Visual Polish Follow-Up First Slice](reports/2026/2026-0701-generated-runtime-visual-polish-follow-up-first-slice.md)。

This implementation work was selected after schema-form consumer notes and is complete for the first slice. / これは schema-form consumer notes 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| GRV1 | Screen summary DOM / screen summary DOM | `DONE` | 0.5 day / 半日 | Added compact field count, action count, and screen key summary chips to each generated runtime preview screen. |
| GRV2 | Visual styling / visual styling | `DONE` | 0.5 day / 半日 | Added restrained summary chip styling without changing preview layout semantics. |
| GRV3 | Focused coverage / focused coverage | `DONE` | 0.5 day / 半日 | Extended runtime PHPUnit, sample28 checker, and runtime UI smoke expectations. |
| GRV4 | Docs and verification / docs・verification | `DONE` | 0.5 day / 半日 | Updated report/current plan and ran focused verification plus full `make test`. |

Boundary / 境界:

- In scope: generated runtime preview scanability, screen-level field/action summary, focused DOM/smoke coverage. / 対象: generated runtime preview scanability、screen-level field / action summary、focused DOM / smoke coverage。
- Out of scope: new visual builder, action execution behavior changes, React bridge behavior changes, JSON Forms/rjsf product runtime adoption, sync/transport/conflict behavior. / 対象外: visual builder 追加、action execution behavior 変更、React bridge behavior 変更、JSON Forms / rjsf product runtime 採用、sync / transport / conflict behavior。
- Verification: `php -l`, `node ... --help`, `make sample28-pack-runtime-test`, `make sample28-no-code-runtime-ui-smoke`, and `make test` (`309 tests, 10278 assertions, skipped 1`). / 検証: `php -l`、`node ... --help`、`make sample28-pack-runtime-test`、`make sample28-no-code-runtime-ui-smoke`、`make test`（`309 tests, 10278 assertions, skipped 1`）。

## Post-Generated Runtime Visual Polish Follow-Up No-Code Product Goal Replan / generated runtime visual polish follow-up 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0701 Post-Generated Runtime Visual Polish Follow-Up No-Code Product Goal Replan](reports/2026/2026-0701-post-generated-runtime-visual-polish-follow-up-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0701 Post-Generated Runtime Visual Polish Follow-Up No-Code Product Goal Replan](reports/2026/2026-0701-post-generated-runtime-visual-polish-follow-up-no-code-product-goal-replan.md)。

This planning item chose Runtime preview accessibility polish as the next small no-code product-facing implementation after generated runtime visual polish follow-up. / この planning item では generated runtime visual polish follow-up 後の次の小さな no-code product-facing implementation として Runtime preview accessibility polish を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| React bridge/schema-form artifact parity notes | Add a compact comparison note explaining when to inspect React bridge vs schema-form probe artifacts. | 0.5 - 1 day / 半日 - 1 日 | Deferred. Handoff clarity is useful, but visible runtime quality is the immediate continuation. |
| Runtime preview accessibility polish | Add small accessibility affordances such as clearer landmarks or table captions to generated runtime preview. | 0.5 - 2 days / 半日 - 2 日 | Selected. Runtime scanability just improved, so adding semantic affordances is a small product-quality continuation. |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that visibility exists in both operator and runtime surfaces. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless accountability becomes the next concrete product gap. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after generated runtime visual polish follow-up. / 対象: generated runtime visual polish follow-up 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: replacing the custom React bridge, broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: custom React bridge の置き換え、広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning / report 更新のみ。

## Runtime Preview Accessibility Polish First Slice / runtime preview accessibility polish first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Runtime Preview Accessibility Polish First Slice](reports/2026/2026-0701-runtime-preview-accessibility-polish-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Runtime Preview Accessibility Polish First Slice](reports/2026/2026-0701-runtime-preview-accessibility-polish-first-slice.md)。

This implementation work was selected after generated runtime visual polish follow-up and is complete for the first slice. / これは generated runtime visual polish follow-up 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RPA1 | Preview landmark label / preview landmark label | `DONE` | 0.25 day / 0.25 日 | Added `aria-labelledby` and a stable title id to the generated preview root. |
| RPA2 | Screen region labels / screen region labels | `DONE` | 0.5 day / 半日 | Added region roles, stable screen title ids, and labelled screen sections. |
| RPA3 | Table and action labels / table・action labels | `DONE` | 0.5 day / 半日 | Added generated list table captions and action navigation labels. |
| RPA4 | Coverage and docs / coverage・docs | `DONE` | 0.5 day / 半日 | Updated PHPUnit, sample28 checker, runtime UI smoke, README, report, current plan, and full `make test` record. |

Boundary / 境界:

- In scope: generated runtime preview landmarks, labelled screen regions, list table captions, action nav labels, focused DOM/smoke coverage. / 対象: generated runtime preview landmark、labelled screen region、list table caption、action nav label、focused DOM / smoke coverage。
- Out of scope: WCAG audit, keyboard interaction redesign, visual builder, React bridge behavior, schema-form runtime behavior, action execution behavior. / 対象外: WCAG audit、keyboard interaction redesign、visual builder、React bridge behavior、schema-form runtime behavior、action execution behavior。
- Verification: `php -l`, `node ... --help`, `make sample28-pack-runtime-test`, `make sample28-no-code-runtime-ui-smoke`, and `make test` (`309 tests, 10284 assertions, skipped 1`). / 検証: `php -l`、`node ... --help`、`make sample28-pack-runtime-test`、`make sample28-no-code-runtime-ui-smoke`、`make test`（`309 tests, 10284 assertions, skipped 1`）。

## Post-Runtime Preview Accessibility Polish No-Code Product Goal Replan / runtime preview accessibility polish 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0701 Post-Runtime Preview Accessibility Polish No-Code Product Goal Replan](reports/2026/2026-0701-post-runtime-preview-accessibility-polish-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0701 Post-Runtime Preview Accessibility Polish No-Code Product Goal Replan](reports/2026/2026-0701-post-runtime-preview-accessibility-polish-no-code-product-goal-replan.md)。

This planning item chose React bridge/schema-form artifact parity notes as the next small no-code product-facing implementation after runtime preview accessibility polish. / この planning item では runtime preview accessibility polish 後の次の小さな no-code product-facing implementation として React bridge / schema-form artifact parity notes を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| React bridge/schema-form artifact parity notes | Add a compact comparison note explaining when to inspect React bridge vs schema-form probe artifacts. | 0.5 - 1 day / 半日 - 1 日 | Selected. Runtime preview accessibility is stable enough; tightening consumer handoff clarity is now the smaller gap. |
| Runtime preview keyboard/action affordance polish | Add focused affordances around action controls and disabled/action states without changing execution behavior. | 0.5 - 2 days / 半日 - 2 日 | Deferred. Useful, but less immediate after the first accessibility pass. |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that visibility exists in both operator and runtime surfaces. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless accountability becomes the next concrete product gap. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after runtime preview accessibility polish. / 対象: runtime preview accessibility polish 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: replacing the custom React bridge, broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: custom React bridge の置き換え、広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning / report 更新のみ。

## React Bridge Schema-Form Artifact Parity Notes First Slice / React bridge・schema-form artifact parity notes first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 React Bridge Schema-Form Artifact Parity Notes First Slice](reports/2026/2026-0701-react-bridge-schema-form-artifact-parity-notes-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 React Bridge Schema-Form Artifact Parity Notes First Slice](reports/2026/2026-0701-react-bridge-schema-form-artifact-parity-notes-first-slice.md)。

This implementation work was selected after runtime preview accessibility polish and is complete for the first slice. / これは runtime preview accessibility polish 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| APN1 | React bridge parity notes / React bridge parity notes | `DONE` | 0.25 day / 0.25 日 | Added generated parity notes to React bridge `consumer_notes` and `CONSUMER-NOTES.md`. |
| APN2 | Schema-form parity notes / schema-form parity notes | `DONE` | 0.25 day / 0.25 日 | Added generated parity notes to JSON Forms/rjsf probe `consumer_notes` and `CONSUMER-NOTES.md`. |
| APN3 | Coverage / coverage | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added sample28 checker and shared foundation assertions for parity notes. |
| APN4 | Docs and verification / docs・verification | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Updated sample README, report, current plan, and ran focused/full verification with full `make test`. |

Boundary / 境界:

- In scope: generated consumer-note comparison guidance for when to inspect `NO-CODE-REACT-BRIDGE` vs `NO-CODE-JSON-FORMS-PROBE`, structured contract notes, focused assertions. / 対象: `NO-CODE-REACT-BRIDGE` と `NO-CODE-JSON-FORMS-PROBE` のどちらを見るべきかを示す generated consumer-note comparison guidance、structured contract notes、focused assertion。
- Out of scope: new artifact kind, replacing the custom React bridge, adopting JSON Forms/rjsf as product runtime, runtime behavior changes, action execution behavior changes. / 対象外: 新 artifact kind、custom React bridge の置き換え、JSON Forms / rjsf の product runtime 採用、runtime behavior 変更、action execution behavior 変更。
- Verification: `php -l`, `make sample28-pack-runtime-test`, and `make test` (`309 tests, 10292 assertions, skipped 1`). / 検証: `php -l`、`make sample28-pack-runtime-test`、`make test`（`309 tests, 10292 assertions, skipped 1`）。

## Post-Artifact Parity Notes No-Code Product Goal Replan / artifact parity notes 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0701 Post-Artifact Parity Notes No-Code Product Goal Replan](reports/2026/2026-0701-post-artifact-parity-notes-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0701 Post-Artifact Parity Notes No-Code Product Goal Replan](reports/2026/2026-0701-post-artifact-parity-notes-no-code-product-goal-replan.md)。

This planning item chose Adapter artifact checklist note as the next small no-code product-facing implementation after React bridge/schema-form artifact parity notes. / この planning item では React bridge / schema-form artifact parity notes 後の次の小さな no-code product-facing implementation として Adapter artifact checklist note を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Runtime preview keyboard/action affordance polish | Add focused affordances around action controls and disabled/action states without changing execution behavior. | 0.5 - 2 days / 半日 - 2 日 | Deferred. Useful, but consumer handoff clarity is already active and can be completed in one small slice. |
| Adapter artifact checklist note | Add a short generated checklist that summarizes required files, stable markers, and smoke commands across React bridge and schema-form probe. | 0.5 - 1 day / 半日 - 1 日 | Selected. It directly follows parity notes and makes generated adapter handoff more actionable. |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that visibility exists in both operator and runtime surfaces. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless accountability becomes the next concrete product gap. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after artifact parity notes. / 対象: artifact parity notes 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: replacing the custom React bridge, broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: custom React bridge の置き換え、広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning / report 更新のみ。

## Adapter Artifact Checklist Notes First Slice / adapter artifact checklist notes first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Adapter Artifact Checklist Notes First Slice](reports/2026/2026-0701-adapter-artifact-checklist-notes-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Adapter Artifact Checklist Notes First Slice](reports/2026/2026-0701-adapter-artifact-checklist-notes-first-slice.md)。

This implementation work was selected after React bridge/schema-form artifact parity notes and is complete for the first slice. / これは React bridge / schema-form artifact parity notes 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| ACN1 | React bridge checklist / React bridge checklist | `DONE` | 0.25 day / 0.25 日 | Added generated required-file, stable-marker, and smoke-command checklist to React bridge consumer notes/contract. |
| ACN2 | Schema-form checklist / schema-form checklist | `DONE` | 0.25 day / 0.25 日 | Added generated required-file, stable-marker, and smoke-command checklist to JSON Forms/rjsf probe consumer notes/contract. |
| ACN3 | Coverage / coverage | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added sample28 checker and shared foundation assertions for checklist text. |
| ACN4 | Docs and verification / docs・verification | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Updated sample README, report, current plan, and ran focused/full verification with full `make test`. |

Boundary / 境界:

- In scope: generated adapter handoff checklists for required files, stable markers, and smoke commands across React bridge and schema-form probe. / 対象: React bridge と schema-form probe の required files、stable markers、smoke commands をまとめる generated adapter handoff checklist。
- Out of scope: new artifact kind, new smoke commands, replacing React bridge, adopting JSON Forms/rjsf as product runtime, runtime behavior changes. / 対象外: 新 artifact kind、新 smoke command、React bridge の置き換え、JSON Forms / rjsf の product runtime 採用、runtime behavior 変更。
- Verification: `php -l`, `make sample28-pack-runtime-test`, and `make test` (`309 tests, 10298 assertions, skipped 1`). / 検証: `php -l`、`make sample28-pack-runtime-test`、`make test`（`309 tests, 10298 assertions, skipped 1`）。

## Post-Adapter Checklist Notes No-Code Product Goal Replan / adapter checklist notes 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0701 Post-Adapter Checklist Notes No-Code Product Goal Replan](reports/2026/2026-0701-post-adapter-checklist-notes-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0701 Post-Adapter Checklist Notes No-Code Product Goal Replan](reports/2026/2026-0701-post-adapter-checklist-notes-no-code-product-goal-replan.md)。

This planning item chose Adapter artifact troubleshooting notes as the next small no-code product-facing implementation after adapter artifact checklist notes. / この planning item では adapter artifact checklist notes 後の次の小さな no-code product-facing implementation として Adapter artifact troubleshooting notes を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Runtime preview keyboard/action affordance polish | Add focused affordances around action controls and disabled/action states without changing execution behavior. | 0.5 - 2 days / 半日 - 2 日 | Deferred. Useful, but adapter handoff clarity can be rounded off with one more small slice. |
| Adapter artifact troubleshooting notes | Add compact generated troubleshooting notes for common adapter handoff failures. | 0.5 - 1 day / 半日 - 1 日 | Selected. It directly follows the checklist notes and helps consumers debug generated adapter artifacts. |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that visibility exists in both operator and runtime surfaces. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless accountability becomes the next concrete product gap. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after adapter checklist notes. / 対象: adapter checklist notes 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: replacing the custom React bridge, broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: custom React bridge の置き換え、広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning / report 更新のみ。

## Adapter Artifact Troubleshooting Notes First Slice / adapter artifact troubleshooting notes first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Adapter Artifact Troubleshooting Notes First Slice](reports/2026/2026-0701-adapter-artifact-troubleshooting-notes-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Adapter Artifact Troubleshooting Notes First Slice](reports/2026/2026-0701-adapter-artifact-troubleshooting-notes-first-slice.md)。

This implementation work was selected after adapter artifact checklist notes and is complete for the first slice. / これは adapter artifact checklist notes 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| ATN1 | React bridge troubleshooting notes / React bridge troubleshooting notes | `DONE` | 0.25 day / 0.25 日 | Added generated troubleshooting notes for build, display value, and action-intent field issues. |
| ATN2 | Schema-form troubleshooting notes / schema-form troubleshooting notes | `DONE` | 0.25 day / 0.25 日 | Added generated troubleshooting notes for smoke render, field mapping, and action-role mismatch issues. |
| ATN3 | Coverage / coverage | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added sample28 checker and shared foundation assertions for troubleshooting text. |
| ATN4 | Docs and verification / docs・verification | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Updated sample README, report, current plan, and ran focused/full verification with full `make test`. |

Boundary / 境界:

- In scope: generated troubleshooting notes for common React bridge and schema-form probe handoff failures. / 対象: React bridge と schema-form probe の common handoff failure 向け generated troubleshooting notes。
- Out of scope: new artifact kind, new smoke commands, replacing React bridge, adopting JSON Forms/rjsf as product runtime, runtime behavior changes. / 対象外: 新 artifact kind、新 smoke command、React bridge の置き換え、JSON Forms / rjsf の product runtime 採用、runtime behavior 変更。
- Verification: `php -l`, `make sample28-pack-runtime-test`, and `make test` (`309 tests, 10304 assertions, skipped 1`). / 検証: `php -l`、`make sample28-pack-runtime-test`、`make test`（`309 tests, 10304 assertions, skipped 1`）。

## Post-Adapter Troubleshooting Notes No-Code Product Goal Replan / adapter troubleshooting notes 後の no-code product goal 再計画

Status: `DONE`. Report: [2026-0701 Post-Adapter Troubleshooting Notes No-Code Product Goal Replan](reports/2026/2026-0701-post-adapter-troubleshooting-notes-no-code-product-goal-replan.md). / Status: `DONE`。Report: [2026-0701 Post-Adapter Troubleshooting Notes No-Code Product Goal Replan](reports/2026/2026-0701-post-adapter-troubleshooting-notes-no-code-product-goal-replan.md)。

This planning item chose Adapter consumer doc index note as the next small no-code product-facing implementation after adapter artifact troubleshooting notes. / この planning item では adapter artifact troubleshooting notes 後の次の小さな no-code product-facing implementation として Adapter consumer doc index note を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Runtime preview keyboard/action affordance polish | Add focused affordances around action controls and disabled/action states without changing execution behavior. | 0.5 - 2 days / 半日 - 2 日 | Deferred. Useful, but adapter handoff docs can be finalized as one readable package first. |
| Adapter consumer doc index note | Add a compact generated index note linking parity, checklist, troubleshooting, and smoke boundaries across adapter artifacts. | 0.5 - 1 day / 半日 - 1 日 | Selected. It ties the recent parity/checklist/troubleshooting notes together. |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that visibility exists in both operator and runtime surfaces. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless accountability becomes the next concrete product gap. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after adapter troubleshooting notes. / 対象: adapter troubleshooting notes 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: replacing the custom React bridge, broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: custom React bridge の置き換え、広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning / report 更新のみ。

## Adapter Consumer Doc Index Notes First Slice / adapter consumer doc index notes first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Adapter Consumer Doc Index Notes First Slice](reports/2026/2026-0701-adapter-consumer-doc-index-notes-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Adapter Consumer Doc Index Notes First Slice](reports/2026/2026-0701-adapter-consumer-doc-index-notes-first-slice.md)。

This implementation work was selected after adapter artifact troubleshooting notes and is complete for the first slice. / これは adapter artifact troubleshooting notes 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| ADI1 | React bridge doc index / React bridge doc index | `DONE` | 0.25 day / 0.25 日 | Added generated documentation index notes to React bridge consumer notes/contract. |
| ADI2 | Schema-form doc index / schema-form doc index | `DONE` | 0.25 day / 0.25 日 | Added generated documentation index notes to JSON Forms/rjsf probe consumer notes/contract. |
| ADI3 | Coverage / coverage | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added sample28 checker and shared foundation assertions for doc index text. |
| ADI4 | Docs and verification / docs・verification | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Updated sample README, report, current plan, and ran focused/full verification. |

Boundary / 境界:

- In scope: generated documentation index notes that link parity, checklist, troubleshooting, stable marker, and contract sections across React bridge and schema-form probe artifacts. / 対象: React bridge と schema-form probe artifacts の parity、checklist、troubleshooting、stable marker、contract section をつなぐ generated documentation index notes。
- Out of scope: new artifact kind, new smoke commands, replacing React bridge, adopting JSON Forms/rjsf as product runtime, runtime behavior changes. / 対象外: 新 artifact kind、新 smoke command、React bridge の置き換え、JSON Forms / rjsf の product runtime 採用、runtime behavior 変更。
- Verification: `php -l`, `make sample28-pack-runtime-test`, and `make test` (`309 tests, 10310 assertions, skipped 1`). / 検証: `php -l`、`make sample28-pack-runtime-test`、`make test`（`309 tests, 10310 assertions, skipped 1`）。

## Post-Adapter Doc Index Notes No-Code Product Goal Replan / adapter doc index notes 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0701 Post-Adapter Doc Index Notes No-Code Product Goal Replan](reports/2026/2026-0701-post-adapter-doc-index-notes-no-code-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0701 Post-Adapter Doc Index Notes No-Code Product Goal Replan](reports/2026/2026-0701-post-adapter-doc-index-notes-no-code-product-goal-replan.md)。

This planning item chose Adapter docs completion report as the short closure step after adapter consumer doc index notes. / この planning item では adapter consumer doc index notes 後の短い closure step として Adapter docs completion report を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Runtime preview keyboard/action affordance polish | Add focused affordances around action controls and disabled/action states without changing execution behavior. | 0.5 - 2 days / 半日 - 2 日 | Deferred for one short closure step. This remains the next implementation-facing product polish. |
| Adapter docs completion report | Add a concise dated summary that records the adapter handoff docs package and remaining open boundaries. | 0.5 day / 半日 | Selected. It closes the recent adapter docs lane cleanly. |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that visibility exists in both operator and runtime surfaces. | 0.5 - 2 days / 半日 - 2 日 | Deferred unless accountability becomes the next concrete product gap. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after adapter doc index notes. / 対象: adapter doc index notes 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: replacing the custom React bridge, broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: custom React bridge の置き換え、広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only. / 検証: planning / report 更新のみ。

## Adapter Docs Completion Report First Slice / adapter docs completion report first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Adapter Docs Completion Report First Slice](reports/2026/2026-0701-adapter-docs-completion-report-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Adapter Docs Completion Report First Slice](reports/2026/2026-0701-adapter-docs-completion-report-first-slice.md)。

This short closure slice records the completed adapter handoff docs package and remaining boundaries before returning the mainline to runtime preview affordance polish. / この短い closure slice では、runtime preview affordance polish へ主線を戻す前に、完了した adapter handoff docs package と残境界を記録しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| ADC1 | Package summary / package summary | `DONE` | 0.25 day / 0.25 日 | Recorded parity, checklist, troubleshooting, and doc index as the completed first adapter handoff docs package. |
| ADC2 | Remaining boundaries / 残境界 | `DONE` | 0.25 day / 0.25 日 | Kept runtime affordances, retry audit, visual builder, adapter replacement, rjsf runtime adoption, transport, and conflict resolution separate. |
| ADC3 | Plan transition / plan transition | `DONE` | 0.25 day / 0.25 日 | Returned the active mainline to runtime preview keyboard/action affordance polish. |

Boundary / 境界:

- In scope: dated completion report, current-plan update, remaining boundary notes. / 対象: 日付付き completion report、current-plan 更新、残境界 note。
- Out of scope: new generated artifact fields, generated runtime behavior changes, new smoke commands, React bridge replacement, JSON Forms/rjsf product runtime adoption. / 対象外: 新 generated artifact field、generated runtime behavior 変更、新 smoke command、React bridge 置き換え、JSON Forms / rjsf product runtime 採用。
- Verification: `rg` documentation marker check only. / 検証: `rg` による documentation marker 確認のみ。

## Runtime Preview Keyboard/Action Affordance Polish First Slice / runtime preview keyboard・action affordance polish first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Runtime Preview Keyboard/Action Affordance Polish First Slice](reports/2026/2026-0701-runtime-preview-keyboard-action-affordance-polish-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Runtime Preview Keyboard/Action Affordance Polish First Slice](reports/2026/2026-0701-runtime-preview-keyboard-action-affordance-polish-first-slice.md)。

This implementation work was selected after the adapter docs completion report and is complete for the first slice. It added focused generated preview affordances around keyboard flow and action controls without changing execution behavior. / これは adapter docs completion report 後に選んだ implementation work で、first slice は完了です。execution behavior は変えず、keyboard flow と action controls 周りの focused generated preview affordance を追加しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RKA1 | Keyboard flow inventory / keyboard flow 棚卸し | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Identified generated action buttons, aria labels, disabled state, and feedback status as the narrow affordance surface. |
| RKA2 | Action affordance markup / action affordance markup | `DONE` | 0.5 day / 半日 | Added deterministic action-control wrappers, keyboard activation markers, screen-scoped hint ids, and disabled action reasons. |
| RKA3 | Smoke and checker coverage / smoke・checker coverage | `DONE` | 0.5 day / 半日 | Extended NoCodeRuntimeTest, sample28 checker, and runtime UI smoke for the new affordance markers. |
| RKA4 | Docs and verification / docs・verification | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Updated report/current plan and ran focused verification; full `make test` recorded below. |

Boundary / 境界:

- In scope: generated `runtime-preview.html` affordance markup, keyboard/focus-readable labels or hints, deterministic action-state markers, focused smoke/checker coverage. / 対象: generated `runtime-preview.html` の affordance markup、keyboard / focus で読める label または hint、deterministic な action-state marker、focused smoke / checker coverage。
- Out of scope: changing operation execution semantics, new metadata tables, broad CSS redesign, visual builder, React bridge replacement, JSON Forms/rjsf product runtime adoption. / 対象外: operation execution semantics 変更、新 metadata table、広い CSS redesign、visual builder、React bridge 置き換え、JSON Forms / rjsf product runtime 採用。
- Verification: `php -l`, `node --check`, `make sample28-no-code-runtime-ui-smoke`, and `make test` (`309 tests, 10314 assertions, skipped 1`). / 検証: `php -l`、`node --check`、`make sample28-no-code-runtime-ui-smoke`、`make test`（`309 tests, 10314 assertions, skipped 1`）。

## Post-Runtime Preview Keyboard/Action Affordance Polish No-Code Product Goal Replan / runtime preview keyboard・action affordance polish 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0701 Post-Runtime Preview Keyboard/Action Affordance Polish No-Code Product Goal Replan](reports/2026/2026-0701-post-runtime-preview-keyboard-action-affordance-polish-no-code-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0701 Post-Runtime Preview Keyboard/Action Affordance Polish No-Code Product Goal Replan](reports/2026/2026-0701-post-runtime-preview-keyboard-action-affordance-polish-no-code-product-goal-replan.md)。

This planning item chose Retry audit trail as the next small no-code product-facing implementation after runtime preview keyboard/action affordance polish. / この planning item では runtime preview keyboard / action affordance polish 後の次の小さな no-code product-facing implementation として Retry audit trail を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that runtime/operator action visibility is stronger. | 0.5 - 2 days / 半日 - 2 日 | Selected. Runtime/operator action visibility is now strong enough that accountability is the next concrete gap. |
| Runtime preview action affordance follow-up | Add more detailed per-field action input guidance if the first affordance marker is not enough. | 0.5 - 2 days / 半日 - 2 日 | Deferred until a concrete payload-guidance gap appears. |
| Operator/admin no-code workflow polish | Return to operator-facing artifact inspection now that generated runtime/adapter handoff surfaces are clearer. | 1 - 3 days / 1 - 3 日 | Deferred. Useful later, but the retry mutation has the narrower accountability gap. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after runtime preview keyboard/action affordance polish. / 対象: runtime preview keyboard / action affordance polish 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only. / 検証: planning / report 更新のみ。

## Retry Audit Trail First Slice / retry audit trail first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Retry Audit Trail First Slice](reports/2026/2026-0701-retry-audit-trail-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Retry Audit Trail First Slice](reports/2026/2026-0701-retry-audit-trail-first-slice.md)。

This implementation work was selected after runtime preview keyboard/action affordance polish and is complete for the first slice. / これは runtime preview keyboard / action affordance polish 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RAT1 | Audit event shape / audit event shape | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added `sync_outbox.retry_requeued` event input with actor, target, before/after status, attempts, last error, and operation metadata. |
| RAT2 | Retry page append / retry page append | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Appended the audit event after successful operator retry requeue. |
| RAT3 | Operator notice / operator notice | `DONE` | 0.25 day / 0.25 日 | Added retry notice audit trail state: recorded, failed, or not reported. |
| RAT4 | Coverage and docs / coverage・docs | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added focused contract tests, report, and current-plan update. |

Boundary / 境界:

- In scope: operator retry requeue audit event, existing `audit_events` repository, detail page notice wording, focused tests. / 対象: operator retry requeue audit event、既存 `audit_events` repository、detail page notice 文言、focused tests。
- Out of scope: new audit storage tables, retry processing behavior changes, scheduler or transport, conflict resolution, broader operator workflow redesign. / 対象外: 新 audit storage table、retry processing behavior 変更、scheduler / transport、conflict resolution、広い operator workflow redesign。
- Verification: `php -l` and `make test` (`310 tests, 10330 assertions, skipped 1`). / 検証: `php -l` と `make test`（`310 tests, 10330 assertions, skipped 1`）。

## Post-Retry Audit Trail No-Code Product Goal Replan / retry audit trail 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0701 Post-Retry Audit Trail No-Code Product Goal Replan](reports/2026/2026-0701-post-retry-audit-trail-no-code-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0701 Post-Retry Audit Trail No-Code Product Goal Replan](reports/2026/2026-0701-post-retry-audit-trail-no-code-product-goal-replan.md)。

This planning item chose Retry audit display follow-up as the next small no-code product-facing implementation after retry audit trail. / この planning item では retry audit trail 後の次の小さな no-code product-facing implementation として Retry audit display follow-up を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Operator/admin no-code workflow polish | Return to operator-facing artifact inspection now that runtime, adapter, retry, and audit surfaces are clearer. | 1 - 3 days / 1 - 3 日 | Deferred for one narrow visibility step. Useful after the retry audit loop is readable end-to-end. |
| Runtime preview action affordance follow-up | Add more detailed per-field action input guidance only if the first affordance marker is insufficient. | 0.5 - 2 days / 半日 - 2 日 | Deferred. No concrete generated preview payload-guidance gap is active. |
| Retry audit display follow-up | Surface recent retry audit events directly on the sync outbox detail page. | 0.5 - 2 days / 半日 - 2 日 | Selected. Recording exists; the next smallest gap is showing recent retry audit events on the sync outbox detail page. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation after retry audit trail. / 対象: retry audit trail 後の次の小さな product-facing continuation を 1 つ選ぶ。
- Out of scope: broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only. / 検証: planning / report 更新のみ。

## Retry Audit Display Follow-Up First Slice / retry audit display follow-up first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Retry Audit Display Follow-Up First Slice](reports/2026/2026-0701-retry-audit-display-follow-up-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Retry Audit Display Follow-Up First Slice](reports/2026/2026-0701-retry-audit-display-follow-up-first-slice.md)。

This implementation work was selected after retry audit trail and is complete for the first slice. / これは retry audit trail 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RAD1 | Audit fetch filter / audit fetch filter | `DONE` | 0.25 day / 0.25 日 | Added `target_key` filtering to latest audit event fetch. |
| RAD2 | Detail display / detail display | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added Recent Retry Audit section to sync outbox detail. |
| RAD3 | Coverage and docs / coverage・docs | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added focused tests, report, and current-plan update. |

Boundary / 境界:

- In scope: recent retry audit display, existing `audit_events` repository, sync outbox detail page, focused tests. / 対象: recent retry audit display、既存 `audit_events` repository、sync outbox detail page、focused tests。
- Out of scope: new audit storage tables, audit search UI, retry processing behavior changes, scheduler or transport, conflict resolution. / 対象外: 新 audit storage table、audit search UI、retry processing behavior 変更、scheduler / transport、conflict resolution。
- Verification: `php -l` and `make test` (`310 tests, 10335 assertions, skipped 1`). / 検証: `php -l` と `make test`（`310 tests, 10335 assertions, skipped 1`）。

## Operator/Admin No-Code Workflow Polish First Slice / operator・admin no-code workflow polish first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Operator/Admin No-Code Workflow Polish First Slice](reports/2026/2026-0701-operator-admin-no-code-workflow-polish-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Operator/Admin No-Code Workflow Polish First Slice](reports/2026/2026-0701-operator-admin-no-code-workflow-polish-first-slice.md)。

This implementation work was selected after retry audit display follow-up and is complete for the first slice. / これは retry audit display follow-up 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| OAW1 | Operator workflow inventory / operator workflow 棚卸し | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Chose a checklist inside the existing `NO-CODE-RUNTIME` source-output inspection summary. |
| OAW2 | Focused UI polish / focused UI polish | `DONE` | 0.5 - 1.5 days / 半日 - 1.5 日 | Added `workflow_steps` with ready/blocked state and rendered Operator Workflow Checklist on the source-output page. |
| OAW3 | Coverage and docs / coverage・docs | `DONE` | 0.25 - 1 day / 0.25 - 1 日 | Added focused assertions, report/current-plan updates, and full `make test` verification. |

Boundary / 境界:

- In scope: one narrow operator/admin inspection workflow improvement. / 対象: narrow な operator / admin inspection workflow improvement を 1 つ。
- Out of scope: broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: `php -l` and `make test` (`310 tests, 10349 assertions, skipped 1`). / 検証: `php -l` と `make test`（`310 tests, 10349 assertions, skipped 1`）。

## Post-Operator/Admin Workflow No-Code Product Goal Replan / operator・admin workflow 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0701 Post-Operator/Admin Workflow No-Code Product Goal Replan](reports/2026/2026-0701-post-operator-admin-workflow-no-code-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0701 Post-Operator/Admin Workflow No-Code Product Goal Replan](reports/2026/2026-0701-post-operator-admin-workflow-no-code-product-goal-replan.md)。

This planning item selected No-code minimum closure report as the next mainline step after the operator/admin no-code workflow checklist slice. / この planning item では operator / admin no-code workflow checklist slice 後の次の mainline step として No-code minimum closure report を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Operator checklist link follow-up | Turn checklist items into more direct handoff links or route affordances where the destination already exists. | 0.5 - 2 days / 半日 - 2 日 | Deferred. The checklist already makes the read-only path visible enough for the current minimum milestone. |
| Runtime preview action affordance follow-up | Add more detailed per-field action input guidance only if current generated runtime hints are still insufficient. | 0.5 - 2 days / 半日 - 2 日 | Deferred. No concrete payload-guidance gap is active after the keyboard/action affordance work. |
| No-code minimum closure report | Record the current generated runtime / adapter / retry / operator surface as a coherent minimum milestone. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Selected. This closes the current minimum lane cleanly before choosing a larger next goal. |

Boundary / 境界:

- In scope: choose one next small product-facing continuation or closure step. / 対象: 次の小さな product-facing continuation または closure step を 1 つ選ぶ。
- Out of scope: broad visual builder, full generated application shell, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、完全な generated application shell、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only. / 検証: planning / report 更新のみ。

## No-Code Minimum Closure Report First Slice / no-code minimum closure report first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 No-Code Minimum Closure Report First Slice](reports/2026/2026-0701-no-code-minimum-closure-report-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 No-Code Minimum Closure Report First Slice](reports/2026/2026-0701-no-code-minimum-closure-report-first-slice.md)。

This docs-only closure step records the current generated runtime, adapter, sync/retry, and operator/admin inspection surfaces as the current minimum no-code milestone. / この docs-only closure step では、現在の generated runtime、adapter、sync / retry、operator / admin inspection surface を current minimum no-code milestone として記録しました。

Boundary / 境界:

- Complete for this milestone: generated runtime preview, React/schema-form adapter artifacts, sync/retry visibility, and operator/admin inspection checklist. / この milestone で完了扱い: generated runtime preview、React / schema-form adapter artifact、sync / retry visibility、operator / admin inspection checklist。
- Still out of scope: visual builder, full generated application shell, publishing workflow, remote sync transport, conflict resolution, offline-first runtime shell, native/Flutter target. / まだ対象外: visual builder、完全な generated application shell、publishing workflow、remote sync transport、conflict resolution、offline-first runtime shell、native / Flutter target。
- Verification: docs-only; latest full verification remains `make test` (`310 tests, 10349 assertions, skipped 1`). / 検証: docs-only。直近の full verification は `make test`（`310 tests, 10349 assertions, skipped 1`）。

## Post-No-Code Minimum Closure Product Goal Replan / no-code minimum closure 後の product goal 再計画

Status: `DONE`. Decision report: [2026-0701 Post-No-Code Minimum Closure Product Goal Replan](reports/2026/2026-0701-post-no-code-minimum-closure-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0701 Post-No-Code Minimum Closure Product Goal Replan](reports/2026/2026-0701-post-no-code-minimum-closure-product-goal-replan.md)。

This planning item selected Commit hygiene / worktree closure after closing the current no-code minimum milestone. / この planning item では current no-code minimum milestone を閉じた後の次 step として Commit hygiene / worktree closure を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Larger product surface | Publishing workflow, approval/revision history, or app packaging. | Replan first; likely 2 - 5 days for a narrow first slice / まず再計画。narrow first slice で 2 - 5 日程度 | Deferred. Product direction should be chosen after the accumulated worktree is grouped. |
| Deeper runtime capability | Relation-shaped forms, richer validation, or generated app shell. | Replan first; likely 2 - 5 days for a narrow first slice / まず再計画。narrow first slice で 2 - 5 日程度 | Deferred. Runtime work needs a fresh concrete sample target. |
| Operational hardening | Route-level production hardening or deploy-readiness checks. | Replan first; likely 1 - 3 days after scope selection / まず再計画。scope 選択後 1 - 3 日程度 | Deferred. Useful, but not before the current branch state is made reviewable. |
| Commit hygiene / worktree closure | Review the accumulated worktree and prepare meaning-sized commits before another large lane. | 0.5 - 1 day / 半日 - 1 日 | Selected. Prepare meaning-sized commit groups without pushing or rewriting user changes. |

Boundary / 境界:

- In scope: choose one next direction after the minimum milestone closure. / 対象: minimum milestone closure 後の次方向を 1 つ選ぶ。
- Out of scope: starting large implementation without a fresh boundary, pushing, or rewriting existing user changes. / 対象外: fresh boundary なしで大きな実装を始めること、push、既存 user change の巻き戻し。
- Verification: planning/report update only. / 検証: planning / report 更新のみ。

## Worktree Closure Commit Hygiene First Slice / worktree closure commit hygiene first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Worktree Closure Commit Hygiene First Slice](reports/2026/2026-0701-worktree-closure-commit-hygiene-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Worktree Closure Commit Hygiene First Slice](reports/2026/2026-0701-worktree-closure-commit-hygiene-first-slice.md)。

This docs-only hygiene step reviewed the accumulated worktree and prepared meaning-sized commit groups. No files were staged, committed, pushed, reverted, or history-rewritten. / この docs-only hygiene step では accumulated worktree を確認し、意味単位の commit group を準備しました。stage、commit、push、revert、history rewrite はしていません。

Recommended commit groups / 推奨 commit group:

- React bridge and adapter foundation. / React bridge と adapter foundation。
- JSON Forms / rjsf schema-form probe. / JSON Forms / rjsf schema-form probe。
- Runtime preview polish and sample28 generated artifact contract. / runtime preview polish と sample28 generated artifact contract。
- Sync retry audit and operator/admin inspection. / sync retry audit と operator / admin inspection。
- Planning, estimate notes, and no-code milestone closure. / planning、estimate notes、no-code milestone closure。

Verification / 検証:

- Docs-only report. / docs-only report。
- Latest full verification before this hygiene step remains `make test` (`310 tests, 10349 assertions, skipped 1`). / この hygiene step 前の直近 full verification は `make test`（`310 tests, 10349 assertions, skipped 1`）。

## Commit Group Execution Decision / commit group 実行判断

Status: `DONE`. Report: [2026-0701 Commit Group Execution Decision](reports/2026/2026-0701-commit-group-execution-decision.md). / Status: `DONE`。Report: [2026-0701 Commit Group Execution Decision](reports/2026/2026-0701-commit-group-execution-decision.md)。

This decision step created the prepared local commits after rerunning full verification. No push was performed. / この decision step では full verification を再実行した後、準備済みの local commit を作成しました。push はしていません。

Execution / 実行:

- `afe9f01` `Complete no-code runtime adapter milestone`.
- `Record no-code milestone planning reports` docs commit.
- Verification before commit: `make test` (`310 tests, 10349 assertions, skipped 1`).
- Push: not performed. / Push: 未実行。

## Post-Commit No-Code Product Goal Replan / commit 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0701 Post-Commit No-Code Product Goal Replan](reports/2026/2026-0701-post-commit-no-code-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0701 Post-Commit No-Code Product Goal Replan](reports/2026/2026-0701-post-commit-no-code-product-goal-replan.md)。

This planning item selected deeper runtime capability after the no-code minimum milestone and local commit cleanup. / この planning item では no-code minimum milestone と local commit cleanup 後の次方向として deeper runtime capability を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Larger product surface | Publishing workflow, approval/revision history, or app packaging. | Replan first; likely 2 - 5 days for a narrow first slice / まず再計画。narrow first slice で 2 - 5 日程度 | Deferred. Publishing/approval/app packaging should follow after generated runtime input behavior is stronger. |
| Deeper runtime capability | Relation-shaped forms, richer validation, or generated app shell. | Replan first; likely 2 - 5 days for a narrow first slice / まず再計画。narrow first slice で 2 - 5 日程度 | Selected. Required validation is a concrete, bounded gap that builds directly on existing metadata hints. |
| Operational hardening | Route-level production hardening or deploy-readiness checks. | Replan first; likely 1 - 3 days after scope selection / まず再計画。scope 選択後 1 - 3 日程度 | Deferred. Useful, but less product-visible than closing the basic validation behavior gap. |
| Pause for review / push decision | Let the two local commits be reviewed before choosing another large lane. | 0.25 day / 0.25 日 | Deferred. The user asked to continue and push remains disabled. |

Boundary / 境界:

- In scope: choose one next direction after local commits. / 対象: local commit 後の次方向を 1 つ選ぶ。
- Out of scope: push, destructive cleanup, history rewrite, or starting implementation without a fresh boundary. / 対象外: push、破壊的 cleanup、history rewrite、fresh boundary なしの実装開始。
- Verification: planning/report update only. / 検証: planning / report 更新のみ。

## Generated Required Validation Enforcement First Slice / generated required validation enforcement first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Generated Required Validation Enforcement First Slice](reports/2026/2026-0701-generated-required-validation-enforcement-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Generated Required Validation Enforcement First Slice](reports/2026/2026-0701-generated-required-validation-enforcement-first-slice.md)。

This implementation step was selected by the post-commit product-goal replan and is complete for the first slice. / これは commit 後の product-goal replan で選んだ implementation step で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RV1 | Runtime validation inventory / runtime validation 棚卸し | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Confirmed missing required checks already existed; blank required string handling was the bounded gap. |
| RV2 | Browser-local required enforcement / browser-local required enforcement | `DONE` | 0.5 - 1.5 days / 半日 - 1.5 日 | Added blank-string required value enforcement to generated browser action-intent helper. |
| RV3 | Feedback and accessibility / feedback・accessibility | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Reused existing fail-closed action error path without changing server/persistence behavior. |
| RV4 | Coverage and docs / coverage・docs | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added PHP blank required test, sample28 browser smoke assertion, report, and current-plan update. |

Boundary / 境界:

- In scope: existing required metadata, generated runtime preview, browser-local action intent guard, sample28 coverage. / 対象: 既存 required metadata、generated runtime preview、browser-local action intent guard、sample28 coverage。
- Out of scope: full validation DSL, cross-field validation, server-side validation behavior, visual builder, publishing workflow, remote sync transport. / 対象外: full validation DSL、cross-field validation、server-side validation behavior、visual builder、publishing workflow、remote sync transport。
- Verification: `php -l`, `node --check`, `make sample28-no-code-runtime-ui-smoke`, and `make test` (`311 tests, 10354 assertions, skipped 1`). / 検証: `php -l`、`node --check`、`make sample28-no-code-runtime-ui-smoke`、`make test`（`311 tests, 10354 assertions, skipped 1`）。

## Post-Required-Validation No-Code Product Goal Replan / required validation 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0701 Post-Required-Validation No-Code Product Goal Replan](reports/2026/2026-0701-post-required-validation-no-code-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0701 Post-Required-Validation No-Code Product Goal Replan](reports/2026/2026-0701-post-required-validation-no-code-product-goal-replan.md)。

This planning item selected React bridge required enforcement parity after generated required validation enforcement. / この planning item では generated required validation enforcement 後の次 step として React bridge required enforcement parity を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Validation feedback polish | Make generated runtime validation errors more user-friendly than raw `input.missing:*` strings. | 0.5 - 1.5 days / 半日 - 1.5 日 | Deferred. Raw error presentation can improve after runtime/adapter behavior is consistent. |
| React bridge required enforcement parity | Apply the same blank required fail-close behavior to generated React bridge action-intent helper. | 0.5 - 1.5 days / 半日 - 1.5 日 | Selected. Keeps the first adapter aligned with generated runtime behavior. |
| Larger product surface | Publishing workflow, approval/revision history, or app packaging. | Replan first; likely 2 - 5 days / まず再計画。2 - 5 日程度 | Deferred. Product surface expansion should follow parity for the current validation behavior. |
| Commit cleanup | Commit this validation slice locally before starting another lane. | 0.25 day / 0.25 日 | Deferred. The previous validation slice was already committed locally and the worktree was clean. |

Boundary / 境界:

- In scope: choose one next step after required validation enforcement. / 対象: required validation enforcement 後の次 step を 1 つ選ぶ。
- Out of scope: push, destructive cleanup, broad validation DSL, or starting implementation without a fresh boundary. / 対象外: push、破壊的 cleanup、広い validation DSL、fresh boundary なしの実装開始。
- Verification: planning/report update only. / 検証: planning / report 更新のみ。

## React Bridge Required Enforcement Parity First Slice / React bridge required enforcement parity first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 React Bridge Required Enforcement Parity First Slice](reports/2026/2026-0701-react-bridge-required-enforcement-parity-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 React Bridge Required Enforcement Parity First Slice](reports/2026/2026-0701-react-bridge-required-enforcement-parity-first-slice.md)。

This implementation work was selected after generated required validation enforcement and is complete for the first slice. / これは generated required validation enforcement 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RBV1 | Bridge validation inventory / bridge validation 棚卸し | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Confirmed the React bridge local intent helper lacked blank required enforcement while generated runtime had it. |
| RBV2 | Helper enforcement / helper enforcement | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added `createActionIntentResult()`, fail-closed blank required input checks, and success-only global event dispatch. |
| RBV3 | Browser smoke parity / browser smoke parity | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | sample28 React bridge smoke now fills all required fields for success and confirms blank `body` returns `input.missing:body`. |
| RBV4 | Coverage and docs / coverage・docs | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added generated helper contract assertion, report, current-plan update, and full test record. |

Boundary / 境界:

- In scope: generated React bridge local action-intent helper, required blank input checks, feedback error state, and sample28 browser smoke coverage. / 対象: generated React bridge local action-intent helper、required blank input check、feedback error state、sample28 browser smoke coverage。
- Out of scope: full validation DSL, cross-field validation, server-side validation behavior, generated runtime validation feedback wording, publishing workflow. / 対象外: full validation DSL、cross-field validation、server-side validation behavior、generated runtime validation feedback wording、publishing workflow。
- Verification: `php -l`, `node --check`, `make sample28-no-code-react-bridge-browser-smoke`, and `make test` (`311 tests, 10355 assertions, skipped 1`). / 検証: `php -l`、`node --check`、`make sample28-no-code-react-bridge-browser-smoke`、`make test`（`311 tests, 10355 assertions, skipped 1`）。

## Post-React-Bridge Required Enforcement No-Code Product Goal Replan / React bridge required enforcement 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0701 Post-React-Bridge Required Enforcement No-Code Product Goal Replan](reports/2026/2026-0701-post-react-bridge-required-enforcement-no-code-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0701 Post-React-Bridge Required Enforcement No-Code Product Goal Replan](reports/2026/2026-0701-post-react-bridge-required-enforcement-no-code-product-goal-replan.md)。

This planning item selected validation feedback polish after React bridge required enforcement parity. / この planning item では React bridge required enforcement parity 後の次 step として validation feedback polish を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Validation feedback polish | Make generated runtime / React bridge validation feedback more user-friendly than raw `input.missing:*` strings. | 0.5 - 1.5 days / 半日 - 1.5 日 | Selected. Behavior is now consistent enough to polish presentation without changing validation semantics. |
| Schema-form validation parity check | Confirm JSON Schema / rjsf required behavior remains aligned with Mtool required metadata and smoke coverage. | 0.5 - 1.5 days / 半日 - 1.5 日 | Deferred. Useful, but the current user-facing feedback gap is smaller and more visible. |
| Larger product surface | Publishing workflow, approval/revision history, or app packaging. | Replan first; likely 2 - 5 days / まず再計画。2 - 5 日程度 | Deferred. Validation presentation should be cleaned up before broad product-surface expansion. |
| Commit cleanup | Commit this React bridge validation parity slice locally before starting another lane. | 0.25 day / 0.25 日 | Deferred. The previous React bridge parity slice was already committed locally and the worktree was clean. |

Boundary / 境界:

- In scope: choose one next step after React bridge required enforcement parity. / 対象: React bridge required enforcement parity 後の次 step を 1 つ選ぶ。
- Out of scope: push, destructive cleanup, broad validation DSL, or starting implementation without a fresh boundary. / 対象外: push、破壊的 cleanup、広い validation DSL、fresh boundary なしの実装開始。
- Verification: planning/report update only. / 検証: planning / report 更新のみ。

## Validation Feedback Polish First Slice / validation feedback polish first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Validation Feedback Polish First Slice](reports/2026/2026-0701-validation-feedback-polish-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Validation Feedback Polish First Slice](reports/2026/2026-0701-validation-feedback-polish-first-slice.md)。

This implementation work was selected after React bridge required enforcement parity and is complete for the first slice. / これは React bridge required enforcement parity 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| VF1 | Message boundary / message 境界 | `DONE` | 0.25 day / 0.25 日 | Kept raw `error` codes for machine checks and added display-ready `message`. |
| VF2 | Runtime message helper / runtime message helper | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added PHP/browser validation message conversion for `input.missing:*` and `input.readonly:*`. |
| VF3 | React bridge message helper / React bridge message helper | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added `MtoolNoCodeActionIntentResult.message` and wired App feedback to `result.message`. |
| VF4 | Coverage and docs / coverage・docs | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Updated PHP tests, runtime/React smokes, report, and current-plan status. |

Boundary / 境界:

- In scope: generated runtime dispatch result message, generated runtime preview browser feedback text, generated React bridge action-intent result message, and React bridge App feedback wiring. / 対象: generated runtime dispatch result message、generated runtime preview browser feedback text、generated React bridge action-intent result message、React bridge App feedback wiring。
- Out of scope: full validation DSL, cross-field validation, persistence/server validation semantics, schema-form/rjsf runtime behavior, localization. / 対象外: full validation DSL、cross-field validation、persistence / server validation semantics、schema-form / rjsf runtime behavior、localization。
- Verification: `php -l`, `node --check`, `make sample28-no-code-runtime-ui-smoke`, `make sample28-no-code-react-bridge-browser-smoke`, and `make test` (`311 tests, 10359 assertions, skipped 1`). / 検証: `php -l`、`node --check`、`make sample28-no-code-runtime-ui-smoke`、`make sample28-no-code-react-bridge-browser-smoke`、`make test`（`311 tests, 10359 assertions, skipped 1`）。

## Post-Validation-Feedback No-Code Product Goal Replan / validation feedback 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0701 Post-Validation-Feedback No-Code Product Goal Replan](reports/2026/2026-0701-post-validation-feedback-no-code-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0701 Post-Validation-Feedback No-Code Product Goal Replan](reports/2026/2026-0701-post-validation-feedback-no-code-product-goal-replan.md)。

This planning item selected schema-form validation parity check after validation feedback polish. / この planning item では validation feedback polish 後の次 step として schema-form validation parity check を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Schema-form validation parity check | Confirm JSON Schema / rjsf required behavior and generated notes stay aligned with Mtool required metadata. | 0.5 - 1.5 days / 半日 - 1.5 日 | Selected. It keeps JSON Forms / rjsf comparison artifacts aligned with runtime and React bridge validation behavior. |
| Larger product surface | Publishing workflow, approval/revision history, or app packaging. | Replan first; likely 2 - 5 days / まず再計画。2 - 5 日程度 | Deferred. Current validation behavior is stronger, but adapter parity is the smaller continuation. |
| Runtime capability continuation | Relation-shaped forms, richer field types, or generated app shell. | Replan first; likely 1 - 3 days after a narrow gap is chosen / まず再計画。narrow gap 選択後 1 - 3 日程度 | Deferred. No narrower runtime gap is currently more concrete than schema-form parity. |
| Commit cleanup | Commit this validation feedback slice locally before starting another lane. | 0.25 day / 0.25 日 | Deferred. The validation feedback slice was already committed locally and the worktree was clean. |

Boundary / 境界:

- In scope: choose one next step after validation feedback polish. / 対象: validation feedback polish 後の次 step を 1 つ選ぶ。
- Out of scope: push, destructive cleanup, broad validation DSL, or starting implementation without a fresh boundary. / 対象外: push、破壊的 cleanup、広い validation DSL、fresh boundary なしの実装開始。
- Verification: planning/report update only. / 検証: planning / report 更新のみ。

## Schema-Form Validation Parity Check First Slice / schema-form validation parity check first slice

Status: `FIRST_SLICE_DONE_WITH_VERIFICATION_GAP`. Report: [2026-0701 Schema-Form Validation Parity Check First Slice](reports/2026/2026-0701-schema-form-validation-parity-check-first-slice.md). / Status: `FIRST_SLICE_DONE_WITH_VERIFICATION_GAP`。Report: [2026-0701 Schema-Form Validation Parity Check First Slice](reports/2026/2026-0701-schema-form-validation-parity-check-first-slice.md)。

This implementation work was selected after validation feedback polish and is complete for the first slice, with Docker-backed verification left to rerun when Docker is available. / これは validation feedback polish 後に選んだ implementation work で、first slice は完了です。ただし Docker-backed verification は Docker が使える状態になってから再実行します。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| SFP1 | Parity boundary / parity 境界 | `DONE` | 0.25 day / 0.25 日 | Kept schema-form probe comparison-only while aligning required string blank handling with runtime / React bridge. |
| SFP2 | JSON Schema metadata / JSON Schema metadata | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Required string properties now include `minLength: 1`, `pattern: "\\S"`, and `x-mtool-blank-is-missing`. |
| SFP3 | Contract and notes / contract・notes | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Added `validation_parity` metadata, stable extension key, handoff/troubleshooting notes, and Validation Parity Boundary section. |
| SFP4 | Focused verification / focused verification | `DONE_WITH_GAP` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Lint and temporary-probe rjsf smoke passed; Docker-backed sample smoke/full test blocked by unavailable Docker daemon. |

Boundary / 境界:

- In scope: schema-form generated JSON Schema required string handling, contract metadata, consumer notes, and rjsf smoke assertion for blank required strings. / 対象: schema-form generated JSON Schema required string handling、contract metadata、consumer notes、blank required string の rjsf smoke assertion。
- Out of scope: JSON Forms / rjsf product runtime adoption, full validation DSL, cross-field validation, server-side validation behavior, localization. / 対象外: JSON Forms / rjsf product runtime adoption、full validation DSL、cross-field validation、server-side validation behavior、localization。
- Verification: `php -l`, `node --check`, temporary generated probe smoke via `node mtool/scripts/check_no_code_schema_form_runtime_smoke.js --probe=/tmp/dego-schema-form-parity-probe --work-dir=/tmp/dego-schema-form-parity-work --cache=/tmp/dego-schema-form-parity-cache`; Docker-backed `make sample28-no-code-schema-form-runtime-smoke` and full `make test` blocked because Docker daemon was unavailable. / 検証: `php -l`、`node --check`、temporary generated probe smoke。Docker-backed `make sample28-no-code-schema-form-runtime-smoke` と full `make test` は Docker daemon unavailable のため未完了。

## Post-Schema-Form Validation Parity No-Code Product Goal Replan / schema-form validation parity 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0701 Post-Schema-Form Validation Parity No-Code Product Goal Replan](reports/2026/2026-0701-post-schema-form-validation-parity-no-code-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0701 Post-Schema-Form Validation Parity No-Code Product Goal Replan](reports/2026/2026-0701-post-schema-form-validation-parity-no-code-product-goal-replan.md)。

This planning item selected no-code product surface boundary inventory as a docs-only continuation while Docker-backed verification remains unavailable. / この planning item では Docker-backed verification が unavailable の間に進める docs-only continuation として no-code product surface boundary inventory を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Docker-backed verification rerun | Close the recorded verification gap once Docker daemon is available. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Deferred until Docker daemon is available. Rerun remains recommended before the next code slice. |
| No-code product surface boundary inventory | Compare larger product-surface candidates and choose a narrow first implementation boundary. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Selected. Advances the main plan with docs-only work while Docker-backed verification is blocked. |
| Larger product surface implementation | Publishing workflow, approval/revision history, or app packaging. | Replan first; likely 2 - 5 days / まず再計画。2 - 5 日程度 | Deferred. Needs a narrower first slice and Docker-backed test availability. |
| Runtime capability continuation | Relation-shaped forms, richer field types, or generated app shell. | Replan first; likely 1 - 3 days after a narrow gap is chosen / まず再計画。narrow gap 選択後 1 - 3 日程度 | Deferred. Current validation lane has reached adapter parity for the first slice. |

Boundary / 境界:

- In scope: choose one next step after schema-form validation parity. / 対象: schema-form validation parity 後の次 step を 1 つ選ぶ。
- Out of scope: push, destructive cleanup, broad validation DSL, or starting implementation without a fresh boundary. / 対象外: push、破壊的 cleanup、広い validation DSL、fresh boundary なしの実装開始。
- Verification: planning/report update only. / 検証: planning / report 更新のみ。

## No-Code Product Surface Boundary Inventory First Slice / no-code product surface boundary inventory first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 No-Code Product Surface Boundary Inventory First Slice](reports/2026/2026-0701-no-code-product-surface-boundary-inventory-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 No-Code Product Surface Boundary Inventory First Slice](reports/2026/2026-0701-no-code-product-surface-boundary-inventory-first-slice.md)。

This docs-only inventory narrowed the larger no-code product-surface lane to published no-code runtime artifact selection. / この docs-only inventory では、larger no-code product-surface lane を published no-code runtime artifact selection へ絞りました。

| Candidate / 候補 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- |
| Published no-code runtime artifact selection | 1 - 3 days / 1 - 3 日 | Selected. Builds on Source Outputs / operator inspection and avoids new runtime semantics. |
| Approval / revision history | 2 - 5 days for a narrow first slice / narrow first slice で 2 - 5 日 | Deferred. Needs a durable publish surface first. |
| Local app packaging | 2 - 5 days for a narrow first slice / narrow first slice で 2 - 5 日 | Deferred. Packaging should follow a clearer published artifact boundary. |
| Generated app shell | 1 - 3 days after scope selection / scope 選択後 1 - 3 日 | Deferred. Current validation / adapter lanes are enough for the first product-surface turn. |

Boundary / 境界:

- In scope: operator/admin surface that identifies the latest generated `NO-CODE-RUNTIME` artifact as publishable or blocked; read-only publish readiness metadata; linkage back to existing Source Outputs inspection and generated preview files. / 対象: latest generated `NO-CODE-RUNTIME` artifact を publishable / blocked として識別する operator/admin surface、read-only publish readiness metadata、既存 Source Outputs inspection と generated preview files への導線。
- Out of scope: public runtime URL, approval workflow, revision history, artifact copying or packaging, new runtime execution behavior, push. / 対象外: public runtime URL、approval workflow、revision history、artifact copy / packaging、新 runtime execution behavior、push。
- Verification: docs-only inventory. Docker-backed schema-form verification remains a recorded gap. / 検証: docs-only inventory。Docker-backed schema-form verification は記録済み gap として残す。

## Published No-Code Runtime Artifact Selection First Slice / published no-code runtime artifact selection first slice

Status: `FIRST_SLICE_DONE_WITH_VERIFICATION_GAP`. Report: [2026-0701 Published No-Code Runtime Artifact Selection First Slice](reports/2026/2026-0701-published-no-code-runtime-artifact-selection-first-slice.md). / Status: `FIRST_SLICE_DONE_WITH_VERIFICATION_GAP`。Report: [2026-0701 Published No-Code Runtime Artifact Selection First Slice](reports/2026/2026-0701-published-no-code-runtime-artifact-selection-first-slice.md)。

This first slice adds a read-only operator/admin signal for identifying the latest generated `NO-CODE-RUNTIME` artifact as publishable or blocked. / この first slice では、latest generated `NO-CODE-RUNTIME` artifact を publishable / blocked として識別する read-only operator / admin signal を追加しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| PNA1 | Surface inventory / surface inventory | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Reused existing Source Outputs no-code inspection card and `app_no_code_operator_inspection_from_catalog()`. |
| PNA2 | Readiness model / readiness model | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added read-only `publish_readiness` with publishable / blocked state, artifact key, archive state, preview readiness, screen/action counts, and blockers. |
| PNA3 | Operator/admin display / operator/admin display | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Surfaced Publish Readiness without public URLs, artifact copying, approval, revision history, or publish mutation. |
| PNA4 | Tests and docs / tests・docs | `DONE_WITH_GAP` | 0.5 day / 半日 | Lint and direct PHP smoke passed; Docker-backed focused/full verification blocked by unavailable Docker daemon. |

Boundary / 境界:

- In scope: read-only artifact selection/readiness surface for generated `NO-CODE-RUNTIME`. / 対象: generated `NO-CODE-RUNTIME` の read-only artifact selection / readiness surface。
- Out of scope: publish mutation, approval workflow, revision history, public runtime URL, packaging, push. / 対象外: publish mutation、approval workflow、revision history、public runtime URL、packaging、push。
- Verification: `php -l` and direct PHP smoke passed. Docker-backed focused PHPUnit and full `make test` blocked because Docker daemon was unavailable. / 検証: `php -l` と direct PHP smoke は通過。Docker daemon unavailable のため Docker-backed focused PHPUnit と full `make test` は未実行。

## Post-Published No-Code Runtime Artifact Selection Product Goal Replan / published no-code runtime artifact selection 後の product goal 再計画

Status: `DONE`. Decision report: [2026-0701 Post-Published No-Code Runtime Artifact Selection Product Goal Replan](reports/2026/2026-0701-post-published-no-code-runtime-artifact-selection-no-code-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0701 Post-Published No-Code Runtime Artifact Selection Product Goal Replan](reports/2026/2026-0701-post-published-no-code-runtime-artifact-selection-no-code-product-goal-replan.md)。

This planning item attempted Docker-backed verification again and selected approval / revision history boundary inventory as the next docs-only continuation because Docker remained unavailable. / この planning item では Docker-backed verification を再試行しましたが Docker が unavailable のままだったため、次の docs-only continuation として approval / revision history boundary inventory を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Docker-backed verification rerun | Close the current code-verification gap for schema-form parity and publish readiness. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Attempted and still blocked: Docker daemon unavailable. Keep as a required rerun before the next code slice. |
| Publish-readiness detail surface | Add a more focused read-only view or artifact detail section for publish readiness. | 0.5 - 1.5 days / 半日 - 1.5 日 | Deferred. Useful, but another read-only UI slice is less important than defining the first mutation boundary. |
| Approval / revision history boundary | Define the first mutation-capable product surface after read-only publish readiness. | Replan first; likely 1 - 3 days / まず再計画。1 - 3 日程度 | Selected as docs-only. It can proceed without Docker and reduces risk before any publish mutation is added. |
| Local app packaging / generated app shell | Revisit package/app shell once published artifact selection is stable. | Replan first; likely 2 - 5 days / まず再計画。2 - 5 日程度 | Deferred. Packaging should follow a clearer publish/approval/revision boundary. |

Boundary / 境界:

- In scope: choose one next step after read-only publish readiness. / 対象: read-only publish readiness 後の次 step を 1 つ選ぶ。
- Out of scope: push, destructive cleanup, public runtime URL, publish mutation without a fresh boundary. / 対象外: push、破壊的 cleanup、public runtime URL、fresh boundary なしの publish mutation。
- Verification: `make sample28-no-code-schema-form-runtime-smoke` attempted and blocked by unavailable Docker daemon. / 検証: `make sample28-no-code-schema-form-runtime-smoke` を再試行し、Docker daemon unavailable のため blocked。

## Approval / Revision History Boundary Inventory First Slice / approval・revision history boundary inventory first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Approval / Revision History Boundary Inventory First Slice](reports/2026/2026-0701-approval-revision-history-boundary-inventory-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Approval / Revision History Boundary Inventory First Slice](reports/2026/2026-0701-approval-revision-history-boundary-inventory-first-slice.md)。

This docs-only inventory narrowed the first mutation-capable product surface to publish candidate revision records. / この docs-only inventory では、最初の mutation-capable product surface を publish candidate revision record に絞りました。

| Candidate / 候補 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- |
| Publish candidate revision record | 1 - 2 days / 1 - 2 日 | Selected. This is the smallest durable mutation before approval actions or public URLs. |
| Approval action buttons | 1 - 3 days / 1 - 3 日 | Deferred. Actions need a revision object and state model first. |
| Public runtime URL | 2 - 5 days / 2 - 5 日 | Deferred. Public exposure should follow explicit revision/approval state. |
| Artifact copying / packaging | 2 - 5 days / 2 - 5 日 | Deferred. Packaging should follow revision selection and approval semantics. |
| Publish-readiness detail surface | 0.5 - 1.5 days / 半日 - 1.5 日 | Deferred. Useful UI polish, but the product path needs a durable boundary first. |

Boundary / 境界:

- In scope: a future read/write model for a publish candidate revision tied to project key, source output key, artifact key, and readiness snapshot. / 対象: project key、source output key、artifact key、readiness snapshot に紐づく publish candidate revision の read/write model。
- Out of scope: approval mutation, revision rollback, public publish, packaging, transport/sync behavior, push. / 対象外: approval mutation、revision rollback、public publish、packaging、transport / sync behavior、push。
- Verification: docs-only inventory. Docker-backed verification remains blocked and must be rerun before code changes for this lane. / 検証: docs-only inventory。Docker-backed verification は blocked のままで、この lane の code change 前に再実行が必要。

## Publish Candidate Revision Record Replan / publish candidate revision record 再計画

Status: `DONE`. Decision report: [2026-0701 Publish Candidate Revision Record Replan](reports/2026/2026-0701-publish-candidate-revision-record-replan.md). / Status: `DONE`。Decision report: [2026-0701 Publish Candidate Revision Record Replan](reports/2026/2026-0701-publish-candidate-revision-record-replan.md)。

This planning item selected revision record schema/docs only before implementing durable publish candidate revision records. / この planning item では durable publish candidate revision record 実装前の次 step として revision record schema/docs only を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Docker-backed verification rerun | Close the current verification gap before another code slice. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Attempted and still blocked: Docker daemon unavailable. |
| Revision record schema/docs only | Define the table/repository contract without wiring mutation UI. | 0.5 - 1 day / 半日 - 1 日 | Selected. It reduces implementation ambiguity without adding unverified mutation code. |
| Minimal publish candidate persistence | Add a fail-closed stored candidate snapshot from publish readiness. | 1 - 2 days / 1 - 2 日 | Deferred. Requires Docker-backed verification before adding a new write path. |
| Approval action planning | Define approve/reject transitions after candidate record exists. | Replan after candidate record / candidate record 後に再計画 | Deferred. |

Boundary / 境界:

- In scope: choose one next step for publish candidate revision records. / 対象: publish candidate revision record の次 step を 1 つ選ぶ。
- Out of scope: push, public runtime URL, approval mutation without candidate record, packaging. / 対象外: push、public runtime URL、candidate record なしの approval mutation、packaging。
- Verification: `make sample28-no-code-schema-form-runtime-smoke` attempted and blocked by unavailable Docker daemon; planning/report update only. / 検証: `make sample28-no-code-schema-form-runtime-smoke` を再試行し Docker daemon unavailable のため blocked。planning / report 更新のみ。

## Publish Candidate Revision Record Schema Contract First Slice / publish candidate revision record schema contract first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Publish Candidate Revision Record Schema Contract First Slice](reports/2026/2026-0701-publish-candidate-revision-record-schema-contract-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Publish Candidate Revision Record Schema Contract First Slice](reports/2026/2026-0701-publish-candidate-revision-record-schema-contract-first-slice.md)。

This docs-only slice defines the first durable publish candidate revision record contract before adding mutation code. / この docs-only slice では mutation code を追加する前に、最初の durable publish candidate revision record contract を定義しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| PCR1 | Record boundary / record 境界 | `DONE` | 0.25 day / 0.25 日 | Candidate revision is a stored readiness snapshot, not approval, public URL, or artifact package. |
| PCR2 | Schema contract / schema contract | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Defined identity fields, readiness snapshot fields, lifecycle fields, and `snapshot_json`. |
| PCR3 | Repository/UI boundary / repository・UI 境界 | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Defined create/list/read helper responsibilities and operator/admin-only UI expectations. |
| PCR4 | Verification boundary / verification 境界 | `DONE` | 0.25 day / 0.25 日 | Requires Docker-backed verification and focused repository tests before any code implementation. |

Boundary / 境界:

- In scope: docs-only durable candidate revision record, repository contract, UI contract, and implementation verification boundary. / 対象: docs-only の durable candidate revision record、repository contract、UI contract、implementation verification boundary。
- Out of scope: database migration, repository implementation, mutation UI, approval/reject/rollback actions, public runtime URL, artifact copying or packaging, push. / 対象外: database migration、repository implementation、mutation UI、approval / reject / rollback action、public runtime URL、artifact copy / packaging、push。
- Verification: docs-only; Docker-backed verification remains blocked by unavailable Docker daemon. / 検証: docs-only。Docker-backed verification は Docker daemon unavailable のため blocked のまま。

## Post-Publish-Candidate-Revision Schema No-Code Product Goal Replan / publish candidate revision schema 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0701 Post-Publish-Candidate-Revision Schema No-Code Product Goal Replan](reports/2026/2026-0701-post-publish-candidate-revision-schema-no-code-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0701 Post-Publish-Candidate-Revision Schema No-Code Product Goal Replan](reports/2026/2026-0701-post-publish-candidate-revision-schema-no-code-product-goal-replan.md)。

This planning item selected approval transition planning as a docs-only continuation after the publish candidate revision schema contract. / この planning item では publish candidate revision schema contract 後の docs-only continuation として approval transition planning を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Docker-backed verification rerun | Close current schema-form/publish-readiness verification gaps before code. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Attempted and still blocked: Docker daemon unavailable. |
| Minimal candidate persistence | Implement the stored candidate snapshot and focused repository tests. | 1 - 2 days / 1 - 2 日 | Deferred. It adds a new write path and should wait for Docker-backed verification. |
| Approval transition planning | Define approve/reject state transitions without adding code. | 0.5 - 1 day / 半日 - 1 日 | Selected. It reduces ambiguity without adding code or mutation behavior. |
| Public runtime URL / packaging | Expose or package a published artifact. | Replan after candidate persistence and approval transitions / candidate persistence と approval transition 後に再計画 | Deferred. |

Boundary / 境界:

- In scope: choose one next step after the schema contract. / 対象: schema contract 後の次 step を 1 つ選ぶ。
- Out of scope: push, public runtime URL, packaging, approval mutation without candidate persistence. / 対象外: push、public runtime URL、packaging、candidate persistence なしの approval mutation。
- Verification: `docker info` attempted and blocked by unavailable Docker daemon; planning/report update only. / 検証: `docker info` を試行し、Docker daemon unavailable のため blocked。planning / report 更新のみ。

## Approval Transition State Model First Slice / approval transition state model first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Approval Transition State Model First Slice](reports/2026/2026-0701-approval-transition-state-model-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Approval Transition State Model First Slice](reports/2026/2026-0701-approval-transition-state-model-first-slice.md)。

This docs-only slice defines approval transition states and transition boundaries before adding approval or publish mutation code. / この docs-only slice では approval / publish mutation code を追加する前に、approval transition state と transition boundary を定義しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| ATS1 | State model / state model | `DONE` | 0.25 day / 0.25 日 | Defined candidate, review, approved/rejected, superseded, and reserved published/rollback states. |
| ATS2 | Transition rules / transition rules | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Defined allowed first transitions and blocked transitions for blocked candidates, publish, and rollback. |
| ATS3 | Transition record contract / transition record contract | `DONE` | 0.25 day / 0.25 日 | Defined append-only transition event fields and current-status denormalization boundary. |
| ATS4 | UI and verification boundary / UI・verification 境界 | `DONE` | 0.25 day / 0.25 日 | Kept UI operator/admin-only and required Docker-backed verification plus focused tests before code. |

Boundary / 境界:

- In scope: docs-only approval transition state model, transition event contract, UI boundary, and verification boundary. / 対象: docs-only の approval transition state model、transition event contract、UI boundary、verification boundary。
- Out of scope: database migration, repository implementation, mutation UI, public runtime URL, artifact copying or packaging, push. / 対象外: database migration、repository implementation、mutation UI、public runtime URL、artifact copy / packaging、push。
- Verification: docs-only; Docker daemon remains unavailable. / 検証: docs-only。Docker daemon は unavailable のまま。

## Post-Approval-Transition-State-Model No-Code Product Goal Replan / approval transition state model 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0701 Post-Approval Transition State Model No-Code Product Goal Replan](reports/2026/2026-0701-post-approval-transition-state-model-no-code-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0701 Post-Approval Transition State Model No-Code Product Goal Replan](reports/2026/2026-0701-post-approval-transition-state-model-no-code-product-goal-replan.md)。

This planning item selected approval action UI contract as a docs-only continuation after the approval transition state model. / この planning item では approval transition state model 後の docs-only continuation として approval action UI contract を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Docker-backed verification rerun | Close schema-form/publish-readiness verification gaps before code. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Attempted and still blocked: Docker daemon unavailable. |
| Minimal candidate persistence | Implement stored candidate snapshots and focused repository tests. | 1 - 2 days / 1 - 2 日 | Deferred. It adds a new write path and should wait for Docker-backed verification. |
| Approval action UI contract | Define request-review / approve / reject / supersede UI boundaries before code or mutation behavior exists. | 0.5 - 1 day / 半日 - 1 日 | Selected. It defines action availability and blocked reasons without adding code. |
| Public runtime URL / packaging | Expose or package a published artifact. | Replan after candidate persistence and approval transitions / candidate persistence と approval transition 後に再計画 | Deferred. |

Boundary / 境界:

- In scope: choose one next step after approval transition planning. / 対象: approval transition planning 後の次 step を 1 つ選ぶ。
- Out of scope: push, public runtime URL, packaging, approval mutation without candidate persistence. / 対象外: push、public runtime URL、packaging、candidate persistence なしの approval mutation。
- Verification: `docker info` attempted and blocked by unavailable Docker daemon; planning/report update only. / 検証: `docker info` を試行し、Docker daemon unavailable のため blocked。planning / report 更新のみ。

## Approval Action UI Contract First Slice / approval action UI contract first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Approval Action UI Contract First Slice](reports/2026/2026-0701-approval-action-ui-contract-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Approval Action UI Contract First Slice](reports/2026/2026-0701-approval-action-ui-contract-first-slice.md)。

This docs-only slice defines the first operator/admin UI contract for future no-code publish candidate approval actions. / この docs-only slice では、将来の no-code publish candidate approval action 向け operator / admin UI contract を定義しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| AUI1 | Action surface / action surface | `DONE` | 0.25 day / 0.25 日 | Defined Request review, Approve, Reject, and Supersede as future first buttons; Publish/Rollback remain reserved. |
| AUI2 | Availability contract / availability contract | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Defined status, readiness, latest-candidate, permission, CSRF, and reason inputs plus blocked reason examples. |
| AUI3 | UI/request contract / UI・request contract | `DONE` | 0.25 day / 0.25 日 | Kept placement inside operator/admin no-code source-output inspection and defined fail-closed POST inputs. |
| AUI4 | Verification boundary / verification 境界 | `DONE` | 0.25 day / 0.25 日 | Requires Docker-backed verification, focused transition tests, route/source contract tests, and `make test` before code. |

Boundary / 境界:

- In scope: docs-only approval action UI contract, disabled/blocked reason behavior, POST request contract, and verification boundary. / 対象: docs-only の approval action UI contract、disabled / blocked reason behavior、POST request contract、verification boundary。
- Out of scope: database migration, candidate persistence implementation, approval mutation implementation, public runtime URL, artifact copying or packaging, push. / 対象外: database migration、candidate persistence implementation、approval mutation implementation、public runtime URL、artifact copy / packaging、push。
- Verification: docs-only; Docker daemon remains unavailable. / 検証: docs-only。Docker daemon は unavailable のまま。

## Post-Approval-Action-UI-Contract No-Code Product Goal Replan / approval action UI contract 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0701 Post-Approval-Action-UI-Contract No-Code Product Goal Replan](reports/2026/2026-0701-post-approval-action-ui-contract-no-code-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0701 Post-Approval-Action-UI-Contract No-Code Product Goal Replan](reports/2026/2026-0701-post-approval-action-ui-contract-no-code-product-goal-replan.md)。

This planning item selected approval route/test implementation plan as a docs-only continuation after the approval action UI contract. / この planning item では approval action UI contract 後の docs-only continuation として approval route/test implementation plan を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Docker-backed verification rerun | Close schema-form/publish-readiness verification gaps before code. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Attempted and still blocked: Docker daemon unavailable. |
| Minimal candidate persistence | Implement stored candidate snapshots and focused repository tests. | 1 - 2 days / 1 - 2 日 | Deferred. This adds a write path and should wait for Docker-backed verification or explicit acceptance of the gap. |
| Approval route/test implementation plan | Define route, repository, and source-contract test files for candidate persistence and first approval actions without writing code. | 0.5 - 1 day / 半日 - 1 日 | Selected. It can proceed docs-only and reduces ambiguity before code. |
| Public runtime URL / packaging | Expose or package a published artifact. | Replan after candidate persistence and approval transitions / candidate persistence と approval transition 後に再計画 | Deferred. |

Boundary / 境界:

- In scope: choose one next step after approval action UI contract. / 対象: approval action UI contract 後の次 step を 1 つ選ぶ。
- Out of scope: push, public runtime URL, packaging, approval mutation without candidate persistence. / 対象外: push、public runtime URL、packaging、candidate persistence なしの approval mutation。
- Verification: `docker info` attempted and blocked by unavailable Docker daemon; planning/report update only. / 検証: `docker info` を試行し Docker daemon unavailable のため blocked。planning / report 更新のみ。

## Approval Route/Test Implementation Plan First Slice / approval route・test implementation plan first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Approval Route/Test Implementation Plan First Slice](reports/2026/2026-0701-approval-route-test-implementation-plan-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Approval Route/Test Implementation Plan First Slice](reports/2026/2026-0701-approval-route-test-implementation-plan-first-slice.md)。

This docs-only slice defines the implementation order, routes, repository boundaries, and test plan for future no-code publish candidate persistence and approval transitions. / この docs-only slice では、将来の no-code publish candidate persistence と approval transition の実装順、route、repository boundary、test plan を定義しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| ARP1 | Implementation order / 実装順 | `DONE` | 0.25 day / 0.25 日 | Candidate repository and create/list/read routes come before approval transition mutation routes. |
| ARP2 | Route plan / route plan | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Defined POST/GET candidate revision routes and POST transition route request contracts. |
| ARP3 | Repository boundary / repository boundary | `DONE` | 0.25 day / 0.25 日 | Split candidate create/list/read from transition allow/append/update behavior. |
| ARP4 | Test and verification gate / test・verification gate | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Defined focused repository/transition tests, route/source-contract tests, and Docker-backed verification gate. |

Boundary / 境界:

- In scope: docs-only route names, request shapes, repository/helper boundaries, focused tests, route/source-contract tests, and verification gate. / 対象: docs-only の route name、request shape、repository / helper boundary、focused test、route / source-contract test、verification gate。
- Out of scope: database migration, repository implementation, route handlers, approval mutation behavior, public runtime URL, artifact copying or packaging, push. / 対象外: database migration、repository implementation、route handler、approval mutation behavior、public runtime URL、artifact copy / packaging、push。
- Verification: docs-only; `docker info` remains blocked by unavailable Docker daemon. / 検証: docs-only。`docker info` は Docker daemon unavailable のため blocked のまま。

## Post-Approval-Route/Test-Plan No-Code Product Goal Replan / approval route・test plan 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0701 Post-Approval Route/Test Plan No-Code Product Goal Replan](reports/2026/2026-0701-post-approval-route-test-plan-no-code-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0701 Post-Approval Route/Test Plan No-Code Product Goal Replan](reports/2026/2026-0701-post-approval-route-test-plan-no-code-product-goal-replan.md)。

This planning item selected publish candidate persistence implementation checklist after the approval route/test implementation plan. / この planning item では approval route/test implementation plan 後の次 step として publish candidate persistence implementation checklist を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Docker-backed verification rerun | Close schema-form/publish-readiness verification gaps before code. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Attempted and still blocked: Docker daemon unavailable. |
| Minimal candidate persistence | Implement stored candidate snapshots and focused repository tests using the route/test plan. | 1 - 2 days / 1 - 2 日 | Deferred. This adds a write path and should wait for Docker-backed verification or explicit acceptance of the gap. |
| Additional docs-only implementation checklist | Convert the route/test plan into file-level implementation checklist and migration checklist. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Selected. It can proceed docs-only and reduces implementation ambiguity before code. |
| Public runtime URL / packaging | Expose or package a published artifact. | Replan after candidate persistence and approval transitions / candidate persistence と approval transition 後に再計画 | Deferred. |

Boundary / 境界:

- In scope: choose one next step after approval route/test implementation planning. / 対象: approval route/test implementation planning 後の次 step を 1 つ選ぶ。
- Out of scope: push, public runtime URL, packaging, approval mutation without candidate persistence. / 対象外: push、public runtime URL、packaging、candidate persistence なしの approval mutation。
- Verification: `docker info` attempted and blocked by unavailable Docker daemon; planning/report update only. / 検証: `docker info` を試行し Docker daemon unavailable のため blocked。planning / report 更新のみ。

## Publish Candidate Persistence Implementation Checklist First Slice / publish candidate persistence implementation checklist first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0701 Publish Candidate Persistence Implementation Checklist First Slice](reports/2026/2026-0701-publish-candidate-persistence-implementation-checklist-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0701 Publish Candidate Persistence Implementation Checklist First Slice](reports/2026/2026-0701-publish-candidate-persistence-implementation-checklist-first-slice.md)。

This docs-only slice defines the concrete checklist for the future no-code publish candidate persistence implementation. / この docs-only slice では、将来の no-code publish candidate persistence implementation 向けの具体 checklist を定義しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| CPC1 | Helper checklist / helper checklist | `DONE` | 0.25 day / 0.25 日 | Defined create/list/read helper signatures and fail-closed inputs. |
| CPC2 | Storage checklist / storage checklist | `DONE` | 0.25 day / 0.25 日 | Defined identity, artifact identity, readiness snapshot, lifecycle, and `snapshot_json` fields. |
| CPC3 | Test checklist / test checklist | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 | Defined focused repository tests and route/source-contract tests before route wiring. |
| CPC4 | Verification gate / verification gate | `DONE` | 0.25 day / 0.25 日 | Kept Docker-backed verification and `make test` as required gates before code commit. |

Boundary / 境界:

- In scope: docs-only helper/storage/test checklist for candidate persistence. / 対象: candidate persistence の docs-only helper / storage / test checklist。
- Out of scope: database migration, repository implementation, route handlers, approval mutation behavior, public runtime URL, artifact copying or packaging, push. / 対象外: database migration、repository implementation、route handler、approval mutation behavior、public runtime URL、artifact copy / packaging、push。
- Verification: docs-only; `docker info` remains blocked by unavailable Docker daemon. / 検証: docs-only。`docker info` は Docker daemon unavailable のため blocked のまま。

## Post-Candidate-Persistence-Checklist No-Code Product Goal Replan / candidate persistence checklist 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0702 Post-Candidate-Persistence-Checklist No-Code Product Goal Replan](reports/2026/2026-0702-post-candidate-persistence-checklist-no-code-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0702 Post-Candidate-Persistence-Checklist No-Code Product Goal Replan](reports/2026/2026-0702-post-candidate-persistence-checklist-no-code-product-goal-replan.md)。

This planning item selected a docs-only migration/source-contract checklist after the candidate persistence implementation checklist. / この planning item では candidate persistence implementation checklist 後の次 step として docs-only migration / source-contract checklist を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Docker-backed verification rerun | Close schema-form/publish-readiness verification gaps before code. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Attempted on 2026-07-02 and still blocked: Docker daemon unavailable. |
| Minimal candidate persistence | Implement stored candidate snapshots and focused repository tests using the checklist. | 1 - 2 days / 1 - 2 日 | Deferred. It adds a write path and still requires Docker-backed verification or explicit verification-gap acceptance. |
| Docs-only migration/source-contract checklist | Define file-level migration/source-contract checklist before persistence code. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Selected. It can proceed safely while Docker remains unavailable and reduces implementation ambiguity. |
| Public runtime URL / packaging | Expose or package a published artifact. | Replan after candidate persistence and approval transitions / candidate persistence と approval transition 後に再計画 | Deferred. |

Boundary / 境界:

- In scope: choose one next step after candidate persistence implementation checklist. / 対象: candidate persistence implementation checklist 後の次 step を 1 つ選ぶ。
- Out of scope: push, public runtime URL, packaging, approval mutation without candidate persistence. / 対象外: push、public runtime URL、packaging、candidate persistence なしの approval mutation。
- Verification: `make sample28-no-code-schema-form-runtime-smoke` attempted and blocked by unavailable Docker daemon; planning/report update only. / 検証: `make sample28-no-code-schema-form-runtime-smoke` を試行し Docker daemon unavailable のため blocked。planning / report 更新のみ。

## Publish Candidate Migration Source-Contract Checklist First Slice / publish candidate migration・source-contract checklist first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Publish Candidate Migration Source-Contract Checklist First Slice](reports/2026/2026-0702-publish-candidate-migration-source-contract-checklist-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Publish Candidate Migration Source-Contract Checklist First Slice](reports/2026/2026-0702-publish-candidate-migration-source-contract-checklist-first-slice.md)。

This docs-only slice defines file-level migration and source-contract checks for the future publish candidate persistence implementation. / この docs-only slice では、将来の publish candidate persistence implementation 向けに file-level migration と source-contract check を定義しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| CMS1 | Migration checklist / migration checklist | `DONE` | 0.25 day / 0.25 日 | Defined `no_code_publish_candidate_revisions` columns and first indexes. |
| CMS2 | Source-contract checklist / source-contract checklist | `DONE` | 0.25 day / 0.25 日 | Defined create/list/find helper contracts and route/source-output integration constraints. |
| CMS3 | Fail-closed assertions / fail-closed assertions | `DONE` | 0.25 day / 0.25 日 | Listed non-runtime output, missing readiness, artifact/readiness mismatch, non-operator, and boundary read rejection cases. |
| CMS4 | Verification gate / verification gate | `DONE` | 0.25 day / 0.25 日 | Kept Docker-backed verification or explicit gap acceptance as the pre-code gate. |

Boundary / 境界:

- In scope: docs-only migration checklist, source-contract checklist, fail-closed assertions, and verification gate. / 対象: docs-only migration checklist、source-contract checklist、fail-closed assertion、verification gate。
- Out of scope: database migration implementation, repository implementation, route handlers, approval mutation behavior, public runtime URL, artifact copy or packaging, push. / 対象外: database migration implementation、repository implementation、route handler、approval mutation behavior、public runtime URL、artifact copy / packaging、push。
- Verification: docs-only; `make sample28-no-code-schema-form-runtime-smoke` is blocked by unavailable Docker daemon. / 検証: docs-only。`make sample28-no-code-schema-form-runtime-smoke` は Docker daemon unavailable のため blocked。

## Post-Candidate-Migration-Checklist No-Code Product Goal Replan / candidate migration checklist 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0702 Post-Candidate-Migration-Checklist No-Code Product Goal Replan](reports/2026/2026-0702-post-candidate-migration-checklist-no-code-product-goal-replan.md). / Status: `DONE`。Decision report: [2026-0702 Post-Candidate-Migration-Checklist No-Code Product Goal Replan](reports/2026/2026-0702-post-candidate-migration-checklist-no-code-product-goal-replan.md)。

This planning item selected repository/API contract test matrix as the final docs-only pre-implementation slice while Docker-backed verification was still blocked. / この planning item では Docker-backed verification がまだ blocked だったため、実装前最後の docs-only slice として repository / API contract test matrix を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Docker-backed verification rerun | Close schema-form/publish-readiness verification gaps before code. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Attempted before Docker restart and blocked; rerun later passed in the verification closure slice. |
| Minimal candidate persistence | Implement stored candidate snapshots and focused repository tests using the checklist. | 1 - 2 days / 1 - 2 日 | Deferred until the repository/API contract test matrix and Docker-backed verification closure were complete. |
| Docs-only repository fixture checklist | Define repository test fixtures and expected candidate records before persistence code. | 0.25 - 0.5 day / 0.25 - 0.5 日 | Selected as repository/API contract test matrix. |
| Public runtime URL / packaging | Expose or package a published artifact. | Replan after candidate persistence and approval transitions / candidate persistence と approval transition 後に再計画 | Deferred. |

Boundary / 境界:

- In scope: choose one next step after candidate migration/source-contract checklist. / 対象: candidate migration / source-contract checklist 後の次 step を 1 つ選ぶ。
- Out of scope: push, public runtime URL, packaging, approval mutation without candidate persistence. / 対象外: push、public runtime URL、packaging、candidate persistence なしの approval mutation。
- Verification: planning/report update only at decision time. Later Docker-backed verification passed in the verification closure slice. / 検証: 判断時点では planning / report 更新のみ。後続の verification closure slice で Docker-backed verification は通過。

## Publish Candidate Repository/API Contract Test Matrix First Slice / publish candidate repository・API contract test matrix first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Publish Candidate Repository API Contract Test Matrix First Slice](reports/2026/2026-0702-publish-candidate-repository-api-contract-test-matrix-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Publish Candidate Repository API Contract Test Matrix First Slice](reports/2026/2026-0702-publish-candidate-repository-api-contract-test-matrix-first-slice.md)。

This docs-only slice defined the repository/API contract test matrix for the future no-code publish candidate persistence implementation. / この docs-only slice では、将来の no-code publish candidate persistence 実装に向けた repository / API contract test matrix を定義しました。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RCM1 | Repository create matrix / repository create matrix | `DONE` | 0.25 day / 0.25 日 | Defined happy-path and fail-closed create cases from publish readiness snapshot. |
| RCM2 | List/find matrix / list・find matrix | `DONE` | 0.25 day / 0.25 日 | Defined project/source-output scoped list and find behavior, including cross-boundary rejection. |
| RCM3 | Source/API gates / Source・API gate | `DONE` | 0.25 day / 0.25 日 | Kept create route, approval routes, and mutation controls absent until repository tests pass. |
| RCM4 | Fixtures and verification gate / fixture・verification gate | `DONE` | 0.25 day / 0.25 日 | Defined happy/blocked fixtures and required Docker-backed verification before code. |

Boundary / 境界:

- In scope: repository create/list/find tests, fail-closed create/read cases, Source Outputs integration assertions, route/API absence/presence gates, and verification gate recap. / 対象: repository create / list / find test、fail-closed create / read case、Source Outputs integration assertion、route / API absence / presence gate、verification gate recap。
- Out of scope: database migration implementation, repository implementation, route handlers, approval mutation behavior, public runtime URL, artifact copy or packaging, push. / 対象外: database migration implementation、repository implementation、route handler、approval mutation behavior、public runtime URL、artifact copy / packaging、push。
- Verification: docs-only; Docker-backed verification passed later in the verification closure slice. / 検証: docs-only。Docker-backed verification は後続の verification closure slice で通過。

## Docker-Backed Verification Rerun Closure / Docker-backed verification rerun closure

Status: `DONE`. Report: [2026-0702 Docker-Backed Verification Rerun Closure](reports/2026/2026-0702-docker-backed-verification-rerun-closure.md). / Status: `DONE`。Report: [2026-0702 Docker-Backed Verification Rerun Closure](reports/2026/2026-0702-docker-backed-verification-rerun-closure.md)。

Docker was restarted and the recorded verification blocker was rerun successfully. / Docker が再起動され、記録済みの verification blocker は再実行で通過しました。

Verification / 検証:

- `make sample28-no-code-schema-form-runtime-smoke`: passed. / 通過。
- `make test`: passed (`311 tests, 10385 assertions, skipped 1`). / 通過（`311 tests, 10385 assertions, skipped 1`）。

## Post-Verification-Closure No-Code Product Goal Replan / verification closure 後の no-code product goal 再計画

Status: `DONE`. / Status: `DONE`。

This planning step chose minimal candidate persistence as the smallest code slice after Docker-backed verification closure. / この planning step では、Docker-backed verification closure 後の最小 code slice として minimal candidate persistence を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Minimal candidate persistence | Implement stored candidate snapshots and focused repository tests using the completed checklist/matrix. | 1 - 2 days / 1 - 2 日 | Selected and completed as the first slice. |
| Migration/bootstrap only | Add only table/bootstrap coverage first, then replan before repository create/list/find. | 0.5 - 1 day / 半日 - 1 日 | Candidate. Lower-risk first code slice if the persistence lane should stay very narrow. |
| Approval transition persistence | Start transition event storage. | Replan after candidate persistence / candidate persistence 後に再計画 | Deferred. Candidate records should exist first. |
| Public runtime URL / packaging | Expose or package a published artifact. | Replan after candidate persistence and approval transitions / candidate persistence と approval transition 後に再計画 | Deferred. |

Boundary / 境界:

- In scope: choose one next step after Docker-backed verification closure. / 対象: Docker-backed verification closure 後の次 step を 1 つ選ぶ。
- Out of scope: push, public runtime URL, packaging, approval mutation without candidate persistence. / 対象外: push、public runtime URL、packaging、candidate persistence なしの approval mutation。
- Verification: planning/report update only unless code is selected. / 検証: code を選ぶまでは planning / report 更新のみ。

## Minimal Publish Candidate Persistence First Slice / minimal publish candidate persistence first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0702 Minimal Publish Candidate Persistence First Slice](reports/2026/2026-0702-minimal-publish-candidate-persistence-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0702 Minimal Publish Candidate Persistence First Slice](reports/2026/2026-0702-minimal-publish-candidate-persistence-first-slice.md)。

This implementation slice added durable draft candidate revisions from publishable `NO-CODE-RUNTIME` readiness snapshots. / この implementation slice では、publishable な `NO-CODE-RUNTIME` readiness snapshot から durable draft candidate revision を保存できるようにしました。

Implemented / 実装:

- `no_code_publish_candidate_revisions` config-store table migration. / config-store table migration。
- Bootstrap required table/column checks. / bootstrap required table / column check。
- `app_pdo_create_no_code_publish_candidate_from_readiness_snapshot(...)`. / 作成 helper。
- `app_pdo_list_no_code_publish_candidates_for_source_output(...)`. / 一覧 helper。
- `app_pdo_find_no_code_publish_candidate(...)`. / 取得 helper。
- SQLite integration tests for bootstrap, create/list/find, fail-closed rejection, and scoped lookup. / bootstrap、create / list / find、fail-closed reject、scoped lookup の SQLite integration test。

Verification / 検証:

- `php -l mtool/app/no_code_publish_candidate_repository_pdo.php`: passed. / 通過。
- `php -l tests/Integration/NoCodePublishCandidateRepositorySqliteTest.php`: passed. / 通過。
- `php -l mtool/app/config_db_bootstrap.php`: passed. / 通過。
- Focused Docker-backed PHPUnit for `NoCodePublishCandidateRepositorySqliteTest`: passed (`4 tests, 52 assertions`). / focused Docker-backed PHPUnit は通過（`4 tests, 52 assertions`）。
- Full `make test`: passed (`315 tests, 10437 assertions, skipped 1`). / full `make test` は通過（`315 tests, 10437 assertions, skipped 1`）。

Boundary / 境界:

- In scope: draft candidate persistence and read repository only. / 対象: draft candidate persistence と read repository のみ。
- Out of scope: approval transitions, public URL, packaging, rollback, and route/UI mutation controls. / 対象外: approval transition、public URL、packaging、rollback、route / UI mutation controls。

## Post-Minimal-Candidate-Persistence No-Code Product Goal Replan / minimal candidate persistence 後の no-code product goal 再計画

Status: `DONE`. / Status: `DONE`。

This planning step selected approval transition persistence after durable draft candidate revisions landed. / この planning step では durable draft candidate revision 実装後に approval transition persistence を選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Approval transition persistence | Add request-review / approve / reject / supersede transition storage on top of draft candidates. | 1 - 2 days / 1 - 2 日 | Selected and completed. Candidate records now exist. |
| Source Outputs read-only candidate list | Surface existing candidate revisions without mutation controls. | 0.5 - 1 day / 半日 - 1 日 | Candidate. Useful if visibility should precede transition mutation. |
| Public runtime URL / packaging | Expose or package an approved artifact. | Replan after approval transitions / approval transition 後に再計画 | Deferred. Approval state should exist first. |

Boundary / 境界:

- In scope: choose the next small product slice after candidate persistence. / 対象: candidate persistence 後の次の小さな product slice を選ぶ。
- Out of scope: push, broad publishing workflow, rollback, public packaging before approval state. / 対象外: push、広い publishing workflow、rollback、approval state 前の public packaging。

## Next No-Code Product Goal After Runtime Polish Decision / runtime polish 後の次 no-code product goal decision

Status: `DONE`. Decision report: [2026-0630 Next No-Code Product Goal After Runtime Polish](reports/2026/2026-0630-next-no-code-product-goal-after-runtime-polish.md). / Status: `DONE`。判断 report: [2026-0630 Next No-Code Product Goal After Runtime Polish](reports/2026/2026-0630-next-no-code-product-goal-after-runtime-polish.md)。

Candidate product goals considered / 検討した候補:

| Candidate / 候補 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- |
| Data-first no-code domain sample 2 | 2 - 5 days / 2 - 5 日 | Selected. The polished runtime should now be tested against a slightly richer product-facing domain. |
| App-local sync demonstration | 2 - 5 days / 2 - 5 日 | Deferred. Still useful, but should follow one more generated Web/runtime domain proof. |
| Operator/admin no-code workflow | 1 - 3 days / 1 - 3 日 | Deferred. Needs a clearer operator surface and may distract from data-first runtime proof. |
| Additional runtime polish slice | 0.5 - 2 days / 半日 - 2 日 | Deferred unless the second domain sample exposes a concrete runtime presentation gap. |

## Generated No-Code Runtime UX Polish First Slice / generated no-code runtime UX polish first slice

This is the active implementation work selected by the post-sample28 product-goal replan. / これは sample28 後の product-goal replan で選んだ active implementation work です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| UX1 | Runtime copy and label polish / runtime 文言・label polish | `DONE` | 0.5 day / 半日 | Generated list/detail/form titles, subtitles, button text, and empty-state copy are readable without hand-editing sample output. |
| UX2 | Runtime state polish / runtime state polish | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Generated preview exposes ready/error preview state, empty/ready screen state, disabled/ready action state, and idle/working/success/error feedback state where deterministic. |
| UX3 | Action feedback polish / action feedback polish | `DONE` | 0.5 day / 半日 | Generated browser dispatch helper shows simple success / failure feedback for the existing authorized update intent smoke. |
| UX4 | Screenshot and smoke refresh / screenshot・smoke 更新 | `DONE` | 0.5 day / 半日 | sample07 and sample28 smoke coverage captures the polished runtime surface and verifies no regression in list/detail/form/update intent behavior. |

First-slice boundary / first slice 境界:

- In scope: generated `runtime-preview.html` / preview data presentation, deterministic browser-visible copy/state/action feedback, sample07 and sample28 smoke expectations, and docs/report updates. / 対象: generated `runtime-preview.html` / preview data presentation、deterministic な browser-visible copy/state/action feedback、sample07 と sample28 の smoke 期待値、docs/report 更新。
- Out of scope: new visual builder, new metadata tables, app-local sync expansion, new sample domain, native/Flutter targets, and broad redesign of generated runtime architecture. / 対象外: visual builder 追加、新 metadata table、app-local sync 拡張、新 sample domain、native / Flutter target、generated runtime architecture の大きな再設計。
- Verification: run the focused no-code runtime tests/smokes first, then choose whether `make test` is needed based on touched generator surface. / 検証: まず no-code runtime の対象 test / smoke を実行し、触った generator surface に応じて `make test` が必要か判断する。

## Next No-Code Product Goal Replan Decision / 次の no-code product goal 再計画 decision

Status: `DONE`. Decision report: [2026-0630 Next No-Code Product Goal Replan](reports/2026/2026-0630-next-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Next No-Code Product Goal Replan](reports/2026/2026-0630-next-no-code-product-goal-replan.md)。

Candidate product goals considered / 検討した候補:

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Notes |
| --- | --- | --- | --- |
| Generated runtime UX polish | Make the generated no-code preview more presentable after sample28. | 0.5 - 2 days / 半日 - 2 日 | Selected. Lowest risk product-facing improvement; builds directly on existing sample07/sample28 smokes. |
| Data-first no-code domain sample 2 | Prove the same path against a slightly richer relation/domain. | 2 - 5 days / 2 - 5 日 | Could introduce relation/read-model pressure; needs careful sample scope. |
| App-local sync demonstration | Connect no-code action intent more visibly to app-local persistence/sync concepts. | 2 - 5 days / 2 - 5 日 | Useful product story, but may touch more foundations. |
| Operator/admin no-code workflow | Show how an operator chooses or publishes no-code runtime artifacts. | 1 - 3 days / 1 - 3 日 | UI/admin surface needs clearer scope before implementation. |

## Completed No-Code Summary / 完了済み no-code summary

This section keeps the completed no-code path visible for context. It is not an active implementation list. / この section は完了済み no-code path を文脈として残します。active な実装リストではありません。

Current completed base / 現在完了済みの土台:

- Shared contract, App-local persistence, managed operation layer, `no-code-screen-definition-v0`, and first `no-code-runtime-v0` render / dispatch adapter are in place. / shared contract、App-local persistence、managed operation layer、`no-code-screen-definition-v0`、最初の `no-code-runtime-v0` render / dispatch adapter は実装済み。
- Remaining no-code work should focus on generated artifacts, sample execution, persisted operation flow, and minimal UI smoke. / 残りの no-code 作業は、生成 artifact、sample 実行、persisted operation flow、最小 UI smoke に集中する。

No-code target boundary / no-code 対象範囲:

| Target / 対象 | Current plan scope / 現在の計画範囲 | Note / 補足 |
| --- | --- | --- |
| Web app no-code / Web app no-code | In scope / 対象 | Primary target. Generate data-first list/detail/form behavior and browser-renderable runtime artifacts from shared contract and managed operation metadata. |
| HTML runtime preview / HTML runtime preview | In scope / 対象 | Current MVP surface. Generated `runtime-preview.html` is the first concrete UI artifact and is the target for basic UI smoke. |
| App-local DB / sync-backed data behavior / App-local DB・同期前提の data behavior | Foundation scope / 基盤対象 | Persistence, sync intent, and App-local execution are treated as foundations for generated data behavior, not as a separate native-app UI target. |
| iOS / Android native app no-code / iOS・Android native app no-code | Out of current scope / 現計画の対象外 | Possible future output family, but not part of the current runtime MVP or `sample28` target. |
| Flutter app no-code / Flutter app no-code | Out of current scope / 現計画の対象外 | Possible future output target only after the Web / HTML data-first runtime path is proven. |
| Visual builder / Visual builder | Out of current scope / 現計画の対象外 | Current plan generates screen definitions and runtime artifacts from canonical metadata; it does not introduce a drag-and-drop app builder. |

| Order | Step / ステップ | Status | Rough effort / 目安 | Commit guidance / コミット方針 |
| --- | --- | --- | --- | --- |
| 1 | `no-code-runtime-json` Source Output artifact / `no-code-runtime-json` Source Output artifact 化 | `DONE` | 30 min - 1.5 hours / 30 分 - 1.5 時間 | Strategy, generator, `screen-definition.json`, `runtime-preview.json`, and artifact generation test are in place. |
| 2 | Artifact publish path verification / artifact publish 経路検証 | `DONE` | 30 min - 1 hour / 30 分 - 1 時間 | Artifact create/publish path is covered by integration test. |
| 3 | Sample connection for no-code runtime artifact / no-code runtime artifact の sample 接続 | `DONE` | 1 - 3 hours / 1 - 3 時間 | sample07 now seeds `NO-CODE-RUNTIME` plus no-code shared contract metadata. |
| 4 | Generated screen/runtime sample check / 生成 screen/runtime の sample run 検証 | `DONE` | 1 - 2 hours / 1 - 2 時間 | sample07 pack check generates/publishes the artifact and verifies generated files, screen definition, and runtime preview. |
| 5 | One persisted operation flow / 1 操作の persisted flow | `DONE` | 0.5 - 1 day / 半日 - 1 日 | no-code runtime action dispatch now bridges to managed operation sync intent and updates a sample07 SQLite row through generated server DBAccess. |
| 6 | Minimal HTML/runtime renderer / 最小 HTML/runtime renderer | `DONE` | 1 - 3 days / 1 - 3 日 | Generated list/detail/form render to `runtime-preview.html` without introducing a visual builder or custom component framework. |
| 7 | Basic UI smoke for list/detail/form / list/detail/form basic UI smoke | `DONE` | 1 - 2 days / 1 - 2 日 | `sample07-no-code-runtime-ui-smoke` opens generated `runtime-preview.html` in headless Chromium and checks list/detail/form DOM plus screenshot capture. |
| 8 | Create/update browser or headless smoke / create/update browser または headless smoke | `DONE` | 1 - 3 days / 1 - 3 日 | Generated HTML now carries action metadata and a browser-side dispatch helper; the headless smoke verifies fail-closed disabled dispatch and an authorized update intent probe through generated key/input fields. |
| 9 | `sample28-no-code-data-app-mvp` / `sample28-no-code-data-app-mvp` | `DONE` | 3 days - 1.5 weeks / 3 日 - 1.5 週間 | First user-facing data-first no-code app MVP is complete through scaffold/catalog, no-code runtime artifact connection, generated flow smoke, docs, and pack verification. |
| 10 | No-code docs, reports, and plan cleanup / no-code docs・reports・plan cleanup | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Completed as part of sample28 MVP polish; future no-code work should be replanned from the next product goal. |

Sample28 first-slice breakdown / sample28 first slice 分解:

| Step | Work / 作業 | Status | Rough effort / 目安 | Commit guidance / コミット方針 |
| --- | --- | --- | --- | --- |
| 9a | sample28 scaffold and catalog entry / sample28 scaffold・catalog 登録 | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added tutorial directory, README, compose/run entrypoint, sample catalog entry, and minimal seed shell. |
| 9b | sample28 no-code runtime artifact connection / sample28 no-code runtime artifact 接続 | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Seeded shared contract / managed operation / Source Output metadata so sample28 emits `NO-CODE-RUNTIME` artifacts through the existing generator. |
| 9c | sample28 data-first generated flow smoke / sample28 data-first 生成 flow smoke | `DONE` | 1 - 2 days / 1 - 2 日 | Added `sample28-no-code-runtime-ui-smoke`, reusing the generated preview smoke with a sample28 profile to verify list/detail/form and operation dispatch intent. |
| 9d | sample28 MVP polish, docs, and pack verification / sample28 MVP polish・docs・pack 検証 | `DONE` | 1 - 3 days / 1 - 3 日 | README / report / current-plan status are updated, and sample28 pack compose/runtime/pack/UI/full-test verification is recorded. |

Rough total from the current state / 現在地からの合計目安:

- Minimal no-code runtime MVP means steps 1-8 and is now complete for the first path. / 最小 no-code runtime MVP は step 1-8 を指し、first path は完了済み。
- User-facing no-code app MVP including sample28 means steps 1-9: 2 - 4 weeks. / sample28 を含む見せられる no-code app MVP は step 1-9 を指し、2 - 4 週間。
- Conservative range with integration, smoke-test buffer, and cleanup means steps 1-10: 3 - 5 weeks. / 統合・smoke test の余裕と cleanup 込みの現実的レンジは step 1-10 を指し、3 - 5 週間。

## AI Context Completion Contract / AI 文脈出力の完了条件

AI context output is not complete merely because one sample emits `AI-CONTEXT-MD`. / AI 文脈出力は、1 sample が `AI-CONTEXT-MD` を出せるだけでは完了扱いにしません。

Completion requires all of the following. / 完了には以下をすべて必須とします。

1. Sample-wide rollout / sample 全体展開
   - Every relevant tutorial sample has an AI context source output definition or an explicit documented exclusion. / 関連する tutorial sample すべてに AI 文脈 Source Output 定義を追加するか、除外理由を明記する。
   - The output is regenerated by Mtool code, not written by AI. / 出力は AI が書かず、Mtool のコードが再生成する。
   - Reference snapshots and tests verify the generated MD/JSON package. / 生成された MD/JSON package を reference snapshot と test で検証する。
2. Default-output transition / 標準出力への移行
   - `AI-CONTEXT-MD` starts as an explicit rollout key, but the target state is default companion documentation output. / `AI-CONTEXT-MD` は展開中の明示 key として始めるが、到達点は標準 companion documentation output とする。
   - The temporary flag/key behavior must be removed or reduced to compatibility naming after default generation is stable. / 標準生成が安定した後、一時的な flag/key 挙動は削除するか互換名に縮小する。
3. Mtool self-output verification / Mtool 自身の self-output 検証
   - Mtool must generate AI context documentation for Mtool itself. / Mtool は Mtool 自身について AI 文脈ドキュメントを生成する。
   - AI reviews the generated documentation as a reader to check whether it correctly explains Mtool's own schema/output model. / AI は reader として生成ドキュメントを確認し、Mtool 自身の schema / output model を正しく説明できているか検証する。
   - Any review findings are fixed in generator code or canonical metadata, not by hand-editing generated output. / 確認で見つかった問題は、生成物を手修正せず、generator code または canonical metadata 側で直す。

Current status / 現在の状態:

- Sample-wide rollout is complete for tutorial samples. / tutorial sample 全体展開は完了。
- Implicit default generation is complete for current/new projects through the canonical source output catalog. / canonical source output catalog により、現在・新規 project への暗黙 default 生成は完了。
- Mtool self-output verification is covered by `ZzzAiContextStandardOutputTest`. / Mtool self-output 検証は `ZzzAiContextStandardOutputTest` で coverage 済み。
- `AI-CONTEXT-MD` remains as the standard compatibility key for generated AI context output. / `AI-CONTEXT-MD` は生成 AI 文脈 output の標準互換 key として残します。

## Status Meanings / 状態の意味

| Status | Meaning / 意味 |
| --- | --- |
| `ACTIVE_NEXT` | Recommended next work / 次に進める主線 |
| `ACTIVE_NEXT_FIRST_SLICE_DONE` | Recommended next work remains active, but a committed first slice is complete / 次に進める主線のままだが、first slice は完了済み |
| `DONE` | Completed within a detailed breakdown; keep only when it clarifies the path to remaining work / 詳細 breakdown 内で完了済み。残作業への道筋を明確にする場合だけ残す |
| `TODO` | Planned but not started / 計画済み・未着手 |
| `TODO_AFTER_REPLAN` | Planned placeholder whose concrete scope and estimate are decided by the preceding replan / 直前の replan で具体 scope と見積もりを決める placeholder |
| `CONDITIONAL` | Add only when the trigger condition becomes concrete / 条件が具体化した時だけ追加する |
| `LATER` | Useful later, not a current priority / 後で有用だが現在の優先ではない |
| `PARKED` | Intentionally deferred and not part of the quick plan list / 意図的に保留し、quick plan list には入れない |
| `PARKED_REPLAN` | Deferred until a fresh scope / value / risk decision is made / scope・価値・risk を再判断するまで保留 |

## Current Boundaries / 現在の境界

- Official date-less docs should describe implemented features only. / 日付なしの正式文書は、実装済み機能だけを説明する。
- Future output ideas stay in dated reports until the generator exists. / 将来の出力案は、generator ができるまで日付付き report に置く。
- Do not store hand-written output under generated-looking `examples/*/reference/` or `examples/*/generated/`. / 手書き出力を generated に見える `examples/*/reference/` や `examples/*/generated/` に置かない。
- Current plan answers should list only unfinished or deferred plans. / 現在の計画回答では、未完了または後回しの計画だけを出す。
- Current plan answers should separate the main plan to minimum from auxiliary later-review items. / 現在の計画回答では、minimum までの主計画と補助・後日検討項目を分けて答える。
- Conditional plans stay listed only when the trigger is clear. / 条件付き計画は、発火条件が明確なものだけ載せる。

## Replan And Parked Items / 再計画・保留項目

These are known candidates, but they should not appear as the next implementation unit without a fresh priority decision. / これらは既知の候補ですが、新しい優先判断なしに次の実装単位として扱いません。

| Item / 項目 | Status | Reopen condition / 再開条件 |
| --- | --- | --- |
| Mtool admin/lab route authorization hardening / admin・lab route authorization 強化 | `PARKED_REPLAN` | Replan when a concrete deployment need or one route cluster is ready, with audit/test scope defined. |
| Mtool config store PostgreSQL support / Mtool config store PostgreSQL 対応 | `PARKED` | Reopen only as a config-store portability project, separate from user DB/generated output PostgreSQL support. |
| SQL Server / Oracle current support / SQL Server・Oracle 現行対応 | `PARKED` | Reopen only with explicit enterprise need and support-scope decision. |
| Japanese invoice / billing / compliance sample / 日本向け請求・インボイス sample | `PARKED` | Reopen only after domain review is available. |
| Approval workflow, rollback / revision history, local app packaging / 承認 workflow、rollback・revision、local app packaging | `PARKED` | Reopen as separate product/foundation plans after current generated-output and docs lanes settle. |

## History / 履歴

Completed work was moved out of this active list. / 完了済み作業は、この active list から履歴側へ移しました。

| Completed scope / 完了済み範囲 | Historical source / 履歴ソース |
| --- | --- |
| AI context source output rollout, default-output transition, and self-output / AI 文脈 Source Output 展開・default 化・self-output | `AI-CONTEXT-MD` generator, all tutorial sample seed definitions, implicit default companion output for current/new projects, `sample17-multi-output-project` reference output, Mtool self-output verification, [Proof Matrix](proof-matrix.md). |
| Modernization audit MVP generator / 現代化診断 MVP generator | `modernization-audit-md` deterministic read-only audit generator, `sample17-multi-output-project` `MODERNIZATION-AUDIT-MD` seed/reference output, `Sample17MultiOutputProjectTest` coverage. |
| Goal-based help and wrapper CLI roadmap / 目的別 help と wrapper CLI roadmap | [Goal-Based Help And Wrapper CLI Roadmap](goal-based-help-and-wrapper-cli-roadmap.md) defining goal help groups, current command mapping, wrapper CLI command shape, and phased implementation order. |
| OSS / consulting readiness docs package | [Adoption Guide](adoption-guide.md), [Consulting Intake](consulting-intake.md), [Deliverables](deliverables.md), [Proof Matrix](proof-matrix.md), [Security And Data Handling](security-and-data-handling.md), [2026-0621 OSS / consulting readiness inventory](reports/2026/2026-0621-oss-consulting-readiness-inventory.md) |
| OSS / consulting readiness inventory | [2026-0621 OSS / consulting readiness inventory](reports/2026/2026-0621-oss-consulting-readiness-inventory.md) |
| Documentation foundation, curated legacy references, Laravel baseline, PostgreSQL input/backing, SQLite input/backing | [2026-0621 plan inventory](reports/2026/2026-0621-plan-inventory.md) |
| Database-first sales assets and future output placement rules | [2026-0621 database-first sales assets plan](reports/2026/2026-0621-database-first-sales-assets-plan.md) |
| PostgreSQL user DB output representative set | [2026-0620 PostgreSQL user DB output first slice](reports/2026/2026-0620-postgresql-user-db-output-first-slice.md) |
| PostgreSQL Input / Output support completion | [2026-0627 PostgreSQL input/output support completion](reports/2026/2026-0627-postgresql-user-db-lane-completion.md) |
| Generated PHP output namespace support | [2026-0627 PHP output namespace support plan](reports/2026/2026-0627-php-output-namespace-plan.md) |
| Mtool auth foundation first slice / Mtool auth 基盤 first slice | [2026-0629 Mtool auth foundation first slice](reports/2026/2026-0629-mtool-auth-foundation-first-slice.md) |
| Gate 0 App-local DB / sync feasibility studies / Gate 0 App 内 DB・同期 FS | [2026-0629 Shared Contract Manifest Spike](reports/2026/2026-0629-shared-contract-manifest-spike.md), [2026-0629 App Local SQLite Schema Spike](reports/2026/2026-0629-app-local-sqlite-schema-spike.md), [2026-0629 DTO Save/Read Spike](reports/2026/2026-0629-dto-save-read-spike.md). |
| Shared Contract Core Vocabulary / shared contract 最小語彙 | [2026-0629 Shared Contract Core Vocabulary](reports/2026/2026-0629-shared-contract-core-vocabulary.md) |
| Shared DataClass contract foundation / shared DataClass contract 基盤 | [2026-0629 Shared DataClass Contract Foundation First Slice](reports/2026/2026-0629-shared-dataclass-contract-foundation-first-slice.md) |
| App-local persistence first demo first slice / App-local persistence first demo first slice | [2026-0629 App-local Persistence First Slice](reports/2026/2026-0629-app-local-persistence-first-slice.md) |
| App-local DBAccess first slice / App-local DBAccess first slice | [2026-0629 App-local DBAccess First Slice](reports/2026/2026-0629-app-local-dbaccess-first-slice.md) |
| App-local persistence sample27 demo / App-local persistence sample27 demo | [2026-0629 App-local Persistence Sample27 Demo](reports/2026/2026-0629-app-local-persistence-sample27-demo.md) |
| App-local persistence Source Output artifacts / App-local persistence Source Output artifacts | [2026-0629 App-local Persistence Source Output Artifacts](reports/2026/2026-0629-app-local-persistence-source-output-artifacts.md) |
| Managed operation metadata first slice / managed operation metadata first slice | [2026-0629 Managed Operation Metadata First Slice](reports/2026/2026-0629-managed-operation-metadata-first-slice.md) |
| Managed operation docs Source Output / managed operation docs Source Output | [2026-0629 Managed Operation Docs Source Output](reports/2026/2026-0629-managed-operation-docs-source-output.md) |
| Managed operation execution plan adapter / managed operation execution plan adapter | [2026-0629 Managed Operation Execution Plan Adapter](reports/2026/2026-0629-managed-operation-execution-plan-adapter.md) |
| Managed operation sync intent skeleton / managed operation sync intent skeleton | [2026-0629 Managed Operation Sync Intent Skeleton](reports/2026/2026-0629-managed-operation-sync-intent-skeleton.md) |
| Managed operation sync outbox first slice / managed operation sync outbox first slice | [2026-0629 Managed Operation Sync Outbox First Slice](reports/2026/2026-0629-managed-operation-sync-outbox-first-slice.md) |
| Managed operation outbox status transitions / managed operation outbox status transitions | [2026-0629 Managed Operation Outbox Status Transitions](reports/2026/2026-0629-managed-operation-outbox-status-transitions.md) |
| Managed operation next pending outbox selection / managed operation next pending outbox selection | [2026-0629 Managed Operation Next Pending Outbox Selection](reports/2026/2026-0629-managed-operation-next-pending-outbox-selection.md) |
| Managed operation outbox claim contract / managed operation outbox claim contract | [2026-0629 Managed Operation Outbox Claim Contract](reports/2026/2026-0629-managed-operation-outbox-claim-contract.md) |
| Managed operation outbox processor contract / managed operation outbox processor contract | [2026-0629 Managed Operation Outbox Processor Contract](reports/2026/2026-0629-managed-operation-outbox-processor-contract.md) |
| Managed operation App-local executor first slice / managed operation App-local executor first slice | [2026-0629 Managed Operation App-local Executor First Slice](reports/2026/2026-0629-managed-operation-app-local-executor-first-slice.md) |
| Managed operation App-local outbox handler / managed operation App-local outbox handler | [2026-0629 Managed Operation App-local Outbox Handler](reports/2026/2026-0629-managed-operation-app-local-outbox-handler.md) |
| Managed operation server DBAccess executor first slice / managed operation server DBAccess executor first slice | [2026-0629 Managed Operation Server DBAccess Executor First Slice](reports/2026/2026-0629-managed-operation-server-dbaccess-executor-first-slice.md) |
| Managed operation server DBAccess outbox handler / managed operation server DBAccess outbox handler | [2026-0629 Managed Operation Server DBAccess Outbox Handler](reports/2026/2026-0629-managed-operation-server-dbaccess-outbox-handler.md) |
| Managed operation server DBAccess binding discovery / managed operation server DBAccess binding discovery | [2026-0629 Managed Operation Server DBAccess Binding Discovery](reports/2026/2026-0629-managed-operation-server-dbaccess-binding-discovery.md) |
| Managed operation server DBAccess candidate selection / managed operation server DBAccess candidate selection | [2026-0629 Managed Operation Server DBAccess Candidate Selection](reports/2026/2026-0629-managed-operation-server-dbaccess-candidate-selection.md) |
| Managed operation server DBAccess project catalog wiring / managed operation server DBAccess project catalog wiring | [2026-0629 Managed Operation Server DBAccess Project Catalog Wiring](reports/2026/2026-0629-managed-operation-server-dbaccess-project-catalog-wiring.md) |
| Managed operation server DBAccess real coverage / managed operation server DBAccess real coverage | [2026-0629 Managed Operation Server DBAccess Real Coverage](reports/2026/2026-0629-managed-operation-server-dbaccess-real-coverage.md) |
| Managed operation sample07 coverage / managed operation sample07 coverage | [2026-0629 Managed Operation Sample07 Coverage](reports/2026/2026-0629-managed-operation-sample07-coverage.md) |
| No-code runtime sample07 artifact connection / no-code runtime sample07 artifact 接続 | [2026-0629 No-Code Runtime Sample07 Artifact Connection](reports/2026/2026-0629-no-code-runtime-sample07-artifact.md) |
| No-code runtime persisted operation flow / no-code runtime persisted operation flow | [2026-0629 No-Code Runtime Persisted Operation Flow](reports/2026/2026-0629-no-code-runtime-persisted-operation-flow.md) |
| No-code runtime HTML renderer / no-code runtime HTML renderer | [2026-0629 No-Code Runtime HTML Renderer](reports/2026/2026-0629-no-code-runtime-html-renderer.md) |
| No-code runtime UI smoke / no-code runtime UI smoke | [2026-0630 No-Code Runtime UI Smoke](reports/2026/2026-0630-no-code-runtime-ui-smoke.md) |
| No-code runtime browser dispatch smoke / no-code runtime browser dispatch smoke | [2026-0630 No-Code Runtime Browser Dispatch Smoke](reports/2026/2026-0630-no-code-runtime-browser-dispatch-smoke.md) |
| Sample28 no-code data app first slice / sample28 no-code data app first slice | [2026-0630 Sample28 No-Code Data App First Slice](reports/2026/2026-0630-sample28-no-code-data-app-first-slice.md) |
| Sample28 no-code runtime UI smoke / sample28 no-code runtime UI smoke | [2026-0630 Sample28 No-Code Runtime UI Smoke](reports/2026/2026-0630-sample28-no-code-runtime-ui-smoke.md) |
| Sample28 no-code data app MVP polish / sample28 no-code data app MVP polish | [2026-0630 Sample28 No-Code Data App MVP Polish](reports/2026/2026-0630-sample28-no-code-data-app-mvp-polish.md) |
| Sample29 no-code support case first slice / sample29 no-code support case first slice | [2026-0630 Sample29 No-Code Support Case First Slice](reports/2026/2026-0630-sample29-no-code-support-case-first-slice.md) |
| Sample30 no-code App-local sync first slice / sample30 no-code App-local sync first slice | [2026-0630 Sample30 No-Code App-local Sync First Slice](reports/2026/2026-0630-sample30-no-code-app-local-sync-first-slice.md) |
| Mtool implementation namespace cleanup boundary / Mtool 実装 namespace cleanup boundary | [2026-0630 Mtool Implementation Namespace Cleanup Boundary](reports/2026/2026-0630-mtool-implementation-namespace-cleanup-boundary.md) |
| App local DB / sync roadmap and feasibility catalog / App 内 DB・同期 roadmap と FS catalog | [2026-0628 App Local DB And Sync Roadmap](reports/2026/2026-0628-app-local-db-and-sync-roadmap.md), [2026-0628 App Local DB Feasibility Studies](reports/2026/2026-0628-app-local-db-feasibility-studies.md). Roadmap / FS catalog drafting is done; Gate 0 core FS results are recorded separately. |
| Generated DataClass naming wording slice | [2026-0626 generated name migration sample follow-up](reports/2026/2026-0626-generated-name-migration-sample-follow-up.md) |
| Generated name migration first slice | [2026-0620 generated name migration plan](reports/2026/2026-0620-generated-name-migration-plan.md) |
| Post-security priority decisions and parked authorization gate | [2026-0620 post-security feature priority plan](reports/2026/2026-0620-post-security-feature-priority-plan.md) |

## Finding Rules / 探し方のルール

- Start here when asking "what plans remain?" / 「残っている計画は何か」を見る時はここから始める。
- Use date-less docs for current commitments. / 現在有効な約束は日付なし文書を見る。
- Use dated reports for history, decisions, and implementation records. / 履歴、判断経緯、実装記録は日付付き report を見る。
- Promote a report item into this page when it becomes active or user-facing. / report 内の項目が active または user-facing になったら、このページへ昇格する。
- Move completed items back to dated reports and keep this list short. / 完了項目は日付付き report へ戻し、この一覧は短く保つ。
