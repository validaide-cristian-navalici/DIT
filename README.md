# DIT - Database Integrity Tool 

## Description

This is a tool that generates a file with all the tables in a MySQL Database and their associated checksums. Furthermore, this file can
be used in a comparison, to find out for example if a cloned database has data integrity issues or not.

## Installation

Clone the project

> git clone https://github.com/validaide-cristian-navalici/DIT.git
 
Composer magic

> composer install

Create the .env file

> touch .env

Add the following variables in the env file with their particular values:

>DB_USER=user
>
>DB_PASSWORD=password
> 
>DB_NAME=MySQL_database_name
> 
>DB_HOST=localhost

## How to use

### Generate checksum files for the first DB

> php dit.php [-v] generateChecksum

### Generate checksum files for the second DB

Modify __.env__ file with the new credentials and database name, and re-run the previous step. A second file will be created.

### Compare checksum files

> php dit.php compareChecksum <first_file> <second_file>

### Help

> php dit.php -h
