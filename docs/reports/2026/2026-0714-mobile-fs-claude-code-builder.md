# Mobile FS: Claude-style code-builder handoff

Date: 2026-07-14

## Summary / summary

Layer: Layer B, AI/code-builder consumers.

Target: Claude-style code-builder handoff.

Study key: `claude-code-builder`.

Recommendation: continue as a provider-neutrality check.

Blocker severity:

- low for provider-neutral task packet design;
- medium for tool-specific execution.

The Codex-style findings mostly generalize to Claude-style and other AI/code-builder consumers. Therefore, Mtool should not make the AI task packet Codex-specific. The durable Mtool artifact should be provider-neutral, with optional companion notes for each execution surface.

## Provider-neutral core / provider-neutral core

The core AI/code-builder packet should define:

- source artifacts;
- implementation facts;
- allowed actions;
- confirmation-required actions;
- forbidden guesses;
- questions to ask;
- validation commands;
- ownership boundary;
- non-goals.

This shape can serve Codex, Claude, or another AI-assisted builder.

## Tool-specific companion notes / tool-specific companion notes

Tool-specific notes may still be useful, but they should not be the contract.

Examples:

- Codex-specific local workspace and test-running guidance;
- Claude-specific prompt packaging guidance;
- provider-specific context-size or file-reading notes;
- provider-specific command execution limitations.

These should be generated from the provider-neutral packet when possible.

## Missing or weak metadata / 不足・弱いmetadata

The same gaps identified in the Codex-style study apply:

1. explicit "ask the user before" list;
2. explicit "do not guess" list;
3. explicit "safe to infer" list;
4. target framework selection prompt;
5. output directory and overwrite policy;
6. dependency-install permission policy;
7. project creation policy;
8. validation command list by target and risk level.

## Recommendation / recommendation

Continue as a provider-neutrality check.

The second pass should define a provider-neutral `mobile-ai-code-builder-task-v1` style contract first. Codex and Claude can then receive companion instructions derived from that contract.
