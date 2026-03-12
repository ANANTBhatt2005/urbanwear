<?php
session_start();
require_once 'api-helper.php';


// 1. Authentication Check
requireLogin();
$user = getCurrentUser();
$token = getAuthToken();

// 2. Fetch Wishlist Data
$wishlistItems = [];
if ($token) {
    $response = $API->get('/api/v1/wishlist/user', $token);
    if ($response['success']) {
        $wishlistItems = $response['data'] ?? [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Wishlist — URBANWEAR</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500&family=DM+Sans:opsz,wght@9..40,200;9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
/* ══════════════════════════════════════════
   URBANWEAR — WISHLIST
   Net-A-Porter × MyTheresa × Farfetch
   Pure white · Serif headlines · Editorial
   ══════════════════════════════════════════ */
:root {
  --white:   #ffffff;
  --off:     #faf9f7;
  --bg:      #f5f3ef;
  --border:  #e8e4de;
  --border2: #d4cfc7;
  --ink:     #1a1814;
  --mid:     #6b6560;
  --muted:   #a8a29a;
  --accent:  #1a1814;
  --gold:    #b8965a;
  --grn:     #2d5a3d;
  --red:     #9e3a2a;
  --ease:    cubic-bezier(0.16,1,0.3,1);
  --sw:      260px;
}
*,*::before,*::after { margin:0; padding:0; box-sizing:border-box; }
html { scroll-behavior:smooth; }
body {
  font-family:'DM Sans',sans-serif;
  background:var(--white);
  color:var(--ink);
  min-height:100vh;
  display:flex;
  flex-direction: column;
  -webkit-font-smoothing:antialiased;
}
a { text-decoration:none; color:inherit; }
button { font-family:'DM Sans',sans-serif; cursor:pointer; }

/* ══ MAIN LAYOUT ══ */
.wishlist-container {
  display: flex;
  flex: 1;
}

/* ══ SIDEBAR (Copied from user-dashboard) ══ */
.sb {
  width: var(--sw);
  background: var(--white);
  border-right: 1px solid var(--border);
  position: sticky; top:0; height:100vh;
  display: flex; flex-direction:column;
  overflow-y: auto; flex-shrink:0;
}
.sb-top { padding: 28px 28px 22px; border-bottom: 1px solid var(--border); }
.sb-logo {
  font-family:'Cormorant Garamond',serif; font-size:1rem; font-weight:600;
  letter-spacing:5px; text-transform:uppercase; color:var(--ink);
  display:block; margin-bottom:2px;
}
.sb-logo-sub { font-size:0.58rem; letter-spacing:0.3em; text-transform:uppercase; color:var(--muted); font-weight:400; }
.sb-user { padding: 24px 28px; border-bottom: 1px solid var(--border); }
.sb-avatar-wrap { display: flex; align-items:center; gap:14px; margin-bottom: 14px; }
.sb-avatar {
  width: 44px; height:44px; border-radius:50%; background: var(--ink);
  display:flex; align-items:center; justify-content:center;
  font-family:'Cormorant Garamond',serif; font-size:1.1rem; font-weight:600; color:var(--white); flex-shrink:0;
}
.sb-uname { font-size:0.9rem; font-weight:600; color:var(--ink); line-height:1.2; }
.sb-uemail { font-size:0.68rem; color:var(--muted); margin-top:2px; font-weight:300; }
.sb-member {
  display:inline-flex; align-items:center; gap:6px;
  font-size:0.58rem; font-weight:600; letter-spacing:0.2em;
  text-transform:uppercase; color:var(--grn);
  background:rgba(45,90,61,0.07); padding:4px 12px; border-radius:20px;
}
.sb-member::before { content:''; width:5px; height:5px; border-radius:50%; background:var(--grn); display:block; }
.sb-nav { padding:18px 0; flex:1; }
.sb-grp-lbl {
  font-size:0.56rem; font-weight:700; letter-spacing:0.38em; text-transform:uppercase; color:var(--muted);
  padding:0 28px; margin:14px 0 4px; display:block;
}
.nl {
  display:flex; align-items:center; gap:11px; padding:10px 28px;
  font-size:0.73rem; font-weight:400; text-transform:uppercase; letter-spacing:0.1em;
  color:var(--mid); cursor:pointer; border-right:2px solid transparent; transition:all 0.22s; user-select:none;
}
.nl i { font-size:0.76rem; width:15px; text-align:center; opacity:0.6; }
.nl:hover { color:var(--ink); background:var(--off); }
.nl.on { color:var(--ink); font-weight:600; border-right-color:var(--ink); background:var(--off); }
.nl.on i { opacity:1; }
.sb-hr { height:1px; background:var(--border); margin:12px 28px; }
.nl.exit { color:rgba(158,58,42,0.6); }
.nl.exit:hover { color:var(--red); background:rgba(158,58,42,0.04); border-right-color:var(--red); }

/* ══ MAIN ══ */
.main { flex:1; min-height:100vh; background:var(--off); display:flex; flex-direction:column; }
.topbar {
  height:54px; background:var(--white); border-bottom:1px solid var(--border);
  display:flex; align-items:center; justify-content:space-between; padding:0 48px;
  position:sticky; top:0; z-index:100; flex-shrink:0;
}
.tb-crumb { display:flex; align-items:center; gap:7px; font-size:0.72rem; letter-spacing:0.06em; }
.tb-crumb .seg { color:var(--muted); }
.tb-crumb .sep { color:var(--border2); }
.tb-crumb .cur { color:var(--ink); font-weight:600; }
.tb-right { display:flex; align-items:center; gap:12px; }
.tb-shop {
  display:flex; align-items:center; gap:7px; font-size:0.68rem; font-weight:600; letter-spacing:0.15em;
  text-transform:uppercase; color:var(--mid); padding:7px 16px; border:1px solid var(--border); transition:all 0.22s;
}
.tb-shop:hover { border-color:var(--ink); color:var(--ink); }
.content { padding:40px 48px 80px; flex:1; }

/* ══ PAGE HEADER ══ */
.ph { margin-bottom:36px; padding-bottom:28px; border-bottom:1px solid var(--border); }
.ph-eyebrow { font-size:0.62rem; font-weight:600; text-transform:uppercase; letter-spacing:0.35em; color:var(--muted); margin-bottom:6px; }
.ph-title { font-family:'Cormorant Garamond',serif; font-size:2.6rem; font-weight:400; color:var(--ink); line-height:1; letter-spacing:-0.02em; }
.ph-title em { font-style:italic; color:var(--gold); }
.ph-sub { font-size:0.78rem; font-weight:300; color:var(--muted); margin-top:6px; letter-spacing:0.04em; }

/* ══ WISHLIST GRID ══ */
.wishlist-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
}
.w-card {
  background: var(--white);
  border: 1px solid var(--border);
  transition: all 0.3s var(--ease);
  position: relative;
  display: flex;
  flex-direction: column;
}
.w-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(26,24,20,0.08); border-color:var(--border2); }
.w-img-wrap { position: relative; aspect-ratio: 3/4; overflow: hidden; background:var(--off); }
.w-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.8s var(--ease); }
.w-card:hover .w-img { transform: scale(1.05); }

