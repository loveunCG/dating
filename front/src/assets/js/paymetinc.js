  setTimeout(function() {
  $.getScript('https://www.paypalobjects.com/api/checkout.js', function() {
    console.log($("#paypal-button").length);
    //script is loaded and executed put your dependent JS here
// <script src="https://www.paypalobjects.com/api/checkout.js"></script>
paypal.Button.render({

        env: 'sandbox', // 'production' Or 'sandbox'

        client: {
            //sandbox:    'AFcWxV21C7fd0v3bYYYRCpSSRl31Ag.nPwEUMRibQeARmWG5vJ-zBGs.',
            sandbox:'AXHc3Ragj01Dqr4H1ceccnEvYEyegmBnjWVi4cXZ3bzr4NMLHewZ3LvKxyoThlmqr_dpMLapMZ4oV6ox',
            production: 'AcZNI2WARVBAgCfKl7xrEh411E5o3UpYvFgP-gLTxDTkHi-bYiaQkRwcT0Zo7-XUkHXUSws0y2SP-gNZ'
            //production: 'APP-80W284485P519543T'
        },

        commit: true, // Show a 'Pay Now' button

        payment: function(data, actions) {

            return actions.payment.create({
                payment: {
                    transactions: [
                        {
                            amount: { total: $('#finaltpay').val(), currency: 'AUD' }
                        }
                    ]
                }
            });

        },

        onAuthorize: function(data, actions) {
          //console.log(data);
            return actions.payment.execute().then(function(payment) {
                console.log('paydata');
                console.log(payment);
                var apipath = 'http://54.218.127.55/projects/Dating/api/v1/';
                var url = apipath+'paymentupdate';
                var hval = 0;
                if($("#addhighlightpro").prop('checked') == true){
                    hval = 1;
                }
                $.ajax({
                  type:'post',
                  url:url,
                  data:{paydata:payment, userid:$('#userid').val(), bonus:$('#bonus').val(), highlight:hval },
                  success: function(result){
                    $('#paymenterror').html('');
                    $('#paymentsuccess').html('');
                    if(result.error){
                        $('#paymenterror').html(result.message);
                    } else{
                        $('#paymentsuccess').html(result.message);
                        $('#wallamount').html(result.wallet);
                        location.reload();
                    }

                  },
                  error:function(){
                    $('#paymentsuccess').html('');
                    $('#paymenterror').html('Payment failed, please check details and try again');
                  }
                })
                // The payment is complete!
                // You can now show a confirmation message to the customer
            });
        }

    }, '#paypal-button');
    });
  }, 500);
