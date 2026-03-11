# Fix ICT Dashboard Error

## Problem
The ICT dashboard is trying to access columns (`laptop_returned`, `equipment_damaged`, etc.) that don't exist in your database yet.

## Solution
Run the SQL update script to add the missing columns:

### Option 1: Using phpMyAdmin
1. Open phpMyAdmin in your browser: http://localhost/phpmyadmin
2. Select the `graduation_clearance` database
3. Click on the "SQL" tab
4. Copy and paste the contents of `update_department_approvals_schema.sql`
5. Click "Go" to execute

### Option 2: Using MySQL Command Line
```bash
mysql -u root -p graduation_clearance < "c:\wamp64\www\code final\update_department_approvals_schema.sql"
```

### Quick Fix SQL (Copy and Run)
```sql
USE graduation_clearance;

ALTER TABLE department_approvals 
ADD COLUMN IF NOT EXISTS laptop_returned ENUM('yes', 'no', 'n/a') DEFAULT 'n/a';

ALTER TABLE department_approvals 
ADD COLUMN IF NOT EXISTS equipment_damaged ENUM('yes', 'no', 'n/a') DEFAULT 'n/a';

ALTER TABLE department_approvals 
ADD COLUMN IF NOT EXISTS damage_description TEXT;

ALTER TABLE department_approvals 
ADD COLUMN IF NOT EXISTS equipment_notes TEXT;

ALTER TABLE department_approvals 
ADD COLUMN IF NOT EXISTS results_confirmed ENUM('yes', 'no', 'pending') DEFAULT 'pending';

ALTER TABLE department_approvals 
ADD COLUMN IF NOT EXISTS dissertation_approved ENUM('yes', 'no', 'n/a') DEFAULT 'n/a';

ALTER TABLE department_approvals 
ADD COLUMN IF NOT EXISTS faculty_name VARCHAR(100);

ALTER TABLE department_approvals 
ADD COLUMN IF NOT EXISTS approved_at TIMESTAMP NULL;
```

After running this SQL, refresh your ICT dashboard and the error will be gone.
