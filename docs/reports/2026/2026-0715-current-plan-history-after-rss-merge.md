# 2026-0715 Current Plan History After RSS Merge

## Purpose / 目的

This dated history archive preserves the completed plan rows that were removed from `docs/current-plans.md` after the shared-state sync packet stack was merged through `master`.

この日付付き履歴 archive は、shared-state sync packet stack が `master` まで merge された後に、`docs/current-plans.md` から退避した完了済み計画行を保持する。

The intent is not to delete completed work. The active plan index should stay small, while completed details remain traceable from this dated history file.

完了済み作業を削除する意図ではない。active plan index は小さく保ち、完了済み詳細はこの日付付き履歴ファイルから辿れるようにする。

## Repository State / repository 状態

As of this history archive:

- `origin/develop` contains the shared-state sync packet stack squashed into four semantic commits.
- `origin/master` contains `origin/develop` via PR #81.
- There is no remaining `origin/master..origin/develop` diff.
- The old feature branch `feature/shared-state-sync-packets` was removed locally and from remote tracking.
- A local backup branch remains: `backup/develop-before-shared-state-squash-20260715`.

## Current Conclusion / 現在の結論

The following scopes are complete for their agreed boundaries:

- Firebird: Mtool/profile + migration-path scope.
- Standalone Mtool no-code: supported-contract scope.
- Mobile/external no-code wrapper handoff: artifact / packet / feasibility / policy scope.
- Shared-state sync: contract / schema / realtime / server-input / client-input packet scope.

The next active plan should be selected as a new product-scope decision. It should not be treated as an unfinished continuation of RSS-1 through RSS-16.

## Archived Main Plan Rows / archive 済み Main Plan 行

These rows were previously retained in `docs/current-plans.md` and are now archived here.

| Order | Work unit / 作業の塊 | Final status / 最終状態 | Notes / 補足 |
| --- | --- | --- | --- |
| 901 | Firebird 100% support replan | `DONE_FIREBIRD_100_QUALIFIED` | Completed for agreed MySQL-equivalent Mtool/profile support plus SQLite -> Firebird and Firebird -> MySQL/MariaDB migration paths. |
| 902 | Mobile app handoff / wrapper productization | `DONE_CURRENT_SCOPE` | MW-1 through MW-13 completed for current artifact / handoff / feasibility / policy scope. |
| 903 | Standalone Mtool no-code completion | `DONE_SUPPORTED_CONTRACT_SCOPE` | Completed by `2026-0715-standalone-no-code-completion-report.md`. |
| 904 | External framework optional output boundary check | `DONE_FIRST_MATRIX` | First optional-output boundary matrix completed. |
| 905 | React/Web + Capacitor optional output packet | `FIRST_SLICE_DONE` | `external-output` artifact added. |
| 906 | External output schema/docs hardening | `EF_M3_DONE` | Schema and CLI docs added; bundle manifest includes external optional output. |
| 907 | Sample35 external-output consumer evidence | `EF_M4_DONE` | Sample35 consumes optional external-output packet without native build. |
| 908 | External output checkpoint | `DONE_COMMITTED` | Optional external-output lane committed. |
| 909 | AI task packet output for external no-code | `EF_M6_FIRST_SLICE_DONE` | Confirmation-driven task packet emitted. |
| 910 | AI task packet sample dry-run | `EF_M7_DRY_RUN_DONE` | CLI dry-run verified pending confirmation and no pre-confirmation writes. |
| 911 | Output-mode config artifact | `EF_M8_FIRST_SLICE_DONE` | `mtool_no_code`, `external_no_code`, and `hybrid` mode-selection artifact added. |
| 912 | PWA readiness metadata artifact | `EF_M9_FIRST_SLICE_DONE` | PWA readiness metadata added without manifest/service-worker generation. |
| 913 | External no-code next implementation selection | `EF_M10_SELECTION_DONE` | React Native second-pass extension metadata selected. |
| 914 | React Native second-pass extension metadata | `EF_M11_DONE` | React Native extension metadata added without source/native project generation. |
| 915 | App surface config for PWA + Flutter WebView | `EF_M12_FIRST_SLICE_DONE` | Shared backend endpoint plus selectable app surfaces defined. |
| 916 | Flutter WebView wrapper extension packet | `EF_M13_DONE` | Flutter WebView wrapper input packet added. |
| 917 | Flutter WebView wrapper docs/schema hardening | `EF_M14_DONE` | User-facing guidance and schema notes added. |
| 918 | Mobile external output checkpoint | `EF_M15_DONE` | EF-M10 through EF-M14 reviewed. |
| 919 | PR preparation for mobile external output stack | `EF_M16_READY_FOR_PR` | Branch and PR text were prepared. |
| 920 | Post-merge cleanup for mobile external output stack | `EF_M17_DONE` | PR #78 merged to develop, PR #79 merged to master, feature branch removed. |
| 921 | Next product slice selection after mobile external output | `EF_M18_DONE_RSS_PROMOTED` | Room/shared-state sync with separate Node.js server promoted. |
| 922 | RSS-1 Shared state sync contract | `RSS_1_DONE` | Identity/token, room, membership, state, event, conflict, transport, validation, and non-goal boundaries defined. |
| 923 | RSS-2 Schema/API contract | `RSS_2_DONE` | Room/membership/invite/shared-state/event schema and REST endpoints defined. |
| 924 | RSS-3 Realtime event contract | `RSS_3_DONE` | WebSocket envelopes, heartbeat, reconnect/latest-fetch, SSE fallback, and polling fallback defined. |
| 925 | RSS-4 Node.js sync server input packet | `RSS_4_DONE` | `sync-server-input.json` / `SYNC-SERVER-INPUT.md` packet contract defined. |
| 926 | RSS-5 Sync server input artifact implementation decision | `RSS_5_DONE_STATIC_FIXTURE_FIRST` | Static/sample consumer fixture selected before Mtool emission. |
| 927 | RSS-6 Sync server input static consumer fixture | `RSS_6_DONE` | Sample36 validates `sync-server-input.sample.json`. |
| 928 | RSS-7 Mtool sync server input artifact emission | `RSS_7_DONE` | Mtool emits `sync-server-input.json` / `SYNC-SERVER-INPUT.md`. |
| 929 | RSS-8 Sync server input docs and dry-run hardening | `RSS_8_DONE` | CLI guidance and dry-run history added. |
| 930 | RSS-9 App client input packet contract | `RSS_9_DONE` | `sync-client-input.json` packet contract defined. |
| 931 | RSS-10 App client input implementation route selection | `RSS_10_DONE_STATIC_FIXTURE_FIRST` | Static/sample consumer fixture selected before Mtool emission. |
| 932 | RSS-11 App client input static consumer fixture | `RSS_11_DONE` | Sample37 validates `sync-client-input.sample.json`. |
| 933 | RSS-12 Mtool app client input artifact emission | `RSS_12_DONE` | Mtool emits `sync-client-input.json` / `SYNC-CLIENT-INPUT.md`. |
| 934 | RSS-13 Client input docs and dry-run hardening | `RSS_13_DONE` | CLI guidance and dry-run history added. |
| 935 | RSS-14 Shared-state sync packet stack checkpoint | `RSS_14_DONE_PR_CHECKPOINT_SELECTED` | RSS-1 through RSS-13 reviewed; PR checkpoint selected. |
| 936 | RSS-15 Shared-state sync PR checkpoint | `RSS_15_DONE` | Commit stack inspected, final focused validation passed, PR text/link prepared. |
| 937 | RSS-16 Shared-state sync PR branch preparation | `RSS_16_DONE_MERGED_TO_MASTER` | Feature branch was prepared, PR was merged, develop was semantically squashed, and master contains the result via PR #81. |

