This is a dummy project used to familiarize myself with FilamentPHP and Scribe documentation.

## Installation

- Clone this repo
- `composer install`
- `cp .env.example .env`
- `php artisan key:generate`
- Set up database connection
- `php artisan migrate`
- Download dataset from https://www.kaggle.com/datasets/rounakbanik/the-movies-dataset
- Place `archive.zip` in `storage/app`
- `php artisan import:csv`
- `npm install`
- `npm run build/dev`
