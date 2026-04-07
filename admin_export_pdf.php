<?php
session_start();
require 'db.php';

// Check if user is admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

// Get filter parameters
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$filter_student = $_POST['student_id'] ?? '';

// Fetch sit-in records based on filters
$query = '
    SELECT 
        ss.id, 
        ss.student_id, 
        ss.entry_time, 
        ss.purpose, 
        ss.lab,
        s.first_name,
        s.last_name,
        s.id_number,
        s.course,
        s.course_level
    FROM sitin_sessions ss
    JOIN students s ON ss.student_id = s.id
    WHERE 1=1
';

$params = [];

if ($start_date) {
    $query .= ' AND DATE(ss.entry_time) >= ?';
    $params[] = $start_date;
}

if ($end_date) {
    $query .= ' AND DATE(ss.entry_time) <= ?';
    $params[] = $end_date;
}

if ($filter_student) {
    $query .= ' AND s.id_number LIKE ?';
    $params[] = '%' . $filter_student . '%';
}

$query .= ' ORDER BY ss.entry_time DESC';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$records = $stmt->fetchAll();

// Generate HTML for browser printing/PDF save
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>CCS Sit-in Report</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333; 
            background-color: #f9f9f9;
            line-height: 1.6;
        }
        .page { 
            page-break-after: always; 
            background-color: white;
            padding: 20px;
            margin: 10px auto;
            max-width: 8.5in;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header { 
            text-align: center; 
            padding: 30px 0 20px 0; 
            border-bottom: 3px solid #003366;
            margin-bottom: 20px;
        }
        .header h1 { 
            font-size: 28px; 
            color: #003366;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .header p {
            font-size: 14px;
            color: #0056b3;
            margin: 3px 0;
        }
        .header .institution {
            font-size: 12px;
            color: #666;
            margin-top: 8px;
            font-style: italic;
        }
        .meta { 
            text-align: right; 
            font-size: 11px; 
            padding: 15px 0;
            color: #666;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 20px;
        }
        .meta p {
            margin: 4px 0;
        }
        .title-section { 
            padding: 0;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #003366;
            margin: 20px 0 10px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #003366;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 15px 0; 
            font-size: 11px;
            background-color: white;
        }
        th { 
            background-color: #003366; 
            color: white; 
            padding: 12px; 
            border: 1px solid #003366;
            text-align: left; 
            font-weight: bold;
            font-size: 12px;
        }
        td { 
            padding: 10px 12px; 
            border: 1px solid #ddd;
            vertical-align: top;
        }
        tbody tr:nth-child(odd) { 
            background-color: #f9f9f9; 
        }
        tbody tr:nth-child(even) { 
            background-color: #ffffff; 
        }
        .summary { 
            margin: 25px 0; 
            padding: 15px;
            background-color: #e8f0f7;
            border-left: 4px solid #003366;
            font-weight: bold; 
            font-size: 13px;
            color: #003366;
        }
        .summary p {
            margin: 8px 0;
        }
        .controls { 
            margin: 30px; 
            text-align: center; 
            padding: 20px;
            background-color: #f0f0f0;
            border-radius: 8px;
        }
        .controls button { 
            padding: 12px 24px; 
            margin: 5px; 
            font-size: 14px; 
            cursor: pointer; 
            border: none; 
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-pdf { 
            background-color: #d9534f; 
            color: white;
        }
        .btn-print { 
            background-color: #003366; 
            color: white; 
        }
        .btn-back { 
            background-color: #666; 
            color: white; 
        }
        .btn-pdf:hover { 
            background-color: #c9302c; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .btn-print:hover { 
            background-color: #004b93;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .btn-back:hover { 
            background-color: #555;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
        .no-records {
            text-align: center; 
            color: #999;
            padding: 30px;
            font-style: italic;
        }
        @media print {
            body { 
                margin: 0; 
                padding: 0; 
                background-color: white;
            }
            .page {
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            .controls { 
                display: none; 
            }
        }
    </style>
</head>
<body>
    <div id="report-content">
        <div class="page">
            <div class="header">
                <h1>📊 CCS SIT-IN MANAGEMENT SYSTEM</h1>
                <p>Attendance Report</p>
                <div class="institution">University of Cebu - College of Computer Studies</div>
            </div>

            <div class="meta">
                <p><strong>Report Generated:</strong> <?= date('l, F d, Y \a\t H:i:s') ?></p>
                <?php if ($start_date || $end_date): ?>
                    <p>
                        <?php if ($start_date) echo '<strong>Period From:</strong> ' . htmlspecialchars($start_date); ?>
                        <?php if ($start_date && $end_date) echo ' — '; ?>
                        <?php if ($end_date) echo '<strong>To:</strong> ' . htmlspecialchars($end_date); ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="title-section">
                <div class="section-title">📋 Sit-in Session Records</div>
                <table>
                    <thead>
                        <tr>
                            <th>ID Number</th>
                            <th>Student Name</th>
                            <th>Purpose</th>
                            <th>Laboratory</th>
                            <th>Login Time</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $record): 
                                $entry_time = new DateTime($record['entry_time']);
                                $login = $entry_time->format('h:i A');
                                $date = $entry_time->format('M d, Y');
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($record['id_number']) ?></td>
                                    <td><?= htmlspecialchars($record['first_name'] . ' ' . $record['last_name']) ?></td>
                                    <td><?= htmlspecialchars($record['purpose']) ?></td>
                                    <td><?= htmlspecialchars($record['lab']) ?></td>
                                    <td><?= $login ?></td>
                                    <td><?= $date ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-records">📭 No records found for the specified period</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="summary">
                    <p>📊 Total Sessions Recorded: <strong><?= count($records) ?></strong></p>
                </div>

                <div class="footer">
                    <p>This is an official report from the CCS Sit-in Management System</p>
                    <p>For inquiries, please contact the College of Computer Studies</p>
                </div>
            </div>
        </div>
    </div>

    <div class="controls">
        <button class="btn-pdf" onclick="downloadPDF()">📥 Download PDF</button>
        <button class="btn-print" onclick="window.print()">🖨️ Print</button>
        <button class="btn-back" onclick="history.back()">←  Back</button>
    </div>

    <script>
        function downloadPDF() {
            const element = document.getElementById('report-content');
            const filename = 'CCS_Sitin_Report_<?= date('Y-m-d_His') ?>.pdf';
            
            const options = {
                margin: 10,
                filename: filename,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
            };
            
            html2pdf().set(options).from(element).save();
        }
    </script>
</body>
</html>

