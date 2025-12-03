
var user = {

  'login':function(username,password,rememberme,referrer, callback)
  {
    var data = {
        username: encodeURIComponent(username),
        password: encodeURIComponent(password),
        rememberme: encodeURIComponent(rememberme),
        referrer: encodeURIComponent(referrer)
    }
    $.ajax({
      type: "POST",
      url: path+"user/login.json",
      data: data,
      dataType: "text",
      async: true,
      success: function(data_in)
      {
         var result;
         try {
             result = JSON.parse(data_in);
             if (result.success==undefined) result = data_in;
         } catch (e) {
             result = data_in;
         }
         if (callback) callback(result);
      },
      error: function (xhr, ajaxOptions, thrownError) {
        var result;
        if(xhr.status==404) {
            result = "404 Not Found: Is modrewrite configured on your system?"
        } else {
            result = xhr.status+" "+thrownError;
        }
        if (callback) callback({success:false, message:result});
      }
    });
  },

  'register':function(username,password,email,timezone, callback)
  {
    $.ajax({
      type: "POST",
      url: path+"user/register.json",
      data: "&username="+encodeURIComponent(username)+"&password="+encodeURIComponent(password)+"&email="+encodeURIComponent(email)+"&timezone="+encodeURIComponent(timezone),
      dataType: "text",
      async: true,
      success: function(data_in)
      {
         var result;
         try {
             result = JSON.parse(data_in);
             if (result.success==undefined) result = data_in;
         } catch (e) {
             result = data_in;
         }
         if (callback) callback(result);
      },
      error: function (xhr, ajaxOptions, thrownError) {
        var result;
        if(xhr.status==404) {
            result = "404 Not Found: Is modrewrite configured on your system?"
        } else {
            result = xhr.status+" "+thrownError;
        }
        if (callback) callback({success:false, message:result});
      }
    });
  },

  'get':function(callback)
  {
    $.ajax({ url: path+"user/get.json", dataType: "json", async: true, success: function(data) {
        if (data.success!==undefined) {
            alert(data.message);
        }
        if (callback) callback(data);
    } });
  },
  
  'passwordreset':function(username,email, callback)
  {
    $.ajax({ url: path+"user/passwordreset.json", data: "&username="+encodeURIComponent(username)+"&email="+encodeURIComponent(email), dataType: "json", async: true, success: function(data) {
        if (callback) callback(data);
    } });
  },

  'set':function(data, callback)
  {
    $.ajax({ type: "POST", url: path+"user/set.json", data: "&data="+encodeURIComponent(JSON.stringify(data)) ,dataType: "json", async: true, success: function(data) {
        if (callback) callback(data);
    } });
  },

  'newapikeywrite':function(callback)
  {
    $.ajax({ url: path+"user/newapikeywrite.json", dataType: "json", async: true, success: function(data) {
        if (callback) callback(data);
    } });
  },
  
  'newapikeyread':function(callback)
  {
    $.ajax({ url: path+"user/newapikeyread.json", dataType: "json", async: true, success: function(data) {
        if (callback) callback(data);
    } });
  },
  
  'timezoneoffset':function(callback)
  {
    $.ajax({ url: path+"user/timezone.json", dataType: "json", async: true, success: function(data) {
        if (callback) callback(data);
    } });
  }

}

