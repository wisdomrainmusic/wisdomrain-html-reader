/**
 * WRHR — Stable Google Translate Integration
 * Phase 4.2 — Wait until Google combo is ready
 */

(function() {

    let translateReady = false;
    let googleCombo = null;

    /**
     * 1) Google Translate "ready" kontrolü
     *    goog-te-combo DOM'da oluşana kadar bekler
     */
    function waitForGoogleCombo() {
        const check = setInterval(() => {
            googleCombo = document.querySelector("select.goog-te-combo");

            if (googleCombo) {
                translateReady = true;
                clearInterval(check);
                console.log("WRHR: Google Translate combo READY!");
            }
        }, 300);
    }

    waitForGoogleCombo();

    /**
     * 2) WRHR dropdown event listener
     */
    window.WRHR_ChangeLanguage = function(langCode) {

        if (!translateReady || !googleCombo) {
            console.warn("WRHR: Translate not ready yet, retrying...");
            // 500 ms sonra tekrar dene
            setTimeout(() => WRHR_ChangeLanguage(langCode), 500);
            return;
        }

        console.log("WRHR: Changing language to →", langCode);

        googleCombo.value = langCode;

        // Google'ın çeviri tetikleme event’i
        const event = document.createEvent("HTMLEvents");
        event.initEvent("change", true, true);
        googleCombo.dispatchEvent(event);
    }

    /**
     * 3) Dropdown → Google Translate Combo bağlantısı
     */
    document.addEventListener('change', function(e) {
        if (e.target.id === 'wrpr-lang-select') {
            const lang = e.target.value;
            localStorage.setItem('wrhr_lang', lang);

            const googleCombo = document.querySelector('.goog-te-combo');
            if (googleCombo) {
                googleCombo.value = lang;
                googleCombo.dispatchEvent(new Event('change'));
            }
        }
    });

})();
