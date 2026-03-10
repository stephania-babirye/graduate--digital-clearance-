# OFFICER PASSWORD SETUP GUIDE

## Why Do I Need This?

The password hashes in the database are environment-specific and may not work on your system. Each system generates slightly different password hashes even for the same password. That's why you need to generate your own password hashes.

---

## Quick Fix - Set All Officer Passwords

### Step 1: Access the Password Generator

Open your browser and go to:
```
http://localhost/code final/admin/officer_password_generator.php
```

### Step 2: Enter Passwords

You'll see a form with fields for each department officer:

- **Finance Officer** (Email: finance@finance)
- **Library Officer** (Email: library@Lib)
- **ICT Officer** (Email: ict@ict.umu.ac.ug)
- **Faculty Dean** (Email: dean@umu.ac.ug)
- **Academic Registrar** (Email: registrar@umu.ac.ug)

Enter your desired password for each officer. You can:
- Use the same password for all (e.g., "officer123")
- Use different passwords for each officer
- Leave some blank if you don't need all roles yet

**Example:**
- Finance: `finance123`
- Library: `library123`
- ICT: `ict123`
- Dean: `dean123`
- Registrar: `registrar123`

Or simply use `officer123` for all.

### Step 3: Generate Hashes

Click the **"Generate Password Hashes"** button.

The page will show:
1. Individual SQL queries for each officer
2. A combined SQL query for all officers at once

### Step 4: Update Database

#### Option A: Individual Queries
Copy each SQL query and run it in phpMyAdmin:
```sql
UPDATE users SET password = '[hash]' WHERE email = 'finance@finance';
```

#### Option B: All at Once (Recommended)
Copy the combined SQL query at the bottom and run it once:
```sql
UPDATE users SET password = '[hash1]' WHERE email = 'finance@finance';
UPDATE users SET password = '[hash2]' WHERE email = 'library@Lib';
UPDATE users SET password = '[hash3]' WHERE email = 'ict@ict.umu.ac.ug';
UPDATE users SET password = '[hash4]' WHERE email = 'dean@umu.ac.ug';
UPDATE users SET password = '[hash5]' WHERE email = 'registrar@umu.ac.ug';
```

**How to run in phpMyAdmin:**
1. Open http://localhost/phpmyadmin
2. Select `graduation_clearance` database from the left sidebar
3. Click the "SQL" tab at the top
4. Paste the SQL query
5. Click "Go"
6. You'll see "5 rows affected" if successful

### Step 5: Test Login

Now try logging in:
1. Go to: http://localhost/code final/login/index.php
2. Use the email and password you just set

**Example:**
- Email: `finance@finance`
- Password: `finance123` (or whatever you set)

---

## Understanding the Problem

### Why doesn't "officer123" work?

The hash in the database (`$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`) was generated on a different system. PHP's `password_hash()` function generates unique hashes each time, even for the same password. While `password_verify()` can usually verify them, sometimes there are compatibility issues between different PHP versions or systems.

### The Solution

Generate fresh password hashes on YOUR system using YOUR PHP installation. This guarantees compatibility.

---

## Alternative Method: Manual Password Setting

If you prefer to do it manually:

### 1. Create a test PHP file

Create `test_hash.php` in your web root:

```php
<?php
$password = "officer123"; // Your desired password
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Hash: " . $hash;
?>
```

### 2. Access the file

Open: http://localhost/test_hash.php

You'll see something like:
```
Hash: $2y$10$abc123def456...
```

### 3. Update database manually

Run in phpMyAdmin:
```sql
UPDATE users SET password = '$2y$10$abc123def456...' WHERE email = 'finance@finance';
```

### 4. Delete test file

Remove `test_hash.php` for security.

---

## Common Issues

### ❌ "Invalid email or password" error

**Cause:** Password hash doesn't match or wasn't updated correctly.

**Fix:**
1. Re-generate the hash using officer_password_generator.php
2. Make sure you copied the ENTIRE hash (it's very long!)
3. Check there are no extra spaces in the SQL query
4. Verify the email is correct

### ❌ Can't access password generator page

**Cause:** WAMP/XAMPP not running or wrong URL.

**Fix:**
1. Make sure WAMP/XAMPP is running (green icon)
2. Check your localhost URL includes the folder name
3. Correct: `http://localhost/code final/admin/...`
4. Wrong: `http://localhost/admin/...`

### ❌ SQL query fails in phpMyAdmin

**Cause:** Database not selected or syntax error.

**Fix:**
1. Click on `graduation_clearance` database in left sidebar first
2. Make sure you copied the complete SQL query
3. Check for any missing quotes or semicolons

---

## Security Best Practices

✅ **DO:**
- Use strong passwords (mix of letters, numbers, symbols)
- Use different passwords for each officer in production
- Delete or restrict access to password generator pages after setup
- Keep a secure record of passwords

❌ **DON'T:**
- Use simple passwords like "123" or "password"
- Share passwords between officers
- Leave password generator pages publicly accessible
- Store passwords in plain text anywhere

---

## Quick Reference

| Officer | Email | Default Location |
|---------|-------|------------------|
| Finance | finance@finance | http://localhost/code final/finance/dashboard.php |
| Library | library@Lib | http://localhost/code final/library/dashboard.php |
| ICT | ict@ict.umu.ac.ug | http://localhost/code final/ict/dashboard.php |
| Dean | dean@umu.ac.ug | http://localhost/code final/dean/dashboard.php |
| Registrar | registrar@umu.ac.ug | http://localhost/code final/registrar/dashboard.php |

---

## Still Having Issues?

1. Check that MySQL service is running
2. Verify database name is `graduation_clearance`
3. Check PHP version (should be 7.4 or higher)
4. Look at error logs in WAMP/XAMPP
5. Try restarting WAMP/XAMPP

---

**Last Updated:** March 9, 2026

For more help, see:
- TEST_CREDENTIALS.md
- README.md
- ADMIN_PASSWORD_SETUP.md