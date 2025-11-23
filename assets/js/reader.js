(function () {
    const readers = document.querySelectorAll('.wrhr-reader');
    readers.forEach((reader) => {
        const source = reader.dataset.source;
        if (!source) {
            return;
        }

        const info = document.createElement('p');
        info.className = 'wrhr-reader__source';
        info.textContent = `Source: ${source}`;
        reader.appendChild(info);
    });
})();
