# Phoenix HRMS

Enterprise-grade HRMS & Payroll Platform.

## Docker

Run the full local stack with:

```bash
docker compose up --build
```

This starts:

- Laravel API on `http://localhost:8000`
- Laravel queue worker in a separate container
- React app on `http://localhost:5173`

The Docker setup automatically:

- copies `.env.example` to `.env` when needed
- creates `apps/api/database/database.sqlite` if it is missing
- ensures Laravel writable directories exist and stay writable
- installs Composer and npm dependencies when their lockfiles change
- generates `APP_KEY` when missing
- runs Laravel migrations before the API starts

Optional on Linux, to keep file ownership aligned with your host user:

```bash
LOCAL_UID=$(id -u) LOCAL_GID=$(id -g) docker compose up --build
```
