import fs from 'node:fs';
import path from 'node:path';
import { SharedStateChatDemo } from '../src/shared-state-chat.mjs';

const sampleRoot = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const repoRoot = path.resolve(sampleRoot, '../../..');

function assert(condition, message) {
  if (!condition) {
    throw new Error(message);
  }
}

function readJson(absolutePath) {
  return JSON.parse(fs.readFileSync(absolutePath, 'utf8'));
}

for (const file of [
  'README.md',
  'src/shared-state-chat.mjs',
  'scripts/validate-sample.mjs'
]) {
  assert(fs.existsSync(path.join(sampleRoot, file)), `Missing required file: ${file}`);
}

assert(!fs.existsSync(path.join(sampleRoot, 'package.json')), 'sample39 must not require package.json');
assert(!fs.existsSync(path.join(sampleRoot, 'node_modules')), 'sample39 must not include node_modules');

const serverPacket = readJson(path.join(repoRoot, 'sample/tutorials/sample36-shared-state-sync-server-input/reference/sync-server-input.sample.json'));
const clientPacket = readJson(path.join(repoRoot, 'sample/tutorials/sample37-shared-state-sync-client-input/reference/sync-client-input.sample.json'));

const chat = new SharedStateChatDemo({ serverPacket, clientPacket });
chat.createChatRoom({
  roomId: 'chat-alpha',
  members: {
    owner_1: 'owner',
    editor_1: 'editor',
    viewer_1: 'viewer'
  },
  initialMessages: [
    { id: 'msg-0', user_id: 'owner_1', text: 'welcome' }
  ]
});
chat.createChatRoom({
  roomId: 'chat-beta',
  members: {
    editor_2: 'editor'
  }
});

const ownerEvents = [];
const viewerEvents = [];
const betaEvents = [];

assert(chat.subscribe({ roomId: 'chat-alpha', userId: 'owner_1', onMessageState: event => ownerEvents.push(event) }).ok === true, 'owner subscribes');
assert(chat.subscribe({ roomId: 'chat-alpha', userId: 'viewer_1', onMessageState: event => viewerEvents.push(event) }).ok === true, 'viewer subscribes');
assert(chat.subscribe({ roomId: 'chat-beta', userId: 'editor_2', onMessageState: event => betaEvents.push(event) }).ok === true, 'other room subscribes');

const initial = chat.listMessages({ roomId: 'chat-alpha', userId: 'viewer_1' });
assert(initial.ok === true, 'viewer can list messages');
assert(initial.revision === 1, 'initial message state revision is 1');
assert(initial.messages.length === 1, 'initial message exists');

const outsiderList = chat.listMessages({ roomId: 'chat-alpha', userId: 'outsider' });
assert(outsiderList.ok === false && outsiderList.error === 'membership_required', 'outsider cannot list messages');

const emptyMessage = chat.appendMessage({
  roomId: 'chat-alpha',
  userId: 'editor_1',
  expectedRevision: 1,
  messageId: 'msg-empty',
  text: '   '
});
assert(emptyMessage.ok === false && emptyMessage.error === 'message_text_required', 'empty message is rejected');

const append = chat.appendMessage({
  roomId: 'chat-alpha',
  userId: 'editor_1',
  expectedRevision: 1,
  messageId: 'msg-1',
  text: 'hello from editor'
});
assert(append.ok === true, 'editor can append message');
assert(append.revision === 2, 'append increments message revision');
assert(append.messages.length === 2, 'message list grows');
assert(append.messages[1].text === 'hello from editor', 'message text is stored');
assert(ownerEvents.length === 1, 'owner receives chat update');
assert(viewerEvents.length === 1, 'viewer receives chat update');
assert(betaEvents.length === 0, 'other room receives no chat update');
assert(ownerEvents[0].state_key === 'messages', 'chat event updates messages state');
assert(ownerEvents[0].body.messages.length === 2, 'chat event contains new message list');

const staleAppend = chat.appendMessage({
  roomId: 'chat-alpha',
  userId: 'editor_1',
  expectedRevision: 1,
  messageId: 'msg-stale',
  text: 'stale'
});
assert(staleAppend.ok === false && staleAppend.error === 'stale_revision', 'stale append is rejected');
assert(staleAppend.latest.revision === 2, 'stale append returns latest revision');

const nonMemberAppend = chat.appendMessage({
  roomId: 'chat-alpha',
  userId: 'outsider',
  expectedRevision: 2,
  messageId: 'msg-outsider',
  text: 'outsider'
});
assert(nonMemberAppend.ok === false && nonMemberAppend.error === 'membership_required', 'non-member cannot append');

const viewerAppend = chat.appendMessage({
  roomId: 'chat-alpha',
  userId: 'viewer_1',
  expectedRevision: 2,
  messageId: 'msg-viewer',
  text: 'viewer'
});
assert(viewerAppend.ok === false && viewerAppend.error === 'update_forbidden', 'viewer cannot append');

const latest = chat.latestRevision({ roomId: 'chat-alpha', userId: 'viewer_1' });
assert(latest.ok === true && latest.revision === 2, 'viewer can read latest revision');
assert(chat.hasForbiddenEventPayloads() === false, 'chat events contain no forbidden secret payloads');
assert(!JSON.stringify(ownerEvents).includes('sso_token'), 'chat events contain no SSO token marker');
assert(!JSON.stringify(ownerEvents).includes('refresh_token'), 'chat events contain no refresh token marker');
assert(!JSON.stringify(ownerEvents).includes('raw_invite_token'), 'chat events contain no raw invite token marker');
assert(!JSON.stringify(ownerEvents).includes('secret'), 'chat events contain no secret marker');

console.log(JSON.stringify({
  ok: true,
  sample: 'sample39-shared-state-chat-demo',
  base_runtime: 'sample38-shared-state-sync-node-runtime',
  messages_after_append: append.messages.length,
  accepted_revision: append.revision,
  same_room_events: ownerEvents.length + viewerEvents.length,
  cross_room_events: betaEvents.length,
  dependency_free: true,
  production_server_generated: false
}, null, 2));
