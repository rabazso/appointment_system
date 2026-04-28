#!/bin/bash

set -e

MODE="${1:-none}"

case "$MODE" in
  none|--migrate|-m|--fresh-seed|-fs) ;;
  -h|--help)
    echo "Használat: bash start.sh [--migrate|--fresh-seed|-m|-fs]"
    exit 0
    ;;
  *)
    echo "Ismeretlen kapcsoló: $MODE"
    echo "Használat: bash start.sh [--migrate|--fresh-seed|-m|-fs]"
    exit 1
    ;;
esac

if [ -f ".env" ]; then
    echo "A .env fájl már létezik"
else
    cp .env.example .env
fi

if ! docker volume inspect shared_pnpm >/dev/null 2>&1; then
  docker volume create shared_pnpm
fi

if ! docker volume inspect shared_composer >/dev/null 2>&1; then
  docker volume create shared_composer
fi

docker run --rm  -v "$(pwd)/frontend:/app" -v "shared_pnpm:/shared_pnpm/" --entrypoint pnpm  idomi27/vue:26 install --dangerously-allow-all-builds


docker compose up -d

docker compose exec backend composer install

if [ "$MODE" = "--fresh-seed" ] || [ "$MODE" = "-fs" ]; then
  echo "Adatbázis újraépítése és seedelése..."
  docker compose exec backend php artisan migrate:fresh --seed
elif [ "$MODE" = "--migrate" ] || [ "$MODE" = "-m" ]; then
  echo "Adatbázis migráció futtatása..."
  docker compose exec backend php artisan migrate
else
  echo "Adatbázis migráció és seeding kihagyva."
fi

if [ -z "${APP_KEY}" ]; then
    docker compose exec backend php artisan key:generate
else
    echo "Az API kulcs már létezik" 
fi
