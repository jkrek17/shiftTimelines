<?php
/**
 * Configuration Save Handler
 * Saves updated config.js and handles restore from default
 */

header('Content-Type: application/json');

// CORS headers if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Read request body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit();
}

// Handle restore default
if (isset($data['action']) && $data['action'] === 'restore_default') {
    try {
        // Check if default config exists
        if (!file_exists('config.default.js')) {
            throw new Exception('Default configuration not found');
        }

        // Copy default to active
        if (!copy('config.default.js', 'config.js')) {
            throw new Exception('Failed to restore default configuration');
        }

        echo json_encode([
            'success' => true,
            'message' => 'Default configuration restored'
        ]);
        exit();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit();
    }
}

// Handle save config
if (!isset($data['content'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No content provided']);
    exit();
}

$content = $data['content'];

try {
    // Backup current config before overwriting
    if (file_exists('config.js')) {
        $backupFile = 'config.backup.' . date('Y-m-d_H-i-s') . '.js';
        copy('config.js', $backupFile);
    }

    // Write new config
    $result = file_put_contents('config.js', $content);

    if ($result === false) {
        throw new Exception('Failed to write config file');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Configuration saved successfully',
        'bytes' => $result,
        'backup' => isset($backupFile) ? $backupFile : null
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
