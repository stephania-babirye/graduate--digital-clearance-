<?php
session_start();
include '../config/db.php';

// Check if user is logged in and is a registrar
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registrar') {
    header("Location: ../login/index.php");
    exit();
}

// Fetch graduation list
$grad_list_query = "SELECT gl.*, u.full_name, u.registration_number, 
                    sp.program_level, sp.course, sp.campus
                    FROM graduation_list gl
                    JOIN users u ON gl.user_id = u.id
                    JOIN student_profiles sp ON u.id = sp.user_id
                    WHERE gl.confirmation_status = 'confirmed'
                    ORDER BY sp.program_level, u.full_name";
$grad_list_result = $conn->query($grad_list_query);

// Get system settings
$settings_query = "SELECT * FROM system_settings WHERE id = 1";
$settings_result = $conn->query($settings_query);
$settings = $settings_result ? $settings_result->fetch_assoc() : null;

// Normalize settings with defaults to avoid undefined key/null warnings on older schemas.
$default_settings = [
    'university_name' => 'Uganda Martyrs University',
    'university_email' => 'info@umu.ac.ug',
    'university_phone' => '+256-414-410-611',
    'graduation_year' => 2026
];

$settings = is_array($settings) ? array_merge($default_settings, $settings) : $default_settings;

$university_name = (string) ($settings['university_name'] ?? $default_settings['university_name']);
$university_email = (string) ($settings['university_email'] ?? $default_settings['university_email']);
$university_phone = (string) ($settings['university_phone'] ?? $default_settings['university_phone']);
$graduation_year = (int) ($settings['graduation_year'] ?? $default_settings['graduation_year']);

