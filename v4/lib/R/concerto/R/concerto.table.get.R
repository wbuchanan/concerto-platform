concerto.table.get <-
function(tableID,workspaceID=concerto$workspaceID){
  dbName <- dbEscapeStrings(concerto$db$connection,concerto.workspace.get(workspaceID))
  tableID <- dbEscapeStrings(concerto$db$connection,toString(tableID))
  result <- dbSendQuery(concerto$db$connection,sprintf("SELECT `id`,`name` FROM `%s`.`Table` WHERE `id`='%s'",dbName,tableID))
  response <- fetch(result,n=-1)
  return(response)
}
