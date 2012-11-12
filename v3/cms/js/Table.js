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

Table.onAfterImport=function(){
    Test.uiTablesChanged();
}

Table.onAfterAdd=function(){
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
    obj["description"]=Methods.getCKEditorData("#form"+this.className+"TextareaDescription");
    if($("#form"+this.className+"SelectOwner").length==1) obj["Owner_id"]=$("#form"+this.className+"SelectOwner").val();
    
    return obj;
}

Table.uiSaveValidate=function(ignoreOnBefore,isNew){
    if(!this.checkRequiredFields([
        $("#form"+this.className+"InputName").val()
        ])) {
        Methods.alert(dictionary["s415"],"alert");
        return false;
    }
    Table.uiSaveValidated(ignoreOnBefore,isNew);
}

Table.uiRemoveColumn=function(obj){
    var thisClass = this;
    Methods.confirm(dictionary["s34"], dictionary["s35"], function(){
        var grid = $("#div"+thisClass.className+"GridStructure").data('kendoGrid');
        var index = obj.closest('tr')[0].sectionRowIndex;
        var item = grid.dataItem(grid.tbody.find("tr:eq("+index+")"));
        
        grid.removeRow(grid.tbody.find("tr:eq("+index+")"));
        
        var dataGrid = $("#div"+thisClass.className+"GridData").data('kendoGrid');
        
        dataGrid.columns.splice(index,1);
        for(var i=0;i<dataGrid.dataSource.data().length;i++){
            delete dataGrid.dataSource.data()[i][item.name];
            delete dataGrid.dataSource.data()[i].fields[item.name]
            delete dataGrid.dataSource.data()[i].defaults[item.name]
        }
        //delete Table.dataGridSchemaFields[item.name];
        Table.structureEmptyCheck();
        Table.uiRefreshDataGrid();
    });
}

Table.structureEmptyCheck=function(){
    var grid = $("#div"+this.className+"GridStructure").data('kendoGrid');
    if(grid.dataSource.data().length>0){
        $("#div"+this.className+"GridDataContainer").show();
        $("#btn"+this.className+"DataGridCaption").show();
        $("#div"+this.className+"DataStructureEmptyCaption").hide();
    } 
    else {
        $("#div"+this.className+"GridDataContainer").hide();
        $("#btn"+this.className+"DataGridCaption").hide();
        $("#div"+this.className+"DataStructureEmptyCaption").show();
    }
}

Table.uiRefreshDataGrid=function(){
    var grid = $("#div"+this.className+"GridData").data('kendoGrid');
    
    var columns = grid.columns;
    var items = grid.dataSource.data();
    
    Table.uiReloadDataGrid(items, columns);
}
Table.uiRefreshStructureGrid=function(){
    var grid = $("#div"+this.className+"GridStructure").data('kendoGrid');
    
    var columns = grid.columns;
    var items = grid.dataSource.data();
    
    Table.uiReloadStructureGrid(items, columns);
}

Table.structureGridSchemaFields=null;
Table.dataGridSchemaFields=null;


