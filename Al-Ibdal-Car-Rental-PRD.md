# Product Requirements Document (PRD)
## Project: Al Ibdal Car Rental Management Platform

### Overview
Develop a comprehensive web and mobile application for "Al Ibdal" to manage car rental operations across Oman. The platform will streamline service requests, car inventory, branch distribution, notifications, rental tracking, invoicing, contract generation, and subcontractor management.

---

### 1. Service Request (SR) Management
- Users can submit rental requests via web/mobile.
- Each SR includes:
  - Customer Name
  - Customer Phone
  - Customer Location/City (within Oman)
  - Rental Period (start/end dates)
- SRs are stored and tracked in the system.

### 2. Car Inventory Management
- Admins can add, edit, delete cars.
- Track car status (available, rented, maintenance).
- Assign cars to branches.
- View car details (make, model, year, registration, etc.).

### 3. Branch Distribution
- System supports multiple branches:
  - Muscat (2 branches)
  - Sohar (1 branch)
  - Salalah (1 branch)
- Rental orders can be distributed to branches based on location and availability.

### 4. Partner Assignment & Notifications
- Registered partners can assign SRs to themselves or other staff.
- Upon assignment:
  - Assigned partner receives confirmation email.
  - HQ office receives notification email of new request.

### 5. Automatic Branch Assignment & Notifications
- System automatically assigns SR to nearest branch based on customer location.
- Assigned branch receives notification.
- Tenant (customer) receives email/SMS/WhatsApp notification with branch contact details and pickup instructions.

### 6. Rental Tracking
- Track status of each rental order (requested, assigned, picked up, returned, completed).
- Timeline/history for each rental.

### 7. Invoicing
- Generate invoices for each rental order.
- View/download invoices (PDF).
- Send invoices via email to customers.

### 8. Temporary Contract Generation
- Automatically generate rental contract with customer details.
- Contract available for download and email.

### 9. Subcontractor Management
- Add/manage subcontractors.
- Assign overflow SRs to subcontractors if demand exceeds capacity.
- Track subcontractor performance and status.

---

### Technical Stack
- **Backend:** Laravel (PHP)
- **Web Frontend:** Vue.js
- **Mobile App:** Flutter
- **Notifications:** Email, SMS, WhatsApp integration
- **Database:** MySQL/PostgreSQL

---

### User Roles
- Admin (HQ)
- Branch Manager
- Partner/Staff
- Customer
- Subcontractor

---

### Key Integrations
- Email service (SMTP, SendGrid, etc.)
- SMS gateway (Twilio, local provider)
- WhatsApp API
- Maps/Location API for branch assignment

---

### Non-Functional Requirements
- Responsive UI (web/mobile)
- Secure authentication (JWT/OAuth)
- Multi-language support (English/Arabic)
- Audit logs for all actions

---

### Github repository update
echo "# al-ibdal-car-rental" >> README.md
git init
git add README.md
git commit -m "first commit"
git branch -M main
git remote add origin https://github.com/kims480/al-ibdal-car-rental.git
git push -u origin main
### Github repository push an existing repository from the command line
git remote add origin https://github.com/kims480/al-ibdal-car-rental.git
git branch -M main
git push -u origin main