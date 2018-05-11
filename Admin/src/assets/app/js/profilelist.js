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
            url: apipath+'getgirlprpofiles',
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
          field: 'username',
          title: 'Name',
          sortable: true,
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
          field: 'phone',
          title: 'Phone',
          filterable: true, // disable or enable filtering
          width:100,
          textAlign: 'center',
        }, {
          field: 'gender',
          title: 'Gender',
          width:50,
          filterable: true, // disable or enable filtering
          textAlign: 'center',
        }, {
          field: 'age',
          title: 'Age',
          width:70,
          filterable: true, // disable or enable filtering
          textAlign: 'center',
        }, {
          field:"profile_pic",
          title:"Profile image",
          sortable:!1,
          width:50,
          selector:!1,
          textAlign:"center",
          template:function(row){
            if(row.profile_pic!=''){
              return '<img src="'+uploadpath+'profile/' + row.profile_pic.image + '" style="width:50px;height:50px">';
            } else{
              return '';
            }
          }
        },
         {
          field: 'Actions',

          title: 'Actions',
          sortable: false,
          overflow: 'visible',
          template: function(row) {
            var dropup = (row.getDatatable().getPageSize() - row.getIndex()) <= 4 ? 'dropup' : '';
            var fval = '';
            var sval = '';
            var tval = '';
            if(row.status == 2){
              fval = 'selected';
            } else if(row.status == 3){
              sval = 'selected';
            } else if(row.status == 4){
              tval = 'selected';
            }

            return '\
            <select id="pstat-'+row.id+'" onchange="adeactuser('+row.id+',this.value)" class="form-control m-input"> \
            <option value="2" '+fval+'>Pending</option> \
            <option value="3" '+sval+'>Approve</option> \
            <option value="4" '+tval+'>Reject</option> \
            <select>\
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
      var url = apipath+'deleteuser';
        var leadids = [];
        $("input:checkbox[name=user_id\\[\\]]:checked").each(function(){
            leadids.push($(this).val());
        });

        //alert(leadids.length);
        if(leadids.length > 0){
            doIt = confirm('Are you sure you want to delete selected users?');
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
                                            ToastrDemo.init();
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
  var url = apipath+'profilestat';

  $.ajax({
    type: 'post',
    url: url,
    data:{'userids':uid, 'stat':stat},
    success: function(result){
      if(result.error == false){
        statusupdate.init('s',0);
      } else{
        statusupdate.init('f',1);
      }
    },
    error:function(){
      statusupdate.init('f',1);
    }
  });
}
