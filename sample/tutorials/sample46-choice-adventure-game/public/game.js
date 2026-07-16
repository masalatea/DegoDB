import scenario from '../reference/choice-adventure-input.sample.json' with { type: 'json' };

const scenes = new Map(scenario.scenes.map(scene => [scene.id, scene]));
const frame = document.querySelector('#frame');
const sceneKind = document.querySelector('#sceneKind');
const sceneTitle = document.querySelector('#sceneTitle');
const sceneText = document.querySelector('#sceneText');
const choicesEl = document.querySelector('#choices');

let currentSceneId = scenario.game.opening_scene;
let currentSceneData = scenes.get(currentSceneId);
let selectedChoiceIndex = 0;
let history = [];
const sessionId = `sample46-${Math.random().toString(16).slice(2)}`;
const adventureApi = createAdventureApi({
  scenario,
  endpoint: globalThis.SAMPLE46_ADVENTURE_API_URL
});

function currentScene() {
  return currentSceneData;
}

function renderScene() {
  const scene = currentSceneData;
  frame.className = `frame ${scene.frame}`;
  sceneKind.textContent = scene.kind.replaceAll('_', ' ');
  sceneTitle.textContent = scene.title;
  sceneText.textContent = scene.text;
  selectedChoiceIndex = Math.min(selectedChoiceIndex, scene.choices.length - 1);
  choicesEl.replaceChildren();
  scene.choices.forEach((choice, index) => {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = `choice ${index === selectedChoiceIndex ? 'selected' : ''}`;
    button.role = 'option';
    button.ariaSelected = index === selectedChoiceIndex ? 'true' : 'false';
    button.textContent = choice.label;
    button.addEventListener('click', () => choose(index));
    choicesEl.append(button);
  });
}

async function choose(index = selectedChoiceIndex) {
  const result = await adventureApi.choose({
    session_id: sessionId,
    current_scene_id: currentSceneId,
    choice_index: index,
    history
  });
  currentSceneData = result.scene;
  currentSceneId = result.scene.id;
  history = result.history;
  selectedChoiceIndex = 0;
  renderScene();
}

function moveSelection(delta) {
  const scene = currentScene();
  selectedChoiceIndex = (selectedChoiceIndex + delta + scene.choices.length) % scene.choices.length;
  renderScene();
}

document.addEventListener('keydown', event => {
  if (event.key === 'ArrowUp') {
    event.preventDefault();
    moveSelection(-1);
  } else if (event.key === 'ArrowDown') {
    event.preventDefault();
    moveSelection(1);
  } else if (event.key === 'Enter') {
    event.preventDefault();
    choose();
  }
});

async function boot() {
  const result = await adventureApi.start({ session_id: sessionId });
  currentSceneData = result.scene;
  currentSceneId = result.scene.id;
  history = result.history;
  renderScene();
}

function createAdventureApi({ scenario, endpoint = '' }) {
  if (endpoint) {
    return {
      async start(payload) {
        return postJson(`${endpoint.replace(/\/$/, '')}/adventure/start`, payload);
      },
      async choose(payload) {
        return postJson(`${endpoint.replace(/\/$/, '')}/adventure/choose`, payload);
      }
    };
  }
  return createMockAdventureApi(scenario);
}

function createMockAdventureApi(scenario) {
  const sceneMap = new Map(scenario.scenes.map(scene => [scene.id, scene]));
  return {
    async start() {
      return {
        scene: sceneMap.get(scenario.game.opening_scene),
        history: []
      };
    },
    async choose({ current_scene_id: sceneId, choice_index: choiceIndex, history: previousHistory }) {
      const scene = sceneMap.get(sceneId);
      const choice = scene?.choices?.[choiceIndex];
      if (!scene || !choice) {
        return {
          scene: sceneMap.get(sceneId) ?? sceneMap.get(scenario.game.opening_scene),
          history: previousHistory
        };
      }
      const nextHistory = [...previousHistory];
      let nextSceneId = choice.target;
      if (choice.target === '__back__') {
        nextSceneId = nextHistory.pop() ?? scenario.game.opening_scene;
      } else if (scene.kind !== 'game_over' && scene.kind !== 'ending') {
        nextHistory.push(scene.id);
      }
      return {
        scene: sceneMap.get(nextSceneId),
        history: nextHistory
      };
    }
  };
}

async function postJson(url, payload) {
  const response = await fetch(url, {
    method: 'POST',
    headers: { 'content-type': 'application/json' },
    body: JSON.stringify(payload)
  });
  if (!response.ok) {
    throw new Error(`Adventure API failed: ${response.status}`);
  }
  return response.json();
}

await boot();

export { choose, createAdventureApi, createMockAdventureApi, currentScene, moveSelection, renderScene };
