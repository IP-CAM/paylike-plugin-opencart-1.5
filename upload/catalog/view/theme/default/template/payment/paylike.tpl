<!-- <div class="buttons">
  <div class="pull-right">
    <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="btn btn-primary" />
  </div>
</div> -->
<div id="content">
    <div style="float: left" id="paylike-payment-widget"></div>
    <div style="clear: both;"></div>
    <div class="buttons" style="margin-top: 15px">
      <a class="button right" id="button-confirm" style="width: 9%;"><span><?php echo $button_confirm ?></span></a>
    </div>
    <input type="hidden" name="payment_method" value="" />
</div>

<script type="text/javascript" src="https://sdk.paylike.io/3.js"></script>
<script type="text/javascript"><!--

$('body').on('click', '#button-confirm', function() {
    var paylike = Paylike('<?php echo $paylike_public_key; ?>');
    paylike.popup({
        title: "<?php echo $popup_title; ?>",
        description: "<?php echo $popup_description; ?>",
        currency: '<?php echo $currency_code; ?>',
        amount: <?php echo $amount; ?>,
        custom: {
            orderId: '<?php echo $order_id; ?>',
            products:  <?php echo $products; ?>,
            customer:{
                name: '<?php echo $name; ?>',
                email: '<?php echo $email; ?>',
                telephone: '<?php echo $telephone; ?>',
                address: '<?php echo $address; ?>',
                customerIp: '<?php echo $ip; ?>'
            },
            platform:{
                name: 'opencart',
                version: '<?php echo VERSION; ?>',
            },
            ecommerce: {
                name: 'opencart',
                version: '<?php echo VERSION; ?>',
            },
            version: '1.0.2'
        },
        locale: '<?php echo $this->language->get("code"); ?>'

    }, function(err, res) {
        if (err)
            return console.log(err);

        $.ajax({
            url: 'index.php?route=payment/paylike/processPayment',
            type: 'post',
            data: {
                'trans_ref': res.transaction.id
            },
            dataType: 'json',
            cache: false,
            beforeSend: function() {
                $('#button-confirm').button('loading');
            },
            complete: function() {
                $('#button-confirm').button('reset');
            },
            success: function(json) {
                console.log(json);
                if( json.hasOwnProperty('error') ) {
                    var html = '<div class="warning">Warning: ' + json.error + '</div>';
                    $('div#confirm .checkout-content').prepend(html);
                }

                if( json.hasOwnProperty('redirect') ) {
                    location.href = json.redirect;
                }
            }
        });
    });

});
//--></script>
