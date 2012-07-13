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

function Setup() { };
Setup.path_external = "";

Setup.steps=[];

Setup.continueSteps = true;
Setup.currentStep=-1;
Setup.maxStep = 0;

Setup.continueDBSteps = true;
Setup.currentDBStep = -1;
Setup.maxDBStep = 0;
    
Setup.initialize=function(){
    $("#divSetupProgressBar").progressbar();
    $("#divSetupDBProgressBar").progressbar();
}
    
Setup.run=function(){
    if(!Setup.continueSteps) {
        Setup.failure();
        return;
    }
    Setup.currentStep++;
    if(Setup.currentStep==Setup.maxStep) {
        Setup.success();
        return;
    }
    Setup.steps[Setup.currentStep].check();
}

Setup.updateProgressBar=function(title,db){
    if(db==null) db=false;
    var value = (db?Setup.currentDBStep:Setup.currentStep)+1;
    var maxValue = (db?Setup.maxDBStep:Setup.maxStep);
    $("#divSetup"+(db?"DB":"")+"ProgressBar").progressbar("value",Math.floor(value/maxValue*100));
    $("#tdCurrent"+(db?"DB":"")+"Step").html(title);
}

Setup.failure=function(){
    $("body").append('<h1 class="ui-state-error" align="center">Please correct your problems using recommendations and run the test again.</h1>');
    
    $("#tdLoadingStep").css("visibility","hidden");
    $("#tdCurrentStep").html("<font style='color:red'><b>failed to finish</b></font>");
}

Setup.success=function(){
    $("body").append('<h1 class="" align="center" style="color:green;">Test completed. Every item passed correctly.</h1>');
    $("body").append("<h1 class='ui-state-highlight' align='center' style='color:blue;'>IT IS STRONGLY RECOMMENDED TO DELETE THIS <b>/setup</b> DIRECTORY NOW!</h1>");
    $("body").append('<h2 class="" align="center"><a href="'+Setup.path_external+'cms/index.php">click here to launch Concerto Platform panel</a> - if this is fresh installation of Concerto then default admin account is <b>login:admin/password:admin</b></h2>');
    
    $("#tdLoadingStep").css("visibility","hidden");
    $("#tdCurrentStep").html("<font style='color:green'><b>finished successfuly</b></font>");
}
    
Setup.insertCheckRow=function(title,db){
    if(db==null) db=false;
    var row = "<tr id='row"+(db?"DB":"")+"-"+(db?Setup.currentDBStep:Setup.currentStep)+"'>"+
    "<td class='ui-widget-content' id='col"+(db?"DB":"")+"-"+(db?Setup.currentDBStep:Setup.currentStep)+"-0'>"+title+"</td>"+
    "<td class='ui-widget-content' id='col"+(db?"DB":"")+"-"+(db?Setup.currentDBStep:Setup.currentStep)+"-1'>please wait...</td>"+
    "<td class='ui-widget-content' id='col"+(db?"DB":"")+"-"+(db?Setup.currentDBStep:Setup.currentStep)+"-2'>-</td>"+
    "</tr>";
    if(db){
        $(row).insertBefore("#row-7");
    } else {
        $("#tbodySetup").append(row);
    }
}

Setup.check=function(obj,check,success,failure){
    if(check=="concerto_version"){
        Methods.checkLatestVersion(function(isNewerVersion,version){
            if(isNewerVersion==1) failure.call(obj,version);
            else success.call(obj,version);
        },"../cms/lib/jfeed/proxy.php");
        return;
    }
    
    if(check=="getDBSteps"){
        Setup.getDBSteps(obj,success,failure);
        return;
    }
    
    $.post("Setup.php",{
        check:check
    },function(data){
        switch(data.result){
            case 0:{
                success.call(obj,data.param);
                break;
            }
            default:{
                failure.call(obj,data.param);
                break;
            }
        }
    },"json");
}

Setup.versions = [];
Setup.create_db = false;
Setup.validate_column_names = false;
Setup.repopulate_TestTemplate = false;
Setup.recalculate_hash = false;
Setup.getDBSteps=function(obj,success,failure){
    $.post("Setup.php",{
        check:"get_db_update_steps_count"
    },function(data){
        var count = 0;
        if(data.create_db) count++;
        count+=data.versions.length;
        if(data.validate_column_names) count++;
        if(data.repopulate_TestTemplate) count++;
        if(data.recalculate_hash) count++;
        Setup.maxDBStep = count;
        Setup.create_db = data.create_db;
        Setup.versions = data.versions;
        Setup.validate_column_names = data.validate_column_names;
        Setup.repopulate_TestTemplate = data.repopulate_TestTemplate;
        Setup.recalculate_hash = data.recalculate_hash;
        
        Setup.runDB(obj,success,failure);
    },"json");
}

