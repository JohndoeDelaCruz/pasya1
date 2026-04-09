#!/bin/sh
set -eu

php artisan queue:work --verbose --tries=3 --timeout=90 --sleep=3
