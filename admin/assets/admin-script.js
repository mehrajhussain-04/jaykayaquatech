/* ==========================================================================
   JAY KAY AQUA TECH ADMIN — SCRIPT
   ========================================================================== */
(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    initSidebar();
    initAlerts();
    initConfirmModal();
    initImagePreview();
  });

  /* ---------------- Sidebar (mobile) ---------------- */
  function initSidebar() {
    var toggle = document.getElementById("sidebarToggle");
    var sidebar = document.getElementById("adminSidebar");
    var scrim = document.getElementById("sidebarScrim");
    if (!toggle || !sidebar) return;

    function open() {
      sidebar.classList.add("open");
      if (scrim) scrim.classList.add("show");
    }
    function close() {
      sidebar.classList.remove("open");
      if (scrim) scrim.classList.remove("show");
    }
    toggle.addEventListener("click", function () {
      sidebar.classList.contains("open") ? close() : open();
    });
    if (scrim) scrim.addEventListener("click", close);
  }

  /* ---------------- Dismissible alerts ---------------- */
  function initAlerts() {
    document.querySelectorAll(".alert .close-alert").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var alert = btn.closest(".alert");
        if (alert) alert.remove();
      });
    });
    // Auto-dismiss success alerts after 6s
    document.querySelectorAll(".alert-success").forEach(function (alert) {
      setTimeout(function () {
        alert.style.transition = "opacity .4s ease";
        alert.style.opacity = "0";
        setTimeout(function () { alert.remove(); }, 400);
      }, 6000);
    });
  }

  /* ---------------- Confirm-delete modal ----------------
     Any element with data-confirm="Message" and data-action="url"
     opens a confirmation modal instead of a raw browser confirm(),
     then submits a POST form to the given action.
  ------------------------------------------------------- */
  function initConfirmModal() {
    var scrim = document.getElementById("confirmScrim");
    if (!scrim) return;
    var titleEl = document.getElementById("confirmTitle");
    var msgEl = document.getElementById("confirmMessage");
    var formEl = document.getElementById("confirmForm");
    var cancelBtn = document.getElementById("confirmCancel");
    var pendingTrigger = null;

    document.querySelectorAll("[data-confirm]").forEach(function (el) {
      el.addEventListener("click", function (e) {
        e.preventDefault();
        pendingTrigger = el;
        titleEl.textContent = el.getAttribute("data-confirm-title") || "Please confirm";
        msgEl.textContent = el.getAttribute("data-confirm") || "Are you sure?";
        formEl.action = el.getAttribute("data-action") || el.getAttribute("href") || "#";

        // Copy any data-field-* attributes into hidden inputs (e.g. id to delete)
        formEl.querySelectorAll("input[data-dynamic]").forEach(function (i) { i.remove(); });
        Array.prototype.slice.call(el.attributes).forEach(function (attr) {
          if (attr.name.indexOf("data-field-") === 0) {
            var fieldName = attr.name.replace("data-field-", "");
            var input = document.createElement("input");
            input.type = "hidden";
            input.name = fieldName;
            input.value = attr.value;
            input.setAttribute("data-dynamic", "1");
            formEl.appendChild(input);
          }
        });

        scrim.classList.add("show");
      });
    });

    function closeModal() { scrim.classList.remove("show"); pendingTrigger = null; }
    if (cancelBtn) cancelBtn.addEventListener("click", closeModal);
    scrim.addEventListener("click", function (e) { if (e.target === scrim) closeModal(); });
    document.addEventListener("keydown", function (e) { if (e.key === "Escape") closeModal(); });
  }

  /* ---------------- Image upload preview ---------------- */
  function initImagePreview() {
    document.querySelectorAll("input[type=file][data-preview]").forEach(function (input) {
      var targetId = input.getAttribute("data-preview");
      var target = document.getElementById(targetId);
      if (!target) return;
      input.addEventListener("change", function () {
        var file = input.files && input.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
          target.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
        };
        reader.readAsDataURL(file);
      });
    });
  }
})();
