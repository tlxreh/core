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

exec "$@"
