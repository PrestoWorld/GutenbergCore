/**
 * Client-side renderers for Presto blocks.
 *
 * Keep in sync with the server model: a block config is
 *   { type, attributes:{...}, content:string, blocks:[ nested ] }
 *
 * These render into the editing canvas (not the final SSR output) — they need
 * to be visual, selectable and responsive (the whole point of this fork).
 */

const esc = (s) =>
  String(s ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');

const LEVELS = [1, 2, 3, 4, 5, 6];

export const renderers = {
  'presto/heading'(block) {
    const a = block.attributes || {};
    const level = LEVELS.includes(Number(a.level)) ? Number(a.level) : 2;
    const color = a.color ? `color:${esc(a.color)};` : '';
    const text = block.content || a.content || '';
    return `<h${level} class="se-b-heading" style="${color}font-weight:700;line-height:1.2;margin:0 0 .5em;">${esc(text) || '&nbsp;'}</h${level}>`;
  },

  'presto/paragraph'(block) {
    const a = block.attributes || {};
    const align = a.align || 'left';
    const text = block.content || a.content || '';
    return `<p class="se-b-paragraph" style="text-align:${esc(align)};margin:0 0 1em;line-height:1.6;">${esc(text) || '&nbsp;'}</p>`;
  },

  'presto/image'(block) {
    const a = block.attributes || {};
    const src = a.url || a.src || '';
    const alt = a.alt || '';
    if (!src) {
      return `<div class="se-b-image-empty" style="padding:24px;text-align:center;background:#f0f0f1;border:1px dashed #dcdcde;border-radius:4px;color:#757575;font-size:12px;">Ảnh chưa được chọn</div>`;
    }
    const ratio = a.aspectRatio ? `aspect-ratio:${esc(a.aspectRatio)};` : '';
    return `<img class="se-b-image" src="${esc(src)}" alt="${esc(alt)}" style="max-width:100%;height:auto;${ratio}display:block;border-radius:2px;" />`;
  },

  'presto/query-loop'(block) {
    const a = block.attributes || {};
    const posts = Number(a.postsToShow || a.perPage || 3);
    let rows = '';
    for (let i = 0; i < Math.max(1, Math.min(posts, 6)); i++) {
      rows += `<div class="se-b-ql-item" style="padding:12px 0;border-bottom:1px solid #f0f0f1;">
        <div style="font-weight:600;margin-bottom:4px;">Bài viết mẫu ${i + 1}</div>
        <div style="color:#757575;font-size:12px;">Trích đoạn nội dung sẽ hiển thị tại đây.</div>
      </div>`;
    }
    return `<div class="se-b-queryloop" data-label="Query Loop">${rows}</div>`;
  },
};

/** Unknown / unregistered block → visible placeholder (never break the canvas). */
export function renderBlock(block) {
  const fn = renderers[block.type];
  if (fn) {
    return fn(block);
  }
  const label = esc(block.type || 'unknown');
  return `<div class="se-b-unknown" style="padding:14px;border:1px dashed #dcdcde;border-radius:4px;background:#fafafa;color:#757575;font-size:12px;font-style:italic;">Block <code>${label}</code> (chưa có renderer ở client — sẽ render SSR)</div>`;
}

/** Get human label for a block type (library cards). */
export function blockLabel(type, definitions = {}) {
  if (definitions[type] && definitions[type].label) {
    return definitions[type].label;
  }
  const base = String(type).split('/').pop() || type;
  return base.replace(/[-_]/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

/** Simple icon glyph per block family (no icon lib dependency). */
export function blockIcon(type) {
  const t = String(type);
  if (t.includes('heading')) return 'H';
  if (t.includes('paragraph')) return '¶';
  if (t.includes('image')) return '▣';
  if (t.includes('query')) return '≡';
  if (t.includes('list')) return '•';
  if (t.includes('button')) return '▢';
  if (t.includes('group')) return '▦';
  return '◇';
}
