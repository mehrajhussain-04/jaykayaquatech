/* ==========================================================================
   JAY KAY AQUA TECH — MAIN SCRIPT
   Sections:
   1. Shared header & footer markup (kept in one place so every page matches)
   2. Header behaviour (sticky shadow, dropdowns, mobile drawer)
   3. Scroll reveal animations
   4. Counter animation
   5. Product filter bar
   6. Tabs (product detail page)
   7. FAQ accordion
   8. Form validation (contact + enquiry)
   9. Back-to-top button
   ========================================================================== */

(function () {
  "use strict";

  /* Path prefix so header/footer links work from any page (all pages are at root, so "") */
  var ROOT = "";

  /* ------------------------------------------------------------------
     1. SHARED HEADER & FOOTER
     ------------------------------------------------------------------ */
  var NAV_ITEMS = [
    { label: "Home", href: "index.html" },
    { label: "About", href: "about.html" },
    {
      label: "Products",
      href: "products.html",
      children: [
        { label: "Aerators", href: "aerators.html" },
        { label: "Floating Products", href: "floating-products.html" },
        { label: "Long Arm Aerator", href: "long-arm-aerator.html" },
        { label: "Probiotics", href: "probiotics.html" },
        { label: "Aqua Chemicals", href: "aqua-chemicals.html" },
        { label: "Aqua Medicine", href: "aqua-medicine.html" },
        { label: "Aerator Spares", href: "aerator-spares.html" }
      ]
    },
    { label: "Events", href: "events.html" },
    { label: "Catalog", href: "catalog.html" },
    { label: "Contact", href: "contact.html" },
    { label: "Admin", href: "admin/", newTab: true }
  ];

  function currentPage() {
    var p = window.location.pathname.split("/").pop();
    return p === "" ? "index.html" : p;
  }

  function buildHeader() {
    var mount = document.getElementById("site-header");
    if (!mount) return;
    var current = currentPage();

    var navHTML = NAV_ITEMS.map(function (item) {
      var isActive = item.href === current || (item.children && item.children.some(function (c) { return c.href === current; }));
      if (item.children) {
        var subLinks = item.children.map(function (c) {
          return '<a href="' + ROOT + c.href + '"><span class="dot"></span>' + c.label + "</a>";
        }).join("");
        return (
          '<li class="has-dropdown' + (isActive ? " active" : "") + '">' +
          '<a href="' + ROOT + item.href + '" class="nav-link">' + item.label + '<span class="caret"></span></a>' +
          '<div class="dropdown">' + subLinks + "</div>" +
          "</li>"
        );
      }
      return '<li' + (isActive ? ' class="active"' : '') + '><a href="' + ROOT + item.href + '" class="nav-link"' + (item.newTab ? ' target="_blank" rel="noopener noreferrer"' : '') + '>' + item.label + "</a></li>";
    }).join("");

    var mobileHTML = NAV_ITEMS.map(function (item, idx) {
      if (item.children) {
        var subs = item.children.map(function (c) {
          return '<a href="' + ROOT + c.href + '">' + c.label + "</a>";
        }).join("");
        return (
          '<div class="m-group" data-idx="' + idx + '">' +
          '<a href="' + ROOT + item.href + '" class="m-link" data-toggle="' + idx + '">' + item.label +
          '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 9l6 6 6-6"/></svg></a>' +
          '<div class="m-sub">' + subs + "</div>" +
          "</div>"
        );
      }
      return '<a href="' + ROOT + item.href + '" class="m-link">' + item.label + "</a>";
    }).join("");

    mount.innerHTML =
      '<header class="header" id="siteHeaderBar">' +
        '<div class="header-inner">' +
          '<a href="' + ROOT + 'index.html" class="brand" aria-label="Jay Kay Aqua Tech home">' +
            '<img src="' + ROOT + 'assets/logo/Jay Kay Aqua Tech.png" alt="Jay Kay Aqua Tech logo">' +
            '<span class="brand-word">JAY KAY AQUA TECH<span>Aquaculture Technology</span></span>' +
          "</a>" +
          '<ul class="nav">' + navHTML + "</ul>" +
          '<div class="header-actions">' +
            '<a href="' + ROOT + 'contact.html" class="btn btn-ghost">Send Enquiry</a>' +
            '<a href="' + ROOT + 'products.html" class="btn btn-primary">Explore Products</a>' +
          "</div>" +
          '<button class="nav-toggle" id="navToggle" aria-label="Open menu" aria-expanded="false">' +
            "<span></span><span></span><span></span>" +
          "</button>" +
        "</div>" +
      "</header>" +
      '<div class="nav-scrim" id="navScrim"></div>' +
      '<nav id="mobile-nav" aria-label="Mobile navigation">' +
        '<div class="mobile-nav-head">' +
          '<a href="' + ROOT + 'index.html" class="brand"><img src="' + ROOT + 'assets/logo/Jay Kay Aqua Tech.png" alt="Jay Kay Aqua Tech"><span class="brand-word">JAY KAY AQUA TECH</span></a>' +
          '<button class="mobile-nav-close" id="mobileNavClose" aria-label="Close menu">' +
            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#07293D" stroke-width="2.4"><path d="M6 6l12 12M18 6L6 18"/></svg>' +
          "</button>" +
        "</div>" +
        mobileHTML +
        '<div class="mobile-nav-cta">' +
          '<a href="' + ROOT + 'contact.html" class="btn btn-ghost btn-block">Send Enquiry</a>' +
          '<a href="' + ROOT + 'products.html" class="btn btn-primary btn-block">Explore Products</a>' +
        "</div>" +
      "</nav>";
  }

  function buildFooter() {
    var mount = document.getElementById("site-footer");
    if (!mount) return;
    var year = new Date().getFullYear();

    mount.innerHTML =
      '<footer class="footer">' +
        '<div class="container">' +
          '<div class="footer-top">' +
            '<div class="footer-brand">' +
              '<img src="' + ROOT + 'assets/logo/Jay Kay Aqua Tech.png" alt="Jay Kay Aqua Tech logo">' +
              '<p>Jay Kay Aqua Tech designs and supplies aeration and aquaculture support equipment built for working ponds and farms.</p>' +
              '<div class="footer-social" aria-label="Social links">' +
                socialIcon("facebook") + socialIcon("instagram") + socialIcon("youtube") + socialIcon("linkedin") +
              "</div>" +
            "</div>" +
            '<div class="footer-col">' +
              "<h4>Company</h4>" +
              '<ul><li><a href="' + ROOT + 'about.html">About Us</a></li>' +
              '<li><a href="' + ROOT + 'events.html">Events</a></li>' +
              '<li><a href="' + ROOT + 'catalog.html">E-Catalog</a></li>' +
              '<li><a href="' + ROOT + 'contact.html">Contact</a></li></ul>' +
            "</div>" +
            '<div class="footer-col">' +
              "<h4>Products</h4>" +
              '<ul><li><a href="' + ROOT + 'aerators.html">Aerators</a></li>' +
              '<li><a href="' + ROOT + 'floating-products.html">Floating Products</a></li>' +
              '<li><a href="' + ROOT + 'long-arm-aerator.html">Long Arm Aerator</a></li>' +
              '<li><a href="' + ROOT + 'probiotics.html">Probiotics</a></li>' +
              '<li><a href="' + ROOT + 'aqua-chemicals.html">Aqua Chemicals</a></li>' +
              '<li><a href="' + ROOT + 'aqua-medicine.html">Aqua Medicine</a></li>' +
              '<li><a href="' + ROOT + 'aerator-spares.html">Aerator Spares</a></li></ul>' +
            "</div>" +
            '<div class="footer-col">' +
              "<h4>Get in touch</h4>" +
              '<ul class="footer-contact">' +
               '<li>' + pinIcon() + '<span>S. NO-7, KASHIBAG, SOCIETY, OPP-AGRI COLLEGE GATE, VIJALPORE ROAD, NAVSARI – 396450, GUJARAT INDIA.</span></li>' +
                '<li>' + phoneIcon() + '<span>Phone number to be added</span></li>' +
                '<li>' + mailIcon() + '<span>Email to be added</span></li>' +
              "</ul>" +
            "</div>" +
          "</div>" +
          '<div class="footer-bottom">' +
            "<span>&copy; " + year + ' Jay Kay Aqua Tech. All rights reserved.</span>' +
            '<div class="legal-links"><a href="' + ROOT + 'contact.html">Privacy</a><a href="' + ROOT + 'contact.html">Terms</a></div>' +
          "</div>" +
        "</div>" +
      "</footer>" +
      '<button class="back-to-top" id="backToTop" aria-label="Back to top">' +
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 19V5M5 12l7-7 7 7"/></svg>' +
      "</button>";
  }

  function socialIcon(kind) {
    var paths = {
      facebook: '<path d="M13 21v-8h3l1-4h-4V7c0-1.1.9-2 2-2h2V1h-3a5 5 0 0 0-5 5v3H6v4h3v8h4z"/>',
      instagram: '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/>',
      youtube: '<rect x="2" y="5" width="20" height="14" rx="4"/><path d="M10 9l5 3-5 3z" fill="#fff" stroke="none"/>',
      linkedin: '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M7 10v7M7 7v.01M11 17v-4a2 2 0 0 1 4 0v4M11 13v4" />'
    };
    return '<a href="#" aria-label="' + kind + '"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">' + paths[kind] + "</svg></a>";
  }
  function pinIcon() { return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>'; }
  function phoneIcon() { return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.7a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.1-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.5 2.7.6a2 2 0 0 1 1.7 2z"/></svg>'; }
  function mailIcon() { return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="m3 7 9 6 9-6"/></svg>'; }

  /* ------------------------------------------------------------------
     2. HEADER BEHAVIOUR
     ------------------------------------------------------------------ */
  function initHeaderBehaviour() {
    var bar = document.getElementById("siteHeaderBar");
    if (bar) {
      window.addEventListener("scroll", function () {
        bar.classList.toggle("is-scrolled", window.scrollY > 12);
      });
    }

    var toggle = document.getElementById("navToggle");
    var drawer = document.getElementById("mobile-nav");
    var scrim = document.getElementById("navScrim");
    var closeBtn = document.getElementById("mobileNavClose");

    function openDrawer() {
      drawer.classList.add("is-open");
      scrim.classList.add("show");
      toggle.classList.add("is-open");
      toggle.setAttribute("aria-expanded", "true");
      document.body.style.overflow = "hidden";
    }
    function closeDrawer() {
      drawer.classList.remove("is-open");
      scrim.classList.remove("show");
      toggle.classList.remove("is-open");
      toggle.setAttribute("aria-expanded", "false");
      document.body.style.overflow = "";
    }
    if (toggle) toggle.addEventListener("click", function () {
      drawer.classList.contains("is-open") ? closeDrawer() : openDrawer();
    });
    if (closeBtn) closeBtn.addEventListener("click", closeDrawer);
    if (scrim) scrim.addEventListener("click", closeDrawer);

    document.querySelectorAll(".m-group .m-link").forEach(function (link) {
      link.addEventListener("click", function (e) {
        e.preventDefault();
        link.closest(".m-group").classList.toggle("open");
      });
    });
  }

  /* ------------------------------------------------------------------
     3. SCROLL REVEAL
     ------------------------------------------------------------------ */
  function initReveal() {
    var items = document.querySelectorAll(".reveal");
    if (!items.length) return;
    if (!("IntersectionObserver" in window)) {
      items.forEach(function (el) { el.classList.add("in"); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add("in");
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.14, rootMargin: "0px 0px -40px 0px" });
    items.forEach(function (el) { io.observe(el); });
  }

  /* ------------------------------------------------------------------
     4. COUNTER ANIMATION (only for legitimate figures the page provides)
     ------------------------------------------------------------------ */
  function initCounters() {
    var counters = document.querySelectorAll("[data-count-to]");
    if (!counters.length) return;
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        var to = parseFloat(el.getAttribute("data-count-to"));
        var suffix = el.getAttribute("data-suffix") || "";
        var dur = 1400, start = null;
        function step(ts) {
          if (!start) start = ts;
          var progress = Math.min((ts - start) / dur, 1);
          var eased = 1 - Math.pow(1 - progress, 3);
          el.textContent = Math.round(eased * to) + suffix;
          if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
        io.unobserve(el);
      });
    }, { threshold: 0.6 });
    counters.forEach(function (el) { io.observe(el); });
  }

  /* ------------------------------------------------------------------
     5. LIVE PRODUCT CATALOGUE
     ------------------------------------------------------------------
     Product cards are loaded from api/products.php, which reads the same
     database used by the admin panel. This keeps the public catalogue in
     sync with products added/edited/deleted in the admin area.
     ------------------------------------------------------------------ */
  function escapeHtml(value) {
    return String(value == null ? "" : value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function slugify(value) {
    return String(value || "").toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/(^-|-$)/g, "");
  }

  function productImageMarkup(product, imageClass) {
    var src = product.images && product.images.length ? product.images[0] : "";
    if (!src) {
      return '<div class="product-image-placeholder" aria-label="No product image">Product Image</div>';
    }
    return '<img class="' + (imageClass || "") + '" alt="' + escapeHtml(product.name) + '" decoding="async" loading="lazy" src="' + escapeHtml(src) + '" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\';"><div class="product-image-placeholder" style="display:none;">Product Image</div>';
  }

  function renderProductCard(product) {
    var specs = Array.isArray(product.specs) ? product.specs : [];
    var chips = specs.slice(0, 3).map(function (spec) {
      return '<span>' + escapeHtml(Array.isArray(spec) ? spec[0] : spec) + '</span>';
    }).join("");
    if (!chips) {
      chips = (Array.isArray(product.features) ? product.features : []).slice(0, 2).map(function (feature) {
        return '<span>' + escapeHtml(feature) + '</span>';
      }).join("");
    }
    if (!chips) chips = '<span>Product details</span>';

    var sub = product.subcategory ? ' / ' + product.subcategory : '';
    return '<article class="product-card reveal" data-cat="' + escapeHtml(product.categorySlug) + '" data-subcat="' + escapeHtml(slugify(product.subcategory)) + '">' +
      '<div class="thumb"><span class="badge">Product Image</span>' + productImageMarkup(product, "") + '</div>' +
      '<div class="body">' +
        '<span class="cat-label">' + escapeHtml(product.categoryName + sub) + '</span>' +
        '<h3>' + escapeHtml(product.name) + '</h3>' +
        '<p>' + escapeHtml(product.short || "Product information available on request.") + '</p>' +
        '<div class="specs">' + chips + '</div>' +
        '<div class="card-actions">' +
          '<a class="btn btn-ghost" href="product-details.html?id=' + encodeURIComponent(product.slug) + '">View Details</a>' +
          '<a class="btn btn-primary" href="contact.html?product=' + encodeURIComponent(product.slug) + '">Enquire</a>' +
        '</div>' +
      '</div>' +
    '</article>';
  }

  function initFilters() {
    var bar = document.querySelector("[data-cat-filter]");
    if (!bar) return;

    var CATEGORY_NAMES = {
      "aerators": "Aerators",
      "floating-products": "Floating Products",
      "long-arm-aerator": "Long Arm Aerator",
      "probiotics": "Probiotics",
      "aqua-chemicals": "Aqua Chemicals",
      "aqua-medicine": "Aqua Medicine",
      "aerator-spares": "Aerator Spares"
    };
    var CATEGORY_ORDER = ["aerators", "floating-products", "long-arm-aerator", "probiotics", "aqua-chemicals", "aqua-medicine", "aerator-spares"];
    var catSelect = document.getElementById("filterCategory");
    var subSelect = document.getElementById("filterSubcategory");
    var group = document.querySelector("[data-filter-group]");
    var emptyState = document.querySelector("[data-cat-filter-empty]");
    var isMultiCat = bar.getAttribute("data-multi-cat") === "1";
    if (!catSelect || !subSelect || !group) return;

    var requestedCategory = catSelect.getAttribute("data-current") || "";
    var apiUrl = "api/products.php";
    if (!isMultiCat && requestedCategory) apiUrl += "?category=" + encodeURIComponent(requestedCategory);

    fetch(apiUrl, { headers: { "Accept": "application/json" } })
      .then(function (res) {
        if (!res.ok) throw new Error("Product API returned " + res.status);
        return res.json();
      })
      .then(function (data) {
        if (!data || !data.success) throw new Error(data && data.message ? data.message : "Unable to load products");
        var products = Array.isArray(data.products) ? data.products : [];
        var categories = Array.isArray(data.categories) ? data.categories : [];

        // Populate categories from the database while retaining the page's
        // current selection. The fallback map keeps the site usable if an
        // older database does not yet expose category metadata.
        if (categories.length) {
          var currentValue = catSelect.value || (isMultiCat ? "all" : requestedCategory);
          var options = isMultiCat ? '<option value="all">All Categories</option>' : '';
          categories.sort(function (a, b) { return CATEGORY_ORDER.indexOf(a.slug) - CATEGORY_ORDER.indexOf(b.slug); });
          options += categories.map(function (cat) {
            return '<option value="' + escapeHtml(cat.slug) + '">' + escapeHtml(cat.name) + '</option>';
          }).join("");
          catSelect.innerHTML = options;
          if (currentValue && (currentValue === "all" || categories.some(function (c) { return c.slug === currentValue; }))) {
            catSelect.value = currentValue;
          } else if (!isMultiCat) {
            catSelect.value = requestedCategory;
          }
        }

        function populateSubcats(catSlug) {
          if (!catSlug || catSlug === "all") {
            subSelect.innerHTML = '<option value="all">All</option>';
            subSelect.disabled = true;
            return;
          }
          var seen = {};
          var subs = products.filter(function (p) { return p.categorySlug === catSlug && p.subcategory; }).map(function (p) { return p.subcategory; }).filter(function (name) {
            var key = slugify(name);
            if (seen[key]) return false;
            seen[key] = true;
            return true;
          }).sort();
          subSelect.innerHTML = '<option value="all">All</option>' + subs.map(function (name) {
            return '<option value="' + escapeHtml(slugify(name)) + '">' + escapeHtml(name) + '</option>';
          }).join("");
          subSelect.disabled = subs.length === 0;
        }

        function applyFilter() {
          var cat = catSelect.value || "all";
          var sub = subSelect.value || "all";
          var filtered = products.filter(function (product) {
            return (cat === "all" || product.categorySlug === cat) &&
                   (sub === "all" || slugify(product.subcategory) === sub);
          });
          group.innerHTML = filtered.map(renderProductCard).join("");
          if (emptyState) emptyState.style.display = filtered.length ? "none" : "";
          initReveal();
        }

        catSelect.addEventListener("change", function () {
          var selected = catSelect.value;
          var current = catSelect.getAttribute("data-current");
          if (!isMultiCat && selected !== current) {
            window.location.href = selected + ".html";
            return;
          }
          subSelect.value = "all";
          populateSubcats(selected);
          applyFilter();
        });
        subSelect.addEventListener("change", applyFilter);

        populateSubcats(catSelect.value || (isMultiCat ? "all" : requestedCategory));
        applyFilter();
      })
      .catch(function (error) {
        console.error("Product catalogue error:", error);
        group.innerHTML = "";
        if (emptyState) {
          emptyState.textContent = "Products could not be loaded. Please try again later.";
          emptyState.style.display = "";
        }
      });
  }

  /* ------------------------------------------------------------------
     6. PRODUCT DETAILS (LIVE DATABASE)
     ------------------------------------------------------------------ */
  function initProductDetails() {
    if (!document.getElementById("pdDetailGrid")) return;
    var id = new URLSearchParams(window.location.search).get("id");
    if (!id) return;

    fetch("api/products.php?slug=" + encodeURIComponent(id), { headers: { "Accept": "application/json" } })
      .then(function (res) {
        if (!res.ok) throw new Error("Product not found");
        return res.json();
      })
      .then(function (data) {
        if (!data || !data.success || !data.product) throw new Error("Product not found");
        var p = data.product;
        var images = Array.isArray(p.images) ? p.images : [];

        document.title = p.name + " | Jay Kay Aqua Tech";
        var current = document.getElementById("pdBreadcrumbCurrent");
        if (current) current.textContent = p.name;
        var cat = document.getElementById("pdCatLabel");
        if (cat) cat.textContent = p.categoryName + (p.subcategory ? " / " + p.subcategory : "");
        var title = document.getElementById("pdTitle");
        if (title) title.textContent = p.name;
        var productField = document.getElementById("pdProductField");
        if (productField) productField.value = p.name;
        var enquireUrl = "contact.html?product=" + encodeURIComponent(p.slug);
        var enquire = document.getElementById("pdEnquire");
        if (enquire) enquire.href = enquireUrl;
        var ctaEnquire = document.getElementById("pdCtaEnquire");
        if (ctaEnquire) ctaEnquire.href = enquireUrl;
        var short = document.getElementById("pdShort");
        if (short) short.textContent = p.short || "";
        var overview = document.getElementById("pdOverview");
        if (overview) overview.textContent = p.full || p.short || "";

        var main = document.getElementById("pdMainFrame");
        var thumbs = document.getElementById("pdThumbs");
        if (main) main.innerHTML = images.length ? '<img src="' + escapeHtml(images[0]) + '" alt="' + escapeHtml(p.name) + '" loading="eager" decoding="async">' : '<div class="product-image-placeholder">Product Image</div>';
        if (thumbs) {
          thumbs.innerHTML = images.map(function (src, i) {
            return '<button class="' + (i === 0 ? 'active' : '') + '" type="button"><img src="' + escapeHtml(src) + '" alt="' + escapeHtml(p.name) + ' view ' + (i + 1) + '" loading="lazy"></button>';
          }).join("");
        }

        var features = Array.isArray(p.features) ? p.features : [];
        var specs = Array.isArray(p.specs) ? p.specs : [];
        var applications = Array.isArray(p.applications) ? p.applications : [];
        var featureHtml = features.length ? '<ul class="feature-list">' + features.map(function (f) {
          return '<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg><span>' + escapeHtml(f) + '</span></li>';
        }).join("") + '</ul>' : '<p>No additional feature information was supplied.</p>';
        var specHtml = specs.length ? '<table class="spec-table"><tbody>' + specs.map(function (s) {
          return '<tr><td>' + escapeHtml(Array.isArray(s) ? s[0] : "") + '</td><td>' + escapeHtml(Array.isArray(s) ? s[1] : s) + '</td></tr>';
        }).join("") + '</tbody></table>' : '<p>No technical specifications were supplied with the current product material.</p>';
        var appHtml = applications.length ? '<ul class="feature-list">' + applications.map(function (a) {
          return '<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg><span>' + escapeHtml(a) + '</span></li>';
        }).join("") + '</ul>' : '<p>No application information was supplied.</p>';

        var featureEl = document.getElementById("pdFeatures"); if (featureEl) featureEl.innerHTML = featureHtml;
        var specEl = document.getElementById("pdSpecs"); if (specEl) specEl.innerHTML = specHtml;
        var appEl = document.getElementById("pdApplications"); if (appEl) appEl.innerHTML = appHtml;
        var tags = document.getElementById("pdTags"); if (tags) tags.innerHTML = specs.slice(0, 4).map(function (s) { return '<span>' + escapeHtml(Array.isArray(s) ? s[0] : s) + '</span>'; }).join("");

        var tabs = document.querySelectorAll(".tab-nav button");
        tabs.forEach(function (button) {
          var tab = button.getAttribute("data-tab");
          if ((tab === "tabFeatures" && !features.length) || (tab === "tabSpecs" && !specs.length) || (tab === "tabApplications" && !applications.length)) {
            button.style.display = "none";
          }
        });

        var related = document.getElementById("pdRelated");
        if (related) {
          fetch("api/products.php?category=" + encodeURIComponent(p.categorySlug), { headers: { "Accept": "application/json" } })
            .then(function (res) { return res.json(); })
            .then(function (relData) {
              var relatedProducts = (relData.products || []).filter(function (q) { return q.slug !== p.slug; }).slice(0, 3);
              related.innerHTML = relatedProducts.length ? '<div class="grid grid-3" data-filter-group="">' + relatedProducts.map(renderProductCard).join("") + '</div>' : '<p>No related products are currently listed in this category.</p>';
              initReveal();
            })
            .catch(function () { related.innerHTML = '<p>No related products are currently listed in this category.</p>'; });
        }

        initTabs();
      })
      .catch(function () {
        var grid = document.getElementById("pdDetailGrid");
        if (grid) grid.innerHTML = '<div class="cat-filter-empty" style="display:block;grid-column:1/-1">This product could not be found or is currently unavailable.</div>';
      });
  }

  /* ------------------------------------------------------------------
     6. TABS (product detail)
     ------------------------------------------------------------------ */
  function initTabs() {
    var nav = document.querySelector(".tab-nav");
    if (!nav) return;
    var buttons = nav.querySelectorAll("button");
    buttons.forEach(function (btn) {
      btn.addEventListener("click", function () {
        buttons.forEach(function (b) { b.classList.remove("active"); });
        document.querySelectorAll(".tab-panel").forEach(function (p) { p.classList.remove("active"); });
        btn.classList.add("active");
        document.getElementById(btn.getAttribute("data-tab")).classList.add("active");
      });
    });

    var thumbs = document.querySelectorAll(".pd-thumbs button");
    var mainFrame = document.querySelector(".pd-gallery .main-frame");
    thumbs.forEach(function (t) {
      t.addEventListener("click", function () {
        thumbs.forEach(function (x) { x.classList.remove("active"); });
        t.classList.add("active");
        if (mainFrame) mainFrame.innerHTML = t.innerHTML;
      });
    });

    var qty = document.getElementById("pdQty");
    var decBtn = document.getElementById("pdQtyDec");
    var incBtn = document.getElementById("pdQtyInc");
    if (qty && decBtn && incBtn) {
      decBtn.addEventListener("click", function () { qty.value = Math.max(1, (parseInt(qty.value, 10) || 1) - 1); });
      incBtn.addEventListener("click", function () { qty.value = (parseInt(qty.value, 10) || 1) + 1; });
    }
  }

  /* ------------------------------------------------------------------
     7. FAQ ACCORDION
     ------------------------------------------------------------------ */
  function initFaq() {
    document.querySelectorAll(".faq-item").forEach(function (item) {
      var q = item.querySelector(".faq-q");
      if (!q) return;
      q.addEventListener("click", function () {
        var wasOpen = item.classList.contains("open");
        item.closest(".faq-list").querySelectorAll(".faq-item").forEach(function (i) { i.classList.remove("open"); });
        if (!wasOpen) item.classList.add("open");
      });
    });
  }

  /* ------------------------------------------------------------------
     8. FORM VALIDATION
     ------------------------------------------------------------------ */
  function validateField(field) {
    var input = field.querySelector("input, textarea, select");
    if (!input) return true;
    var valid = input.checkValidity();
    if (input.type === "tel" && input.value.trim()) {
      valid = /^[0-9+\-\s()]{7,16}$/.test(input.value.trim());
    }
    field.classList.toggle("has-error", !valid);
    return valid;
  }

  function initForms() {
    document.querySelectorAll("form[data-validate]").forEach(function (form) {
      var status = form.querySelector(".form-status");
      form.addEventListener("submit", function (e) {
        e.preventDefault();
        var fields = form.querySelectorAll(".field");
        var allValid = true;
        fields.forEach(function (field) {
          if (!validateField(field)) allValid = false;
        });

        if (!allValid) {
          if (status) {
            status.textContent = "Please check the highlighted fields and try again.";
            status.className = "form-status show err";
          }
          return;
        }

        var action = form.getAttribute("action");
        var submitBtn = form.querySelector('button[type="submit"]');
        var originalBtnText = submitBtn ? submitBtn.textContent : "";

        if (!action) {
          // No server endpoint configured on this form — nothing to submit to.
          if (status) {
            status.textContent = "Thanks — your message has been received.";
            status.className = "form-status show ok";
          }
          form.reset();
          return;
        }

        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.textContent = "Sending…";
        }

        fetch(action, {
          method: form.getAttribute("method") || "POST",
          body: new FormData(form),
          headers: { "X-Requested-With": "XMLHttpRequest" }
        })
          .then(function (res) { return res.json().catch(function () { return { success: false, message: "Unexpected response from the server." }; }); })
          .then(function (data) {
            if (status) {
              status.textContent = data.message || (data.success ? "Thanks — your message has been received." : "Something went wrong. Please try again.");
              status.className = "form-status show " + (data.success ? "ok" : "err");
            }
            if (data.success) {
              form.reset();
            } else if (data.errors) {
              Object.keys(data.errors).forEach(function (fieldName) {
                var input = form.querySelector('[name="' + fieldName + '"]');
                var fieldWrap = input ? input.closest(".field") : null;
                if (fieldWrap) {
                  fieldWrap.classList.add("has-error");
                  var msg = fieldWrap.querySelector(".error-msg");
                  if (msg) msg.textContent = data.errors[fieldName];
                }
              });
            }
          })
          .catch(function () {
            if (status) {
              status.textContent = "Network error — please check your connection and try again.";
              status.className = "form-status show err";
            }
          })
          .finally(function () {
            if (submitBtn) {
              submitBtn.disabled = false;
              submitBtn.textContent = originalBtnText;
            }
          });
      });

      form.querySelectorAll(".field input, .field textarea, .field select").forEach(function (input) {
        input.addEventListener("blur", function () { validateField(input.closest(".field")); });
      });
    });
  }

  /* ------------------------------------------------------------------
     9. BACK TO TOP
     ------------------------------------------------------------------ */
  function initBackToTop() {
    var btn = document.getElementById("backToTop");
    if (!btn) return;
    window.addEventListener("scroll", function () {
      btn.classList.toggle("show", window.scrollY > 500);
    });
    btn.addEventListener("click", function () {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  /* ------------------------------------------------------------------
     INIT
     ------------------------------------------------------------------ */
  document.addEventListener("DOMContentLoaded", function () {
    buildHeader();
    buildFooter();
    initHeaderBehaviour();
    initReveal();
    initCounters();
    initFilters();
    initProductDetails();
    initTabs();
    initFaq();
    initForms();
    initBackToTop();
  });
})();
