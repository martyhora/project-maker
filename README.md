# Project Maker

Web application written in Nette framework that allows you to define structure of a backend application and generate it's source code afterwards.

The application generates all the grids and forms for you.

# Installation

- clone project by running ```git clone https://github.com/martyhora/project-maker``` into your DocumentRoot path
- run ```composer install``` in the project root
- run ```npm i``` in the project root
- run ```bower i``` in the project root
- run ```webpack --watch``` to compile changes in JS a LESS files
- create database and run SQL script in ```/app/sql/db.sql``` to create database structure in it
- create ```app/config/config.local.neon``` and set up the the database connection
- open the project in the browser
