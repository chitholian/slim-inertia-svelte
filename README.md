
# Slim, Inertia, Svelte

A template for web development with [Slim](https://www.slimframework.com/docs/v4/), [Inertia](https://inertiajs.com/) and [Svelte](https://svelte.dev/)

### How to Run

- Clone this repository: `git clone https://github.com/chitholian/slim-inertia-svelte`.
- Go to the project directory: `cd slim-inertia-svelte`.
- Install backend dependencies: `composer install`.
- Install frontend dependencies: `yarn`.
- Compile frontend files: `yarn build`. You can use `--mode=production`.
- Edit `app/config.php` file and set database credentials.
- Run database migration: `php app/CLI/db.php -m`. See bellow for more about migrations.
- Start `PHP` Built-in web server: `php -S localhost:8000 -t public/`.
- Open [http://localhost:8000](http://localhost:8000) in your browser.
- Default username `admin`, password: `admin`.

### Database Migrations

No `ORM` library is used, a very simple migration system is provided.

Migration files are created inside `app/Database/Migrations` directory.

- Create a migration file: `php app/CLI/db.php -c "Your Migration Name"`.
- Perform migrations: `php app/CLI/db.php -m`.
- Rollback last migrations: `php app/CLI/db.php -r`.
- Rollback all migrations: `php app/CLI/db.php -t`.

### Required Tools

- `PHP v7.4+`.
- `Composer`.
- `NodeJS` with `yarn`.
- `PHP` extensions: `json, openssl, pdo, gd`.

### More Info

- No explicit `CSRF` protection is implemented yet.
- You can create your own `middleware` for `CSRF` protection.
- Session cookies are set with `httponly, samesite=Strict`.
