<?php

declare(strict_types=1);

namespace Nexus\ErrorPanel;

use Nexus\Config\Config;
use Nexus\Kernel\ErrorPanelState;
use Nexus\Kernel\KernelContext;

final class Panel
{
    public function __construct(
        private readonly Config $config,
        private readonly ErrorPanelState $state,
        private readonly KernelContext $context,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->config->isDebug()
            && (bool) $this->config->get('error_panel.enabled', true);
    }

    public function render(\Throwable $e): string
    {
        $theme = $this->theme();
        $widgets = $this->widgets();

        $sections = [];
        if ($widgets['stack_trace']) {
            $sections[] = $this->stackTraceSection($e);
        }
        if ($widgets['request_data']) {
            $sections[] = $this->requestSection();
        }
        if ($widgets['resolved_pattern']) {
            $sections[] = $this->patternSection();
        }
        if ($widgets['config_state']) {
            $sections[] = $this->configSection();
        }
        if ($widgets['timings']) {
            $sections[] = $this->timingsSection();
        }
        if ($widgets['query_log']) {
            $sections[] = $this->queryLogSection();
        }

        return $this->layout($e, $theme, $sections);
    }

    private function theme(): array
    {
        $default = [
            'primary' => '#7c3aed',
            'background' => '#0b0f19',
            'foreground' => '#e5e7eb',
            'accent' => '#22d3ee',
            'logo' => null,
        ];
        $theme = $this->config->get('error_panel.theme', []);
        return is_array($theme) ? array_merge($default, $theme) : $default;
    }

    private function widgets(): array
    {
        $default = [
            'stack_trace' => true,
            'query_log' => true,
            'request_data' => true,
            'timings' => true,
            'resolved_pattern' => true,
            'config_state' => true,
        ];
        $widgets = $this->config->get('error_panel.widgets', []);
        return is_array($widgets) ? array_merge($default, $widgets) : $default;
    }

