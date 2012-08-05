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

function Concerto(container,hash,sid,tid,queryPath,callbackGet,callbackSend,debug,remote,loadingImageSource,resumeFromLastTemplate){
    this.resumeFromLastTemplate = false;
    if(resumeFromLastTemplate!=null) this.resumeFromLastTemplate = resumeFromLastTemplate;
    this.loadingImageSource = 'css/img/ajax-loader.gif';
    if(loadingImageSource!=null) this.loadingImageSource = loadingImageSource;
    this.remote = false;
    if(remote!=null) this.remote = remote;
    this.isDebug = false;
    if(debug!=null && debug==true) this.isDebug = true;
    this.container = container;
    this.sessionID = sid;
    this.hash = hash;
    this.testID = tid;
    this.queryPath = queryPath==null?"query/":queryPath;
    this.callbackGet = callbackGet;
    this.callbackSend = callbackSend;
    this.isStopped = false;
    
    this.data = null;
    this.debug = null;
    this.status = Concerto.statusTypes.created;
    this.finished = false;
    
    this.timer = 0;
    this.timeObj = null;
    
    this.timeTemplateLoaded = null;
    
    this.clearTimer=function(){
        if(this.timeObj!=null) {
            clearTimeout(this.timeObj);
        }
    }
    this.iniTimer = function(){
        var thisClass=this;
        var limit = this.data["TIME_LIMIT"];
        this.timeTemplateLoaded = new Date();
        
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
        this.status = Concerto.statusTypes.working;
        ConcertoMethods.loading(this.container,this.loadingImageSource);
        var thisClass = this;
        
        var params = {};
        params["resume_from_last_template"] = this.resumeFromLastTemplate?"1":"0";
        this.resumeFromLastTemplate = false;
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
        if(this.isDebug!=null && this.isDebug==true) params["debug"]=1;
        else params["debug"]=0;
        
        $.post(this.remote?this.queryPath:this.queryPath+"r_call.php",
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
                thisClass.finished = thisClass.data["FINISHED"]==1;
                
                if(thisClass.data["STATUS"]==Concerto.statusTypes.template) thisClass.loadTemplate(thisClass.data["HTML"],thisClass.data["HEAD"]);
                if(thisClass.data["STATUS"]==Concerto.statusTypes.completed) $(thisClass.container).html("");
                if(thisClass.data["STATUS"]==Concerto.statusTypes.tampered) $(thisClass.container).html("<h2>Session unavailable.</h2>");
                
                if(thisClass.data["STATUS"]==Concerto.statusTypes.error){
                    if(thisClass.debug==null){
                        $(thisClass.container).html("<h2>Fatal test exception encounterd. Test halted.</h2>");
                    }
                    else {
                        $(thisClass.container).html("<h2>R return code</h2>");
                        $(thisClass.container).append(thisClass.debug["return"]);
                        $(thisClass.container).append("<hr/>");
                        $(thisClass.container).append("<h2>R code</h2>");
                        $(thisClass.container).append(thisClass.debug["code"].replace(/\n/g,'<br />'));
                        $(thisClass.container).append("<hr/>");
                        $(thisClass.container).append("<h2>R output</h2>");
                        for(var i=0; i<thisClass.debug["output"].length;i++){
                            if(thisClass.debug["output"][i]==null) continue;
                            $(thisClass.container).append(thisClass.debug["output"][i].replace(/\n/g,'<br />')+"<br/>");
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
    
    this.loadTemplate=function(html,head){
        var thisClass = this;
        $("head").append(head);
        $(thisClass.container).html(thisClass.insertSpecialVariables(html));
        thisClass.addSubmitEvents();
        thisClass.iniTimer();
    };
    
    this.getControlsValues=function(){
        var values = new Array();
        
        $(this.container).find("input:text").each(function(){
            var obj = {
                name:$(this).attr("name"),
                value:$(this).val()
            };
            values.push($.toJSON(obj));
        });
        
        $(this.container).find("input:hidden").each(function(){
            var obj = {
                name:$(this).attr("name"),
                value:$(this).val()
            };
            values.push($.toJSON(obj));
        });
        
        $(this.container).find("input:password").each(function(){
            var obj = {
                name:$(this).attr("name"),
                value:$(this).val()
            };
            values.push($.toJSON(obj));
        });
        
        $(this.container).find("textarea").each(function(){
            var obj = {
                name:$(this).attr("name"),
                value:$(this).val()
            };
            values.push($.toJSON(obj));
        });
        
        $(this.container).find("select").each(function(){
            var obj = {
                name:$(this).attr("name"),
                value:$(this).val()
            };
            values.push($.toJSON(obj));
        });
        
        $(this.container).find("input:checkbox").each(function(){
            var obj = {
                name:$(this).attr("name"),
                value:$(this).is(":checked")?1:0
            };
            values.push($.toJSON(obj));
        });
        
        var radios = {};
        $(this.container).find("input:radio").each(function(){
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
        var currentTime = new Date();
        var thisClass=this;
        this.clearTimer();
        if(this.isStopped) return;
        var vals = this.getControlsValues();
        vals.push($.toJSON({
            name:"TIME_TAKEN",
            value:(currentTime.getTime()-thisClass.timeTemplateLoaded.getTime())/1000
        }));
        this.run(btnName,vals);
        if(this.callbackSend!=null) this.callbackSend.call(this,btnName,vals);
    };
    
    this.addSubmitEvents=function(){
        var thisClass = this;
        
        $(container).find(":button:not(.notInteractive)").click(function(){
            thisClass.submit($(this).attr("name"));
        });
        $(container).find(":image:not(.notInteractive)").click(function(){
            thisClass.submit($(this).attr("name"));
        });
        $(container).find(":submit:not(.notInteractive)").click(function(){
            thisClass.submit($(this).attr("name"));
        });
    }
};

Concerto.statusTypes={
    created:0,
    working:1,
    template:2,
    completed:3,
    error:4,
    tampered:5
};