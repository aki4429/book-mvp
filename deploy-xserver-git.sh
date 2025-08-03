#!/bin/bash

# Xサーバー git clone デプロイスクリプト

# サーバー情報
SERVER_USER="xs5840"
SERVER_HOST="xs5840.xsrv.jp"
SERVER_PORT="10022"
SERVER_PATH="/home/xs5840/odachin.net/public_html/demo001"
REPO_URL="https://github.com/aki4429/book-mvp.git"

echo "🚀 Xサーバーへのデプロイを開始します..."
echo "サーバー: $SERVER_HOST"
echo "パス: $SERVER_PATH"
echo ""

# 1. アセットビルド
echo "🎨 アセットをビルド中..."
npm run build

if [ $? -ne 0 ]; then
    echo "❌ アセットビルドに失敗しました。"
    exit 1
fi

# 2. Git リポジトリをコミット・プッシュ
echo "📤 Gitにコミット・プッシュ中..."
git add .
git commit -m "Deploy to Xserver $(date)"
git push origin main

# 3. サーバーに接続してデプロイ
echo "🔗 サーバーに接続してデプロイ中..."

ssh -p $SERVER_PORT $SERVER_USER@$SERVER_HOST << 'ENDSSH'

# デプロイ先パス
DEPLOY_PATH="/home/xs5840/odachin.net/public_html/demo001"
REPO_URL="https://github.com/aki4429/book-mvp.git"

echo "📁 デプロイディレクトリを準備中..."

# 既存のディレクトリがある場合はバックアップ
if [ -d "$DEPLOY_PATH" ]; then
    echo "🔄 既存のアプリケーションをバックアップ中..."
    mv "$DEPLOY_PATH" "${DEPLOY_PATH}_backup_$(date +%Y%m%d_%H%M%S)"
fi

echo "📥 リポジトリをクローン中..."
git clone "$REPO_URL" "$DEPLOY_PATH"

if [ $? -ne 0 ]; then
    echo "❌ Git クローンに失敗しました。"
    exit 1
fi

cd "$DEPLOY_PATH"

echo "📦 Composer依存関係をインストール中..."
/usr/bin/php8.2 /usr/bin/composer install --no-dev --optimize-autoloader --no-interaction

echo "🔧 環境設定中..."
# .env.production を .env にコピー
cp .env.production .env

# データベースパスワードを設定（手動で更新が必要）
echo "⚠️  データベースパスワードを手動で設定してください:"
echo "   nano .env"
echo "   DB_PASSWORD=YOUR_ACTUAL_PASSWORD"

echo "🔑 アプリケーションキーを生成中..."
/usr/bin/php8.2 artisan key:generate --force

echo "🧹 キャッシュをクリア中..."
/usr/bin/php8.2 artisan config:clear
/usr/bin/php8.2 artisan route:clear
/usr/bin/php8.2 artisan view:clear

echo "⚡ 本番用キャッシュを生成中..."
/usr/bin/php8.2 artisan config:cache
/usr/bin/php8.2 artisan route:cache
/usr/bin/php8.2 artisan view:cache

echo "🗂️ ディレクトリ権限を設定中..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo "🎯 シンボリックリンクを作成中..."
/usr/bin/php8.2 artisan storage:link

echo "✅ デプロイ完了！"
echo ""
echo "📋 次の手順を手動で実行してください:"
echo "1. データベースの作成（XサーバーコントロールパネルでMySQL追加）"
echo "2. .envファイルでデータベースパスワードを設定"
echo "3. マイグレーション実行:"
echo "   cd $DEPLOY_PATH"
echo "   /usr/bin/php8.2 artisan migrate --force"
echo "4. 初期データ投入（必要に応じて）:"
echo "   /usr/bin/php8.2 artisan db:seed --force"

ENDSSH

echo ""
echo "🎉 デプロイスクリプト完了！"
echo ""
echo "🔗 アクセスURL: https://odachin.net/demo001"
echo ""
echo "📋 サーバー上での最終設定:"
echo "1. SSH接続: ssh -p 10022 xs5840@xs5840.xsrv.jp"
echo "2. アプリケーションディレクトリに移動: cd /home/xs5840/odachin.net/public_html/demo001"
echo "3. .envファイルを編集: nano .env"
echo "4. データベースパスワードを設定"
echo "5. マイグレーション実行: /usr/bin/php8.2 artisan migrate --force"
