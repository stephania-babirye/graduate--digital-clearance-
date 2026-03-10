# Quick Fix - Run This SQL Now!

## Problem
You're getting errors about missing columns in ICT and Faculty Dean dashboards.

## Solution
Copy and paste **ALL** of these commands into phpMyAdmin SQL tab and click "Go":

```sql
USE graduation_clearance;

-- ICT COLUMNS
ALTER TABLE department_approvals ADD COLUMN IF NOT EXISTS laptop_returned ENUM('yes', 'no', 'n/a') DEFAULT 'n/a';
ALTER TABLE department_approvals ADD COLUMN IF NOT EXISTS equipment_damaged ENUM('yes', 'no', 'n/a') DEFAULT 'n/a';
ALTER TABLE department_approvals ADD COLUMN IF NOT EXISTS damage_description TEXT;
ALTER TABLE department_approvals ADD COLUMN IF NOT EXISTS equipment_notes TEXT;

-- FACULTY DEAN COLUMNS
ALTER TABLE department_approvals ADD COLUMN IF NOT EXISTS results_confirmed ENUM('yes', 'no', 'pending') DEFAULT 'pending';
ALTER TABLE department_approvals ADD COLUMN IF NOT EXISTS dissertation_approved ENUM('yes', 'no', 'n/a') DEFAULT 'n/a';
ALTER TABLE department_approvals ADD COLUMN IF NOT EXISTS faculty_name VARCHAR(100);
ALTER TABLE department_approvals ADD COLUMN IF NOT EXISTS approved_at TIMESTAMP NULL;
ALTER TABLE department_approvals ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
```

## Steps
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Click on `graduation_clearance` database on the left
3. Click "SQL" tab at the top
4. Copy and paste ALL the SQL commands above
5. Click "Go" button
6. Refresh your ICT/Dean dashboard pages

✅ **Done!** The errors should now be fixed.

For more details, see [DATABASE_UPDATE_GUIDE.md](DATABASE_UPDATE_GUIDE.md)
