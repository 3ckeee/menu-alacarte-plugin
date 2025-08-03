jQuery(document).ready(function($) {
    // Toggle the allergens legend when the button is clicked.
    $('.allergens-button').on('click', function(e) {
        e.stopPropagation();
        var $button = $(this);
        var $list = $button.next('.allergens-list');
        $list.slideToggle(300);
        if ($button.text() === 'Alergény') {
            $button.text('Skryť alergény');
        } else {
            $button.text('Alergény');
        }
    });
    
    // Hide allergens legend when clicking outside.
    $(document).on('click', function(e) {
        $('.allergens-list:visible').slideUp(300);
        $('.allergens-button').text('Alergény');
    });
    
    // Prevent closing when clicking inside the allergens legend.
    $('.allergens-list').on('click', function(e) {
        e.stopPropagation();
    });
});