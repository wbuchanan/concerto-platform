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

test = null;

function Test() { };
OModule.inheritance(Test);

Test.className="Test";

Test.widgetTypes = {
    table:1,
    template:2,
    test:4,
    QTI:5
}

Test.onAfterEdit=function()
{
    };

Test.onAfterImport=function(){
    Template.uiList();
    Table.uiList();
    QTIAssessmentItem.uiList();
    
    Test.uiTestsChanged();
    Test.uiTablesChanged();
    Test.uiTemplatesChanged();
    Test.uiQTIAssessmentItemsChanged();
}

Test.onAfterAdd=function(){
    }

Test.onAfterSave=function()
{
    };

Test.onAfterDelete=function(){
    Test.uiTestsChanged();
}

Test.getAddSaveObject=function()
{
    return { 
        oid:this.currentID,
        class_name:this.className,
        name:$("#form"+this.className+"InputName").val(),
        open:$("#form"+this.className+"CheckboxOpen").is(":checked")?1:0,
        Sharing_id:$("#form"+this.className+"SelectSharing").val()
    };
};

Test.getFullSaveObject = function() {
    var obj = this.getAddSaveObject();
    obj["parameters"]=Test.getSerializedParameterVariables();
    obj["returns"]=Test.getSerializedReturnVariables();
    obj["description"]=$("#form"+this.className+"TextareaDescription").val();
    obj["loader_Template_id"]=$("#selectLoaderTemplate").val();
    obj["code"]=$("#textareaTestLogic").val();
    if($("#form"+this.className+"SelectOwner").length==1) obj["Owner_id"]=$("#form"+this.className+"SelectOwner").val();
    return obj;
}

Test.uiSaveValidate=function(ignoreOnBefore,isNew){
    if(!this.checkRequiredFields([
        $("#form"+this.className+"InputName").val()
        ])) {
        Methods.alert(dictionary["s415"],"alert");
        return false;
    }
    Test.uiSaveValidated(ignoreOnBefore,isNew);
}

Test.codeMirrors = new Array();
Test.uiRefreshCodeMirrors=function(){
    for(var i=0;i<Test.codeMirrors.length;i++){
        try{
            Test.codeMirrors[i].refresh();
        }
        catch(err){
            
        }
    }
}

Test.uiGoToRelatedObject=function(type,oid){
    if(oid==0) return;
    switch(type){
        //templates
        case Test.widgetTypes.template:{
            $("#tnd_mainMenu").tabs("select","#tnd_mainMenu-templates");
            Template.uiEdit(oid);
            break;
        }
        //tables
        case Test.widgetTypes.table:{
            $("#tnd_mainMenu").tabs("select","#tnd_mainMenu-tables");
            Table.uiEdit(oid);
            break;
        }
        //tests
        case Test.widgetTypes.test:{
            Test.uiEdit(oid);
            break;
        }
        //QTI
        case Test.widgetTypes.QTIInitialization:{
            $("#tnd_mainMenu").tabs("select","#tnd_mainMenu-QTI");
            QTIAssessmentItem.uiEdit(oid);
            break;
        }
    }
}

Test.uiTemplatesChanged=function(){
    Test.uiRefreshLoader($("#selectLoaderTemplate").val());
}

Test.uiTestsChanged=function(){
    }

Test.uiQTIAssessmentItemsChanged=function(){
    }

Test.uiTablesChanged=function(){
    }

Test.variableValidation=function(value,special){
    if(special == null) special = true;
    var oldValue = value;
    var newValue = Test.convertVariable(oldValue,special);
    if(oldValue!=newValue) return false;
    else return true;
}

Test.convertVariable=function(value,special){
    if(special == null) special = true;
    if(special) {
        value = value.replace(/[^A-Z^a-z^0-9^\.^_]/gi,"");
        value = value.replace(/\.{2,}/gi,".");
    }
    else value = value.replace(/[^A-Z^a-z^0-9^_]/gi,"");
    value = value.replace(/^([^A-Z^a-z]{1,})*/gi,"");
    value = value.replace(/([^A-Z^a-z^0-9]{1,})$/gi,"");
    return value;
}

