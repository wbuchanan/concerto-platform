concerto.template.loader <-
function(templateID,params=list(),workspaceID=concerto$workspaceID, effectShow="default", effectShowOptions="default", effectHide="default",effectHideOptions="default"){
  print(paste("setting loader template #",workspaceID,":",templateID,"...",sep=''))

  if(templateID==0){
    concerto:::concerto.updateLoaderHTML("")
    return
  }

  if(!is.list(params)) stop("'params' must be a list!")
  
  template <- concerto.template.get(templateID,workspaceID=workspaceID)
  if(dim(template)[1]==0) stop(paste("Template #",workspaceID,":",templateID," not found!",sep=''))
  concerto:::concerto.updateLoaderTemplateWorkspaceID(workspaceID)
  concerto:::concerto.updateLoaderTemplateID(templateID)
  
  concerto:::concerto.updateLoaderHead(concerto.template.fillHTML(template[1,"head"],params))
  concerto:::concerto.updateLoaderHTML(concerto.template.fillHTML(template[1,"HTML"],params))

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
  concerto:::concerto.updateLoaderEffectShow(effectShow)  
  concerto:::concerto.updateLoaderEffectHide(effectHide)  
  concerto:::concerto.updateLoaderEffectShowOptions(effectShowOptions) 
  concerto:::concerto.updateLoaderEffectHideOptions(effectHideOptions)  
}
