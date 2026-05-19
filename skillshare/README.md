# ⚡ SkillBridge — Student Skill Sharing Platform
A full intermediate-level PHP + MySQL web application.

---

## 🚀 Setup Instructions

### Step 1 — Place in XAMPP
Copy the `skillshare/` folder to:
```
C:\xampp\htdocs\skillshare\
```

### Step 2 — Create Database
1. Open **phpMyAdmin** → http://localhost/phpmyadmin
2. Click **Import** (top menu)
3. Choose `database.sql` from this folder
4. Click **Go**

OR paste the contents of `database.sql` into the **SQL** tab and run it.

### Step 3 — Open in Browser
```
http://localhost/skillshare/
```

---

## 🔐 Demo Login Credentials

| Name         | Email                  | Password   | Role    |
|--------------|------------------------|------------|---------|
| Anika Islam  | anika@example.com      | password   | Student |
| Rafiq Hossain| rafiq@example.com      | password   | Student |
| Mitu Akter   | mitu@example.com       | password   | Student |
| Tanvir Ahmed | tanvir@example.com     | password   | Student |
| Nadia Rahman | nadia@example.com      | password   | Student |
| Admin        | admin@skillbridge.com  | password   | Admin   |

---

## 📁 Project Structure

```
skillshare/
├── config/
│   ├── config.php         → BASE_URL & SITE_NAME constants
│   ├── db.php             → PDO MySQL connection
│   └── functions.php      → Helper functions (auth, flash, sanitize, etc.)
│
├── includes/
│   ├── header.php         → Navbar, Bootstrap, CSS
│   └── footer.php         → Footer, JS scripts
│
├── assets/
│   ├── css/style.css      → Full custom stylesheet
│   └── js/main.js         → Notification JS
│
├── pages/
│   ├── auth/
│   │   ├── login.php      → Login form
│   │   ├── register.php   → Registration form
│   │   └── logout.php     → Session destroy
│   │
│   ├── skills/
│   │   ├── browse.php     → Browse + search + filter skills
│   │   ├── view.php       → Single skill detail + request + review
│   │   ├── create.php     → Post a new skill
│   │   ├── edit.php       → Edit own skill
│   │   └── delete.php     → Delete own skill
│   │
│   └── profile/
│       ├── view.php       → Public profile page
│       ├── edit_profile.php → Edit name/bio/password
│       ├── my_skills.php  → Manage own skills
│       ├── requests.php   → View/manage connection requests
│       ├── notifications.php → All notifications
│       └── mark_read.php  → AJAX: mark notifications read
│
├── database.sql           → Full DB schema + sample data
├── index.php              → Homepage
└── README.md              → This file
```

---

## ✨ Features

- 🔐 **Authentication** — Register, Login, Logout, Password Change
- 📚 **Skill Posts** — Offer or Request skills with categories & tags
- 🔍 **Browse & Search** — Filter by keyword, category, type, sort order
- 📬 **Connection Requests** — Express interest with a message, Accept/Decline
- ⭐ **Reviews & Ratings** — Leave ratings on skills, view avg ratings
- 👤 **Public Profiles** — View any student's profile, skills, and reviews
- 🔔 **Notifications** — Real-time badge count, mark as read
- 📊 **Dashboard** — Stats, latest skills, top-rated students

---

## 🛠️ Tech Stack

| Layer      | Technology              |
|------------|-------------------------|
| Backend    | PHP 8+ (pure, no framework) |
| Database   | MySQL (via PDO)         |
| Frontend   | Bootstrap 5.3 + Custom CSS |
| Fonts      | Google Fonts (Syne + DM Sans) |
| Icons      | Bootstrap Icons         |

---

## 🔧 Configuration

If your project is NOT at `http://localhost/skillshare/`, update `BASE_URL` in:
- `config/config.php`
- `index.php` (top line)
- All files inside `pages/auth/`, `pages/skills/`, `pages/profile/`

Change `define('BASE_URL', '/skillshare/');` to match your path.
