/* Base class for message box notifications
   Requires FontAwesome 4.1.0 or later
   */

// initialize the NYSS namespace
var NYSS = NYSS || {};

(function ($,undefined) {
  // set up a shortcut to add non-enumerable properties
  var defProp = function(obj, name, value) {
    Object.defineProperty( obj.prototype, name, {writable:true, enumerable:false, value:value} );
  };

  /* NYSS.MessageBox - provide notification icons w/hover-over messages */
  NYSS.MessageBox = function(options) {
    if (!(this instanceof NYSS.MessageBox)) {
      throw new TypeError("MessageBox must be instanced; use 'new' to call");
    }
    this.validateSettings(options);
  };

  /* *************************************************
     NYSS.MessageBox object defaults
     ************************************************* */
  NYSS.MessageBox.prototype.caption = '';
  NYSS.MessageBox.prototype.container_class = '';
  NYSS.MessageBox.prototype.container_id = '';
  NYSS.MessageBox.prototype.icon_class = "fa-warning";
  NYSS.MessageBox.prototype.icon_element = "i";
  NYSS.MessageBox.prototype.messages = [];
  NYSS.MessageBox.prototype.parent_target = 'body';
  NYSS.MessageBox.prototype.use_icon = true;

  /* *************************************************
     NYSS.MessageBox class methods
     ************************************************* */

  /* method NYSS.MessageBox.appendTo(s)
     Attempts to append the built notification box to its intended target.

     Parameters:
       s (object): the jQuery target object.  If not passed, this.parent_target is used.
     Return: boolean indicating if append was successful
     */
  defProp(NYSS.MessageBox, 'appendTo', function(s) {
    var ret = false;
    if (!this._builtBox) { this.buildBox(true); }
    this._builtBox.css('display','');
    this._builtBox.find('.NYSS-MessageBox').show();
    if (!s) { s = this.parent_target; }
    return Boolean(this._builtBox.appendTo(s).length);
  });

  /* method NYSS.MessageBox.buildBox()
     Builds the entire box for the notification area.  Includes the container,
     icon, caption, and messages.  The built box is cached in the instance
     for easy reference.  If parameter r is passed and is true, the box will
     be rebuilt even if already cached.

     Parameters:
       r (boolean): if the box should be forcibly rebuilt (default to true)
     Return: the box element (jQuery object)
     */
  defProp(NYSS.MessageBox, 'buildBox', function(r,undefined) {
    if (r==undefined) { r = true; }
    if (r) { this._builtBox = undefined; }
    if (!this._builtBox || r) {
      this._builtBox = this.buildContainer();
      var thisobj = this;
      ['Icon','Caption','Messages'].forEach(function(v,i) {
        thisobj._builtBox.append(thisobj['build'+v]());
      });
    }
    return this._builtBox;
  });

  /* method NYSS.MessageBox.buildCaption()
     Builds the div for the caption element

     Parameters: none
     Return: the caption element (jQuery object), if populated
             otherwise '' (blank string)
     */
  defProp(NYSS.MessageBox, 'buildCaption', function() {
    var ret='';
    if (this.caption) {
      ret = $(['<div class="NYSS-MessageBox-caption">',this.caption,'</div>'].join(''));
    }
    return ret;
  });

  /* method NYSS.MessageBox.buildCloseButton()
     Builds the close button for messages' containing div

     Parameters:
       s (string): the interior content of the button element
     Return: the button element (jQuery object)
     */
  defProp(NYSS.MessageBox, 'buildCloseButton', function(s) {
    if (!s) { s = ''; }
    thisobj=this;
    ret = $(['<div class="NYSS-MessageBox-messages-close">',String(s),'</div>'].join(''))
          .bind('click',{s:true,o:thisobj},thisobj.removeMessages);
    return ret;
  });

  /* method NYSS.MessageBox.buildContainer()
     Builds the containing div for the notification box

     Parameters: none
     Return: the containing element (jQuery object)
     */
  defProp(NYSS.MessageBox, 'buildContainer', function() {
    var ret = $('<div class="NYSS-MessageBox"></div>')
                  .addClass(this.formatClasses(this.container_class));
    if (this.container_id) {
      ret.attr('id',this.container_id);
    }
    return ret;
  });

  /* method NYSS.MessageBox.buildIcon()
     Builds the div for the notification icon, if used

     Parameters: none
     Return: the icon element (jQuery object), if used
             otherwise '' (blank string)
     */
  defProp(NYSS.MessageBox, 'buildIcon', function() {
    var ret = '';
    if (this.use_icon && this.icon_element) {
      var iclasses = this.formatClasses(this.icon_class);
      ret = $(['<',this.icon_element,'></',this.icon_element,'>'].join(''))
              .addClass(['fa',(iclasses ? iclasses : 'fa-warning')].join(' '));
    }
    return ret;
  });

  /* method NYSS.MessageBox.buildMessage()
     Builds the div for a single message element

     Parameters:
       s (string): the message to include
     Return: the message element (jQuery object), if populated
             otherwise '' (blank string)
     */
  defProp(NYSS.MessageBox, 'buildMessage', function(s) {
    var ret='';
    if (s) {
      ret = $(['<div class="NYSS-MessageBox-message">',String(s),'</div>'].join(''));
    }
    return ret;
  });

  /* method NYSS.MessageBox.buildMessages()
     Builds the containing div for any message elements, if required

     Parameters: none
     Return: the messages containing div, with messages (jQuery object), if messages
             were available.
             otherwise '' (blank string)
     */
  defProp(NYSS.MessageBox, 'buildMessages', function() {
    var ret='';
    if (this.messages.length) {
      ret = $('<div class="NYSS-MessageBox-messages"></div>');
      ret.append(this.buildCloseButton());
      var thisobj = this;
      this.messages.forEach(function(v,i){
        ret.append(thisobj.buildMessage(v));
      });
    }
    return ret;
  });

  /* method NYSS.MessageBox.formatClasses(s)
     This function standardizes the input parameter into a space-delimited string.
     Arrays are collapsed using .join(), strings are passed as-is, and all other
     objects are passed through the String() constructor.

     Parameters:
       s (mixed): a string or Array of class names to add
     Return: string
     */
  defProp(NYSS.MessageBox,'formatClasses',function(s) {
    var ret = '';
    switch(s.constructor) {
      case Array: ret = s.join(' '); break;
      case String: ret = s; break;
      default: ret = String(s);
    }
    return ret;
  })

  /* method NYSS.MessageBox.generateID()
     Provides a method of generating a unique ID for a message box

     Parameters: none
     Return: string (the generated ID)
     */
  defProp(NYSS.MessageBox,'generateID',function(undefined) {
    var static_count=0;
    var static_mark=0;
    return (function(){
      var gid = moment().valueOf();
      if (gid == static_mark) {
        static_count++;
        gid = [gid,'_',static_count].join('');
      } else {
        static_mark = gid;
        static_count = 0;
      }
      return ['NYSS_MessageBox_',gid].join('');
    });
  }());

  /* method NYSS.MessageBox.removeBox()
     Remove the notification box.

     Parameters:
      s (boolean) : indicates if box should be removed from the dom
     Return: none
     */
  defProp(NYSS.MessageBox, 'removeBox', function(s,undefined) {
    if (s===undefined) { s = true; }
    if (this._builtBox) {
      var thisobj=this;
      var cleanfunc = (Boolean(s) ? function() {
                                      thisobj._builtBox.remove();
                                      thisobj._builtBox = null;
                                    }
                                  : undefined);
      this._builtBox.fadeOut(1000,cleanfunc);
    }
  });

  /* method NYSS.MessageBox.removeMessages()
     Remove the notification's messages box, if present

     Parameters:
       s (boolean): indicates if the messages should also be cleared on the object
     Return: none
     */
  defProp(NYSS.MessageBox, 'removeMessages', function(s,undefined) {
    if (s.data) {
      var thisobj=s.data.o;
      s = s.data.s;
    } else {
      var thisobj=this;
    }
    if (thisobj._builtBox) {
      var cleanfunc = (Boolean(s) ? function(){
                                      thisobj.messages=[];
                                      thisobj._builtBox.find('.NYSS-MessageBox-messages').remove();
                                      thisobj.removeBox(true);
                                      }
                                  : undefined);
      thisobj._builtBox.find('.NYSS-MessageBox-messages').fadeOut(300,cleanfunc);
    }
  });

  /* method NYSS.MessageBox.render(s)
     Creates the jQuery object for the notification box from the current configuration.
     If argument s is provided, the instance's settings are updated with any properties
     if contains (see validateSettings() above).  Once the box is built, it will be
     appended to the target element, providing parent_target is populated with an existing
     element.

     Parameters:
       s (object): an optional object with configuration settings as properties
     Return: the box object if it could not be appended to a target,
             otherwise true
     */
  defProp(NYSS.MessageBox, 'render', function(s) {
    if (s) { this.validateSettings(s); }
    return (this.appendTo() ? true : this._builtBox);
  });

  /* method NYSS.MessageBox.validateSettings(s)
     Updates the configuration of the instance and standardizes all properties
     into expected, acceptable values.  If an object s is passed as an argument,
     any properties found in the object will be added to the instance.

     Parameters:
       s (object): an optional object with configuration settings as properties
     Return: none
     */
  defProp(NYSS.MessageBox,'validateSettings',function(s) {
    $.extend(this,s);
    var thisobj = this;
    ['caption','container_id','icon_element','parent_target'].forEach(function(v,i){
      thisobj[v] = thisobj[v] ? String(thisobj[v]) : '';
    });
    if (!this.container_id) { this.container_id=this.generateID(); }
    this.container_class = this.formatClasses(this.container_class);
    this.icon_class = this.formatClasses(this.icon_class);
    if (this.messages) {
      if (this.messages.__proto__.constructor != Array) {
        this.messages = [String(this.messages)];
      }
    } else {
      this.messages = [];
    }
    this.parent_target = this.parent_target ? this.parent_target : 'body';
    this.use_icon = this.use_icon ? true : false;
  });
})(jQuery);