## Archived Immediate Next Sequence / archive 済み直近進行順

The Immediate Next Sequence previously listed F100, MW, NC-S, EF-M, and RSS rows as active-plan context. Their detailed work remains available in dated history files and permanent docs.

The final RSS sequence was:

| Sequence | Final status / 最終状態 |
| --- | --- |
| RSS-1 through RSS-4 | Contracts completed: shared-state, schema/API, realtime, and Node.js server input packet. |
| RSS-5 through RSS-8 | Server input static fixture, Mtool artifact emission, docs, and dry-run completed. |
| RSS-9 through RSS-13 | Client input contract, static fixture, Mtool artifact emission, docs, and dry-run completed. |
| RSS-14 through RSS-16 | Stack checkpoint, PR checkpoint, branch preparation, semantic squash, and master merge completed. |

## Validation History / 検証履歴

Focused validation was run during the shared-state sync packet stack and final semantic squash:

- PHP lint for server/client input apps and scripts.
- Sample36 and Sample37 static validators.
- Focused PHPUnit integration tests for server/client input emission.
- Final tree equivalence check after semantic squash.
- `origin/master` confirmed to contain `origin/develop` after PR #81.

## Next Product-Scope Candidates / 次候補

After this archive, `docs/current-plans.md` should expose only the next decision. Candidate lanes are:

- Shared-state sync combined bundle / manifest / validation checklist.
- Writable artifact execution UI controls with CSRF, output-dir, overwrite, audit, and failure handling.
- External consumer integration pass.
- A new sample or domain lane.

Only one should be promoted to active Main Plan when selected.

Follow-up note:

- The direct writable execution UI candidate was later reframed as an AI-assisted artifact execution packet route. Direct browser execution remains read-only until safety controls exist.
- The shared-state sync bundle / manifest / validation checklist candidate was selected first.

追記:

- 直接 write する execution UI 候補は、後続判断で AI-assisted artifact execution packet route として再整理された。直接 browser 実行は safety control が揃うまで read-only のまま。
- shared-state sync bundle / manifest / validation checklist 候補が最初に選定された。
