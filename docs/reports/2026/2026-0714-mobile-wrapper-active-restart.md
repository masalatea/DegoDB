# Mobile wrapper active restart / mobile wrapper active restart

Date: 2026-07-14

## Decision / 判断

The next active lane after Firebird is Mobile app handoff / wrapper productization.

Firebird remains complete for the agreed F100 scope. The mobile wrapper lane was already checkpointed at C1 wrapper-readiness, so the next slice is not native app generation. The next slice is to make the existing C1 package visible and repeatable from Mtool.

## Active first slice / active first slice

MW-1: React/web Capacitor C1 source-output route.

The first route is a CLI/source-output artifact path that emits:

- `wrapper-target-contract.json`
- `WRAPPER-CONSUMER-NOTES.md`

The route must not create:

- `package.json`
- `capacitor.config.ts`
- `ios/`
- `android/`
- signing files
- store submission files

## Initial implementation / 初期実装

Added:

- `mtool/scripts/create_mobile_wrapper_target.php`

Supported first command:

```sh
php mtool/scripts/create_mobile_wrapper_target.php --sample=sample28 --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/mobile-wrapper-target
```

This command uses the existing sample28 C1 handoff and the existing controlled emitter.

## Verification / 検証

- `php -l mtool/scripts/create_mobile_wrapper_target.php`: OK
- `php -l tests/Integration/MobileWrapperTargetTest.php`: OK
- CLI smoke:

```sh
php mtool/scripts/create_mobile_wrapper_target.php --sample=sample28 --target-dir=/private/tmp/mtool-mobile-wrapper-target-cli-check
```

Result:

- `ok: true`
- emitted `WRAPPER-CONSUMER-NOTES.md`
- emitted `wrapper-target-contract.json`

- Focused PHPUnit via sample pack container:

```sh
bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/MobileWrapperTargetTest.php
```

Result: `OK (11 tests, 71 assertions)`.

- Full test:

```sh
make test
```

Result: `OK, but incomplete, skipped, or risky tests! Tests: 664, Assertions: 15634, Skipped: 6.`

## Next / 次

MW-1 is complete as a CLI route.

The next active slice is MW-2: React/Web wrapper app handoff proof. This preserves the originally agreed order:

1. prove the React/Web wrapper app path first;
2. use Capacitor-style tooling as the first iOS/Android preparation target;
3. only after that, prepare Flutter and React Native input packets as later platform targets.

MW-3 can then generalize the input source beyond sample28, and MW-4 can produce Flutter / React Native input packets from the same validated app handoff idea.

## MW-2 first slice / MW-2 first slice

Added React/Web wrapper app handoff proof generation.

New functions:

- `app_mobile_wrapper_target_build_react_app_handoff_proof(array $handoff)`
- `app_mobile_wrapper_target_emit_react_app_handoff_proof(array $handoff, string $targetDir)`
- `app_mobile_wrapper_target_emit_sample28_react_app_handoff_proof(string $targetDir)`

Updated CLI:

```sh
php mtool/scripts/create_mobile_wrapper_target.php --sample=sample28 --artifact=react-wrapper-app --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/react-wrapper-app-handoff
```

Emitted files:

- `react-wrapper-app-handoff.json`
- `REACT-WRAPPER-APP-HANDOFF.md`

Boundary:

- no `package.json`;
- no `capacitor.config.ts`;
- no `ios/`;
- no `android/`;
- no signing or store submission files.

This keeps the agreed sequence: React/Web wrapper app proof first, Flutter / React Native input packets later.

Verification:

- `php -l mtool/app/mobile_wrapper_target.php`: OK
- `php -l mtool/scripts/create_mobile_wrapper_target.php`: OK
- `php -l tests/Integration/MobileWrapperTargetTest.php`: OK
- CLI smoke for `--artifact=react-wrapper-app`: OK
- Focused PHPUnit via sample pack container: `OK (14 tests, 97 assertions)`

## MW-3 handoff-file input / MW-3 handoff-file input

Added generic handoff file input to the CLI.

The command now accepts either:

- `--sample=sample28`; or
- `--handoff-file=PATH`.

