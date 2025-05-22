<?php get_header(); ?>
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/home.css">

<div class="container my-5 home-hero">
    <div class="row align-items-center">
        <!-- Left Image -->
        <div class="col-md-3 mb-4 mb-md-0">
            <div class="hero-img-box">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/model_left.png" class="img-fluid rounded-4" alt="Fashion Left">
            </div>
        </div>
        <!-- Center Content -->
        <div class="col-md-6 text-center">
            <div class="row mb-3">
                <div class="col-12">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/model_top.png" class="img-fluid rounded-4 mb-3" alt="Fashion Top">
                </div>
            </div>
            <h1 class="display-4 fw-bold mb-0">ULTIMATE</h1>
            <h1 class="display-1 fw-light mb-2 sale-outline">SALE</h1>
            <div class="mb-2 text-uppercase" style="letter-spacing:2px;">New Collection</div>
            <a href="#" class="btn btn-dark btn-lg mb-3 px-5">Shop Now</a>
            <div class="row">
                <div class="col-12">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/model_bottom.png" class="img-fluid rounded-4" alt="Fashion Bottom">
                </div>
            </div>
        </div>
        <!-- Right Image -->
        <div class="col-md-3 mt-4 mt-md-0">
            <div class="hero-img-box">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/model_right.png" class="img-fluid rounded-4" alt="Fashion Right">
            </div>
        </div>
    </div>
</div>

<!-- Brand Section -->
<div class="container-fluid py-4 bg-white brand-bar">
    <div class="row justify-content-center align-items-center">
        <div class="col-auto">
            <span class="brand-logo fw-bold fs-3">CHANEL</span>
        </div>
        <div class="col-auto">
            <span class="brand-logo fs-5">LOUIS VUITTON</span>
        </div>
        <div class="col-auto">
            <span class="brand-logo fw-bold fs-2">PRADA</span>
        </div>
        <div class="col-auto">
            <span class="brand-logo fs-4">Calvin Klein</span>
        </div>
        <div class="col-auto">
            <span class="brand-logo fw-bold fs-2">DENIM</span>
        </div>
    </div>
</div>

