# 2026-06-17 Config DB Backup Rotation

## Summary

Lightweight SQLite persistence plan の first slice として、current MySQL / MariaDB config DB backup を強化した。

この slice は SQLite support そのものではなく、以後の dialect / store work の安全網である。

## Changes

- `Makefile`
  - `CONFIG_DB_BACKUP_DIR` を追加した。
  - `CONFIG_DB_BACKUP_KEEP_DAYS` を追加した。
  - `CONFIG_DB_BACKUP_KEEP_COUNT` を追加した。
  - `backup-config-db` / `backup-config-db-mtool` の出力先を `CONFIG_DB_BACKUP_DIR` で変更できるようにした。
  - backup 作成時に `.manifest.json` を同時出力するようにした。
  - `backup-config-db-rotate` / `backup-config-db-mtool-rotate` を追加した。
  - restore 実行前に current state を automatic backup するようにした。
- `.env.example`
  - backup directory / retention 設定を追加した。
- `docs/common-tasks.md`
  - rotation target と restore 前 auto backup を追記した。
- `docs/quickstart.md`
  - 継続利用時の rotation target を案内した。
- `docs/reports/2026/2026-0616-design-data-persistence-report.md`
  - current implementation note に rotation と restore 前 backup を追記した。

## Current Commands

MTOOL core seed stack:

```bash
make backup-config-db-mtool
make backup-config-db-mtool-rotate
make restore-config-db-mtool BACKUP_FILE=work/backups/config-db/config_db-mtool-YYYYMMDD-HHMMSS.sql CONFIRM_RESTORE=yes
```

local default stack:

```bash
make backup-config-db
make backup-config-db-rotate
make restore-config-db BACKUP_FILE=work/backups/config-db/config_db-YYYYMMDD-HHMMSS.sql CONFIRM_RESTORE=yes
```

Retention override example:

```bash
CONFIG_DB_BACKUP_KEEP_DAYS=14 CONFIG_DB_BACKUP_KEEP_COUNT=20 make backup-config-db-rotate
```

## Notes

- rotation target always creates a fresh backup before deleting old backups.
- failed backup does not proceed to rotation.
- restore requires `CONFIRM_RESTORE=yes` and creates a fresh backup before applying `BACKUP_FILE`.
- external config DB remains outside this local container dump flow. Managed DB / vendor-native backup should be preferred there.

## Next

- Add a dialect inventory report.
- Start the SQLite output support slice with a small runtime sample.
