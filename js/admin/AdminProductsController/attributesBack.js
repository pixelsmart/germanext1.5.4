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