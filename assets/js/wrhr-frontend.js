/* WRHR — Remove Google branding inside iframe */
function wrhrRemoveGoogleBranding() {
    const iframes = document.querySelectorAll('iframe');

    iframes.forEach(ifr => {
        try {
            const inner = ifr.contentDocument || ifr.contentWindow.document;

            // branding text
            const logo = inner.querySelector('.goog-logo-link, span[textContent*="desteklenmektedir"]');
            if (logo) logo.remove();

            // remove internal banners
            const banner = inner.querySelector('.goog-te-banner-frame');
            if (banner) banner.remove();

        } catch (e) {}
    });
}

/* WRHR — Force Remove Google Banner */
function wrhrRemoveGoogleTopBar() {
    try {
        // Remove Google Translate iframe banner
        document.querySelectorAll("iframe.goog-te-banner-frame").forEach(el => el.remove());

        // Remove Google expand tooltips
        const ids = [
            "goog-gt-tt",
            "goog-gt-expanded",
            "goog-gt-placeholder"
        ];
        ids.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.remove();
        });

        // Reset spacing Google forces
        document.body.style.top = "0px";
    } catch (e) {}
}

// Run immediately
wrhrRemoveGoogleBranding();
wrhrRemoveGoogleTopBar();

// Keep killing every 0.5s (Google tries to bring it back)
setInterval(() => {
    wrhrRemoveGoogleBranding();
    wrhrRemoveGoogleTopBar();
}, 500);

/* ============================================
   WRHR CUSTOM TRANSLATE MENU – ENGINE
   ============================================ */

function wrhrApplyLanguage(lang) {
    const combo = document.querySelector('.goog-te-combo');
    if (combo) {
        combo.value = lang;
        combo.dispatchEvent(new Event("change"));
    }
}

(function() {
    const select = document.getElementById('wrhr-lang');

    // LocalStorage Load
    const saved = localStorage.getItem('wrhr_lang');
    if (saved) {
        select.value = saved;
        if (saved !== 'en') {
            setTimeout(() => wrhrApplyLanguage(saved), 300);
        }
    }

    // On Change
    select.addEventListener('change', function() {
        localStorage.setItem('wrhr_lang', this.value);

        if (this.value === 'en') {
            // original language
            wrhrApplyLanguage('');
        } else {
            wrhrApplyLanguage(this.value);
        }
    });

    // Pagination refresh
    document.addEventListener('wrhr_page_changed', function() {
        const lang = localStorage.getItem('wrhr_lang');
        if (!lang || lang === 'en') return;

        setTimeout(() => wrhrApplyLanguage(lang), 200);
    });
})();

// Hard kill Google banner iframe repeatedly
setInterval(() => {
    document.querySelectorAll("iframe.goog-te-banner-frame").forEach(el => el.remove());
    document.body.style.top = "0px";
}, 500);
