#!/bin/sh
set -e
cd "$(dirname "$0")/.."
DATA_DIR="${DATA_DIR:-$(pwd)/data}"
export DATA_DIR
mkdir -p "$DATA_DIR" parseh_app/storage/logs parseh_app/storage/private public_html/uploads
cp docker/config.demo.php parseh_app/config.php
if [ ! -f "$DATA_DIR/parseh.db" ]; then
  php docker/init-demo.php
fi
export PHP_CLI_SERVER_WORKERS=4
exec php -d upload_max_filesize=16M -d post_max_size=40M -S 0.0.0.0:${PORT:-8080} -t public_html docker/router.php
