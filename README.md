# RPWVERSE — Social Community Platform

RPWVERSE is a PHP/MySQL RPW social platform with one shared public news feed (no separate Groups or Marketplace).

## Features
- Account registration and login with hashed passwords
- Public text and photo posts (JPG, PNG, GIF, WEBP; max 5 MB)
- Clickable RP profiles with bio, profile photo, cover photo, and public post history
- Comments and four reactions: Heart, Wow, Angry, Sad
- Follow/unfollow members, notifications, and private messages
- Members can permanently delete their own account after confirming their current password and typing DELETE
- Admin dashboard for reports, posts, and member moderation
- Pastel pink responsive design

## XAMPP installation
1. Extract the `RPWVERSE` folder to `C:\xampp\htdocs\RPWVERSE`.
2. Start Apache and MySQL in XAMPP.
3. Open `http://localhost/phpmyadmin`.
4. If this is a fresh installation, import `database/rpwverse.sql`.
5. If you already used the earlier RPWVERSE version and want to KEEP your registered account, select the existing `rpwverse` database and import `database/upgrade_social.sql` ONCE. Do not import the fresh schema over an existing database as a substitute for the upgrade.
6. Visit `http://localhost/RPWVERSE/`.

## Admin access
Register normally, then in phpMyAdmin select `rpwverse` > SQL and run (replace the email with your own registered email):
```sql
UPDATE users SET role='admin' WHERE email='your-email@example.com';
```
Log out and log in again. There is no separate admin password.

## Account deletion
Members can open **My RP Profile** and scroll to **Delete my account**. They must enter their current password and type `DELETE`. The deletion is permanent and removes the member's related posts, comments, reactions, follows, notifications, reports, messages, and uploaded profile/post images through the configured database relationships.

## Configuration
Database settings and `BASE_URL` are in `config/config.php`. Default XAMPP settings are host `localhost`, user `root`, blank password, database `rpwverse`.

## Notes
This is a local development starter, not a production-audited service. Before public deployment, configure HTTPS, secure database credentials, rate limiting, upload scanning, backups, privacy/terms pages, moderation procedures, and abuse prevention. Never ask users for GCash PINs, OTPs, or account passwords.
