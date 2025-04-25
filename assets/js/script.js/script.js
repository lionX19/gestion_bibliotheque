// Global app data structure
const appData = {
    products: [],
    movements: [],
    nextProductId: 1,
    nextMovementId: 1
};

// DOM ready event
document.addEventListener('DOMContentLoaded', function() {
    // Initialize app
    initializeApp();
    
    // Setup event listeners
    setupEventListeners();
});

// Initialize app with sample data (if needed)
function initializeApp() {
    // Load data from localStorage if available
    loadData();
    
    // If no products are loaded, add sample data
    if (appData.products.length === 0) {
        // Sample products
        const sampleProducts = [
            { id: 1, reference: 'PRD001', name: 'Ordinateur portable', category: 'Informatique', price: 899.99, quantity: 15, threshold: 5 },
            { id: 2, reference: 'PRD002', name: 'Smartphone', category: 'Téléphonie', price: 499.99, quantity: 8, threshold: 10 },
            { id: 3, reference: 'PRD003', name: 'Imprimante', category: 'Informatique', price: 149.99, quantity: 3, threshold: 5 },
            { id: 4, reference: 'PRD004', name: 'Écran 24"', category: 'Informatique', price: 199.99, quantity: 0, threshold: 3 },
            { id: 5, reference: 'PRD005', name: 'Clavier sans fil', category: 'Accessoires', price: 39.99, quantity: 20, threshold: 8 }
        ];
            
        // Sample movements
        const sampleMovements = [
            { id: 1, productId: 1, type: 'entry', quantity: 5, date: '2023-06-10', comment: 'Réapprovisionnement fournisseur A' },
            { id: 2, productId: 2, type: 'exit', quantity: 2, date: '2023-06-11', comment: 'Vente client B' },
            { id: 3, productId: 3, type: 'entry', quantity: 10, date: '2023-06-12', comment: 'Livraison initiale' },
            { id: 4, productId: 4, type: 'exit', quantity: 3, date: '2023-06-13', comment: 'Transfert à la succursale C' },
            { id: 5, productId: 5, type: 'entry', quantity: 15, date: '2023-06-14', comment: 'Réception commande fournisseur D' }
        ];
            
        appData.products = sampleProducts;
        appData.movements = sampleMovements;
        appData.nextProductId = 6;
        appData.nextMovementId = 6;
        
        // Save to localStorage
        saveData();
    }
}

// Setup global event listeners
function setupEventListeners() {
    // Mobile menu toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('show');
        });
    }
    
    // Search functionality
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterProducts(searchTerm);
        });
    }
    
    // Add product modal
    const addProductBtn = document.getElementById('add-product-btn');
    if (addProductBtn) {
        addProductBtn.addEventListener('click', function() {
            window.location.href = '../produits/create.php';
        });
    }
    
    // Add entry modal
    const addEntryBtn = document.getElementById('add-entry-btn');
    if (addEntryBtn) {
        addEntryBtn.addEventListener('click', function() {
            window.location.href = '../mouvements/entree.php';
        });
    }
    
    // Add exit modal
    const addExitBtn = document.getElementById('add-exit-btn');
    if (addExitBtn) {
        addExitBtn.addEventListener('click', function() {
            window.location.href = '../mouvements/sortie.php';
        });
    }
    
    // Movement filter
    const movementFilter = document.getElementById('movement-filter');
    if (movementFilter) {
        movementFilter.addEventListener('change', function() {
            filterMovements(this.value);
        });
    }
}

// Save data to localStorage
function saveData() {
    localStorage.setItem('products', JSON.stringify(appData.products));
    localStorage.setItem('movements', JSON.stringify(appData.movements));
    localStorage.setItem('nextProductId', appData.nextProductId);
    localStorage.setItem('nextMovementId', appData.nextMovementId);
}

// Load data from localStorage
function loadData() {
    const products = localStorage.getItem('products');
    const movements = localStorage.getItem('movements');
    const nextProductId = localStorage.getItem('nextProductId');
    const nextMovementId = localStorage.getItem('nextMovementId');
    
    if (products) appData.products = JSON.parse(products);
    if (movements) appData.movements = JSON.parse(movements);
    if (nextProductId) appData.nextProductId = parseInt(nextProductId);
    if (nextMovementId) appData.nextMovementId = parseInt(nextMovementId);
}

// Product functions
function addProduct(product) {
    product.id = appData.nextProductId++;
    appData.products.push(product);
    saveData();
    return product;
}

function updateProduct(product) {
    const index = appData.products.findIndex(p => p.id === parseInt(product.id));
    if (index !== -1) {
        appData.products[index] = product;
        saveData();
        return true;
    }
    return false;
}

function deleteProduct(productId) {
    const index = appData.products.findIndex(p => p.id === parseInt(productId));
    if (index !== -1) {
        // Delete product
        appData.products.splice(index, 1);
        
        // Delete associated movements
        appData.movements = appData.movements.filter(m => m.productId !== parseInt(productId));
        
        saveData();
        return true;
    }
    return false;
}

