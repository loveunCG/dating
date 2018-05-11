//== Class definition
var apipath = $('meta[name=apiurl]').attr("content");
var uploadpath = $('meta[name=uploadurl]').attr("content");
var adminpath = $('meta[name=adminurl]').attr("content");
var DatatableRemoteAjaxDemo = function() {
  //== Private functions

  // basic demo
  var demo = function() {

    var datatable = $('.m_datatable').mDatatable({
      // datasource definition
      data: {
        type: 'remote',
        source: {
          read: {
            // sample GET method
            method: 'POST',
            url: apipath+'getinactivelist',
            map: function(raw) {
              // sample data mapping
              var dataSet = raw;
              if (typeof raw.data !== 'undefined') {
                dataSet = raw.data;
              }
              //console.log(dataSet);
              return dataSet;
            },
          },
        },
        pageSize: 10,
        saveState: {
          cookie: false,
          webstorage: false,
        },
        serverPaging: true,
        serverFiltering: true,
        serverSorting: true,
      },

      // layout definition
      layout: {
        theme: 'default', // datatable theme
        class: '', // custom wrapper class
        scroll: false, // enable/disable datatable scroll both horizontal and vertical when needed.
        footer: false // display/hide footer
      },

      // column sorting
      sortable: true,

      pagination: true,

      toolbar: {
        // toolbar items
        items: {
          // pagination
          pagination: {
            // page size select
            pageSizeSelect: [10, 20, 30, 50, 100],
          },
        },
      },

      search: {
        input: $('#generalSearch'),
      },

      // columns definition
      columns: [
        {
          field:'delaction',
          title:"<input id='selallleads' type='checkbox'>",
          sortable:!1,
          width:50,
          selector:!1,
          textAlign:"center",
          template:function(row){
              return '<input id="checkBox" type="checkbox" name="user_id[]" value="'+row.id+'">';
          }
        },
        {
          field: 'username',
          title: 'Name',
          sortable: true,
          // sortable: 'asc', // default sort
          filterable: true, // disable or enable filtering
          width:100,
          textAlign: 'center',
        },
        {
          field: 'lastlogin',
          title: 'Last loggedin',
          sortable:'desc',
          width:100,
          filterable: true, // disable or enable filtering
          textAlign: 'center',
        },
         {
          field: 'Actions',
          width:100,
          title: 'Actions',
          sortable: false,
          overflow: 'visible',
          template: function(row) {
            var chval = '';
            var titlev = '';
            if(row.admin_status == 1){
              chval = 'checked';
              titlev = "Deactivate";
            } else if(row.admin_status == 2){
              titlev = "Activate";
            }
            return '\
            <span class="m-switch m-switch--sm m-switch--icon">\
												<label>\
						                <input type="checkbox" '+chval+' name="adminstat" id="adminstat-'+row.id+'" onchange="adeactuser('+row.id+',this.value)" data-toggle="m-tooltip" title="'+titlev+'" data-original-title="Activate/Deactivate user">\
						                <span></span>\
						            </label>\
						        </span>\
            \
					';
          },
        }],
    });
// /users/edit/'+row.id+'
    var query = datatable.getDataSourceQuery();

    $('#m_form_status').on('change', function() {
      // shortcode to datatable.getDataSourceParam('query');
      var query = datatable.getDataSourceQuery();
      query.Status = $(this).val().toLowerCase();
      // shortcode to datatable.setDataSourceParam('query', query);
      datatable.setDataSourceQuery(query);
      datatable.load();
    }).val(typeof query.Status !== 'undefined' ? query.Status : '');

    $('#m_form_type').on('change', function() {
      // shortcode to datatable.getDataSourceParam('query');
      var query = datatable.getDataSourceQuery();
      query.Type = $(this).val().toLowerCase();
      // shortcode to datatable.setDataSourceParam('query', query);
      datatable.setDataSourceQuery(query);
      datatable.load();
    }).val(typeof query.Type !== 'undefined' ? query.Type : '');

    $('#m_form_status, #m_form_type').selectpicker();

    $('#delselall').on('click',function(){
      var url = apipath+'inactiveact';
        var leadids = [];
        $("input:checkbox[name=user_id\\[\\]]:checked").each(function(){
            leadids.push($(this).val());
        });

        //alert(leadids.length);
        if(leadids.length > 0){
            doIt = confirm('Sure you want to deactivate these user accounts?');
      if (doIt) {

        $.ajax({
                                         type:'post',
                                         url:url,
                                         data:{
                                             'userids':leadids
                                         },
                                         success:function(data){
                                           $('#selallleads').prop("checked",false);
                                           $('#selallleads').parent().removeClass('checked');
                                            // alert(data);
                                            inactivetoast.init('s',0);
                                            var query = datatable.getDataSourceQuery();

                                            // shortcode to datatable.setDataSourceParam('query', query);
                                            datatable.setDataSourceQuery(query);
                                            datatable.load();

                                         }
        });
        }
    } else{
        alert('Please select atleast one user');
    }
    });

  };


  function delallsel(){

  }

  return {
    // public functions
    init: function() {
      demo();
    },
  };
}();

jQuery(document).ready(function() {
  DatatableRemoteAjaxDemo.init();

  $("#selallleads").click(function () {
      //$('#deleteallsel').toggle();
      //alert('sel click');
      //var checkBoxes = $("input[name=lead_id\\[\\]]");
      if(document.getElementById('selallleads').checked){
//                checkBoxes.prop("checked", !checkBoxes.prop("checked"));
//                checkBoxes.parent().addClass('checked');
          $("input:checkbox[name=user_id\\[\\]]").each(function(){
              //alert('check');
              if(!$(this).prop("checked")){
                  $(this).prop("checked",true);
                  $(this).parent().addClass('checked');
              }
          });
      } else{
          $("input:checkbox[name=user_id\\[\\]]").each(function(){
              if($(this).prop("checked")){
                  $(this).prop("checked",false);
                  $(this).parent().removeClass('checked');
              }
          });
      }
  });
});


function deluser(uid){
  var url = apipath+'deleteuser';
  var leadids = [];
  var r = confirm("Are you sure you want to delete this user?");
  if (r == true) {
    leadids.push(uid);
    $.ajax({
      type: 'post',
      url: url,
      data:{'userids':leadids},
      success: function(result){
          $("#deluser-"+uid).parent().parent().parent().remove();
          ToastrDemo.init();
      }
    });
  } else {

  }
}

function adeactuser(uid, stat){
  var url = apipath+'userstatus';
  var astat = 2;
  if($('#adminstat-'+uid).prop("checked")){
    astat = 1;
  }
  console.log(astat);
  $.ajax({
    type: 'post',
    url: url,
    data:{'userids':uid, 'stat':astat},
    success: function(result){
      if(result.error == false){
        inactivetoast.init('s',0);
      } else{
        inactivetoast.init('f',1);
      }
    },
    error:function(){
      inactivetoast.init('f',1);
    }
  });
}
