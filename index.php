<?php 
session_start();
require_once __DIR__ . '/api-helper.php';
// Fetch latest products for Home (12 items)
$products = [];
$api_error = false;
$res = $API->get('/api/v1/products?limit=12');
if (!empty($res) && isset($res['success']) && $res['success'] && is_array($res['data'])) {
  $products = $res['data'];
} else {
  $api_error = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UrbanWear | Modern Fashion for the Streets</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@200;300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root {
      --bg-white: #ffffff;
      --bg-soft: #fdfbf7;
      --bg-light: #f8f8f8;
      --text-primary: #1a1a1a;
      --text-muted: #767676;
      --accent-gold: #d4af37;
      --border-light: #f0f0f0;
      --transition-premium: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
      --shadow-soft: 0 10px 40px rgba(0, 0, 0, 0.04);
      --container-max: 1440px;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body {
      font-family: 'Inter', sans-serif;
      color: var(--text-primary);
      background-color: var(--bg-white);
      line-height: 1.7;
      overflow-x: hidden;
      -webkit-font-smoothing: antialiased;
    }

    a { text-decoration: none; color: inherit; transition: var(--transition-premium); }
    
    .container {
      max-width: var(--container-max);
      margin: 0 auto;
      padding: 0 60px;
    }

    /* NAVBAR */
    .navbar {
      position: fixed;
      top: 0;
      width: 100%;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      z-index: 1000;
      padding: 24px 60px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid var(--border-light);
      transition: var(--transition-premium);
    }

    .navbar.scrolled { padding: 18px 60px; }

    .logo {
      font-family: 'Playfair Display', serif;
      font-size: 1.6rem;
      font-weight: 700;
      letter-spacing: 3px;
      text-transform: uppercase;
    }

    .nav-links { display: flex; gap: 40px; align-items: center; }
    .nav-links a {
      font-size: 0.8rem;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.15em;
      color: var(--text-muted);
      position: relative;
    }
    .nav-links a:hover { color: var(--text-primary); }
    .nav-links a::after {
      content: ''; position: absolute; bottom: -5px; left: 0; width: 0; height: 1px;
      background: var(--text-primary); transition: var(--transition-premium);
    }
    .nav-links a:hover::after { width: 100%; }

    /* HERO */
    main { padding-top: 100px; }

    .hero {
      height: 80vh;
      background: var(--bg-soft);
      display: flex;
      align-items: center;
      margin-bottom: 120px;
      position: relative;
      overflow: hidden;
    }

    .hero-content {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      gap: 60px;
    }

    .hero-text { flex: 1; padding-right: 40px; }
    .hero-text span {
      display: block; font-size: 0.85rem; text-transform: uppercase;
      letter-spacing: 0.4em; color: var(--text-muted); margin-bottom: 25px;
    }
    .hero-text h1 {
      font-family: 'Playfair Display', serif; font-size: 4.2rem;
      line-height: 1.1; margin-bottom: 40px; font-weight: 400;
    }
    
    .hero-image { flex: 1.2; height: 90%; overflow: hidden; border-radius: 2px; }
    .hero-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 2s ease; }
    .hero:hover .hero-image img { transform: scale(1.05); }

    .btn-premium {
      display: inline-block; padding: 18px 45px; background: var(--text-primary);
      color: #fff; font-size: 0.8rem; font-weight: 600; text-transform: uppercase;
      letter-spacing: 0.2em; border: 1px solid var(--text-primary); transition: var(--transition-premium);
    }
    .btn-premium:hover { background: transparent; color: var(--text-primary); transform: translateY(-3px); }

    /* TRUST BAR */
    .trust-bar {
      display: grid; grid-template-columns: repeat(4, 1fr); gap: 40px;
      padding: 100px 0; border-bottom: 1px solid var(--border-light); margin-bottom: 120px;
    }
    .trust-item { text-align: center; }
    .trust-item i { font-size: 1.5rem; color: var(--accent-gold); margin-bottom: 20px; }
    .trust-item h4 { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 12px; }
    .trust-item p { font-size: 0.95rem; color: var(--text-muted); font-weight: 300; }

    /* CATEGORIES */
    .section-title { text-align: center; margin-bottom: 80px; }
    .section-title p { font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.3em; margin-bottom: 15px; }
    .section-title h2 { font-family: 'Playfair Display', serif; font-size: 2.8rem; font-weight: 400; }

    .category-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; margin-bottom: 140px; }
    .category-card { position: relative; height: 500px; overflow: hidden; background: var(--bg-light); }
    .category-card img { width: 100%; height: 100%; object-fit: cover; transition: var(--transition-premium); }
    .category-card:hover img { transform: scale(1.05); }
    .category-label {
      position: absolute; bottom: 0; left: 0; width: 100%; padding: 40px;
      background: linear-gradient(transparent, rgba(0,0,0,0.4)); color: #fff;
      display: flex; flex-direction: column; justify-content: flex-end; height: 50%;
    }
    .category-label h3 { font-size: 1.2rem; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 10px; }
    .category-label span { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0; transform: translateY(10px); transition: var(--transition-premium); }
    .category-card:hover .category-label span { opacity: 1; transform: translateY(0); }

    /* PRODUCTS */
    .products-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 40px; margin-bottom: 100px; }
    .product-card { transition: var(--transition-premium); }
    .product-image { aspect-ratio: 3/4; background: var(--bg-light); overflow: hidden; margin-bottom: 20px; position: relative; }
    .product-image img { width: 100%; height: 100%; object-fit: cover; transition: var(--transition-premium); }
    .product-card:hover .product-image img { transform: scale(1.03); }
    .product-info { text-align: left; padding: 0 5px; }
    .product-title { font-size: 0.95rem; font-weight: 400; margin-bottom: 8px; letter-spacing: 0.02em; }
    .product-price { font-size: 1rem; font-weight: 600; letter-spacing: 0.05em; color: var(--text-primary); }
    .product-card:hover { transform: translateY(-10px); }

    /* FOOTER */
    footer { padding: 100px 0 60px; background: #fff; border-top: 1px solid var(--border-light); }
    .footer-content { display: grid; grid-template-columns: 2fr 1fr 1fr 1.5fr; gap: 80px; margin-bottom: 80px; }
    .footer-logo { font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 700; letter-spacing: 3px; margin-bottom: 30px; }
    .footer-desc { font-size: 0.95rem; color: var(--text-muted); line-height: 1.8; max-width: 320px; }
    .footer-col h4 { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 30px; }
    .footer-col ul li { margin-bottom: 15px; }
    .footer-col ul li a { font-size: 0.9rem; color: var(--text-muted); }
    .footer-col ul li a:hover { color: var(--text-primary); padding-left: 5px; }
    .footer-bottom { padding-top: 50px; border-top: 1px solid var(--border-light); display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-muted); }

    /* REVEAL ANIMATION */
    .reveal { opacity: 0; transform: translateY(40px); transition: var(--transition-premium); }
    .reveal.active { opacity: 1; transform: translateY(0); }

    @media (max-width: 1200px) {
      .container { padding: 0 40px; }
      .hero-text h1 { font-size: 3.5rem; }
      .products-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 768px) {
      .navbar { padding: 20px 30px; }
      .nav-links { display: none; }
      .hero { height: auto; padding: 120px 0 60px; }
      .hero-content { flex-direction: column; text-align: center; }
      .hero-text { padding: 0; margin-bottom: 40px; }
      .hero-image { width: 100%; height: 400px; }
      .trust-bar, .category-grid, .products-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
      .footer-content { grid-template-columns: 1fr 1fr; gap: 40px; }
    }
    @media (max-width: 480px) {
      .category-grid, .products-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar" id="navbar">
    <script>
      window.IS_LOGGED_IN = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
      window.AUTH_TOKEN = '<?php echo getAuthToken(); ?>';
    </script>
    <a href="index.php" class="logo">URBANWEAR</a>
    <div class="nav-links">
      <a href="index.php">Home</a>
      <a href="index.php#drop">Urban Drop</a>
      <a href="men.php">Men</a>
      <a href="women.php">Women</a>
      <a href="kids.php">Kids</a>
      <a href="index.php#trending">Trending</a>
      <a href="cart.php" style="position:relative; margin-left:15px; font-size: 1.1rem; color: var(--text-primary);">
        <i class="fa-solid fa-bag-shopping"></i>
        <span class="cart-count-badge" style="display:none; position: absolute; top: -8px; right: -12px; background: #c5a059; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 10px; font-weight: 700; min-width: 18px; text-align: center; display: flex; align-items: center; justify-content: center;">0</span>
      </a>
      <?php if(isset($_SESSION['user_id'])): ?>
        <div style="border-left: 1px solid var(--border-light); padding-left: 25px; margin-left:10px; display: flex; align-items: center;">
          <?php include 'includes/user-profile-dropdown.php'; ?>
        </div>
      <?php else: ?>
        <a href="login.php" style="border-left: 1px solid var(--border-light); padding-left: 25px; margin-left: 10px;">Login</a>
        <a href="signup.php">Join Now</a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- Hero Section -->
  <main>
    <section class="hero">
      <div class="container hero-content">
        <div class="hero-text">
          <span>ESSENTIALS 2025</span>
          <h1>Modern Urban <br>Fashion</h1>
          <a href="#drop" class="btn-premium">Explore Collection</a>
        </div>
        <div class="hero-image">
          <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=2070&auto=format&fit=crop" alt="Premium Urban Fashion">
        </div>
      </div>
    </section>

    <!-- Why This Brand Section -->
    <div class="container reveal">
      <div class="trust-bar">
        <div class="trust-item">
          <i class="fas fa-gem"></i>
          <h4>Premium Fabric</h4>
          <p>Highest quality materials sourced ethically.</p>
        </div>
        <div class="trust-item">
          <i class="fas fa-map-marker-alt"></i>
          <h4>Made in India</h4>
          <p>Designed and crafted with local pride.</p>
        </div>
        <div class="trust-item">
          <i class="fas fa-sync"></i>
          <h4>Easy Returns</h4>
          <p>Seamless 30-day return policy for peace of mind.</p>
        </div>
        <div class="trust-item">
          <i class="fas fa-shipping-fast"></i>
          <h4>Fast Shipping</h4>
          <p>Express delivery to your doorstep, anywhere.</p>
        </div>
      </div>
    </div>

    <!-- Categories Section -->
    <section class="container reveal">
      <div class="section-title">
        <p>Your Style, Your Way</p>
        <h2>Shop Categories</h2>
      </div>
      <div class="category-grid">
        <a href="men.php" class="category-card">
          <img src="https://hips.hearstapps.com/hmg-prod/images/mhl-mens-cloth-huckberry-749-67c1ded533637.jpg" alt="Men's Wardrobe">
          <div class="category-label">
            <h3>Men</h3>
            <span>Explore More</span>
          </div>
        </a>
        <a href="women.php" class="category-card">
          <img src="https://assets.vogue.com/photos/67db1d379b250aa9279caf73/master/w_2560%2Cc_limit/Holding%2520Collage%2520(2).jpg" alt="Women's Wardrobe">
          <div class="category-label">
            <h3>Women</h3>
            <span>Explore More</span>
          </div>
        </a>
        <a href="kids.php" class="category-card">
          <img src="https://as1.ftcdn.net/jpg/02/36/25/70/1000_F_236257045_SsBI44Y7mDukuEOMYBwFO0zrwL6eOsDg.jpg" alt="Kids' Wardrobe">
          <div class="category-label">
            <h3>Kids</h3>
            <span>Explore More</span>
          </div>
        </a>
      </div>
    </section>

    <!-- Urban Drop Section -->
    <section id="drop" class="container reveal">
      <div class="section-title">
        <p>Fresh Out the Streets</p>
        <h2>Urban Drop</h2>
      </div>

      <?php if ($api_error): ?>
        <div style="text-align: center; padding: 80px; color: var(--text-muted); font-weight: 300;">Unable to load products. Please check back later.</div>
      <?php else: ?>
        <?php
          $dropProducts = array_slice($products, 0, 8);
          $trendProducts = array_slice($products, 8, 4);
          $res2 = $API->get('/api/v1/products/trending?limit=4');
          if (!empty($res2) && isset($res2['success']) && $res2['success'] && is_array($res2['data']) && count($res2['data'])>0) {
            $trendProducts = $res2['data'];
          }
        ?>
        <div class="products-grid">
          <?php foreach ($dropProducts as $p): 
            $img = (is_array($p['images']) && count($p['images'])>0) ? $p['images'][0] : ($p['image'] ?? 'https://via.placeholder.com/800');
            $title = htmlspecialchars($p['title'] ?? $p['name'] ?? 'Untitled');
            $price = isset($p['price']) ? '₹'.number_format($p['price']) : '—';
            $href = 'product.php?id=' . urlencode($p['_id'] ?? $p['id'] ?? '');
          ?>
          <a class="product-card" href="<?php echo $href; ?>">
            <div class="product-image">
              <img src="<?php echo $img;?>" alt="<?php echo $title;?>">
            </div>
            <div class="product-info">
              <div class="product-title"><?php echo $title;?></div>
              <div class="product-price"><?php echo $price;?></div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <!-- Trending Section -->
    <section id="trending" class="container reveal">
      <div class="section-title">
        <p>Customer Favorites</p>
        <h2>Trending Now</h2>
      </div>
      <?php if (!$api_error && !empty($trendProducts)): ?>
        <div class="products-grid">
          <?php foreach ($trendProducts as $p): 
            $img = (is_array($p['images']) && count($p['images'])>0) ? $p['images'][0] : ($p['image'] ?? 'https://via.placeholder.com/800');
            $title = htmlspecialchars($p['title'] ?? $p['name'] ?? 'Untitled');
            $price = isset($p['price']) ? '₹'.number_format($p['price']) : '—';
            $href = 'product.php?id=' . urlencode($p['_id'] ?? $p['id'] ?? '');
          ?>
          <a class="product-card" href="<?php echo $href; ?>">
            <div class="product-image">
              <img src="<?php echo $img;?>" alt="<?php echo $title;?>">
            </div>
            <div class="product-info">
              <div class="product-title"><?php echo $title;?></div>
              <div class="product-price"><?php echo $price;?></div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <!-- Footer -->
    <footer>
      <div class="container">
        <div class="footer-content">
          <div class="footer-col">
            <div class="footer-logo">URBANWEAR</div>
            <p class="footer-desc">Redefining modern urban fashion with premium quality and timeless designs. Based in India, shipping worldwide.</p>
          </div>
          <div class="footer-col">
            <h4>Collections</h4>
            <ul>
              <li><a href="men.php">Men's Wear</a></li>
              <li><a href="women.php">Women's Wear</a></li>
              <li><a href="kids.php">Kids' Wear</a></li>
              <li><a href="#drop">New Arrivals</a></li>
            </ul>
          </div>
          <div class="footer-col">
            <h4>Customer Care</h4>
            <ul>
              <li><a href="#">Track Order</a></li>
              <li><a href="#">Shipping Policy</a></li>
              <li><a href="#">Returns & Exchanges</a></li>
              <li><a href="#">Contact Us</a></li>
            </ul>
          </div>
          <div class="footer-col">
            <h4>Newsletter</h4>
            <p class="footer-desc" style="margin-bottom: 20px;">Join our community for early access and fashion updates.</p>
            <div style="font-size: 0.9rem; color: var(--text-muted);">
              CURRENCY: INR (₹)
            </div>
          </div>
        </div>
        <div class="footer-bottom">
          <div>&copy; 2025 URBANWEAR. All rights reserved.</div>
          <div>Privacy Policy | Terms of Service</div>
        </div>
      </div>
    </footer>

  </main>

  <script>
    // Scroll reveal animation
    const reveals = document.querySelectorAll('.reveal');
    const options = { threshold: 0.15 };
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('active');
        }
      });
    }, options);
    
    reveals.forEach(reveal => observer.observe(reveal));

    // Navbar scroll effect
    window.addEventListener('scroll', () => {
      const nav = document.getElementById('navbar');
      if (window.scrollY > 50) {
        nav.classList.add('scrolled');
      } else {
        nav.classList.remove('scrolled');
      }
    });

    // Lazy load images fade-in
    document.querySelectorAll('img').forEach(img => {
      img.style.opacity = '0';
      img.style.transition = 'opacity 1s ease';
      img.onload = () => img.style.opacity = '1';
      if (img.complete) img.style.opacity = '1';
    });

    // User dropdown toggle
    function toggleUserDropdown(event) {
      event.stopPropagation();
      const menu = document.getElementById('userDropdownMenu');
      if (menu) {
        menu.classList.toggle('active');
      }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
      const dropdown = document.querySelector('.user-avatar-wrapper');
      const menu = document.getElementById('userDropdownMenu');
      if (dropdown && menu && !dropdown.contains(event.target)) {
        menu.classList.remove('active');
      }
    });
  </script>

  <script src="assets/js/cart.js"></script>
</body>
</html>
