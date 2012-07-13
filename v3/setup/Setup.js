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
    Setup.steps = [
    Setup.checkConcertoVersion,
    Setup.checkPHPVersion,
    Setup.checkPHPSafeModeVersion,
    Setup.checkPHPMagicQuotes,
    Setup.checkPHPShortOpenTags,
    Setup.checkMySQLConnection,
    Setup.checkMySQLDBConnection,
    Setup.checkDBStructure,
    Setup.checkRscript,
    Setup.checkRVersion,
    Setup.checkPHPPath,
    Setup.checkRPath,
    Setup.checkMediaDirWritable,
    Setup.checkSocksDirWritable,
    Setup.checkTempDirWritable,
    Setup.checkFilesDirWritable,
    Setup.checkCacheDirWritable,
    Setup.checkCatRRPackage,
    Setup.checkSessionRPackage,
    Setup.checkRMySQLRPackage
    ];
    Setup.maxStep = Setup.steps.length;
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
    Setup.steps[Setup.currentStep].call(this);
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

Setup.check=function(check,success,failure){
    $.post("Setup.php",{
        check:check
    },function(data){
        switch(data.result){
            case 0:{
                success.call(this,data.param);
                break;
            }
            default:{
                failure.call(this,data.param);
                break;
            }
        }
    },"json");
}

//Concerto version
Setup.checkConcertoVersion=function(){
    var title = "Check for the latest <b>Concerto Platform</b> version";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Methods.checkLatestVersion(function(isNewerVersion,version){
        if(isNewerVersion==1) Setup.checkConcertoVersionFailure.call(this);
        else Setup.checkConcertoVersionSuccess.call(this,version);
    },"../cms/lib/jfeed/proxy.php");
}
Setup.checkConcertoVersionFailure=function(version){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html("newer version is available: <b>v"+version+"</b>. Your current version <b>v"+Methods.currentVersion+"</b> <b style='color:red;'>IS OUTDATED</b>");
    $("#col-"+Setup.currentStep+"-2").html("You can find the latest version at the link below:<br/><a href='http://code.google.com/p/concerto-platform'>http://code.google.com/p/concerto-platform</a>");
    Setup.run();
    
}
Setup.checkConcertoVersionSuccess=function(){
    $("#col-"+Setup.currentStep+"-1").html("your current version: <b>v"+Methods.currentVersion+"</b> <b style='color:green;'>IS UP TO DATE</b>");
    Setup.run();
}

//PHP version
Setup.checkPHPVersion=function(){
    var title = "PHP version at least <b>v5.3</b>";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Setup.check("php_version_check",Setup.checkPHPVersionSuccess,Setup.checkPHPVersionFailure);
}
Setup.checkPHPVersionFailure=function(version){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html("your PHP version: <b>"+version+" - <b style='color:red;'>FAILED</b>");
    $("#col-"+Setup.currentStep+"-2").html("Update your PHP to v5.3 or higher.");
    
    Setup.continueSteps = false;
    Setup.run();
    
}
Setup.checkPHPVersionSuccess=function(version){
    $("#col-"+Setup.currentStep+"-1").html("your PHP version: <b>"+version+" - <b style='color:green;'>PASSED</b>");
    Setup.run();
}

//PHP safe mode
Setup.checkPHPSafeModeVersion=function(){
    var title = "PHP <b>'safe mode'</b> must be turned <b>OFF</b>";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Setup.check("php_safe_mode_check",Setup.checkPHPSafeModeVersionSuccess,Setup.checkPHPSafeModeVersionFailure);
}
Setup.checkPHPSafeModeVersionFailure=function(value){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html("your PHP <b>'safe mode'</b> is turned <b>ON</b> - <b style='color:red;'>FAILED</b>");
    $("#col-"+Setup.currentStep+"-2").html("Ask your server administrator to turn PHP 'safe mode' OFF.");
    
    Setup.continueSteps = false;
    Setup.run();
    
}
Setup.checkPHPSafeModeVersionSuccess=function(value){
    $("#col-"+Setup.currentStep+"-1").html("your PHP <b>'safe mode'</b> is turned <b>OFF</b> - <b style='color:green;'>PASSED</b>");
    Setup.run();
}