Exactly one must be specified. The `--handoff-file` route loads a mobile app handoff JSON object and uses the same validation/build pipeline as the sample route.

Example:

```sh
php mtool/scripts/create_mobile_wrapper_target.php --handoff-file=work/mobile-app-handoff.json --artifact=react-wrapper-app --target-dir=work/mobile-wrapper-target/react-wrapper-app-handoff
```

Supported artifacts from handoff file:

- `--artifact=c1`
- `--artifact=react-wrapper-app`

Verification:

- `php -l mtool/scripts/create_mobile_wrapper_target.php`: OK
- `php -l tests/Integration/MobileWrapperTargetTest.php`: OK
- CLI smoke with `--handoff-file=/private/tmp/mtool-generic-mobile-app-handoff.json --artifact=react-wrapper-app`: OK
- Focused PHPUnit via sample pack container: `OK (17 tests, 109 assertions)`

MW-3 is complete for file-packet input. Project/source-output lookup can be added later if needed, but is not required before the later platform input packet work.

## MW-4 later platform input packet first slice / MW-4 later platform input packet first slice

Added later platform input packet generation for Flutter and React Native.

New functions:

- `app_mobile_wrapper_target_build_later_platform_input_packets(array $handoff)`
- `app_mobile_wrapper_target_emit_later_platform_input_packets(array $handoff, string $targetDir)`
- `app_mobile_wrapper_target_emit_sample28_later_platform_input_packets(string $targetDir)`

Updated CLI:

```sh
php mtool/scripts/create_mobile_wrapper_target.php --handoff-file=work/mobile-app-handoff.json --artifact=platform-input-packets --target-dir=work/mobile-wrapper-target/later-platform-input-packets
```

Emitted files:

- `flutter-input-packet.json`
- `react-native-input-packet.json`
- `LATER-PLATFORM-INPUT-PACKETS.md`

Boundary:

- Mtool emits structured input packets only;
- Mtool does not emit Dart source;
- Mtool does not emit React Native source;
- Mtool does not emit iOS / Android project files;
- Mtool does not emit signing or store-submission files.

Verification:

- `php -l mtool/app/mobile_wrapper_target.php`: OK
- `php -l mtool/scripts/create_mobile_wrapper_target.php`: OK
- `php -l tests/Integration/MobileWrapperTargetTest.php`: OK
- CLI smoke with `--artifact=platform-input-packets`: OK
- Focused PHPUnit via sample pack container: `OK (19 tests, 129 assertions)`

MW-4 is complete for the first input-packet slice.

## Current next decision / 現在の次判断

MW-1 through MW-4 now cover:

1. C1 wrapper-readiness package;
2. React/Web wrapper app handoff proof;
3. generic `--handoff-file` input;
4. Flutter / React Native later platform input packets.

Next work should be explicitly selected from:

- bundled package manifest that emits all mobile wrapper artifacts together;
- project/source-output lookup instead of only `--handoff-file`;
- external-framework input validation showing that React/Web + Capacitor-style builders can consume the generated handoff without Mtool owning the app scaffold;
- stop and commit/review this productization slice.

## MW-5 bundled package manifest / MW-5 bundled package manifest

Added a bundle manifest artifact that indexes the mobile wrapper package set without generating app projects.

New functions:

- `app_mobile_wrapper_target_build_bundle_manifest(array $handoff)`
- `app_mobile_wrapper_target_emit_bundle_manifest(array $handoff, string $targetDir)`
- `app_mobile_wrapper_target_emit_sample28_bundle_manifest(string $targetDir)`

Updated CLI:

```sh
php mtool/scripts/create_mobile_wrapper_target.php --handoff-file=work/mobile-app-handoff.json --artifact=bundle-manifest --target-dir=work/mobile-wrapper-target/mobile-wrapper-bundle
```

Emitted files:

- `mobile-wrapper-bundle-manifest.json`
- `MOBILE-WRAPPER-BUNDLE.md`

The manifest records the intended order:

1. C1 wrapper-readiness package;
2. React/Web wrapper app handoff;
3. later Flutter / React Native platform input packets.

Boundary:

