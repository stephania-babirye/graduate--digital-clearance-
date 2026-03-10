# TEST CREDENTIALS - Graduate Digital Clearance System

## Quick Access Guide

### Admin Account
- **Email:** admin@umu.ac.ug
- **Password:** Use password_generator.php to set your own password
- **Dashboard:** http://localhost/code final/admin/dashboard.php
- **Setup Link:** http://localhost/code final/admin/password_generator.php

---

## Department Officers

⚠️ **IMPORTANT:** The default password hashes in the database don't work. You MUST set passwords using the officer password generator!

### Setup Officers Passwords First:
**Go to:** http://localhost/code final/admin/officer_password_generator.php

This tool allows you to:
1. Set passwords for all department officers at once
2. Generate secure password hashes
3. Get SQL queries to update the database

---

### Finance Officer (Bursary)
- **Email:** finance@finance
- **Password:** Set via officer_password_generator.php
- **Dashboard:** http://localhost/code final/finance/dashboard.php
- **Features:**
  - View student clearance applications
  - Check fee payment status
  - Approve/Reject applications
  - View activity logs

### Library Officer
- **Email:** library@Lib
- **Password:** Set via officer_password_generator.php
- **Dashboard:** http://localhost/code final/library/dashboard.php
- **Features:**
  - Check books returned
  - Check fines cleared
  - Approve/Reject applications

### ICT Officer
- **Email:** ict@ict.umu.ac.ug
- **Password:** Set via officer_password_generator.php
- **Dashboard:** http://localhost/code final/ict/dashboard.php
- **Features:**
  - Check equipment returned
  - Check for damages
  - Approve/Reject applications

### Faculty Dean
- **Email:** dean@umu.ac.ug
- **Password:** Set via officer_password_generator.php
- **Dashboard:** http://localhost/code final/dean/dashboard.php
- **Features:**
  - Confirm results
  - Approve dissertations
  - Approve/Reject applications

### Academic Registrar
- **Email:** registrar@umu.ac.ug
- **Password:** Set via officer_password_generator.php
- **Dashboard:** http://localhost/code final/registrar/dashboard.php
- **Features:**
  - Final approval authority
  - Manage graduation list
  - Download official graduation list

---

## Quick Setup Steps

### 1. Setup Database
```sql
-- Run in phpMyAdmin
mysql -u root -p < database_setup.sql
```

### 2. Set Admin Password
1. Go to: http://localhost/code final/admin/password_generator.php
2. Enter your desired admin password
3. Copy the SQL query
4. Run it in phpMyAdmin

### 3. Set Officer Passwords
1. Go to: http://localhost/code final/admin/officer_password_generator.php
2. Enter passwords for all officers
3. Copy the SQL queries
4. Run them in phpMyAdmin

### 4. Test Login
Now you can login with the credentials you set!

---

## Student Accounts
Students can register themselves at:
**http://localhost/code final/login/register.php**

### Test Student Workflow:
1. Register as a student
2. Complete profile (add photo, date of birth, etc.)
3. Apply for clearance
4. Wait for department approvals
5. Download clearance certificate (after all approvals)

---

## Role-Based Email Domains

The system automatically routes users based on email domain:
- `@finance` → Finance Officer Dashboard
- `@Lib` → Library Officer Dashboard
- `@ict.umu.ac.ug` → ICT Officer Dashboard
- `dean@umu.ac.ug` → Faculty Dean Dashboard
- `registrar@umu.ac.ug` → Academic Registrar Dashboard
- `admin@umu.ac.ug` → System Administrator Dashboard
- Other emails → Student Dashboard

---

## Testing the Finance Dashboard

### Steps:
1. Login with finance officer credentials
2. You'll see:
   - Statistics cards (Pending, Approved, Rejected)
   - List of student applications
   - Your recent activity log

3. For each student:
   - Click "View Details" to check fee status
   - Click "✅ Approve" if fees are cleared
   - Click "❌ Reject" if fees are pending (must provide reason)

4. All actions are logged with:
   - Officer name
   - Date and time
   - Action taken
   - Reason (for rejections)

---

## Important Notes

⚠️ **Security:**
- Change default passwords in production
- Admin password MUST be set via password_generator.php

📝 **Database:**
- Make sure to run database_setup.sql first
- All test accounts will be created automatically

🔐 **Password Hash:**
- Default hash is for "officer123"
- Generated using PHP's password_hash() function

---

## Common Issues & Solutions

### Can't Login?
- Check if database is properly set up
- Verify email and password are correct
- Clear browser cache and cookies

### No Students in Finance Dashboard?
- Students need to register and apply for clearance first
- Check if clearance applications table has data

### Changes Not Saving?
- Check database connection in config/db.php
- Verify port number (3306 or 3307)
- Check MySQL service is running

---

## Support

For issues or questions, check:
- README.md in root folder
- ADMIN_PASSWORD_SETUP.md for admin setup
- Database logs in phpMyAdmin

---

**System Version:** 1.0
**Last Updated:** March 9, 2026