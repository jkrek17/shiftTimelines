<?php
/**
 * Product Monitor Checker
 * Scrapes NOAA product monitor page to check which products have been issued
 * If a product is NOT listed on the monitor, it means it has been updated (on time)
 * 
 * This script:
 * 1. Reads config.js to get task -> productId mappings
 * 2. Scrapes the prodmon page to get list of overdue (not yet issued) products
 * 3. Returns list of task IDs that can be auto-completed (product was issued)
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display, but log them

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Product monitor URL
$prodmon_url = 'https://ocean.weather.gov/prodmon/index.php?limit=50&width=normal&zoom=medium';

/**
 * Parse config.js to extract task productId mappings
 */
function getTaskProductMappings() {
    $config_file = __DIR__ . '/config.js';
    
    if (!file_exists($config_file)) {
        throw new Exception('config.js not found');
    }
    
    $config_content = file_get_contents($config_file);
    
    // Extract the SHIFT_TIMELINES object
    $json_start = strpos($config_content, '{');
    $json_end = strrpos($config_content, '}');
    
    if ($json_start === false || $json_end === false) {
        throw new Exception('Could not parse config.js');
    }
    
    $json_str = substr($config_content, $json_start, $json_end - $json_start + 1);
    
    // Fix JavaScript object to valid JSON
    // First, replace single quotes with double quotes
    $json_str = str_replace("'", '"', $json_str);
    
    // Add quotes around unquoted keys (keys that aren't already quoted)
    // Match word characters followed by colon, but not if preceded by a quote
    $json_str = preg_replace('/(?<!["\w])(\w+)\s*:/m', '"$1":', $json_str);
    
    // Remove trailing commas before } or ]
    $json_str = preg_replace('/,(\s*[\}\]])/', '$1', $json_str);
    
    $config = json_decode($json_str, true);
    
    if ($config === null) {
        throw new Exception('Failed to parse config JSON: ' . json_last_error_msg());
    }
    
    // Build task ID -> productId mapping
    $mappings = array();
    
    foreach ($config as $deskName => $deskConfig) {
        if (!isset($deskConfig['shifts'])) continue;
        
        foreach ($deskConfig['shifts'] as $shiftType => $tasks) {
            if (!is_array($tasks)) continue;
            foreach ($tasks as $index => $task) {
                if (!empty($task['productId'])) {
                    $taskId = $deskName . '-' . $shiftType . '-' . $index;
                    $mappings[$taskId] = array(
                        'productId' => $task['productId'],
                        'name' => $task['name'],
                        'deadline' => $task['deadline'],
                        'desk' => $deskName,
                        'shift' => $shiftType
                    );
                }
            }
        }
    }
    
    return $mappings;
}

/**
 * Scrape the product monitor page - use shell exec to call curl (bypasses PHP restrictions)
 */
function scrapeProductMonitor($url) {
    $html = false;
    $error_msg = '';
    
    // Try shell exec with curl first (works when PHP curl is blocked)
    if (function_exists('shell_exec')) {
        $escaped_url = escapeshellarg($url);
        $html = shell_exec("curl -s -L -k --max-time 30 {$escaped_url} 2>&1");
        
        if ($html === null || strpos($html, 'curl:') === 0) {
            $error_msg = 'shell_exec curl failed: ' . ($html ? $html : 'null response');
            $html = false;
        }
    }
    
    // Fallback to PHP cURL
    if ($html === false && function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        
        $html = curl_exec($ch);
        
        if ($html === false) {
            $error_msg .= ' | PHP cURL error: ' . curl_error($ch);
        }
        curl_close($ch);
    }
    
    // Fallback to file_get_contents
    if ($html === false) {
        $context = stream_context_create(array(
            'http' => array(
                'timeout' => 30,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ),
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false
            )
        ));
        
        $html = @file_get_contents($url, false, $context);
    }
    
    if ($html === false || empty($html)) {
        throw new Exception('Failed to fetch product monitor page: ' . $error_msg);
    }
    
    // Parse the HTML to find overdue/pending products
    $overdue_products = array();
    $overdue_with_times = array();
    
    // Pattern: Match OPC product codes
    $product_pattern = '/\b(OFF[A-Z0-9]{2,5}|HSF[A-Z]{2,4}[0-9]?|PY[AB][A-Z][0-9]{2}|P[WPJA][ABCK][MK]?[0-9]{2}|PJCK[0-9]{2}|PPCK[0-9]{2}|OFFN[0-9]{2})\b/i';
    
    // First pass: find all product codes with position info
    preg_match_all($product_pattern, $html, $matches, PREG_OFFSET_CAPTURE);
    
    if (!empty($matches[0])) {
        foreach ($matches[0] as $match) {
            $code = strtoupper($match[0]);
            $position = $match[1];
            
            // Look for issue time near this product code
            $surrounding = substr($html, max(0, $position - 100), 300);
            
            // Try to find issue time (00Z, 06Z, 12Z, 18Z)
            if (preg_match('/\b(00|06|12|18)\s*Z\b/i', $surrounding, $time_match)) {
                $issue_time = $time_match[1] . 'Z';
                $full_id = $code . '-' . $issue_time;
                $overdue_with_times[$full_id] = true;
            }
            
            $overdue_products[$code] = true;
        }
    }
    
    // Second pass: look at table rows for more context
    preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $html, $rows);
    foreach ($rows[1] as $row) {
        if (preg_match($product_pattern, $row, $prod_match)) {
            $code = strtoupper($prod_match[1]);
            $overdue_products[$code] = true;
            
            // Try to get issue time from the same row
            if (preg_match('/\b(00|06|12|18)\s*Z\b/i', $row, $time_match)) {
                $issue_time = $time_match[1] . 'Z';
                $full_id = $code . '-' . $issue_time;
                $overdue_with_times[$full_id] = true;
            }
        }
    }
    
    return array(
        'products' => array_keys($overdue_products),
        'products_with_times' => array_keys($overdue_with_times),
        'html_length' => strlen($html)
    );
}

