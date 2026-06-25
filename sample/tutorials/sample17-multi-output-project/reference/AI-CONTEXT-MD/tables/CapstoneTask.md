# Table: CapstoneTask

- Physical name: `CapstoneTask`
- Generated name: `CapstoneTask`
- Column count: `7`

## Columns

| Column | Physical Name | Generated Name | Type | Null | Key | Default | Extra | Memo |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Id | Id | id | bigint(20) unsigned | NO | PRI | unknown | auto_increment | unknown |
| Title | Title | title | varchar(255) | NO | unknown | unknown | unknown | unknown |
| Status | Status | status | varchar(32) | NO | MUL | 'open' | unknown | unknown |
| OwnerName | OwnerName | ownerName | varchar(100) | NO | unknown | '' | unknown | unknown |
| Priority | Priority | priority | int(11) | NO | unknown | 0 | unknown | unknown |
| DueDate | DueDate | dueDate | date | YES | unknown | NULL | unknown | unknown |
| UpdatedAt | UpdatedAt | updatedAt | datetime | NO | unknown | unknown | unknown | unknown |
