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

## Expected Preview / 期待される preview

The page title should be `No-Code Runtime Preview`.

The preview should show `SAMPLE28` with three generated screens:

- `No Code Ticket List`
- `No Code Ticket Detail`
- `No Code Ticket Form`

The list screen should include sample tickets such as `First no-code app ticket`, `Review generated customer fields`, and `Prepare approval handoff`.

Some actions may be disabled in this preview when policy checks do not enable them. That is expected for the guarded preview path.

When you edit fields in the generated form, the `Action Intent Draft` panel updates locally. This shows the no-code action-intent shape that would be handed to the managed operation layer. The state badge and summary line call out whether the draft is ready or blocked, including policy reasons such as `principal.missing`; the metadata row shows the action key, operation key, and operation type; the field row shows key/input/filter field names; the payload row shows key/input/filter field counts; and the `Draft JSON` disclosure includes the full draft and policy check lists. Use `Copy draft JSON` if you want to keep the current draft in notes while trying the preview. It is only a preview: disabled actions stay disabled and no server update is executed from this static page.

## If You Get Lost / 迷った場合

- If you only see artifact download or manifest information, go back to `NO-CODE-RUNTIME` detail. The artifact detail page is read-only.
- If the public preview returns `404`, the runtime probably has not been approved yet, or no current public revision has been selected.
- If `app-local package` readiness is blocked on the Source Outputs page, continue with the Web preview path. App-local packaging is a different scenario, and the `Tryout Next Steps` card on `NO-CODE-RUNTIME` points back to the Web preview route.
