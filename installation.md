# System requirements (v2.0 ) #
  * **PHP** >= v5.3
  * PHP **'short open tags'** option must be set to on
  * PHP **'safe mode'** must be turned off
  * **R**   >= v2.12 http://www.r-project.org/
  * **session** R package installed from R console as a root
  * **RMySQL** R package installed from R console as a root
> > http://biostat.mc.vanderbilt.edu/wiki/Main/RMySQL
  * **catR** R package installed from R console as a root
  * **MySQL** >= v5.0
  * optionaly: **RStudio**
> > http://rstudio.org/

# Installation ( v2.x ) #

**DO NOT** install Concerto v2.x on the database used for Concerto v1.x. Create new database to avoid any incompatibility errors.

  1. extract the contents of the [installation zip archive](http://code.google.com/p/concerto-platform/downloads/list) to the public web folder of your choice on your server.
  1. edit **installation\_path/SETTINGS.php** file to set your MySQL database connection parameters.
  1. run the **installation\_path/setup/** page and follow the instructions.

## Setup Page ##

The Setup Page is the first page that should be run when extracting Concerto files. It allows you to check whether your system meets all requirements and to finalize the setup of the application. If you pass all the checks here you should not have any problem with running Concerto. The tests consists of:
  * PHP settings check,
  * Directory rights check,
  * MySQL configuration check,
  * etc.

The setup page will also allow you to create a login for the first user's login and password, your application installation external URL, RScript path and RStudio integration. This page also checks if there is a newer version of **Concerto Platform** available. If any of the checks fail then the setup page will give recommendations on how to fix it. You should restart the Setup Page after solving the problem.

http://dev.myiqtest.org/concerto2/wiki_images/setup_page.PNG

# First use #

First, read the [Manual](manual.md) and [Tutorial - Simple test Step-By-Step](tutorial.md), then:
  * To enter the test creator navigate to **installation\_path/admin**
  * To start the test navigate to **installation\_path/index.php?hash=enter\_item\_hash\_here**