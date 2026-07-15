# 2026-0716 Whiteboard Room Sync Plan

## Summary

After `sample41-simple-whiteboard`, the next slice is a room-based whiteboard communication sample.

The room behavior should reuse the temporary room idea from `sample40-ephemeral-room-chat-site`, but the retention rule must differ from chat.

## Retention decision

Chat can expire individual messages after 24 hours because message deletion does not corrupt the rest of the conversation.

Whiteboard operations should not expire individually:

- expiring one stroke or text operation can make the drawing visually nonsensical;
- replaying only the remaining operations can produce a broken board;
- partial TTL makes the whiteboard harder for users to reason about.

Therefore the whiteboard rule is:

- no per-stroke / per-text 24-hour TTL;
- board operations remain together while the room is active;
- if the room has no activity for 7 days, clear the whole board;
- keep the room name / URL registry;
- reopening the same URL after cleanup recreates an empty board.

## First room-sync target

The first room-sync slice should validate:

- URL-named rooms;
- automatic room creation on access;
- room registry preservation;
- board operation append;
- latest board fetch;
- revision or sequence conflict handling;
- 7-day inactive board clear;
- same URL reopening as an empty board after clear;
- no individual operation TTL.

## Boundary

This slice should not silently become a production collaborative whiteboard.

Still out of scope:

- public deployment;
- SSO/auth;
- moderation;
- binary image upload;
- multi-node sync;
- Redis/pubsub;
- guaranteed offline replay;
- native app packaging.
