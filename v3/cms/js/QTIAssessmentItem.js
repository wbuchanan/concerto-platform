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

function QTIAssessmentItem() { };
OModule.inheritance(QTIAssessmentItem);

QTIAssessmentItem.className="QTIAssessmentItem";

QTIAssessmentItem.onAfterEdit=function()
{
    };

QTIAssessmentItem.onAfterSave=function(isNewObject)
{
    Test.uiQTIAssessmentItemsChanged();
};

QTIAssessmentItem.onAfterDelete=function(){
    Test.uiQTIAssessmentItemsChanged();
}

QTIAssessmentItem.onAfterAdd=function(){
    Methods.iniCKEditor("#form"+this.className+"TextareaDescription",function(){
        $("#divAddFormDialog").dialog("option","width",990);
        $("#divAddFormDialog").dialog("option","position","center"); 
    });
    if(QTIAssessmentItem.formCodeMirror!=null) QTIAssessmentItem.formCodeMirror.refresh();
}

QTIAssessmentItem.formCodeMirror=null;
QTIAssessmentItem.getAddSaveObject=function()
{
    return { 
        oid:this.currentID,
        class_name:this.className,
        name:$("#form"+this.className+"InputName").val(),
        XML:$("#form"+this.className+"TextareaXML").val(),
        description:Methods.getCKEditorData("#form"+this.className+"TextareaDescription"),
        Sharing_id:$("#form"+this.className+"SelectSharing").val()
    };
};

QTIAssessmentItem.getFullSaveObject = function(){
    var obj = this.getAddSaveObject();
    if($("#form"+this.className+"SelectOwner").length==1) obj["Owner_id"]=$("#form"+this.className+"SelectOwner").val();
    return obj;
}

QTIAssessmentItem.uiSaveValidate=function(ignoreOnBefore){
    if(!this.checkRequiredFields([
        $("#form"+this.className+"InputName").val()
    ])) {
        Methods.alert(dictionary["s415"],"alert");
        return false;
    }
    QTIAssessmentItem.uiSaveValidated(ignoreOnBefore);
}