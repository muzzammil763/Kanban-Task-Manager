# Task Manager

A Kanban Style Task Manager With PHP/MySQL Backend.

## URLs

| Page | URL |
|------|-----|
| Task Manager | http://localhost/Task-Manager/index.html |
| Database Viewer | http://localhost/Task-Manager/db.html |
| phpMyAdmin | http://localhost/phpmyadmin |

## Features

- **Kanban Board** - Drag & drop tasks between columns (To Do, In Progress, Testing, Completed)
- **Projects** - Organize tasks by projects with color-coded badges
- **SQL History** - View SQL queries executed for each action (stored in browser localStorage)
- **API History** - View all API request/response logs (stored in browser localStorage)

## Requirements

- XAMPP (Apache + MySQL)
- Database: `task_manager`

## Database Schema

### Tables

| Table | Description |
|-------|-------------|
| `projects` | Project groups for organizing tasks |
| `tasks` | Individual tasks linked to projects |

> **Note:** SQL and API history are now stored in browser localStorage - no database table needed!

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

-- Insert default project
INSERT INTO projects (name, description, color, created_at, updated_at) 
VALUES ('Default Project', 'Default project for tasks', '#10b981', NOW(), NOW());
```

---

## Quick Start with Sample Data

Run this to create the database with 2 sample Flutter app projects and 5 tasks each:

```sql
-- =============================================
-- COMPLETE SETUP WITH SAMPLE DATA
-- =============================================

CREATE DATABASE IF NOT EXISTS task_manager;
USE task_manager;

-- Drop existing tables if any
DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS api_request_history;

-- Create Projects table
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#10b981',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Tasks table
CREATE TABLE tasks (
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

-- Create 2 Sample Projects
INSERT INTO projects (name, description, color, created_at, updated_at) VALUES
('Salonary', 'Flutter mobile app for salon booking', '#10b981', NOW(), NOW()),
('XTREM', 'Flutter mobile app - XTREM platform', '#8b5cf6', NOW(), NOW());

-- Create 5 Tasks for Salonary (project_id = 1)
INSERT INTO tasks (project_id, content, status, created_at, updated_at) VALUES
(1, 'Add Google Sign-In authentication', 'todo', NOW(), NOW()),
(1, 'Add Apple Sign-In authentication', 'in-progress', NOW(), NOW()),
(1, 'Build CRM dashboard for salon owners', 'in-progress', NOW(), NOW()),
(1, 'UI responsiveness testing on various screen sizes', 'testing', NOW(), NOW()),
(1, 'Setup Firebase project and configuration', 'completed', NOW(), NOW());

-- Create 5 Tasks for XTREM (project_id = 2)
INSERT INTO tasks (project_id, content, status, created_at, updated_at) VALUES
(2, 'Add Google Sign-In authentication', 'todo', NOW(), NOW()),
(2, 'Implement dark mode / light mode toggle', 'todo', NOW(), NOW()),
(2, 'Build CRM analytics dashboard', 'in-progress', NOW(), NOW()),
(2, 'Performance testing and optimization', 'testing', NOW(), NOW()),
(2, 'Implement state management (Riverpod/BLoC)', 'completed', NOW(), NOW());
```

---

## Reset Database (Fresh Start)

Run this to clear all data and start fresh with sample projects:

```sql
DELETE FROM tasks;
DELETE FROM projects;
ALTER TABLE projects AUTO_INCREMENT = 1;
ALTER TABLE tasks AUTO_INCREMENT = 1;

-- Re-insert sample projects
INSERT INTO projects (name, description, color, created_at, updated_at) VALUES
('Salonary', 'Flutter mobile app for salon booking', '#10b981', NOW(), NOW()),
('XTREM', 'Flutter mobile app - XTREM platform', '#8b5cf6', NOW(), NOW());

-- Re-insert sample tasks
INSERT INTO tasks (project_id, content, status, created_at, updated_at) VALUES
(1, 'Add Google Sign-In authentication', 'todo', NOW(), NOW()),
(1, 'Add Apple Sign-In authentication', 'in-progress', NOW(), NOW()),
(1, 'Build CRM dashboard for salon owners', 'in-progress', NOW(), NOW()),
(1, 'UI responsiveness testing', 'testing', NOW(), NOW()),
(1, 'Setup Firebase configuration', 'completed', NOW(), NOW()),
(2, 'Add Google Sign-In authentication', 'todo', NOW(), NOW()),
(2, 'Implement dark/light mode toggle', 'todo', NOW(), NOW()),
(2, 'Build CRM analytics dashboard', 'in-progress', NOW(), NOW()),
(2, 'Performance testing', 'testing', NOW(), NOW()),
(2, 'Setup state management', 'completed', NOW(), NOW());
```

---

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

---

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
WHERE p.name = 'Salonary';
```

---

## Files

| File | Description |
|------|-------------|
| `index.html` | Task Manager UI with Kanban board |
| `db.html` | Real-time Database Viewer |
| `api.php` | Backend REST API |

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

### Step 4: Create Database with Sample Data

1. Open browser and go to: http://localhost/phpmyadmin
2. Click **"SQL"** tab at the top
3. Copy and paste the SQL from **Quick Start with Sample Data** section above
4. Click **"Go"** to execute

### Step 5: Open the App

Open your browser and go to:
- Task Manager: http://localhost/Task-Manager/index.html

Done! You'll have 2 projects (Salonary & XTREM) with 5 tasks each ready to use.