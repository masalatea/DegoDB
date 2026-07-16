# Sample38-47 Current Plan History / sample38-47 計画履歴

This dated report preserves completed main-plan rows #952-#968 that were removed from the active index on 2026-07-17. The current source of truth for active work remains `docs/current-plans.md`.

この日付付き report は、2026-07-17 に active index から移した完了済み Main Plan #952-#968 を保存します。現在の active work の正本は引き続き `docs/current-plans.md` です。

## Completed sequence / 完了済み sequence

| Order | Work unit / 作業の塊 | Status | Completion boundary / 完了境界 |
| --- | --- | --- | --- |
| 952-954 | Shared-state sync runtime, HTTP/SSE fallback, and Mtool artifact linkage | `FIRST_SLICE_DONE` | `sample38` consumes sample36/37 and CLI-emitted packets and validates membership, revision conflict, room-scoped events, latest fetch, and loopback HTTP/SSE without becoming a production server. |
| 955-956 | Shared-state chat domain and image attachment metadata | `FIRST_SLICE_DONE` | `sample39` validates chat append, room isolation, conflict handling, secret-free events, and ephemeral image bytes outside shared state. |
| 957-958 | Ephemeral room chat site and next-slice decision | `FIRST_SLICE_DONE` | `sample40` validates URL rooms, 24-hour message expiry, 7-day inactive room expiry, SQLite default storage, loopback HTTP routes, and documented production boundaries; later work moved to the next sample lane. |
| 959-962 | Simple whiteboard, room sync, and hardening checkpoint | `FIRST_SLICE_DONE` | `sample41` validates drawing operations, room recreation, revision conflict, SSE updates, inactive-board clearing, and a production-hardening checklist; the next-sample option was selected. |
| 963 | Room shooter game | `FIRST_SLICE_DONE` | `sample42` validates a two-player shared-state game contract, room-scoped SSE, direct runtime behavior, and Mtool artifact linkage. |
| 964 | Tank survival game | `FIRST_SLICE_DONE` | `sample43` validates multiplayer join, movement, obstacles, bullets, HP/explosion, winner state, inactive reset, and Mtool artifact linkage. |
| 965 | Raycast FPS line demo | `FIRST_SLICE_DONE` | `sample44` validates line-only raycasting, turning, collision, shooting, defeat/winner state, inactive reset, and Mtool artifact linkage. |
| 966 | Map info popup site | `FIRST_SLICE_DONE` | `sample45` validates key-free structured location data, markers/list popups, filters, and Mtool handoff boundaries without remote map services. |
| 967 | Choice adventure game | `FIRST_SLICE_DONE` | `sample46` validates structured scenarios, CSS flipbook scenes, keyboard/pointer choices, mock API transitions, goal/game-over paths, and restart/back behavior. |
| 968 | Open world RPG demo | `FIRST_SLICE_DONE` | `sample47` validates URL rooms, multiple players, PvP-disabled movement/combat, enemies, rewards, idle regen, collision, room-scoped SSE, and Mtool artifact linkage. |

## Continuing boundary / 継続境界

These samples are verified reference/demo slices, not production applications. Authentication, moderation, anti-cheat, production persistence, public deployment, scale, real WebSocket infrastructure, and operational ownership remain explicit new scope unless a sample README says otherwise.

これらは検証済み reference/demo slice であり、production application ではありません。認証、moderation、anti-cheat、production persistence、public deployment、scale、real WebSocket infrastructure、運用 ownership は、各 sample README に別記がない限り明示的な新規 scope です。
