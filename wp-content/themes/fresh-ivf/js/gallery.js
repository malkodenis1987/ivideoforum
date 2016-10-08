/**
 * Created with JetBrains PhpStorm.
 * User: Elvis
 * Date: 30.10.13
 * Time: 1:22
 * To change this template use File | Settings | File Templates.
 */
jQuery(document).ready(function(){
    /*
    jQuery('.full-width #gallery UL LI A').click(function(){
        var href = jQuery(this).attr('href');
        jQuery("#contest-frame").attr("src", href);
        return false;
    });
    */
    jQuery(".fancybox").fancybox({
        maxWidth	: 800,
        maxHeight	: 600,
        fitToView	: false,
        width		: '70%',
        height		: '70%',
        autoSize	: false,
        closeClick	: false,
        openEffect	: 'none',
        closeEffect	: 'none'
    });
});
