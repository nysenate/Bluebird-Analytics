/* ****** Global variables ****** */
var widgets = {};
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

var report_config = {
  dashboard: [
    { report_name:  'page_views',
      report_type:  'summary',
      target_table: 'summary',
      datapoints:   [ { field:'page_views', mod:'sum', fmt:'intcomma' } ],
      props:{
          headerIcon:   'fa fa-files-o fa-3x',
          linkTarget:   '/datatable',
          linkText:     'Browse Content',
          valueCaption: 'Pages Served',
          wrapperID:    'page_views'
      }
    },
    { report_name:  'active_users',
      report_type:  'summary',
      target_table: 'summary',
      datapoints:   [ { field:'remote_ip', mod:'countd', fmt:'intcomma' } ],
      props:{
          headerIcon:   'fa fa-files-o fa-3x',
          linkTarget:   '/users/list',
          linkText:     'User Overview',
          valueCaption: 'Active Users',
          wrapperID:    'unique_users'
      }
    },
    { report_name:  'active_instances',
      report_type:  'summary',
      target_table: 'summary',
      datapoints:   [ { field:'instance_id', mod:'countd', fmt:'intcomma' } ],
      props:{
          headerIcon:   'fa fa-users fa-3x',
          linkTarget:   '/users/list',
          linkText:     'Office Overview',
          valueCaption: 'Active Offices',
          wrapperID:    'unique_instances'
      }
    },
    { report_name:  'uptime',
      report_type:  'summary',
      target_table: 'summary',
      datapoints:   [ { field:'uptime', mod:'calc', fmt:'percent|2' } ],
      props:{
          headerIcon:   'fa fa-users fa-3x',
          linkTarget:   '/performance',
          linkText:     'Performance Overview',
          valueCaption: 'Uptime',
          wrapperID:    'uptime'
      }
    },
    { report_name: 'view_history',
      report_type: 'chart',
      target_table: 'summary',
      datapoints: [ { field:'page_views', mod:'sum' } ],
      props:{
              wrapperSize:  'col-lg-12',
              headerIcon:   'fa fa-bar-chart-o',
              valueCaption: 'Page Views',
              graphData:    {
                              chart: { type:'spline' },
                              title: { text:'Page Views' },
                            }
            }
    },
    { report_name:  'top_active_instances',
      report_type:  'list',
      target_table: 'summary',
      datapoints: [ { field:'instance_name', header:'Instance', mod:'group'  },
                    { field:'remote_ip',     header:'Users',    mod:'countd', fmt:'intcomma' },
                    { field:'page_views',    header:'Requests', mod:'sum',    fmt:'intcomma' } ],
      props: { titleText:'Most Active Instances',
               widgetID:'top_instances',
               orderBy:[ '!page_views', '!remote_ip' ]
             },
    },
    { report_name:  'top_active_users',
      report_type:  'list',
      target_table: 'summary',
      datapoints: [ { field:'location_name', header:'Location', mod:'group'  },
                    { field:'instance_name', header:'Instance', mod:'group'  },
                    { field:'page_views',    header:'Requests', mod:'sum', fmt:'intcomma' } ],
      props: { titleText:'Most Active Users',
               widgetID:'top_users',
               orderBy:[ '!page_views','location_name','instance_name' ]
             }
    },
  ],
  performance: [
    {
      report_name:  'uptime',
      report_type:  'summary',
      target_table: 'summary',
      datapoints:   [ { field:'uptime', mod:'calc', fmt:'percent' } ],
      props:{
          headerIcon:   'fa fa-files-o fa-3x',
          valueCaption: 'Uptime',
          wrapperID:    'uptime'
      }
    },
    { report_name:  'http_500',
      report_type:  'summary',
      target_table: 'summary',
      datapoints:   [ { field:'http_500', mod:'sum', fmt:'intcomma' } ],
      props:{
          headerIcon:   'fa fa-files-o fa-3x',
          valueCaption: 'Application (500) Errors',
          wrapperID:    'http_500'
      }
    },
    { report_name:  'http_503',
      report_type:  'summary',
      target_table: 'summary',
      datapoints:   [ { field:'http_503', mod:'sum', fmt:'intcomma' } ],
      props:{
          headerIcon:   'fa fa-files-o fa-3x',
          valueCaption: 'Database (503) Errors',
          wrapperID:    'http_503'
      }
    },
    { report_name:  'response_time',
      report_type:  'summary',
      target_table: 'summary',
      datapoints:   [ { field:'avg_resp_time', mod:'calc', fmt:'microsec|2' } ],
      props:{
          headerIcon:   'fa fa-files-o fa-3x',
          valueCaption: 'Average Response Time',
          wrapperID:    'avg_response_time'
      }
    },
    { report_name: 'view_history',
      report_type: 'chart',
      target_table: 'summary',
      datapoints: [ { field:'http_500',      mod:'sum',  fmt:'intcomma'},
                    { field:'http_503',      mod:'sum',  fmt:'intcomma'},
                    { field:'page_views',    mod:'sum',  fmt:'floatperk'},
                    { field:'avg_resp_time', mod:'calc', fmt:'microsec'} ],
      props:{
              wrapperSize:  'col-lg-12',
              headerIcon:   'fa fa-bar-chart-o',
              valueCaption: 'Page Views',
              graphData:    {
                              chart: { type:'spline' },
                              title: { text:'Performance Stats' },
                            }
            }
    },
    { report_name:  'top_queries',
      report_type:  'list',
      target_table: 'request',
      datapoints: [ { field:'path',          header:'Path',     mod:'group'  },
                    { field:'resp_code',     header:'Views',    mod:'count'  },
                    { field:'avg_resp_time', header:'Avg Time', mod:'calc', fmt:'microsec' } ],
      props: { titleText:'Worst Performing Requests',
               widgetID:'top_queries',
               orderBy:['!avg_resp_time'],
               wrapperSize:'col-lg-9 center-block'
             }
    },
  ],
  usage: [
    {
      report_name:  'avg_use',
      report_type:  'list',
      target_table: 'summary',
      datapoints: [ { field:'instance_name', mod:'group', header:'Instance Name' },
                    { field:'page_views',    mod:'sum',   header:'Total Views', fmt:'int' },
                    { field:'avg_resp_time', mod:'calc',  header:'Avg Time',    fmt:'microsec' } ],
      props: { titleText:'Usage', widgetID:'usage', wrapperSize:'col-lg-9 center-block' },
      sortorder: { page_views:'DESC' }
    },
    {
      report_name:  'common_task',
      report_type:  'list',
      target_table: 'request',
      datapoints: [ { field:'instance_name', mod:'group', header:'Instance Name' },
                    { field:'url_name',      mod:'group', header:'Action' },
                    { field:'timerange',     mod:'count', header:'Total Views', fmt:'int' },
                    { field:'avg_resp_time', mod:'calc',  header:'Avg Time',    fmt:'microsec' } ],
      props: { titleText:'Request Usage', widgetID:'requsage', wrapperSize:'col-lg-9 center-block' },
      sortorder: { timerange:'DESC' }
    },
  ],
};


