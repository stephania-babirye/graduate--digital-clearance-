# Graduate Digital Clearance System - Uganda Martyrs University

## ✅ **SYSTEM STATUS: 100% COMPLETE**

A comprehensive web-based system for managing graduate clearance processes across multiple departments. All features implemented and production-ready!

## 🎯 Complete Features

- ✅ **Landing Page**: Public-facing page with university branding and graduation requirements
- ✅ **Unified Login System**: Single entry point with role-based routing
- ✅ **Student Registration**: Self-registration with email verification
- ✅ **Role-Based Dashboards**:
  - ✅ **Student Dashboard** - Profile, photo upload, clearance application, status tracking, certificate download
  - ✅ **Finance Officer Dashboard** - Fee verification, approval/rejection, activity logging
  - ✅ **Library Officer Dashboard** - Books/fines verification, approval workflow
  - ✅ **ICT Officer Dashboard** - Equipment verification, approval workflow
  - ✅ **Faculty Dean Dashboard** - Academic verification, results & dissertation approval
  - ✅ **Academic Registrar Dashboard** - Master approval table, graduation list, PDF generation
  - ✅ **System Administrator Dashboard** - Complete control panel:
    - User Management (Create, Edit, Delete, Activate/Deactivate)
    - Role-Based Access Control (RBAC)
    - Graduation Closure Controls
    - System Settings Configuration
    - Activity Logs Viewer
    - Database Backup & Restore

## 🔥 New Admin Features

### User Management
- Create new users (students, officers, admins)
- Edit user details and roles
- Activate/Deactivate user accounts
- Delete users with cascading cleanup
- Bulk operations support

### System Control
- Open/Close clearance applications
- Update graduation year
- Lock clearance records
- Configure university settings
- Monitor system health

### Security & Monitoring
- Complete activity log tracking
- IP address logging
- Full audit trail
- Database backup & restore
- Automated backup management

## 📁 Complete File Structure

```
code final/
├── index.php                     # ✅ Landing page
├── database_setup.sql            # ✅ Complete schema (updated)
├── SYSTEM_DOCUMENTATION.md       # ✅ Full documentation
├── QUICK_START.md               # ✅ 5-minute setup guide
├── README.md                    # ✅ This file
│
├── login/                       # ✅ Authentication system
├── student/                     # ✅ Student portal (100%)
├── finance/                     # ✅ Finance dashboard (100%)
├── library/                     # ✅ Library dashboard (100%)
├── ict/                         # ✅ ICT dashboard (100%)
├── dean/                        # ✅ Dean dashboard (100%)
├── registrar/                   # ✅ Registrar dashboard (100%)
└── admin/                       # ✅ Admin panel (100%)
    ├── dashboard.php            # Main control panel
    ├── user_management.php      # CRUD operations
    ├── create_user.php          # Add users
    ├── edit_user.php            # Edit users
    ├── graduation_closure.php   # Control applications
    ├── system_settings.php      # Configure system
    ├── logs.php                 # Activity logs
    ├── backup.php               # Database backups
    └── password_generator.php   # Password tools
```

## 📊 Database Tables (All Implemented)

1. **users** - All system users (students, officers, admins)
2. **student_profiles** - Student details and photos
3. **clearance_applications** - Clearance requests with all department statuses
4. **department_approvals** - Detailed approval information
5. **approval_history** - Complete audit trail
6. **graduation_list** - Confirmed graduates
7. **system_settings** - Global configuration
8. **activity_logs** - System activity tracking

## Technology Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Server**: Apache (WAMP/XAMPP/LAMP)

## 🚀 Quick Installation

### 1. Copy Files
```
Copy entire folder to: C:\wamp64\www\code final\
```

### 2. Import Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Import `database_setup.sql`
3. Database `graduation_clearance` will be created

### 3. Configure Connection
Open `config/db.php` and verify:
```php
$host = 'localhost:3307';  // Change to 3306 if needed
$db   = 'graduation_clearance';
$user = 'root';
$pass = '';
```

### 4. Access System
```
Homepage: http://localhost/code%20final/
Login:    http://localhost/code%20final/login/
Admin:    http://localhost/code%20final/admin/dashboard.php
```

Download Bootstrap 5 files and place them in:
- CSS: `assets/css/bootstrap.min.css`
- JS: `assets/js/bootstrap.bundle.min.js`

Or use CDN links in `includes/header.php`

### 4. File Permissions

Ensure the uploads folder has write permissions:
```bash
chmod 755 uploads/photos
```

### 5. Access the System

1. Start WAMP/XAMPP server
2. Open browser and navigate to:
   ```
   http://localhost/code final/
   ```

