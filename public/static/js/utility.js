/* Utility functions */
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

Object.defineProperty(String.prototype,'capitalize',
    {
      writable:true,
      value:function(first_only,undefined){
        if (first_only===undefined) {first_only = false;}
        r = first_only ? /(?!^\/)\b([a-z])/ : /(?!^\/)\b([a-z])/g;
        return this.replace(r,function(m){return m.toUpperCase()});
      }
    });