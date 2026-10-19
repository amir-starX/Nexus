> **برای نسخه فارسی به پایین اسکرول کنید.**
> **Scroll down for Persian version.**

---

# Nexus Framework — Complete Documentation

**Version:** 1.0
**PHP:** 8.2+
**License:** MIT

Nexus is a configuration-driven, multi-paradigm PHP framework. A single file — `config/main.json` — controls architecture, routing, DI, adapters, plugins, and the error panel. You can switch architectural patterns globally, per-route, per-module, or run several at once inside the same request. Existing frameworks are integrated through adapters rather than rewritten.

---

## Table of Contents

1. Introduction
2. Requirements & Installation
3. Project Structure
4. The Single Source of Truth: `main.json`
5. Request Lifecycle
6. Architectural Patterns (Kernels)
7. Hot-Switching Paradigms
8. Coexistence (Compose Mode)
9. Routing
10. Dependency Injection Container
11. Service Providers
12. Adapters
13. Plugin Bridges & Auto-Install
14. Error Panel
15. Handlers & Responses
16. Environment & Configuration Loading
17. Apache Setup
18. Examples
19. Extending the Framework
20. Packaging & Deployment
21. Troubleshooting

---

## 1. Introduction

Nexus is not a framework that forces one architecture. It treats architecture as runtime configuration. The same codebase can serve an MVC page, a CQRS API endpoint, an ADR report, and a hexagonal module — all in one deployment, all driven by `main.json`.

Core principles:

- **Configuration over code.** Behavior changes come from `main.json`, not from editing PHP files.
- **Hot-switchable paradigms.** Global, per-route, per-module, and coexistence.
- **Adapters, not rewrites.** Symfony, Illuminate, Slim, Laminas, Doctrine, Twig, etc. are wrapped, not reimplemented.
- **Minimal core.** Small, strict, typed, PSR-compliant.
- **Themeable developer experience.** The error panel is fully driven by `main.json`.

---

## 2. Requirements & Installation

**Requirements**

- PHP 8.2 or newer
- Composer
- Apache with `mod_rewrite` enabled
- Writable `storage/cache` and `storage/logs`

**Installation**

```
git clone <repo> project
cd project
composer install
cp .env.example .env
```

Edit `.env`:

```
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:CHANGE_ME
APP_URL=http://localhost
CONFIG_PATH=config/main.json
```

Point Apache DocumentRoot to `public/`, or access it as `http://localhost/project/public/`.

---

## 3. Project Structure

```
project/
├── app/                    Application code (namespace App\)
│   ├── Controller/
│   ├── Cqrs/
│   ├── Module/
│   ├── Provider/
│   └── Kernel/
├── config/
│   ├── main.json           Primary configuration
│   └── main.composite.json Alternative (compose mode)
├── public/
│   ├── index.php           Front controller
│   └── .htaccess           Rewrite rules
├── src/                    Framework core (namespace Nexus\)
│   ├── Adapters/
│   ├── Bootstrap/
│   ├── Config/
│   ├── Container/
│   ├── Contracts/
│   ├── ErrorPanel/
│   ├── Exception/
│   ├── Http/
│   ├── Kernel/
│   ├── Kernels/            Pattern implementations
│   ├── Plugins/
│   ├── Routing/
│   └── Support/
├── storage/
│   ├── cache/
│   └── logs/
├── .env
├── .env.example
├── composer.json
└── README.md
```

---

## 4. The Single Source of Truth: `main.json`

Everything is driven by `config/main.json`. Top-level sections:

| Section | Purpose |
|---|---|
| `app` | Name, environment, debug, key |
| `architecture` | Default pattern, per-route, per-module, compose chain |
| `routing` | Route definitions |
| `container` | Container bindings |
| `providers` | Service provider classes |
| `adapters` | Enabled adapters (list of names) |
| `adapters_options` | Per-adapter options (Doctrine, Eloquent, Twig, etc.) |
| `plugins` | Plugin bridges and auto-install flag |
| `error_panel` | Theme, widgets, enable flag |

Changing `main.json` alone changes runtime behavior. No PHP edits are required for the standard workflows.

---

## 5. Request Lifecycle

```
Apache
  └── public/index.php
        └── Application::create($basePath)
              ├── Env::load()
              ├── ConfigLoader::load()        ← main.json
              └── ContextFactory::build()
                    ├── ContainerFactory::build()
                    ├── RouterFactory::build()
                    ├── PatternResolver + KernelRegistry + CompositeKernel
                    ├── KernelLoader::register()       ← all pattern kernels
                    ├── AdapterRegistry::boot()        ← enabled adapters
                    ├── BridgeBootstrapper::boot()     ← plugin bridges
                    └── ProviderLoader::register/boot()
        └── Application::handle($uri, $method)
              └── Dispatcher::dispatch()
                    ├── Router::match()             ← returns Request
                    └── CompositeKernel::handle()
                          ├── PatternResolver::chain()   ← resolve pattern(s)
                          └── for each kernel in chain: Kernel::handle()
                                └── HandlerResolver::invoke()
                                      └── Response
        └── ResponseEmitter::send()
```

