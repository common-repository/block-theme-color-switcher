document.addEventListener('DOMContentLoaded', function () {

    const storedValue = localStorage.getItem('btc-switcher-selected-palette');

    if (storedValue) {
        try {
            const selectedPalette = JSON.parse(storedValue);
            updatePageColors(selectedPalette);
        } catch (e) {
            console.log(e);
        }
    }

    const paletteContainer = document.querySelector('.palette-container');
    const paletteMap = new Map(Object.entries(palettes));

    paletteMap.forEach((colors, paletteName) => {

        const paletteBox = document.createElement('div');
        paletteBox.className = 'palette-box';
        paletteBox.onclick = function () {
            changeColorPalette(paletteName);
        };

        const text = document.createElement('h6');
        text.className = 'color-circle-text';
        text.textContent = paletteName;

        paletteBox.appendChild(text);

        Object.entries(colors).forEach(([colorName, colorValue], index) => {

            const colorCircle = document.createElement('div');
            colorCircle.className = 'color-circle';
            colorCircle.style.backgroundColor = colorValue;

            paletteBox.appendChild(colorCircle);

        });

        paletteContainer.appendChild(paletteBox);

    });

    let offCanvasButton = document.querySelector('.off-canvas-button');
    let switcherButtonText = document.getElementById('switcher-button-text');

    offCanvasButton.onmouseover = function () {
        switcherButtonText.style.display = 'inline';
    };

    offCanvasButton.onmouseout = function () {
        switcherButtonText.style.display = 'none';
    };

});


function removeSelectedPaletteData() {

    localStorage.removeItem('btc-switcher-selected-palette');
    localStorage.removeItem('btc-switcher-selected-palette-name');

    window.location.reload();

}

function resetColorPalette(paletteName) {

    const selectedPalette = palettes[paletteName];

    for (const [key, value] of Object.entries(selectedPalette)) {
        document.documentElement.style.setProperty(key, value);
    }

    updatePageColors(selectedPalette);

}

function changeColorPalette(paletteName) {

    const selectedPalette = palettes[paletteName];
    localStorage.setItem("btc-switcher-selected-palette", JSON.stringify(selectedPalette));
    localStorage.setItem("btc-switcher-selected-palette-name", paletteName);
    for (const [key, value] of Object.entries(selectedPalette)) {
        document.documentElement.style.setProperty(key, value);
    }

    updatePageColors(selectedPalette);

}

function updatePageColors(palette) {

    const bodyElement = document.body;

    for (const [key, value] of Object.entries(palette)) {
        bodyElement.style.setProperty(key, value);
    }
}

function toggleColorSwitcherMenu() {
    let menu = document.getElementById('colorSwitcherMenu');
    menu.classList.toggle('open');
}

window.removeSelectedPaletteData = removeSelectedPaletteData;
window.changeColorPalette = changeColorPalette;
window.updatePageColors = updatePageColors;
window.toggleColorSwitcherMenu = toggleColorSwitcherMenu;

let button = document.querySelector('.off-canvas-button');
let menu = document.querySelector('.off-canvas-menu');
document.addEventListener('click', function (event) {
    if (!menu.contains(event.target) && !button.contains(event.target)) {
        menu.classList.remove('open');
    }
});
