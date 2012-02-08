function UserGroup() { };
OModule.inheritance(UserGroup);

UserGroup.className="UserGroup";

UserGroup.onAfterEdit=function()
{
    $("#divUsersAccordion").accordion("resize");
};

UserGroup.onAfterList=function(){
}

UserGroup.onAfterChangeListLength=function(){
    $("#divUsersAccordion").accordion("resize");
};

UserGroup.onAfterSave=function()
{
    if(this.currentID!=0 && User.currentID!=0) User.uiReload(User.currentID);
    if(this.currentID==0 && User.currentID!=0) User.uiEdit(User.currentID);
    if(this.currentID!=0 && User.currentID==0) User.uiList();
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

UserGroup.onAfterDelete=function(){
    if(this.currentID!=0 && User.currentID!=0) User.uiReload(User.currentID);
    if(this.currentID==0 && User.currentID!=0) User.uiEdit(User.currentID);
    if(this.currentID!=0 && User.currentID==0) User.uiList();
}

UserGroup.getFullSaveObject=function()
{
    var obj = this.getAddSaveObject();
    if($("#form"+this.className+"SelectOwner").length==1) obj["Owner_id"]=$("#form"+this.className+"SelectOwner").val();
    return obj;
}