The request is matched once, the pattern chain is resolved once, and each kernel in the chain gets the same `KernelContext`. The previous kernel's `Response` is passed to the next as `RequestContext::previous`, allowing fallthrough composition.

---

## 6. Architectural Patterns (Kernels)

Every pattern implements `PatternKernelInterface`:

```php
interface PatternKernelInterface {
    public function name(): string;
    public function supports(RequestContext $ctx): bool;
    public function handle(KernelContext $context, RequestContext $ctx): Response;
}
```

Built-in kernels:

| Name | Semantics |
|---|---|
| `mvc` | Controller method handles the request |
| `hmvc` | Same as MVC plus module attribute propagation (`hmvc.module`) |
| `adr` | Action handles the request (handler acts as action) |
| `cqrs` | Dispatches a command through a `CommandBus` if present |
| `hexagonal` | Binds configured ports as request attributes, then invokes |
| `microkernel` | Boots every registered plugin, then invokes |
| `layered` | Runs configured layer pipeline (each layer wraps the next) |
| `event-driven` | Invokes handler, then dispatches queued events |
| `modular` | Boots all registered modules (or one specific module), then invokes |

Custom kernels can be registered through `architecture.kernels`:

```json
"architecture": {
  "kernels": {
    "my-pattern": "App\\Kernel\\MyPatternKernel"
  }
}
```

Any class implementing `PatternKernelInterface` is acceptable.

---

## 7. Hot-Switching Paradigms

**Global** — applies to everything:

```json
"architecture": { "default": "mvc" }
```

**Per-route** — keyed by path pattern:

```json
"architecture": {
  "routes": {
    "/api/users": "cqrs",
    "/admin": "hmvc",
    "/reports/{id}": "adr"
  }
}
```

A route can also specify its pattern inline:

```json
"routing": {
  "routes": {
    "/api/users": {
      "methods": ["POST"],
      "handler": "App\\Controller\\UsersController@create",
      "architecture": "cqrs"
    }
  }
}
```

**Per-module** — keyed by module name:

```json
"architecture": {
  "modules": {
    "Billing": "hexagonal",
    "Auth": "mvc"
  }
}
```

Resolution order:

1. Route-level `architecture` field.
2. Module match (`architecture.modules[route.module]`).
3. Path match (`architecture.routes[path]`).
4. Global default (`architecture.default`).

---

## 8. Coexistence (Compose Mode)

Coexistence runs multiple patterns in one request:

```json
"architecture": {
  "compose": ["mvc", "cqrs"],
  "strategy": "fallthrough"
}
```

When `compose` is non-empty, it overrides default/routes/modules for **all** requests. Each kernel in the chain receives the previous kernel's `Response` as `RequestContext::previous`. If a kernel returns a non-null response, the chain stops. `strategy: "fallthrough"` means each kernel decides whether to handle by returning a response or letting the next take over.

Typical composition: an MVC controller dispatches a `Command` by setting `$request->attributes['command']`. The `cqrs` kernel, seeing the command, dispatches it through the `CommandBus` and returns the result. One request, two patterns.

To toggle coexistence for the whole app, set `CONFIG_PATH=config/main.composite.json` in `.env`.

---

## 9. Routing

Routes are declared in `routing.routes`, keyed by path pattern:

```json
"routing": {
  "routes": {
    "/": { "methods": ["GET"], "handler": "App\\Controller\\HomeController@index" },
    "/reports/{id}": { "methods": ["GET"], "handler": "App\\Controller\\ReportController@show" }
  }
}
```

- `{param}` placeholders capture path segments and become `$request->attributes['params']['param']`.
- `methods` is an array; `["*"]` matches all verbs.
- `handler` is `"Class@method"` or a class name (for `__invoke`).
- `architecture` (optional) overrides the pattern for that route.
- `module` (optional) associates the route with a module for per-module switching.

Route matching is linear in declaration order.

---

## 10. Dependency Injection Container

`Nexus\Container\Container` is a small PSR-11-compatible container with auto-wiring.

```php
$container->set(Foo::class, fn ($c) => new Foo($c->get(Bar::class)));
$container->instance(Bar::class, new Bar());
$container->get(Foo::class);       // resolves
$container->has(Foo::class);       // true
```

Bindings from `main.json`:

```json
"container": {
  "bindings": {
    "App\\Contract\\Mailer": "App\\Mail\\SmtpMailer"
  }
}
```

Circular dependencies are detected and throw `ContainerException`. Non-instantiable classes throw as well. Missing services auto-wire if the class exists.

---

## 11. Service Providers

Service providers follow the standard register/boot split:

```php
interface ServiceProviderInterface {
    public function register(KernelContext $context): void;
    public function boot(KernelContext $context): void;
}
```

