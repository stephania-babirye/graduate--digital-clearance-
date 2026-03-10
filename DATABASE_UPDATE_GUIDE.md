# Department Approvals Database Update Required

## Issue
The `department_approvals` table in your database is missing department-specific columns needed for the ICT and Faculty Dean Dashboards to work properly.

## Missing Columns

### ICT Officer Columns
- `laptop_returned` - To track if laptop was returned
- `equipment_damaged` - To track if equipment is damaged
- `damage_description` - To store damage details
- `equipment_notes` - Additional equipment notes

### Faculty Dean Columns
- `results_confirmed` - To track if student results are confirmed
- `dissertation_approved` - To track if dissertation is approved
- `faculty_name` - To store the faculty name
- `approved_at` - Timestamp for approval
- `updated_at` - Timestamp for last update

## Solution

### Option 1: Run SQL Update Script (Recommended)

1. Open phpMyAdmin or your MySQL client
2. Select the `graduation_clearance` database
3. Go to the SQL tab
4. Copy and paste the contents of `update_department_approvals_schema.sql`
5. Click "Go" or "Execute"

### Option 2: Run from Command Line

```bash
cd "c:\wamp64\www\code final"
mysql -u root -p graduation_clearance < update_department_approvals_schema.sql
```

### Option 3: Manual ALTER Commands

If the above methods don't work, run these commands one by one in phpMyAdmin:

```sql
USE graduation_clearance;

-- ICT Officer specific fields
ALTER TABLE department_approvals 
ADD COLUMN laptop_returned ENUM('yes', 'no', 'n/a') DEFAULT 'n/a';

ALTER TABLE department_approvals 
ADD COLUMN equipment_damaged ENUM('yes', 'no', 'n/a') DEFAULT 'n/a';

ALTER TABLE department_approvals 
ADD COLUMN damage_description TEXT;

ALTER TABLE department_approvals 
ADD COLUMN equipment_notes TEXT;

-- Faculty Dean specific fields
ALTER TABLE department_approvals 
ADD COLUMN results_confirmed ENUM('yes', 'no', 'pending') DEFAULT 'pending';

ALTER TABLE department_approvals 
ADD COLUMN dissertation_approved ENUM('yes', 'no', 'n/a') DEFAULT 'n/a';

ALTER TABLE department_approvals 
ADD COLUMN faculty_name VARCHAR(100);

ALTER TABLE department_approvals 
ADD COLUMN approved_at TIMESTAMP NULL;

ALTER TABLE department_approvals 
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
```

## Verification

After running the update, verify the columns were added:

```sql
DESCRIBE department_approvals;
```

You should see all the new columns listed with their types.

## Note

The code has been updated to handle missing columns gracefully, so both the ICT and Faculty Dean dashboards will work with default values even if the update hasn't been run yet. However, for full functionality, it's recommended to run the update script.

## Files Modified

### ICT Dashboard
1. `ict/dashboard.php` - Updated to fetch equipment details safely
2. `ict/get_equipment_details.php` - Added error handling for missing columns

### Faculty Dean Dashboard
3. `dean/dashboard.php` - Updated to fetch faculty details safely
4. `dean/get_faculty_details.php` - Added error handling for missing columns

### Schema Update
5. `update_department_approvals_schema.sql` - Created SQL update script for both ICT and Faculty Dean columns

## After Update

Once you run the update script, both dashboards will function with full capability:

### ICT Dashboard
- Track laptop returns (Yes/No/N/A)
- Track equipment damage (Yes/No/N/A)
- Store damage descriptions
- Add equipment notes

### Faculty Dean Dashboard
- Track results confirmation (Yes/No/Pending)
- Track dissertation approval (Yes/No/N/A)
- Store faculty name assignments
- Record proper timestamps
