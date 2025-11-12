# 🧾 Gov-Doc-Attestation-Portal
> A secure and efficient web-based platform for government document attestation and verification.

This project provides an online solution for managing the document attestation process — enabling **clients** to upload documents, **workers** to process attestation requests, and **admins** to manage the entire workflow.  
Built with **PHP, HTML, CSS, JavaScript**, and **MySQL (XAMPP)**.

---

## 🚀 Features

### 👤 Clients
- Register and log in securely.
- Upload educational or official documents for attestation.
- Track job progress in real time.
- Download verified/attested documents upon completion.

### 👷 Workers
- View and accept pending attestation jobs.
- Process and update job statuses.
- Upload verified documents for admin approval.

### 🛠️ Admin
- Manage clients and workers.
- Approve or reject attested documents.
- Monitor platform activity, jobs, and payments.

---

## 🧩 Tech Stack

| Component | Technology |
|------------|-------------|
| **Frontend** | HTML, CSS, JavaScript |
| **Backend** | PHP (Procedural) |
| **Database** | MySQL (via XAMPP) |
| **Server** | Apache |
| **Storage** | Local uploads directory |
| **Version Control** | Git & GitHub |

---

## 🏗️ Folder Structure

attestation_app/
├── admin/ # Admin dashboard files
├── assets/ # CSS, JS, and images
├── auth/ # Authentication (login, register)
├── config/ # Database connection and constants
├── dashboards/ # Dashboards for client/worker/admin
├── functions/ # Reusable functions
├── includes/ # Headers, footers, partials
├── notifications/ # Notifications and alerts
├── uploads/ # Uploaded user documents
├── about.php # About page
├── index.php # Homepage
└── attestation_app.sql # Database schema

markdown
Copy code

---

## ⚙️ Installation Guide

1. **Install XAMPP**
   - Download from [Apache Friends](https://www.apachefriends.org/index.html)
   - Start **Apache** and **MySQL**

2. **Clone or Download this Repository**
   ```bash
   git clone https://github.com/Hassan-xl/Gov-Doc-Attestation-Portal.git
Move Project Folder
Place it inside your XAMPP directory:

makefile
Copy code
C:\xampp\htdocs\
Set Up Database

Open phpMyAdmin

Create a new database (e.g., attestation_app)

Import the file: attestation_app.sql

Run the Project
Visit in your browser:

arduino
Copy code
http://localhost/Gov-Doc-Attestation-Portal/
🧠 Future Enhancements
Email notifications for job updates.

Payment gateway integration (Stripe / EasyPaisa / PayPal).

Document verification API.

Role-based permission system.

Improved UI/UX with responsive design.

👨‍💻 Author
Hassan
📧 [workxl5801@gmail.com]
🌐 https://github.com/Hassan-xl

🪪 License
This project is open-source and available under the MIT License.