### 6. Set Up Passwords

⚠️ **IMPORTANT:** You must set passwords before logging in!

## 🔑 Test Credentials (Working)

### System Administrator:
- **Email:** `admin@umu.ac.ug`
- **Password:** Set via `admin/password_generator.php`

### Department Officers (All Working):
**Default Password for All Officers: `officer123`**

| Role | Email | Password |
|------|-------|----------|
| Finance Officer | finance@finance | officer123 |
| Library Officer | library@Lib | officer123 |
| ICT Officer | ict@ict.umu.ac.ug | officer123 |
| Faculty Dean | dean@umu.ac.ug | officer123 |
| Academic Registrar | registrar@umu.ac.ug | officer123 |

### Students:
- Register at: `http://localhost/code%20final/login/register.php`
- Use any valid email and create password

**Note:** Admin password must be set using the password generator tool. Officer passwords are pre-configured and working!

---

## 🎯 Complete Workflow Example

### 1. Student Journey:
1. **Register** → Create account with email
2. **Login** → Access student dashboard
3. **Complete Profile** → Fill all required fields (100%)
4. **Upload Photo** → Passport photo (max 2MB)
5. **Apply for Clearance** → Submit application
6. **Track Status** → Monitor department approvals
7. **Download Certificate** → After all approvals

### 2. Officer Workflow:
1. **Login** → Use department credentials
2. **View Applications** → See pending clearances
3. **Review Details** → Click "View Details" button
4. **Check Requirements** → Use department checklist
5. **Approve/Reject** → Provide reason if rejected
6. **View Activity Log** → Track all actions

### 3. Registrar Process:
1. **Monitor All Departments** → Master status table
2. **Final Approval** → Only when all departments approve
3. **Add to Graduation List** → Confirm graduates
4. **Generate PDF** → Official graduation list

### 4. Admin Operations:
1. **User Management** → Create/Edit/Delete users
2. **Control Applications** → Open/Close clearance period
3. **Monitor System** → View activity logs
4. **Backup Database** → Regular backups
5. **Configure Settings** → University details

---

## 📱 Quick Access URLs

### Main Pages:
- Homepage: `http://localhost/code%20final/`
- Login: `http://localhost/code%20final/login/`
- Register: `http://localhost/code%20final/login/register.php`

### Dashboards:
- Student: `/student/dashboard.php`
- Finance: `/finance/dashboard.php`
- Library: `/library/dashboard.php`
- ICT: `/ict/dashboard.php`
- Dean: `/dean/dashboard.php`
- Registrar: `/registrar/dashboard.php`
- Admin: `/admin/dashboard.php`

### Admin Tools:
- User Management: `/admin/user_management.php`
- Graduation Control: `/admin/graduation_closure.php`
- System Settings: `/admin/system_settings.php`
- Activity Logs: `/admin/logs.php`
- Database Backup: `/admin/backup.php`

---

```
/code final/
├── assets/          # CSS, JS, images
├── config/          # Database configuration
├── includes/        # Header, footer, auth files
├── login/           # Login, registration, authentication
├── student/         # Student dashboard and features
├── finance/         # Finance officer dashboard
├── library/         # Library officer dashboard
├── ict/             # ICT officer dashboard
├── dean/            # Faculty dean dashboard
├── registrar/       # Academic registrar dashboard
├── admin/           # System administrator dashboard
├── uploads/         # Student photos and documents
└── index.php        # Landing page (main entry point)
```

## User Roles and Access

1. **Student**: Apply for clearance, upload photo, view status, download certificate
2. **Finance Officer**: Review and approve/reject based on fee payment
3. **Library Officer**: Verify books returned and fines cleared
4. **ICT Officer**: Verify equipment returned
5. **Faculty Dean**: Confirm results and approve dissertations
6. **Registrar**: Final approval and graduation list management
7. **Administrator**: User management, system settings, backup

## Role-Based Email Domains

The system uses email domains to identify user roles:
- `@finance` → Finance Officer
- `@Lib` → Library Officer
- `@ict.umu.ac.ug` → ICT Officer
- Other patterns can be configured in the authentication logic

## Security Features

- Password hashing (bcrypt)
- SQL injection prevention
- Session management
- Role-based access control (RBAC)
- Activity logging
- Secure file upload validation

## Support

For technical support, contact:
- Email: support@umu.ac.ug
- Phone: +256-XXX-XXXXXX

## License

© 2026 Uganda Martyrs University. All rights reserved.
#   g r a d u a t e - - d i g i t a l - c l e a r a n c e -  
 