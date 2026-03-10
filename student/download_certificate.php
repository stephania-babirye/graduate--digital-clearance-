<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch student and clearance info
$query = "SELECT u.full_name, u.registration_number, u.email,
          sp.program_level, sp.campus, sp.date_of_birth, sp.photo_path,
          ca.applied_at, ca.finance_status, ca.library_status, 
          ca.ict_status, ca.faculty_status, ca.registrar_status
          FROM users u
          JOIN student_profiles sp ON u.id = sp.user_id
          JOIN clearance_applications ca ON u.id = ca.user_id
          WHERE u.id = $user_id";

$result = $conn->query($query);
$data = $result->fetch_assoc();

// Check if all departments approved
if ($data['finance_status'] != 'approved' || $data['library_status'] != 'approved' || 
    $data['ict_status'] != 'approved' || $data['faculty_status'] != 'approved' || 
    $data['registrar_status'] != 'approved') {
    $_SESSION['error'] = "Certificate not available. Complete clearance first!";
    header("Location: dashboard.php");
    exit();
}

// Generate HTML certificate
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Clearance Certificate</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            padding: 40px;
            background: white;
        }
        .certificate {
            max-width: 800px;
            margin: 0 auto;
            border: 10px double #800000;
            padding: 40px;
            background: white;
        }
        .header {
            text-align: center;
            color: #800000;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 10px 0;
            font-size: 32px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 24px;
            color: #FFD700;
            text-shadow: 1px 1px 2px #800000;
        }
        .photo {
            text-align: center;
            margin: 20px 0;
        }
        .photo img {
            width: 150px;
            height: 150px;
            border: 3px solid #800000;
            border-radius: 5px;
        }
        .content {
            line-height: 2;
            font-size: 16px;
        }
        .field {
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
            color: #800000;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 14px;
        }
        .signature {
            margin-top: 50px;
            text-align: right;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="background: #800000; color: #FFD700; border: none; padding: 10px 30px; font-size: 16px; cursor: pointer; border-radius: 5px;">Print Certificate</button>
        <button onclick="window.close()" style="background: #666; color: white; border: none; padding: 10px 30px; font-size: 16px; cursor: pointer; border-radius: 5px; margin-left: 10px;">Close</button>
    </div>

    <div class="certificate">
        <div class="header">
            <h1>UGANDA MARTYRS UNIVERSITY</h1>
            <h2>GRADUATE CLEARANCE CERTIFICATE</h2>
            <p style="font-style: italic; color: #666;">This is to certify that</p>
        </div>

        <?php if ($data['photo_path']): ?>
        <div class="photo">
            <img src="../<?php echo htmlspecialchars($data['photo_path']); ?>" alt="Student Photo">
        </div>
        <?php endif; ?>

        <div class="content">
            <div class="field">
                <span class="label">Full Name:</span>
                <span><?php echo htmlspecialchars($data['full_name']); ?></span>
            </div>
            
            <div class="field">
                <span class="label">Registration Number:</span>
                <span><?php echo htmlspecialchars($data['registration_number']); ?></span>
            </div>
            
            <div class="field">
                <span class="label">Programme:</span>
                <span><?php echo htmlspecialchars($data['program_level']); ?></span>
            </div>
            
            <div class="field">
                <span class="label">Date of Birth:</span>
                <span><?php echo date('F j, Y', strtotime($data['date_of_birth'])); ?></span>
            </div>
            
            <div class="field">
                <span class="label">Campus:</span>
                <span><?php echo htmlspecialchars($data['campus']); ?></span>
            </div>

            <p style="margin-top: 30px; text-align: justify;">
                Has successfully completed all clearance requirements from the Finance Department, 
                Library, ICT Department, Faculty, and the Academic Registrar's Office, and is hereby 
                cleared to participate in the graduation ceremony.
            </p>

            <div class="field" style="margin-top: 30px;">
                <span class="label">Date Issued:</span>
                <span><?php echo date('F j, Y'); ?></span>
            </div>
        </div>

        <div class="signature">
            <p>_________________________</p>
            <p style="font-weight: bold;">Academic Registrar</p>
            <p>Uganda Martyrs University</p>
        </div>

        <div class="footer">
            <p style="color: #800000; border-top: 2px solid #FFD700; padding-top: 20px;">
                This certificate is valid for the current academic year only.
            </p>
        </div>
    </div>
</body>
</html>