import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { EphemeralImageStore } from '../src/ephemeral-image-store.mjs';
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
  'src/ephemeral-image-store.mjs',
  'src/shared-state-chat.mjs',
  'scripts/validate-sample.mjs'
]) {
  assert(fs.existsSync(path.join(sampleRoot, file)), `Missing required file: ${file}`);
}

assert(!fs.existsSync(path.join(sampleRoot, 'package.json')), 'sample39 must not require package.json');
assert(!fs.existsSync(path.join(sampleRoot, 'node_modules')), 'sample39 must not include node_modules');

const serverPacket = readJson(path.join(repoRoot, 'sample/tutorials/sample36-shared-state-sync-server-input/reference/sync-server-input.sample.json'));
const clientPacket = readJson(path.join(repoRoot, 'sample/tutorials/sample37-shared-state-sync-client-input/reference/sync-client-input.sample.json'));

const imageStoreRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'sample39-chat-images-'));
const imageStore = new EphemeralImageStore({ rootDir: imageStoreRoot, maxBytes: 1024 });
process.on('exit', () => {
  fs.rmSync(imageStoreRoot, { recursive: true, force: true });
});
const chat = new SharedStateChatDemo({ serverPacket, clientPacket, imageStore });
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

const storedImage = chat.storeImageAttachment({
  attachmentId: 'img-1',
  fileName: 'hello.png',
  mimeType: 'image/png',
  bytes: Buffer.from([0x89, 0x50, 0x4e, 0x47]),
  width: 16,
  height: 16
});
assert(storedImage.ok === true, 'image attachment is stored in ephemeral store');
assert(fs.existsSync(storedImage.absolute_path), 'stored image exists on disk');
assert(imageStore.readImage(storedImage.attachment.storage_key).length === 4, 'stored image can be read back');

const imageAppend = chat.appendMessage({
  roomId: 'chat-alpha',
  userId: 'editor_1',
  expectedRevision: 2,
  messageId: 'msg-2',
  text: 'image attached',
  attachments: [storedImage.attachment]
});
assert(imageAppend.ok === true, 'editor can append image attachment message');
assert(imageAppend.revision === 3, 'image attachment append increments revision');
assert(imageAppend.messages[2].attachments.length === 1, 'message contains one attachment metadata record');
assert(imageAppend.messages[2].attachments[0].storage_key === storedImage.attachment.storage_key, 'message stores image storage key');
assert(!('bytes' in imageAppend.messages[2].attachments[0]), 'message metadata must not contain raw image bytes');
assert(ownerEvents.length === 2, 'owner receives image attachment chat update');
assert(viewerEvents.length === 2, 'viewer receives image attachment chat update');
assert(betaEvents.length === 0, 'other room still receives no chat update');

const unsupportedImage = chat.storeImageAttachment({
  attachmentId: 'img-bad',
  fileName: 'bad.svg',
  mimeType: 'image/svg+xml',
  bytes: Buffer.from('<svg></svg>'),
  width: 16,
  height: 16
});
assert(unsupportedImage.ok === false && unsupportedImage.error === 'unsupported_image_mime_type', 'unsupported image mime type is rejected');

const oversizedImage = chat.storeImageAttachment({
  attachmentId: 'img-large',
  fileName: 'large.png',
  mimeType: 'image/png',
  bytes: Buffer.alloc(1025),
  width: 100,
  height: 100
});
assert(oversizedImage.ok === false && oversizedImage.error === 'image_too_large', 'oversized image is rejected');

const staleAppend = chat.appendMessage({
  roomId: 'chat-alpha',
  userId: 'editor_1',
  expectedRevision: 2,
  messageId: 'msg-stale',
  text: 'stale'
});
assert(staleAppend.ok === false && staleAppend.error === 'stale_revision', 'stale append is rejected');
assert(staleAppend.latest.revision === 3, 'stale append returns latest revision');

const nonMemberAppend = chat.appendMessage({
  roomId: 'chat-alpha',
  userId: 'outsider',
  expectedRevision: 3,
  messageId: 'msg-outsider',
  text: 'outsider'
});
assert(nonMemberAppend.ok === false && nonMemberAppend.error === 'membership_required', 'non-member cannot append');

const viewerAppend = chat.appendMessage({
  roomId: 'chat-alpha',
  userId: 'viewer_1',
  expectedRevision: 3,
  messageId: 'msg-viewer',
  text: 'viewer'
});
assert(viewerAppend.ok === false && viewerAppend.error === 'update_forbidden', 'viewer cannot append');

const latest = chat.latestRevision({ roomId: 'chat-alpha', userId: 'viewer_1' });
assert(latest.ok === true && latest.revision === 3, 'viewer can read latest revision');
assert(chat.hasForbiddenEventPayloads() === false, 'chat events contain no forbidden secret payloads');
assert(!JSON.stringify(ownerEvents).includes('sso_token'), 'chat events contain no SSO token marker');
assert(!JSON.stringify(ownerEvents).includes('refresh_token'), 'chat events contain no refresh token marker');
assert(!JSON.stringify(ownerEvents).includes('raw_invite_token'), 'chat events contain no raw invite token marker');
assert(!JSON.stringify(ownerEvents).includes('secret'), 'chat events contain no secret marker');
assert(!JSON.stringify(ownerEvents).includes('89504e47'), 'chat events do not contain raw PNG bytes as hex text');
imageStore.removeAll();
assert(!fs.existsSync(imageStoreRoot), 'ephemeral image store is removed after validation');

console.log(JSON.stringify({
  ok: true,
  sample: 'sample39-shared-state-chat-demo',
  base_runtime: 'sample38-shared-state-sync-node-runtime',
  messages_after_append: imageAppend.messages.length,
  accepted_revision: imageAppend.revision,
  image_attachments: imageAppend.messages[2].attachments.length,
  same_room_events: ownerEvents.length + viewerEvents.length,
  cross_room_events: betaEvents.length,
  dependency_free: true,
  production_server_generated: false
}, null, 2));
