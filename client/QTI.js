function QTI(){};

QTI.maxChoices=function(obj,name,maxChoices){
    if($("input:checkbox:checked[name='"+name+"']").length>maxChoices) $(obj).attr("checked",false);
}