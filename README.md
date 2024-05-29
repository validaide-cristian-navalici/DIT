# DIT - Database Integrity Tool 

## Description

This is a tool that generates a file with all the tables in a MySQL Database and their associated checksums. Further more, this file can
be used in a comparison, to find out for example if a cloned database has data integrity issues or not.

## Installation

Clone the project

> git clone https://github.com/validaide-cristian-navalici/DIT.git
 
Composer magic

> composer install

Create the .env file

> touch .env

Add the following variables in the env file with their particular values:

>DB_USER=<user>
>
>DB_PASSWORD=<password>
> 
>DB_NAME=<MySQL_DB>

## How to use

### Generate checksum files

> php dit.php [-v] generateChecksum

### Compare checksum files

> php dit.php compareChecksum <first_file> <second_file>

### Help

> php dit.php -h