- no production React app;
- no Capacitor project;
- no Flutter project;
- no React Native project;
- no iOS / Android project;
- no signing or store-submission files.

Verification:

- `php -l mtool/app/mobile_wrapper_target.php`: OK
- `php -l mtool/scripts/create_mobile_wrapper_target.php`: OK
- `php -l tests/Integration/MobileWrapperTargetTest.php`: OK
- CLI smoke with `--artifact=bundle-manifest`: OK
- Focused PHPUnit via sample pack container: `OK (21 tests, 146 assertions)`

MW-5 is complete. The remaining next decision is whether to add project/source-output lookup or user-facing UI integration inside Mtool, or to validate the generated inputs against an external React/Web + Capacitor-style builder boundary. The latter must remain an input-validation lane: Mtool emits handoff artifacts; the external framework/builder owns any app scaffold and native project.

## MW-6 project/source-output lookup / MW-6 project/source-output lookup

Added project/source-output lookup to the CLI.

The command now accepts one of:

- `--sample=sample28`;
- `--handoff-file=PATH`;
- `--project-key=KEY --source-output-key=KEY`.

Project/source-output lookup resolves:

```text
work/source-outputs/{PROJECT}/{SOURCE_OUTPUT}/mobile-app-handoff.json
```

The root can be overridden with:

```text
--source-output-root=DIR
```

Example:

```sh
php mtool/scripts/create_mobile_wrapper_target.php --project-key=PROJECT1 --source-output-key=MOBILE-HANDOFF --artifact=bundle-manifest --target-dir=work/mobile-wrapper-target/mobile-wrapper-bundle
```

Supported artifacts from project/source-output lookup:

- `c1`;
- `react-wrapper-app`;
- `platform-input-packets`;
- `bundle-manifest`.

Verification:

- `php -l mtool/scripts/create_mobile_wrapper_target.php`: OK
- `php -l tests/Integration/MobileWrapperTargetTest.php`: OK
- CLI smoke with `--project-key=PROJECT1 --source-output-key=MOBILE-HANDOFF --artifact=bundle-manifest`: OK
- Focused PHPUnit via sample pack container: `OK (22 tests, 154 assertions)`

MW-6 is complete. The remaining choices are now user-facing Mtool UI integration or external-framework input validation. The external-framework path should stay outside automatic continuation unless explicitly selected, because Mtool should only validate and improve the handoff artifacts; any app project remains owned by the external framework/builder.

## MW-7 read-only UI guide first slice / MW-7 read-only UI guide first slice

Added a read-only Mtool page helper for mobile wrapper artifact guidance.

New file:

- `mtool/app/mobile_wrapper_artifact_page.php`

New functions:

- `app_mobile_wrapper_artifact_page_contract()`
- `app_mobile_wrapper_artifact_page_html(array $contract, string $projectKey = '', string $sourceOutputKey = '')`

Boundary:

- no route wiring yet;
- no artifact generation execution from UI;
- no native project creation;
- no signing file creation;
- no store submission.

The helper renders:

- supported artifacts;
- example CLI command;
- explicit no-execution/no-native-project boundary.

Verification:

- `php -l mtool/app/mobile_wrapper_artifact_page.php`: OK
- `php -l tests/Integration/MobileWrapperTargetTest.php`: OK
- Focused PHPUnit via sample pack container: `OK (24 tests, 164 assertions)`

MW-7 is complete as a read-only helper. The next choice is whether to wire it into an authenticated Mtool route, add execution UI, or validate the generated input packets against an external React/Web + Capacitor-style consumer. Route wiring should define auth and no-execution boundaries before implementation.

## MW-8 authenticated read-only route wiring / MW-8 authenticated read-only route wiring

Wired the read-only mobile wrapper artifact guide into Mtool routing.

Route:

```text
GET /projects/{project_key}/mobile-wrapper-artifacts
```

Route name:

```text
project_mobile_wrapper_artifacts
```

Boundary:

- route requires auth;
- route is GET-only;
- route renders guidance only;
- route does not execute artifact generation;
- route does not create native/app/scaffold files.

Files updated:

