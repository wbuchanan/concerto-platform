## 
## Concerto Platform - Online Adaptive Testing Platform
## Copyright (C) 2011-2012, The Psychometrics Centre, Cambridge University
##
## This program is free software; you can redistribute it and/or
## modify it under the terms of the GNU General Public License
## as published by the Free Software Foundation; version 2
## of the License, and not any of the later versions.
##
## This program is distributed in the hope that it will be useful,
## but WITHOUT ANY WARRANTY; without even the implied warranty of
## MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
## GNU General Public License for more details.
##
## You should have received a copy of the GNU General Public License
## along with this program; if not, write to the Free Software
## Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
##

CONCERTO_ARGS <- commandArgs(T)
CONCERTO_DB_HOST <- CONCERTO_ARGS[1]
CONCERTO_DB_PORT <- as.numeric(CONCERTO_ARGS[2])
CONCERTO_DB_LOGIN <- CONCERTO_ARGS[3]
CONCERTO_DB_PASSWORD <- CONCERTO_ARGS[4]
CONCERTO_DB_NAME <- CONCERTO_ARGS[5]
CONCERTO_TEST_SESSION_ID <- CONCERTO_ARGS[6]
 
setwd(CONCERTO_TEMP_PATH)
library(catR)
options(digits=3)
if(!is.na(CONCERTO_ARGS[7])) Sys.setenv("MYSQL_HOME"=CONCERTO_ARGS[7])

update.session.counter <- function(CONCERTO_PARAM){
   CONCERTO_PARAM <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM))
   dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("UPDATE `%s`.`TestSession` SET `counter` = '%s' WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),CONCERTO_PARAM,dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_TEST_SESSION_ID)))
}

update.session.status <- function(CONCERTO_PARAM){
   CONCERTO_PARAM <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM))
   dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("UPDATE `%s`.`TestSession` SET `status` = '%s' WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),CONCERTO_PARAM,dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_TEST_SESSION_ID)))
}

update.session.release <- function(CONCERTO_PARAM){
   CONCERTO_PARAM <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM))
   dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("UPDATE `%s`.`TestSession` SET `release` = '%s' WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),CONCERTO_PARAM,dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_TEST_SESSION_ID)))
}

update.session.time_limit <- function(CONCERTO_PARAM){
   CONCERTO_PARAM <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM))
   dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("UPDATE `%s`.`TestSession` SET `time_limit` = '%s' WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),CONCERTO_PARAM,dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_TEST_SESSION_ID)))
}

update.session.template_testsection_id <- function(CONCERTO_PARAM){
   CONCERTO_PARAM <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM))
   dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("UPDATE `%s`.`TestSession` SET `Template_TestSection_id` ='%s' WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),CONCERTO_PARAM,dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_TEST_SESSION_ID)))
}

update.session.template_id <- function(CONCERTO_PARAM){
   CONCERTO_PARAM <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM))
   dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("UPDATE `%s`.`TestSession` SET `Template_id` ='%s' WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),CONCERTO_PARAM,dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_TEST_SESSION_ID)))
}

update.session.HTML <- function(CONCERTO_PARAM){
   CONCERTO_PARAM <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(fill.session.HTML(get.template.HTML(CONCERTO_PARAM))))
   dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("UPDATE `%s`.`TestSession` SET `HTML` = '%s' WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),CONCERTO_PARAM,dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_TEST_SESSION_ID)))
}

get.template.HTML <- function(CONCERTO_PARAM) {
    CONCERTO_SQL <- sprintf("SELECT `HTML` FROM `Template` WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM)))
    CONCERTO_SQL_RESULT <- dbSendQuery(CONCERTO_DB_CONNECTION,CONCERTO_SQL)
    CONCERTO_SQL_RESULT <- fetch(CONCERTO_SQL_RESULT,n=-1)
    return(CONCERTO_SQL_RESULT[1,1])
}

fill.session.HTML <- function(CONCERTO_PARAM){
    CONCERTO_HTML_MATCHES <- unlist(regmatches(CONCERTO_PARAM,gregexpr("\\{\\{[^\\}\\}]*\\}\\}",CONCERTO_PARAM)))
    CONCERTO_HTML_MATCHES_INDEX <- 1
    while(CONCERTO_HTML_MATCHES_INDEX<=length(CONCERTO_HTML_MATCHES)){
        CONCERTO_HTML_MATCH_VALUE <- gsub("\\{\\{","",CONCERTO_HTML_MATCHES[CONCERTO_HTML_MATCHES_INDEX])
        CONCERTO_HTML_MATCH_VALUE <- gsub("\\}\\}","",CONCERTO_HTML_MATCH_VALUE)
        if(exists(CONCERTO_HTML_MATCH_VALUE)){
            CONCERTO_PARAM <- gsub(CONCERTO_HTML_MATCHES[CONCERTO_HTML_MATCHES_INDEX],toString(get(CONCERTO_HTML_MATCH_VALUE)),CONCERTO_PARAM, fixed=TRUE)
        }
        CONCERTO_HTML_MATCHES_INDEX=CONCERTO_HTML_MATCHES_INDEX+1
    }
    return(CONCERTO_PARAM)
}

library(RMySQL)
CONCERTO_DB_DRIVER <- dbDriver('MySQL')
CONCERTO_DB_CONNECTION <- dbConnect(CONCERTO_DB_DRIVER, user = CONCERTO_DB_LOGIN, password = CONCERTO_DB_PASSWORD, dbname = CONCERTO_DB_NAME, host = CONCERTO_DB_HOST, port = CONCERTO_DB_PORT)
dbSendQuery(CONCERTO_DB_CONNECTION,statement = "SET NAMES 'utf8';")

rm(CONCERTO_DB_HOST)
rm(CONCERTO_DB_PORT)
rm(CONCERTO_DB_LOGIN)
rm(CONCERTO_DB_PASSWORD)
rm(CONCERTO_ARGS)