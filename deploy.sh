#!/usr/bin/env bash
# Đẩy code lên GitHub rồi kéo về server (git pull qua SSH).
# Dùng: ./deploy.sh
set -euo pipefail

SSH_KEY="$HOME/.ssh/id_ed25519_onehost"
SSH_PORT=65333
SSH_TARGET="stqvakdvhosting@103.75.184.20"
REMOTE_DIR="domains/chungnhantangni.ghpgvn.vn"
PHP=/opt/alt/php84/usr/bin/php  # CLI mặc định là 8.1, site chạy 8.4

if [[ -n "$(git status --porcelain)" ]]; then
  echo "Còn thay đổi chưa commit. Hãy commit trước khi deploy." >&2
  exit 1
fi

git push origin main

ssh -i "$SSH_KEY" -p "$SSH_PORT" "$SSH_TARGET" "
  set -e
  PHP=$PHP
  cd $REMOTE_DIR
  git fetch origin main
  git reset --hard origin/main
  $PHP \$(which composer) install --no-dev --optimize-autoloader --no-interaction
  \$PHP artisan migrate --force
  \$PHP artisan optimize:clear
  \$PHP artisan optimize
"
echo "Deploy xong."
