/**
 * Dashboard JavaScript - Enhanced Version
 * ERP Pergudangan System
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ========== SIDEBAR TOGGLE - ENHANCED ==========
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const mainWrapper = document.querySelector('.main-wrapper');
    const topbar = document.querySelector('.topbar');
    
    // Toggle sidebar on button click
    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
        });
    }

    // Close sidebar when clicking outside on mobile/tablet
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });

    // Close sidebar when clicking on menu items on mobile/tablet
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(function(item) {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('active');
            }
        });
    });

    // Close sidebar with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
        }
    });

    // Auto hide sidebar on resize to mobile
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('active');
            }
        }, 250);
    });

    // ========== AUTO-HIDE ALERTS ==========
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // ========== ACTIVE MENU HIGHLIGHT ==========
    const currentPath = window.location.pathname;
    
    menuItems.forEach(function(item) {
        const href = item.getAttribute('href');
        if (href && currentPath.includes(href)) {
            // Remove active from all items
            menuItems.forEach(mi => mi.classList.remove('active'));
            // Add active to current item
            item.classList.add('active');
        }
    });

    // ========== STAT CARDS ANIMATION ==========
    const statCards = document.querySelectorAll('.stat-card');
    if (statCards.length > 0) {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry, index) {
                if (entry.isIntersecting) {
                    setTimeout(function() {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 100);
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        statCards.forEach(function(card) {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.5s ease';
            observer.observe(card);
        });
    }

    // ========== ANIMATE STAT VALUES ==========
    function animateValue(element, start, end, duration) {
        let startTimestamp = null;
        const step = function(timestamp) {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            element.textContent = value.toLocaleString();
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    // Animate stat values on page load
    const statValues = document.querySelectorAll('.stat-value');
    statValues.forEach(function(statValue) {
        const finalValue = parseInt(statValue.textContent.replace(/,/g, ''));
        if (!isNaN(finalValue)) {
            statValue.textContent = '0';
            setTimeout(function() {
                animateValue(statValue, 0, finalValue, 1000);
            }, 300);
        }
    });

    // ========== TABLE ROW CLICK ==========
    const tableRows = document.querySelectorAll('.data-table tbody tr');
    tableRows.forEach(function(row) {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function() {
            console.log('Row clicked:', this);
            // Add your navigation logic here
        });
    });

    // ========== NOTIFICATION CLICK ==========
    const notificationBtn = document.querySelector('.notification-btn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function() {
            alert('Notifications feature coming soon!');
        });
    }

    // ========== PREVENT DOUBLE SUBMIT ==========
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Loading...';
                
                // Re-enable after 3 seconds (in case of error)
                setTimeout(function() {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }, 3000);
            }
        });
    });

    // ========== SMOOTH SCROLL ==========
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
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

    // ========== USER ACTIVITY TRACKING ==========
    let lastActivity = Date.now();
    
    function updateActivity() {
        lastActivity = Date.now();
    }

    document.addEventListener('mousemove', updateActivity);
    document.addEventListener('keypress', updateActivity);
    document.addEventListener('scroll', updateActivity);
    document.addEventListener('click', updateActivity);

    // Check for inactivity every minute
    setInterval(function() {
        const inactiveTime = Date.now() - lastActivity;
        const inactiveMinutes = Math.floor(inactiveTime / 60000);
        
        // Show warning after 25 minutes of inactivity
        if (inactiveMinutes >= 25 && inactiveMinutes < 30) {
            console.warn('User inactive for ' + inactiveMinutes + ' minutes');
        }
        
        // Auto-logout after 30 minutes of inactivity
        if (inactiveMinutes >= 30) {
            console.log('Auto-logout due to inactivity');
            // Uncomment to enable auto-logout
            // window.location.href = '/logout';
        }
    }, 60000);

    // ========== MOBILE TOUCH SWIPE FOR SIDEBAR ==========
    let touchStartX = 0;
    let touchEndX = 0;

    document.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    });

    document.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });

    function handleSwipe() {
        if (window.innerWidth <= 768) {
            // Swipe right to open sidebar (from left edge)
            if (touchStartX < 50 && touchEndX > touchStartX + 50) {
                sidebar.classList.add('active');
            }
            // Swipe left to close sidebar
            if (touchEndX < touchStartX - 50 && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        }
    }

    // ========== AUTO-HIDE NAVBAR ON SCROLL (Optional) ==========
    if (topbar) {
        let lastScrollTop = 0;
        let isScrolling = false;
        const scrollThreshold = 5; // Minimum scroll to trigger
        
        window.addEventListener('scroll', function() {
            if (!isScrolling) {
                window.requestAnimationFrame(function() {
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    
                    // Only hide if scrolled more than threshold
                    if (Math.abs(scrollTop - lastScrollTop) > scrollThreshold) {
                        // Scroll down - hide navbar
                        if (scrollTop > lastScrollTop && scrollTop > 100) {
                            topbar.style.transform = 'translateY(-100%)';
                        } 
                        // Scroll up - show navbar
                        else {
                            topbar.style.transform = 'translateY(0)';
                        }
                    }
                    
                    // Always show navbar when at top
                    if (scrollTop <= 10) {
                        topbar.style.transform = 'translateY(0)';
                    }
                    
                    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
                    isScrolling = false;
                });
                
                isScrolling = true;
            }
        });
    }

    // ========== RESPONSIVE HANDLING ==========
    function handleResponsive() {
        const width = window.innerWidth;
        
        // Auto remove active class when switching to desktop
        if (width > 768 && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
        }
        
        // Update body class for different breakpoints
        document.body.classList.remove('mobile', 'tablet', 'desktop');
        if (width <= 480) {
            document.body.classList.add('mobile');
        } else if (width <= 768) {
            document.body.classList.add('tablet');
        } else {
            document.body.classList.add('desktop');
        }
    }

    // Run on load
    handleResponsive();

    // Run on resize with debounce
    let resizeDebounce;
    window.addEventListener('resize', function() {
        clearTimeout(resizeDebounce);
        resizeDebounce = setTimeout(handleResponsive, 100);
    });

    // ========== CONSOLE INFO ==========
    console.log('%c🚀 Dashboard initialized successfully!', 'color: #e89a6b; font-size: 14px; font-weight: bold;');
    console.log('%c📱 Responsive breakpoints:', 'color: #6ba8d8; font-weight: bold;');
    console.log('   Mobile: < 480px');
    console.log('   Tablet: 481px - 768px');
    console.log('   Desktop: > 768px');
    console.log('%c⌨️  Keyboard shortcuts:', 'color: #52c97e; font-weight: bold;');
    console.log('   ESC: Close sidebar');
    console.log('%c📜 Scroll Features:', 'color: #f0a878; font-weight: bold;');
    console.log('   Auto-hide navbar on scroll (Optional)');
});