<?php
//MySQL
$db_host = "localhost";
$db_port = "3306";
$db_user = "db_user";
$db_password = "db_password";
$db_name = "db_name";

//paths
$path_external = "http://domain.com/"; //e.g. http://domain.com/concerto/
$path_r_script = "/usr/bin/Rscript"; //e.g. /usr/bin/Rscript
$path_r_exe = "/usr/bin/R"; //e.g. /usr/bin/R
$path_php_exe = "/usr/bin/php"; //e.g. /usr/bin/php
$path_mysql_home = ""; //Home directory of MySQL server. It will be probably needed if you want to install Concerto on Windows platform. e.g. C:/Program Files/MySQL/MySQL Server 5.5
$path_sock = ""; //leave blank for default - /[concerto_installation_path]/socks/
$path_temp = ""; //leave blank for default - /[concerto_installation_path]/temp/

//R connection
$r_instances_persistant = false; //true  - R instances are persistant and open throughout the whole test ( faster, but consumes more system resources, EXPERIMENTAL ), false - R instances are closed and restored when needed ( slower, but consumes less system resources )                           
$r_instances_persistant_instance_timeout = 300; //after set period of instance inactivity in seconds the instance will be closed
$r_instances_persistant_server_timeout = 420; //after set period of server inactivity in seconds the server will be closed ( new instances can restart it anytime )
$r_max_execution_time = 60; //maximum R execution time ( prevents infinite loops in R on server )
$unix_locale = ""; //Unix locale LANG variable. Must be installed on the system. Leave blank for none/default.

//general
$timezone = 'Europe/London';
$public_registration = false;
$public_registration_default_UserType_id = 4;
$cms_session_keep_alive = true;
$cms_session_keep_alive_interval = 900000; //miliseconds
$contact_emails = "pl362@cam.ac.uk,mk583@cam.ac.uk,vm298@cam.ac.uk"; 
$forum_url = "http://concerto.e-psychometrics.com/forum/";
$project_homepage_url = "http://code.google.com/p/concerto-platform/";

//remote client
$remote_client_password = "pass";

//ALWAYS RUN /setup AFTER CHANGING SETTINGS IN THIS FILE!
?>