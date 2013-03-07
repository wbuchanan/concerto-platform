concerto.table.query <-
function(sql,params=list()){
  sql <- concerto.table.fillSQL(sql,params)
  result <- dbSendQuery(concerto$db$connection,sql)
  response <- fetch(result,n=-1)
  return(response)
}
