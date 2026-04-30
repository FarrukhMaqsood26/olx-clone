<?php
require_once 'includes/config.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=login_required");
    exit;
}

$user_id = $_SESSION['user_id'];
$ad_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch ad details and verify ownership
$stmt = $pdo->prepare("SELECT * FROM ads WHERE id = ? AND user_id = ?");
$stmt->execute([$ad_id, $user_id]);
$ad = $stmt->fetch();

if (!$ad) {
    header("Location: profile.php?error=unauthorized");
    exit;
}

// Fetch categories
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll();

// Fetch current images
$imgStmt = $pdo->prepare("SELECT * FROM ad_images WHERE ad_id = ?");
$imgStmt->execute([$ad_id]);
$currentImages = $imgStmt->fetchAll();

include 'includes/header.php';
?>

<!-- Full Screen Loader Overlay -->
<div id="postingLoader" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-[100] hidden flex-col items-center justify-center transition-all duration-500">
    <div class="relative">
        <div class="w-24 h-24 rounded-full border-4 border-brand/20 animate-ping absolute inset-0"></div>
        <div class="w-24 h-24 rounded-full border-4 border-t-brand border-r-transparent border-b-brand/40 border-l-transparent animate-spin relative z-10"></div>
        <div class="absolute inset-0 flex items-center justify-center z-20">
            <i class="fas fa-sync-alt text-brand text-2xl animate-spin"></i>
        </div>
    </div>
    <div class="mt-8 text-center">
        <h3 class="text-white font-bold text-xl tracking-tight">Updating Your Listing</h3>
        <p class="text-slate-300 text-sm mt-2 font-medium">Please wait, we're saving your changes...</p>
    </div>
</div>

