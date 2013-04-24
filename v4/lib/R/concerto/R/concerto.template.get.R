concerto.template.get <-
function(templateID,workspaceID=concerto$workspaceID){
  dbName <- dbEscapeStrings(concerto$db$connection,concerto.workspace.get(workspaceID))

  objField <- "id"
  if(is.character(templateID)){
    objField <- "name"
  }

  templateID <- dbEscapeStrings(concerto$db$connection,toString(templateID))
  result <- dbSendQuery(concerto$db$connection,sprintf("SELECT `id`,`name`,`head`,`HTML` FROM `%s`.`Template` WHERE `%s`='%s'",dbName,objField,templateID))
  response <- fetch(result,n=-1)
  return(response)
}
