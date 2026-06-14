# 2026-05-21 Legacy Project Pack Slug Alignment

## 概要

- `sample/legacy-projects/` の project seed contract を見直し、runtime pack の `projects.slug` は pack directory 名と一致させる方針へ揃えた。
- `sample4-whiteboard-reference` だけ旧 slug の取りこぼしが残っていたため、seed を current pack 名へ修正した。
- これに合わせて `LegacyProjectSampleCatalogTest` でも slug contract を固定した。

## 変更内容

- `sample/legacy-projects/sample4-whiteboard-reference/seed/900_010_sample4_project_seed.sql`
  - `projects.slug` を `sample4-whiteboard` から `sample4-whiteboard-reference` へ修正した。
- `tests/Integration/LegacyProjectSampleCatalogTest.php`
  - `sample2-8` の `projects.slug` が pack 名と一致することを検証する assertion を追加した。

## 背景

- current category split では、runtime pack の disposable root、compose override、README path、catalog path がすべて pack 名を基準に揃っている。
- その中で `sample4` だけ `projects.slug` が古い `sample4-whiteboard` のまま残っており、project-shaped pack metadata と path naming の間に不要な例外があった。
- これは `sample/legacy-projects/` の contract を増やしているだけで利点がないため、pack 名に揃える方が自然だった。

## 検証

- `docker compose -f compose.yaml -f sample/patterns/sample1-simple-table/compose.yaml exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/LegacyProjectSampleCatalogTest.php`
  - pass

## 補足

- これは sample metadata の整流であり、runtime generator や promoted runtime reference の変更は含まない。
