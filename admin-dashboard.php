<?php
/**
 * ADMIN DASHBOARD - Connected to Node.js Backend
 * 
 * Complete admin panel for:
 * - Viewing dashboard statistics
 * - Managing products (add, edit, delete)
 * - Managing orders
 * - Managing users
 * All data synced with MongoDB via Node.js API
 */

session_start();
require_once 'api-helper.php';

// Require admin login
requireAdmin();

$user = getCurrentUser();
$token = getAuthToken();
$stats = [];
$products = [];
$orders = [];
$users = [];

$error = '';
$success = '';

// Fetch dashboard statistics
$response = $API->get('/api/v1/admin/dashboard', $token);
if ($response['success']) {
    // admin controller returns { success, message, stats: { ... } }
    $stats = $response['raw']['stats'] ?? $response['data'] ?? [];
}

// Fetch products (admin route for full list + admin-only data)
$response = $API->get('/api/v1/admin/products', $token);
if ($response['success']) {
    // controllers return data: [products...]
    $products = $response['data'] ?? $response['raw']['data'] ?? [];
} else {
    // Fall back to public products endpoint for compatibility
    $response = $API->get('/api/v1/products?limit=50', $token);
    if ($response['success']) {
        $products = $response['data'] ?? $response['raw']['data'] ?? [];
    }
}

// Fetch orders
$response = $API->get('/api/v1/admin/orders', $token);
if ($response['success']) {
    $orders = $response['data'] ?? [];
    // Filter to only show CONFIRMED orders
    $orders = array_filter($orders, function($order) {
        $status = $order['orderStatus'] ?? $order['status'] ?? '';
        return in_array($status, ['PAID', 'CONFIRMED', 'Shipped', 'Delivered']);
    });
}

// New order notification (orders in last 15 minutes)
$newOrders = [];
foreach ($orders as $o) {
    $created = strtotime($o['createdAt'] ?? $o['created_at'] ?? null);
    if ($created && $created >= time() - 15*60) {
        $newOrders[] = $o;
    }
}
$newOrderCount = count($newOrders);

// Additional analytics
$response = $API->get('/api/v1/admin/analytics/top-selling-products', $token);
$topSelling = $response['success'] ? ($response['data'] ?? []) : [];

$response = $API->get('/api/v1/admin/analytics/sales', $token);
$salesAnalytics = $response['success'] ? ($response['data'] ?? []) : [];

$response = $API->get('/api/v1/admin/analytics/reviews', $token);
$reviewsOverview = $response['success'] ? ($response['data'] ?? []) : [];

// Trending products for admin view (read-only)
$response = $API->get('/api/v1/products/trending', $token);
$trendingProducts = $response['success'] ? ($response['data'] ?? []) : [];

// Visual Decision Support Analytics (REQUIRED FOR CHARTS)
$response = $API->get('/api/v1/admin/analytics/visual', $token);
$visualAnalytics = $response['success'] ? ($response['data'] ?? []) : [];

// --- NEW: Advanced Decision Support Intelligence ---
$response = $API->get('/api/v1/admin/analytics/advanced-dss', $token);
$advancedDSS = $response['success'] ? ($response['data'] ?? []) : [
    'overstockInsights' => [],
    'customerBehavior' => ['mostReviewed'=>[], 'lowRated'=>[], 'mostRated'=>[]],
    'orderSummary' => ['totalOrders'=>0, 'cancelledOrders'=>0, 'recentOrders'=>[]]
];

