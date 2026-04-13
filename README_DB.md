# Database setup (XAMPP / phpMyAdmin)

1. Start XAMPP and enable Apache + MySQL.
2. Open phpMyAdmin at: http://localhost/phpmyadmin
3. Create a database named `smartplate` (or another name — update `db.php` accordingly).

Run the following SQL to create the `comments` table:

```sql
CREATE TABLE `comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `article_id` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

4. Copy this project folder into XAMPP's `htdocs`, for example:

```
C:\xampp\htdocs\smartplate\
```

5. Open in browser: http://localhost/smartplate/inter-front.html

Notes:
- Update `db.php` if your MySQL username/password or database name differ.
- `status` = 0 means pending moderation; change workflow as needed.
