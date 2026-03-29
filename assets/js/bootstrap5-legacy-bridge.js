(function (window, document, $) {
  "use strict";

  function setIfMissing(el, fromAttr, toAttr) {
    var val = el.getAttribute(fromAttr);
    if (val && !el.getAttribute(toAttr)) {
      el.setAttribute(toAttr, val);
    }
  }

  function normalizeLegacyDataAttrs(root) {
    var scope = root || document;
    var all = scope.querySelectorAll("[data-toggle], [data-target], [data-dismiss], [data-parent], [data-spy], [data-ride]");

    for (var i = 0; i < all.length; i++) {
      var el = all[i];
      setIfMissing(el, "data-toggle", "data-bs-toggle");
      setIfMissing(el, "data-target", "data-bs-target");
      setIfMissing(el, "data-dismiss", "data-bs-dismiss");
      setIfMissing(el, "data-parent", "data-bs-parent");
      setIfMissing(el, "data-spy", "data-bs-spy");
      setIfMissing(el, "data-ride", "data-bs-ride");

      if (!el.getAttribute("data-bs-target")) {
        var href = el.getAttribute("href");
        if (href && href.charAt(0) === "#") {
          var legacyToggle = el.getAttribute("data-toggle") || el.getAttribute("data-bs-toggle");
          if (legacyToggle === "collapse" || legacyToggle === "tab" || legacyToggle === "modal") {
            el.setAttribute("data-bs-target", href);
          }
        }
      }
    }
  }

  function ensureJQueryBridge() {
    if (!$ || !window.bootstrap) {
      return;
    }

    if (!$.fn.modal) {
      $.fn.modal = function (arg) {
        return this.each(function () {
          var inst = bootstrap.Modal.getOrCreateInstance(this);
          if (arg === "show") { inst.show(); }
          else if (arg === "hide") { inst.hide(); }
          else if (arg === "toggle") { inst.toggle(); }
        });
      };
    }

    if (!$.fn.dropdown) {
      $.fn.dropdown = function (arg) {
        return this.each(function () {
          var inst = bootstrap.Dropdown.getOrCreateInstance(this);
          if (arg === "toggle" || typeof arg === "undefined") { inst.toggle(); }
        });
      };
    }

    if (!$.fn.tab) {
      $.fn.tab = function (arg) {
        return this.each(function () {
          var inst = bootstrap.Tab.getOrCreateInstance(this);
          if (arg === "show" || typeof arg === "undefined") { inst.show(); }
        });
      };
    }

    if (!$.fn.collapse) {
      $.fn.collapse = function (arg) {
        return this.each(function () {
          var inst = bootstrap.Collapse.getOrCreateInstance(this, { toggle: false });
          if (arg === "show") { inst.show(); }
          else if (arg === "hide") { inst.hide(); }
          else if (arg === "toggle" || typeof arg === "undefined") { inst.toggle(); }
        });
      };
    }

    if (!$.fn.tooltip) {
      $.fn.tooltip = function () {
        return this.each(function () {
          bootstrap.Tooltip.getOrCreateInstance(this);
        });
      };
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      normalizeLegacyDataAttrs(document);
      ensureJQueryBridge();
    });
  } else {
    normalizeLegacyDataAttrs(document);
    ensureJQueryBridge();
  }

  document.addEventListener("click", function (event) {
    var target = event.target.closest("[data-toggle], [data-target], [data-dismiss], [data-parent]");
    if (target) {
      normalizeLegacyDataAttrs(document);
    }
  });

  if (window.jQuery) {
    $(document).ajaxComplete(function () {
      normalizeLegacyDataAttrs(document);
    });
  }
})(window, document, window.jQuery);
