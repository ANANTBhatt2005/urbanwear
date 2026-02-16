<?php
/**
 * PREMIUM USER DASHBOARD - Connected to Node.js Backend
 * 
 * Complete redesign for URBANWEAR brand.
 * Includes: Account Overview, My Orders, Address Management, Order Cancellation.
 */

session_start();
require_once 'api-helper.php';

// Require login
requireLogin();

// Get user info from session
$user = getCurrentUser();
$token = getAuthToken();

$orders = [];
$addresses = [];
$profile_error = '';
$profile_success = '';

// Fetch user data
if ($token) {
    // 0. Fetch Full Profile (for Member Since)
    $profileRes = $API->get('/api/v1/auth/me', $token);
    if ($profileRes['success']) {
        $user = $profileRes['data'];
    }

    // 1. Fetch Orders
    $orderResponse = $API->get('/api/v1/orders', $token);
    if ($orderResponse['success']) {
        $orders = $orderResponse['data'] ?? [];
    }
    
    // 2. Fetch Addresses
    $userResponse = $API->get('/api/v1/addresses', $token); // address.controller returns addresses array
    if ($userResponse['success']) {
        $addresses = $userResponse['data'] ?? [];
    }
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // --> Cancel Order
    if ($_POST['action'] === 'cancel_order') {
        $orderId = $_POST['order_id'];
        $resp = $API->put("/api/v1/orders/$orderId/cancel", [], $token);
        if ($resp['success']) {
            $profile_success = "Order #$orderId has been cancelled.";
            // Refresh orders
            $orderResponse = $API->get('/api/v1/orders', $token);
            $orders = $orderResponse['data'] ?? [];
        } else {
            $profile_error = "Cancellation failed: " . ($resp['message'] ?? 'Error');
        }
    }

    // --> Add/Edit Address
    if ($_POST['action'] === 'save_address') {
        $addrId = $_POST['address_id'] ?? null;
        $payload = [
            'name' => $_POST['name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'street' => $_POST['street'] ?? '',
            'city' => $_POST['city'] ?? '',
            'state' => $_POST['state'] ?? '',
            'zip' => $_POST['zip'] ?? '',
            'country' => 'India'
        ];
        
        if ($addrId) {
            // Update
            $resp = $API->put("/api/v1/addresses/$addrId", $payload, $token);
            if ($resp['success']) {
                $profile_success = "Address updated successfully.";
                $addresses = $resp['data'] ?? [];
            } else {
                $profile_error = "Error updating address: " . $resp['message'];
            }
        } else {
            // Add
            $resp = $API->post('/api/v1/addresses', $payload, $token);
            if ($resp['success']) {
                $profile_success = "Address added successfully.";
                $addresses = $resp['data'] ?? [];
            } else {
                $profile_error = "Error adding address: " . $resp['message'];
            }
        }
    }

    // --> Delete Address
    if ($_POST['action'] === 'delete_address') {
        $addrId = $_POST['address_id'];
        $resp = $API->delete("/api/v1/addresses/$addrId", $token);
        if ($resp['success']) {
            $profile_success = "Address deleted.";
            $addresses = $resp['data'] ?? [];
        } else {
            $profile_error = "Error deleting address.";
        }
    }
}

// Calculate Total Spent for Overview
$totalSpent = 0;
foreach($orders as $o) {
    if(($o['orderStatus'] ?? '') !== 'CANCELLED') {
        $totalSpent += ($o['totalAmount'] ?? 0);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account – URBANWEAR</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Premium Dashboard CSS -->
    <link rel="stylesheet" href="css/premium-dashboard.css">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body>

    <div class="dashboard-container">
        
        <!-- Sidebar -->
        <aside class="dashboard-sidebar">
            <div class="user-profile-summary">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                <p style="color:var(--text-muted); font-size:14px;"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            
            <nav class="nav-menu">
                <div class="nav-item">
                    <a class="nav-link active" onclick="switchSection('overview', this)">
                        <i class="fa-solid fa-house"></i> Overview
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" onclick="switchSection('orders', this)">
                        <i class="fa-solid fa-bag-shopping"></i> My Orders
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" onclick="switchSection('addresses', this)">
                        <i class="fa-solid fa-location-dot"></i> Addresses
                    </a>
                </div>
                <div class="nav-item" style="margin-top: 50px;">
                    <a href="index.php" class="nav-link">
                        <i class="fa-solid fa-arrow-left"></i> Back to Shop
                    </a>
                    <a href="logout-connected.php" class="nav-link" style="color: var(--brand-danger);">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="dashboard-main">
            
            <!-- Global Alerts -->
            <?php if ($profile_error): ?>
                <div style="background: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 12px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($profile_error); ?>
                </div>
            <?php endif; ?>
            <?php if ($profile_success): ?>
                <div style="background: #dcfce7; color: #15803d; padding: 15px; border-radius: 12px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($profile_success); ?>
                </div>
            <?php endif; ?>

            <!-- OVERVIEW SECTION -->
            <section id="overview" class="dashboard-content active">
                <div class="section-header">
                    <h2 class="section-title">Account Overview</h2>
                </div>
                
                <div class="overview-grid">
                    <div class="stat-card">
                        <div class="stat-label">Total Spent</div>
                        <div class="stat-value">₹<?php echo number_format($totalSpent); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Orders Placed</div>
                        <div class="stat-value"><?php echo count($orders); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Member Status</div>
                        <div class="stat-value" style="color:var(--brand-success); font-size: 20px;">✓ Active Citizen</div>
                    </div>
                </div>

                <div class="stat-card" style="margin-bottom: 30px;">
                    <h4 style="margin-bottom: 15px;">Profile Details</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <p class="stat-label">Full Name</p>
                            <p style="font-weight:600;"><?php echo htmlspecialchars($user['name']); ?></p>
                        </div>
                        <div>
                            <p class="stat-label">Email Address</p>
                            <p style="font-weight:600;"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <div>
                            <p class="stat-label">Member Since</p>
                            <p style="font-weight:600;"><?php echo date('F d, Y', strtotime($user['createdAt'] ?? 'now')); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Recent Order Mini-List -->
                <div class="section-header">
                    <h3 style="font-size: 18px;">Recent Order</h3>
                    <a onclick="switchSection('orders', document.querySelector('.nav-link[onclick*=\"orders\"]'))" style="font-size: 14px; color: var(--brand-accent); cursor:pointer;">See all</a>
                </div>
                <?php if(!empty($orders)): $o = $orders[0]; ?>
                    <div class="order-card">
                        <div class="order-info">
                            <h4>Order #<?php echo substr($o['_id'], -6); ?></h4>
                            <p class="order-meta"><?php echo date('M d, Y', strtotime($o['createdAt'])); ?> • ₹<?php echo number_format($o['totalAmount']); ?></p>
                        </div>
                        <div class="order-actions" style="display:flex; align-items:center; gap:15px;">
                            <span class="order-status status-<?php echo strtolower($o['orderStatus']); ?>"><?php echo $o['orderStatus']; ?></span>
                            <button class="btn btn-outline btn-sm" onclick="viewOrderDetails('<?php echo $o['_id']; ?>')">Details</button>
                        </div>
                    </div>
                <?php else: ?>
                    <p style="color:var(--text-muted);">No orders yet.</p>
                <?php endif; ?>
            </section>

            <!-- ORDERS SECTION -->
            <section id="orders" class="dashboard-content">
                <div class="section-header">
                    <h2 class="section-title">My Orders</h2>
                </div>
                
                <div class="order-list">
                    <?php if(empty($orders)): ?>
                        <div style="text-align:center; padding: 60px 0;">
                            <i class="fa-solid fa-box-open" style="font-size: 48px; color: #ddd; margin-bottom: 20px;"></i>
                            <p style="color:var(--text-muted);">You haven't placed any orders yet.</p>
                            <a href="index.php" class="btn btn-primary" style="margin-top: 20px;">Start Shopping</a>
                        </div>
                    <?php else: ?>
                        <?php foreach($orders as $o): 
                            $status = $o['orderStatus'] ?? 'Placed';
                            $canCancel = in_array($status, ['Placed', 'Processing', 'CONFIRMED', 'Pending']);
                        ?>
                            <div class="order-card">
                                <div class="order-info">
                                    <h4>Order #<?php echo substr($o['_id'], -8); ?></h4>
                                    <p class="order-meta">
                                        <?php echo date('D, M d, Y', strtotime($o['createdAt'])); ?> • 
                                        <span style="font-weight:700; color:var(--brand-black);">₹<?php echo number_format($o['totalAmount']); ?></span>
                                    </p>
                                    <p class="order-meta" style="margin-top:5px; font-size:12px;">Payment: <?php echo $o['paymentMethod'] ?? 'COD'; ?> (<?php echo $o['paymentStatus'] ?? 'Pending'; ?>)</p>
                                </div>
                                <div class="order-actions" style="display:flex; align-items:center; gap:12px;">
                                    <span class="order-status status-<?php echo strtolower($status); ?>"><?php echo $status; ?></span>
                                    <button class="btn btn-outline" onclick="viewOrderDetails('<?php echo $o['_id']; ?>')">View details</button>
                                    <?php if($canCancel): ?>
                                        <form method="POST" onsubmit="return confirm('Cancel this order?');" style="margin:0;">
                                            <input type="hidden" name="action" value="cancel_order">
                                            <input type="hidden" name="order_id" value="<?php echo $o['_id']; ?>">
                                            <button type="submit" class="btn btn-danger">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <!-- ADDRESSES SECTION -->
            <section id="addresses" class="dashboard-content">
                <div class="section-header">
                    <h2 class="section-title">Saved Addresses</h2>
                    <button class="btn btn-primary" onclick="openAddressModal()">+ Add New</button>
                </div>
                
                <div class="address-grid">
                    <?php foreach($addresses as $addr): ?>
                        <div class="address-card">
                            <div class="address-name"><?php echo htmlspecialchars($addr['name'] ?? $user['name']); ?></div>
                            <div class="address-details">
                                <?php echo htmlspecialchars($addr['street']); ?><br>
                                <?php echo htmlspecialchars($addr['city'] . ', ' . $addr['state'] . ' - ' . $addr['zip']); ?><br>
                                Phone: <?php echo htmlspecialchars($addr['phone']); ?>
                            </div>
                            <div class="address-actions">
                                <button class="btn btn-outline btn-sm" onclick='editAddress(<?php echo json_encode($addr); ?>)'>Edit</button>
                                <form method="POST" onsubmit="return confirm('Delete this address?');" style="margin:0;">
                                    <input type="hidden" name="action" value="delete_address">
                                    <input type="hidden" name="address_id" value="<?php echo $addr['_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if(empty($addresses)): ?>
                        <div style="grid-column: 1/-1; text-align:center; padding: 40px; border: 2px dashed #eee; border-radius: 16px;">
                            <p style="color:var(--text-muted);">No addresses saved yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

        </main>
    </div>

    <!-- ADDRESS MODAL -->
    <div id="addressModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('addressModal')">&times;</span>
            <h3 id="addrModalTitle" style="margin-bottom:20px;">Add New Address</h3>
            <form id="addressForm" method="POST">
                <input type="hidden" name="action" value="save_address">
                <input type="hidden" name="address_id" id="addr_id">
                
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" id="addr_name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Mobile Number</label>
                    <input type="text" name="phone" id="addr_phone" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Street Address</label>
                    <input type="text" name="street" id="addr_street" class="form-input" required>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">City</label>
                        <input type="text" name="city" id="addr_city" class="form-input" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">State</label>
                        <input type="text" name="state" id="addr_state" class="form-input" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Pincode</label>
                    <input type="text" name="zip" id="addr_zip" class="form-input" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;">Save Address</button>
            </form>
        </div>
    </div>

    <!-- ORDER DETAILS MODAL -->
    <div id="orderModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close-modal" onclick="closeModal('orderModal')">&times;</span>
            <h3>Order Details</h3>
            <div id="orderDetailsContent" style="margin-top:20px;">
                <!-- Filled by JS -->
                <p>Loading...</p>
            </div>
        </div>
    </div>

    <!-- REVIEW MODAL -->
    <div id="reviewModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <span class="close-modal" onclick="closeModal('reviewModal')">&times;</span>
            <h3>⭐ Rate this Product</h3>
            <div id="reviewFormContent" style="margin-top:20px;">
                <input type="hidden" id="review_product_id">
                <input type="hidden" id="review_order_id">
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 10px;">Your Rating</label>
                    <div class="stars-selector" id="reviewStarSelector" style="display: flex; gap: 8px; font-size: 32px;">
                        <span class="review-star" data-rating="1" onclick="selectRating(1)">☆</span>
                        <span class="review-star" data-rating="2" onclick="selectRating(2)">☆</span>
                        <span class="review-star" data-rating="3" onclick="selectRating(3)">☆</span>
                        <span class="review-star" data-rating="4" onclick="selectRating(4)">☆</span>
                        <span class="review-star" data-rating="5" onclick="selectRating(5)">☆</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Your Review (Optional)</label>
                    <textarea id="review_text" class="form-input" rows="4" placeholder="Share your experience with this product..."></textarea>
                </div>

                <button onclick="submitProductReview()" id="submitReviewBtn" class="btn btn-primary" style="width:100%;">Submit Review</button>
                <div id="reviewMessage" style="margin-top: 10px; font-size: 14px;"></div>
            </div>
        </div>
    </div>

    <style>
    .review-star {
        cursor: pointer;
        color: #ddd;
        transition: color 0.2s;
    }
    .review-star:hover,
    .review-star.active {
        color: #ffc107;
    }
    </style>

    <script>
        // Section Switching
        function switchSection(id, element) {
            document.querySelectorAll('.dashboard-content').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            
            document.getElementById(id).classList.add('active');
            if(element) element.classList.add('active');
        }

        // Modal Controls
        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        // Address Management
        function openAddressModal() {
            document.getElementById('addrModalTitle').innerText = 'Add New Address';
            document.getElementById('addr_id').value = '';
            document.getElementById('addressForm').reset();
            document.getElementById('addressModal').classList.add('active');
        }

        function editAddress(addr) {
            document.getElementById('addrModalTitle').innerText = 'Edit Address';
            document.getElementById('addr_id').value = addr._id;
            document.getElementById('addr_name').value = addr.name || '';
            document.getElementById('addr_phone').value = addr.phone || '';
            document.getElementById('addr_street').value = addr.street || '';
            document.getElementById('addr_city').value = addr.city || '';
            document.getElementById('addr_state').value = addr.state || '';
            document.getElementById('addr_zip').value = addr.zip || '';
            document.getElementById('addressModal').classList.add('active');
        }

        // View Order Details
        async function viewOrderDetails(id) {
            const modal = document.getElementById('orderModal');
            const content = document.getElementById('orderDetailsContent');
            modal.classList.add('active');
            content.innerHTML = '<p>Searching for order details...</p>';

            try {
                // Use the backend API directly via proxy logic if needed, 
                // but since we're in PHP, let's use a hidden endpoint or just a simple fetch if CORS is okay.
                // Assuming the backend is on port 5000 as per other files.
                const resp = await fetch('urbanwear-backend/src/api/orders/' + id, { // This path might be wrong depending on how you've set up proxies
                    // Alternatively, we can use a PHP proxy script or just rely on the data already in the $orders array if we find it.
                });
                
                // Let's find it in the already fetched PHP $orders array (converted to JS)
                const ordersData = <?php echo json_encode($orders); ?>;
                const o = ordersData.find(order => order._id === id);

                if (o) {
                    let itemsHtml = o.products.map(p => `
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:15px 0; border-bottom:1px solid #eee;">
                            <div style="flex:1;">
                                <p style="font-weight:600; margin-bottom:4px;">${p.productId?.title || 'Product'}</p>
                                <p style="font-size:12px; color:#666;">Size: ${p.size || 'M'} | Qty: ${p.quantity}</p>
                            </div>
                            <div style="text-align:right;">
                                <p style="font-weight:700; margin-bottom:8px;">₹${(p.price * p.quantity).toLocaleString()}</p>
                                <button class="btn btn-outline btn-sm" onclick="openReviewModal('${p.productId?._id || p.productId}', '${o._id}')" style="font-size:12px; padding:6px 12px;">
                                    ⭐ Rate Product
                                </button>
                            </div>
                        </div>
                    `).join('');

                    content.innerHTML = `
                        <div style="margin-bottom:20px;">
                            <p style="font-size:14px; color:#666;">Order ID: <b>#${o._id}</b></p>
                            <p style="font-size:14px; color:#666;">Placed on: ${new Date(o.createdAt).toLocaleDateString()} at ${new Date(o.createdAt).toLocaleTimeString()}</p>
                        </div>
                        <div style="background:#f9f9f9; padding:15px; border-radius:12px; margin-bottom:20px;">
                            <h5 style="margin-bottom:10px;">Ship to:</h5>
                            <p style="font-size:14px;">${o.shipping?.name || 'Customer'}</p>
                            <p style="font-size:14px; color:#666;">${o.shipping?.address || o.shipping?.street || ''}, ${o.shipping?.city || ''}</p>
                            <p style="font-size:14px; color:#666;">Phone: ${o.shipping?.mobile || o.shipping?.phone || ''}</p>
                        </div>
                        <h5 style="margin-bottom:10px;">Items Ordered:</h5>
                        ${itemsHtml}
                        <div style="margin-top:20px; text-align:right;">
                            <p style="font-size:14px;">Subtotal: ₹${o.totalAmount.toLocaleString()}</p>
                            <p style="font-size:18px; font-weight:800; margin-top:5px;">Total: ₹${o.totalAmount.toLocaleString()}</p>
                        </div>
                        <div style="margin-top:20px; padding-top:15px; border-top:1px solid #eee;">
                            <p style="font-size:12px; color:#999;">Payment Method: ${o.paymentMethod || 'COD'}</p>
                            <p style="font-size:12px; color:#999;">Status: ${o.orderStatus}</p>
                        </div>
                    `;
                } else {
                    content.innerHTML = '<p>Order not found.</p>';
                }
            } catch (e) {
                content.innerHTML = '<p>Error loading order details.</p>';
            }
        }

        // Review Functions
        let selectedReviewRating = 0;

        function selectRating(rating) {
            selectedReviewRating = rating;
            const stars = document.querySelectorAll('.review-star');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('active');
                    star.textContent = '★';
                } else {
                    star.classList.remove('active');
                    star.textContent = '☆';
                }
            });
        }

        function openReviewModal(productId, orderId) {
            document.getElementById('review_product_id').value = productId;
            document.getElementById('review_order_id').value = orderId;
            document.getElementById('review_text').value = '';
            document.getElementById('reviewMessage').innerHTML = '';
            selectedReviewRating = 0;
            
            // Reset stars
            document.querySelectorAll('.review-star').forEach(star => {
                star.classList.remove('active');
                star.textContent = '☆';
            });
            
            document.getElementById('reviewModal').classList.add('active');
        }

        async function submitProductReview() {
            const productId = document.getElementById('review_product_id').value;
            const orderId = document.getElementById('review_order_id').value;
            const reviewText = document.getElementById('review_text').value;
            const messageDiv = document.getElementById('reviewMessage');
            const submitBtn = document.getElementById('submitReviewBtn');

            if (selectedReviewRating === 0) {
                messageDiv.innerHTML = '<span style="color:#dc2626;">Please select a rating</span>';
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
            messageDiv.innerHTML = '';

            try {
                const response = await fetch('http://localhost:5000/api/v1/reviews', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer <?php echo getAuthToken(); ?>'
                    },
                    body: JSON.stringify({
                        productId: productId,
                        orderId: orderId,
                        rating: selectedReviewRating,
                        reviewText: reviewText
                    })
                });

                const data = await response.json();

                if (data.success) {
                    messageDiv.innerHTML = '<span style="color:#15803d;">✓ Review submitted successfully!</span>';
                    setTimeout(() => {
                        closeModal('reviewModal');
                        location.reload();
                    }, 1500);
                } else {
                    messageDiv.innerHTML = `<span style="color:#dc2626;">${data.message || 'Error submitting review'}</span>`;
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Review';
                }
            } catch (error) {
                messageDiv.innerHTML = '<span style="color:#dc2626;">Network error. Please try again.</span>';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Review';
            }
        }
    </script>
</body>
</html>
