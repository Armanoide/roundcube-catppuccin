<select id="rcmfd_catppuccin_flavor" name="_catppuccin_flavor" class="form-control">
    <?php foreach ($flavors as $slug => $label): ?>
        <option value="<?= htmlspecialchars($slug) ?>"
                <?= $slug === $current_flavor ? 'selected' : '' ?>>
            <?= htmlspecialchars($label) ?>
        </option>
    <?php endforeach; ?>
</select>
