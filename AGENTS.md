# AGENTS.md

Laravel 13 (PHP `^8.3`, CI uses 8.5) + Inertia v3 + React 19 + Tailwind v4 + Wayfinder. Frontend commands go through `vite-plus` (`vp`), not raw vite.

## Setup & commands

- Initial setup: `composer setup` (install, key:generate, migrate, `npm install`, `npm run build`).
- Dev server: `composer dev` (`php artisan dev`).
- Full verification: `composer test` = `config:clear` → `pint --test` → `phpstan analyse` → `php artisan test`. CI (`composer ci:check`) = `npm run check` + `npm run types:check` + `composer test`.
- Single test: `php artisan test --filter=Name` or `vendor/bin/pest --filter=Name <path>`.
- PHP lint/format: `composer lint` / `composer lint:check` (`vendor/bin/pint --dirty` after editing PHP).
- Frontend: `npm run check` (vp lint, `denyWarnings: true`), `npm run types:check` (`tsc --noEmit`). Rebuild on manifest errors: `npm run build`.

## Architecture

- Domain logic: `app/Convert/` (`FlowchartGraph.php`, `Graph.php`, `Ast/` — flowchart graph → AST → codegen).
- API: `routes/api.php` has one endpoint, `GET /api/convert/{sourceLanguage}/to/{targetLanguage}` → `ConvertController@convert`. Allowed values gated by `config/convert.php` (`sourceLanguages: [flowchart]`, `targetLanguages: [python]`).
- Response envelope (`App\Traits\ApiResponse`): success `{status,message,data}`, error `{status,message,details,code}`.
- Web: `routes/web.php` has only `Route::inertia('/', 'welcome')`; pages live in `resources/js/pages/` (`welcome.tsx` is still the starter-kit placeholder).
- Frontend entry: `resources/js/app.tsx` + `resources/css/app.css` (see `vite.config.ts`). Call backend via Wayfinder (`@/actions/`, `@/routes/`), never hardcoded URLs.

## Gotchas

- `ConvertController::convert` currently ignores the request body (validation commented out) and uses hardcoded `$nodes`/`$edges` fixtures — restore `$request->validate(['nodes'=>'array|required','edges'=>'array|required'])` when wiring real input.
- DB: local dev defaults to pgsql (`DB_DATABASE=ic` per `.env.example`); tests force sqlite `:memory:` via `phpunit.xml`. `tests/Pest.php` applies `RefreshDatabase` to `Feature` only.
- Generated frontend files are linter-ignored, do not hand-edit: `resources/js/actions/**`, `resources/js/routes/**`, `resources/js/wayfinder/**`, `resources/js/components/ui/*`, `bootstrap/ssr/**`.
- `php artisan make:* --no-interaction` for new files; Pest tests via `php artisan make:test --pest {Name}` (no suite prefix). Check installed package versions (`composer show`, `package.json`) before trusting API docs.
