function updatePaymentModuleFee()
{
	$('div.paymentModuleDataPrompt').slideUp('fast', function(){
		$(this).remove();
	});
	
	var hasOwnTemplate = false,
		collection     = $('input[name=payment_module]'),
		idModule       = -1;
    
    if (collection.length <= 0)
        return ;
    
    //alert ('ass');
    var selectedModule = collection.filter(':checked');
    
    if (selectedModule.length > 0)
        idModule = selectedModule.val();
    else
    {
        selectedModule = collection.eq(0);
        
        selectedModule.attr('checked', true);
        
        idModule = selectedModule.val();
    }

    if (($('#cgv:checked').length != 0))
    {
        setOrderButtonState(false);
    }
		
	$('#opc_payment_methods-overlay').fadeIn('slow');
      
        $.ajax({
            type: 'POST',
            url: orderUrl,
            async: true,
            cache: false,
            dataType : "json",
            data: 'ajax=true&method=updatePaymentModule&payment_module=' + idModule + '&token=' + static_token,
            success: function(json) {
                if (false) //json.hasError)
                {
                    var errors = '';
                    
                    for(error in json.errors)
                        //IE6 bug fix
                        if(error != 'indexOf')
                            errors += json.errors[error] + "\n";
                }
                else
                {
					if ('dataPrompt' in json)
					{
						var wrapper = $(document.createElement('div')).addClass('paymentModuleDataPrompt').hide();
							wrapper.append(json.dataPrompt);
							
						selectedModule.parent('.radio').append(wrapper.slideDown('fast'));
						hasOwnTemplate = true;
						
						$('#opc_payment_methods-overlay').fadeOut('slow');
					}
					
                    //alert ('ass');
                    $('#button_order').attr('href', json.BUTTON_ORDER_HREF);
                    updateCartSummary(json.summary);
					
					if ( ! hasOwnTemplate)
						$('#opc_payment_methods-overlay').fadeOut('slow');
                }
            }
      });
}

function getHrefQueryVar(variable, hrefAttr)
{
	var searchQuery = stristr(hrefAttr, '?');
	
	if ( ! searchQuery)
		return false;

	var query = searchQuery.substring(1),
		vars  = query.split('&');

	for (var i = 0; i < vars.length; i++)
	{ 
		var pair = vars[i].split('=');
		
		if (pair[0] == variable)
			return pair[1]; 
	}
	
	return false;
}

function stristr(haystack, needle)
{
    var pos = 0;
 
    haystack += '';
    pos = haystack.toLowerCase().indexOf((needle + '').toLowerCase());
	
    if (pos == -1)
        return false;
    else
        return haystack.slice(pos);
}

$(function(){
    updatePaymentModuleFee();
    
    $('input[name=payment_module]').live('click', function() {
        updatePaymentModuleFee();
    });
});