# Task Manager

A Kanban Style Task Manager With PHP/MySQL Backend.

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

## Database Schema

### Tables

| Table | Description |
|-------|-------------|
| `projects` | Project groups for organizing tasks |
| `tasks` | Individual tasks linked to projects |
| `api_request_history` | API request logs |

### Relationship

```
projects (1) ──────< tasks (many)
    │
    └── id ←──── project_id
```

## Database Setup

Run in phpMyAdmin SQL tab:

```sql
CREATE DATABASE IF NOT EXISTS task_manager;
USE task_manager;

-- Projects table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#10b981',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tasks table (with project_id foreign key)
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NULL,
    content TEXT NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_status (status),
    INDEX idx_project (project_id),
    INDEX idx_created (created_at),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API Request History table
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
    created_at DATETIME NOT NULL,
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default project
INSERT INTO projects (name, description, color, created_at, updated_at) 
VALUES ('Default Project', 'Default project for tasks', '#10b981', NOW(), NOW());
```

## If You Already Have Tasks (Migration)

Run these commands to add project support to existing tasks:

```sql
-- Step 1: Create projects table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#10b981',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 2: Insert default project
INSERT INTO projects (name, description, color, created_at, updated_at) 
VALUES ('Default Project', 'Default project for tasks', '#10b981', NOW(), NOW());

-- Step 3: Add project_id column to tasks
ALTER TABLE tasks 
ADD COLUMN project_id INT NULL AFTER id,
ADD INDEX idx_project (project_id),
ADD FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL;

-- Step 4: Assign all existing tasks to default project (id = 1)
UPDATE tasks SET project_id = 1 WHERE project_id IS NULL;
```

## JOIN Query Examples

### Get all tasks with project info
```sql
SELECT 
    p.name AS project_name,
    p.color AS project_color,
    t.id AS task_id,
    t.content,
    t.status,
    t.created_at
FROM projects p
INNER JOIN tasks t ON p.id = t.project_id
ORDER BY p.name, t.created_at DESC;
```

### Get task count per project
```sql
SELECT 
    p.name AS project,
    COUNT(t.id) AS total_tasks,
    SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) AS completed
FROM projects p
LEFT JOIN tasks t ON p.id = t.project_id
GROUP BY p.id;
```

### Get tasks for specific project
```sql
SELECT t.* FROM tasks t
INNER JOIN projects p ON t.project_id = p.id
WHERE p.name = 'Default Project';
```

## Files

- `index.html` - Task Manager UI
- `db.html` - Database Viewer
- `apihistory.html` - API Request History
- `api.php` - Backend API

---

## Setup Guide From Scratch

### Step 1: Install XAMPP

1. Download XAMPP from https://www.apachefriends.org/
2. Run the installer and follow the prompts
3. Install to default location (`/Applications/XAMPP` on Mac, `C:\xampp` on Windows)

### Step 2: Start XAMPP Servers

1. Open XAMPP Control Panel
2. Start **Apache** (click Start)
3. Start **MySQL** (click Start)
4. Both should show green/running status

### Step 3: Copy Project to htdocs

1. Navigate to XAMPP's htdocs folder:
   - Mac: `/Applications/XAMPP/xamppfiles/htdocs/`
   - Windows: `C:\xampp\htdocs\`
2. Create a folder named `Task-Manager`
3. Copy all project files into this folder

### Step 4: Create Database in phpMyAdmin

1. Open browser and go to: http://localhost/phpmyadmin
2. Click **"New"** in the left sidebar
3. Enter database name: `task_manager`
4. Click **"Create"**

### Step 5: Create Tables

1. Select `task_manager` database from left sidebar
2. Click **"SQL"** tab at the top
3. Copy and paste the SQL from **Database Setup** section above
4. Click **"Go"** to execute

### Step 6: Open the App

Open your browser and go to:
- Task Manager: http://localhost/Task-Manager/index.html

Done! You can now create, edit, and manage tasks.