//PHP magic quotes
Setup.checkPHPMagicQuotes=function(){
    var title = "PHP <b>'magic quotes'</b> must be turned <b>OFF</b>";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Setup.check("php_magic_quotes_check",Setup.checkPHPMagicQuotesSuccess,Setup.checkPHPMagicQuotesFailure);
}
Setup.checkPHPMagicQuotesFailure=function(value){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html("your PHP <b>'magic quotes'</b> is turned <b>ON</b> - <b style='color:red;'>FAILED</b>");
    $("#col-"+Setup.currentStep+"-2").html("Ask your server administrator to turn PHP 'magic quotes' OFF.");
    
    Setup.continueSteps = false;
    Setup.run();
    
}
Setup.checkPHPMagicQuotesSuccess=function(value){
    $("#col-"+Setup.currentStep+"-1").html("your PHP <b>'magic quotes'</b> is turned <b>OFF</b> - <b style='color:green;'>PASSED</b>");
    Setup.run();
}

//PHP short open tags
Setup.checkPHPShortOpenTags=function(){
    var title = "PHP <b>'short open tag'</b> must be turned <b>ON</b>";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Setup.check("php_short_open_tag_check",Setup.checkPHPShortOpenTagsSuccess,Setup.checkPHPShortOpenTagsFailure);
}
Setup.checkPHPShortOpenTagsFailure=function(value){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html("your PHP <b>'short open tag'</b> is turned <b>OFF</b> - <b style='color:red;'>FAILED</b>");
    $("#col-"+Setup.currentStep+"-2").html("Ask your server administrator to turn PHP 'short open tag' ON.");
    
    Setup.continueSteps = false;
    Setup.run();
    
}
Setup.checkPHPShortOpenTagsSuccess=function(value){
    $("#col-"+Setup.currentStep+"-1").html("your PHP <b>'short open tag'</b> is turned <b>ON</b> - <b style='color:green;'>PASSED</b>");
    Setup.run();
}

//mysql connection
Setup.checkMySQLConnection=function(){
    var title = "<b>MySQL</b> connection test";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Setup.check("mysql_connection_check",Setup.checkMySQLConnectionSuccess,Setup.checkMySQLConnectionFailure);
}
Setup.checkMySQLConnectionFailure=function(value){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html(value+" <b>CAN'T CONNECT</b> - <b style='color:red;'>FAILED</b>");
    $("#col-"+Setup.currentStep+"-2").html("Set <b>db_host, db_port, db_user, db_password</b> in /SETTINGS.php file.");
    
    Setup.continueSteps = false;
    Setup.run();
    
}
Setup.checkMySQLConnectionSuccess=function(value){
    $("#col-"+Setup.currentStep+"-1").html(value+" <b>CONNECTED</b> - <b style='color:green;'>PASSED</b>");
    Setup.run();
}

//mysql database
Setup.checkMySQLDBConnection=function(){
    var title = "<b>MySQL</b> database connection test";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Setup.check("mysql_select_db_check",Setup.checkMySQLDBConnectionSuccess,Setup.checkMySQLDBConnectionFailure);
}
Setup.checkMySQLDBConnectionFailure=function(value){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html("<b>MySQL</b> database <b>"+value+"</b> <b>IS NOT CONNECTABLE</b> - <b style='color:red;'>FAILED</b>");
    $("#col-"+Setup.currentStep+"-2").html("Set <b>db_name</b> in <b>/SETTINGS.php</b> file. Check if database name is correct and if it is - check if MySQL user has required permissions to access this database.");
    
    Setup.continueSteps = false;
    Setup.run();
    
}
Setup.checkMySQLDBConnectionSuccess=function(value){
    $("#col-"+Setup.currentStep+"-1").html("<b>MySQL</b> database <b>"+value+"</b> <b>IS CONNECTABLE</b> - <b style='color:green;'>PASSED</b>");
    Setup.run();
}

//Rscript check
Setup.checkRscript=function(){
    var title = "<b>Rscript</b> file path must be set.";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Setup.check("rscript_check",Setup.checkRscriptSuccess,Setup.checkRscriptFailure);
}
Setup.checkRscriptFailure=function(value){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html("your <b>Rscript</b> file path: <b>"+value+"</b> <b>DOESN'T EXISTS</b> - <b style='color:red;'>FAILED</b>");
    $("#col-"+Setup.currentStep+"-2").html("Rscript file path not set, set incorrectly or unaccesible to PHP.<br/>Usually the Rscript file path is <b>/usr/bin/Rscript</b>. Set your Rscript path in <b>/SETTINGS.php</b> file.");
    
    Setup.continueSteps = false;
    Setup.run();
    
}
Setup.checkRscriptSuccess=function(value){
    $("#col-"+Setup.currentStep+"-1").html("your <b>Rscript</b> file path: <b>"+value+"</b> <b>EXISTS</b> - <b style='color:green;'>PASSED</b>");
    Setup.run();
}

