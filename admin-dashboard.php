<?php
/**
 * ADMIN DASHBOARD - Connected to Node.js Backend
 * PHP LOGIC: 100% ORIGINAL — Only design redesigned
 */

session_start();
require_once 'api-helper.php';

requireAdmin();

$user = getCurrentUser();
$token = getAuthToken();
$stats = [];
$products = [];
$orders = [];
$users = [];
$categories = [];

$error = '';
$success = '';

$response = $API->get('/api/v1/admin/dashboard', $token);
if ($response['success']) {
    $stats = $response['raw']['stats'] ?? $response['data'] ?? [];
}

$response = $API->get('/api/v1/admin/products', $token);
if ($response['success']) {
    $products = $response['data'] ?? $response['raw']['data'] ?? [];
} else {
    $response = $API->get('/api/v1/products/?limit=50', $token);
    if ($response['success']) {
        $products = $response['data'] ?? $response['raw']['data'] ?? [];
    }
}

$response = $API->get('/api/v1/admin/categories', $token);
if ($response['success']) {
    $categories = $response['data'] ?? [];
}

$response = $API->get('/api/v1/admin/orders', $token);
if ($response['success']) {
    $orders = $response['orders'] ?? $response['raw']['orders'] ?? $response['data'] ?? [];
}

$newOrders = [];
foreach ($orders as $o) {
    $created = strtotime($o['createdAt'] ?? $o['created_at'] ?? null);
    if ($created && $created >= time() - 15*60) {
        $newOrders[] = $o;
    }
}
$newOrderCount = count($newOrders);

$response = $API->get('/api/v1/admin/analytics/top-selling-products', $token);
$topSelling = $response['success'] ? ($response['data'] ?? []) : [];

$response = $API->get('/api/v1/admin/analytics/sales', $token);
$salesAnalytics = $response['success'] ? ($response['data'] ?? []) : [];

$response = $API->get('/api/v1/admin/analytics/reviews', $token);
$reviewsOverview = $response['success'] ? ($response['data'] ?? []) : [];

$response = $API->get('/api/v1/products/trending', $token);
$trendingProducts = $response['success'] ? ($response['data'] ?? []) : [];

$response = $API->get('/api/v1/admin/analytics/visual', $token);
$visualAnalytics = $response['success'] ? ($response['data'] ?? []) : [];

$response = $API->get('/api/v1/admin/analytics/advanced-dss', $token);
$advancedDSS = $response['success'] ? ($response['data'] ?? []) : [
    'overstockInsights' => [],
    'customerBehavior' => ['mostReviewed'=>[], 'lowRated'=>[], 'mostRated'=>[]],
    'orderSummary' => ['totalOrders'=>0, 'cancelledOrders'=>0, 'recentOrders'=>[]]
];

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
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $product_data['image'] = new CURLFile($_FILES['image']['tmp_name'], $_FILES['image']['type'], $_FILES['image']['name']);
            }
            $response = $API->post('/api/v1/admin/products', $product_data, $token);
            if ($response['success']) {
                $success = 'Product added successfully';
                header('Location: admin-dashboard.php');
                exit;
            } else {
                $error = $response['message'] ?? 'Failed to add product';
            }
            break;

        case 'update_product':
            $product_id = $_POST['product_id'] ?? '';
            if (!$product_id) { $error = 'Missing product id for update'; break; }
            $product_data = [
                'title'               => $_POST['title'] !== '' ? $_POST['title'] : null,
                'price'               => isset($_POST['price']) && $_POST['price'] !== '' ? (float)$_POST['price'] : null,
                'discount_percentage' => isset($_POST['discount']) && $_POST['discount'] !== '' ? (float)$_POST['discount'] : null,
                'stock'               => isset($_POST['stock']) && $_POST['stock'] !== '' ? (int)$_POST['stock'] : null,
                'category'            => $_POST['category'] !== '' ? $_POST['category'] : null,
                'description'         => $_POST['description'] !== '' ? $_POST['description'] : null
            ];
            
            // Remove null values so we only send provided fields (partial update)
            $product_data = array_filter($product_data, function($val) { return $val !== null; });

            // Send JSON partial update via PUT (as requested by user)
            $response = $API->put('/api/v1/admin/products/' . $product_id, $product_data, $token);
            if ($response['success']) {
                $success = 'Product updated successfully';
                header('Location: admin-dashboard.php');
                exit;
            } else {
                $error = $response['message'] ?? ('Failed to update product (status: ' . ($response['statusCode'] ?? 'unknown') . ')');
            }
            break;

        case 'delete_product':
            $product_id = $_POST['product_id'] ?? '';
            if (!$product_id) { $error = 'Missing product id for delete'; break; }
            $response = $API->delete('/api/v1/admin/products/' . $product_id, $token);
            if ($response['success']) {
                $success = 'Product deleted successfully';
                header('Location: admin-dashboard.php');
                exit;
            } else {
                $error = $response['message'] ?? 'Failed to delete product';
            }
            break;
        
        case 'update_order_status':
            $order_id = $_POST['order_id'] ?? '';
            $status = $_POST['status'] ?? '';
            $response = $API->put('/api/v1/admin/orders/' . $order_id . '/status', ['orderStatus' => $status], $token);
            if ($response['success']) {
                $success = 'Order status updated';
                header('Location: admin-dashboard.php');
                exit;
            } else {
                $error = $response['message'] ?? 'Failed to update order status';
            }
            break;

        case 'add_category':
            $cat_data = [
                'name' => $_POST['name'] ?? '',
                'slug' => $_POST['slug'] ?? '',
                'order' => isset($_POST['order']) ? (int)$_POST['order'] : 0,
                'isActive' => isset($_POST['isActive']) ? (bool)$_POST['isActive'] : true,
                'bannerType' => $_POST['bannerType'] ?? 'image'
            ];
            if ($cat_data['bannerType'] === 'video') {
                if (!empty($_POST['bannerVideoUrl'])) {
                    $cat_data['bannerVideo'] = $_POST['bannerVideoUrl'];
                }
                if (isset($_FILES['bannerVideo']) && $_FILES['bannerVideo']['error'] === UPLOAD_ERR_OK) {
                    $cat_data['bannerFile'] = new CURLFile($_FILES['bannerVideo']['tmp_name'], $_FILES['bannerVideo']['type'], $_FILES['bannerVideo']['name']);
                }
            } else {
                if (!empty($_POST['bannerImageUrl'])) {
                    $cat_data['bannerImage'] = $_POST['bannerImageUrl'];
                }
                if (isset($_FILES['bannerImage']) && $_FILES['bannerImage']['error'] === UPLOAD_ERR_OK) {
                    $cat_data['bannerFile'] = new CURLFile($_FILES['bannerImage']['tmp_name'], $_FILES['bannerImage']['type'], $_FILES['bannerImage']['name']);
                }
            }
            $response = $API->post('/api/v1/admin/categories', $cat_data, $token);
            if ($response['success']) {
                $success = 'Category added successfully';
                header('Location: admin-dashboard.php');
                exit;
            } else {
                $error = $response['message'] ?? 'Failed to add category';
            }
            break;

        case 'update_category':
            $cat_id = $_POST['category_id'] ?? '';
            $cat_data = [
                'name' => $_POST['name'] ?? '',
                'slug' => $_POST['slug'] ?? '',
                'order' => isset($_POST['order']) ? (int)$_POST['order'] : 0,
                'isActive' => isset($_POST['isActive']) ? (bool)$_POST['isActive'] : true,
                'bannerType' => $_POST['bannerType'] ?? 'image'
            ];
            if ($cat_data['bannerType'] === 'video') {
                if (!empty($_POST['bannerVideoUrl'])) {
                    $cat_data['bannerVideo'] = $_POST['bannerVideoUrl'];
                }
                if (isset($_FILES['bannerVideo']) && $_FILES['bannerVideo']['error'] === UPLOAD_ERR_OK) {
                    $cat_data['bannerFile'] = new CURLFile($_FILES['bannerVideo']['tmp_name'], $_FILES['bannerVideo']['type'], $_FILES['bannerVideo']['name']);
                }
            } else {
                if (!empty($_POST['bannerImageUrl'])) {
                    $cat_data['bannerImage'] = $_POST['bannerImageUrl'];
                }
                if (isset($_FILES['bannerImage']) && $_FILES['bannerImage']['error'] === UPLOAD_ERR_OK) {
                    $cat_data['bannerFile'] = new CURLFile($_FILES['bannerImage']['tmp_name'], $_FILES['bannerImage']['type'], $_FILES['bannerImage']['name']);
                }
            }
            $response = $API->put('/api/v1/admin/categories/' . $cat_id, $cat_data, $token);
            if ($response['success']) {
                $success = 'Category updated successfully';
                header('Location: admin-dashboard.php');
                exit;
            } else {
                $error = $response['message'] ?? 'Failed to update category';
            }
            break;

        case 'delete_category':
            $cat_id = $_POST['category_id'] ?? '';
            $response = $API->delete('/api/v1/admin/categories/' . $cat_id, $token);
            if ($response['success']) {
                $success = 'Category deleted successfully';
                header('Location: admin-dashboard.php');
                exit;
            } else {
                $error = $response['message'] ?? 'Failed to delete category';
            }
            break;
    }
}

