IATI-Public Validator
=====================

This is an application to help people check any random file for IATI complience.

the plan is to build a modular application adding tests as we go.

Basic functions are:

* Test for well formedness
* Test for validation against the iati schema

Some basic statistics:

* Elements found
* Basic info

Requirements
------------
PHP > 5.2.11 - for the use of `libxml_disable_entity_loader` to protect against xml injection attacks

Quick Start
-----------
* Checkout the files to your webserver.
* You will need to add an `upload` directory to the root of the project and make it writable by your webserver.
* Make a copy of example.settings.php and rename it settings.php
* Edit the configuration information in that file and save it. 
* Don't forget to set a path for your log file. Basic info is collected about the use of the upload functions. You may need to enable write permissions on your log file.
* To clear out the `upload` directory you can (copy) and alter the example.remove_files.php file and hit it on a cron run

About file upload size
----------------------
The application sets a limit in the `functions/process_files.php` file, BUT your webserver will probably also have an upload limit in place.
Alter your php.ini file or checkout the override rules in the `.htaccess` file included in the project. Obviously this only works if your webserver reads the `.htaccess` file.

IATI Schema
-----------
Currently the application is a bit inconsistent in the way it refrences the schema. Sometimes it uses the remote URL at others it links to downloaded files.

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
Apart from the well formed and validation checks, results are stored in files in json format. This is then used to display the results.
Which page gets called is controlled by the `$_GET` variables passed by the URL. These are sanitised by an array of allowed values at the top of index.php

Tests
-----
There are application tests in the `tests.php` file. These are run using [PHPUnit](https://github.com/sebastianbergmann/phpunit/#phpunit), `phpunit tests.php`. Individual XML files that should pass or fail various validator tests are found in the `tests/` directory.


