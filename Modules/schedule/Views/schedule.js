var schedule = {
    
    'list':function(callback)
    {
        $.ajax({ url: path+"schedule/list.json", dataType: 'json', async: true, success: function(data) {
            if (callback) callback(data);
        } });
    },

    'get':function(id, callback)
    {
        $.ajax({ url: path+"schedule/get.json", data: "id="+id, async: true, success: function(data){ 
            if (callback) callback(data);
        } });
    },

    'set':function(id, fields, callback)
    {
        $.ajax({ url: path+"schedule/set.json", data: "id="+id+"&fields="+JSON.stringify(fields), async: true, success: function(data) {
            if (callback) callback(data);
        } });
    },

    'remove':function(id, callback)
    {
        $.ajax({ url: path+"schedule/delete.json", data: "id="+id, async: true, success: function(data){
            if (callback) callback(data);
        } });
    },
    
    'test':function(id, callback)
    {
        $.ajax({ url: path+"schedule/test.json", data: "id="+id, async: true, success: function(data){
            if (callback) callback(data);
        } });
    }
}
