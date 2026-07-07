# No-Code Tryout / no-code をまず試す

English companion:
Use this page when you want to start the Docker sample and see a generated no-code app preview in the browser before reading the deeper implementation documents. / 詳細な実装文書を読む前に、Docker sample を起動して生成 no-code app preview をブラウザで見るための入口です。

## What This Shows / 何が見えるか

This tryout uses `sample28-no-code-data-app-mvp`. It shows the current Web no-code path:

1. start the local Docker stack;
2. open the admin UI;
3. inspect the generated `NO-CODE-RUNTIME`;
4. create and approve a publish candidate;
5. open the public runtime preview.

This is a Web preview flow. App-local package readiness is a separate lane and may be blocked in sample28 when the app-local package Source Output definition is not present.

This no-code path sits on top of DegoDB's database-first foundation. The preview is generated from canonical metadata and Source Output artifacts, then exposed through publish candidate approval. It is not a separate screen builder detached from the database model.

Why the foundation matters / なぜ基盤が重要か:

- The list/detail/form screens come from canonical table, Data Class, DB Access, and no-code metadata. / list / detail / form screen は canonical table、Data Class、DB Access、no-code metadata から生成されます。
- Artifact-key previews stay static for immutable artifact inspection, while authenticated current/alias previews can fetch read-only live runtime data through `runtime-data.json`. / artifact-key preview は immutable artifact inspection 用に static のままで、authenticated current / alias preview は `runtime-data.json` 経由で read-only live runtime data を取得できます。
- Submit uses a managed-operation intent and sync outbox boundary instead of a hidden browser-only mutation model. / submit は hidden な browser-only mutation model ではなく、managed-operation intent と sync outbox boundary を使います。
- Public preview URLs are tied to reviewed publish candidates, current revision selection, and optional aliases. / public preview URL は review 済み publish candidate、current revision selection、optional alias に紐づきます。
- Demo-only synchronous processing can be enabled for tryout environments, but the default product path remains async outbox handoff. / tryout environment では demo-only synchronous processing を有効化できますが、default product path は async outbox handoff のままです。

## Start Docker / Docker を起動する

From the repository root:

```bash
./sample/tutorials/sample28-no-code-data-app-mvp/run.sh up
./sample/tutorials/sample28-no-code-data-app-mvp/run.sh apply-seed
```

Open:

```text
http://127.0.0.1:18291
```

Local stub login:

```text
user: admin-local
password: change-this-admin-password
```

## Browser Path / ブラウザで辿る道順

1. Log in to the admin site.
2. Open `projects`.
3. Open `SAMPLE28`.
4. Open `source-outputs`.
5. Open `NO-CODE-RUNTIME`.
6. Confirm the publish readiness is `publishable`.
7. Read `Tryout Next Steps` on the detail page.
8. For the guided demo path, click `Run Sample28 Tryout Approval`.
9. Open `current public runtime preview`.

The normal operator path is still available:

1. Click `Create Publish Candidate`.
2. Click `Request Review`.
3. Click `Approve`.
4. Click `Set Current Public Revision` when the approved candidate is not yet current.
5. Keep or set alias `stable` with `Set Public Alias`.
6. Open `current public runtime preview`.

The demo shortcut is scoped to sample28. It creates a candidate, requests review, approves it, selects the current public revision, and sets the `stable` alias so the first tryout can reach the preview quickly.

The current public preview URL is:

```text
http://127.0.0.1:18291/runs/no-code/SAMPLE28/current/runtime-preview.html
```

The stable alias preview URL is:

```text
http://127.0.0.1:18291/runs/no-code/SAMPLE28/alias/stable/runtime-preview.html
```

Current and alias previews can also load live read-only runtime data from the matching `runtime-data.json` endpoint after login. Use the generated Refresh / Search / Filter / Sort / Page controls to explore the current data snapshot. Artifact-key preview URLs are intentionally static and keep reload-style behavior instead of fetching live runtime data.

