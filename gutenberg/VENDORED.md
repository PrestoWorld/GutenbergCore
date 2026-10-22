# Vendored Gutenberg — PrestoWorld editor source

Đây là **clone đầy đủ source** của [WordPress/gutenberg] dùng để build
bundle cho Site Editor của prestoworld.org. Vendored vào repo để:

1. **Tự-contained** — không phụ thuộc checkout ngoài máy
   (`/Users/puleeno/Projects/gutenberg` chỉ là bản sao, không dùng để build).
2. **Tùy biến tự do** — sửa trực tiếp `packages/*/src` rồi build lại.

- **Nguồn:** https://github.com/WordPress/gutenberg
- **Commit:** `e59587e516c` — "feat: add PrestoWorld build config and API adapter"
- **License:** GPL-2.0-or-later (xem `GUTENBERG-LICENSE.md`)

## Build

Chạy từ thư mục cha (`gutenberg-core/`):

```bash
npm install               # lần đầu
npm run build:gutenberg   # production → ../../../public/assets/gutenberg/
npm run dev:gutenberg     # watch mode
```

- Webpack config: `gutenberg/webpack.presto.js` (`context` = thư mục này,
  `output.path` = `../../../../public/assets/gutenberg`).
- Script build tự `cd gutenberg/` để babel tìm thấy `babel.config.js`.
- Entry `editor` = `packages/edit-post/src/index`,
  `presto` = `packages/edit-site/src/presto-boot`,
  style = `presto-styles.js`.

## Tùy biến ở đâu

Sửa trong `packages/*/src` rồi `npm run build:gutenberg` — alias
`@wordpress/*` trong webpack trỏ thẳng vào `packages/*/src`, nên **mọi
thay đổi source được bundle ngay**:

- UI Site Editor: `packages/edit-site/src/`, `packages/editor/src/`
- Block library: `packages/block-library/src/<block>/`
- Component: `packages/components/src/`
- Code riêng của PrestoWorld:
  - `packages/api-fetch/src/presto-adapter.js` — redirect `/wp/v2/` → `/pw-api/v1/`
  - `presto-styles.js` — style entry
  - `webpack.presto.js` — config build (entries/aliases/output)

## Ghi chú kỹ thuật

- **npm, không pnpm** — dependency exact-pinned trong
  `../package.json` khớp phiên bản của bản build tham chiếu
  (react 19.3.0, webpack 5.112.0, css-loader 6.2.x, …).
  Output giống hệt bản build gốc **vào module/chunk id** — id số được
  webpack hash từ absolute path + layout node_modules nên khi chuyển sang
  npm-flat sẽ renumber (CSS byte-identical; JS content identical).
- `gutenberg/package.json` **cố ý không có trường `type`**: giữ `.js` ở đây
  là CommonJS để webpack nhận đúng module type (xoá field này đi sẽ hỏng
  `presto-styles.js`) trong khi package cha là `"type": "module"`.
- Không xoá `babel.config.js` / `tools/build-scripts/babel.config.cjs` —
  babel-loader merge root config khi cwd = thư mục này.
- Chunk số (`539/566/784.min.js`...) là async chunks — giữ nguyên khi commit.

[WordPress/gutenberg]: https://github.com/WordPress/gutenberg
