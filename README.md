# OmniCore

**A configuration-driven, multi-paradigm PHP framework.**  
**One core. Multiple architectures.**

OmniCore is an experimental PHP framework designed to let an application use different architectural patterns through configuration instead of forcing every module into one structure. Its current codebase includes architecture kernels, routing, a dependency-injection container, adapters for selected third-party packages, plugin bridges, and a configurable development error panel.

> **Project status:** This project is under active development. Treat it as experimental until its APIs, integrations, tests, and production deployment guidance are fully verified. The presence of an adapter or an architecture kernel does not automatically mean that every feature of an external framework is supported.

## Contents

- [Goals](#goals)
- [Current capabilities](#current-capabilities)
- [Architecture patterns](#architecture-patterns)
- [Configuration example](#configuration-example)
- [Requirements](#requirements)
- [Installation](#installation)
- [Web server setup](#web-server-setup)
- [Project structure](#project-structure)
- [Documentation](#documentation)
- [Security notes](#security-notes)
- [Contributing](#contributing)
- [License](#license)

## Goals

OmniCore aims to provide:

- **Configuration-driven behavior:** use `config/main.json` to configure architecture and framework services.
- **Multiple architectural patterns:** select patterns globally, per route, or per module where supported.
- **Extensibility:** add custom kernels, adapters, and service providers through defined contracts.
- **Integration instead of reimplementation:** connect selected third-party packages through adapters.
- **A small, understandable core:** keep application code separate from framework internals.

OmniCore does not literally combine every PHP framework or guarantee complete compatibility with them. Its goal is to provide a common core and integration points for selected tools.

## Current capabilities

The repository documentation describes the following capabilities:

- A configurable application bootstrap and request lifecycle.
- Routing with HTTP methods, path parameters, handlers, and optional per-route architecture selection.
- A dependency-injection container with autowiring and PSR-11-oriented behavior.
- Architecture kernels for several patterns.
- Custom kernel registration.
- Service providers with separate registration and boot phases.
- Adapters for selected third-party packages.
- Plugin bridges that can declare package requirements.
- A configurable HTML error panel for development.
- Environment-based configuration through `.env` and `CONFIG_PATH`.

These capabilities should be considered subject to the implementation and tests in the current repository version. Check [`DOCUMENTATION.md`](DOCUMENTATION.md) for details.

## Architecture patterns

The current documentation describes these built-in patterns:

| Pattern | Intended role |
| --- | --- |
| `mvc` | Route requests to controller methods |
| `hmvc` | MVC-style handling with module context |
| `adr` | Handle requests through action classes |
| `cqrs` | Dispatch commands when a command bus is available |
| `hexagonal` | Connect request handling to configured ports |
| `microkernel` | Integrate registered plugins into request handling |
| `layered` | Process requests through a configured layer pipeline |
| `event-driven` | Dispatch queued events after handler execution |
| `modular` | Initialize registered modules |

Custom kernels can be registered by implementing the relevant kernel contract. See [`DOCUMENTATION.md`](DOCUMENTATION.md) for the exact interface and configuration.

## Configuration example

A simplified example of selecting an architecture per route and module:

```json
{
  "architecture": {
    "default": "mvc",
    "routes": {
      "/api/users": "cqrs",
      "/admin": "hmvc"
    },
    "modules": {
      "Billing": "hexagonal"
    },
    "compose": []
  }
}
```

This is an illustrative configuration fragment, not a complete `main.json` file. Use the repository's configuration and documentation as the source of truth for all required settings.

## Requirements

According to the current project documentation:

- PHP 8.2 or newer
- Composer
- Apache with `mod_rewrite` enabled, if using the documented Apache rewrite setup

Some adapters require additional Composer packages. Install only the dependencies needed for the integrations you enable.

## Installation

Clone the repository and install its Composer dependencies:

```bash
git clone https://github.com/amir-starX/OmniCore.git
cd OmniCore
composer install
```

If `.env.example` exists in your checked-out version, create a local environment file:

```bash
cp .env.example .env
```

Then review `.env` and `config/main.json` before starting the application. Do not commit real credentials, API keys, or production secrets.

> If the repository's default branch or setup files change, follow the current files in the repository and `DOCUMENTATION.md`.

## Web server setup

For the documented Apache setup:

1. Enable `mod_rewrite`.
2. Allow `.htaccess` overrides for the project directory.
3. Prefer setting the web server's document root to the `public/` directory.
4. Keep configuration, application source, dependencies, and environment files outside the public document root.

For local development, use the server configuration described in [`DOCUMENTATION.md`](DOCUMENTATION.md). Do not expose a development error panel or debug output in production.

## Project structure

The current repository includes these main areas:

```text
OmniCore/
├── app/
│   └── Controller/
├── config/
├── public/
├── src/
├── DOCUMENTATION.md
├── LICENSE
├── composer.json
└── README.md
```

The exact structure may evolve as the framework develops. See the repository for the complete current tree.

## Documentation

- [Full documentation](DOCUMENTATION.md)
- [Source code](https://github.com/amir-starX/OmniCore)
- [Report a bug or request a feature](https://github.com/amir-starX/OmniCore/issues)

## Security notes

- Keep `APP_DEBUG` disabled in production.
- Do not store real secrets in committed JSON, `.env.example`, or source files.
- Review third-party packages and their licenses before enabling adapters.
- The documentation describes an optional plugin-bridge auto-install feature. Avoid installing Composer packages automatically during application boot in production; install and audit dependencies as part of your deployment process.
- Do not assume that plugin bridges provide sandboxing or isolation unless that behavior is explicitly implemented and tested.

If you discover a security issue, please avoid posting sensitive exploit details publicly in an issue. Contact the maintainer through an appropriate private channel if one is provided by the repository.

## Contributing

Contributions, bug reports, documentation fixes, and tests are welcome.

Before opening a pull request:

1. Describe the problem or proposed change.
2. Include reproduction steps for bug fixes.
3. Add or update tests where possible.
4. Update the documentation when behavior or configuration changes.
5. Avoid claiming compatibility with a package or architecture unless it has been tested.

## License

This repository currently identifies its license as **Apache-2.0**. See [`LICENSE`](LICENSE) for the exact terms.
