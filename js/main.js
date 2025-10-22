// Velvet Vogue - Main JavaScript File

// DOM Content Loaded Event
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

// Initialize Application
function initializeApp() {
    initMobileMenu();
    initCartFunctionality();
    initProductFilters();
    initFormValidation();
    initScrollAnimations();
    updateCartCount();
    initImageLazyLoading();
    initContactForm();
    initFAQ();
    loadFeaturedProducts();
}

// Mobile Menu Toggle
function initMobileMenu() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            
            // Animate hamburger menu
            this.classList.toggle('active');
        });
    }
}

// Cart Functionality
function initCartFunctionality() {
    // Add to cart buttons
    const addToCartBtns = document.querySelectorAll('.add-to-cart');
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            const size = document.querySelector(`#size-${productId}`)?.value;
            const color = document.querySelector(`#color-${productId}`)?.value;
            const quantity = document.querySelector(`#quantity-${productId}`)?.value || 1;
            
            addToCart(productId, size, color, quantity);
        });
    });
    
    // Remove from cart buttons
    const removeFromCartBtns = document.querySelectorAll('.remove-from-cart');
    removeFromCartBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            removeFromCart(productId);
        });
    });
    
    // Update quantity buttons
    const updateQuantityBtns = document.querySelectorAll('.update-quantity');
    updateQuantityBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const newQuantity = this.parentNode.querySelector('.quantity-input').value;
            updateCartQuantity(productId, newQuantity);
        });
    });
}

// Add to Cart Function
function addToCart(productId, size, color, quantity) {
    // Determine the correct path to cart.php based on current location
    const cartPath = window.location.pathname.includes('/pages/') ? '../php/cart.php' : 'php/cart.php';
    
    const formData = new FormData();
    formData.append('action', 'add_to_cart');
    formData.append('product_id', productId);
    formData.append('size', size);
    formData.append('color', color);
    formData.append('quantity', quantity);
    
    fetch(cartPath, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product added to cart!', 'success');
            updateCartCount();
        } else {
            showNotification(data.message || 'Error adding product to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding product to cart', 'error');
    });
}

// Remove from Cart Function
function removeFromCart(productId) {
    // Determine the correct path to cart.php based on current location
    const cartPath = window.location.pathname.includes('/pages/') ? '../php/cart.php' : 'php/cart.php';
    
    const formData = new FormData();
    formData.append('action', 'remove_from_cart');
    formData.append('product_id', productId);
    
    fetch(cartPath, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product removed from cart!', 'success');
            updateCartCount();
            location.reload(); // Reload to update cart display
        } else {
            showNotification(data.message || 'Error removing product from cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error removing product from cart', 'error');
    });
}

// Update Cart Quantity
function updateCartQuantity(productId, quantity) {
    // Determine the correct path to cart.php based on current location
    const cartPath = window.location.pathname.includes('/pages/') ? '../php/cart.php' : 'php/cart.php';
    
    const formData = new FormData();
    formData.append('action', 'update_quantity');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    
    fetch(cartPath, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount();
            updateCartTotal();
        } else {
            showNotification(data.message || 'Error updating quantity', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating quantity', 'error');
    });
}

// Update Cart Count Display
function updateCartCount() {
    // Skip cart updates in admin area
    if (window.location.pathname.includes('/admin/')) {
        console.log('Skipping cart update in admin area');
        return;
    }
    
    // Determine the correct path to cart.php based on current location
    let cartPath;
    if (window.location.pathname.includes('/pages/')) {
        cartPath = '../php/cart.php';
    } else {
        cartPath = 'php/cart.php';
    }
    
    fetch(cartPath + '?action=get_count')
    .then(response => response.json())
    .then(data => {
        const cartCountElement = document.querySelector('.cart-count');
        if (cartCountElement) {
            cartCountElement.textContent = data.count || 0;
            cartCountElement.style.display = data.count > 0 ? 'flex' : 'none';
        }
    })
    .catch(error => {
        console.error('Error updating cart count:', error);
    });
}

// Update Cart Total
function updateCartTotal() {
    // Determine the correct path to cart.php based on current location
    const cartPath = window.location.pathname.includes('/pages/') ? '../php/cart.php' : 'php/cart.php';
    
    fetch(cartPath + '?action=get_total')
    .then(response => response.json())
    .then(data => {
        const cartTotalElement = document.querySelector('.cart-total');
        if (cartTotalElement) {
            cartTotalElement.textContent = '$' + (data.total || 0).toFixed(2);
        }
    })
    .catch(error => {
        console.error('Error updating cart total:', error);
    });
}

// Product Filters
function initProductFilters() {
    const filterForm = document.querySelector('#filter-form');
    if (filterForm) {
        const filterInputs = filterForm.querySelectorAll('select, input[type="range"]');
        
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                applyFilters();
            });
        });
    }
}

// Apply Product Filters
function applyFilters() {
    const formData = new FormData(document.querySelector('#filter-form'));
    
    fetch('php/filter_products.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        const productGrid = document.querySelector('.product-grid');
        if (productGrid) {
            productGrid.innerHTML = html;
            initCartFunctionality(); // Re-initialize cart buttons
        }
    })
    .catch(error => {
        console.error('Error applying filters:', error);
    });
}

