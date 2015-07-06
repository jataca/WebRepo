jQuery(document).ready(function(){

  jQuery("ul.aeropaneltabs li").click(function(ev){
    jQuery("ul.aeropaneltabs li").removeClass('active');
    jQuery(this).toggleClass('active');
  
    jQuery('.container .aerotab').hide();
    jQuery('#tab_' + jQuery("a", this).attr('href').replace("#", "").replace(" ","_")).fadeIn();

    ev.preventDefault();
  });

  jQuery('form a.submit').click(function(evt){
    var href = jQuery(this).attr('href');
    if ('#' != href) {
      jQuery(this).parents('form').attr('action', href);
    }
    jQuery(this).parents('form').get(0).submit();
    evt.preventDefault();
  });
  
});