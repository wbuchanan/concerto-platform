concerto.test.getReturnVariables <-
function(testID,workspaceID=concerto$workspaceID){
  dbName <- dbEscapeStrings(concerto$db$connection,concerto.workspace.get(workspaceID))

  if(!is.numeric(testID)) {
    stop("testID must be of numeric type")
  }

  testID <- dbEscapeStrings(concerto$db$connection,toString(testID))
  result <- dbSendQuery(concerto$db$connection,sprintf("SELECT `name` FROM `%s`.`TestVariable` WHERE `Test_id`='%s' AND `type`=1",dbName,testID))
  response <- fetch(result,n=-1)
  
  result <- c()
  for(i in dim(response)[1]){
    result <- c(result,response[i,"name"])
  }
  return(result)
}