Register them in `main.json`:

```json
"providers": [
  "App\\Provider\\AppServiceProvider"
]
```

`register()` runs before `boot()`. All providers register before any boot, matching Laravel semantics.

---

## 12. Adapters

Adapters wire third-party packages into the container without rewriting them. Enable them in `main.json`:

```json
"adapters": ["illuminate", "symfony", "twig", "monolog"]
```

Supported adapters:

| Name | Package | Service ID |
|---|---|---|
| `illuminate` | illuminate/container | `illuminate.container` |
| `symfony` | symfony/event-dispatcher, http-foundation | `symfony.event_dispatcher`, `symfony.request` |
| `slim` | slim/slim | `slim.app` |
| `laminas` | laminas/laminas-servicemanager, laminas-diactoros | `laminas.service_manager`, `laminas.request` |
| `doctrine` | doctrine/orm | `doctrine.entity_manager` |
| `eloquent` | illuminate/database | `eloquent.capsule` |
| `twig` | twig/twig | `twig` |
| `blade` | illuminate/view | `blade.factory` |
| `latte` | latte/latte | `latte` |
| `php-di` | php-di/php-di | `phpdi.container` |
| `monolog` | monolog/monolog | `monolog` |
| `fastroute` | nikic/fast-route | `fastroute.dispatcher` |
| `league` | league/container | `league.container` |

Per-adapter options go in `adapters_options`:

```json
"adapters_options": {
  "twig": { "paths": ["resources/views"], "cache": "storage/cache/twig" },
  "eloquent": { "driver": "mysql", "host": "localhost", "database": "app", "username": "root", "password": "" },
  "monolog": { "path": "storage/logs/app.log", "level": 100 }
}
```

Adapters silently skip if the underlying package is not installed — unless auto-install is enabled.

---

## 13. Plugin Bridges & Auto-Install

A plugin bridge is a declared integration with third-party code:

```php
interface PluginBridgeInterface {
    public function name(): string;
    public function requires(): array;     // composer packages
    public function register(KernelContext $context): void;
}
```

Register in `main.json`:

```json
"plugins": {
  "bridges": [
    { "class": "Vendor\\Bridge\\StripeBridge", "requires": ["stripe/stripe-php"] }
  ],
  "auto_install": true
}
```

If `auto_install` is `true`, missing packages listed in `requires` are installed via `composer require` at boot time. In production, prefer setting `auto_install: false` and installing manually.

---

## 14. Error Panel

The error panel appears when `app.debug = true` and `error_panel.enabled = true`. It renders exceptions as an HTML page with optional widgets.

```json
"error_panel": {
  "enabled": true,
  "theme": {
    "primary": "#6d28d9",
    "background": "#0f172a",
    "foreground": "#e2e8f0",
    "accent": "#22d3ee",
    "logo": "https://example.com/logo.svg"
  },
  "widgets": {
    "stack_trace": true,
    "query_log": true,
    "request_data": true,
    "timings": true,
    "resolved_pattern": true,
    "config_state": true
  }
}
```

All theming comes from this object. `logo` accepts a URL or null. Widgets can be toggled individually. In production (`app.debug = false`) the panel is disabled and a plain "Internal Server Error" is returned.

---

## 15. Handlers & Responses

A handler is either a callable, an invokable class, or a `Class@method` string. All are passed `(Request $request, KernelContext $context)`.

Return values are normalized:

| Return type | Response |
|---|---|
| `Nexus\Http\Response` | Passed through |
| `array` or `object` | JSON-encoded (`Response::json`) |
| `string` | Plain text (`Response::text`) |

`Response` constructors:

```php
Response::html($body, $status = 200, $headers = []);
Response::json($data, $status = 200, $headers = []);
Response::text($body, $status = 200, $headers = []);
Response::notFound();
```

---

## 16. Environment & Configuration Loading

`.env` is loaded via `vlucas/phpdotenv` in `Env::load()`. Access values through `nexus_env('KEY', $default)` or `Env::get()`. Values are typed: `true`, `false`, `null`, `empty` are converted.

`CONFIG_PATH` selects the active `main.json`. This is the primary way to toggle between configurations (e.g. `main.json` vs `main.composite.json`) without touching PHP.

---

## 17. Apache Setup

DocumentRoot → `public/`. Required `.htaccess` (already in `public/`):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>
```

If you cannot set DocumentRoot, URL form `http://localhost/project/public/` works because `.htaccess` rewrites all requests to `index.php`.

---

## 18. Examples

**Single pattern (MVC)**

```json
"architecture": { "default": "mvc" }
```

**Per-route switch**

```json
"architecture": {
  "routes": {
    "/api/users": "cqrs",
    "/reports/{id}": "adr"
  }
}
```

**Per-module switch**

```json
"architecture": {
  "modules": { "Billing": "hexagonal", "Auth": "mvc" }
}
```

**Coexistence**

