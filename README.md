# Tamil News Portal

PHP MVC · Bootstrap 5 · PDO · FCM · YouTube API · RSS Engine

## Setup

1. Copy `.env.example` → `.env` and fill in values
2. Import `tamilnews_db.sql` into MySQL
3. Point web server document root to `/public`
4. Set cron jobs from `cron/crontab.txt`
5. Ensure `storage/` and `public/uploads/` are writable:
   ```
   chmod -R 775 storage/ public/uploads/
   ```
6. Visit `/admin/login` — create first admin user via SQL:
   ```sql
   INSERT INTO tn_users (role_id, name, email, password, is_active)
   VALUES (1, 'Admin', 'admin@example.com', '<bcrypt_hash>', 1);
   ```
   Generate hash in PHP: `echo password_hash('yourpassword', PASSWORD_BCRYPT);`

## Contributor Portal

| URL | Action |
|-----|--------|
| `/contribute/login` | Contributor Google OAuth login |
| `/contribute/dashboard` | Contributor dashboard |
| `/contribute/articles` | View all own submissions |
| `/contribute/articles/create` | Submit new article |
| `/admin/contributors` | Admin: manage all contributors |
| `/admin/contributors/show/{id}` | Admin: view one contributor's articles |

**Flow:**
1. Admin adds contributor (name + email) at `/admin/contributors`
2. Admin assigns categories to contributor
3. Contributor visits `/contribute/login` → signs in via Google
4. System matches their Google email → grants access
5. Contributor submits articles → lands as `review` status
6. Admin/Editor reviews and publishes from `/admin/articles`

## Reader Ratings

- Readers visit any article page
- Click "Sign in with Google" to rate
- 1–5 star rating + optional text review
- One rating per reader per article (can update)
- Include `partials/rating_widget.php` in your frontend article view

## Google OAuth Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
2. Create OAuth 2.0 Client ID (Web application)
3. Add Authorized redirect URIs:
   - `https://yourdomain.com/contribute/auth/callback`
   - `https://yourdomain.com/auth/reader/callback`
4. Copy Client ID and Secret to `.env`

## DB Additions

Run `db_additions.sql` after the main `tamilnews_db.sql`.
Order matters — run in this sequence:
```
1. tamilnews_db.sql         (main schema)
2. db_additions.sql         (contributors, readers, ratings)
```


```
*/5  * * * *  php /path/cron/scheduled_publish.php
0    * * * *  php /path/cron/youtube_import.php
*/30 * * * *  php /path/cron/rss_intake.php
```

## Admin Panel Modules

| URL                    | Module                  |
|------------------------|-------------------------|
| /admin/dashboard       | Dashboard + Stats       |
| /admin/articles        | Article Management      |
| /admin/categories      | Category CRUD + Sort    |
| /admin/tags            | Tag Management          |
| /admin/locations       | State/District/City     |
| /admin/media           | Media Library           |
| /admin/users           | User Management         |
| /admin/youtube         | YouTube Automation      |
| /admin/rss             | RSS Feed Management     |
| /admin/push            | FCM Push Notifications  |
| /admin/ads             | Ad Slot Manager         |
| /admin/analytics       | View Analytics          |
| /admin/settings        | All Settings            |
