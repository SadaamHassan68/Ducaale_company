# ✈️ Ducaale Airline Ticket & Flight Booking System

A robust, enterprise-grade, object-oriented **PHP & MySQL** web application for real-time flight scheduling, dynamic seat selection, ticket booking, and boarding pass management. Features a highly refined administrative panel, automated activity logging, print-optimized boarding passes, dynamic QR code generation, and automated email confirmation workflows.

---

## 🚀 Key Features

### 🧑‍✈️ Passenger Portal
- **Interactive Flight Search:** Advanced filtering by origin, destination, date, price, and seat class.
- **Dynamic Seat Selection:** Visually select seats with real-time availability updates (Economy, Business, and First Class).
- **Profile & Booking Management:** View transaction history, edit passenger details, track booking statuses, and cancel reservations.
- **Instant Boarding Passes:** High-fidelity, print-optimized ticket views complete with dynamic QR codes.

### 💼 Admin & Staff Portal
- **Interactive Dashboard:** Complete oversight of total revenue, booking counts, active flights, and passenger demographics.
- **Flight & Class Management:** Complete CRUD operations for flight scheduling, status tracking (`Scheduled`, `Delayed`, `Boarding`, `Departed`, `Cancelled`), and aircraft configuration.
- **Seat & Pricing Control:** Update seat structures and real-time class pricing models dynamically.
- **Flight Manifests:** Generate and export real-time passenger flight manifest lists.
- **Support & Feedback Desk:** Handle inquiries and customer interactions via an integrated support module.
- **Security Audit Logs:** Secure system track of admin actions, login attempts, and operations.

---

## 🛠️ Technology Stack

- **Backend:** PHP 8.x (Object-Oriented Architecture, PDO prepared statements, transactional database operations).
- **Database:** MySQL / MariaDB (relational schema, row-level locking for seat reservation concurrency).
- **Frontend:** HTML5, Vanilla CSS3 (custom layouts, responsive grid/flexbox, native dark/light theme options, and custom print-media styling).
- **Integrations:** PHPMailer (SMTP configuration for automatic transactional emails), QR Code generator APIs.

---

## 📂 Project Directory Structure

```text
Booking/
├── admin/                     # Administrative control panel pages
│   ├── manage_bookings.php    # Manage/Cancel bookings & issue tickets
│   ├── manage_flights.php     # CRUD operations for flight schedules
│   ├── manage_staff.php       # Admin & staff user management
│   ├── seat_pricing.php       # Dynamic seat class price adjustments
│   ├── manifest.php           # Real-time passenger boarding manifests
│   ├── activity_logs.php      # Administrative security audit trail
│   ├── support.php            # Helpdesk support center
│   └── dashboard.php          # Overview metrics, graphs, and system telemetry
├── assets/                    # Static assets
│   ├── css/                   # Core CSS files (admin-premium.css, print.css, etc.)
│   └── js/                    # Core JS scripts for real-time validation & UI reactivity
├── classes/                   # Object-oriented core logic classes
│   ├── User.php               # Handles authentication, roles, registration, & profiles
│   ├── Flight.php             # Handles flight queries, searches, and seat check locks
│   └── Booking.php            # Manages database transactions, booking state, & payments
├── config/                    # Configuration files
│   └── db.php                 # Hybrid local/production database connection (PDO)
├── includes/                  # Global reusable UI templates & helper functions
│   ├── header.php             # Site header & navigation bar
│   ├── footer.php             # Premium multi-column footer
│   ├── admin_sidebar.php      # Unified admin/staff navigation panel
│   └── functions.php          # Global utility functions (URLs, currency, date formatting)
├── booking_process.php        # Transactional flight checkout and booking registration
├── bookings.php               # User booking history dashboard
├── cancel_booking.php         # Secure cancellation engine
├── print_ticket.php           # Print-optimized ticket delivery view
├── ticket.php                 # Interactive mobile-responsive boarding pass
├── database.sql               # Relational SQL schema definitions
└── index.php                  # Customer-facing homepage & flight search center
```

---

## 💾 Database Architecture

The application relies on 5 highly optimized relational tables designed with cascade constraint integrity:

