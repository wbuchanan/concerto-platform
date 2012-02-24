function Table() { };
OModule.inheritance(Table);

Table.className="Table";

Table.onAfterEdit=function()
{
    };

Table.onAfterSave=function(){
    Test.uiTablesChanged();
};
    
Table.onAfterDelete=function(){
    Test.uiTablesChanged();
}

Table.getAddSaveObject=function()
{
    return { 
        oid:this.currentID,
        class_name:this.className,
        name:$("#form"+this.className+"InputName").val(),
        Sharing_id:$("#form"+this.className+"SelectSharing").val()
    };
};

Table.getFullSaveObject=function(){
    var obj = this.getAddSaveObject();
    obj["cols"] = Table.getSerializedColumns();
    obj["rows"] = Table.getRows();
    if($("#form"+this.className+"SelectOwner").length==1) obj["Owner_id"]=$("#form"+this.className+"SelectOwner").val();
    
    return obj;
}

Table.uiAddRow=function(){
    var id = "#form"+Table.className+"Table";
    var cols = Table.getColumns();
    var colsHTML = "";
    colsHTML = "";
    for(var i=0;i<cols.length;i++){
        colsHTML+="<td class='noWrap ui-widget-content'>";
        switch(cols[i].type){
            case 1:{
                colsHTML+='<div class="horizontalMargin"><input type="text" value="" class="fullWidth ui-widget-content ui-corner-all" /></div>';
                break;
            }
            case 2:{
                colsHTML+='<div class="horizontalMargin"><input type="text" value="" class="fullWidth ui-widget-content ui-corner-all" /></div>';
                break;
            }
            case 3:{
                colsHTML+='<div class="horizontalMargin" align="center"><span class="spanIcon tooltipTableStructure ui-icon ui-icon-document-b" onclick="Table.uiChangeHTML($(this).next())" title="edit HTML"></span><textarea class="notVisible"></textarea></div>';
                break;
            }
        }
        colsHTML+="</td>";
    }
    colsHTML+="<td class='ui-widget-header' align='center' style='width:50px;'><span class='spanIcon tooltip ui-icon ui-icon-trash' onclick='Table.uiRemoveRow($(this).parent().parent())' title='"+dictionary["s11"]+"'></span></td>";
    
    $(id+" > .tbodyTable").append("<tr>"+colsHTML+"</tr>");
    Table.checkTableEmpty();
    Methods.iniTooltips();
    Table.uiIniHTMLTooltips();
}

Table.getColumns=function(){
    var id = "#form"+Table.className+"Table";
    
    var cols = new Array();
    
    $(id+" .theadTable tr:first th:not(:last)").each(function(){
        var oid = $(this).attr("coloid");
        var name = $(this).attr("colname");
        var type = parseInt($(this).attr("coltype"));
        cols.push({
            name:name,
            type:type,
            oid:oid
        })
    })
    return cols;
}

Table.getSerializedColumns=function(){
    var cols = Table.getColumns();
    var result = new Array();
    for(var i=0;i<cols.length;i++) {
        result.push($.toJSON( cols[i]));
    }
    return result;
}

Table.getRows=function(){
    var rows = new Array();
    var cols = Table.getColumns();
    var id = "#form"+Table.className+"Table";
    
    var ri = 0;
    $(id+" .tbodyTable tr").each(function(){
        var ci = 0;
        var row = {};
        $(this).children("td:not(:last)").each(function(){
            switch(cols[ci].type)
            {
                case 1:
                {
                    $(this).find("input").each(function(){
                        row[cols[ci].name]=$(this).val();
                    });
                    break;
                }
                case 2:
                {
                    $(this).find("input").each(function(){
                        row[cols[ci].name]=$(this).val();
                    });
                    break;
                }
                case 3:
                {
                    $(this).find("textarea").each(function(){
                        row[cols[ci].name]=$(this).val();
                    });
                    break;
                }
            }
            ci++;
        });
        rows.push(row);
        ri++;
    });
    return rows;
}

Table.doesColumnExists=function(name){
    var cols = this.getColumns();
    for(var i=0;i<cols.length;i++){
        if(cols[i].name==name) return true;
    }
    return false;
}

