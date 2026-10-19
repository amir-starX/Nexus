<?php

declare(strict_types=1);

namespace App\Controller;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Kernel\KernelContext;

final class HomeController
{
    public function index(Request $request, KernelContext $context): Response
    {
        $appName = htmlspecialchars((string) $context->config->get('app.name', 'Nexus'), ENT_QUOTES);
        $php = htmlspecialchars(PHP_VERSION, ENT_QUOTES);
        $env = htmlspecialchars((string) $context->config->get('app.env', 'local'), ENT_QUOTES);

        return Response::html(<<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$appName} — Multi-paradigm PHP Framework</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&family=Vazirmatn:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #06060b;
    --panel: #0d0d16;
    --panel-2: #12121e;
    --border: #1e1e2e;
    --border-hover: #2d2d44;
    --text: #ececf5;
    --muted: #8b8ba3;
    --dim: #5a5a72;
    --violet: #a855f7;
    --violet-2: #7c3aed;
    --cyan: #22d3ee;
    --emerald: #10b981;
    --amber: #f59e0b;
  }
  * { box-sizing: border-box; }
  html, body { margin: 0; padding: 0; }
  body {
    min-height: 100vh;
    background:
      radial-gradient(1200px 600px at 15% -5%, rgba(168,85,247,.22), transparent 55%),
      radial-gradient(1000px 500px at 95% 10%, rgba(34,211,238,.15), transparent 55%),
      radial-gradient(800px 500px at 50% 100%, rgba(124,58,237,.12), transparent 60%),
      var(--bg);
    color: var(--text);
    font-family: 'Space Grotesk', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 15px;
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
  }
  [dir="rtl"] body, [dir="rtl"] { font-family: 'Vazirmatn', 'Space Grotesk', Tahoma, sans-serif; }
  a { color: var(--cyan); text-decoration: none; }
  a:hover { text-decoration: underline; }
  code {
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: .86em;
    background: rgba(255,255,255,.055);
    padding: .12em .42em;
    border-radius: .35em;
    border: 1px solid rgba(255,255,255,.07);
    color: #c4b5fd;
  }
  .shell { max-width: 1180px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }

