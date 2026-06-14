# 2026-05-28 Close docs normalization progress

## 要約

- `Close. verification / docs / status freeze` に着手し、`README.md`、`docs/*.md`、`docs/internal/*.md` の permanent docs 全 `27` file を `English companion:` 付きの冒頭構成へ揃えた。
- `docs/README.md` と `docs/internal/README.md` に、`恒久 docs は日本語本文 + 英語 companion`、`docs/reports/` は progress / handoff を日本語のみで運用する、という rule と、`top-level docs は外部ユーザ向け / internal docs は 1 段内側` という整理を明文化した。
- docker-based verification はこの slice では完了していない。`DocsEntranceContractTest` 再実行時に `exec /usr/bin/phpunit: input/output error` が出て、その後の `docker compose exec/logs/restart` は host 側で別 shell の `docker system prune` が走っている間は hang した。
- follow-up の verification completion / status freeze は `docs/reports/2026/2026-0528-close-verification-status-freeze.md` に記録した。

## 今回の docs 変更

- 冒頭の英語 companion を追加:
  - `README.md`
  - `docs/*.md` / `docs/internal/*.md` の permanent docs 全件
- docs contract を補強:
  - `tests/Integration/DocsEntranceContractTest.php`
  - permanent docs 全件の `English companion:` と、`docs/README.md` / `docs/internal/README.md` の言語運用 rule を assert する test を追加
- rule を明文化:
  - `docs/README.md`
  - `docs/internal/README.md`
- 既存の日本語本文、section anchor、link 構造は維持したまま、冒頭だけを日英併記に寄せた。

## local check

- `rg -c "English companion:" README.md docs/*.md docs/internal/*.md`
  - permanent docs 全 `27` file が `1` 件ずつ `English companion:` を持つことを確認した。

## verification attempt

- 着手コマンド:
  - `docker compose -f compose.yaml -f compose.local-db-config.yaml exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/DocsEntranceContractTest.php`
- 結果:
  - `exec /usr/bin/phpunit: input/output error`
- follow-up:
  - `docker compose ... exec -T web-admin php -v`
  - `docker compose ... exec -T web-admin ls /usr/bin/phpunit`
  - `docker compose ... logs --tail=80 web-admin`
  - `docker compose ... restart web-admin web-lab db-config db-lab`
  - いずれも hang
- host observation:
  - `ps -o pid,ppid,stat,command -ax` で別 shell の `docker system prune` が並行実行中だった
  - この slice では user 側の docker maintenance に干渉せず、verification は再試行待ちに留めた

## この slice の境界

- docs の日英併記 normalization 自体はこの slice で完了した。
- ただしこの時点では verification / status freeze は未完で、docs contract の再実行と self-loop / full suite の最終確認は follow-up に送った。
- したがってこの文書は `Close` の docs normalization slice だけを表し、最終的な close 判定は `2026-0528-close-verification-status-freeze.md` を正本として読む。
