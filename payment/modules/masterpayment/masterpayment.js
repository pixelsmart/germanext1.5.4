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

function setHrefQueryVar(variable, newValue, hrefAttr)
{
	var searchQuery = hrefAttr.split('?'),
        hostPart = searchQuery[0];
        
    if (searchQuery.length > 1)
        var query = searchQuery[1];
	
	if (typeof(query) == 'undefined')
		return hrefAttr;

	var vars  = query.split('&'),
        tmp   = {},
        found = false;

	for (var i = 0; i < vars.length; i++)
	{ 
		var pair = vars[i].split('=');
		
		if (pair[0] == variable)
        {
            found = true;
            pair[1] = newValue;
        }
            
        tmp[pair[0]] = pair[1];
	}
    
    if ( ! found)
        tmp[variable] = newValue;
    
    if (tmp.length == 0)
        return hrefAttr;
    
    var newHref = hostPart + '?';

    for (var i in tmp)
        newHref+= i + '=' + tmp[i] + '&';
        
    newHref = newHref.substr(0, (newHref.length - 1));
	
	return newHref;
}

function setPaymentOption() {
	var selectedMethod = $('input[name=payment_module]:checked');
	
	if (selectedMethod.length > 0) {
		var content = selectedMethod.next('label'),
		    paymentOptionInput = content.find('.masterpaymentOptions'),
		    paymentOption;
		    
		if (paymentOptionInput.length > 0) {
			paymentOption = paymentOptionInput.val();
			
			var orderSubmit = $('a#button_order');
			
			$('a#button_order').attr('href', setHrefQueryVar('payment_method', paymentOption, orderSubmit.attr('href')));
		}	
	}
}

$(document).ready(function(){
	setPaymentOption();
	
	$('input[name=payment_module]').click(function(){
	    setPaymentOption();
	});
});
