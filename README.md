# ChatSearch-API

<p>
  <a href="LICENSE"><img alt="License: MIT" src="https://img.shields.io/badge/License-MIT-blue.svg"></a>
  <img alt="PHP" src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white">
  <img alt="Guzzle" src="https://img.shields.io/badge/Guzzle-7-8892BF">
  <img alt="OpenAI" src="https://img.shields.io/badge/OpenAI-chat--completions-412991?logo=openai&logoColor=white">
</p>

A minimal PHP backend for the **ChatSearch** Chrome extension. It exposes a single
endpoint that forwards a chat conversation to the OpenAI chat-completions API and
returns the assistant's reply, so the API key never ships in the extension.

## How it works

`index.php` is the whole server. On a request it:

1. Loads the OpenAI API key from a `.env` file via [phpdotenv](https://github.com/vlucas/phpdotenv).
2. Builds a [Guzzle](https://docs.guzzlephp.org/) client pointed at `https://api.openai.com/v1/`.
3. Sends permissive CORS headers and answers the `OPTIONS` preflight.
4. On `POST /api/chat`, forwards the `messages` array to `chat/completions` and
   returns the first choice's message content as JSON.

## Endpoint

### `POST /api/chat`

Request body:

```json
{
  "model": "gpt-3.5-turbo",
  "messages": [
    { "role": "user", "content": "Hello!" }
  ]
}
```

- `messages` — required. An array of chat messages in OpenAI's format.
- `model` — optional. Defaults to `gpt-3.5-turbo`.

Success response (`200`):

```json
{ "response": "Hi there — how can I help?" }
```

Error responses:

- `400` — invalid input (e.g. missing `messages`), as `{ "error": "..." }`.
- `404` — any route other than `POST /api/chat`.
- `500` — the upstream OpenAI request failed, with the error message forwarded.

CORS is open (`Access-Control-Allow-Origin: *`) for local development — in
production, replace the `*` with your extension's origin in `index.php`.

## Getting started

**Requirements:** PHP 7.4+ and [Composer](https://getcomposer.org/).

```bash
# 1. Install dependencies (Guzzle + phpdotenv)
composer install

# 2. Configure your OpenAI key
echo "OPENAI_API_KEY=sk-your-key-here" > .env

# 3. Run it locally
php -S localhost:8000
```

Then call the endpoint:

```bash
curl -X POST http://localhost:8000/api/chat \
  -H "Content-Type: application/json" \
  -d '{"messages":[{"role":"user","content":"Hello!"}]}'
```

> The `.env` file holds your secret key and is not committed — keep it out of
> version control.

## Tech stack

- **PHP** — single-file HTTP handler (`index.php`).
- **[Guzzle](https://docs.guzzlephp.org/)** — HTTP client for the OpenAI API.
- **[phpdotenv](https://github.com/vlucas/phpdotenv)** — loads the API key from `.env`.

## License

Released under the [MIT License](LICENSE) © 2026 Olivier Lüthy. You're free to use, modify and distribute this
software, including commercially, as long as the copyright notice and license are included.

## Author

Built by **Olivier Lüthy** — [GitHub](https://github.com/olivierluethy).
