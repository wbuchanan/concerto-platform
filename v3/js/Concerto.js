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

function Concerto(selector,hash,sid,tid,queryPath,callbackGet,callbackSend){
    this.selector = selector;
    this.sessionID = sid;
    this.hash = hash;
    this.testID = tid;
    this.queryPath = queryPath==null?"query/":queryPath;
    this.callbackGet = callbackGet;
    this.callbackSend = callbackSend;
    this.isStopped = false;
    
    this.data = null;
    this.debug = null;
    this.status = Concerto.statusTypes.started;
    
    this.timer = 0;
    this.timeObj = null;
    
    this.timePassed = 0;
    
    this.clearTimer=function(){
        if(this.timeObj!=null) {
            clearTimeout(this.timeObj);
        }
    }
    this.iniTimer = function(){
        var thisClass=this;
        var limit = this.data["TIME_LIMIT"];
        
        this.timePassed=0;
        
        if(limit>0){
            this.timer = limit;
            $(".fontTimeLeft").html(this.timer);
            this.timeObj = setInterval(function(){
                thisClass.timePassedTick();
                thisClass.timeTick();
            },1000);
        }
        else {
            thisClass.timePassedTick();
        }
    }
    
    this.timePassedTick = function(){
        if(this.isStopped) return;
        this.timePassed++;
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
        this.status = Concerto.statusTypes.loading;
        ConcertoMethods.loading(this.selector);
        var thisClass = this;
        
        var params = {};
        if(this.hash!=null && this.sessionID!=null) 
        {
            params["hash"] = this.hash;
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
                thisClass.data = data.data;
                if(data.debug){
                    thisClass.debug = data.debug;
                }
                
                thisClass.hash = thisClass.data["HASH"];
                thisClass.sessionID = thisClass.data["TEST_SESSION_ID"];
                thisClass.testID = thisClass.data["TEST_ID"];
                thisClass.status = thisClass.data["STATUS"];
                
                if(thisClass.data["STATUS"]==Concerto.statusTypes.template) thisClass.loadTemplate(thisClass.data["HTML"]);
                if(thisClass.data["STATUS"]==Concerto.statusTypes.finished) $(thisClass.selector).html("");
                if(thisClass.data["STATUS"]==Concerto.statusTypes.tampered) $(thisClass.selector).html("<h2>Tampering detected or session timed out! Test session has been deleted!</h2>");
                
                if(thisClass.data["STATUS"]==Concerto.statusTypes.error){
                    if(thisClass.debug==null){
                        $(thisClass.selector).html("<h2>Fatal test exception encounterd. Test halted.</h2>");
                    }
                    else {
                        $(thisClass.selector).html("<h2>RScript return code</h2>");
                        $(thisClass.selector).append(thisClass.debug["return"]);
                        $(thisClass.selector).append("<hr/>");
                        //$(thisClass.selector).append("<h2>HTML variables</h2>");
                        //for(var k in data.values){
                        //    $(thisClass.selector).append("<b>"+k+"</b> = "+data.values[k].replace(/\n/g,'<br />')+"<br/>") ;
                        //}
                        //$(thisClass.selector).append("<hr/>");
                        $(thisClass.selector).append("<h2>R code</h2>");
                        $(thisClass.selector).append(thisClass.debug["code"].replace(/\n/g,'<br />'));
                        $(thisClass.selector).append("<hr/>");
                        $(thisClass.selector).append("<h2>R output</h2>");
                        for(var i=0; i<thisClass.debug["output"].length;i++){
                            $(thisClass.selector).append(thisClass.debug["output"][i].replace(/\n/g,'<br />')+"<br/>");
                        }
                    }
                }
                if(thisClass.callbackGet!=null) thisClass.callbackGet.call(thisClass, data);
                return thisClass.data;
            },"json");
        return null;
    };
    
    this.insertSpecialVariables=function(html){
        html = html.replace("{{TIME_LEFT}}","<font class='fontTimeLeft'></font>");
        return html;
    };
    
    this.loadTemplate=function(html){
        var thisClass = this;
        $(thisClass.selector).html(thisClass.insertSpecialVariables(html));
        thisClass.addSubmitEvents();
        thisClass.iniTimer();
    };
    
    this.getControlsValues=function(){
        var values = new Array();
        
        $(this.selector+" input:text").each(function(){
            var obj = {
                name:$(this).attr("name"),
                value:$(this).val()
            };
            values.push($.toJSON(obj));
        });
        
        $(this.selector+" input:password").each(function(){
            var obj = {
                name:$(this).attr("name"),
                value:$(this).val()
            };
            values.push($.toJSON(obj));
        });
        
        $(this.selector+" textarea").each(function(){
            var obj = {
                name:$(this).attr("name"),
                value:$(this).val()
            };
            values.push($.toJSON(obj));
        });
        
        $(this.selector+" select").each(function(){
            var obj = {
                name:$(this).attr("name"),
                value:$(this).val()
            };
            values.push($.toJSON(obj));
        });
        
        $(this.selector+" input:checkbox").each(function(){
            var obj = {
                name:$(this).attr("name"),
                value:$(this).is(":checked")?1:0
            };
            values.push($.toJSON(obj));
        });
        
        var radios = {};
        $(this.selector+" input:radio").each(function(){
            var checked = $(this).is(":checked");
            var name = $(this).attr("name");
            
            var obj = {
                name:name,
                value:(checked?$(this).val():"NA")
            };
            
            var found = false;
            for(var key in radios){
                if(key==name) {
                    found = true;
                    if(checked&&radios[key].value=="NA") {
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
        var thisClass=this;
        this.clearTimer();
        if(this.isStopped) return;
        var vals = this.getControlsValues();
        vals.push($.toJSON({
            name:"TIME_TAKEN",
            value:thisClass.timePassed
        }));
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

Concerto.statusTypes={
    started:0,
    loading:1,
    template:2,
    completed:3,
    stopped:4,
    error:5,
    tampered:6
};