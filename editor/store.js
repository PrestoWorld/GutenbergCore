/**
 * Editor store — single source of truth + mutations.
 *
 * Selection model: `{ region, path: [i, j, ...] }`.
 * `path` indexes into nested `blocks` arrays:
 *   path [2]      -> layout[region][2]
 *   path [2, 0]   -> layout[region][2].blocks[0]
 */

import { fetchLayout, saveLayout, fetchBlocks } from './api.js';

const bootstrap = window.__SITE_EDITOR_STATE__ || {};

const state = {
  contextType: bootstrap.contextType || 'post',
  contextTypes: bootstrap.contextTypes || {},
  regions: ['header', 'content', 'sidebar', 'footer'],
  layout: { header: [], content: [], sidebar: [], footer: [] },
  blocks: {},
  selection: null, // { region, path:number[] }
  activeRegion: 'content',
  dirty: false,
  saving: false,
  loading: false,
  view: 'desktop',
  error: null,
};

const listeners = new Set();

export function getState() {
  return state;
}

export function subscribe(fn) {
  listeners.add(fn);
  return () => listeners.delete(fn);
}

function emit(scope = 'all') {
  for (const fn of listeners) {
    try {
      fn(state, scope);
    } catch (err) {
      console.error('[site-editor] listener failed', err);
    }
  }
}

/* ---------- path helpers ---------- */

export function getBlockByPath(region, path) {
  const root = state.layout[region];
  if (!Array.isArray(root)) return null;
  let list = root;
  let block = null;
  for (const idx of path) {
    block = list?.[idx];
    if (!block) return null;
    list = Array.isArray(block.blocks) ? block.blocks : [];
  }
  return block || null;
}

/** The array that directly contains the block at path (for insert/remove). */
function getListContaining(region, path) {
  if (path.length === 0) return null;
  const parentPath = path.slice(0, -1);
  if (parentPath.length === 0) {
    return Array.isArray(state.layout[region]) ? state.layout[region] : null;
  }
  const parent = getBlockByPath(region, parentPath);
  if (!parent || !Array.isArray(parent.blocks)) return null;
  return parent.blocks;
}

function markDirty() {
  state.dirty = true;
}

/* ---------- mutations ---------- */

export async function loadForContext(contextType) {
  state.loading = true;
  state.error = null;
  state.selection = null;
  emit('loading');

  try {
    const [layoutRes, blocksRes] = await Promise.all([
      fetchLayout(contextType),
      Object.keys(state.blocks).length ? Promise.resolve(null) : fetchBlocks(),
    ]);

    state.contextType = contextType;
    const raw = layoutRes.layout || {};
    // Normalise: ensure every declared region exists.
    const layout = {};
    for (const region of state.regions) {
      layout[region] = Array.isArray(raw[region]) ? raw[region] : [];
    }
    // Keep any extra regions the server returned.
    for (const [k, v] of Object.entries(raw)) {
      if (!(k in layout)) layout[k] = Array.isArray(v) ? v : [];
    }
    state.layout = layout;
    state.regions = layoutRes.regions || state.regions;

    if (blocksRes && blocksRes.blocks) {
      state.blocks = blocksRes.blocks;
    }

    state.dirty = false;
    state.loading = false;
    emit('all');
  } catch (err) {
    state.loading = false;
    state.error = err.message;
    emit('all');
    throw err;
  }
}

export function select(region, path) {
  state.selection = path && path.length ? { region, path: [...path] } : null;
  if (region) state.activeRegion = region;
  emit('selection');
}

export function clearSelection() {
  state.selection = null;
  emit('selection');
}

export function setAttribute(region, path, key, value) {
  const block = getBlockByPath(region, path);
  if (!block) return;
  block.attributes = block.attributes || {};
  block.attributes[key] = value;
  // Keep `content` mirrors in sync so canvas + server agree.
  if (key === 'content') {
    block.content = value;
  }
  markDirty();
  emit('canvas');
}

/** Insert a new block (from library) into a region, after selection (or at end). */
export function insertBlock(region, definition, atIndex = null) {
  if (!Array.isArray(state.layout[region])) {
    state.layout[region] = [];
  }
  const list = state.layout[region];
  const defaults = {};
  for (const [key, schema] of Object.entries(definition.attributes || {})) {
    defaults[key] = schema && 'default' in schema ? schema.default : '';
  }
  const block = {
    type: definition.name,
    attributes: defaults,
    content: defaults.content || '',
    blocks: [],
  };

  let index = atIndex;
  if (index === null) {
    if (state.selection && state.selection.region === region) {
      index = state.selection.path[state.selection.path.length - 1] + 1;
    } else {
      index = list.length;
    }
  }
  list.splice(Math.max(0, Math.min(index, list.length)), 0, block);

  state.activeRegion = region;
  state.selection = { region, path: [index] };
  markDirty();
  emit('all');
}

export function removeBlock(region, path) {
  const list = getListContaining(region, path);
  if (!list) return;
  const idx = path[path.length - 1];
  if (idx < 0 || idx >= list.length) return;
  list.splice(idx, 1);
  state.selection = null;
  markDirty();
  emit('all');
}

export function duplicateBlock(region, path) {
  const list = getListContaining(region, path);
  if (!list) return;
  const idx = path[path.length - 1];
  const source = list[idx];
  if (!source) return;
  const copy = JSON.parse(JSON.stringify(source));
  list.splice(idx + 1, 0, copy);
  state.selection = { region, path: [...path.slice(0, -1), idx + 1] };
  markDirty();
  emit('all');
}

export function moveBlock(region, path, delta) {
  const list = getListContaining(region, path);
  if (!list) return;
  const idx = path[path.length - 1];
  const target = idx + delta;
  if (idx < 0 || target < 0 || target >= list.length) return;
  const [item] = list.splice(idx, 1);
  list.splice(target, 0, item);
  state.selection = { region, path: [...path.slice(0, -1), target] };
  markDirty();
  emit('canvas');
}

export function setView(view) {
  state.view = view;
  emit('view');
}

export function setActiveRegion(region) {
  state.activeRegion = region;
  emit('selection');
}

export async function persist() {
  if (state.saving) return false;
  state.saving = true;
  state.error = null;
  emit('save');

  try {
    const res = await saveLayout(state.contextType, state.layout);
    if (res && res.success) {
      state.dirty = false;
      state.saving = false;
      emit('save');
      return true;
    }
    throw new Error(res && res.error ? res.error : 'Lưu thất bại');
  } catch (err) {
    state.saving = false;
    state.error = err.message;
    emit('save');
    throw err;
  }
}

export function countBlocks(layout = state.layout) {
  let n = 0;
  const walk = (list) => {
    if (!Array.isArray(list)) return;
    for (const b of list) {
      n++;
      if (b && Array.isArray(b.blocks)) walk(b.blocks);
    }
  };
  for (const list of Object.values(layout)) walk(list);
  return n;
}
