function UserGroup() { };
OModule.inheritance(UserGroup);

UserGroup.className="UserGroup";

UserGroup.reloadOnModification=true;
UserGroup.reloadHash="tnd_mainMenu-users";

UserGroup.onBeforeSave=function(){
    Methods.confirmUnsavedLost(function(){
        UserGroup.uiSave(true);
    });
}

UserGroup.onBeforeDelete=function(oid){
    Methods.confirmUnsavedLost(function(){
        UserGroup.uiDelete(oid,true);
    });
}

UserGroup.onAfterEdit=function()
{
    $("#divUsersAccordion").accordion("resize");
};

UserGroup.onAfterList=function(){
    }

UserGroup.onAfterChangeListLength=function(){
    $("#divUsersAccordion").accordion("resize");
};

UserGroup.getAddSaveObject=function()
{
    return { 
        oid:this.currentID,
        class_name:this.className,
        name:$("#form"+this.className+"InputName").val(),
        Sharing_id:$("#form"+this.className+"SelectSharing").val()
    };
};

UserGroup.getFullSaveObject=function()
{
    var obj = this.getAddSaveObject();
    if($("#form"+this.className+"SelectOwner").length==1) obj["Owner_id"]=$("#form"+this.className+"SelectOwner").val();
    return obj;
}