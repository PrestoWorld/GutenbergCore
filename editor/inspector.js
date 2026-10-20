/**
 * Inspector — right panel: edits the selected block's attributes based on the
 * attribute schema returned by the server (`blocks` definitions).
 */

import {
  getState,
  getBlockByPath,
  setAttribute,
  removeBlock,
  duplicateBlock,
  moveBlock,
  subscribe,
} from './store.js';
import { blockLabel } from './blocks.js';

const ALIGN_OPTIONS = ['left', 'center', 'right', 'justify'];
const FONT_SIZES = [12, 14, 16, 18, 20, 24, 28, 32, 40, 48];

let root;
let titleEl;
let tagEl;

export function initInspector(bodyEl, titleElRef, tagElRef) {
  root = bodyEl;
  titleEl = titleElRef;
  tagEl = tagElRef;

  subscribe((state, scope) => {
    if (scope === 'inspector' || scope === 'canvas' || scope === 'all' || scope === 'selection') {
      render(state);
    }
  });
  render(getState());
}

function render(state) {
  const sel = state.selection;
  if (!sel) {
    if (titleEl) titleEl.textContent = 'Cài đặt';
    if (tagEl) tagEl.textContent = '';
    root.innerHTML =
      '<div class="se-insp-empty">Chọn một block trong canvas để chỉnh sửa cài đặt của nó.</div>';
    return;
  }

  const block = getBlockByPath(sel.region, sel.path);
  if (!block) {
    root.innerHTML = '<div class="se-insp-empty">Block không tồn tại (đã xoá?).</div>';
    return;
  }

  const def = state.blocks[block.type] || {};
  const attrs = block.attributes || {};
  const schema = def.attributes || {};

  if (titleEl) titleEl.textContent = blockLabel(block.type, state.blocks);
  if (tagEl) tagEl.textContent = block.type;

  root.innerHTML = '';

  // --- Fields from schema (fallback: whatever attributes exist on the block) ---
  const keys = Object.keys(schema).length ? Object.keys(schema) : Object.keys(attrs);

  if (keys.length === 0) {
    root.appendChild(emptyNote('Block này không có thuộc tính để chỉnh.'));
  } else {
    for (const key of keys) {
      root.appendChild(buildField(key, schema[key] || { type: 'string' }, attrs[key], sel, block));
    }
  }

  // --- Actions ---
  const actions = document.createElement('div');
  actions.className = 'se-insp-actions';

  const up = actionBtn('Lên', () => moveBlock(sel.region, sel.path, -1));
  const down = actionBtn('Xuống', () => moveBlock(sel.region, sel.path, 1));
  const dup = actionBtn('Nhân bản', () => duplicateBlock(sel.region, sel.path));
  const del = actionBtn('Xoá', () => removeBlock(sel.region, sel.path), 'se-btn-danger');

  actions.append(up, down, dup, del);
  root.appendChild(actions);

  // Path breadcrumb for nested context.
  if (sel.path.length > 1) {
    const crumb = document.createElement('div');
    crumb.className = 'se-insp-empty';
    crumb.style.padding = '8px 4px 0';
    crumb.textContent = 'Đường dẫn: ' + sel.path.join(' › ');
    root.appendChild(crumb);
  }
}

