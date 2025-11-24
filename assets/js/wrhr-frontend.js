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

/* WRHR — Remove Google Translate top blue banner */
function wrhrRemoveGoogleTopBar() {
    document.querySelectorAll("iframe.goog-te-banner-frame").forEach(el => el.remove());

    ["goog-gt-tt", "goog-gt-expanded", "goog-gt-placeholder"].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.remove();
    });

    document.body.style.top = "0px";
}

/* Auto-run both removers forever */
setInterval(() => {
    wrhrRemoveGoogleBranding();
    wrhrRemoveGoogleTopBar();
}, 500);

// Initial run
wrhrRemoveGoogleBranding();
wrhrRemoveGoogleTopBar();
