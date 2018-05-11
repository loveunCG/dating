//== Class definition
var apipath = $('meta[name=apiurl]').attr("content");
var uploadpath = $('meta[name=uploadurl]').attr("content");
var adminpath = $('meta[name=adminurl]').attr("content");
var datatable;
var DatatableRemoteAjaxDemo = function() {
  //== Private functions

  // basic demo
  var demo = function() {

    datatable = $('.m_datatable').mDatatable({
      // datasource definition
      data: {
        type: 'remote',
        source: {
          read: {
            // sample GET method
            method: 'POST',
            url: apipath+'getsubadmins',
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
      sortable: false,

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
          field: 'name',
          title: 'Name',
          sortable: false,
          // sortable: 'asc', // default sort
          filterable: true, // disable or enable filtering
          width:100,
          textAlign: 'center',
        },
        {
          field: 'email',
          title: 'Email',
          // sortable: 'asc', // default sort
          filterable: true, // disable or enable filtering
          width:150,
          textAlign: 'center',
        },
         {
          field: 'Actions',

          title: 'Actions',
          sortable: false,
          overflow: 'visible',
          template: function(row) {

            return '\
            <a href="'+adminpath+'#/sub-admins/edit/'+row.id+'" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="Edit details">\
							<i class="la la-edit"></i>\
						</a>\
						<a href="javascript:void(0)" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" id="deluser-'+row.id+'" title="Delete" onclick="deluser('+row.id+')">\
							<i class="la la-trash"></i>\
						</a>\
					';
          // <select id="pstat-'+row.id+'" onchange="adeactuser('+row.id+',this.value)" class="form-control m-input"> \
          // <option value="1" '+ftval+'>Active</option> \
          // <option value="2" '+sval+'>Inactive</option> \
          // <select>\
          },
        }],
    });
// /users/edit/'+row.id+'
    var query = datatable.getDataSourceQuery();

    datatable.sort('id');

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
      var url = apipath+'deletesubadmin';
        var leadids = [];
        $("input:checkbox[name=user_id\\[\\]]:checked").each(function(){
            leadids.push($(this).val());
        });

        //alert(leadids.length);
        if(leadids.length > 0){
            doIt = confirm('Are you sure you want to delete selected sub admins?');
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
                                            statusupdate.init('s',2);
                                            var query = datatable.getDataSourceQuery();

                                            // shortcode to datatable.setDataSourceParam('query', query);
                                            datatable.setDataSourceQuery(query);
                                            datatable.load();

                                         }
        });
        }
    } else{
        alert('Please select atleast one sub admin');
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
  var url = apipath+'deletesubadmin';
  var leadids = [];
  var r = confirm("Are you sure you want to delete this sub admin?");
  if (r == true) {
    leadids.push(uid);
    $.ajax({
      type: 'post',
      url: url,
      data:{'userids':leadids},
      success: function(result){
        leadids = [];
        var query = datatable.getDataSourceQuery();

        // shortcode to datatable.setDataSourceParam('query', query);
        datatable.setDataSourceQuery(query);
        datatable.load();
        statusupdate.init('s',2);
      }
    });
  } else {
    statusupdate.init('f',1);
  }
}

function adeactuser(uid, stat){
  var url = apipath+'userstatus';
  var astat = 2;
  if($('#adminstat-'+uid).prop("checked")){
    astat = 1;
  }

  $.ajax({
    type: 'post',
    url: url,
    data:{'userids':uid, 'stat':astat},
    success: function(result){
      if(result.error == false){
        statusupdate.init('s',0);
      } else{
        statusupdate.init('f',1);
      }
    }
  });
}
