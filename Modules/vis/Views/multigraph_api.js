
var multigraph = {

  'new':function(callback)
  {
    $.ajax({ url: path+"vis/multigraph/new.json", async: true, success: function(data){
        if (callback) callback(data);
    } });
  },

  'set':function(id,feedlist,name, callback)
  {
    $.ajax({ url: path+"vis/multigraph/set.json", data: "id="+id+"&name="+encodeURIComponent(name)+"&feedlist="+encodeURIComponent(JSON.stringify(feedlist)), async: true, success: function(data){
        if (callback) callback(data);
    } });
  },

  'get':function(id, callback)
  {
    $.ajax({ url: path+"vis/multigraph/get.json", data: "id="+id, dataType: 'json', async: true, success: function(data){
        if (callback) callback(data);
    } });
  },

  'remove':function(id, callback)
  {
    $.ajax({ url: path+"vis/multigraph/delete.json", data: "id="+id, async: true, success: function(data){
        if (callback) callback(data);
    } });
  },

  'getlist':function(callback)
  {
    $.ajax({ url: path+"vis/multigraph/getlist.json", async: true, dataType: 'json', success: function(data){
        if (callback) callback(data);
    } });
  }

}
