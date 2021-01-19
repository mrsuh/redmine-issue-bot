# Redmine issue bot

* sync issue statuses
* tg notification

## Installation
```bash
cp .env .env.local
sh bin/build.sh
```

## Usage

```bash
# add user
php bin/console user:upsert --redmineId 50 --redmineLogin anton --telegramLogin 'Anton'

#add status
php bin/console status:upsert --redmineId 1 --redmineName 'New' --type new

#notify to tg
php bin/console issue:notify

#sync issue statuses
php bin/console issue:sync --period=3
```

## Tests
```bash
sh bin/test.sh
```

## Configuration

.env.local

```dotenv
REDMINE_HTTP_URL='http://basicAuthUsername:basicAuthPassword@127.0.0.1:9999?token=token&timeout=3&connectionTimeout=3'
TELEGRAM_TOKEN='token:token'
TELEGRAM_CHAT_ID='chat_id'
```

## TG Webhook

```bash
curl -X POST —data '{"url": "https://yourdomain.ru/telegram/webhook"}' -H "Content-Type: application/json" "https://api.telegram.org/bot{your_bot_token}/setWebhook"
```
