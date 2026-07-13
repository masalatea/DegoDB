# Configurable Ollama local adapter / 設定可能Ollama local adapter

Date: 2026-07-13

## Summary

#880 removes the last hard-coded Sample19-only Ollama transport details from the local fallback wrapper.

This is still only a fallback path. Codex/Claude-style agent review remains the primary path. The Ollama adapter is local-only, credential-free, explicitly executed, and testable without running Ollama.

## Added boundary

- `mtool/app/task_packet_ollama_adapter.php`
  - defines `APP_TASK_PACKET_OLLAMA_ADAPTER_VERSION`;
  - normalizes endpoint/model/timeout/context/temperature;
  - requires `http://127.0.0.1`, `http://localhost`, or `http://[::1]` style endpoints;
  - rejects embedded credentials and credential-like config keys;
  - builds the Ollama `/api/generate` JSON payload;
  - accepts a transport callback for fake-provider tests;
  - returns advisory candidate metadata without mutation authority.

## Sample19 wrapper change

`mtool/scripts/run_sample19_local_ai_proposal.php` now keeps the Sample19 prompt-building responsibility but delegates Ollama generation to the generic adapter.

Supported explicit options:

- `--ollama-endpoint=...`
- `--ollama-model=...`
- `--timeout-seconds=...`
- `--num-ctx=...`
- `--temperature=...`

The command still refuses to run unless `--execute-local-fallback` is present.

## Normal-test policy

Normal tests do not require a local Ollama daemon, network call, model download, or paid provider credential.

The adapter is tested with fake transport, so the contract covers:

- config defaults;
- local-only endpoint validation;
- credential rejection;
- payload shape;
- empty provider response failure;
- no mutation.

## Remaining work

Proceed to #881: shared validation and advisory artifact integration.

The remaining issue is not how to call Ollama. It is how to present Codex/Claude/Ollama candidates through one validation/review pipeline while keeping fallback artifacts advisory and separate from accepted output.
