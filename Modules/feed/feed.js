
var feed = {

    apikey: false,
    public_userid: 0,
    public_username: "",
    
    apikeystr: function() {
        if (feed.apikey) {
            return "?apikey="+feed.apikey;
        } else {
            return "";
        }
    },
    
    public_username_str: function() {
        if (feed.public_userid) {
            return public_username+"/";
        } else {
            return "";
        }    
    },
    
    create: function(tag, name, engine, options, unit, callback){
        var data = {
            tag: tag,
            name: name,
            engine: engine,
            options: JSON.stringify(options),
            unit: unit || ''
        }
        $.ajax({ url: path+"feed/create.json", data: data, dataType: 'json', async: true, success: function(data){ if (callback) callback(data); } });
    },

    list: function(callback)
    {   
        $.ajax({                                      
            url: path+this.public_username_str()+"feed/list.json"+this.apikeystr(),
            dataType: 'json',
            cache: false,
            async: true,                      
            success: function(result) {
                if (!result || result===null || result==="" || result.constructor!=Array) {
                    console.log("ERROR","feed.list invalid response: "+result);
                    result = null;
                }

                if (typeof callback === "function") {
                    callback(result);
                }
            }
        });
    },

    // Returns an object with feeds grouped by tag
    by_tag: function(feeds) {
        if (!Array.isArray(feeds) || feeds.length === 0) return {};
        const bytag = {};
        for (const feed of feeds) {
            if (!feed || !feed.tag) continue;
            if (bytag[feed.tag]) {
                bytag[feed.tag].push(feed);
            } else {
                bytag[feed.tag] = [feed];
            }
        }
        return bytag;
    },

    // Returns an object with feeds grouped by group
    by_id: function(feeds) {
        if (!Array.isArray(feeds) || feeds.length === 0) return {};
        const byid = {};
        for (const feed of feeds) {
            if (!feed || feed.id == null) continue;
            byid[String(feed.id)] = feed;
        }
        return byid;
    },
    
    listbyid: function(callback) {
        feed.list(function(feeds){
            if (feeds === null) { 
                callback(null);
                return;
            }
            var byid = {};
            for (z in feeds) byid[feeds[z].id] = feeds[z];
            callback(byid);
        });
    },

    listbyidasync: function(f)
    {   
        this.list(f);
    },

    set_fields: function(id, fields, callback){
        $.ajax({ url: path+"feed/set.json", data: {id:id,fields:JSON.stringify(fields)}, success: function(result) {
            if (result.success!=undefined && !result.success) {
                alert(result.message);
            }
            if (callback) callback(result);
        }});
    },

    remove: function(id, callback){
        $.ajax({ url: path+"feed/delete.json", data: {id:id}, async: true, success: function(data){
             // Clean processlists of deleted feeds
             $.ajax({ url: path+"input/cleanprocesslistfeeds.json", async: true, success: function(data){} });
             if (callback) callback(data);
        }});
    },

    clear: function(id, callback){
        let data = {
            id: id
        }
        $.ajax({ url: path+"feed/clear.json", data: data, async: true, success: function(data){ if (callback) callback(data); } });
    },
    
    trim: function(id,start_time, callback){
        let data = {
            id: id,
            start_time: start_time
        }
        $.ajax({ url: path+"feed/trim.json", data: data, async: true, success: function(data){ if (callback) callback(data); } });
    },

    getvalue: function(feedid,time,callback,context=false) {
        let data = {
            id: feedid,
            time: time
        }
        if (feed.apikey) data.apikey = feed.apikey;
       
        $.ajax({ 
            url: path+"feed/value.json", 
            data: data, 
            async: true, 
            success: function(result) {
                if (result===null || result==="") {
                    console.log("ERROR","feed.getvalue invalid response: "+result);
                    result = false;
                }
                
                if (context) {
                    callback(context,result);
                } else {
                    callback(result);
                }
            }
        });
    },

    getdata: function(feedid,start,end,interval,average=0,delta=0,skipmissing=0,limitinterval=0,callback,context=false,timeformat='unixms'){
        let data = {
            id: feedid,
            start: start,
            end: end,
            interval: interval,
            average:average,
            delta:delta,
            skipmissing: skipmissing,
            limitinterval: limitinterval,
            timeformat: timeformat
        };
        
        if (typeof feedid === "object") {
            delete data['id'];
            data['ids'] = feedid.join(",");
        }
        
        if (feed.apikey) data.apikey = feed.apikey;

        $.ajax({
            url: path+'feed/data.json',
            data: data,
            dataType: 'json',
            async: true,
            success: function(result) {
                if (!result || result===null || result==="" || result.constructor!=Array) {
                    console.log("ERROR","feed.getdata invalid response: "+result);
                } else {
                    if (timeformat=="notime") {
                        if (data.ids!=undefined) {
                            for (var i in result) {
                                result[i].data = feed.populate_timestamps(result[i].data, start, interval);
                            }
                        } else {
                            result = feed.populate_timestamps(result, start, interval);
                        }
                    }
                }
                
                if (context) {
                    callback(context,result);
                } else {
                    callback(result);
                }
            }
        });
    },
    
    getdataDMY_time_of_use: function(id,start,end,interval,split, callback)
    {
        let data = {
            id: id,
            start: start,
            end: end,
            interval: interval,
            split:split
        };
        if (feed.apikey) data.apikey = feed.apikey;
        
        $.ajax({                                      
            url: path+"feed/data.json",                         
            data: data,
            dataType: 'json',
            async: true,                      
            success: function(result) {
                if (!result || result===null || result==="" || result.constructor!=Array) {
                    console.log("ERROR","feed.getdataDMY_time_of_use invalid response: "+result);
                }
                if (callback) callback(result);
            }
        });
    },

    // Virtual feed process
    set_process: function(feedid,processlist, callback){
        let json_processlist = JSON.stringify(processlist);
        $.ajax({ url: path+"feed/process/set.json?id="+feedid, method: "POST", data: "processlist="+json_processlist, async: true, success: function(data){ if (callback) callback(data); } });
    },

    get_process: function(feedid, callback){
        $.ajax({ url: path+"feed/process/get.json", data: "id="+feedid, async: true, dataType: 'json', success: function(data){
            var processlist = [];
            if (data!="")
            {
                var tmp = data.split(",");
                for (n in tmp)
                {
                    var process = tmp[n].split(":"); 
                    processlist.push(process);
                }
            }
            if (callback) callback(processlist);
        } });
    },

    reset_processlist: function(feedid, callback){
        $.ajax({ url: path+"feed/process/reset.json", data: "id="+feedid, async: true, success: function(data){ if (callback) callback(data); } });
    },
    
    getmeta: function(feedid, callback)
    {
        let data = {
          id: feedid
        };
        if (feed.apikey) data.apikey = feed.apikey;
        
        $.ajax({                                      
            url: path+'feed/getmeta.json',
            data: data,
            dataType: 'json',
            async: true,
            success: function(result) {
                if (!result || result===null || result==="" || result.constructor!=Object) {
                    console.log("ERROR","feed.getmeta invalid response: "+result);
                }
                if (callback) callback(result);
            } 
        });
    },
    
    populate_timestamps: function(values, start, interval) {
        var intervalms = interval*1000;
        var time = Math.floor(start/intervalms)*intervalms;
        var with_time = [];
        for (var z in values) {
            with_time.push([time,values[z]]);
            time += intervalms;
        }
        return with_time;
    } 
}

