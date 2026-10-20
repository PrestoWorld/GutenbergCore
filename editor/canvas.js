/**
 * Canvas — renders the layout (regions + blocks) into the editor frame.
 * Pure DOM build; re-renders on store changes scoped to 'canvas'/'all'.
 */

import { getState, select, subscribe } from './store.js';
import { renderBlock, blockLabel } from './blocks.js';

let frame;

export function initCanvas(container) {
  frame = container;
  subscribe((state, scope) => {
    if (scope === 'canvas' || scope === 'all' || scope === 'loading') {
      render(state);
    } else if (scope === 'selection') {
      syncSelection(state);
    }
  });
  render(getState());
}

function render(state) {
  // Preserve loading overlay handling in main; here we own the regions.
  frame.innerHTML = '';

  if (state.loading) {
    const loader = document.createElement('div');
    loader.className = 'se-loading';
    loader.textContent = 'Đang tải layout…';
    frame.appendChild(loader);
    return;
  }

  if (state.error) {
    const err = document.createElement('div');
    err.className = 'se-block-empty';
    err.style.borderColor = '#d63638';
    err.style.color = '#d63638';
    err.textContent = 'Lỗi: ' + state.error;
    frame.appendChild(err);
  }

  for (const region of state.regions) {
    frame.appendChild(renderRegion(state, region));
  }

  syncSelection(state);
}

function renderRegion(state, region) {
  const el = document.createElement('div');
  el.className = 'se-region';
  el.dataset.region = region;
  if (state.activeRegion === region && !state.selection) el.classList.add('is-active');

  const label = document.createElement('span');
  label.className = 'se-region-label';
  label.textContent = region;
  el.appendChild(label);

  const blocks = state.layout[region] || [];

  if (blocks.length === 0) {
    const empty = document.createElement('div');
    empty.className = 'se-block-empty';
    empty.textContent = `Region "${region}" trống — chèn block từ thư viện bên trái.`;
    empty.addEventListener('click', (e) => {
      e.stopPropagation();
      select(region, []);
    });
    el.appendChild(empty);
  } else {
    blocks.forEach((block, i) => {
      el.appendChild(renderBlockEl(state, region, [i], block));
    });
  }

  // Click empty region area → make it the active insert target.
  el.addEventListener('click', (e) => {
    if (e.target === el || e.target === label) {
      select(region, []);
    }
  });

  return el;
}

function renderBlockEl(state, region, path, block) {
  const el = document.createElement('div');
  el.className = 'se-block';
  el.dataset.block = block.type || '';
  el.dataset.path = path.join(',');

  const inner = document.createElement('div');
  inner.className = 'se-block-body';
  inner.innerHTML = renderBlock(block);
  el.appendChild(inner);

  // Tiny type badge (only on hover via CSS is nicer; keep it lightweight).
  el.title = blockLabel(block.type, state.blocks) + ' — click để chọn';

  el.addEventListener('click', (e) => {
    e.stopPropagation();
    select(region, path);
  });

  // Recurse nested blocks.
  if (Array.isArray(block.blocks) && block.blocks.length) {
    const nest = document.createElement('div');
    nest.className = 'se-block-children';
    block.blocks.forEach((child, i) => {
      nest.appendChild(renderBlockEl(state, region, [...path, i], child));
    });
    el.appendChild(nest);
  }

  return el;
}

/** Toggle .is-selected on the DOM node matching current selection. */
function syncSelection(state) {
  const selected = frame.querySelectorAll('.se-block.is-selected');
  selected.forEach((n) => n.classList.remove('is-selected'));

  frame.querySelectorAll('.se-region.is-active').forEach((n) => n.classList.remove('is-active'));

  if (state.selection) {
    const { region, path } = state.selection;
    const pathStr = path.join(',');
    const node = frame.querySelector(
      `.se-block[data-path="${pathStr}"]`
    );
    // data-path collides across regions, so scope by region.
    const regionEl = frame.querySelector(`.se-region[data-region="${region}"]`);
    if (regionEl) {
      regionEl.classList.add('is-active');
      const scoped = regionEl.querySelector(`.se-block[data-path="${pathStr}"]`);
      if (scoped) scoped.classList.add('is-selected');
    } else if (node) {
      node.classList.add('is-selected');
    }
  } else if (state.activeRegion) {
    const regionEl = frame.querySelector(`.se-region[data-region="${state.activeRegion}"]`);
    if (regionEl) regionEl.classList.add('is-active');
  }
}
