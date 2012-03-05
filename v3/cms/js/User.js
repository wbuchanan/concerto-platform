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

function User() { };
OModule.inheritance(User);

User.className="User";
User.sessionID="";
User.reloadOnModification=true;
User.reloadHash="tnd_mainMenu-users";

User.onBeforeSave=function(){
    Methods.confirmUnsavedLost(function(){
        User.uiSave(true);
    });
}

User.onBeforeDelete=function(oid){
    Methods.confirmUnsavedLost(function(){
        User.uiDelete(oid,true);
    });
}

User.onAfterEdit=function() {
    $("#divUsersAccordion").accordion("resize");
};

User.onAfterChangeListLength=function(){
    $("#divUsersAccordion").accordion("resize");
};

User.onAfterList=function(){
    }

User.getAddSaveObject=function()
{
    return { 
        oid:this.currentID,
        class_name:this.className,
        login:$("#form"+this.className+"InputLogin").val(),
        firstname:$("#form"+this.className+"InputFirstname").val(),
        lastname:$("#form"+this.className+"InputLastname").val(),
        email:$("#form"+this.className+"InputEmail").val(),
        phone:$("#form"+this.className+"InputPhone").val(),
        UserGroup_id:$("#form"+this.className+"SelectUserGroup").val(),
        modify_password:$("#form"+this.className+"CheckboxPassword").is(":checked")?1:0,
        password:$("#form"+this.className+"InputPassword").val(),
        UserType_id:$("#form"+this.className+"SelectUserType").val()
    };
};

User.getFullSaveObject=function()
{
    var obj = this.getAddSaveObject();
    
    return obj;
}

User.uiFormNotValidated=function()
{
    var result;
    if($("#form"+this.className+"CheckboxPassword").is(":checked")&&$("#form"+this.className+"InputPassword").val()!=$("#form"+this.className+"InputPasswordConf").val())
    {
        result = dictionary["s66"];
        return result;
    }
    return false;
};

User.uiLogIn=function()
{
    var thisClass=this;
    $("#dd_login").parent().mask(dictionary["s319"]);
    $.post("query/log_in.php",
    {
        login:$("#dd_login_inp_login").val(),
        password:$("#dd_login_inp_password").val()
    },
    function(data){
        $("#dd_login").parent().unmask();
        if(data.success==1)
        {
            $("#dd_login").dialog("close");
            Methods.modalLoading();
            $.post("view/layout.php",{},
                function(data){
                    Methods.stopModalLoading();
                    $("#content").html(data);
                });
        }
        else Methods.alert(dictionary["s67"],"alert");
    },"json");
};

User.uiLogOut=function()
{
    Methods.modalLoading();
    $.post("query/log_out.php",{},
        function(data){
            location.href="index.php";
        });
};