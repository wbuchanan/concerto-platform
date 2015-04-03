# Concerto v4 installation and updating #


---


## Requirements ##

  * **Linux** operating system
  * **PHP** version equal or greater than **v5.3**
  * **php-process** package (needs to be installed in some Linux distributions such as CentOS)
  * **PHP safe mode** must be turned **OFF**
  * **PHP magic quotes** must be turned **OFF**
  * **PHP short open tags** must be turned **ON**
  * **PHP open base dir** restrictions must allow access to R, Rscript and php binaries
  * **MySQL** version equal or greater than **v5**
  * **R** version equal or greater than **v2.15**
  * **concerto** R package must be installed
  * Linux user with **root access**
  * MySQL user with **ALL privileges** and **GRANT** option on **`*.*`**


---


## Concerto Amazon Machine Image ##

If you want to launch Concerto image on Amazon EC2 instance, you can search for: **Ubuntu 13.04 - Concerto v4.0.0.beta5** in your EC2 Management Console AMI search engine. After setting up Concerto instance it is recommended to update Concerto version to the latest available using the guide below.


---


## Installation ##

**This tutorial assumes that Apache is your HTTP server**

### Step 1 ###

Download the latest Concerto v4 package.

### Step 2 ###

Copy the contents of the package to your Apache public www directory

### Step 3 ###

As a user with root access do:

Start R by typing: **`R`**

Then type **`install.packages("rjson")`** at the R prompt, afterwards quit R with **`q()`**

**`cd /var/www/concerto/lib/R`**

... replace **`/var/www/concerto/`** with your Concerto installation directory path

**`R CMD build concerto`**

**`R CMD INSTALL -l /usr/lib/R/library concerto`**



### Step 4 ###

Create new Concerto master MySQL database

### Step 5 ###

Edit **{installation\_dir}/SETTINGS.php** file.

### Step 6 ###

Run **http://yourdomain/{concerto_directory}/setup** and correct any reported problem.

### Step 7 ###

Run:

**`sudo visudo`**

... and add this lines:

**`www-data ALL=(%concerto) NOPASSWD: /usr/bin/R`**

**`www-data ALL=(%concerto) NOPASSWD: /bin/kill`**


Replace **www-data** with your PHP process user name (corresponds to **$php\_user** variable from SETTINGS.php), **concerto** with your Linux group name for Concerto Linux users (corresponds to **$r\_users\_group** variable from SETTINGS.php) and **/usr/bin/R** with path to R executable (corresponds to **$path\_r\_exe** variable from SETTINGS.php)

If you see line with:

**`Defaults requiretty`**

... then comment it out.

### Step 8 ###

As a root user run:

**`crontab -e`**

... and add following lines:

**`* * * * * /usr/bin/php /var/www/concerto/cron/users.php`**

... and:

**`0 0 1 * * /usr/bin/php /var/www/concerto/cron/autocomplete.php`**

