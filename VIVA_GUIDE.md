# Bazaar - Technical Documentation & Viva Guide

## 1. Project Overview
This project is a comprehensive **Bazaar** built as a full-stack web application. It features a modern user interface, real-time communication, and a robust administrative backend. The primary goal is to provide a platform for users to buy and sell items within categories.

## 2. Technology Stack
- **Frontend:** HTML5, CSS3 (Custom Design System), JavaScript (ES6+), AJAX.
- **Backend:** PHP (PDO for database interactions).
- **Database:** MySQL.
- **Authentication:** Custom Session-based Auth + Google OAuth 2.0.
- **Email System:** PHPMailer (SMTP) for OTP and Password Resets.
- **Tools:** Composer (for dependency management), XAMPP (Local Environment).

---

## 3. Folder & File Structure

### Root Directory
- `index.php`: The homepage featuring categories and featured ads.
- `ad.php`: Detailed view for a single advertisement.
- `chat.php`: The real-time messaging interface.
- `post-ad.php`: Form for users to create new listings.
- `profile.php`: User dashboard to manage their ads and personal info.
- `login.php` / `signup.php`: Authentication entry points.

### Subdirectories
- `/admin`: Dashboard for administrators to manage users, ads, and categories.
- `/api`: Contains the "Engine" of the app.
    - `auth.php`: Registration, Login, Google OAuth, OTP logic.
    - `ads.php`: CRUD operations for advertisements.
    - `messages.php`: Chat logic, typing indicators, and file attachments.
- `/includes`: Reusable components.
    - `config.php`: Database connection, global constants, and helper functions.
    - `header.php` / `footer.php`: Global UI layout components.
- `/database`: SQL scripts for schema and dummy data.
- `/uploads`: Storage for user avatars, ad images, and chat attachments.

---

## 4. Database Architecture
The database consists of several interconnected tables:
1. **`users`**: Manages account details and verification status.
2. **`categories`**: Stores listing categories (Mobiles, Vehicles, etc.).
3. **`ads`**: Stores the main listing data (title, price, description, location).
4. **`ad_images`**: Allows multiple images per advertisement (One-to-Many relationship).
5. **`messages`**: Stores conversation history, including text, images, and audio notes.
6. **`favorites`**: Tracks users' bookmarked ads.
7. **`chat_typing_status`**: Real-time tracking of user activity in chat.

---

## 5. Core Functionality & Logic

### Authentication Flow
1. User signs up -> Password hashed using `BCRYPT`.
2. System generates a 6-digit OTP -> Saved to DB -> Sent via Email.
3. User verifies OTP -> Account activated -> Session created.

### Real-Time Chat
- Uses **AJAX Long Polling**.
- The client-side JS requests `api/messages.php?action=poll` every few seconds.
- Supports **Voice Notes** (blob uploads) and **Image Sharing**.
- Includes **Typing Indicators** to enhance UX.

### Ad Management
- Users can post ads with multiple images.
- Images are processed, renamed to prevent collisions, and saved in `/assets/uploads/`.
- Sellers can mark ads as "Sold" or "Active" to manage visibility.

---

## 6. Security Features
- **SQL Injection Prevention:** All queries use PDO Prepared Statements.
- **XSS Protection:** Input sanitization using `htmlspecialchars()` and `strip_tags()`.
- **Password Security:** Use of `password_hash()` and `password_verify()`.
- **CSRF Awareness:** Session-based validation for critical actions.
- **Authentication Guard:** Sensitive pages check for `$_SESSION['user_id']` before rendering.

---

## 7. How to Run (Viva Demo)
1. Open **XAMPP Control Panel** and start Apache & MySQL.
2. Import `database/schema.sql` into a database named `bazaar`.
3. Configure `includes/config.php` with your database credentials.
4. Access the project via `http://localhost/bazaar/`.
5. Login with a test user or sign up to experience the OTP flow.

---
*Created for the Viva Voce Examination - 2026*
