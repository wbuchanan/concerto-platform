/*
Concerto Platform - Online Adaptive Testing Platform
Copyright (C) 2011-2012, The Psychometrics Centre, Cambridge University

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; version 2
of the License, and not any of the later versions.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

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