# 🚑 Nearby Ambulance Locator

A full-stack emergency ambulance management system built with **PHP, MySQL, Bootstrap 5, JavaScript, AJAX**, and PHP REST APIs for Flutter integration.

---

## 📋 Project Overview

This system allows users to:
- Register and log in securely
- Browse nearby available ambulances
- Send emergency ambulance requests
- Track request history

Admins can:
- Manage ambulances (Add / Edit / Delete)
- View and update emergency requests
- Manage registered users
- View statistics and charts on the dashboard

---

## 🛠️ Technologies Used

| Layer      | Technology                          |
|------------|-------------------------------------|
| Frontend   | HTML5, CSS3, Bootstrap 5, Font Awesome |
| Backend    | Core PHP (no framework)             |
| Database   | MySQL (via MySQLi with prepared statements) |
| Server     | XAMPP (Apache + MySQL)              |
| Charts     | Chart.js                            |
| Maps       | Google Maps Embed API               |
| APIs       | PHP REST APIs (JSON) for Flutter    |

---

## 📁 Folder Structure

```
ambulance_locator/
├── admin/
│   ├── dashboard.php       ← Admin dashboard with charts
│   ├── ambulances.php      ← Manage ambulances (CRUD)
│   ├── requests.php        ← Manage emergency requests
│   ├── users.php           ← Manage registered users
│   ├── login.php           ← Admin login
│   └── logout.php
├── api/
│   ├── get_ambulances.php  ← GET: list ambulances (JSON)
│   ├── request_ambulance.php ← POST: create request (JSON)
│   ├── login.php           ← POST: user login (JSON)
│   └── register.php        ← POST: user register (JSON)
├── assets/
│   ├── css/style.css       ← All custom styles
│   ├── js/script.js        ← All custom JS
│   └── images/
├── config/
│   └── database.php        ← DB connection
├── includes/
│   ├── navbar.php          ← Public navbar
│   ├── footer.php          ← Public footer
│   └── sidebar.php         ← Admin sidebar
├── user/
│   ├── dashboard.php       ← User dashboard
│   ├── ambulances.php      ← Browse & request ambulances
│   ├── request.php         ← Request history
│   └── profile.php         ← Update profile & password
├── database/
│   └── ambulance_locator.sql ← Full DB schema + seed data
├── index.php               ← Landing page
├── login.php               ← User login
├── register.php            ← User registration
├── logout.php
└── README.md
```

---

## ⚙️ Installation Steps

### Step 1 – Install XAMPP
Download and install XAMPP from https://www.apachefriends.org/

### Step 2 – Copy Project Files
Copy the `ambulance_locator` folder to:
```
C:\xampp\htdocs\ambulance_locator
```

### Step 3 – Start XAMPP Services
Open XAMPP Control Panel and start:
- **Apache**
- **MySQL**

### Step 4 – Import Database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click **"New"** to create a database named `ambulance_locator`
3. Select the database → click **"Import"** tab
4. Choose the file: `database/ambulance_locator.sql`
5. Click **"Go"**

> The SQL file creates all tables and inserts sample data automatically.

### Step 5 – Configure Database (if needed)
Open `config/database.php` and update credentials if your MySQL has a password:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');   // ← add your password here if set
define('DB_NAME', 'ambulance_locator');
```

### Step 6 – Open the Project
Visit: `http://localhost/ambulance_locator/`

---

## 🔐 Default Login Credentials

### Admin Panel
| Field    | Value      |
|----------|------------|
| URL      | `http://localhost/ambulance_locator/admin/login.php` |
| Username | `admin`    |
| Password | `admin123` |

### User Panel (Demo Accounts)
| Email                  | Password |
|------------------------|----------|
| rahul@example.com      | user123  |
| priya@example.com      | user123  |
| amit@example.com       | user123  |

---

## 📡 Flutter API Endpoints

Base URL: `http://YOUR_IP/ambulance_locator/api/`

| Endpoint               | Method | Description              |
|------------------------|--------|--------------------------|
| `register.php`         | POST   | Register new user        |
| `login.php`            | POST   | Login user               |
| `get_ambulances.php`   | GET    | Get ambulance list (JSON)|
| `request_ambulance.php`| POST   | Submit emergency request |

### Example: Get Ambulances
```
GET http://localhost/ambulance_locator/api/get_ambulances.php?status=available
```
Response:
```json
{
  "success": true,
  "count": 7,
  "ambulances": [
    {
      "id": 1,
      "driver_name": "Rajan Mehta",
      "phone": "9001122334",
      "vehicle_no": "MH-01-AB-1234",
      "area": "Andheri West, Mumbai",
      "latitude": 19.136,
      "longitude": 72.826,
      "status": "available"
    }
  ]
}
```

### Example: Request Ambulance
```
POST http://localhost/ambulance_locator/api/request_ambulance.php
Body: { "user_id": 1, "ambulance_id": 1, "location": "Andheri Station" }
```

---

## 🎨 Color Palette

| Color   | Hex       | Usage                  |
|---------|-----------|------------------------|
| Red     | `#ff3b3b` | Primary / Emergency    |
| Navy    | `#0f172a` | Background / Sidebar   |
| White   | `#ffffff` | Cards / Content        |
| Light   | `#f1f5f9` | Page background        |
| Blue    | `#2563eb` | Secondary / Admin      |

---

## 🔒 Security Features

- Passwords hashed with `password_hash()` (bcrypt)
- All DB queries use **prepared statements** (SQL injection prevention)
- Session-based authentication for users and admins
- Input sanitization with `htmlspecialchars()` and `trim()`
- CORS headers on API endpoints for Flutter

---

## 📊 Database Tables

| Table       | Description                        |
|-------------|------------------------------------|
| `users`     | Registered users                   |
| `admins`    | Admin accounts                     |
| `ambulances`| Ambulance fleet with GPS coords    |
| `requests`  | Emergency requests with status     |

---

## 🚀 Features Summary

- ✅ Modern landing page with hero, features, stats, testimonials
- ✅ User registration & login with validation
- ✅ Admin panel with sidebar, charts (Chart.js), tables
- ✅ Full ambulance CRUD (Add/Edit/Delete)
- ✅ Emergency request management with status updates
- ✅ User profile update & password change
- ✅ REST APIs for Flutter integration
- ✅ Responsive design (mobile-friendly)
- ✅ Google Maps embed
- ✅ Geolocation detection
- ✅ Toast notifications & loading animations

---

## 👨‍💻 Built For

College Mini Project – Full Stack Web Development  
**Technologies:** PHP · MySQL · Bootstrap 5 · JavaScript · AJAX · Chart.js
