#!/bin/bash
# リモート環境（CC on Web）でのみ実行
if [ "$CLAUDE_CODE_REMOTE" != "true" ]; then
  exit 0
fi

# -------------------------------------------------------
# PHP 環境のセットアップ
# -------------------------------------------------------
# PHP 8.4+ と必要な拡張のインストール
apt-get install -y -q php php-cli php-mbstring php-xml php-curl php-zip unzip 2>/dev/null || true

# Composer のインストール（未インストールの場合）
if ! command -v composer &>/dev/null; then
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# -------------------------------------------------------
# 依存関係のインストール
# -------------------------------------------------------
cd "$CLAUDE_PROJECT_DIR"
composer install --no-interaction --prefer-dist