// Form Validation
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

// Validate Form Function
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        const value = field.value.trim();
        const fieldGroup = field.closest('.form-group');
        
        // Remove existing error messages
        const existingError = fieldGroup.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        if (!value) {
            showFieldError(field, 'This field is required');
            isValid = false;
        } else if (field.type === 'email' && !isValidEmail(value)) {
            showFieldError(field, 'Please enter a valid email address');
            isValid = false;
        } else if (field.type === 'password' && value.length < 6) {
            showFieldError(field, 'Password must be at least 6 characters long');
            isValid = false;
        }
    });
    
    // Check password confirmation
    const password = form.querySelector('input[name="password"]');
    const confirmPassword = form.querySelector('input[name="confirm_password"]');
    
    if (password && confirmPassword && password.value !== confirmPassword.value) {
        showFieldError(confirmPassword, 'Passwords do not match');
        isValid = false;
    }
    
    return isValid;
}

// Show Field Error
function showFieldError(field, message) {
    const fieldGroup = field.closest('.form-group');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.color = 'var(--error-color)';
    errorDiv.style.fontSize = '0.9rem';
    errorDiv.style.marginTop = '0.25rem';
    errorDiv.textContent = message;
    
    fieldGroup.appendChild(errorDiv);
    field.focus();
}

// Email Validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Show Notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = `notification alert alert-${type}`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.style.animation = 'slideInRight 0.3s ease-out';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease-in';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

// Scroll Animations
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);
    
    const animateElements = document.querySelectorAll('.card, .product-card, .hero-content');
    animateElements.forEach(element => {
        observer.observe(element);
    });
}

// Lazy Loading for Images
function initImageLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Search Functionality
function searchProducts(query) {
    fetch(`php/search.php?q=${encodeURIComponent(query)}`)
    .then(response => response.json())
    .then(data => {
        displaySearchResults(data.products);
    })
    .catch(error => {
        console.error('Search error:', error);
    });
}

// Display Search Results
function displaySearchResults(products) {
    const resultsContainer = document.querySelector('.search-results');
    if (!resultsContainer) return;
    
    if (products.length === 0) {
        resultsContainer.innerHTML = '<p class="text-center">No products found.</p>';
        return;
    }
    
    const productsHTML = products.map(product => `
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card product-card">
                <img src="${product.image_url}" class="card-img" alt="${product.name}">
                <div class="card-body">
                    <h5 class="card-title">${product.name}</h5>
                    <p class="card-text">${product.description}</p>
                    <div class="card-price">$${product.price}</div>
                    <button class="btn btn-primary add-to-cart" data-product-id="${product.id}">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>
    `).join('');
    
    resultsContainer.innerHTML = productsHTML;
    initCartFunctionality(); // Re-initialize cart functionality
}

// Wishlist Functionality
function toggleWishlist(productId) {
    const formData = new FormData();
    formData.append('action', 'toggle_wishlist');
    formData.append('product_id', productId);
    
    fetch('php/wishlist.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const wishlistBtn = document.querySelector(`[data-wishlist-id="${productId}"]`);
            if (wishlistBtn) {
                wishlistBtn.classList.toggle('active');
                const icon = wishlistBtn.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fas');
                    icon.classList.toggle('far');
                }
            }
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message || 'Error updating wishlist', 'error');
        }
    })
    .catch(error => {
        console.error('Wishlist error:', error);
        showNotification('Error updating wishlist', 'error');
    });
}

// Price Range Slider
function initPriceRange() {
    const priceRange = document.querySelector('#price-range');
    const priceDisplay = document.querySelector('#price-display');
    
    if (priceRange && priceDisplay) {
        priceRange.addEventListener('input', function() {
            priceDisplay.textContent = `$0 - $${this.value}`;
        });
    }
}

// Auto-save Cart (for logged-in users)
function autoSaveCart() {
    setInterval(() => {
        fetch('php/cart.php?action=auto_save', {
            method: 'POST'
        });
    }, 30000); // Save every 30 seconds
}

// Initialize auto-save if user is logged in
if (document.querySelector('[data-user-logged-in]')) {
    autoSaveCart();
}

// Smooth Scrolling for Anchor Links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        // Skip empty or invalid selectors
        if (!href || href === '#' || href.length <= 1) {
            return;
        }
        
        e.preventDefault();
        const target = document.querySelector(href);
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// CSS Animations for notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .lazy {
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .lazy.loaded {
        opacity: 1;
    }