function buildField(key, schema, value, sel, block) {
  const wrap = document.createElement('div');
  wrap.className = 'se-insp-field';

  const label = document.createElement('label');
  label.className = 'se-insp-label';
  label.textContent = humanize(key);
  wrap.appendChild(label);

  const type = schema.type || 'string';
  const current = value !== undefined && value !== null ? value : (schema.default ?? '');

  if (key === 'align') {
    wrap.appendChild(selectInput(ALIGN_OPTIONS, current || 'left', (v) => setAttribute(sel.region, sel.path, key, v)));
  } else if (key === 'color') {
    wrap.appendChild(colorInput(current || '#000000', (v) => setAttribute(sel.region, sel.path, key, v)));
  } else if (type === 'integer' || type === 'number') {
    if (key === 'level') {
      wrap.appendChild(selectInput([1, 2, 3, 4, 5, 6], Number(current) || 2, (v) => setAttribute(sel.region, sel.path, key, Number(v))));
    } else if (key === 'fontSize' || key === 'size') {
      wrap.appendChild(selectInput(FONT_SIZES, Number(current) || 16, (v) => setAttribute(sel.region, sel.path, key, Number(v))));
    } else {
      wrap.appendChild(numberInput(current, (v) => setAttribute(sel.region, sel.path, key, v)));
    }
  } else if (type === 'boolean') {
    wrap.appendChild(checkboxInput(!!current, (v) => setAttribute(sel.region, sel.path, key, v)));
  } else if (key === 'content' || key === 'text' || key === 'description' || type === 'text') {
    wrap.appendChild(textareaInput(current, (v) => setAttribute(sel.region, sel.path, key, v)));
  } else {
    wrap.appendChild(textInput(current, (v) => setAttribute(sel.region, sel.path, key, v)));
  }

  return wrap;
}

/* ---------- input factories ---------- */

function textInput(value, onInput) {
  const el = document.createElement('input');
  el.className = 'se-insp-input';
  el.type = 'text';
  el.value = value;
  el.addEventListener('input', () => onInput(el.value));
  return el;
}

function textareaInput(value, onInput) {
  const el = document.createElement('textarea');
  el.className = 'se-insp-textarea';
  el.value = value;
  el.addEventListener('input', () => onInput(el.value));
  return el;
}

function numberInput(value, onInput) {
  const el = document.createElement('input');
  el.className = 'se-insp-input';
  el.type = 'number';
  el.value = value === '' ? '' : Number(value);
  el.addEventListener('input', () => onInput(el.value === '' ? '' : Number(el.value)));
  return el;
}

function colorInput(value, onInput) {
  const row = document.createElement('div');
  row.className = 'se-insp-row';
  const picker = document.createElement('input');
  picker.type = 'color';
  picker.value = /^#[0-9a-fA-F]{6}$/.test(value) ? value : '#000000';
  picker.style.cssText = 'width:44px;height:34px;padding:2px;border:1px solid #dcdcde;border-radius:4px;background:#fff;cursor:pointer;';
  const text = document.createElement('input');
  text.className = 'se-insp-input';
  text.type = 'text';
  text.value = value;
  picker.addEventListener('input', () => {
    text.value = picker.value;
    onInput(picker.value);
  });
  text.addEventListener('input', () => {
    onInput(text.value);
    if (/^#[0-9a-fA-F]{6}$/.test(text.value)) picker.value = text.value;
  });
  row.append(picker, text);
  return row;
}

function selectInput(options, current, onChange) {
  const el = document.createElement('select');
  el.className = 'se-insp-select';
  for (const opt of options) {
    const o = document.createElement('option');
    o.value = String(opt);
    o.textContent = String(opt);
    if (String(opt) === String(current)) o.selected = true;
    el.appendChild(o);
  }
  el.addEventListener('change', () => onChange(el.value));
  return el;
}

function checkboxInput(checked, onChange) {
  const el = document.createElement('input');
  el.type = 'checkbox';
  el.checked = checked;
  el.style.cssText = 'width:16px;height:16px;';
  el.addEventListener('change', () => onChange(el.checked));
  return el;
}

function actionBtn(text, onClick, extraClass = '') {
  const b = document.createElement('button');
  b.className = 'se-btn ' + extraClass;
  b.type = 'button';
  b.textContent = text;
  b.addEventListener('click', onClick);
  return b;
}

function emptyNote(msg) {
  const d = document.createElement('div');
  d.className = 'se-insp-empty';
  d.textContent = msg;
  return d;
}

function humanize(key) {
  return String(key)
    .replace(/[_-]/g, ' ')
    .replace(/\b\w/g, (c) => c.toUpperCase());
}
