<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SV Shop - Mua sắm online</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; background: #f5f5f5; }

        .header { background: #fff; border-bottom: 2px solid #e74c3c; }
        .header-top { background: #e74c3c; color: #fff; text-align: center; padding: 5px; font-size: 12px; }
        .header-main { max-width: 1100px; margin: 0 auto; padding: 10px 20px; display: flex; align-items: center; justify-content: space-between; }
        .logo { font-size: 26px; font-weight: bold; color: #e74c3c; text-decoration: none; }
        .logo span { color: #333; }
        .nav { display: flex; gap: 20px; }
        .nav a { text-decoration: none; color: #333; font-size: 13px; }
        .nav a:hover { color: #e74c3c; }
        .header-actions { display: flex; align-items: center; gap: 15px; }
        .search-box { display: flex; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; }
        .search-box input { padding: 6px 10px; border: none; outline: none; width: 200px; font-size: 13px; }
        .search-box button { background: #e74c3c; color: #fff; border: none; padding: 6px 14px; cursor: pointer; font-size: 13px; }
        .cart-icon { font-size: 22px; cursor: pointer; position: relative; }
        .cart-badge { position: absolute; top: -6px; right: -8px; background: #e74c3c; color: #fff; border-radius: 50%; width: 16px; height: 16px; font-size: 10px; display: flex; align-items: center; justify-content: center; }

        .banner { background: linear-gradient(135deg, #f39c12, #e74c3c); max-width: 1100px; margin: 15px auto; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: space-between; padding: 30px 50px; min-height: 160px; }
        .banner-text h2 { font-size: 42px; color: #fff; font-weight: 900; text-shadow: 2px 2px 4px rgba(0,0,0,0.2); }
        .banner-text p { color: #fff; font-size: 16px; margin: 8px 0; }
        .banner-btn { background: #fff; color: #e74c3c; border: none; padding: 10px 28px; border-radius: 25px; font-size: 14px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .banner-img { font-size: 80px; }

        .search-result { max-width: 1100px; margin: 0 auto 10px; padding: 0 20px; }
        .search-alert { background: #fff3cd; border-left: 4px solid #f39c12; padding: 10px 15px; border-radius: 4px; font-size: 13px; }

        .section { max-width: 1100px; margin: 0 auto 25px; padding: 0 20px; }
        .section-title { font-size: 18px; font-weight: bold; color: #333; margin-bottom: 15px; padding-bottom: 8px; border-bottom: 2px solid #e74c3c; display: flex; align-items: center; gap: 8px; }
        .section-title::before { content: ''; display: inline-block; width: 4px; height: 18px; background: #e74c3c; border-radius: 2px; }

        .products { display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; }
        .product-card { background: #fff; border-radius: 6px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.08); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; }
        .product-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .product-img { width: 100%; height: 160px; object-fit: cover; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 50px; }
        .product-info { padding: 10px; }
        .product-name { font-size: 13px; color: #333; margin-bottom: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .product-price { color: #e74c3c; font-weight: bold; font-size: 14px; }
        .product-old-price { color: #999; text-decoration: line-through; font-size: 12px; margin-left: 5px; }
        .product-badge { display: inline-block; background: #e74c3c; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 3px; margin-bottom: 5px; }

        .news-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        .news-card { background: #fff; border-radius: 6px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .news-img { width: 100%; height: 140px; object-fit: cover; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 40px; }
        .news-info { padding: 12px; }
        .news-title { font-size: 13px; font-weight: bold; color: #333; margin-bottom: 6px; line-height: 1.4; }
        .news-desc { font-size: 12px; color: #666; line-height: 1.5; }
        .news-date { font-size: 11px; color: #999; margin-top: 8px; }
        .news-btn { display: inline-block; margin-top: 8px; padding: 4px 12px; background: #e74c3c; color: #fff; border-radius: 3px; font-size: 12px; text-decoration: none; }

        .brands { max-width: 1100px; margin: 0 auto 25px; padding: 15px 20px; background: #fff; border-radius: 6px; display: flex; align-items: center; justify-content: space-around; }
        .brand { font-size: 18px; font-weight: bold; color: #999; letter-spacing: 2px; }

        .footer { background: #2c3e50; color: #ccc; padding: 30px 20px 15px; margin-top: 20px; }
        .footer-content { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 20px; }
        .footer-col h4 { color: #fff; margin-bottom: 12px; font-size: 14px; }
        .footer-col p, .footer-col a { font-size: 12px; color: #aaa; text-decoration: none; display: block; margin-bottom: 5px; line-height: 1.6; }
        .footer-bottom { text-align: center; border-top: 1px solid #3d5166; padding-top: 15px; font-size: 12px; color: #666; }

        .file-viewer { max-width: 1100px; margin: 0 auto 20px; padding: 0 20px; }
        .file-content { background: #1e1e1e; color: #00ff00; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 12px; overflow-x: auto; }

        .loading { text-align: center; padding: 20px; color: #999; font-size: 13px; }
    </style>
</head>
<body>

<div class="header-top">
    🎉 Miễn phí vận chuyển cho đơn hàng trên 300.000đ | Hotline: 1900-xxxx
</div>

<div class="header">
    <div class="header-main">
        <a href="?" class="logo">SV<span>Shop</span></a>
        <nav class="nav">
            <a href="?">Trang chủ</a>
            <a href="?cat=thoi-trang">Thời trang</a>
            <a href="?cat=dien-tu">Điện tử</a>
            <a href="?cat=tui-xach">Túi xách</a>
            <a href="?cat=phu-kien">Phụ kiện</a>
            <a href="?cat=sale">🔥 SALE</a>
        </nav>
        <div class="header-actions">
            <!-- SEARCH - XSS VULNERABILITY -->
            <form method="GET" class="search-box">
                <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                <button type="submit">🔍</button>
            </form>
            <div class="cart-icon">🛒<span class="cart-badge">0</span></div>
        </div>
    </div>
</div>

<?php

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $keyword = $_GET['search'];
    echo "<div class='search-result'>";
    echo "<div class='search-alert'>";
    echo "🔍 Kết quả tìm kiếm cho: <b style='color:#e74c3c'>" . $keyword . "</b>";
    echo " &nbsp;|&nbsp; Tìm thấy <b>0</b> sản phẩm";
    echo "</div>";
    echo "</div>";
}
?>

<div style="max-width:1100px;margin:0 auto;padding:0 20px;">
<div class="banner">
    <div class="banner-text">
        <h2>GIẢM 70%</h2>
        <p>Cùng nhiều quà tặng hấp dẫn</p>
        <p style="font-size:13px;opacity:0.9;">Áp dụng cho tất cả sản phẩm thời trang</p>
        <button class="banner-btn">MUA NGAY</button>
    </div>
    <div class="banner-img">🛍️</div>
</div>
</div>

<div class="section">
    <div class="section-title">Sản phẩm mới</div>
    <div class="products">
        <div class="product-card">
            <div class="product-img">👜</div>
            <div class="product-info">
                <span class="product-badge">MỚI</span>
                <div class="product-name">Túi xách nữ cao cấp</div>
                <div><span class="product-price">250.000đ</span><span class="product-old-price">399.000đ</span></div>
            </div>
        </div>
        <div class="product-card">
            <div class="product-img">👟</div>
            <div class="product-info">
                <span class="product-badge">MỚI</span>
                <div class="product-name">Giày thể thao nam</div>
                <div><span class="product-price">380.000đ</span><span class="product-old-price">520.000đ</span></div>
            </div>
        </div>
        <div class="product-card">
            <div class="product-img">⌚</div>
            <div class="product-info">
                <span class="product-badge">HOT</span>
                <div class="product-name">Đồng hồ thời trang</div>
                <div><span class="product-price">450.000đ</span><span class="product-old-price">700.000đ</span></div>
            </div>
        </div>
        <div class="product-card">
            <div class="product-img">🕶️</div>
            <div class="product-info">
                <div class="product-name">Kính mát UV400</div>
                <div><span class="product-price">120.000đ</span><span class="product-old-price">199.000đ</span></div>
            </div>
        </div>
        <div class="product-card">
            <div class="product-img">🎒</div>
            <div class="product-info">
                <span class="product-badge">MỚI</span>
                <div class="product-name">Balo laptop 15.6"</div>
                <div><span class="product-price">280.000đ</span><span class="product-old-price">420.000đ</span></div>
            </div>
        </div>
    </div>
</div>

<div class="section">
    <div class="section-title">Sản phẩm nổi bật</div>
    <div class="products">
        <div class="product-card">
            <div class="product-img">👗</div>
            <div class="product-info">
                <span class="product-badge">-40%</span>
                <div class="product-name">Váy hoa nhí dễ thương</div>
                <div><span class="product-price">180.000đ</span><span class="product-old-price">299.000đ</span></div>
            </div>
        </div>
        <div class="product-card">
            <div class="product-img">💼</div>
            <div class="product-info">
                <div class="product-name">Cặp da công sở</div>
                <div><span class="product-price">320.000đ</span><span class="product-old-price">500.000đ</span></div>
            </div>
        </div>
        <div class="product-card">
            <div class="product-img">🌸</div>
            <div class="product-info">
                <span class="product-badge">HOT</span>
                <div class="product-name">Nước hoa mini 30ml</div>
                <div><span class="product-price">95.000đ</span><span class="product-old-price">150.000đ</span></div>
            </div>
        </div>
        <div class="product-card">
            <div class="product-img">🧣</div>
            <div class="product-info">
                <div class="product-name">Khăn len cao cấp</div>
                <div><span class="product-price">75.000đ</span><span class="product-old-price">120.000đ</span></div>
            </div>
        </div>
        <div class="product-card">
            <div class="product-img">💍</div>
            <div class="product-info">
                <span class="product-badge">-30%</span>
                <div class="product-name">Nhẫn bạc thời trang</div>
                <div><span class="product-price">85.000đ</span><span class="product-old-price">120.000đ</span></div>
            </div>
        </div>
        <div class="product-card">
            <div class="product-img">👒</div>
            <div class="product-info">
                <div class="product-name">Mũ rộng vành dạo phố</div>
                <div><span class="product-price">65.000đ</span><span class="product-old-price">99.000đ</span></div>
            </div>
        </div>
        <div class="product-card">
            <div class="product-img">🎀</div>
            <div class="product-info">
                <span class="product-badge">MỚI</span>
                <div class="product-name">Bộ phụ kiện tóc</div>
                <div><span class="product-price">45.000đ</span><span class="product-old-price">75.000đ</span></div>
            </div>
        </div>
        <div class="product-card">
            <div class="product-img">🧴</div>
            <div class="product-info">
                <div class="product-name">Kem dưỡng da ban đêm</div>
                <div><span class="product-price">129.000đ</span><span class="product-old-price">199.000đ</span></div>
            </div>
        </div>
        <div class="product-card">
            <div class="product-img">🌂</div>
            <div class="product-info">
                <span class="product-badge">-50%</span>
                <div class="product-name">Ô che mưa nắng 2in1</div>
                <div><span class="product-price">55.000đ</span><span class="product-old-price">110.000đ</span></div>
            </div>
        </div>
        <div class="product-card">
            <div class="product-img">🏃</div>
            <div class="product-info">
                <div class="product-name">Quần short thể thao</div>
                <div><span class="product-price">115.000đ</span><span class="product-old-price">180.000đ</span></div>
            </div>
        </div>
    </div>
</div>

<div class="section">
    <div class="section-title">Tin tức mới nhất</div>
    <div class="news-grid">
        <div class="news-card">
            <div class="news-img">👔</div>
            <div class="news-info">
                <div class="news-title">Top 10 xu hướng thời trang 2026 không thể bỏ qua</div>
                <div class="news-desc">Cập nhật những xu hướng thời trang hot nhất năm 2026 từ các tuần lễ thời trang thế giới...</div>
                <div class="news-date">📅 28/04/2026</div>
                <a href="?page=news1.txt" class="news-btn">Xem thêm</a>
            </div>
        </div>
        <div class="news-card">
            <div class="news-img">🛒</div>
            <div class="news-info">
                <div class="news-title">Bí quyết mua sắm online tiết kiệm dành cho sinh viên</div>
                <div class="news-desc">Những mẹo hay giúp bạn tiết kiệm tối đa khi mua sắm online mà vẫn có được sản phẩm chất lượng...</div>
                <div class="news-date">📅 25/04/2026</div>
                <a href="?page=news2.txt" class="news-btn">Xem thêm</a>
            </div>
        </div>
        <div class="news-card">
            <div class="news-img">🎁</div>
            <div class="news-info">
                <div class="news-title">SV Shop khai trương chi nhánh mới tại TP.HCM</div>
                <div class="news-desc">SV Shop chính thức khai trương thêm chi nhánh thứ 5 tại quận 1, TP.HCM với nhiều ưu đãi hấp dẫn...</div>
                <div class="news-date">📅 20/04/2026</div>
                <a href="?page=news3.txt" class="news-btn">Xem thêm</a>
            </div>
        </div>
    </div>
</div>

<?php

if (isset($_GET['page']) && !empty($_GET['page'])) {
    $file = $_GET['page'];
    echo "<div class='file-viewer'>";
    echo "<div class='file-content'>";
    echo "<div style='color:#888;margin-bottom:8px;'>// [DEBUG] Loading file: " . $file . "</div>";
    @include($file);
    echo "</div>";
    echo "</div>";
}
?>

<div class="brands">
    <span class="brand">PUMA</span>
    <span class="brand">PRADA</span>
    <span class="brand">BOSS</span>
    <span class="brand">CALVIN KLEIN</span>
    <span class="brand">GUESS</span>
    <span class="brand">NIKE</span>
</div>

<div class="footer">
    <div class="footer-content">
        <div class="footer-col">
            <h4>🛍️ SV Shop</h4>
            <p>Website mua sắm online dành cho sinh viên với giá cả phải chăng và chất lượng đảm bảo.</p>
            <p>📞 Hotline: 1900-xxxx</p>
            <p>📧 Email: svshop@email.com</p>
        </div>
        <div class="footer-col">
            <h4>Danh mục</h4>
            <a href="#">Thời trang nữ</a>
            <a href="#">Thời trang nam</a>
            <a href="#">Túi xách</a>
            <a href="#">Phụ kiện</a>
            <a href="#">Giày dép</a>
        </div>
        <div class="footer-col">
            <h4>Hỗ trợ</h4>
            <a href="#">Chính sách đổi trả</a>
            <a href="#">Hướng dẫn mua hàng</a>
            <a href="#">Tra cứu đơn hàng</a>
            <a href="#">Câu hỏi thường gặp</a>
        </div>
        <div class="footer-col">
            <h4>Theo dõi chúng tôi</h4>
            <a href="#">📘 Facebook</a>
            <a href="#">📸 Instagram</a>
            <a href="#">🎵 TikTok</a>
            <a href="#">▶️ YouTube</a>
        </div>
    </div>
    <div class="footer-bottom">
        © 2026 SV Shop - Đồ án môn Bảo mật Web | Nhóm sinh viên CNTT
    </div>
</div>

</body>
</html>