/* Utility functions */

/* Add Array.unique() function to javascript arrays
   Returns a new array including only unique values found in the original
   */
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

/* Add String.capitalize() function to javascript strings
   Parameters:
      first_only: indicates if the capitalization should be only the first word (true),
                  or all words (false).  Defaults to false.
   Returns a new string with capitalized replacements
   */
Object.defineProperty(String.prototype,'capitalize',
    {
      writable:true,
      value:function(first_only,undefined){
        if (first_only===undefined) {first_only = false;}
        r = first_only ? /(?!^\/)\b([a-z])/ : /(?!^\/)\b([a-z])/g;
        return this.replace(r,function(m){return m.toUpperCase()});
      }
    });