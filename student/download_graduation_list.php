<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if student is on graduation list
$check_query = "SELECT * FROM graduation_list WHERE user_id = $user_id";
$check_result = $conn->query($check_query);

if (!$check_result || $check_result->num_rows == 0) {
    $_SESSION['error'] = "You are not on the graduation list!";
    header("Location: dashboard.php");
    exit();
}

// Fetch all students on graduation list for display
$list_query = "SELECT u.full_name, u.registration_number, sp.program_level, sp.campus, gl.added_at
               FROM graduation_list gl
               JOIN users u ON gl.user_id = u.id
               JOIN student_profiles sp ON u.id = sp.user_id
               ORDER BY sp.program_level, u.full_name";

$list_result = $conn->query($list_query);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Graduation List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background: white;
        }
        .document {
            max-width: 900px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            color: #800000;
            margin-bottom: 30px;
            border-bottom: 3px double #FFD700;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 10px 0;
            font-size: 28px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background-color: #800000;
            color: #FFD700;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        @media print {
            body { padding: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="background: #800000; color: #FFD700; border: none; padding: 10px 30px; font-size: 16px; cursor: pointer; border-radius: 5px;">Print List</button>
        <button onclick="window.close()" style="background: #666; color: white; border: none; padding: 10px 30px; font-size: 16px; cursor: pointer; border-radius: 5px; margin-left: 10px;">Close</button>
    </div>

    <div class="document">
        <div class="header">
            <h1>UGANDA MARTYRS UNIVERSITY</h1>
            <h2>OFFICIAL GRADUATION LIST</h2>
            <p>Academic Year 2025/2026</p>
            <p style="font-size: 14px;">Generated on: <?php echo date('F j, Y g:i A'); ?></p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Registration Number</th>
                    <th>Full Name</th>
                    <th>Programme</th>
                    <th>Campus</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $count = 1;
                while ($row = $list_result->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo $count++; ?></td>
                    <td><?php echo htmlspecialchars($row['registration_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['course']); ?></td>
                    <td><?php echo htmlspecialchars($row['campus']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="footer">
            <p style="border-top: 2px solid #800000; padding-top: 20px;">
                <strong>Total Confirmed Graduates: <?php echo $count - 1; ?></strong>
            </p>
            <p style="margin-top: 20px;">
                This is an official document of Uganda Martyrs University.<br>
                Clearance System - <?php echo date('Y'); ?>
            </p>
        </div>
    </div>
</body>
</html>