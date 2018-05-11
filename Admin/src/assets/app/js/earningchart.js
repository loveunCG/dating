//== Class definition
var apipath = $('meta[name=apiurl]').attr("content");
var uploadpath = $('meta[name=uploadurl]').attr("content");
var adminpath = $('meta[name=adminurl]').attr("content");
var FlotchartsDemo = function(year) {
    //== Private functions
    var demo6 = function(year) {
      var ticks = [[1, "January"], [2, "Fabruary"], [3, "March"],[4, "April"], [5, "May"], [6, 'June'], [7, 'July'], [8, 'August'], [9, 'September'], [10, 'October'], [11,'November'], [12,'December']];
      var options = {
          series: {
              bars: {
                  show: true
              }
          },
          bars: {
              barWidth: 0.8,
              lineWidth: 0, // in pixels
              shadowSize: 0,
              align: 'left'
          },
          xaxis: {
             axisLabel: "Months",
             axisLabelUseCanvas: true,
             axisLabelFontSizePixels: 12,
             axisLabelFontFamily: 'Verdana, Arial',
             axisLabelPadding: 10,
             ticks: ticks
         },
         yaxis: {
              axisLabel: "Earnings(amount)",
              axisLabelUseCanvas: true,
              axisLabelFontSizePixels: 12,
              axisLabelFontFamily: 'Verdana, Arial',
              axisLabelPadding: 10,
          },
          grid: {
              tickColor: "#eee",
              borderColor: "#eee",
              borderWidth: 1
          }
      };
        // bar chart:
        var url = apipath+'getearnings';
        var data = [];
        var ticks = [[1, "January"], [2, "Fabruary"], [3, "March"],[4, "April"], [5, "May"], [6, 'June'], [7, 'July'], [8, 'August'], [9, 'September'], [10, 'October'], [11,'November'], [12,'December']];
        $.ajax({
          type: 'post',
          url: url,
          data:{'year':year},
          success: function(result){
            if(!result.error){
              var resultdata = result.data;

              $.each(resultdata, function(k, v) {
                  //display the key and value pair
                  data.push([parseInt(k),v]);
              });
            }

            $.plot($("#m_flotcharts_6"), [{
                data: data,
                lines: {
                    lineWidth: 1,
                },
                shadowSize: 0
            }], options);
          }
        });

        // function GenerateSeries(syear) {
        //   var fdata = [];
        //     //fdata = [[1, 20], [2, 10],[3,0],[4,0],[5,0],[6,0],[7,0],[8,0],[9,0],[10,0],[11,0],[12,0]];
        //     console.log(fdata);
        //     return fdata;
        //
        // }
    }


    return {
        // public functions
        init: function(year) {
            // default charts
            demo6(year);
        }
    };
}();

jQuery(document).ready(function() {
  var sely = $('#selyear').val();
  FlotchartsDemo.init(sely);

});
function getdata(){
  var sely = $('#selyear').val();
  FlotchartsDemo.init(sely);
}
