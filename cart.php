<?php
session_start();
require_once __DIR__ . '/api-helper.php';

// Protect page
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cart — UrbanWear</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif}
    body{background:#f8f9fa;color:#333}
    .container{max-width:1200px;margin:40px auto;padding:0 20px}
    .site-nav{display:flex;align-items:center;justify-content:space-between;padding:22px 48px;border-bottom:1px solid #eee;background:#fff}
    .site-nav a{color:#333;text-decoration:none;margin-left:20px;font-weight:500}
    .site-nav a:hover{color:#c5a059}
    h1{margin:30px 0;font-weight:600}
    .cart-layout{display:grid;grid-template-columns:1fr 380px;gap:40px;align-items:start}
    @media(max-width:900px){.cart-layout{grid-template-columns:1fr}}
    .cart-section{background:#fff;border-radius:12px;padding:24px;box-shadow:0 4px 15px rgba(0,0,0,0.06)}
    .cart-item{display:flex;align-items:center;padding:20px 0;border-bottom:1px solid #eee;gap:20px}
    .cart-item:last-child{border-bottom:none}
    .cart-item img{width:100px;height:100px;object-fit:cover;border-radius:8px}
    .item-details{flex:1}
    .item-details h4{font-size:1rem;margin-bottom:4px}
    .item-details .price{color:#333;font-weight:600}
    .quantity-control{display:flex;align-items:center;gap:8px}
    .qty-btn{width:36px;height:36px;border:1px solid #ddd;background:#fff;border-radius:50%;cursor:pointer;font-size:1.2rem;display:flex;align-items:center;justify-content:center}
    .qty-btn:hover{background:#f5f5f5}
    .qty-input{width:50px;text-align:center;padding:8px;border:1px solid #ddd;border-radius:6px}
    .remove-btn{background:none;color:#ff4757;border:1px solid #ff4757;padding:8px 14px;border-radius:6px;cursor:pointer;font-size:0.9rem;transition:all 0.2s}
    .remove-btn:hover{background:#ff4757;color:#fff}
    .item-total{font-weight:700;min-width:100px;text-align:right}
    .order-summary{position:sticky;top:100px}
    .order-summary h2{font-size:1.2rem;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #eee}
    .summary-row{display:flex;justify-content:space-between;padding:10px 0;color:#555}
    .summary-row.total{font-size:1.3rem;font-weight:700;color:#333;border-top:2px solid #eee;margin-top:10px;padding-top:16px}
    .checkout-btn{width:100%;background:#2ecc71;color:#fff;border:none;padding:16px 24px;font-size:1.1rem;font-weight:600;border-radius:8px;cursor:pointer;margin-top:20px;transition:background 0.2s}
    .checkout-btn:hover{background:#27ae60}
    .empty-cart{text-align:center;padding:80px 20px;color:#777}
    .empty-cart i{font-size:4rem;color:#ddd;margin-bottom:20px}
  </style>
</head>
<body>
<nav class="site-nav">
  <div style="display:flex; align-items:center">
      <a href="index.php" style="font-weight:700;font-size:18px; margin-left:0">URBANWEAR</a>
  </div>
  <div>
    <a href="index.php">Home</a>
    <a href="men.php">Men</a>
    <a href="women.php">Women</a>
    <a href="kids.php">Kids</a>
    <a href="cart.php" style="position:relative; margin-left:15px;">
        <i class="fa-solid fa-bag-shopping"></i> Cart
        <span class="cart-count-badge" style="display:none; position: absolute; top: -8px; right: -12px; background: #c5a059; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 10px; font-weight: 700; min-width: 18px; text-align: center; display: flex; align-items: center; justify-content: center;">0</span>
    </a>
  </div>
</nav>

<div class="container" id="cartContainer">
  <h1>Your Cart</h1>
  <div class="cart-layout">
    <div class="cart-section">
      <div id="emptyCart" class="empty-cart" style="display:none">
        <i class="fas fa-shopping-cart"></i>
        <p>Your cart is empty</p>
      </div>
      <div id="cartList"></div>
    </div>
    <div class="cart-section order-summary" id="orderSummary" style="display:none">
        <h2>Order Summary</h2>
        <div class="summary-row"><span>Total MRP</span><span id="mrpTotal">₹0</span></div>
        <div class="summary-row" style="color:#28a745"><span>Discount</span><span id="discountTotal">-₹0</span></div>
        <div class="summary-row"><span>GST</span><span id="gstTotal">₹0</span></div>
        <div class="summary-row"><span>Delivery Fee</span><span id="deliveryFee">₹49</span></div>
        <div class="summary-row total"><span>Total Amount</span><span id="grandTotal">₹0</span></div>
        <a href="checkout.php"><button class="checkout-btn">Proceed to Checkout</button></a>
    </div>
  </div>
</div>

<script>
  function getCart(){ 
    try { return JSON.parse(localStorage.getItem('urbanwear_cart') || '[]'); } catch(e){ return []; }
  }

  function renderCart(){
    const cart = getCart();
    const empty = document.getElementById('emptyCart');
    const list = document.getElementById('cartList');
    const summaryEl = document.getElementById('orderSummary');
  
    if (cart.length === 0) {
      empty.style.display = 'block';
      list.innerHTML = '';
      summaryEl.style.display = 'none';
      return;
    }
  
    empty.style.display = 'none';
    summaryEl.style.display = 'block';
    
    // Use backend summary
    const summaryStored = localStorage.getItem('urbanwear_cart_summary');
    const summary = summaryStored ? JSON.parse(summaryStored) : { totalMRP:0, totalDiscount:0, totalGST:0, deliveryFee:49, grandTotal:0 };

    list.innerHTML = cart.map((item, index) => {
      const qty = parseInt(item.qty) || 1;
      const price = parseFloat(item.price) || 0;
      const discount = parseFloat(item.discount_percentage) || 0;
      const displayedPrice = discount > 0 ? (price * (1 - discount/100)) : price;
      
      return `
        <div class="cart-item">
          <img src="${item.img || 'https://via.placeholder.com/100'}" alt="${item.name.replace(/"/g, '&quot;')}">
          <div class="item-details">
            <h4>${item.name}</h4>
            <div class="price">
                ₹${displayedPrice.toLocaleString('en-IN')} 
                ${discount > 0 ? `<span style="text-decoration:line-through; color:#999; font-size:0.85em; font-weight:400; margin-left:5px">₹${price.toLocaleString('en-IN')}</span> <span style="color:#388e3c; font-size:0.85em; margin-left:5px">${discount}% OFF</span>` : ''}
            </div>
          </div>
          <div class="quantity-control">
            <button class="qty-btn" onclick="changeQty('${item.id}', -1)">−</button>
            <input type="number" class="qty-input" value="${qty}" min="1" onchange="setQty('${item.id}', this.value)">
            <button class="qty-btn" onclick="changeQty('${item.id}', 1)">+</button>
          </div>
          <div class="item-total">₹${Math.round(displayedPrice * qty).toLocaleString('en-IN')}</div>
          <button class="remove-btn" onclick="removeItem('${item.id}')" style="margin-left:15px">
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>
      `;
    }).join('');
    
    document.getElementById('mrpTotal').textContent = '₹' + summary.totalMRP.toLocaleString('en-IN');
    document.getElementById('discountTotal').textContent = '-₹' + summary.totalDiscount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    if (summary.subtotal !== undefined) {
        // Optional: you could add a subtotal row here if desired, 
        // but let's keep it consistent with the request for checkout.php primarily.
    }
    document.getElementById('gstTotal').textContent = '₹' + summary.totalGST.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('deliveryFee').textContent = '₹' + summary.deliveryFee;
    document.getElementById('grandTotal').textContent = '₹' + summary.grandTotal.toLocaleString('en-IN');
  }

  function changeQty(id, delta){
      if (typeof window.changeMiniCartQty === 'function') {
          window.changeMiniCartQty(id, delta);
      }
  }

  function setQty(id, val){
      const v = Math.max(1, parseInt(val) || 1);
      const cart = getCart();
      const item = cart.find(i => String(i.id) === String(id));
      if(item) {
          // Trigger same logic as changeQty but with absolute target
          // In simpler terms, we'll use addToCart or syncWithBackend
          if (window.changeMiniCartQty) {
              const current = parseInt(item.qty) || 1;
              window.changeMiniCartQty(id, v - current);
          }
      }
  }

  function removeItem(id) {
      if (typeof window.removeMiniCartItem === 'function') {
          window.removeMiniCartItem(id);
      }
  }

  document.addEventListener('DOMContentLoaded', renderCart);
</script>

<script>
    // Pass PHP context to JS
    window.IS_LOGGED_IN = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    window.AUTH_TOKEN = '<?php echo getAuthToken() ?? ''; ?>';
</script>
<script src="assets/js/cart.js"></script>
</body>
</html>
