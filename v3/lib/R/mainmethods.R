args <- commandArgs(T)
DB_HOST <- args[2]
DB_PORT <- as.numeric(args[3])
DB_LOGIN <- args[4]
DB_PASSWORD <- args[5]
DB_NAME <- args[6]
TEST_SESSION_ID <- args[7]
 
setwd(TEMP_PATH)
library(catR)
options(digits=3)
if(!is.na(args[8])) Sys.setenv("MYSQL_HOME"=args[8])
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