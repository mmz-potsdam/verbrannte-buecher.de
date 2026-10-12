<<<<<<< ours
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
=======
# AGENTS.md

This is a Symfony project. Check `composer.json` for the exact Symfony/PHP version
in use, and read `symfony.lock` to see which recipes ran. Don't assume Doctrine,
Twig, API Platform, Messenger, or Lock are installed unless one of those says so.

## Ask before generating

If the task doesn't specify, ask rather than guess:

- Persistence: Doctrine ORM, Doctrine ODM, or none?
- Interface: server-rendered (Twig), API (Serializer, maybe API Platform), or both?
- Auth: SecurityBundle, and which authenticator?

If you can't ask (no interactive channel), state the assumption you're making and
pick the smallest option (e.g. no persistence layer) rather than scaffolding a
full stack nobody asked for.

## Adding features: Flex, not hand-wiring

Install new capabilities with `composer require <package>` (e.g. `symfony/lock`,
`symfony/messenger`, `orm-pack`) and let the Flex recipe register the bundle and
generate its config. Don't hand-edit `config/bundles.php` or hand-write a bundle's
base config; that's what the recipe is for. Don't skip a good-fit component just
because it isn't installed yet; installing it is one command.

## Conventions

Follow https://symfony.com/doc/current/best_practices.html to write idiomatic
Symfony:

- Use PHP attributes for framework metadata, and not only on controllers:
  `#[Route]`, `#[MapRequestPayload]`, `#[IsGranted]` on actions, `#[Assert\...]`
  on properties, `#[AsCommand]`, `#[AsEventListener]`, `#[AsMessageHandler]`, and
  `#[AsAlias]` / `#[AsTaggedItem]` / `#[Autoconfigure]` on services. No YAML or
  XML routing.
- Rely on autowiring and autoconfiguration. Type-hint constructor arguments and
  let the container resolve them. Where a type-hint can't express it, stay in the
  class with `#[Autowire]` (parameters, env vars, expressions) or `#[Target]` (one
  of several implementations of an interface). A YAML service definition is the
  last resort, not the first.
- Controllers extend `AbstractController`, stay thin, and delegate to services.
- Use the framework for what it already does: Form for server-rendered forms,
  Validator for validation, Serializer for JSON, Messenger for async work,
  Security (voters, authenticators) for access control, Twig `path()`/`url()`
  instead of hardcoded URLs.
- Before hand-writing infrastructure (locks, queues, caches, HTTP clients,
  mailers, schedulers) or reaching for a third-party library, check whether a
  Symfony component covers it. It usually does.

Three specifics worth spelling out, because they are easy to get wrong:

- Bind request data with `#[MapRequestPayload]` / `#[MapQueryString]` on action
  arguments, which wires up Serializer and Validator for you, instead of calling
  `json_decode()` or `SerializerInterface` by hand. If neither package is
  installed yet, `composer require` them rather than falling back to manual
  parsing.
- Use constructor property promotion, and `readonly` for DTOs and value objects.
  Don't mark a service `readonly` if it might become `lazy: true`: a lazy proxy
  can't extend a `readonly` class.
- Use `symfony/lock` (`LockFactory`) for mutual exclusion. A hand-built flag or
  lock file looks fine in review and is usually wrong under concurrency.

## Everyday workflow

- Run the app with `symfony serve -d`, and commands with `symfony console ...`
  (or `bin/console` when the Symfony CLI isn't available).
- When something fails, read `var/log/dev.log` and the web profiler
  (`/_profiler`) before changing code.
- If `maker-bundle` is installed, prefer `bin/console make:*` with every argument
  passed up front and `--no-interaction` where supported: makers prompt on a
  terminal by default, which hangs a non-interactive shell. If a maker still
  needs interactive input, hand-write the code instead.
- If Doctrine ORM is installed, schema changes go through migrations
  (`bin/console make:migration`, then `doctrine:migrations:migrate`), never
  `doctrine:schema:update` or hand-written SQL.
- `.env` is committed and holds defaults only. Real secrets belong in `.env.local`
  (git-ignored) or the secrets vault (`bin/console secrets:set`), read via
  `%env(...)%`.

## Testing

Install `symfony/test-pack` if it isn't already. Functional/HTTP tests extend
`WebTestCase`; service-level tests extend `KernelTestCase`. Run
`php bin/phpunit` (falls back to `vendor/bin/phpunit`). A feature isn't done
until it has a test that exercises it the way a caller would, an HTTP request for
a controller or a service call for a service, not just "it didn't throw."

## Code style

Symfony's coding standard, the `@Symfony` php-cs-fixer ruleset (a PSR-12-derived
superset). Run `vendor/bin/php-cs-fixer fix` if `friendsofphp/php-cs-fixer` is
installed; it isn't part of the skeleton by default.

## Discover, don't guess

Framework APIs change between versions and your training data may be stale. Look
things up in the project instead of relying on memory:

- `bin/console about`: versions, environment, paths.
- `bin/console debug:router`, `debug:container`, `debug:autowiring <name>`,
  `debug:config <bundle>`, `config:dump-reference <bundle>`: what exists and how
  it is configured.
- `bin/console lint:container`, plus `lint:twig templates/` and
  `lint:yaml config/` where those packages are installed: validate before running.
- Read the installed source and docblocks under `vendor/`.
- Docs: https://symfony.com/doc/current/ (switch to the version matching
  `composer.json` if it differs).
>>>>>>> theirs
