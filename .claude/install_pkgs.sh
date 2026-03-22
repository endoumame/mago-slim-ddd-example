#!/bin/bash
# リモート環境（CC on Web）でのみ実行
if [ "$CLAUDE_CODE_REMOTE" != "true" ]; then
  exit 0
fi

set -euo pipefail

PHP_VERSION="8.5.4"
PHP_PREFIX="/usr/local/php85"

# -------------------------------------------------------
# PHP 環境のセットアップ
# -------------------------------------------------------
# すでにビルド済みならスキップ
if [ -x "$PHP_PREFIX/bin/php" ]; then
  echo "PHP $PHP_VERSION is already installed at $PHP_PREFIX"
else
  echo "Building PHP $PHP_VERSION from source..."

  # ビルド依存パッケージ（Ubuntu 公式リポジトリから取得）
  apt-get install -y -q \
    autoconf bison re2c gcc make \
    libxml2-dev libcurl4-openssl-dev libonig-dev libzip-dev \
    libreadline-dev libsqlite3-dev zlib1g-dev libssl-dev \
    libsodium-dev \
    2>/dev/null

  # GitHub からソースをダウンロード（PPA はプロキシでブロックされるため）
  BUILD_DIR=$(mktemp -d)
  curl -sL "https://github.com/php/php-src/archive/refs/tags/php-${PHP_VERSION}.tar.gz" \
    -o "$BUILD_DIR/php-src.tar.gz"
  tar xzf "$BUILD_DIR/php-src.tar.gz" -C "$BUILD_DIR"

  cd "$BUILD_DIR/php-src-php-${PHP_VERSION}"

  ./buildconf --force
  ./configure \
    --prefix="$PHP_PREFIX" \
    --with-openssl \
    --with-curl \
    --with-zlib \
    --with-readline \
    --with-sodium \
    --with-libxml \
    --enable-mbstring \
    --enable-sockets \
    --enable-pcntl \
    --enable-dom \
    --enable-xml \
    --enable-phar \
    --enable-tokenizer \
    --enable-simplexml \
    --enable-xmlwriter \
    --enable-xmlreader

  make -j"$(nproc)"
  make install

  # sodium 拡張を有効化
  echo "extension=sodium" > "$PHP_PREFIX/lib/php.ini"

  # クリーンアップ
  rm -rf "$BUILD_DIR"

  echo "PHP $PHP_VERSION built and installed at $PHP_PREFIX"
fi

# デフォルトの php コマンドとして登録
update-alternatives --install /usr/bin/php php "$PHP_PREFIX/bin/php" 85
update-alternatives --set php "$PHP_PREFIX/bin/php"

echo "Active PHP: $(php -v | head -1)"

# -------------------------------------------------------
# Composer のセットアップ
# -------------------------------------------------------
# システムの Composer が存在すればそれを使用
if ! command -v composer &>/dev/null; then
  echo "WARNING: Composer not found. Install it manually."
else
  echo "Composer: $(composer --version)"
fi

# -------------------------------------------------------
# 依存関係のインストール
# -------------------------------------------------------
cd "$CLAUDE_PROJECT_DIR"
composer install --no-interaction --prefer-dist --ignore-platform-req=ext-bcmath
