# PANDUAN DEPLOYMENT KE SERVER PRODUKSI
## SMART WATER KOTA PAYAKUMBUH

---

## 1. Arsitektur Server Produksi yang Disarankan
- **Sistem Operasi**: Ubuntu 22.04 LTS / Debian 12 / Rocky Linux 9.
- **Web Server**: Nginx (direkomendasikan) atau Apache 2.4.
- **PHP**: PHP 8.2-FPM atau 8.3-FPM.
- **Database**: MySQL 8.0+ / MariaDB 10.11+ LTS.
- **SSL**: Sertifikat SSL TLS (Let's Encrypt / DigiCert).

---

## 2. Langkah Deployment Bertahap

### Langkah 1: Klon Repositori di Server
```bash
cd /var/www
git clone https://github.com/organization/smart-water.git smart-water
cd smart-water
```

### Langkah 2: Atur Kepemilikan & Hak Akses Direktori
```bash
sudo chown -R www-data:www-data /var/www/smart-water
sudo chmod -R 775 /var/www/smart-water/storage /var/www/smart-water/bootstrap/cache
```

### Langkah 3: Pasang Dependensi Produksi (Tanpa Dev Packages)
```bash
composer install --no-dev --optimize-autoloader
```

### Langkah 4: Konfigurasi File Lingkungan Produksi (`.env`)
```bash
cp .env.example .env
nano .env
```
Sesuaikan parameter penting:
```env
APP_NAME="Smart Water Kota Payakumbuh"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://smartwater.payakumbuhkota.go.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartwater_prod
DB_USERNAME=smartwater_user
DB_PASSWORD=KATA_SANDI_SANGAT_KUAT_PRODUKSI

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```

### Langkah 5: Generate Key, Migrasi, & Storage Link
```bash
php artisan key:generate --force
php artisan migrate --seed --force
php artisan storage:link
```

### Langkah 6: Optimasi Cache Laravel
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 3. Konfigurasi Virtual Host Nginx
```nginx
server {
    listen 80;
    server_name smartwater.payakumbuhkota.go.id;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name smartwater.payakumbuhkota.go.id;

    ssl_certificate /etc/letsencrypt/live/smartwater.payakumbuhkota.go.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/smartwater.payakumbuhkota.go.id/privkey.pem;

    root /var/www/smart-water/public;
    index index.php index.html;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 4. Prosedur Pencadangan (Backup) & Pemulihan (Restore)

### Backup Database Harian:
```bash
mysqldump -u smartwater_user -p smartwater_prod > /backup/smartwater_$(date +\%F).sql
```

### Backup Direktori Unggahan Bukti Lapor Meter & Pengaduan:
```bash
tar -czvf /backup/uploads_$(date +\%F).tar.gz /var/www/smart-water/storage/app/public/
```

### Restore Database:
```bash
mysql -u smartwater_user -p smartwater_prod < /backup/smartwater_YYYY-MM-DD.sql
```
