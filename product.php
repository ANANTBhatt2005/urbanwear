<?php
session_start();
require_once __DIR__ . '/api-helper.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /clothing_project/index.php');
    exit;
}



$res = $API->get('/api/v1/products/' . $id);
if (!$res['success']) {
    http_response_code(404);
    echo 'Product not found';
    exit;
}
$p = $res['data'];
$productId = (string)($p['_id'] ?? $p['id'] ?? '');
$productName = $p['title'] ?? $p['name'] ?? 'Untitled';
$productPrice = isset($p['price']) ? (float)$p['price'] : 0;
$productDiscount = isset($p['discount_percentage']) ? (float)$p['discount_percentage'] : 0;
$discountedPrice = $productPrice - ($productPrice * $productDiscount / 100);
$images = (is_array($p['images']) && count($p['images']) > 0) ? $p['images'] : [$p['image'] ?? 'https://via.placeholder.com/800'];
$mainImg = $images[0];

// --- DSS DATA: REVIEWS & SUMMARY ---
$reviewsData = [];
$summary = ['averageRating' => 0, 'totalCount' => 0, 'distribution' => [1=>0, 2=>0, 3=>0, 4=>0, 5=>0]];
$isTrending = false;

$revRes = $API->get('/api/v1/products/' . $productId . '/reviews');
if ($revRes['success']) {
    $reviewsData = $revRes['data'] ?? [];
}

$sumRes = $API->get('/api/v1/reviews/summary/' . $productId);
if ($sumRes['success']) {
    $summary = $sumRes['data'];
}

$trendRes = $API->get('/api/v1/products/trending?limit=10');
if ($trendRes['success']) {
    foreach ($trendRes['data'] as $tp) {
        if (($tp['_id'] ?? $tp['id']) == $productId) { $isTrending = true; break; }
    }
}