Test.getReturnVars=function(){
    var vars = new Array();
    $(".inputReturnVar").each(function(){
        var v = {
            name:$(this).val(),
            section:[Test.sectionDivToObject($(this).parents(".divSection"))],
            type:["return"]
        };
        var exists = false;
        for(var i=0;i<vars.length;i++){
            if(v.name==vars[i].name){
                vars[i].section = vars[i].section.concat(v.section);
                vars[i].type = vars[i].type.concat(v.type);
                exists = true;
                break;
            }
        }
        if(!exists){
            vars.push(v);
        }
    });
    return vars;
};

Test.getParameterVars=function(){
    var vars = new Array();
    $(".inputParameterVar").each(function(){
        var v = {
            name:$(this).val(),
            section:[Test.sectionDivToObject($(this).parents(".divSection"))],
            type:["parameter"]
        };
        var exists = false;
        for(var i=0;i<vars.length;i++){
            if(v.name==vars[i].name){
                vars[i].section = vars[i].section.concat(v.section);
                vars[i].type = vars[i].type.concat(v.type);
                exists = true;
                break;
            }
        }
        if(!exists){
            vars.push(v);
        }
    });
    return vars;
};

Test.uiVarNameChanged=function(obj){
    if(obj!=null){
        var oldValue = obj.val();
        if(!Test.variableValidation(oldValue)){
            var newValue = Test.convertVariable(oldValue);
            obj.val(newValue);
            Methods.alert(dictionary["s1"].format(oldValue,newValue), "info", dictionary["s2"]);
        }
    }
};

Test.uiAddParameter=function(){
    var vars = this.getSerializedParameterVariables();
    var v = {
        name:"",
        description:""
    };
    vars.push($.toJSON(v));
    this.uiRefreshVariables(vars,null);
};

Test.uiRemoveParameter=function(index){
    var vars = this.getSerializedParameterVariables();
    vars.splice(index,1);
    this.uiRefreshVariables(vars,null);
};

Test.uiAddReturn=function(){
    var vars = this.getSerializedReturnVariables();
    var v = {
        name:"",
        description:""
    };
    vars.push($.toJSON(v));
    this.uiRefreshVariables(null,vars);
};

Test.uiRemoveReturn=function(index){
    var vars = this.getSerializedReturnVariables();
    vars.splice(index,1);
    this.uiRefreshVariables(null,vars);
};

Test.getSerializedParameterVariables=function(){
    var vars = new Array();
    $(".table"+this.className+"Parameters tr").each(function(){
        var v = {};
        v["name"]=$(this).find("input").val();
        v["description"]=$(this).find("textarea").val();
        vars.push($.toJSON(v));
    });
    return vars;
}

Test.getSerializedReturnVariables=function(){
    var vars = new Array();
    $(".table"+this.className+"Returns tr").each(function(){
        var v = {};
        v["name"]=$(this).find("input").val();
        v["description"]=$(this).find("textarea").val();
        vars.push($.toJSON(v));
    });
    return vars;
}

Test.uiRefreshVariables=function(parameters,returns){
    if(parameters==null) parameters=this.getSerializedParameterVariables();
    if(returns==null) returns = this.getSerializedReturnVariables();
    
    $("#div"+Test.className+"Variables").mask(dictionary["s319"]);
    $.post("view/Test_variables.php",{
        oid:this.currentID,
        class_name:this.className,
        parameters:parameters,
        returns:returns
    },function(data){
        $("#div"+Test.className+"Variables").unmask();
        $("#div"+Test.className+"Variables").html(data);
    })
}

Test.uiRefreshLoader=function(oid){
    
    $("#div"+Test.className+"Loader").mask(dictionary["s319"]);
    $.post("view/Test_loader.php",{
        oid:this.currentID,
        class_name:this.className,
        loader:oid
    },function(data){
        $("#div"+Test.className+"Loader").unmask();
        $("#div"+Test.className+"Loader").html(data);
    })
}

Test.onScroll=function(){
    if($(window).scrollTop()>$("#divTestResponse").offset().top){
        $(".divTestVerticalElement").css("position","fixed");        
                
        $(".divTestVerticalElement:eq(0)").css("top","0px");
        $(".divTestVerticalElement:eq(1)").css("top",$(".divTestVerticalElement:eq(1)").css("height"));
                
    } else {
        $(".divTestVerticalElement").css("position","relative");
        $(".divTestVerticalElement").css("top","auto");
    }
}