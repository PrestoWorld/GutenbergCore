/**
 * Block library — left panel. Renders cards from the server block definitions,
 * supports search, inserts into the active region.
 */

import { getState, insertBlock, setActiveRegion, subscribe } from './store.js';
import { blockLabel, blockIcon } from './blocks.js';

let gridEl;
let searchEl;
let searchQuery = '';

export function initLibrary(grid, search) {
  gridEl = grid;
  searchEl = search;

  searchEl.addEventListener('input', () => {
    searchQuery = searchEl.value.trim().toLowerCase();
    render(getState());
  });

  subscribe((state, scope) => {
    if (scope === 'all' || scope === 'library') render(state);
  });

  render(getState());
}

function render(state) {
  gridEl.innerHTML = '';
  const defs = state.blocks || {};
  const names = Object.keys(defs);

  if (names.length === 0) {
    const note = document.createElement('div');
    note.className = 'se-insp-empty';
    note.style.gridColumn = '1 / -1';
    note.textContent = 'Chưa có block nào được đăng ký.';
    gridEl.appendChild(note);
    return;
  }

  const filtered = names.filter((name) => {
    if (!searchQuery) return true;
    const label = blockLabel(name, defs).toLowerCase();
    return name.toLowerCase().includes(searchQuery) || label.includes(searchQuery);
  });

  if (filtered.length === 0) {
    const note = document.createElement('div');
    note.className = 'se-insp-empty';
    note.style.gridColumn = '1 / -1';
    note.textContent = `Không tìm thấy "${searchQuery}".`;
    gridEl.appendChild(note);
    return;
  }

  for (const name of filtered) {
    const def = defs[name];
    const card = document.createElement('button');
    card.type = 'button';
    card.className = 'se-block-card';
    card.title = `Chèn ${blockLabel(name, defs)} vào region "${state.activeRegion}"`;

    const ico = document.createElement('span');
    ico.className = 'ico';
    ico.textContent = blockIcon(name);

    const text = document.createElement('span');
    text.textContent = blockLabel(name, defs);

    card.append(ico, text);
    card.addEventListener('click', () => {
      insertBlock(state.activeRegion, def);
      toast(`Đã chèn ${blockLabel(name, defs)} vào "${state.activeRegion}"`);
    });

    gridEl.appendChild(card);
  }
}

/* small local toast (avoids importing main just for this) */
function toast(message) {
  const el = document.getElementById('se-toast');
  if (!el) return;
  el.textContent = message;
  el.classList.remove('is-err');
  el.classList.add('is-show');
  clearTimeout(el.__t);
  el.__t = setTimeout(() => el.classList.remove('is-show'), 1800);
}
