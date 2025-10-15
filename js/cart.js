// Cart functionality - Shared across all pages
class CartSystem {
    constructor() {
        this.cart = this.getCart();
        this.init();
    }

    init() {
        this.updateCartCount();
    }

    getCart() {
        try {
            return JSON.parse(localStorage.getItem('cart')) || [];
        } catch (e) {
            console.error('Error reading cart from localStorage:', e);
            return [];
        }
    }

    saveCart() {
        try {
            localStorage.setItem('cart', JSON.stringify(this.cart));
        } catch (e) {
            console.error('Error saving cart to localStorage:', e);
        }
    }

    addToCart(product) {
        // Ensure product has all required fields
        const cartProduct = {
            id: Number(product.id),
            name: String(product.name),
            price: Number(product.price),
            image: product.image || null,
            quantity: 1
        };

        const existingItem = this.cart.find(item => item.id === cartProduct.id);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            this.cart.push(cartProduct);
        }
        
        this.saveCart();
        this.updateCartCount();
        this.showNotification('Product added to cart!');
        this.dispatchCartUpdateEvent(); // Dispatch event
        return true;
    }

    removeFromCart(productId) {
        const productIdNum = Number(productId);
        this.cart = this.cart.filter(item => item.id !== productIdNum);
        this.saveCart();
        this.updateCartCount();
        this.showNotification('Product removed from cart!');
        this.dispatchCartUpdateEvent(); // Dispatch event
        return true;
    }

    updateQuantity(productId, newQuantity) {
        const productIdNum = Number(productId);
        const quantity = Number(newQuantity);
        
        if (quantity < 1) {
            return this.removeFromCart(productIdNum);
        }
        
        const item = this.cart.find(item => item.id === productIdNum);
        if (item) {
            item.quantity = quantity;
            this.saveCart();
            this.updateCartCount();
            this.dispatchCartUpdateEvent(); // Dispatch event
            return true;
        }
        return false;
    }

    getTotalItems() {
        return this.cart.reduce((sum, item) => sum + item.quantity, 0);
    }

    getSubtotal() {
        return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    }

    clearCart() {
        this.cart = [];
        this.saveCart();
        this.updateCartCount();
        this.dispatchCartUpdateEvent(); // Dispatch event
    }

    updateCartCount() {
        const cartCountElements = document.querySelectorAll('.cart-count');
        const totalItems = this.getTotalItems();
        
        cartCountElements.forEach(element => {
            if (element) {
                element.textContent = totalItems;
            }
        });
    }

    showNotification(message) {
        // Remove existing notification
        const existingNotification = document.getElementById('cartNotification');
        if (existingNotification) {
            existingNotification.remove();
        }

        // Create new notification
        const notification = document.createElement('div');
        notification.id = 'cartNotification';
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 10000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            max-width: 300px;
        `;

        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-check-circle"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Animate out and remove
        setTimeout(() => {
            notification.style.transform = 'translateX(400px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }, 3000);
    }

    // Method to load and render the cart page
    loadCartPage() {
        const cartItems = document.getElementById('cartItems');
        const cartSummary = document.getElementById('cartSummary');
        
        if (!cartItems) return; // Not on cart page
        
        console.log('Loading cart page, items:', this.cart);
        
        if (this.cart.length === 0) {
            cartItems.innerHTML = `
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Your cart is empty</h3>
                    <p>Looks like you haven't added any items to your cart yet.</p>
                    <a href="product_page.html" class="btn-shop">Start Shopping</a>
                </div>
            `;
            if (cartSummary) cartSummary.innerHTML = '';
            return;
        }
        
        // Calculate totals
        const subtotal = this.getSubtotal();
        const shipping = subtotal > 50 ? 0 : 5.99;
        const tax = subtotal * 0.08;
        const total = subtotal + shipping + tax;
        
        // Render cart items
        cartItems.innerHTML = `
            <div class="cart-header">
                <h2 class="cart-title">Shopping Cart</h2>
                <span class="cart-count-badge">${this.cart.length} ${this.cart.length === 1 ? 'item' : 'items'}</span>
            </div>
            ${this.cart.map(item => `
                <div class="cart-item" data-id="${item.id}">
                    <div class="item-image">
                        ${item.image ? 
                            `<img src="../uploads/${item.image}" alt="${item.name}" 
                                  onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">` : 
                            ''
                        }
                        <div class="no-image" style="${item.image ? 'display: none;' : ''}">
                            <i class="fas fa-image"></i>
                        </div>
                    </div>
                    <div class="item-details">
                        <h3 class="item-name">${this.escapeHtml(item.name)}</h3>
                        <div class="item-price">$${parseFloat(item.price).toFixed(2)}</div>
                        <div class="item-actions">
                            <div class="quantity-controls">
                                <button class="quantity-btn minus-btn" onclick="cartSystem.updateQuantity(${item.id}, ${item.quantity - 1})">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="quantity-input" value="${item.quantity}" min="1" 
                                       onchange="cartSystem.updateQuantityInput(${item.id}, this)">
                                <button class="quantity-btn plus-btn" onclick="cartSystem.updateQuantity(${item.id}, ${item.quantity + 1})">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <button class="remove-btn" onclick="cartSystem.removeFromCart(${item.id})">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                    <div class="item-total">$${(parseFloat(item.price) * item.quantity).toFixed(2)}</div>
                </div>
            `).join('')}
        `;
        
        // Render cart summary
        if (cartSummary) {
            cartSummary.innerHTML = `
                <h2 class="summary-title">Order Summary</h2>
                <div class="summary-row">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value">$${subtotal.toFixed(2)}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Shipping</span>
                    <span class="summary-value">${shipping === 0 ? 'Free' : '$' + shipping.toFixed(2)}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Tax</span>
                    <span class="summary-value">$${tax.toFixed(2)}</span>
                </div>
                <div class="summary-total">
                    <span class="total-label">Total</span>
                    <span class="total-value">$${total.toFixed(2)}</span>
                </div>
                <button class="checkout-btn" onclick="cartSystem.proceedToCheckout()">Proceed to Checkout</button>
                <a href="product_page.html" class="continue-shopping">Continue Shopping</a>
            `;
        }
    }

    // Method to handle input field changes
    updateQuantityInput(productId, inputElement) {
        const newQuantity = parseInt(inputElement.value) || 1;
        this.updateQuantity(productId, newQuantity);
    }

    // Method to proceed to checkout
    proceedToCheckout() {
        if (this.cart.length === 0) {
            alert('Your cart is empty. Add some items before checking out.');
            return;
        }
        window.location.href = 'checkout.html';
    }

    // Helper method to escape HTML
    escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Dispatch cart update event
    dispatchCartUpdateEvent() {
        const event = new CustomEvent('cartUpdated', {
            detail: {
                cart: this.cart,
                totalItems: this.getTotalItems(),
                subtotal: this.getSubtotal()
            }
        });
        window.dispatchEvent(event);
    }
}

// Initialize cart system
const cartSystem = new CartSystem();

// Make functions globally available
function addToCart(product) {
    return cartSystem.addToCart(product);
}

function removeFromCart(productId) {
    return cartSystem.removeFromCart(productId);
}

function updateQuantity(productId, newQuantity) {
    return cartSystem.updateQuantity(productId, newQuantity);
}

function getCart() {
    return cartSystem.getCart();
}

function clearCart() {
    return cartSystem.clearCart();
}

// Debug function
function debugCart() {
    console.log('Current Cart:', cartSystem.getCart());
    console.log('LocalStorage:', localStorage.getItem('cart'));
}