Setup.runDB=function(obj,success,failure){
    if(!Setup.continueDBSteps) {
        Setup.failureDB(obj,failure);
        return;
    }
    Setup.currentDBStep++;
    if(Setup.currentDBStep==Setup.maxDBStep) {
        Setup.successDB(obj,success);
        return;
    }
    
    var offset = 0;
    if(Setup.create_db){
        offset = 1;
        if(Setup.currentDBStep==0) {
            Setup.checkCreateDB();
            return;
        }
    }
    
    if(Setup.currentDBStep<Setup.versions.length+offset) {
        Setup.checkDatabaseUpdate(Setup.versions[Setup.currentDBStep-offset]);
    } else {
        if(Setup.validate_column_names){
            Setup.validate_column_names=false;
            Setup.checkDatabaseValidateColumnNames();
            return;
        }
        if(Setup.repopulate_TestTemplate){
            Setup.repopulate_TestTemplate=false;
            Setup.checkDatabaseRepopulateTestTemplate();
            return;
        }
        if(Setup.recalculate_hash){
            Setup.recalculate_hash=false;
            Setup.checkDatabaseRecalculateHash();
            return;
        }
    };
}

Setup.failureDB=function(obj,failure){
    $("#tdLoadingDBStep").css("visibility","hidden");
    $("#tdCurrentDBStep").html("<font style='color:red'><b>failed to finish</b></font>");
    
    failure.call(obj);
}

Setup.successDB=function(obj,success){
    $("#tdLoadingDBStep").css("visibility","hidden");
    $("#tdCurrentDBStep").html("<font style='color:green'><b>finished successfuly</b></font>");
    
    success.call(obj);
}

//version update
Setup.checkDatabaseUpdate=function(version){
    var title = "<b>MySQL</b> database update to version <b>"+version+"</b>";
    Setup.insertCheckRow(title,true);
    Setup.updateProgressBar(title,true);
    
    $.post("Setup.php",{
        check:"update_db"
    },function(data){
        switch(data.result){
            case 0:{
                Setup.checkDatabaseUpdateSuccess(Setup.versions[Setup.currentDBStep+(Setup.create_db?1:0)]);
                break;
            }
            case 1:{
                Setup.checkDatabaseUpdateFailure(version,data.msg);
                break;
            }
            default:{
                Setup.checkDatabaseUpdateFailure(version,data.msg);
                break;
            }
        }
    },"json");
}
Setup.checkDatabaseUpdateSuccess=function(version){
    $("#colDB-"+Setup.currentDBStep+"-1").html("<b>MySQL</b> database update to <b>v"+version+"</b> - <b style='color:green;'>PASSED</b>");
    Setup.runDB();
}
    
Setup.checkDatabaseUpdateFailure=function(version,msg){
    $("#colDB-"+Setup.currentDBStep+"-1").removeClass("ui-state-highlight");
    $("#colDB-"+Setup.currentDBStep+"-1").addClass("ui-state-error");
    
    $("#colDB-"+Setup.currentDBStep+"-1").html("<b>MySQL</b> database update to <b>v"+version+"</b> failed with '<b>"+msg+"</b>' - <b style='color:red;'>FAILED</b>");
    $("#colDB-"+Setup.currentDBStep+"-2").html("Setup application was unable to create valid database structure.");
    
    Setup.continueDBSteps = false;
    Setup.runDB();
}

//validate_column_names
Setup.checkDatabaseValidateColumnNames=function(){
    var title = "<b>MySQL</b> database - validate column names";
    Setup.insertCheckRow(title,true);
    Setup.updateProgressBar(title,true);
    
    $.post("Setup.php",{
        check:"update_db_validate_column_names"
    },function(data){
        switch(data.result){
            case 0:{
                Setup.checkDatabaseValidateColumnNamesSuccess();
                break;
            }
            case 1:{
                Setup.checkDatabaseValidateColumnNamesFailure();
                break;
            }
            default:{
                Setup.checkDatabaseValidateColumnNamesFailure();
                break;
            }
        }
    },"json");
}
Setup.checkDatabaseValidateColumnNamesSuccess=function(){
    $("#colDB-"+Setup.currentDBStep+"-1").html("<b>MySQL</b> database update - validate column names - <b style='color:green;'>PASSED</b>");
    Setup.runDB();
}
    