/* Remove button */
.w-remove {
  position: absolute;
  top: 12px; right: 12px;
  width: 32px; height: 32px;
  border-radius: 50%;
  background: var(--white);
  border: 1px solid var(--border);
  color: var(--muted);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  z-index: 2;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.w-remove:hover { color: var(--red); border-color: rgba(158,58,42,0.3); background: rgba(158,58,42,0.03); transform:scale(1.05); }

.w-info { padding: 20px; display: flex; flex-direction: column; flex: 1; }
.w-cat { font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em; color: var(--muted); margin-bottom: 6px; }
.w-title { font-size: 0.95rem; font-weight: 500; color: var(--ink); margin-bottom: 8px; line-height: 1.3; }
.w-price { font-family: 'Cormorant Garamond',serif; font-size: 1.25rem; font-weight: 600; color: var(--ink); margin-bottom: 20px; margin-top: auto; }

/* ══ BUTTONS ══ */
.btn-dark {
  width: 100%;
  padding: 12px 20px;
  background: var(--ink); color: var(--white);
  border: none;
  font-family: 'DM Sans',sans-serif; font-size: 0.68rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.18em;
  transition: all 0.25s var(--ease);
}
.btn-dark:hover { background: #333; }

/* ══ EMPTY STATE ══ */
.empty-panel { text-align:center; padding:80px 40px; background:var(--white); border:1px solid var(--border); }
.empty-icon { font-size: 3rem; color: var(--gold); margin-bottom: 20px; display: inline-block; opacity: 0.8; }
.empty-panel p { color:var(--muted); font-weight:300; margin-bottom:24px; font-size:0.95rem; }

/* Responsive */
@media(max-width:820px) {
  .wishlist-container { flex-direction: column; }
  .sb { width:100%; height:auto; position:static; border-right:none; border-bottom:1px solid var(--border); }
  .sb-nav { display:flex; flex-wrap:wrap; padding:10px; }
  .nl { border-right:none; border-bottom:2px solid transparent; }
  .nl.on { border-right:none; border-bottom-color:var(--ink); }
  .topbar { padding:0 20px; }
  .content { padding:24px 18px 48px; }
  .ph-title { font-size:2rem; }
}
</style>
</head>
<body>

<div class="wishlist-container">
  <!-- ══ SIDEBAR ══ -->
  <aside class="sb">
    <div class="sb-top">
      <a href="index.php" class="sb-logo">Urbanwear</a>
      <span class="sb-logo-sub">My Account</span>
    </div>

    <div class="sb-user">
      <div class="sb-avatar-wrap">
        <div class="sb-avatar"><?php echo strtoupper(substr($user['name']??'U',0,1)); ?></div>
        <div>
          <div class="sb-uname"><?php echo htmlspecialchars($user['name']??''); ?></div>
          <div class="sb-uemail"><?php echo htmlspecialchars($user['email']??''); ?></div>
        </div>
      </div>
      <div class="sb-member">Active Member</div>
    </div>

    <nav class="sb-nav">
      <span class="sb-grp-lbl">Dashboard</span>
      <a href="user-dashboard.php" class="nl">
        <i class="fa-solid fa-grid-2"></i> Overview
      </a>
      <a href="user-dashboard.php#orders" class="nl">
        <i class="fa-solid fa-bag-shopping"></i> My Orders
      </a>
      <a href="user-dashboard.php#addresses" class="nl">
        <i class="fa-solid fa-location-dot"></i> Addresses
      </a>
      <a href="wishlist.php" class="nl on">
        <i class="fa-solid fa-heart"></i> Wishlist
        <span class="nl-badge"><?php echo count($wishlistItems); ?></span>
      </a>
      <div class="sb-hr"></div>
      <span class="sb-grp-lbl">More</span>
      <a href="index.php" class="nl"><i class="fa-solid fa-arrow-left"></i> Back to Shop</a>
      <a href="logout-connected.php" class="nl exit"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
    </nav>
  </aside>

  <!-- ══ MAIN ══ -->
  <div class="main">
    <div class="topbar">
      <div class="tb-crumb">
        <span class="seg">Account</span><span class="sep">/</span><span class="cur">Wishlist</span>
      </div>
      <div class="tb-right">
        <a href="index.php" class="tb-shop"><i class="fa-solid fa-arrow-up-right-from-square" style="font-size:0.7rem;"></i> Continue Shopping</a>
      </div>
    </div>

    <div class="content">
      <div class="ph">
        <div class="ph-eyebrow">Saved Items</div>
        <h1 class="ph-title">My <em>Wishlist</em></h1>
        <p class="ph-sub">Curate your perfect wardrobe. Items saved here remain across devices.</p>
      </div>

      <?php if (empty($wishlistItems)): ?>
        <div class="empty-panel">
          <div class="empty-icon"><i class="fa-solid fa-heart"></i></div>
          <h3 style="font-family:'Cormorant Garamond',serif; font-size:1.8rem; font-weight:400; color:var(--ink); margin-bottom:12px;">❤️ Your wishlist is empty</h3>
          <p>Discover our latest collections and save your favorite pieces.</p>
          <a href="index.php" class="btn btn-dark" style="display:inline-flex; width:auto;"><i class="fa-solid fa-basket-shopping"></i> Continue Shopping</a>
        </div>
      <?php else: ?>
        <div class="wishlist-grid">
          <?php foreach ($wishlistItems as $item): 
            $img = !empty($item['images']) ? htmlspecialchars($item['images'][0]) : 'assets/images/placeholder.jpg';
          ?>
            <div class="w-card" id="w-item-<?php echo htmlspecialchars($item['productId']); ?>">
              <div class="w-img-wrap">
                <button class="w-remove" onclick="removeFromWishlist('<?php echo htmlspecialchars($item['productId']); ?>')" title="Remove from wishlist">
                  <i class="fa-solid fa-xmark"></i>
                </button>
                <a href="product.php?id=<?php echo urlencode($item['productId']); ?>">
                  <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-img" loading="lazy">
                </a>
              </div>
              <div class="w-info">
                <div class="w-cat"><?php echo htmlspecialchars($item['category'] ?? 'Product'); ?></div>
                <a href="product.php?id=<?php echo urlencode($item['productId']); ?>" class="w-title"><?php echo htmlspecialchars($item['title']); ?></a>
                <div class="w-price">₹<?php echo number_format((float)($item['price'] ?? 0)); ?></div>
                <button class="btn-dark add-to-cart-btn" 
                        data-id="<?php echo htmlspecialchars($item['productId']); ?>"
                        data-name="<?php echo htmlspecialchars($item['title']); ?>"
                        data-price="<?php echo htmlspecialchars($item['price'] ?? 0); ?>"
                        data-image="<?php echo $img; ?>">
                  Add to Cart
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="assets/js/wishlist.js"></script>
<!-- Assuming a global cart.js exists for "add-to-cart-btn" functionality -->
<script src="assets/js/cart.js"></script>


</body>
</html>
