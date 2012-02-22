function UserType() { };
OModule.inheritance(UserType);

UserType.className="UserType";

UserType.reloadOnModification=true;
UserType.reloadHash="tnd_mainMenu-users";

UserType.onBeforeSave=function(){
    Methods.confirmUnsavedLost(function(){
        UserType.uiSave(true);
    });
}

UserType.onBeforeDelete=function(oid){
    Methods.confirmUnsavedLost(function(){
        UserType.uiDelete(oid,true);
    });
}

UserType.onAfterEdit=function()
{
    User.uiReload(User.currentID);
    $("#divUsersAccordion").accordion("resize");
};

UserType.onAfterList=function(){
}

UserType.onAfterChangeListLength=function(){
    $("#divUsersAccordion").accordion("resize");
};

UserType.getAddSaveObject=function()
{
    var rws = new Array();
    var ids = new Array();
    var values = new Array();
	
    $(".form"+this.className+"ModuleRights").each(function(index){
        var id = $(this).attr("id");
        id = id.substr(id.indexOf('_')+1);
        rws.push(id.substr(0,1));
        ids.push(id.substr(id.indexOf("_")+1));
        if($(this).is(":checkbox")) 
        {
            values.push($(this).is(":checked")?1:0);
        }
        else values.push($(this).val());
    });
    return { 
        "oid":this.currentID,
        "class_name":this.className,
        "name":$("#form"+this.className+"InputName").val(),
        "Sharing_id":$("#form"+this.className+"SelectSharing").val(),
        "rws[]":rws,
        "ids[]":ids,
        "values[]":values
    };
};

UserType.getFullSaveObject=function(){
    var obj = this.getAddSaveObject();
    if($("#form"+this.className+"SelectOwner").length==1) obj["Owner_id"]=$("#form"+this.className+"SelectOwner").val();
    return obj;
}
