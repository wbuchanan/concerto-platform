concerto.table.query <-
function(sql){
  result <- dbSendQuery(concerto$db$connection,sql)
  response <- fetch(result,n=-1)
  return(response)
}
