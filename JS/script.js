/* ================================
   BESTSELLERS HORIZONTAL SCROLL
================================ */

const productsContainer = document.getElementById("productsContainer");

if (productsContainer) {

    productsContainer.addEventListener("scroll", function () {

        const maxScroll =
            productsContainer.scrollWidth -
            productsContainer.clientWidth;

        const scrollPosition = productsContainer.scrollLeft;

        // When the user scrolls to the right,
        // make the Chocolate Latte step out beside the Blueberry.
        if (scrollPosition > 50) {
            productsContainer.classList.add("scrolled-right");
        } else {
            productsContainer.classList.remove("scrolled-right");
        }

    });
}