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

args <- commandArgs(T)
DB_HOST <- args[1]
DB_PORT <- as.numeric(args[2])
DB_LOGIN <- args[3]
DB_PASSWORD <- args[4]
DB_NAME <- args[5]
TEST_SESSION_ID <- args[6]
 
setwd(TEMP_PATH)
library(catR)
options(digits=3)
if(!is.na(args[7])) Sys.setenv("MYSQL_HOME"=args[7])
print(Sys.getenv("MYSQL_HOME"))

set.var <- function(variable, value, sid=TEST_SESSION_ID, dbn=DB_NAME){

   variable <- dbEscapeStrings(con,toString(variable))
   value <- dbEscapeStrings(con,toString(value))

   sql <- sprintf("REPLACE INTO `%s`.`TestSessionVariable` (`TestSession_id`,`name`,`value`) VALUES('%s','%s','%s')",dbn,sid,variable,value)

   dbSendQuery(con, statement = sql)
   print(paste("HTML variable <b>",variable,"</b> set to: <b>",value,"</b>",sep=''))
}

get.var <- function(variable, sid=TEST_SESSION_ID, dbn=DB_NAME){
    variable <- dbEscapeStrings(con,toString(variable))

    query <- sprintf("SELECT `value` FROM `TestSessionVariable` WHERE `TestSession_id`='%s' AND `name`='%s'",sid,variable)
    sqlResult <- dbSendQuery(con, statement = query)
    return(fetch(sqlResult,n=-1))
}

library(RMySQL)
drv <- dbDriver('MySQL')
con <- dbConnect(drv, user = DB_LOGIN, password = DB_PASSWORD, dbname = DB_NAME, host = DB_HOST, port = DB_PORT)