(where **/var/www/concerto/** is your Concerto installation directory)


---


## Update ##

**Concerto v3.x and lower are not compatible with v4.x. You need to make a clean installation first if you want to move to v4.x.**

### Step 1 ###

Download the latest Concerto v4 package.

### Step 2 ###

Copy the contents of the package to your Concerto installation directory

### Step 3 ###

As a user with root access do:

**`cd /var/www/concerto/lib/R`**

... replace **`/var/www/concerto/`** with your Concerto installation directory path

**`R CMD build concerto`**

**`R CMD INSTALL -l /usr/lib/R/library concerto`**

### Step 4 ###

Edit **{installation\_dir}/SETTINGS.php** file.

### Step 5 ###

Run **http://yourdomain/{concerto_directory}/setup** and correct any reported problem.


---


## /SETTINGS.php file ##

This file contains all configurable options that needs to be set before you can run Concerto on your system.

| **VARIABLE NAME** | **DESCRIPTION** | **EXAMPLE VALUE** |
|:------------------|:----------------|:------------------|
| $db\_host | MySQL server host | "localhost" |
| $db\_port | MySQL server port | 3306 |
| $db\_master\_user | MySQL user name with ALL privileges and GRANT option | "root" |
| $db\_master\_password | MySQL password for user with ALL privileges and GRANT option | "topsecret" |
| $db\_master\_name | Concerto master MySQL database name ( user need to create it manually prior to running /setup ) | "concerto" |
| $db\_users\_name\_prefix | prefix for MySQL user names created by Concerto ( workspace id will be appended to it ) - **SET IT ONLY ONCE PRIOR TO RUNNING /setup**| "concerto_"_|
| $db\_users\_db\_name\_prefix | prefix for MySQL database names created by Concerto ( workspace id will be appended to it ) - **SET IT ONLY ONCE PRIOR TO RUNNING /setup** | "concerto_"_|
| $path\_external | Concerto full URL **ending with slash character ('/')** | "http://domain.com/concerto/" |
| $path\_r\_script | Rscript executable path | "/usr/bin/Rscript" |
| $path\_r\_exe | R executable path | "/usr/bin/R" |
| $path\_php\_exe | php executable path | "/usr/bin/php" |
| $path\_sock | socks directory path ending with slash character ('/'), leave blank for default - /{concerto\_installation\_path}/socks/ | "/var/concerto\_socks" |
| $path\_data | data directory path ending with slash character ('/'), leave blank for default - /{concerto\_installation\_path}/data/ | "/var/concerto\_data" |
| $server\_socks\_type | socket server type ( UNIX or TCP, **UNIX highly recommended** ) | "UNIX" |
| $server\_host | choose host to connect to ( **only when TCP** ) | "127.0.0.1" |
| $server\_port | choose port used for connection ( **only when TCP** ) | "8888" |
| $r\_instances\_persistant\_instance\_timeout | after set period of instance inactivity in seconds the instance will be serialized and closed | 900 |
| $r\_instances\_persistant\_server\_timeout | after set period of server inactivity in seconds the server will be closed ( new instances can restart it anytime ) | 1200 |
| $r\_max\_execution\_time | maximum R execution time after which instance will be terminated ( prevents infinite loops in R on server ) | 30 |
| $unix\_locale | Unix locale LANG variable. Must be installed on the system. Leave blank for none/default | "en\_GB.UTF8" |
| $timezone | PHP timezone settings | "Europe/London" |
| $mysql\_timezone | MySQL timezone settings, leave blank to make it the same as **$timezone** | "+0:00" |
| $public\_registration | is open registration from login form allowed | false |
| $cms\_session\_keep\_alive | prevents session expiry when in panel | true |
| $cms\_session\_keep\_alive\_interval | time interval between session keep alive requests in miliseconds | 300000 |
| $remote\_client\_password | password required by remote clients to use this Concerto server | "topsecret" |
| $r\_users\_name\_prefix | prefix for Linux users created by Concerto ( user id will be appended to it ) - **SET IT ONLY ONCE PRIOR TO RUNNING /setup** | "concerto_"_|
| $r\_users\_group | Linux group name for users above | "concerto" |
| $php\_user | PHP process user name | "www-data" |
| $php\_user\_group | PHP process user group | "www-data" |
| $log\_server\_events | socket communication info and test server php errors will be printed to a text file in data directory | false |
| $log\_server\_streams | socket server streams will be logged too | false |
| $log\_js\_errors | logs all test specific js errors from client side | true |
| $log\_r\_errors | logs all test specific R errors | true |

**ALWAYS RUN /setup AFTER CHANGING SETTINGS IN THIS FILE!**


---


## Uninstallation ##

When uninstalling it is highly recommended to first:

  * remove every user through Concerto panel
  * wait until Concerto users cron task will fire (it will remove any Linux users and groups created by Concerto)

**`* * * * * /usr/bin/php /var/www/concerto/cron/users.php`** - using default settings it fires every minute

  * remove files and Concerto master database