```json
"architecture": {
  "compose": ["mvc", "cqrs"],
  "strategy": "fallthrough"
}
```

An MVC controller sets `$request->attributes['command']` and delegates to the `cqrs` kernel; the `CommandBus` dispatches it; the response goes back through the chain.

---

## 19. Extending the Framework

**Add a custom pattern kernel**

1. Implement `Nexus\Contracts\PatternKernelInterface`.
2. Register it in `main.json`: `"architecture": { "kernels": { "my-kernel": "App\\Kernel\\MyKernel" } }`.

**Add a custom adapter**

1. Extend `Nexus\Adapters\Adapter`.
2. Register the class in `AdapterRegistry::$classes`.
3. Enable it in `main.json` `"adapters": [...]`.

**Add a custom service provider**

1. Implement `Nexus\Bootstrap\ServiceProviderInterface`.
2. Add the class name to `"providers": [...]`.

**Add a custom plugin bridge**

1. Implement `Nexus\Contracts\PluginBridgeInterface`.
2. Add a `{ "class": "...", "requires": [...] }` entry to `plugins.bridges`.

**Add a custom module (modular pattern)**

1. Implement `Nexus\Kernels\Modular\ModuleInterface`.
2. Register it during boot in a provider:

```php
$registry = $context->container->get(ModuleRegistry::class);
$registry->register(new BillingModule());
```

---

## 20. Packaging & Deployment

**Build a deployable archive** (exclude vendor and runtime state):

```
zip -r project.zip . -x "vendor/*" "storage/*" ".env" ".git/*"
```

Windows PowerShell:

```
Compress-Archive -Path * -DestinationPath project.zip -Force
```

**On the target server**

```
unzip project.zip -d project
cd project
composer install --no-dev --optimize-autoloader
cp .env.example .env
# edit .env: APP_ENV=production, APP_DEBUG=false, CONFIG_PATH=config/main.json
chmod -R 775 storage/
```

Set DocumentRoot to `public/`. Done.

---

## 21. Troubleshooting

| Symptom | Cause | Fix |
|---|---|---|
| `Class "Nexus\\Bootstrap\\KernelLoader" not found` | Missing `use` import in `ContextFactory.php` | Add `use Nexus\Kernel\KernelLoader;` |
| 404 on all routes | `.htaccess` not honored | Enable `mod_rewrite` and `AllowOverride All` |
| 500 without HTML panel | `app.debug = false` | Set `APP_DEBUG=true` for local |
| Empty response on a route | Handler returned `null` | Return a string, array, or `Response` |
| Container circular dependency | Two services require each other | Break the cycle with a factory |
| `No route matched` | Path mismatch | Check `routing.routes` and `methods` |

---

**End of English documentation.**

---

<div dir="rtl" align="right">

# مستندات کامل فریمورک Nexus

**نسخه:** ۱.۰
**PHP:** 8.2 به بالا
**مجوز:** MIT

Nexus یک فریمورک PHP مبتنی بر پیکربندی و چند-پارادایمی است. یک فایل — `config/main.json` — معماری، مسیریابی، تزریق وابستگی، آداپترها، پلاگین‌ها و پنل خطا را کنترل می‌کند. می‌توانید الگوی معماری را به‌صورت سراسری، per-route، per-module یا هم‌زمان چند الگو در یک request تغییر دهید. فریمورک‌های موجود از طریق آداپتر ادغام می‌شوند، نه بازنویسی.

---

## فهرست

۱. مقدمه
۲. پیش‌نیاز و نصب
۳. ساختار پروژه
۴. تنها منبع حقیقت: `main.json`
۵. چرخه حیات request
۶. الگوهای معماری (Kernelها)
۷. سوییچ داغ الگوها
۸. همزیستی (حالت Compose)
۹. مسیریابی
۱۰. کانتینر تزریق وابستگی
۱۱. Service Providerها
۱۲. آداپترها
۱۳. پل‌های پلاگین و نصب خودکار
۱۴. پنل خطا
۱۵. هندلرها و Response
۱۶. بارگذاری محیط و پیکربندی
۱۷. تنظیم آپاچی
۱۸. مثال‌ها
۱۹. توسعه فریمورک
۲۰. بسته‌بندی و استقرار
۲۱. عیب‌یابی

---

## ۱. مقدمه

Nexus فریمورکی نیست که شما را به یک معماری خاص محدود کند. معماری را به‌عنوان پیکربندی زمان اجرا در نظر می‌گیرد. همان کد می‌تواند یک صفحه MVC، یک endpoint API با CQRS، یک گزارش ADR و یک ماژول hexagonal را هم‌زمان در یک استقرار ارائه دهد — همه از طریق `main.json`.

اصول اصلی:

