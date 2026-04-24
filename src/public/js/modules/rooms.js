jQuery(document).ready(($) => {
    const containerEl = document.querySelector('.vmb-rooms');
    
    if (containerEl) {
        const mixer = mixitup(containerEl, {
            selectors: {
                target: '.vmb-room'
            },
            animation: {
                duration: 300
            }
        });
    }

    $('.vmb-room-filters span.active').trigger('click');

    // Initialize the filters
    $('.vmb-room-filters span').on('click', function() {
        const filterValue = $(this).attr('data-filter');
        mixer.filter(filterValue);
        $('.vmb-room-filters span').removeClass('active');
        $(this).addClass('active');
    });
});
