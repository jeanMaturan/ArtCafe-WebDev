<?php

/* =====================================
   IMAGE RESOLVER

   Products/events added through the new
   admin pages store their image in
   product_images/ or event_images/.

   Items that existed before the admin
   catalog (seeded from the old hardcoded
   pages) reference a filename that lives
   in the site's shared images/ folder.

   This checks both locations so either
   source displays correctly.
===================================== */

function resolve_catalog_image($filename, $upload_dir) {

    if (empty($filename)) {
        return null;
    }

    if (file_exists($upload_dir . $filename)) {
        return $upload_dir . $filename;
    }

    if (file_exists("images/" . $filename)) {
        return "images/" . $filename;
    }

    return null;
}

?>