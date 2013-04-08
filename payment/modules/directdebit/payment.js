var bName = false;

$(document).ready(function(){
   $('input[name=code]').live('keyup', function(evt) {
	//var code = evt.keyCode || evt.which;
	  var bankCode = $(this),
         bankDisplay = $('#bankName'),
         warningDiv = $('#bankNotFound');
      
      if (bankCode.length > 0 && bankCode.val().length == 8)
      {
         var bankName = "";
         var bFound = false;
         $.ajax({ type: 'POST',
                  url: checkPath + 'ajax/valid.xml',
                  async: false,
                  cache: false,
                  dataType: 'xml',
                  success: function(xml) {
                      var result = $(xml).find('banks bank[code=' + bankCode.val() + ']');
                      if (result.length > 0)
                      {                         
                        bankName = result.text();
                        bFound = true;
                      }
                  }
               });
	  }
      else
      {
         bFound = false;
      }
      
      if (bFound)
      {
         if (warningDiv.length > 0)
         {
            warningDiv.slideUp('fast', function(){ $(this).remove(); });
         }
            
         if (bankDisplay.length > 0)
         {
            if (bankDisplay.is(':visible'))
            {
               bankDisplay.slideUp('fast', function(){
                  $(this).text(bankName);
                  $(this).slideDown('fast');
               });
            }
            else
            {
               bankDisplay.text(bankName);
               bankDisplay.slideDown('fast');
            }
         }
      }
      else
      {
         if (warningDiv.length == 0)
         {
            var warning = $(document.createElement('div')).addClass('warning warn').attr('id', 'bankNotFound').html('Could not find the bank you provided. By clicking "check out" you confirm that the code you have provided is correct').hide();
            
            warning.insertAfter(bankDisplay).slideDown('fast');
         }
         
         bankDisplay.slideUp('fast', function(){$(this).empty();});
      }
	});
});