var ready = (function(){    

    var readyList,
        DOMContentLoaded,
        class2type = {};
        class2type["[object Boolean]"] = "boolean";
        class2type["[object Number]"] = "number";
        class2type["[object String]"] = "string";
        class2type["[object Function]"] = "function";
        class2type["[object Array]"] = "array";
        class2type["[object Date]"] = "date";
        class2type["[object RegExp]"] = "regexp";
        class2type["[object Object]"] = "object";

    var ReadyObj = {
        // Is the DOM ready to be used? Set to true once it occurs.
        isReady: false,
        // A counter to track how many items to wait for before
        // the ready event fires. See #6781
        readyWait: 1,
        // Hold (or release) the ready event
        holdReady: function( hold ) {
            if ( hold ) {
                ReadyObj.readyWait++;
            } else {
                ReadyObj.ready( true );
            }
        },
        // Handle when the DOM is ready
        ready: function( wait ) {
            // Either a released hold or an DOMready/load event and not yet ready
            if ( (wait === true && !--ReadyObj.readyWait) || (wait !== true && !ReadyObj.isReady) ) {
                // Make sure body exists, at least, in case IE gets a little overzealous (ticket #5443).
                if ( !document.body ) {
                    return setTimeout( ReadyObj.ready, 1 );
                }

                // Remember that the DOM is ready
                ReadyObj.isReady = true;
                // If a normal DOM Ready event fired, decrement, and wait if need be
                if ( wait !== true && --ReadyObj.readyWait > 0 ) {
                    return;
                }
                // If there are functions bound, to execute
                readyList.resolveWith( document, [ ReadyObj ] );

                // Trigger any bound ready events
                //if ( ReadyObj.fn.trigger ) {
                //  ReadyObj( document ).trigger( "ready" ).unbind( "ready" );
                //}
            }
        },
        bindReady: function() {
            if ( readyList ) {
                return;
            }
            readyList = ReadyObj._Deferred();

            // Catch cases where $(document).ready() is called after the
            // browser event has already occurred.
            if ( document.readyState === "complete" ) {
                // Handle it asynchronously to allow scripts the opportunity to delay ready
                return setTimeout( ReadyObj.ready, 1 );
            }

            // Mozilla, Opera and webkit nightlies currently support this event
            if ( document.addEventListener ) {
                // Use the handy event callback
                document.addEventListener( "DOMContentLoaded", DOMContentLoaded, false );
                // A fallback to window.onload, that will always work
                window.addEventListener( "load", ReadyObj.ready, false );

            // If IE event model is used
            } else if ( document.attachEvent ) {
                // ensure firing before onload,
                // maybe late but safe also for iframes
                document.attachEvent( "onreadystatechange", DOMContentLoaded );

                // A fallback to window.onload, that will always work
                window.attachEvent( "onload", ReadyObj.ready );

                // If IE and not a frame
                // continually check to see if the document is ready
                var toplevel = false;

                try {
                    toplevel = window.frameElement == null;
                } catch(e) {}

                if ( document.documentElement.doScroll && toplevel ) {
                    doScrollCheck();
                }
            }
        },
        _Deferred: function() {
            var // callbacks list
                callbacks = [],
                // stored [ context , args ]
                fired,
                // to avoid firing when already doing so
                firing,
                // flag to know if the deferred has been cancelled
                cancelled,
                // the deferred itself
                deferred  = {

                    // done( f1, f2, ...)
                    done: function() {
                        if ( !cancelled ) {
                            var args = arguments,
                                i,
                                length,
                                elem,
                                type,
                                _fired;
                            if ( fired ) {
                                _fired = fired;
                                fired = 0;
                            }
                            for ( i = 0, length = args.length; i < length; i++ ) {
                                elem = args[ i ];
                                type = ReadyObj.type( elem );
                                if ( type === "array" ) {
                                    deferred.done.apply( deferred, elem );
                                } else if ( type === "function" ) {
                                    callbacks.push( elem );
                                }
                            }
                            if ( _fired ) {
                                deferred.resolveWith( _fired[ 0 ], _fired[ 1 ] );
                            }
                        }
                        return this;
                    },

                    // resolve with given context and args
                    resolveWith: function( context, args ) {
                        if ( !cancelled && !fired && !firing ) {
                            // make sure args are available (#8421)
                            args = args || [];
                            firing = 1;
                            try {
                                while( callbacks[ 0 ] ) {
                                    callbacks.shift().apply( context, args );//shifts a callback, and applies it to document
                                }
                            }
                            finally {
                                fired = [ context, args ];
                                firing = 0;
                            }
                        }
                        return this;
                    },

                    // resolve with this as context and given arguments
                    resolve: function() {
                        deferred.resolveWith( this, arguments );
                        return this;
                    },

                    // Has this deferred been resolved?
                    isResolved: function() {
                        return !!( firing || fired );
                    },

                    // Cancel
                    cancel: function() {
                        cancelled = 1;
                        callbacks = [];
                        return this;
                    }
                };

            return deferred;
        },
        type: function( obj ) {
            return obj == null ?
                String( obj ) :
                class2type[ Object.prototype.toString.call(obj) ] || "object";
        }
    }
    // The DOM ready check for Internet Explorer
    function doScrollCheck() {
        if ( ReadyObj.isReady ) {
            return;
        }

        try {
            // If IE is used, use the trick by Diego Perini
            // http://javascript.nwbox.com/IEContentLoaded/
            document.documentElement.doScroll("left");
        } catch(e) {
            setTimeout( doScrollCheck, 1 );
            return;
        }

        // and execute any waiting functions
        ReadyObj.ready();
    }
    // Cleanup functions for the document ready method
    if ( document.addEventListener ) {
        DOMContentLoaded = function() {
            document.removeEventListener( "DOMContentLoaded", DOMContentLoaded, false );
            ReadyObj.ready();
        };

    } else if ( document.attachEvent ) {
        DOMContentLoaded = function() {
            // Make sure body exists, at least, in case IE gets a little overzealous (ticket #5443).
            if ( document.readyState === "complete" ) {
                document.detachEvent( "onreadystatechange", DOMContentLoaded );
                ReadyObj.ready();
            }
        };
    }
    function ready( fn ) {
        // Attach the listeners
        ReadyObj.bindReady();

        var type = ReadyObj.type( fn );

        // Add the callback
        readyList.done( fn );//readyList is result of _Deferred()
    }
    return ready;
})();

