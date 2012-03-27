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

function Concerto(selector,sid,tid,queryPath,callbackGet,callbackSend){
    this.selector = selector;
    this.sessionID = sid;
    this.testID = tid;
    this.queryPath = queryPath==null?"query/":queryPath;
    this.callbackGet = callbackGet;
    this.callbackSend = callbackSend;
    this.isStopped = false;
    
    this.lastResults = null;
    
    this.timer = 0;
    this.timeObj = null;
    this.clearTimer=function(){
        if(this.timeObj!=null) {
            clearTimeout(this.timeObj);
        }
    }
    this.iniTimer = function(){
        var thisClass=this;
        var limit = this.getTimeLimit();
        if(limit>0){
            this.timer = limit;
            $(".fontTimeLeft").html(this.timer);
            this.timeObj = setInterval(function(){
                thisClass.timeTick();
            },1000);
        }
    }
    
    this.timeTick = function(){
        if(this.isStopped) return;
        if(this.timer>0){
            this.timer--;
            $(".fontTimeLeft").html(this.timer);
            if(this.timer==0){
                this.submit("NONE");
            }
        }
    }
    
    this.stop=function(){
        this.clearTimer();
        this.isStopped = true;
    }
    
    this.run=function(btnName,values){
        if(this.isStopped) return;
        ConcertoMethods.loading(this.selector);
        var thisClass = this;
        
        var params = {};
        if(this.sessionID!=null) 
        {
            params["sid"] = this.sessionID;
        }
        else
        {
            if(this.testID!=null) params["tid"] = this.testID;
        }
        if(btnName!=null) params["btn_name"] = btnName;
        if(values!=null) params["values"] = values;
        
        $.post(this.queryPath+"r_call.php",
            params,
            function(data){
                thisClass.lastResults = data;
                
                thisClass.sessionID = thisClass.getVariableValue("TEST_SESSION_ID");
                thisClass.testID = thisClass.getVariableValue("TEST_ID");
                
                if(data.control.halt_type == Concerto.haltTypes.loadTemplate && data.result["return"] == 0)
                {
                    thisClass.loadTemplate(thisClass.getVariableValue("CURRENT_TEMPLATE_ID"));
                }
                
                if(data.control.end && data.result["return"] == 0)
                {
                    $(thisClass.selector).html("");
                }
                
                if(data.result["return"] != 0){
                    $(thisClass.selector).html("<h2>RScript return code</h2>");
                    $(thisClass.selector).append(data.result["return"]);
                    $(thisClass.selector).append("<hr/>");
                    $(thisClass.selector).append("<h2>HTML variables</h2>");
                    for(var k in data.values){
                        $(thisClass.selector).append("<b>"+k+"</b> = "+data.values[k].replace(/\n/g,'<br />')+"<br/>") ;
                    }
                    $(thisClass.selector).append("<hr/>");
                    $(thisClass.selector).append("<h2>R code</h2>");
                    $(thisClass.selector).append(data.result.code.replace(/\n/g,'<br />'));
                    $(thisClass.selector).append("<hr/>");
                    $(thisClass.selector).append("<h2>R output</h2>");
                    for(var i=0; i<data.result.output.length;i++){
                        $(thisClass.selector).append(data.result.output[i].replace(/\n/g,'<br />')+"<br/>");
                    }
                }
                if(thisClass.callbackGet!=null) thisClass.callbackGet.call(thisClass, data);
                return data;
            },"json");
        return null;
    };
    
    this.getVariableValue=function(name){
        var result = this.lastResults.values[name];
        return result;
    };
    
    this.getValues=function(){
        var result = this.lastResults.values;
        return result;
    };
    
    this.insertSpecialVariables=function(html){
        html = html.replace("{{TIME_LEFT}}","<font class='fontTimeLeft'></font>");
        return html;
    };
    
    this.loadTemplate=function(templateID){
        var thisClass = this;
        
        $.post(this.queryPath+"get_html.php",{
            template_id:templateID,
            values:this.getValues()
        },function(data){
            $(thisClass.selector).html(thisClass.insertSpecialVariables(data.html));
            thisClass.addSubmitEvents();
            thisClass.iniTimer();
        },"json");
    };
    
    this.getTimeLimit=function(){
        if(this.getVariableValue("TIME_LIMIT")){
            return this.getVariableValue("TIME_LIMIT");
        }
        return 0;
    }
    
    this.getControlsValues=function(){
        var values = new Array();
        
        $(this.selector+" input:text").each(function(){
            var hasVisibility = $(this).is("[returnvisibility]");
            var hasType = $(this).is("[returntype]");
            var obj = {
                name:$(this).attr("name"),
                value:$(this).val(),
                visibility:(hasVisibility?$(this).attr("returnvisibility"):2),
                type:(hasType?$(this).attr("returntype"):0)
            };
            values.push($.toJSON(obj));
        });
        
        $(this.selector+" input:password").each(function(){
            var hasVisibility = $(this).is("[returnvisibility]");
            var hasType = $(this).is("[returntype]");
            var obj = {
                name:$(this).attr("name"),
                value:$(this).val(),
                visibility:(hasVisibility?$(this).attr("returnvisibility"):2),
                type:(hasType?$(this).attr("returntype"):0)
            };
            values.push($.toJSON(obj));
        });
        
        $(this.selector+" textarea").each(function(){
            var hasVisibility = $(this).is("[returnvisibility]");
            var hasType = $(this).is("[returntype]");
            var obj = {
                name:$(this).attr("name"),
                value:$(this).val(),
                visibility:(hasVisibility?$(this).attr("returnvisibility"):2),
                type:(hasType?$(this).attr("returntype"):0)
            };
            values.push($.toJSON(obj));
        });
        
        $(this.selector+" select").each(function(){
            var hasVisibility = $(this).is("[returnvisibility]");
            var hasType = $(this).is("[returntype]");
            var obj = {
                name:$(this).attr("name"),
                value:$(this).val(),
                visibility:(hasVisibility?$(this).attr("returnvisibility"):2),
                type:(hasType?$(this).attr("returntype"):0)
            };
            values.push($.toJSON(obj));
        });
        
        $(this.selector+" input:checkbox").each(function(){
            var hasVisibility = $(this).is("[returnvisibility]");
            var hasType = $(this).is("[returntype]");
            var obj = {
                name:$(this).attr("name"),
                value:$(this).is(":checked")?1:0,
                visibility:(hasVisibility?$(this).attr("returnvisibility"):2),
                type:(hasType?$(this).attr("returntype"):0)
            };
            values.push($.toJSON(obj));
        });
        
        var radios = {};
        $(this.selector+" input:radio").each(function(){
            var checked = $(this).is(":checked");
            var name = $(this).attr("name");
            var hasVisibility = $(this).is("[returnvisibility]");
            var hasType = $(this).is("[returntype]");
            
            var obj = {
                name:name,
                value:(checked?$(this).val():"NA"),
                visibility:(hasVisibility?$(this).attr("returnvisibility"):2),
                type:(checked?(hasType?$(this).attr("returntype"):0):3)
            };
            
            var found = false;
            for(var key in radios){
                if(key==name) {
                    found = true;
                    if(checked&&radios[key].type==3) {
                        radios[key]=obj;
                        break;
                    }
                }
            }
            if(!found){
                radios[name]=obj;
            }
        });
        for(var key in radios){
            values.push($.toJSON(radios[key]));
        }
        
        return values;
    }
    
    this.submit=function(btnName){
        if(this.isStopped) return;
        var vals = this.getControlsValues();
        this.clearTimer();
        this.run(btnName,vals);
        if(this.callbackSend!=null) this.callbackSend.call(this,btnName,vals);
    };
    
    this.addSubmitEvents=function(){
        var thisClass = this;
        
        $(selector+" :button:not(.notInteractive)").click(function(){
            thisClass.submit($(this).attr("name"));
        });
        $(selector+" :image:not(.notInteractive)").click(function(){
            thisClass.submit($(this).attr("name"));
        });
        $(selector+" :submit:not(.notInteractive)").click(function(){
            thisClass.submit($(this).attr("name"));
        });
    }
};

Concerto.haltTypes={
    loadTemplate:2
};