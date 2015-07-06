jQuery(document).ready(function(){
  
  jQuery("select[name=lbe_theme]").change(function() {
    jQuery(".theme_preview").hide();
    jQuery(".theme_" + jQuery(this).val()).fadeIn();
  }).each(function() {
    jQuery(".theme_preview").hide();
    jQuery(".theme_" + jQuery(this).val()).fadeIn();
  });
  
});