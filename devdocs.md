# Developer Documentation

## 1. Project overview
This project is a full stack barbershop booking system.
It has a Vue frontend, a Laravel backend, a MySQL database, and a Docker based local setup.
The repository also contains a docs site, Swagger UI, a JSON server, Mailcatcher, and a separate Selenium test project.

## 2. Main stack
- Frontend: Vue 3, Vite, Vue Router, Pinia, Tailwind CSS, FormKit, Axios
- Backend: PHP 8.2, Laravel 12, Laravel Sanctum, MySQL, Intervention Image
- Infrastructure: Docker Compose, Nginx proxy, phpMyAdmin, Swagger UI, Mailcatcher
- Tests: PHPUnit for backend, NUnit + Selenium for browser tests

## 3. Main services in Docker
- `proxy`: main reverse proxy on port 80
- `frontend`: Vue app
- `backend`: Laravel app
- `webserver`: nginx in front of Laravel
- `db`: MySQL
- `phpmyadmin`: database UI
- `docs`: documentation site
- `swagger`: Swagger UI
- `jsonserver`: small mock JSON service
- `mailcatcher`: local email inbox for testing

## 4. Local setup

### Environment file
- The main `.env.example` is in the root of the project.
- The containers use this root `.env` file.
- `start.sh` copies `.env.example` to `.env` if `.env` does not exist yet.

### Start command
- Use this command for the first start:

```bash
./start.sh
```

### What `start.sh` does
- Creates the root `.env` file if needed
- Creates the shared Docker volumes for `pnpm` and Composer
- Installs frontend packages with `pnpm`
- Starts the Docker services
- Runs `composer install` in the backend container
- Runs `php artisan migrate:fresh --seed`
- Runs `php artisan key:generate` if `APP_KEY` is empty

### Important note
- `migrate:fresh --seed` deletes the old database data and creates fresh demo data again.
- Because of this, running `./start.sh` resets the database state.

### Stop commands

```bash
docker compose stop
docker compose down -v
```

- The `-v` option also deletes the Docker volumes.

## 5. Local URLs
- Frontend: `http://frontend.vm1.test`
- Backend: `http://backend.vm1.test`
- API host: `http://api.vm1.test`
- Docs: `http://docs.vm1.test`
- Swagger UI: `http://swagger.vm1.test`
- phpMyAdmin: `http://pma.vm1.test`
- JSON server: `http://jsonserver.vm1.test`
- Mailcatcher: `http://mailcatcher.vm1.test`
- Responsive static page: `http://responsive.vm1.test`

## 6. Hostname note
- The project uses local hostnames like `frontend.vm1.test` and `backend.vm1.test`.
- These names must point to your local machine.
- In a normal local setup this usually means you need host file entries for the used `*.vm1.test` names.

## 7. Important folders
- `frontend/`: Vue frontend source code
- `backend/`: Laravel backend source code
- `proxy/`: main nginx reverse proxy config
- `webserver/`: nginx config for the Laravel app
- `docs/`: docs site content
- `swagger/`: OpenAPI file for Swagger UI
- `jsonserver/`: JSON server mock service
- `responsive/`: extra static responsive page
- `AppointmentSystemTests/`: .NET Selenium test project

## 8. Frontend structure
- The router is in `frontend/src/router/index.js`.
- Public pages include home, barbers, contact, booking, confirmation, summary, your appointments, verify email, forgot password, and reset password.
- There are separate login pages for admin and employee users.
- The auth store is in `frontend/src/stores/AuthStore.js`.
- Auth data is kept in `localStorage` with `token`, `user_id`, `user_name`, and `role`.
- The API client is in `frontend/src/api/index.js`.
- The frontend API base URL is hardcoded to `http://backend.vm1.test/api`.

## 9. Backend structure
- Main API routes are in `backend/routes/api.php`.
- Authentication uses Sanctum tokens.
- Role based access is used for `customer`, `employee`, and `admin`.
- Main controllers include auth, appointment, booking, review, employee profile, and admin related controllers.
- Booking logic is mainly in `App\Calculations\CreateAppointment` and in the services inside `backend/app/Services/Booking`.
- Mail classes are used for verification, booking confirmation, booking summary, cancellation, and review request emails.

## 10. Main data model
- `User`: login and role data
- `Customer`: customer profile data
- `Employee`: barber or employee profile data
- `Service`: barber services
- `Appointment`: booking data
- `Review`: customer feedback
- The project also has version and configuration tables for employee services, schedule, booking rules, and time off handling.

## 11. Current user flow in code
- A customer can register and must verify the email before login.
- A customer can also book as a guest.
- Booking creates a pending appointment and sends a confirmation email.
- The confirmation link changes the appointment to confirmed and sends the user to the summary page.
- Signed in customers can see their appointments and cancel pending or confirmed ones.
- Signed in customers can leave a review for completed appointments.
- Employees have a dashboard UI for appointments, profile, and time off.
- Admin users have pages for services, employees, schedule, and time off.

## 12. Seeded demo data

### Admin
- Email: `admin@barbershop.com`
- Password: `password`

### Employees
- `blowout.ben@barbershop.test`
- `crispy.chris@barbershop.test`
- `bouncy.bella@barbershop.test`
- `loud.lucy@barbershop.test`
- `haircut.harry@barbershop.test`
- Password for all employee accounts: `password`

### Other seeded data
- Sample customers
- Sample services
- Employee images
- Opening hours
- Special days
- Appointments
- Reviews

## 13. Default shop data
- The seeder creates weekday opening hours from Monday to Friday.
- The seeded opening time is `10:00` and the seeded closing time is `20:00`.
- Saturday and Sunday are seeded as closed in the database.

## 14. Tests

### Backend tests
- Backend tests are in `backend/tests/Feature` and `backend/tests/Unit`.
- They cover important flows like email verification, weekly booking limit, barber cancellation, and barber/admin data.
- A normal backend test command is:

```bash
docker compose exec backend php artisan test
```

### Browser tests
- The Selenium project is in `AppointmentSystemTests/`.
- It uses NUnit and ChromeDriver.
- The tests open `http://frontend.vm1.test`.

## 15. Current developer notes
- `swagger/openapi.yaml` is still a small placeholder file and does not describe the real API yet.
- `docs/content/index.md` is still very small, so the docs site is not finished yet.
- The admin `Schedule` and `Time Off` pages currently use local frontend sample data, not full backend data.
- There is naming mismatch in the employee area: some frontend API calls and backend tests still use `barber` endpoint names, while the current `routes/api.php` uses `employee` prefixes.
- Because of this, the employee area should be checked carefully before new work starts there.

## 16. Good files to read first
- `compose.yaml`
- `start.sh`
- `frontend/src/router/index.js`
- `frontend/src/api/index.js`
- `backend/routes/api.php`
- `backend/app/Calculations/CreateAppointment.php`
- `backend/app/Services/Booking/AppointmentAvailabilityService.php`
- `backend/database/seeders/`
