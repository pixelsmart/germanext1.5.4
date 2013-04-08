$(document).ready(function(){
	function delegate(type, delegate, action)
	{ 
		return $(document).bind(type, function(evt)
		{
			var target = $(evt.target);
			
			if(target.is(delegate))
			{
				return action.apply(target, arguments)
			}
		});
	}

    function setNewName(el, fieldsRegexp, fieldIncrement)
    {
        var currName = el.attr('name');
        
        if (currName && currName.length > 0)
        {
            var re = new RegExp(fieldsRegexp);
            var result = currName.match(re);

            var newName = '';
            for (i = 1; i < result.length; i++)
            {
                newName+= (i == 1 ? result[i] : '[' + ((i == fieldIncrement) ? parseInt(parseInt(result[i]) + 1) : result[i]) + ']');
            }
            
            el.attr('name', newName);
        }
    }
    
    
    function replaceElementsInRow(row, hint, fieldsRegexp, fieldIncrement)
    {
        if ( ! hint)
            return false;
        
        var collection = row.find('*[name^=' + hint + ']');
        
        if (collection.length > 0)
        {
            collection.each(function(){
                setNewName($(this), fieldsRegexp, fieldIncrement);
                $(this).val('');
            });
        }
        
        return row;
    }
    
    
    function createNewRow(row, hint, fieldsRegexp, fieldIncrement)
    {
        var newRow = row.clone();
        
        row.removeClass('rowToAdd');

        if (row.hasClass('odd_row'))
		{
            newRow.removeClass('odd_row').addClass('even_row');
		}
        else
		{
            newRow.removeClass('even_row').addClass('odd_row');
		}
        
        newRow = replaceElementsInRow(newRow, hint, fieldsRegexp, fieldIncrement);
        
        return newRow;
    }
    
    $('.duplicatedFields').find('a.addField').click(function(evt){
        evt.preventDefault();
        
        var parent = $(this).parents('.duplicatedFields'),
            row = parent.find('.duplicatedRow:last'),
            hint = parent.attr('rel'),
            parentClasses = parent.attr('class'),
            fieldsRegexp = '',
            fieldIncrement;
            
        if (parentClasses.length > 0)
        {
            for (var i = 0; i < parentClasses.length; i++)
            {
                var parentClass = parentClasses[i];

                if (parentClass.substr(0, 4) == 'reg_')
                {
                    var regClasses = parentClass.substr(4).split('-');
                    
                    if (regClasses.length > 0)
                    {
                        for (y = 0; y < regClasses.length; y++)
                        {
                            var current = regClasses[y];
                            
                            fieldsRegexp+= (y == 0 ? '(\\' + (current == 't' ? 'w' : 'd') + '+)' : '\\[(\\' + (current == 't' ? 'w' : 'd') + '+)\\]');
                            
                            if (current == 'n')
                                fieldIncrement = y + 1;
                        }
                    }
                }
            }
        }
        
        if (fieldsRegexp == '')
        {
            fieldsRegexp = '(\\w+)\\[(\\d+)\\]\\[(\\w+)\\]';
            fieldIncrement = 2;
        }
        
        if (row.length > 0)
        {
            var newRow = createNewRow(row, hint, fieldsRegexp, fieldIncrement);
            
            newRow.insertAfter(row).css('display', '');
        }
        
        var allRows = parent.find('.duplicatedRow').not(':last');
        
        if (allRows.length > 0)
        {
            allRows.each(function(){
                if ($(this).find('a.deleteRow').length == 0)
                    $(this).append($(document.createElement('a')).attr('href', '#').addClass('deleteRow rounded sm-padding').html(deleteRowStr));
            });
        }

        return false;
    });
    
	delegate('click', 'a.deleteRow', function(evt){
        evt.preventDefault();
        
        $(this).parents('.duplicatedRow').slideUp('fast', function(){
			$(this).remove();
		});
	});
});