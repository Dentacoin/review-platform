$(document).ready(function(){

    if ($('.swiper-container').length && typeof Swiper !== 'undefined') {

        if (window.innerWidth > 768) {

            var swiper = new Swiper('.swiper-container', {
                slidesPerView: 3,
                slidesPerGroup: 3,
                spaceBetween: 0,
            });
        } else {
            var swiper = new Swiper('.swiper-container', {
                slidesPerView: 1,
                spaceBetween: 0,
                effect: 'coverflow',
                grabCursor: true,
                centeredSlides: true,
                coverflowEffect: {
                    rotate: 50,
                    stretch: 0,
                    depth: 100,
                    modifier: 1,
                    slideShadows : false,
                },
            });
        }
    }

    //Bans
    if( $('.popup.banned').length ) {
        hoursCountdown();
    }

    if ($('body').hasClass('sp-vox-iframe')) {

        var content_heigth = $('.popup.active').length ? $('.popup.active').height() + $('.site-content').height() : $('.site-content').height();
        
        function triggerIframeSizeEventForParent() {
            window.parent.postMessage(
                {
                    event_id: 'iframe_size_event',
                    data: {
                        width: $('.site-content').width(),
                        height: content_heigth
                    }
                },
                "*"
            );
        }
        triggerIframeSizeEventForParent();
        $(window).resize(triggerIframeSizeEventForParent);

        $('a').attr('target', '_top');
    } 

    $('table.paging').each(function() {
        var table = $(this);
        var currentPage = 0;
        var numPerPage = table.attr('num-paging');
        var numRows = table.find('tbody tr').length;
        var numPages = Math.ceil(numRows / numPerPage);
        var pager = $('<div class="pager"></div>');

        if(numRows > numPerPage) {
            table.bind('repaginate', function() {
                table.find('tbody tr').hide().slice(currentPage * numPerPage, (currentPage + 1) * numPerPage).show();
            });
            table.trigger('repaginate');

            for (var page = 0; page < numPages; page++) {
                $('<span class="page-number"></span>').text(page + 1).bind('click', {
                    newPage: page
                }, function(event) {
                    currentPage = event.data['newPage'];
                    table.trigger('repaginate');
                    $(this).addClass('active').siblings().removeClass('active');
                }).appendTo(pager).addClass('clickable');
            }
            pager.insertAfter(table).find('span.page-number:first').addClass('active');
        }

    });

});