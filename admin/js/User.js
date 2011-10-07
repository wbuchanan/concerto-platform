/*
  Concerto Testing Platform,
  Web based adaptive testing platform utilizing R language for computing purposes.

  Copyright (C) 2011  Psychometrics Centre, Cambridge University

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

function User() { };
OModule.inheritance(User);

User.className="User";
User.sessionID="";

User.captionIncorrectLogin="login/password combination is incorrect!";
User.captionDelete="Are you sure you want to delete user";
User.captionPasswordsMismatch="Password and password confirmation doesn't match!";

User.extraDeleteCallback=function() { };

User.extraSaveCallback=function() 
{
    if(this.currentID!=0) 
    {
        Group.uiReload(Group.currentID);
    }
    else 
    {
        Group.uiEdit(Group.currentID);
    }
};

User.getSaveObject=function()
{
    return { 
        oid:this.currentID,
        class_name:this.className,
        login:$("#form"+this.className+"InputLogin").val(),
        firstname:$("#form"+this.className+"InputFirstname").val(),
        lastname:$("#form"+this.className+"InputLastname").val(),
        email:$("#form"+this.className+"InputEmail").val(),
        phone:$("#form"+this.className+"InputPhone").val(),
        Group_id:$("#form"+this.className+"SelectGroup").val(),
        modify_password:$("#form"+this.className+"CheckboxPassword").is(":checked")?1:0,
        password:$("#form"+this.className+"InputPassword").val(),
        superadmin:$("#form"+this.className+"CheckboxSuperadmin").is(":checked")?1:0
    };
};

User.uiFormNotValidated=function()
{
    var result;
    
    //fields
    var login = $("#form"+this.className+"InputLogin").val();
    var password = $("#form"+this.className+"InputPassword").val();
    var confirmation = $("#form"+this.className+"InputPasswordConf").val();
    var password_mod = $("#form"+this.className+"CheckboxPassword").is(":checked");
    
    //required fields
    if(jQuery.trim(login)=="") return Methods.captionRequiredFields;
    if(User.currentID==0)
    {
        if(jQuery.trim(password)=="") return Methods.captionRequiredFields;         
    }
    
    if(password_mod&&password!=confirmation)
    {
        result = User.captionPasswordsMismatch;
        return result;
    }
    
    return false;
};

User.uiLogOut=function()
{
    $.post("query/log_out.php",{},
        function(data){
            location.href="index.php";
        });
};

User.uiLogin=function()
{
    $.post(
        'query/login.php',
        {
            login:$('#login').val(), 
            password:$('#password').val()
        },
        function(data)
        {
            if(data.success=="1") location.reload(true);
            else Methods.alert(User.captionIncorrectLogin,"alert");
        },
        "json");		
};