current / alias preview は login 後、対応する `runtime-data.json` endpoint から read-only live runtime data も読み込めます。生成された Refresh / Search / Filter / Sort / Page controls で current data snapshot を確認します。artifact-key preview URL は意図的に static であり、live runtime data を取得せず reload-style behavior を保ちます。

Second-domain reference / 2 つ目の domain reference:

`sample29-no-code-support-case-demo` is the current second-domain proof for the same runtime submit and outbox processing shape. Use it when you want to confirm that the no-code flow is not hard-coded to sample28 tickets.

`sample29-no-code-support-case-demo` は、同じ runtime submit と outbox processing の形を確認するための 2 つ目の domain proof です。no-code flow が sample28 ticket 専用ではないことを確認したい時に使います。

`sample31-no-code-inventory-request-demo` is the current third-domain generated runtime proof. It uses an inventory request domain with warehouse, item, quantity, status, and fulfillment note fields to confirm that the database-first no-code runtime repeats beyond ticket/support workflows. Its public runtime smoke also verifies current/alias submit, endpoint enqueue, and generated server DBAccess outbox processing against an isolated SQLite row.

`sample31-no-code-inventory-request-demo` は、現在の 3 つ目の domain generated runtime proof です。warehouse、item、quantity、status、fulfillment note を持つ inventory request domain を使い、database-first no-code runtime が ticket / support workflow 以外にも反復できることを確認します。public runtime smoke では current / alias submit、endpoint enqueue、generated server DBAccess outbox processing による isolated SQLite row 更新も確認します。

## Expected Preview / 期待される preview

The page title should be `No-Code Runtime Preview`.

The preview should show `SAMPLE28` with three generated screens:

- `No Code Ticket List`
- `No Code Ticket Detail`
- `No Code Ticket Form`

The list screen should include sample tickets such as `First no-code app ticket`, `Review generated customer fields`, and `Prepare approval handoff`.

Some actions may be disabled in artifact previews when policy checks do not enable them. That is expected for the guarded preview path. Authenticated current and alias previews can expose `Submit to server` when the action draft is ready.

When you edit fields in the generated form, the `Action Intent Draft` panel updates locally. Required generated form fields are marked inline, and the required hint follows the local draft state so a filled value is shown as present and a blank required value is shown as missing. The panel shows the no-code action-intent shape that would be handed to the managed operation layer. The state badge and summary line call out whether the draft is ready or blocked, including policy reasons such as `principal.missing`; the metadata row shows the action key, operation key, and operation type; the field row shows key/input/filter field names; the payload row shows key/input/filter field counts; and the `Draft JSON` disclosure includes the full draft and policy check lists. Use `Copy draft JSON` if you want to keep the current draft in notes while trying the preview.

After a server submit is accepted, the runtime shows the sync outbox status, the outbox detail path, copy/open affordances, and a Submit / Outbox tracking / Refresh flow indicator. In the default path, processing remains async: process the outbox item, then use `Refresh preview` to reload the public runtime page. Demo-only synchronous processing is an opt-in environment mode; do not expect it in the normal sample stack unless it was explicitly enabled.

On current/alias previews, `Refresh preview` fetches read-only live runtime data and re-renders list/detail/form bodies from the approved runtime selection. If that fetch fails, the preview keeps the current rendered data and shows non-mutating error wording. On artifact-key previews, Refresh keeps the static artifact inspection behavior.

## If You Get Lost / 迷った場合

- If you only see artifact download or manifest information, go back to `NO-CODE-RUNTIME` detail. The artifact detail page is read-only.
- If the public preview returns `404`, the runtime probably has not been approved yet, or no current public revision has been selected.
- If `app-local package` readiness is blocked on the Source Outputs page, continue with the Web preview path. App-local packaging is a different scenario, and the `Tryout Next Steps` card on `NO-CODE-RUNTIME` points back to the Web preview route.