- `mtool/app/router.php`
- `mtool/app/http.php`
- `mtool/app/mobile_wrapper_artifact_page.php`
- `tests/Integration/MobileWrapperTargetTest.php`

Verification:

- `php -l mtool/app/mobile_wrapper_artifact_page.php`: OK
- `php -l mtool/app/router.php`: OK
- `php -l mtool/app/http.php`: OK
- `php -l tests/Integration/MobileWrapperTargetTest.php`: OK
- Focused PHPUnit via sample pack container: `OK (25 tests, 167 assertions)`

MW-8 is complete. The remaining choices are artifact execution UI or external-framework input validation. Execution UI needs explicit CSRF, output-dir, overwrite, audit, and failure-policy design. External framework validation must keep Mtool as the artifact/input producer only; app scaffolding, native project creation, signing, and store work remain outside Mtool ownership.

## End-of-day status / 今日時点のステータス

As of the end of this work session, the mobile wrapper productization lane has moved from C1 checkpoint evidence to a usable Mtool artifact path and read-only UI guide.

Completed:

1. C1 wrapper-readiness artifact output.
2. React/Web wrapper app handoff proof.
3. Generic `--handoff-file` input.
4. Flutter / React Native later platform input packets.
5. Bundled mobile wrapper manifest.
6. Project/source-output lookup.
7. Read-only Mtool UI guide helper.
8. Authenticated read-only route:

```text
GET /projects/{project_key}/mobile-wrapper-artifacts
```

Current supported CLI input modes:

- `--sample=sample28`
- `--handoff-file=PATH`
- `--project-key=KEY --source-output-key=KEY`

Current supported artifact modes:

- `--artifact=c1`
- `--artifact=react-wrapper-app`
- `--artifact=platform-input-packets`
- `--artifact=bundle-manifest`

Still intentionally excluded:

- production React app generation;
- Capacitor project generation;
- Flutter project generation;
- React Native project generation;
- iOS / Android native project generation;
- signing files;
- store submission;
- UI-triggered artifact generation.

Latest focused verification:

- `OK (25 tests, 167 assertions)`
- `git diff --check`: OK

## Remaining decision / 残判断

The next step should not proceed automatically without an explicit product decision.

Remaining choices:

1. Define the feasibility study matrix first.
   - Compare multiple external FE/no-code consumers against the same Mtool output/handoff idea.
   - Do not harden one target or one output mode before comparable evidence exists.
2. Run first-round feasibility studies.
   - Start with React/Web + Capacitor-style consumption.
   - Add Flutter, React Native, and AI/code-builder consumption as comparable candidates when useful.
   - Record fit, missing metadata, required artifacts, ownership boundary, and blockers per candidate.
3. Extract common requirements in a second pass.
   - Identify the common Mtool-owned handoff artifact set.
   - Separate target-specific extensions from shared contract requirements.
   - Only after this pass should output mode settings be hardened.
4. Harden output mode settings.
   - Let the app creator choose Mtool-owned no-code output, external no-code/framework-compatible output, or an explicit hybrid output.
   - This should be recorded as source-output/app-output configuration before adding deeper implementation or execution UI.
5. Add artifact execution UI inside Mtool only after output modes are explicit.
   - Needs CSRF, output-dir policy, overwrite policy, audit policy, and failure policy.
6. Stop here and commit/review the mobile wrapper productization slice.

## Boundary correction / 境界訂正

The mobile wrapper lane should not be read as an immediate full migration from Mtool output to external frameworks, nor as a plan for Mtool to generate complete React, Capacitor, Flutter, React Native, iOS, or Android application projects.

The corrected product line is:

- Mtool already emits useful web/no-code/runtime artifacts.
- Mtool continues to own and improve those outputs in the short-to-mid term.
- Mtool also emits validated app handoff artifacts and framework input packets where external-framework support is useful.
- Existing external frameworks/builders can consume those inputs.
- React/Web + Capacitor-style tooling is the first external consumer target.
- Flutter and React Native remain later input-packet targets.
- When an external target is used, the external owner/tool can own the app codebase, scaffold, native project, token-storage implementation, build configuration, signing, QA, and store submission.
- Long term, frontend/app-surface ownership should move toward external FE/no-code frameworks because that is not Mtool's core responsibility.
- Full migration should wait until the external FE/no-code target is proven good enough for Mtool users.

