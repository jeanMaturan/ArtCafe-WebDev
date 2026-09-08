<?php

/* =====================================
   SHARED ADMIN SIDEBAR

   Set $admin_active before including this
   file to highlight the current page, e.g.:

       $admin_active = "artworks";

   Valid values: dashboard, artists, artworks,
   reviews, messages, subscribers.
===================================== */

$admin_active = $admin_active ?? "";

$admin_nav_items = [
    "dashboard"   => ["href" => "admin_dashboard.php",  "label" => "Dashboard"],
    "artists"     => ["href" => "admin.php",            "label" => "Artists"],
    "artworks"    => ["href" => "admin_artworks.php",   "label" => "Artworks"],
    "reviews"     => ["href" => "admin_reviews.php",    "label" => "Reviews"],
    "messages"    => ["href" => "admin_messages.php",   "label" => "Messages"],
    "subscribers" => ["href" => "admin_subscribers.php","label" => "Subscribers"],
];

?>
<aside class="dash-sidebar">

    <div class="dash-brand">
        <img src="images/logo.png" alt="Maturan's Art Cafe">
        <span>Maturan's<br>Art Cafe</span>
    </div>

    <nav class="dash-nav">

        <?php foreach ($admin_nav_items as $key => $item): ?>

            <a
                href="<?php echo htmlspecialchars($item["href"]); ?>"
                class="dash-nav-link<?php echo $admin_active === $key ? " active" : ""; ?>"
            >
                <?php echo htmlspecialchars($item["label"]); ?>
            </a>

        <?php endforeach; ?>

    </nav>

    <div class="dash-sidebar-footer">
        <a href="index.php">View Site</a>
        <a href="admin_logout.php">Logout</a>
    </div>

</aside>