Setup.checkDatabaseValidateColumnNamesFailure=function(){
    $("#colDB-"+Setup.currentDBStep+"-1").removeClass("ui-state-highlight");
    $("#colDB-"+Setup.currentDBStep+"-1").addClass("ui-state-error");
    
    $("#colDB-"+Setup.currentDBStep+"-1").html("<b>MySQL</b> database - validate column names - <b style='color:red;'>FAILED</b>");
    $("#colDB-"+Setup.currentDBStep+"-2").html("Setup application was unable to validate column names.");
    
    Setup.continueDBSteps = false;
    Setup.runDB();
}

//repopulate_TestTemplate
Setup.checkDatabaseRepopulateTestTemplate=function(){
    var title = "<b>MySQL</b> database - repopulate TestTemplate";
    Setup.insertCheckRow(title,true);
    Setup.updateProgressBar(title,true);
    
    $.post("Setup.php",{
        check:"update_db_repopulate_TestTemplate"
    },function(data){
        switch(data.result){
            case 0:{
                Setup.checkDatabaseRepopulateTestTemplateSuccess();
                break;
            }
            case 1:{
                Setup.checkDatabaseRepopulateTestTemplateFailure();
                break;
            }
            default:{
                Setup.checkDatabaseRepopulateTestTemplateFailure();
                break;
            }
        }
    },"json");
}
Setup.checkDatabaseRepopulateTestTemplateSuccess=function(){
    $("#colDB-"+Setup.currentDBStep+"-1").html("<b>MySQL</b> database update - repopulate TestTemplate - <b style='color:green;'>PASSED</b>");
    Setup.runDB();
}
    
Setup.checkDatabaseRepopulateTestTemplateFailure=function(){
    $("#colDB-"+Setup.currentDBStep+"-1").removeClass("ui-state-highlight");
    $("#colDB-"+Setup.currentDBStep+"-1").addClass("ui-state-error");
    
    $("#colDB-"+Setup.currentDBStep+"-1").html("<b>MySQL</b> database - repopulate TestTemplate - <b style='color:red;'>FAILED</b>");
    $("#colDB-"+Setup.currentDBStep+"-2").html("Setup application was unable to repopulate TestTemplate.");
    
    Setup.continueDBSteps = false;
    Setup.runDB();
}

//recalculate_hash
Setup.checkDatabaseRecalculateHash=function(){
    var title = "<b>MySQL</b> database - recalculate hash";
    Setup.insertCheckRow(title,true);
    Setup.updateProgressBar(title,true);
    
    $.post("Setup.php",{
        check:"update_db_recalculate_hash"
    },function(data){
        switch(data.result){
            case 0:{
                Setup.checkDatabaseRecalculateHashSuccess();
                break;
            }
            case 1:{
                Setup.checkDatabaseRecalculateHashFailure();
                break;
            }
            default:{
                Setup.checkDatabaseRecalculateHashFailure();
                break;
            }
        }
    },"json");
}
Setup.checkDatabaseRecalculateHashSuccess=function(){
    $("#colDB-"+Setup.currentDBStep+"-1").html("<b>MySQL</b> database update - recalculate hash - <b style='color:green;'>PASSED</b>");
    Setup.runDB();
}
    
Setup.checkDatabaseRecalculateHashFailure=function(){
    $("#colDB-"+Setup.currentDBStep+"-1").removeClass("ui-state-highlight");
    $("#colDB-"+Setup.currentDBStep+"-1").addClass("ui-state-error");
    
    $("#colDB-"+Setup.currentDBStep+"-1").html("<b>MySQL</b> database - recalculate hash - <b style='color:red;'>FAILED</b>");
    $("#colDB-"+Setup.currentDBStep+"-2").html("Setup application was unable to recalculate hash.");
    
    Setup.continueDBSteps = false;
    Setup.runDB();
}

