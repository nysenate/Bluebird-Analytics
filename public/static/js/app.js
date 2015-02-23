/* TODO:
    use of the HashStorage module is inline..  migrate to hashstorage.js
    do we even need HashStorage at this point?
*/

/* ****** Global variables ****** */
// HashStorage mechanism, see hashstorage.js
var HashStorage = NYSS.HashStorageModule;

// line colors for charts.  Order of appearance is important
var chart_colors = {
                     solidblue:   '#2222AA', solidred:    '#AA2222', solidgreen:  '#22AA22',
                     slateblue:   '#53777A', lightred:    '#C02942', lightorange: '#D95B43',
                     deeppurple:  '#542437', firered:     '#DF151A',
                   }
// extend chart_colors for easy reference
Object.defineProperty(chart_colors, 'getColors', {
  writable:true,
  value: function getColors(colornames) {
    var ret=[];
    if (!colornames) { colornames = function(t){var r=[];for (c in t) {r.push(c);} return r;}(this); }
    for (x in colornames) {
      ret.push(this[colornames[x]]);
    }
    return ret;
  }
});

/* ****** Extensions for Array.prototype ****** */
// Array method to count the number of AJAX requests still pending
Object.defineProperty(Array.prototype,'countActiveAJAX',{
  get: function() {
    var size = 0, xx;
    for (xx in this) {
      if (this.hasOwnProperty(xx)
          && this[xx].hasOwnProperty('readyState')
          && this[xx].readyState!=4
         ) { size++; }
    }
    return size;
  }
});


/* ****** Extensions to moment.js ****** */
// quick-reference for date formats
moment.NYSS_df = { data:'YYYY-MM-DD HH:mm', display:'MMMM Do YYYY, h:mm a' };

/* Add granularity function to the moment prototype.
   end_moment: another moment object.
   If end_moment is not a moment object, it will be set to moment() (i.e., current time).
   The difference in seconds is compared to the scaleBreaks array to find the best range.
   Returns an object {scale:(string), diff:(seconds)} */
moment.fn.getScale = function(end_moment) {
  /* configurable ranges.  array should *ALWAYS* be sorted numerically */
  var scaleBreaks = [
                     {scale:'minute',   diff:12*60*60},     /* 12 hours */
                     {scale:'15minute', diff:7*24*60*60},   /* 7 days */
                     {scale:'hour',     diff:14*24*60*60},  /* 14 days */
                     {scale:'day',      diff:200*24*60*60}, /* 200 days */
                     {scale:'month',    diff:0}             /* default */
                    ]
                    .sort(function (a, b) { return a.diff - b.diff; });
  /* make sure the parameter is a moment object, default to current time */
  if (!(
        typeof end_moment=='object'
        && end_moment.hasOwnProperty('_isAMomentObject')
        && end_moment._isAMomentObject
        )) {
    end_moment = moment();
  }
  /* get the difference in seconds */
  var diff = Math.abs(end_moment.unix() - this.unix());
  /* find the nearest match */
  var ret = scaleBreaks.filter( function(i){ return diff < i.diff } ).shift();
  /* if no match, look for the default */
  if (!(ret)) { ret = scaleBreaks.filter( function(i){ return i.diff==0; } ).shift(); }
  /* if no default, use the largest range */
  if (!(ret)) { ret = scaleBreaks[scaleBreaks.length - 1]; }
  return ret;
};

/* Function to retrieve page-level filters */
function get_page_filters() {
  return {
          starttime:   $('#reportrange').bbdaterangepicker().start_moment.format(moment.NYSS_df.data),
          endtime:     $('#reportrange').bbdaterangepicker().end_moment.format(moment.NYSS_df.data),
          granularity: $('#reportrange').bbdaterangepicker().granularity,
          instance:    $('#instance-picker').val(),
          custom:      [],
         };
}