//R version check
Setup.checkRVersion=function(){
    var title = "R version installed must be at least <b>v2.12</b> .";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Setup.check("r_version_check",Setup.checkRVersionSuccess,Setup.checkRVersionFailure);
}
Setup.checkRVersionFailure=function(value){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html("your <b>R</b> version is: <b>v"+value+"</b> <b>INCORRECT</b> - <b style='color:red;'>FAILED</b>");
    $("#col-"+Setup.currentStep+"-2").html("Please update your R installation to version <b>v2.12</b> at least.");
    
    Setup.continueSteps = false;
    Setup.run();
    
}
Setup.checkRVersionSuccess=function(value){
    $("#col-"+Setup.currentStep+"-1").html("your <b>R</b> version is: <b>v"+value+"</b> <b>CORRECT</b> - <b style='color:green;'>PASSED</b>");
    Setup.run();
}

//php.exe check
Setup.checkPHPPath=function(){
    var title = "<b>PHP</b> executable file path must be set.";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Setup.check("php_exe_path_check",Setup.checkPHPPathSuccess,Setup.checkPHPPathFailure);
}
Setup.checkPHPPathFailure=function(value){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html("your <b>PHP</b> executable file path: <b>"+value+"</b> <b>DOESN'T EXIST</b> - <b style='color:red;'>FAILED</b>");
    $("#col-"+Setup.currentStep+"-2").html("PHP executable file path not set, set incorrectly or unaccesible to PHP.<br/>Usually the PHP executable file path is <b>/usr/bin/php</b>. Set your PHP executable path in <b>/SETTINGS.php</b> file.");
    
    Setup.continueSteps = false;
    Setup.run();
    
}
Setup.checkPHPPathSuccess=function(value){
    $("#col-"+Setup.currentStep+"-1").html("your <b>PHP</b> executable file path: <b>"+value+"</b> <b>EXIST</b> - <b style='color:green;'>PASSED</b>");
    Setup.run();
}

//R.exe check
Setup.checkRPath=function(){
    var title = "<b>R</b> executable file path must be set.";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Setup.check("R_exe_path_check",Setup.checkRPathSuccess,Setup.checkRPathFailure);
}
Setup.checkRPathFailure=function(value){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html("your <b>R</b> executable file path: <b>"+value+"</b> <b>DOESN'T EXIST</b> - <b style='color:red;'>FAILED</b>");
    $("#col-"+Setup.currentStep+"-2").html("R executable file path not set, set incorrectly or unaccesible to PHP.<br/>Usually the R executable file path is <b>/usr/bin/R</b>. Set your R executable path in <b>/SETTINGS.php</b> file.");
    
    Setup.continueSteps = false;
    Setup.run();
    
}
Setup.checkRPathSuccess=function(value){
    $("#col-"+Setup.currentStep+"-1").html("your <b>R</b> executable file path: <b>"+value+"</b> <b>EXIST</b> - <b style='color:green;'>PASSED</b>");
    Setup.run();
}

//media dir writable
Setup.checkMediaDirWritable=function(){
    var title = "<b>media</b> directory path must be writable";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Setup.check("media_directory_writable_check",Setup.checkMediaDirWritableSuccess,Setup.checkMediaDirWritableFailure);
}
Setup.checkMediaDirWritableFailure=function(value){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html("your <b>media</b> directory: <b>"+value+"</b> <b>IS NOT WRITABLE</b> - <b style='color:red;'>FAILED</b>");
    $("#col-"+Setup.currentStep+"-2").html("Set <b>media</b> directory rigths to 0777.");
    
    Setup.continueSteps = false;
    Setup.run();
    
}
Setup.checkMediaDirWritableSuccess=function(value){
    $("#col-"+Setup.currentStep+"-1").html("your <b>media</b> directory: <b>"+value+"</b> <b>IS WRITABLE</b> - <b style='color:green;'>PASSED</b>");
    Setup.run();
}