/* ****** Extensions for Array.prototype ****** */
// Array method to count the number of AJAX requests still pending
Object.defineProperty(Array.prototype,'countActiveAJAX',{
  get: function() {
    var size = 0, key;
    for (key in this) {
      if (this.hasOwnProperty(key) && this[key].hasOwnProperty('readyState') && this[key].readyState!=4) size++;
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
          instance:    $('#instance-picker').instancepicker().instance_name,
          custom:      [],
         };
}


/* ****** Application logic ****** */
(function($,undefined) {

  $(document).ready(function() {
    /* Set up some initial variables */
    var view = $('body').data('view');


    ///////////////////////////////////////////////
    // BBDateRangePicker Plugin
    ////////////////////////////////
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
    ///////////////////////////////
    $.fn.instancepicker = function(user_options) {
      var options = $.extend({}, user_options);

      // We automatically bind update to 'this' to avoid context errors
      var update = function(new_instance) {
        console.log("Updating instance name to: "+new_instance);
        if (this.instance_name != new_instance) {
          this.val(new_instance);
        }
        this.instance_name = new_instance;
        $.cookie('data-instance', new_instance);
        HashStorage.update({'data-instance': new_instance});
      }.bind(this);


      if(HashStorage.has(['data-instance'])) {
        update(HashStorage.data['data-instance']);
      }
      else if ($.cookie('data-instance') != undefined) {
        console.log('Cookie val: '+$.cookie('data-instance'));
        update($.cookie('data-instance'));
      }
      else {
        update(this.val());
      }

      this.selectpicker();
      this.change(function (event) {
        update($(this).val());
        $('#page-wrapper').Render();
      });
      return this;
    };

    //////////////////////////////////////////////////////
    // ChartPicker Plugin
    ///////////////////////////////
    $.fn.chartpicker = function(user_options) {
      var options = $.extend({}, user_options);

      // We automatically bind update to 'this' to avoid context errors
      var update = function(new_instance) {
        console.log("Updating instance name to: "+new_instance);
        if (this.instance_name != new_instance) {
          this.val(new_instance);
        }
        this.instance_name = new_instance;
        $.cookie('data-instance', new_instance);
        HashStorage.update({'data-instance': new_instance});
      }.bind(this);


      if(HashStorage.has(['data-instance'])) {
        update(HashStorage.data['data-instance']);
      }
      // else if ($.cookie('data-instance') != undefined) {
      //   console.log('Cookie val: '+$.cookie('data-instance'));
      //   update($.cookie('data-instance'));
      // }
      else {
        update(this.val());
      }

      this.selectpicker();
      this.change(function (event) {
        update($(this).val());
        $('#page-wrapper').Render();
      });
      return this;
    };

    var DateRange = $('#reportrange').bbdaterangepicker();
    var Instance = $('#instance-picker').instancepicker();

    // for each of the configuration parameters execute an AJAX call
    // on callback place data in correct container
    $.fn.Render = function() {

      // set up an AJAX request for each report configured in the current view
      var current_requests = {summary:[],list:[],chart:[]};
      // sort the reports by type and add filters to each definition
      if (report_config[view]) {
        $.each(report_config[view], function( report_key, report_def ) {
          current_requests[report_def.report_type].push(report_def);
        });
      }
      // call each report
      // one request per type
      var tfilters = get_page_filters();
      $.each(current_requests, function(report_type, reports) {
        if (widgets[report_type]) {
          widgets[report_type].remove();
          widgets[report_type] = null;
        }
        var thisWidget = report_type.capitalize()+'ReportWidget';
        if (NYSS[thisWidget] && reports.length) {
          var target_elem = '#'+report_type+'-wrapper';
          widgets[report_type] = new NYSS[thisWidget]({target_wrapper:target_elem, reports:reports, filters:tfilters});
          widgets[report_type].render();
        }
      });

    };

    /* UI/UX for pseudo-persistent version notes element */
    if ($.cookie('application_version') == $('.app-version').text() ) {
      $('.cookie').hide();
    };
    /* Enable closing/dismissing the version notes element */
    $('.cookie .close').click(function() {
      $.cookie('application_version', $('.app-version').text(), { expires: 365, path: '/' });
    });

    // render the current page
    $('#page-wrapper').Render();

  });
})(jQuery);
