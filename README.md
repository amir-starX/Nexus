> **برای نسخه فارسی به پایین اسکرول کنید.**
> **Scroll down for Persian version.**

---

# Nexus Framework

**Configuration-driven, multi-paradigm PHP framework — PHP 8.2+**

Nexus is a PHP framework that treats architecture as runtime configuration. A single file — `config/main.json` — controls architecture, routing, dependency injection, adapters, plugins, and the error panel. The same codebase can serve MVC pages, CQRS endpoints, ADR actions, and hexagonal modules — all in one deployment.

---

## Highlights

- **Single source of truth.** `config/main.json` drives everything. No PHP edits for standard workflows.
- **Hot-switchable paradigms.** Switch globally, per-route, per-module, or run several at once.
- **Coexistence.** Compose `["mvc", "cqrs"]` and run both inside one request via fallthrough.
- **Adapters, not rewrites.** Illuminate, Symfony, Slim, Laminas, Doctrine, Eloquent, Twig, Blade, Latte, PHP-DI, Monolog, FastRoute, League.
- **Auto-installing plugin bridges.** Declare a package in `main.json`; it installs on boot if missing.
- **Themeable error panel.** Colors, logo, layout, widgets — all from `main.json`.
- **Strict, typed, PSR-compliant core.** PHP 8.2+, `declare(strict_types=1)`, readonly, enums.

---

## Supported Architectural Patterns

`mvc`, `hmvc`, `adr`, `cqrs`, `hexagonal`, `microkernel`, `layered`, `event-driven`, `modular` — plus custom kernels via `architecture.kernels`.

---

## Quick Start

```
git clone <repo> project
cd project
composer install
cp .env.example .env
```

Point Apache DocumentRoot to `public/`, or open `http://localhost/project/public/`.

Edit `config/main.json` to change patterns, routes, adapters, and plugins.

---

## Minimal Example

```json
{
  "architecture": {
    "default": "mvc",
    "routes": { "/api/users": "cqrs", "/admin": "hmvc" },
    "modules": { "Billing": "hexagonal" },
    "compose": []
  }
}
```

Every route resolves to a pattern. Change one line, change behavior — no code touched.

---

## Coexistence Mode

```json
"architecture": {
  "compose": ["mvc", "cqrs"],
  "strategy": "fallthrough"
}
```

An MVC controller sets a command; the CQRS kernel dispatches it; the response flows back through the chain. One request, two patterns.

---

## Requirements

- PHP 8.2+
- Composer
- Apache with `mod_rewrite`

---

## Documentation

Full reference: see `DOCUMENTATION.md`.

---

## License

MIT.

---

**End of English README.**

---

<div dir="rtl" align="right">

# فریمورک Nexus

**فریمورک PHP چند-پارادایمی مبتنی بر پیکربندی — PHP 8.2 به بالا**

Nexus فریمورکی است که معماری را به‌عنوان پیکربندی زمان اجرا در نظر می‌گیرد. یک فایل — `config/main.json` — معماری، مسیریابی، تزریق وابستگی، آداپترها، پلاگین‌ها و پنل خطا را کنترل می‌کند. همان کد می‌تواند صفحه MVC، endpoint CQRS، Action ADR و ماژول hexagonal را هم‌زمان در یک استقرار ارائه دهد.

---

## ویژگی‌ها

- **تنها منبع حقیقت.** `config/main.json` همه‌چیز را هدایت می‌کند. برای جریان‌های استاندارد نیازی به ویرایش PHP نیست.
- **الگوهای قابل سوییچ داغ.** سراسری، per-route، per-module، یا هم‌زمان چند الگو.
- **همزیستی.** ترکیب `["mvc", "cqrs"]` و اجرای هر دو در یک request با استراتژی fallthrough.
- **آداپتر به‌جای بازنویسی.** Illuminate، Symfony، Slim، Laminas، Doctrine، Eloquent، Twig، Blade، Latte، PHP-DI، Monolog، FastRoute، League.
- **پل‌های پلاگین با نصب خودکار.** پکیج را در `main.json` اعلام کنید؛ اگر نصب نباشد، در boot نصب می‌شود.
- **پنل خطا با تم قابل تنظیم.** رنگ‌ها، لوگو، چیدمان، ویجت‌ها — همه از `main.json`.
- **هسته strict، typed، سازگار با PSR.** PHP 8.2+، `declare(strict_types=1)`، readonly، enum.

---

## الگوهای معماری پشتیبانی‌شده

`mvc`، `hmvc`، `adr`، `cqrs`، `hexagonal`، `microkernel`، `layered`، `event-driven`، `modular` — به‌همراه Kernelهای سفارشی از طریق `architecture.kernels`.

---

## شروع سریع

```
git clone <repo> project
cd project
composer install
cp .env.example .env
```

DocumentRoot آپاچی را به `public/` تنظیم کنید، یا `http://localhost/project/public/` را باز کنید.

`config/main.json` را برای تغییر الگوها، مسیرها، آداپترها و پلاگین‌ها ویرایش کنید.

---

## مثال مینیمال

```json
{
  "architecture": {
    "default": "mvc",
    "routes": { "/api/users": "cqrs", "/admin": "hmvc" },
    "modules": { "Billing": "hexagonal" },
    "compose": []
  }
}
```

هر مسیر به یک الگو resolve می‌شود. یک خط تغییر، رفتار عوض می‌شود — بدون دست زدن به کد.

---

## حالت همزیستی

```json
"architecture": {
  "compose": ["mvc", "cqrs"],
  "strategy": "fallthrough"
}
```

کنترلر MVC یک command تنظیم می‌کند؛ Kernel CQRS آن را ارسال می‌کند؛ پاسخ از زنجیره برمی‌گردد. یک request، دو الگو.

---

## پیش‌نیازها

- PHP 8.2 به بالا
- Composer
- Apache با `mod_rewrite`

---

## مستندات

مرجع کامل: `DOCUMENTATION.md`.

---

## مجوز

MIT.

---

**پایان README فارسی.**

</div>
