<div id="catppuccin-theme-picker" style="padding:.75rem;margin-top:1rem;">
    <div style="font-weight:bold;">Catppuccin Theme</div>
    <input type="hidden" name="_catppuccin_flavor"
           id="rcmfd_catppuccin_flavor"
           value="<?= htmlspecialchars($current_flavor) ?>">
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:.5rem;">

    <?php foreach ($flavors as $slug => $label):
        $checked = $slug === $current_flavor;
        $border  = $checked ? '#cba6f7' : '#45475a';
        $clr     = $checked ? 'font-weight:bold;' : '';
    ?>
        <label style="cursor:pointer;padding:6px 14px;border-radius:6px;
                         border:1px solid <?= $border ?>;
                         background:transparent;"
               onclick="catppuccin_choose(this)">
            <input type="radio"
                   name="_catppuccin_flavor"
                   value="<?= htmlspecialchars($slug) ?>"
                   <?= $checked ? 'checked' : '' ?>
                   style="display:none;">
            <span style="<?= $clr ?>"><?= htmlspecialchars($label) ?></span>
        </label>
    <?php endforeach; ?>

    </div>
</div>
