concerto.test.getReturnVariables <-
function(testID,workspaceID=concerto$workspaceID){
  dbName <- dbEscapeStrings(concerto$db$connection,concerto.workspace.get(workspaceID))
  testID <- dbEscapeStrings(concerto$db$connection,toString(testID))
  result <- dbSendQuery(concerto$db$connection,sprintf("SELECT `name` FROM `%s`.`TestVariable` WHERE `Test_id`='%s' AND `type`=1",dbName,testID))
  response <- fetch(result,n=-1)
  
  result <- c()
  for(i in response){
    result <- c(result,i["name"])
  }
  return(result)
}
