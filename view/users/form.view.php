<?php

use App\Core\View;

$mode = (string) ($mode ?? 'create');
$isEdit = $mode !== 'create';
$isSelfEdit = $mode === 'edit-self';
$action = $isEdit
    ? '/users/' . View::escape((string) ($studentId ?? '')) . '/update'
    : '/users';
?>
<section class="stack center">
    <div class="card" style="max-width: 620px;">
        <h1 class="title" style="font-size: 28px;"><?= $isEdit ? ($isSelfEdit ? 'Edit My Account' : 'Edit Student') : 'Create Student' ?></h1>
        <p class="subtitle">
            <?= $isSelfEdit
                ? 'You can update your email, phone, and password. Name and username are locked.'
                : 'Manage student account information for your class.' ?>
        </p>

        <?php if (!empty($errors)): ?>
            <div class="alert">
                <?php foreach ($errors as $error): ?>
                    <div><?= View::escape((string) $error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="form" action="<?= $action ?>" method="post" enctype="multipart/form-data" style="max-width:none;">
            <?= \App\Core\Csrf::field() ?>
            <label>
                <span class="muted">Full name</span>
                <input class="input" type="text" name="name" value="<?= View::escape((string) ($form['name'] ?? '')) ?>" <?= $isSelfEdit ? 'readonly' : 'required' ?>>
            </label>

            <label>
                <span class="muted">Email</span>
                <input class="input" type="email" name="email" value="<?= View::escape((string) ($form['email'] ?? '')) ?>" required>
            </label>

            <label>
                <span class="muted">Phone</span>
                <input class="input" type="text" name="phone" value="<?= View::escape((string) ($form['phone'] ?? '')) ?>" required>
            </label>

            <label>
                <span class="muted">Username</span>
                <input class="input" type="text" name="username" value="<?= View::escape((string) ($form['username'] ?? '')) ?>" <?= $isSelfEdit ? 'readonly' : 'required' ?>>
            </label>

            <label>
                <span class="muted"><?= $isEdit ? 'Password (leave blank to keep current)' : 'Password' ?></span>
                <input class="input" type="password" name="password" <?= $isEdit ? '' : 'required' ?>>
            </label>

            <?php if ($isSelfEdit): ?>
                <div style="margin: 16px 0; padding: 16px; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <h3 style="margin-top:0; font-size: 16px;">Avatar Upload</h3>
                    
                    <label>
                        <span class="muted">Fetch from URL</span>
                        <div style="display:flex; gap: 8px;">
                            <input class="input" type="url" id="avatar-url" placeholder="https://example.com/image.png" style="flex:1;">
                            <button type="button" class="btn" id="fetch-avatar-btn">Fetch</button>
                        </div>
                        <span id="fetch-message" style="font-size: 12px; color: #64748b; display: block; margin-top: 4px;"></span>
                    </label>

                    <label style="margin-top: 12px; display: block;">
                        <span class="muted">Or choose local file (.jpg, .png)</span>
                        <input class="input" type="file" id="avatar-file-input" name="avatar_file" accept=".jpg,.jpeg,.png">
                    </label>
                </div>
            <?php endif; ?>

            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Save Changes' : 'Create Student' ?></button>
                <a class="pill" href="/users">Cancel</a>
            </div>
        </form>
    </div>
</section>

<?php if ($isSelfEdit): ?>
<script>
document.getElementById('fetch-avatar-btn').addEventListener('click', async () => {
    const urlInput = document.getElementById('avatar-url').value;
    const msg = document.getElementById('fetch-message');
    const fileInput = document.getElementById('avatar-file-input');
    
    if (!urlInput) {
        msg.textContent = 'Please enter a URL first.';
        msg.style.color = '#ef4444';
        return;
    }

    msg.textContent = 'Fetching...';
    msg.style.color = '#64748b';

    try {
        const response = await fetch(urlInput);
        if (!response.ok) throw new Error('Network response was not ok');
        
        const blob = await response.blob();
        if (!blob.type.startsWith('image/')) {
            throw new Error('URL does not point to an image');
        }

        const ext = blob.type.split('/')[1] || 'png';
        const filename = 'fetched_avatar.' + ext;
        
        const file = new File([blob], filename, { type: blob.type });
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        
        fileInput.files = dataTransfer.files;
        
        msg.textContent = 'Image fetched and attached successfully.';
        msg.style.color = '#10b981';
    } catch (error) {
        msg.textContent = 'Failed to fetch image: ' + error.message;
        msg.style.color = '#ef4444';
    }
});
</script>
<?php endif; ?>