// Handle product operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_product':
            $product_data = [
                'title' => $_POST['title'] ?? '',
                'price' => (float)($_POST['price'] ?? 0),
                'discount_percentage' => (float)($_POST['discount'] ?? 0),
                'stock' => (int)($_POST['stock'] ?? 0),
                'category' => $_POST['category'] ?? '',
                'description' => $_POST['description'] ?? ''
            ];

            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $product_data['image'] = new CURLFile(
                    $_FILES['image']['tmp_name'], 
                    $_FILES['image']['type'], 
                    $_FILES['image']['name']
                );
            }
            
            // Use correct admin API v1 endpoint
            $response = $API->post('/api/v1/admin/products', $product_data, $token);
            if ($response['success']) {
                $success = 'Product added successfully';
                header('Location: /clothing_project/admin-dashboard.php');
                exit;
            } else {
                $error = $response['message'] ?? 'Failed to add product';
                error_log('Add product failed: ' . json_encode($response));
            }
            break;

        case 'update_product':
            $product_id = $_POST['product_id'] ?? '';
            $product_data = [
                'title' => $_POST['title'] ?? '',
                'price' => (float)($_POST['price'] ?? 0),
                'discount_percentage' => (float)($_POST['discount'] ?? 0),
                'stock' => (int)($_POST['stock'] ?? 0),
                'category' => $_POST['category'] ?? '',
                'description' => $_POST['description'] ?? ''
            ];

            if (!$product_id) {
                $error = 'Missing product id for update';
                break;
            }

            // Handle image update
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $product_data['image'] = new CURLFile(
                    $_FILES['image']['tmp_name'], 
                    $_FILES['image']['type'], 
                    $_FILES['image']['name']
                );
            }

            $response = $API->put('/api/v1/admin/products/' . $product_id, $product_data, $token);
            if ($response['success']) {
                $success = 'Product updated successfully';
                header('Location: /clothing_project/admin-dashboard.php');
                exit;
            } else {
                $error = $response['message'] ?? 'Failed to update product';
                error_log('Update product failed: ' . json_encode($response));
            }
            break;

        case 'delete_product':
            $product_id = $_POST['product_id'] ?? '';
            if (!$product_id) {
                $error = 'Missing product id for delete';
                break;
            }

            $response = $API->delete('/api/v1/admin/products/' . $product_id, $token);
            if ($response['success']) {
                $success = 'Product deleted successfully';
                header('Location: /clothing_project/admin-dashboard.php');
                exit;
            } else {
                $error = $response['message'] ?? 'Failed to delete product';
                error_log('Delete product failed: ' . json_encode($response));
            }
            break;
        
        case 'update_order_status':
            $order_id = $_POST['order_id'] ?? '';
            $status = $_POST['status'] ?? '';
            
            $response = $API->put('/api/v1/admin/orders/' . $order_id . '/status', ['orderStatus' => $status], $token);
            if ($response['success']) {
                $success = 'Order status updated';
                header('Location: /clothing_project/admin-dashboard.php');
                exit;
            } else {
                $error = $response['message'] ?? 'Failed to update order status';
            }
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | URBANWEAR</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary: #1a1a1a;
            --accent: #2563eb;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg: #f3f4f6;
            --card-bg: #ffffff;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border: #e5e7eb;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Inter', sans-serif; }
        
        body { background: var(--bg); color: var(--text-main); padding-bottom: 60px; }

        /* Header */
        .admin-header {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 50;
        }
        
        .brand { font-size: 1.25rem; font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 8px; }
        .nav-links a { color: var(--text-muted); text-decoration: none; margin-left: 24px; font-weight: 500; transition: 0.2s; }
        .nav-links a:hover, .nav-links a.active { color: var(--accent); }
        .logout { color: var(--danger) !important; }

        /* Main Config */
        .container { max-width: 1400px; margin: 2rem auto; padding: 0 1.5rem; }
        
        /* Alerts */
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 12px; font-weight: 500; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

        /* KPI Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card {
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid var(--border);
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-label { color: var(--text-muted); font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; }
        .stat-value { font-size: 2rem; font-weight: 700; color: var(--primary); }
        .stat-trend { font-size: 0.875rem; margin-top: 0.5rem; font-weight: 500; }
        .trend-up { color: var(--success); }

        /* Dashboard Main Layout */
        .dashboard-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
        @media (max-width: 1024px) { .dashboard-layout { grid-template-columns: 1fr; } }

        .card { background: var(--card-bg); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--border); overflow: hidden; }
        .card-header { padding: 1.25rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .card-title { font-size: 1rem; font-weight: 600; color: var(--primary); }
        .card-body { padding: 1.25rem; }

        /* Tables */
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th { text-align: left; padding: 0.75rem 1rem; color: var(--text-muted); font-weight: 600; border-bottom: 1px solid var(--border); background: #f9fafb; white-space: nowrap; }
        td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); color: var(--text-main); }
        tr:last-child td { border-bottom: none; }
        
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: inline-block; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-cancelled { background: #f3f4f6; color: #374151; }
        
        .stock-badge { font-weight: 700; }
        .text-danger { color: var(--danger); }

        /* Buttons & Forms */
        .btn { padding: 0.5rem 1rem; border-radius: 6px; font-weight: 500; border: none; cursor: pointer; transition: 0.2s; font-size: 0.875rem; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: #000; }
        .btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text-main); }
        .btn-outline:hover { background: #f9fafb; border-color: #d1d5db; }
        .btn-danger { background: #fee2e2; color: #991b1b; }

        input, select, textarea { width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 6px; margin-top: 4px; font-family: inherit; font-size: 0.9rem; }
        input:focus { outline: none; border-color: var(--accent); ring: 2px solid rgba(37,99,235,0.1); }
        
        /* Modal */
        .modal { position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index: 1000; display: none; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: white; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; border-radius: 12px; padding: 2rem; }
    </style>
</head>
<body>

<div class="admin-header">
    <div class="brand">
        🛍️ URBANWEAR <span style="font-weight:400; color:var(--text-muted); font-size:0.9rem;">Admin</span>
    </div>
    <div class="nav-links">
        <a href="admin-dashboard.php" class="active">Overview</a>
        <a href="admin/orders.php">Orders</a>
        <a href="logout-connected.php" class="logout">Logout</a>
    </div>
</div>

<div class="container">
    
    <!-- System Alerts -->
    <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

    <?php 
        $lowStock = $stats['lowStockCount'] ?? 0; 
        if($lowStock > 0):
    ?>
    <div class="alert alert-warning">
        ⚠️ <strong>Inventory Alert:</strong> &nbsp; <?php echo $lowStock; ?> products are running low on stock (≤ 5 units).
    </div>
    <?php endif; ?>

    <!-- KPI Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">₹<?php echo isset($stats['totalSales']) ? number_format($stats['totalSales'], 0) : '0'; ?></div>
            <div class="stat-trend trend-up">Lifetime Volume</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value"><?php echo isset($stats['totalOrders']) ? $stats['totalOrders'] : '0'; ?></div>
            <div class="stat-trend">All time</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active Products</div>
            <div class="stat-value"><?php echo isset($stats['totalProducts']) ? $stats['totalProducts'] : '0'; ?></div>
            <div class="stat-trend"><?php echo $lowStock > 0 ? "<span class='text-danger'>{$lowStock} Low Stock</span>" : 'In Stock'; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Users</div>
            <div class="stat-value"><?php echo isset($stats['totalUsers']) ? $stats['totalUsers'] : '0'; ?></div>
            <div class="stat-trend">Registered Customers</div>
        </div>
    </div>

    <!-- ADVANCED DECISION SUPPORT INTELLIGENCE (NEW) -->
    <div style="margin-bottom: 2rem;">
        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--primary); margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;">
            🧠 Decision Support Intelligence <span style="font-size: 0.75rem; background: var(--accent); color: white; padding: 2px 8px; border-radius: 4px; font-weight: 500;">ADVANCED</span>
        </h2>
        
        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
            
            <!-- Inventory Intelligence -->
            <div class="card">
                <div class="card-header" style="background: #f8fafc;">
                    <div class="card-title">📦 Inventory Intelligence</div>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 15px;">
                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--danger); text-transform: uppercase;">Critical Low Stock</div>
                        <div style="font-size: 1.5rem; font-weight: 700;"><?php echo count($visualAnalytics['lowStockProducts'] ?? []); ?> Items</div>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--warning); text-transform: uppercase;">Overstock Insight (Low Velocity)</div>
                        <div style="font-size: 0.9rem; margin-top: 5px;">
                            <?php if(empty($advancedDSS['overstockInsights'])): ?>
                                <span style="color: var(--success);">✔ No overstock issues detected</span>
                            <?php else: ?>
                                <ul style="list-style: none;">
                                    <?php foreach(array_slice($advancedDSS['overstockInsights'], 0, 3) as $os): ?>
                                        <li style="padding: 4px 0; border-bottom: 1px solid #f1f5f9;">
                                            <strong><?php echo htmlspecialchars($os['title']); ?></strong>: <?php echo $os['stock']; ?> units (<?php echo $os['sales']; ?> sales)
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Behavior Insights -->
            <div class="card">
                <div class="card-header" style="background: #fdf2f2;">
                    <div class="card-title">👥 Customer Behavior Insights</div>
                </div>
                <div class="card-body">
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div>
                            <div style="font-size: 0.75rem; font-weight: 700; color: #b91c1c; text-transform: uppercase;">Lowest Rated Products</div>
                            <?php foreach(array_slice($advancedDSS['customerBehavior']['lowRated'], 0, 2) as $lr): ?>
                                <div style="font-size: 0.85rem; display: flex; justify-content: space-between; margin-top: 4px;">
                                    <span><?php echo htmlspecialchars($lr['title']); ?></span>
                                    <span style="font-weight: 700; color: var(--danger);"><?php echo $lr['avgRating']; ?>★</span>
                                </div>
                            <?php endforeach; ?>
                            <?php if(empty($advancedDSS['customerBehavior']['lowRated'])) echo "<div style='font-size:0.85rem; color:var(--success)'>All products have healthy ratings</div>"; ?>
                        </div>
                        <div style="border-top: 1px solid #fee2e2; padding-top: 8px;">
                            <div style="font-size: 0.75rem; font-weight: 700; color: #047857; text-transform: uppercase;">Customer Favorites (Most Rated)</div>
                            <?php foreach(array_slice($advancedDSS['customerBehavior']['mostRated'], 0, 2) as $mr): ?>
                                <div style="font-size: 0.85rem; display: flex; justify-content: space-between; margin-top: 4px;">
                                    <span><?php echo htmlspecialchars($mr['title']); ?></span>
                                    <span style="font-weight: 700; color: var(--success);"><?php echo $mr['avgRating']; ?>★</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary Intelligence -->
            <div class="card">
                <div class="card-header" style="background: #f0f9ff;">
                    <div class="card-title">📊 Order Summary Insights</div>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                        <div style="background: #fff; padding: 10px; border-radius: 8px; border: 1px solid #e0f2fe;">
                            <div style="font-size: 0.7rem; font-weight: 700; color: #0369a1; text-transform: uppercase;">Total Volume</div>
                            <div style="font-size: 1.2rem; font-weight: 700;"><?php echo $advancedDSS['orderSummary']['totalOrders']; ?></div>
                        </div>
                        <div style="background: #fff; padding: 10px; border-radius: 8px; border: 1px solid #fef2f2;">
                            <div style="font-size: 0.7rem; font-weight: 700; color: #b91c1c; text-transform: uppercase;">Cancellations</div>
                            <div style="font-size: 1.2rem; font-weight: 700;"><?php echo $advancedDSS['orderSummary']['cancelledOrders']; ?></div>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Recent Performance</div>
                        <div style="font-size: 0.8rem;">
                            Avg. Order Value: <strong>₹<?php 
                                $total = array_sum(array_column($advancedDSS['orderSummary']['recentOrders'], 'amount'));
                                $count = count($advancedDSS['orderSummary']['recentOrders']);
                                echo $count > 0 ? number_format($total / $count) : 0;
                            ?></strong>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Analytics Section -->
    <div class="dashboard-layout">
        <!-- Sales Trend Line Chart -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Orders Sales Trend (Last 30 Days)</div>
            </div>
            <div class="card-body">
                <canvas id="ordersTrendChart" height="100"></canvas>
            </div>
        </div>
        
        <!-- Category Distribution Pie Chart -->
        <div class="card">
             <div class="card-header">
                <div class="card-title">Category-wise Demand</div>
            </div>
             <div class="card-body">
                <canvas id="categoryChart" height="180"></canvas>
             </div>
        </div>
    </div>

    <div class="dashboard-layout">
        <!-- Order Status Bar Chart -->
        <div class="card">
             <div class="card-header">
                <div class="card-title">Top Selling Products</div>
            </div>
             <div class="card-body">
                <canvas id="statusChart" height="120"></canvas>
             </div>
        </div>

        <!-- Recent Activity List -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Recent Orders</div>
                <a href="admin/orders.php" class="btn btn-outline btn-sm">View All</a>
            </div>
            <div class="card-body p-0">
                <table style="width:100%">
                    <tbody>
                        <?php 
                        $recentOrders = array_slice($orders, 0, 5);
                        foreach($recentOrders as $ro): 
                            $s = $ro['orderStatus'] ?? $ro['status'] ?? 'Placed';
                            $amt = $ro['total'] ?? $ro['totalAmount'] ?? 0;
                        ?>
                        <tr>
                            <td style="padding:12px; border-bottom:1px solid #eee;">
                                <div style="font-weight:600">Order #<?php echo substr($ro['_id'], -6); ?></div>
                                <div style="font-size:0.8rem; color:var(--text-muted)"><?php echo isset($ro['createdAt']) ? date('M d', strtotime($ro['createdAt'])) : 'N/A'; ?></div>
                            </td>
                            <td style="padding:12px; border-bottom:1px solid #eee; text-align:right;">
                                <div style="font-weight:700">₹<?php echo number_format($amt); ?></div>
                                <span class="status-badge" style="font-size:0.7rem; background:#f3f4f6; color:#111;"><?php echo htmlspecialchars($s); ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if(empty($recentOrders)) echo "<div style='padding:20px; text-align:center; color:#999'>No recent orders</div>"; ?>
            </div>
        </div>
    </div>
    
    <!-- Management Section -->
    <div class="dashboard-layout" style="margin-bottom: 2rem;">
        <!-- Low Stock Indicator -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Low Stock Alert (Stock ≤ 10)</div>
            </div>
            <div class="card-body p-0">
                <table style="width:100%">
                    <thead>
                        <tr>
                            <th style="font-size: 0.75rem;">Product</th>
                            <th style="font-size: 0.75rem; text-align: right;">Current Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $lowStockItems = $visualAnalytics['lowStockProducts'] ?? [];
                        foreach($lowStockItems as $lsi): 
                        ?>
                        <tr>
                            <td style="padding:10px; border-bottom:1px solid #eee;">
                                <div style="font-weight:500; font-size: 0.85rem;"><?php echo htmlspecialchars($lsi['title']); ?></div>
                                <div style="font-size:0.75rem; color:var(--text-muted)"><?php echo ucfirst($lsi['category']); ?></div>
                            </td>
                            <td style="padding:10px; border-bottom:1px solid #eee; text-align:right;">
                                <span class="status-badge <?php echo ($lsi['stock'] <= 5) ? 'btn-danger' : 'status-pending'; ?>" style="font-size:0.75rem;">
                                    <?php echo $lsi['stock']; ?> left
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if(empty($lowStockItems)) echo "<div style='padding:40px; text-align:center; color:#999'>All stock levels healthy ✅</div>"; ?>
            </div>
        </div>

        <!-- System Summary Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Quick Actions</div>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: 10px;">
                <button class="btn btn-primary" onclick="openAddModal()" style="width: 100%;">+ Add New Product</button>
                <a href="admin/orders.php" class="btn btn-outline" style="text-align: center;">Manage All Orders</a>
                <div style="margin-top: 10px; padding: 15px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <h4 style="font-size: 0.8rem; margin-bottom: 5px;">Inventory Health</h4>
                    <p style="font-size: 0.75rem; color: #64748b;">
                        <?php echo count($lowStockItems); ?> items require immediate attention.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <div class="card-title">Product Management</div>
            <button class="btn btn-primary" onclick="openAddModal()">+ Add Product</button>
        </div>
        <div class="card-body p-0 table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $p): ?>
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:40px; height:40px; border-radius:4px; overflow:hidden; background:#eee; flex-shrink:0;">
                                    <?php 
                                        $img = !empty($p['images']) ? $p['images'][0] : 'https://via.placeholder.com/40';
                                    ?>
                                    <img src="<?php echo $img; ?>" style="width:100%; height:100%; object-fit:cover;">
                                </div>
                                <div>
                                    <div style="font-weight:600"><?php echo htmlspecialchars($p['title']); ?></div>
                                    <div style="font-size:0.8rem; color:var(--text-muted)"><?php echo substr($p['description'] ?? '', 0, 30); ?>...</div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo ucfirst($p['category']); ?></td>
                        <td>₹<?php echo number_format($p['price'], 2); ?></td>
                        <td class="<?php echo ($p['stock']<=5) ? 'text-danger stock-badge' : ''; ?>">
                            <?php echo $p['stock']; ?>
                            <?php if($p['stock']<=5) echo " (Low)"; ?>
                        </td>
                        <td>
                            <button class="btn btn-outline btn-sm" onclick='editProduct(<?php echo json_encode($p); ?>)'>Edit</button>
                            <button class="btn btn-danger btn-sm" onclick="deleteProduct('<?php echo $p['_id']; ?>')">Del</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- DATA INJECTION FOR JS -->