function getProduct(productId) {
    return appData.products.find(p => p.id === parseInt(productId)) || null;
}

function getAllProducts() {
    return appData.products;
}

function getLowStockProducts() {
    return appData.products.filter(p => p.quantity <= p.threshold);
}

function getOutOfStockProducts() {
    return appData.products.filter(p => p.quantity === 0);
}

function filterProducts(searchTerm) {
    if (!searchTerm) {
        renderProductsTable(appData.products);
        return;
    }
    
    const filtered = appData.products.filter(p => 
        p.reference.toLowerCase().includes(searchTerm) || 
        p.name.toLowerCase().includes(searchTerm) ||
        p.category.toLowerCase().includes(searchTerm)
    );
    
    renderProductsTable(filtered);
}

// Movement functions
function addMovement(movement) {
    movement.id = appData.nextMovementId++;
    appData.movements.push(movement);
    
    // Update product quantity
    const productIndex = appData.products.findIndex(p => p.id === parseInt(movement.productId));
    if (productIndex !== -1) {
        if (movement.type === 'entry') {
            appData.products[productIndex].quantity += parseInt(movement.quantity);
        } else if (movement.type === 'exit') {
            appData.products[productIndex].quantity -= parseInt(movement.quantity);
            
            // Ensure quantity doesn't go negative
            if (appData.products[productIndex].quantity < 0) {
                appData.products[productIndex].quantity = 0;
            }
        }
    }
    
    saveData();
    return movement;
}

function deleteMovement(movementId) {
    const movement = appData.movements.find(m => m.id === parseInt(movementId));
    if (!movement) return false;
    
    // Restore product quantity
    const productIndex = appData.products.findIndex(p => p.id === parseInt(movement.productId));
    if (productIndex !== -1) {
        if (movement.type === 'entry') {
            appData.products[productIndex].quantity -= parseInt(movement.quantity);
            
            // Ensure quantity doesn't go negative
            if (appData.products[productIndex].quantity < 0) {
                appData.products[productIndex].quantity = 0;
            }
        } else if (movement.type === 'exit') {
            appData.products[productIndex].quantity += parseInt(movement.quantity);
        }
    }
    
    // Remove movement
    const index = appData.movements.findIndex(m => m.id === parseInt(movementId));
    if (index !== -1) {
        appData.movements.splice(index, 1);
        saveData();
        return true;
    }
    
    return false;
}

function getMovement(movementId) {
    return appData.movements.find(m => m.id === parseInt(movementId)) || null;
}

function getAllMovements() {
    return appData.movements;
}

function getRecentMovements(limit = 5) {
    return [...appData.movements]
        .sort((a, b) => new Date(b.date) - new Date(a.date))
        .slice(0, limit);
}

function getProductMovements(productId) {
    return appData.movements.filter(m => m.productId === parseInt(productId));
}

function filterMovements(type, searchTerm = '') {
    let filtered = appData.movements;
    
    // Filter by type
    if (type && type !== 'all') {
        filtered = filtered.filter(m => m.type === type);
    }
    
    // Filter by search term
    if (searchTerm) {
        filtered = filtered.filter(m => {
            const product = getProduct(m.productId);
            return product && 
                (product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                 product.reference.toLowerCase().includes(searchTerm.toLowerCase()));
        });
    }
    
    renderMovementsTable(filtered);
}

// UI Rendering functions
function renderProductsTable(products) {
    const tableBody = document.getElementById('products-table-body');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    if (products.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center">Aucun produit trouvé</td>
            </tr>
        `;
        return;
    }
    
    products.forEach(product => {
        // Determine status
        let statusClass = 'badge-success';
        let statusText = 'En stock';
        
        if (product.quantity === 0) {
            statusClass = 'badge-danger';
            statusText = 'Rupture';
        } else if (product.quantity < product.threshold) {
            statusClass = 'badge-warning';
            statusText = 'Stock faible';
        }
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${product.reference}</td>
            <td>${product.name}</td>
            <td>${product.category}</td>
            <td>${product.price.toFixed(2)} €</td>
            <td>${product.quantity}</td>
            <td>${product.threshold}</td>
            <td><span class="badge ${statusClass}">${statusText}</span></td>
            <td>
                <a href="../produits/edit.php?id=${product.id}" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="../produits/delete.php?id=${product.id}" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i>
                </a>
            </td>
        `;
        
        tableBody.appendChild(row);
    });
}

