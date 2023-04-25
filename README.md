## Installation

`cp .env.example .env`

Fill your account in `.env` file.

```
ENKIN_USERNAME={your_email}
ENKIN_PASSWORD={your_password}
```

Run composer

`composer install`

Start schedule to log start work

`php artisan schedule:work`

By default time will be log at 08:25. You can change this by changing `.env` file.

For example:

`ENKIN_LOG_TIME=08:20`
