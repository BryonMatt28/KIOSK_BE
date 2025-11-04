<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SavorBite Grill - Modern Street Food</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-red-50 to-white font-sans text-gray-800">
  <header class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
      <h1 class="text-3xl font-bold text-red-600">SavorBite</h1>
      <nav class="space-x-6">
        <a href="#about" class="text-gray-700 hover:text-red-600 transition">About</a>
        <a href="/KIOSK/public/kiosk.php" class="bg-red-600 text-white px-6 py-2 rounded-full hover:bg-red-700 transition">Order Now</a>
        <a href="/KIOSK/public/admin/login.php" class="text-gray-700 hover:text-red-600 transition">Admin</a>
      </nav>
    </div>
  </header>

  <main>
    <section class="relative h-[600px] flex items-center justify-center bg-cover bg-center" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&q=80')">
      <div class="text-center text-white">
        <h2 class="text-5xl font-bold mb-4">Welcome to SavorBite</h2>
        <p class="text-xl max-w-2xl mx-auto mb-8">Experience the perfect blend of street food classics and gourmet flavors, crafted with passion and served with style.</p>
        <a href="/KIOSK/public/kiosk.php" class="bg-red-600 text-white px-8 py-3 rounded-full text-lg font-semibold hover:bg-red-700 transition">Explore Our Menu</a>
      </div>
    </section>

    <section id="about" class="py-20 bg-white">
      <div class="max-w-6xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
          <div>
            <h3 class="text-3xl font-bold mb-6">Our Digital Kiosk</h3>
            <p class="text-gray-600 leading-relaxed mb-6">
              Our modern kiosk system is designed to make your ordering experience faster, more convenient, and personalized. Skip the lines and customize your meal with just a few taps.
            </p>
            <div class="grid grid-cols-2 gap-6">
              <div class="bg-red-50 p-4 rounded-lg">
                <h4 class="font-bold text-red-600 mb-2">Quick Ordering</h4>
                <p class="text-sm text-gray-600">Select and customize your meals in seconds.</p>
              </div>
              <div class="bg-red-50 p-4 rounded-lg">
                <h4 class="font-bold text-red-600 mb-2">Easy Navigation</h4>
                <p class="text-sm text-gray-600">Intuitive interface for all ages and tech levels.</p>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <img src="https://images.squarespace-cdn.com/content/v1/5a0f6a9b2aeba52a9ef3cf27/1671650188950-FPSIACDXF96B7ZUHPJXW/kiosk_hero.png" alt="Kiosk Interface" class="rounded-lg shadow-lg">
          </div>
        </div>
      </div>
    </section>

    <section id="features" class="py-20 bg-gray-50">
      <div class="max-w-6xl mx-auto px-6">
        <h2 class="text-4xl font-bold text-center mb-12">Kiosk Features</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition">
            <div class="text-red-600 text-4xl mb-4">🍔</div>
            <h3 class="text-xl font-bold mb-2">Customizable Meals</h3>
            <p class="text-gray-600">Personalize your burger, sides, and drinks with easy modifications.</p>
          </div>
          <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition">
            <div class="text-red-600 text-4xl mb-4">💳</div>
            <h3 class="text-xl font-bold mb-2">Multiple Payment Options</h3>
            <p class="text-gray-600">Pay with cash, card, mobile wallet, or loyalty points.</p>
          </div>
          <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition">
            <div class="text-red-600 text-4xl mb-4">⏱️</div>
            <h3 class="text-xl font-bold mb-2">Quick Service</h3>
            <p class="text-gray-600">Reduce waiting times with our streamlined ordering process.</p>
          </div>
          <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition">
            <div class="text-red-600 text-4xl mb-4">🌐</div>
            <h3 class="text-xl font-bold mb-2">Language Support</h3>
            <p class="text-gray-600">Available in multiple languages for diverse customers.</p>
          </div>
          <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition">
            <div class="text-red-600 text-4xl mb-4">♿</div>
            <h3 class="text-xl font-bold mb-2">Accessibility</h3>
            <p class="text-gray-600">Designed to be user-friendly for all, including those with disabilities.</p>
          </div>
          <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition">
            <div class="text-red-600 text-4xl mb-4">📊</div>
            <h3 class="text-xl font-bold mb-2">Order History</h3>
            <p class="text-gray-600">Save and recall your favorite orders for quick reordering.</p>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="bg-gray-900 text-white py-12">
    <div class="max-w-6xl mx-auto px-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div>
          <h3 class="text-2xl font-bold text-red-600 mb-4">SavorBite</h3>
          <p class="text-gray-400">Transforming street food ordering with technology.</p>
        </div>
        <div>
          <h4 class="font-bold mb-4">Hours</h4>
          <p class="text-gray-400">24/7 Digital Ordering<br>Store Hours May Vary</p>
        </div>
        <div>
          <h4 class="font-bold mb-4">Support</h4>
          <p class="text-gray-400">Customer Support<br>1-800-SAVOR-BITE<br>support@savorbite.com</p>
        </div>
      </div>
      <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
        &copy; 2025 SavorBite. All rights reserved.
      </div>
    </div>
  </footer>

  <script>
    tailwind.config = { theme: { extend: { colors: { 'red-600': '#da291c' } } } }
  </script>
</body>
</html>
