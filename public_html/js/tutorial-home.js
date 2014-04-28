$(document).ready(function(){
	$('#walkthrough').pagewalkthrough({
		steps:
        [
               {
                   wrapper: '.open_series:first',
                   margin: '0',
                   popup:
                   {
                       content: '#tutorial-open',
                       type: 'tooltip',
                       position: 'right',
                       offsetHorizontal: 0,
                       offsetVertical: 0,
                       width: '300'
                   }        
               },
               {
                   wrapper: '#series-data',
                   margin: '0',
                   popup:
                   {
                       content: '#tutorial-episode',
                       type: 'tooltip',
                       position: 'top',
                       offsetHorizontal: 0,
                       offsetVertical: 0,
                       width: '300'
                   }        
               },
               {
                   wrapper: '.home_unfollow',
                   margin: '0',
                   popup:
                   {
                       content: '#tutorial-unfollow',
                       type: 'nohighlight',
                       position: 'left',
                       offsetHorizontal: 0,
                       offsetVertical: 0,
                       width: '300'
                   } 
               },
               {
                   wrapper: '#calendar-link',
                   margin: '0',
                   popup:
                   {
                       content: '#tutorial-calendar',
                       type: 'tooltip',
                       position: 'bottom',
                       offsetHorizontal: 10,
                       offsetVertical: 0,
                       width: '300'
                   }
               },
               {
                   wrapper: '#series-link',
                   margin: '0',
                   popup:
                   {
                       content: '#tutorial-series',
                       type: 'tooltip',
                       position: 'bottom',
                       offsetHorizontal: -45,
                       offsetVertical: 0,
                       width: '300'
                   }
               },
        ],
        name: ''
	});
	
	$('.prev-step').live('click', function(e){
		$.pagewalkthrough('prev',e);
	});
	
	$('.next-step').live('click', function(e){
		$.pagewalkthrough('next',e);
	});
});
