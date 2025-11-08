# 🧭 HRMS – Human Resource Management System (Laravel)

A modern HR management system built with **Laravel** for small and medium-sized businesses.  
The system helps organizations manage employees, attendance, payroll, leave requests, and more — all in one platform.

---

## 🚀 Features
- 👨‍💼 Employee management (add, edit, deactivate staff)
- 🗓 Leave management (request, approve, track)
- 💰 Payroll processing and payslip generation
- 🕒 Attendance and check-in/out tracking
- 🗃 Department and role management
- 🔐 Role-based access control (Admin, HR, Employee)
- 📁 Document uploads and storage
- 📨 Notifications and announcements
- ⚙️ Built with Laravel 11 + Filament + MySQL

---

## 🧰 Tech Stack
| Layer | Technology |
|-------|-------------|
| Backend | Laravel 11 (PHP 8.2+) |
| Frontend | Blade / Livewire / Tailwind |
| Database | MySQL 
| Caching | Redis |
| Deployment | Laravel Cloud |
| Version Control | GitHub |

---

## ⚙️ Installation (Local Development)
```bash
git clone https://github.com/yourusername/hrms.git
cd hrms
composer install
cp .env.example .env
php artisan key:generate
