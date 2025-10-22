// assets/js/home.js

// Fetch and Display Products from API
async function loadProducts() {
    try {
        const response = await fetch('includes/get_products.php');
        const data = await response.json();
        
        if (data.success) {
            displayProducts(data.data.products);
        } else {
            displayProducts([]);
        }
    } catch (error) {
        console.error('Error loading products:', error);
        displayProducts([]);
    }
}

function displayProducts(products) {
    const productList = document.getElementById('productList');
    
    if (!products || products.length === 0) {
        productList.innerHTML = `
            <div class="no-products">
                <h3>No Products Available</h3>
                <p>We're working on adding amazing products. Check back soon!</p>
            </div>
        `;
        return;
    }
    
    // Show only 6 featured products on homepage
    const featuredProducts = products.slice(0, 6);
    
    productList.innerHTML = featuredProducts.map(product => `
        <div class="product-card">
            ${product.id % 3 === 0 ? '<span class="product-badge">New</span>' : ''}
            ${product.id % 5 === 0 ? '<span class="product-badge">Sale</span>' : ''}
            <div class="product-image">
                ${product.image ? 
                    `<img src="uploads/${product.image}" alt="${escapeHtml(product.name)}">` :
                    `<div class="no-image">
                        <i class="fas fa-image"></i>
                    </div>`
                }
            </div>
            <div class="product-content">
                <h3 class="product-title">${escapeHtml(product.name)}</h3>
                <div class="product-price">$${parseFloat(product.price).toFixed(2)}</div>
                <p class="product-description">${escapeHtml(product.description || 'No description available.')}</p>
                <div class="product-actions">
                    <button class="btn-cart" onclick="addToCart(${JSON.stringify(product).replace(/"/g, '&quot;')})">
                        <i class="fas fa-shopping-cart"></i> 
                        <span>Add to Cart</span>
                    </button>
                    <button class="btn-view" onclick="viewProduct(${product.id})" title="View Product Details">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// Helper function to escape HTML
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Function to view product details
function viewProduct(productId) {
    window.location.href = `product_details.php?id=${productId}`;
}

// Newsletter form submission
const newsletterForm = document.getElementById('newsletterForm');
if (newsletterForm) {
    newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.querySelector('.newsletter-input').value;
        if (window.userAuth) {
            window.userAuth.showNotification('Thank you for subscribing with: ' + email);
        } else {
            alert('Thank you for subscribing with: ' + email);
        }
        this.reset();
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
});