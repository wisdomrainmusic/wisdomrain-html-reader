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

const WRHR_LANG_STORAGE_KEY = 'wrhr_lang';
const WRHR_LANG_ORIGINAL    = 'ORIGINAL';

function wrhrApplyLanguage(lang, delay = 0) {
    const combo = document.querySelector('select.goog-te-combo');

    const run = () => {
        if (!combo) {
            // Combo henüz hazır değilse sadece storage güncelle
            window.localStorage.setItem(WRHR_LANG_STORAGE_KEY, lang);
            return;
        }

        if (lang === WRHR_LANG_ORIGINAL) {
            // Orijinale dön → Google Translate’i resetle
            combo.value = '';
            combo.dispatchEvent(new Event('change'));

            // Tarayıcının "sayfa dili TR" sapıtmasını engellemek için lang temizle
            const html = document.documentElement;
            if (html) {
                html.removeAttribute('lang');
            }
        } else {
            // Normal bir hedef dil → Google combo’yu o dile al
            combo.value = lang;
            combo.dispatchEvent(new Event('change'));
        }

        window.localStorage.setItem(WRHR_LANG_STORAGE_KEY, lang);
    };

    if (delay > 0) {
        setTimeout(run, delay);
    } else {
        run();
    }
}

(function() {
    const select = document.getElementById('wrhr-custom-lang');
    if (!select) return;

    const saved = localStorage.getItem(WRHR_LANG_STORAGE_KEY);

    if (saved) {
        select.value = saved === WRHR_LANG_ORIGINAL ? 'EN' : saved;
        wrhrApplyLanguage(saved, 500);
    }

    select.addEventListener('change', function() {
        const value = this.value; // EN, FR, DE, ES...
        const target =
            value === 'EN'
                ? WRHR_LANG_ORIGINAL
                : value;

        wrhrApplyLanguage(target, 150);
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
        const saved = window.localStorage.getItem(WRHR_LANG_STORAGE_KEY);
        if (!saved) return;

        // İngilizce (Original) seçiliyse her sayfa değişiminde net reset
        if (saved === WRHR_LANG_ORIGINAL) {
            wrhrApplyLanguage(WRHR_LANG_ORIGINAL, 50);
            return;
        }

        wrhrApplyLanguage(saved, 50);
    });
})();

(function () {
    let runs = 0;
    const maxRuns = 40; // ~40 * 1000ms = 40 saniye

    const intervalId = setInterval(() => {
        runs++;

        document.querySelectorAll("iframe.goog-te-banner-frame").forEach(el => el.remove());
        if (document.body && document.body.style.top !== '0px') {
            document.body.style.top = '0px';
        }

        if (runs >= maxRuns) {
            clearInterval(intervalId);
        }
    }, 1000); // 1 saniyeye düşürdük
})();