- **پیکربندی به‌جای کد.** تغییرات رفتار از `main.json` می‌آید، نه ویرایش فایل‌های PHP.
- **الگوهای قابل سوییچ داغ.** سراسری، per-route، per-module و همزیستی.
- **آداپتر به‌جای بازنویسی.** Symfony، Illuminate، Slim، Laminas، Doctrine، Twig و … بسته‌بندی می‌شوند نه بازنویسی.
- **هسته مینیمال.** کوچک، strict، typed، سازگار با PSR.
- **تجربه توسعه‌دهنده با تم قابل تنظیم.** پنل خطا کاملاً از `main.json` هدایت می‌شود.

---

## ۲. پیش‌نیاز و نصب

**پیش‌نیازها**

- PHP 8.2 یا جدیدتر
- Composer
- Apache با `mod_rewrite` فعال
- دسترسی نوشتن به `storage/cache` و `storage/logs`

**نصب**

```
git clone <repo> project
cd project
composer install
cp .env.example .env
```

ویرایش `.env`:

```
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:CHANGE_ME
APP_URL=http://localhost
CONFIG_PATH=config/main.json
```

DocumentRoot آپاچی را به `public/` تنظیم کنید، یا از مسیر `http://localhost/project/public/` استفاده کنید.

---

## ۳. ساختار پروژه

```
project/
├── app/                    کد برنامه (namespace App\)
│   ├── Controller/
│   ├── Cqrs/
│   ├── Module/
│   ├── Provider/
│   └── Kernel/
├── config/
│   ├── main.json           پیکربندی اصلی
│   └── main.composite.json حالت compose
├── public/
│   ├── index.php           فرانت کنترلر
│   └── .htaccess           قواعد rewrite
├── src/                    هسته فریمورک (namespace Nexus\)
│   ├── Adapters/
│   ├── Bootstrap/
│   ├── Config/
│   ├── Container/
│   ├── Contracts/
│   ├── ErrorPanel/
│   ├── Exception/
│   ├── Http/
│   ├── Kernel/
│   ├── Kernels/            پیاده‌سازی الگوها
│   ├── Plugins/
│   ├── Routing/
│   └── Support/
├── storage/
│   ├── cache/
│   └── logs/
├── .env
├── .env.example
├── composer.json
└── README.md
```

---

## ۴. تنها منبع حقیقت: `main.json`

همه‌چیز از `config/main.json` هدایت می‌شود. بخش‌های سطح بالا:

| بخش | کاربرد |
|---|---|
| `app` | نام، محیط، debug، کلید |
| `architecture` | الگوی پیش‌فرض، per-route، per-module، زنجیره compose |
| `routing` | تعریف مسیرها |
| `container` | bindingهای کانتینر |
| `providers` | کلاس‌های service provider |
| `adapters` | آداپترهای فعال (لیست نام‌ها) |
| `adapters_options` | تنظیمات هر آداپتر (Doctrine، Eloquent، Twig و …) |
| `plugins` | پل‌های پلاگین و پرچم نصب خودکار |
| `error_panel` | تم، ویجت‌ها، پرچم فعال‌سازی |

تغییر `main.json` به‌تنهایی رفتار runtime را عوض می‌کند. برای جریان‌های استاندارد نیازی به ویرایش PHP نیست.

---

## ۵. چرخه حیات request

```
Apache
  └── public/index.php
        └── Application::create($basePath)
              ├── Env::load()
              ├── ConfigLoader::load()        ← main.json
              └── ContextFactory::build()
                    ├── ContainerFactory::build()
                    ├── RouterFactory::build()
                    ├── PatternResolver + KernelRegistry + CompositeKernel
                    ├── KernelLoader::register()       ← همه Kernelها
                    ├── AdapterRegistry::boot()        ← آداپترهای فعال
                    ├── BridgeBootstrapper::boot()     ← پل‌های پلاگین
                    └── ProviderLoader::register/boot()
        └── Application::handle($uri, $method)
              └── Dispatcher::dispatch()
                    ├── Router::match()             ← بازگشت Request
                    └── CompositeKernel::handle()
                          ├── PatternResolver::chain()   ← resolve الگو(ها)
                          └── برای هر Kernel در زنجیره: Kernel::handle()
                                └── HandlerResolver::invoke()
                                      └── Response
        └── ResponseEmitter::send()
```

request یک بار match می‌شود، زنجیره الگو یک بار resolve می‌شود و هر Kernel در زنجیره همان `KernelContext` را می‌گیرد. `Response` قبلی به‌عنوان `RequestContext::previous` به Kernel بعدی داده می‌شود و امکان fallthrough را فراهم می‌کند.

---

## ۶. الگوهای معماری (Kernelها)

هر الگو `PatternKernelInterface` را پیاده می‌کند:

```php
interface PatternKernelInterface {
    public function name(): string;
    public function supports(RequestContext $ctx): bool;
    public function handle(KernelContext $context, RequestContext $ctx): Response;
}
```

Kernelهای داخلی:

