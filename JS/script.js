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

/* ================================
   REVIEWS HORIZONTAL SCROLL
================================ */

const reviewsContainer =
    document.querySelector(".reviews-container");

const reviewGroups =
    document.querySelectorAll(".review-group");

const reviewDots =
    document.querySelectorAll(".review-dot");

if (
    reviewsContainer &&
    reviewGroups.length === 3 &&
    reviewDots.length === 3
) {

    function updateReviewDot() {

        const groupWidth =
            reviewsContainer.clientWidth;

        const currentGroup =
            Math.round(
                reviewsContainer.scrollLeft / groupWidth
            );

        reviewDots.forEach(function (dot, index) {

            dot.classList.toggle(
                "active",
                index === currentGroup
            );

        });
    }


    /* Change dot when scrolling */

    reviewsContainer.addEventListener(
        "scroll",
        updateReviewDot
    );


    /* Click dots */

    reviewDots.forEach(function (dot, index) {

        dot.addEventListener("click", function () {

            reviewsContainer.scrollTo({
                left:
                    index * reviewsContainer.clientWidth,
                behavior: "smooth"
            });

        });

    });


    updateReviewDot();
}

/* ================================
   RETURN TO FOOTER AFTER SUBSCRIBE
================================ */

const subscribeStatus =
    new URLSearchParams(window.location.search).get("subscribe");

if (subscribeStatus) {

    const footer =
        document.getElementById("contact");

    if (footer) {

        window.addEventListener("load", function () {

            setTimeout(function () {

                footer.scrollIntoView({
                    behavior: "auto",
                    block: "start"
                });

            }, 100);

        });

    }

}

/* ================================
   NEWSLETTER SUBSCRIPTION
================================ */

const subscribeForm =
    document.getElementById("subscribeForm");

const subscribeEmail =
    document.getElementById("subscribeEmail");

const subscribeMessage =
    document.getElementById("subscribeMessage");


if (subscribeForm) {

    subscribeForm.addEventListener("submit", function (event) {

        event.preventDefault();


        const email =
            subscribeEmail.value.trim();


        const formData =
            new FormData();

        formData.append("email", email);


        fetch("subscribe.php", {

            method: "POST",

            body: formData

        })

        .then(function (response) {

            return response.json();

        })

        .then(function (data) {

            subscribeMessage.textContent =
                data.message;

            subscribeMessage.style.display =
                "block";


            if (data.status === "success") {

                subscribeMessage.className =
                    "subscribe-message success";

                subscribeEmail.value = "";

            }

            else if (data.status === "exists") {

                subscribeMessage.className =
                    "subscribe-message exists";

            }

            else {

                subscribeMessage.className =
                    "subscribe-message error";

            }

        })

        .catch(function () {

            subscribeMessage.textContent =
                "Something went wrong. Please try again.";

            subscribeMessage.className =
                "subscribe-message error";

            subscribeMessage.style.display =
                "block";

        });

    });

}

const menuToggle = document.getElementById("menuToggle");
const navbar = document.querySelector(".navbar");

if (menuToggle && navbar) {
    menuToggle.addEventListener("click", function () {
        navbar.classList.toggle("show");
    });
}