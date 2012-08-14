function QTI(){};

QTI.maxChoices=function(tsid,obj,name,maxChoices){
    if($(".QTIItemBody_"+tsid+" input:checkbox:checked[name='"+name+"']").length>maxChoices) $(obj).attr("checked",false);
}

QTI.initializeOrdered = function(tsid){
    $('.QTIItemBody_'+tsid+' .QTIOrderedContainer').sortable();
}

QTI.initializeAssociation=function(tsid,name,maxAssociations){
    $('.QTIItemBody_'+tsid+' .QTIAssociateDraggable').draggable({
        revert:'invalid'
    });
    $('.QTIItemBody_'+tsid+' .QTIAsssociateAddAssociationDropable').droppable({
        drop:function(event,ui){
            QTI.addAssociation(tsid,name,ui.draggable,maxAssociations)
        }
    });
    $('.QTIItemBody_'+tsid+' .QTIAsssociateRemoveAssociationDropable').droppable({
        drop:function(event,ui){
            QTI.removeAssociation(tsid,name,ui.draggable)
        }
    });
}
QTI.refreshAssociationOptions = function(tsid, name){
    if($(".QTIItemBody_"+tsid+" .QTIDraggableOptionsContainer").children().length==0) $(".QTIItemBody_"+tsid+" .QTIDraggableOptionsContainer").hide(0);
    else $(".QTIItemBody_"+tsid+" .QTIDraggableOptionsContainer").show(0);
}
QTI.refreshAssociations = function(tsid, name){
    $(".QTIItemBody_"+tsid+" .QTIAssociation").each(function(){
        if($(this).html()=="") {
            $(this).remove();
            return;
        }
        $(this).children("input").remove();
        if($(this).children(".QTIItemBody_"+tsid+" .QTIAssociateDraggable").length==2){
            var value = "";
            $(this).children(".QTIItemBody_"+tsid+" .QTIAssociateDraggable").each(function(){
                if(value!="") value+=" ";
                value+=$(this).attr("identifier");
            });
            $(this).append("<input type='hidden' name='"+name+"' value='"+value+"' />");
        }
    });
}
QTI.removeAssociation=function(tsid, name,obj){
    obj.css("top","0px");
    obj.css("left","0px");
    $(".QTIItemBody_"+tsid+" .QTIDraggableOptionsContainer").append(obj);
    QTI.refreshAssociations(tsid, name);
    QTI.refreshAssociationOptions(tsid, name);
}
QTI.addAssociation=function(tsid, name,obj,maxAssociations){
    var currentAssociations = $('.QTIItemBody_'+tsid+' .QTIAssociation').length;
    obj.css("top","0px");
    obj.css("left","0px");
    if(currentAssociations<maxAssociations || maxAssociations==0){
        var identifier = obj.attr("identifier");
        if(obj.attr("matchmax")==0 || obj.attr("matchmax")>$(".QTIItemBody_"+tsid+" .QTIAssociateDraggable[identifier='"+identifier+"']").length) obj = obj.clone();
        $("<div class='QTIAssociation QTIDisplayTable'></div>").append(obj).appendTo($(".QTIItemBody_"+tsid+" .QTIAssociationsContainer"));
    }
    $('.QTIItemBody_'+tsid+' .QTIAssociateDraggable').draggable({
        revert:'invalid'
    });
    $(".QTIItemBody_"+tsid+" .QTIAssociation").droppable({
        drop:function(event,ui){
            ui.draggable.css("top","0px");
            ui.draggable.css("left","0px");
            if($(this).children("div").length==2) return;
            if(ui.draggable.attr("matchGroup")!=undefined && ui.draggable.attr("matchGroup")!="" && $(this).children("div").length==1) {
                var groups = ui.draggable.attr("matchGroup").split(" ");
                var otherIdentifier = $(this).children("div").attr("identifier");
                var found = false;
                for(var i=0;i<groups.length;i++){
                    if(otherIdentifier == groups[i]){
                        found = true;
                        break;
                    }
                }
                if(!found) return;
            }
            $(this).append(ui.draggable);
            
            QTI.refreshAssociations(tsid, name);
            QTI.refreshAssociationOptions(tsid, name);
        }
    });
    QTI.refreshAssociations(tsid, name);
    QTI.refreshAssociationOptions(tsid, name);
}

QTI.matchInteractionCheck=function(tsid, name,obj,maxAssociations){
    var hi = $(obj).attr("hi");
    var vi = $(obj).attr("vi");
    var hmm = $(obj).attr("hmm");
    var vmm = $(obj).attr("vmm");
    if($(".QTIItemBody_"+tsid+" .QTImatchInteractionCheckbox:checked[name='"+name+"']").length>maxAssociations) $(obj).attr("checked",false);
    if($(".QTIItemBody_"+tsid+" .QTImatchInteractionCheckbox:checked[name='"+name+"'][hi='"+hi+"']").length>hmm) $(obj).attr("checked",false);
    if($(".QTIItemBody_"+tsid+" .QTImatchInteractionCheckbox:checked[name='"+name+"'][vi='"+vi+"']").length>vmm) $(obj).attr("checked",false);
}

QTI.gapMatchInteractionCheck=function(tsid, name,obj){
    var hi = $(obj).attr("hi");
    var vi = $(obj).attr("vi");
    var hmm = $(obj).attr("hmm");
    if($(".QTIItemBody_"+tsid+" .QTIgapMatchInteractionCheckbox:checked[name='"+name+"'][hi='"+hi+"']").length>hmm) $(obj).attr("checked",false);
    if($(".QTIItemBody_"+tsid+" .QTIgapMatchInteractionCheckbox:checked[name='"+name+"'][vi='"+vi+"']").length>1) $(obj).attr("checked",false);
    
    var elem = $(".QTIItemBody_"+tsid+" font[identifier='"+vi+"']");
    var content = $(".QTIItemBody_"+tsid+" .choiceContent_"+hi).html();
    if($(obj).is(":checked")) elem.html(content);
    else {
        if($(".QTIItemBody_"+tsid+" .QTIgapMatchInteractionCheckbox:checked[name='"+name+"'][vi='"+vi+"']").length==0) elem.html("___"+vi+"___");
    }
}