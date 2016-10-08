$bamber = jQuery.noConflict();

$bamber(document).ready(function() {
    var f = $bamber.farbtastic('#picker');
    var p = $bamber('#picker').addClass('colorwell-hidden');
    var selected;
    var colorid = 0;
    
	$bamber('.colorwell')
      .each(function() { 
	  	f.linkTo(this); colorid++; 
		$bamber(this).addClass('cid-' + colorid);
		$bamber('#picker').append('<a href="#cid-' + colorid + '"" class="clearcolor-button hidden clearpicker-' + colorid + '">clear</a>'); 
	  })
      .focus(function() {
        f.linkTo(this);
		
		var X = $bamber(this).position().left;
		var Y = $bamber(this).position().top;
		
		p.css("position", "absolute");
		p.css("top", -(202-Y)+"px");
		p.css("left", X+100+"px");
		
		p.removeClass('colorwell-hidden');
		
		var $cid = $bamber(this).attr("class");
		$cid = $cid.split(" ", 2);
		$cid = $cid[1].split("-", 2);
		$cid = $cid[1];
		
		$bamber('.clearcolor-button').addClass('hidden');
        $bamber('.clearpicker-' + $cid).removeClass('hidden');
        $bamber(selected = this).addClass('colorwell-selected');
      })
	  .blur(function() {
	      $bamber('.clearcolor-button').click( function() {
			  	var $clearid = $bamber(this).attr("href").substring(1);
			  	$bamber('input.' + $clearid).attr({style:"", value:""});
				$bamber('#picker').addClass('colorwell-hidden');
				$bamber('.colorwell').removeClass('colorwell-selected');	 
		  });
	      
		  $bamber('.closepicker-button').click( function() {
		  	$bamber('#picker').addClass('colorwell-hidden');
		  	$bamber('.colorwell').removeClass('colorwell-selected');
		  });
	  });
      
      $bamber('#picker').append('<a href="#closepicker" class="closepicker-button">OK</a>');
       		
});
