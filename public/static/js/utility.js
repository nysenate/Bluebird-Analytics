/* Utility functions */

/* Add Array.intersect() function to javascript arrays
   Parameters:
      test_array: an array to intersect with the current array
   Returns a new array including only values present in host array and the passed array.
   */
if (!Array.prototype.intersect) {
  Object.defineProperty(Array.prototype,'intersect',
      {
        writable: true,
        value: function intersect(test_array){
                  var ret=[];
                  if (test_array.indexOf) {
                    for (x in this) {
                        if (test_array.indexOf(this[x])>=0) { ret.push(this[x]); }
                    }
                  }
                  return ret;
        }
      });
}

/* Add Array.unique() function to javascript arrays
   Returns a new array including only unique values found in the original
   */
if (!Array.prototype.unique) {
  Object.defineProperty(Array.prototype,'unique',
      {
        writable: true,
        value: function(){
          var u = {}, a = [];
          for(var i = 0, l = this.length; i < l; ++i){
            if(u.hasOwnProperty(this[i])) {
               continue;
            }
            a.push(this[i]);
            u[this[i]] = 1;
          }
          return a;
        }
      });
}

/* Add String.capitalize() function to javascript strings
   Parameters:
      first_only: indicates if the capitalization should be only the first word (true),
                  or all words (false).  Defaults to false.
   Returns a new string with capitalized replacements
   */
if (!String.prototype.capitalize) {
  Object.defineProperty(String.prototype,'capitalize',
      {
        writable:true,
        value:function(first_only,undefined){
          if (first_only===undefined) {first_only = false;}
          r = first_only ? /(?!^\/)\b([a-z])/ : /(?!^\/)\b([a-z])/g;
          return this.replace(r,function(m){return m.toUpperCase()});
        }
      });
}