ready(function(){
	if (typeof(window.product_tabs) != 'undefined')
	{
		var parentSelf = product_tabs['Combinations'];
		
		product_tabs['Combinations'].bindEdit = function(){
			$('table[name=list_table]').delegate('a.edit', 'click', function(e){
				e.preventDefault();
				editProductAttribute(this.href, $(this).closest('tr'));
			});
	
			function editProductAttribute (url, parent){
				$.ajax({
					url: url,
					data: {
						id_product: id_product,
						ajax: true,
						action: 'editProductAttribute'
					},
					context: document.body,
					dataType: 'json',
					context: this,
					async: false,
					success: function(data) {
						// color the selected line
						parent.siblings().removeClass('selected-line');
						parent.addClass('selected-line');
	
						$('#add_new_combination').show();
						$('#attribute_quantity').show();
						$('#product_att_list').html('');
						parentSelf.removeButtonCombination('update');
						$.scrollTo('#add_new_combination', 1200, { offset: -100 });
						var wholesale_price = Math.abs(data[0]['wholesale_price']);
						var price = Math.abs(data[0]['price']);
						var weight = Math.abs(data[0]['weight']);
						var unit_impact = Math.abs(data[0]['unit_price_impact']);
						var net_impact = Math.abs(data[0]['unit_net_impact']);
						var reference = data[0]['reference'];
						var ean = data[0]['ean13'];
						var quantity = data[0]['quantity'];
						var image = false;
						var product_att_list = new Array();
						for(var i in data)
						{
							if (typeof(data[i]) == 'object' && data[i] != null) {
								product_att_list.push(data[i]['group_name']+' : '+data[i]['attribute_name']);
								product_att_list.push(data[i]['id_attribute']);
							}
						}
	
						var id_product_attribute = data[0]['id_product_attribute'];
						var default_attribute = data[0]['default_on'];
						var eco_tax = data[0]['ecotax'];
						var upc = data[0]['upc'];
						var minimal_quantity = data[0]['minimal_quantity'];
						var available_date = data[0]['available_date'];
	
						if (wholesale_price != 0 && wholesale_price > 0)
						{
							$("#attribute_wholesale_price_full").show();
							$("#attribute_wholesale_price_blank").hide();
						}
						else
						{
							$("#attribute_wholesale_price_full").hide();
							$("#attribute_wholesale_price_blank").show();
						}
						parentSelf.fillCombination(
							wholesale_price,
							price,
							weight,
							unit_impact,
							net_impact,
							reference,
							ean,
							quantity,
							image,
							product_att_list,
							id_product_attribute,
							default_attribute,
							eco_tax,
							upc,
							minimal_quantity,
							available_date
						);
						calcImpactPriceTI();
					}
				});
			}
		};
		
		product_tabs['Combinations'].fillCombination = function(
			wholesale_price,
			price_impact,
			weight_impact,
			unit_impact,
			net_impact,
			reference,
			ean,
			quantity,
			image,
			old_attr,
			id_product_attribute,
			default_attribute,
			eco_tax,
			upc,
			minimal_quantity,
			available_date
		)
		{
			var link = '';
			parentSelf.init_elems();
			$('#stock_mvt_attribute').show();
			$('#initial_stock_attribute').hide();
			$('#attribute_quantity').html(quantity);
			$('#attribute_quantity').show();
			$('#attr_qty_stock').show();
	
			$('#attribute_minimal_quantity').val(minimal_quantity);
	
			getE('attribute_reference').value = reference;
	
			getE('attribute_ean13').value = ean;
			getE('attribute_upc').value = upc;
			getE('attribute_wholesale_price').value = Math.abs(wholesale_price);
			getE('attribute_price').value = ps_round(Math.abs(price_impact), 2);
			getE('attribute_priceTEReal').value = Math.abs(price_impact);
			getE('attribute_weight').value = Math.abs(weight_impact);
			getE('attribute_unity').value = Math.abs(unit_impact);
			getE('attribute_net').value = Math.abs(net_impact);
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
			
			if (net_impact < 0)
			{
				getE('attribute_net_impact').options[getE('attribute_net_impact').selectedIndex].value = -1;
				getE('attribute_net_impact').selectedIndex = 2;
			}
			else if (!net_impact)
			{
				getE('attribute_net_impact').options[getE('attribute_net_impact').selectedIndex].value = 0;
				getE('attribute_net_impact').selectedIndex = 0;
			}
			else if (net_impact > 0)
			{
				getE('attribute_net_impact').options[getE('attribute_net_impact').selectedIndex].value = 1;
				getE('attribute_net_impact').selectedIndex = 1;
			}
	
			$("#add_new_combination").show();
	
			/* Reset all combination images */
			combinationImages = $('#id_image_attr').find("input[id^=id_image_attr_]");
			combinationImages.each(function() {
				this.checked = false;
			});
	
			/* Check combination images */
			if (typeof(combination_images[id_product_attribute]) != 'undefined')
				for (i = 0; i < combination_images[id_product_attribute].length; i++)
					$('#id_image_attr_' + combination_images[id_product_attribute][i]).attr('checked', true);
			check_impact();
			check_weight_impact();
			check_unit_impact();
	
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
		};
	}
});
