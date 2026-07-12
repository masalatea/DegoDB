# No-Code Product Roadmap Order Clarification

## 結論

No Code productの拘束的な展開順は次の通りとする。

1. representative sample sliceによる必須No Code capabilityの網羅
2. capability matrixの完了条件達成
3. Mtool自身のcontained workflowを段階的・部分的・hybridにNo Code置き換え
4. AI structural normalizationのproduct化
5. 同じ正規化資料からQ&A・No Code UIを生成するG-L5

## 誤解を防ぐ境界

- Sample18・Sample29の認定は、sample展開全体の完了ではなく代表2 entryのfeasibility・再利用pattern実証である。
- 全sample・全screenの完全No Code化は要求しない。確認すべきcapabilityが代表sampleで網羅された時点をL1 exitとする。
- No Codeの自動生成部分とcustom codeは共存可能であり、sampleとMtoolのどちらでも部分置換を正規の構成として扱う。
- application全体の100%生成は目標にしない。Mtoolがsupportすると宣言したcontractは100%対応し、反復可能な80〜90%相当を自動化して残りを明示custom境界へ渡す。この原則はDB classとNo Codeに共通する。
- Mtool Source Output inspectionはdefault-off・read-onlyのfeasibility probeであり、Mtool全面No Code化の開始・完了ではない。
- Sample19 task packet・schema reviewはAI review境界のfeasibility evidenceであり、資料to UI product phaseの開始・完了ではない。
- 後段phaseのprobeが存在しても、前段product phaseの順序と完了条件を飛ばさない。

## 次の作業

#789で既存sample evidenceを必須capability matrixへ対応付け、`covered`・`gap`・`理由付きnot required`を判定する。gapごとに最小の代表sample slice、generated/custom境界、完了条件、見積もりを決める。全sample変換と実装はこの棚卸し単位に含めない。

Status: `DONE`