<div class="max-w-3xl mx-auto bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-10">

    <div class="mb-8 border-b border-slate-100 pb-6">
        <h2 class="text-3xl font-extrabold text-slate-900 flex items-center gap-3">
            <i class="fas fa-edit text-brand hidden sm:block"></i> EDIT YOUR AD
        </h2>
        <p class="text-slate-500 mt-2">Update your listing details below.</p>
    </div>

    <form action="api/ads.php?action=update&id=<?= $ad_id ?>" method="POST" enctype="multipart/form-data" id="editAdForm" class="space-y-6">

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Ad Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="<?= htmlspecialchars($ad['title']) ?>" required maxlength="150"
                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 transition">
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Description <span class="text-red-500">*</span></label>
            <textarea name="description" required
                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 transition min-h-[150px] resize-y"><?= htmlspecialchars($ad['description']) ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Category <span class="text-red-500">*</span></label>
                <select name="category_id" required
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 bg-white transition">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $ad['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Condition <span class="text-red-500">*</span></label>
                <select name="condition_type" required
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 bg-white transition">
                    <option value="new" <?= $ad['condition_type'] == 'new' ? 'selected' : '' ?>>Brand New</option>
                    <option value="used" <?= $ad['condition_type'] == 'used' ? 'selected' : '' ?>>Used</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Price (PKR) <span class="text-red-500">*</span></label>
                <input type="number" name="price" value="<?= $ad['price'] ?>" required min="1"
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 transition">
            </div>

            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-bold text-slate-700 mb-2">Location <span class="text-red-500">*</span></label>
                <input type="text" name="location" value="<?= htmlspecialchars($ad['location']) ?>" required
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 transition">
            </div>
        </div>

        <!-- Image Management -->
        <div class="pt-6 border-t border-slate-100">
            <label class="block text-sm font-bold text-slate-700 mb-4">Manage Photos (Keep or Add New)</label>
            <div id="image-upload-container" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                
                <!-- Existing Images -->
                <?php foreach($currentImages as $img): ?>
                <div class="existing-image-card relative aspect-square border border-slate-200 rounded-xl overflow-hidden shadow-sm group">
                    <img src="<?= htmlspecialchars(get_ad_image($img['image_path'])) ?>" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                        <label class="flex items-center gap-2 cursor-pointer bg-white/20 hover:bg-white/40 p-2 rounded-lg text-white text-xs font-bold transition">
                            <input type="checkbox" name="delete_images[]" value="<?= $img['id'] ?>" class="w-4 h-4 rounded accent-red-500">
                            Remove
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Add New Photos Box -->
                <div id="add-more-box" class="cursor-pointer border-2 border-dashed border-slate-300 rounded-xl aspect-square flex flex-col items-center justify-center gap-2 hover:border-brand hover:bg-brand/5 transition text-slate-400 hover:text-brand bg-slate-50">
                    <i class="fas fa-plus text-2xl"></i>
                    <span class="text-[10px] font-bold uppercase tracking-wider">Add More</span>
                </div>
            </div>
            <p class="text-[11px] text-slate-500 mt-4 italic">Note: You can add new photos. Checked photos will be deleted.</p>
            <input type="file" id="file-upload-input" name="images[]" multiple accept="image/*" class="hidden">
        </div>

        <div class="flex flex-col sm:flex-row gap-4 pt-8">
            <a href="profile.php" class="w-full sm:w-1/3 text-center bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold py-3.5 px-6 rounded-xl transition">
                Cancel
            </a>
            <button type="submit" class="w-full sm:w-2/3 bg-brand hover:bg-brand-light text-white font-bold py-3.5 px-6 rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                <i class="fas fa-save"></i> UPDATE AD
            </button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        const MAX_IMAGES = 10;
        let newUploadedFiles = [];
        const $container = $('#image-upload-container');
        const $fileInput = $('#file-upload-input');
        const $addMoreBox = $('#add-more-box');
        const $form = $('#editAdForm');

        // Trigger file input
        $addMoreBox.click(() => $fileInput.click());

        // Handle File Selection
        $fileInput.on('change', function(e) {
            const files = Array.from(e.target.files);
            const totalImages = $('.existing-image-card:not(:has(input:checked))').length + newUploadedFiles.length + files.length;

            if (totalImages > MAX_IMAGES) {
                alert(`You can only have a total of ${MAX_IMAGES} images.`);
                return;
            }

            files.forEach(file => {
                newUploadedFiles.push(file);
                renderNewPreview(file);
            });
            
            // Clear input so same file can be selected again
            $(this).val('');
        });

        function renderNewPreview(file) {
            const reader = new FileReader();
            const $card = $(`
                <div class="new-preview-card relative aspect-square border-2 border-brand/20 rounded-xl overflow-hidden shadow-sm group">
                    <img src="" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                         <button type="button" class="remove-new-btn bg-red-500 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-600 transition">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                    <span class="absolute top-1 left-1 bg-brand text-white text-[8px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wider">New</span>
                </div>
            `);

            reader.onload = (e) => $card.find('img').attr('src', e.target.result);
            reader.readAsDataURL(file);

            $addMoreBox.before($card);

            $card.find('.remove-new-btn').click(function() {
                const index = newUploadedFiles.indexOf(file);
                if (index > -1) newUploadedFiles.splice(index, 1);
                $card.remove();
            });
        }

        // Form Submission
        $form.submit(function(e) {
            e.preventDefault();
            
            const $btn = $(this).find('button[type="submit"]');
            const originalBtnHtml = $btn.html();
            
            $('#postingLoader').removeClass('hidden').addClass('flex');
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

            const formData = new FormData(this);
            
            // Remove empty images[]
            formData.delete('images[]');
            
            // Add new files
            newUploadedFiles.forEach(file => {
                formData.append('images[]', file);
            });

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const res = typeof response === 'object' ? response : JSON.parse(response);
                        if (res.success) {
                            window.location.href = 'profile.php?success=ad_updated';
                        } else {
                            alert('Error: ' + res.message);
                            resetBtn();
                        }
                    } catch(e) {
                        console.error(response);
                        alert('An error occurred. Check console.');
                        resetBtn();
                    }
                },
                error: function() {
                    alert('Server communication error.');
                    resetBtn();
                }
            });

            function resetBtn() {
                $('#postingLoader').addClass('hidden').removeClass('flex');
                $btn.prop('disabled', false).html(originalBtnHtml);
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
