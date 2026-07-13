# AI schema proposal handoff guide / AI schema proposal handoff guide

Date: 2026-07-13

## Summary

#882 adds a stable guide for the AI/operator handoff around schema proposal task packets.

The guide is intentionally operational:

- where task packet files live;
- which files are authoritative;
- what confirmation wording the AI should use before writing;
- how formal candidate generation differs from optional fallback;
- when fallback can be copied/adapted into formal output;
- what validation metadata distinguishes advisory from formal output.

## Added doc

- `docs/ai-schema-proposal-handoff-guide.md`

## Updated entrances

- `docs/README.md`
- `docs/ai-task-packet-workflow.md`
- `docs/current-plans.md`

## Boundary

This remains a review-only handoff.

The guide does not enable automatic schema application, metadata import, SQL execution, build, publish, or remote paid-provider calls.

## Next

Proceed to #883: optional fallback qualification and checkpoint.