| نام | معناشناسی |
|---|---|
| `mvc` | متد Controller درخواست را مدیریت می‌کند |
| `hmvc` | مثل MVC به‌همراه انتقال ماژول (`hmvc.module`) |
| `adr` | Action درخواست را مدیریت می‌کند (هندلر همان action است) |
| `cqrs` | در صورت وجود `CommandBus`، command را ارسال می‌کند |
| `hexagonal` | پورت‌های پیکربندی‌شده را به‌عنوان attribute به request می‌چسباند و سپس فراخوانی می‌کند |
| `microkernel` | همه پلاگین‌های ثبت‌شده را boot می‌کند و سپس فراخوانی می‌کند |
| `layered` | پایپ‌لاین لایه‌های پیکربندی‌شده را اجرا می‌کند |
| `event-driven` | هندلر را فراخوانی و سپس رویدادهای صف‌شده را dispatch می‌کند |
| `modular` | همه ماژول‌ها (یا یک ماژول خاص) را boot می‌کند و فراخوانی می‌کند |

Kernel سفارشی از طریق `architecture.kernels` ثبت می‌شود:

```json
"architecture": {
  "kernels": {
    "my-pattern": "App\\Kernel\\MyPatternKernel"
  }
}
```

هر کلاسی که `PatternKernelInterface` را پیاده کند پذیرفته می‌شود.

---

## ۷. سوییچ داغ الگوها

**سراسری** — برای همه:

```json
"architecture": { "default": "mvc" }
```

**per-route** — کلید بر اساس الگوی مسیر:

```json
"architecture": {
  "routes": {
    "/api/users": "cqrs",
    "/admin": "hmvc",
    "/reports/{id}": "adr"
  }
}
```

مسیر می‌تواند الگو را درون خود تعریف کند:

```json
"routing": {
  "routes": {
    "/api/users": {
      "methods": ["POST"],
      "handler": "App\\Controller\\UsersController@create",
      "architecture": "cqrs"
    }
  }
}
```

**per-module** — کلید بر اساس نام ماژول:

```json
"architecture": {
  "modules": {
    "Billing": "hexagonal",
    "Auth": "mvc"
  }
}
```

ترتیب resolve:

۱. فیلد `architecture` در سطح مسیر.
۲. تطابق ماژول (`architecture.modules[route.module]`).
۳. تطابق مسیر (`architecture.routes[path]`).
۴. پیش‌فرض سراسری (`architecture.default`).

---

## ۸. همزیستی (حالت Compose)

همزیستی چند الگو را در یک request اجرا می‌کند:

```json
"architecture": {
  "compose": ["mvc", "cqrs"],
  "strategy": "fallthrough"
}
```

وقتی `compose` غیرخالی باشد، برای **همه** requestها جایگزین default/routes/modules می‌شود. هر Kernel در زنجیره `Response` قبلی را به‌عنوان `RequestContext::previous` می‌گیرد. اگر Kernel پاسخی برگرداند، زنجیره متوقف می‌شود. `strategy: "fallthrough"` یعنی هر Kernel خودش تصمیم می‌گیرد پاسخ بدهد یا به بعدی واگذار کند.

ترکیب معمول: یک کنترلر MVC با تنظیم `$request->attributes['command']` یک `Command` ارسال می‌کند. Kernel `cqrs` با دیدن command آن را از طریق `CommandBus` ارسال و نتیجه را برمی‌گرداند. یک request، دو الگو.

برای سوییچ سراسری به حالت همزیستی، در `.env` مقدار `CONFIG_PATH=config/main.composite.json` را بگذارید.

---

## ۹. مسیریابی

مسیرها در `routing.routes` با کلید الگوی مسیر تعریف می‌شوند:

```json
"routing": {
  "routes": {
    "/": { "methods": ["GET"], "handler": "App\\Controller\\HomeController@index" },
    "/reports/{id}": { "methods": ["GET"], "handler": "App\\Controller\\ReportController@show" }
  }
}
```

- `{param}` یک segment مسیر را می‌گیرد و به `$request->attributes['params']['param']` تبدیل می‌شود.
- `methods` یک آرایه است؛ `["*"]` همه متدها را می‌پذیرد.
- `handler` به‌صورت `"Class@method"` یا نام کلاس (برای `__invoke`) است.
- `architecture` (اختیاری) الگوی همان مسیر را override می‌کند.
- `module` (اختیاری) مسیر را به یک ماژول برای سوییچ per-module متصل می‌کند.

تطابق مسیر به ترتیب تعریف، خطی است.

---

## ۱۰. کانتینر تزریق وابستگی

`Nexus\Container\Container` یک کانتینر کوچک سازگار با PSR-11 با auto-wiring است.

```php
$container->set(Foo::class, fn ($c) => new Foo($c->get(Bar::class)));
$container->instance(Bar::class, new Bar());
$container->get(Foo::class);
$container->has(Foo::class);
```

bindingها از `main.json`:

```json
"container": {
  "bindings": {
    "App\\Contract\\Mailer": "App\\Mail\\SmtpMailer"
  }
}
```

