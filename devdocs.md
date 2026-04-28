# Developer Documentation

## 1. What is this project?
This is a full-stack barbershop appointment booking system.

Main technologies:
- Frontend: Vue 3 + Vite
- Backend: Laravel 12 + PHP 8.2
- Database: MySQL
- Infrastructure: Docker Compose + Nginx
- Tests: Laravel tests and a separate Selenium/NUnit project

## 2. How to start the project

### 2.1 First start
1. Go to the project root directory.
2. Run:

```bash
./start.sh
```

This script:
- creates the `.env` file (if it does not exist),
- creates the required Docker volumes,
- installs frontend packages,
- starts the containers,
- runs `composer install`,
- runs `php artisan migrate:fresh --seed`.

Important:
- `migrate:fresh --seed` deletes old database data and loads fresh demo data.

### 2.2 Stop
```bash
docker compose stop
```

### 2.3 Full cleanup (including data)
```bash
docker compose down -v
```

## 3. Local URLs
- Frontend: `http://frontend.vm1.test`
- Backend: `http://backend.vm1.test`
- API host: `http://api.vm1.test`
- phpMyAdmin: `http://pma.vm1.test`
- Mailcatcher: `http://mailcatcher.vm1.test`

## 4. Testing

### 4.1 Backend tests (Laravel)

Test locations:
- `backend/tests/Feature`
- `backend/tests/Unit`

Run:
```bash
docker compose exec backend php artisan test
```

### 4.2 UI/E2E tests (Selenium + NUnit)
Test location:
- `AppointmentSystemTests/AppointmentSystemTests`

Run:
```bash
cd AppointmentSystemTests/AppointmentSystemTests
dotnet test
```

Note:
- Selenium tests open `http://frontend.vm1.test/`.
- Because of this, make sure the Docker environment is running before tests.

## 5. Important folders (quick overview)
- `frontend/` - Vue frontend
- `backend/` - Laravel backend
- `backend/routes/api.php` - API routes
- `backend/database/seeders/` - demo data
- `proxy/` and `webserver/` - Nginx configurations
- `AppointmentSystemTests/` - Selenium/NUnit tests

## 6. Demo accounts

### Admin
- Email: `admin@barbershop.com`
- Password: `password`

### Employees
- `blowout.ben@barbershop.test`
- `crispy.chris@barbershop.test`
- `bouncy.bella@barbershop.test`
- `loud.lucy@barbershop.test`
- `haircut.harry@barbershop.test`
- Password for all: `password`
