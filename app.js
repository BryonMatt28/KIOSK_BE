$(document).ready(function() {
    const cart = [];

    // Populate menu items
    function populateMenu() {
        const $menuContainer = $('#menu-items');
        MENU_ITEMS.forEach(item => {
            const $menuItem = $(`
                <div class="menu-item bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 p-4 text-center">
                    <img src="${item.image}" alt="${item.name}" class="w-full h-32 object-cover rounded-t-lg mb-4">
                    <h3 class="text-lg font-semibold mb-2">${item.name}</h3>
                    <p class="text-gray-600 text-sm mb-2">${item.description}</p>
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-bold text-mcd-red">$${item.price.toFixed(2)}</span>
                        <button class="add-to-cart bg-mcd-red text-white px-3 py-1 rounded hover:bg-red-700 transition" data-id="${item.id}">
                            Add to Cart
                        </button>
                    </div>
                </div>
            `);
            $menuContainer.append($menuItem);
        });
    }

    // Update cart total
    function updateTotal() {
        const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        $('#total-price').text(total.toFixed(2));
    }

    // Add to cart functionality
    $(document).on('click', '.add-to-cart', function() {
        const itemId = $(this).data('id');
        const item = MENU_ITEMS.find(i => i.id === itemId);
        
        const existingCartItem = cart.find(cartItem => cartItem.id === itemId);
        if (existingCartItem) {
            existingCartItem.quantity++;
        } else {
            cart.push({...item, quantity: 1});
        }

        renderCart();
        updateTotal();
    });

    // Render cart items
    function renderCart() {
        const $cartContainer = $('#cart-items');
        $cartContainer.empty();
        
        cart.forEach(item => {
            const $cartItem = $(`
                <li class="cart-item flex justify-between items-center bg-white p-2 rounded shadow-sm">
                    <div>
                        <span class="font-medium">${item.name}</span>
                        <span class="text-gray-500 ml-2">x ${item.quantity}</span>
                    </div>
                    <span class="font-bold text-mcd-red">$${(item.price * item.quantity).toFixed(2)}</span>
                </li>
            `);
            $cartContainer.append($cartItem);
        });
    }

    // Clear cart functionality
    $('#clear-cart').click(function() {
        cart.length = 0;
        renderCart();
        updateTotal();
    });

    // Checkout functionality
    $('#checkout-btn').click(function() {
        if (cart.length === 0) {
            alert('Your cart is empty!');
            return;
        }

        const receiptContent = generateReceipt();
        $('#receipt-content').html(receiptContent);
        $('#receipt-modal').removeClass('hidden');
        
        // Reset cart after checkout
        cart.length = 0;
        renderCart();
        updateTotal();
    });

    // Close receipt modal
    $('#close-receipt').click(function() {
        $('#receipt-modal').addClass('hidden');
    });

    // Generate receipt
    function generateReceipt() {
        const date = new Date().toLocaleString();
        let receiptHtml = `
            <h2 class="text-2xl font-bold mb-4">McDo Kiosk Receipt</h2>
            <p class="text-gray-600 mb-4">${date}</p>
            <hr class="mb-4">
        `;

        cart.forEach(item => {
            receiptHtml += `
                <div class="flex justify-between mb-2">
                    <span>${item.name} x ${item.quantity}</span>
                    <span>$${(item.price * item.quantity).toFixed(2)}</span>
                </div>
            `;
        });

        const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        receiptHtml += `
            <hr class="my-4">
            <div class="flex justify-between font-bold text-xl">
                <span>Total:</span>
                <span>$${total.toFixed(2)}</span>
            </div>
            <p class="text-gray-600 mt-4">Thank you for your purchase!</p>
        `;

        return receiptHtml;
    }

    // Initial setup
    populateMenu();
});