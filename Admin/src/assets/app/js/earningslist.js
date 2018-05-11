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
            url: apipath+'getgirlearnings',
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
          field: '',
          title: "<input id='selallleads' type='checkbox'>",
          sortable: false,
          // sortable: 'asc', // default sort
          filterable: false, // disable or enable filtering
          width:'auto',
          textAlign: 'center',
          template:function(row){
              return '<input id="checkBox" type="checkbox" name="user_id[]" value="'+row.id+'">';
          }
        },
        {
          field: 'first_name',
          title: 'Receiver Name',
          sortable: false,
          // sortable: 'asc', // default sort
          filterable: true, // disable or enable filtering
          width:100,
          textAlign: 'left',
        },
        {
          field: 'total',
          title: 'Total',
          sortable:false,
          // sortable: 'asc', // default sort
          filterable: true, // disable or enable filtering
          width:70,
          textAlign: 'center',
        },
        {
          field: 'useram',
          title: 'User earning',
          sortable:false,
          // sortable: 'asc', // default sort
          filterable: true, // disable or enable filtering
          width:70,
          textAlign: 'center',
        },
        {
          field: 'remam',
          title: 'Admin earning',
          sortable:false,
          // sortable: 'asc', // default sort
          filterable: true, // disable or enable filtering
          width:70,
          textAlign: 'center',
        },
        {
          field: 'pstat',
          title: 'Status',
          sortable:false,
          // sortable: 'asc', // default sort
          filterable: true, // disable or enable filtering
          width:70,
          textAlign: 'center',
          template:function(row){
            if(row.pstat == 1){
              return 'Unpaid';
            } else{
              return 'Paid';
            }
          }
        },
         {
          field: 'Actions',
          title: 'Actions',
          sortable:false,
          filterable: false, // disable or enable filtering
          width:'200',
          textAlign: 'center',
          template:function(row){
            if(row.pstat == 1){
              return '<a href="'+adminpath+'#/transactions/'+row.id+'" class="m-portlet__nav-link btn m-btn--pill btn-outline-info btn-sm" title="View">\
  							View\
  						</a>\
  						<a href="javascript:void(0)" class="m-portlet__nav-link btn m-btn--pill btn-outline-success btn-sm" id="deluser-'+row.id+'" title="Paid" onclick="deluser('+row.id+')">\
  							Mark as paid\
  						</a>';
            } else{
              return '<a href="'+adminpath+'#/transactions/'+row.id+'" class="m-portlet__nav-link btn m-btn--pill btn-outline-info btn-sm" title="View">\
  							View\
  						</a>';
            }

          }
        }
        ],
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

    $('#delselall').on('click',function(){
      var url = apipath+'transfertowallet';
        var leadids = [];
        $("input:checkbox[name=user_id\\[\\]]:checked").each(function(){
            leadids.push($(this).val());
        });

        //alert(leadids.length);
        if(leadids.length > 0){
            doIt = confirm('Transfer the amount to wallet for selected users?');
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
                                            earningstransfer.init();
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
  var url = apipath+'markpaid';
  var leadids = uid;
  var r = confirm("Sure mark this user as paid?");
  if (r == true) {
    $.ajax({
      type: 'post',
      url: url,
      data:{'userids':leadids},
      success: function(result){
          //ToastrDemo.init();
          earningstransfer.init();
          //$("#deluser-"+uid).closest('td').siblings().last().find('span').text('0');
          //DatatableRemoteAjaxDemo.init();
          datatable.load();
      }
    });
  } else {
    //console.log(uid);

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
