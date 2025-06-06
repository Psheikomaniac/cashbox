#!/bin/sh
# docker/php/health-check.sh
set -e

# Prüfen, ob der PHP-Server läuft
if [ "$1" = "web" ]; then
  if curl -f http://localhost:8000/health > /dev/null 2>&1; then
    echo "Web server is running"
    exit 0
  else
    echo "Web server is not running"
    exit 1
  fi
fi

# Prüfen, ob PHP-FPM läuft
if [ "$1" = "fpm" ]; then
  if pgrep php-fpm > /dev/null; then
    echo "PHP-FPM is running"
    exit 0
  else
    echo "PHP-FPM is not running"
    exit 1
  fi
fi

# Standard: PHP-Befehl ausführen
php -v > /dev/null
exit $?
