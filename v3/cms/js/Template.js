function Template() { };
OModule.inheritance(Template);

Template.className="Template";

Template.onAfterEdit=function()
{
    };

Template.onAfterSave=function()
{
    Test.uiTemplatesChanged();
};

Template.onAfterDelete=function(){
    Test.uiTemplatesChanged();
}

Template.onAfterAdd=function(){
    $("#divAddFormDialog").dialog("option","width",800);
    $("#divAddFormDialog").dialog("option","position","center"); 
}

Template.getAddSaveObject=function()
{
    return { 
        oid:this.currentID,
        class_name:this.className,
        name:$("#form"+this.className+"InputName").val(),
        HTML:Methods.getCKEditorData("#form"+this.className+"TextareaHTML"),
        Sharing_id:$("#form"+this.className+"SelectSharing").val()
    };
};

Template.getFullSaveObject = function(){
    var obj = this.getAddSaveObject();
    if($("#form"+this.className+"SelectOwner").length==1) obj["Owner_id"]=$("#form"+this.className+"SelectOwner").val();
    return obj;
}