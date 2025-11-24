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

function wrhrApplyLanguage(lang, delay = 0) {
    const apply = () => {
        const combo = document.querySelector('.goog-te-combo');
        if (!combo) return;

        if (!lang || lang === 'en') {
            combo.value = '';
            combo.dispatchEvent(new Event('change'));

            // cache kırmak için ikinci tetikleme
            setTimeout(() => {
                combo.value = '';
                combo.dispatchEvent(new Event('change'));
            }, 100);
            return;
        }

        combo.value = lang;
        combo.dispatchEvent(new Event('change'));
    };

    if (delay > 0) {
        setTimeout(apply, delay);
    } else {
        apply();
    }
}

(function() {
    const select = document.getElementById('wrhr-custom-lang');
    if (!select) return;

    const STORAGE_KEY = 'wrhr_lang';
    const saved = localStorage.getItem(STORAGE_KEY) || 'en';

    select.value = saved;
    if (saved !== 'en') {
        wrhrApplyLanguage(saved, 300);
    }

    select.addEventListener('change', function() {
        const lang = this.value;
        const combo = document.querySelector('.goog-te-combo');

        if (lang === 'en') {
            if (combo) {
                wrhrApplyLanguage('en');
            }
            this.value = 'en';
            localStorage.setItem(STORAGE_KEY, 'en');
            return;
        }

        if (combo) {
            wrhrApplyLanguage(lang);
        }
        localStorage.setItem(STORAGE_KEY, lang);
    });

    // Menü çevirisini engelle
    const menuBlocker = setInterval(() => {
        const menu = document.getElementById('wrhr-custom-lang');
        if (!menu) return;

        menu.classList.add('notranslate');
        menu.querySelectorAll('option').forEach(o => o.classList.add('notranslate'));

        clearInterval(menuBlocker);
    }, 300);

    document.addEventListener('wrhr_page_changed', function() {
        const lang = localStorage.getItem(STORAGE_KEY);
        if (!lang || lang === 'en') return;

        wrhrApplyLanguage(lang, 150);
    });
})();

// Hard kill Google banner iframe repeatedly
setInterval(() => {
    document.querySelectorAll("iframe.goog-te-banner-frame").forEach(el => el.remove());
    document.body.style.top = "0px";
}, 500);

/* ========================================================
   WRHR - MINI FIX: English Reset Always Works
   ======================================================== */
(function() {

    const select = document.getElementById("wrhr-custom-lang");
    if (!select) return;

    function wrhrForceEnglishReset() {
        const combo = document.querySelector(".goog-te-combo");
        if (!combo) return;

        // Google’ın güvenli sıfırlama modu
        combo.selectedIndex = 0;
        combo.dispatchEvent(new Event("change"));

        // Browser’ın otomatik dil algılama zorlamasını kır
        document.documentElement.removeAttribute("lang");

        setTimeout(() => {
            combo.selectedIndex = 0;
            combo.dispatchEvent(new Event("change"));

            document.documentElement.removeAttribute("lang");
        }, 120);
    }

    // Dropdown değişiminde EN seçilmişse → çeviriyi tamamen kapat
    select.addEventListener("change", function() {
        if (this.value === "en") {
            wrhrForceEnglishReset();

            // Menü İngilizce’de kalsın
            this.value = "en";

            localStorage.setItem("wrhr_lang", "en");
        }
    });

})();
