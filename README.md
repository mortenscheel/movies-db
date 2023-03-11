This is a dummy project used to familiarize myself with FilamentPHP and Scribe documentation.

## Installation

- Clone this repo
- `cp .env.example .env`
- `php artisan key:generate`
- Set up database connection
- `php artisan migrate`
- Download dataset from https://www.kaggle.com/datasets/rounakbanik/the-movies-dataset
- Extract csv files to `storage/app/csv`
- `php artisan prepare:csv`
- `php artisan import:csv`