//create db
Setup.checkCreateDB=function(){
    var title = "<b>MySQL</b> database update - create missing tables";
    Setup.insertCheckRow(title,true);
    Setup.updateProgressBar(title,true);
    
    $.post("Setup.php",{
        check:"create_db"
    },function(data){
        switch(data.result){
            case 0:{
                Setup.checkCreateDBSuccess();
                break;
            }
            case 1:{
                Setup.checkCreateDBFailure();
                break;
            }
            default:{
                Setup.checkCreateDBFailure();
                break;
            }
        }
    },"json");
}
Setup.checkCreateDBSuccess=function(){
    $("#colDB-"+Setup.currentDBStep+"-1").html("<b>MySQL</b> database update - create  missing tables - <b style='color:green;'>PASSED</b>");
    Setup.runDB();
}
    
Setup.checkCreateDBFailure=function(){
    $("#colDB-"+Setup.currentDBStep+"-1").removeClass("ui-state-highlight");
    $("#colDB-"+Setup.currentDBStep+"-1").addClass("ui-state-error");
    
    $("#colDB-"+Setup.currentDBStep+"-1").html("<b>MySQL</b> database update - create missing tables - <b style='color:red;'>FAILED</b>");
    $("#colDB-"+Setup.currentDBStep+"-2").html("Setup application was unable to create missing database tables.");
    
    Setup.continueDBSteps = false;
    Setup.runDB();
}

function SetupStep(db,title,method,successCaption,failureCaption,failureReccomendation,required,successCallback,failureCallback){
    this.db = false;
    if(db!=null) this.db = db;
    this.title = "";
    if(title!=null) this.title = title;
    this.method = "";
    if(method!=null) this.method = method;
    this.successCaption = "";
    if(successCaption!=null) this.successCaption = successCaption;
    this.failureCaption = "";
    if(failureCaption!=null) this.failureCaption = failureCaption;
    this.failureReccomendation = "";
    if(failureReccomendation!=null) this.failureReccomendation = failureReccomendation;
    this.required = true;
    if(required!=null) this.required = required;
    this.successCallback = function(){};
    if(successCallback!=null) this.successCallback = successCallback;
    this.failureCallback = function(){}
    if(failureCallback!=null) this.failureCallback = failureCallback;
    
    this.check=function(){
        Setup.insertCheckRow(this.title,this.db);
        Setup.updateProgressBar(this.title,this.db);
        
        Setup.check(this,this.method, this.success, this.failure)
    }
    
    this.success=function(param){
        $("#col"+(this.db?"DB":"")+"-"+(this.db?Setup.currentDBStep:Setup.currentStep)+"-1").html(this.successCaption.format(param));
        if(this.db){
            Setup.runDB();
        } else {
            Setup.run();
        }
        this.successCallback();
    }
    
    this.failure=function(param){
        $("#col"+(this.db?"DB":"")+"-"+(this.db?Setup.currentDBStep:Setup.currentStep)+"-1").removeClass("ui-state-highlight");
        $("#col"+(this.db?"DB":"")+"-"+(this.db?Setup.currentDBStep:Setup.currentStep)+"-1").addClass("ui-state-error");
    
        $("#col"+(this.db?"DB":"")+"-"+(this.db?Setup.currentDBStep:Setup.currentStep)+"-1").html(this.failureCaption.format(param));
        $("#col"+(this.db?"DB":"")+"-"+(this.db?Setup.currentDBStep:Setup.currentStep)+"-2").html(this.failureReccomendation.format(param));
    
        if(this.db){
            Setup.continueDBSteps = !this.required;
            Setup.runDB();
        } else {
            Setup.continueSteps = !this.required;
            Setup.run();
        }
        this.failureCallback();
    }
}

