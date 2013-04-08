$(function(){
    $('a.fancybox').live('click',function(ev){
        ev.preventDefault();

        $.fancybox({
            'href': $(this).attr('href'),
            'type'              : 'iframe',
            'width'				: '50%',
            'height'			: '75%',
            'autoScale'			: false,
            'transitionIn'		: 'none',
            'transitionOut'		: 'none'
        });
    });
});