<script>
    const statsData = <?php echo json_encode($stats); ?>;
    const visualData = <?php echo json_encode($visualAnalytics); ?>;
</script>

<!-- MODALS & JS -->
<!-- Note: Keeping the existing form logic hidden or in modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <h2 id="modalTitle" style="margin-bottom:1rem;">Add Product</h2>
        <form id="productForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" id="formAction" value="add_product">
            <input type="hidden" name="product_id" id="productId">
            
            <label>Title</label>
            <input type="text" name="title" id="pTitle" required>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:10px; margin-top:10px;">
                <div>
                    <label>Price (₹)</label>
                    <input type="number" name="price" id="pPrice" step="0.01" required>
                </div>
                <div>
                    <label>Discount (%)</label>
                    <input type="number" name="discount" id="pDiscount" min="0" max="100" step="1" value="0">
                </div>
                <div>
                    <label>Stock</label>
                    <input type="number" name="stock" id="pStock" required>
                </div>
            </div>
            
            <label style="margin-top:10px; display:block">Category</label>
            <select name="category" id="pCategory">
                <option value="men">Men</option>
                <option value="women">Women</option>
                <option value="kids">Kids</option>
            </select>
            
            <label style="margin-top:10px; display:block">Description</label>
            <textarea name="description" id="pDesc" rows="3"></textarea>

            <label style="margin-top:10px; display:block">Product Image</label>
            <div id="imagePreviewContainer" style="margin-top:5px; margin-bottom:10px; display:none;">
                <img id="pPreview" src="" style="width:100px; height:100px; object-fit:cover; border-radius:8px; border:1px solid #eee;">
            </div>
            <input type="file" name="image" id="pImage" accept="image/*" onchange="previewImage(this)">
            
            <div style="margin-top:20px; text-align:right;">
                <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none">
    <input type="hidden" name="action" value="delete_product">
    <input type="hidden" name="product_id" id="deleteId">
