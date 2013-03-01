concerto.test.get <-
function(testID,workspaceID=concerto$workspaceID){
  dbName <- dbEscapeStrings(concerto$db$connection,concerto.workspace.get(workspaceID))
  testID <- dbEscapeStrings(concerto$db$connection,toString(testID))
  result <- dbSendQuery(concerto$db$connection,sprintf("SELECT `id`,`name`,`code` FROM `%s`.`Test` WHERE `id`='%s'",dbName,testID))
  response <- fetch(result,n=-1)
  response$returnVariables <- concerto:::concerto.test.getReturnVariables(testID,workspaceID=workspaceID)
  return(response)
}
