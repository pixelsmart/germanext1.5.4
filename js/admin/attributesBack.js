function check_net_impact()
{
	if ($('#attribute_net_impact').get(0).selectedIndex == 0)
	{
		$('#span_net_impact').hide();
		$('#attribute_net').val('0.00');
	}
	else
		$('#span_net_impact').show();
}

// This might seem tricky, so here's a short explanation: we try to avoid
// replacing original prestashop's files as much as possible. In this case,
// prestashop is calling a fillCombination function from it's
// /js/attributesBack.js file. So to avoid replacing that file, we need to
// overload the function from here using javascript's "window" object.
if (typeof(window.fillCombination) != 'undefined')
{
	window.fillCombination = function(wholesale_price, price_impact, weight_impact, unit_impact, reference,
ean, quantity, image, old_attr, id_product_attribute, default_attribute, eco_tax, upc, minimal_quantity, available_date,
virtual_product_name_attribute, virtual_product_filename_attribute, virtual_product_nb_downloable, virtual_product_expiration_date_attribute, 
virtual_product_nb_days, is_shareable){
		var link = '';
		init_elems();
		$('#stock_mvt_attribute').show();
		$('#initial_stock_attribute').hide();
		$('#attribute_quantity').html(quantity);
		$('#attribute_quantity').show();
		$('#attr_qty_stock').show();
	
		$('#attribute_minimal_quantity').val(minimal_quantity);
	
		getE('attribute_reference').value = reference;
		
		getE('virtual_product_name_attribute').value = virtual_product_name_attribute;
		getE('virtual_product_nb_downloable_attribute').value = virtual_product_nb_downloable;
		getE('virtual_product_expiration_date_attribute').value = virtual_product_nb_downloable;
		getE('virtual_product_expiration_date_attribute').value = virtual_product_expiration_date_attribute;
		getE('virtual_product_nb_days_attribute').value = virtual_product_nb_days;
		
		getE('attribute_ean13').value = ean;
		getE('attribute_upc').value = upc;
		getE('attribute_wholesale_price').value = Math.abs(wholesale_price);
		getE('attribute_price').value = ps_round(Math.abs(price_impact), 2);
		getE('attribute_priceTEReal').value = Math.abs(price_impact);
		getE('attribute_weight').value = Math.abs(weight_impact);
		getE('attribute_unity').value = Math.abs(unit_impact);
		if ($('#attribute_ecotax').length != 0)
			getE('attribute_ecotax').value = eco_tax;
	
		if (default_attribute == 1)
			getE('attribute_default').checked = true;
		else
			getE('attribute_default').checked = false;
	
		if (price_impact < 0)
		{
			getE('attribute_price_impact').options[getE('attribute_price_impact').selectedIndex].value = -1;
			getE('attribute_price_impact').selectedIndex = 2;
		}
		else if (!price_impact)
		{
			getE('attribute_price_impact').options[getE('attribute_price_impact').selectedIndex].value = 0;
			getE('attribute_price_impact').selectedIndex = 0;
		}
		else if (price_impact > 0)
		{
			getE('attribute_price_impact').options[getE('attribute_price_impact').selectedIndex].value = 1;
			getE('attribute_price_impact').selectedIndex = 1;
		}
		if (weight_impact < 0)
		{
			getE('attribute_weight_impact').options[getE('attribute_weight_impact').selectedIndex].value = -1;
			getE('attribute_weight_impact').selectedIndex = 2;
		}
		else if (!weight_impact)
		{
			getE('attribute_weight_impact').options[getE('attribute_weight_impact').selectedIndex].value = 0;
			getE('attribute_weight_impact').selectedIndex = 0;
		}
		else if (weight_impact > 0)
		{
			getE('attribute_weight_impact').options[getE('attribute_weight_impact').selectedIndex].value = 1;
			getE('attribute_weight_impact').selectedIndex = 1;
		}
		if (unit_impact < 0)
		{
			getE('attribute_unit_impact').options[getE('attribute_unit_impact').selectedIndex].value = -1;
			getE('attribute_unit_impact').selectedIndex = 2;
		}
		else if (!unit_impact)
		{
			getE('attribute_unit_impact').options[getE('attribute_unit_impact').selectedIndex].value = 0;
			getE('attribute_unit_impact').selectedIndex = 0;
		}
		else if (unit_impact > 0)
		{
			getE('attribute_unit_impact').options[getE('attribute_unit_impact').selectedIndex].value = 1;
			getE('attribute_unit_impact').selectedIndex = 1;
		}
	
		if (is_shareable > 0)
			$("#virtual_product_is_shareable_attribute").attr("checked", "checked");
		
		if (id_product_attribute != '' && virtual_product_filename_attribute != '')
			$("#gethtmlink").show();
		link = $("#make_downloadable_product_attribute").attr('href');		
		$("#make_downloadable_product_attribute").attr('href', link+"&id_product_attribute="+id_product_attribute);
	
		$("#virtual_product_filename_attribute").val(virtual_product_filename_attribute);
		$("#add_new_combination").show();
	
		/* Reset all combination images */
		combinationImages = $('#id_image_attr').find("input[id^=id_image_attr_]");
		combinationImages.each(function() {
			this.checked = false;
		});
	
		/* Check combination images */
		if (typeof(combination_images[id_product_attribute]) != 'undefined')
			for (i = 0; i < combination_images[id_product_attribute].length; i++)
				$('#id_image_attr_' + combination_images[id_product_attribute][i]).attr('checked', 'checked');
		check_impact();
		check_weight_impact();
		check_unit_impact();
		check_net_impact();
	
		var elem = getE('product_att_list');
	
		for (var i = 0; i < old_attr.length; i++)
		{
			var opt = document.createElement('option');
			opt.text = old_attr[i++];
			opt.value = old_attr[i];
			try {
				elem.add(opt, null);
			}
			catch(ex) {
				elem.add(opt);
			}
		}
		getE('id_product_attribute').value = id_product_attribute;
	
		$('#available_date_attribute').val(available_date);
	}
	
    function updateUnitNet()
    {
        $('#unity_net_second').html( document.getElementById('unity').value );
          
        var Price = parseFloat(document.getElementById('priceTE').value.replace(/,/g, '.'));
		
        if (isNaN(Price) || Price <= 0)
		{
			return;
		}
         
        var UnitPrice = parseFloat(document.getElementById('unit_price').value.replace(/,/g, '.'));  
        var UnitNet = parseFloat(document.getElementById('unit_net').value.replace(/,/g, '.'));
		
        if (isNaN(UnitPrice) || UnitPrice < 0)
		{
			UnitPrice = 0;
		}
		
        if (isNaN(UnitNet)   || UnitNet < 0  )  
        {
            UnitNet = 0;
        }
         
        UnitPrice = (UnitNet > 0) ? Price/UnitNet : 0;
        document.getElementById('unit_price').value = ps_round(UnitPrice, 2).toFixed(2);
        unitPriceWithTax('unit');
    }
}