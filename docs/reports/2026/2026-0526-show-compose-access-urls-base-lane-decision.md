# 2026-05-26 Show Compose Access URLs Base-Lane Decision

## 要約

- `mtool/scripts/show_compose_access_urls.sh` は current では base-only default のまま維持する。
- compose stack helper (`list_compose_stack_files.sh`) へは寄せない。
- 理由は、script の責務が root web service の published port 表示に限られ、local overlay はその port を変えていないためである。

## 判断理由

- script の current caller は root `Makefile` の `up` / `up-external-config-db` / `start` だけで、sample/helper 系の compose stack resolver とは責務が違う。
- `show_compose_access_urls.sh` はもともと `--compose-file=...` を受けられるので、必要になった時だけ追加 compose を明示できる。今すぐ共通 helper に寄せる必要はない。
- `compose.local-db-config.yaml` は `db-config` service だけを足しており、`web-admin` / `web-lab` / `lab-db-ui` の published port を変えていない。
- 実際に current local stack で
  - `bash mtool/scripts/show_compose_access_urls.sh`
  - `bash mtool/scripts/show_compose_access_urls.sh --compose-file=compose.local-db-config.yaml`
    の出力差分は空だった。
- ここで helper の default local lane を流用すると、external lane でも `--lane=base` を毎回意識する必要が出て、むしろ script の責務が曖昧になる。

## current rule

- `show_compose_access_urls.sh`
  - default は base `compose.yaml`
  - 必要な時だけ `--compose-file=...` を足す
- `list_compose_stack_files.sh`
  - sample/helper 系の compose stack merge をそろえるための helper
  - access URL script の default source of truth にはしない

## 見直し条件

- `compose.local-db-config.yaml` や別 overlay が `web-admin` / `web-lab` / `lab-db-ui` の published port を上書きするようになった時
- sample pack / scenario 単位で access URL 表示を current workflow に入れた時
- `show_compose_access_urls.sh` の caller が root stack 以外へ広がった時

## 検証

```bash
bash mtool/scripts/show_compose_access_urls.sh >/tmp/show_compose_access_urls_base.txt
bash mtool/scripts/show_compose_access_urls.sh --compose-file=compose.local-db-config.yaml >/tmp/show_compose_access_urls_local.txt
diff -u /tmp/show_compose_access_urls_base.txt /tmp/show_compose_access_urls_local.txt
```

- diff result: empty
