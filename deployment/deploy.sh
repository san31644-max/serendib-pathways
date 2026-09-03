#!/usr/bin/env bash
set -euo pipefail

SOURCE_DIR=/opt/serendib-source
WEB_DIR=/var/www/html

cd "$SOURCE_DIR"
git fetch --quiet origin main

current_commit=$(git rev-parse HEAD)
remote_commit=$(git rev-parse origin/main)

if [[ "$current_commit" == "$remote_commit" ]]; then
  exit 0
fi

git reset --hard --quiet origin/main
rsync -a \
  --exclude='.git/' \
  --exclude='.github/' \
  --exclude='config/database.php' \
  --exclude='config/gemini.local.php' \
  --exclude='assets/uploads/' \
  "$SOURCE_DIR/" "$WEB_DIR/"

chown -R ec2-user:apache "$WEB_DIR"
find "$WEB_DIR" -type d -exec chmod 2775 {} \;
find "$WEB_DIR" -type f -exec chmod 0664 {} \;
apachectl configtest
systemctl reload httpd

logger -t serendib-deploy "Deployed commit $remote_commit"
