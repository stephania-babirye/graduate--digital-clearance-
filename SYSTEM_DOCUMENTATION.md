# Graduate Digital Clearance System - Complete Documentation
## Uganda Martyrs University

---

## 🎓 System Overview

This is a comprehensive web-based Graduate Clearance Management System designed for Uganda Martyrs University to streamline the graduation clearance process. The system manages clearance applications across multiple departments with role-based access control.

---

## 🚀 Features Completed

### ✅ **All Dashboards Implemented:**

1. **Student Dashboard** - Profile management, clearance application, status tracking, certificate download
2. **Finance Officer Dashboard** - Fee verification, approval/rejection with reasons, activity logging
3. **Library Officer Dashboard** - Books/fines verification, approval/rejection workflow
4. **ICT Officer Dashboard** - Equipment verification, approval/rejection workflow
5. **Faculty Dean Dashboard** - Academic verification (results, dissertations), approval workflow
6. **Academic Registrar Dashboard** - Master approval table, graduation list management, PDF generation
7. **System Administrator Dashboard** - Complete system control panel with:
   - User Management (Create, Edit, Delete, Activate/Deactivate users)
   - Role-Based Access Control (RBAC) visualization
   - Graduation Closure controls
   - System Settings configuration
   - Activity Logs viewer
   - Database Backup & Restore

---

## 👥 User Roles & Credentials

### Test Credentials

**System Administrator:**
- Email: `admin@umu.ac.ug`
- Password: Use `admin/password_generator.php` to set custom password

**Finance Officer:**
- Email: `finance@finance`
- Password: `officer123`

**Library Officer:**
- Email: `library@Lib`
- Password: `officer123`

**ICT Officer:**
- Email: `ict@ict.umu.ac.ug`
- Password: `officer123`

**Faculty Dean:**
- Email: `dean@umu.ac.ug`
- Password: `officer123`

**Academic Registrar:**
- Email: `registrar@umu.ac.ug`
- Password: `officer123`

**Students:**
- Register through the registration page

---

## 📋 Clearance Workflow

### Sequential Approval Process:

1. **Student** - Submits clearance application
2. **Finance Officer** - Verifies fees paid
3. **Library Officer** - Verifies books returned and fines cleared
4. **ICT Officer** - Verifies equipment returned
5. **Faculty Dean** - Verifies results and dissertation
6. **Academic Registrar** - Final approval & adds to graduation list

### Status Color Coding:
- 🔴 **Red (Pending)** - Waiting for department action
- 🟢 **Green (Approved)** - Department approved
- 🔵 **Blue (Rejected)** - Department rejected with reason

---

## 🔧 Installation Instructions

### Prerequisites:
- WAMP/XAMPP Server (Apache + MySQL)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Steps:

1. **Copy Files:**
   ```
   Copy entire "code final" folder to:
   C:\wamp64\www\code final\
   ```

2. **Configure Database:**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Click "Import" tab
   - Select `database_setup.sql` file
   - Click "Go" to create database and tables

3. **Update Database Port (if needed):**
   - Open `config/db.php`
   - Change port from 3307 to 3306 if your MySQL uses default port

4. **Access System:**
   - Homepage: http://localhost/code%20final/
   - Login: http://localhost/code%20final/login/
   - Register: http://localhost/code%20final/login/register.php

5. **Set Admin Password:**
   - Visit: http://localhost/code%20final/admin/password_generator.php
   - Enter desired password
   - Copy the UPDATE query
   - Run in phpMyAdmin SQL tab

---

## 📁 File Structure