Setup.steps = [
    new SetupStep(
        false,
        "Check for the latest <b>Concerto Platform</b> version",
        "concerto_version",
        "your current version: <b>v{0}</b> <b style='color:green;'>IS UP TO DATE</b>",
        "newer version is available: <b>v{0}</b>. Your current version <b style='color:red;'>IS OUTDATED</b>",
        "You can find the latest version at the link below:<br/><a href='http://code.google.com/p/concerto-platform'>http://code.google.com/p/concerto-platform</a>",
        false
        ),
    new SetupStep(
        false,
        "PHP version at least <b>v5.3</b>",
        "php_version_check",
        "your PHP version: <b>{0}</b> - <b style='color:green;'>PASSED</b>",
        "your PHP version: <b>{0}</b> - <b style='color:red;'>FAILED</b>",
        "Update your PHP to v5.3 or higher.",
        true     
        ),
    new SetupStep(
        false,
        "PHP <b>'safe mode'</b> must be turned <b>OFF</b>",
        "php_safe_mode_check",
        "your PHP <b>'safe mode'</b> is turned <b>OFF</b> - <b style='color:green;'>PASSED</b>",
        "your PHP <b>'safe mode'</b> is turned <b>ON</b> - <b style='color:red;'>FAILED</b>",
        "Ask your server administrator to turn PHP 'safe mode' OFF.",
        true     
        ),
    new SetupStep(
        false,
        "PHP <b>'magic quotes'</b> must be turned <b>OFF</b>",
        "php_magic_quotes_check",
        "your PHP <b>'magic quotes'</b> is turned <b>OFF</b> - <b style='color:green;'>PASSED</b>",
        "your PHP <b>'magic quotes'</b> is turned <b>ON</b> - <b style='color:red;'>FAILED</b>",
        "Ask your server administrator to turn PHP 'magic quotes' OFF.",
        true     
        ),
    new SetupStep(
        false,
        "PHP <b>'short open tags'</b> must be turned <b>ON</b>",
        "php_short_open_tag_check",
        "your PHP <b>'short open tags'</b> is turned <b>ON</b> - <b style='color:green;'>PASSED</b>",
        "your PHP <b>'short open tags'</b> is turned <b>OFF</b> - <b style='color:red;'>FAILED</b>",
        "Ask your server administrator to turn PHP 'short open tags' ON.",
        true     
        ),
    new SetupStep(
        false,
        "<b>MySQL</b> connection test",
        "mysql_connection_check",
        "{0} <b>CONNECTED</b> - <b style='color:green;'>PASSED</b>",
        "{0} <b>CAN'T CONNECT</b> - <b style='color:red;'>FAILED</b>",
        "Set <b>db_host, db_port, db_user, db_password</b> in /SETTINGS.php file.",
        true     
        ),
    new SetupStep(
        false,
        "<b>MySQL</b> database connection test",
        "mysql_select_db_check",
        "<b>MySQL</b> database <b>{0}</b> <b>IS CONNECTABLE</b> - <b style='color:green;'>PASSED</b>",
        "<b>MySQL</b> database <b>{0}</b> <b>IS NOT CONNECTABLE</b> - <b style='color:red;'>FAILED</b>",
        "Set <b>db_name</b> in <b>/SETTINGS.php</b> file. Check if database name is correct and if it is - check if MySQL user has required permissions to access this database.",
        true     
        ),
    new SetupStep(
        false,
        "<b>MySQL</b> database tables structure test",
        "getDBSteps",
        "<b>MySQL</b> database tables structure <b>IS CORRECT</b> - <b style='color:green;'>PASSED</b>",
        "<b>MySQL</b> database tables structure <b>IS NOT CORRECT</b> - <b style='color:red;'>FAILED</b>",
        "Setup application was unable to create valid database structure. Please restore database from the backup and revert Concerto to previous version.",
        true     
        ),
    new SetupStep(
        false,
        "<b>Rscript</b> file path must be set.",
        "rscript_check",
        "your <b>Rscript</b> file path: <b>{0}</b> <b>EXISTS</b> - <b style='color:green;'>PASSED</b>",
        "your <b>Rscript</b> file path: <b>{0}</b> <b>DOESN'T EXISTS</b> - <b style='color:red;'>FAILED</b>",
        "Rscript file path not set, set incorrectly or unaccesible to PHP.<br/>Usually the Rscript file path is <b>/usr/bin/Rscript</b>. Set your Rscript path in <b>/SETTINGS.php</b> file.",
        true     
        ),
    new SetupStep(
        false,
        "<b>PHP</b> executable file path must be set.",
        "php_exe_path_check",
        "your <b>PHP</b> executable file path: <b>{0}</b> <b>EXIST</b> - <b style='color:green;'>PASSED</b>",
        "your <b>PHP</b> executable file path: <b>{0}</b> <b>DOESN'T EXIST</b> - <b style='color:red;'>FAILED</b>",
        "PHP executable file path not set, set incorrectly or unaccesible to PHP.<br/>Usually the PHP executable file path is <b>/usr/bin/php</b>. Set your PHP executable path in <b>/SETTINGS.php</b> file.",
        true     
        ),
    new SetupStep(
        false,
        "<b>R</b> executable file path must be set.",
        "R_exe_path_check",
        "your <b>R</b> executable file path: <b>{0}</b> <b>EXIST</b> - <b style='color:green;'>PASSED</b>",
        "your <b>R</b> executable file path: <b>{0}</b> <b>DOESN'T EXIST</b> - <b style='color:red;'>FAILED</b>",
        "R executable file path not set, set incorrectly or unaccesible to PHP.<br/>Usually the R executable file path is <b>/usr/bin/R</b>. Set your R executable path in <b>/SETTINGS.php</b> file.",
        true     
        ),
    new SetupStep(
        false,
        "<b>media</b> directory path must be writable",
        "media_directory_writable_check",
        "your <b>media</b> directory: <b>{0}</b> <b>IS WRITABLE</b> - <b style='color:green;'>PASSED</b>",
        "your <b>media</b> directory: <b>{0}</b> <b>IS NOT WRITABLE</b> - <b style='color:red;'>FAILED</b>",
        "Set <b>media</b> directory rigths to 0777.",
        true     
        ),
    new SetupStep(
        false,
        "<b>socks</b> directory path must be writable",
        "socks_directory_writable_check",
        "your <b>socks</b> directory: <b>{0}</b> <b>IS WRITABLE</b> - <b style='color:green;'>PASSED</b>",
        "your <b>socks</b> directory: <b>{0}</b> <b>IS NOT WRITABLE</b> - <b style='color:red;'>FAILED</b>",
        "Set <b>socks</b> directory rigths to 0777.",
        true     
        ),
    new SetupStep(
        false,
        "<b>temp</b> directory path must be writable",
        "temp_directory_writable_check",
        "your <b>temp</b> directory: <b>{0}</b> <b>IS WRITABLE</b> - <b style='color:green;'>PASSED</b>",
        "your <b>temp</b> directory: <b>{0}</b> <b>IS NOT WRITABLE</b> - <b style='color:red;'>FAILED</b>",
        "Set <b>temp</b> directory rigths to 0777.",
        true     
        ),
    new SetupStep(
        false,
        "<b>files</b> directory path must be writable",
        "files_directory_writable_check",
        "your <b>files</b> directory: <b>{0}</b> <b>IS WRITABLE</b> - <b style='color:green;'>PASSED</b>",
        "your <b>files</b> directory: <b>{0}</b> <b>IS NOT WRITABLE</b> - <b style='color:red;'>FAILED</b>",
        "Set <b>files</b> directory rigths to 0777.",
        true     
        ),
    new SetupStep(
        false,
        "<b>cache</b> directory path must be writable",
        "cache_directory_writable_check",
        "your <b>cache</b> directory: <b>{0}</b> <b>IS WRITABLE</b> - <b style='color:green;'>PASSED</b>",
        "your <b>cache</b> directory: <b>{0}</b> <b>IS NOT WRITABLE</b> - <b style='color:red;'>FAILED</b>",
        "Set <b>cache</b> directory rigths to 0777.",
        true     
        ),
    new SetupStep(
        false,
        "<b>catR</b> R package must be installed.",
        "catR_r_package_check",
        "<b>catR</b> package <b>IS INSTALLED</b> - <b style='color:green;'>PASSED</b>",
        "<b>catR</b> package <b>IS NOT INSTALLED</b> - <b style='color:red;'>FAILED</b>",
        "Install <b>catR</b> package to main R library directory.",
        true     
        ),
    new SetupStep(
        false,
        "<b>session</b> R package must be installed.",
        "session_r_package_check",
        "<b>session</b> package <b>IS INSTALLED</b> - <b style='color:green;'>PASSED</b>",
        "<b>session</b> package <b>IS NOT INSTALLED</b> - <b style='color:red;'>FAILED</b>",
        "Install <b>session</b> package to main R library directory.",
        true     
        ),
    new SetupStep(
        false,
        "<b>RMySQL</b> R package must be installed.",
        "RMySQL_r_package_check",
        "<b>RMySQL</b> package <b>IS INSTALLED</b> - <b style='color:green;'>PASSED</b>",
        "<b>RMySQL</b> package <b>IS NOT INSTALLED</b> - <b style='color:red;'>FAILED</b>",
        "Install <b>RMySQL</b> package to main R library directory.",
        true     
        )
    ];
Setup.maxStep = Setup.steps.length;