// Set headers for PDF download
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Graduation List <?php echo $graduation_year; ?></title>
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .letterhead {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            border-bottom: 4px double #800000;
        }
        .letterhead-logo {
            width: 100px;
            height: 100px;
            margin: 0 auto 15px;
            background: linear-gradient(135deg, #800000, #FFD700);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
            font-weight: bold;
        }
        .letterhead h1 {
            color: #800000;
            margin: 5px 0;
            font-size: 28px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .letterhead .motto {
            font-style: italic;
            color: #666;
            font-size: 14px;
            margin: 10px 0;
        }
        .letterhead .contact {
            font-size: 11px;
            color: #888;
            margin-top: 10px;
        }
        .document-title {
            text-align: center;
            margin: 30px 0;
            padding: 15px;
            background: linear-gradient(to right, #f8f9fa, #fff, #f8f9fa);
            border-left: 5px solid #800000;
            border-right: 5px solid #FFD700;
        }
        .document-title h2 {
            color: #800000;
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }
        .document-title .year {
            color: #FFD700;
            background: #800000;
            padding: 5px 20px;
            display: inline-block;
            margin-top: 10px;
            border-radius: 3px;
        }
        .meta-info {
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .meta-info strong {
            color: #800000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th {
            background: linear-gradient(to bottom, #800000, #600000);
            color: white;
            padding: 14px;
            text-align: left;
            border: 1px solid #600000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }
        td {
            padding: 12px;
            border: 1px solid #ddd;
            font-size: 13px;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        tr:hover {
            background-color: #f0f0f0;
        }
        .summary-box {
            margin: 30px 0;
            padding: 20px;
            background: linear-gradient(135deg, #fff5e6, #ffffff);
            border-left: 5px solid #FFD700;
            border-radius: 5px;
        }
        .summary-box strong {
            color: #800000;
            font-size: 16px;
        }
        .signature-section {
            margin-top: 60px;
            page-break-inside: avoid;
        }
        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 40px;
        }
        .signature-box {
            padding: 20px;
            border: 2px solid #800000;
            border-radius: 8px;
            background: #fafafa;
        }
        .signature-title {
            color: #800000;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
            text-transform: uppercase;
        }
        .signature-line {
            border-top: 2px solid #000;
            margin-top: 50px;
            padding-top: 8px;
            text-align: center;
        }
        .signature-name {
            font-weight: bold;
            color: #800000;
            margin-bottom: 5px;
        }
        .digital-signature {
            text-align: center;
            margin-top: 10px;
            font-style: italic;
            color: #666;
            font-size: 11px;
        }
        .seal {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            border: 3px double #800000;
            border-radius: 50%;
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 30px auto;
            background: radial-gradient(circle, #FFD700, #FFA500);
        }
        .seal-text {
            font-weight: bold;
            color: #800000;
            font-size: 11px;
            text-align: center;
            line-height: 1.3;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 11px;
            color: #666;
            border-top: 3px double #800000;
            padding-top: 20px;
            page-break-inside: avoid;
        }
        .footer .confidential {
            color: #800000;
            font-weight: bold;
            margin-bottom: 10px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
        <button onclick="window.print()" style="padding: 12px 25px; background: #800000; color: white; border: none; cursor: pointer; margin-right: 10px; border-radius: 5px; font-weight: bold;">🖨️ Print / Save as PDF</button>
        <a href="graduation_list.php" style="padding: 12px 25px; background: #666; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">← Back to List</a>
        <span style="float: right; color: #666; padding-top: 12px;">💾 Use Print to save as PDF</span>
    </div>

    <!-- University Letterhead -->
    <div class="letterhead">
        <div class="letterhead-logo">UMU</div>
        <h1><?php echo htmlspecialchars($university_name); ?></h1>
        <p class="motto">making a difference</p>
        <p class="contact">
            P.O. Box 5498, Kampala, Uganda | 
            Tel: <?php echo htmlspecialchars($university_phone); ?> | 
            Email: <?php echo htmlspecialchars($university_email); ?>
        </p>
    </div>

    <!-- Document Title -->
    <div class="document-title">
        <h2>📜 Official Graduation List</h2>
        <div class="year">Graduation Year: <?php echo $graduation_year; ?></div>
    </div>

    <!-- Meta Information -->
    <div class="meta-info">
        <p><strong>Document Reference:</strong> UMU/REG/GRAD/<?php echo $graduation_year; ?>/<?php echo date('m'); ?></p>
        <p><strong>Generated on:</strong> <?php echo date('l, F j, Y'); ?> at <?php echo date('g:i A'); ?></p>
        <p><strong>Generated by:</strong> <?php echo htmlspecialchars($_SESSION['full_name']); ?> (Academic Registrar)</p>
    </div>

    <!-- Graduation List Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 60px;">No.</th>
                <th style="width: 150px;">Reg. Number</th>
                <th>Full Name</th>
                <th>Programme</th>
                <th style="width: 120px;">Campus</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $counter = 1;
            $total_students = 0;
            while ($grad = $grad_list_result->fetch_assoc()): 
                $total_students++;
            ?>
            <tr>
                <td style="text-align: center; font-weight: bold;"><?php echo $counter++; ?></td>
                <td><strong><?php echo htmlspecialchars($grad['registration_number']); ?></strong></td>
                <td><?php echo htmlspecialchars($grad['full_name']); ?></td>
                <td><?php echo htmlspecialchars($grad['course'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($grad['campus'] ?? 'N/A'); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Summary Box -->
    <div class="summary-box">
        <strong>📊 Total Students Confirmed for Graduation: <?php echo $total_students; ?></strong>
        <p style="margin: 10px 0 0 0; color: #666; font-size: 13px;">
            All students listed above have successfully completed clearance requirements from Finance, Library, ICT, and Faculty departments.
        </p>
    </div>

    <!-- Official Seal -->
    <div class="seal">
        <div class="seal-text">
            OFFICIAL SEAL<br>
            <?php echo htmlspecialchars($university_name); ?><br>
            <?php echo $graduation_year; ?>
        </div>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-grid">
            <div class="signature-box">
                <div class="signature-title">Academic Registrar</div>
                <div class="signature-line">
                    <div class="signature-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                    <div>Academic Registrar</div>
                    <div style="margin-top: 10px; font-size: 12px;">Date: <?php echo date('F j, Y'); ?></div>
                </div>
                <div class="digital-signature">
                    🔒 Digitally signed on <?php echo date('Y-m-d H:i:s'); ?>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Vice Chancellor</div>
                <div class="signature-line">
                    <div class="signature-name">_______________________</div>
                    <div>Vice Chancellor</div>
                    <div style="margin-top: 10px; font-size: 12px;">Date: _________________</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p class="confidential">⚠️ CONFIDENTIAL DOCUMENT</p>
        <p><strong><?php echo htmlspecialchars($settings['university_name']); ?></strong></p>
        <p>This is an official document generated by the Graduate Digital Clearance System</p>
        <p style="margin-top: 10px;">Document ID: GRAD-<?php echo $settings['graduation_year']; ?>-<?php echo date('YmdHis'); ?></p>
        <p style="margin-top: 15px; font-size: 10px; color: #999;">
            Generated automatically. No signature required for digital copy. For official use only.
        </p>
    </div>

    <?php
    // Log activity
    $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                 VALUES ({$_SESSION['user_id']}, 'PDF Generated', 
                 'Generated official graduation list PDF for year {$settings['graduation_year']} with $total_students students', '{$_SERVER['REMOTE_ADDR']}')";
    $conn->query($log_query);
    ?>
</body>
</html>