/**
 * FRLimousine - JavaScript Élégant & Professionnel
 * ================================================
 * Interactions essentielles pour un site haut de gamme
 */

// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {

    // ============================================
    // MENU MOBILE - Supprimé
    // ============================================
    // Le menu burger a été supprimé du site

    // ============================================
    // SMOOTH SCROLLING - Navigation Fluide
    // ============================================

    function initSmoothScrolling() {
        const anchors = document.querySelectorAll('a[href^="#"]');

        anchors.forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');

                // Ignorer les liens juste "#"
                if (href === '#') return;

                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();

                    // Calculer la position avec la hauteur du header
                    const headerHeight = document.querySelector('#header').offsetHeight;
                    const targetPosition = target.offsetTop - headerHeight - 20;

                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });

                }
            });
        });
    }

    // ============================================
    // SLIDER D'AVIS CLIENTS - Automatique
    // ============================================

    function initTestimonialsSlider() {
        const testimonials = document.querySelectorAll('.testimonial-card');
        const dots = document.querySelectorAll('.dot');
        const prevBtn = document.querySelector('.prev');
        const nextBtn = document.querySelector('.next');
        let currentIndex = 0;
        let autoSlideInterval;

        function showTestimonial(index) {
            // Masquer tous les témoignages
            testimonials.forEach(testimonial => {
                testimonial.classList.remove('active');
            });

            // Retirer la classe active de tous les dots
            dots.forEach(dot => {
                dot.classList.remove('active');
            });

            // Afficher le témoignage actuel
            if (testimonials[index]) {
                testimonials[index].classList.add('active');
            }

            // Activer le dot correspondant
            if (dots[index]) {
                dots[index].classList.add('active');
            }

            currentIndex = index;
        }

        function nextTestimonial() {
            const nextIndex = (currentIndex + 1) % testimonials.length;
            showTestimonial(nextIndex);
        }

        function previousTestimonial() {
            const prevIndex = (currentIndex - 1 + testimonials.length) % testimonials.length;
            showTestimonial(prevIndex);
        }

        function goToTestimonial(index) {
            showTestimonial(index);
        }

        function startAutoSlide() {
            autoSlideInterval = setInterval(nextTestimonial, 10000); // 10 secondes
        }

        function stopAutoSlide() {
            clearInterval(autoSlideInterval);
        }

        // Navigation avec les boutons
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                stopAutoSlide();
                nextTestimonial();
                startAutoSlide();
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                stopAutoSlide();
                previousTestimonial();
                startAutoSlide();
            });
        }

        // Navigation avec les dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                stopAutoSlide();
                goToTestimonial(index);
                startAutoSlide();
            });
        });

        // Pause auto slide au survol
        const sliderContainer = document.querySelector('.testimonials-slider');
        if (sliderContainer) {
            sliderContainer.addEventListener('mouseenter', stopAutoSlide);
            sliderContainer.addEventListener('mouseleave', startAutoSlide);
        }

        // Démarrer le slider automatique
        if (testimonials.length > 0) {
            showTestimonial(0);
            startAutoSlide();
        }
    }

    // ============================================
    // ANIMATIONS AU SCROLL - Élégantes
    // ============================================

    function initScrollAnimations() {
        // Éléments à animer
        const animateElements = document.querySelectorAll('.service-card, .vehicle-card, .contact-method, .testimonial-card');

        // Options de l'observer
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        // Créer l'observer
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-on-scroll', 'in-view');
                }
            });
        }, observerOptions);

        // Observer tous les éléments
        animateElements.forEach(element => {
            observer.observe(element);
        });
    }

    // ============================================
    // FORMULAIRE - Gestion Professionnelle
    // ============================================

    function initFormHandling() {
        const forms = document.querySelectorAll('form');

        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const submitBtn = form.querySelector('.submit-btn');
                if (submitBtn) {
                    // Animation de chargement
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
                    submitBtn.disabled = true;

                    // Simulation d'envoi
                    setTimeout(() => {
                        submitBtn.innerHTML = '<i class="fas fa-check"></i> Demande envoyée !';
                        submitBtn.style.background = '#28a745';

                        // Reset après 3 secondes
                        setTimeout(() => {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                            submitBtn.style.background = '';
                            form.reset();
                        }, 3000);
                    }, 2000);
                }
            });
        });
    }

    // ============================================
    // HEADER SCROLL - Effet Subtil
    // ============================================

    function initHeaderScroll() {
        const header = document.querySelector('#header');
        let lastScrollTop = 0;

        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            lastScrollTop = scrollTop;
        });
    }

    // ============================================
    // IMAGES - Optimisation Élégante
    // ============================================

    function initImageOptimization() {
        const images = document.querySelectorAll('img');

        images.forEach(function(img) {
            // Lazy loading
            img.loading = 'lazy';

            // Gestion d'erreur simple
            img.addEventListener('error', function() {
                this.style.display = 'none';
                console.warn('Image failed to load:', this.src);
            });
        });
    }

    // ============================================
    // INITIALISATION - Démarrage Propre
    // ============================================

    // Initialiser toutes les fonctionnalités
    // initMobileMenu(); // Menu supprimé
    initSmoothScrolling();
    initTestimonialsSlider();
    initScrollAnimations();
    initFormHandling();
    initHeaderScroll();
    initImageOptimization();

    // Retirer la classe preload après chargement
    window.addEventListener('load', function() {
        document.body.classList.remove('is-preload');
    });

    console.log('🚀 FRLimousine website loaded - Élégant & Professionnel');
});