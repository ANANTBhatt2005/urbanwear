<?php 
require_once __DIR__ . '/api-helper.php';
// Fetch latest products for Home (12 items, split into Drop and Trending)
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
  <title>UrbanWear - Redefine Your Streets</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root{
      --white:#ffffff;
      --bg:#ffffff;
      --text:#111;
      --muted:#6b6b6b;
      --accent:#000;
      --gap:28px;
      --card-radius:12px;
      --card-shadow: 0 6px 24px rgba(0,0,0,0.06);
    }
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);line-height:1.5}

    /* NAV */
    .navbar{position:fixed;top:0;width:100%;background:var(--white);border-bottom:1px solid #f0f0f0;padding:18px 36px;display:flex;align-items:center;justify-content:space-between;z-index:1000}
    .logo{font-family:'Playfair Display',serif;font-size:1.4rem;font-weight:700;color:var(--accent);text-decoration:none}
    .nav-links{display:flex;gap:18px;align-items:center}
    .nav-links a{color:var(--text);text-decoration:none;font-weight:500;font-size:0.98rem}

    main{padding-top:84px}

    /* HERO */
    .hero{display:flex;align-items:center;justify-content:center;text-align:center;padding:96px 20px;background:var(--white)}
    .hero h1{font-family:'Playfair Display',serif;font-size:3.2rem;margin-bottom:12px;letter-spacing:-1px}
    .hero p{color:var(--muted);font-size:1.05rem;margin-bottom:18px}
    .hero .cta{display:inline-block;padding:12px 22px;border-radius:8px;background:var(--accent);color:var(--white);text-decoration:none;font-weight:600}

    /* CATEGORIES */
    .categories{max-width:1200px;margin:36px auto;padding:0 20px}
    .cat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:24px}
    .cat-card{position:relative;border-radius:12px;overflow:hidden;border:1px solid #f2f2f2}
    .cat-card img{width:100%;height:280px;object-fit:cover;display:block}
    .cat-card .meta{padding:16px}
    .cat-card h3{margin:0;font-size:1.2rem}
    .cat-card p{color:var(--muted);font-size:0.95rem;margin-top:6px}

    /* DROP / TRENDING - simple product grid */
    .section{max-width:1200px;margin:0 auto;padding:48px 20px}
    .section h2{font-family:'Playfair Display',serif;font-size:1.6rem;margin-bottom:8px}
    .section p{color:var(--muted);margin-bottom:18px}

    .products-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px}
    .product{background:var(--white);border-radius:var(--card-radius);overflow:hidden;border:1px solid #f2f2f2;box-shadow:var(--card-shadow);text-align:left}
    .product .media{height:320px;overflow:hidden}
    .product img{width:100%;height:100%;object-fit:cover;display:block}
    .product .info{padding:12px 14px}
    .product .title{font-weight:600;font-size:1rem;color:var(--text);margin-bottom:6px}
    .product .price{font-weight:700;color:var(--accent);font-size:1rem}

    /* Footer */
    footer{padding:40px 20px;text-align:center;color:var(--muted);font-size:0.95rem}

    @media (max-width:800px){
      .hero h1{font-size:2rem}
      .product .media{height:220px}
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar">
    <a href="index.php" class="logo">URBANWEAR</a>
    <div class="nav-links">
      <a href="index.php">Home</a>
      <a href="index.php#drop">Urban Drop</a>
      <a href="men.php">Men</a>
      <a href="women.php">Women</a>
      <a href="kids.php">Kids</a>
      <a href="index.php#trending">Trending</a>
     <a href="/clothing_project/login.php">Login</a>
        <a href="/clothing_project/signup.php">Sign Up</a>
    </div>
  </nav>

  <!-- Hero -->
  <main>
    <section class="hero">
      <div class="hero-content">
        <h1>URBANWEAR</h1>
        <p>Minimal. Modern. Urban fashion.</p>
        <a href="index.php#drop" class="cta">Shop the Drop</a>
      </div>
    </section>

    <!-- DROP (dynamic) -->
    <section id="drop" class="section">
      <h2>Urban Drop</h2>
      <p>Latest arrivals ΓÇö handpicked for the city.</p>

      <?php if ($api_error): ?>
        <div style="max-width:1200px;margin:40px auto;padding:0 20px;color:#777">Unable to load products. Please try again later.</div>
      <?php else: ?>
        <?php
          $dropProducts = array_slice($products, 0, 8);
          // Try to fetch trending products (real backend analytics) - fallback to slice
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
            $price = isset($p['price']) ? 'Γé╣'.number_format($p['price']) : 'ΓÇö';
            $href = 'product.php?id=' . urlencode($p['_id'] ?? $p['id'] ?? '');
          ?>
          <a class="product" href="<?php echo $href; ?>">
            <div class="media"><img src="<?php echo $img;?>" alt="<?php echo $title;?>"></div>
            <div class="info"><div class="title"><?php echo $title;?></div><div class="price"><?php echo $price;?></div></div>
          </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <!-- Trending (dynamic, no duplicates) -->
    <section id="trending" class="section">
      <h2>Trending</h2>
      <p>Customer favorites right now.</p>
      <?php if (!$api_error && !empty($trendProducts)): ?>
        <div class="products-grid">
          <?php foreach ($trendProducts as $p): 
            $img = (is_array($p['images']) && count($p['images'])>0) ? $p['images'][0] : ($p['image'] ?? 'https://via.placeholder.com/800');
            $title = htmlspecialchars($p['title'] ?? $p['name'] ?? 'Untitled');
            $price = isset($p['price']) ? 'Γé╣'.number_format($p['price']) : 'ΓÇö';
            $href = 'product.php?id=' . urlencode($p['_id'] ?? $p['id'] ?? '');
          ?>
          <a class="product" href="<?php echo $href; ?>">
            <div class="media"><img src="<?php echo $img;?>" alt="<?php echo $title;?>"></div>
            <div class="info"><div class="title"><?php echo $title;?></div><div class="price"><?php echo $price;?></div></div>
          </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <!-- Categories -->
    <section class="categories">
      <div class="cat-grid">
        <a href="men.php" class="cat-card">
          <img src="https://hips.hearstapps.com/hmg-prod/images/mhl-mens-cloth-huckberry-749-67c1ded533637.jpg" alt="Men">
          <div class="meta"><h3>Men</h3><p>Premium streetwear for the modern gentleman</p></div>
        </a>
        <a href="women.php" class="cat-card">
          <img src="https://assets.vogue.com/photos/67db1d379b250aa9279caf73/master/w_2560%2Cc_limit/Holding%2520Collage%2520(2).jpg" alt="Women">
          <div class="meta"><h3>Women</h3><p>Elegant fashion meets urban edge</p></div>
        </a>
        <a href="kids.php" class="cat-card">
          <img src="https://as1.ftcdn.net/jpg/02/36/25/70/1000_F_236257045_SsBI44Y7mDukuEOMYBwFO0zrwL6eOsDg.jpg" alt="Kids">
          <div class="meta"><h3>Kids</h3><p>Fun, comfortable clothing for little urban explorers</p></div>
        </a>
      </div>
    </section>


    <!-- Minimal Footer -->
    <footer>
      ┬⌐ 2025 UrbanWear ΓÇö Minimal, Modern, Urban Fashion
    </footer>

  </main>

  <script>
    // Minimal JS: fade images in when loaded and simple reveal on scroll
    document.querySelectorAll('.product img').forEach(img => {
      img.style.opacity = 0;
      img.addEventListener('load', () => img.style.opacity = 1);
      // in case image is cached
      if (img.complete) img.style.opacity = 1;
    });

    const io = new IntersectionObserver((entries)=>{
      entries.forEach(e=>{ if(e.isIntersecting) e.target.classList.add('reveal'); });
    }, {threshold:.08});

    document.querySelectorAll('.product, .cat-card, .hero-content').forEach(el=>io.observe(el));
  </script>

</body>
</html>
