# 勤怠管理アプリ

## 環境構築

### Dockerビルド

1. GitHubからクローン
2. DockerDesktopアプリを立ち上げる
3. docker-compose up -d --build

```bash
git clone git@github.com:xxxxx/coachtech-kintai.git
cd coachtech-kintai
docker-compose up -d --build
```

## Laravel環境構築

1. PHPコンテナへログイン

```bash
docker-compose exec php bash
```

2. composer install

```bash
composer install
```

3.「.env.example」ファイルから「.env」を作成し、環境変数を変更

```bash
cp .env.example .env
```

4. アプリケーションキーの作成

```bash
php artisan key:generate
```

5. マイグレーション実行

```bash
php artisan migrate
```

6. シーディング実行

```bash
php artisan db:seed
```

## メール認証

MailHogを使用しています。

http://localhost:8025

会員登録後、MailHogに認証メールが送信されます。

認証URLをクリックするとログイン可能になります。

.env

```env
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_FROM_ADDRESS=test@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

## 使用技術（実行環境）

- PHP 8.1.33
- Laravel 8.83.29
- MySQL 8.0.26
- nginx 1.21.1
- Docker/Docker Compose

## テーブル仕様

### usersテーブル

| カラム名 | 型 | primary key | unique key | not null | foreign key |
|---|---|---|---|---|---|
| id | bigint unsigned | ○ |  | ○ |  |
| name | varchar(255) |  |  | ○ |  |
| email | varchar(255) |  | ○ | ○ |  |
| email_verified_at | timestamp |  |  |  |  |
| password | varchar(255) |  |  | ○ |  |
| role | varchar(20) |  |  | ○ |  |
| two_factor_secret | text |  |  |  |  |
| two_factor_recovery_codes | text |  |  |  |  |
| two_factor_confirmed_at | timestamp |  |  |  |  |
| remember_token | varchar(100) |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

---

### attendancesテーブル

| カラム名 | 型 | primary key | unique key | not null | foreign key |
|---|---|---|---|---|---|
| id | bigint unsigned | ○ |  | ○ |  |
| user_id | bigint unsigned |  |  | ○ | users(id) |
| work_date | date |  |  | ○ |  |
| clock_in_at | datetime |  |  |  |  |
| clock_out_at | datetime |  |  |  |  |
| status | varchar(20) |  |  | ○ |  |
| note | text |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

---

### attendance_breaksテーブル

| カラム名 | 型 | primary key | unique key | not null | foreign key |
|---|---|---|---|---|---|
| id | bigint unsigned | ○ |  | ○ |  |
| attendance_id | bigint unsigned |  |  | ○ | attendances(id) |
| break_start_at | datetime |  |  | ○ |  |
| break_end_at | datetime |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

---

### attendance_correction_requestsテーブル

| カラム名 | 型 | primary key | unique key | not null | foreign key |
|---|---|---|---|---|---|
| id | bigint unsigned | ○ |  | ○ |  |
| attendance_id | bigint unsigned |  |  | ○ | attendances(id) |
| user_id | bigint unsigned |  |  | ○ | users(id) |
| requested_clock_in_at | datetime |  |  |  |  |
| requested_clock_out_at | datetime |  |  |  |  |
| reason | text |  |  | ○ |  |
| status | varchar(20) |  |  | ○ |  |
| reviewed_by | bigint unsigned |  |  |  | users(id) |
| reviewed_at | datetime |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

---

### attendance_correction_request_breaksテーブル

| カラム名 | 型 | primary key | unique key | not null | foreign key |
|---|---|---|---|---|---|
| id | bigint unsigned | ○ |  | ○ |  |
| attendance_correction_request_id | bigint unsigned |  |  | ○ | attendance_correction_requests(id) |
| requested_break_start_at | datetime |  |  | ○ |  |
| requested_break_end_at | datetime |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

## ER図

```mermaid
erDiagram
    users ||--o{ attendances : has
    users ||--o{ attendance_correction_requests : requests
    users ||--o{ attendance_correction_requests : reviews
    attendances ||--o{ attendance_breaks : has
    attendances ||--o{ attendance_correction_requests : has
    attendance_correction_requests ||--o{ attendance_correction_request_breaks : has

    users {
        bigint id PK
        varchar name
        varchar email UK
        timestamp email_verified_at
        varchar password
        varchar role
        timestamp created_at
        timestamp updated_at
    }

    attendances {
        bigint id PK
        bigint user_id FK
        date work_date
        datetime clock_in_at
        datetime clock_out_at
        varchar status
        text note
        timestamp created_at
        timestamp updated_at
    }

    attendance_breaks {
        bigint id PK
        bigint attendance_id FK
        datetime break_start_at
        datetime break_end_at
        timestamp created_at
        timestamp updated_at
    }

    attendance_correction_requests {
        bigint id PK
        bigint attendance_id FK
        bigint user_id FK
        datetime requested_clock_in_at
        datetime requested_clock_out_at
        text reason
        varchar status
        bigint reviewed_by FK
        datetime reviewed_at
        timestamp created_at
        timestamp updated_at
    }

    attendance_correction_request_breaks {
        bigint id PK
        bigint attendance_correction_request_id FK
        datetime requested_break_start_at
        datetime requested_break_end_at
        timestamp created_at
        timestamp updated_at
    }

## URL

- 開発環境：http://localhost/
- phpMyAdmin：http://localhost:8080/
- MailHog：http://localhost:8025/

## テスト実行
```bash
php artisan test
```


### 管理者アカウント

- email：admin@coachtech.com
- password：password

### 一般ユーザー

- email：reina.n@coachtech.com
- password：password

- taro.y@coachtech.com / password
- issei.m@coachtech.com / password
- keikichi.y@coachtech.com / password
- tomomi.a@coachtech.com / password
- norio.n@coachtech.com / password

## ダミーデータ

```bash
php artisan db:seed
```

以下のデータを作成しています。

- 管理者
- 一般ユーザー
- 勤怠データ
- 修正申請データ

## PHPUnitを利用したテスト

### テスト実行

```bash
php artisan test
```

### テスト用DB作成

```bash
docker-compose exec mysql bash
mysql -u root -p
```

パスワードは `root`

```sql
create database test_database;
```

```bash
docker-compose exec php bash
php artisan migrate:fresh --env=testing
./vendor/bin/phpunit
```

- 以下のテストを実施済みです。

- 会員登録機能
- ログイン機能
- 勤怠ステータス機能
- 出勤機能
- 休憩機能
- 退勤機能
- 勤怠一覧表示機能
- 勤怠詳細表示機能
- 修正申請機能
- 管理者承認機能

## 機能一覧

### 一般ユーザー
- 会員登録
- ログイン
- ログアウト
- メール認証
- 出勤打刻
- 退勤打刻
- 休憩開始
- 休憩終了
- 勤怠一覧表示
- 勤怠詳細表示
- 勤怠修正申請

### 管理者
- 管理者ログイン
- 全ユーザー勤怠一覧表示
- スタッフ一覧表示
- 勤怠詳細修正
- 修正申請承認
- 修正申請却下


## レスポンシブ対応

1400px〜1540pxでレイアウト崩れが発生しないよう実装しています。