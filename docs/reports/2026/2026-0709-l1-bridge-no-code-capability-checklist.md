# L1 Bridge No-Code Capability Checklist

English companion:
This report records #545, the minimum capability checklist for converting `sample18-mini-task-board-demo` into the first existing sample UI no-code target.

## Summary

- Status: `DONE`
- Date: 2026-07-09
- Target: `sample18-mini-task-board-demo`
- Push: not performed.

## Existing Sample18 Surface

- Existing lab route: `/samples/sample18-task-board`
- Stable seed table: `task_card`
- Current user-facing flow: status filter, create, edit/update, complete, reopen, delete
- Existing outer verification: `make sample18-pack-runtime-test` and `make sample18-http-runtime-smoke`
- Existing reference artifact: `HTML-PAGE` static sample page

## Minimum Capability Checklist

| Area | Required before conversion proceeds |
| --- | --- |
| Data shape | Represent `id`, `title`, `body`, `status`, `assigned_to`, `priority`, `due_date`, `completed_at`, and `updated_at` with key, required, readonly, client-write, number, and date metadata. |
| List screen | Render task title/body, status, assignee, priority, due date, row identity, and status filter boundary as stable JSON and DOM markers. |
| Detail screen | Render row identity and readonly field display without requiring mutation. |
| Form screen | Render create/update input fields, required markers, readonly identity, number/date metadata, and local disabled/dry-run submit affordances. |
| Actions | Describe create, update, complete, reopen, and delete as route-boundary-aware disabled or dry-run operations with unavailable reasons and `generated_button_enabled=false`. |
| Fast tests | Add PHPUnit JSON and `DOMDocument` contract assertions before relying on headless Chrome. |
| Golden fixture | Freeze existing sample18 seed rows and route DOM markers before comparing no-code output. |
| Outer smoke | Keep `make sample18-http-runtime-smoke` as representative confidence, not the inner loop. |

## Boundary

- No sample18 generated UI replaces the existing lab route in this slice.
- No generated button execution or mutation route is enabled.
- No new JavaScript DOM test dependency is added.
- Browser smoke remains parked until the fast contract shape exists.

## Plan Impact

- #546 becomes `ACTIVE_NEXT`: freeze a sample18 golden fixture with stable route and seed expectations.
- #552 remains the fast contract harness code slice and should use the sample18 checklist as one input.

## Verification

- `git diff --check`
