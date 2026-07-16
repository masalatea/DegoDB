import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import { once } from 'node:events';
import { createServer, readPacket } from '../src/server.mjs';

const sampleRoot = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');

for (const file of [
  'README.md',
  'public/index.html',
  'public/styles.css',
  'public/app.js',
  'reference/map-info-site-input.sample.json',
  'src/server.mjs',
  'scripts/validate-sample.mjs'
]) {
  assert.equal(fs.existsSync(path.join(sampleRoot, file)), true, `Missing required file: ${file}`);
}

assert.equal(fs.existsSync(path.join(sampleRoot, 'package.json')), false, 'sample45 must not require package.json');
assert.equal(fs.existsSync(path.join(sampleRoot, 'node_modules')), false, 'sample45 must not include node_modules');

const packet = readPacket();

assert.equal(packet.schema_version, 'map_info_popup_site_input.v1', 'schema_version mismatch');
assert.equal(packet.generated_by?.tool, 'mtool', 'generated_by.tool must be mtool');
assert.equal(packet.generated_by?.artifact, 'map_info_popup_site_input', 'artifact name mismatch');
assert.equal(packet.map_provider?.default, 'local_static_canvas_map', 'default provider must be local and key-free');
assert.equal(packet.map_provider?.requires_api_key, false, 'local sample must not require API key');
assert.equal(packet.map_provider?.google_maps_optional, true, 'Google Maps must be optional only');
assert.equal(packet.map_provider?.google_maps_api_key_stored_in_packet, false, 'Google Maps API key must not be stored in packet');
assert.equal(packet.search?.enabled, true, 'site search must be enabled');
assert.equal(packet.search?.hit_icon, '🔎', 'site search hit icon mismatch');
assert.equal(packet.search?.show_hit_count, true, 'site search hit count must be enabled');
assert.equal(packet.search?.highlight_marker_hits, true, 'site search marker hit highlight must be enabled');
assert.equal(packet.search?.highlight_list_hits, true, 'site search list hit highlight must be enabled');
assert.equal(packet.locations.length >= 4, true, 'sample should include multiple locations');

for (const field of packet.marker_schema.required_fields) {
  for (const location of packet.locations) {
    assert.notEqual(location[field], undefined, `Location ${location.id} missing ${field}`);
  }
}

for (const field of ['title', 'category', 'summary', 'details', 'url']) {
  assert.equal(packet.marker_schema.popup_fields.includes(field), true, `popup field missing: ${field}`);
}

for (const check of [
  'no_api_key_required',
  'google_maps_key_not_stored',
  'structured_location_data',
  'marker_click_popup',
  'list_click_popup',
  'site_search',
  'site_search_hit_icon',
  'site_search_hit_count',
  'category_filter',
  'popup_fields_present',
  'local_runtime_serves_json'
]) {
  assert.equal(packet.validation.required_checks.includes(check), true, `Missing validation check: ${check}`);
}

for (const forbidden of [
  'store_google_maps_api_key_in_repo',
  'require_google_maps_api_for_local_sample',
  'claim_production_map_provider_integration',
  'load_remote_tiles_in_validator'
]) {
  assert.equal(packet.forbidden_actions.includes(forbidden), true, `Missing forbidden action: ${forbidden}`);
}

const serializedPacket = JSON.stringify(packet).toLowerCase();
for (const forbiddenToken of ['google_maps_api_key=', 'api_key=', 'secret', 'password']) {
  assert.equal(serializedPacket.includes(forbiddenToken), false, `Packet must not contain ${forbiddenToken}`);
}

const server = createServer();

function listen() {
  return new Promise((resolve, reject) => {
    server.once('error', reject);
    server.listen(0, '127.0.0.1', () => {
      server.off('error', reject);
      const address = server.address();
      resolve(`http://${address.address}:${address.port}`);
    });
  });
}

async function close() {
  server.close();
  await once(server, 'close');
}

try {
  const baseUrl = await listen();
  const html = await fetch(`${baseUrl}/`).then(response => {
    assert.equal(response.status, 200, 'index loads');
    return response.text();
  });
  assert.match(html, /markerLayer/, 'index includes marker layer');
  assert.match(html, /popup/, 'index includes popup element');
  assert.match(html, /siteSearch/, 'index includes site search input');
  assert.match(html, /hitCount/, 'index includes hit count element');

  const api = await fetch(`${baseUrl}/api/site`).then(response => {
    assert.equal(response.status, 200, 'site API loads');
    return response.json();
  });
  assert.equal(api.map_provider.requires_api_key, false, 'site API says no API key required');
  assert.equal(api.locations.length, packet.locations.length, 'site API serves all locations');
  assert.deepEqual(api.marker_schema.popup_fields, packet.marker_schema.popup_fields, 'site API serves popup schema');

  const js = await fetch(`${baseUrl}/app.js`).then(response => {
    assert.equal(response.status, 200, 'app JS loads');
    return response.text();
  });
  assert.match(js, /showPopup/, 'client has popup function');
  assert.match(js, /marker\.addEventListener\('click'/, 'marker click opens popup');
  assert.match(js, /card\.addEventListener\('click'/, 'list click opens popup');
  assert.match(js, /siteSearch\.addEventListener\('input'/, 'site search input is wired');
  assert.match(js, /isSearchHit/, 'client computes search hits');
  assert.match(js, /hit-icon/, 'client renders search hit icon in list');
  assert.match(js, /marker.*hit/s, 'client marks hit markers');
  assert.match(js, /categoryFilter\.addEventListener\('change'/, 'category filter is wired');
  assert.match(js, /\/api\/site/, 'client loads structured site data');
  assert.doesNotMatch(js, /maps\.googleapis\.com|google\.maps|GOOGLE_MAPS_API_KEY/, 'client must not require Google Maps API');

  const css = await fetch(`${baseUrl}/styles.css`).then(response => {
    assert.equal(response.status, 200, 'CSS loads');
    return response.text();
  });
  assert.match(css, /\.marker::before/, 'CSS renders markers');
  assert.match(css, /\.marker\.hit::after/, 'CSS renders marker hit icon');
  assert.match(css, /\.location-card\.hit/, 'CSS highlights list hits');
  assert.match(css, /\.popup/, 'CSS renders popup');

  console.log(JSON.stringify({
    ok: true,
    sample: 'sample45-map-info-popup-site',
    locations: api.locations.length,
    provider: api.map_provider.default,
    api_key_required: api.map_provider.requires_api_key,
    marker_click_popup: true,
    list_click_popup: true,
    site_search_hit_icon: true,
    category_filter: true
  }, null, 2));
} finally {
  await close();
}