$lowStock = $stats['lowStockCount'] ?? 0;
$lowStockItems = $visualAnalytics['lowStockProducts'] ?? [];
$recentOrders = array_slice($orders, 0, 6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Admin — URBANWEAR</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:ital,wght@0,400;0,500;1,400&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
/* ═══════════════════════════════════════════════
   URBANWEAR ADMIN  ·  Professional Dark Theme
   Inspired by: Vercel · Linear · Shopify · Stripe
   ═══════════════════════════════════════════════ */
:root {
  --sw: 220px;

  /* Dark sidebar */
  --s0: #0c0c0c;
  --s1: #161616;
  --s2: rgba(255,255,255,0.055);
  --st: rgba(255,255,255,0.48);
  --sa: rgba(255,255,255,0.90);
  --sb: rgba(255,255,255,0.07);

  /* Light content */
  --bg:      #f6f5f3;
  --surface: #ffffff;
  --border:  #e8e6e1;
  --border2: #d4d1ca;

  /* Text */
  --t1: #18181b;
  --t2: #6b6966;
  --t3: #a8a49e;

  /* Accents */
  --grn:   #16a34a;
  --grn-b: #dcfce7;
  --amb:   #b45309;
  --amb-b: #fef3c7;
  --red:   #dc2626;
  --red-b: #fee2e2;
  --blu:   #2563eb;
  --blu-b: #dbeafe;
  --vio:   #7c3aed;
  --vio-b: #ede9fe;

  --r: 7px;
  --ease: cubic-bezier(0.16,1,0.3,1);
}

*,*::before,*::after { margin:0; padding:0; box-sizing:border-box; }
html { height:100%; }
body {
  font-family: 'DM Sans', sans-serif;
  background: var(--bg);
  color: var(--t1);
  min-height: 100vh;
  display: flex;
  -webkit-font-smoothing: antialiased;
}
a { text-decoration:none; color:inherit; }
button { font-family:'DM Sans',sans-serif; cursor:pointer; }
::-webkit-scrollbar { width:3px; height:3px; }
::-webkit-scrollbar-thumb { background:var(--border2); border-radius:3px; }
::-webkit-scrollbar-track { background:transparent; }

/* ─ SIDEBAR ─ */
.sb {
  width: var(--sw);
  background: var(--s0);
  position: fixed; top:0; left:0; bottom:0;
  display: flex; flex-direction:column;
  z-index: 200;
  border-right: 1px solid rgba(255,255,255,0.06);
}
.sb-brand {
  padding: 22px 18px 18px;
  border-bottom: 1px solid rgba(255,255,255,0.06);
  display: flex; align-items:center; gap:10px;
}
.sb-brand-logo {
  font-size: 0.78rem; font-weight:700; letter-spacing:4px;
  text-transform:uppercase; color: rgba(255,255,255,0.9);
  line-height:1;
}
.sb-brand-sub {
  font-size: 0.56rem; color: rgba(255,255,255,0.22);
  letter-spacing:0.2em; text-transform:uppercase; margin-top:3px;
}
.sb-pulse {
  width:7px; height:7px; border-radius:50%;
  background:#22c55e; flex-shrink:0;
  box-shadow: 0 0 0 2px rgba(34,197,94,0.25);
  animation: pulse 2s infinite;
}
@keyframes pulse {
  0%,100% { box-shadow:0 0 0 2px rgba(34,197,94,0.25); }
  50% { box-shadow:0 0 0 5px rgba(34,197,94,0.08); }
}

.sb-nav { padding: 18px 10px 0; flex:1; overflow-y:auto; }
.sb-group { margin-bottom:22px; }
.sb-group-label {
  font-size: 0.56rem; font-weight:600; letter-spacing:0.4em;
  text-transform:uppercase; color:rgba(255,255,255,0.18);
  padding: 0 8px; margin-bottom:4px; display:block;
}
.sb-item {
  display:flex; align-items:center; gap:9px;
  padding: 8px 10px;
  font-size: 0.77rem; font-weight:400;
  color: var(--st); cursor:pointer;
  border-radius: 5px; transition: all 0.18s;
  margin-bottom:1px; letter-spacing:0.01em;
}
.sb-item i { font-size:0.76rem; width:15px; text-align:center; opacity:0.55; transition:opacity 0.18s; }
.sb-item:hover { background:var(--sb); color:rgba(255,255,255,0.78); }
.sb-item:hover i { opacity:0.8; }
.sb-item.on { background:rgba(255,255,255,0.09); color:var(--sa); font-weight:500; }
.sb-item.on i { opacity:1; }
.sb-item.danger { color:rgba(239,68,68,0.65); }
.sb-item.danger:hover { background:rgba(239,68,68,0.1); color:#f87171; }
.sb-count {
  margin-left:auto; font-size:0.58rem; font-weight:700;
  padding:2px 7px; border-radius:20px;
  background:rgba(239,68,68,0.18); color:#f87171;
}

.sb-divider { height:1px; background:rgba(255,255,255,0.06); margin:4px 10px 14px; }

.sb-user {
  margin-top:auto;
  padding:14px 10px;
  border-top:1px solid rgba(255,255,255,0.06);
}
.sb-user-row {
  display:flex; align-items:center; gap:10px;
  padding:8px 8px; border-radius:5px;
}
.sb-avatar {
  width:30px; height:30px; border-radius:50%;
  background:linear-gradient(135deg,#6366f1,#a855f7);
  display:flex; align-items:center; justify-content:center;
  font-size:0.7rem; font-weight:700; color:#fff; flex-shrink:0;
}
.sb-uname { font-size:0.77rem; font-weight:500; color:rgba(255,255,255,0.7); line-height:1; }
.sb-urole { font-size:0.58rem; color:rgba(255,255,255,0.25); margin-top:2px; letter-spacing:0.05em; }

/* ─ MAIN ─ */
.main { margin-left:var(--sw); flex:1; min-height:100vh; display:flex; flex-direction:column; }

.topbar {
  height:50px;
  background:var(--surface);
  border-bottom:1px solid var(--border);
  display:flex; align-items:center; justify-content:space-between;
  padding:0 28px;
  position:sticky; top:0; z-index:100;
}
.topbar-crumb {
  display:flex; align-items:center; gap:6px;
  font-size:0.8rem;
}
.topbar-crumb .seg { color:var(--t2); }
.topbar-crumb .sep { color:var(--t3); }
.topbar-crumb .cur { color:var(--t1); font-weight:600; }
.topbar-r { display:flex; align-items:center; gap:10px; }
.tb-clock {
  font-family:'DM Mono',monospace;
  font-size:0.7rem; color:var(--t2);
  background:var(--bg); padding:5px 10px;
  border:1px solid var(--border); border-radius:5px;
  letter-spacing:0.04em;
}
.tb-btn {
  width:32px; height:32px; border-radius:5px;
  border:1px solid var(--border); background:var(--bg);
  display:flex; align-items:center; justify-content:center;
  color:var(--t2); cursor:pointer; transition:all 0.2s;
  position:relative;
}
.tb-btn:hover { border-color:var(--t1); color:var(--t1); }
.tb-dot {
  position:absolute; top:6px; right:6px;
  width:5px; height:5px; border-radius:50%;
  background:#ef4444; border:1.5px solid var(--surface);
}

/* ─ CONTENT ─ */
.content { padding:24px 28px 60px; flex:1; }

/* ─ ALERTS ─ */
.alert {
  display:flex; align-items:center; gap:10px;
  padding:11px 16px; border-radius:var(--r);
  margin-bottom:18px; font-size:0.82rem; font-weight:500;
  border:1px solid; animation:slideDown 0.3s var(--ease);
}
@keyframes slideDown { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
.alert-error { background:var(--red-b); color:var(--red); border-color:#fecaca; }
.alert-success { background:var(--grn-b); color:var(--grn); border-color:#bbf7d0; }
.alert-warning { background:var(--amb-b); color:var(--amb); border-color:#fde68a; }

/* ─ PAGE HEADER ─ */
.ph {
  display:flex; align-items:flex-start; justify-content:space-between;
  margin-bottom:22px;
}
.ph h1 {
  font-size:1.3rem; font-weight:700; color:var(--t1);
  letter-spacing:-0.025em; line-height:1;
}
.ph p { font-size:0.77rem; color:var(--t2); margin-top:4px; font-weight:300; }
.ph-actions { display:flex; gap:8px; }

/* ─ KPI ─ */
.kpi-row {
  display:grid; grid-template-columns:repeat(4,1fr);
  gap:14px; margin-bottom:22px;
}
.kpi {
  background:var(--surface); border:1px solid var(--border);
  border-radius:var(--r); padding:18px 20px;
  position:relative; overflow:hidden;
  transition:box-shadow 0.25s, transform 0.25s;
  animation:fadeUp 0.4s var(--ease) both;
}
.kpi:nth-child(1){animation-delay:0.05s}
.kpi:nth-child(2){animation-delay:0.1s}
.kpi:nth-child(3){animation-delay:0.15s}
.kpi:nth-child(4){animation-delay:0.2s}
@keyframes fadeUp { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
.kpi:hover { box-shadow:0 4px 22px rgba(0,0,0,0.07); transform:translateY(-1px); }
.kpi::after {
  content:''; position:absolute; top:0; left:0; right:0; height:2px;
}
.kpi:nth-child(1)::after { background:var(--grn); }
.kpi:nth-child(2)::after { background:var(--blu); }
.kpi:nth-child(3)::after { background:var(--amb); }
.kpi:nth-child(4)::after { background:var(--vio); }
.kpi-icon {
  width:32px; height:32px; border-radius:6px;
  display:flex; align-items:center; justify-content:center;
  font-size:0.85rem; margin-bottom:14px;
}
.kpi:nth-child(1) .kpi-icon { background:var(--grn-b); color:var(--grn); }
.kpi:nth-child(2) .kpi-icon { background:var(--blu-b); color:var(--blu); }
.kpi:nth-child(3) .kpi-icon { background:var(--amb-b); color:var(--amb); }
.kpi:nth-child(4) .kpi-icon { background:var(--vio-b); color:var(--vio); }
.kpi-lbl { font-size:0.67rem; font-weight:600; text-transform:uppercase; letter-spacing:0.12em; color:var(--t2); margin-bottom:5px; }
.kpi-val {
  font-family:'DM Mono',monospace;
  font-size:1.65rem; font-weight:500; color:var(--t1);
  letter-spacing:-0.02em; line-height:1;
}
.kpi-sub { font-size:0.7rem; color:var(--t3); margin-top:6px; }
.kpi-sub .up { color:var(--grn); font-weight:600; }
.kpi-sub .dn { color:var(--red); font-weight:600; }

/* ─ SECTION LABEL ─ */
.sec-lbl {
  display:flex; align-items:center; gap:10px;
  font-size:0.67rem; font-weight:700; letter-spacing:0.2em;
  text-transform:uppercase; color:var(--t2);
  margin: 26px 0 14px;
}
.sec-lbl::after { content:''; flex:1; height:1px; background:var(--border); }

/* ─ CARDS ─ */
.card {
  background:var(--surface); border:1px solid var(--border);
  border-radius:var(--r); overflow:hidden;
}
.card-hd {
  display:flex; align-items:center; justify-content:space-between;
  padding:13px 18px; border-bottom:1px solid var(--border);
}
.card-ttl {
  font-size:0.8rem; font-weight:600; color:var(--t1);
  display:flex; align-items:center; gap:8px;
}
.card-ttl i { color:var(--t3); font-size:0.75rem; }
.card-lnk {
  font-size:0.68rem; font-weight:500; color:var(--t2);
  display:flex; align-items:center; gap:5px;
  transition:color 0.2s; cursor:pointer;
}
.card-lnk:hover { color:var(--t1); }
.card-bd { padding:16px 18px; }
.card-bd-0 { padding:0; }

/* ─ GRIDS ─ */
.g2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.g3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; }
.g64 { display:grid; grid-template-columns:1.55fr 1fr; gap:14px; }

/* ─ DSS CARDS ─ */
.dss-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:22px; }
.dss-c {
  background:var(--surface); border:1px solid var(--border);
  border-radius:var(--r); padding:18px;
  animation:fadeUp 0.4s var(--ease) both;
}
.dss-c:nth-child(1){animation-delay:0.1s}
.dss-c:nth-child(2){animation-delay:0.15s}
.dss-c:nth-child(3){animation-delay:0.2s}
.dss-hd {
  display:flex; align-items:center; justify-content:space-between;
  margin-bottom:14px; padding-bottom:12px;
  border-bottom:1px solid var(--border);
}
.dss-ttl { font-size:0.77rem; font-weight:600; color:var(--t1); display:flex; align-items:center; gap:7px; }
.adv-tag {
  font-size:0.55rem; font-weight:700; letter-spacing:0.1em;
  text-transform:uppercase; padding:2px 8px; border-radius:20px;
  background:var(--blu-b); color:var(--blu);
}
.dss-row {
  display:flex; justify-content:space-between; align-items:center;
  padding:6px 0; border-bottom:1px solid var(--border);
  font-size:0.77rem;
}
.dss-row:last-child { border-bottom:none; }
.dss-num {
  font-family:'DM Mono',monospace;
  font-size:1.5rem; font-weight:500; color:var(--t1); line-height:1;
}
.dss-micro { font-size:0.62rem; font-weight:700; text-transform:uppercase; letter-spacing:0.12em; color:var(--t2); margin-bottom:5px; }

/* ─ TABLE ─ */
table { width:100%; border-collapse:collapse; }
thead th {
  padding:9px 14px;
  font-size:0.65rem; font-weight:700; letter-spacing:0.12em;
  text-transform:uppercase; color:var(--t2);
  background:#fafaf8; border-bottom:1px solid var(--border);
  white-space:nowrap; text-align:left;
}
tbody td {
  padding:11px 14px;
  font-size:0.81rem; color:var(--t1);
  border-bottom:1px solid var(--border); vertical-align:middle;
}
tbody tr:last-child td { border-bottom:none; }
tbody tr { transition:background 0.15s; }
tbody tr:hover td { background:#fafaf8; }

.thumb {
  width:36px; height:44px; object-fit:cover;
  border-radius:3px; background:var(--bg); flex-shrink:0;
}
.prod-cell { display:flex; align-items:center; gap:10px; }
.prod-name { font-size:0.82rem; font-weight:600; color:var(--t1); }
.prod-sub { font-size:0.7rem; color:var(--t3); margin-top:2px; }

/* ─ PILLS ─ */
.pill {
  display:inline-flex; align-items:center; gap:4px;
  padding:3px 9px; border-radius:20px;
  font-size:0.62rem; font-weight:700; letter-spacing:0.06em;
  white-space:nowrap;
}
.pill::before { content:''; width:5px; height:5px; border-radius:50%; flex-shrink:0; }
.p-grn { background:var(--grn-b); color:var(--grn); }
.p-grn::before { background:var(--grn); }
.p-amb { background:var(--amb-b); color:var(--amb); }
.p-amb::before { background:var(--amb); }
.p-red { background:var(--red-b); color:var(--red); }
.p-red::before { background:var(--red); }
.p-blu { background:var(--blu-b); color:var(--blu); }
.p-blu::before { background:var(--blu); }
.p-gray { background:#f4f4f5; color:#52525b; }
.p-gray::before { background:#a1a1aa; }

/* ─ STOCK BAR ─ */
.stk { display:flex; align-items:center; gap:8px; }
.stk-trk { flex:1; height:4px; background:var(--border); border-radius:2px; overflow:hidden; min-width:40px; }
.stk-fill { height:100%; border-radius:2px; }
.stk-n { font-family:'DM Mono',monospace; font-size:0.78rem; font-weight:500; }

/* ─ HEALTH BARS ─ */
.hb { margin-bottom:10px; }
.hb-top { display:flex; justify-content:space-between; font-size:0.7rem; color:var(--t2); margin-bottom:5px; font-weight:500; }
.hb-bar { height:4px; background:var(--border); border-radius:3px; overflow:hidden; }
.hb-fill { height:100%; border-radius:3px; transition:width 1s var(--ease); }

/* ─ BUTTONS ─ */
.btn {
  display:inline-flex; align-items:center; gap:6px;
  padding:7px 14px; border:none; border-radius:var(--r);
  font-family:'DM Sans',sans-serif; font-size:0.74rem; font-weight:600;
  letter-spacing:0.03em; cursor:pointer; transition:all 0.2s;
  white-space:nowrap;
}
.btn-dark { background:var(--t1); color:#fff; }
.btn-dark:hover { background:#2a2a28; }
.btn-out { background:var(--surface); color:var(--t1); border:1px solid var(--border); }
.btn-out:hover { background:var(--bg); border-color:var(--border2); }
.btn-red { background:var(--red-b); color:var(--red); border:1px solid #fecaca; }
.btn-red:hover { background:#fecaca; }
.btn-xs { padding:4px 10px; font-size:0.67rem; }

/* ─ QUICK ACTIONS ─ */
.qa { display:flex; flex-direction:column; gap:8px; }
.qa-row {
  display:flex; align-items:center; gap:10px;
  padding:10px 13px;
  background:var(--bg); border:1px solid var(--border);
  border-radius:var(--r); font-size:0.77rem; font-weight:500;
  color:var(--t1); cursor:pointer; transition:all 0.2s;
  text-align:left; width:100%;
}
.qa-row:hover { background:var(--surface); border-color:var(--border2); box-shadow:0 2px 8px rgba(0,0,0,0.05); }
.qa-row i { color:var(--t2); font-size:0.78rem; width:15px; text-align:center; }

/* ─ CHART WRAP ─ */
.cw { position:relative; height:200px; }
.cw canvas { max-height:200px; }

/* ─ MODALS ─ */
.modal {
  display:none; position:fixed; inset:0;
  background:rgba(0,0,0,0.48); backdrop-filter:blur(6px);
  z-index:9000; align-items:center; justify-content:center; padding:20px;
}
.modal.active { display:flex; animation:mfade 0.22s ease; }
@keyframes mfade { from{opacity:0} to{opacity:1} }
.modal-box {
  background:var(--surface); width:100%; max-width:560px;
  max-height:90vh; overflow-y:auto;
  border-radius:10px; border:1px solid var(--border);
  box-shadow:0 24px 80px rgba(0,0,0,0.18);
  animation:mup 0.32s var(--ease);
}
@keyframes mup { from{transform:translateY(14px);opacity:0} to{transform:translateY(0);opacity:1} }
.modal-top {
  padding:17px 22px; border-bottom:1px solid var(--border);
  display:flex; align-items:center; justify-content:space-between;
  position:sticky; top:0; background:var(--surface); z-index:1;
}
.modal-top h2 { font-size:0.92rem; font-weight:700; color:var(--t1); }
.modal-x {
  width:28px; height:28px; display:flex; align-items:center; justify-content:center;
  border:none; background:var(--bg); border-radius:5px;
  color:var(--t2); cursor:pointer; font-size:0.95rem; transition:all 0.2s;
}
.modal-x:hover { background:var(--border); color:var(--t1); }
.modal-bd { padding:22px; }
.modal-ft {
  padding:13px 22px; border-top:1px solid var(--border);
  display:flex; justify-content:flex-end; gap:8px;
  position:sticky; bottom:0; background:var(--surface);
}

/* ─ FORM ─ */
.fld { margin-bottom:14px; }
.fld label {
  display:block; font-size:0.65rem; font-weight:700;
  text-transform:uppercase; letter-spacing:0.12em;
  color:var(--t2); margin-bottom:6px;
}
.fld input,.fld select,.fld textarea {
  width:100%; padding:9px 12px;
  background:var(--bg); border:1px solid var(--border);
  border-radius:var(--r); font-family:'DM Sans',sans-serif;
  font-size:0.87rem; color:var(--t1);
  outline:none; transition:all 0.22s;
}
.fld input:focus,.fld select:focus,.fld textarea:focus {
  border-color:var(--t1); background:var(--surface);
  box-shadow:0 0 0 3px rgba(24,24,27,0.07);
}
.fld textarea { resize:vertical; min-height:78px; }
.fr3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; }
.fr2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

/* ─ BANNER MANAGER ─ */
.bm { margin-top:16px; padding-top:16px; border-top:1px solid var(--border); }
.bm-ttl { font-size:0.65rem; font-weight:700; text-transform:uppercase; letter-spacing:0.15em; color:var(--t2); margin-bottom:12px; }
.bm-prev {
  height:118px; background:var(--bg); border:1px solid var(--border);
  border-radius:var(--r); overflow:hidden;
  display:flex; align-items:center; justify-content:center;
  margin-bottom:12px; font-size:0.75rem; color:var(--t3);
}
.bm-prev img,.bm-prev video { width:100%; height:100%; object-fit:cover; }
.bm-tabs { display:flex; margin-bottom:14px; }
.bm-tab {
  flex:1; padding:7px; text-align:center;
  font-size:0.67rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase;
  background:var(--bg); border:1px solid var(--border);
  cursor:pointer; transition:all 0.2s; color:var(--t2);
}
.bm-tab:first-child { border-radius:var(--r) 0 0 var(--r); }
.bm-tab:last-child { border-radius:0 var(--r) var(--r) 0; border-left:none; }
.bm-tab.on { background:var(--t1); color:#fff; border-color:var(--t1); }
.bm-sep {
  text-align:center; font-size:0.65rem; color:var(--t3);
  margin:10px 0; position:relative;
}
.bm-sep::before { content:''; position:absolute; top:50%; left:0; right:0; height:1px; background:var(--border); }
.bm-sep span { background:var(--surface); padding:0 10px; position:relative; }
.bm-save {
  width:100%; padding:10px; margin-top:12px;
  background:var(--t1); color:#fff; border:none; border-radius:var(--r);
  font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.18em;
  cursor:pointer; transition:background 0.2s;
}
.bm-save:hover { background:#2a2a28; }
#bannerStatus { text-align:center; font-size:0.73rem; font-weight:500; margin-top:8px; }

/* ─ RESPONSIVE ─ */
@media(max-width:1200px) {
  .kpi-row { grid-template-columns:1fr 1fr; }
  .dss-grid { grid-template-columns:1fr 1fr; }
  .g64 { grid-template-columns:1fr; }
}
@media(max-width:860px) {
  .sb { transform:translateX(-100%); }
  .main { margin-left:0; }
  .g2,.g3,.kpi-row,.dss-grid { grid-template-columns:1fr; }
  .content { padding:18px 16px 40px; }
}
</style>
</head>
<body>

<!-- ════ SIDEBAR ════ -->
<aside class="sb">
  <div class="sb-brand">
    <div>
      <div class="sb-brand-logo">Urbanwear</div>
      <div class="sb-brand-sub">Admin Console</div>
    </div>
    <div class="sb-pulse" title="Systems online"></div>
  </div>

  <nav class="sb-nav">
    <div class="sb-group">
      <span class="sb-group-label">Dashboard</span>
      <a class="sb-item on" href="admin-dashboard.php">
        <i class="fa-solid fa-grid-2"></i> Overview
      </a>
      <a class="sb-item" href="#products-section">
        <i class="fa-solid fa-shirt"></i> Products
        <?php if(count($products)>0): ?>
          <span class="sb-count" style="background:rgba(255,255,255,0.07);color:rgba(255,255,255,0.3);"><?php echo count($products); ?></span>
        <?php endif; ?>
      </a>
      <a class="sb-item" href="#categories-section">
        <i class="fa-solid fa-folder-open"></i> Categories
      </a>
      <a class="sb-item" href="admin/orders.php">
        <i class="fa-solid fa-bag-shopping"></i> Orders
        <?php if($lowStock > 0): ?><span class="sb-count"><?php echo $lowStock; ?></span><?php endif; ?>
      </a>
    </div>

    <div class="sb-group">
      <span class="sb-group-label">Intelligence</span>
      <a class="sb-item" href="#dss-section">
        <i class="fa-solid fa-brain"></i> DSS Analytics
      </a>
      <a class="sb-item" href="#analytics-section">
        <i class="fa-solid fa-chart-line"></i> Sales Trends
      </a>
    </div>

    <div class="sb-divider"></div>

    <div class="sb-group">
      <a href="index.php" target="_blank" class="sb-item">
        <i class="fa-solid fa-arrow-up-right-from-square"></i> View Storefront
      </a>
      <a href="logout-connected.php" class="sb-item danger">
        <i class="fa-solid fa-right-from-bracket"></i> Sign Out
      </a>
    </div>
  </nav>

  <div class="sb-user">
    <div class="sb-user-row">
      <div class="sb-avatar"><?php echo strtoupper(substr($user['name'] ?? 'A', 0, 1)); ?></div>
      <div>
        <div class="sb-uname"><?php echo htmlspecialchars($user['name'] ?? 'Admin'); ?></div>
        <div class="sb-urole">Administrator</div>
      </div>
    </div>
  </div>
</aside>

<!-- ════ MAIN ════ -->
<div class="main">

  <!-- Topbar -->
  <div class="topbar">
    <div class="topbar-crumb">
      <span class="seg">Admin</span>
      <span class="sep">/</span>
      <span class="cur">Overview</span>
    </div>
    <div class="topbar-r">
      <div class="tb-clock" id="tbClock">—</div>
      <div class="tb-btn">
        <i class="fa-regular fa-bell" style="font-size:0.75rem;"></i>
        <?php if($lowStock > 0 || $newOrderCount > 0): ?><span class="tb-dot"></span><?php endif; ?>
      </div>
      <div class="tb-btn" onclick="location.reload()" title="Refresh">
        <i class="fa-solid fa-rotate-right" style="font-size:0.72rem;"></i>
      </div>
    </div>
  </div>

  <!-- Content -->
  <div class="content">

    <!-- Alerts -->
    <?php if ($error): ?>
      <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if ($lowStock > 0): ?>
      <div class="alert alert-warning">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <strong><?php echo $lowStock; ?> products</strong>&nbsp; running critically low (≤ 5 units) — restock immediately.
      </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="ph">
      <div>
        <h1>Dashboard Overview</h1>
        <p>Real-time store performance &amp; inventory intelligence</p>
      </div>
      <div class="ph-actions">
        <button class="btn btn-out" onclick="location.reload()"><i class="fa-solid fa-rotate-right"></i> Refresh</button>
        <button class="btn btn-dark" onclick="openAddModal()"><i class="fa-solid fa-plus"></i> New Product</button>
      </div>
    </div>

    <!-- KPIs -->
    <div class="kpi-row">
      <div class="kpi">
        <div class="kpi-icon"><i class="fa-solid fa-indian-rupee-sign"></i></div>
        <div class="kpi-lbl">Total Revenue</div>
        <div class="kpi-val">₹<?php echo isset($stats['totalSales']) ? number_format($stats['totalSales'],0) : '0'; ?></div>
        <div class="kpi-sub"><span class="up">↑</span> Lifetime volume</div>
      </div>
      <div class="kpi">
        <div class="kpi-icon"><i class="fa-solid fa-bag-shopping"></i></div>
        <div class="kpi-lbl">Total Orders</div>
        <div class="kpi-val"><?php echo $stats['totalOrders'] ?? '0'; ?></div>
        <div class="kpi-sub">All confirmed orders</div>
      </div>
      <div class="kpi">
        <div class="kpi-icon"><i class="fa-solid fa-shirt"></i></div>
        <div class="kpi-lbl">Active Products</div>
        <div class="kpi-val"><?php echo $stats['totalProducts'] ?? '0'; ?></div>
        <div class="kpi-sub">
          <?php echo $lowStock > 0 ? "<span class='dn'>$lowStock low stock</span>" : 'All healthy ✓'; ?>
        </div>
      </div>
      <div class="kpi">
        <div class="kpi-icon"><i class="fa-solid fa-users"></i></div>
        <div class="kpi-lbl">Customers</div>
        <div class="kpi-val"><?php echo $stats['totalUsers'] ?? '0'; ?></div>
        <div class="kpi-sub">Registered accounts</div>
      </div>
    </div>

    <!-- DSS Section -->
    <div id="dss-section" class="sec-lbl">
      <i class="fa-solid fa-brain"></i> Decision Support Intelligence
      <span class="adv-tag" style="margin-left:4px;">Live</span>
    </div>

    <div class="dss-grid">

      <!-- Inventory Intelligence -->
      <div class="dss-c">
        <div class="dss-hd">
          <div class="dss-ttl"><i class="fa-solid fa-boxes-stacked" style="color:var(--amb);"></i> Inventory Intelligence</div>
          <span class="adv-tag">DSS</span>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
          <div>
            <div class="dss-micro" style="color:var(--red);">Critical Low</div>
            <div class="dss-num" style="color:var(--red);"><?php echo count($lowStockItems); ?></div>
          </div>
          <div>
            <div class="dss-micro">Overstock</div>
            <div class="dss-num"><?php echo count($advancedDSS['overstockInsights']); ?></div>
          </div>
        </div>
        <?php if(empty($advancedDSS['overstockInsights'])): ?>
          <div style="font-size:0.74rem;color:var(--grn);display:flex;align-items:center;gap:5px;">
            <i class="fa-solid fa-check-circle"></i> No overstock detected
          </div>
        <?php else: ?>
          <?php foreach(array_slice($advancedDSS['overstockInsights'],0,3) as $os): ?>
            <div class="dss-row">
              <span style="font-size:0.74rem;font-weight:500;"><?php echo htmlspecialchars($os['title']); ?></span>
              <span class="pill p-amb"><?php echo $os['stock']; ?> units</span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Customer Behavior -->
      <div class="dss-c">
        <div class="dss-hd">
          <div class="dss-ttl"><i class="fa-solid fa-users" style="color:var(--blu);"></i> Customer Insights</div>
        </div>
        <div class="dss-micro" style="color:var(--red);margin-bottom:6px;">Lowest Rated</div>
        <?php foreach(array_slice($advancedDSS['customerBehavior']['lowRated'],0,2) as $lr): ?>
          <div class="dss-row">
            <span style="font-size:0.74rem;"><?php echo htmlspecialchars($lr['title']); ?></span>
            <span class="pill p-red"><?php echo $lr['avgRating']; ?>★</span>
          </div>
        <?php endforeach; ?>
        <?php if(empty($advancedDSS['customerBehavior']['lowRated'])): ?>
          <div style="font-size:0.74rem;color:var(--grn);margin-bottom:10px;"><i class="fa-solid fa-check-circle"></i> All ratings healthy</div>
        <?php endif; ?>
        <div class="dss-micro" style="color:var(--grn);margin-top:12px;margin-bottom:6px;">Top Favorites</div>
        <?php foreach(array_slice($advancedDSS['customerBehavior']['mostRated'],0,2) as $mr): ?>
          <div class="dss-row">
            <span style="font-size:0.74rem;"><?php echo htmlspecialchars($mr['title']); ?></span>
            <span class="pill p-grn"><?php echo $mr['avgRating']; ?>★</span>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Order Summary -->
      <div class="dss-c">
        <div class="dss-hd">
          <div class="dss-ttl"><i class="fa-solid fa-chart-column" style="color:var(--grn);"></i> Order Intelligence</div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
          <div>
            <div class="dss-micro">Total Volume</div>
            <div class="dss-num"><?php echo $advancedDSS['orderSummary']['totalOrders']; ?></div>
          </div>
          <div>
            <div class="dss-micro" style="color:var(--red);">Cancelled</div>
            <div class="dss-num" style="color:var(--red);"><?php echo $advancedDSS['orderSummary']['cancelledOrders']; ?></div>
          </div>
        </div>
        <div class="dss-micro">Avg. Order Value</div>
        <div style="font-family:'DM Mono',monospace;font-size:1.1rem;font-weight:500;color:var(--t1);margin-top:4px;">
          ₹<?php
            $rOrds = $advancedDSS['orderSummary']['recentOrders'];
            $rTotal = array_sum(array_column($rOrds, 'amount'));
            $rCount = count($rOrds);
            echo $rCount > 0 ? number_format($rTotal / $rCount) : '—';
          ?>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div id="analytics-section" class="sec-lbl">
      <i class="fa-solid fa-chart-line"></i> Sales Analytics
    </div>

    <div class="g64" style="margin-bottom:16px;">
      <div class="card">
        <div class="card-hd">
          <div class="card-ttl"><i class="fa-solid fa-chart-line"></i> Orders Trend — Last 30 Days</div>
        </div>
        <div class="card-bd"><div class="cw"><canvas id="ordersTrendChart"></canvas></div></div>
      </div>
      <div class="card">
        <div class="card-hd">
          <div class="card-ttl"><i class="fa-solid fa-chart-pie"></i> Category Demand</div>
        </div>
        <div class="card-bd"><div class="cw"><canvas id="categoryChart"></canvas></div></div>
      </div>
    </div>

    <div class="g64" style="margin-bottom:22px;">
      <div class="card">
        <div class="card-hd">
          <div class="card-ttl"><i class="fa-solid fa-chart-bar"></i> Top Selling Products</div>
        </div>
        <div class="card-bd"><div class="cw"><canvas id="statusChart"></canvas></div></div>
      </div>

      <!-- Recent Orders -->
      <div class="card">
        <div class="card-hd">
          <div class="card-ttl"><i class="fa-solid fa-clock-rotate-left"></i> Recent Orders</div>
          <a href="admin/orders.php" class="card-lnk">All orders <i class="fa-solid fa-arrow-right" style="font-size:0.6rem;"></i></a>
        </div>
        <div class="card-bd-0">
          <table>
            <tbody>
              <?php foreach($recentOrders as $ro):
                $s = $ro['orderStatus'] ?? $ro['status'] ?? 'Placed';
                $amt = $ro['total'] ?? $ro['totalAmount'] ?? 0;
                $pillCls = in_array($s,['Delivered','PAID','CONFIRMED'])?'p-grn':(in_array($s,['Shipped'])?'p-blu':(in_array($s,['Cancelled'])?'p-red':'p-amb'));
              ?>
              <tr>
                <td>
                  <div style="font-family:'DM Mono',monospace;font-size:0.77rem;font-weight:500;color:var(--t1);">#<?php echo substr($ro['_id'],-6); ?></div>
                  <div style="font-size:0.67rem;color:var(--t3);margin-top:2px;"><?php echo isset($ro['createdAt']) ? date('M d, H:i', strtotime($ro['createdAt'])) : '—'; ?></div>
                </td>
                <td style="text-align:right;">
                  <div style="font-family:'DM Mono',monospace;font-weight:600;font-size:0.84rem;">₹<?php echo number_format($amt); ?></div>
                  <span class="pill <?php echo $pillCls; ?>" style="margin-top:4px;"><?php echo htmlspecialchars($s); ?></span>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($recentOrders)): ?>
                <tr><td colspan="2" style="text-align:center;padding:28px;color:var(--t3);font-size:0.8rem;">No confirmed orders yet</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Low Stock + Quick Actions -->
    <div class="g64" style="margin-bottom:22px;">
      <div class="card">
        <div class="card-hd">
          <div class="card-ttl">
            <i class="fa-solid fa-triangle-exclamation" style="color:var(--red);"></i>
            Low Stock Alert
            <span class="pill p-red" style="margin-left:2px;"><?php echo count($lowStockItems); ?> items</span>
          </div>
        </div>
        <div class="card-bd-0">
          <?php if(empty($lowStockItems)): ?>
            <div style="padding:36px;text-align:center;color:var(--grn);font-size:0.82rem;"><i class="fa-solid fa-check-circle"></i>&nbsp; All stock levels are healthy</div>
          <?php else: ?>
            <table>
              <thead><tr><th>Product</th><th>Category</th><th>Stock Level</th></tr></thead>
              <tbody>
                <?php foreach($lowStockItems as $lsi): ?>
                <tr>
                  <td style="font-weight:500;font-size:0.8rem;"><?php echo htmlspecialchars($lsi['title']); ?></td>
                  <td><span class="pill p-gray"><?php echo ucfirst($lsi['category']); ?></span></td>
                  <td>
                    <div class="stk">
                      <span class="stk-n" style="color:<?php echo $lsi['stock']<=5?'var(--red)':'var(--amb)';?>;"><?php echo $lsi['stock']; ?></span>
                      <div class="stk-trk">
                        <div class="stk-fill" style="width:<?php echo min(100,$lsi['stock']*10); ?>%;background:<?php echo $lsi['stock']<=5?'var(--red)':'var(--amb)';?>;"></div>
                      </div>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-hd"><div class="card-ttl"><i class="fa-solid fa-bolt"></i> Quick Actions</div></div>
        <div class="card-bd">
          <div class="qa">
            <button class="qa-row" onclick="openAddModal()"><i class="fa-solid fa-plus"></i> Add New Product</button>
            <button class="qa-row" onclick="openAddCategoryModal()"><i class="fa-solid fa-folder-plus"></i> Add New Category</button>
            <a href="admin/orders.php" class="qa-row"><i class="fa-solid fa-list-check"></i> Manage All Orders</a>
            <a href="index.php" target="_blank" class="qa-row"><i class="fa-solid fa-arrow-up-right-from-square"></i> View Storefront</a>
          </div>

          <div style="margin-top:16px;padding:14px;background:var(--bg);border-radius:var(--r);border:1px solid var(--border);">
            <div style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.14em;color:var(--t2);margin-bottom:10px;">Inventory Health</div>
            <?php
              $cats = [
                ['Men', $stats['menStock'] ?? 45],
                ['Women', $stats['womenStock'] ?? 65],
                ['Kids', $stats['kidsStock'] ?? 30],
              ];
              foreach($cats as [$cname,$cval]):
                $pct = min(100,$cval);
                $clr = $pct>60?'var(--grn)':($pct>30?'var(--amb)':'var(--red)');
            ?>
            <div class="hb">
              <div class="hb-top"><span><?php echo $cname; ?></span><span style="font-family:'DM Mono',monospace;font-size:0.67rem;"><?php echo $cval; ?> units</span></div>
              <div class="hb-bar"><div class="hb-fill" style="width:<?php echo $pct; ?>%;background:<?php echo $clr; ?>;"></div></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Category Management -->
    <div id="categories-section" class="sec-lbl">
      <i class="fa-solid fa-folder-open"></i> Category Management
    </div>
    <div class="card" style="margin-bottom:22px;">
      <div class="card-hd">
        <div class="card-ttl">All Categories</div>
        <button class="btn btn-dark btn-xs" onclick="openAddCategoryModal()"><i class="fa-solid fa-plus"></i> Add</button>
      </div>
      <div class="card-bd-0" style="overflow-x:auto;">
        <table>
          <thead><tr><th>#</th><th>Category</th><th>Slug</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            <?php if(empty($categories)): ?>
              <tr><td colspan="5" style="text-align:center;padding:28px;color:var(--t3);">No categories found.</td></tr>
            <?php endif; ?>
            <?php foreach($categories as $cat): ?>
            <tr>
              <td><span style="font-family:'DM Mono',monospace;font-size:0.78rem;color:var(--t2);"><?php echo $cat['order'] ?? 0; ?></span></td>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <img src="<?php echo htmlspecialchars($cat['bannerImage'] ?? ''); ?>" style="width:40px;height:28px;object-fit:cover;border-radius:3px;background:var(--bg);" onerror="this.style.display='none'">
                  <span style="font-weight:600;font-size:0.82rem;"><?php echo htmlspecialchars($cat['name']); ?></span>
                </div>
              </td>
              <td><span style="font-family:'DM Mono',monospace;font-size:0.72rem;color:var(--t2);"><?php echo htmlspecialchars($cat['slug']); ?></span></td>
              <td><span class="pill <?php echo ($cat['isActive'] ?? true) ? 'p-grn' : 'p-gray'; ?>"><?php echo ($cat['isActive'] ?? true) ? 'Active' : 'Inactive'; ?></span></td>
              <td>
                <div style="display:flex;gap:6px;">
                  <button class="btn btn-out btn-xs" onclick='editCategory(<?php echo json_encode($cat); ?>)'>Edit</button>
                  <button class="btn btn-red btn-xs" onclick="deleteCategory('<?php echo $cat['_id']; ?>')">Delete</button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Product Management -->
    <div id="products-section" class="sec-lbl">
      <i class="fa-solid fa-shirt"></i> Product Management
    </div>
    <div class="card" style="margin-bottom:40px;">
      <div class="card-hd">
        <div class="card-ttl">
          All Products
          <span class="pill p-gray" style="margin-left:2px;"><?php echo count($products); ?></span>
        </div>
        <button class="btn btn-dark btn-xs" onclick="openAddModal()"><i class="fa-solid fa-plus"></i> Add Product</button>
      </div>
      <div class="card-bd-0" style="overflow-x:auto;">
        <table>
          <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach($products as $p):
              $img = !empty($p['images']) ? $p['images'][0] : '';
            ?>
            <tr>
              <td>
                <div class="prod-cell">
                  <?php if($img): ?>
                    <img src="<?php echo $img; ?>" class="thumb" onerror="this.style.display='none'">
                  <?php endif; ?>
                  <div>
                    <div class="prod-name"><?php echo htmlspecialchars($p['title']); ?></div>
                    <div class="prod-sub"><?php echo htmlspecialchars(substr($p['description'] ?? '', 0, 32)); ?>…</div>
                  </div>
                </div>
              </td>
              <td><span class="pill p-gray"><?php echo ucfirst($p['category']); ?></span></td>
              <td>
                <div style="font-family:'DM Mono',monospace;font-size:0.82rem;font-weight:500;">₹<?php echo number_format($p['price'],0); ?></div>
                <?php if(($p['discount_percentage'] ?? 0) > 0): ?>
                  <div style="font-size:0.67rem;color:var(--grn);font-weight:600;margin-top:2px;"><?php echo $p['discount_percentage']; ?>% off</div>
                <?php endif; ?>
              </td>
              <td>
                <div class="stk">
                  <span class="stk-n" style="color:<?php echo $p['stock']<=5?'var(--red)':($p['stock']<=10?'var(--amb)':'var(--grn)'); ?>;"><?php echo $p['stock']; ?></span>
                  <?php if($p['stock'] <= 5): ?>
                    <span class="pill p-red" style="margin-left:4px;">Low</span>
                  <?php endif; ?>
                </div>
              </td>
              <td>
                <div style="display:flex;gap:6px;">
                  <button class="btn btn-out btn-xs" onclick='editProduct(<?php echo json_encode($p); ?>)'>Edit</button>
                  <button class="btn btn-red btn-xs" onclick="deleteProduct('<?php echo $p['_id']; ?>')">Delete</button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->

<!-- ════ PRODUCT MODAL ════ -->
<div id="productModal" class="modal">
  <div class="modal-box">
    <div class="modal-top">
      <h2 id="modalTitle">Add Product</h2>
      <button class="modal-x" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-bd">
      <form id="productForm" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" id="formAction" value="add_product">
        <input type="hidden" name="product_id" id="productId">

        <div class="fld"><label>Title</label><input type="text" name="title" id="pTitle" required placeholder="Product name"></div>
        <div class="fr3">
          <div class="fld"><label>Price (₹)</label><input type="number" name="price" id="pPrice" step="0.01" required placeholder="0.00"></div>
          <div class="fld"><label>Discount %</label><input type="number" name="discount" id="pDiscount" min="0" max="100" value="0"></div>
          <div class="fld"><label>Stock</label><input type="number" name="stock" id="pStock" required placeholder="0"></div>
        </div>
        <div class="fr2">
          <div class="fld"><label>Category</label>
            <select name="category" id="pCategory">
              <option value="men">Men</option>
              <option value="women">Women</option>
              <option value="kids">Kids</option>
            </select>
          </div>
          <div class="fld"><label>Product Image</label>
            <input type="file" name="image" id="pImage" accept="image/*" onchange="previewImage(this)">
          </div>
        </div>
        <div id="imagePreviewContainer" style="margin-bottom:12px;display:none;">
          <img id="pPreview" src="" style="width:60px;height:74px;object-fit:cover;border-radius:4px;border:1px solid var(--border);">
        </div>
        <div class="fld"><label>Description</label><textarea name="description" id="pDesc" rows="3" placeholder="Product description…"></textarea></div>
      </form>
    </div>
    <div class="modal-ft">
      <button class="btn btn-out" onclick="closeModal()">Cancel</button>
      <button class="btn btn-dark" onclick="document.getElementById('productForm').submit()"><i class="fa-solid fa-check"></i> Save Product</button>
    </div>
  </div>
</div>

<!-- ════ CATEGORY MODAL ════ -->
<div id="categoryModal" class="modal">
  <div class="modal-box">
    <div class="modal-top">
      <h2 id="catModalTitle">Add Category</h2>
      <button class="modal-x" onclick="closeCatModal()">✕</button>
    </div>
    <div class="modal-bd">
      <form id="categoryForm" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" id="catFormAction" value="add_category">
        <input type="hidden" name="category_id" id="catId">
        <input type="hidden" name="bannerType" id="catBannerType" value="image">

        <div class="fr2">
          <div class="fld"><label>Name</label><input type="text" name="name" id="catName" placeholder="e.g. Men" required></div>
          <div class="fld"><label>Slug</label><input type="text" name="slug" id="catSlug" placeholder="e.g. men" required></div>
        </div>
        <div class="fr2">
          <div class="fld"><label>Display Order</label><input type="number" name="order" id="catOrder" placeholder="1" required></div>
          <div class="fld"><label>Status</label>
            <select name="isActive" id="catActive">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>

        <!-- Banner Manager -->
        <div class="bm">
          <div class="bm-ttl">Hero Banner</div>
          <div class="bm-prev" id="bannerPreviewBox">No banner set</div>
          <div class="bm-tabs">
            <button type="button" class="bm-tab on" id="bTabImg" onclick="switchBannerType('image')">Image</button>
            <button type="button" class="bm-tab" id="bTabVid" onclick="switchBannerType('video')">Video</button>
          </div>

          <div id="imageSection">
            <div class="fld"><label>Upload Image (JPG/PNG/WEBP · max 10MB)</label><input type="file" name="bannerImage" id="bannerImageFile" accept="image/jpeg,image/png,image/webp" onchange="previewBannerFile(this,'image')"></div>
            <div class="bm-sep"><span>— or —</span></div>
            <div class="fld"><label>Paste Image URL</label><input type="text" name="bannerImageUrl" id="bannerImageUrl" placeholder="https://…" oninput="previewBannerUrl(this.value,'image')"></div>
          </div>

          <div id="videoSection" style="display:none;">
            <div class="fld"><label>Upload Video (MP4/WEBM · max 50MB)</label><input type="file" name="bannerVideo" id="bannerVideoFile" accept="video/mp4,video/webm,video/quicktime" onchange="previewBannerFile(this,'video')"></div>
            <div class="bm-sep"><span>— or —</span></div>
            <div class="fld"><label>Paste Video URL</label><input type="text" name="bannerVideoUrl" id="bannerVideoUrl" placeholder="https://…" oninput="previewBannerUrl(this.value,'video')"></div>
          </div>

          <button type="button" class="bm-save" id="saveBannerBtn" style="display:none;" onclick="saveCategoryBanner()">Save Banner</button>
          <div id="bannerStatus"></div>
        </div>
      </form>
    </div>
    <div class="modal-ft">
      <button class="btn btn-out" onclick="closeCatModal()">Cancel</button>
      <button class="btn btn-dark" onclick="document.getElementById('categoryForm').submit()"><i class="fa-solid fa-check"></i> Save Category</button>
    </div>
  </div>
</div>

<form id="deleteProductForm" method="POST" style="display:none"><input type="hidden" name="action" value="delete_product"><input type="hidden" name="product_id" id="deleteProdId"></form>
<form id="deleteCategoryForm" method="POST" style="display:none"><input type="hidden" name="action" value="delete_category"><input type="hidden" name="category_id" id="deleteCatId"></form>

<!-- DATA + CHARTS + JS -->
<script>
const statsData   = <?php echo json_encode($stats); ?>;
const visualData  = <?php echo json_encode($visualAnalytics); ?>;
const API_BASE_URL = 'http://127.0.0.1:5000';
window.ADMIN_TOKEN = '<?php echo $token; ?>';

// Live clock
(function tick(){
  const n=new Date();
  document.getElementById('tbClock').textContent =
    n.toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false});
  setTimeout(tick,1000);
})();

// Chart defaults
Chart.defaults.font.family = "'DM Sans', sans-serif";
Chart.defaults.color = '#6b6966';

// 1. Line chart
const tD = visualData.ordersOverTime || {};
new Chart('ordersTrendChart',{
  type:'line',
  data:{
    labels:Object.keys(tD).map(d=>d.slice(8)),
    datasets:[{
      label:'Orders',
      data:Object.values(tD),
      borderColor:'#18181b',
      borderWidth:2,
      tension:0.4,
      fill:true,
      backgroundColor:'rgba(24,24,27,0.06)',
      pointBackgroundColor:'#18181b',
      pointRadius:3,
      pointHoverRadius:5
    }]
  },
  options:{
    responsive:true,maintainAspectRatio:false,
    plugins:{legend:{display:false}},
    scales:{
      y:{beginAtZero:true,ticks:{precision:0},grid:{color:'rgba(0,0,0,0.04)'}},
      x:{grid:{display:false}}
    }
  }
});

// 2. Doughnut
const dem = visualData.categoryDemand || {men:0,women:0,kids:0};
new Chart('categoryChart',{
  type:'doughnut',
  data:{
    labels:['Men','Women','Kids'],
    datasets:[{
      data:[dem.men,dem.women,dem.kids],
      backgroundColor:['#18181b','#6366f1','#f59e0b'],
      borderWidth:0
    }]
  },
  options:{
    responsive:true,maintainAspectRatio:false,cutout:'68%',
    plugins:{legend:{position:'bottom',labels:{padding:16,usePointStyle:true,pointStyleWidth:8}}}
  }
});

// 3. Horizontal bar
const tp = visualData.topSellingProducts || [];
new Chart('statusChart',{
  type:'bar',
  data:{
    labels:tp.map(p=>p.title.length>18?p.title.slice(0,18)+'…':p.title),
    datasets:[{
      label:'Qty Sold',
      data:tp.map(p=>p.quantity),
      backgroundColor:'#18181b',
      borderRadius:3,
      hoverBackgroundColor:'#444'
    }]
  },
  options:{
    indexAxis:'y',responsive:true,maintainAspectRatio:false,
    plugins:{legend:{display:false}},
    scales:{
      x:{beginAtZero:true,ticks:{precision:0},grid:{color:'rgba(0,0,0,0.04)'}},
      y:{grid:{display:false}}
    }
  }
});

// ── PRODUCT MODAL ──
const modal = document.getElementById('productModal');
const form  = document.getElementById('productForm');

function openAddModal(){
  document.getElementById('modalTitle').innerText = 'Add Product';
  document.getElementById('formAction').value = 'add_product';
  form.reset();
  document.getElementById('imagePreviewContainer').style.display='none';
  modal.classList.add('active');
}
function editProduct(p){
  document.getElementById('modalTitle').innerText = 'Edit Product';
  document.getElementById('formAction').value = 'update_product';
  document.getElementById('productId').value = p._id;
  document.getElementById('pTitle').value = p.title;
  document.getElementById('pPrice').value = p.price;
  document.getElementById('pDiscount').value = p.discount_percentage || 0;
  document.getElementById('pStock').value = p.stock;
  document.getElementById('pCategory').value = p.category;
  document.getElementById('pDesc').value = p.description || '';
  if(p.images && p.images[0]){
    document.getElementById('pPreview').src = p.images[0];
    document.getElementById('imagePreviewContainer').style.display='block';
  } else {
    document.getElementById('imagePreviewContainer').style.display='none';
  }
  modal.classList.add('active');
}
function closeModal(){
  modal.classList.remove('active');
  document.getElementById('imagePreviewContainer').style.display='none';
}
function previewImage(inp){
  if(inp.files&&inp.files[0]){
    const r=new FileReader();
    r.onload=e=>{document.getElementById('pPreview').src=e.target.result;document.getElementById('imagePreviewContainer').style.display='block';};
    r.readAsDataURL(inp.files[0]);
  }
}
function deleteProduct(id){
  if(confirm('Delete this product?')){
    document.getElementById('deleteProdId').value=id;
    document.getElementById('deleteProductForm').submit();
  }
}

// ── CATEGORY MODAL ──
const catModal = document.getElementById('categoryModal');
const catForm  = document.getElementById('categoryForm');

function openAddCategoryModal(){
  document.getElementById('catModalTitle').innerText='Add Category';
  document.getElementById('catFormAction').value='add_category';
  catForm.reset();
  switchBannerType('image');
  updatePreview('','image');
  document.getElementById('bannerImageUrl').value='';
  document.getElementById('bannerVideoUrl').value='';
  document.getElementById('saveBannerBtn').style.display='none';
  document.getElementById('bannerStatus').textContent='';
  catModal.classList.add('active');
}
function editCategory(c){
  document.getElementById('catModalTitle').innerText='Edit Category';
  document.getElementById('catFormAction').value='update_category';
  document.getElementById('catId').value=c._id;
  document.getElementById('catName').value=c.name;
  document.getElementById('catSlug').value=c.slug;
  document.getElementById('catOrder').value=c.order??0;
  document.getElementById('catActive').value=c.isActive?'1':'0';
  const t=c.bannerType||'image';
  switchBannerType(t);
  if(t==='video'&&c.bannerVideo){
    updatePreview(c.bannerVideo,'video');
    document.getElementById('bannerVideoUrl').value=c.bannerVideo;
  } else if(c.bannerImage){
    updatePreview(c.bannerImage,'image');
    document.getElementById('bannerImageUrl').value=c.bannerImage;
  } else {
    updatePreview('','image');
  }
  document.getElementById('saveBannerBtn').style.display='block';
  document.getElementById('bannerStatus').textContent='';
  catModal.classList.add('active');
}
function closeCatModal(){catModal.classList.remove('active');}
function deleteCategory(id){
  if(confirm('Delete this category?')){
    document.getElementById('deleteCatId').value=id;
    document.getElementById('deleteCategoryForm').submit();
  }
}

// Banner
function switchBannerType(t){
  if(document.getElementById('catBannerType')) document.getElementById('catBannerType').value = t;
  document.getElementById('imageSection').style.display=t==='image'?'block':'none';
  document.getElementById('videoSection').style.display=t==='video'?'block':'none';
  document.getElementById('bTabImg').classList.toggle('on',t==='image');
  document.getElementById('bTabVid').classList.toggle('on',t==='video');
}
function previewBannerFile(inp,t){
  if(!inp.files||!inp.files[0])return;
  updatePreview(URL.createObjectURL(inp.files[0]),t);
}
function previewBannerUrl(url,t){if(url.trim())updatePreview(url,t);}
function updatePreview(url,t){
  const b=document.getElementById('bannerPreviewBox');
  if(!url){b.innerHTML='No banner set';return;}
  b.innerHTML=t==='video'
    ?`<video src="${url}" style="width:100%;height:100%;object-fit:cover;" muted autoplay loop playsinline></video>`
    :`<img src="${url}" style="width:100%;height:100%;object-fit:cover;">`;
}
async function saveCategoryBanner(){
  const id=document.getElementById('catId').value;
  const t=document.getElementById('bTabImg').classList.contains('on')?'image':'video';
  const st=document.getElementById('bannerStatus');
  if(!id){alert('Save category first');return;}
  st.textContent='Saving…';st.style.color='var(--t2)';
  try{
    const fi=t==='video'?document.getElementById('bannerVideoFile'):document.getElementById('bannerImageFile');
    const ui=t==='video'?document.getElementById('bannerVideoUrl').value.trim():document.getElementById('bannerImageUrl').value.trim();
    const file=fi?.files?.[0];
    if(file){
      const fd=new FormData();fd.append('bannerFile',file);fd.append('bannerType',t);
      const r=await fetch(`${API_BASE_URL}/api/v1/categories/${id}/banner`,{method:'POST',headers:{'Authorization':'Bearer '+window.ADMIN_TOKEN},body:fd});
      const d=await r.json();
      if(d.success){st.textContent='✓ Saved';st.style.color='var(--grn)';setTimeout(()=>location.reload(),800);}
      else throw new Error(d.message||'Upload failed');
    }else if(ui){
      const body={bannerType:t};
      if(t==='video')body.bannerVideo=ui;else body.bannerImage=ui;
      const r=await fetch(`${API_BASE_URL}/api/v1/categories/${id}`,{method:'PATCH',headers:{'Content-Type':'application/json','Authorization':'Bearer '+window.ADMIN_TOKEN},body:JSON.stringify(body)});
      const d=await r.json();
      if(d.success){st.textContent='✓ Saved';st.style.color='var(--grn)';setTimeout(()=>location.reload(),800);}
      else throw new Error(d.message||'Save failed');
    }else{st.textContent='Select file or enter URL';st.style.color='var(--red)';}
  }catch(e){st.textContent='✗ '+e.message;st.style.color='var(--red)';}
}

form.onsubmit=()=>true;
catForm.onsubmit=()=>true;
</script>
</body>
</html>