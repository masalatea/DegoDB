# 2026-0702 No-Code Docker Trial Scenario First Pass

Status: `DONE`

## Purpose

Try the current no-code flow as a first-time local Docker user and record the product-facing scenario, not just the implementation verification path.

This pass used `sample28-no-code-data-app-mvp` because it is the smallest user-facing no-code app MVP with generated list/detail/form preview screens and the public runtime delivery lane already implemented.

## Local Setup

- Started sample stack: `./sample/tutorials/sample28-no-code-data-app-mvp/run.sh up`
- Applied seed: `./sample/tutorials/sample28-no-code-data-app-mvp/run.sh apply-seed`
- Admin URL: `http://127.0.0.1:18291`
- Lab URL: `http://127.0.0.1:18292`
- Login used for the local stub admin:
  - user: `admin-local`
  - password: `change-this-admin-password`

## Trial Scenario

1. Open admin site and log in.
2. Open `Projects`.
3. Select `SAMPLE28` / `Sample 28 No-Code Data App MVP`.
4. Open `Source Output`.
5. Inspect `NO-CODE-RUNTIME`.
6. Confirm publish readiness is `publishable`.
7. Create a publish candidate.
8. Request review.
9. Approve the candidate.
10. Set the approved candidate as current public revision.
11. Set public alias `stable`.
12. Open the public runtime preview.

## Result

The trial reached a usable public no-code preview.

- Candidate revision: `20260702-123500-72418739fef74248`
- Artifact key: `20260702-060926-918d4267`
- Artifact-key public preview: `http://127.0.0.1:18291/runs/no-code/SAMPLE28/20260702-060926-918d4267/runtime-preview.html`
- Current public preview: `http://127.0.0.1:18291/runs/no-code/SAMPLE28/current/runtime-preview.html`
- Stable alias public preview: `http://127.0.0.1:18291/runs/no-code/SAMPLE28/alias/stable/runtime-preview.html`

All three public preview routes returned `200` after approval and selection. The browser was left on the current public preview.

The preview page title is `No-Code Runtime Preview`, and it shows three generated screens:

- `No Code Ticket List`
- `No Code Ticket Detail`
- `No Code Ticket Form`

## User-Scenario Observations

- The core Docker trial is viable now: a user can start the sample, log in, approve a generated no-code runtime, and view a public preview.
- The shortest understandable path is through `Projects` -> `SAMPLE28` -> `Source Output` -> `NO-CODE-RUNTIME` -> publish candidate workflow.
- `Source Output Artifact` detail is read-only and offers download/manifest inspection, but it does not directly lead a first-time user to the preview. The preview link appears naturally after candidate approval on `NO-CODE-RUNTIME` detail.
- The public delivery route is product-shaped: artifact-key, current, and alias routes are all available after approval.
- The alias URL shape is singular: `/runs/no-code/SAMPLE28/alias/stable/runtime-preview.html`.
- The Delivery Overview on the Source Outputs page correctly marks public runtime as ready, but `app-local package` is blocked in sample28 because the App-local package Source Output definition is missing. For a pure web no-code trial this is acceptable; for an app-local trial, sample30 or a package-enabled sample is a better next scenario.

## Product Notes

For an external user, this wants a short "try no-code locally" guide or UI affordance that makes the first path obvious:

- which sample to start;
- which login credentials to use;
- where to click after login;
- why publish candidate approval is required before public preview;
- which preview URL to open first;
- how to distinguish web public runtime from app-local package readiness.

This is not a code blocker. It is a documentation/onboarding gap exposed by using the feature as a user.

## Verification

- Docker containers for sample28 were running on ports `18291`, `18292`, `43291`, and `43292`.
- Public preview HTTP checks returned:
  - artifact-key route: `200`
  - current route: `200`
  - alias route: `200`
- Browser inspection of the current preview confirmed the generated `SAMPLE28` page and three no-code screens.

No push was performed as part of this trial report.
