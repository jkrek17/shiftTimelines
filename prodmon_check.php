<?php
/**
 * Product Monitor Checker
 * Scrapes NOAA product monitor page to check which products have been issued
 * If a product is NOT listed on the monitor, it means it has been updated (on time)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Product monitor URL
$prodmon_url = 'https://ocean.weather.gov/prodmon/index.php?limit=50&width=normal&zoom=medium';

try {
    // Fetch the product monitor page
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    
    $html = @file_get_contents($prodmon_url, false, $context);
    
    if ($html === false) {
        throw new Exception('Failed to fetch product monitor page');
    }
    
    // Parse the HTML to find overdue/pending products
    // Products listed on this page have NOT been updated yet
    $overdue_products = [];
    $overdue_with_times = [];
    
    // Try to find product codes with their issue times
    // Look for patterns like "OFFPZ5" near time indicators like "00Z", "06Z", "12Z", "18Z"
    
    // Pattern: Match product codes
    $product_pattern = '/\b(OFF[A-Z0-9]{2,5}|HSF[A-Z]{2,4}[0-9]?|PY[AB][A-Z][0-9]{2}|P[WPJA][ABCK][MK]?[0-9]{2}|PJCK[0-9]{2}|PPCK[0-9]{2}|OFFN[0-9]{2})\b/i';
    
    preg_match_all($product_pattern, $html, $matches, PREG_OFFSET_CAPTURE);
    
    if (!empty($matches[0])) {
        foreach ($matches[0] as $match) {
            $code = strtoupper($match[0]);
            $position = $match[1];
            
            // Look for issue time near this product code (within 100 chars)
            $surrounding = substr($html, max(0, $position - 50), 200);
            
            // Try to find issue time (00Z, 06Z, 12Z, 18Z, or 00, 06, 12, 18)
            if (preg_match('/\b(00|06|12|18)Z?\b/i', $surrounding, $time_match)) {
                $issue_time = $time_match[1] . 'Z';
                $full_id = $code . '-' . $issue_time;
                $overdue_with_times[$full_id] = true;
            }
            
            $overdue_products[$code] = true;
        }
    }
    
    // Also look for table structure patterns
    // Many prodmon pages have tables with product info
    preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $html, $rows);
    foreach ($rows[1] as $row) {
        if (preg_match($product_pattern, $row, $prod_match)) {
            $code = strtoupper($prod_match[1]);
            $overdue_products[$code] = true;
            
            // Try to get issue time from the row
            if (preg_match('/\b(00|06|12|18)Z?\b/i', $row, $time_match)) {
                $issue_time = $time_match[1] . 'Z';
                $full_id = $code . '-' . $issue_time;
                $overdue_with_times[$full_id] = true;
            }
        }
    }
    
    // Return the results
    echo json_encode([
        'success' => true,
        'timestamp' => date('c'),
        'overdue_products' => array_keys($overdue_products),
        'overdue_with_times' => array_keys($overdue_with_times),
        'count' => count($overdue_products),
        'source_url' => $prodmon_url,
        'debug_html_length' => strlen($html)
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
