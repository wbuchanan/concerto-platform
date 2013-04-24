concerto.updateLoaderTemplateID <-
function(loaderTemplateID) {
  dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
  sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
  loaderTemplateID <- dbEscapeStrings(concerto$db$connection,toString(loaderTemplateID))
  dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `loader_Template_id` = '%s' WHERE `id`=%s",dbName,loaderTemplateID,sessionID))
}
