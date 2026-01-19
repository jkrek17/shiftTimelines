<?php
/**
 * NOAA OPC Timeline Dashboard - Backend API
 * 
 * Simple PHP backend to persist task completion states
 * No database required - uses JSON file storage
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration
$dataFile = 'task_states.json';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Load task states from JSON file
 */
function loadStates($dataFile) {
    if (!file_exists($dataFile)) {
        return [];
    }
    
    $json = file_get_contents($dataFile);
    $data = json_decode($json, true);
    
    return $data ?: [];
}

/**
 * Save task states to JSON file
 */
function saveStates($dataFile, $states) {
    $json = json_encode($states, JSON_PRETTY_PRINT);
    file_put_contents($dataFile, $json);
}

// Route handling
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            // Load all task states
            $states = loadStates($dataFile);
            echo json_encode([
                'success' => true,
                'data' => $states
            ]);
            break;
            
        case 'POST':
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'save') {
                // Save a single task state
                $taskId = $input['taskId'] ?? null;
                $done = $input['done'] ?? false;
                
                if (!$taskId) {
                    throw new Exception('Task ID required');
                }
                
                $states = loadStates($dataFile);
                $states[$taskId] = [
                    'done' => $done,
                    'updated' => date('Y-m-d H:i:s')
                ];
                saveStates($dataFile, $states);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Task state saved'
                ]);
                
            } elseif ($action === 'bulk') {
                // Save multiple task states at once
                $tasks = $input['tasks'] ?? [];
                
                $states = loadStates($dataFile);
                foreach ($tasks as $taskId => $done) {
                    $states[$taskId] = [
                        'done' => $done,
                        'updated' => date('Y-m-d H:i:s')
                    ];
                }
                saveStates($dataFile, $states);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Bulk save completed'
                ]);
                
            } elseif ($action === 'reset') {
                // Reset all tasks (new shift/day)
                saveStates($dataFile, []);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'All task states reset'
                ]);
                
            } else {
                throw new Exception('Invalid action');
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