<!-- Deals Of The Month Section -->
<div class="container my-5 deals-section">
    <div class="row align-items-center">
        <!-- Left Content -->
        <div class="col-md-5 mb-4 mb-md-0">
            <h2 class="fw-bold mb-3">Deals Of The Month</h2>
            <p class="mb-4 text-muted">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Scelerisque duis ultrices sollicitudin aliquam sem. Scelerisque duis ultrices sollicitudin.
            </p>
            <a href="#" class="btn btn-dark mb-4 px-4 py-2">Buy Now</a>
            <div class="mb-4">
                <h5 class="fw-semibold mb-3">Hurry, Before It's Too Late!</h5>
                <div id="deal-countdown" class="d-flex gap-3">
                    <div class="countdown-box text-center">
                        <div class="countdown-time" id="deal-days">02</div>
                        <div class="countdown-label">Days</div>
                    </div>
                    <div class="countdown-box text-center">
                        <div class="countdown-time" id="deal-hours">06</div>
                        <div class="countdown-label">Hr</div>
                    </div>
                    <div class="countdown-box text-center">
                        <div class="countdown-time" id="deal-mins">05</div>
                        <div class="countdown-label">Mins</div>
                    </div>
                    <div class="countdown-box text-center">
                        <div class="countdown-time" id="deal-secs">30</div>
                        <div class="countdown-label">Sec</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Right Slider -->
        <div class="col-md-7">
            <div id="dealCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <!-- Slide 1 -->
                    <div class="carousel-item active">
                        <div class="row g-3">
                            <div class="col-6 col-md-6">
                                <div class="deal-img-box position-relative">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/deal1.png" class="img-fluid rounded-4" alt="Deal 1">
                                    <div class="deal-badge position-absolute bottom-0 start-0 m-3 bg-white px-3 py-2 rounded-3 shadow">
                                        <div class="small text-muted mb-1">01 — Spring Sale</div>
                                        <div class="fw-bold fs-5">30% OFF</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-6">
                                <div class="deal-img-box">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/deal2.png" class="img-fluid rounded-4" alt="Deal 2">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Slide 2 -->
                    <div class="carousel-item">
                        <div class="row g-3">
                            <div class="col-6 col-md-6">
                                <div class="deal-img-box">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/deal3.png" class="img-fluid rounded-4" alt="Deal 3">
                                </div>
                            </div>
                            <div class="col-6 col-md-6">
                                <div class="deal-img-box">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/deal4.png" class="img-fluid rounded-4" alt="Deal 4">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Thêm slide nếu muốn -->
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#dealCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#dealCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- New Arrivals Section -->
<div class="container my-5 new-arrivals-section">
    <h2 class="text-center fw-bold mb-5">New Arrivals</h2>
    <p class="text-center text-muted mb-5">
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Scelerisque duis ultrices sollicitudin aliquam sem. Scelerisque duis ultrices sollicitudin.
    </p>
    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs justify-content-center mb-5" id="arrivalTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link text-muted" id="men-tab" data-bs-toggle="tab" data-bs-target="#men" type="button" role="tab">Men's Fashion</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="women-tab" data-bs-toggle="tab" data-bs-target="#women" type="button" role="tab">Women's Fashion</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-muted" id="women-accessories-tab" data-bs-toggle="tab" data-bs-target="#women-accessories" type="button" role="tab">Women Accessories</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-muted" id="men-accessories-tab" data-bs-toggle="tab" data-bs-target="#men-accessories" type="button" role="tab">Men Accessories</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-muted" id="discount-deals-tab" data-bs-toggle="tab" data-bs-target="#discount-deals" type="button" role="tab">Discount Deals</button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="arrivalTabContent">
        <!-- Women's Fashion Tab (Active by Default) -->
        <div class="tab-pane fade show active" id="women" role="tabpanel" aria-labelledby="women-tab">
            <!-- First Row -->
            <div class="row g-4 justify-content-center mb-5">
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/newArrival1.png" class="card-img-top rounded-3" alt="Shiny Dress">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <h6 class="card-title text-muted mb-0 me-2">Shiny Dress</h6>
                                <p class="text-muted small mb-0 me-2">Al Koram</p>
                                <div class="rating mb-0">
                                    <span class="text-warning">★★★★☆</span> (41)
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-center">
                                <h5 class="card-price fw-bold mb-0 me-2">$95.50</h5>
                                <span class="badge bg-danger text-white">Almost Sold Out</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/newArrival2.png" class="card-img-top rounded-3" alt="Long Dress">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <h6 class="card-title text-muted mb-0 me-2">Long Dress</h6>
                                <p class="text-muted small mb-0 me-2">Al Koram</p>
                                <div class="rating mb-0">
                                    <span class="text-warning">★★★★☆</span> (41)
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-center">
                                <h5 class="card-price fw-bold mb-0 me-2">$95.50</h5>
                                <span class="badge bg-danger text-white">Almost Sold Out</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/newArrival3.png" class="card-img-top rounded-3" alt="Full Sweater">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <h6 class="card-title text-muted mb-0 me-2">Full Sweater</h6>
                                <p class="text-muted small mb-0 me-2">Al Koram</p>
                                <div class="rating mb-0">
                                    <span class="text-warning">★★★★☆</span> (41)
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-center">
                                <h5 class="card-price fw-bold mb-0 me-2">$95.50</h5>
                                <span class="badge bg-danger text-white">Almost Sold Out</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Second Row -->
            <div class="row g-4 justify-content-center mb-5">
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/newArrival4.png" class="card-img-top rounded-3" alt="White Dress">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <h6 class="card-title text-muted mb-0 me-2">White Dress</h6>
                                <p class="text-muted small mb-0 me-2">Al Koram</p>
                                <div class="rating mb-0">
                                    <span class="text-warning">★★★★☆</span> (41)
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-center">
                                <h5 class="card-price fw-bold mb-0 me-2">$95.50</h5>
                                <span class="badge bg-danger text-white">Almost Sold Out</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/newArrival5.png" class="card-img-top rounded-3" alt="Colorful Dress">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <h6 class="card-title text-muted mb-0 me-2">Colorful Dress</h6>
                                <p class="text-muted small mb-0 me-2">Al Koram</p>
                                <div class="rating mb-0">
                                    <span class="text-warning">★★★★☆</span> (41)
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-center">
                                <h5 class="card-price fw-bold mb-0 me-2">$95.50</h5>
                                <span class="badge bg-danger text-white">Almost Sold Out</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/newArrival6.png" class="card-img-top rounded-3" alt="White Shirt">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <h6 class="card-title text-muted mb-0 me-2">White Shirt</h6>
                                <p class="text-muted small mb-0 me-2">Al Koram</p>
                                <div class="rating mb-0">
                                    <span class="text-warning">★★★★☆</span> (41)
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-center">
                                <h5 class="card-price fw-bold mb-0 me-2">$95.50</h5>
                                <span class="badge bg-danger text-white">Almost Sold Out</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5">
                <a href="#" class="btn btn-dark px-4 py-2">View More</a>
            </div>
        </div>
        <!-- Other Tab Panes (Men's Fashion, Women Accessories, Men Accessories, Discount Deals) -->
        <div class="tab-pane fade" id="men" role="tabpanel" aria-labelledby="men-tab">
            <!-- Add similar content here if needed -->
        </div>
        <div class="tab-pane fade" id="women-accessories" role="tabpanel" aria-labelledby="women-accessories-tab">
            <!-- Add similar content here if needed -->
        </div>
        <div class="tab-pane fade" id="men-accessories" role="tabpanel" aria-labelledby="men-accessories-tab">
            <!-- Add similar content here if needed -->
        </div>
        <div class="tab-pane fade" id="discount-deals" role="tabpanel" aria-labelledby="discount-deals-tab">
            <!-- Add similar content here if needed -->
        </div>
    </div>
</div>
<!-- Footer -->
<footer class="footer py-4 bg-white">
    <div class="container">
        <div class="row align-items-center">
            <!-- Logo -->
            <div class="col-auto">
                <a href="<?php echo home_url(); ?>" class="footer-logo text-dark text-uppercase fw-bold">Fasco</a>
            </div>
            <!-- Navigation Links -->
            <div class="col-auto ms-auto">
                <ul class="nav footer-nav">
                    <li class="nav-item">
                        <a class="nav-link text-muted" href="#">Support Center</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-muted" href="#">Invoicing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-muted" href="#">Contract</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-muted" href="#">Careers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-muted" href="#">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-muted" href="#">FAQ's</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Copyright -->
        <div class="text-center mt-3">
            <p class="text-muted small mb-0">Copyright © <?php echo date('Y'); ?> Xpro. All Rights Reserved.</p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
<!-- Countdown JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set your deal end date here
    var dealEnd = new Date();
    dealEnd.setDate(dealEnd.getDate() + 2); // 2 days from now

    function updateCountdown() {
        var now = new Date();
        var diff = dealEnd - now;
        if (diff < 0) diff = 0;
        var days = Math.floor(diff / (1000 * 60 * 60 * 24));
        var hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
        var mins = Math.floor((diff / (1000 * 60)) % 60);
        var secs = Math.floor((diff / 1000) % 60);
        document.getElementById('deal-days').textContent = String(days).padStart(2, '0');
        document.getElementById('deal-hours').textContent = String(hours).padStart(2, '0');
        document.getElementById('deal-mins').textContent = String(mins).padStart(2, '0');
        document.getElementById('deal-secs').textContent = String(secs).padStart(2, '0');
    }
    setInterval(updateCountdown, 1000);
    updateCountdown();
});
</script>

<?php get_footer(); ?>