//socks dir writable
Setup.checkSocksDirWritable=function(){
    var title = "<b>socks</b> directory path must be writable";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Setup.check("socks_directory_writable_check",Setup.checkSocksDirWritableSuccess,Setup.checkSocksDirWritableFailure);
}
Setup.checkSocksDirWritableFailure=function(value){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html("your <b>socks</b> directory: <b>"+value+"</b> <b>IS NOT WRITABLE</b> - <b style='color:red;'>FAILED</b>");
    $("#col-"+Setup.currentStep+"-2").html("Set <b>socks</b> directory rigths to 0777.");
    
    Setup.continueSteps = false;
    Setup.run();
    
}
Setup.checkSocksDirWritableSuccess=function(value){
    $("#col-"+Setup.currentStep+"-1").html("your <b>socks</b> directory: <b>"+value+"</b> <b>IS WRITABLE</b> - <b style='color:green;'>PASSED</b>");
    Setup.run();
}

//temp dir writable
Setup.checkTempDirWritable=function(){
    var title = "<b>temp</b> directory path must be writable";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Setup.check("temp_directory_writable_check",Setup.checkTempDirWritableSuccess,Setup.checkTempDirWritableFailure);
}
Setup.checkTempDirWritableFailure=function(value){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html("your <b>temp</b> directory: <b>"+value+"</b> <b>IS NOT WRITABLE</b> - <b style='color:red;'>FAILED</b>");
    $("#col-"+Setup.currentStep+"-2").html("Set <b>temp</b> directory rigths to 0777.");
    
    Setup.continueSteps = false;
    Setup.run();
    
}
Setup.checkTempDirWritableSuccess=function(value){
    $("#col-"+Setup.currentStep+"-1").html("your <b>temp</b> directory: <b>"+value+"</b> <b>IS WRITABLE</b> - <b style='color:green;'>PASSED</b>");
    Setup.run();
}

//files dir writable
Setup.checkFilesDirWritable=function(){
    var title = "<b>files</b> directory path must be writable";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Setup.check("files_directory_writable_check",Setup.checkFilesDirWritableSuccess,Setup.checkFilesDirWritableFailure);
}
Setup.checkFilesDirWritableFailure=function(value){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html("your <b>files</b> directory: <b>"+value+"</b> <b>IS NOT WRITABLE</b> - <b style='color:red;'>FAILED</b>");
    $("#col-"+Setup.currentStep+"-2").html("Set <b>files</b> directory rigths to 0777.");
    
    Setup.continueSteps = false;
    Setup.run();
    
}
Setup.checkFilesDirWritableSuccess=function(value){
    $("#col-"+Setup.currentStep+"-1").html("your <b>files</b> directory: <b>"+value+"</b> <b>IS WRITABLE</b> - <b style='color:green;'>PASSED</b>");
    Setup.run();
}

//cache dir writable
Setup.checkCacheDirWritable=function(){
    var title = "<b>media cache</b> directory path must be writable";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Setup.check("cache_directory_writable_check",Setup.checkCacheDirWritableSuccess,Setup.checkCacheDirWritableFailure);
}
Setup.checkCacheDirWritableFailure=function(value){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html("your <b>media cache</b> directory: <b>"+value+"</b> <b>IS NOT WRITABLE</b> - <b style='color:red;'>FAILED</b>");
    $("#col-"+Setup.currentStep+"-2").html("Set <b>media cache</b> directory rigths to 0777.");
    
    Setup.continueSteps = false;
    Setup.run();
    
}
Setup.checkCacheDirWritableSuccess=function(value){
    $("#col-"+Setup.currentStep+"-1").html("your <b>media cache</b> directory: <b>"+value+"</b> <b>IS WRITABLE</b> - <b style='color:green;'>PASSED</b>");
    Setup.run();
}

//catR R package
Setup.checkCatRRPackage=function(){
    var title = "<b>catR</b> R package must be installed.";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Setup.check("catR_r_package_check",Setup.checkCatRRPackageSuccess,Setup.checkCatRRPackageFailure);
}
Setup.checkCatRRPackageFailure=function(value){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html("<b>catR</b> package <b>IS NOT INSTALLED</b> - <b style='color:red;'>FAILED</b>");
    $("#col-"+Setup.currentStep+"-2").html("Install <b>catR</b> package to main R library directory.");
    
    Setup.continueSteps = false;
    Setup.run();
    
}
Setup.checkCatRRPackageSuccess=function(value){
    $("#col-"+Setup.currentStep+"-1").html("<b>catR</b> package <b>IS INSTALLED</b> - <b style='color:green;'>PASSED</b>");
    Setup.run();
}