| Table | Purpose | Primary Key | Foreign Keys / Key Columns |
| :--- | :--- | :--- | :--- |
| **`users`** | Holds credentials, metadata, and roles (`Passenger`, `Staff`, `Admin`). | `id` | `email` (Unique) |
| **`flights`** | Flight inventory, departures, arrivals, schedules, and base prices. | `id` | `flight_number` (Unique) |
| **`seats`** | Catalog of seats per flight, tracking class & status (`Available`, `Reserved`, `Booked`). | `id` | `flight_id` ➔ `flights(id)` |
| **`bookings`** | Booked flights, associated seats, final prices, references, and status. | `id` | `user_id` ➔ `users(id)`, `flight_id` ➔ `flights(id)`, `seat_id` ➔ `seats(id)` |
| **`activity_logs`** | Security audit system logging admin changes. | `id` | `admin_id` ➔ `users(id)` |

---

## ⚙️ Installation & Setup

Follow these simple steps to run this application locally:

### Prerequisites
- **Local Server:** [XAMPP](https://www.apache.org/xampp/) (PHP 7.4+ or 8.x recommended).
- **Database:** MySQL / MariaDB server.
- **Git** (optional).

### Setup Instructions

1. **Clone the Repository:**
   Clone this repository to your local web server root (e.g., `C:\xampp\htdocs\Booking`):
   ```bash
   git clone <repository_url> Booking
   ```

2. **Configure the Database:**
   - Launch your MySQL Server (via XAMPP Panel).
   - Navigate to `http://localhost/phpmyadmin/`.
   - Create a new database named `flight_booking_system`.
   - Import the **`database.sql`** schema file directly into `flight_booking_system`.

3. **Verify Environment Configurations (`config/db.php`):**
   The application features a **hybrid connection engine** that auto-detects if it is running on a local host or live web-hosting (e.g., InfinityFree).
   ```php
   // Local Configuration automatically triggers on localhost:
   $host = '127.0.0.1';
   $db   = 'flight_booking_system';
   $user = 'root';
   $pass = '';
   ```
   No changes are required for local testing!

4. **Launch the Application:**
   Open your browser and navigate to:
   ```text
   http://localhost/Booking/
   ```

5. **Default Admin Login:**
   If you imported the database and ran `seed.php`, use the seeded admin account:
   - Email: `admin@example.com`
   - Password: `password123`

---

## 🔒 Security Practices Built-in
- **Prepared Statements (PDO):** Absolute protection against SQL Injection attacks.
- **Hash Protections:** Sensitive credentials stored using secure one-way encryption (`password_hash` with `PASSWORD_DEFAULT`).
- **Concurrent Seat Protection:** Utilizes SQL database transactions (`SELECT ... FOR UPDATE` and `beginTransaction`) to lock rows and guarantee seat booking collision prevention.
- **Role-Based Access Control:** Strict routing checks via `User::requireRole()` guarding sensitive administrative views.

---

## 🤝 Contributing
Contributions are highly welcome! Please fork the repository, make your adjustments, and submit a pull request.

---

## 📋 Common Use Cases

### For Passengers
1. **Register/Login** → Search flights by origin, destination, date
2. **Select & Book** → Choose preferred seats and complete payment
3. **View Bookings** → Access booking history and manage reservations
4. **Print Tickets** → Generate and print boarding passes with QR codes

### For Admins
1. **Dashboard Overview** → Monitor key metrics and system health
2. **Flight Management** → Create, update, or cancel flights
3. **Seat & Pricing** → Configure seat layouts and dynamic pricing
4. **Activity Audit** → Review security logs and admin actions
5. **Support** → Respond to customer inquiries and feedback

---

## 🐛 Troubleshooting

| Issue | Solution |
| :--- | :--- |
| **Database connection error** | Verify MySQL is running; check `config/db.php` credentials |
| **"Table doesn't exist"** | Re-import `database.sql` and ensure schema is complete |
| **Seats not appearing** | Check `seats` table population; run seed data scripts |
| **Email not sending** | Configure PHPMailer SMTP settings in `classes/Booking.php` |
| **QR code not generating** | Verify QR generator library is properly integrated |
| **Permission denied on uploads** | Ensure `uploads/` folder has write permissions (chmod 755) |

---

## 📧 Contact & Support
For questions, bug reports, or feature requests, please open an issue on the repository or contact the development team.

---

## 📄 License
This project is licensed under the MIT License - see LICENSE file for details.

---

## ✨ Acknowledgments
- Built with PHP 8.x Object-Oriented Architecture
- Database concurrency handled via SQL transactions
- UI enhanced with responsive CSS3 and Vanilla JavaScript
- Email functionality powered by PHPMailer

**Last Updated:** July 5, 2026
