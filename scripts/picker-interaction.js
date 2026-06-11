/**
 * Visual feedback for the Catppuccin theme picker
 * settings UI. Highlights the chosen flavour label
 * and updates the hidden form field.
 */
function catppuccin_choose(el) {
    var picker = document.getElementById('catppuccin-theme-picker');
    picker.querySelectorAll('label').forEach(function (lbl) {
        lbl.style.borderColor = '#45475a';
        lbl.querySelector('span').style.fontWeight = '';
    });

    el.style.borderColor = '#cba6f7';
    el.querySelector('span').style.fontWeight = 'bold';
    document.getElementById('rcmfd_catppuccin_flavor').value =
        el.querySelector('input').value;
}
