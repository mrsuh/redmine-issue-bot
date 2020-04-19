# Redmine issue bot

![](https://github.com/mrsuh/redmine-issue-bot/workflows/Tests/badge.svg)

* track issue time
* sync issue statuses

## Installation
```bash
cp .env .env.local
sh bin/build.sh
```

## Run
```bash
sh bin/run.sh
```

## Tests
```bash
sh bin/test.sh
```

## Configuration
#### Multiple users
.env.local
```dotenv
REDMINE_HTTP_URL='http://@127.0.0.1:9999?token=ADMIN_TOKEN'
TRACK_TIME_USER_IDS='5,6,7,'
SYNC_ISSUES_STATUS_USER_IDS='5,6,7'
```
#### Personal usage
.env.local
```dotenv
REDMINE_HTTP_URL='http://@127.0.0.1:9999?token=USER_TOKEN'
TRACK_TIME_USER_IDS='me'
SYNC_ISSUES_STATUS_USER_IDS='me'
```