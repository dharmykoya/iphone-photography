## Assignment


- Unlocking Achievements based on Lessons Watched and Comments Written.
- Unlocking Badges Based on achievements made .
- Endpoint that will return the following
    - unlocked_achievements
    - next_available_achievements
    - current_badge,
    - next_badge,
    - remaining_to_unlock_next_badge

## Installation

1. Ensure you have PHP and composer installed

2. Clone this repo

```bash
$ git clone https://github.com/dharmykoya/metar_weather_condition.git
```

3. From the root directory run to install Dependencies:

```bash
$ composer install
```

4. You must have a MySql database running locally


5. Update the database details in ‘.env’ to match your local setup


6. To setup the database tables run:

```bash
php artisan migrate
```

7. To seed data into database run:

```bash
php artisan db:seed
```

8. To start server run:

```bash
php artisan serve
```

## Testing

```bash
php artisan test
```

## Available Endpoint

|                    DESCRIPTION                     | HTTP METHOD | ROUTES                                             |
|:--------------------------------------------------:|-------------|----------------------------------------------------|
|               Achievements endpoint                | GET         | {{url}}/users/{user}/achievements                  |
| watch a lesson and to trigger lesson watched event | GET         | {{url}}/api/users/:user_id/watch/:lesson_id/lesson |
|                 to write a comment                 | POST        | {{url}}/api/comments                               |
