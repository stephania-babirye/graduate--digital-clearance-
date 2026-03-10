# 🚀 Quick Start Guide
## Uganda Martyrs University - Graduate Clearance System

---

## ⚡ 5-Minute Setup

### Step 1: Install WAMP/XAMPP
- Download WAMP from [wampserver.com](http://www.wampserver.com/)
- Install and start all services (Apache + MySQL)

### Step 2: Copy Files
```
Copy "code final" folder to:
C:\wamp64\www\
```

### Step 3: Create Database
1. Open browser: `http://localhost/phpmyadmin`
2. Click "Import" tab
3. Choose file: `database_setup.sql`
4. Click "Go"

### Step 4: Check Database Port
- Open `config/db.php`
- Default port: `3307`
- If error, change to: `3306`

### Step 5: Access System
```
Homepage: http://localhost/code%20final/
Login:    http://localhost/code%20final/login/
```

---

## 🔑 Quick Login

### Test as Admin:
1. Go to: `http://localhost/code%20final/admin/password_generator.php`
2. Enter password: `admin123`
3. Copy the SQL query
4. Run in phpMyAdmin
5. Login: `admin@umu.ac.ug` / `admin123`

### Test as Officer:
- Email: `finance@finance`
- Password: `officer123`

### Test as Student:
- Click "Register" button
- Fill form and create account

---

## ✅ Verify Installation

Check these URLs work:

✓ Landing Page: `http://localhost/code%20final/`
✓ Login: `http://localhost/code%20final/login/`
✓ Register: `http://localhost/code%20final/login/register.php`
✓ Admin: `http://localhost/code%20final/admin/dashboard.php`

---

## 🎯 Quick Test Workflow

### As Student:
1. Register → Complete Profile → Upload Photo
2. Apply for Clearance → Track Status

### As Finance Officer:
1. Login: `finance@finance` / `officer123`
2. View Applications → Approve/Reject

### As Admin:
1. Set password using generator
2. Login to admin dashboard
3. View statistics and users

---

## 🐛 Quick Fixes

### "Cannot connect to database"
- Start MySQL service in WAMP
- Check port in `config/db.php`

### "Page not found"
- Check folder name is "code final"
- URL should have `%20` for space

### "Permission denied"
- Right-click `uploads` folder
- Properties → Security → Full Control

---

## 📱 Quick Access Links

After installation, bookmark these:

**Student:**
- Dashboard: `/student/dashboard.php`

**Officers:**
- Finance: `/finance/dashboard.php`
- Library: `/library/dashboard.php`
- ICT: `/ict/dashboard.php`
- Dean: `/dean/dashboard.php`
- Registrar: `/registrar/dashboard.php`

**Admin:**
- Dashboard: `/admin/dashboard.php`
- Users: `/admin/user_management.php`
- Settings: `/admin/system_settings.php`
- Backup: `/admin/backup.php`

---

## 🎓 Default Credentials

```
Admin:     admin@umu.ac.ug          (set via generator)
Finance:   finance@finance          officer123
Library:   library@Lib              officer123
ICT:       ict@ict.umu.ac.ug        officer123
Dean:      dean@umu.ac.ug           officer123
Registrar: registrar@umu.ac.ug     officer123
```

---

## ⚠️ Important Notes

1. **Always backup** before graduation season
2. **Test workflow** with dummy students first
3. **Update passwords** from defaults
4. **Check activity logs** regularly
5. **Keep database updated**

---

## 🆘 Need Help?

1. Read full documentation: `SYSTEM_DOCUMENTATION.md`
2. Check troubleshooting section
3. Review activity logs in admin panel
4. Verify database tables exist

---

## ✨ You're Ready!

The system is fully functional and ready for use. Start by:
1. Setting admin password
2. Creating test student account
3. Testing complete approval workflow
4. Generating graduation PDF

**Happy Graduation Season! 🎉**