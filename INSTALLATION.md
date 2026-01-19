# HRIS System Configuration

## 📋 Petunjuk Implementasi

### 1. Environment Setup
Pastikan sudah memiliki:
- XAMPP dengan PHP 7.4+
- MySQL 5.7+
- Minimal 100MB disk space

### 2. Installation Steps

```bash
# Step 1: Copy file ke xampp
Copy folder bsdmv2 ke C:\xampp\htdocs\

# Step 2: Start Services
- Start Apache
- Start MySQL

# Step 3: Import Database
- Buka http://localhost/phpmyadmin
- Import file: database/hris_db.sql

# Step 4: Access System
- Buka: http://localhost/bsdmv2/login.php
```

### 3. Login Credentials

**Admin:**
- Username: admin
- Password: admin123
- Role: Full Access

**Employee:**
- Username: karyawan1
- Password: admin123
- Role: Limited Access

**Manager:**
- Username: manajer1
- Password: admin123
- Role: Team Management

### 4. Database Connection

File: `config/database.php`

```php
DB_HOST: localhost
DB_USER: root
DB_PASS: (kosong/sesuai config)
DB_NAME: hris_db
```

### 5. Features Checklist

- [x] User Authentication & Authorization
- [x] Employee Management (CRUD)
- [x] Attendance Tracking
- [x] Leave Request System
- [x] Salary Management
- [x] Performance Evaluation
- [x] Department Management
- [x] Reporting & Analytics
- [x] Dashboard
- [x] Responsive Design

### 6. Security Checklist

- [x] Password Hashing (bcrypt)
- [x] Session Management
- [x] SQL Injection Prevention
- [x] Input Validation
- [x] Role-based Access
- [x] CSRF Protection ready

### 7. Troubleshooting

**Issue: Database Connection Error**
```
Solution:
1. Verify MySQL running
2. Check credentials di config/database.php
3. Ensure database 'hris_db' exists
```

**Issue: Login Failed**
```
Solution:
1. Clear browser cookies
2. Check password is correct
3. Verify user exists in database
```

**Issue: Page Not Found**
```
Solution:
1. Check file paths are correct
2. Verify BASE_URL in config
3. Check Apache DocumentRoot
```

### 8. Maintenance

**Backup Database:**
```
mysqldump -u root hris_db > backup_hris_$(date +%Y%m%d).sql
```

**Restore Database:**
```
mysql -u root hris_db < backup_file.sql
```

### 9. Performance Optimization

- Gunakan indexes pada database
- Cache static files
- Optimize images
- Minimize CSS/JS

### 10. Future Enhancements

- Mobile app (iOS/Android)
- Email notifications
- SMS alerts
- Advanced reporting
- REST API
- Two-factor authentication
- Document management

---

## 📞 Support

Untuk bantuan lebih lanjut:
- Baca file README.md
- Baca file SETUP.md
- Akses halaman Help di sistem

## 📅 Version Info

- Version: 1.0.0
- Release Date: November 2025
- PHP: 7.4+
- MySQL: 5.7+
- Bootstrap: 5.3

---

**Selamat menggunakan HRIS System!** 🎉
