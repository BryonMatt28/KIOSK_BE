<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title>McDo Kiosk - Order System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  </head>
  <body class="bg-gradient-to-b from-red-50 to-white font-sans text-gray-800">
    <header class="bg-white shadow-md sticky top-0 z-50 mb-6">
      <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <a href="/KIOSK/public/index.php" class="text-3xl font-bold text-mcd-red">Online Kiosk</a>
        <h2 class="text-xl font-semibold text-gray-700">Order System</h2>
      </div>
    </header>

    <div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
      <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4">
        <div class="mb-8">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-green-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Thank you for ordering!</h2>
        <p class="text-gray-600 mb-6">Your order is being made fresh and will be ready shortly.</p>
        <button onclick="closeConfirmation()" class="w-full bg-mcd-red text-white px-4 py-3 rounded-full hover:bg-red-700 transition">Return to Home</button>
      </div>
    </div>

    <div id="orderSummaryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
      <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-4 text-center">Order Summary</h2>
        <div id="orderSummaryContent" class="mb-6 space-y-3 max-h-[300px] overflow-y-auto"></div>
        <div class="border-t border-gray-200 pt-4 mb-4">
          <div class="flex justify-between items-center">
            <span class="text-lg font-semibold">Total Amount:</span>
            <span id="orderSummaryTotal" class="text-2xl font-bold text-mcd-red">$0.00</span>
          </div>
        </div>
        <div class="flex space-x-4">
          <button id="confirmOrderButton" onclick="closeOrderSummary()" class="w-full bg-mcd-red text-white px-4 py-3 rounded-full hover:bg-red-700 transition">
            Confirm Order
          </button>
        </div>
      </div>
    </div>

    <div id="quantityModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
      <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4">
        <h2 id="quantityModalTitle" class="text-2xl font-bold mb-4 text-center">Select Quantity</h2>
        <div class="flex items-center justify-center space-x-4 mb-6">
          <button onclick="decreaseQuantity()" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition">-</button>
          <span id="quantityDisplay" class="text-2xl font-bold">1</span>
          <button onclick="increaseQuantity()" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition">+</button>
        </div>
        <div class="flex space-x-4">
          <button onclick="cancelQuantitySelection()" class="w-1/2 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">Cancel</button>
          <button onclick="confirmQuantity()" class="w-1/2 px-4 py-2 bg-mcd-red text-white rounded-lg hover:bg-red-700 transition">Confirm</button>
        </div>
      </div>
    </div>

    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
      <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Select Payment Method</h2>
        <div class="grid grid-cols-2 gap-4">
          <button onclick="selectPaymentType('counter')" class="bg-gray-100 p-4 rounded-lg hover:bg-gray-200 transition flex flex-col items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v18H3V3zm3 3h12v12H6V6zm3 3v6h6V9H9z" />
            </svg>
            Pay at Counter
          </button>
          <button onclick="selectPaymentType('online')" class="bg-gray-100 p-4 rounded-lg hover:bg-gray-200 transition flex flex-col items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
            </svg>
            Online Payment
          </button>
        </div>
        <div class="mt-6 text-center">
          <button onclick="closePaymentModal()" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition">Cancel</button>
        </div>
      </div>
    </div>

    <div id="onlinePaymentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
      <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Online Payment Methods</h2>
        <div class="grid grid-cols-1 gap-4">
          <div class="bg-gray-100 p-4 rounded-lg hover:bg-gray-200 transition flex items-center">
            <div class="w-20 h-12 mr-4 flex items-center justify-center">
              <svg xmlns="https://static.vecteezy.com/system/resources/thumbnails/000/357/048/small_2x/3__2821_29.jpg" class="h-12 w-16 text-blue-600" viewBox="0 0 64 40" fill="currentColor"><path d="M60.5 3.5C58.2 1.2 55.2 0 52 0H12C5.4 0 0 5.4 0 12v16c0 6.6 5.4 12 12 12h40c6.6 0 12-5.4 12-12V12c0-3.2-1.2-6.2-3.5-8.5zM12 4h40c1.9 0 3.7.7 5.1 2L32 22 6.9 6c1.4-1.3 3.2-2 5.1-2zm-8 24V12c0-.8.1-1.5.4-2.2L20.3 24l-15.9 9.2c-.3-.7-.4-1.4-.4-2.2zm52 4H12c-1.9 0-3.7-.7-5.1-2L32 18l15.1 12c-1.4 1.3-3.2 2-5.1 2zm8-6c0 .8-.1 1.5-.4 2.2L43.7 16l15.9-9.2c.3.7.4 1.4.4 2.2v16z"/></svg>
            </div>
            <div class="flex-grow">
              <h3 class="font-bold">Credit Card</h3>
              <p class="text-sm text-gray-600">Pay with Visa, Mastercard, etc.</p>
            </div>
            <input type="radio" name="onlinePayment" value="creditCard" class="form-radio">
          </div>
          <div class="bg-gray-100 p-4 rounded-lg hover:bg-gray-200 transition flex items-center">
            <div class="w-20 h-12 mr-4 flex items-center justify-center">
              <img src="https://play-lh.googleusercontent.com/fdQjxsIO8BTLaw796rQPZtLEnGEV8OJZJBJvl8dFfZLZcGf613W93z7y9dFAdDhvfqw" class="w-16 h-16 object-cover rounded-lg">
            </div>
            <div class="flex-grow">
              <h3 class="font-bold">Paymaya</h3>
              <p class="text-sm text-gray-600">Pay using Paymaya</p>
            </div>
            <input type="radio" name="onlinePayment" value="paymaya" class="form-radio">
          </div>
          <div class="bg-gray-100 p-4 rounded-lg hover:bg-gray-200 transition flex items-center">
            <div class="w-20 h-12 mr-4 flex items-center justify-center">
              <img src="https://images.seeklogo.com/logo-png/52/1/gcash-logo-png_seeklogo-522261.png" class="w-16 h-16 object-cover rounded-lg">
            </div>
            <div class="flex-grow">
              <h3 class="font-bold">Gcash</h3>
              <p class="text-sm text-gray-600">Pay using Gcash</p>
            </div>
            <input type="radio" name="onlinePayment" value="gcash" class="form-radio">
          </div>
        </div>
        <div class="mt-6 flex space-x-4">
          <button onclick="closeOnlinePaymentModal()" class="w-1/2 bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition">Cancel</button>
          <button onclick="confirmOnlinePayment()" class="w-1/2 bg-mcd-red text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">Confirm Payment</button>
        </div>
      </div>
    </div>

    <div id="languageSelector" class="fixed bottom-4 right-4 z-50">
      <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" class="bg-mcd-red text-white px-4 py-2 rounded-full hover:bg-red-700 transition flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7 2a1 1 0 011 1v1h3a1 1 0 110 2H9.578a18.87 18.87 0 01-1.724 4.78c.29.354.607.676.948.97a1 1 0 11-1.4 1.44 9.984 9.984 0 01-1.198-1.276 9.984 9.984 0 01-1.198 1.276 1 1 0 01-1.4-1.44c.341-.294.658-.616.948-.97A18.871 18.871 0 014.422 6H3a1 1 0 110-2h3V3a1 1 0 011-1zm6 5a1 1 0 01.707.293l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L14.586 11H10a1 1 0 110-2h4.586l-2.293-2.293A1 1 0 0113 5z" clip-rule="evenodd" /></svg>
          Language
        </button>
        <div x-show="open" @click.away="open = false" class="absolute bottom-full mb-2 right-0 bg-white rounded-lg shadow-lg">
          <button onclick="changeLanguage('en')" class="block w-full text-left px-4 py-2 hover:bg-gray-100">English</button>
          <button onclick="changeLanguage('es')" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Spanish</button>
          <button onclick="changeLanguage('fr')" class="block w-full text-left px-4 py-2 hover:bg-gray-100">French</button>
        </div>
      </div>
    </div>

    <div class="flex flex-col lg:flex-row min-h-[calc(100vh-80px)] max-w-7xl mx-auto px-6 gap-6">
      <section class="w-full lg:w-1/4 bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6">Categories</h2>
        <div id="categoryList" class="space-y-4"></div>
      </section>

      <section class="w-full lg:w-2/3">
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h2 id="categoryTitle" class="text-2xl font-bold mb-6">Menu</h2>
          <div id="menu" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
        </div>
      </section>

      <section class="w-full lg:w-1/3 sticky top-[100px] h-fit">
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h2 class="text-2xl font-bold mb-4">Order Summary</h2>
          <div class="border-b border-gray-200 mb-4"></div>
          <ul id="cart" class="space-y-3 max-h-[400px] overflow-y-auto mb-4"></ul>
          <div class="border-t border-gray-200 pt-4">
            <div class="flex justify-between items-center mb-6">
              <span class="text-lg font-semibold">Total Amount:</span>
              <span class="text-2xl font-bold text-mcd-red">$<span id="total">0.00</span></span>
            </div>
            <div class="flex gap-4">
              <button onclick="clearCart()" class="w-1/2 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">Clear Cart</button>
              <button onclick="checkout()" class="w-1/2 px-4 py-2 bg-mcd-red text-white rounded-lg hover:bg-red-700 transition">Checkout</button>
            </div>
          </div>
        </div>
      </section>
    </div>

    <script>
      tailwind.config = { theme: { extend: { colors: { 'mcd-red': '#DA291C', 'mcd-yellow': '#FFC72C' } } } }

      let cart = [];
      let products = [];
      let selectedPaymentMethod = null;
      const menuDiv = document.getElementById('menu');
      const categoryTitle = document.getElementById('categoryTitle');
      const cartList = document.getElementById('cart');
      const totalEl = document.getElementById('total');
      const orderSummaryModal = document.getElementById('orderSummaryModal');
      const orderSummaryContent = document.getElementById('orderSummaryContent');
      const orderSummaryTotal = document.getElementById('orderSummaryTotal');
      const quantityModal = document.getElementById('quantityModal');
      const quantityDisplay = document.getElementById('quantityDisplay');
      const quantityModalTitle = document.getElementById('quantityModalTitle');
      const categoryList = document.getElementById('categoryList');

      let currentItemIndex = null;
      let currentQuantity = 1;
      let activeCategory = null;

      async function fetchProducts() {
        const res = await fetch('/KIOSK/public/api/products.php');
        products = await res.json();
        buildCategories();
        const firstCategory = [...new Set(products.map(p => p.category || 'Uncategorized'))][0] || 'Uncategorized';
        setActiveCategory(firstCategory);
      }

      function buildCategories() {
        const cats = [...new Set(products.map(p => p.category || 'Uncategorized'))];
        categoryList.innerHTML = '';
        cats.forEach(cat => {
          const el = document.createElement('div');
          el.className = 'flex items-center space-x-4 px-4 py-4 rounded-xl cursor-pointer transition duration-300 ease-in-out transform hover:scale-105 hover:shadow-md hover:bg-gray-50 border border-transparent hover:border-gray-100';
          el.innerHTML = `<span class="font-semibold">${cat}</span>`;
          el.addEventListener('click', () => setActiveCategory(cat));
          categoryList.appendChild(el);
        });
      }

      function setActiveCategory(cat) {
        activeCategory = cat;
        categoryTitle.textContent = cat;
        renderMenuItems(cat);
        // highlight active
        [...categoryList.children].forEach(child => {
          if (child.textContent.trim() === cat) {
            child.classList.add('bg-mcd-red','text-white'); child.classList.remove('bg-white');
          } else {
            child.classList.remove('bg-mcd-red','text-white'); child.classList.add('bg-white');
          }
        });
      }

      function renderMenuItems(category) {
        menuDiv.innerHTML = '';
        const categoryItems = products.filter(item => (item.category || 'Uncategorized') === category);
        categoryItems.forEach((item, index) => {
          const div = document.createElement('div');
          div.className = 'bg-white border border-gray-200 p-4 rounded-lg hover:shadow-md transition cursor-pointer';
          div.innerHTML = `
            <div class="flex justify-between items-start mb-2">
              <h3 class="font-bold text-lg">${item.name}</h3>
              <span class="text-mcd-red font-bold">$${Number(item.price).toFixed(2)}</span>
            </div>
            ${item.image_url ? `<img src="${item.image_url}" alt="${item.name}" class="w-full h-32 object-cover rounded mb-3" />` : ''}
            <button onclick="showQuantityModal(${index})" class="w-full px-4 py-2 bg-red-100 text-mcd-red rounded hover:bg-red-200 transition text-sm font-semibold">Add to Order</button>
          `;
          menuDiv.appendChild(div);
        });
      }

      function showQuantityModal(index) {
        // find actual index in products filtered set
        const filtered = products.filter(item => (item.category || 'Uncategorized') === activeCategory);
        const item = filtered[index];
        currentItemIndex = products.findIndex(p => p.id === item.id);
        currentQuantity = 1;
        quantityDisplay.textContent = currentQuantity;
        quantityModalTitle.textContent = `How many ${item.name}?`;
        quantityModal.classList.remove('hidden');
      }
      function increaseQuantity() { currentQuantity++; quantityDisplay.textContent = currentQuantity; }
      function decreaseQuantity() { if (currentQuantity > 1) { currentQuantity--; quantityDisplay.textContent = currentQuantity; } }
      function cancelQuantitySelection() { quantityModal.classList.add('hidden'); currentItemIndex = null; currentQuantity = 1; }
      function confirmQuantity() {
        if (currentItemIndex !== null) {
          const item = products[currentItemIndex];
          for (let i = 0; i < currentQuantity; i++) { cart.push({ name: item.name, price: Number(item.price) }); }
          renderCart();
          quantityModal.classList.add('hidden');
          currentItemIndex = null; currentQuantity = 1;
        }
      }

      function renderCart() {
        cartList.innerHTML = '';
        let total = 0;
        const cartItemCounts = {};
        cart.forEach(item => { cartItemCounts[item.name] ? cartItemCounts[item.name].quantity++ : cartItemCounts[item.name] = { ...item, quantity: 1 }; });
        Object.values(cartItemCounts).forEach(item => {
          const itemTotal = item.price * item.quantity; total += itemTotal;
          const li = document.createElement('li');
          li.className = 'flex justify-between items-start pb-3';
          li.innerHTML = `
            <div>
              <h4 class="font-semibold">${item.name} x ${item.quantity}</h4>
              <p class="text-sm text-gray-600">$${itemTotal.toFixed(2)}</p>
            </div>
            <button onclick="removeItemFromCart('${item.name}')" class="text-mcd-red hover:text-red-700">✕</button>
          `;
          cartList.appendChild(li);
        });
        totalEl.textContent = total.toFixed(2);
      }

      function removeItemFromCart(itemName) { cart = cart.filter(item => item.name !== itemName); renderCart(); }
      function clearCart() { cart.length = 0; renderCart(); }

      function checkout() { if (cart.length === 0) { alert('Please add items to your cart before checking out.'); return; } showOrderSummary(); }
      function closeOrderSummary() { orderSummaryModal.classList.add('hidden'); document.getElementById('paymentModal').classList.remove('hidden'); }
      function showOrderSummary() {
        orderSummaryContent.innerHTML = '';
        const cartItemCounts = {}; cart.forEach(item => { cartItemCounts[item.name] ? cartItemCounts[item.name].quantity++ : cartItemCounts[item.name] = { ...item, quantity: 1 }; });
        let total = 0; Object.values(cartItemCounts).forEach(item => { const itemTotal = item.price * item.quantity; total += itemTotal; const itemDiv = document.createElement('div'); itemDiv.className = 'flex justify-between items-center mb-2'; itemDiv.innerHTML = `<div><span class="font-semibold">${item.name}</span><span class="text-gray-600 ml-2">x${item.quantity}</span></div><span class="font-bold">$${itemTotal.toFixed(2)}</span>`; orderSummaryContent.appendChild(itemDiv); });
        orderSummaryTotal.textContent = `$${total.toFixed(2)}`; orderSummaryModal.classList.remove('hidden'); }

      function selectPaymentType(type) { 
        if (type === 'counter') { 
          selectedPaymentMethod = 'Pay at Counter';
          closePaymentModal(); 
          submitOrder(); 
        } else if (type === 'online') { 
          closePaymentModal(); 
          openOnlinePaymentModal(); 
        } 
      }
      
      async function submitOrder() { 
        try { 
          const items = cart.map(i => ({ name: i.name, price: i.price, quantity: 1 })); 
          await fetch('/KIOSK/public/api/order_create.php', { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' }, 
            body: JSON.stringify({ 
              items, 
              payment_method: selectedPaymentMethod || null 
            }) 
          }); 
        } catch (e) {
          console.error('Order submission error:', e);
        } 
        showConfirmation(); 
        cart = []; 
        selectedPaymentMethod = null;
        renderCart(); 
      }
      
      function showConfirmation() { 
        orderSummaryModal.classList.add('hidden'); 
        document.getElementById('paymentModal').classList.add('hidden'); 
        document.getElementById('onlinePaymentModal').classList.add('hidden'); 
        document.getElementById('confirmationModal').classList.remove('hidden'); 
      }
      
      function confirmOnlinePayment() { 
        const selectedMethod = document.querySelector('input[name="onlinePayment"]:checked'); 
        if (selectedMethod) { 
          const methodValue = selectedMethod.value;
          // Map the value to a readable name
          const methodNames = {
            'creditCard': 'Credit Card',
            'paymaya': 'Paymaya',
            'gcash': 'Gcash'
          };
          selectedPaymentMethod = methodNames[methodValue] || methodValue;
          closeOnlinePaymentModal(); 
          submitOrder(); 
        } else { 
          alert('Please select a payment method'); 
        } 
      }
      function closePaymentModal() { document.getElementById('paymentModal').classList.add('hidden'); }
      function openOnlinePaymentModal() { document.getElementById('onlinePaymentModal').classList.remove('hidden'); }
      function closeOnlinePaymentModal() { document.getElementById('onlinePaymentModal').classList.add('hidden'); }
      function closeConfirmation() { window.location.href = '/KIOSK/public/index.php'; }

      // Language (minimal, unchanged)
      function changeLanguage(lang) {
        const translations = { 'en': { categoryTitle: 'Menu', clearCart: 'Clear Cart', checkout: 'Checkout' }, 'es': { categoryTitle: 'Menú', clearCart: 'Limpiar Carrito', checkout: 'Pagar' }, 'fr': { categoryTitle: 'Menu', clearCart: 'Vider le Panier', checkout: 'Payer' } };
        const t = translations[lang];
        categoryTitle.textContent = t.categoryTitle;
        document.querySelector('button[onclick="clearCart()"]').textContent = t.clearCart;
        document.querySelector('button[onclick="checkout()"]').textContent = t.checkout;
      }

      // Kick off
      fetchProducts();
    </script>
  </body>
</html>
