// initialize namespace
NYSS = NYSS || {};
NYSS.Analytics = NYSS.Analytics || {};

(function($,undefined) {
  $(document).ready(function() {
    // utility functions
    NYSS.Analytics.select_add_options = function(opts) {
      var thisobj = this;
      $.each(opts, function(v,t){
        var oneopt = $('<option />').val(t.id).html(t.name);
        if (t.disabled) { oneopt.attr('disabled','disabled'); }
        thisobj.append(oneopt);
      });
    }

    NYSS.Analytics.select_clear_options = function() {
      var thisobj = this;
      thisobj.find('option').remove();
    }

    NYSS.Analytics.select_load_options = function(a) {
      var ajaxparams = {
                         url:        $('body').data('context-path')+'/api/info',
                         timeout:    60000,
                         type:       'POST',
                         data:       {action:this.ajax_action, filters:get_page_filters(), reports:{no_reports:1} },
                         context:    this,
                       }
      var x=$.ajax(ajaxparams).always(this.load_options_handler);
    }

    NYSS.Analytics.select_load_options_handler = function(a,b,c) {
      var thisobj = this;
      thisobj.set_default_options();
      if (b=='success') {
        thisobj.find('option[value="-1"]').remove();
        thisobj.add_options(JSON.parse(a).data);
      } else {
        if (!thisobj.find('option[value="-1"]')) {
          thisobj.append('<option />').val(-1);
        }
        thisobj.find('option[value="-1"]').html('An error occurred while loading.  Please refresh this page.');
      }
    }

    NYSS.Analytics.select_set_default_options = function() {
      var thisobj = this;
      thisobj.clear_options();
      thisobj.add_options(thisobj.default_options);
    }

    // create some easy references
    NYSS.Analytics.query_selector = $('#query-choice');
    NYSS.Analytics.builder_fieldname = $('#builder-fieldname');
    var qs = NYSS.Analytics.query_selector;
    var bldrField = NYSS.Analytics.builder_fieldname;

    // function to reload all controls
    NYSS.Analytics.reload_all_controls = function() {
      qs.load_options();
      bldrField.load_options();
    }

    /* ************************************************
     * Bind all general functions to specific controls
     ************************************************ */
    $.each(NYSS.Analytics, function(k,v) {
      mm=k.match(/^select_(.*_options(_.*|$))/);
      if (mm) {
        qs[mm[1]] = v.bind(qs);
        bldrField[mm[1]] = v.bind(bldrField);
      }
    });

    /* ************************************************
     * Configure options for #query-choice selector
     ************************************************ */
    qs.ajax_action = 'saved_queries';
    qs.default_options = [
                          { id:0, name:'Create New Query' },
                          { id:-1, name:'Loading Saved Queries . . .', disabled:1 }
                         ];

    /* ************************************************
     * Configure options for #query-choice selector
     ************************************************ */
    bldrField.ajax_action = 'avail_datapoints';
    bldrField.default_options = [
                                  { id:0, name:'-- Select a Datapoint --' },
                                  { id:-1, name:'Loading . . .', disabled:1 }
                                ];

    /* ************************************************
     * Hooks and Events
     ************************************************ */
    // hook for "show builder" button caption
    $('#builder-controls').on('hide.bs.collapse show.bs.collapse', function (e) {
      var newtext = (e.type=='show') ? 'Hide' : 'Show';
      $('#show-editor').html(newtext+' Editor');
    });

    // hook to reload controls
    $('#reload-all-controls').on('click', function() {
      NYSS.Analytics.reload_all_controls();
    });

    /* ************************************************
     * Page spin-up
     ************************************************ */
    // load all control data
    NYSS.Analytics.reload_all_controls();

  });
})(jQuery);