`;
document.head.appendChild(style);

// Contact form functionality
function initContactForm() {
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', handleContactSubmit);
    }
}

function handleContactSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'contact');
    
    // Show loading state
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Sending...';
    submitButton.disabled = true;
    
    fetch('php/contact.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Message sent successfully!', 'success');
            form.reset();
        } else {
            showNotification(data.message || 'Failed to send message', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        submitButton.textContent = originalText;
        submitButton.disabled = false;
    });
}

// FAQ functionality
function initFAQ() {
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        if (question) {
            question.addEventListener('click', () => {
                const answer = item.querySelector('.faq-answer');
                const isOpen = item.classList.contains('active');
                
                // Close all other FAQ items
                faqItems.forEach(otherItem => {
                    otherItem.classList.remove('active');
                    const otherAnswer = otherItem.querySelector('.faq-answer');
                    if (otherAnswer) {
                        otherAnswer.style.maxHeight = null;
                    }
                });
                
                // Toggle current item
                if (!isOpen) {
                    item.classList.add('active');
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                }
            });
        }
    });
}

// Load Featured Products on Homepage
function loadFeaturedProducts() {
    const featuredProductsContainer = document.getElementById('featured-products');
    
    // Only load featured products if we're on a page that has the featured products container
    if (!featuredProductsContainer) {
        return;
    }
    
    // Determine the correct path to products.php based on current location
    const productsPath = window.location.pathname.includes('/pages/') ? '../php/products.php' : 'php/products.php';
    
    // Show loading spinner
    featuredProductsContainer.innerHTML = '<div class="spinner"></div>';
    
    // Fetch featured products
    fetch(`${productsPath}?action=get_all&featured=true&limit=4`)
        .then(response => response.json())
        .then(products => {
            if (products && products.length > 0) {
                displayFeaturedProducts(products, featuredProductsContainer);
            } else {
                displayNoFeaturedProducts(featuredProductsContainer);
            }
        })
        .catch(error => {
            console.error('Error loading featured products:', error);
            displayNoFeaturedProducts(featuredProductsContainer);
        });
}

// Display Featured Products
function displayFeaturedProducts(products, container) {
    let html = '';
    
    products.forEach(product => {
        // Handle different size and color formats
        let sizes = product.sizes;
        let colors = product.colors;
        
        // Parse JSON if it's a JSON string
        if (typeof sizes === 'string' && sizes.startsWith('[')) {
            try {
                sizes = JSON.parse(sizes);
            } catch (e) {
                sizes = sizes.split(',').map(s => s.trim());
            }
        } else if (typeof sizes === 'string') {
            sizes = sizes.split(',').map(s => s.trim());
        }
        
        if (typeof colors === 'string' && colors.startsWith('[')) {
            try {
                colors = JSON.parse(colors);
            } catch (e) {
                colors = colors.split(',').map(c => c.trim());
            }
        } else if (typeof colors === 'string') {
            colors = colors.split(',').map(c => c.trim());
        }
        
        // Create size options
        const sizeOptions = Array.isArray(sizes) ? sizes.map(size => 
            `<option value="${size}">${size}</option>`
        ).join('') : '';
        
        // Create color options
        const colorOptions = Array.isArray(colors) ? colors.map(color => 
            `<option value="${color}">${color}</option>`
        ).join('') : '';
        
        // Calculate display price
        const originalPrice = parseFloat(product.price);
        const salePrice = product.sale_price ? parseFloat(product.sale_price) : null;
        const displayPrice = salePrice && product.is_on_sale ? salePrice : originalPrice;
        
        // Create badges
        let badges = '';
        if (product.is_new_arrival) badges += '<span class="product-badge new">New</span>';
        if (product.is_on_sale) badges += '<span class="product-badge sale">Sale</span>';
        if (product.is_featured) badges += '<span class="product-badge featured">Featured</span>';
        
        html += `
            <div class="product-card" data-product-id="${product.id}">
                <div class="product-image">
                    <img src="${product.image_url}" alt="${product.name}" loading="lazy">
                    <div class="product-badges">${badges}</div>
                    <div class="product-overlay">
                        <button class="btn btn-primary add-to-cart" data-product-id="${product.id}">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <h3 class="product-title">${product.name}</h3>
                    <p class="product-category">${product.category_name || ''}</p>
                    <div class="product-price">
                        ${product.is_on_sale && salePrice ? 
                            `<span class="sale-price">$${displayPrice.toFixed(2)}</span>
                             <span class="original-price">$${originalPrice.toFixed(2)}</span>` : 
                            `<span class="price">$${displayPrice.toFixed(2)}</span>`
                        }
                    </div>
                    <div class="product-options">
                        ${sizeOptions ? `
                            <select id="size-${product.id}" class="size-select">
                                <option value="">Size</option>
                                ${sizeOptions}
                            </select>
                        ` : ''}
                        ${colorOptions ? `
                            <select id="color-${product.id}" class="color-select">
                                <option value="">Color</option>
                                ${colorOptions}
                            </select>
                        ` : ''}
                        <input type="number" id="quantity-${product.id}" class="quantity-input" value="1" min="1" max="${product.stock_quantity}">
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Re-initialize cart functionality for the new products
    initCartFunctionality();
}

// Display No Featured Products Message
function displayNoFeaturedProducts(container) {
    container.innerHTML = `
        <div class="no-products">
            <div class="no-products-content">
                <i class="fas fa-shopping-bag"></i>
                <h3>No Featured Products Found</h3>
                <p>We're updating our featured collection. Please check back soon!</p>
                <a href="pages/products.html" class="btn btn-primary">View All Products</a>
            </div>
        </div>
    `;
}