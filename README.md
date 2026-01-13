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
3. Paste and run this SQL:

```sql
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    INDEX idx_created (created_at),
    INDEX idx_task_id (task_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

4. Click **"Go"** to execute

### Step 6: Open the App

Open your browser and go to:
- Task Manager: http://localhost/Task-Manager/index.html

Done! You can now create, edit, and manage tasks.
