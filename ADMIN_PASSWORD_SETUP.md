# ADMIN PASSWORD SETUP INSTRUCTIONS

## Quick Start Guide

### Step 1: Access the Password Generator
Open your browser and navigate to:
```
http://localhost/code final/admin/password_generator.php
```

### Step 2: Generate Your Password Hash
1. Enter your desired admin password in the form
2. Click "Generate Password Hash"
3. Copy the SQL query that appears

### Step 3: Update Database
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Select the `graduation_clearance` database
3. Click on the "SQL" tab
4. Paste the copied SQL query
5. Click "Go" to execute

### Step 4: Login
1. Go to: http://localhost/code final/login/index.php
2. Use these credentials:
   - **Email:** admin@umu.ac.ug
   - **Password:** [Your password from Step 2]

---

## Alternative Method (Direct Database Edit)

If you prefer to set the password directly:

1. Generate hash at: http://localhost/code final/admin/password_generator.php
2. Copy the hashed password
3. In phpMyAdmin, go to `graduation_clearance` database
4. Find the `users` table
5. Edit the admin user row
6. Replace the password field with your copied hash
7. Save changes

---

## Default Admin Account

**Email:** admin@umu.ac.ug
**Role:** admin

**Note:** The initial password in the database is set to 'CHANGE_ME_USE_PASSWORD_GENERATOR' 
which won't work for login. You MUST use the password generator to set your password.

---

## Troubleshooting

### Can't access password_generator.php?
Make sure your WAMP server is running and you're using the correct URL path.

### Password doesn't work after updating?
1. Make sure you copied the FULL hash (it's very long)
2. Verify the SQL query executed successfully in phpMyAdmin
3. Try generating a new hash and updating again

### Forgot admin password?
Just use the password generator again to create a new one and update the database.

---

## Security Notes

- Always use a strong password (at least 8 characters, mix of letters, numbers, symbols)
- Don't share your admin password
- Change the default admin email if needed in the database
- Delete or restrict access to password_generator.php after initial setup for security

---

For additional support, contact the system administrator.