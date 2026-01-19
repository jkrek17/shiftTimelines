<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Monitor Test</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1, h2, h3 { color: #0c4a6e; }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status-good { color: #10b981; }
        .status-bad { color: #dc2626; }
        .status-warning { color: #f59e0b; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th { background: #f8f9fa; font-weight: 600; }
        tr:hover { background: #f8f9fa; }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-overdue { background: #fee2e2; color: #dc2626; }
        .badge-issued { background: #d1fae5; color: #059669; }
        .badge-pending { background: #fef3c7; color: #d97706; }
        .code { 
            font-family: monospace; 
            background: #f1f5f9; 
            padding: 2px 6px; 
            border-radius: 4px;
        }
        pre {
            background: #1e293b;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 13px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 900px) {
            .grid { grid-template-columns: 1fr; }
        }
        .refresh-btn {
            background: #0c4a6e;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        .refresh-btn:hover { background: #0891b2; }
    </style>
</head>
<body>
    <h1>🔍 Product Monitor Test Page</h1>
    <p>This page shows what the prodmon_check.php script is detecting for comparison.</p>
    <button class="refresh-btn" onclick="location.reload()">🔄 Refresh</button>
    
<?php
// Product monitor URL
$prodmon_url = 'https://ocean.weather.gov/prodmon/index.php?limit=50&width=normal&zoom=medium';

/**
 * Get current shift info
 */
function getShiftInfo() {
    $hour = (int) gmdate('H');
    $shift = ($hour >= 0 && $hour < 12) ? 'Night Shift' : 'Day Shift';
    
    if ($hour >= 0 && $hour < 6) $issueTime = '00Z';
    elseif ($hour >= 6 && $hour < 12) $issueTime = '06Z';
    elseif ($hour >= 12 && $hour < 18) $issueTime = '12Z';
    else $issueTime = '18Z';
    
    return array(
        'shift' => $shift,
        'issueTime' => $issueTime,
        'utcTime' => gmdate('H:i:s'),
        'utcDate' => gmdate('Y-m-d')
    );
}

/**
 * Parse config.js
 */
function getTasksFromConfig() {
    $config_file = __DIR__ . '/config.js';
    
    if (!file_exists($config_file)) {
        return array('error' => 'config.js not found');
    }
    
    $config_content = file_get_contents($config_file);
    
    $json_start = strpos($config_content, '{');
    $json_end = strrpos($config_content, '}');
    
    if ($json_start === false || $json_end === false) {
        return array('error' => 'Could not parse config.js');
    }
    
    $json_str = substr($config_content, $json_start, $json_end - $json_start + 1);
    
    // Fix JavaScript object to valid JSON
    // First, replace single quotes with double quotes
    $json_str = str_replace("'", '"', $json_str);
    
    // Add quotes around unquoted keys
    $json_str = preg_replace('/(?<!["\w])(\w+)\s*:/m', '"$1":', $json_str);
    
    // Remove trailing commas before } or ]
    $json_str = preg_replace('/,(\s*[\}\]])/', '$1', $json_str);
    
    $config = json_decode($json_str, true);
    
    if ($config === null) {
        return array('error' => 'JSON parse error: ' . json_last_error_msg());
    }
    
    return $config;
}

/**
 * Get tasks for current shift with productId
 */
function getCurrentShiftTasks($config, $shiftInfo) {
    $tasks = array();
    
    foreach ($config as $deskName => $deskConfig) {
        if (!isset($deskConfig['shifts'][$shiftInfo['shift']])) continue;
        
        $shiftTasks = $deskConfig['shifts'][$shiftInfo['shift']];
        if (!is_array($shiftTasks)) continue;
        
        foreach ($shiftTasks as $index => $task) {
            $taskId = $deskName . '-' . $shiftInfo['shift'] . '-' . $index;
            $tasks[] = array(
                'taskId' => $taskId,
                'desk' => $deskName,
                'name' => isset($task['name']) ? $task['name'] : '',
                'deadline' => isset($task['deadline']) ? $task['deadline'] : '',
                'productId' => isset($task['productId']) ? $task['productId'] : null,
                'link' => isset($task['link']) ? $task['link'] : ''
            );
        }
    }
    
    // Sort by deadline
    usort($tasks, function($a, $b) {
        return strcmp($a['deadline'], $b['deadline']);
    });
    
    return $tasks;
}

/**
 * Scrape prodmon page
 */
function scrapeProdmon($url) {
    $context = stream_context_create(array(
        'http' => array(
            'timeout' => 15,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ),
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false
        )
    ));
    
    $html = @file_get_contents($url, false, $context);
    
    if ($html === false) {
        return array('error' => 'Failed to fetch prodmon page', 'html' => '');
    }
    
    // Extract overdue products
    $overdue = array();
    $product_pattern = '/\b(OFF[A-Z0-9]{2,5}|HSF[A-Z]{2,4}[0-9]?|PY[AB][A-Z][0-9]{2}|P[WPJA][ABCK][MK]?[0-9]{2}|PJCK[0-9]{2}|PPCK[0-9]{2}|OFFN[0-9]{2})\b/i';
    
    preg_match_all($product_pattern, $html, $matches);
    if (!empty($matches[0])) {
        $overdue = array_unique(array_map('strtoupper', $matches[0]));
    }
    
    // Try to get product + time combinations
    $overdueWithTimes = array();
    preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $html, $rows);
    foreach ($rows[1] as $row) {
        if (preg_match($product_pattern, $row, $prod_match)) {
            $code = strtoupper($prod_match[1]);
            if (preg_match('/\b(00|06|12|18)\s*Z\b/i', $row, $time_match)) {
                $overdueWithTimes[] = $code . '-' . $time_match[1] . 'Z';
            }
        }
    }
    
    return array(
        'products' => array_values($overdue),
        'products_with_times' => array_unique($overdueWithTimes),
        'html' => $html,
        'html_length' => strlen($html)
    );
}

// Get data
$shiftInfo = getShiftInfo();
$config = getTasksFromConfig();
$prodmonData = scrapeProdmon($prodmon_url);

$currentTasks = [];
if (!isset($config['error'])) {
    $currentTasks = getCurrentShiftTasks($config, $shiftInfo);
}

$overdueSet = array_flip($prodmonData['products'] ?? []);
$overdueWithTimesSet = array_flip($prodmonData['products_with_times'] ?? []);

?>

    <div class="card">
        <h2>⏰ Current Time & Shift</h2>
        <table>
            <tr><th>UTC Date</th><td><?= $shiftInfo['utcDate'] ?></td></tr>
            <tr><th>UTC Time</th><td><strong><?= $shiftInfo['utcTime'] ?></strong></td></tr>
            <tr><th>Current Shift</th><td><strong><?= $shiftInfo['shift'] ?></strong></td></tr>
            <tr><th>Current Issue Cycle</th><td><span class="code"><?= $shiftInfo['issueTime'] ?></span></td></tr>
        </table>
    </div>

    <div class="grid">
        <div class="card">
            <h2>📡 Prodmon Scrape Results</h2>
            <?php if (isset($prodmonData['error'])): ?>
                <p class="status-bad">❌ <?= htmlspecialchars($prodmonData['error']) ?></p>
            <?php else: ?>
                <p class="status-good">✅ Successfully fetched prodmon page (<?= number_format($prodmonData['html_length']) ?> bytes)</p>
                
                <h3>Overdue Products Found (<?= count($prodmonData['products']) ?>)</h3>
                <?php if (empty($prodmonData['products'])): ?>
                    <p class="status-good">No overdue products detected - all products may be current!</p>
                <?php else: ?>
                    <p>These products are listed as overdue (NOT yet issued):</p>
                    <ul>
                    <?php foreach ($prodmonData['products'] as $prod): ?>
                        <li><span class="code"><?= htmlspecialchars($prod) ?></span></li>
                    <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                
                <?php if (!empty($prodmonData['products_with_times'])): ?>
                    <h3>Products with Issue Times (<?= count($prodmonData['products_with_times']) ?>)</h3>
                    <ul>
                    <?php foreach ($prodmonData['products_with_times'] as $prod): ?>
                        <li><span class="code"><?= htmlspecialchars($prod) ?></span></li>
                    <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>📋 Current Shift Tasks with Product IDs</h2>
            <?php 
            $monitoredTasks = array_filter($currentTasks, function($t) { return !empty($t['productId']); });
            ?>
            <p>Found <?= count($monitoredTasks) ?> monitored tasks (with productId) for <?= $shiftInfo['shift'] ?></p>
            
            <table>
                <thead>
                    <tr>
                        <th>Deadline</th>
                        <th>Desk</th>
                        <th>Task</th>
                        <th>Product ID</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($currentTasks as $task): ?>
                    <?php if (empty($task['productId'])) continue; ?>
                    <?php
                    $productCode = explode('-', $task['productId'])[0];
                    $isOverdue = isset($overdueSet[$productCode]) || isset($overdueWithTimesSet[$task['productId']]);
                    $statusClass = $isOverdue ? 'badge-overdue' : 'badge-issued';
                    $statusText = $isOverdue ? 'Pending' : 'Issued ✓';
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($task['deadline']) ?></strong></td>
                        <td><?= htmlspecialchars($task['desk']) ?></td>
                        <td><?= htmlspecialchars($task['name']) ?></td>
                        <td><span class="code"><?= htmlspecialchars($task['productId']) ?></span></td>
                        <td><span class="badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2>🔬 Raw Prodmon HTML (for debugging)</h2>
        <p>First 5000 characters of the fetched HTML:</p>
        <pre><?= htmlspecialchars(substr($prodmonData['html'] ?? '', 0, 5000)) ?>...</pre>
    </div>

    <div class="card">
        <h2>📊 Full API Response</h2>
        <p>This is what <code>prodmon_check.php</code> would return:</p>
        <pre><?php
        // Simulate the API response
        $apiResponse = array(
            'success' => true,
            'timestamp' => gmdate('c'),
            'current_shift' => $shiftInfo['shift'],
            'current_issue_time' => $shiftInfo['issueTime'],
            'overdue_products' => isset($prodmonData['products']) ? $prodmonData['products'] : array(),
            'overdue_with_times' => isset($prodmonData['products_with_times']) ? $prodmonData['products_with_times'] : array(),
            'overdue_count' => isset($prodmonData['products']) ? count($prodmonData['products']) : 0
        );
        echo htmlspecialchars(json_encode($apiResponse, JSON_PRETTY_PRINT));
        ?></pre>
    </div>

</body>
</html>