Table.uiReloadDataGrid=function(data,columns){
    var thisClass = this;
    
    $("#div"+this.className+"GridDataContainer").html("<div id='div"+this.className+"GridData' class='grid'></div>");
        
    var dataSource = new kendo.data.DataSource({
        data:data,
        schema:{
            model:{
                id:"id",
                fields:Table.dataGridSchemaFields
            }
        },
        pageSize:20
    });
    
    $("#div"+thisClass.className+"GridData").kendoGrid({
        dataBound:function(e){
            Methods.iniTooltips();  
            Table.uiIniHTMLTooltips();
        },
        dataSource: dataSource,
        scrollable:true,
        resizable: true,
        sortable:true,
        columnMenu:{
            messages: {
                filter: dictionary["s341"],
                columns: dictionary["s533"],
                sortAscending: dictionary["s534"],
                sortDescending: dictionary["s535"]
            }  
        },
        pageable: {
            refresh:true,
            pageSizes:true,
            messages: {
                display: dictionary["s527"],
                empty: dictionary["s528"],
                page: dictionary["s529"],
                of: dictionary["s530"],
                itemsPerPage: dictionary["s531"],
                first: dictionary["s523"],
                previous: dictionary["s524"],
                next: dictionary["s525"],
                last: dictionary["s526"],
                refresh: dictionary["s532"]
            }
        },
        filterable:{
            messages: {
                info: dictionary["s340"],
                filter: dictionary["s341"],
                clear: dictionary["s342"],
                and: dictionary["s227"],
                or: dictionary["s228"]
            },
            operators: {
                string: {
                    contains: dictionary["s344"],
                    eq: dictionary["s222"],
                    neq: dictionary["s221"],
                    startswith: dictionary["s343"],
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
        columns: columns,
        toolbar:[
        {
            name: "create", 
            template: '<button class="btnAdd" onclick="Table.uiAddRow()">'+dictionary["s37"]+'</button>'
        },
        {
            name: "clear", 
            template: '<button class="btnRemove" onclick="Table.uiClearRows()">'+dictionary["s366"]+'</button>'
        }
        ],
        editable: {
            mode:"incell",
            confirmation:false
        }
    });
    Methods.iniIconButton(".btnAdd", "plus");
    Methods.iniIconButton(".btnRemove", "trash");
}

Table.uiIniDataGrid=function(){
    var thisClass = this;
    
    $("#div"+this.className+"GridDataContainer").html("<div id='div"+this.className+"GridData' class='grid'></div>");
    
    $.post("query/Table_column_list.php?oid="+this.currentID,{},function(data){
        var fields = {};
        fields["id"]={
            editable: false, 
            nullable: true
        };
        var columns = [];
        for(var i=0;i<data.length;i++)
        {
            var templateSet = false;
            var title = data[i].name;
            fields[data[i].name] = {}
            var col = {
                title:title,
                field:data[i].name
            };
            
            col["editor"] = Table.stringEditor;
            fields[data[i].name]["type"] = "string";
            fields[data[i].name]["editable"] = true;
            fields[data[i].name]["defaultValue"] = data[i].defaultValue;
            fields[data[i].name]["nullable"] = data[i].nullable==1;
            
            switch(data[i].type){
                case "tinyint":
                case "smallint":
                case "mediumint":
                case "int":
                case "bigint":
                case "decimal":
                case "float":
                case "double":
                case "real":
                case "bit":
                case "boolean":
                case "serial":{
                    col["editor"] = Table.numberEditor;
                    fields[data[i].name]["type"]="number";
                    break;
                }
                case "set":
                case "enum":{
                    col["editor"] = Table.setEditor;
                    break;
                }
                case "HTML":{
                    col["editor"] = Table.htmlEditor;
                    col["template"] = '<div class="horizontalMargin" align="center">'+
                    '<span class="spanIcon tooltipTableStructure ui-icon ui-icon-document-b" onclick="Table.uiChangeHTML($(this).next(),\''+data[i].name+'\')" title="'+dictionary["s130"]+'"></span>'+
                    '<textarea class="notVisible">#='+data[i].name+'#</textarea>'+
                    '</div>';
                    fields[data[i].name]["type"]="string";
                    fields[data[i].name]["editable"] = false;
                    templateSet = true;
                    break;
                }
            }
            
            if(fields[data[i].name]["nullable"] && !templateSet) col["template"]="#= "+data[i].name+"==null?'<span style=\"font-style:italic;\"><b>null</b></span>':"+data[i].name+" #";
            
            columns.push(col);
        }
        columns.push({
            title:' ',
            width:30,
            sortable:false,
            filterable:false,
            resizable:false,
            template:'<span style="display:inline-block;" class="spanIcon tooltip ui-icon ui-icon-trash" onclick="'+thisClass.className+'.uiRemoveRow($(this))" title="'+dictionary["s11"]+'"></span>'
        });
        
        var dataSource = new kendo.data.DataSource({
            transport:{
                read: {
                    url:"query/Table_data_list.php?oid="+thisClass.currentID,
                    dataType:"json"
                }
            },
            schema:{
                model:{
                    id: "id",
                    fields:fields
                }
            },
            pageSize:20
        });
        
        Table.dataGridSchemaFields = fields;
    
        $("#div"+thisClass.className+"GridData").kendoGrid({
            save:function(e){
            },
            dataBound:function(e){
                Methods.iniTooltips();  
                Table.uiIniHTMLTooltips();
                if(this.dataSource.group().length == 0) {
                    setTimeout( function() {
                        $(".k-grouping-header").html(dictionary["s339"]);
                    });
                }
            },
            dataSource: dataSource,
            scrollable:true,
            resizable: true,
            sortable:true,
            columnMenu:{
                messages: {
                    filter: dictionary["s341"],
                    columns: dictionary["s533"],
                    sortAscending: dictionary["s534"],
                    sortDescending: dictionary["s535"]
                }  
            },
            pageable: {
                refresh:true,
                pageSizes:true,
                messages: {
                    display: dictionary["s527"],
                    empty: dictionary["s528"],
                    page: dictionary["s529"],
                    of: dictionary["s530"],
                    itemsPerPage: dictionary["s531"],
                    first: dictionary["s523"],
                    previous: dictionary["s524"],
                    next: dictionary["s525"],
                    last: dictionary["s526"],
                    refresh: dictionary["s532"]
                }
            },
            filterable:{
                messages: {
                    info: dictionary["s340"],
                    filter: dictionary["s341"],
                    clear: dictionary["s342"],
                    and: dictionary["s227"],
                    or: dictionary["s228"]
                },
                operators: {
                    string: {
                        contains: dictionary["s344"],
                        eq: dictionary["s222"],
                        neq: dictionary["s221"],
                        startswith: dictionary["s343"],
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
            columns: columns,
            toolbar:[
            {
                name: "create", 
                template: '<button class="btnAdd" onclick="Table.uiAddRow()">'+dictionary["s37"]+'</button>'
            },
            {
                name: "clear", 
                template: '<button class="btnRemove" onclick="Table.uiClearRows()">'+dictionary["s366"]+'</button>'
            }
            ],
            editable: {
                mode:"incell",
                confirmation:false
            }
        });
        
        Methods.iniIconButton(".btnAdd", "plus");
        Methods.iniIconButton(".btnRemove", "trash");
        
    },"json");
}

Table.uiReloadStructureGrid=function(data,columns){
    var thisClass = this;
    
    $("#div"+this.className+"GridStructureContainer").html("<div id='div"+this.className+"GridStructure' class='grid'></div>");
        
    var dataSource = new kendo.data.DataSource({
        data:data,
        schema:{
            model:{
                fields:Table.structureGridSchemaFields
            }
        }
    });
    
    $("#div"+thisClass.className+"GridStructure").kendoGrid({
        dataBound:function(e){
            Table.structureEmptyCheck();
            Methods.iniTooltips();  
        },
        dataSource: dataSource,
        columns: columns,
        toolbar:[
        {
            name: "create", 
            template: '<button class="btnAdd" onclick="Table.uiAddColumn()">'+dictionary["s37"]+'</button>'
        }
        ],
        editable: false,
        scrollable:false
    });
    Methods.iniIconButton(".btnAdd", "plus");
}

Table.uiIniStructureGrid=function(){
    var thisClass = this;
    
    $("#div"+this.className+"GridStructureContainer").html("<div id='div"+this.className+"GridStructure' class='grid'></div>");
    
    var fields = {
        id:{
            type:"number"
        },
        name:{
            type:"string"
        },
        type:{
            type:"string"
        },
        lengthValues:{
            type:"string"
        },
        defaultValue:{
            type:"string"
        },
        attributes:{
            type:"string"
        },
        nullable:{
            type:"number"
        },
        auto_increment:{
            type:"number"
        }
    };
    
    var dataSource = new kendo.data.DataSource({
        transport:{
            read: {
                url:"query/Table_column_list.php?oid="+thisClass.currentID,
                dataType:"json"
            }
        },
        schema:{
            model:{
                id:"id",
                fields:fields
            }
        }
    });
    
    Table.structureGridSchemaFields = fields;
    
    $("#div"+this.className+"GridStructure").kendoGrid({
        dataBound:function(e){
            Table.structureEmptyCheck();
            Methods.iniTooltips();  
        },
        dataSource: dataSource,
        columns: [{
            title:dictionary["s70"],
            field:"name"
        },{
            title:dictionary["s122"],
            field:"type"
        },{
            title:dictionary["s585"],
            field:"lengthValues"
        },{
            title:dictionary["s538"],
            field:"defaultValue"
        },{
            title:dictionary["s588"],
            field:"attributes"
        },{
            title:dictionary["s590"],
            field:"nullable",
            template:'<input type="checkbox" #= nullable==1?"checked":"" # disabled />'
        },{
            title:dictionary["s592"],
            field:"auto_increment",
            template:'<input type="checkbox" #= auto_increment==1?"checked":"" # disabled />'
        },{
            title:' ',
            width:50,
            template:'<span style="display:inline-block;" class="spanIcon tooltip ui-icon ui-icon-pencil" onclick="'+thisClass.className+'.uiEditColumn($(this))" title="'+dictionary["s19"]+'"></span>'+
            '<span style="display:inline-block;" class="spanIcon tooltip ui-icon ui-icon-trash" onclick="'+thisClass.className+'.uiRemoveColumn($(this))" title="'+dictionary["s204"]+'"></span>'
        }],
        toolbar:[
        {
            name: "create", 
            template: '<button class="btnAdd" onclick="Table.uiAddColumn()">'+dictionary["s37"]+'</button>'
        }
        ],
        editable: false,
        scrollable:true
    });
    Methods.iniIconButton(".btnAdd", "plus");
}

Table.uiAddRow=function(){
    var grid = $("#div"+this.className+"GridData").data('kendoGrid');
    grid.addRow();
    
    Methods.iniTooltips();
    Table.uiIniHTMLTooltips();
}

Table.getColumns=function(){
    var grid = $("#div"+this.className+"GridStructure").data('kendoGrid');
    var cols = new Array();
    if(grid==null) return cols;
    var data = grid.dataSource.data();
    
    for(var i=0;i<data.length;i++){
        cols.push({
            name:data[i].name,
            type:data[i].type,
            lengthValues:data[i].lengthValues,
            defaultValue:data[i].defaultValue,
            attributes:data[i].attributes,
            nullable:data[i].nullable,
            auto_increment:data[i].auto_increment
        })
    }
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
    var result = new Array();
    
    var struct = $("#div"+this.className+"GridStructure").data('kendoGrid');
    var data = $("#div"+this.className+"GridData").data('kendoGrid');
    
    var cols = struct.dataSource.data();
    var rows = data.dataSource.data();
    
    for(var i=0;i<rows.length;i++){
        var row = {};
        for(var j=0;j<cols.length;j++){
            row[cols[j].name]=rows[i][cols[j].name];
        }
        result.push($.toJSON(row));
    }
    return result;
}

Table.doesColumnExists=function(name){
    var grid = $("#div"+this.className+"GridData").data('kendoGrid');
    
    var columns = grid.columns;
    for(var i=0;i<columns.length;i++){
        if(columns[i].field==name) return true;
    }
    return false;
}

Table.uiEditColumn=function(obj){
    var thisClass = this;
    
    var structGrid = $("#div"+thisClass.className+"GridStructure").data('kendoGrid');
    var index = obj.closest('tr')[0].sectionRowIndex;
    var item = structGrid.dataItem(structGrid.tbody.find("tr:eq("+index+")"));
    
    var oldName = item.name;
    var oldType = item.type;
    var oldLengthValues = item.lengthValues;
    var oldDefaultValue = item.defaultValue;
    var oldAttributes = item.attributes;
    var oldNullable = item.nullable;
    var oldAutoIncrement = item.auto_increment;
    
    var name = $("#form"+Table.className+"InputColumnName");
    name.val(oldName);
    var type = $("#form"+Table.className+"SelectColumnType");
    type.val(oldType);
    var lengthValues = $("#form"+Table.className+"InputColumnLength");
    lengthValues.val(oldLengthValues);
    var defaultValue = $("#form"+Table.className+"InputColumnDefault");
    defaultValue.val(oldDefaultValue);
    var attributes = $("#form"+Table.className+"SelectColumnAttributes");
    attributes.val(oldAttributes);
    var nullable = $("#form"+Table.className+"CheckboxColumnNull");
    nullable.attr("checked",oldNullable==1);
    var auto_increment = $("#form"+Table.className+"CheckboxColumnAutoIncrement");
    auto_increment.attr("checked",oldAutoIncrement==1);
    
    $("#div"+this.className+"Dialog").dialog({
        title:dictionary["s12"],
        modal:true,
        resizable:false,
        width:400,
        open:function(){
            $('.ui-widget-overlay').css('position', 'fixed');
        },
        close:function(){
            name.val("");
            type.val("HTML");
            lengthValues.val("");
            defaultValue.val("");
            attributes.val("");
            nullable.attr("checked",false);
            auto_increment.attr("checked",false);
            //$('.ui-widget-overlay').css('position', 'absolute');
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
                
                if ( !Test.variableValidation(name.val(),false))
                {
                    var oldValue = name.val();
                    var newValue = Test.convertVariable(name.val(),false);
                    name.val(newValue);
                    Methods.alert(dictionary["s1"].format(oldValue,newValue), "info", dictionary["s2"]);
                    return;
                }
                
                //structGrid mod start
                var rowStruct = structGrid.dataSource.data()[index];
                rowStruct["name"]=name.val();
                rowStruct["type"]=type.val();
                rowStruct["lengthValues"]=lengthValues.val();
                rowStruct["defaultValue"]=defaultValue.val();
                rowStruct["attributes"]=attributes.val();
                rowStruct["nullable"]=nullable.is(":checked")?1:0;
                rowStruct["auto_increment"]=auto_increment.is(":checked")?1:0;
                Table.uiRefreshStructureGrid();
                
                //dataGrid mod start
                var dataGrid = $("#div"+thisClass.className+"GridData").data('kendoGrid');
                
                var templateSet = false;
                
                var ftype="string";
                var fdefault=defaultValue.val();
                switch(type.val()){
                    case "tinyint":
                    case "smallint":
                    case "mediumint":
                    case "int":
                    case "bigint":
                    case "decimal":
                    case "float":
                    case "double":
                    case "real":
                    case "boolean":
                    case "bit":
                    case "serial":{
                        ftype="number";
                        fdefault=0;
                        break;
                    }
                }
                //delete Table.dataGridSchemaFields[oldName];
                Table.dataGridSchemaFields[name.val()] = {
                    type:ftype,
                    defaultValue:fdefault,
                    editable: type.val()!="HTML"
                }
        
                dataGrid.columns[index] = {
                    title:name.val(),
                    field:name.val()
                };
                
                dataGrid.columns[index].editor = Table.stringEditor;
                
                switch(type.val()){
                    case "tinyint":
                    case "smallint":
                    case "mediumint":
                    case "int":
                    case "bigint":
                    case "decimal":
                    case "float":
                    case "double":
                    case "real":
                    case "boolean":
                    case "bit":
                    case "serial":{
                        dataGrid.columns[index].editor = Table.numberEditor;
                        break;
                    }
                    case "HTML":{
                        dataGrid.columns[index].editor = Table.htmlEditor;
                        dataGrid.columns[index].template = '<div class="horizontalMargin" align="center">'+
                        '<span class="spanIcon tooltipTableStructure ui-icon ui-icon-document-b" onclick="Table.uiChangeHTML($(this).next(),\''+name.val()+'\')" title="'+dictionary["s130"]+'"></span>'+
                        '<textarea class="notVisible">#='+name.val()+'#</textarea>'+
                        '</div>';
                        templateSet = true;
                        break;
                    }
                }
                
                if(nullable.is(":checked") && !templateSet) dataGrid.columns[index].template="#= "+name.val()+"==null?'<span style=\"font-style:italic;\"><b>null</b></span>':"+name.val()+" #";
                    
                for(var i=0;i<dataGrid.dataSource.data().length;i++){
                    var item = dataGrid.dataSource.data()[i];
                    item[name.val()]=item[oldName];
                    if(oldName!=name.val()){
                        delete item[oldName];
                        delete item.fields[oldName]
                        delete item.defaults[oldName]
                    }
                    item.fields[name.val()]={
                        type:ftype,
                        defaultValue:fdefault,
                        editable: type.val()!="HTML"
                    }
                    item.defaults[name.val()]=fdefault;
                }
        
                Table.uiRefreshDataGrid();
                
                if(type.val()!=oldType)
                {
                //fill
                }
                
                $(this).dialog("close");
                
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
        open:function(){
            $('.ui-widget-overlay').css('position', 'fixed');
        },
        close:function(){
        //$('.ui-widget-overlay').css('position', 'absolute');
        },
        buttons:[{
            text:dictionary["s265"],
            click:function(){
                var delimeter = $("#inputTableCSVExportDelimeter").val();
                var enclosure = $("#inputTableCSVExportEnclosure").val();
                
                if($.trim(delimeter)=="" || $.trim(enclosure)==""){
                    Methods.alert(dictionary["s334"], "alert", dictionary["s25"]);
                    return;
                }
                
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
            open:function(){
                $('.ui-widget-overlay').css('position', 'fixed');  
            },
            close:function(){
            //$('.ui-widget-overlay').css('position', 'absolute');  
            },
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
                        switch(parseInt(data.result)){
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
        //$('.ui-widget-overlay').css('position', 'absolute');
        },
        beforeClose:function(){
            
        },
        open:function(){
            $('.ui-widget-overlay').css('position', 'fixed');
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
                    Methods.modalProgress();
                    $("#div"+Table.className+"DialogImportCSV").dialog("close");
                },
                progress: function(e,data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    Methods.changeProgress(progress);
                },
                done: function (e, data) {
                    $.each(data.result, function (index, file) {
                        Table.isFileUploaded = true;
                        var delimeter = $("#inputTableCSVImportDelimeter").val();
                        var enclosure = $("#inputTableCSVImportEnclosure").val();
                        
                        if($.trim(delimeter)=="" || $.trim(enclosure)==""){
                            Methods.alert(dictionary["s334"], "alert", dictionary["s25"]);
                            return;
                        }
                            
                        Methods.confirm(dictionary["s28"], dictionary["s29"], function(){
                            $("#div"+Table.className+"DialogImportCSV").parent().mask(dictionary["s319"]);
                            $.post("query/Table_csv_import.php",{
                                oid:Table.currentID,
                                file:file.name,
                                delimeter:delimeter,
                                enclosure:enclosure,
                                header:$("#inputTableCSVImportHeader").is(":checked")?1:0
                            },function(data){
                                $("#div"+Table.className+"DialogImportCSV").parent().unmask();
                                $("#div"+Table.className+"DialogImportCSV").dialog("close");
                                switch(parseInt(data.result)){
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

Table.stringEditor = function(container,options){
    $("<textarea style='resize:none; margin:auto; width:100%; height:100px;' data-bind='value:" + options.field + "' />").appendTo(container);
}

Table.setEditor = function(container,options){
    var grid = $("#div"+Table.className+"GridStructure").data('kendoGrid');
    var items = grid.dataSource.data();
    
    var editor = $("<select style='resize:none; margin:auto; width:100%;' data-bind='value:" + options.field + "' />");
    var col = null;
    for(var i=0;i<items.length;i++){
        if(items[i].name==options.field) {
            col = items[i];
            break;
        }
    }
    
    if(col.nullable==1){
        editor.html("<option>&lt;"+dictionary["s73"]+"&gt;</option>");
    }
    
    if(col.lengthValues.trim()!=""){
        var options = col.lengthValues.split("','");
        
        if(options.length>0){
            if(options[0].charAt(0)=="'") options[0] = options[0].substr(1);
            
            var last = options[options.length-1];
            if(last.charAt(last.length-1)=="'") {
                last = last.substr(0,last.length-1);
                options[options.length-1] = last;
            }
        }
        
        for(var i=0;i<options.length;i++){
            editor.html(editor.html()+"<option value='"+options[i]+"'>"+options[i]+"</option>");
        }
    }
    
    editor.appendTo(container);
}

Table.numberEditor = function(container,options){
    $("<input type='text' style='resize:none; margin:auto; width:100%;' data-bind='value:" + options.field + "' />").appendTo(container);
}
Table.htmlEditor = function(container,options){
    $("<textarea style='resize:none; margin:auto; width:100%; height:100px;' data-bind='value:" + options.field + "' />").appendTo(container);
}

Table.uiAddColumn=function(){
    var thisClass = this;
    
    var name = $("#form"+Table.className+"InputColumnName");
    var type = $("#form"+Table.className+"SelectColumnType");
    var lengthValues = $("#form"+Table.className+"InputColumnLength");
    var defaultValue = $("#form"+Table.className+"InputColumnDefault");
    var attributes = $("#form"+Table.className+"SelectColumnAttributes");
    var nullable = $("#form"+Table.className+"CheckboxColumnNull");
    var auto_increment = $("#form"+Table.className+"CheckboxColumnAutoIncrement");
    
    $("#div"+this.className+"Dialog").dialog({
        title:dictionary["s31"],
        resizable:false,
        modal:true,
        width:400,
        open:function(){
            $('.ui-widget-overlay').css('position', 'fixed');  
        },
        close:function(){
            name.val("");
            type.val(1);
            //$('.ui-widget-overlay').css('position', 'absolute');
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
                
                if ( !Test.variableValidation(name.val(),false))
                {
                    var oldValue = name.val();
                    var newValue = Test.convertVariable(name.val(),false);
                    name.val(newValue);
                    Methods.alert(dictionary["s1"].format(oldValue,newValue), "info", dictionary["s2"]);
                    return;
                }
                
                if(Table.doesColumnExists(name.val())) 
                {
                    Methods.alert(dictionary["s15"], "alert", dictionary["s14"]);
                    return;
                }
                
                var structGrid = $("#div"+thisClass.className+"GridStructure").data('kendoGrid');
                structGrid.dataSource.add({
                    name:name.val(),
                    type:type.val(),
                    lengthValues:lengthValues.val(),
                    defaultValue:defaultValue.val(),
                    attributes:attributes.val(),
                    nullable:nullable.is(":checked")?1:0,
                    auto_increment:auto_increment.is(":checked")?1:0
                })
                
                //dataGrid mod start
                var dataGrid = $("#div"+thisClass.className+"GridData").data('kendoGrid');
                
                var templateSet = false;
                
                var ftype="string";
                var fdefault=defaultValue.val();
                switch(type.val()){
                    case "tinyint":
                    case "smallint":
                    case "mediumint":
                    case "int":
                    case "bigint":
                    case "decimal":
                    case "float":
                    case "double":
                    case "real":
                    case "boolean":
                    case "bit":
                    case "serial":{
                        ftype="number";
                        fdefault=0;
                        break;
                    }
                }
                Table.dataGridSchemaFields[name.val()] = {
                    type:ftype,
                    defaultValue:fdefault
                }
                
                var col = {
                    title:name.val(),
                    field:name.val()
                }
                
                col.editor = Table.stringEditor;
                
                switch(type.val()){
                    case "tinyint":
                    case "smallint":
                    case "mediumint":
                    case "int":
                    case "bigint":
                    case "decimal":
                    case "float":
                    case "double":
                    case "real":
                    case "boolean":
                    case "bit":
                    case "serial":{
                        col.editor = Table.numberEditor;
                        break;
                    }
                    case "HTML":{
                        col.editor = Table.htmlEditor;
                        //Table.dataGridSchemaFields[name.val()].editable = false;
                        col.template = '<div class="horizontalMargin" align="center">'+
                        '<span class="spanIcon tooltipTableStructure ui-icon ui-icon-document-b" onclick="Table.uiChangeHTML($(this).next(),\''+name.val()+'\')" title="'+dictionary["s130"]+'"></span>'+
                        '<textarea class="notVisible">#='+name.val()+'#</textarea>'+
                        '</div>';
                        templateSet = true;
                        break;
                    }
                }
        
                if(nullable.is(":checked") && !templateSet) col["template"]="#= "+name.val()+"==null?'<span style=\"font-style:italic;\"><b>null</b></span>':"+name.val()+" #";
                
                dataGrid.columns.splice(dataGrid.columns.length-1,0,col);
                
                for(var i=0;i<dataGrid.dataSource.data().length;i++){
                    var row = dataGrid.dataSource.data()[i];
                    row[name.val()]=fdefault;
                    row.fields[name.val()]={
                        type:ftype,
                        defaultValue:fdefault,
                        editable: type.val()!="HTML"
                    }
                    row.defaults[name.val()]=fdefault;
                }
        
                Table.uiRefreshDataGrid();
                
                $(this).dialog("close");
                
                Methods.iniTooltips();
                Table.structureEmptyCheck();
                Table.uiIniHTMLTooltips();
            //dataGrid mod end
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

Table.uiRemoveRow=function(obj){
    var thisClass=this;
    var index = obj.closest('tr')[0].sectionRowIndex;
    Methods.confirm(dictionary["s32"], dictionary["s33"], function(){
        var grid = $("#div"+thisClass.className+"GridData").data('kendoGrid');
        grid.removeRow(grid.tbody.find("tr:eq("+index+")"));
    });
}

Table.uiClearRows=function(){
    var thisClass=this;
    Methods.confirm(dictionary["s368"], dictionary["s367"], function(){
        var grid = $("#div"+thisClass.className+"GridData").data('kendoGrid');
    
        var columns = grid.columns;
        var items = [];
    
        Table.uiReloadDataGrid(items, columns);
    });
}

Table.uiChangeHTML=function(obj,field){
    var grid = $("#div"+this.className+"GridData").data('kendoGrid');
    var index = obj.closest("tr")[0].sectionRowIndex;
    var item = grid.dataItem(grid.tbody.find("tr:eq("+index+")"));
    $("#form"+Table.className+"TextareaHTML").val(obj.val());
    $("#div"+Table.className+"DialogHTML").dialog({
        title:dictionary["s36"],
        resizable:false,
        modal:true,
        width:840,
        open:function(){
            $('.ui-widget-overlay').css('position', 'fixed');
        },
        close:function(){
        //$('.ui-widget-overlay').css('position', 'absolute');  
        },
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
                item[field]=Methods.getCKEditorData($(this).find('textarea'));
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
        },
        position:{
            my: "left top", 
            at: "left bottom", 
            offset: "15 0"
        }
    }); 
}