وابستگی چرخه‌ای تشخیص داده شده و `ContainerException` می‌دهد. کلاس‌های غیرقابل نمونه‌سازی هم استثنا می‌دهند. سرویس‌های غایب در صورت وجود کلاس auto-wire می‌شوند.

---

## ۱۱. Service Providerها

Service providerها از الگوی register/boot پیروی می‌کنند:

```php
interface ServiceProviderInterface {
    public function register(KernelContext $context): void;
    public function boot(KernelContext $context): void;
}
```

در `main.json` ثبت کنید:

```json
"providers": [
  "App\\Provider\\AppServiceProvider"
]
```

`register()` قبل از `boot()` اجرا می‌شود. همه providerها قبل از هر boot ثبت می‌شوند (مطابق معناشناسی Laravel).

---

## ۱۲. آداپترها

آداپترها پکیج‌های third-party را بدون بازنویسی به کانتینر وصل می‌کنند. در `main.json` فعال کنید:

```json
"adapters": ["illuminate", "symfony", "twig", "monolog"]
```

آداپترهای پشتیبانی‌شده:

| نام | پکیج | Service ID |
|---|---|---|
| `illuminate` | illuminate/container | `illuminate.container` |
| `symfony` | symfony/event-dispatcher, http-foundation | `symfony.event_dispatcher`, `symfony.request` |
| `slim` | slim/slim | `slim.app` |
| `laminas` | laminas/laminas-servicemanager, laminas-diactoros | `laminas.service_manager`, `laminas.request` |
| `doctrine` | doctrine/orm | `doctrine.entity_manager` |
| `eloquent` | illuminate/database | `eloquent.capsule` |
| `twig` | twig/twig | `twig` |
| `blade` | illuminate/view | `blade.factory` |
| `latte` | latte/latte | `latte` |
| `php-di` | php-di/php-di | `phpdi.container` |
| `monolog` | monolog/monolog | `monolog` |
| `fastroute` | nikic/fast-route | `fastroute.dispatcher` |
| `league` | league/container | `league.container` |

تنظیمات هر آداپتر در `adapters_options`:

```json
"adapters_options": {
  "twig": { "paths": ["resources/views"], "cache": "storage/cache/twig" },
  "eloquent": { "driver": "mysql", "host": "localhost", "database": "app", "username": "root", "password": "" },
  "monolog": { "path": "storage/logs/app.log", "level": 100 }
}
```

اگر پکیج نصب نباشد، آداپتر بی‌صدا رد می‌شود — مگر اینکه نصب خودکار فعال باشد.

---

## ۱۳. پل‌های پلاگین و نصب خودکار

پل پلاگین یک ادغام اعلام‌شده با کد third-party است:

```php
interface PluginBridgeInterface {
    public function name(): string;
    public function requires(): array;     // پکیج‌های composer
    public function register(KernelContext $context): void;
}
```

در `main.json` ثبت کنید:

```json
"plugins": {
  "bridges": [
    { "class": "Vendor\\Bridge\\StripeBridge", "requires": ["stripe/stripe-php"] }
  ],
  "auto_install": true
}
```

اگر `auto_install` برابر `true` باشد، پکیج‌های غایب فهرست‌شده در `requires` در زمان boot از طریق `composer require` نصب می‌شوند. در production ترجیحاً `auto_install: false` و نصب دستی.

---

## ۱۴. پنل خطا

پنل خطا زمانی نمایش داده می‌شود که `app.debug = true` و `error_panel.enabled = true`. استثناها را به‌صورت HTML با ویجت‌های اختیاری رندر می‌کند.

```json
"error_panel": {
  "enabled": true,
  "theme": {
    "primary": "#6d28d9",
    "background": "#0f172a",
    "foreground": "#e2e8f0",
    "accent": "#22d3ee",
    "logo": "https://example.com/logo.svg"
  },
  "widgets": {
    "stack_trace": true,
    "query_log": true,
    "request_data": true,
    "timings": true,
    "resolved_pattern": true,
    "config_state": true
  }
}
```

تمام تم از این شیء می‌آید. `logo` یک URL یا `null` می‌پذیرد. ویجت‌ها تک‌تک قابل خاموش/روشن هستند. در production (`app.debug = false`) پنل غیرفعال است و «Internal Server Error» ساده برمی‌گردد.

---

## ۱۵. هندلرها و Response

هندلر یا callable است، یا کلاس invokable، یا رشته `Class@method`. همه `(Request $request, KernelContext $context)` دریافت می‌کنند.

مقادیر بازگشتی نرمال می‌شوند:

| نوع بازگشتی | Response |
|---|---|
| `Nexus\Http\Response` | عبور مستقیم |
| `array` یا `object` | تبدیل به JSON (`Response::json`) |
| `string` | متن ساده (`Response::text`) |

سازنده‌های `Response`:

