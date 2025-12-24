<?php
// ----------------------------
// Hostname
// ----------------------------
$hostname = gethostname();

// ----------------------------
// Disk information
// ----------------------------
$diskTotal = disk_total_space("/");
$diskFree  = disk_free_space("/");
$diskUsed  = $diskTotal - $diskFree;

function formatBytes($bytes) {
    $units = ['B','KB','MB','GB','TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

// ----------------------------
// RAM information (Linux)
// ----------------------------
$meminfo = file("/proc/meminfo");
$mem = [];

foreach ($meminfo as $line) {
    list($key, $val) = explode(":", $line);
    $mem[$key] = trim($val);
}

$memTotal = intval(filter_var($mem['MemTotal'], FILTER_SANITIZE_NUMBER_INT)) * 1024;
$memFree  = intval(filter_var($mem['MemAvailable'], FILTER_SANITIZE_NUMBER_INT)) * 1024;
$memUsed  = $memTotal - $memFree;

// ----------------------------
// Network information
// ----------------------------
$ipAddresses = gethostbynamel(gethostname());

$networkStats = [];
if (file_exists("/proc/net/dev")) {
    $lines = file("/proc/net/dev");
    foreach ($lines as $line) {
        if (strpos($line, ":") !== false) {
            list($iface, $data) = explode(":", $line);
            $iface = trim($iface);
            $data  = preg_split('/\s+/', trim($data));

            $networkStats[$iface] = [
                'rx_bytes' => $data[0],
                'tx_bytes' => $data[8],
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Information</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #0f172a;
            color: #e5e7eb;
            padding: 20px;
        }
        h1, h2 {
            color: #38bdf8;
        }
        .box {
            background: #020617;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            border-bottom: 1px solid #1e293b;
            text-align: left;
        }
    </style>
</head>
<body>

<h1>System Information</h1>

<div class="box">
    <h2>Hostname</h2>
    <p><?= htmlspecialchars($hostname) ?></p>
</div>

<div class="box">
    <h2>Disk Usage (/)</h2>
    <table>
        <tr><th>Total</th><td><?= formatBytes($diskTotal) ?></td></tr>
        <tr><th>Used</th><td><?= formatBytes($diskUsed) ?></td></tr>
        <tr><th>Free</th><td><?= formatBytes($diskFree) ?></td></tr>
    </table>
</div>

<div class="box">
    <h2>Memory (RAM)</h2>
    <table>
        <tr><th>Total</th><td><?= formatBytes($memTotal) ?></td></tr>
        <tr><th>Used</th><td><?= formatBytes($memUsed) ?></td></tr>
        <tr><th>Available</th><td><?= formatBytes($memFree) ?></td></tr>
    </table>
</div>

<div class="box">
    <h2>Network</h2>

    <h3>IP Addresses</h3>
    <ul>
        <?php foreach ($ipAddresses as $ip): ?>
            <li><?= htmlspecialchars($ip) ?></li>
        <?php endforeach; ?>
    </ul>

    <h3>Interfaces</h3>
    <table>
        <tr>
            <th>Interface</th>
            <th>RX</th>
            <th>TX</th>
        </tr>
        <?php foreach ($networkStats as $iface => $stats): ?>
            <tr>
                <td><?= htmlspecialchars($iface) ?></td>
                <td><?= formatBytes($stats['rx_bytes']) ?></td>
                <td><?= formatBytes($stats['tx_bytes']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
