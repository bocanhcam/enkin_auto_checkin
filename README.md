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

By default, time will be logged at 08:25. You can change this by changing `.env` file.

For example:

`ENKIN_LOG_TIME=08:20`

Or if you want to random your log time from `8h10` to `8h29` just set in `.env` file:

`ENKIN_RANDOM_TIME=true`
