/**
 * Created by remco on 28/10/15.
 */
 /* global jQuery, window, SmoothScroll, tocbot */
(function($) {

    // init page nagivation
    if($("#toc").length){

        // add id's to h2
		$('#content-core h2').each( function(i) {
			if($(this).attr('id') === undefined) {
				$(this).attr('id', 'anchor' + i );
			}
		});

        // init tocbot
        tocbot.init({
            tocSelector: '#toc',
            contentSelector: '#content-core',
            headingSelector: 'h2',
            activeLinkClass: 'nav-active',
            listClass: 'linklist linklist-dark',
            positionFixedSelector: 'nav[data-lockfixed="true"]',
            positionFixedClass: 'stick',
            fixedSidebarOffset: $('nav[data-lockfixed="true"]').offset().top,
            orderedList: false
        });

        // fix toc width
        $(window).on('scroll resize', function() {
            var $nav = $('nav[data-lockfixed="true"]');

            if($nav.width() !== $('#nav-anchor').width() - 40) {
                $nav.css({'width':$('#nav-anchor').width()});
            }
        });
    }

    // datepicker
    $('.input-group.date').each(function () {
        $(this).datetimepicker(
            {
                "format": "DD-MM-YYYY",
                "locale": "nl"
            }
        );
    });

    $('.input-group.date-en').each(function () {
        $(this).datetimepicker(
            {
                "format": "MM-DD-YYYY",
                "locale": "en"
            }
        );
    });

    // Toggle search
    $('.pageheader .toggle-search').on('click', function () {
        $('.pageheader').toggleClass('showsearch');
        $('.pageheader #search').focus();
    });

    // SmoothScroll
    var scroll = new SmoothScroll('a[href*="#"]'); // eslint-disable-line no-unused-vars

}(jQuery));
