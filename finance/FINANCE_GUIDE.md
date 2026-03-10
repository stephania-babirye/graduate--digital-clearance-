# QUICK START GUIDE - Finance Officer Dashboard

## Getting Started

### 1. Setup Your Password First

⚠️ **IMPORTANT:** Before you can login, you need to set your password!

**Go to:** http://localhost/code final/admin/officer_password_generator.php

1. Enter your desired password in the "Finance Officer" field
2. Click "Generate Password Hashes"
3. Copy the SQL query for Finance Officer
4. Open phpMyAdmin → Select `graduation_clearance` database → SQL tab
5. Paste and execute the query
6. Your password is now set!

### 2. Login to Finance Dashboard
Navigate to: `http://localhost/code final/login/index.php`

**Finance Officer Credentials:**
- Email: `finance@finance`
- Password: `[Your password from step 1]`

### 3. Dashboard Overview

When you log in, you'll see:

#### Statistics Cards
- **Pending Review:** Number of applications waiting for your approval
- **Approved:** Number of applications you've approved
- **Rejected:** Number of applications you've rejected

#### Student Applications Table
Displays all student clearance applications with:
- Student Name
- Registration Number
- Course
- Campus
- Applied Date
- Current Status
- Action Buttons

### 3. Reviewing Applications

#### View Details
Click **"View Details"** button to:
- Check student's fee payment status
- View detailed payment records
- See outstanding balances (if any)
- Print receipts

#### Approve Application
1. Review the student's fee status
2. If all fees are cleared, click **✅ Approve**
3. Confirm your decision
4. Application status changes to "Approved" (Green badge)

#### Reject Application
1. Click **❌ Reject** button
2. A modal will appear requiring you to:
   - Select a rejection reason from dropdown, OR
   - Enter a custom reason
3. Common rejection reasons:
   - Outstanding tuition balance
   - Graduation fee not paid
   - Pending accommodation fees
   - Outstanding library fines
4. Click "Confirm Rejection"
5. Application status changes to "Rejected" (Blue badge)
6. Student will see your rejection reason

### 4. Activity Log

#### Recent Activity (Dashboard)
Shows your last 10 actions on the dashboard

#### Full Activity Log
- Click **"View Full Log"** button
- See complete history of all your decisions
- Each entry shows:
  - Date & Time
  - Student Name
  - Registration Number
  - Action (Approved/Rejected)
  - Reason/Notes
  - Officer Name

### 5. Important Notes

✅ **Best Practices:**
- Always verify fee payment status before approving
- Provide clear, specific reasons when rejecting
- Review the student's complete payment history
- Check for any pending fines or balances

⚠️ **Important:**
- All actions are permanently logged
- Rejection reasons are visible to students
- You cannot undo an approval/rejection (contact admin if needed)
- Only pending applications can be approved/rejected

### 6. Color Coding

The system uses color badges for easy identification:
- 🔴 **Red Badge (Pending):** Awaiting your decision
- 🟢 **Green Badge (Approved):** You've approved this application
- 🔵 **Blue Badge (Rejected):** You've rejected this application

### 7. Fee Check Interface

When you click "View Details", you'll see:
- **Tuition Fees:** Paid/Pending status
- **Graduation Fee:** Paid/Pending status
- **Library Fines:** Cleared/Pending amount
- **Accommodation:** Paid/Pending status
- **Total Balance:** Any outstanding amount

💡 **Tip:** In production, this connects to the actual finance system. Currently shows sample data.

### 8. Workflow

**Standard Approval Process:**
1. Student applies for clearance
2. Finance Officer (you) receives application
3. Review fee payment status
4. Make decision (Approve/Reject)
5. System logs your action
6. Student sees updated status
7. If approved, goes to next department (Library)

### 9. Logout

When finished, click **"Logout"** button at the bottom of the dashboard.

---

## Need Help?

### Common Questions

**Q: Can I change my decision after approving?**
A: No, contact the system administrator to reverse a decision.

**Q: What if a student has partial payment?**
A: Reject with specific reason stating the outstanding amount.

**Q: How do I see all my past actions?**
A: Click "View Full Log" button in the activity section.

**Q: Can I print the activity log?**
A: Yes, use the "Print Log" button on the full activity log page.

---

## Contact Support

- **Email:** support@umu.ac.ug
- **Phone:** +256-XXX-XXXXXX
- **Office Hours:** Monday - Friday, 8:00 AM - 5:00 PM

---

**Last Updated:** March 9, 2026