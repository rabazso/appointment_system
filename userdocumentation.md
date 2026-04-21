# User Documentation

## 1. What this system is
This project is an online barbershop appointment system.
Customers can look at services and barbers, book an appointment, confirm it by email, and later check or cancel it.
The project also has pages for employees and for the admin.

## 2. Main public pages

### Home page
- The home page is the main landing page of the system.
- It shows the hero section, service preview, barber preview, customer reviews, and the footer.
- From here the user can open the booking flow or move to other pages from the top menu.

### Barbers page
- This page shows the barbers of the shop.
- The user can see the barber photo, name, and short bio.
- The user can also start booking from this page.

### Contact page
- This page shows the contact information of the shop.
- The user can see the address, phone number, email address, opening hours, and a Google map.

## 3. Sign up and sign in

### Sign up
- A new customer can create an account from the Sign In window by choosing the Sign Up option.
- The user must give first name, last name, email, password, and password confirmation.
- The password must follow the rules shown on the page.

### Email verification
- After registration the system sends a verification email.
- The user must open the link in the email before logging in.
- If the email is already verified, the page also shows this clearly.

### Sign in
- Customers can sign in from the header.
- If the user forgot the password, the Sign In form has a "Forgot password?" link.

### Forgot password and reset password
- On the forgot password page the user gives an email address.
- The system sends a reset link by email.
- On the reset page the user can set a new password.

## 4. Booking an appointment

### Booking steps
- The booking page guides the user step by step.
- First the user chooses a service.
- Then the user chooses a barber.
- After that the user chooses a date and a free time slot.

### Booking as guest
- The user does not need an account to book.
- If the user is not signed in, the booking form asks for name and email at the last step.

### Booking rules
- The system does not allow booking in the past.
- The system only shows free time slots.
- The system does not allow double booking for the same barber and time.
- One person can only keep one active appointment in the same calendar week.
- A guest email cannot be the same as an already registered user email.

### Booking confirmation
- After the booking is sent, the system sends a confirmation email.
- The user must click the email link to confirm the booking.
- After confirmation the system opens the summary page.

## 5. Customer dashboard

### Your Appointments page
- Signed in customers can open the dashboard from the header.
- On this page the user can see the service, barber, date, time, price, and status of each appointment.
- Pending and confirmed appointments can be cancelled from this page.

### Reviews
- The home page shows customer reviews.
- A signed in customer can open the "Leave a review" window from the home page.
- The user can review completed appointments.
- One appointment can only have one review.

## 6. Employee and admin pages

### Employee login and dashboard
- Employees use a separate login page: `/employee/login`.
- After login they can open the employee dashboard.
- The employee dashboard has sections for appointments, profile, and time off.

### Admin login and pages
- The admin uses a separate login page: `/admin/login`.
- The admin pages are used for services, employees, schedule, and time off management.

## 7. Demo accounts

### Admin account
- Email: `admin@barbershop.com`
- Password: `password`

### Employee accounts
- `blowout.ben@barbershop.test`
- `crispy.chris@barbershop.test`
- `bouncy.bella@barbershop.test`
- `loud.lucy@barbershop.test`
- `haircut.harry@barbershop.test`
- Password for all employee accounts: `password`

### Customer access
- A customer can create a new account.
- A customer can also book as a guest without registration.

## 8. Short user notes
- If you register, verify your email before you try to log in.
- If you book an appointment, check your email and confirm the booking.
- If you want to leave a review, sign in first and choose a completed appointment.