This matches the earlier roadmap in `docs/reports/2026/2026-0713-mobile-app-handoff-wrapper-roadmap.md`, with one clarification: the target is staged compatibility first, then possible long-term migration of FE/app-surface responsibility after the external tool choice is validated.

## Feasibility-first direction / feasibility-first 方針

Because this is not an immediate full migration, the next lane should be evidence-gathering rather than output-mode hardening.

Planned order:

1. first-round feasibility studies across several external FE/no-code consumers;
2. second-pass common requirement extraction;
3. output mode and artifact contract hardening;
4. implementation/UI policy only after the contract is stable.

First-round studies should be comparable. Each candidate should use the same representative Mtool handoff/output idea and report:

- whether the candidate can consume Mtool's current artifacts naturally;
- which metadata is missing;
- which generated packet shape it needs;
- what remains outside Mtool ownership;
- whether it is worth carrying into the second pass.

## Layered feasibility study targets / layer別 FS target 一覧

The first pass should not compare unlike layers as if they were alternatives. A FE/app framework, an AI/code-builder, and a delivery mode answer different questions. Feasibility studies should therefore be grouped by layer, compared inside the same layer, and then connected through dependency notes.

1周目では、layer の違うものを同列比較しない。FE / app framework、AI / code-builder、delivery mode はそれぞれ答える問いが違う。したがって FS は layer ごとに分け、同じ layer 内で比較し、layer 間は依存関係として記録する。

### Layer A: FE / app framework consumers

This layer compares app-surface implementation frameworks. These are the closest candidates for long-term external ownership of frontend/app UI.

| Priority | FS target | Why it is included | Main study question | Expected Mtool-owned input |
| --- | --- | --- | --- | --- |
| A1 | React/Web + Capacitor-style wrapper | Closest to current Mtool web/no-code/runtime output and the existing mobile wrapper lane. | Can existing Mtool web/runtime artifacts become a mobile wrapper app input without Mtool owning native projects? | `mobile-app-handoff.json`, wrapper target contract, API/auth/screen/action/error metadata, bundle manifest. |
| A2 | Flutter input packet | Strong cross-platform UI/app framework candidate, but it needs framework-specific widget/state/navigation decisions. | What extra metadata is required for Dart/widget/state/navigation generation compared with the React/Web wrapper route? | Framework-neutral app spec plus Flutter extension packet. |
| A3 | React Native input packet | Useful where native UI is required, but likely needs more platform-specific state/navigation/plugin choices. | What differs from Flutter and React/Web wrapper, and what can remain shared? | Framework-neutral app spec plus React Native extension packet. |

### Layer B: AI / code-builder consumers

This layer is not a FE framework layer. It checks whether an AI builder can read Mtool artifacts and drive implementation in a selected framework without guessing core behavior.

| Priority | FS target | Why it is included | Main study question | Expected Mtool-owned input |
| --- | --- | --- | --- | --- |
| B1 | Codex-style code-builder handoff | Likely practical route for users: AI reads Mtool artifacts and creates/adjusts app code with user confirmation. | Is the handoff clear enough that an AI builder can ask only necessary questions and avoid guessing core app behavior? | Human-readable handoff, structured task packet, validation checklist, non-goals, ownership boundary. |
| B2 | Claude-style code-builder handoff | Same broad consumer class as Codex, useful to check whether the packet is provider-neutral. | Which instructions must be provider-neutral versus tool-specific? | Provider-neutral task packet plus optional tool-specific notes. |

### Layer C: delivery / runtime mode consumers

This layer is not a FE framework layer. It studies how an already-created web/app surface is delivered or run.

