//== Class definition
var apipath = $('meta[name=apiurl]').attr("content");
var uploadpath = $('meta[name=uploadurl]').attr("content");
var adminpath = $('meta[name=adminurl]').attr("content");
var DatatableRemoteAjaxDemo = function() {
  //== Private functions
var url = document.URL;
//console.log('url',url);
var uparts = url.split('/');

var uid = uparts[8];
console.log(uid);

  // basic demo
  var demo = function() {
    var stype = '';
    var fuid = '';
    if(uid === 'undefined' || uid === undefined || uid == 'undefined' || uid == undefined){

      //$('#userfilter').val(uid);
      //$('#m_form_type').val(3);
    } else{
      console.log('uid',uid);
      stype = 3;
      fuid=uid;
      $('#userfilter').val(uid);
    }
    var datatable = $('.m_datatable').mDatatable({
      // datasource definition
      data: {

        type: 'remote',
        source: {
          read: {
            // sample GET method
            method: 'POST',
            url: apipath+'gettransactionlist',
            params:{
              query:{
                userid:fuid,
                Type:stype
              }
            },
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
          field: 'tdate',
          title: 'Date',
          sortable: 'desc',
          // sortable: 'asc', // default sort
          filterable: true, // disable or enable filtering
          width:70,
          textAlign: 'left',
        },
        {
          field: 'ttime',
          title: 'Time',
          sortable:true,
          // sortable: 'asc', // default sort
          filterable: true, // disable or enable filtering
          width:50,
          textAlign: 'left',
        },
         {
          field: 'type',
          title: 'Type',
          sortable:true,
          filterable: true, // disable or enable filtering
          width:50,
          textAlign: 'center',
          template:function(row){
            if(row.type==1){
              return 'Credit';
            } else if(row.type == 2){
              return 'Debit';
            } else{
              return 'Earnings';
            }
          }
        }, {
          field: 'amount',
          title: 'Money',
          width:50,
          filterable: true, // disable or enable filtering
          textAlign: 'center',
          template:function(row){
            return '$'+row.amount;
          }
        },
        {
          field: 'adearn',
          title: 'Admin earnings',
          width:50,
          sortable:false,
          filterable: true, // disable or enable filtering
          textAlign: 'center',
          template:function(row){
            return '$'+row.adearn;
          }
        },
         {
          field: 'remarks',
          title: 'Remarks',
          width:70,
          filterable: true, // disable or enable filtering
          textAlign: 'center',
        }, {
          field:"touser",
          title:"To",
          sortable:false,
          width:50,
          selector:!1,
          textAlign:"center",
          template:function(row){
            if(row.to_user){
              return row.to_user;
            } else if(row.to_admin){
              return ;
            } else{
              return '-';
            }
          }
        },
        {
          field:"fromuser",
          title:"From",
          sortable:false,
          width:50,
          selector:!1,
          textAlign:"center",
          template:function(row){
            if(row.from_user){
              return row.from_user;
            } else{
              return row.from_admin;
            }
          }
        }],
    });
// /users/edit/'+row.id+'
    var query = datatable.getDataSourceQuery();

    $('#userfilter').on('change',function(){
      var query = datatable.getDataSourceQuery();
      query.userid = $(this).val();
      // shortcode to datatable.setDataSourceParam('query', query);
      datatable.setDataSourceQuery(query);
      datatable.load();
    }).val(typeof query.userid !== 'undefined' ? query.userid : '');

    $('#m_daterangepicker_2').daterangepicker({
        buttonClasses: 'm-btn btn',
        applyClass: 'btn-primary',
        cancelClass: 'btn-secondary'
    }, function(start, end, label) {
        $('#m_daterangepicker_2 .form-control').val( start.format('YYYY-MM-DD') + ' / ' + end.format('YYYY-MM-DD'));

        var query = datatable.getDataSourceQuery();
        query.daterange = $('#daterangesel').val();
        // shortcode to datatable.setDataSourceParam('query', query);
        datatable.setDataSourceQuery(query);
        datatable.load();

    });

    $('.cancelBtn').on('click',function(){
      $('#daterangesel').val('');
      query.daterange = '';
      datatable.setDataSourceQuery(query);
      datatable.load();
    });

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
    $('#userfilter').val(fuid);

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
  var url = apipath+'userstatus';

  $.ajax({
    type: 'post',
    url: url,
    data:{'userids':uid, 'stat':stat},
    success: function(result){
      if(result.error == false){

      }
    }
  });
}