// --- KEYWORD DSS LOGIC ---
$keywords = [
    'Fabric Quality' => ['fabric', 'material', 'cloth', 'cotton', 'quality'],
    'Comfort' => ['comfortable', 'soft', 'comfort', 'easy'],
    'Fit' => ['fit', 'size', 'fitting', 'perfect'],
    'Style' => ['style', 'look', 'design', 'trendy', 'fashion'],
    'Value' => ['value', 'price', 'money', 'worth', 'cheap', 'budget']
];
$keywordScores = array_fill_keys(array_keys($keywords), 0);
foreach($reviewsData as $rev) {
    $txt = strtolower($rev['reviewText'] ?? '');
    foreach($keywords as $key => $matches) {
        foreach($matches as $m) {
            if(strpos($txt, $m) !== false) {
                $keywordScores[$key]++;
                break;
            }
        }
    }
}
arsort($keywordScores);
$topKeywords = array_filter($keywordScores, function($v) { return $v > 0; });
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($productName); ?> | UrbanWear</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --premium-black: #1a1a1a; --urban-gold: #c5a059; --bg-light: #fdfdfd; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); color: var(--premium-black); }
        .product-container { max-width: 1200px; margin: 40px auto; display: flex; gap: 40px; padding: 0 20px; }
        
        /* LEFT: GALLERY */
        .gallery-section { flex: 1; display: flex; gap: 15px; }
        .thumbnails { display: flex; flex-direction: column; gap: 10px; }
        .thumbnails img { width: 70px; height: 90px; object-fit: cover; border: 1px solid #eee; cursor: pointer; border-radius: 4px; transition: 0.2s; }
        .thumbnails img:hover { border-color: var(--urban-gold); }
        .main-image-container { flex: 1; position: relative; overflow: hidden; border-radius: 8px; border: 1px solid #eee; background: #fff; height: 600px; }
        .main-image { width: 100%; height: 100%; object-fit: contain; transition: transform 0.3s ease; }
        .main-image-container:hover .main-image { transform: scale(1.1); }

        /* RIGHT: DETAILS */
        .details-section { flex: 1.2; }
        .breadcrumb { font-size: 0.85rem; color: #888; margin-bottom: 15px; }
        .product-title { font-size: 2rem; font-weight: 700; margin-bottom: 5px; }
        .badge-trending { display: inline-block; background: #fff4e5; color: #ff8c00; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; border: 1px solid #ffd8a8; margin-bottom: 10px; }
        .rating-header { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
        .stars-avg { background: #388e3c; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 4px; }
        .review-count-header { color: #878787; font-weight: 600; font-size: 0.9rem; }
        .price-tag { font-size: 1.8rem; font-weight: 700; color: #212121; margin: 15px 0; }
        .price-tag span { font-size: 1rem; color: #878787; text-decoration: line-through; margin-left: 10px; font-weight: 400; }
        
        .size-selection { margin: 25px 0; }
        .size-title { font-weight: 700; font-size: 0.9rem; margin-bottom: 10px; text-transform: uppercase; }
        .size-btns { display: flex; gap: 10px; }
        .size-box { border: 1px solid #e0e0e0; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: 600; transition: 0.2s; }
        .size-box:hover, .size-box.active { border-color: var(--urban-gold); color: var(--urban-gold); background: #fffaf0; }

        .action-btns { display: flex; gap: 15px; margin-top: 30px; }
        .btn-buy { flex: 1; padding: 18px; border: none; border-radius: 4px; font-weight: 700; font-size: 1rem; cursor: pointer; text-transform: uppercase; background: #fb641b; color: #fff; }
        .btn-cart { flex: 1; padding: 18px; border: none; border-radius: 4px; font-weight: 700; font-size: 1rem; cursor: pointer; text-transform: uppercase; background: #ff9f00; color: #fff; }

        /* DSS SECTION: REVIEWS */
        .dss-section { background: #fff; margin-top: 40px; border: 1px solid #eee; border-radius: 8px; padding: 30px; }
        .dss-grid { display: flex; gap: 40px; }
        .rating-summary { flex: 1; border-right: 1px solid #eee; padding-right: 40px; }
        .dist-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; font-size: 0.85rem; }
        .bar-bg { flex: 1; height: 6px; background: #eee; border-radius: 10px; overflow: hidden; }
        .bar-fill { height: 100%; background: #388e3c; border-radius: 10px; }
        
        .keyword-insights { flex: 1; }
        .insight-tag { display: inline-block; background: #f0f5ff; color: #2874f0; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: 600; margin: 0 8px 8px 0; border: 1px solid #dce8ff; }

        .review-list { margin-top: 30px; }
        .review-item { border-bottom: 1px solid #f0f0f0; padding: 20px 0; }
        .rev-rating-box { background: #388e3c; color: #fff; font-size: 0.75rem; padding: 1px 6px; border-radius: 3px; font-weight: 700; margin-right: 10px; }
        .rev-text { color: #212121; line-height: 1.5; margin: 10px 0; }
        .rev-meta { font-size: 0.8rem; color: #878787; display: flex; align-items: center; gap: 10px; }
        .verified-tag { color: #388e3c; font-weight: 700; display: flex; align-items: center; gap: 4px; }
        
        /* FORM */
        .review-form-container { margin-top: 40px; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; background: #fafafa; }
        .form-title { font-weight: 700; margin-bottom: 15px; }
        textarea { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; resize: vertical; margin-bottom: 15px; font-family: inherit; }
        .stars-selector { margin-bottom: 15px; display: flex; gap: 5px; font-size: 1.5rem; }
        .star-in { cursor: pointer; color: #ccc; }
        .star-in.active { color: #f59e0b; }
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

<div class="product-container">
    <!-- IMAGES -->
    <div class="gallery-section">
        <div class="thumbnails">
            <?php foreach($images as $img): ?>
                <img src="<?php echo htmlspecialchars($img); ?>" onclick="setMainImage(this.src)">
            <?php endforeach; ?>
        </div>
        <div class="main-image-container">
            <img id="mainImg" src="<?php echo htmlspecialchars($mainImg); ?>" class="main-image">
        </div>
    </div>

    <!-- DETAILS -->
    <div class="details-section">
        <div class="breadcrumb">Home > <?php echo htmlspecialchars($p['category']); ?> > <?php echo htmlspecialchars($productName); ?></div>
        
        <?php if($isTrending): ?>
            <div class="badge-trending">🔥 ON TRENDING IN <?php echo strtoupper($p['category']); ?></div>
        <?php endif; ?>

        <h1 class="product-title"><?php echo htmlspecialchars($productName); ?></h1>
        
        <div class="rating-header">
            <div class="stars-avg"><?php echo $summary['averageRating']; ?> ★</div>
            <div class="review-count-header"><?php echo $summary['totalCount']; ?> Ratings & <?php echo count($reviewsData); ?> Reviews</div>
        </div>

        <div class="price-tag">
            <?php if ($productDiscount > 0): ?>
                ₹<?php echo number_format($discountedPrice); ?>
                <span>₹<?php echo number_format($productPrice); ?></span>
                <small style="color:#388e3c; font-size:1rem; margin-left:5px;"><?php echo $productDiscount; ?>% OFF</small>
            <?php else: ?>
                ₹<?php echo number_format($productPrice); ?>
            <?php endif; ?>
        </div>

        <div style="color: #388e3c; font-weight: 700; margin-bottom: 20px;">
            <?php echo ($p['stock'] > 10) ? 'Available in Stock' : 'Low Stock: Only ' . $p['stock'] . ' left!'; ?>
        </div>

        <div class="size-selection">
            <div class="size-title">Select Size</div>
            <div class="size-btns">
                <?php 
                $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
                foreach($sizes as $sh): ?>
                    <div class="size-box" onclick="this.parentElement.querySelectorAll('.size-box').forEach(b => b.classList.remove('active')); this.classList.add('active');"><?php echo $sh; ?></div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="action-btns">
            <button onclick="handlePageAddToCart(true)" class="btn-buy">Buy Now</button>
            <button onclick="handlePageAddToCart()" class="btn-cart">Add to Cart</button>
        </div>
        <div id="cartMsg" style="margin-top: 15px; color: #2874f0; font-weight: 600;"></div>

        <div style="margin-top: 30px; line-height: 1.6; color: #212121;">
            <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($p['description'])); ?></p>
        </div>
    </div>
</div>

<div class="product-container" style="display:block;">
    <div class="dss-section">
        <h2 style="font-size: 1.5rem; border-bottom: 1px solid #f0f0f0; padding-bottom: 20px; margin-bottom: 20px;">Ratings & Reviews</h2>
        
        <div class="dss-grid">
            <!-- RATINGS DIST -->
            <div class="rating-summary">
                <div style="display:flex; align-items:center; gap:20px; margin-bottom:30px;">
                    <div style="font-size: 3rem; font-weight: 700;"><?php echo $summary['averageRating']; ?>★</div>
                    <div style="color:#878787;">
                        <div style="font-weight:700; color:#212121;"><?php echo $summary['totalCount']; ?> Ratings &</div>
                        <div><?php echo count($reviewsData); ?> Reviews</div>
                    </div>
                </div>
                <?php 
                for($i=5; $i>=1; $i--): 
                    $pct = ($summary['totalCount'] > 0) ? ($summary['distribution'][$i] / $summary['totalCount']) * 100 : 0;
                ?>
                <div class="dist-row">
                    <div style="width:30px;"><?php echo $i; ?>★</div>
                    <div class="bar-bg"><div class="bar-fill" style="width: <?php echo $pct; ?>%;"></div></div>
                    <div style="width:40px; text-align:right; color:#878787;"><?php echo $summary['distribution'][$i]; ?></div>
                </div>
                <?php endfor; ?>
            </div>

            <!-- KEYWORD INSIGHTS -->
            <div class="keyword-insights">
                <div class="size-title">Customer Decision Indicators</div>
                <p style="font-size: 0.85rem; color: #878787; margin-bottom: 15px;">Based on recent purchases and feedback:</p>
                <?php if(empty($topKeywords)): ?>
                    <p style="font-size: 0.9rem; font-style: italic;">Awaiting more customer feedback...</p>
                <?php else: ?>
                    <div style="margin-bottom: 10px; font-weight:600; font-size:0.9rem;">👍 Customers liked this for:</div>
                    <?php foreach($topKeywords as $k => $count): ?>
                        <span class="insight-tag"><?php echo $k; ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div style="margin-top: 20px; background:#fff9f0; padding:15px; border-radius:4px; border:1px solid #ffedcc;">
                    <div style="font-size:0.85rem; font-weight:700; color:#ff8c00;">DSS TIP:</div>
                    <p style="font-size:0.8rem; margin:5px 0 0 0;">Check "Verified Buyer" reviews for accurate sizing feedback.</p>
                </div>
            </div>
        </div>

        <!-- REVIEW SUBMISSION INFO -->
        <div class="review-form-container">
            <div style="background: #fff9e6; padding: 20px; border-radius: 8px; border: 1px solid #ffd966; text-align: center;">
                <div style="font-size: 1.1rem; font-weight: 600; color: #333; margin-bottom: 8px;">📦 Want to review this product?</div>
                <p style="font-size: 0.9rem; color: #666; margin: 0;">
                    Purchase this product and you'll be able to rate it from <strong>My Orders</strong> after delivery!
                </p>
            </div>
        </div>

        <!-- LIST -->
        <div class="review-list">
            <?php if(empty($reviewsData)): ?>
                <p style="color:#878787; text-align:center; padding:40px;">No reviews yet. Be the first to help others with your feedback!</p>
            <?php else: ?>
                <?php foreach($reviewsData as $r): ?>
                    <div class="review-item">
                        <div style="display:flex; align-items:center;">
                            <span class="rev-rating-box"><?php echo $r['rating']; ?> ★</span>
                            <span style="font-weight:700; font-size:0.95rem;"><?php echo ($r['rating'] >= 4) ? 'Highly Recommended' : ($r['rating'] >= 3 ? 'Good Product' : 'Average'); ?></span>
                        </div>
                        <div class="rev-text"><?php echo htmlspecialchars($r['reviewText'] ?? ''); ?></div>
                        <div class="rev-meta">
                            <span style="font-weight:700; color:#212121;"><?php echo htmlspecialchars($r['userId']['name'] ?? 'Generic User'); ?></span>
                            <?php if($r['verifiedBuyer']): ?>
                                <span class="verified-tag">✔ Verified Buyer</span>
                            <?php endif; ?>
                            <span><?php echo date('M, Y', strtotime($r['createdAt'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const product = {
        id: "<?php echo $productId; ?>",
        name: "<?php echo addslashes($productName); ?>",
        price: <?php echo $productPrice; ?>,
        discount_percentage: <?php echo $productDiscount; ?>,
        img: "<?php echo addslashes($mainImg); ?>"
    };

    window.IS_LOGGED_IN = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
    window.AUTH_TOKEN = '<?php echo getAuthToken(); ?>';

    function setMainImage(src) { document.getElementById('mainImg').src = src; }

    function handlePageAddToCart(go = false) {
        if (!window.IS_LOGGED_IN) {
            if (confirm("Please login or register to continue your purchase.")) {
                window.location.href = 'login.php';
            }
            return;
        }

        if (typeof window.addToCart === 'function') {
            window.addToCart(product.id, product.name, product.price, product.img, 1, product.discount_percentage);
        } else {
            // Fallback if global script not loaded
            const cartKey = 'urbanwear_cart';
            let cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
            const existing = cart.find(i => i.id === product.id);
            if(existing) { existing.qty++; } else { cart.push({...product, qty: 1}); }
            localStorage.setItem(cartKey, JSON.stringify(cart));
            if(go) { window.location.href = 'cart.php'; }
            else { document.getElementById('cartMsg').innerText = '✔ Added to cart successfully!'; }
        }
    }


</script>

  <script src="assets/js/cart.js"></script>
</body>
</html>
