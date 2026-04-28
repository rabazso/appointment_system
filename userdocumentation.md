# User Documentation

## 1. Short system overview
In this system, users can:
- choose a service and a barber,
- book an appointment,
- confirm the booking by email,
- view or cancel appointments later,
- leave a review after a completed appointment.

## 2. Before you start
- Booking is possible with or without an account.
- After registration, email verification is required.

## 3. Public pages

### 3.1 Home page
- Shows a short overview of services and barbers.
- From the menu, users can open other pages and start booking.

### 3.2 Barbers page
- Shows barber photo, name, and short description.
- Booking can also be started from this page.

### 3.3 Contact page
- Shows address, phone number, email, opening hours, and map.

## 4. Login and account management

### 4.1 Registration
1. Open the `Sign In` window.
2. Choose the `Sign Up` option.
3. Enter: first name, last name, email, password, password confirmation.
4. Submit the form.

### 4.2 Email verification
1. The system sends a verification email.
2. Click the link in that email.
3. Only after this step can you log in.

### 4.3 Login
1. Open login from the header.
2. Enter your email and password.
3. If needed, use the `Forgot password?` link.

### 4.4 Password reset
1. Enter your email on the reset page.
2. Open the link sent by email.
3. Set a new password.

## 5. Booking an appointment (main flow)

### 5.1 Step-by-step booking
1. Choose a service.
2. Choose a barber.
3. Choose a date and available time slot.
4. Log in or continue as guest.
5. Submit the booking.

### 5.2 Booking rules
- Past dates cannot be booked.
- Only free time slots are shown.
- Double booking is not allowed for the same barber and time.
- One person can have only one active booking in the same calendar week.
- A guest email cannot match an already registered user email.

### 5.3 Booking confirmation
1. After submission, a confirmation email is sent.
2. The booking is confirmed only after clicking the email link.
3. Then the system opens the summary page.

## 6. Customer dashboard

### 6.1 My appointments
- Shows: service, barber, date, time, price, status.
- `Pending` and `Confirmed` appointments can be canceled.

### 6.2 Reviews
- Reviews can be submitted from the `Leave a review` section on the home page.
- Only completed appointments can be reviewed.
- One appointment can have one review.

## 7. Staff and admin pages

### 7.1 Staff login
- URL: `/employee/login`
- Main sections: appointments, profile, time off.

### 7.2 Admin login
- URL: `/admin/login`
- Main sections: services, employees, schedule, time off management.

## 9. Demo accounts

### Admin
- Email: `admin@barbershop.com`
- Password: `password`

### Staff
- `blowout.ben@barbershop.test`
- `crispy.chris@barbershop.test`
- `bouncy.bella@barbershop.test`
- `loud.lucy@barbershop.test`
- `haircut.harry@barbershop.test`
- Password for all: `password`

