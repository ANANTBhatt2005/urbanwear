<?php
session_start();
require_once 'api-helper.php';

// Protect page
requireLogin();

$isLoggedIn = isLoggedIn();
$user = getCurrentUser();
$token = getAuthToken();

// Fetch addresses if logged in
$addresses = [];
if ($isLoggedIn && $token) {
    $resp = $API->get('/api/v1/addresses', $token);
    if ($resp['success']) {
        $addresses = $resp['data'] ?? [];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout — UrbanWear</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
  <style>
    .container{max-width:800px;margin:40px auto;padding:20px}
    .form-group{margin-bottom:16px}
    .form-group label{display:block;margin-bottom:6px;font-weight:600;color:#333}
    .form-group input, .form-group select{width:100%;padding:12px;border:1px solid #ddd;border-radius:6px;font-size:14px}
    .summary-item{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #eee}
    .total-row{font-size:1.3rem;font-weight:700;margin-top:20px;display:flex;justify-content:space-between;border-top:2px solid #333;padding-top:15px}
    
    .btn{width:100%;padding:14px;border:none;border-radius:6px;font-size:1rem;font-weight:bold;cursor:pointer;margin-bottom:10px;transition:0.3s}
    .btn-cod{background:#6c757d;color:white}
    .btn-online{background:#007bff;color:white}
    .btn:hover{opacity:0.9}
    
    .checkout-grid{display:grid;grid-template-columns:1fr 0.8fr;gap:40px}
    @media(max-width:768px){.checkout-grid{grid-template-columns:1fr}}
    
    .address-card{border:2px solid #eee;border-radius:6px;padding:15px;margin-bottom:10px;cursor:pointer;position:relative}
    .address-card.selected{border-color:#007bff;background:#f0f7ff}
    .address-card input{position:absolute;top:15px;right:15px}
    
    .success-msg{background:#d4edda;color:#155724;padding:20px;border-radius:8px;text-align:center}
  </style>
</head>
<body>
<nav style="display:flex; justify-content:space-between; align-items:center; padding: 20px 60px; border-bottom:1px solid #eee; background:#fff;">
    <a href="index.php" style="font-family:'Inter', sans-serif; font-weight:700; letter-spacing:2px; text-decoration:none; color:#000;">URBANWEAR</a>
    <div style="display:flex; align-items:center; gap:30px;">
        <a href="men.php" style="text-decoration:none; color:#666; font-size:14px; text-transform:uppercase;">Men</a>
        <a href="women.php" style="text-decoration:none; color:#666; font-size:14px; text-transform:uppercase;">Women</a>
        <a href="kids.php" style="text-decoration:none; color:#666; font-size:14px; text-transform:uppercase;">Kids</a>
        <a href="cart.php" style="position:relative; text-decoration:none; color:#000;">
            <i class="fa-solid fa-bag-shopping" style="font-size:1.2rem;"></i>
            <span class="cart-count-badge" style="display:none; position: absolute; top: -8px; right: -12px; background: #c5a059; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 10px; font-weight: 700; min-width: 18px; text-align: center; display: flex; align-items: center; justify-content: center;">0</span>
        </a>
    </div>
</nav>

<div class="container">
  <h2>Checkout</h2>
  
  <div id="successMsg" class="success-msg" style="display:none">
    <h3>Order Confirmed! 🎉</h3>
    <p>Thank you for your purchase.</p>
    <a href="index.php" style="display:inline-block;margin-top:15px;color:#155724;text-decoration:underline">Continue Shopping</a>
  </div>

  <div class="checkout-grid" id="checkoutGrid">
    <!-- Left: Address & Details -->
    <div>
      <h3 style="margin-bottom:20px">Shipping Details</h3>
      
      <?php if ($isLoggedIn && !empty($addresses)): ?>
        <div class="form-group">
            <label>Select Saved Address</label>
            <div id="savedAddresses">
                <?php foreach($addresses as $idx => $addr): ?>
                    <div class="address-card <?php echo $idx === 0 ? 'selected' : ''; ?>" onclick="selectAddress(this, <?php echo htmlspecialchars(json_encode($addr)); ?>)">
                        <input type="radio" name="addr" <?php echo $idx === 0 ? 'checked' : ''; ?>>
                        <strong><?php echo htmlspecialchars($user['name'] ?? ''); ?></strong><br>
                        <?php echo htmlspecialchars($addr['street'] ?? ''); ?><br>
                        <?php echo htmlspecialchars(($addr['city'] ?? '') . ', ' . ($addr['state'] ?? '') . ' - ' . ($addr['zip'] ?? '')); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <p style="margin:15px 0;text-align:center;color:#666">- OR -</p>
            <button type="button" onclick="toggleManualAddress()" style="background:none;border:none;color:#007bff;cursor:pointer;text-decoration:underline">Enter New Address</button>
        </div>
      <?php endif; ?>

      <form id="addressForm" style="<?php echo ($isLoggedIn && !empty($addresses)) ? 'display:none' : 'display:block'; ?>">
        <div class="form-group">
          <label>Full Name</label>
          <input id="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required />
        </div>
        <div class="form-group">
          <label>Email</label>
          <input id="email" type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required />
        </div>
        <div class="form-group">
            <label>Street Address</label>
            <input id="street" required />
        </div>
        <div class="form-group" style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
            <div><label>City</label><input id="city" required /></div>
            <div><label>State</label><input id="state" required /></div>
        </div>
        <div class="form-group" style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
            <div><label>Zip Code</label><input id="zip" required /></div>
            <div><label>Phone</label><input id="phone" required /></div>
        </div>
      </form>
    </div>
    
    <!-- Right: Summary & Pay -->
    <div>
      <h3 style="margin-bottom:20px">Order Summary</h3>
      <div id="cartSummary" style="background:#f9f9f9;padding:20px;border-radius:8px"></div>
      
      <div style="margin-top:20px">
        <button id="codBtn" class="btn btn-cod">Confirm Order (Cash on Delivery) 🚚</button>
      </div>
      <div id="msg" style="margin-top:10px;text-align:center;color:#dc3545"></div>
    </div>
  </div>
</div>

<script>
const CART_KEY = 'urbanwear_cart';
const SUMMARY_KEY = 'urbanwear_cart_summary';
const API_ROOT = 'http://localhost:5000';
const AUTH_TOKEN = "<?php echo $token; ?>";
const IS_LOGGED_IN = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

// Selected address state
let selectedAddress = <?php echo ($isLoggedIn && !empty($addresses)) ? json_encode($addresses[0]) : 'null'; ?>;
let useManualAddress = <?php echo ($isLoggedIn && !empty($addresses)) ? 'false' : 'true'; ?>;

function getCart(){
  return JSON.parse(localStorage.getItem(CART_KEY) || '[]');
}

async function initCheckout() {
    console.log('[DEBUG] Initializing Checkout...');
    console.log('[DEBUG] API_ROOT:', API_ROOT);
    
    // Ensure we have latest data from backend before rendering
    if (typeof window.refreshCartFromBackend === 'function') {
        console.log('[DEBUG] Calling refreshCartFromBackend...');
        const backendData = await window.refreshCartFromBackend();
        console.log('[DEBUG] Backend Data Received on Checkout Page:', backendData);
    } else {
        console.error('[CRITICAL] refreshCartFromBackend not found! Check assets/js/cart.js');
    }
    renderSummary();
}

function renderSummary(){
    const cart = getCart();
    const el = document.getElementById('cartSummary');
    const summaryStored = localStorage.getItem(SUMMARY_KEY);
    
    console.log('[DEBUG] Rendering Summary. Cart:', cart);
    console.log('[DEBUG] Backend Summary Stored:', summaryStored);

    if (!cart || cart.length === 0) {
      el.innerHTML = '<p>Cart is empty. <a href="index.php">Go shop</a></p>';
      document.getElementById('codBtn').disabled = true;
      return;
    }

    const summary = summaryStored ? JSON.parse(summaryStored) : { totalMRP:0, totalDiscount:0, subtotal:0, totalGST:0, deliveryFee:49, grandTotal:0 };

    let itemsHtml = '';
    cart.forEach(item => {
      const price = parseFloat(item.price) || 0;
      const qty = parseInt(item.qty) || 1;
      const disc = parseFloat(item.discount_percentage) || 0;
      
      itemsHtml += `
        <div class="summary-item">
            <div style="flex:1">
                <strong>${item.name}</strong> × ${qty}
                <div style="font-size:12px; color:#666">
                    MRP: ₹${price.toLocaleString('en-IN')} ${disc > 0 ? `| ${disc}% OFF` : ''}
                </div>
            </div>
            <div style="text-align:right">
                ₹${Math.round(item.taxableValue || (price * qty * (1 - disc/100))).toLocaleString('en-IN')}
                <div style="font-size:11px; color:#888">+ GST</div>
            </div>
        </div>`;
    });

    window.orderTotal = summary.grandTotal;

    // RESTORATION: Strictly use backend summary fields for breakdown
    el.innerHTML = itemsHtml + 
      `<div style="border-top:1px dashed #ddd; margin-top:10px; padding-top:10px;">
          <div class="summary-item"><span>Total MRP</span><span>₹${summary.totalMRP.toLocaleString('en-IN')}</span></div>
          <div class="summary-item" style="color:#28a745"><span>Total Discount</span><span>-₹${summary.totalDiscount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
          <div class="summary-item" style="font-weight:600"><span>Subtotal</span><span>₹${summary.subtotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
          <div class="summary-item"><span>Total GST</span><span>+₹${summary.totalGST.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>
          <div class="summary-item"><span>Delivery Fee</span><span>₹${summary.deliveryFee}</span></div>
       </div>
       <div class="total-row">
          <span>Final Total</span><span>₹${summary.grandTotal.toLocaleString('en-IN')}</span>
       </div>`;
}

function selectAddress(el, addr) {
    document.querySelectorAll('.address-card').forEach(c => c.classList.remove('selected'));
    document.querySelectorAll('.address-card input').forEach(i => i.checked = false);
    el.classList.add('selected');
    el.querySelector('input').checked = true;
    
    selectedAddress = addr;
    useManualAddress = false;
    document.getElementById('addressForm').style.display = 'none';
}

function toggleManualAddress() {
    useManualAddress = true;
    selectedAddress = null;
    document.getElementById('addressForm').style.display = 'block';
    
    document.querySelectorAll('.address-card').forEach(c => c.classList.remove('selected'));
    document.querySelectorAll('.address-card input').forEach(i => i.checked = false);
}

function getOrderPayload() {
    const cart = getCart();
    const summaryStored = localStorage.getItem(SUMMARY_KEY);
    const summary = summaryStored ? JSON.parse(summaryStored) : { grandTotal: 0 };
    
    let shipping = {};
    if (useManualAddress) {
        shipping = {
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            address: document.getElementById('street').value,
            city: document.getElementById('city').value,
            state: document.getElementById('state').value,
            zip: document.getElementById('zip').value,
            mobile: document.getElementById('phone').value
        };
        if(!shipping.name || !shipping.email || !shipping.address || !shipping.mobile) {
            alert("Please fill all details");
            return null;
        }
    } else {
        if(!selectedAddress) {
            alert("Please select an address");
            return null;
        }
        shipping = {
            name: "<?php echo $user['name'] ?? ''; ?>",
            email: "<?php echo $user['email'] ?? ''; ?>",
            address: selectedAddress.street,
            city: selectedAddress.city,
            state: selectedAddress.state,
            zip: selectedAddress.zip,
            mobile: selectedAddress.phone
        };
    }

    return {
        products: cart.map(i => ({
            productId: i.id,
            quantity: i.qty || 1,
            size: i.size || 'M',
            color: i.color || 'Black'
        })),
        shipping: shipping, 
        paymentMethod: 'COD',
        totalAmount: summary.grandTotal
    };
}

document.getElementById('codBtn').addEventListener('click', async () => {
    const payload = getOrderPayload();
    if(!payload) return;
    
    const btn = document.getElementById('codBtn');
    btn.disabled = true;
    btn.innerText = 'Processing...';
    
    try {
        const url = API_ROOT + '/api/v1/orders';
        const headers = { 
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + AUTH_TOKEN 
        };

        const r = await fetch(url, { method: 'POST', headers, body: JSON.stringify(payload) });
        const d = await r.json();

        if (d.success) {
            localStorage.removeItem(CART_KEY);
            localStorage.removeItem(SUMMARY_KEY);
            document.getElementById('checkoutGrid').style.display = 'none';
            document.getElementById('successMsg').style.display = 'block';
        } else {
            throw new Error(d.message);
        }
    } catch (e) {
        alert("Order Failed: " + e.message);
        btn.disabled = false;
        btn.innerText = 'Confirm Order (Cash on Delivery) 🚚';
    }
});

document.addEventListener('DOMContentLoaded', initCheckout);
</script>

<script>
    window.IS_LOGGED_IN = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
    window.AUTH_TOKEN = '<?php echo $token; ?>';
</script>
<script src="assets/js/cart.js"></script>
</body>
</html>