```php
Response::html($body, $status = 200, $headers = []);
Response::json($data, $status = 200, $headers = []);
Response::text($body, $status = 200, $headers = []);
Response::notFound();
```

---

## ۱۶. بارگذاری محیط و پیکربندی

`.env` از طریق `vlucas/phpdotenv` در `Env::load()` بارگذاری می‌شود. مقادیر را با `nexus_env('KEY', $default)` یا `Env::get()` بخوانید. مقادیر typed می‌شوند: `true`، `false`، `null`، `empty` تبدیل می‌شوند.

`CONFIG_PATH` فایل `main.json` فعال را انتخاب می‌کند. این اصلی‌ترین راه جابه‌جایی بین پیکربندی‌ها (مثلاً `main.json` و `main.composite.json`) بدون دست زدن به PHP است.

---

## ۱۷. تنظیم آپاچی

DocumentRoot → `public/`. فایل `.htaccess` لازم (از قبل در `public/` هست):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>
```

اگر امکان تنظیم DocumentRoot نیست، فرم `http://localhost/project/public/` کار می‌کند چون `.htaccess` همه را به `index.php` rewrite می‌کند.

---

## ۱۸. مثال‌ها

**تک‌الگو (MVC)**

```json
"architecture": { "default": "mvc" }
```

**سوییچ per-route**

```json
"architecture": {
  "routes": {
    "/api/users": "cqrs",
    "/reports/{id}": "adr"
  }
}
```

**سوییچ per-module**

```json
"architecture": {
  "modules": { "Billing": "hexagonal", "Auth": "mvc" }
}
```

**همزیستی**

```json
"architecture": {
  "compose": ["mvc", "cqrs"],
  "strategy": "fallthrough"
}
```

یک کنترلر MVC با تنظیم `$request->attributes['command']` به Kernel `cqrs` واگذار می‌کند؛ `CommandBus` آن را ارسال می‌کند؛ پاسخ از زنجیره برمی‌گردد.

---

## ۱۹. توسعه فریمورک

**افزودن Kernel سفارشی**

۱. `Nexus\Contracts\PatternKernelInterface` را پیاده کنید.
۲. در `main.json` ثبت کنید: `"architecture": { "kernels": { "my-kernel": "App\\Kernel\\MyKernel" } }`.

**افزودن آداپتر سفارشی**

۱. از `Nexus\Adapters\Adapter` ارث‌بری کنید.
۲. کلاس را در `AdapterRegistry::$classes` ثبت کنید.
۳. در `"adapters": [...]` فعال کنید.

**افزودن Service Provider سفارشی**

۱. `Nexus\Bootstrap\ServiceProviderInterface` را پیاده کنید.
۲. نام کلاس را به `"providers": [...]` بیفزایید.

**افزودن پل پلاگین سفارشی**

۱. `Nexus\Contracts\PluginBridgeInterface` را پیاده کنید.
۲. یک ورودی `{ "class": "...", "requires": [...] }` به `plugins.bridges` بیفزایید.

**افزودن ماژول سفارشی (الگوی modular)**

۱. `Nexus\Kernels\Modular\ModuleInterface` را پیاده کنید.
۲. در زمان boot در یک provider ثبت کنید:

```php
$registry = $context->container->get(ModuleRegistry::class);
$registry->register(new BillingModule());
```

---

## ۲۰. بسته‌بندی و استقرار

**ساخت آرشیو قابل استقرار** (بدون vendor و state):

```
zip -r project.zip . -x "vendor/*" "storage/*" ".env" ".git/*"
```

Windows PowerShell:

```
Compress-Archive -Path * -DestinationPath project.zip -Force
```

**روی سرور مقصد**

```
unzip project.zip -d project
cd project
composer install --no-dev --optimize-autoloader
cp .env.example .env
# ویرایش .env: APP_ENV=production, APP_DEBUG=false, CONFIG_PATH=config/main.json
chmod -R 775 storage/
```

DocumentRoot را به `public/` تنظیم کنید. تمام.

---

## ۲۱. عیب‌یابی

| نشانه | علت | رفع |
|---|---|---|
| `Class "Nexus\\Bootstrap\\KernelLoader" not found` | نبود `use` در `ContextFactory.php` | افزودن `use Nexus\Kernel\KernelLoader;` |
| 404 روی همه مسیرها | `.htaccess` اجرا نمی‌شود | فعال‌سازی `mod_rewrite` و `AllowOverride All` |
| 500 بدون پنل HTML | `app.debug = false` | برای local `APP_DEBUG=true` |
| پاسخ خالی روی یک مسیر | هندلر `null` برگرداند | برگرداندن string، array یا `Response` |
| وابستگی چرخه‌ای در کانتینر | دو سرویس یکدیگر را می‌خواهند | شکستن چرخه با factory |
| `No route matched` | عدم تطابق مسیر | بررسی `routing.routes` و `methods` |

---

**پایان مستندات فارسی.**

</div>