Table.uiEditColumn=function(col){
    var id = "#form"+Table.className+"Table";
    
    var oldName = col.attr("colname");
    var oldType = col.attr("coltype");
    var colIndex = col.index();
    
    var name = $("#form"+Table.className+"InputColumnName");
    name.val(oldName);
    var type = $("#form"+Table.className+"SelectColumnType");
    type.val(oldType);
    
    $("#div"+this.className+"Dialog").dialog({
        title:dictionary["s12"],
        modal:true,
        resizable:false,
        close:function(){
            $(this).dialog("destroy");
        },
        buttons:[
        {
            text:dictionary["s95"],
            click:function(){
                name.val($.trim(name.val()));
                
                if(name.val()=="")
                {
                    Methods.alert(dictionary["s13"], dictionary["s14"]);
                    return;
                }
                
                if(Table.doesColumnExists(name.val())&&oldName!=name.val()) 
                {
                    Methods.alert(dictionary["s15"], "alert", dictionary["s14"]);
                    return;
                }
                
                var typeName = dictionary["s16"];
                switch(type.val())
                {
                    case "2":
                    {
                        typeName=dictionary["s17"];
                        break;
                    }
                    case "3":
                    {
                        typeName=dictionary["s18"];
                        break;
                    }
                }
                $(id+" .theadTable tr:first th:eq("+colIndex+")").replaceWith('<th class="ui-widget-header thSortable noWrap" colname="'+name.val()+'" coloid="0" coltype="'+type.val()+'"><table class="fullWidth"><tr><td>'+name.val()+' ( '+typeName+' )</td><td><span class="spanIcon tooltip ui-icon ui-icon-pencil" onclick="Table.uiEditColumn($(this).parent().parent().parent().parent().parent())" title="'+dictionary["s19"]+'"></span></td><td><span class="spanIcon tooltip ui-icon ui-icon-trash" onclick="Table.uiRemoveColumn($(this).parent().parent().parent().parent().parent())" title="'+dictionary["s20"]+'"></span></td></tr></table></th>');
                
                if(type.val()!=oldType)
                {
                    $(id+" .tbodyTable tr").each(function(){
                        $(this).children("td:eq("+colIndex+")").each(function(){
                            var value = "";
                            if(oldType==1){
                                value = $(this).find("input").val();
                            }
                            if(oldType==2){
                                value = $(this).find("input").val();
                            }
                            if(oldType==3){
                                value = $(this).find("textarea").val();
                            }
                    
                            var colsHTML="<td class='noWrap ui-widget-content'>";
                            switch(type.val()){
                                case "1":{
                                    colsHTML+='<div class="horizontalMargin"><input type="text" value="'+value+'" class="fullWidth ui-widget-content ui-corner-all" /></div>';
                                    break;
                                }
                                case "2":{
                                    colsHTML+='<div class="horizontalMargin"><input type="text" value="'+value+'" class="fullWidth ui-widget-content ui-corner-all" /></div>';
                                    break;
                                }
                                case "3":{
                                    colsHTML+='<div class="horizontalMargin" align="center"><span class="spanIcon tooltipTableStructure ui-icon ui-icon-document-b" onclick="Table.uiChangeHTML($(this).next())" title="edit HTML"></span><textarea class="notVisible">'+value+'</textarea></div>';
                                    break;
                                }
                            }
                            colsHTML+="</td>";
                    
                            $(this).replaceWith(colsHTML);
                        });
                    });
                }
                
                $(this).dialog("close");
                
                name.val("");
                
                Methods.iniSortableTableHeaders();
                Methods.iniTooltips();
                Table.uiIniHTMLTooltips();
            }
        },
        {
            text:dictionary["s23"],
            click:function(){
                $(this).dialog("close");
            }
        }
        ]
    });
}

Table.uiExportCSV=function(){
    var thisClass = this;
    $("#div"+Table.className+"DialogExportCSV").dialog({
        title:dictionary["s329"],
        modal:true,
        resizable:false,
        buttons:[{
            text:dictionary["s265"],
            click:function(){
                var delimeter = $("#inputTableCSVExportDelimeter").val();
                var enclosure = $("#inputTableCSVExportEnclosure").val();
                var header = $("#inputTableCSVExportHeader").is(":checked")?1:0;
                location.href='query/Table_csv_export.php?oid='+thisClass.currentID+"&delimeter="+delimeter+"&enclosure="+enclosure+"&header="+header;
                $(this).dialog("close");
            }
        },{
            text:dictionary["s23"],
            click:function(){
                $(this).dialog("close");
            }
        }
        ]
    });
}