//session R package
Setup.checkSessionRPackage=function(){
    var title = "<b>session</b> R package must be installed.";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Setup.check("session_r_package_check",Setup.checkSessionRPackageSuccess,Setup.checkSessionRPackageFailure);
}
Setup.checkSessionRPackageFailure=function(value){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html("<b>session</b> package <b>IS NOT INSTALLED</b> - <b style='color:red;'>FAILED</b>");
    $("#col-"+Setup.currentStep+"-2").html("Install <b>session</b> package to main R library directory.");
    
    Setup.continueSteps = false;
    Setup.run();
    
}
Setup.checkSessionRPackageSuccess=function(value){
    $("#col-"+Setup.currentStep+"-1").html("<b>session</b> package <b>IS INSTALLED</b> - <b style='color:green;'>PASSED</b>");
    Setup.run();
}

//RMySQL R package
Setup.checkRMySQLRPackage=function(){
    var title = "<b>RMySQL</b> R package must be installed.";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Setup.check("RMySQL_r_package_check",Setup.checkRMySQLRPackageSuccess,Setup.checkRMySQLRPackageFailure);
}
Setup.checkRMySQLRPackageFailure=function(value){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html("<b>RMySQL</b> package <b>IS NOT INSTALLED</b> - <b style='color:red;'>FAILED</b>");
    $("#col-"+Setup.currentStep+"-2").html("Install <b>RMySQL</b> package to main R library directory.");
    
    Setup.continueSteps = false;
    Setup.run();
    
}
Setup.checkRMySQLRPackageSuccess=function(value){
    $("#col-"+Setup.currentStep+"-1").html("<b>RMySQL</b> package <b>IS INSTALLED</b> - <b style='color:green;'>PASSED</b>");
    Setup.run();
}

//mysql database structure
Setup.checkDBStructure=function(){
    var title = "<b>MySQL</b> database tables structure test";
    Setup.insertCheckRow(title);
    Setup.updateProgressBar(title);
    
    Setup.getDBSteps();
}
Setup.checkDBStructureFailure=function(){
    $("#col-"+Setup.currentStep+"-1").removeClass("ui-state-highlight");
    $("#col-"+Setup.currentStep+"-1").addClass("ui-state-error");
    
    $("#col-"+Setup.currentStep+"-1").html("<b>MySQL</b> database tables structure <b>IS NOT CORRECT</b> - <b style='color:red;'>FAILED</b>");
    $("#col-"+Setup.currentStep+"-2").html("Setup application was unable to create valid database structure. Please restore database from the backup and revert Concerto to previous version.");
    
    Setup.continueSteps = false;
    Setup.run();
    
}
Setup.checkDBStructureSuccess=function(){
    $("#col-"+Setup.currentStep+"-1").html("<b>MySQL</b> database tables structure <b>IS CORRECT</b> - <b style='color:green;'>PASSED</b>");
    Setup.run();
}

Setup.versions = [];
Setup.create_db = false;
Setup.validate_column_names = false;
Setup.repopulate_TestTemplate = false;
Setup.recalculate_hash = false;
Setup.getDBSteps=function(){
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
        
        Setup.runDB();
    },"json");
}

Setup.runDB=function(){
    if(!Setup.continueDBSteps) {
        Setup.failureDB();
        return;
    }
    Setup.currentDBStep++;
    if(Setup.currentDBStep==Setup.maxDBStep) {
        Setup.successDB();
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

Setup.failureDB=function(){
    $("#tdLoadingDBStep").css("visibility","hidden");
    $("#tdCurrentDBStep").html("<font style='color:red'><b>failed to finish</b></font>");
    
    Setup.checkDBStructureFailure();
}

Setup.successDB=function(){
    $("#tdLoadingDBStep").css("visibility","hidden");
    $("#tdCurrentDBStep").html("<font style='color:green'><b>finished successfuly</b></font>");
    
    Setup.checkDBStructureSuccess();
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