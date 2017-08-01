# php_buzz

PHP app using Silex and MySQL

## Directory Structure

```
|- src/  # application code and views
|   |- controllers/
|   |- models/
|   |- views/   
|- vendor/  # library directory for Composer
|- web/  # index.php and static files
|   |- static/
|   |   |- css/
|   |   |- img/
|   |   |- js/
|   |- index.php
|- composer.json  # dependency list for Composer
```

## Features

### User Story

* Catch up on the buzz around town! All the tweets are listed, with the most recent at the top
* Add a tweet by entering your name and message
* Validation messages will let you know if your input needs any adjustment

### Technical Points of Interest  

* Database with two tables: User and Tweet
* * User table can be expanded for a full log-in & registration
* Tweet controller handles adding users and tweets, and deleting tweets

## Desirable Features / Improvements

* Full log-in & registration; hash passwords
   * Delete only available for tweet's authored by the logged-in user
* Use AJAX to update tweets without refreshing entire page
* Clean up route roots for deployment
* Implement security measures
   * Protect against SQL injection by properly handling form input
   * Use authentication tokens to prevent cross-site request forgery (CSRF)
* Turn debug off for production

## Use
* Fork code
* Run Composer update to install dependencies (`composer update` in terminal at the project's root directory)
* Create a MySQL database schema named 'php_buzz_db'
* Run an Apache server for the PHP app and a MySQL server for the database (e.g. with MAMP)
* Navigate to localhost:8888/web (or whatever port the Apache server is running on) in your browser
