# User Documentation

## 1. Brief System Overview

The application is a barbershop appointment booking system where customers can choose services, barbers, and available time slots online. The system manages bookings, email confirmations, appointment tracking, reviews, and provides dedicated interfaces for barbers and administrators.

The project’s goal is to model the operation of a real salon: customers can book quickly, barbers can manage their schedules, and admins can oversee business operations.

### Main Features:

* View services and barbers
* Book appointments as a guest or registered user
* Select multiple services within one booking
* Email booking confirmation
* View and cancel personal appointments
* Leave reviews after completed appointments
* Barber-side appointment and profile management
* Admin-side service, employee, schedule, and time-off management

## 2. System Access

In the local development environment, the main URLs are:

* Frontend: `http://frontend.vm1.test`
* Backend API: `http://backend.vm1.test/api`
* phpMyAdmin: `http://pma.vm1.test`
* Mailcatcher: `http://mailcatcher.vm1.test`

Mailcatcher displays outgoing development emails such as registration confirmations, booking confirmations, and password reset links.

## 3. User Roles

### 3.1 Guest or Customer

Guests can browse public pages, view services and barbers, and book appointments. Registration is not required; guests only need to provide their name and email address. Registered users can later view and cancel their appointments.

### Customer Capabilities:

* Book appointments
* Confirm bookings via email
* View personal bookings
* Cancel active appointments
* Leave reviews after completed appointments

### 3.2 Barber

Barbers use a dedicated internal interface where they can manage bookings, update profiles, and request time off.

### Barber Capabilities:

* View personal appointments
* Cancel appointments with reason
* Mark appointments as completed or `no_show`
* Manage profile picture, bio, links, and gallery
* View personal settings
* Submit time-off requests
* View personal reviews

### 3.3 Administrator

Administrators manage business operations and core system data.

### Admin Capabilities:

* Create, edit, and delete services
* Create and configure employees
* Assign services, pricing, and durations to barbers
* Manage working hours, breaks, and booking rules
* Configure opening hours and special days
* Approve or reject time-off requests
* Filter bookings and manage statuses
* Edit shop profile and gallery
* Manage review visibility

## 4. Public Pages

### 4.1 Homepage

The homepage introduces the salon, services, barbers, and gallery. Booking can also be started here. If the user is not logged in, the system asks whether they want to continue as a guest or sign in.

### 4.2 Services

The services list is loaded from the database. Demo services include:

* Short haircut
* Normal haircut
* Long haircut
* Beard trim
* Fullbox
* Father and son haircut
* Brothers haircut

Pricing and duration may vary by barber, since admins configure service availability individually.

### 4.3 Barbers and Profile Pages

The `Barbers` page displays barbers with images, names, bios, and ratings. Individual profile pages include detailed descriptions, contact links, services, galleries, and reviews.

### 4.4 Contact Page

The `Contact` page displays public business information:

* Address
* Phone number
* Email address
* Opening hours
* Links
* Map

Admins can edit this information.

## 5. Registration and Login

### 5.1 Registration

Registration is available through the `Sign In` modal via the `Sign Up` option.

Required fields:

* Name
* Email
* Password

Password requirements:

* Minimum 8 characters
* Uppercase letter
* Lowercase letter
* Number
* Special character

After registration, the system sends a verification email. The account becomes active only after email confirmation.

### 5.2 Login

Users log in using email and password. If email verification is incomplete, login is blocked and a new verification email is sent.

### 5.3 Forgot Password

The `Forgot password?` link allows users to request a password reset email.

## 6. Booking Process

Bookings can be started from:

* Homepage
* Services page
* Barbers page
* Individual barber profiles

### Booking Steps:

1. Select service(s)
2. Select barber
3. Choose available date and time
4. Login or enter guest information
5. Submit booking
6. Confirm booking via email
7. View booking summary

The system only offers barbers who provide the selected services. Availability considers:

* Business hours
* Barber schedules
* Breaks
* Time off
* Existing bookings

Guest bookings require name and email. Logged-in users use stored account information.

## 7. Booking Rules and Statuses

### Booking Rules:

* No past-date bookings
* Only available services can be selected
* Only free time slots are shown
* Double-booking is prevented
* No booking during breaks, closures, or approved leave
* Guest email cannot match a registered account email
* Booking becomes final only after email confirmation

### Appointment Statuses:

* `pending`: Created but awaiting confirmation
* `confirmed`: Email confirmed
* `completed`: Service finished
* `cancelled`: Appointment cancelled
* `no_show`: Customer did not attend

## 8. Customer Interface

Logged-in customers can view appointments on the `Your Appointments` page.

Displayed details:

* Service
* Barber
* Date
* Time
* Duration
* Price
* Status

Customers can cancel `pending` or `confirmed` appointments. After `completed` appointments, they may leave one review per booking.

## 9. Barber Interface

Barber login URL: `/employee/login`

### Main Pages:

* `Your Appointments`
* `My Configuration`
* `Profile`
* `Time Off`
* `Reviews`

Barbers can:

* Cancel future appointments with reason
* Mark appointments as `completed`
* Mark no-shows as `no_show`

## 10. Admin Interface

Admin login URL: `/admin/login`

### Main Pages:

* `Appointments`
* `Services`
* `Employees`
* `Schedule`
* `Time Off`
* `Reviews`
* `Shop Profile`

Admins can configure:

* Employee services
* Pricing
* Duration
* Work schedules
* Breaks
* Booking rules
* Shop profile
* Galleries

The system supports versioned configurations, allowing future-dated changes.

## 11. Demo Accounts

### Admin

* Email: `admin@barbershop.test`
* Password: `password`
* Login: `/admin/login`

### Barbers

All barber passwords: `password`

* `blowout.ben@barbershop.test`
* `crispy.chris@barbershop.test`
* `bouncy.bella@barbershop.test`
* `loud.lucy@barbershop.test`
* `haircut.harry@barbershop.test`

Login: `/employee/login`

## 12. Typical Workflows

### Guest Booking

1. Open homepage
2. Start booking
3. Choose `Continue as Guest`
4. Select service, barber, date, and time
5. Enter name and email
6. Submit booking
7. Confirm via email link

### Barber Daily Workflow

1. Log into employee panel
2. Review appointments
3. Cancel future bookings if necessary
4. Mark completed services
5. Mark no-shows

### Admin Maintenance Workflow

1. Log into admin panel
2. Review bookings and time-off requests
3. Update services or employees as needed
4. Modify opening hours or business profile
5. Manage review visibility
