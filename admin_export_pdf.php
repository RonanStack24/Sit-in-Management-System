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
        * { margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; color: #333; }
        .page { page-break-after: always; }
        .header { text-align: center; padding: 20px; border-bottom: 2px solid #003366; }
        .header h1 { font-size: 24px; margin-bottom: 10px; }
        .meta { text-align: right; font-size: 11px; padding: 10px 15px; color: #666; }
        .title-section { padding: 15px; }
        table { width: 100%; border-collapse: collapse; margin: 15px auto; font-size: 12px; }
        th { background-color: #336699; color: white; padding: 10px; border: 1px solid #333; text-align: left; font-weight: bold; }
        td { padding: 8px; border: 1px solid #999; }
        tr:nth-child(even) { background-color: #f5f5f5; }
        tr:hover { background-color: #e8f0f7; }
        .summary { margin: 20px 15px; font-weight: bold; font-size: 12px; }
        .controls { margin: 20px; text-align: center; }
        .controls button { padding: 10px 20px; margin: 5px; font-size: 14px; cursor: pointer; border: none; border-radius: 5px; }
        .btn-pdf { background-color: #d9534f; color: white; }
        .btn-print { background-color: #336699; color: white; }
        .btn-back { background-color: #666; color: white; }
        .btn-pdf:hover { background-color: #c9302c; }
        .btn-print:hover { background-color: #004b93; }
        .btn-back:hover { background-color: #555; }
        @media print {
            body { margin: 0; padding: 0; }
            .controls { display: none; }
        }
    </style>
</head>
<body>
    <div id="report-content">
        <div class="page">
            <div class="header">
                <h1>CCS | Home</h1>
                <p>Sit-in Management System Report</p>
            </div>

            <div class="meta">
                <p><strong>Report Generated:</strong> <?= date('F d, Y H:i:s') ?></p>
                <?php if ($start_date || $end_date): ?>
                    <p>
                        <?php if ($start_date) echo '<strong>From:</strong> ' . htmlspecialchars($start_date); ?>
                        <?php if ($start_date && $end_date) echo ' | '; ?>
                        <?php if ($end_date) echo '<strong>To:</strong> ' . htmlspecialchars($end_date); ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="title-section">
                <table>
                    <thead>
                        <tr>
                            <th>ID Number</th>
                            <th>Name</th>
                            <th>Purpose</th>
                            <th>Laboratory</th>
                            <th>Login</th>
                            <th>Logout</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $record): 
                                $entry_time = new DateTime($record['entry_time']);
                                $login = $entry_time->format('h:i:sa');
                                $date = $entry_time->format('Y-m-d');
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($record['id_number']) ?></td>
                                    <td><?= htmlspecialchars($record['first_name'] . ' ' . $record['last_name']) ?></td>
                                    <td><?= htmlspecialchars($record['purpose']) ?></td>
                                    <td><?= htmlspecialchars($record['lab']) ?></td>
                                    <td><?= $login ?></td>
                                    <td>-</td>
                                    <td><?= $date ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #999;">No records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="summary">
                    <p>Total Records: <?= count($records) ?></p>
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

