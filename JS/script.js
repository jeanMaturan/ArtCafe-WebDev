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
/* ================================
   MESSENGER-STYLE MESSAGE THREADS
   (used by my_messages.php and
   admin_messages.php)
================================ */

const msgrApp = document.querySelector(".msgr-app");

if (msgrApp) {

    const mode = msgrApp.dataset.mode; // "admin" or "user"

    const listItems = msgrApp.querySelectorAll(".msgr-list-item");
    const panels = msgrApp.querySelectorAll(".msgr-panel");
    const placeholder = msgrApp.querySelector(".msgr-placeholder");

    const storageKey =
        mode === "admin"
            ? "artcafe_open_thread_admin"
            : "artcafe_open_thread_user";

    const seenKey = "artcafe_msgr_seen_user";


    function openThread(id, rememberChoice) {

        listItems.forEach(function (item) {
            item.classList.toggle(
                "active",
                item.dataset.messageId === String(id)
            );
        });

        panels.forEach(function (panel) {
            panel.classList.toggle(
                "active",
                panel.dataset.messageId === String(id)
            );
        });

        if (placeholder) {
            placeholder.classList.remove("active");
        }

        msgrApp.classList.add("chat-open");

        if (rememberChoice !== false) {
            try {
                sessionStorage.setItem(storageKey, id);
            } catch (e) {}
        }


        const openedItem = msgrApp.querySelector(
            '.msgr-list-item[data-message-id="' + id + '"]'
        );

        if (!openedItem) {
            return;
        }


        /* Admin: opening an unread conversation marks it read,
           messenger-style -- quietly, in the background. */

        if (mode === "admin" && openedItem.classList.contains("unread")) {

            openedItem.classList.remove("unread");

            const badge = document.querySelector(".msgr-unread-badge");

            if (badge) {

                const remaining = Math.max(
                    0,
                    parseInt(badge.dataset.count || "0", 10) - 1
                );

                badge.dataset.count = remaining;
                badge.textContent = remaining + " unread";

                if (remaining === 0) {
                    badge.style.display = "none";
                }
            }

            const formData = new FormData();
            formData.append("action", "read");
            formData.append("message_id", id);

            fetch("admin_messages.php", {
                method: "POST",
                headers: { "X-Requested-With": "XMLHttpRequest" },
                body: formData
            }).catch(function () {});
        }


        /* Customer side: remember locally that this thread's
           latest activity has been seen. */

        if (mode === "user") {

            let seen = {};

            try {
                seen = JSON.parse(localStorage.getItem(seenKey)) || {};
            } catch (e) {}

            seen[id] = openedItem.dataset.lastActivity;

            try {
                localStorage.setItem(seenKey, JSON.stringify(seen));
            } catch (e) {}

            openedItem.classList.remove("unread");
        }
    }


    function closeThread() {

        msgrApp.classList.remove("chat-open");

        panels.forEach(function (panel) {
            panel.classList.remove("active");
        });

        listItems.forEach(function (item) {
            item.classList.remove("active");
        });

        if (placeholder) {
            placeholder.classList.add("active");
        }

        try {
            sessionStorage.removeItem(storageKey);
        } catch (e) {}
    }


    listItems.forEach(function (item) {

        item.addEventListener("click", function () {
            openThread(item.dataset.messageId, true);
        });
    });

    msgrApp.querySelectorAll(".msgr-back-btn").forEach(function (btn) {
        btn.addEventListener("click", closeThread);
    });


    /* Customer side: flag threads with activity the visitor
       hasn't opened yet (tracked locally in this browser). */

    if (mode === "user") {

        let seen = {};

        try {
            seen = JSON.parse(localStorage.getItem(seenKey)) || {};
        } catch (e) {}

        listItems.forEach(function (item) {

            const id = item.dataset.messageId;
            const lastActivity = item.dataset.lastActivity;

            if (seen[id] !== lastActivity) {
                item.classList.add("unread");
            }
        });
    }


    /* Reopen whichever conversation was open before a reply
       was sent (page reloads after every reply). */

    let savedId = null;

    try {
        savedId = sessionStorage.getItem(storageKey);
    } catch (e) {}

    if (
        savedId &&
        msgrApp.querySelector('.msgr-panel[data-message-id="' + savedId + '"]')
    ) {
        openThread(savedId, false);
    }
}