  /* Nav */
  .nav {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: .9rem 1.25rem;
    border: 1px solid var(--border);
    border-radius: 14px;
    background: rgba(13,13,22,.65);
    backdrop-filter: blur(14px);
    margin-bottom: 3rem;
  }
  .logo { display: flex; align-items: center; gap: .7rem; font-weight: 700; font-size: 1.05rem; letter-spacing: -.01em; }
  .logo-mark {
    width: 28px; height: 28px; border-radius: .55rem;
    background: conic-gradient(from 200deg, var(--violet), var(--cyan), var(--violet));
    box-shadow: 0 0 28px rgba(168,85,247,.55);
    flex-shrink: 0;
  }
  .nav-spacer { flex: 1; }
  .nav-meta { display: flex; gap: .45rem; flex-wrap: wrap; }
  .chip {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .28rem .7rem; border-radius: 999px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--border);
    font-size: .74rem; color: var(--muted); font-weight: 500;
    font-family: ui-monospace, monospace;
  }
  .chip .dot { width: 6px; height: 6px; border-radius: 50%; background: var(--emerald); box-shadow: 0 0 8px var(--emerald); }
  .lang-switch { display: flex; gap: .15rem; padding: .2rem; border-radius: 8px; background: var(--panel); border: 1px solid var(--border); }
  .lang-switch button {
    all: unset; cursor: pointer;
    padding: .3rem .7rem; border-radius: 6px;
    font-size: .8rem; font-weight: 600; color: var(--muted);
    font-family: inherit;
    transition: .15s;
  }
  .lang-switch button:hover { color: var(--text); }
  .lang-switch button.active { background: linear-gradient(135deg, var(--violet), var(--violet-2)); color: #fff; box-shadow: 0 4px 14px rgba(168,85,247,.4); }

  /* Hero */
  .hero { text-align: center; margin-bottom: 3.5rem; padding: .5rem 0; }
  .kicker {
    display: inline-block;
    padding: .35rem .9rem;
    border-radius: 999px;
    font-size: .7rem; letter-spacing: .18em; text-transform: uppercase; font-weight: 700;
    color: var(--cyan);
    background: rgba(34,211,238,.07);
    border: 1px solid rgba(34,211,238,.25);
    margin-bottom: 1.5rem;
  }
  .hero h1 {
    font-size: clamp(2.2rem, 5.5vw, 3.9rem);
    line-height: 1.04; margin: 0 0 1.25rem;
    letter-spacing: -.035em; font-weight: 800;
  }
  .hero h1 .grad {
    background: linear-gradient(110deg, #d8b4fe 0%, #67e8f9 50%, #d8b4fe 100%);
    background-size: 200% 100%;
    -webkit-background-clip: text; background-clip: text; color: transparent;
    animation: shimmer 6s ease-in-out infinite;
  }
  @keyframes shimmer { 0%,100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
  .hero p {
    color: var(--muted);
    font-size: 1.05rem; line-height: 1.7;
    max-width: 64ch; margin: 0 auto;
  }

  /* Features grid */
  .features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1rem;
    margin-bottom: 3rem;
  }
  .feature {
    position: relative;
    padding: 1.5rem 1.5rem 1.4rem;
    border: 1px solid var(--border);
    border-radius: 14px;
    background: linear-gradient(180deg, rgba(255,255,255,.028), rgba(255,255,255,0));
    overflow: hidden;
    transition: .2s;
  }
  .feature:hover { border-color: var(--border-hover); transform: translateY(-2px); }
  .feature::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, var(--violet), var(--cyan), transparent);
    opacity: .6;
  }
  .feature .ico {
    display: inline-flex; align-items: center; justify-content: center;
    width: 36px; height: 36px; border-radius: .6rem;
    background: rgba(168,85,247,.12);
    border: 1px solid rgba(168,85,247,.25);
    color: var(--violet);
    font-size: 1.1rem; margin-bottom: 1rem;
    font-weight: 800;
  }
  .feature h3 { margin: 0 0 .5rem; font-size: 1rem; font-weight: 700; letter-spacing: -.01em; }
  .feature p { margin: 0; color: var(--muted); font-size: .9rem; line-height: 1.6; }

  /* Section */
  .section-title {
    font-size: .74rem; letter-spacing: .16em; text-transform: uppercase;
    color: var(--dim); font-weight: 700; margin: 0 0 1.25rem;
    display: flex; align-items: center; gap: .75rem;
  }
  .section-title::after { content: ''; flex: 1; height: 1px; background: var(--border); }

  /* Kernels strip */
  .kernels { display: flex; flex-wrap: wrap; gap: .55rem; margin-bottom: 3rem; }
  .kernel {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .55rem 1rem; border-radius: 10px;
    border: 1px solid var(--border);
    background: var(--panel);
    font-size: .88rem; font-weight: 500;
    font-family: ui-monospace, monospace;
    color: var(--text);
    transition: .15s;
  }
  .kernel:hover { border-color: rgba(168,85,247,.4); background: rgba(168,85,247,.08); transform: translateY(-1px); }
  .kernel.active { background: linear-gradient(135deg, var(--violet), var(--violet-2)); border-color: transparent; color: #fff; box-shadow: 0 6px 20px rgba(168,85,247,.4); }
  .kernel::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; opacity: .5; }
  .kernel.active::before { opacity: 1; background: #fff; }

  /* Code block */
  .code-card {
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 3rem;
    background: var(--panel);
  }
  .code-head {
    display: flex; align-items: center; gap: .5rem;
    padding: .7rem 1rem;
    border-bottom: 1px solid var(--border);
    background: rgba(255,255,255,.02);
    font-family: ui-monospace, monospace;
    font-size: .78rem;
    color: var(--dim);
  }
  .code-head .dots { display: flex; gap: .35rem; }
  .code-head .d { width: 10px; height: 10px; border-radius: 50%; background: var(--border-hover); }
  .code-head .d:nth-child(1) { background: #ff5f57; }
  .code-head .d:nth-child(2) { background: #febc2e; }
  .code-head .d:nth-child(3) { background: #28c840; }
  .code-head .fname { margin-left: .5rem; }
  [dir="rtl"] .code-head .fname { margin-left: 0; margin-right: .5rem; }
  .code-body {
    padding: 1.2rem 1.4rem;
    font-family: ui-monospace, monospace;
    font-size: .82rem;
    line-height: 1.65;
    overflow-x: auto;
    color: #c4b5fd;
    background: #08080f;
    margin: 0;
    direction: ltr; text-align: left;
  }
  .code-body .k { color: #c4b5fd; } /* keywords */
  .code-body .s { color: #86efac; } /* strings */
  .code-body .n { color: #fbbf24; } /* numbers */
  .code-body .c { color: #5a5a72; } /* comments */
  .code-body .p { color: #8b8ba3; } /* punctuation */
  .code-body .key { color: #67e8f9; } /* json keys */

  /* Footer */
  .foot {
    display: flex; justify-content: space-between; align-items: center;
    gap: 1rem; flex-wrap: wrap;
    margin-top: 3rem; padding-top: 1.75rem;
    border-top: 1px solid var(--border);
    color: var(--dim); font-size: .85rem;
  }
  .foot-links { display: flex; gap: 1.25rem; }

  /* i18n */
  [data-lang] { display: none; }
  [data-lang].active { display: block; }
  .inline [data-lang].active { display: inline; }

  @media (max-width: 640px) {
    .shell { padding: 1.25rem 1rem 3rem; }
    .nav { flex-wrap: wrap; margin-bottom: 2rem; }
    .nav-spacer { display: none; }
    .nav-meta { order: 3; width: 100%; }
    .hero h1 { font-size: 2rem; }
    .foot { flex-direction: column; align-items: flex-start; }
  }
</style>
</head>
<body>

<div class="shell">

  <nav class="nav">
    <div class="logo"><div class="logo-mark"></div><span>{$appName}</span></div>
    <div class="nav-spacer"></div>
    <div class="nav-meta">
      <span class="chip"><span class="dot"></span>php {$php}</span>
      <span class="chip">env: {$env}</span>
    </div>
    <div class="lang-switch" role="tablist" aria-label="Language">
      <button type="button" data-set-lang="en" class="active">EN</button>
      <button type="button" data-set-lang="fa">فا</button>
    </div>
  </nav>

  <header class="hero">
    <span class="kicker" data-lang="en">Multi-paradigm · Config-driven · PHP 8.2+</span>
    <span class="kicker" data-lang="fa">چند-پارادایمی · مبتنی بر پیکربندی · PHP 8.2+</span>

    <h1 data-lang="en">One file. <span class="grad">Nine architectures.</span> Zero rewrites.</h1>
    <h1 data-lang="fa" dir="rtl">یک فایل. <span class="grad">نُه معماری.</span> بدون بازنویسی.</h1>

    <p data-lang="en">Nexus treats architecture as runtime configuration. A single <code>config/main.json</code> switches patterns globally, per-route, per-module — or composes several inside the same request. Existing frameworks are integrated through adapters, never rewritten.</p>
    <p data-lang="fa" dir="rtl">Nexus معماری را به‌عنوان پیکربندی زمان اجرا در نظر می‌گیرد. یک فایل <code>config/main.json</code> الگوها را به‌صورت سراسری، per-route، per-module تغییر می‌دهد — یا چند الگو را هم‌زمان در یک request ترکیب می‌کند. فریمورک‌های موجود از طریق آداپتر ادغام می‌شوند، نه بازنویسی.</p>
  </header>

  <div class="section-title" data-lang="en">Why Nexus</div>
  <div class="section-title" data-lang="fa" dir="rtl">چرا Nexus</div>

  <div class="features">
    <div class="feature">
      <div class="ico">⚙</div>
      <h3 data-lang="en">Configuration over code</h3>
      <h3 data-lang="fa" dir="rtl">پیکربندی به‌جای کد</h3>
      <p data-lang="en">Behavior changes come from <code>main.json</code>, not from editing PHP. Swap patterns, adapters, routes, or themes without touching the core.</p>
      <p data-lang="fa" dir="rtl">تغییرات رفتار از <code>main.json</code> می‌آید، نه ویرایش PHP. الگوها، آداپترها، مسیرها یا تم‌ها را بدون دست زدن به هسته عوض کن.</p>
    </div>

    <div class="feature">
      <div class="ico">⇄</div>
      <h3 data-lang="en">Hot-switchable paradigms</h3>
      <h3 data-lang="fa" dir="rtl">الگوهای قابل سوییچ داغ</h3>
      <p data-lang="en">Global, per-route, per-module, or coexistence. Different endpoints can use different architectures at the same time in one deployment.</p>
      <p data-lang="fa" dir="rtl">سراسری، per-route، per-module یا همزیستی. endpointهای مختلف می‌توانند هم‌زمان از الگوهای متفاوت در یک استقرار استفاده کنند.</p>
    </div>

    <div class="feature">
      <div class="ico">⊕</div>
      <h3 data-lang="en">Coexistence in one request</h3>
      <h3 data-lang="fa" dir="rtl">همزیستی در یک request</h3>
      <p data-lang="en">Compose <code>["mvc", "cqrs"]</code> and both kernels run in sequence. MVC dispatches a command; CQRS handles it — all in one cycle.</p>
      <p data-lang="fa" dir="rtl">ترکیب <code>["mvc", "cqrs"]</code> و اجرای هر دو kernel پشت‌سرهم. MVC یک command ارسال می‌کند؛ CQRS آن را مدیریت می‌کند — همه در یک چرخه.</p>
    </div>

    <div class="feature">
      <div class="ico">⌘</div>
      <h3 data-lang="en">Adapters, not rewrites</h3>
      <h3 data-lang="fa" dir="rtl">آداپتر، نه بازنویسی</h3>
      <p data-lang="en">Illuminate, Symfony, Slim, Laminas, Doctrine, Eloquent, Twig, Blade, Latte, PHP-DI, Monolog, FastRoute, League — wrapped, not reimplemented.</p>
      <p data-lang="fa" dir="rtl">Illuminate، Symfony، Slim، Laminas، Doctrine، Eloquent، Twig، Blade، Latte، PHP-DI، Monolog، FastRoute، League — بسته‌بندی شده، نه بازنویسی.</p>
    </div>

    <div class="feature">
      <div class="ico">◆</div>
      <h3 data-lang="en">Auto-installing bridges</h3>
      <h3 data-lang="fa" dir="rtl">پل‌های نصب خودکار</h3>
      <p data-lang="en">Declare a Composer package in <code>main.json</code>. If it's missing at boot, it's installed automatically. Third-party integrations without friction.</p>
      <p data-lang="fa" dir="rtl">پکیج Composer را در <code>main.json</code> اعلام کن. اگر در boot نصب نباشد، خودکار نصب می‌شود. ادغام‌های third-party بدون اصطکاک.</p>
    </div>

    <div class="feature">
      <div class="ico">◈</div>
      <h3 data-lang="en">Themeable error panel</h3>
      <h3 data-lang="fa" dir="rtl">پنل خطای تم‌پذیر</h3>
      <p data-lang="en">Colors, logo, layout, widgets — everything driven by <code>main.json</code>. Stack traces, query log, timings, resolved pattern, config state.</p>
      <p data-lang="fa" dir="rtl">رنگ‌ها، لوگو، چیدمان، ویجت‌ها — همه از <code>main.json</code> هدایت می‌شوند. Stack trace، query log، زمان‌بندی، الگوی resolve‌شده، وضعیت config.</p>
    </div>
  </div>

  <div class="section-title" data-lang="en">Supported patterns</div>
  <div class="section-title" data-lang="fa" dir="rtl">الگوهای پشتیبانی‌شده</div>

  <div class="kernels">
    <span class="kernel active">mvc</span>
    <span class="kernel">hmvc</span>
    <span class="kernel">adr</span>
    <span class="kernel">cqrs</span>
    <span class="kernel">hexagonal</span>
    <span class="kernel">microkernel</span>
    <span class="kernel">layered</span>
    <span class="kernel">event-driven</span>
    <span class="kernel">modular</span>
  </div>

  <div class="section-title" data-lang="en">One file, one source of truth</div>
  <div class="section-title" data-lang="fa" dir="rtl">یک فایل، یک منبع حقیقت</div>

  <div class="code-card">
    <div class="code-head">
      <div class="dots"><div class="d"></div><div class="d"></div><div class="d"></div></div>
      <span class="fname">config/main.json</span>
    </div>
    <pre class="code-body"><span class="p">{</span>
  <span class="key">"architecture"</span><span class="p">:</span> <span class="p">{</span>
    <span class="key">"default"</span><span class="p">:</span> <span class="s">"mvc"</span><span class="p">,</span>
    <span class="key">"routes"</span><span class="p">:</span> <span class="p">{</span>
      <span class="key">"/api/users"</span><span class="p">:</span> <span class="s">"cqrs"</span><span class="p">,</span>
      <span class="key">"/admin"</span><span class="p">:</span> <span class="s">"hmvc"</span><span class="p">,</span>
      <span class="key">"/reports/{id}"</span><span class="p">:</span> <span class="s">"adr"</span>
    <span class="p">},</span>
    <span class="key">"modules"</span><span class="p">:</span> <span class="p">{</span>
      <span class="key">"Billing"</span><span class="p">:</span> <span class="s">"hexagonal"</span>
    <span class="p">},</span>
    <span class="key">"compose"</span><span class="p">:</span> <span class="p">[</span><span class="s">"mvc"</span><span class="p">,</span> <span class="s">"cqrs"</span><span class="p">]</span>
  <span class="p">}</span>
<span class="p">}</span></pre>
  </div>

  <footer class="foot">
    <span data-lang="en">Nexus Framework · v1.0 · MIT License</span>
    <span data-lang="fa" dir="rtl">فریمورک Nexus · نسخه ۱.۰ · مجوز MIT</span>
    <div class="foot-links">
      <a href="https://github.com" data-lang="en">Documentation</a>
      <a href="https://github.com" data-lang="fa" dir="rtl">مستندات</a>
      <a href="https://github.com" data-lang="en">GitHub</a>
      <a href="https://github.com" data-lang="fa" dir="rtl">گیت‌هاب</a>
    </div>
  </footer>

</div>

<script>
  (function () {
    const buttons = document.querySelectorAll('[data-set-lang]');
    const setLang = (lang) => {
      document.documentElement.lang = lang;
      document.documentElement.dir = lang === 'fa' ? 'rtl' : 'ltr';
      buttons.forEach(b => b.classList.toggle('active', b.dataset.setLang === lang));
      document.querySelectorAll('[data-lang]').forEach(el => {
        el.classList.toggle('active', el.getAttribute('data-lang') === lang);
      });
      try { localStorage.setItem('nexus.lang', lang); } catch (e) {}
    };
    buttons.forEach(b => b.addEventListener('click', () => setLang(b.dataset.setLang)));
    let saved = 'en';
    try { saved = localStorage.getItem('nexus.lang') || 'en'; } catch (e) {}
    setLang(saved === 'fa' ? 'fa' : 'en');
  })();
</script>

</body>
</html>
HTML);
    }
}