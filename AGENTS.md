# AGENTS

This repository is the Symfony frontend for verbrannte-buecher.de. Keep instructions here minimal and link to source documents for details.

## Start Here

- Project setup and content workflows: [README.md](README.md)
- Service wiring and application parameters: [config/services.yaml](config/services.yaml)
- Route overrides and manual bundle routes: [config/routes.yaml](config/routes.yaml)
- Bundle and framework configuration: [config/packages](config/packages)
- Application code: [src](src)
- SCSS entrypoint: [assets/scss/base.scss](assets/scss/base.scss)
- Static analysis scope: [phpstan.dist.neon](phpstan.dist.neon)
- Annotation-to-attribute migration rules: [rector.php](rector.php)

## Stack And Boundaries

- PHP 8.4.1+, Symfony 8.1, Doctrine ORM, Twig, MySQL, Solr.
- `App\\` code lives in [src](src) and is PSR-4 autowired by default.
- The app relies heavily on the vendor `igdj/tei-edition-bundle`; prefer app-side overrides, decorators, and route overrides instead of editing vendor code.
- Controllers are split between `App\\Controller` and bundle controllers. App controllers typically extend `BaseController`; many bundle routes are remapped in [config/routes.yaml](config/routes.yaml).
- Doctrine mapping uses PHP attributes. When touching older annotation-style code, keep it aligned with [rector.php](rector.php) and prefer attributes over introducing new annotations.
- The site is currently German-first and -only. Existing routes, labels, and content assumptions are usually `de` only unless a file clearly shows otherwise.

## Editing Conventions

- Prefer adding new behavior in `src/` and only touch [config/services.yaml](config/services.yaml) when you need explicit arguments, tags, bindings, or decorators.
- Check [config/routes.yaml](config/routes.yaml) before changing route behavior. Some URLs intentionally override vendor defaults.
- Treat `data/tei`, `data/img`, and generated files under `public/viewer` as content pipelines, not normal source code.
- Avoid editing `vendor/` directly. If bundle behavior must change, look for an app controller, service decoration, event listener, or config override first.
- Keep changes compatible with the current Symfony setup; this repo is already partway through annotation-to-attribute cleanup.

## Validation

- Install dependencies: `composer install`
- Local server: `symfony server:start`
- Database setup: `bin/console doctrine:database:create` and `bin/console doctrine:schema:create`
- Static analysis: `vendor/bin/phpstan analyse`
- Formatting: `vendor/bin/php-cs-fixer fix`
- Refactoring support for attribute migration: `vendor/bin/rector process src config public`
- Useful framework checks: `bin/console debug:router`, `bin/console cache:clear`

For small changes, prefer validating only the touched path, for example `vendor/bin/phpstan analyse src/Service`.

## Environment Gotchas

- Search depends on Solr and does not work fully without the local core setup described in [README.md](README.md).
- Some content workflows require external tools and local paths: Java, Saxon JAR, and ImageMagick.
- Several Composer dependencies come from custom VCS repositories. Avoid unnecessary dependency churn and expect network-sensitive installs.
- Environment variables such as `DATABASE_URL`, `ZOTERO_API_KEY`, `JAVA_BIN`, `IMAGEMAGICK_PATH`, and Datacite settings can change runtime behavior significantly.

## Typical Agent Workflow

- Read [README.md](README.md) for setup or content-import tasks before changing code.
- For UI or request behavior, inspect the matching controller in [src/Controller](src/Controller), then the related Twig template in [templates](templates), then any route override in [config/routes.yaml](config/routes.yaml).
- For service-level behavior, start in [src/Service](src/Service) or [src/Utils](src/Utils), then confirm whether [config/services.yaml](config/services.yaml) adds explicit wiring.
- For search or bibliography issues, verify whether the problem is code, missing Solr state, or missing external credentials before editing.