```
code final/
├── index.php                 # Landing page
├── database_setup.sql        # Complete database schema
├── system.php               # System overview (this was your original file)
│
├── config/
│   └── db.php               # Database configuration
│
├── includes/
│   ├── header.php           # Common header
│   ├── footer.php           # Common footer
│   └── auth.php             # Authentication check
│
├── login/
│   ├── index.php            # Login page
│   ├── register.php         # Registration page
│   ├── authenticate.php     # Login processing
│   ├── process_register.php # Registration processing
│   └── logout.php           # Logout handler
│
├── student/
│   ├── dashboard.php        # Student main dashboard
│   ├── upload_photo.php     # Photo upload handler
│   ├── update_profile.php   # Profile update handler
│   ├── apply_clearance.php  # Clearance application
│   ├── download_certificate.php  # Certificate PDF
│   └── download_graduation_list.php  # Graduation list PDF
│
├── finance/
│   ├── dashboard.php        # Finance officer dashboard
│   ├── process_action.php   # Approve/reject handler
│   └── activity_log.php     # Activity history
│
├── library/
│   ├── dashboard.php        # Library officer dashboard
│   ├── process_action.php   # Approve/reject handler
│   └── activity_log.php     # Activity history
│
├── ict/
│   ├── dashboard.php        # ICT officer dashboard
│   ├── process_action.php   # Approve/reject handler
│   └── activity_log.php     # Activity history
│
├── dean/
│   ├── dashboard.php        # Faculty dean dashboard
│   ├── process_action.php   # Approve/reject handler
│   └── activity_log.php     # Activity history
│
├── registrar/
│   ├── dashboard.php        # Academic registrar dashboard
│   ├── process_action.php   # Final approval handler
│   ├── add_to_graduation_list.php  # Add to grad list
│   ├── graduation_list.php  # View graduation list
│   └── generate_graduation_pdf.php  # Generate official PDF
│
├── admin/
│   ├── dashboard.php        # Admin main dashboard
│   ├── user_management.php  # User CRUD operations
│   ├── create_user.php      # Create new user
│   ├── edit_user.php        # Edit user details
│   ├── toggle_user_status.php  # Activate/deactivate user
│   ├── delete_user.php      # Delete user
│   ├── graduation_closure.php  # Close/open applications
│   ├── system_settings.php  # Configure system
│   ├── logs.php             # View activity logs
│   ├── clear_logs.php       # Clear all logs
│   ├── backup.php           # Database backup
│   ├── download_backup.php  # Download backup file
│   ├── delete_backup.php    # Delete backup file
│   ├── password_generator.php  # Admin password tool
│   └── officer_password_generator.php  # Officer password tool
│
├── assets/
│   ├── css/
│   │   └── style.css        # University colors & styling
│   └── images/              # Logo and images
│
└── uploads/
    └── photos/              # Student photos
```

---

## 🗄️ Database Tables

### 1. **users** - All system users
- Stores: students, officers, and administrators
- Fields: id, full_name, email, password, role, registration_number, is_active

### 2. **student_profiles** - Student details
- Fields: user_id, course, campus, date_of_birth, year_of_intake, photo_path

### 3. **clearance_applications** - Clearance requests
- Fields: user_id, finance_status, library_status, ict_status, faculty_status, registrar_status, all_approved, graduation_year, locked

### 4. **department_approvals** - Detailed approval info
- Fields: application_id, department, status, approved_by, rejection_reason

### 5. **approval_history** - Audit trail
- Fields: application_id, department, action, officer_id, officer_name, reason, action_date

### 6. **graduation_list** - Confirmed graduates
- Fields: user_id, application_id, graduation_year, confirmed_by, confirmation_status

### 7. **system_settings** - Global configuration
- Fields: university_name, university_email, university_phone, graduation_year, clearance_open

### 8. **activity_logs** - System activity tracking
- Fields: user_id, action, description, ip_address, created_at

---

## ⚙️ Admin Functions

### User Management:
- **Create User**: Add new students, officers, or admins
- **Edit User**: Modify user details and roles
- **Deactivate User**: Temporarily disable accounts
- **Delete User**: Permanently remove users and related data

### Graduation Control:
- **Close Applications**: Prevent new clearance submissions
- **Open Applications**: Allow clearance submissions
- **Update Graduation Year**: Change current graduation year
- **Lock Records**: Prevent modifications to clearance records

### System Management:
- **System Settings**: Configure university details
- **Activity Logs**: View all system actions
- **Database Backup**: Create full database backups
- **Restore**: Download backups for restoration

---

## 🎨 Design Specifications

