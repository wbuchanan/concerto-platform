concerto.qti.get <-
function(qtiID,workspaceID=concerto$workspaceID){
  dbName <- dbEscapeStrings(concerto$db$connection,concerto.workspace.get(workspaceID))
  qtiID <- dbEscapeStrings(concerto$db$connection,toString(qtiID))
  result <- dbSendQuery(concerto$db$connection,sprintf("SELECT `id`,`name`,`ini_r_code`,`response_proc_r_code` FROM `%s`.`QTIAssessmentItem` WHERE `id`='%s'",dbName,qtiID))
  response <- fetch(result,n=-1)
  return(response)
}
