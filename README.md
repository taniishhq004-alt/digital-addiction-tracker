# 📱 Digital Addiction Tracking Database

---

## 📖 About the Project

The **Digital Addiction Tracking Database** is a web-based application that helps users record, monitor, and analyze the time they spend on digital devices across **10 different categories**. It uses a relational MySQL database with PHP backend and HTML/CSS frontend, running on a local XAMPP server.

The project demonstrates core **DBMS concepts** including:
- Relational schema design with foreign keys
- SQL operations: `INSERT`, `SELECT`, `UPDATE`, `DELETE`
- Aggregate functions: `SUM()`, `ROUND()`, `COUNT()`
- Clauses: `JOIN`, `GROUP BY`, `HAVING`, `WHERE`, `ORDER BY`
- Date functions: `CURDATE()`, `INTERVAL`

---

## ✨ Features

- 📊 **Live Dashboard** — Real-time stats fetched via SQL queries
- ➕ **Manual Screen Time Entry** — Add hours spent per category per day
- 📱 **10 Usage Categories** — Social Media, Gaming, Video/OTT, Work/Study, and more
- 🚫 **Daily Limits** — Set per-user, per-category limits stored in DB
- ⚠️ **Limit Alerts** — Red/yellow alerts using SQL `HAVING` clause
- 📋 **Filterable Records** — Filter all DB rows by user, category, or date
- 📄 **PDF Report** — Printable report with category breakdown and violations
- 🗄️ **SQL Demo Page** — 6 live queries running against the database 
- 🎴 **CardSwap Animation** — GSAP 3D animated card stack on the dashboard
- 👤 **Multi-user Support** — Each user has their own limits and usage history

---

## 🗄️ Database Schema

```
digital_addiction_db
├── users           → user_id (PK), full_name, age, age_group, occupation
├── categories      → cat_id (PK), cat_name, icon
├── usage_records   → record_id (PK), user_id (FK), cat_id (FK), app_name, usage_date, hours_used, note
└── usage_limits    → limit_id (PK), user_id (FK), cat_id (FK), daily_limit
```

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Database | MySQL 8.0 |
| Backend | PHP 8.2 |
| Frontend | HTML5, CSS3, JavaScript |
| Server | Apache via XAMPP |
| Animation | GSAP (GreenSock) |
| DB Tool | phpMyAdmin |
| Version Control | Git + GitHub |

---

## 📁 Project Structure

```
digital_addiction/
├── index.php           ← Dashboard (homepage)
├── add_usage.php       ← Add screen time manually
├── users.php           ← Manage users
├── limits.php          ← Set daily limits
├── records.php         ← View and filter all records
├── report.php          ← Generate PDF report
├── sql_demo.php        ← Live SQL query demo page
├── database.sql        ← Import this in phpMyAdmin
│
├── includes/
│   ├── db.php          ← Database connection
│   └── sidebar.php     ← Navigation sidebar
│
├── css/
│   └── style.css       ← Main stylesheet (dark theme)
│
└── components/
    └── CardSwap.css    ← GSAP card animation styles
```

---

## 🚀 How to Run Locally

### Step 1 — Install XAMPP
Download from [https://www.apachefriends.org/](https://www.apachefriends.org/) and install.  
Start **Apache** and **MySQL** from the XAMPP Control Panel.

### Step 2 — Clone this repository
```bash
git clone https://github.com/YOUR_USERNAME/digital-addiction-tracker.git
```
Move the cloned folder to:
```
C:\xampp\htdocs\digital_addiction\
```

### Step 3 — Import the Database
1. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click **New** → Name it `digital_addiction_db` → Click **Create**
3. Click **Import** → Choose `database.sql` → Click **Go**

### Step 4 — Open in Browser
```
http://localhost/digital_addiction/index.php
```

---

| Dashboard | Add Usage | SQL Demo |
|-----------|-----------|----------|
| ![dashboard](screenshots/dashboard.png) | ![add](screenshots/add_usage.png) | ![sql](screenshots/sql_demo.png) |

---

## 🗃️ Key SQL Queries Used

**Total screen time per user:**
```sql
SELECT u.full_name, ROUND(SUM(r.hours_used), 2) AS total_hours
FROM users u
JOIN usage_records r ON u.user_id = r.user_id
GROUP BY u.user_id
ORDER BY total_hours DESC;
```

**Detect daily limit violations:**
```sql
SELECT u.full_name, c.cat_name, ROUND(SUM(r.hours_used), 1) AS used_today
FROM usage_records r
JOIN users u ON r.user_id = u.user_id
JOIN categories c ON r.cat_id = c.cat_id
JOIN usage_limits l ON r.user_id = l.user_id AND r.cat_id = l.cat_id
WHERE r.usage_date = CURDATE() AND l.daily_limit > 0
GROUP BY u.user_id, c.cat_id, l.daily_limit
HAVING ROUND(SUM(r.hours_used), 1) >= l.daily_limit;
```

---

## 📄 License

This project is made for academic purposes only.  
© 2025 Tanishq Gupta, Rajat Singh, Shrestha Tiwari, Abhinav Rathore — JIIT Noida
