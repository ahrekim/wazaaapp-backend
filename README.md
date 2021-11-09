# wazaaapp-backend
Backend for event planning and public event aggregation application.

The goal with this backend is to provide functionality for [wazaaapp](https://github.com/ahrekim/wazaaapp) and to contain various backgound processes
that populate the database with events from different sources. Example of an API that populates the database is in the seeders. (See instructions).

## Requirements
- PHP 7.4 or newer
- Composer

## Instructions
- After cloning run composer update
- Copy and modify .env.example file to a .env file pointing to a database
- run ```php artisan key:generate```
- run ```php artisan migrate```

Finally run ```php artisan db:seed``` For seeding the database with example data, there is a seeder that gets events from a Helsinki API. 

The project can be served with ```php artisan serve```

## License
[MIT](https://choosealicense.com/licenses/mit/)
