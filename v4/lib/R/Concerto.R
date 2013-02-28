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
 
concerto <- list(
    initialize = function(testID,sessionID,workspaceID,workspacePrefix,user,password,dbName,host='localhost',port=3306,tempPath,mediaPath,dbTimezone,dbConnect,userIP){
        print("initialization...")

        options(encoding='UTF-8')
        concerto$testID <<- testID
        concerto$sessionID <<- sessionID
        concerto$workspaceID <<- workspaceID
        concerto$workspacePrefix <<- workspacePrefix
        concerto$templateFIFOPath <<- paste(tempPath,"/fifo_",sessionID,sep='')
        concerto$sessionPath <<- paste(tempPath,"/session_",sessionID,".Rs",sep='')
        concerto$mediaPath <<- mediaPath
        concerto$userIP <<- userIP

        if(!file.exists(concerto$templateFIFOPath)){
        }

        setwd(tempPath)
        print(paste("working directory set to:",tempPath))

        library(session)
        library(rjson)
        library(RMySQL)

        if(dbConnect) concerto$db$connect(user,password,dbName,host,port,dbTimezone)
    },

    workspace = list(
        get = function(workspaceID){
            return(paste(concerto$workspacePrefix,workspaceID,sep=''))
        }
    ),

    finalize = function(){
        print("finalizing...")

        closeAllConnections()

        concerto$updateAllReturnVariables()
        concerto$updateStatus(3)
        dbDisconnect(concerto$db$connection)
    },

    db = list(
        connect = function(user,password,dbName,host='localhost',port=3306,dbTimezone){
            print("connecting to database...")

            drv <- dbDriver('MySQL')

            con <- dbConnect(drv, user = user, password = password, dbname = dbName, host = host, port = port, client.flag=CLIENT_MULTI_STATEMENTS)
            dbSendQuery(con,statement = "SET NAMES 'utf8';")
            dbSendQuery(con,statement = paste("SET time_zone='",dbTimezone,"';",sep=''))

            concerto$db$connection <<- con
            concerto$db$name <<- dbName
        }
    ),

    table = list(
        get = function(tableID,workspaceID=concerto$workspaceID){
            dbName <- dbEscapeStrings(concerto$db$connection,concerto$workspace$get(workspaceID))
            tableID <- dbEscapeStrings(concerto$db$connection,toString(tableID))
            result <- dbSendQuery(concerto$db$connection,sprintf("SELECT `id`,`name` FROM `%s`.`Table` WHERE `id`='%s'",dbName,tableID))
            response <- fetch(result,n=-1)
            if(dim(response)[1]>0) response$table_name = paste("c3tbl_",tableID,sep='')
            return(response)
        }
    ),

    template = list(
        show = function(templateID,params=list(),timeLimit=0,finalize=F,workspaceID=concerto$workspaceID){
            print(paste("showing template #",workspaceID,":",templateID,"...",sep=''))
            if(!is.list(params)) stop("'params' must be a list!")

            template <- concerto$template$get(templateID,workspaceID=workspaceID)
            if(dim(template)[1]==0) stop(paste("Template #",workspaceID,":",templateID," not found!",sep=''))
            concerto$updateTemplateWorkspaceID(workspaceID)
            concerto$updateTemplateID(templateID)
            concerto$updateTimeLimit(timeLimit)

            concerto$updateHead(concerto$template$fillHTML(template[1,"head"],params))
            concerto$updateHTML(concerto$template$fillHTML(template[1,"HTML"],params))
            
            if(finalize){
                concerto$updateAllReturnVariables()
                concerto$updateRelease(1)
            }
            concerto$updateStatus(2)
            
            return(concerto$interpretResponse())
        },

        fillHTML = function(html,params=list()){
            matches <- unlist(regmatches(html,gregexpr("\\{\\{[^\\}\\}]*\\}\\}",html)))
            matches <- matches[!matches == '{{TIME_LEFT}}'] 
            while(length(matches)>0){
                index <- 1
                while(index<=length(matches)){
                    value <- gsub("\\{\\{","",matches[index])
                    value <- gsub("\\}\\}","",value)
                    if(!is.null(params[[value]])){
                        html <- gsub(matches[index],toString(params[[value]]),html, fixed=TRUE)
                    }
                    else {
                        html <- gsub(matches[index],"",html, fixed=TRUE)
                    }
                    index=index+1
                }
                matches <- unlist(regmatches(html,gregexpr("\\{\\{[^\\}\\}]*\\}\\}",html)))
                matches <- matches[!matches == '{{TIME_LEFT}}'] 
            }
            return(html)
        },

        get = function(templateID,workspaceID=concerto$workspaceID){
            dbName <- dbEscapeStrings(concerto$db$connection,concerto$workspace$get(workspaceID))
            templateID <- dbEscapeStrings(concerto$db$connection,toString(templateID))
            result <- dbSendQuery(concerto$db$connection,sprintf("SELECT `id`,`head`,`HTML` FROM `%s`.`Template` WHERE `id`='%s'",dbName,templateID))
            response <- fetch(result,n=-1)
            return(response)
        }
    ),

    test = list(
        get = function(testID,workspaceID=concerto$workspaceID){
            dbName <- dbEscapeStrings(concerto$db$connection,concerto$workspace$get(workspaceID))
            testID <- dbEscapeStrings(concerto$db$connection,toString(testID))
            result <- dbSendQuery(concerto$db$connection,sprintf("SELECT `id`,`code` FROM `%s`.`Test` WHERE `id`='%s'",dbName,testID))
            response <- fetch(result,n=-1)
            response$returnVariables <- concerto$test$getReturnVariables(testID,workspaceID=workspaceID)
            return(response)
        },

        getReturnVariables = function(testID,workspaceID=concerto$workspaceID){
            dbName <- dbEscapeStrings(concerto$db$connection,concerto$workspace$get(workspaceID))
            testID <- dbEscapeStrings(concerto$db$connection,toString(testID))
            result <- dbSendQuery(concerto$db$connection,sprintf("SELECT `name` FROM `%s`.`TestVariable` WHERE `Test_id`='%s' AND `type`=1",dbName,testID))
            response <- fetch(result,n=-1)

            result <- c()
            for(i in response){
                result <- c(result,i["name"])
            }
            return(result)
        },

        run = function(testID,params=list(),workspaceID=concerto$workspaceID){
            print(paste("running test #",workspaceID,":",testID,"...",sep=''))

            test <- concerto$test$get(testID,workspaceID=workspaceID)
            if(dim(test)[1]==0) stop(paste("Test #",workspaceID,":",testID," not found!",sep=''))

            for(param in ls(params)){
                assign(param,params[[param]])
            }

            eval(parse(text=test[1,"code"]))

            return <- list()
            for(ret in test$returnVariables){
                if(exists(ret)) return[[ret]] <- get(ret)
            }
            return(return)
        }
    ),

    serialize = function(){
        print("serializing session...")
        closeAllConnections()
        if(exists("onSerialize")) do.call("onSerialize",list(),envir=.GlobalEnv);
        save.session(concerto$sessionPath)
        concerto$updateStatus(7)
        dbDisconnect(concerto$db$connection)
        print("serialization finished")
        #stop("done")
        Sys.sleep(3600)
    },

    unserialize = function(){
        print("unserializing session...")
        restore.session(concerto$sessionPath)
    },

    updateReturnVariable = function(variable){
        if(exists(variable)) {
            dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
            sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
            value <- dbEscapeStrings(concerto$db$connection,toString(get(variable)))
            variable <- dbEscapeStrings(concerto$db$connection,toString(variable))
            value <- dbEscapeStrings(concerto$db$connection,toString(value))
            dbSendQuery(concerto$db$connection, statement = sprintf("REPLACE INTO `%s`.`TestSessionReturn` SET `TestSession_id` ='%s', `name`='%s', `value`='%s'",dbName,sessionID,variable, value))
        }
    },

    updateAllReturnVariables = function() {
        print("updating all return variables...")

        test <- concerto$test$get(concerto$testID)
        for(ret in test$returnVariables){
            concerto$updateReturnVariable(ret)
        }
    },

    updateHTML = function(html){
        dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
        sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
        html <- dbEscapeStrings(concerto$db$connection,toString(html))
        dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `HTML` = '%s' WHERE `id`=%s",dbName,html,sessionID))
    },

    updateHead = function(html){
        dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
        sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
        html <- dbEscapeStrings(concerto$db$connection,toString(html))
        dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `head` = '%s' WHERE `id`=%s",dbName,html,sessionID))
    },

    updateStatus = function(status) {
        dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
        sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
        status <- dbEscapeStrings(concerto$db$connection,toString(status))
        dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `status` = '%s' WHERE `id`=%s",dbName,status,sessionID))
    },

    updateRelease = function(release) {
        dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
        sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
        release <- dbEscapeStrings(concerto$db$connection,toString(release))
        dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `release` = '%s' WHERE `id`=%s",dbName,release,sessionID))
    },

    updateState = function() {
        dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
        sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
        state <- list()
        for(var in ls(envir=.GlobalEnv)){
            if(!is.function(get(var))) state[[var]] <- toString(get(var))
        }
        state <- rjson::toJSON(state)
        state <- dbEscapeStrings(concerto$db$connection,toString(state))
        result <- dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `state` = '%s' WHERE `id`=%s",dbName,state,sessionID))
    },
    
    updateTemplateWorkspaceID = function(workspaceID) {
        dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
        sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
        workspaceID <- dbEscapeStrings(concerto$db$connection,toString(workspaceID))
        dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `Template_UserWorkspace_id` = '%s' WHERE `id`=%s",dbName,workspaceID,sessionID))
    },

    updateTemplateID = function(templateID) {
        dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
        sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
        templateID <- dbEscapeStrings(concerto$db$connection,toString(templateID))
        dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `Template_id` = '%s' WHERE `id`=%s",dbName,templateID,sessionID))
    },

    updateTimeLimit = function(timeLimit) {
        dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
        sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
        timeLimit <- dbEscapeStrings(concerto$db$connection,toString(timeLimit))
        dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `time_limit` = '%s' WHERE `id`=%s",dbName,timeLimit,sessionID))
    },
    
    interpretResponse = function(){
      closeAllConnections()
      fifo_connection <- fifo(concerto$templateFIFOPath,"r",blocking=TRUE)
      response <- readLines(fifo_connection,warn=FALSE)
      closeAllConnections()
      if(response=="serialize"){
          concerto$serialize()
      } else if(response=="close") {
          stop("close command recieved")
      } else {
          response <- rjson::fromJSON(response)
      }
      return(response)
    },

    convertToNumeric = function(var){
        result <- tryCatch({
            if(is.character(var)) var <- as.numeric(var)
            return(var)
        }, warning = function(w) {
            return(var)
        }, error = function(e) {
            return(var)
        }, finally = function(){
            return(var)
        })
        return(result)
    },

    containsOrderedVector = function(subject, search){
        j = 1;
        for(i in subject){
            if(search[j]==i){
                if(length(search)==j) return(TRUE)
                j=j+1
            } else {
                j = 1
            }
        }
        return(FALSE)
    },

    qti = list(
        initialize = function(qtiID,params=list(),workspaceID=concerto$workspaceID){
            print(paste("initializing QTI #",workspaceID,":",qtiID,"...",sep=''))
            if(!is.list(params)) stop("'params' must be a list!")
            
            qti <- concerto$qti$get(qtiID,workspaceID=workspaceID)
            if(dim(qti)[1]==0) stop(paste("QTI #",workspaceID,":",qtiID," not found!",sep=''))
            
            #create 'result' list
            result <- list()
            eval(parse(text=qti[1,"ini_r_code"]))
            if(length(params)>0){
                for(i in ls(params)){
                    result[[i]] <- params[[i]]
                }
            }
            result$QTI_HTML <- concerto$template$fillHTML(result$QTI_HTML,result)
            return(result)
        },
        responseProcessing = function(qtiID,ini,userResponse,workspaceID=concerto$workspaceID){
            print(paste("response processing of QTI #",workspaceID,":",qtiID,"...",sep=''))
            if(!is.list(ini)) stop("'initialization variable' must be a list!")
            
            if(!is.list(userResponse)) stop("'user response variable' must be a list!")
            
            qti <- concerto$qti$get(qtiID,workspaceID=workspaceID)
            if(dim(qti)[1]==0) stop(paste("QTI #",workspaceID,":",qtiID," not found!",sep=''))
            
            result <- ini
            if(length(userResponse)>0){
                for(i in ls(userResponse)){
                    result[[i]] <- userResponse[[i]]
                }
            }
            eval(parse(text=qti[1,"response_proc_r_code"]))
            
            return(result)
        },
        get = function(qtiID,workspaceID=concerto$workspaceID){
            dbName <- dbEscapeStrings(concerto$db$connection,concerto$workspace$get(workspaceID))
            qtiID <- dbEscapeStrings(concerto$db$connection,toString(qtiID))
            result <- dbSendQuery(concerto$db$connection,sprintf("SELECT `id`,`name`,`ini_r_code`,`response_proc_r_code` FROM `%s`.`QTIAssessmentItem` WHERE `id`='%s'",dbName,qtiID))
            response <- fetch(result,n=-1)
            return(response)
        },
        mapResponse = function(variableName){
            variable <- get(variableName)
            mapEntry <- get(paste(variableName,".mapping.mapEntry",sep=''))
            defaultValue <- get(paste(variableName,".mapping.defaultValue",sep=''))

            result <- 0
            for(v in unique(variable)){
                v <- as.character(v)
                if(get(paste(variableName,".baseType",sep=""))=="pair"){
                    v2 = unlist(strsplit(v," "))
                    v2 = paste(v2[2]," ",v2[1],sep="")

                    if(!is.na(mapEntry[v])) result <- result + mapEntry[v]
                    else if(!is.na(mapEntry[v2])) result <- result + mapEntry[v2]
                    else result <- result + defaultValue
                } else {
                    if(!is.na(mapEntry[v])) result <- result + mapEntry[v]
                    else result <- result + defaultValue
                }
            }
            if(exists(paste(variableName,".mapping.lowerBound",sep=''))){
                lowerBound <- get(paste(variableName,".mapping.lowerBound",sep=''))
                if(result<lowerBound) result <- lowerBound
            }
            if(exists(paste(variableName,".mapping.upperBound",sep=''))){
                upperBound <- get(paste(variableName,".mapping.upperBound",sep=''))
                if(result>upperBound) result <- upperBound
            }
            return(result)
        },

        equal = function(arg1,arg2,baseType){
            if(length(arg1)!=length(arg2)) return(FALSE)
            if(baseType!='pair') return(all(arg1%in%arg2))
            i = 1
            for(a in arg1){
                v2 = unlist(strsplit(v," "))
                v2 = paste(v2[2]," ",v2[1],sep="")
                if(a != arg2[i] && v2 != arg2[i]) return(FALSE)
            }
            return(TRUE)
        },

        contains = function(exp1,exp2,baseType,cardinality){
            if(cardinality=='ordered') {
                if(baseType!='pair') {
                    concerto$containsOrderedVector(exp1,exp2) 
                } else {
                    j = 1;
                    for(i in exp1){
                        v2 = unlist(strsplit(i," "))
                        v2 = paste(v2[2]," ",v2[1],sep="")
                        if(exp2[j]==i || exp2[j]==v2){
                            if(length(exp2)==j) return(TRUE)
                            j=j+1
                        } else {
                            j = 1
                        }
                    }
                    return(FALSE)
                }
            } else {
                if(baseType!='pair') {
                    all(exp2 %in% exp1)
                } else {
                    for(i in exp2){
                        v2 = unlist(strsplit(i," "))
                        v2 = paste(v2[2]," ",v2[1],sep="")
                        if(!i%in%exp1 && !v2%in%exp1) return(FALSE)
                    }
                    return(TRUE)
                }
            }
        },

        delete = function(exp1,exp2,baseType){
            if(baseType!="pair") return((exp2)[which(exp2!=exp1)])
            result = c()
            for(i in exp2){
                if(concerto$qti$equal(i,exp1,"pair")) result = c(result,i)
            }
            return(result)
        }
    )
)