concerto.template.show <-
function(templateID,params=list(),timeLimit=0,finalize=F,workspaceID=concerto$workspaceID, effectShow="default", effectShowOptions="default", effectHide="default",effectHideOptions="default"){
  print(paste("showing template #",workspaceID,":",templateID,"...",sep=''))
  if(!is.list(params)) stop("'params' must be a list!")
  
  template <- concerto.template.get(templateID,workspaceID=workspaceID)
  if(dim(template)[1]==0) stop(paste("Template #",workspaceID,":",templateID," not found!",sep=''))
  concerto:::concerto.updateTemplateWorkspaceID(workspaceID)
  concerto:::concerto.updateTemplateID(templateID)
  concerto:::concerto.updateTimeLimit(timeLimit)
  
  concerto:::concerto.updateHead(concerto.template.fillHTML(template[1,"head"],params))
  concerto:::concerto.updateHTML(concerto.template.fillHTML(template[1,"HTML"],params))

  if(effectShow=="default") {
    effectShow <- template[1,"effect_show"]
  }
  if(effectHide=="default") {
    effectHide <- template[1,"effect_hide"]
  }
  if(effectShowOptions=="default") {
    effectShowOptions <- template[1,"effect_show_options"]
  }
  if(effectHideOptions=="default") {
    effectHideOptions <- template[1,"effect_hide_options"]
  }
  concerto:::concerto.updateEffectShow(effectShow)  
  concerto:::concerto.updateEffectHide(effectHide)  
  concerto:::concerto.updateEffectShowOptions(effectShowOptions) 
  concerto:::concerto.updateEffectHideOptions(effectHideOptions)  
  
  if(finalize){
    concerto:::concerto.test.updateAllReturnVariables()
    concerto:::concerto.updateRelease(1)
  }
  concerto:::concerto.updateStatus(2)
  
  return(concerto:::concerto.interpretResponse())
}
