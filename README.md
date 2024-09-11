# Laravel Vue.js Bootstrap Project with Vite

This is a Laravel application integrated with Vue.js 3, Bootstrap 5, and Vite for asset bundling and development.

## Project Overview

This project allows users to upload images to extract text with OCR. View and copy the converted text. Limited to 5 processes per IP per day.

## Requirements

Before setting up the project, ensure you have the following installed:

- **PHP**: ^8.2
- **Composer**: Latest version
- **Node.js**: >= 16 (for Vite)
- **NPM**: Latest version (comes with Node.js)

## Installation
- **Clone the project**: `git clone`
- **Install dependencies**: `composer install` and `npm install`
- **Run Laravel server**: `php artisan serve`
- **Compile assets**: `npm run dev` for development or `npm run build` for production
- **Copy env**: `cp .env.example .env`
- **Generate application key:**: `php artisan key:generate`

## Note: 
- Set your OCR_API_KEY in env file

### Visit
```
http://localhost
```

or

```
http://localhost:8000
```

## License
Copyright © 2024, Pindar Jimenez Jr