# School Management System - Production Readiness Guide

## Prerequisites
- PHP 8.4+
- Node.js 20+
- Composer 2.7+
- Database (MySQL, PostgreSQL, or MariaDB recommended for production)
- Web server (Apache, Nginx)
- SSL/TLS certificate (HTTPS required for production)

---

## Step 1: Environment Configuration

Copy `.env.example` to `.env` and update production values:
```bash
cp .env.example .env
```

Set production mode and disable debug:
```env
APP_NAME="School Management System"
APP_ENV=production
APP_KEY= # Generate with `php artisan key:generate`
APP_DEBUG=false
APP_URL=https://your-school-domain.com
```

---

## Step 2: Database Configuration

For production use a dedicated database (not SQLite):

### MySQL/MariaDB Example:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_management
DB_USERNAME=school_admin
DB_PASSWORD=secure-password
```

---

## Step 3: Caching & Performance

Optimize Laravel for production:
```bash
# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Run all optimization commands
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## Step 4: Queue Workers

The system uses queues for SMS and other background tasks! Set up a queue worker with Supervisor (recommended):

### Install Supervisor (Ubuntu/Debian):
```bash
sudo apt install supervisor -y
```

### Create queue worker config (`/etc/supervisor/conf.d/school-worker.conf`):
```ini
[program:school-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600
```

### Start Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start school-worker:*
```

---

## Step 5: SSL & HTTPS

Always use HTTPS in production!

### Let's Encrypt (free SSL):
For Nginx:
```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d your-school-domain.com
```

---

## Step 6: Backups

Configure automated database backups!

### Example backup command:
```bash
mysqldump -u school_admin -p school_management | gzip > $(date +%Y-%m-%d)-school-db.sql.gz
```

---

## Step 7: Security Hardening

1. Disable public access to `storage/logs/` and other sensitive directories
2. Enable 2FA (Two-Factor Authentication) for all admin users
3. Regularly update dependencies:
   ```bash
   composer audit
   npm audit
   ```

---

## Step 8: Deploy Frontend Assets

Build production-ready assets:
```bash
npm run build
```

---

## Step 9: Maintenance Mode (for updates)

Enable maintenance mode before deploying changes:
```bash
php artisan down --secret="your-secret-password"
php artisan up
```

---

## Docker Deployment (Optional but Recommended)

We provide `Dockerfile` and `docker-compose.prod.yml` for containerized deployment.

### Build & Deploy with Docker:
```bash
docker-compose -f docker-compose.prod.yml up -d
```
