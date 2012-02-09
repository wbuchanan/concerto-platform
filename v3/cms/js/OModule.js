function OModule() {};

OModule.inheritance=function(obj)
{
    obj.currentID=0;
    obj.listLength=10;
    
    obj.uiChangeListLength=function(length)
    {
        this.listLength=length;
    };
	
    obj.uiReload=function(oid)
    {
        this.uiEdit(oid);
        this.uiList();
    };
        
    obj.highlightCurrentElement=function()
    {
        $(".row"+this.className+" td").removeClass("ui-state-highlight");
        $("#row"+this.className+this.currentID+" td").addClass("ui-state-highlight");
    };
    
    obj.uiAdd=function()
    {
        var thisClass = this;
        if(thisClass.onBeforeAdd) thisClass.onBeforeAdd();
        
        if(this.currentID!=0) this.uiEdit(0);
        
        Methods.loading("#divAddFormDialog");
        $("#divAddFormDialog").dialog({
            modal:true,
            resizable:false,
            title:dictionary["s7"],
            width:400,
            open:function(){
            },
            buttons:{
                save:function(){
                    thisClass.uiSave();
                },
                cancel:function(){
                    $(this).dialog("close");
                }
            }
        })
        
        $.post("view/"+this.className+"_form.php",{
            oid:-1
        },function(data){
            $("#divAddFormDialog").html(data);
            if(thisClass.onAfterAdd) thisClass.onAfterAdd();
            $("#divAddFormDialog").dialog("option","buttons",[
                {
                    text:dictionary["s95"],
                    click:function(){
                        thisClass.uiSave();
                    }
                },
                {
                    text:dictionary["s23"],
                    click:function(){
                        $(this).dialog("close");
                    }
                }
            ])
        })
    }
	
    obj.uiEdit=function(oid)
    {
        var thisClass = this;
        if(thisClass.onBeforeEdit) thisClass.onBeforeEdit();
		
        this.currentID=oid;
        $.post("view/"+this.className+"_form.php",
        {
            oid:oid
        },
        function(data){
            $("#div"+thisClass.className+"Form").html(data);
            thisClass.highlightCurrentElement();
            if(thisClass.onAfterEdit) thisClass.onAfterEdit();
        });
    };
	
    obj.uiList=function()
    {
        var thisClass = this;
        $.post("view/list.php",{
            oid:thisClass.currentID,
            class_name:thisClass.className,
            list_length:thisClass.listLength
        },
        function(data)
        {
            $('#div'+thisClass.className+'List').html(data);
            thisClass.highlightCurrentElement();
            if(thisClass.onAfterList) thisClass.onAfterList();
        });
    };
	
    obj.uiDelete=function(oid)
    {
        var thisClass = this;
        Methods.confirm(dictionary["s8"].format(oid),null,function(){
            if(oid==thisClass.currentID) thisClass.uiEdit(0);
            $.post("query/delete_object.php",
            {
                class_name:thisClass.className,
                oid:oid
            },
            function(data)
            {
                switch(data.result){
                    case 0:{
                        thisClass.uiList();
                        if(thisClass.onAfterDelete) thisClass.onAfterDelete();
                        break;
                    }
                    case -1:{
                        Methods.alert(dictionary["s278"], "alert", dictionary["s273"]);
                        location.reload();
                        break;
                    }
                    case -2:{
                        Methods.alert(dictionary["s81"], "alert", dictionary["s273"]);
                        break;
                    }
                }
            },"json");
        });
    };
    
    obj.uiImport=function(){
        var thisClass = this;
        $("#div"+this.className+"DialogImport").dialog({
            title:dictionary["s268"],
            modal:true,
            resizable:false,
            minHeight: 50,
            close:function(){
            },
            beforeClose:function(){
            
            },
            open:function(){
                $('#file'+thisClass.className+'Import').fileupload({
                    dataType: 'json',
                    url: 'js/lib/fileupload/php/index.php',
                    formData:function(form){
                        return [{
                            name:"class_name",
                            value:thisClass.className
                        }]  
                    },
                    done: function (e, data) {
                        $.each(data.result, function (index, file) {
                            Table.isFileUploaded = true;
                            Methods.confirm(dictionary["s269"], dictionary["s29"], function(){
                                $.post("query/import_object.php",{
                                    class_name:thisClass.className,
                                    file:file.name
                                },function(data){
                                    $("#div"+thisClass.className+"DialogImport").dialog("close");
                                    switch(data.result){
                                        case 0:{
                                            Methods.alert(dictionary["s270"], "info", dictionary["s268"]);
                                            thisClass.uiReload(data.oid);
                                            break;
                                        }
                                        case -1:{
                                            Methods.alert(dictionary["s278"], "alert", dictionary["s268"]);
                                            location.reload();
                                            break;
                                        }
                                        case -2:{
                                            Methods.alert(dictionary["s81"], "alert", dictionary["s268"]);
                                            break;
                                        }
                                        case -3:{
                                            Methods.alert(dictionary["s271"], "alert", dictionary["s268"]);
                                            break;
                                        }
                                    }
                                },"json");
                            });
                        });
                    }
                });
            },
            buttons:[{
                text:dictionary["s23"],
                click:function(){
                    $(this).dialog("close");
                }
            }]
        }); 
    }
    
    obj.uiExport=function(oid){
        location.href="query/export_object.php?class_name="+this.className+"&oid="+oid;
    };
	
    obj.uiSave=function()
    {
        var thisClass = this;
		
        if(this.uiFormNotValidated)
        {
            var notValidated = this.uiFormNotValidated();
            if(notValidated) 
            {
                Methods.alert(notValidated,"alert");
                return;
            }
        }
		
        $.post("query/save_object.php",
            (this.currentID==0?this.getAddSaveObject():this.getFullSaveObject()),
            function(data)
            {
                if(thisClass.currentID==0) $("#divAddFormDialog").dialog("close");
                switch(data.result){
                    case 0:{
                        if(data.oid!=0)
                        {
                            if(thisClass.currentID!=0) thisClass.uiList();
                            else thisClass.uiReload(data.oid);
                            if(thisClass.onAfterSave) thisClass.onAfterSave();
                            Methods.alert(dictionary["s9"],"info", dictionary["s274"]);
                        }
                        else Methods.alert(dictionary["s10"],"alert", dictionary["s274"]);
                        break;
                    }
                    case -1:{
                        Methods.alert(dictionary["s278"], "alert", dictionary["s274"]);
                        location.reload();
                        break;     
                    }
                    case -2:{
                        Methods.alert(dictionary["s81"], "alert", dictionary["s274"]);
                        break;     
                    }
                }
            },"json");
    };
};