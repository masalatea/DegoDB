# archive

- 役割: current catalog 外へ外した sample directory や historical leftover の archive 置き場
- ここにある path は current sample pack catalog / current test entry / current helper default からは参照しない
- 後で戻す必要が出た場合だけ、active category (`sample/tutorials/`、`sample/internal-patterns/`、`sample/legacy-projects/`) へ戻し、catalog / tests / docs を更新する

運用ルール:

- 単なる空 directory を増やさない
- current active sample と紛らわしい名前を残さない
- archive のまま参照だけ残す場合でも、実ツールや sample test の load target に戻さない
