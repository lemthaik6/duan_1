document.addEventListener('DOMContentLoaded', function() {
    // Initialize Hero Slider
    const heroSlider = new Swiper('.hero-slider', {
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });

    // Initialize Testimonials Slider
    const testimonialsSlider = new Swiper('.testimonials-slider', {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            640: {
                slidesPerView: 2,
            },
            1024: {
                slidesPerView: 3,
            },
        },
    });

    // Animate on scroll
    const animatedElements = document.querySelectorAll('.fade-in');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });

    animatedElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'all 0.6s ease-out';
        observer.observe(element);
    });

    // Price Range Filter
    const minPriceInput = document.querySelector('input[name="min_price"]');
    const maxPriceInput = document.querySelector('input[name="max_price"]');

    if (minPriceInput && maxPriceInput) {
        minPriceInput.addEventListener('input', function() {
            if (this.value && maxPriceInput.value && parseInt(this.value) > parseInt(maxPriceInput.value)) {
                maxPriceInput.value = this.value;
            }
        });

        maxPriceInput.addEventListener('input', function() {
            if (this.value && minPriceInput.value && parseInt(this.value) < parseInt(minPriceInput.value)) {
                minPriceInput.value = this.value;
            }
        });
    }

    // Newsletter Form
    const newsletterForm = document.querySelector('.newsletter form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[type="email"]');
            
            if (emailInput.value) {
                // TODO: Implement newsletter subscription
                alert('Cảm ơn bạn đã đăng ký nhận thông tin!');
                emailInput.value = '';
            }
        });
    }

    // Booking Form Validation
    const bookingForm = document.querySelector('#bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const loggedIn = bookingForm.dataset.loggedIn === '1';
            if (!loggedIn) {
                if (confirm('Bạn cần đăng nhập để đặt tour. Chuyển tới trang đăng nhập?')) {
                    window.location.href = (typeof APP_BASE_URL !== 'undefined' ? APP_BASE_URL : '/') + 'login';
                }
                return;
            }

            const numberOfPeople = parseInt(this.querySelector('input[name="number_of_people"]').value) || 0;
            const availableSlots = parseInt(this.querySelector('input[name="available_slots"]').value) || 0;

            if (numberOfPeople <= 0) {
                alert('Vui lòng nhập số lượng người hợp lệ.');
                return;
            }

            if (numberOfPeople > availableSlots) {
                alert('Số lượng người vượt quá số chỗ còn trống!');
                return;
            }

            const submitBtn = document.getElementById('bookingSubmitBtn');
            const btnText = document.getElementById('bookingBtnText');
            const spinner = document.getElementById('bookingBtnSpinner');
            const feedback = document.getElementById('bookingFeedback');

            if (submitBtn) submitBtn.disabled = true;
            if (btnText) btnText.textContent = 'Đang gửi...';
            if (spinner) spinner.style.display = 'inline-block';
            if (feedback) { feedback.style.display = 'none'; feedback.innerHTML = ''; }

            const formData = new FormData(bookingForm);
            const controller = new AbortController();
            const timeoutMs = 30000; // 30s (increase to accommodate transient DB locks)
            const timeoutId = setTimeout(() => controller.abort(), timeoutMs);

            (async () => {
                try {
                    const res = await fetch(bookingForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: controller.signal
                    });

                    clearTimeout(timeoutId);

                    const ct = res.headers.get('content-type') || '';
                    let data = null;
                    if (ct.indexOf('application/json') !== -1) {
                        data = await res.json();
                    } else {
                        data = { html: await res.text() };
                    }

                    if (data && data.success) {
                        if (feedback) {
                            feedback.style.display = 'block';
                            feedback.innerHTML = '<div class="alert alert-success">' + (data.message || 'Đặt tour thành công.') + '</div>';
                        }
                        // Update cart badge immediately if present
                        try {
                            const badge = document.getElementById('cartBadge');
                            if (badge) {
                                let n = parseInt(badge.textContent) || 0;
                                n = n + 1;
                                badge.textContent = n;
                                badge.style.display = '';
                            }
                        } catch (e) {}

                        // Follow server redirect (bookings page)
                        const redirectTo = data.redirect || ((typeof APP_BASE_URL !== 'undefined' ? APP_BASE_URL : '/') + 'bookings');
                        setTimeout(() => { window.location.href = redirectTo; }, 1000);
                        return;
                    }

                    // non-json fallback -> go to bookings
                    if (data && data.html) {
                        window.location.href = (typeof APP_BASE_URL !== 'undefined' ? APP_BASE_URL : '/') + 'bookings';
                        return;
                    }

                    // error
                    if (feedback) {
                        feedback.style.display = 'block';
                        feedback.innerHTML = '<div class="alert alert-danger">' + (data && data.message ? data.message : 'Đặt tour thất bại. Vui lòng thử lại.') + '</div>';
                    }
                } catch (err) {
                    // handle abort/timeouts and other errors
                    const msg = (err.name === 'AbortError') ? 'Yêu cầu đặt tour quá thời gian. Vui lòng thử lại.' : 'Lỗi mạng hoặc máy chủ. Vui lòng thử lại.';
                    if (feedback) {
                        feedback.style.display = 'block';
                        feedback.innerHTML = '<div class="alert alert-danger">' + msg + '</div>';
                    }
                } finally {
                    // restore button state if we didn't redirect
                    if (submitBtn) submitBtn.disabled = false;
                    if (btnText) btnText.textContent = 'Đặt ngay';
                    if (spinner) spinner.style.display = 'none';
                    try { clearTimeout(timeoutId); } catch (e) {}
                }
            })();
        });
    }

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Smooth scroll to anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Flash message auto-hide
    const flashMessage = document.querySelector('.alert');
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.opacity = '0';
            setTimeout(() => {
                flashMessage.remove();
            }, 300);
        }, 3000);
    }
});