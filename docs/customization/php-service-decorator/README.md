# PHP Service Decorator

Skips Cloudflare Turnstile validation in the contact form.

> **Note:** Turnstile requires a secret key (`TURNSTILE_SECRET_KEY` in `.env`).
> If you only want to disable the widget for local development, you can also set
> `TURNSTILE_SITE_KEY=` (empty) in `.env` — the form will render without the widget
> and the validator receives an empty token. This decorator goes one step further
> and always returns `true`, which is useful for integration testing or trusted
> environments.

## What It Does

- Overrides `TurnstileValidator::verify()` to return `true` unconditionally
- Delegates all other methods to the inner service unchanged
- Lives in `local/src/` under the `NotACms\Local\` namespace

## Files

```
local/src/Service/
└── TurnstileSkipDecorator.php
```

## How to Apply

```bash
cp -r docs/customization/php-service-decorator/src/ local/src/
```

Clear cache:

```bash
ddev exec bin/console cache:clear
```

## Result

Contact form submissions bypass Turnstile validation without touching core files.
