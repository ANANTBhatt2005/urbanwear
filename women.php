<?php
session_start();
// WOMEN Category - premium catalog view
require_once __DIR__ . '/api-helper.php';
$category = 'women';
$res = $API->get('/api/v1/products?category=' . urlencode($category) . '&limit=200');
$products = [];
$api_error = null;
if (!empty($res) && isset($res['success']) && $res['success'] && is_array($res['data'])) {
  foreach ($res['data'] as $p) {
    $products[] = [
      'id' => (string)($p['_id'] ?? $p['id'] ?? ''),
      'name' => $p['title'] ?? $p['name'] ?? 'Untitled',
      'price' => isset($p['price']) ? (float)$p['price'] : 0.0,
      'discount_percentage' => isset($p['discount_percentage']) ? (float)$p['discount_percentage'] : 0,
      'img' => (is_array($p['images']) && count($p['images'])) ? $p['images'][0] : ($p['image'] ?? 'https://via.placeholder.com/900'),
      'category' => $p['category'] ?? 'women'
    ];
  }
} else {
  $api_error = $res['message'] ?? 'Unable to load products';
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="category" content="women" />
  <title>Women | UrbanWear Collection</title>
  <link href="css/premium-plp.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
  
  <nav class="site-nav">
    <script>
      window.IS_LOGGED_IN = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
      window.AUTH_TOKEN = '<?php echo getAuthToken(); ?>';
    </script>
    <div class="brand"><a href="index.php">URBANWEAR</a></div>
    <div class="links">
      <a href="index.php">Home</a>
      <a href="index.php#drop">New Drops</a>
      <a href="men.php">Men</a>
      <a href="women.php" class="active">Women</a>
      <a href="kids.php">Kids</a>
      <a href="cart.php" style="position:relative; margin-right: 15px;">
        <i class="fa-solid fa-bag-shopping"></i> Cart
        <span class="cart-count-badge" style="display:none; position: absolute; top: -8px; right: -12px; background: #c5a059; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 10px; font-weight: 700; min-width: 18px; text-align: center; display: flex; align-items: center; justify-content: center;">0</span>
      </a>
      <?php if(isset($_SESSION['user_id'])): ?>
        <?php include 'includes/user-profile-dropdown.php'; ?>
      <?php else: ?>
        <a href="login.php"><i class="fa-regular fa-user"></i> Login</a>
      <?php endif; ?>
    </div>
  </nav>

  <header class="plp-hero">
    <h1>Women's Collection</h1>
    <p>Refined edits. Elegant aesthetics.</p>
  </header>

  <main class="catalog">
    <!-- Analytics Section -->
    <section class="analytics-section">
      <div class="filter-sidebar">
        <button class="filter-btn active" data-filter="all">
          <i class="fa-solid fa-grid-2"></i> All Products
        </button>
        <button class="filter-btn" data-filter="bestsellers">
          <i class="fa-solid fa-fire"></i> Bestsellers
        </button>
        <button class="filter-btn" data-filter="trending">
          <i class="fa-solid fa-arrow-trend-up"></i> Trending
        </button>
        <button class="filter-btn" data-filter="timeless">
          <i class="fa-solid fa-gem"></i> Timeless
        </button>
      </div>

      <div class="chart-container" id="analyticsChart" style="display:none;">
        <canvas id="donutChart"></canvas>
        <div class="chart-legend" id="chartLegend"></div>
      </div>
    </section>

    <!-- Product Grid -->
    <?php if ($api_error): ?>
      <div class="empty">We couldn't load the collection. Please try again later.</div>
    <?php elseif (empty($products)): ?>
      <div class="empty">No products found in this collection.</div>
    <?php else: ?>
      <div class="grid">
        <?php foreach ($products as $p): ?>
          <!-- Product Card -->
          <a href="product.php?id=<?php echo urlencode($p['id']); ?>" class="product-card-link" style="text-decoration: none; color: inherit; display: block;">
          <article class="product-card">
            
            <div class="card-media">
               <?php 
                 if (rand(0, 10) > 8) echo '<span class="badge-new">Trending</span>';
               ?>
              <img src="<?php echo htmlspecialchars($p['img']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
              
              <div class="card-actions">
                <button class="add-to-cart-btn"
                  data-id="<?php echo htmlspecialchars($p['id']); ?>"
                  data-name="<?php echo htmlspecialchars($p['name']); ?>"
                  data-price="<?php echo htmlspecialchars(number_format($p['price'],2,'.','')); ?>"
                  data-discount-percentage="<?php echo htmlspecialchars($p['discount_percentage']); ?>"
                  data-image="<?php echo htmlspecialchars($p['img']); ?>"
                  data-category="women">
                  ADD TO CART
                </button>
              </div>
            </div>

            <div class="card-meta">
              <div class="product-title"><?php echo htmlspecialchars($p['name']); ?></div>
              <?php 
                $originalPrice = $p['price'];
                $discount = $p['discount_percentage'];
                if ($discount > 0) {
                    $discountedPrice = $originalPrice - ($originalPrice * $discount / 100);
                    echo '<div class="product-price">₹' . number_format($discountedPrice) . ' <span style="text-decoration:line-through; color:#878787; font-size:0.8rem; margin-left:5px;">₹' . number_format($originalPrice) . '</span> <small style="color:#388e3c; margin-left:5px;">' . $discount . '% OFF</small></div>';
                } else {
                    echo '<div class="product-price">₹' . number_format($originalPrice) . '</div>';
                }
              ?>
            </div>

          </article>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <script src="assets/js/cart.js"></script>
  <script src="assets/js/category-analytics.js"></script>
</body>
</html>
