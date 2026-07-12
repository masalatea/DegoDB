# Task Review HTTP/Browser Promotion And G-L4 Closure

Status: `DONE_QUALIFIED`

## Result

The concrete Codex-produced Sample19 task artifact passed real authenticated HTTP/browser review promotion. G-L4 is satisfied through the intended provider-neutral task-packet workflow, with no automatic mutation and Ollama remaining optional.

## Browser evidence

- unauthenticated exact task-review URL redirected to login with the full return path;
- authenticated default-off route returned not found;
- explicit feature enablement rendered the validated task artifact;
- page showed AI-authored candidate, interactive-agent provenance, task ID, verified source/candidate/review hashes, and Mtool-derived diff ownership;
- four entity/evidence rows and four independently derived unchanged diff rows rendered;
- canonical return link loaded the Sample19 Project Hub.

DOM counts:

- AI-authored marker: 1;
- task evidence section: 1;
- verified candidate hash: 1;
- verified review-artifact hash: 1;
- Mtool diff owner: 1;
- entities: 4;
- diff categories: four unchanged;
- forms/buttons/scripts/POST forms/apply links: 0.

## HTTP evidence

Apache recorded GET review -> login, POST login, GET review 200, and GET project hub 200. No POST targeted task review, validation, candidate, apply, import, metadata, or execution. The sole POST was authentication.

## Rollback

The Sample19 admin container was recreated without the feature override. `app_schema_proposal_task_review_enabled()` returned false. The stack is default-off again.

## G-L4 decision

G-L4 is qualified:

- Codex read a bounded task packet after task-specific user confirmation;
- AI produced a source-bound proposal candidate;
- a single public validation pipeline checked it;
- Mtool independently derived canonical diff;
- a distinct review artifact was authenticated and visibly reviewable;
- no automatic mutation or provider API integration was required;
- deterministic scan and Ollama remain advisory optional fallbacks.

## Verification

- Fast/full foundation: 464 tests, 14,133 assertions, 1 skipped.
- In-app browser promotion: passed.
- Review-route POST count: zero.
- Stack restored default-off.

## Next

#783 closes the task-packet/G-L4 lane, reviews documentation and UX entry points, and identifies only concrete remaining polish before selecting another roadmap lane.
