# mtool-single-proxy

- 対象 project: `MTOOL`
- 役割: `Project 1` 上で `SAMPLE-SINGLE-PROXY-SERVER` / `SAMPLE-SINGLE-PROXY-CLIENT` を追加し、single-function proxy generator を補助検証する
- 位置づけ: `Project 2+` sample pack ではなく、`Project 1` の検証 scenario
- durable input: `seed/`
- disposable output: `work/scenarios/mtool-single-proxy/`

起動:

```bash
./tests/scenarios/mtool-single-proxy/run.sh up
```

停止して volume も消す:

```bash
./tests/scenarios/mtool-single-proxy/run.sh reset
```

起動済み環境へ seed だけ流したい場合:

```bash
./tests/scenarios/mtool-single-proxy/run.sh apply-seed
```
