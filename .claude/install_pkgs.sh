#!/bin/bash
# リモート環境（CC on Web）でのみ実行
if [ "$CLAUDE_CODE_REMOTE" != "true" ]; then
  exit 0
fi

PHP_VERSION=8.5

# -------------------------------------------------------
# PHP 環境のセットアップ
# -------------------------------------------------------
# CC on Web 環境ではプロキシにより ppa.launchpadcontent.net が
# ブロックされるため、apt での PPA パッケージ取得は不可。
# 代わりに shivammathur/php-builder の事前ビルド済みバイナリを
# GitHub Releases から取得し、拡張が必要とするシステムライブラリは
# Ubuntu 標準リポジトリ (archive.ubuntu.com) からインストールする。
# -------------------------------------------------------
if ! php -v 2>/dev/null | grep -q "PHP ${PHP_VERSION}"; then
  UBUNTU_VERSION=$(grep VERSION_ID /etc/os-release | cut -d'"' -f2)
  TAR_FILE="php_${PHP_VERSION}+ubuntu${UBUNTU_VERSION}.tar.xz"
  DOWNLOAD_URL="https://github.com/shivammathur/php-builder/releases/download/${PHP_VERSION}/${TAR_FILE}"

  curl -sL "$DOWNLOAD_URL" -o "/tmp/${TAR_FILE}"
  tar -xJf "/tmp/${TAR_FILE}" -C /
  rm -f "/tmp/${TAR_FILE}"

  # 拡張が必要とするシステムライブラリをインストール
  apt-get install -y -q \
    liblmdb0 libqdbm14 \
    libenchant-2-2 \
    libmagickwand-6.q16-7t64 \
    libc-client2007e \
    libmemcached11t64 \
    unixodbc \
    libsybdb5 \
    libfbclient2 \
    libsnmp40t64 \
    libtidy5deb1 \
    libzmq5

  # デフォルトの PHP バージョンを切り替え
  update-alternatives --install /usr/bin/php php "/usr/bin/php${PHP_VERSION}" 85
  update-alternatives --set php "/usr/bin/php${PHP_VERSION}"
fi

# Composer のインストール（未インストールの場合）
if ! command -v composer &>/dev/null; then
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# -------------------------------------------------------
# 依存関係のインストール
# -------------------------------------------------------
cd "$CLAUDE_PROJECT_DIR"
composer install --no-interaction --prefer-dist
