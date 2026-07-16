const categoryColors = {
  culture: '#7c3aed',
  food: '#ea580c',
  transport: '#2563eb',
  nature: '#16a34a'
};

const siteTitle = document.querySelector('#siteTitle');
const siteDescription = document.querySelector('#siteDescription');
const categoryFilter = document.querySelector('#categoryFilter');
const siteSearch = document.querySelector('#siteSearch');
const locationList = document.querySelector('#locationList');
const markerLayer = document.querySelector('#markerLayer');
const popup = document.querySelector('#popup');
const hitCount = document.querySelector('#hitCount');

let siteData = null;
let activeLocationId = null;

function popupHtml(location) {
  return `
    <h3>${escapeHtml(location.title)}</h3>
    <span class="category">${escapeHtml(location.category)}</span>
    <p>${escapeHtml(location.summary)}</p>
    <p>${escapeHtml(location.details)}</p>
    <a href="${escapeAttribute(location.url)}" target="_blank" rel="noreferrer">More information</a>
  `;
}

function escapeHtml(value) {
  return String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function escapeAttribute(value) {
  return escapeHtml(value).replaceAll('`', '&#096;');
}

function visibleLocations() {
  const category = categoryFilter.value;
  const query = normalizedSearchQuery();
  return siteData.locations.filter(location => (
    (category === 'all' || location.category === category) &&
    (!query || searchableText(location).includes(query))
  ));
}

function normalizedSearchQuery() {
  return siteSearch.value.trim().toLowerCase();
}

function searchableText(location) {
  return [
    location.title,
    location.category,
    location.summary,
    location.details,
    location.url
  ].join(' ').toLowerCase();
}

function isSearchHit(location) {
  const query = normalizedSearchQuery();
  return query !== '' && searchableText(location).includes(query);
}

function showPopup(location) {
  activeLocationId = location.id;
  popup.innerHTML = popupHtml(location);
  popup.style.left = `${location.x}%`;
  popup.style.top = `${location.y}%`;
  popup.classList.remove('hidden');
  renderLocations();
}

function renderCategories() {
  const categories = [...new Set(siteData.locations.map(location => location.category))].sort();
  for (const category of categories) {
    const option = document.createElement('option');
    option.value = category;
    option.textContent = category;
    categoryFilter.append(option);
  }
}

function renderLocations() {
  const locations = visibleLocations();
  markerLayer.replaceChildren();
  locationList.replaceChildren();
  const query = normalizedSearchQuery();
  hitCount.textContent = query ? `(${locations.length} hit${locations.length === 1 ? '' : 's'})` : '';
  if (!locations.some(location => location.id === activeLocationId)) {
    activeLocationId = null;
    popup.classList.add('hidden');
  }

  for (const location of locations) {
    const hit = isSearchHit(location);
    const marker = document.createElement('button');
    marker.className = `marker ${location.id === activeLocationId ? 'active' : ''} ${hit ? 'hit' : ''}`;
    marker.type = 'button';
    marker.style.left = `${location.x}%`;
    marker.style.top = `${location.y}%`;
    marker.style.setProperty('--marker-color', categoryColors[location.category] ?? '#2563eb');
    marker.setAttribute('aria-label', `Show ${location.title}`);
    marker.addEventListener('click', () => showPopup(location));
    markerLayer.append(marker);

    const card = document.createElement('button');
    card.className = `location-card ${location.id === activeLocationId ? 'active' : ''} ${hit ? 'hit' : ''}`;
    card.type = 'button';
    card.innerHTML = `<strong>${hit ? '<span class="hit-icon">🔎</span>' : ''}${escapeHtml(location.title)}</strong><span>${escapeHtml(location.category)} · ${escapeHtml(location.summary)}</span>`;
    card.addEventListener('click', () => showPopup(location));
    locationList.append(card);
  }
}

async function boot() {
  siteData = await fetch('/api/site').then(response => response.json());
  siteTitle.textContent = siteData.site.title;
  siteDescription.textContent = siteData.site.description;
  renderCategories();
  renderLocations();
  if (siteData.locations[0]) {
    showPopup(siteData.locations[0]);
  }
}

categoryFilter.addEventListener('change', renderLocations);
siteSearch.addEventListener('input', renderLocations);

await boot();

export { isSearchHit, popupHtml, renderLocations, showPopup };
