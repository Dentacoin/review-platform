$(document).ready(function(){

    if ($('body').hasClass('sp-trp-iframe')) {
        function triggerIframeSizeEventForParent() {
            window.parent.postMessage(
                {
                    event_id: 'iframe_size_event',
                    data: {
                        width: $('.site-content').width(),
                        height: $('.site-content').height()
                    }
                },
                "*"
            );
        }
        triggerIframeSizeEventForParent();
        $(window).resize(triggerIframeSizeEventForParent);

        $('a').attr('target', '_top');
    }
});