function renderMovementsTable(movements) {
    const tableBody = document.getElementById('movements-table-body');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    if (movements.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center">Aucun mouvement trouvé</td>
            </tr>
        `;
        return;
    }
    
    // Sort by date (newest first)
    movements.sort((a, b) => new Date(b.date) - new Date(a.date));
    
    movements.forEach(movement => {
        const product = getProduct(movement.productId);
        if (!product) return;
        
        // Format date
        const date = new Date(movement.date);
        const formattedDate = date.toLocaleDateString();
        
        // Determine type style
        const typeClass = movement.type === 'entry' ? 'badge-success' : 'badge-danger';
        const typeText = movement.type === 'entry' ? 'Entrée' : 'Sortie';
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${formattedDate}</td>
            <td>${product.name}</td>
            <td><span class="badge ${typeClass}">${typeText}</span></td>
            <td>${movement.quantity}</td>
            <td>${movement.comment}</td>
            <td>
                <a href="../mouvements/delete.php?id=${movement.id}" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i>
                </a>
            </td>
        `;
        
        tableBody.appendChild(row);
    });
}

function renderLowStockAlerts() {
    const alertsContainer = document.getElementById('low-stock-alerts');
    if (!alertsContainer) return;
    
    const lowStockProducts = getLowStockProducts();
    
    if (lowStockProducts.length === 0) {
        alertsContainer.innerHTML = '<p class="text-center">Aucune alerte de stock</p>';
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-striped">';
    html += `
        <thead>
            <tr>
                <th>Référence</th>
                <th>Produit</th>
                <th>Stock actuel</th>
                <th>Seuil</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
    `;
    
    lowStockProducts.forEach(product => {
        // Determine status
        let statusClass = 'badge-warning';
        let statusText = 'Stock faible';
        
        if (product.quantity === 0) {
            statusClass = 'badge-danger';
            statusText = 'Rupture';
        }
        
        html += `
            <tr>
                <td>${product.reference}</td>
                <td>${product.name}</td>
                <td>${product.quantity}</td>
                <td>${product.threshold}</td>
                <td><span class="badge ${statusClass}">${statusText}</span></td>
                <td>
                    <a href="../mouvements/entree.php?product_id=${product.id}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus-circle"></i> Ajouter du stock
                    </a>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    alertsContainer.innerHTML = html;
}

function updateDashboardStats() {
    // Total products
    const totalProducts = document.getElementById('total-products');
    if (totalProducts) {
        totalProducts.textContent = appData.products.length;
    }
    
    // Low stock count
    const lowStockCount = document.getElementById('low-stock-count');
    if (lowStockCount) {
        const count = appData.products.filter(p => p.quantity > 0 && p.quantity < p.threshold).length;
        lowStockCount.textContent = count;
    }
    
    // Out of stock count
    const outOfStockCount = document.getElementById('out-of-stock-count');
    if (outOfStockCount) {
        const count = appData.products.filter(p => p.quantity === 0).length;
        outOfStockCount.textContent = count;
    }
}

// Form validation functions
function validateProductForm() {
    const reference = document.getElementById('reference').value;
    const name = document.getElementById('name').value;
    const category = document.getElementById('category').value;
    const price = parseFloat(document.getElementById('price').value);
    const quantity = parseInt(document.getElementById('quantity').value);
    const threshold = parseInt(document.getElementById('threshold').value);
    
    if (!reference || !name || !category || isNaN(price) || isNaN(quantity) || isNaN(threshold)) {
        alert('Veuillez remplir tous les champs correctement.');
        return false;
    }
    
    if (price < 0 || quantity < 0 || threshold < 0) {
        alert('Les valeurs numériques doivent être positives.');
        return false;
    }
    
    return true;
}

function validateMovementForm() {
    const productId = document.getElementById('product').value;
    const quantity = parseInt(document.getElementById('quantity').value);
    const date = document.getElementById('date').value;
    const comment = document.getElementById('comment').value;
    
    if (!productId || isNaN(quantity) || !date || !comment) {
        alert('Veuillez remplir tous les champs correctement.');
        return false;
    }
    
    if (quantity <= 0) {
        alert('La quantité doit être supérieure à zéro.');
        return false;
    }
    
    // For exit movements, check if enough stock is available
    const type = document.getElementById('type').value;
    if (type === 'exit') {
        const product = getProduct(productId);
        if (product && quantity > product.quantity) {
            alert(`Erreur: La quantité à sortir (${quantity}) est supérieure au stock disponible (${product.quantity}).`);
            return false;
        }
    }
    
    return true;
}

// Export functions for use in other files
window.appData = appData;
window.productFunctions = {
    addProduct,
    updateProduct,
    deleteProduct,
    getProduct,
    getAllProducts,
    getLowStockProducts,
    getOutOfStockProducts,
    filterProducts
};

window.movementFunctions = {
    addMovement,
    deleteMovement,
    getMovement,
    getAllMovements,
    getRecentMovements,
    getProductMovements,
    filterMovements
};

window.uiFunctions = {
    renderProductsTable,
    renderMovementsTable,
    renderLowStockAlerts,
    updateDashboardStats
};

window.formFunctions = {
    validateProductForm,
    validateMovementForm
};