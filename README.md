IATI-Public Validator
=====================

[![Build Status](https://travis-ci.org/IATI/IATI-Public-Validator.svg?branch=master)](https://travis-ci.org/IATI/IATI-Public-Validator)
[![License: MIT](https://img.shields.io/badge/license-AGPLv3-blue.svg)](https://github.com/IATI/IATI-Public-Validator/blob/master/LICENSE.md)

Introduction
------------

This is an application to help people check any random file for IATI compliance.

the plan is to build a modular application adding tests as we go.

Basic functions are:

* Test for wellformedness
* Test for validation against the IATI schema

Some basic statistics:

* Elements found
* Basic info

Requirements
------------
PHP > 5.2.11 - for the use of `libxml_disable_entity_loader` to protect against xml injection attacks  
php5-curl is used to fetch remote files, see `functions/fetch_data_from_url.php` (on Ubuntu sudo apt-get install php5-curl)

Quick Start
-----------
* Checkout the files to your webserver.
* You will need to add an `upload` directory to the root of the project and make it writable by your webserver.
* Make a copy of example.settings.php and rename it settings.php
* Edit the configuration information in that file and save it.
* Download all versions of the IATI Schemas using `./get_iati_schemas.sh`
* Don't forget to set a path for your log file. Basic info is collected about the use of the upload functions. You may need to enable write permissions on your log file.
* To clear out the `upload` directory you can (copy) and alter the example.remove_files.php file and hit it on a cron run

Running a local development version
-----------------------------------
A local version of the is possible using the PHP in-built webserver. Using terminal, navigate to the the folder when you have cloned this repository to and enter the following command:

    php -S localhost:8000

Vising `http://localhost:8000/` in your browser should load the homepage for the validator.


About file upload size
----------------------
The application sets a limit in the `functions/process_files.php` file, BUT your webserver will probably also have an upload limit in place.
Alter your php.ini file or checkout the override rules in the `.htaccess` file included in the project. Obviously this only works if your webserver reads the `.htaccess` file.

IATI Schema
-----------
Currently the application is a bit inconsistent in the way it references the schema. Sometimes it uses the remote URL at others it links to downloaded files.

Sorry about that!

Cron (Tidy Up)
--------------
The upload directory will store files people upload to the service.
The file `example.remove_files.php` when run will remove files older than a specified time.
You should edit this file to set the path to your upload directory, and alter the time period.

How it works
------------

Once a file is uploaded or pulled from the web, the path to the file (and file details) are saved in session variable.
Files are stored locally with an appended time stamp. This way more than one person can load a file of the same name (or from the same URL).
Data generated against that file can then also be stored uniquely.

Once we have a file, we can perform various tests.
The index.php file controls all page views.
Each test is contained in it's own 'page' within the `pages/` directory.
Apart from the well formed and validation checks, results are stored in files in JSON format. This is then used to display the results.
Which page gets called is controlled by the `$_GET` variables passed by the URL. These are sanitised by an array of allowed values at the top of index.php

Tests
-----
There are application tests in the `tests/tests.php` file. These are run using [PHPUnit](https://github.com/sebastianbergmann/phpunit/#phpunit):

    phpunit tests/tests.php

Individual XML files that should pass or fail various validator tests are found in the `tests/xml/` directory.  Note recent versions of PHPUnit require PHP version 5.6 or above.  These tests were written retrospectively, and therefore take more of a start-to-end functional approach, rather than testing lower level functionality (for example functions/methods).

Upgrades
--------
To upgrade the application you need to update the files, and sometimes you will need to update the IATI Schema.

To upgrade the files, if you use git, a git pull on the appropriate directory will update your files. Alternatively, re-install using the Quick Start instructions.

To make sure you have the latest schema files, run `./get_iati_schemas.sh`
