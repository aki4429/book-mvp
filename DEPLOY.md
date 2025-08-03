# Xサーバーデプロイ手順書

## 🚀 自動デプロイ（推奨）

```bash
./deploy-xserver-git.sh
```

## 📋 手動デプロイ手順

### 1. ローカルでの準備

```bash
# アセットビルド
npm run build

# Gitコミット・プッシュ
git add .
git commit -m "Deploy to Xserver"
git push origin main
```

### 2. サーバーへの接続

```bash
ssh -p 10022 xs5840@xs5840.xsrv.jp
```

### 3. アプリケーションのクローン

```bash
cd /home/xs5840/odachin.net/public_html/

# 既存のアプリケーションがある場合はバックアップ
if [ -d "demo001" ]; then
    mv demo001 demo001_backup_$(date +%Y%m%d_%H%M%S)
fi

# リポジトリをクローン
git clone https://github.com/aki4429/book-mvp.git demo001
cd demo001
```

### 4. 依存関係のインストール

```bash
/usr/bin/php8.2 /usr/bin/composer install --no-dev --optimize-autoloader --no-interaction
```

### 5. 環境設定

```bash
# 環境ファイルをコピー
cp .env.production .env

# アプリケーションキーを生成
/usr/bin/php8.2 artisan key:generate --force

# データベース設定を編集
nano .env
# DB_PASSWORD=YOUR_ACTUAL_PASSWORD に変更
```

### 6. キャッシュとパーミッション

```bash
# キャッシュクリア
/usr/bin/php8.2 artisan config:clear
/usr/bin/php8.2 artisan route:clear
/usr/bin/php8.2 artisan view:clear

# 本番用キャッシュ生成
/usr/bin/php8.2 artisan config:cache
/usr/bin/php8.2 artisan route:cache
/usr/bin/php8.2 artisan view:cache

# パーミッション設定
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# シンボリックリンク作成
/usr/bin/php8.2 artisan storage:link
```

### 7. データベース設定

#### Xサーバーコントロールパネルで:
1. MySQL → MySQL追加
2. データベース名: `xs5840_demo001`
3. ユーザー名: `xs5840`
4. パスワードを設定

#### サーバー上で:
```bash
# .envファイルを編集
nano .env

# 以下を設定:
DB_HOST=mysql5840.xserver.jp
DB_DATABASE=xs5840_demo001
DB_USERNAME=xs5840
DB_PASSWORD=設定したパスワード
```

### 8. マイグレーション実行

```bash
# マイグレーション実行
/usr/bin/php8.2 artisan migrate --force

# 初期データ投入（必要に応じて）
/usr/bin/php8.2 artisan db:seed --force
```

## 🔗 アクセス情報

- **URL**: https://odachin.net/demo001
- **管理者ログイン**: 
  - メール: admin@example.com
  - パスワード: password（初回ログイン後変更推奨）

## 🛠️ トラブルシューティング

### エラー時の確認事項:

1. **500エラー**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **パーミッションエラー**:
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

3. **データベース接続エラー**:
   - .envファイルのDB設定を確認
   - Xサーバーのデータベース設定を確認

4. **キャッシュ問題**:
   ```bash
   /usr/bin/php8.2 artisan config:clear
   /usr/bin/php8.2 artisan cache:clear
   ```

## 🔄 更新時の手順

```bash
cd /home/xs5840/odachin.net/public_html/demo001
git pull origin main
/usr/bin/php8.2 /usr/bin/composer install --no-dev --optimize-autoloader
/usr/bin/php8.2 artisan migrate --force
/usr/bin/php8.2 artisan config:cache
/usr/bin/php8.2 artisan route:cache
/usr/bin/php8.2 artisan view:cache
```
