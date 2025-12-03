// JSHint settings
/* globals path */

var input = {

    'list':function(callback)
    {
        $.ajax({ url: path+"input/list.json", dataType: 'json', async: true, success: function(data) {
            if (callback) callback(data);
        } });
    },

    'list_assoc':function(callback)
    {
        $.ajax({ url: path+"input/list.json", dataType: 'json', async: true, success: function(data) {
            var inputs = {};
            for (var z in data) inputs[data[z].id] = data[z];
            if (callback) callback(inputs);
        } });
    },
    
    'set':function(id, fields, callback)
    {
        var options = { 
            url: path+"input/set.json",
            data: "inputid="+id+"&fields="+JSON.stringify(fields),
            async: true
        }
        
        $.ajax(options)
        .done(function(response){
            if (!response.success) alert(response.message);
            if (callback) callback(response);
        })
        .error(function(msg){
            alert('Error saving data. '+msg);
            if (callback) callback({success:false, message:msg});
        })
    },

    'remove':function(id, callback)
    {
        $.ajax({ url: path+"input/delete.json", data: "inputid="+id, async: true, success: function(data){
            if (callback) callback(data);
        } });
    },
    
    'delete_multiple':function(ids, callback)
    {
        $.ajax({ url: path+"input/delete.json", data: "inputids="+JSON.stringify(ids), async: true, success: function(data){
            if (callback) callback(data);
        } });
    },

    delete_multiple_async: function(ids) {
        return $.getJSON(path + "input/delete.json", {inputids: JSON.stringify(ids)})
    },

    // Process

    'set_process':function(inputid,processlist, callback)
    {
        let json_processlist = JSON.stringify(processlist);
        $.ajax({ url: path+"input/process/set.json?inputid="+inputid, method: "POST", data: "processlist="+json_processlist, async: true, success: function(data){
            if (callback) callback(data);
        } });
    },

    'get_process':function(inputid, callback)
    {
        $.ajax({ url: path+"input/process/get.json", data: "inputid="+inputid, async: true, dataType: 'json', success: function(data){
            var processlist = [];
            if (data!="")
            {
                var tmp = data.split(",");
                for (var n in tmp)
                {
                    var process = tmp[n].split(":"); 
                    processlist.push(process);
                }
            }
            if (callback) callback(processlist);
        } });
    },

    'reset_processlist':function(inputid, callback)
    {
        $.ajax({ url: path+"input/process/reset.json", data: "inputid="+inputid, async: true, success: function(data){
            if (callback) callback(data);
        } });
    }

}

