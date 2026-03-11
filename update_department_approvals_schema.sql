-- ========================================
-- Update department_approvals table
-- Add missing columns for Faculty Dean functionality
-- Run this script to update your existing database
-- ========================================

USE graduation_clearance;

-- Check if columns exist and add them if missing

-- Faculty Dean specific fields
-- Add results_confirmed column
ALTER TABLE department_approvals 
ADD COLUMN IF NOT EXISTS results_confirmed ENUM('yes', 'no', 'pending') DEFAULT 'pending';

-- Add dissertation_approved column
ALTER TABLE department_approvals 
ADD COLUMN IF NOT EXISTS dissertation_approved ENUM('yes', 'no', 'n/a') DEFAULT 'n/a';

-- Add faculty_name column
ALTER TABLE department_approvals 
ADD COLUMN IF NOT EXISTS faculty_name VARCHAR(100);

-- Add approved_at column if missing
ALTER TABLE department_approvals 
ADD COLUMN IF NOT EXISTS approved_at TIMESTAMP NULL;

-- Add updated_at column if missing (with ON UPDATE trigger)
ALTER TABLE department_approvals 
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

SELECT 'Database schema updated successfully! Faculty Dean columns added.' as status;