</form>

<script>
    // --- CHARTS CONFIG ---
    const ctxTrend = document.getElementById('ordersTrendChart');
    const ctxPie = document.getElementById('categoryChart');
    const ctxBar = document.getElementById('statusChart');

    // 1. Line Chart (Orders Over Time - 30 Days)
    const trendData = visualData.ordersOverTime || {};
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: Object.keys(trendData).map(d => d.slice(8)), // Show DD only for cleaner look
            datasets: [{
                label: 'Orders',
                data: Object.values(trendData),
                borderColor: '#2563eb',
                tension: 0.4,
                fill: true,
                backgroundColor: 'rgba(37, 99, 235, 0.1)'
            }]
        },
        options: { 
            responsive: true, 
            plugins: { 
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: function(items) {
                            return 'Date: ' + Object.keys(trendData)[items[0].dataIndex];
                        }
                    }
                }
            }, 
            scales: { 
                y: { beginAtZero: true, ticks: { precision: 0 } },
                x: { title: { display: true, text: 'Day of Month', font: { size: 10 } } }
            } 
        }
    });

    // 2. Pie Chart (Category-wise Demand)
    const demandStats = visualData.categoryDemand || {men:0, women:0, kids:0};
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ['Men', 'Women', 'Kids'],
            datasets: [{
                data: [demandStats.men, demandStats.women, demandStats.kids],
                backgroundColor: ['#1a1a1a', '#ec4899', '#f59e0b'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: { 
            responsive: true, 
            cutout: '65%', 
            plugins: { 
                legend: { position: 'bottom' },
                title: { display: true, text: 'Units Sold per Category', font: { size: 12 } }
            } 
        }
    });

    // 3. Bar Chart (Top Selling Products)
    const topProducts = visualData.topSellingProducts || [];
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: topProducts.map(p => p.title.length > 15 ? p.title.slice(0, 15) + '...' : p.title),
            datasets: [{
                label: 'Quantity Sold',
                data: topProducts.map(p => p.quantity),
                backgroundColor: '#10b981',
                borderRadius: 4,
                hoverBackgroundColor: '#059669'
            }]
        },
        options: { 
            indexAxis: 'y', // Horizontal bar chart looks better for product names
            responsive: true, 
            plugins: { 
                legend: { display: false },
                title: { display: true, text: 'Total Units Sold', font: { size: 12 } }
            }, 
            scales: { 
                x: { beginAtZero: true, ticks: { precision: 0 } }
            } 
        }
    });


    // --- CRUD OPS ---
    const modal = document.getElementById('productModal');
    const form = document.getElementById('productForm');

    function openAddModal() {
        document.getElementById('modalTitle').innerText = 'Add Product';
        document.getElementById('formAction').value = 'add_product';
        form.reset();
        modal.classList.add('active');
    }

    function editProduct(p) {
        document.getElementById('modalTitle').innerText = 'Edit Product';
        document.getElementById('formAction').value = 'update_product';
        document.getElementById('productId').value = p._id;
        document.getElementById('pTitle').value = p.title;
        document.getElementById('pPrice').value = p.price;
        document.getElementById('pDiscount').value = p.discount_percentage || 0;
        document.getElementById('pStock').value = p.stock;
        document.getElementById('pCategory').value = p.category;
        document.getElementById('pDesc').value = p.description;
        
        // Image preview
        if (p.images && p.images.length > 0) {
            document.getElementById('pPreview').src = p.images[0];
            document.getElementById('imagePreviewContainer').style.display = 'block';
        } else {
            document.getElementById('imagePreviewContainer').style.display = 'none';
        }
        
        modal.classList.add('active');
    }

    function closeModal() {
        modal.classList.remove('active');
        document.getElementById('imagePreviewContainer').style.display = 'none';
        document.getElementById('pPreview').src = '';
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('pPreview').src = e.target.result;
                document.getElementById('imagePreviewContainer').style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function deleteProduct(id) {
        if(confirm('Are you sure you want to delete this product?')) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }

    form.onsubmit = (e) => {
        // Just submit naturally to let PHP handle it with files
        // No need for AJAX or fetch here since the PHP handler is on the same page
        return true;
    };
    
</script>
</body>
</html>
