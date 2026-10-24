/* ქართული ვერსია: სათაურები, მენიუები და ღილაკები — მთავრულით.
   ბრაუზერები CSS-ის text-transform: uppercase-ს ქართულზე არ იყენებენ, ამიტომ ეკრანზე ტექსტი
   მთავრულის ასოებით (U+1C90…) იცვლება. HTML-ში და ბაზაში ტექსტი მხედრულად რჩება (SEO, ძებნა). */
(function () {
    if (document.documentElement.lang !== 'ka') {
        return;
    }

    var selector = 'h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6,'
        + ' .navbar .navbar-nav .nav-link, .navbar .dropdown-item, .breadcrumb, .footer-menu a, .btn';

    // მხედრული ა–ჰ (U+10D0–10FA) და ჵ–ჿ (U+10FD–10FF) → მთავრული, სხვაობა 0xBC0
    function toMtavruli(text) {
        return text.replace(/[ა-ჺჽ-ჿ]/g, function (ch) {
            return String.fromCharCode(ch.charCodeAt(0) + 0xBC0);
        });
    }

    function convert(root) {
        var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
        var node;
        while ((node = walker.nextNode())) {
            var converted = toMtavruli(node.nodeValue);
            if (converted !== node.nodeValue) {
                node.nodeValue = converted;
            }
        }
        if (root.tagName === 'INPUT' && root.value) {
            root.value = toMtavruli(root.value);
        }
    }

    document.querySelectorAll(selector).forEach(function (el) {
        if (!el.closest('[contenteditable], textarea, script, style')) {
            convert(el);
        }
    });
})();