/* ****** Application logic ****** */
(function($,undefined) {

  $(document).ready(function() {
    /* Set up some initial variables */
    var view = $('body').data('view');


    //////////////////////////////////////////////////////
    // BBDateRangePicker Plugin
    // need to factor the HashStorage code
    //////////////////////////////////////////////////////
    $.fn.bbdaterangepicker = function(user_options) {
      var options = $.extend({}, user_options);

      // We automatically bind update to 'this' to avoid context errors
      var update = function(chosenLabel, new_start_moment, new_end_moment) {
        // if no custom range, set the start/end
        if (chosenLabel !== "Custom Range") {
          new_end_moment = moment();
          switch (chosenLabel) {
            case "Last Hour": new_start_moment = moment().subtract(1, 'hours'); break;
            case "Today":     new_start_moment = moment().startOf('day'); break;
            case "Last 7 Days": new_start_moment = moment().subtract(6, 'days').startOf('day'); break;
            case "Last 30 Days": new_start_moment = moment().subtract(29, 'days').startOf('day');break;
            /* default to "Yesterday" */
            default:
              new_start_moment = moment().subtract(1, 'days').startOf('day');
              new_end_moment = moment().subtract(1, 'days').endOf('day');
              chosenLabel = "Yesterday";
              break;
          }
        }
        // set the properties
        this.start_moment = new_start_moment;
        this.end_moment = new_end_moment;
        this.chosenLabel = chosenLabel;
        this.granularity = this.start_moment.getScale(this.end_moment).scale;
        this.find('span').html(
            new_start_moment.format(moment.NYSS_df.display) +
            ' - ' + new_end_moment.format(moment.NYSS_df.display)
            );
        // Save these new values to the hash and cookie
        HashStorage.update({
          'data-start': new_start_moment.format(moment.NYSS_df.data),
          'data-end': new_end_moment.format(moment.NYSS_df.data),
          'data-type': chosenLabel
        });
        $.cookie('data-start',new_start_moment.format(moment.NYSS_df.data),{expires:1,path:'/'});
        $.cookie('data-end',new_end_moment.format(moment.NYSS_df.data),{expires:1,path:'/'});
        $.cookie('data-type',chosenLabel,{expires:1,path:'/'});
      }.bind(this);

      // // Initialize the date range
      if (HashStorage.has(['data-start', 'data-end', 'data-type'])) {
        update(HashStorage.data['data-type'], moment(HashStorage.data['data-start']), moment(HashStorage.data['data-end']));
      }
      else if ($.cookie('data-start') !== undefined) {
        update($.cookie('data-type'), moment($.cookie('data-start')), moment($.cookie('data-end')));
      }
      else {
        update("Last Hour", moment().subtract(1, 'hours'), moment(), 'Relative');
      }

      $('#reportrange').daterangepicker({
          ranges: {
            'Last Hour': [ moment().subtract(1, 'hours'), moment()],
            'Today': [moment().startOf('day'), moment()],
            'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
            'Last 7 Days': [moment().subtract(6, 'days').startOf('day'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days').startOf('day'), moment()],
          },
          timePicker: true,
          timePicker12Hour: false,
          dateLimit: { days: 360 },
          minDate: '04/10/2013',
          maxDate: moment().endOf('day'),
          timePickerIncrement: 5,
          startDate: this.start_moment,
          endDate: this.end_moment,
          parentEl: ".navbar"
      });
      this.on('apply.daterangepicker', function(ev, picker) {
        update(picker.chosenLabel, picker.startDate, picker.endDate);
        $('#page-wrapper').Render();
      });
      $('.ranges li').removeClass('active');
      $('.ranges li:contains("'+this.chosenLabel+'")').addClass('active');
      return this;
    }

    //////////////////////////////////////////////////////
    // InstancePicker Plugin
    // need to factor the HashStorage code
    //////////////////////////////////////////////////////
    $.fn.instancepicker = function(user_options) {
      var options = $.extend({}, user_options);

      /* use .bind() to avoid context errors */
      var update = function(new_instance) {
        NYSS.log("Updating instance name to: "+new_instance);
        $.cookie('data-instance', new_instance);
        HashStorage.update({'data-instance': new_instance});
      }.bind(this);


      if(HashStorage.has(['data-instance'])) {
        update(HashStorage.data['data-instance']);
      }
      else if ($.cookie('data-instance') != undefined) {
        NYSS.log('Cookie val: '+$.cookie('data-instance'));
        update($.cookie('data-instance'));
      }
      else {
        update(this.val());
      }

      this.selectpicker();
      this.change(function (event) {
        NYSS.log(event);
        update($(this).val());
        $('#page-wrapper').Render();
      });
      return this;
    };

    var DateRange = $('#reportrange').bbdaterangepicker();
    var Instance = $('#instance-picker').instancepicker();

    /* the ReportsCollection object will handle generating the necessary AJAX
       calls, and rendering the report data returned */
    $.fn.Render = function() {
      NYSS.ReportsCollection.render({ view:view, filters:get_page_filters() }, true);
    };

    /* UI/UX for pseudo-persistent version notes element */
    if ($.cookie('application_version') == $('.app-version').text() ) {
      $('.cookie').hide();
    };
    /* Enable closing/dismissing the version notes element */
    $('.cookie .close').click(function() {
      $.cookie('application_version', $('.app-version').text(), { expires: 365, path: '/' });
    });

    /* render the current page's reports */
    $('#page-wrapper').Render();

  });
})(jQuery);
