# OmniCore — Complete Documentation

**Project:** OmniCore  
**Type:** Configuration-driven, multi-paradigm PHP framework  
**PHP:** 8.2+  
**Status:** Experimental / under active development  
**License:** See the repository's `LICENSE` file

> OmniCore is designed to let an application select and combine supported architectural patterns through configuration. It integrates selected third-party packages through adapters; it does not automatically provide full compatibility with every PHP framework.

**فارسی:** راهنمای فارسی پس از نسخهٔ انگلیسی آمده است.

## Table of contents

1. [Overview](#1-overview)
2. [Requirements and installation](#2-requirements-and-installation)
3. [Project structure](#3-project-structure)
4. [Configuration](#4-configuration)
5. [Request lifecycle](#5-request-lifecycle)
6. [Architecture kernels](#6-architecture-kernels)
7. [Selecting an architecture](#7-selecting-an-architecture)
8. [Compose mode](#8-compose-mode)
9. [Routing](#9-routing)
10. [Dependency injection](#10-dependency-injection)
11. [Service providers](#11-service-providers)
12. [Adapters](#12-adapters)
13. [Plugin bridges](#13-plugin-bridges)
14. [Development error panel](#14-development-error-panel)
15. [Handlers and responses](#15-handlers-and-responses)
16. [Environment variables](#16-environment-variables)
17. [Apache configuration](#17-apache-configuration)
18. [Extending OmniCore](#18-extending-omnicore)
19. [Deployment and security](#19-deployment-and-security)
20. [Troubleshooting](#20-troubleshooting)
21. [Contributing](#21-contributing)
22. [مجوز و وضعیت پروژه](#مجوز-و-وضعیت-پروژه)

---

## 1. Overview

OmniCore is a configuration-driven PHP framework intended to support more than one application architecture in the same codebase. Depending on the features implemented and enabled in the checked-out version, architecture selection can be configured globally, for a route, or for a module.

Design goals:

- **Configuration-driven behavior:** use `config/main.json` for supported runtime choices.
- **Multiple architecture patterns:** use a suitable kernel for each supported route or module.
- **Extensibility:** register custom kernels, service providers, adapters, and plugin bridges through the framework's contracts.
- **Integration rather than reimplementation:** connect selected third-party packages through adapters.
- **Separation of concerns:** keep application code separate from framework internals.

### Important scope note

The names of architecture patterns and third-party packages in this document describe integration points documented by the project. They should not be interpreted as a guarantee that all features of the named frameworks are implemented or interchangeable. Check the actual source, dependency list, and tests for the version you use.

## 2. Requirements and installation

### Requirements

- PHP 8.2 or newer
- Composer
- Apache with `mod_rewrite` enabled if using the Apache setup below
- Write access to runtime directories used by the application, such as `storage/cache` and `storage/logs`

Some adapters require additional Composer packages.

### Clone and install

```bash
git clone https://github.com/amir-starX/OmniCore.git
cd OmniCore
composer install
```

If the repository contains `.env.example`, create a local environment file:

```bash
cp .env.example .env
```

Review `.env` and `config/main.json` before running the application. Never commit real credentials or production secrets.

Example environment settings:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
CONFIG_PATH=config/main.json
```

Use the exact variables supported by the current source version. Do not assume a placeholder value such as `APP_KEY` is required unless the application or an enabled package actually uses it.

## 3. Project structure

The documented project layout is approximately:

```text
OmniCore/
├── app/                    Application code
│   ├── Controller/
│   ├── Cqrs/
│   ├── Module/
│   ├── Provider/
│   └── Kernel/
├── config/
│   ├── main.json
│   └── main.composite.json
├── public/
│   ├── index.php
│   └── .htaccess
├── src/                    Framework core
│   ├── Adapters/
│   ├── Bootstrap/
│   ├── Config/
│   ├── Container/
│   ├── Contracts/
│   ├── ErrorPanel/
│   ├── Exception/
│   ├── Http/
│   ├── Kernel/
│   ├── Kernels/
│   ├── Plugins/
│   ├── Routing/
│   └── Support/
├── storage/
│   ├── cache/
│   └── logs/
├── .env.example
├── composer.json
├── DOCUMENTATION.md
└── README.md
```

This is an illustrative tree based on the documented layout; use the actual repository tree as the source of truth if files have changed.

> **Namespace note:** some existing classes and examples may still use the `Nexus\` namespace from the project's earlier name. Renaming the GitHub repository does not rename PHP namespaces, Composer autoload mappings, or class references. Check `composer.json` and the source before changing namespaces.

## 4. Configuration

The primary configuration file is `config/main.json`. The documented top-level sections include:

| Section | Purpose |
|---|---|
| `app` | Application environment and debug settings |
| `architecture` | Default pattern, route/module overrides, custom kernels, composition |
| `routing` | Route definitions |
| `container` | Dependency-injection bindings |
| `providers` | Service provider classes |
| `adapters` | Enabled adapter names |
| `adapters_options` | Adapter-specific options |
| `plugins` | Plugin bridges and related options |
| `error_panel` | Development error-panel options |

### Configuration rules

- Keep JSON valid: double quotes are required for keys and string values, and comments are not allowed in standard JSON.
- Use only options supported by the current implementation.
- Validate configuration before deploying it.
- Keep credentials out of committed configuration files.
- Treat `CONFIG_PATH` as a path to the active configuration file, not as a way to enable features that have not been implemented.

A minimal illustrative architecture fragment:

```json
{
  "architecture": {
    "default": "mvc",
    "routes": {
      "/api/users": "cqrs",
      "/reports/{id}": "adr"
    },
    "modules": {
      "Billing": "hexagonal"
    },
    "compose": []
  }
}
```

This is a fragment, not a complete configuration file. Merge it into the structure expected by your checked-out version.

## 5. Request lifecycle

The documented high-level request flow is:

```text
Web server
  └── public/index.php
        └── Application bootstrap
              ├── Load environment
              ├── Load configuration
              ├── Build application context
              ├── Build container and router
              ├── Register architecture kernels
              ├── Boot enabled adapters and plugin bridges
              └── Register and boot service providers
        └── Match request route
              └── Resolve architecture kernel(s)
                    └── Resolve and invoke handler
                          └── Create response
        └── Emit response
```

This diagram is conceptual and may not represent every internal call in every version. Consult the implementation when debugging lifecycle order or relying on side effects.

## 6. Architecture kernels

Architecture kernels encapsulate request-handling strategies. The current documentation lists the following patterns:

| Pattern | Intended behavior |
|---|---|
| `mvc` | Invoke a controller handler |
| `hmvc` | MVC-style handling with module context |
| `adr` | Use an action as the request handler |
| `cqrs` | Dispatch a command when a compatible command bus is configured |
| `hexagonal` | Expose configured ports to the request handler |
| `microkernel` | Integrate registered plugins into request handling |
| `layered` | Process a request through a configured layer pipeline |
| `event-driven` | Dispatch queued events after handler execution |
| `modular` | Initialize registered modules before handling |

These labels describe intended behavior, not a guarantee that every pattern is production-ready or implements the full corresponding architectural methodology.

### Custom kernels

The documented extension point is `PatternKernelInterface`. Check the exact namespace and method signatures in the source before implementing a custom kernel; older examples may use the `Nexus\` namespace.

The configuration shape documented for registering a custom kernel is:

```json
{
  "architecture": {
    "kernels": {
      "my-pattern": "App\\Kernel\\MyPatternKernel"
    }
  }
}
```

This fragment must be merged into the main configuration file.

## 7. Selecting an architecture

### Default architecture

```json
{
  "architecture": {
    "default": "mvc"
  }
}
```

### Architecture by route

```json
{
  "architecture": {
    "routes": {
      "/api/users": "cqrs",
      "/admin": "hmvc",
      "/reports/{id}": "adr"
    }
  }
}
```

### Architecture by module

```json
{
  "architecture": {
    "modules": {
      "Billing": "hexagonal",
      "Auth": "mvc"
    }
  }
}
```

A route may also declare an `architecture` field inline if supported by the active routing implementation.

The documented resolution order is:

1. Architecture declared directly on the route.
2. Architecture selected for the route's module.
3. Matching architecture path rule.
4. Global default.

Confirm this precedence against the current implementation before relying on it in production.

## 8. Compose mode

Compose mode is documented as a way to run more than one kernel in a request chain.

Illustrative fragment:

```json
{
  "architecture": {
    "compose": ["mvc", "cqrs"],
    "strategy": "fallthrough"
  }
}
```

The existing documentation describes the `compose` list as taking precedence over the default, route, and module selections when enabled. It also describes a fallthrough strategy in which a kernel can allow the next kernel to handle the request.

**Important:** kernel composition is not automatically equivalent to combining two complete frameworks. Each kernel must agree on request context, response handling, error propagation, and whether the chain continues. Verify these behaviors with tests for your version.

If the repository includes `config/main.composite.json`, `CONFIG_PATH` can be set to that file to select it:

```dotenv
CONFIG_PATH=config/main.composite.json
```

## 9. Routing

The documented route format is a path-keyed map:

```json
{
  "routing": {
    "routes": {
      "/": {
        "methods": ["GET"],
        "handler": "App\\Controller\\HomeController@index"
      },
      "/reports/{id}": {
        "methods": ["GET"],
        "handler": "App\\Controller\\ReportController@show"
      }
    }
  }
}
```

Documented route fields:

- `methods`: HTTP methods; the existing documentation also describes `["*"]` as matching all methods.
- `handler`: a `Class@method` string or an invokable class, depending on the handler resolver.
- `architecture`: optional route-level architecture override, if supported.
- `module`: optional module identifier for module-level architecture selection, if supported.

The existing documentation states that route matching follows declaration order. Avoid overlapping route patterns unless you have verified which route takes precedence.

## 10. Dependency injection

The documented container supports service bindings and automatic construction of eligible classes. The existing examples use a PSR-11-oriented API.

Illustrative PHP:

```php
$container->set(
    App\Contract\Mailer::class,
    fn ($container) => new App\Mail\SmtpMailer()
);

$mailer = $container->get(App\Contract\Mailer::class);
```

Illustrative configuration binding:

```json
{
  "container": {
    "bindings": {
      "App\\Contract\\Mailer": "App\\Mail\\SmtpMailer"
    }
  }
}
```

Use the actual container API and namespace from the checked-out source. Automatic construction is not a replacement for explicit bindings when a service has scalar constructor arguments or other configuration requirements.

## 11. Service providers

Service providers are intended to register services and perform boot-time setup. The existing documentation describes separate `register()` and `boot()` phases.

Example configuration:

```json
{
  "providers": [
    "App\\Provider\\AppServiceProvider"
  ]
}
```

Implement the interface defined by the current source. Do not assume providers run in a particular order unless that order is documented and tested.

## 12. Adapters

Adapters are integration layers for selected third-party packages. The documented adapter names include:

- `illuminate`
- `symfony`
- `slim`
- `laminas`
- `doctrine`
- `eloquent`
- `twig`
- `blade`
- `latte`
- `php-di`
- `monolog`
- `fastroute`
- `league`

Example configuration:

```json
{
  "adapters": ["illuminate", "symfony", "twig", "monolog"]
}
```

Optional adapter settings are documented under `adapters_options`. For example:

```json
{
  "adapters_options": {
    "twig": {
      "paths": ["resources/views"],
      "cache": "storage/cache/twig"
    }
  }
}
```

The supported service IDs, required Composer packages, and option names are version-dependent. Consult the adapter implementation and `composer.json` before using an adapter.

**Compatibility limitation:** exposing a package through the container does not mean OmniCore can run that package's entire framework lifecycle or that all its components are interchangeable with another framework.

## 13. Plugin bridges

A plugin bridge is an integration point for a third-party package or service. The documented contract includes a name, a list of required Composer packages, and a registration method.

Example configuration shape:

```json
{
  "plugins": {
    "bridges": [
      {
        "class": "Vendor\\Bridge\\ExampleBridge",
        "requires": ["vendor/package"]
      }
    ],
    "auto_install": false
  }
}
```

Only use keys supported by the current implementation.

### Dependency installation warning

The older documentation describes an `auto_install` option that can run `composer require` during application startup. Automatically changing dependencies at runtime is risky and is not recommended for production. Prefer installing, reviewing, and locking dependencies during development or deployment, and keep automatic installation disabled unless you understand and explicitly accept its behavior.

A plugin bridge is **not** a security sandbox. Treat third-party plugin code as executable application code with the permissions of the PHP process.

## 14. Development error panel

The documented error panel can display exception details and optional diagnostic widgets when debugging is enabled.

Example configuration shape:

```json
{
  "error_panel": {
    "enabled": true,
    "theme": {
      "primary": "#6d28d9",
      "background": "#0f172a",
      "foreground": "#e2e8f0",
      "accent": "#22d3ee"
    },
    "widgets": {
      "stack_trace": true,
      "request_data": true,
      "timings": true
    }
  }
}
```

This is illustrative; use only the keys and widgets implemented by your version.

**Never expose stack traces, environment values, request secrets, SQL details, or configuration dumps to public users.** Disable debug mode and the development panel in production.

## 15. Handlers and responses

The existing documentation describes handlers as callables, invokable classes, or `Class@method` strings. It also describes normalizing common return values into responses.

The documented response helpers include:

```php
Response::html($body, $status = 200, $headers = []);
Response::json($data, $status = 200, $headers = []);
Response::text($body, $status = 200, $headers = []);
Response::notFound();
```

Verify the current `Response` class namespace, constructor signatures, supported return types, and handler arguments in source before copying this example into an application.

## 16. Environment variables

The existing documentation describes `.env` loading through `vlucas/phpdotenv` and selection of the active configuration through `CONFIG_PATH`.

Example:

```dotenv
APP_ENV=local
APP_DEBUG=true
CONFIG_PATH=config/main.json
```

- Use local environment files only for local development.
- Keep `.env` out of version control.
- Provide placeholders, not real secrets, in `.env.example`.
- Use production environment variables or a secret-management system for deployment credentials.
- Do not assume every variable shown in older examples is read by the current code.

## 17. Apache configuration

The documented deployment setup uses `public/` as the web document root. The `public/.htaccess` file may use rewrite rules similar to:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>
```

Confirm the actual `.htaccess` file in the repository. Apache must allow the required overrides and have `mod_rewrite` enabled.

For security, do not set the document root to the repository root if that would expose `.env`, source files, configuration, or other private files.

## 18. Extending OmniCore

Before writing an extension, inspect the matching interface and examples in the current source.

Common extension points described by the project:

- **Custom architecture kernel:** implement the kernel contract and register it under `architecture.kernels`.
- **Adapter:** implement the adapter contract and register the adapter in the adapter registry.
- **Service provider:** implement the provider interface and add the class to `providers`.
- **Plugin bridge:** implement the bridge contract and register it under `plugins.bridges`.
- **Module:** implement the module contract and register it through the supported module registry.

The precise namespaces and method signatures may still contain the earlier `Nexus\` naming. Do not rename those references manually without also updating Composer autoload configuration, source namespaces, tests, and examples.

## 19. Deployment and security

Before deploying:

1. Run the test suite available in the repository.
2. Install dependencies from the committed lock file where one exists.
3. Set production environment values and disable debug output.
4. Keep `.env`, source code, configuration, logs, and cache outside the public document root where possible.
5. Ensure runtime directories have only the permissions they need.
6. Review every enabled adapter and plugin, including its package license and maintenance status.
7. Do not install Composer packages automatically during web requests.
8. Validate configuration and test error handling for unknown kernels, invalid routes, missing packages, and plugin failures.
9. Back up data and test deployment procedures before upgrading.

The project is experimental; do not assume it is production-ready solely because a feature is described in this document.

## 20. Troubleshooting

| Symptom | Possible cause | Suggested check |
|---|---|---|
| Composer cannot install dependencies | PHP version or extension mismatch | Check `php -v`, `composer.json`, and Composer's error output |
| All routes return 404 | Rewrite rules or route definitions are incorrect | Check `public/.htaccess`, Apache settings, path, and HTTP method |
| HTTP 500 with no useful details | Debug mode is disabled or logs are unavailable | Inspect server logs; enable debugging only in a local environment |
| Handler returns an empty response | Unsupported return value or handler mismatch | Check handler signature and response normalization |
| Container cannot resolve a service | Missing binding or invalid class name | Check namespace, autoloading, and container bindings |
| Architecture name is not recognized | Kernel is not registered or configuration is misspelled | Check `architecture.kernels` and the kernel registry |
| Adapter appears unavailable | Required package is missing or adapter is disabled | Check Composer dependencies and adapter configuration |
| Plugin bridge fails during boot | Missing dependency or invalid bridge implementation | Check the bridge contract, package list, and application logs |
| `.env` changes have no effect | Wrong environment file or configuration path | Verify the working directory and `CONFIG_PATH` |

Do not fix a runtime error by adding imports or changing namespaces based only on an error message; inspect the actual class location and Composer autoload configuration first.

## 21. Contributing

Contributions, bug reports, tests, and documentation improvements are welcome.

Before opening a pull request:

1. Explain the problem or proposed improvement.
2. Include reproduction steps for bugs.
3. Add or update tests when possible.
4. Update documentation when public behavior or configuration changes.
5. Avoid claiming compatibility unless it has been tested.
6. Do not include secrets, local environment files, generated runtime data, or unrelated changes.

Open issues and pull requests at: <https://github.com/amir-starX/OmniCore>

## License and project status

See the repository's [`LICENSE`](LICENSE) file for the legally authoritative license text. Keep the license stated here, in `README.md`, and in package metadata consistent with that file before publishing a release.

---

# مستندات OmniCore — راهنمای کامل

**نوع پروژه:** فریم‌ورک PHP مبتنی بر پیکربندی و چندالگویی  
**نسخهٔ موردنیاز PHP:** 8.2 یا جدیدتر  
**وضعیت:** آزمایشی و در حال توسعه  
**مجوز:** متن معتبر مجوز در فایل `LICENSE` مخزن قرار دارد.

> OmniCore با هدف فراهم‌کردن امکان انتخاب و ترکیب الگوهای معماری پشتیبانی‌شده از طریق تنظیمات ساخته شده است. این پروژه تمام فریم‌ورک‌های PHP را به‌طور کامل با هم ادغام نمی‌کند و وجود Adapter به معنی سازگاری کامل با تمام قابلیت‌های یک فریم‌ورک دیگر نیست.

## فهرست

1. [معرفی](#معرفی)
2. [پیش‌نیازها و نصب](#پیشنیازها-و-نصب)
3. [ساختار پروژه](#ساختار-پروژه)
4. [تنظیمات](#تنظیمات)
5. [چرخهٔ درخواست](#چرخهٔ-درخواست)
6. [الگوهای معماری](#الگوهای-معماری)
7. [انتخاب معماری](#انتخاب-معماری)
8. [حالت Compose](#حالت-compose)
9. [مسیریابی](#مسیریابی)
10. [تزریق وابستگی](#تزریق-وابستگی)
11. [Service Providerها](#service-providerها)
12. [Adapterها](#adapterها)
13. [پل‌های پلاگین](#پلهای-پلاگین)
14. [پنل خطای توسعه](#پنل-خطای-توسعه)
15. [Handler و Response](#handler-و-response)
16. [متغیرهای محیطی](#متغیرهای-محیطی)
17. [تنظیم Apache](#تنظیم-apache)
18. [گسترش OmniCore](#گسترش-omnicore)
19. [استقرار و امنیت](#استقرار-و-امنیت)
20. [عیب‌یابی](#عیبیابی)
21. [مشارکت](#مشارکت)
22. [مجوز و وضعیت پروژه](#مجوز-و-وضعیت-پروژه)

---

## معرفی

OmniCore یک فریم‌ورک PHP مبتنی بر تنظیمات است که هدف آن پشتیبانی از چند الگوی معماری در یک کدبیس است. بسته به قابلیت‌های پیاده‌سازی‌شده در نسخهٔ مورد استفاده، می‌توان معماری را به‌صورت سراسری، برای مسیر یا برای ماژول انتخاب کرد.

اهداف اصلی:

- **رفتار مبتنی بر تنظیمات:** تنظیم قابلیت‌های پشتیبانی‌شده از طریق `config/main.json`.
- **چند الگوی معماری:** استفاده از Kernel مناسب برای مسیر یا ماژول.
- **قابلیت توسعه:** ثبت Kernel سفارشی، Service Provider، Adapter و Plugin Bridge از طریق قراردادهای مشخص.
- **ادغام به‌جای بازنویسی:** اتصال کتابخانه‌های منتخب از طریق Adapter.
- **جداسازی مسئولیت‌ها:** جدا نگه‌داشتن کد برنامه از هستهٔ فریم‌ورک.

### محدودیت مهم

نام الگوها و بسته‌های شخص ثالث در این مستند، قراردادها و نقاط اتصال توصیف‌شدهٔ پروژه را نشان می‌دهد؛ نه تضمین پشتیبانی کامل از تمام امکانات آن‌ها. برای اطمینان، سورس، وابستگی‌ها و تست‌های همان نسخه را بررسی کنید.

## پیش‌نیازها و نصب

### پیش‌نیازها

- PHP نسخهٔ 8.2 یا جدیدتر
- Composer
- Apache با `mod_rewrite` فعال، در صورت استفاده از تنظیمات Apache این راهنما
- دسترسی نوشتن به پوشه‌های زمان اجرا مانند `storage/cache` و `storage/logs`

بعضی Adapterها به پکیج‌های Composer جداگانه نیاز دارند.

### دریافت پروژه

```bash
git clone https://github.com/amir-starX/OmniCore.git
cd OmniCore
composer install
```

اگر فایل `.env.example` در نسخهٔ شما وجود دارد:

```bash
cp .env.example .env
```

قبل از اجرا، فایل‌های `.env` و `config/main.json` را بررسی کنید. رمزها و کلیدهای واقعی را در Git ثبت نکنید.

نمونهٔ تنظیمات محیطی:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
CONFIG_PATH=config/main.json
```

فقط از متغیرهایی استفاده کنید که سورس نسخهٔ فعلی واقعاً می‌خواند. برای مثال، اگر برنامه یا یکی از پکیج‌های فعال از `APP_KEY` استفاده نمی‌کند، آن را بدون دلیل ضروری فرض نکنید.

## ساختار پروژه

ساختار مستندشدهٔ پروژه به‌صورت تقریبی:

```text
OmniCore/
├── app/
│   ├── Controller/
│   ├── Cqrs/
│   ├── Module/
│   ├── Provider/
│   └── Kernel/
├── config/
│   ├── main.json
│   └── main.composite.json
├── public/
│   ├── index.php
│   └── .htaccess
├── src/
│   ├── Adapters/
│   ├── Bootstrap/
│   ├── Config/
│   ├── Container/
│   ├── Contracts/
│   ├── ErrorPanel/
│   ├── Exception/
│   ├── Http/
│   ├── Kernel/
│   ├── Kernels/
│   ├── Plugins/
│   ├── Routing/
│   └── Support/
├── storage/
│   ├── cache/
│   └── logs/
├── .env.example
├── composer.json
├── DOCUMENTATION.md
└── README.md
```

این ساختار نمونه‌ای بر اساس مستندات قبلی است؛ در صورت تغییر پروژه، درخت واقعی ریپازیتوری ملاک است.

**نکته دربارهٔ Namespace:** ممکن است بعضی کلاس‌ها و مثال‌ها هنوز از Namespace قدیمی `Nexus\` استفاده کنند. تغییر نام ریپازیتوری در GitHub، Namespaceهای PHP، تنظیمات Autoload در Composer یا ارجاع‌های کلاس را خودکار تغییر نمی‌دهد. پیش از تغییر آن‌ها، `composer.json` و سورس را بررسی کنید.

## تنظیمات

فایل اصلی تنظیمات `config/main.json` است. بخش‌های مستندشده عبارت‌اند از:

| بخش | کاربرد |
|---|---|
| `app` | محیط اجرا و تنظیمات اشکال‌زدایی |
| `architecture` | معماری پیش‌فرض، تنظیم مسیر و ماژول، Kernelهای سفارشی و ترکیب |
| `routing` | تعریف مسیرها |
| `container` | اتصال سرویس‌ها به Container |
| `providers` | کلاس‌های Service Provider |
| `adapters` | نام Adapterهای فعال |
| `adapters_options` | تنظیمات اختصاصی Adapterها |
| `plugins` | پل‌های پلاگین و گزینه‌های مرتبط |
| `error_panel` | تنظیمات پنل خطای توسعه |

قواعد مهم:

- JSON باید معتبر باشد؛ کلیدها و رشته‌ها با دابل‌کوتیشن نوشته می‌شوند و JSON استاندارد کامنت ندارد.
- فقط گزینه‌های پیاده‌سازی‌شده در نسخهٔ فعلی را استفاده کنید.
- قبل از استقرار، تنظیمات را اعتبارسنجی کنید.
- اطلاعات محرمانه را در فایل‌های قابل‌انتشار ذخیره نکنید.
- `CONFIG_PATH` مسیر فایل تنظیمات فعال را مشخص می‌کند؛ این متغیر قابلیت پیاده‌سازی‌نشده‌ای را ایجاد نمی‌کند.

نمونهٔ ساده از تنظیم معماری:

```json
{
  "architecture": {
    "default": "mvc",
    "routes": {
      "/api/users": "cqrs",
      "/reports/{id}": "adr"
    },
    "modules": {
      "Billing": "hexagonal"
    },
    "compose": []
  }
}
```

این قطعه یک تنظیم کامل نیست؛ آن را با ساختار مورد انتظار نسخهٔ فعلی ترکیب کنید.

## چرخهٔ درخواست

جریان کلی درخواست در مستندات قبلی به این شکل توصیف شده است:

```text
وب‌سرور
  └── public/index.php
        └── راه‌اندازی برنامه
              ├── بارگذاری محیط
              ├── بارگذاری تنظیمات
              ├── ساخت Context برنامه
              ├── ساخت Container و Router
              ├── ثبت Kernelهای معماری
              ├── راه‌اندازی Adapterها و Plugin Bridgeها
              └── ثبت و راه‌اندازی Providerها
        └── تطبیق مسیر درخواست
              └── انتخاب Kernel یا زنجیرهٔ Kernelها
                    └── پیدا کردن و اجرای Handler
                          └── ساخت Response
        └── ارسال Response
```

این نمودار مفهومی است و ممکن است همهٔ فراخوانی‌های داخلی نسخهٔ فعلی را نشان ندهد. برای تکیه بر ترتیب دقیق اجرا، سورس را بررسی کنید.

## الگوهای معماری

در مستندات فعلی، الگوهای زیر معرفی شده‌اند:

| الگو | کاربرد موردنظر |
|---|---|
| `mvc` | اجرای Handler مربوط به Controller |
| `hmvc` | پردازش شبیه MVC همراه با Context ماژول |
| `adr` | استفاده از Action به‌عنوان Handler |
| `cqrs` | ارسال Command در صورت پیکربندی Command Bus سازگار |
| `hexagonal` | در دسترس قراردادن Portهای پیکربندی‌شده برای Handler |
| `microkernel` | ادغام پلاگین‌های ثبت‌شده در پردازش درخواست |
| `layered` | اجرای درخواست از میان زنجیره‌ای از لایه‌ها |
| `event-driven` | ارسال رویدادهای صف‌شده پس از اجرای Handler |
| `modular` | راه‌اندازی ماژول‌های ثبت‌شده پیش از پردازش |

این نام‌ها رفتار موردنظر را توضیح می‌دهند؛ تضمین نمی‌کنند که هر الگو تمام اصول معماری مربوطه را پیاده‌سازی کرده یا برای محیط Production آماده است.

### Kernel سفارشی

نقطهٔ توسعهٔ معرفی‌شده در مستندات `PatternKernelInterface` است. پیش از ساخت Kernel سفارشی، Namespace و امضای دقیق متدها را از سورس نسخهٔ فعلی بخوانید؛ ممکن است مثال‌های قدیمی هنوز از `Nexus\` استفاده کنند.

شکل تنظیمات ثبت Kernel سفارشی:

```json
{
  "architecture": {
    "kernels": {
      "my-pattern": "App\\Kernel\\MyPatternKernel"
    }
  }
}
```

این قطعه را در تنظیمات اصلی ادغام کنید.

## انتخاب معماری

### معماری پیش‌فرض

```json
{
  "architecture": {
    "default": "mvc"
  }
}
```

### انتخاب معماری بر اساس مسیر

```json
{
  "architecture": {
    "routes": {
      "/api/users": "cqrs",
      "/admin": "hmvc",
      "/reports/{id}": "adr"
    }
  }
}
```

### انتخاب معماری بر اساس ماژول

```json
{
  "architecture": {
    "modules": {
      "Billing": "hexagonal",
      "Auth": "mvc"
    }
  }
}
```

در صورت پشتیبانی Router، یک مسیر می‌تواند فیلد `architecture` را مستقیماً در تعریف خودش داشته باشد.

ترتیب اولویت مستندشده:

1. معماری تعریف‌شده در خود مسیر.
2. معماری مربوط به ماژول مسیر.
3. قاعدهٔ معماری منطبق با مسیر.
4. معماری پیش‌فرض برنامه.

پیش از اتکا به این ترتیب در Production، آن را با پیاده‌سازی فعلی تطبیق دهید.

## حالت Compose

حالت Compose برای اجرای چند Kernel در زنجیرهٔ یک درخواست معرفی شده است.

نمونه:

```json
{
  "architecture": {
    "compose": ["mvc", "cqrs"],
    "strategy": "fallthrough"
  }
}
```

طبق مستندات قبلی، فعال‌بودن `compose` می‌تواند انتخاب پیش‌فرض، مسیر و ماژول را تحت‌تأثیر قرار دهد. همچنین در حالت `fallthrough` یک Kernel ممکن است اجازه دهد Kernel بعدی درخواست را بررسی کند.

**توجه:** زنجیره‌کردن Kernelها به‌خودی‌خود به معنی ترکیب کامل دو فریم‌ورک نیست. هر Kernel باید دربارهٔ Request Context، Response، مدیریت خطا و ادامه یا توقف زنجیره رفتار سازگار داشته باشد. این موارد را با تست بررسی کنید.

اگر فایل `config/main.composite.json` در پروژه موجود است، می‌توان از آن به‌عنوان تنظیم فعال استفاده کرد:

```dotenv
CONFIG_PATH=config/main.composite.json
```

## مسیریابی

قالب مستندشدهٔ مسیرها:

```json
{
  "routing": {
    "routes": {
      "/": {
        "methods": ["GET"],
        "handler": "App\\Controller\\HomeController@index"
      },
      "/reports/{id}": {
        "methods": ["GET"],
        "handler": "App\\Controller\\ReportController@show"
      }
    }
  }
}
```

فیلدهای معرفی‌شده:

- `methods`: متدهای HTTP؛ مستندات قبلی `["*"]` را برای همهٔ متدها ذکر می‌کند.
- `handler`: رشتهٔ `Class@method` یا کلاس قابل فراخوانی، بسته به Handler Resolver.
- `architecture`: بازنویسی اختیاری معماری مسیر، در صورت پشتیبانی.
- `module`: شناسهٔ اختیاری ماژول برای انتخاب معماری ماژول، در صورت پشتیبانی.

مستندات قبلی تطبیق مسیر را بر اساس ترتیب تعریف توصیف می‌کند. از مسیرهای هم‌پوشان استفاده نکنید مگر اینکه اولویت آن‌ها را بررسی کرده باشید.

## تزریق وابستگی

طبق مستندات، Container از اتصال سرویس‌ها و ساخت خودکار کلاس‌های واجد شرایط پشتیبانی می‌کند. API واقعی را از سورس بررسی کنید.

نمونهٔ مفهومی:

```php
$container->set(
    App\Contract\Mailer::class,
    fn ($container) => new App\Mail\SmtpMailer()
);

$mailer = $container->get(App\Contract\Mailer::class);
```

نمونهٔ اتصال در تنظیمات:

```json
{
  "container": {
    "bindings": {
      "App\\Contract\\Mailer": "App\\Mail\\SmtpMailer"
    }
  }
}
```

ساخت خودکار جایگزین اتصال صریح سرویس‌هایی نیست که به مقادیر پیکربندی یا آرگومان‌های scalar نیاز دارند.

## Service Providerها

هدف Service Provider ثبت سرویس‌ها و انجام تنظیمات زمان راه‌اندازی است. مستندات قبلی دو مرحلهٔ `register()` و `boot()` را معرفی می‌کند.

نمونهٔ تنظیمات:

```json
{
  "providers": [
    "App\\Provider\\AppServiceProvider"
  ]
}
```

Interface و ترتیب اجرای واقعی را از سورس بررسی کنید؛ تا زمانی که ترتیب به‌صورت رسمی تعریف و تست نشده، به آن وابسته نشوید.

## Adapterها

Adapterها لایهٔ اتصال به پکیج‌های منتخب شخص ثالث هستند. نام‌های معرفی‌شده در مستندات شامل موارد زیر است:

`illuminate`، `symfony`، `slim`، `laminas`، `doctrine`، `eloquent`، `twig`، `blade`، `latte`، `php-di`، `monolog`، `fastroute` و `league`.

نمونهٔ فعال‌سازی:

```json
{
  "adapters": ["illuminate", "symfony", "twig", "monolog"]
}
```

تنظیمات اختصاصی Adapterها در `adapters_options` قرار می‌گیرند. برای مثال:

```json
{
  "adapters_options": {
    "twig": {
      "paths": ["resources/views"],
      "cache": "storage/cache/twig"
    }
  }
}
```

شناسهٔ سرویس‌ها، وابستگی‌های Composer و نام گزینه‌ها ممکن است با نسخه تغییر کنند. پیش از استفاده، کد Adapter و `composer.json` را بررسی کنید.

**محدودیت سازگاری:** قرار دادن یک پکیج در Container به معنی اجرای کامل چرخهٔ عمر فریم‌ورک اصلی آن یا سازگاری تمام اجزایش با سایر فریم‌ورک‌ها نیست.

## پل‌های پلاگین

Plugin Bridge نقطهٔ اتصال یک پکیج یا سرویس شخص ثالث است. قرارداد معرفی‌شده شامل نام، فهرست وابستگی‌های Composer و متد ثبت است.

نمونهٔ شکل تنظیمات:

```json
{
  "plugins": {
    "bridges": [
      {
        "class": "Vendor\\Bridge\\ExampleBridge",
        "requires": ["vendor/package"]
      }
    ],
    "auto_install": false
  }
}
```

فقط کلیدهایی را استفاده کنید که نسخهٔ فعلی پشتیبانی می‌کند.

### هشدار نصب وابستگی

مستندات قدیمی گزینه‌ای به نام `auto_install` را توصیف می‌کند که ممکن است هنگام راه‌اندازی برنامه دستور `composer require` اجرا کند. تغییر خودکار وابستگی‌ها در زمان اجرای وب‌سایت ریسک دارد و برای Production توصیه نمی‌شود. نصب، بررسی و قفل‌کردن وابستگی‌ها را در مرحلهٔ توسعه یا استقرار انجام دهید و نصب خودکار را غیرفعال نگه دارید، مگر اینکه رفتار آن را کاملاً بشناسید.

Plugin Bridge یک **Sandbox امنیتی** نیست. کد پلاگین شخص ثالث را کدی اجرایی با دسترسی‌های فرایند PHP در نظر بگیرید.

## پنل خطای توسعه

پنل خطا برای نمایش جزئیات Exception و اطلاعات عیب‌یابی هنگام توسعه معرفی شده است.

نمونهٔ مفهومی تنظیمات:

```json
{
  "error_panel": {
    "enabled": true,
    "theme": {
      "primary": "#6d28d9",
      "background": "#0f172a",
      "foreground": "#e2e8f0",
      "accent": "#22d3ee"
    },
    "widgets": {
      "stack_trace": true,
      "request_data": true,
      "timings": true
    }
  }
}
```

این نمونه صرفاً برای توضیح شکل تنظیمات است؛ فقط گزینه‌ها و Widgetهای پیاده‌سازی‌شده را استفاده کنید.

**در محیط Production، Stack Trace، اطلاعات محیطی، رمزها، جزئیات SQL یا محتوای حساس تنظیمات را به کاربران نمایش ندهید.** حالت Debug و پنل توسعه را غیرفعال کنید.

## Handler و Response

مستندات قبلی Handler را به‌صورت Callable، کلاس دارای `__invoke` یا رشتهٔ `Class@method` توصیف می‌کند و تبدیل برخی خروجی‌ها به Response را شرح می‌دهد.

متدهای معرفی‌شده برای Response:

```php
Response::html($body, $status = 200, $headers = []);
Response::json($data, $status = 200, $headers = []);
Response::text($body, $status = 200, $headers = []);
Response::notFound();
```

قبل از استفاده، Namespace، امضای متدها، انواع خروجی پشتیبانی‌شده و آرگومان‌های Handler را از سورس بررسی کنید.

## متغیرهای محیطی

مستندات قبلی بارگذاری `.env` از طریق `vlucas/phpdotenv` و انتخاب فایل تنظیمات با `CONFIG_PATH` را معرفی می‌کند.

نمونه:

```dotenv
APP_ENV=local
APP_DEBUG=true
CONFIG_PATH=config/main.json
```

- فایل محیطی محلی را وارد Git نکنید.
- در `.env.example` فقط مقدارهای نمونه قرار دهید.
- برای اطلاعات محرمانهٔ سرور از متغیر محیطی یا ابزار مدیریت Secret استفاده کنید.
- فرض نکنید تمام متغیرهای مثال‌های قدیمی در سورس فعلی خوانده می‌شوند.

## تنظیم Apache

در تنظیمات مستندشده، Document Root باید به `public/` اشاره کند. فایل `public/.htaccess` ممکن است از قوانینی مشابه این استفاده کند:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>
```

فایل واقعی `.htaccess` را بررسی کنید. Apache باید اجازهٔ Overrideهای لازم را بدهد و `mod_rewrite` فعال باشد.

برای امنیت، اگر Document Root ریشهٔ ریپازیتوری را در معرض وب قرار می‌دهد، از آن استفاده نکنید؛ ممکن است `.env`، سورس و فایل‌های تنظیمات افشا شوند.

## گسترش OmniCore

قبل از نوشتن Extension، Interface و مثال متناظر را در سورس فعلی بررسی کنید.

نقاط توسعهٔ معرفی‌شده:

- **Kernel معماری سفارشی:** پیاده‌سازی قرارداد Kernel و ثبت آن در `architecture.kernels`.
- **Adapter:** پیاده‌سازی قرارداد Adapter و ثبت آن در Adapter Registry.
- **Service Provider:** پیاده‌سازی Interface مربوطه و افزودن کلاس به `providers`.
- **Plugin Bridge:** پیاده‌سازی قرارداد Bridge و ثبت آن در `plugins.bridges`.
- **ماژول:** پیاده‌سازی قرارداد ماژول و ثبت از طریق Module Registry پشتیبانی‌شده.

Namespaceها و امضای متدها را از سورس استخراج کنید. برخی ارجاع‌ها ممکن است هنوز از نام قدیمی `Nexus\` استفاده کنند. تغییر آن‌ها باید همراه با اصلاح Composer Autoload، Namespaceهای سورس، تست‌ها و مثال‌ها باشد.

## استقرار و امنیت

پیش از استقرار:

1. تست‌های موجود در ریپازیتوری را اجرا کنید.
2. در صورت وجود Lock File، وابستگی‌ها را بر اساس آن نصب کنید.
3. متغیرهای محیط Production را تنظیم و Debug را غیرفعال کنید.
4. فایل‌های `.env`، سورس، تنظیمات، Log و Cache را تا حد امکان بیرون از Document Root نگه دارید.
5. دسترسی نوشتن پوشه‌های Runtime را به حداقل لازم محدود کنید.
6. Adapterها و پلاگین‌های فعال، مجوز و وضعیت نگهداری آن‌ها را بررسی کنید.
7. هنگام درخواست‌های وب، پکیج Composer را به‌طور خودکار نصب نکنید.
8. برای Kernel ناشناخته، مسیر نامعتبر، پکیج گمشده و خطای پلاگین تست داشته باشید.
9. پیش از ارتقا، از داده‌ها نسخهٔ پشتیبان بگیرید و روش استقرار را آزمایش کنید.

توصیف یک قابلیت در این فایل به معنی آماده‌بودن آن برای Production نیست؛ پروژه هنوز آزمایشی است.

## عیب‌یابی

| مشکل | علت احتمالی | بررسی پیشنهادی |
|---|---|---|
| نصب Composer ناموفق است | ناسازگاری نسخهٔ PHP یا Extension | `php -v`، `composer.json` و خروجی Composer را بررسی کنید |
| تمام مسیرها 404 می‌دهند | Rewrite یا تعریف مسیرها اشتباه است | `.htaccess`، تنظیمات Apache، مسیر و HTTP Method را بررسی کنید |
| خطای 500 بدون جزئیات | Debug خاموش است یا Log در دسترس نیست | Log سرور را بررسی کنید؛ Debug را فقط در محیط محلی روشن کنید |
| پاسخ Handler خالی است | خروجی پشتیبانی نمی‌شود یا Handler اشتباه است | امضای Handler و تبدیل خروجی به Response را بررسی کنید |
| Container سرویس را پیدا نمی‌کند | Binding موجود نیست یا نام کلاس اشتباه است | Namespace، Autoload و Bindingها را بررسی کنید |
| معماری شناخته نمی‌شود | Kernel ثبت نشده یا نام تنظیمات غلط است | `architecture.kernels` و Kernel Registry را بررسی کنید |
| Adapter در دسترس نیست | پکیج لازم نصب نشده یا Adapter غیرفعال است | وابستگی‌های Composer و تنظیمات Adapter را بررسی کنید |
| Plugin Bridge هنگام Boot خطا می‌دهد | وابستگی گمشده یا پیاده‌سازی نامعتبر است | قرارداد Bridge، فهرست پکیج‌ها و Log را بررسی کنید |
| تغییر `.env` اثر ندارد | فایل محیطی یا مسیر تنظیمات اشتباه است | پوشهٔ اجرای برنامه و `CONFIG_PATH` را بررسی کنید |

صرفاً بر اساس پیام خطا، Import یا Namespace جدید اضافه نکنید؛ ابتدا محل واقعی کلاس و تنظیمات Autoload در Composer را بررسی کنید.

## مشارکت

برای گزارش باگ، تست، بهبود مستندات و قابلیت‌های جدید می‌توانید مشارکت کنید.

پیش از ارسال Pull Request:

1. مشکل یا پیشنهاد را دقیق توضیح دهید.
2. برای باگ‌ها مراحل بازتولید بنویسید.
3. در صورت امکان تست اضافه یا اصلاح کنید.
4. تغییرات رفتار عمومی یا تنظیمات را در مستندات منعکس کنید.
5. بدون تست، ادعای سازگاری نکنید.
6. Secret، فایل محیطی محلی، دادهٔ Runtime یا تغییرات نامرتبط را ارسال نکنید.

ریپازیتوری و بخش Issues:

<https://github.com/amir-starX/OmniCore>

## مجوز و وضعیت پروژه

برای متن حقوقی معتبر مجوز، فایل [`LICENSE`](LICENSE) را بخوانید. پیش از انتشار نسخه، مجوز درج‌شده در این مستند، `README.md` و اطلاعات پکیج باید با همان فایل سازگار باشد.
