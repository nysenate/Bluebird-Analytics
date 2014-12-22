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
              graphData:    { ykeys:['page_views'], labels:['Page Views'], xkey:'timerange', }
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
                    { field:'page_views',    mod:'sum',  fmt:'intperk'},
                    { field:'avg_resp_time', mod:'calc', fmt:'microsec'} ],
      props:{
              wrapperSize: 'col-lg-12',
              headerIcon: 'fa fa-bar-chart-o',
              valueCaption: 'Performance Stats',
              graphData: { ykeys:['http_500','http_503','avg_resp_time','page_views'],
                           labels:['App Errors','Database Errors','Response Time','Page Views (x1000)'],
                           xkey:'timerange' }
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
  users: [
    {
      report_name:  'user_list',
      report_type:  'list',
      target_table: 'summary',
      datapoints: [ { field:'location_name', mod:'group', header:'Office Location' },
                    { field:'server_name', mod:'group', header:'Server'},
                    { field:'remote_ip', mod:'group', header:'Remote IP'},
                    { field:'page_views',    mod:'sum',  fmt:'int', header:'Total Views' }  ],
      props: { titleText:'Most Active Instances', widgetID:'active_instances', wrapperSize:'col-lg-9 center-block' },
      sortorder: { page_views:'DESC' }
    },
  ],
  userdetails: [
    {
      report_name:  'page_views',
      report_type:  'summary',
      target_table: 'summary',
      datapoints:   [ { field:'page_views', mod:'sum', fmt:'intcomma' } ],
      props:{
          headerIcon:   'fa fa-files-o fa-3x',
          valueCaption: 'Pages Served',
          wrapperID:    'page_views'
      }
    },
    {
      report_name:  'unique_pages',
      report_type:  'summary',
      target_table: 'uniques',
      datapoints:   [ { field:'path', mod:'countd', fmt:'intcomma' } ],
      props:{
          headerIcon:   'fa fa-files-o fa-3x',
          valueCaption: 'Unique URLs',
          wrapperID:    'unique_pages'
      }
    },
    /* removed pending review of reports */
    /*{
      report_name:  'avg_response',
      report_type:  'summary',
      target_table: 'summary',
      datapoints:   [ { field:'avg_resp_time', mod:'calc', fmt:'microsec' } ],
      props:{
          headerIcon:   'fa fa-files-o fa-3x',
          valueCaption: 'Avg Resp Time',
          wrapperID:    'avg_resp_time'
      }
    },*/
    {
      report_name:  'num_instances',
      report_type:  'summary',
      target_table: 'summary',
      datapoints:   [ { field:'instance_id', mod:'countd', fmt:'intcomma' } ],
      props:{
          headerIcon:   'fa fa-files-o fa-3x',
          valueCaption: 'Active Instances',
          wrapperID:    'num_instances'
      }
    },
    {
      report_name:  'uptime',
      report_type:  'summary',
      target_table: 'summary',
      datapoints:   [ { field:'uptime', mod:'calc', fmt:'percent' } ],
      props:{
          headerIcon:   'fa fa-files-o fa-3x',
          valueCaption: 'Successful Requests',
          wrapperID:    'uptime'
      }
    },
    /* removed pending review of reports */
    /*{ report_name:  'user_details',
      report_type:  'list',
      target_table: 'request',
      datapoints: [ { field:'path',          header:'Path',     mod:'group'  },
                    { field:'resp_code',     header:'Views',    mod:'count'  },
                    { field:'avg_resp_time', header:'Avg Time', mod:'calc', fmt:'microsec' } ],
      props: { titleText:'Top Queries', widgetID:'top_queries',wrapperSize:'' }
    },*/
  ]
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
    var Performance = $('#performance-picker').chartpicker();

    // for each of the configuration parameters execute an AJAX call
    // on callback place data in correct container
    $.fn.Render = function() {

      // compile the filters being used for current reports
      page_filters = {
                      starttime:   DateRange.start_moment.format(moment.NYSS_df.data),
                      endtime:     DateRange.end_moment.format(moment.NYSS_df.data),
                      granularity: DateRange.granularity,
                      instance:    Instance.instance_name,
                      /*listcount:   10,
                      listpage:    1,*/
                      custom:      [],
                     };

      // set up an AJAX request for each report configured in the current view
      var current_requests = {summary:[],list:[],chart:[]};
      // sort the reports by type and add filters to each definition
      $.each(report_config[view], function( report_key, report_def ) {
        current_requests[report_def.report_type].push(report_def);
      });
      // call each report
      // one request per type
      $.each(current_requests, function(report_type, reports) {
        thisWidget = report_type.capitalize()+'ReportWidget';
        if (NYSS[thisWidget] && reports.length) {
          var target_elem = '#'+report_type+'-wrapper';
          widgets[report_type] = new NYSS[thisWidget]({target_wrapper:target_elem, reports:reports, filters:page_filters});
          widgets[report_type].render();
        }
      });

    };

    // add responsiveness hooks for Morris charts
    $(window).resize(function() {
      $.each(widgets.chart.report_obj, function(i,v){ v.graphObj.redraw(); });
    });

    // TODO: optimize/refactor all below
    ////////////////////////////////
    // Data tables code starts here
    ////////////////////////////////
    var dimensions = [], observations = [];
    $('#select-observation').multiSelect({
      keepOrder: true,
      selectableHeader: "<div class='custom-header'>Available Observations</div>",
      selectionHeader: "<div class='custom-header'>Selected Observations</div>",
      afterSelect: function(values) {
        Array.prototype.push.apply(observations, values)
      },
      afterDeselect: function(values) {
        observations = observations.filter(function(observation) {
          return values.indexOf(observation) == -1
        });
      }
    });

    $('#select-dimension').multiSelect({
      keepOrder: true,
      selectableHeader: "<div class='custom-header'>Available Dimensions</div>",
      selectionHeader: "<div class='custom-header'>Selected Dimensions</div>",
      afterSelect: function(values) {
        Array.prototype.push.apply(dimensions, values)
      },
      afterDeselect: function(values) {
        dimensions = dimensions.filter(function(dimension) {
          return values.indexOf(dimension) == -1
        });
      }
    });

    $('#build-table').click(function() {
      var headers = [];
      var aoColumns = [];
      $.each(dimensions, function() {
        headers.push("<th data-searchable='1'>"+$('#select-dimension option[value="'+this+'"]').html()+"</th>");
        aoColumns.push({ bSearchable: true});
      });
      $.each(observations, function() {
        headers.push("<th>"+$('#select-observation option[value='+this+']').html()+"</th>");
        aoColumns.push({ bSearchable: false});
      });
      var html = "<table><thead><tr>"+headers.join(" ")+"</tr></thead><tbody></tbody></table>";
      $('#datatable-container').html(html).find('table').dataTable({
        bProcessing: true,
        bServerSide: true,
        sAjaxSource: $("body").data("context-path")+"/api/get_datatable",
        fnServerParams: function ( aoData ) {
          aoData.push(
            { name: "view", value: "datatable"},
            { name: "start_datetime", value: DateRange.start_moment.format(moment.NYSS_df.data)},
            { name: "end_datetime", value: DateRange.end_moment.format(moment.NYSS_df.data)},
            { name: "granularity", value: DateRange.granularity},
            { name: "install_class", value: "prod"},
            { name: "instance_name", value: Instance.instance_name},
            { name: "dimensions", value: dimensions.join(',')},
            { name: "observations", value: observations.join(',')}
          );
        },
        "aoColumns": aoColumns,
      }); //.fnSetFilteringDelay(500);
    })

    function loadQuery(name, new_dimensions, new_observations) {
      $('#select-dimension').multiSelect('deselect_all').multiSelect('select', new_dimensions);
      $('#select-observation').multiSelect('deselect_all').multiSelect('select', new_observations);
      if (name != "Create New Query") {
        $('#query-name').val(name);
        $("#save-query").html("Update Query");
        $("#delete-query").show();
      }
      else {
        $('#query-name').val('');
        $("#save-query").html("Save Query");
        $("#delete-query").hide();
      }
    }

    $("#delete-query").click(function() {
      $.ajax({
        url: $("body").data("context-path")+"/api/delete_query",
        dataType: 'json',
        data: {
          id: $("#saved-queries").val(),
        },
        success: function(data) {
          $('#saved-queries option[value='+data.id+']').remove();
          $('#saved-queries').change();
        },
        error: function(data) {
          alert("Delete failure!");
        }
      });
    });

    $("#save-query").click(function() {
      $.ajax({
        url: $("body").data("context-path")+"/api/save_query",
        dataType: 'json',
        data: {
          id: $("#saved-queries").val(),
          name: $('#query-name').val(),
          dimensions: dimensions.join(','),
          observations: observations.join(','),
        },
        success: function(data) {
          option = $('#saved-queries option[value='+data.id+']');
          if (option.length) {
            option.data('dimensions', data.dimensions);
            option.data('observations', data.observations);
            option.html(data.name);
          }
          else {
            var option = "<option value='"+data.id+"' data-dimensions='"+data.dimensions+"' data-observations='"+data.observations+"'>"+data.name+"</option>";
            $('#saved-queries').append(option).val(data.id).change();
          }
        },
        error: function(data) {
          alert("Save failure!");
        }
      });
    });

    $('#saved-queries').change(function() {
      var option = $(this).find('option:selected');
      loadQuery(option.html(), option.data('dimensions').split(','), option.data('observations').split(','));
    }).change();

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
