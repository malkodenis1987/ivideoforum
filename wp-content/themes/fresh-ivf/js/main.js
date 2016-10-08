jQuery(document).ready(function () {
    jQuery(".tab_content").hide();
    jQuery("ul.tabs li:first").addClass("active").show();
    jQuery(".tab_content:first").show();
    jQuery("ul.tabs li").click(function () {
        jQuery("ul.tabs li").removeClass("active");
        jQuery(this).addClass("active");
        jQuery(".tab_content").hide();
        var activeTab = jQuery(this).find("a").attr("href");
        jQuery(activeTab).fadeIn();
        return false;
    });
    jQuery('.cycle-slideshow').cycle();
});
