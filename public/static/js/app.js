!function ($) {

  // a big configuration object for the sites dynamically loaded areas
  // pages have three kinds of AJAX rendered views (summary, list, graph)
  // we map the JSON keys to the HTML objects class to populate the data
  var config = {
    dashboard: {
      summary : {
        page_views : "page_views",
        unique_pages : "_distinct_pages",
        unique_users : "unique_ips",
        unique_instances : "active_instances",
        uptime : "",
      },
      chart : {
        element: 'overview',
        ykeys: ['page_views'],
        labels: ['Page Views'],
      },
      list : {
        //top 10 active instances, users
        top_instances: {
          element: '#top_instances',
          headers: ['Server Name', 'Active Users', 'Total Requests']
        },

        top_users: {
          element: '#top_users',
          headers: ['User IP', 'Instance', 'Total Requests']
        }
      },
    },
    performance: {
      summary : {
        'app_errors': '500_errors',
        'db_errors': '503_errors',
        response_time : 'avg_response_time',
        uptime : "",
      },
      chart : {
        element: 'overview',
        ykeys: ['500_errors','503_errors','avg_response_time','page_view'],
        labels: ['App Errors','Database Errors','Response Time','Page Views (x1000)'],
      },
      list : {
        slow_queries: {
          element: '#slow_queries',
          headers: ["Path", "Total Views", "Average Response Time"]
        }
      },
    },
    datatable: {},
    audience: {},
    behavior: {}
  };

  $(document).ready(function() {
    function get_granularity(start_moment, end_moment) {
      var difference = end_moment.unix() - start_moment.unix();
      var granularity;
      var minute = 60;
      var hour = 60*60;
      var day = 24*60*60;
      switch (true) {
        case (difference < 12*hour):
          granularity = 'minute';
          break;
        case (difference < 7*day):
          granularity = '15minute';
          break;
        case (difference < 14*day):
          granularity = 'hour';
          break;
        case (difference < 200*day):
          granularity = 'day';
          break;
        default:
          granularity = 'month';
          break
      }
      console.log("Date range in seconds: "+difference);
      console.log("Chosen granularity: "+granularity);
      return granularity;
    };

    var graph = '';
    var jqxhr = [];
    var count = 0;
    var view = $('body').data('view');
    var version = $('.app-version').text();
    // we have a persistent green block for release notes that can be dismissed
    if ($.cookie('application_version') == version ) {
      $('.cookie').hide();
    };
    $('.cookie .close').click(function() {
      $.cookie('application_version', version, { expires: 365, path: '/' });
    });


    ///////////////////////////////////////////////////////
    // HashStorageModule
    ///////////////////////////
    var HashStorageModule = function() {
      this.data = {}
      if (window.location.hash) {
        values = window.location.hash.substr(1).split('&');
        for (key in values) {
          parts = values[key].split('=', 2)
          this.data[parts[0]] = parts[1];
        }
        console.log(this.data);
      }
      return this;
    };

    HashStorageModule.prototype.update = function(new_data) {
      this.data = $.extend(this.data, new_data);
      values = []
      for (key in this.data) {
        values.push(key+'='+this.data[key])
      }
      window.location.hash = values.join('&');
    };

    HashStorageModule.prototype.has = function(keys) {
      for (index in keys) {
        key = keys[index];
        if (!(key in this.data)) {
          return false;
        }
      }
      return true;
    };

    HashStorage = new HashStorageModule();


    ///////////////////////////////////////////////
    // BBDateRangePicker Plugin
    ////////////////////////////////
    var data_df = 'YYYY-MM-DD HH:mm';
    var display_df = 'MMMM Do YYYY, h:mm a';
    $.fn.bbdaterangepicker = function(user_options) {
      var options = $.extend({}, user_options);

      // We automatically bind update to 'this' to avoid context errors
      var update = function(new_start_moment, new_end_moment) {
        console.log("Updating date range to: "+new_start_moment.format(display_df) + ' - ' + new_end_moment.format(display_df));
        this.start_moment = new_start_moment;
        this.end_moment = new_end_moment
        this.granularity = get_granularity(this.start_moment, this.end_moment);
        this.find('span').html(new_start_moment.format(display_df) + ' - ' + new_end_moment.format(display_df));

        // Save these new values to the hash and cookie
        HashStorage.update({
          'data-start': new_start_moment.format(data_df),
          'data-end': new_end_moment.format(data_df)
        });
        // $.cookie('data-start', new_start_moment.format(data_df), { expires: 1, path: '/' });
        // $.cookie('data-end', new_end_moment.format(data_df), { expires: 1, path: '/' });
      }.bind(this);

      // Initialize the date range
      if (HashStorage.has(['data-start', 'data-end'])) {
        update(moment(HashStorage.data['data-start']), moment(HashStorage.data['data-end']));
      }
      // else if ($.cookie('data-start') !== undefined) {
      //   update(moment($.cookie('data-start')), moment($.cookie('data-end')));
      // }
      else {
        update(moment().subtract('hours', 1), moment());
      }

      // Initialize the date picker
      this.daterangepicker({
          ranges: {
             'Last Hour': [ moment().subtract('hours', 1), moment()],
             'Today': [moment().startOf('day'), moment()],
             'Yesterday': [moment().subtract('days', 1).startOf('day'), moment().subtract('days', 1).endOf('day')],
             'Last 7 Days': [moment().subtract('days', 6).startOf('day'), moment()],
             'Last 30 Days': [moment().subtract('days', 29).startOf('day'), moment()],
             // 'This Month': [moment().startOf('month').startOf('day'), moment().endOf('month')],
             // 'Last Month': [moment().subtract('month', 1).startOf('month').startOf('day'), moment().subtract('month', 1).endOf('month').endOf('day')]
            },
          timePicker: true,
          dateLimit: { days: 360 },
          minDate: '04/10/2013',
          maxDate: moment().endOf('day'),
          timePickerIncrement: 15,
          startDate: this.start_moment,
          endDate: this.end_moment,
      });

      this.on('apply.daterangepicker', function(ev, picker) {
          update(picker.startDate, picker.endDate);
          $('#page-wrapper').Render();
      });

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
        // $.cookie('data-instance', new_instance);
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

    $.fn.chartpicker = function(user_options) {
      var options = $.extend({}, user_options);

      // // We automatically bind update to 'this' to avoid context errors
      // var update = function(new_instance) {
      //   console.log("Updating instance name to: "+new_instance);
      //   if (this.instance_name != new_instance) {
      //     this.val(new_instance);
      //   }
      //   this.instance_name = new_instance;
      //   // $.cookie('data-instance', new_instance);
      //   HashStorage.update({'data-instance': new_instance});
      // }.bind(this);


      // if(HashStorage.has(['data-instance'])) {
      //   update(HashStorage.data['data-instance']);
      // }
      // // else if ($.cookie('data-instance') != undefined) {
      // //   console.log('Cookie val: '+$.cookie('data-instance'));
      // //   update($.cookie('data-instance'));
      // // }
      // else {
      //   update(this.val());
      // }

      // this.selectpicker();
      // this.change(function (event) {
      //   update($(this).val());
      //   $('#page-wrapper').Render();
      // });
      // return this;
    };



    var DateRange = $('#reportrange').bbdaterangepicker();
    var Instance = $('#instance-picker').instancepicker();
    var Performance = $('#performance-picker').chartpicker();


    // for each of the configuration parameters execute an AJAX call
    // on callback place data in correct container
    // use a count to see if we have processed view types
    $.fn.Render = function() {
      if (view == 'datatable') {
        $(".dataTable").each(function() {
          $(this).dataTable().fnDraw();
        });
        return;
      }

      // don't duplicate previous requests
      $('.jumbotron h1 .fa').remove();
      $('.jumbotron h1').append('<i class="fa fa-cog fa-spin"></i>');
      $.each(config[view], function( index, settings ) {
        var type = index
        jqxhr[index] = $.ajax({
          url: $("body").data("context-path")+"/api/get_"+type,
          timeout: 100000,
          data: {
            view: view,
            start_datetime: DateRange.start_moment.format(data_df),
            end_datetime: DateRange.end_moment.format(data_df),
            granularity: DateRange.granularity,
            install_class: 'prod',
            instance_name: Instance.instance_name,
            list_size: 10,
            list_offset: 0
          }
        })
        .done(function(response) {
          if(type === "summary"){
            // loop through the selector mapping for this section
            // match data keys to div id's
            $.each(settings, function( key, value ) {

              // some custom formatting
              switch(key)
              {
              case 'response_time':
                output = parseFloat((response.data[value] /1000000)).toFixed(2)+"s";
                console.log(parseInt(output));
                if(parseInt(output) > 1 ) {
                  $('#'+key).parent().parent().parent().parent().removeClass('panel-info').addClass('panel-danger');
                }else{
                  $('#'+key).parent().parent().parent().parent().removeClass('panel-danger').addClass('panel-info');
                };
                break;
              case 'uptime':
                output = (response.data['503_errors']+response.data['500_errors'])/response.data['page_views'];
                output = !isNaN(output) ? parseFloat((100-output)).toFixed(2)+"%" : "N/A";
                if(parseInt(output) != 100 && isNaN(output)) {
                  $('#'+key).parent().parent().parent().parent().removeClass('panel-info').addClass('panel-danger');
                }else{
                  $('#'+key).parent().parent().parent().parent().removeClass('panel-danger').addClass('panel-info');
                };
                break;
              case 'app_errors':
              case 'db_errors':
                if(parseInt(response.data[value]) != 0) { $('#'+key).parent().parent().parent().parent().removeClass('panel-info').addClass('panel-danger')};
                output = response.data[value];
                break;
              default:
                output = response.data[value];
              }

              $('#'+key).html(output);
            });
            $('.summary').slideDown();
          }else if (type === "chart") {
            // add data to chart
            $('.chart').slideDown();
            if($('#'+settings['element']+" svg").length) {
              // update existing chart
              graph.setData(response.data);
            }else{
              // parse data in morris
              graph = Morris.Line({
                element: settings['element'],
                data: response.data,
                xkey: 'chart_time',
                ykeys: settings['ykeys'],
                labels: settings['labels'],
                pointSize: 3,
                // C02942 - lt red
                // D95B43 - lt orange
                // 53777A - slate blue
                // 542437 - deep purple
                // DF151A - fire truck red
                lineColors: ["#C02942", "#D95B43","#53777A", "#542437","#DF151A"],
                parseTime: true,
                // goals: [5.0,10.0,50.0,100.0],
                // goalLineColors: ["#EA7E58","#D13A43", "#A51D35","#931A21"],
                // goalStrokeWidth:2,
                continuousLine: false,
                // axes: false,
                // ymin: 0,
                grid: true,
                ymax: 'auto 100',
                hideHover: true,
                hoverCallback: function (index, options, content) {
                  switch (DateRange.granularity) {
                    case 'minute':
                    case '15minute':
                        var time = moment(options.data[index]["chart_time"]).format('ddd l, hh:mm A');
                      break;
                    case 'hour':
                        var time = moment(options.data[index]["chart_time"]).format('ddd l, h A');
                      break;
                    case 'day':
                        var time = moment(options.data[index]["chart_time"]).format('ddd l');
                      break;
                    case 'month':
                        var time = moment(options.data[index]["chart_time"]).format('MMMM YYYY');
                      break;
                    default:
                        var time = moment(options.data[index]["chart_time"]).format('ddd l, hh:mm A');
                      break
                  }
                  var html = " <div class='morris-hover-row-label'> "+time+"</div>";
                  $.each(options.data[index], function( key, values ) {
                    if($.inArray(key, options.ykeys)!==-1){
                      var id = $.inArray(key, options.ykeys);
                      html += " <div class='morris-hover-point' style='color: "+options.lineColors[id]+"'> "+options.labels[id]+": "+values+"</div> ";
                    }
                  });
                  return html;
                },
                smooth: true
              });

            }

          } else if (type === "list") {
            $.each(settings, function( table, table_data ) {
              var response_data = response['data'][table];
              var table = $(table_data['element']);
              var thead = "<thead><tr>";
              for (var header in table_data['headers']) {
                thead +="<th>"+table_data['headers'][header]+'</th>';
              }
              table.html(thead+"</tr></thead>");
              var tbody = "<tbody>";
              for (var row in response_data) {
                var row_data = response_data[row]
                tbody += "<tr>";
                for (var col in row_data) {
                  // if row isn't a number, link it to the
                  if (isNaN(row_data[col])) {
                    tbody += "<td> <a href='/"+table_data['link_to']+"?q="+row_data[col]+"'>"+toTitleCase(row_data[col])+"</a></td>";
                  }else{
                    tbody += "<td>"+toTitleCase(row_data[col])+"</td>";
                  }
                }
                tbody += "</tr>";
              }
              table.append(tbody+"</tbody>");
            });
            $('.table-list').slideDown();
          }
        })
        .fail(function() {
          console.log('error -- view: '+view+" | type: "+type);
          $('.jumbotron h1 .fa-cog').remove();
          if ($('.jumbotron h1 .fa').length == 0) {
            $('.jumbotron h1').append('<i class="fa fa-warning danger"></i>');
          };
        })
        .always(function() {
          count++;
          if(count >= Object.keys(config[view]).length){
            $('.jumbotron h1 .fa-spin').fadeOut(1000);
          }
        });
      });
    };


    function toTitleCase(str)
    {
      // dont mess with urls
      if (str.charAt(0) != "/") {
        return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
      } else{
        return str;
      }
    }

    $('#page-wrapper').Render();


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
            { name: "start_datetime", value: DateRange.start_moment.format(data_df)},
            { name: "end_datetime", value: DateRange.end_moment.format(data_df)},
            { name: "granularity", value: DateRange.granularity},
            { name: "install_class", value: "prod"},
            { name: "instance_name", value: Instance.instance_name},
            { name: "dimensions", value: dimensions.join(',')},
            { name: "observations", value: observations.join(',')}
          );
        },
        "aoColumns": aoColumns,
      }).fnSetFilteringDelay(500);
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
  });
}(jQuery)