    private function layout(\Throwable $e, array $theme, array $sections): string
    {
        $logo = $theme['logo'] !== null
            ? '<img src="' . htmlspecialchars((string) $theme['logo'], ENT_QUOTES) . '" alt="logo" style="height:28px;border-radius:.4rem">'
            : '<span class="brand-mark"></span>';

        $appName = htmlspecialchars((string) $this->config->get('app.name', 'Nexus'), ENT_QUOTES);
        $env = htmlspecialchars((string) $this->config->get('app.env', 'local'), ENT_QUOTES);
        $class = htmlspecialchars(get_class($e), ENT_QUOTES);
        $msg = htmlspecialchars($e->getMessage() !== '' ? $e->getMessage() : '(no message)', ENT_QUOTES);
        $file = htmlspecialchars($e->getFile(), ENT_QUOTES);
        $line = (int) $e->getLine();
        $bg = htmlspecialchars((string) $theme['background'], ENT_QUOTES);
        $fg = htmlspecialchars((string) $theme['foreground'], ENT_QUOTES);
        $primary = htmlspecialchars((string) $theme['primary'], ENT_QUOTES);
        $accent = htmlspecialchars((string) $theme['accent'], ENT_QUOTES);
        $body = implode('', $sections);

        return <<<HTML
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$class} · {$appName}</title>
<style>
  :root { --bg:{$bg}; --fg:{$fg}; --primary:{$primary}; --accent:{$accent}; }
  *{box-sizing:border-box}
  body{margin:0;font-family:-apple-system,Segoe UI,Inter,Roboto,Helvetica,Arial,sans-serif;background:radial-gradient(900px 500px at 0% -10%, rgba(124,58,237,.25), transparent 55%), var(--bg);color:var(--fg);min-height:100vh;}
  .topbar{display:flex;align-items:center;gap:.7rem;padding:1rem 1.25rem;background:linear-gradient(90deg, var(--primary), rgba(99,102,241,.85));color:#fff;box-shadow:0 8px 30px rgba(0,0,0,.35);}
  .brand-mark{display:inline-block;width:22px;height:22px;border-radius:.4rem;background:linear-gradient(135deg,#fff,#c4b5fd);}
  .appname{font-weight:700;letter-spacing:.02em;}
  .pill{display:inline-block;padding:.15rem .55rem;border-radius:999px;background:rgba(255,255,255,.18);font-size:.75rem;}
  .topbar .right{margin-left:auto;font-size:.8rem;opacity:.85;}
  .wrap{max-width:1100px;margin:0 auto;padding:1.5rem 1.25rem 2.5rem;}
  .hero{margin-bottom:1.4rem;}
  .hero h1{margin:.2rem 0 .4rem;font-size:clamp(1.4rem,2.4vw,1.9rem);letter-spacing:-.01em;}
  .hero .msg{color:rgba(255,255,255,.85);word-break:break-word;}
  .hero .where{margin-top:.5rem;font-size:.85rem;color:rgba(229,231,235,.65);}
  .section{border:1px solid rgba(255,255,255,.08);background:linear-gradient(180deg, rgba(255,255,255,.035), rgba(255,255,255,.01));border-radius:.75rem;overflow:hidden;margin-bottom:1rem;}
  .section h2{margin:0;padding:.65rem 1rem;font-size:.75rem;text-transform:uppercase;letter-spacing:.12em;color:var(--accent);background:rgba(255,255,255,.03);font-weight:600;}
  .section pre, .section .content{margin:0;padding:1rem;font-size:.85rem;line-height:1.55;overflow:auto;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;}
  .section pre{white-space:pre;}
  table{width:100%;border-collapse:collapse;}
  td,th{text-align:left;padding:.45rem .7rem;border-bottom:1px solid rgba(255,255,255,.06);font-size:.85rem;vertical-align:top;}
  tr:last-child td{border-bottom:none;}
  th{color:rgba(229,231,235,.7);font-weight:500;width:38%;}
  code{background:rgba(255,255,255,.06);padding:.1rem .35rem;border-radius:.3rem;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:.88em;}
  a{color:var(--accent);text-decoration:none}
  a:hover{text-decoration:underline}
</style></head><body>
<div class="topbar">
  {$logo}
  <span class="appname">{$appName}</span>
  <span class="pill">env: {$env}</span>
  <span class="right">{$class}</span>
</div>
<div class="wrap">
  <div class="hero">
    <h1>Something went wrong</h1>
    <div class="msg">{$msg}</div>
    <div class="where">at {$file}:{$line}</div>
  </div>
  {$body}
</div>
</body></html>
HTML;
    }

    private function stackTraceSection(\Throwable $e): string
    {
        return '<div class="section"><h2>Stack Trace</h2><pre>' . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES) . '</pre></div>';
    }

    private function requestSection(): string
    {
        $keys = ['REQUEST_METHOD', 'REQUEST_URI', 'HTTP_HOST', 'REMOTE_ADDR', 'HTTP_USER_AGENT'];
        $rows = '';
        foreach ($keys as $k) {
            $v = htmlspecialchars((string) ($_SERVER[$k] ?? '-'), ENT_QUOTES);
            $rows .= '<tr><th>' . htmlspecialchars($k, ENT_QUOTES) . '</th><td>' . $v . '</td></tr>';
        }
        return '<div class="section"><h2>Request</h2><div class="content"><table>' . $rows . '</table></div></div>';
    }

    private function patternSection(): string
    {
        $arch = $this->config->get('architecture', []);
        $default = is_array($arch) ? (string) ($arch['default'] ?? 'mvc') : 'mvc';
        $compose = is_array($arch) ? (array) ($arch['compose'] ?? []) : [];
        $composeStr = $compose !== [] ? implode(' → ', array_map('strval', $compose)) : '(none)';

        $rows = '<tr><th>default</th><td><span class="pill">' . htmlspecialchars($default, ENT_QUOTES) . '</span></td></tr>';
        $rows .= '<tr><th>compose</th><td>' . htmlspecialchars($composeStr, ENT_QUOTES) . '</td></tr>';

        $routes = is_array($arch) ? (array) ($arch['routes'] ?? []) : [];
        foreach ($routes as $path => $pattern) {
            $rows .= '<tr><th>' . htmlspecialchars((string) $path, ENT_QUOTES) . '</th><td>' . htmlspecialchars((string) $pattern, ENT_QUOTES) . '</td></tr>';
        }

        return '<div class="section"><h2>Resolved Pattern</h2><div class="content"><table>' . $rows . '</table></div></div>';
    }

    private function configSection(): string
    {
        $summary = [
            'app.env' => $this->config->get('app.env', 'local'),
            'app.debug' => $this->config->isDebug() ? 'true' : 'false',
            'adapters' => implode(', ', array_map('strval', (array) $this->config->get('adapters', []))) ?: '(none)',
            'plugins.bridges' => count((array) $this->config->get('plugins.bridges', [])),
            'providers' => count((array) $this->config->get('providers', [])),
        ];

        $rows = '';
        foreach ($summary as $k => $v) {
            $rows .= '<tr><th>' . htmlspecialchars((string) $k, ENT_QUOTES) . '</th><td>' . htmlspecialchars((string) $v, ENT_QUOTES) . '</td></tr>';
        }
        return '<div class="section"><h2>Config State</h2><div class="content"><table>' . $rows . '</table></div></div>';
    }

    private function timingsSection(): string
    {
        $start = (float) ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));
        $elapsed = (microtime(true) - $start) * 1000;

        $rows = '<tr><th>request elapsed</th><td>' . number_format($elapsed, 2) . ' ms</td></tr>';
        $rows .= '<tr><th>peak memory</th><td>' . number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB</td></tr>';
        $rows .= '<tr><th>included files</th><td>' . count(get_included_files()) . '</td></tr>';

        return '<div class="section"><h2>Timings</h2><div class="content"><table>' . $rows . '</table></div></div>';
    }

    private function queryLogSection(): string
    {
        return '<div class="section"><h2>Query Log</h2><div class="content" style="opacity:.6">no queries recorded (enable an ORM adapter to populate this section)</div></div>';
    }
}