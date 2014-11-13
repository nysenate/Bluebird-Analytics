// initialize the NYSS namespace
var NYSS = NYSS || {};

/*
   HashStorageModule
   A global storage object.  Auto-initializes to hold any data points
   found in the location's query string.
*/
(
  function( HSM, $, undefined ) {
    HSM.use_debug = true;
    HSM.data = {};

    function conlog(msg,data) {
      if (HSM.use_debug) {
        console.log(msg);
        if (data!==undefined) { console.log(data); }
      }
    }

    HSM.update = function(new_data) {
      HSM.data = $.extend(HSM.data, new_data);
      values = []
      for (key in HSM.data) {
        values.push(key+'='+HSM.data[key])
      }
      window.location.hash = values.join('&');
      conlog("Data updated in hash: ",HSM.data);
    };

    HSM.has = function(keys) {
      for (index in keys) {
        key = keys[index];
        if (!(key in HSM.data)) {
          return false;
        }
      }
      return true;
    };

    if (window.location.hash) {
      values = window.location.hash.substr(1).split('&');
      for (key in values) {
        parts = values[key].split('=', 2)
        HSM.data[parts[0]] = parts[1];
      }
      conlog("Data stored in hash: ",HSM.data);
    }
  }( NYSS.HashStorageModule = NYSS.HashStorageModule || {}, jQuery )
);
