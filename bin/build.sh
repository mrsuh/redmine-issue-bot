#!/bin/sh

Fail() { echo "ERROR: $@" 1>&2; exit 1; }

for c in php ; do
  which $c >/dev/null 2>&1 || Fail "$c not found"
done

# This script will stop when one of the commands returns with a non-zero value
set -ex pipefail

# Need to change directory to one level up from current script location
cd "$(cd `dirname $0` && pwd)/.."

rm -rf var/cache/*

composer install --prefer-dist --no-interaction
composer dump-autoload --optimize --classmap-authoritative