# Sample28 No-Code Data App MVP

- Role: user-facing data-first no-code app MVP sample.
- Path: canonical table metadata -> shared contract -> managed operation -> `NO-CODE-RUNTIME` artifact -> generated list/detail/form preview -> `NO-CODE-REACT-BRIDGE` artifact -> `NO-CODE-JSON-FORMS-PROBE` comparison artifact.
- Current MVP scope: sample scaffold, catalog entry, minimal `no_code_ticket` model, no-code runtime artifact generation, React-first bridge scaffold generation, JSON Forms / rjsf comparison artifact generation, and headless browser smoke for generated action dispatch.

Try it in the browser:

```bash
./sample/tutorials/sample28-no-code-data-app-mvp/run.sh up
./sample/tutorials/sample28-no-code-data-app-mvp/run.sh apply-seed
```

Then open `http://127.0.0.1:18291` and log in with:

```text
user: admin-local
password: change-this-admin-password
```

Open `projects` -> `SAMPLE28` -> `source-outputs` -> `NO-CODE-RUNTIME`, then either use `Run Sample28 Tryout Approval` for the demo path or create/review/approve a publish candidate manually. After approval, open the current public runtime preview:

```text
http://127.0.0.1:18291/runs/no-code/SAMPLE28/current/runtime-preview.html
```

For the fuller first-time tryout guide, see [No-Code Tryout](../../../docs/no-code-tryout.md).

In the generated runtime preview, editing form fields updates the local `Action Intent Draft` panel. This is a browser-side intent preview only; it does not execute a server update or bypass disabled policy checks.

Run:

```bash
./sample/tutorials/sample28-no-code-data-app-mvp/run.sh up
./sample/tutorials/sample28-no-code-data-app-mvp/run.sh apply-seed
make sample28-pack-runtime-test
make sample28-no-code-runtime-ui-smoke
make sample28-no-code-react-bridge-build-smoke
make sample28-no-code-react-bridge-browser-smoke
bash mtool/scripts/check_sample_pack_compose_smoke.sh --pack=sample28-no-code-data-app-mvp
bash mtool/scripts/check_sample_pack_runtime_smoke.sh --pack=sample28-no-code-data-app-mvp
```

Generated runtime preview target:

```text
work/source-outputs/SAMPLE28/NO-CODE-RUNTIME/runtime-preview.html
work/source-outputs/SAMPLE28/NO-CODE-RUNTIME/runtime-preview.json
work/source-outputs/SAMPLE28/NO-CODE-RUNTIME/screen-definition.json
work/source-outputs/SAMPLE28/NO-CODE-REACT-BRIDGE/bridge-contract.json
work/source-outputs/SAMPLE28/NO-CODE-REACT-BRIDGE/CONSUMER-NOTES.md
work/source-outputs/SAMPLE28/NO-CODE-REACT-BRIDGE/src/mtoolNoCodeBridge.ts
work/source-outputs/SAMPLE28/NO-CODE-JSON-FORMS-PROBE/schema-form-contract.json
work/source-outputs/SAMPLE28/NO-CODE-JSON-FORMS-PROBE/json-schema.json
work/source-outputs/SAMPLE28/NO-CODE-JSON-FORMS-PROBE/ui-schema.json
work/source-outputs/SAMPLE28/NO-CODE-JSON-FORMS-PROBE/CONSUMER-NOTES.md
```

The generated browser smoke verifies:

- `no_code_ticket_list`
- `no_code_ticket_detail`
- `no_code_ticket_form`
- disabled action dispatch fails closed
- authorized `update_no_code_ticket` dispatch maps key/input fields into a runtime action intent
- runtime preview screen summaries expose field count, action count, and screen key
- runtime preview accessibility affordances expose landmarks, labelled screen regions, action nav labels, and list table captions
- runtime preview action controls expose keyboard intent markers, screen-scoped action hints, and disabled action reasons
- React bridge artifact exposes a React + TypeScript scaffold around the same runtime/action-intent contract
- React bridge browser smoke renders the generated scaffold and observes its action-intent emission path
- React bridge display helpers render runtime cell `display_value` text without leaking raw `[object Object]` values
- React bridge contract invariants fix the scaffold schema version, runtime/action intent versions, and required files
- React bridge form inputs keep local edit state and emit changed scalar values in action intents
- React bridge form fields display existing required/readonly metadata as lightweight hints
- React bridge action feedback displays the last generated action intent locally
- React bridge consumer notes document the generated artifact boundary and frontend/schema-form ownership split
- React bridge and JSON Forms / rjsf probe consumer notes include parity notes for choosing which artifact to inspect
- React bridge and JSON Forms / rjsf probe consumer notes include adapter handoff checklists with required files, stable markers, and smoke commands
- React bridge and JSON Forms / rjsf probe consumer notes include troubleshooting notes for common adapter handoff failures
- React bridge and JSON Forms / rjsf probe consumer notes include a documentation index for parity, checklist, troubleshooting, and contract sections
- JSON Forms / rjsf probe emits comparison JSON Schema and UI Schema artifacts from the same form metadata
- JSON Forms / rjsf probe includes Mtool extension metadata for field keys, field types, required/readonly flags, action field roles, client-write hints, and UI validation hints
- JSON Forms / rjsf probe has a focused rjsf runtime smoke via `make sample28-no-code-schema-form-runtime-smoke`
- JSON Forms / rjsf probe consumer notes document the comparison-only boundary and schema-form consumer ownership split
