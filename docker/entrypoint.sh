#!/usr/bin/env bash
set -e

# Testbench hardcodes its mail settings in testbench.yaml, which would otherwise
# ignore the container environment. Align them with the provided env vars so the
# subscriber/verification emails can be captured — in the log by default, or in
# Mailpit when running via docker compose.
if [ -f testbench.yaml ]; then
    sed -i -E "s|(- MAIL_MAILER=).*|\1${MAIL_MAILER:-log}|" testbench.yaml
    sed -i -E "s|(- MAIL_HOST=).*|\1${MAIL_HOST:-127.0.0.1}|" testbench.yaml
    sed -i -E "s|(- MAIL_PORT=).*|\1${MAIL_PORT:-2525}|" testbench.yaml
fi

# Prepare the database. When the database directory is mounted as a persistent
# named volume it survives restarts; a fresh volume is seeded once, and pulling
# an updated image applies any pending migrations without wiping data.
DB_PATH="vendor/orchestra/testbench-core/laravel/database/database.sqlite"
if [ ! -f "$DB_PATH" ]; then
    echo "[cachet] No database found — creating, migrating and seeding."
    mkdir -p "$(dirname "$DB_PATH")"
    : > "$DB_PATH"
    php vendor/bin/testbench migrate --force --seed --seeder="Cachet\\Database\\Seeders\\DatabaseSeeder"
else
    echo "[cachet] Database found — applying any pending migrations."
    php vendor/bin/testbench migrate --force
fi

exec "$@"
