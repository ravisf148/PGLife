window.addEventListener("load", function () {
    var signup_form = document.getElementById("signup-form");
    if (signup_form) {
        signup_form.addEventListener("submit", function (event) {
            event.preventDefault();

            var XHR = new XMLHttpRequest();
            var form_data = new FormData(signup_form);

            var loading = document.getElementById("loading");
            if (loading) {
                loading.style.display = "block";
            }

            XHR.addEventListener("load", function (event) {
                if (loading) {
                    loading.style.display = "none";
                }
                var response = JSON.parse(event.target.responseText);
                if (response.success) {
                    alert(response.message);
                    window.location.reload();
                } else {
                    alert(response.message);
                }
            });

            XHR.addEventListener("error", function (event) {
                if (loading) {
                    loading.style.display = "none";
                }
                alert("Something went wrong!");
            });

            XHR.open("POST", "api/signup_submit.php");
            XHR.send(form_data);
        });
    }

    var login_form = document.getElementById("login-form");
    if (login_form) {
        login_form.addEventListener("submit", function (event) {
            event.preventDefault();

            var XHR = new XMLHttpRequest();
            var form_data = new FormData(login_form);

            var loading = document.getElementById("loading");
            if (loading) {
                loading.style.display = "block";
            }

            XHR.addEventListener("load", function (event) {
                if (loading) {
                    loading.style.display = "none";
                }
                var response = JSON.parse(event.target.responseText);
                if (response.success) {
                    window.location.reload();
                } else {
                    alert(response.message);
                }
            });

            XHR.addEventListener("error", function (event) {
                if (loading) {
                    loading.style.display = "none";
                }
                alert("Something went wrong!");
            });

            XHR.open("POST", "api/login_submit.php");
            XHR.send(form_data);
        });
    }

    // Toggle interested click handler
    document.addEventListener("click", function (event) {
        var heartIcon = event.target.closest(".is-interested-image");
        if (!heartIcon) return;

        var property_id = heartIcon.getAttribute("property_id");
        if (!property_id) return;

        // Prevent rapid clicks from sending multiple requests concurrently
        if (heartIcon.getAttribute("data-loading") === "true") {
            return;
        }
        heartIcon.setAttribute("data-loading", "true");

        var XHR = new XMLHttpRequest();
        XHR.addEventListener("load", function (event) {
            heartIcon.removeAttribute("data-loading");
            var response = {};
            try {
                response = JSON.parse(event.target.responseText);
            } catch (e) {
                return;
            }

            if (response.is_logged_in === false) {
                if (typeof $ !== "undefined") {
                    $("#login-modal").modal("show");
                }
                return;
            }

            if (response.success) {
                var card = heartIcon.closest(".property-card");
                var userCountEl = card ? card.querySelector(".interested-user-count") : document.querySelector(".interested-user-count");

                if (response.is_interested) {
                    heartIcon.classList.remove("far");
                    heartIcon.classList.add("fas");
                    if (userCountEl) {
                        var currentCount = parseInt(userCountEl.textContent) || 0;
                        userCountEl.textContent = currentCount + 1;
                    }
                } else {
                    heartIcon.classList.remove("fas");
                    heartIcon.classList.add("far");
                    if (userCountEl) {
                        var currentCount = parseInt(userCountEl.textContent) || 0;
                        userCountEl.textContent = Math.max(0, currentCount - 1);
                    }
                    if (document.querySelector(".my-interested-properties") && card) {
                        card.style.display = "none";
                    }
                }
            } else if (response.message) {
                alert(response.message);
            }
        });

        XHR.addEventListener("error", function () {
            heartIcon.removeAttribute("data-loading");
        });

        XHR.open("GET", "api/toggle_interested.php?property_id=" + encodeURIComponent(property_id));
        XHR.send();
    });
});
