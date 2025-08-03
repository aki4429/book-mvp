#!/bin/bash

# Xサーバー デプロイスクリプト (Livewire除去版)

echo "🚀 Xサーバーデプロイの準備中（Livewire除去版）..."

# 1. Livewire関連のパッケージを除去
echo "🗑️  Livewire関連パッケージを除去中..."
composer remove livewire/livewire --no-interaction

# 2. 依存関係の最適化
echo "📦 Composer依存関係を最適化中..."
composer install --no-dev --optimize-autoloader

# 3. 設定キャッシュのクリア
echo "🧹 キャッシュをクリア中..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 4. 本番用キャッシュの生成
echo "⚡ 本番用キャッシュを生成中..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. アセットの確認
echo "🎨 アセットビルドの確認..."
if [ ! -d "public/build" ]; then
    echo "❌ エラー: public/buildディレクトリが見つかりません。"
    echo "   先に 'npm run build' を実行してください。"
    exit 1
fi

# 6. Livewire関連のビューファイルをバックアップ
echo "📋 Livewire関連ファイルをバックアップ中..."
mkdir -p backup/livewire-views
if [ -d "resources/views/livewire" ]; then
    cp -r resources/views/livewire backup/livewire-views/
fi

echo "✅ デプロイ準備完了（Livewire除去版）！"
echo ""
echo "📋 次のステップ:"
echo "1. 以下のファイル・ディレクトリをXサーバーにアップロード:"
echo "   - app/"
echo "   - bootstrap/"
echo "   - config/"
echo "   - database/"
echo "   - public/"
echo "   - resources/"
echo "   - routes/"
echo "   - storage/"
echo "   - vendor/"
echo "   - .htaccess"
echo "   - artisan"
echo "   - composer.json"
echo "   - composer.lock"
echo ""
echo "2. .env.productionを.envにリネーム"
echo "3. storage/とbootstrap/cache/の権限を755に設定"
echo "4. データベース情報を.envファイルで更新"
echo "5. マイグレーション実行: php artisan migrate --force"
echo ""
echo "🎯 変更点:"
echo "- Livewireを除去し、従来のAjax + Bladeテンプレートに変更"
echo "- /calendar: Livewireなし版の公開カレンダー"
echo "- /calendar-livewire: 旧Livewire版（バックアップ）"
