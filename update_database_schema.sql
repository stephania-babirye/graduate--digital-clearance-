-- ========================================
-- Add program_level column to student_profiles
-- Run this to update existing database
-- ========================================

USE graduation_clearance;

-- Add program_level column if it doesn't exist
ALTER TABLE student_profiles 
ADD COLUMN IF NOT EXISTS program_level VARCHAR(50) DEFAULT NULL AFTER user_id;

-- Ensure users.role supports pending staff/officer registrations before admin assignment
ALTER TABLE users
MODIFY role ENUM('student', 'staff', 'finance', 'library', 'ict', 'dean', 'registrar', 'admin') NOT NULL;

-- Optional: Copy existing course data to program_level if needed
-- UPDATE student_profiles SET program_level = course WHERE program_level IS NULL AND course IS NOT NULL;

SELECT 'Database schema updated successfully!' as status;
