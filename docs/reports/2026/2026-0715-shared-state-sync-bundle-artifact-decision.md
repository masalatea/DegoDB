# 2026-0715 Shared-State Sync Bundle Artifact Decision

## 目的

941 として、Mtool が combined `shared-state-sync-bundle.json` のような artifact を今すぐ出すべきか判断した。

## 判断

現 scope では、combined JSON artifact はまだ実装しない。

`docs/shared-state-sync-bundle-manifest.md` と `docs/shared-state-sync-validation-checklist.md` で十分に区切りがよい。

## 理由

- server input packet と client input packet は既に個別 CLI で出せる。
- それぞれの packet には stable `bundle_manifest_key` がある。
- sample36 / sample37 で packet consumability を確認できる。
- combined artifact を追加すると、現時点では「production runtime や SDK も束ねて生成する」と誤解されやすい。
- まず外部 consumer 連携の milestone を見極めた方が、combined manifest に本当に必要な field が分かる。

## 後で実装してよい条件

次のいずれかが具体化したら、combined artifact を再検討する。

- 外部 consumer が server/client packet を一括 discovery する必要がある。
- AI-assisted execution packet が combined manifest を入力にした方が安全になる。
- sample / validation runner が server/client packet をまとめて検証する必要がある。
- external consumer integration lane で共通 manifest field が明確になる。

## 結論

939〜941 の RSS bundle/checklist lane は、docs-level manifest + validation checklist + no combined artifact decision で一区切りとする。

次は 942 として、外部 consumer 連携 milestone の見極めに進む。

## 状態

`DONE_DOCS_ONLY_SUFFICIENT_FOR_NOW`