### University Colors:
- **Maroon**: #800000 (Primary)
- **Gold**: #FFD700 (Accent)

### Campuses:
- Nkozi
- Rubaga
- Masaka
- Ngetta
- Fort Portal

---

## 🔒 Security Features

1. **Password Hashing**: bcrypt with cost factor 10
2. **Session Management**: Secure PHP sessions
3. **Role-Based Access Control**: Strict role isolation
4. **SQL Injection Prevention**: Prepared statements & mysqli_real_escape_string
5. **Input Validation**: Server-side and client-side validation
6. **Activity Logging**: Full audit trail of all actions

---

## 📊 Reporting Features

### For Students:
- Clearance Certificate PDF (after full approval)
- Graduation List PDF (view own status)

### For Officers:
- Department-specific activity logs
- Application statistics

### For Registrar:
- Official Graduation List PDF (printable)
- Master clearance status table
- Graduation statistics

### For Admin:
- Complete activity logs (all users)
- User management reports
- System usage statistics

---

## 🐛 Troubleshooting

### Common Issues:

**1. Cannot login:**
- Check database connection in `config/db.php`
- Verify correct port (3307 or 3306)
- Ensure database is imported

**2. Photos not uploading:**
- Check `uploads/photos/` folder exists
- Verify folder permissions (777)
- Maximum file size: 2MB

**3. Database errors:**
- Run `database_setup.sql` again
- Check all fields added (all_approved, graduation_year, locked)
- Update system_settings table structure

**4. Password not working:**
- Use password generator tools
- Default password for officers: `officer123`
- Admin password must be set via password_generator.php

---

## 📝 Usage Guidelines

### For Students:
1. Register with university email
2. Complete profile (100% required)
3. Upload passport photo
4. Apply for clearance
5. Track status on dashboard
6. Download certificate when approved

### For Officers:
1. Login with department credentials
2. Review pending applications
3. Click "View Details" to see student info
4. Use department checklist
5. Approve or Reject with reason
6. View activity log for history

### For Registrar:
1. Monitor all department statuses
2. Give final approval when all departments approve
3. Add approved students to graduation list
4. Generate official PDF for ceremonies

### For Admin:
1. Manage all users
2. Control graduation year
3. Open/close applications
4. Monitor system activity
5. Create backups regularly

---

## 🔄 System Workflow Example

1. Student registers → Creates account
2. Student completes profile → Uploads photo
3. Student applies for clearance → Application created
4. Finance reviews → Approves (fees paid)
5. Library reviews → Approves (books returned)
6. ICT reviews → Approves (equipment returned)
7. Dean reviews → Approves (results confirmed)
8. Registrar reviews → Final approval
9. Registrar adds to graduation list → Official graduate
10. System generates graduation certificate → Student downloads

---

## 📞 Support & Maintenance

### Regular Maintenance Tasks:
- Back up database weekly
- Review activity logs monthly
- Update graduation year annually
- Clear old logs periodically
- Test all workflows before graduation season

### For Issues:
- Check activity logs for errors
- Review database integrity
- Verify all tables exist
- Check file permissions

---

## 🎯 Future Enhancements

Potential additions:
- Email notifications for status changes
- SMS alerts for approvals
- Bulk user import (CSV)
- Advanced reporting dashboard
- Mobile responsive improvements
- API for external integrations

---

## ✅ System Status

**Current Version**: 1.0 Complete
**Status**: Production Ready
**Last Updated**: <?php echo date('F Y'); ?>

**Implemented Features**: 100%
- ✅ Student Dashboard
- ✅ All Officer Dashboards (Finance, Library, ICT, Dean, Registrar)
- ✅ Admin Dashboard
- ✅ User Management
- ✅ Graduation Control
- ✅ PDF Generation
- ✅ Activity Logging
- ✅ Database Backup

---

## 📄 License

Developed for Uganda Martyrs University
Graduate Digital Clearance System
All Rights Reserved

---

**System Administrator Contact:**
Email: admin@umu.ac.ug
Phone: +256-414-410-611

**End of Documentation**