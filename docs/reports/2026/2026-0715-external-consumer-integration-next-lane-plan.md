# 2026-0715 External Consumer Integration Next Lane Plan

## Purpose / 目的

This history note records the decision to continue external consumer / external tool integration after the shared-state sync bundle / manifest / validation checklist lane reaches a clean stopping point.

この履歴は、shared-state sync bundle / manifest / validation checklist lane が区切りまで進んだ後、外部 consumer / 外部 tool 連携を続ける判断を記録する。

## Decision / 判断

The next external integration work should start with boundary selection, not implementation.

次の外部連携作業は、実装からではなく境界選定から始める。

The reason is that "external tool integration" can mean many different layers:

- React/Web + Capacitor handoff;
- PWA readiness;
- Flutter WebView wrapper handoff;
- React Native metadata / handoff;
- Codex / Claude / AI-assisted code-builder task packets;
- another consumer that reads Mtool packets and artifacts.

「外部 tool 連携」は複数 layer を意味し得る。

- React/Web + Capacitor handoff;
- PWA readiness;
- Flutter WebView wrapper handoff;
- React Native metadata / handoff;
- Codex / Claude / AI-assisted code-builder task packet;
- Mtool packet / artifact を読む別 consumer。

## Good Stopping Point / 区切りのよい地点

The first good stopping point is not a full app or full framework migration.

最初の区切りは、full app や full framework 移行ではない。

The preferred milestone is one of:

1. a selected external consumer and a written boundary;
2. a gap inventory for that consumer;
3. a minimal handoff packet / manifest update;
4. a sample consumer proof that reads the packet without native build or dependency installation;
5. a validation checklist that tells users and AI what is ready.

望ましい milestone は次のいずれか。

1. 外部 consumer の選定と境界文書;
2. その consumer に必要な gap 棚卸し;
3. 最小 handoff packet / manifest 更新;
4. native build や dependency install なしで packet を読む sample consumer proof;
5. 利用者と AI が ready 判定できる validation checklist。

## Non-goals / 非scope

Unless explicitly promoted later, this lane should not silently take ownership of:

- native project initialization;
- dependency installation;
- signing;
- store submission;
- production app generation;
- full external framework migration;
- user source overwrite;
- undeclared network calls.

後で明示昇格しない限り、この lane は次を暗黙に所有しない。

- native project initialization;
- dependency installation;
- signing;
- store submission;
- production app generation;
- full external framework migration;
- user source overwrite;
- 未宣言 network call。

## Current Plan Update / current plan 更新

`docs/current-plans.md` now adds:

- #942 External consumer integration milestone selection;
- #943 External consumer handoff gap inventory;
- #944 External consumer first bounded slice.

These follow #939-#941, because the RSS bundle/checklist work is the immediate active lane.

## Status / 状態

`PLANNED_AFTER_RSS_BUNDLE`
