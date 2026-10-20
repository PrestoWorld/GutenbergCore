/**
 * Site Editor (Context Editor) — entry point.
 *
 * Boots canvas + inspector + library and wires the WP-style header controls
 * (context switch, save, preview, viewport modes, panel toggles).
 */

import './styles.css';

import {
  getState,
  subscribe,
  loadForContext,
  setView,
  setActiveRegion,
  clearSelection,
  persist,
  countBlocks,
} from './store.js';

import { initCanvas } from './canvas.js';
import { initInspector } from './inspector.js';
import { initLibrary } from './library.js';
import { renderPreview } from './api.js';

const bootstrap = window.__SITE_EDITOR_STATE__ || {};
const $ = (id) => document.getElementById(id);

/* ---------- boot ---------- */

function boot() {
  const frame = $('se-frame');
  const inspectorBody = $('se-inspector');
  const libGrid = $('se-block-lib');
  const libSearch = $('se-block-search');

  initCanvas(frame);
  initInspector(inspectorBody, $('se-insp-title'), $('se-insp-tag'));
  initLibrary(libGrid, libSearch);

  wireHeader();
  wireContextSwitch();
  wireViewModes();
  wirePanelToggles();
  wireKeyboard();
  wireStoreSync();

  // Initial load.
  loadForContext(getState().contextType).catch((err) => {
    toast('Không tải được layout: ' + err.message, true);
  });
}

/* ---------- header ---------- */

function wireHeader() {
  $('se-save')?.addEventListener('click', async () => {
    const btn = $('se-save');
    btn.disabled = true;
    try {
      await persist();
      toast('Đã lưu layout ✓');
    } catch (err) {
      toast('Lưu thất bại: ' + err.message, true);
    }
    btn.disabled = !getState().dirty;
  });

  $('se-preview')?.addEventListener('click', async () => {
    const state = getState();
    const btn = $('se-preview');
    const original = btn.textContent;
    btn.textContent = 'Đang render…';
    btn.disabled = true;
    try {
      const res = await renderPreview(state.contextType, {});
      const html = res && res.html ? res.html : '<p>Không có HTML.</p>';
      const blob = new Blob([html], { type: 'text/html' });
      const url = URL.createObjectURL(blob);
      window.open(url, '_blank', 'noopener');
      setTimeout(() => URL.revokeObjectURL(url), 30000);
    } catch (err) {
      toast('Preview thất bại: ' + err.message, true);
    } finally {
      btn.textContent = original;
      btn.disabled = false;
    }
  });
}

function wireContextSwitch() {
  const select = $('se-context-switch');
  if (!select) return;

  select.addEventListener('change', async () => {
    const state = getState();
    if (state.dirty) {
      const ok = window.confirm('Có thay đổi chưa lưu. Chuyển context sẽ mất?');
      if (!ok) {
        select.value = state.contextType;
        return;
      }
    }
    const next = select.value;
    try {
      await loadForContext(next);
      updateContextLabel();
      toast('Đã chuyển sang ' + next);
    } catch (err) {
      toast('Lỗi chuyển context: ' + err.message, true);
      select.value = state.contextType;
    }
  });
}

function updateContextLabel() {
  const state = getState();
  const type = state.contextTypes[state.contextType];
  const label = $('se-context-label');
  if (label && type) label.textContent = type.label || state.contextType;
}

/* ---------- view modes (responsive preview — the point of the fork) ---------- */

function wireViewModes() {
  const views = document.querySelectorAll('.se-view');
  const frame = $('se-frame');
  views.forEach((btn) => {
    btn.addEventListener('click', () => {
      views.forEach((b) => b.classList.remove('is-active'));
      btn.classList.add('is-active');
      const view = btn.dataset.view || 'desktop';
      setView(view);
      if (frame) {
        frame.classList.remove('se-view-desktop', 'se-view-tablet', 'se-view-mobile');
        frame.classList.add('se-view-' + view);
        frame.style.maxWidth = view === 'mobile' ? '420px' : view === 'tablet' ? '760px' : '1000px';
      }
    });
  });
}

/* ---------- panel toggles ---------- */

function wirePanelToggles() {
  const left = $('se-panel-left');
  const right = $('se-panel-right');
  const toggleLeft = $('se-toggle-lib');
  const toggleRight = $('se-toggle-insp');

  if (toggleLeft && left) {
    toggleLeft.addEventListener('click', () => {
      const hidden = left.style.display === 'none';
      left.style.display = hidden ? '' : 'none';
      toggleLeft.setAttribute('aria-pressed', String(hidden));
    });
  }
  if (toggleRight && right) {
    toggleRight.addEventListener('click', () => {
      const hidden = right.style.display === 'none';
      right.style.display = hidden ? '' : 'none';
      toggleRight.setAttribute('aria-pressed', String(hidden));
    });
  }
}

/* ---------- keyboard ---------- */

function wireKeyboard() {
  document.addEventListener('keydown', (e) => {
    const mod = e.metaKey || e.ctrlKey;

    if (mod && e.key.toLowerCase() === 's') {
      e.preventDefault();
      $('se-save')?.click();
      return;
    }
    if (mod && e.key.toLowerCase() === 'z') {
      e.preventDefault();
      // Undo not wired in v1 — swallow to avoid browser back navigation.
      toast('Undo chưa hỗ trợ trong bản này.', true);
      return;
    }
    if (e.key === 'Escape') {
      clearSelection();
    }
  });
}

/* ---------- store → UI sync ---------- */

function wireStoreSync() {
  const saveBtn = $('se-save');
  const saveState = $('se-save-state');
  const regionCount = $('se-region-count');

  subscribe((state, scope) => {
    if (saveBtn && scope !== 'selection') {
      saveBtn.disabled = !state.dirty || state.saving;
      saveBtn.textContent = state.saving ? 'Đang lưu…' : 'Lưu';
    }
    if (saveState) {
      if (state.saving) saveState.textContent = 'Đang lưu…';
      else if (state.dirty) saveState.textContent = 'Chưa lưu thay đổi';
      else if (state.lastSaved) saveState.textContent = 'Đã lưu';
      else saveState.textContent = '';
    }
    if (regionCount && (scope === 'all' || scope === 'canvas')) {
      regionCount.textContent = countBlocks() + ' block · ' + state.regions.length + ' region';
    }
  });
}

/* ---------- util ---------- */

function toast(message, isError = false) {
  const el = $('se-toast');
  if (!el) return;
  el.textContent = message;
  el.classList.toggle('is-err', !!isError);
  el.classList.add('is-show');
  clearTimeout(el.__t);
  el.__t = setTimeout(() => el.classList.remove('is-show'), 2400);
}

/* ---------- go ---------- */

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot);
} else {
  boot();
}
