# School Management System - Go-Live Checklist

## Production Setup Complete ✓

- ✅ Enhanced `composer.json` to block insecure packages
- ✅ Patched Laravel security vulnerability (v13.15.0 installed)
- ✅ Created production-ready Docker deployment files
- ✅ Created `PRODUCTION_READINESS.md` guide
- ✅ All security vulnerabilities fixed!

---

## Before Deploying to Production

### 1. Env File Setup
- [ ] Copy `.env.example` to `.env`
- [ ] Run `php artisan key:generate`
- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Configure database (MySQL/PostgreSQL, NOT SQLite for production)
- [ ] Set `APP_URL=https://your-domain.com`

### 2. Database
- [ ] Create dedicated database & user
- [ ] Run migrations: `php artisan migrate --force`
- [ ] (Optional) Seed initial data: `php artisan db:seed --force`

### 3. Optimizations
- [ ] Run:
  ```bash
  composer install --no-dev --optimize-autoloader
  npm run build
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```

### 4. SSL/HTTPS
- [ ] Obtain SSL certificate (Let's Encrypt recommended)
- [ ] Configure web server to use HTTPS only!

### 5. Queues & Background Jobs
- [ ] Set up Supervisor for queue worker (see `PRODUCTION_READINESS.md`)

---

## Docker Deployment (Quick)

Run this on your production server:
```bash
docker-compose -f docker-compose.prod.yml up -d --build
```