Table.uiImportTable=function(){
    var thisClass = this;
    Methods.modalLoading();
    $.post("view/Table_import_mysql.php",{},function(data){
        Methods.stopModalLoading();
        $("#div"+Table.className+"DialogImportMySQL").html(data);
        var selectTable = $("#form"+thisClass.className+"SelectMySQLTable");
        
        $("#div"+Table.className+"DialogImportMySQL").dialog({
            title:dictionary["s21"],
            modal:true,
            resizable:false,
            buttons:[{
                text:dictionary["s22"],
                click:function(){
                    if(selectTable.val()==0){
                        Methods.alert(dictionary["s24"], "alert", dictionary["s25"]);
                        return;
                    }
                    $("#div"+Table.className+"DialogImportMySQL").parent().mask(dictionary["s319"]);
                    $.post("query/Table_mysql_import.php",{
                        oid:thisClass.currentID,
                        table:selectTable.val()
                    },function(data){
                        $("#div"+Table.className+"DialogImportMySQL").parent().unmask();
                        $("#div"+Table.className+"DialogImportMySQL").dialog("close");
                        switch(data.result){
                            case 0:{
                                thisClass.uiEdit(thisClass.currentID);
                                Methods.alert(dictionary["s26"], "info", dictionary["s25"]);
                                break;
                            }
                            case -1:{
                                Methods.alert(dictionary["s278"], "alert", dictionary["s25"]);
                                location.reload();
                                break;
                            }
                            case -2:{
                                Methods.alert(dictionary["s81"], "alert", dictionary["s25"]);
                                break;
                            }
                            default:{
                                Methods.alert(dictionary["s30"], "alert", dictionary["s25"]);
                                break;    
                            }
                        }
                    },"json")
                }
            }, {
                text:dictionary["s23"],
                click:function(){
                    $(this).dialog("close");
                }
            }]
        });
    });
}

