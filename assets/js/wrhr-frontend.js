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
