# Lain game web edition

## Installation

1. Install composer

2. `composer update`

3. Set `public_html` as your document root.

4. Create database and load data from `database/laingame.sql`.

5. Set your db parameters to `config.php`.

6. On NGINX you need rewrite all your queries to `/router.php?req=$1`

### Docker

`cd env/docker`

Set your port numbers in `.env.sample` file and save it to `.env`

`docker-compose up --build`

## Assets

Game data assets must be located at `public_html/media` directory.
