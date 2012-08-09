function QTI(){};

QTI.maxChoices=function(obj,name,maxChoices){
    if($("input:checkbox:checked[name='"+name+"']").length>maxChoices) $(obj).attr("checked",false);
}

QTI.initializeAssociation=function(name,maxAssociations){
    $('.QTIAssociateDraggable').draggable({
        revert:'invalid'
    });
    $('#QTIAsssociateAddAssociationDropable').droppable({
        drop:function(event,ui){
            QTI.addAssociation(name,ui.draggable,maxAssociations)
        }
    });
    $('#QTIAsssociateRemoveAssociationDropable').droppable({
        drop:function(event,ui){
            QTI.removeAssociation(name,ui.draggable)
        }
    });
}
QTI.refreshAssociationOptions = function(name){
    if($(".QTIDraggableOptionsContainer").children().length==0) $(".QTIDraggableOptionsContainer").hide(0);
    else $(".QTIDraggableOptionsContainer").show(0);
}
QTI.refreshAssociations = function(name){
    $(".QTIAssociation").each(function(){
        if($(this).html()=="") {
            $(this).remove();
            return;
        }
        $(this).children("input").remove();
        if($(this).children(".QTIAssociateDraggable").length==2){
            var value = "";
            $(this).children(".QTIAssociateDraggable").each(function(){
                if(value!="") value+=" ";
                value+=$(this).attr("identifier");
            });
            $(this).append("<input type='hidden' name='"+name+"' value='"+value+"' />");
        }
    });
}
QTI.removeAssociation=function(name,obj){
    obj.css("top","0px");
    obj.css("left","0px");
    $(".QTIDraggableOptionsContainer").append(obj);
    QTI.refreshAssociations(name);
    QTI.refreshAssociationOptions(name);
}
QTI.addAssociation=function(name,obj,maxAssociations){
    var currentAssociations = $('.QTIAssociation').length;
    obj.css("top","0px");
    obj.css("left","0px");
    if(currentAssociations<maxAssociations || maxAssociations==0){
        var identifier = obj.attr("identifier");
        if(obj.attr("matchmax")==0 || obj.attr("matchmax")>$(".QTIAssociateDraggable[identifier='"+identifier+"']").length) obj = obj.clone();
        $("<div class='QTIAssociation QTIDisplayTable'></div>").append(obj).appendTo($(".QTIAssociationsContainer"));
    }
    $('.QTIAssociateDraggable').draggable({
        revert:'invalid'
    });
    $(".QTIAssociation").droppable({
        drop:function(event,ui){
            ui.draggable.css("top","0px");
            ui.draggable.css("left","0px");
            if($(this).children("div").length==2) return;
            $(this).append(ui.draggable);
            
            QTI.refreshAssociations(name);
            QTI.refreshAssociationOptions(name);
        }
    });
    QTI.refreshAssociations(name);
    QTI.refreshAssociationOptions(name);
}