/**
 * Get current UTC hour to determine which issue times are relevant
 */
function getCurrentIssueTime() {
    $hour = (int) gmdate('H');
    
    // Determine which issue cycle we're in
    if ($hour >= 0 && $hour < 6) return '00Z';
    if ($hour >= 6 && $hour < 12) return '06Z';
    if ($hour >= 12 && $hour < 18) return '12Z';
    return '18Z';
}

/**
 * Check if a task's deadline has passed or is within window
 */
function isTaskInWindow($deadline, $windowMinutes = 30) {
    $now = new DateTime('now', new DateTimeZone('UTC'));
    $currentHour = (int) $now->format('H');
    $currentMinute = (int) $now->format('i');
    $currentDecimal = $currentHour + ($currentMinute / 60);
    
    // Parse deadline (format: "HH:MM" or "H:MM")
    $parts = explode(':', $deadline);
    $deadlineHour = (int) $parts[0];
    $deadlineMinute = isset($parts[1]) ? (int) $parts[1] : 0;
    $deadlineDecimal = $deadlineHour + ($deadlineMinute / 60);
    
    // Calculate time difference
    $diff = $deadlineDecimal - $currentDecimal;
    
    // Handle day wrap
    if ($diff < -12) $diff += 24;
    if ($diff > 12) $diff -= 24;
    
    // Task is in window if deadline passed or within windowMinutes
    $windowHours = $windowMinutes / 60;
    return $diff <= $windowHours;
}

// Main execution
try {
    // Get task mappings from config
    $taskMappings = getTaskProductMappings();
    
    // Scrape the product monitor
    $prodmonData = scrapeProductMonitor($prodmon_url);
    
    $overdueProducts = array_flip($prodmonData['products']);
    $overdueWithTimes = array_flip($prodmonData['products_with_times']);
    
    // Determine which tasks can be auto-completed
    $tasksToComplete = array();
    $currentIssueTime = getCurrentIssueTime();
    
    foreach ($taskMappings as $taskId => $taskInfo) {
        $productId = $taskInfo['productId'];
        $parts = explode('-', $productId);
        $productCode = $parts[0];
        $issueTime = isset($parts[1]) ? $parts[1] : '';
        
        // Check if this task's deadline is in the completion window
        if (!isTaskInWindow($taskInfo['deadline'], 60)) {
            continue; // Skip tasks not yet due
        }
        
        // Determine if product was issued (NOT in overdue list)
        $isIssued = false;
        
        // First try exact match with time
        if (!empty($overdueWithTimes)) {
            $isIssued = !isset($overdueWithTimes[$productId]);
        } else {
            // Fall back to just product code
            $isIssued = !isset($overdueProducts[$productCode]);
        }
        
        if ($isIssued) {
            $tasksToComplete[] = array(
                'taskId' => $taskId,
                'productId' => $productId,
                'name' => $taskInfo['name'],
                'deadline' => $taskInfo['deadline'],
                'desk' => $taskInfo['desk'],
                'shift' => $taskInfo['shift']
            );
        }
    }
    
    // Return results
    echo json_encode(array(
        'success' => true,
        'timestamp' => gmdate('c'),
        'current_issue_time' => $currentIssueTime,
        'tasks_to_complete' => $tasksToComplete,
        'overdue_products' => $prodmonData['products'],
        'overdue_with_times' => $prodmonData['products_with_times'],
        'overdue_count' => count($prodmonData['products']),
        'total_monitored_tasks' => count($taskMappings)
    ), JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'error' => $e->getMessage()
    ));
}
