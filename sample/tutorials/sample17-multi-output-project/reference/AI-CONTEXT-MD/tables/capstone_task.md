# Table: capstone_task

- Physical name: `capstone_task`
- Generated name: `CapstoneTask`
- Column count: `7`

## Columns

| Column | Physical Name | Generated Name | Type | Null | Key | Default | Extra | Memo |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| id | id | id | bigint(20) unsigned | NO | PRI | unknown | auto_increment | unknown |
| title | title | title | varchar(255) | NO | unknown | unknown | unknown | unknown |
| status | status | status | varchar(32) | NO | MUL | 'open' | unknown | unknown |
| owner_name | owner_name | ownerName | varchar(100) | NO | unknown | '' | unknown | unknown |
| priority | priority | priority | int(11) | NO | unknown | 0 | unknown | unknown |
| due_date | due_date | dueDate | date | YES | unknown | NULL | unknown | unknown |
| updated_at | updated_at | updatedAt | datetime | NO | unknown | unknown | unknown | unknown |