Table.isFileUploaded = false;
Table.uiImportCSV=function(){
    $("#div"+Table.className+"DialogImportCSV").dialog({
        title:dictionary["s27"],
        resizable:false,
        modal:true,
        width:400,
        close:function(){
        },
        beforeClose:function(){
            
        },
        open:function(){
            $('#file'+Table.className+'CSVImport').fileupload({
                dataType: 'json',
                url: 'js/lib/fileupload/php/index.php',
                formData:function(form){
                    return [{
                        name:"oid",
                        value:Table.currentID
                    }]  
                },
                send: function(e,data){
                    $("#div"+Table.className+"DialogImportCSV").parent().mask(dictionary["s319"]);
                },
                done: function (e, data) {
                    $("#div"+Table.className+"DialogImportCSV").parent().unmask();
                    $.each(data.result, function (index, file) {
                        Table.isFileUploaded = true;
                        
                        Methods.confirm(dictionary["s28"], dictionary["s29"], function(){
                            $("#div"+Table.className+"DialogImportCSV").parent().mask(dictionary["s319"]);
                            $.post("query/Table_csv_import.php",{
                                oid:Table.currentID,
                                file:file.name,
                                delimeter:$("#inputTableCSVImportDelimeter").val(),
                                enclosure:$("#inputTableCSVImportEnclosure").val(),
                                header:$("#inputTableCSVImportHeader").is(":checked")?1:0
                            },function(data){
                                $("#div"+Table.className+"DialogImportCSV").parent().unmask();
                                $("#div"+Table.className+"DialogImportCSV").dialog("close");
                                switch(data.result){
                                    case 0:{
                                        Methods.alert(dictionary["s26"], "info", dictionary["s25"]);
                                        Table.uiEdit(Table.currentID);
                                        break;
                                    }
                                    case -1:{
                                        Methods.alert(dictionary["s278"], "alert", dictionary["s25"]);
                                        location.reload();
                                        break;
                                    }
                                    case -2:{
                                        Methods.alert(dictionary["s81"], "alert", dictionary["s25"]);
                                        break;
                                    }
                                    case -3:{
                                        Methods.alert(dictionary["s272"], "alert", dictionary["s25"]);
                                        break;
                                    }
                                    default:{
                                        Methods.alert(dictionary["s30"], "alert", dictionary["s25"]);
                                        Table.uiEdit(Table.currentID);
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

Table.uiAddColumn=function(){
    var id = "#form"+Table.className+"Table";
    
    var name = $("#form"+Table.className+"InputColumnName");
    var type = $("#form"+Table.className+"SelectColumnType");
    
    $("#div"+this.className+"Dialog").dialog({
        title:dictionary["s31"],
        resizable:false,
        modal:true,
        close:function(){
            $(this).dialog("destroy");
        },
        buttons:[
        {
            text:dictionary["s37"],
            click:function(){
                name.val($.trim(name.val()));
                
                if(name.val()=="")
                {
                    Methods.alert(dictionary["s13"], dictionary["s14"]);
                    return;
                }
                
                if(Table.doesColumnExists(name.val())) 
                {
                    Methods.alert(dictionary["s15"], "alert", dictionary["s14"]);
                    return;
                }
                
                var typeName = dictionary["s16"];
                switch(type.val())
                {
                    case "2":
                    {
                        typeName=dictionary["s17"];
                        break;
                    }
                    case "3":
                    {
                        typeName=dictionary["s18"];
                        break;
                    }
                }
                $(id+" .theadTable tr:first th:last").before('<th class="ui-widget-header thSortable noWrap" colname="'+name.val()+'" coloid="0" coltype="'+type.val()+'"><table class="fullWidth"><tr><td>'+name.val()+' ( '+typeName+' )</td><td><span class="spanIcon tooltip ui-icon ui-icon-pencil" onclick="Table.uiEditColumn($(this).parent().parent().parent().parent().parent())" title="'+dictionary["s19"]+'"></span></td><td><span class="spanIcon tooltip ui-icon ui-icon-trash" onclick="Table.uiRemoveColumn($(this).parent().parent().parent().parent().parent())" title="'+dictionary["s20"]+'"></span></td></tr></table></th>');
                
                var colsHTML="<td class='noWrap ui-widget-content'>";
                switch(type.val()){
                    case "1":{
                        colsHTML+='<div class="horizontalMargin"><input type="text" value="" class="fullWidth ui-widget-content ui-corner-all" /></div>';
                        break;
                    }
                    case "2":{
                        colsHTML+='<div class="horizontalMargin"><input type="text" value="" class="fullWidth ui-widget-content ui-corner-all" /></div>';
                        break;
                    }
                    case "3":{
                        colsHTML+='<div class="horizontalMargin" align="center"><span class="spanIcon tooltipTableStructure ui-icon ui-icon-document-b" onclick="Table.uiChangeHTML($(this).next())" title="edit HTML"></span><textarea class="notVisible"></textarea></div>';
                        break;
                    }
                }
                colsHTML+="</td>";
                
                $(id+" .tbodyTable tr").each(function(){
                    $(this).children("td:last").before(colsHTML);
                });
                $(this).dialog("close");
                
                name.val("");
                
                Methods.iniSortableTableHeaders();
                Table.checkAddRowBtnEnabled();
                Methods.iniTooltips();
                Table.uiIniHTMLTooltips();
            }
        },
        {
            text:dictionary["s23"],
            click:function(){
                $(this).dialog("close");
            }
        }
        ]
    });
}

Table.checkAddRowBtnEnabled=function(){
    var cols = Table.getColumns();
    if(cols.length>0) $(".spanAddRow").css("display","");
    else $(".spanAddRow").css("display","none");
}

Table.checkTableEmpty=function(){
    if(Table.getColumns().length>0 && Table.getRows().length>0) $("#div"+this.className+"EmptyTable").hide(0);
    else $("#div"+this.className+"EmptyTable").show(0);
}

Table.uiRemoveRow=function(row){
    Methods.confirm(dictionary["s32"], dictionary["s33"], function(){
        row.remove();  
        Table.checkTableEmpty();
    });
}

Table.uiRemoveColumn=function(col){
    Methods.confirm(dictionary["s34"], dictionary["s35"], function(){
        var id = "#form"+Table.className+"Table";
        var index = col.index();
        $(id+" .theadTable th:eq("+index+")").remove();  
        $(id+" .tbodyTable tr").each(function(){
            $(this).children("td:eq("+index+")").remove();
        });
        Table.checkAddRowBtnEnabled();
    }); 
}

Table.uiChangeHTML=function(obj){
    $("#form"+Table.className+"TextareaHTML").val(obj.val());
    $("#div"+Table.className+"DialogHTML").dialog({
        title:dictionary["s36"],
        resizable:false,
        modal:true,
        width:800,
        create:function(){
            var thisDialog = $("#div"+Table.className+"DialogHTML");
            Methods.iniCKEditor($(this).find("textarea"),function(){
                thisDialog.dialog("option","position","center");
            });
        },
        buttons:[
        {
            text:dictionary["s38"],
            click:function(){
                obj.val(Methods.getCKEditorData($(this).find('textarea')));
                $(this).dialog("close");
            }
        },
        {
            text:dictionary["s23"],
            click:function(){
                $(this).dialog("close");
            }
        }
        ]
    }); 
}

Table.uiIniHTMLTooltips=function(){
    $(".tooltipTableStructure").tooltip({
        content:function(){
            return dictionary["s39"]+"<hr/>"+$(this).next().val();
        }
    }); 
}