jQuery(document).ready(function($) {
    console.log('main-shop.js loaded');
    
    // Color filter interaction
    $('.color-option').on('click', function() {
        $(this).toggleClass('selected');
        updateFilters();
    });
    
    // Size filter interaction
    $('.size-option').on('click', function(e) {
        e.preventDefault();
        $('.size-option').removeClass('active');
        $(this).addClass('active');
        updateFilters();
    });
    
    // Category filter interaction
    $('input[name="category"]').on('change', function() {
        updateFilters();
    });
    
    // Sort change
    $('#sort-products').on('change', function() {
        updateFilters();
    });
    
    // View options interaction
    $('.view-btn').on('click', function() {
        $('.view-btn').removeClass('active');
        $(this).addClass('active');
        
        var view = $(this).data('view');
        var container = $('#products-container');
        var items = container.find('.product-item');
        
        items.removeClass('col-lg-6 col-lg-4 col-lg-3');
        
        switch(view) {
            case 2:
                items.addClass('col-lg-6');
                break;
            case 3:
                items.addClass('col-lg-4');
                break;
            case 4:
                items.addClass('col-lg-3');
                break;
        }
    });
    
    // Product color switching
    $(document).on('click', '.product-color-dot', function(e) {
        console.log('Color dot clicked', this);
        e.preventDefault();
        e.stopPropagation();

        var $this = $(this);
        var colorId = $this.data('color-id');
        var $productCard = $this.closest('.product-card');

        // Đổi class active cho màu
        $productCard.find('.product-color-dot').removeClass('active');
        $this.addClass('active');

        // Đổi ảnh chính
        var $mainImage = $productCard.find('.product-main-image');
        var $targetImage = $productCard.find('.product-image-hidden[data-color-id="' + colorId + '"]');
        if ($targetImage.length > 0) {
            var newSrc = $targetImage.attr('src');
            var newAlt = $targetImage.attr('alt');
            $mainImage.fadeOut(150, function() {
                $mainImage.attr('src', newSrc);
                $mainImage.attr('alt', newAlt);
                $mainImage.attr('data-color-id', colorId);
                $mainImage.fadeIn(150);
            });
        }
    });
    
    // Update filters function
    function updateFilters() {
        $('#loading-overlay').show();
        
        var params = new URLSearchParams();
        
        // Get selected colors
        var selectedColors = [];
        $('.color-option.selected').each(function() {
            selectedColors.push($(this).data('color'));
        });
        if (selectedColors.length > 0) {
            params.set('colors', selectedColors.join(','));
        }
        
        // Get selected size
        var selectedSize = $('.size-option.active').data('size');
        if (selectedSize) {
            params.set('size', selectedSize);
        }
        
        // Get selected category
        var selectedCategory = $('input[name="category"]:checked').val();
        if (selectedCategory) {
            params.set('category', selectedCategory);
        }
        
        // Get price range
        var minPrice = $('#min_price').val();
        var maxPrice = $('#max_price').val();
        if (minPrice) params.set('min_price', minPrice);
        if (maxPrice) params.set('max_price', maxPrice);
        
        // Get sort
        var sort = $('#sort-products').val();
        if (sort) params.set('sort', sort);
        
        // Reset to page 1
        params.set('page', '1');
        
        // Redirect with new parameters
        window.location.href = window.location.pathname + '?' + params.toString();
    }
});

function applyPriceFilter() {
    jQuery(document).ready(function($) {
        $('#loading-overlay').show();
        
        var params = new URLSearchParams(window.location.search);
        
        var minPrice = $('#min_price').val();
        var maxPrice = $('#max_price').val();
        
        if (minPrice) {
            params.set('min_price', minPrice);
        } else {
            params.delete('min_price');
        }
        
        if (maxPrice) {
            params.set('max_price', maxPrice);
        } else {
            params.delete('max_price');
        }
        
        // Reset to page 1
        params.set('page', '1');
        
        window.location.href = window.location.pathname + '?' + params.toString();
    });
}

function clearAllFilters() {
    jQuery(document).ready(function($) {
        $('#loading-overlay').show();
        window.location.href = window.location.pathname;
    });
}