function CustomSection() { };
OModule.inheritance(CustomSection);

CustomSection.className="CustomSection";

CustomSection.onAfterEdit=function()
{
    };

CustomSection.onAfterSave=function()
{
    Test.uiCustomSectionsChanged();
};

CustomSection.onAfterAdd=function(){
    Methods.iniCKEditor("#form"+this.className+"TextareaDescription");
}

CustomSection.getAddSaveObject=function()
{
    return { 
        oid:this.currentID,
        class_name:this.className,
        name:$("#form"+this.className+"InputName").val(),
        description:Methods.getCKEditorData("#form"+this.className+"TextareaDescription"),
        Sharing_id:$("#form"+this.className+"SelectSharing").val()
    };
};

CustomSection.onAfterDelete=function(){
    Test.uiCustomSectionsChanged();
}

CustomSection.uiVarNameChanged=function(obj){
    if(obj!=null){
        var oldValue = obj.val();
        if(!Test.variableValidation(oldValue)){
            var newValue = Test.convertVariable(oldValue);
            obj.val(newValue);
            Methods.alert(dictionary["s1"].format(oldValue,newValue), "info", dictionary["s2"]);
        }
    }
    
    CustomSection.uiRefreshComboboxes();
};

CustomSection.uiRefreshComboboxes=function(){
    var vars = new Array();
    $(".comboboxCustomSectionVars").each(function(){
        var value = $(this).val();
        if(value!="" && vars.indexOf(value)==-1){
            vars.push(value);
        }
    });
    vars = vars.sort();
    
    $(".comboboxCustomSectionVars").each(function(){
        var value = $(this).val();
        var source = vars;
        $(this).autocomplete({
            source: source,
            minLength:0
        }).click(function(){
            $(this).autocomplete("search",'');
        });
        $(this).val(value);
    });
}

CustomSection.getSerializedParameterVariables=function(){
    var vars = new Array();
    $(".div"+this.className+"Parameters table").each(function(){
        var v = {};
        v["name"]=$(this).find("input").val();
        v["description"]=$(this).find("textarea").val();
        vars.push($.toJSON(v));
    });
    return vars;
}

CustomSection.getSerializedReturnVariables=function(){
    var vars = new Array();
    $(".div"+this.className+"Returns table").each(function(){
        var v = {};
        v["name"]=$(this).find("input").val();
        v["description"]=$(this).find("textarea").val();
        vars.push($.toJSON(v));
    });
    return vars;
}

CustomSection.uiAddParameter=function(){
    var vars = this.getSerializedParameterVariables();
    var v = {
        name:"",
        description:""
    };
    vars.push($.toJSON(v));
    this.uiRefreshLogic(vars,null);
};

CustomSection.uiRemoveParameter=function(){
    var vars = this.getSerializedParameterVariables();
    vars.pop();
    this.uiRefreshLogic(vars,null);
};

CustomSection.uiAddReturn=function(){
    var vars = this.getSerializedReturnVariables();
    var v = {
        name:"",
        description:""
    };
    vars.push($.toJSON(v));
    this.uiRefreshLogic(null,vars);
};

CustomSection.uiRemoveReturn=function(){
    var vars = this.getSerializedReturnVariables();
    vars.pop();
    this.uiRefreshLogic(null,vars);
};
    
CustomSection.uiRefreshLogic=function(parameters,returns){
    if(parameters==null) parameters=this.getSerializedParameterVariables();
    if(returns==null) returns = this.getSerializedReturnVariables();
    $.post("view/CustomSection_logic.php",{
        oid:this.currentID,
        class_name:this.className,
        code:$("#form"+this.className+"TextareaCode").val(),
        parameters:parameters,
        returns:returns
    },function(data){
        $("#td"+CustomSection.className+"Logic").html(data);
    })
}
    
CustomSection.uiEditVariableDescription=function(obj){
    $("#formDialog"+CustomSection.className+"TextareaDescription").val(obj.val());
    $("#div"+CustomSection.className+"DialogDescription").dialog({
        title:dictionary["s3"],
        show:"slide" ,
        hide:"slide",
        modal:true,
        width:800,
        height:500,
        close:function(){
        //$(this).dialog("destroy");
        },
        beforeClose:function(){
            Methods.removeCKEditor($(this).find('textarea'));
        },
        open:function(){
            Methods.iniCKEditor($(this).find("textarea"));
        },
        buttons:{
            change:function(){
                obj.val(Methods.getCKEditorData($(this).find('textarea')));
                $(this).dialog("close");
            },
            cancel:function(){
                $(this).dialog("close");
            }
        }
    }); 
}

CustomSection.getFullSaveObject=function()
{
    var obj = this.getAddSaveObject();
    if($("#form"+this.className+"SelectOwner").length==1) obj["Owner_id"]=$("#form"+this.className+"SelectOwner").val();
    obj["parameters"]=CustomSection.getSerializedParameterVariables();
    obj["returns"]=CustomSection.getSerializedReturnVariables();
    obj["code"]=$("#form"+this.className+"TextareaCode").val();
    return obj;
}