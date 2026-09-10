<aside class="dash-sidebar">

    <div class="dash-brand">
        <img src="images/logo.png" alt="Maturan's Art Cafe">
        <span>Maturan's<br>Art Cafe</span>
    </div>

    <nav class="dash-nav">
        <a href="admin_dashboard.php" class="dash-nav-link<?php echo ($active_page ?? '') === 'dashboard' ? ' active' : ''; ?>">Dashboard</a>
        <a href="admin_reservations.php" class="dash-nav-link<?php echo ($active_page ?? '') === 'reservations' ? ' active' : ''; ?>">Reservations</a>
        <a href="admin.php" class="dash-nav-link<?php echo ($active_page ?? '') === 'artists' ? ' active' : ''; ?>">Artists</a>
        <a href="admin_artworks.php" class="dash-nav-link<?php echo ($active_page ?? '') === 'artworks' ? ' active' : ''; ?>">Artworks</a>
        <a href="admin_products.php" class="dash-nav-link<?php echo ($active_page ?? '') === 'products' ? ' active' : ''; ?>">Products</a>
        <a href="admin_events.php" class="dash-nav-link<?php echo ($active_page ?? '') === 'events' ? ' active' : ''; ?>">Events</a>
        <a href="admin_reviews.php" class="dash-nav-link<?php echo ($active_page ?? '') === 'reviews' ? ' active' : ''; ?>">Reviews</a>
        <a href="admin_messages.php" class="dash-nav-link<?php echo ($active_page ?? '') === 'messages' ? ' active' : ''; ?>">Messages</a>
        <a href="admin_subscribers.php" class="dash-nav-link<?php echo ($active_page ?? '') === 'subscribers' ? ' active' : ''; ?>">Subscribers</a>
    </nav>

    <div class="dash-sidebar-footer">
        <a href="index.php">View Site</a>
        <a href="admin_logout.php">Logout</a>
    </div>

</aside>