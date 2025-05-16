# Fee Calculator

This is a PHP Symfony console application that calculates fees based on a CSV input file.

## Requirements

- PHP 7.4 or higher
- Composer
- Symfony CLI

## Getting Started

### 1. Install Dependencies

Make sure you have [Composer](https://getcomposer.org/) installed, then run:

composer install

### 2. Run the Application
To process an input file and calculate fees, use:

php bin/console app:calculate-fees input.csv
Replace input.csv with the path to your CSV file.

### 3. Run Tests
To run the unit tests:

php bin/phpunit