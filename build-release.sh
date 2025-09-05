#!/bin/bash
#
# NS Mega Menu 配布用ZIP作成スクリプト
# GPL-2.0-or-later
#

set -e

# 設定
PLUGIN_NAME="ns-mega-menu"
VERSION="0.10.0"
BUILD_DIR="build"
ARCHIVE_NAME="${PLUGIN_NAME}-${VERSION}"

echo "=== NS Mega Menu 配布用ZIP作成 ==="
echo "プラグイン: ${PLUGIN_NAME}"
echo "バージョン: ${VERSION}"
echo ""

# ビルドディレクトリをクリーンアップ
if [ -d "${BUILD_DIR}" ]; then
    echo "既存のビルドディレクトリを削除..."
    rm -rf "${BUILD_DIR}"
fi

mkdir -p "${BUILD_DIR}"

# Git archiveで配布用ファイルを抽出（.gitattributesに従って開発ファイルを除外）
echo "配布用ファイルを抽出中..."
git archive --format=tar --prefix="${PLUGIN_NAME}/" HEAD | tar -xf - -C "${BUILD_DIR}"

# 開発依存関係がある場合は手動で削除（念のため）
cd "${BUILD_DIR}/${PLUGIN_NAME}"

# Composer依存関係を削除
if [ -d "vendor" ]; then
    echo "vendor/ ディレクトリを削除..."
    rm -rf vendor/
fi

# 開発用ファイルを削除
echo "開発用ファイルを削除..."
rm -f composer.json composer.lock
rm -f phpcs.xml phpstan.neon phpstan-bootstrap.php
rm -f .gitattributes .gitignore
rm -rf .git/

# 不要なドキュメントを削除（配布版では簡略化）
rm -f docs/DEVELOPER.md docs/BLOCK-NEXT.md
rm -f TESTING.md CONTRIBUTING.md

echo "配布用ファイル一覧:"
find . -type f | sort

# ZIPファイルを作成
cd ..
echo ""
echo "ZIPファイルを作成中..."
zip -r "${ARCHIVE_NAME}.zip" "${PLUGIN_NAME}/"

# ファイルサイズを表示
echo ""
echo "=== 作成完了 ==="
echo "ファイル: ${BUILD_DIR}/${ARCHIVE_NAME}.zip"
echo "サイズ: $(du -h "${ARCHIVE_NAME}.zip" | cut -f1)"
echo ""

# ZIPの内容を確認
echo "ZIP内容の確認:"
unzip -l "${ARCHIVE_NAME}.zip" | head -20

echo ""
echo "✅ 配布用ZIPの作成が完了しました！"
echo "ファイル場所: $(pwd)/${ARCHIVE_NAME}.zip"