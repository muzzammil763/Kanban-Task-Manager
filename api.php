<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'task_manager');
class TaskDatabase {
    private $conn;
    
    public function __construct() {
        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8mb4");
            $this->initialize();
        } catch (Exception $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    private function initialize() {
        $sql = "CREATE TABLE IF NOT EXISTS tasks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            content TEXT NOT NULL,
            status VARCHAR(50) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX idx_status (status),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (!$this->conn->query($sql)) {
            throw new Exception("Error creating table: " . $this->conn->error);
        }
    }
    
    public function getAllTasks() {
        $sql = "SELECT * FROM tasks ORDER BY created_at DESC";
        $result = $this->conn->query($sql);
        
        if (!$result) {
            throw new Exception("Error fetching tasks: " . $this->conn->error);
        }
        
        $tasks = [];
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
        
        return $tasks;
    }
    
    public function createTask($content, $status) {
        $sql = "INSERT INTO tasks (content, status, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("ss", $content, $status);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $id = $this->conn->insert_id;
        $stmt->close();
        
        return $this->getTask($id);
    }
    
    public function updateTask($id, $content, $status) {
        $sql = "UPDATE tasks SET content = ?, status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("ssi", $content, $status, $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        
        return $this->getTask($id);
    }
    
    public function updateTaskStatus($id, $status) {
        $sql = "UPDATE tasks SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("si", $status, $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        
        return $this->getTask($id);
    }
    
    public function deleteTask($id) {
        $sql = "DELETE FROM tasks WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        
        return ['success' => true];
    }
    
    private function getTask($id) {
        $sql = "SELECT * FROM tasks WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $task = $result->fetch_assoc();
        $stmt->close();
        
        return $task;
    }
    
    // API History Logging Methods
    public function logApiRequest($action, $method, $taskId = null, $taskContent = null, $taskStatus = null, $requestData = null, $responseStatus = 200) {
        // Initialize api_request_history table if it doesn't exist
        $initSql = "CREATE TABLE IF NOT EXISTS api_request_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            action VARCHAR(100) NOT NULL,
            method VARCHAR(10) NOT NULL,
            task_id INT NULL,
            task_content TEXT NULL,
            task_status VARCHAR(50) NULL,
            request_data JSON NULL,
            response_status INT NOT NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            created_at DATETIME NOT NULL,
            INDEX idx_action (action),
            INDEX idx_created (created_at),
            INDEX idx_task_id (task_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->conn->query($initSql);
        
        // Get client IP and user agent
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        // Convert request data to JSON
        $requestDataJson = $requestData ? json_encode($requestData) : null;
        
        $sql = "INSERT INTO api_request_history (action, method, task_id, task_content, task_status, request_data, response_status, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            // Fail silently if logging fails - don't break the main functionality
            return false;
        }
        
        $stmt->bind_param("ssisssiss", $action, $method, $taskId, $taskContent, $taskStatus, $requestDataJson, $responseStatus, $ipAddress, $userAgent);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    public function getApiHistory() {
        $sql = "SELECT * FROM api_request_history ORDER BY created_at DESC LIMIT 1000";
        $result = $this->conn->query($sql);
        
        if (!$result) {
            return [];
        }
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        
        return $history;
    }
    
    public function clearApiHistory() {
        $sql = "TRUNCATE TABLE api_request_history";
        return $this->conn->query($sql);
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

try {
    $db = new TaskDatabase();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database initialization failed',
        'message' => $e->getMessage(),
        'hint' => 'Make sure MySQL is running and database exists'
    ]);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($action) {
        case 'get_tasks':
            // Don't log get_tasks - only log actual task modifications (create, update, delete)
            echo json_encode($db->getAllTasks());
            break;
            
        case 'create_task':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['content']) || !isset($data['status'])) {
                http_response_code(400);
                $db->logApiRequest('create_task', $_SERVER['REQUEST_METHOD'], null, null, null, $data, 400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }
            $result = $db->createTask($data['content'], $data['status']);
            $db->logApiRequest('create_task', $_SERVER['REQUEST_METHOD'], $result['id'], $data['content'], $data['status'], $data, 200);
            echo json_encode($result);
            break;
            
        case 'update_task':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['id']) || !isset($data['content']) || !isset($data['status'])) {
                http_response_code(400);
                $db->logApiRequest('update_task', $_SERVER['REQUEST_METHOD'], null, null, null, $data, 400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }
            $result = $db->updateTask($data['id'], $data['content'], $data['status']);
            $db->logApiRequest('update_task', $_SERVER['REQUEST_METHOD'], $data['id'], $data['content'], $data['status'], $data, 200);
            echo json_encode($result);
            break;
            
        case 'update_status':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['id']) || !isset($data['status'])) {
                http_response_code(400);
                $db->logApiRequest('update_status', $_SERVER['REQUEST_METHOD'], null, null, null, $data, 400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }
            $result = $db->updateTaskStatus($data['id'], $data['status']);
            $db->logApiRequest('update_status', $_SERVER['REQUEST_METHOD'], $data['id'], null, $data['status'], $data, 200);
            echo json_encode($result);
            break;
            
        case 'delete_task':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['id'])) {
                http_response_code(400);
                $db->logApiRequest('delete_task', $_SERVER['REQUEST_METHOD'], null, null, null, $data, 400);
                echo json_encode(['error' => 'Missing task ID']);
                exit;
            }
            $result = $db->deleteTask($data['id']);
            $db->logApiRequest('delete_task', $_SERVER['REQUEST_METHOD'], $data['id'], null, null, $data, 200);
            echo json_encode($result);
            break;
        
        case 'get_api_history':
            $result = $db->getApiHistory();
            echo json_encode($result);
            break;
        
        case 'clear_api_history':
            $result = $db->clearApiHistory();
            echo json_encode(['success' => true, 'message' => 'API history cleared']);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Invalid API endpoint']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
