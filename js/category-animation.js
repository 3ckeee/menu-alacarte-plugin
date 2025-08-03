jQuery(document).ready(function($) {
    // When a category tab is clicked...
    $('.menu-category-nav a').on('click', function(e) {
        e.preventDefault();
        var $clickedTab     = $(this);
        var targetID        = $clickedTab.attr('href'); // e.g., "#cat-antipasti"
        var $currentContainer = $('.menu-category-items.active');
        var $targetContainer  = $(targetID);

        // Mark the clicked tab as active
        $('.menu-category-nav a').removeClass('active');
        $clickedTab.addClass('active');

        // If there's no active container, simply show the target
        if (!$currentContainer.length) {
            $targetContainer
                .addClass('active')
                .fadeIn(300, function() {
                    // Animate each menu item inside target container
                    $targetContainer.find('.menu-alacarte-item').each(function(i) {
                        $(this).delay(i * 100).queue(function(next) {
                            $(this).addClass('animate');
                            next();
                        });
                    });
                });
        } else {
            // Determine slide direction based on relative positions
            var currentIndex = $('.menu-category-items').index($currentContainer);
            var targetIndex  = $('.menu-category-items').index($targetContainer);
            var direction    = (targetIndex > currentIndex) ? 'right' : 'left';

            // Animate current container out
            $currentContainer.animate(
                { opacity: 0, marginLeft: (direction === 'right' ? '-100%' : '100%') },
                300,
                function() {
                    $currentContainer
                        .removeClass('active')
                        .hide()
                        .css({ opacity: '', marginLeft: '' });

                    // Prepare target container: start offscreen in the appropriate direction
                    $targetContainer.css({
                        display: 'block',
                        opacity: 0,
                        marginLeft: (direction === 'right' ? '100%' : '-100%')
                    });

                    // Animate target container in
                    $targetContainer.animate(
                        { opacity: 1, marginLeft: '0' },
                        300,
                        function() {
                            $targetContainer.addClass('active');

                            // Animate each menu item individually
                            $targetContainer.find('.menu-alacarte-item').each(function(i) {
                                $(this).delay(i * 100).queue(function(next) {
                                    $(this).addClass('animate');
                                    next();
                                });
                            });
                        }
                    );
                }
            );
        }
    });

    // Trigger animation for the initially active category (if any)
    var $initial = $('.menu-category-items.active');
    if ($initial.length) {
        $initial.find('.menu-alacarte-item').each(function(i) {
            $(this).delay(i * 100).queue(function(next) {
                $(this).addClass('animate');
                next();
            });
        });
    } else {
        // If no category is marked active by default, show the first one.
        var $first = $('.menu-category-items').first();
        $first.addClass('active').fadeIn(300, function() {
            $first.find('.menu-alacarte-item').each(function(i) {
                $(this).delay(i * 100).queue(function(next) {
                    $(this).addClass('animate');
                    next();
                });
            });
        });
    }

    // ---------- MOBILE‐FRIENDLY SUBMENU TOGGLE ----------
    // If a parent <a> has a .sub-menu, tapping it on touch devices toggles that submenu
    $('.menu-category-nav li > a').on('click', function(e) {
        var $parentLi = $(this).parent('li');
        var $submenu  = $parentLi.children('.sub-menu');
        if ( $submenu.length ) {
            // If there's a submenu, prevent the normal category switch for now
            e.preventDefault();
            // Close any other open submenus
            $parentLi.siblings().find('.sub-menu').slideUp(150);
            // Toggle this submenu
            $submenu.slideToggle(150);
        }
        // If no submenu exists, the above “.menu-category-nav a onClick” handler will run
    });
});