| Priority | FS target | Why it is included | Main study question | Expected Mtool-owned input |
| --- | --- | --- | --- | --- |
| C1 | PWA from Mtool web/no-code output | Low-friction browser/mobile delivery path that may share much of the React/Web wrapper input. | Can PWA readiness be expressed as metadata and checklist without becoming a separate app generation lane? | Web runtime readiness, manifest/service-worker requirements, auth/storage constraints, validation checklist. |
| C2 | Native wrapper packaging boundary | Needed to describe what Capacitor-style wrapping requires without making Mtool own native build output. | Which native preparation facts belong in Mtool metadata versus external tooling? | Native capability declaration, plugin checklist, signing/store non-goals. |

### Parked / out-of-scope layer

| FS target | Reason parked | Revisit condition |
| --- | --- | --- |
| Direct native iOS/Android generation | Too broad for Mtool ownership: native build, signing, device QA, store submission. | Only revisit if a concrete product demand appears. |
| Specific commercial no-code platforms | Provider-specific and likely unstable before common requirements are known. | Revisit after common handoff requirements are extracted and a real target is selected. |

First-pass comparison columns should be stable inside each layer:

- setup assumptions;
- required Mtool artifacts;
- missing metadata;
- auth/token-storage expectations;
- API/action/validation/error mapping;
- local storage/offline expectations;
- native/plugin responsibility;
- generated files owned by external tool;
- blocker severity;
- recommendation: continue, park, or reject.

Cross-layer notes should be recorded separately. For example, React/Web + Capacitor in Layer A may depend on PWA/runtime readiness in Layer C, and Codex-style handoff in Layer B may generate a React Native implementation in Layer A. Those are dependencies, not same-layer alternatives.

## Feasibility study storage decision / FS置き場所の決定

The current, date-less specification for mobile external feasibility studies is:

- `docs/mobile-external-feasibility-study.md`

Use that document as the stable reference for layer definitions, FS target grouping, comparison columns, and storage paths.

Repository development working artifacts go under:

```text
work/feasibility-studies/mobile-wrapper/{YYYYMMDD}-{study-key}/
```

User-workspace feasibility artifacts go under:

```text
{project_root}/mtool-workspace/mtool-project/feasibility-studies/mobile-wrapper/{study-key}/
```

User-facing summaries and compact validation evidence go under:

```text
{project_root}/mtool-workspace/review-artifacts/mobile-feasibility/{study-key}.md
{project_root}/mtool-workspace/validation/mobile-feasibility/{study-key}-summary.json
```

Reviewed, durable decisions go under dated reports:

```text
docs/reports/YYYY/YYYY-MMDD-mobile-fs-{study-key}.md
```

This keeps three meanings separate:

1. `work/` is local repository development scratch/evidence.
2. `mtool-workspace/mtool-project/` is Mtool-owned user workspace state.
3. `docs/reports/` is the durable project decision/history record.

Bulky generated apps, dependency folders, native projects, build outputs, credentials, tokens, and signing material must not be committed. If such artifacts are needed during a study, keep them outside the repository or under ignored work storage, then summarize the result in the dated report.

## Output setting direction / output setting 方針

The output setting remains necessary, but it should be hardened after the feasibility pass and common-requirement pass, not before them.

Candidate output modes:

| Mode | Meaning | Mtool ownership |
| --- | --- | --- |
| `mtool_no_code` | Use Mtool's own generated web/no-code/runtime output as the primary app surface. | Short-to-mid-term supported fallback and current working output. Mtool owns the generated output and its validation boundary. |
| `external_no_code` | Emit handoff/manifest/input artifacts for an external no-code or app framework builder. | Long-term preferred responsibility boundary once the external FE/no-code target is proven. Mtool owns the input packet; the external tool owns its generated app/project. |
| `hybrid` | Keep Mtool output and also emit external-framework input artifacts for selected targets. | Transitional mode while external FE/no-code suitability is being validated. Mtool owns its output plus the handoff boundary; external tool owns its own app/project. |

This makes the product behavior explicit:

- selecting external support is not an immediate full migration away from Mtool;
- selecting Mtool output does not block later external handoff;
- hybrid is allowed only when the app creator intentionally wants both output surfaces;
- long-term preference can be external while short-to-mid-term execution remains selectable and evidence-driven;
- validation should check that the selected mode has the required artifacts and does not silently imply native project ownership.
