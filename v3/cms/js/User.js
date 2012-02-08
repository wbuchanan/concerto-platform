function User() { };
OModule.inheritance(User);

User.className="User";
User.sessionID="";

User.onAfterEdit=function() {
    $("#divUsersAccordion").accordion("resize");
};

User.onAfterChangeListLength=function(){
    $("#divUsersAccordion").accordion("resize");
};

User.onAfterSave=function() 
{
    UserType.uiList();
    UserGroup.uiList();
    Template.uiList();
    Test.uiList();
    Table.uiList();
};

User.onAfterDelete=function(){
    UserType.uiList();
    UserGroup.uiList();
    Template.uiList();
    Test.uiList();
    Table.uiList();
}

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
    $.post("query/log_in.php",
    {
        login:$("#dd_login_inp_login").val(),
        password:$("#dd_login_inp_password").val()
    },
    function(data){
        if(data.success==1)
        {
            $("#dd_login").dialog("close");
            $.post("view/layout.php",{},
                function(data){
                    $("#content").html(data);
                });
        }
        else Methods.alert(dictionary["s67"],"alert");
    },"json");
};

User.uiLogOut=function()
{
    $.post("query/log_out.php",{},
        function(data){
            location.href="index.php";
        });
};