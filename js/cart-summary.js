if (typeof(window.updateCartSummary) != 'undefined')
{
    window.updateCartSummary = function(json)
    {
        var i;
        var nbrProducts = 0;
    
        if (typeof json === 'undefined')
            return;
    
        for (i=0;i<json.products.length;i++)
        {
            // if reduction, we need to show it in the cart by showing the initial price above the current one
            var reduction = json.products[i].reduction_applies;
            var initial_price_text = '';
            initial_price = '';
            if (typeof(json.products[i].price_without_quantity_discount) !== 'undefined')
                initial_price = formatCurrency(json.products[i].price_without_quantity_discount, currencyFormat, currencySign, currencyBlank);
            var current_price = '';
            if (priceDisplayMethod !== 0)
                current_price = formatCurrency(json.products[i].price, currencyFormat, currencySign, currencyBlank);
            else
                current_price = formatCurrency(json.products[i].price_wt, currencyFormat, currencySign, currencyBlank);
            if (reduction && typeof(initial_price) !== 'undefined')
            {
                if (initial_price !== '' && initial_price > current_price)
                    initial_price_text = '<span style="text-decoration:line-through;">'+initial_price+'</span><br />';
            }
    
            key_for_blockcart = json.products[i].id_product+'_'+json.products[i].id_product_attribute;
            if (json.products[i].id_product_attribute == 0)
                key_for_blockcart = json.products[i].id_product;
    
            $('#cart_block_product_'+key_for_blockcart+' span.quantity').html(json.products[i].cart_quantity);
    
            if (priceDisplayMethod !== 0)
            {
                $('#cart_block_product_'+key_for_blockcart+' span.price').html(formatCurrency(json.products[i].total, currencyFormat, currencySign, currencyBlank));
                $('#product_price_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute+'_'+json.products[i].id_address_delivery).html(initial_price_text+current_price);
                $('#total_product_price_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute+'_'+json.products[i].id_address_delivery).html(formatCurrency(json.products[i].total, currencyFormat, currencySign, currencyBlank));
            }
            else
            {
                $('#cart_block_product_'+key_for_blockcart+' span.price').html(formatCurrency(json.products[i].total_wt, currencyFormat, currencySign, currencyBlank));
                $('#product_price_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute+'_'+json.products[i].id_address_delivery).html(initial_price_text+current_price);
                $('#total_product_price_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute+'_'+json.products[i].id_address_delivery).html(formatCurrency(json.products[i].total_wt, currencyFormat, currencySign, currencyBlank));
            }
    
            nbrProducts += parseInt(json.products[i].cart_quantity);
    
            if(json.products[i].id_customization == null)
            {
                $('input[name=quantity_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute+'_0_'+json.products[i].id_address_delivery+']').val(json.products[i].cart_quantity);
                $('input[name=quantity_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute+'_0_'+json.products[i].id_address_delivery+'_hidden]').val(json.products[i].cart_quantity);
            }
            else
            {
                //$('input[name=quantity_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute+'_'+json.products[i].id_customization+'_'+json.products[i].id_address_delivery+']')
                //	.val(json.products[i].cart_quantity);
                $('#cart_quantity_custom_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute+'_'+json.products[i].id_address_delivery)
                    .html(json.products[i].cart_quantity);
            }
    
            // Show / hide quantity button if minimal quantity
            if (parseInt(json.products[i].minimal_quantity) === parseInt(json.products[i].cart_quantity) && json.products[i].minimal_quantity != 1)
                $('#cart_quantity_down_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute+Number(json.products[i].id_customization)+'_'+json.products[i].id_address_delivery).fadeTo('slow',0.3);
            else
                $('#cart_quantity_down_'+json.products[i].id_product+'_'+json.products[i].id_product_attribute+Number(json.products[i].id_customization)+'_'+json.products[i].id_address_delivery).fadeTo('slow',1);
    
        }
    
        // Update discounts
        if (json.discounts.length == 0)
        {
            $('.cart_discount').each(function(){$(this).remove()});
            $('.cart_total_voucher').remove();
        }
        else
        {
            if (priceDisplayMethod != 0)
                $('#total_discount').html(formatCurrency(json.total_discounts_tax_exc, currencyFormat, currencySign, currencyBlank));
            else
                $('#total_discount').html(formatCurrency(json.total_discounts, currencyFormat, currencySign, currencyBlank));
    
            $('.cart_discount').each(function(){
                var idElmt = $(this).attr('id').replace('cart_discount_','');
                var toDelete = true;
    
                for (i=0;i<json.discounts.length;i++)
                {
                    if (json.discounts[i].id_discount == idElmt)
                    {
                        if (json.discounts[i].value_real != '!')
                        {
                            if (priceDisplayMethod != 0)
                                $('#cart_discount_' + idElmt + ' td.cart_discount_price span.price-discount').html(formatCurrency(json.discounts[i].value_tax_exc * -1, currencyFormat, currencySign, currencyBlank));
                            else
                                $('#cart_discount_' + idElmt + ' td.cart_discount_price span.price-discount').html(formatCurrency(json.discounts[i].value_real * -1, currencyFormat, currencySign, currencyBlank));
    
                        }
                        toDelete = false;
                    }
                }
                if (toDelete)
                    $('#cart_discount_' + idElmt + ', #cart_total_voucher').fadeTo('fast', 0, function(){ $(this).remove(); });
            });
        }
    
        // Block cart
        if (priceDisplayMethod != 0)
        {
            $('#cart_block_shipping_cost').html(formatCurrency(json.total_shipping_tax_exc, currencyFormat, currencySign, currencyBlank));
            $('#cart_block_wrapping_cost').html(formatCurrency(json.total_wrapping_tax_exc, currencyFormat, currencySign, currencyBlank));
            $('#cart_block_total').html(formatCurrency(json.total_price_without_tax, currencyFormat, currencySign, currencyBlank));
        } else {
            $('#cart_block_shipping_cost').html(formatCurrency(json.total_shipping, currencyFormat, currencySign, currencyBlank));
            $('#cart_block_wrapping_cost').html(formatCurrency(json.total_wrapping, currencyFormat, currencySign, currencyBlank));
            $('#cart_block_total').html(formatCurrency(json.total_price, currencyFormat, currencySign, currencyBlank));
        }
    
        $('#cart_block_tax_cost').html(formatCurrency(json.total_tax, currencyFormat, currencySign, currencyBlank));
        $('.ajax_cart_quantity').html(nbrProducts);
    
        // Cart summary
        $('#summary_products_quantity').html(nbrProducts+' '+(nbrProducts > 1 ? txtProducts : txtProduct));
        if (priceDisplayMethod != 0)
            $('#total_product').html(formatCurrency(json.total_products, currencyFormat, currencySign, currencyBlank));
        else
            $('#total_product').html(formatCurrency(json.total_products_wt, currencyFormat, currencySign, currencyBlank));
        $('#total_price').html(formatCurrency(json.total_price, currencyFormat, currencySign, currencyBlank));
        $('#total_price_without_tax').html(formatCurrency(json.total_price_without_tax, currencyFormat, currencySign, currencyBlank));
        $('#total_tax').html(formatCurrency(json.total_tax, currencyFormat, currencySign, currencyBlank));
    
        if (json.total_shipping <= 0)
        {
            $('.cart_total_delivery').fadeOut();
        }
        else
        {
            $('tr.cart_total_delivery').find('span.shipping_label').fadeIn('fast');
            
            $('.cart_total_delivery').fadeIn();
            if (priceDisplayMethod != 0)
            {
                $('#total_shipping').html(formatCurrency(json.total_shipping_tax_exc, currencyFormat, currencySign, currencyBlank));
            }
            else
            {
                $('#total_shipping').html(formatCurrency(json.total_shipping, currencyFormat, currencySign, currencyBlank));
            }
        }
    
        if (json.free_ship > 0 && !json.is_virtual_cart)
        {
            $('.cart_free_shipping').fadeIn();
            $('#free_shipping').html(formatCurrency(json.free_ship, currencyFormat, currencySign, currencyBlank));
        }
        else
            $('.cart_free_shipping').hide();
    
        if (json.total_wrapping > 0)
        {
            $('#total_wrapping').html(formatCurrency(json.total_wrapping, currencyFormat, currencySign, currencyBlank));
            $('#total_wrapping').parent().show();
        }
        else
        {
            $('#total_wrapping').html(formatCurrency(json.total_wrapping, currencyFormat, currencySign, currencyBlank));
            $('#total_wrapping').parent().hide();
        }
        
        // GERMANEXT CHANGE
        if (json.total_payment == 0)
        {
           $('.cart_total_payment').fadeOut();
        }
        else 
        {
            $('.cart_total_payment').fadeIn();
            
            if (priceDisplayMethod == 0)
            {
                $('#total_payment').html(formatCurrency(json.total_payment, currencyFormat, currencySign, currencyBlank));
            }
            else
            {
                $('#total_payment').html(formatCurrency(json.total_payment_tax_exc, currencyFormat, currencySign, currencyBlank));   
            }
            
            if (json.payment_cost_name)
            {
                $('#payment_cost_name').html(json.payment_cost_name);   
            }
            else
            {
                $('#payment_cost_name').html('');   
            }
        }
        // OEF GERMANEXT CHANGE
        
        if (window.ajaxCart !== undefined)
            ajaxCart.refresh();
    };
}