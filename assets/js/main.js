document.addEventListener('DOMContentLoaded', () => {
    // Mobile Navigation Toggle
    const menuToggle = document.getElementById('mobile-menu-toggle');
    const mainNav = document.getElementById('main-nav');
    
    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', () => {
            menuToggle.classList.toggle('active');
            mainNav.classList.toggle('active');
        });
    }

    // Smooth Scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                e.preventDefault();
                
                // Close mobile menu if open
                if (mainNav.classList.contains('active')) {
                    menuToggle.classList.remove('active');
                    mainNav.classList.remove('active');
                }

                window.scrollTo({
                    top: targetElement.offsetTop - 80, // Offset for fixed header
                    behavior: 'smooth'
                });
            }
        });
    });

    // Simple sticky header effect on scroll
    const header = document.querySelector('.site-header');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
});

    // AJAX for Like and Bookmark forms using Event Delegation for bulletproof reliability
    document.addEventListener('submit', async (e) => {
        const form = e.target;
        if (!form || form.tagName !== 'FORM') return;

        const actionAttr = form.getAttribute('action');
        if (actionAttr === '/like_handler.php' || actionAttr === '/bookmark_handler.php') {
            e.preventDefault();
            
            const formData = new FormData(form);
            formData.append('ajax', '1');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (submitBtn) {
                // Basic debouncing/visual feedback
                submitBtn.style.opacity = '0.5';
                submitBtn.style.pointerEvents = 'none';
            }

            try {
                // Use the form's action attribute to ensure exact pathing
                const response = await fetch(actionAttr, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }

                if (response.status === 401) {
                    window.location.href = '/auth/login.php';
                    return;
                }

                const data = await response.json();
                
                if (data.status === 'success') {
                    // Update form action hidden input
                    const actionInput = form.querySelector('input[name="action"]');
                    if (actionInput) actionInput.value = data.new_action;
                    
                    if (submitBtn) {
                        // Update button UI
                        const isLike = actionAttr === '/like_handler.php';
                        let hasLiked = false;
                        let hasBookmarked = false;
                        
                        if (isLike) {
                            hasLiked = data.new_action === 'unlike'; // If new action is unlike, it means we just liked it
                            submitBtn.className = `btn ${hasLiked ? 'btn-primary' : 'btn-outline'}`;
                            submitBtn.innerHTML = `❤️ ${data.new_count}`;
                            submitBtn.title = hasLiked ? 'Unlike' : 'Like';
                            submitBtn.setAttribute('aria-label', submitBtn.title);
                        } else {
                            hasBookmarked = data.new_action === 'remove';
                            submitBtn.className = `btn ${hasBookmarked ? 'btn-secondary' : 'btn-outline'}`;
                            submitBtn.innerHTML = `🔖 ${data.new_count}`;
                            submitBtn.title = hasBookmarked ? 'Remove Bookmark' : 'Bookmark';
                            submitBtn.setAttribute('aria-label', submitBtn.title);
                        }
                        
                        // If we are on the dashboard and we just unliked/removed a bookmark, we should hide the card
                        if (window.location.pathname === '/dashboard.php' && (!hasLiked && isLike || !hasBookmarked && !isLike)) {
                            const card = form.closest('.post-card');
                            if (card) {
                                card.style.display = 'none';
                            }
                        }
                    }
                } else {
                    console.error('API Error:', data.message);
                }
            } catch (error) {
                console.error('AJAX error:', error);
            } finally {
                if (submitBtn) {
                    submitBtn.style.opacity = '1';
                    submitBtn.style.pointerEvents = 'auto';
                }
            }
        }
    });
