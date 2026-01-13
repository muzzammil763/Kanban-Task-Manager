# Task Manager

A Kanban-style task manager with PHP/MySQL backend.

## URLs

| Page | URL |
|------|-----|
| Task Manager | http://localhost/Task-Manager/index.html |
| Database Viewer | http://localhost/Task-Manager/db.html |
| API History | http://localhost/Task-Manager/apihistory.html |
| phpMyAdmin | http://localhost/phpmyadmin |

## Requirements

- XAMPP (Apache + MySQL)
- Database: `task_manager`

## Database Setup

Run in phpMyAdmin SQL tab:

```sql
CREATE DATABASE IF NOT EXISTS task_manager;
USE task_manager;

CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS api_request_history (
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
    created_at DATETIME NOT NULL
);
```

## Files

- `index.html` - Task Manager UI
- `db.html` - Database Viewer
- `apihistory.html` - API Request History
- `api.php` - Backend API
