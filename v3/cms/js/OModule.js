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

function OModule() {};

OModule.inheritance=function(obj)
{
    obj.currentID=0;
    obj.listLength=25;
    obj.reloadOnModification=false;
    obj.reloadHash="";
    obj.currentPanel = "list";
    
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
    
    obj.download=function(oid){
        var thisClass = this;
        $.post("query/download_object.php",{
            class_name:this.className,
            oid:oid
        },function(data){
            switch(data.result){
                case 0:{
                    $("#divDialogDownload").dialog("close");
                    Methods.alert(dictionary["s388"], "info", dictionary["s387"]);
                    thisClass.uiReload(data.oid);
                    break;
                }
                case -1:{
                    Methods.alert(dictionary["s278"], "alert", dictionary["s387"]);
                    location.reload();
                    break;
                }
                case -2:{
                    Methods.alert(dictionary["s81"], "alert", dictionary["s387"]);
                    break;
                }
                case -3:{
                    Methods.alert(dictionary["s389"], "alert", dictionary["s387"]);
                    break;
                }
            }
        },"json");
    }
    
    obj.uiDownload=function(){
        var thisClass = this;
        
        $("#divDialogDownload").dialog({
            modal:true,
            resizable:false,
            title:dictionary["s387"],
            width:950,
            open:function(){
                Methods.stopModalLoading();
                $("#divDialogDownload").html("<div id='divDialogDownloadGrid'></div>");
                $("#divDialogDownloadGrid").kendoGrid({
                    dataBound:function(e){
                        Methods.iniTooltips();
                        if(this.dataSource.group().length == 0) {
                            setTimeout( function() {
                                $(".k-grouping-header").html(dictionary["s339"]);
                                $("select[name='logic']").each(function() {
                                    $(this).data("kendoDropDownList").dataSource.data([
                                    {
                                        text: dictionary["s227"], 
                                        value: "and"
                                    },

                                    {
                                        text: dictionary["s228"], 
                                        value: "or"
                                    }
                                    ]);
                                    $(this).data("kendoDropDownList").select(0);
                                });
                            });
                        }
                    },
                    dataSource: {
                        transport:{
                            read: {
                                url:"query/get_library_list.php?class_name="+thisClass.className,
                                dataType:"json"
                            }
                        },
                        schema:{
                            model:{
                                id: "id",
                                fields:{
                                    id: {
                                        type: "number"
                                    },
                                    description: {
                                        type: "string"
                                    },
                                    name: {
                                        type:"string"
                                    },
                                    author: {
                                        type:"string"
                                    },
                                    revision: {
                                        type:"string"
                                    },
                                    uploaded: {
                                        type:"string"
                                    },
                                    count: {
                                        type:"number"
                                    }
                                }
                            }
                        },
                        pageSize:15
                    },
                    filterable:{
                        messages: {
                            info: dictionary["s340"],
                            filter: dictionary["s341"],
                            clear: dictionary["s342"]
                        },
                        operators: {
                            string: {
                                eq: dictionary["s222"],
                                neq: dictionary["s221"],
                                startswith: dictionary["s343"],
                                contains: dictionary["s344"],
                                endswith: dictionary["s345"]
                            },
                            number: {
                                eq: dictionary["s222"],
                                neq: dictionary["s221"],
                                gte: dictionary["s224"],
                                gt: dictionary["s223"],
                                lte: dictionary["s226"],
                                lt: dictionary["s225"]
                            }
                        }
                    },
                    sortable:true,
                    pageable:true,
                    groupable:true,
                    scrollable:false,
                    columns:[
                    {
                        title: dictionary["s371"],
                        width: 40,
                        template: "<span class='spanIcon ui-icon ui-icon-help tooltip' title='${description}'></span>",
                        field: "description",
                        filterable: false,
                        sortable: false,
                        groupable: false
                    },
                    {
                        title: dictionary["s69"],
                        width: 40,
                        field: "id",
                        filterable: true,
                        sortable: true,
                        groupable: false
                    },
                    {
                        title: dictionary["s70"],
                        field: "name",
                        filterable: true,
                        sortable: true,
                        groupable: false
                    },
                    {
                        title: dictionary["s378"],
                        field: "author",
                        filterable: true,
                        sortable: true,
                        groupable: true
                    },
                    {
                        title: dictionary["s379"],
                        field: "revision",
                        filterable: true,
                        sortable: true,
                        groupable: true
                    },
                    {
                        title: dictionary["s385"],
                        field: "uploaded",
                        filterable: true,
                        sortable: true,
                        groupable: true
                    },
                    {
                        title: dictionary["s386"],
                        field: "count",
                        filterable: true,
                        sortable: true,
                        groupable: true
                    },
                    {
                        title:'', 
                        width:30, 
                        filterable: false, 
                        sortable: false, 
                        groupable: false, 
                        template: "<span style='display:inline-block;' class='spanIcon tooltip ui-icon ui-icon-gear' onclick='"+thisClass.className+".download(${ id })' title='"+dictionary["s374"]+"'></span>"
                    }
                    ]
                });
            
                Methods.iniIconButton(".btnDownload","gear");
                
                $("#divDialogDownload").dialog("option","position","center"); 
            },
            buttons:[
            {
                text:dictionary["s23"],
                click:function(){
                    $(this).dialog("close");
                }
            }
            ]
        })
    };
    
    obj.upload=function(oid){
        $.post("query/upload_object.php",{
            class_name:this.className,
            oid:oid,
            name:$("#inputDialogUploadName").val(),
            description:Methods.getCKEditorData("#textareaDialogUploadDescription"),
            author:$("#inputDialogUploadAuthor").val(),
            revision:$("#inputDialogUploadRevision").val()
        },function(data){
            switch(data.result){
                case 0:{
                    $("#divDialogUpload").dialog("close");
                    Methods.alert(dictionary["s384"], "info", dictionary["s382"]);
                    break;
                }
                case -1:{
                    Methods.alert(dictionary["s278"], "alert", dictionary["s382"]);
                    location.reload();
                    break;
                }
                case -2:{
                    Methods.alert(dictionary["s81"], "alert", dictionary["s382"]);
                    break;
                }
            }
        },"json");
    }
    
    obj.uiUpload=function(oid){
        var thisClass = this;
        Methods.modalLoading();
        $.post("view/upload_form.php",{
            class_name:thisClass.className,
            oid:oid
        },function(data){
            $("#divDialogUpload").html(data);
            $("#divDialogUpload").dialog({
                modal:true,
                resizable:false,
                title:dictionary["s382"],
                width:950,
                open:function(){
                    Methods.stopModalLoading();
                    Methods.iniCKEditor("#textareaDialogUploadDescription", function(){
                        $("#divDialogUpload").dialog("option","position","center"); 
                    })
                },
                buttons:[
                {
                    text:dictionary["s383"],
                    click:function(){
                        thisClass.upload(oid);
                    }
                },
                {
                    text:dictionary["s23"],
                    click:function(){
                        $(this).dialog("close");
                    }
                }
                ]
            })
        })
    };
    
    obj.uiAdd=function(ignoreOnBefore)
    {
        if(ignoreOnBefore==null) ignoreOnBefore=false;
        var thisClass = this;
        if(thisClass.onBeforeAdd && !ignoreOnBefore) {
            if(!thisClass.onBeforeAdd()) return;
        }
        
        if(this.currentID!=0) this.uiEdit(0);
        
        Methods.modalLoading();
        $.post("view/"+this.className+"_form.php",{
            oid:-1
        },function(data){
            $("#divAddFormDialog").html(data);
            $("#divAddFormDialog").dialog({
                modal:true,
                resizable:false,
                title:dictionary["s7"],
                width:500,
                open:function(){
                    Methods.stopModalLoading();
                    if(thisClass.onAfterAdd) thisClass.onAfterAdd();
                },
                buttons:[
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
                ]
            })
        });
    }
    
    obj.uiShowForm=function(){
        if(this.currentPanel=="form") return;
        $("#div"+this.className+"List").hide();
        $("#div"+this.className+"Form").show();
        $("#radio"+this.className+"List").removeAttr("checked");
        $("#radio"+this.className+"Form").attr("checked","checked");
        $( "#div"+this.className+"RadioMenu" ).buttonset("refresh"); 
        this.currentPanel="form";
    }
    
    obj.uiShowList=function(){
        if(this.currentPanel=="list") return;
        $("#div"+this.className+"Form").hide();
        $("#div"+this.className+"List").show();
        $("#radio"+this.className+"Form").removeAttr("checked");
        $("#radio"+this.className+"List").attr("checked","checked");
        $( "#div"+this.className+"RadioMenu" ).buttonset("refresh"); 
        this.currentPanel="list";
    }
	
    obj.uiEdit=function(oid,ignoreOnBefore)
    {
        if(ignoreOnBefore==null) ignoreOnBefore=false;
        var thisClass = this;
        if(thisClass.onBeforeEdit && !ignoreOnBefore) {
            if(!thisClass.onBeforeEdit()) return;
        }
		
        this.currentID=oid;
        
        if(this.currentID>0) {
            $("#radio"+this.className+"Form").button("enable");
            $("#radio"+this.className+"Form").button("option","label",dictionary["s338"]+" #"+this.currentID);
            this.uiShowForm();
        }
        else {
            $("#radio"+this.className+"Form").button("disable");
            $("#radio"+this.className+"Form").button("option","label",dictionary["s338"]+" "+dictionary["s73"]);
            this.uiShowList();
        }
        
        $("#div"+thisClass.className+"Form").mask(dictionary["s319"]);
        $.post("view/"+this.className+"_form.php",
        {
            oid:oid
        },
        function(data){
            $("#div"+thisClass.className+"Form").unmask();
            $("#div"+thisClass.className+"Form").html(data);
            thisClass.highlightCurrentElement();
            if(thisClass.onAfterEdit) thisClass.onAfterEdit();
        });
    };
	
    obj.uiList=function()
    {
        var thisClass = this;
        $("#div"+thisClass.className+"List").mask(dictionary["s319"]);
        var grid = $("#div"+thisClass.className+"Grid").data("kendoGrid");
        grid.dataSource.read(); 
        grid.refresh();
        $("#div"+thisClass.className+"List").unmask();
    };
	
    obj.uiDelete=function(oid,ignoreOnBefore)
    {
        if(ignoreOnBefore==null) ignoreOnBefore=false;
        var thisClass = this;
        
        if(thisClass.onBeforeDelete && !ignoreOnBefore) {
            if(!thisClass.onBeforeDelete(oid)) return;
        }
        
        Methods.confirm(dictionary["s8"].format(oid),null,function(){
            if(thisClass.reloadOnModification) { 
                Methods.modalLoading();
            }
            
            if(oid==thisClass.currentID && !thisClass.reloadOnModification) thisClass.uiEdit(0);
            $.post("query/delete_object.php",
            {
                class_name:thisClass.className,
                oid:oid
            },
            function(data)
            {
                if(thisClass.reloadOnModification) Methods.stopModalLoading();
                switch(data.result){
                    case 0:{
                        if(!thisClass.reloadOnModification) {
                            thisClass.uiList();
                            if(thisClass.onAfterDelete) thisClass.onAfterDelete();
                        }
                        else {
                            Methods.modalLoading();
                            Methods.reload(thisClass.reloadHash);
                        }
                        break;
                    }
                    case -1:{
                        Methods.alert(dictionary["s278"], "alert", dictionary["s273"],function(){
                            Methods.modalLoading();
                            Methods.reload(thisClass.reloadHash); 
                        });
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
                    //maxChunkSize: 1000000,
                    url: 'js/lib/fileupload/php/index.php',
                    formData:function(form){
                        return [{
                            name:"class_name",
                            value:thisClass.className
                        }]  
                    },
                    send: function (e, data) {
                        Methods.modalProgress();
                        $("#div"+thisClass.className+"DialogImport").dialog("close");
                    },
                    progress: function(e,data) {
                         var progress = parseInt(data.loaded / data.total * 100, 10);
                         Methods.changeProgress(progress);
                    },
                    done: function (e, data) {
                        $.each(data.result, function (index, file) {
                            Table.isFileUploaded = true;
                            Methods.confirm(dictionary["s269"], dictionary["s29"], function(){
                                $("#div"+thisClass.className+"DialogImport").parent().mask(dictionary["s319"]);
                                $.post("query/import_object.php",{
                                    class_name:thisClass.className,
                                    file:file.name
                                },function(data){
                                    $("#div"+thisClass.className+"DialogImport").parent().unmask();
                                    $("#div"+thisClass.className+"DialogImport").dialog("close");
                                    switch(data.result){
                                        case 0:{
                                            Methods.alert(dictionary["s270"], "info", dictionary["s268"]);
                                            thisClass.uiReload(data.oid);
                                            if(thisClass.onAfterImport) thisClass.onAfterImport();
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
                                        case -4:{
                                            Methods.alert(dictionary["s333"], "alert", dictionary["s268"]);
                                            break;
                                        }
                                        case -5:{
                                            Methods.alert(dictionary["s370"], "alert", dictionary["s268"]);
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
    
    obj.getMessageSuccessfulSave = function(){
        return dictionary["s9"];
    }
    obj.uiSaveValidated=function(ignoreOnBefore){
        var thisClass = this;
            
        if(thisClass.onBeforeSave && !ignoreOnBefore) {
            if(!thisClass.onBeforeSave()) return;
        }
		
        if(thisClass.reloadOnModification) { 
            Methods.modalLoading();
        }
        
        $("#divAddFormDialog").parent().mask(dictionary["s319"]);
        $("#div"+thisClass.className+"Form").mask(dictionary["s319"]);
        $.post("query/save_object.php",
            (this.currentID==0?this.getAddSaveObject():this.getFullSaveObject()),
            function(data)
            {
                $("#divAddFormDialog").parent().unmask();
                $("#div"+thisClass.className+"Form").unmask();
                if(thisClass.currentID==0) $("#divAddFormDialog").dialog("close");
                
                if(thisClass.reloadOnModification) { 
                    Methods.stopModalLoading();
                }
                
                switch(data.result){
                    case 0:{
                        if(data.oid!=0)
                        {
                            var isNewObject = false;
                            if(thisClass.currentID==0) isNewObject = true;
                            if(!thisClass.reloadOnModification) { 
                                if(thisClass.currentID!=0) thisClass.uiList();
                                else thisClass.uiReload(data.oid);
                            }
                            Methods.alert(thisClass.getMessageSuccessfulSave(isNewObject),"info", dictionary["s274"],function(){
                                if(thisClass.reloadOnModification) {
                                    Methods.modalLoading();
                                    Methods.reload(thisClass.reloadHash);
                                }
                                if(thisClass.onAfterSave) thisClass.onAfterSave(isNewObject);
                            });
                        }
                        else {
                            Methods.alert(dictionary["s10"],"alert", dictionary["s274"]);
                        }
                        break;
                    }
                    case -1:{
                        Methods.alert(dictionary["s278"], "alert", dictionary["s274"],function(){
                            Methods.modalLoading();
                            Methods.reload(thisClass.reloadHash); 
                        });
                        break;     
                    }
                    case -2:{
                        Methods.alert(dictionary["s81"], "alert", dictionary["s274"]);
                        break;     
                    }
                }
            },"json");
    }
	
    obj.uiSave=function(ignoreOnBefore)
    {
        if(ignoreOnBefore==null) ignoreOnBefore=false;
        var thisClass = this;
        
        if(thisClass.uiSaveValidate) thisClass.uiSaveValidate(ignoreOnBefore);
        else thisClass.uiSaveValidated(ignoreOnBefore);
    };
};