# Digital Addiction Tracking Database
## DBMS Minor Project — BCA 2nd Semester
### Jaypee Institute of Information Technology | Session 2025-26

**Team Members:**
- Tanishq Gupta (992505170075) — BCA3
- Rajat Singh (992505170083) — BCA3
- Shrestha Tiwari (992505170062) — BCA3
- Abhinav Rathore (992505170061) — BCA3

---

## ✅ HOW TO RUN THIS PROJECT (Step by Step)

### STEP 1 — Install XAMPP
Download and install XAMPP from: https://www.apachefriends.org/
- Start **Apache** ✅
- Start **MySQL** ✅

---

### STEP 2 — Copy Project Files
Copy the entire `digital_addiction` folder to:
```
C:\xampp\htdocs\digital_addiction\
```

---

### STEP 3 — Create the Database
1. Open your browser and go to: http://localhost/phpmyadmin
2. Click **"New"** to create a new database
3. Name it: `digital_addiction_db` → Click **Create**
4. Click on your new database → click **"Import"** tab
5. Click **"Choose File"** → select `database.sql` → Click **Go**
6. ✅ All tables and sample data will be created!

---

### STEP 4 — Run the Project
Open your browser and go to:
```
http://localhost/digital_addiction/index.php
```

---

## 📁 FILE STRUCTURE
```
digital_addiction/
├── index.php          ← Dashboard (Homepage)
├── add_usage.php      ← Add screen time manually
├── users.php          ← Manage users
├── limits.php         ← Set daily limits
├── records.php        ← View all database records
├── report.php         ← Generate PDF report
├── sql_demo.php       ← Live SQL query demo (for teacher!)
├── database.sql       ← SQL file to import in phpMyAdmin
│
├── includes/
│   ├── db.php         ← Database connection
│   └── sidebar.php    ← Navigation sidebar
│
├── css/
│   └── style.css      ← Main stylesheet
│
└── components/
    └── CardSwap.css   ← CardSwap animation styles
```

---

## 🗄️ DATABASE TABLES

| Table | Purpose |
|-------|---------|
| `users` | Stores user info (name, age, occupation) |
| `categories` | 10 app categories (Social, Gaming, etc.) |
| `usage_records` | Daily screen time entries |
| `usage_limits` | Per-user daily limits per category |

---

## 🔥 FEATURES
- ✅ 10 app categories with icons
- ✅ Manually add screen time
- ✅ Set daily limits per user per category
- ✅ Dashboard with live stats from SQL
- ✅ Filter records by user / category / date
- ✅ Generate printable PDF report
- ✅ SQL Queries demo page (for viva)
- ✅ CardSwap animated cards on dashboard
- ✅ Pre-loaded sample data (7 days)
