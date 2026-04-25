<?php 
require_once 'includes/config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=login_required");
    exit;
}

$catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$all_categories = $catStmt->fetchAll();

include 'includes/header.php'; 
?>

<main class="flex-grow py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-10">
        
        <div class="mb-8 border-b border-slate-100 pb-6">
            <h2 class="text-3xl font-extrabold text-slate-900 flex items-center gap-3">
                <i class="fas fa-plus-circle text-brand hidden sm:block"></i> POST YOUR AD
            </h2>
            <p class="text-slate-500 mt-2">Fill in the details below to create your listing. Ads with photos get 5x more views!</p>
        </div>
        
        <form action="api/ads.php?action=create" method="POST" enctype="multipart/form-data" id="postAdForm" class="space-y-6">
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Ad Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" placeholder="e.g. iPhone 14 Pro Max 256GB" required maxlength="150" 
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 transition">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Description <span class="text-red-500">*</span></label>
                <textarea name="description" placeholder="Describe what you are selling, include condition, features, and any details buyers should know..." required 
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 transition min-h-[150px] resize-y"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Category <span class="text-red-500">*</span></label>
                    <select name="category_id" required class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 bg-white transition">
                        <option value="">Select Category</option>
                        <?php foreach($all_categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Condition <span class="text-red-500">*</span></label>
                    <select name="condition_type" required class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 bg-white transition">
                        <option value="">Select Condition</option>
                        <option value="new">Brand New</option>
                        <option value="used">Used</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Price (PKR) <span class="text-red-500">*</span></label>
                    <input type="number" name="price" placeholder="e.g. 50000" required min="1" 
                        class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 transition">
                </div>
                
                <div class="relative">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Location <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="text" name="location" id="locationInput" placeholder="Start typing your address..." required autocomplete="off"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 transition">
                        <div id="addressSuggestions" class="absolute z-50 left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-lg shadow-xl hidden max-h-60 overflow-y-auto"></div>
                    </div>
                    <input type="hidden" name="valid_location" id="validLocation" value="">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Upload Photos (Max 10)</label>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <?php for($i=0; $i<10; $i++): $id = "ad-img-".$i; ?>
                    <label for="<?= $id ?>" class="relative aspect-square border-2 border-dashed border-slate-300 hover:border-brand bg-slate-50 rounded-xl flex flex-col items-center justify-center p-2 transition cursor-pointer group overflow-hidden">
                        <input type="file" name="images[]" id="<?= $id ?>" accept="image/*" class="hidden img-input">
                        <div class="text-center group-hover:text-brand transition-colors img-placeholder">
                            <i class="fas fa-camera text-2xl text-slate-400 mb-1"></i>
                            <p class="text-[10px] font-bold text-slate-500"><?= $i === 0 ? 'Main Photo' : 'Photo '.($i+1) ?></p>
                        </div>
                        <img src="" class="absolute inset-0 w-full h-full object-cover hidden img-preview">
                        <button type="button" class="absolute top-1 right-1 bg-red-500 text-white w-5 h-5 rounded-full text-[10px] items-center justify-center hidden remove-img z-20">
                            <i class="fas fa-times"></i>
                        </button>
                    </label>
                    <?php endfor; ?>
                </div>
                <p class="text-[10px] text-slate-500 mt-3"><i class="fas fa-info-circle mr-1"></i> First photo will be the main listing image. Max 10 photos.</p>
            </div>

            <div class="pt-6 mt-8 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="text-sm text-slate-500 flex items-center gap-2 max-w-sm text-center sm:text-left">
                    <i class="fas fa-lightbulb text-brand text-lg hidden sm:block"></i>
                    <span>Ads with clear photos and detailed descriptions sell faster!</span>
                </div>
                <button type="submit" class="w-full sm:w-auto bg-brand hover:bg-brand-light text-white font-bold py-3.5 px-8 rounded-lg shadow-sm hover:shadow transition flex items-center justify-center gap-2">
                    <i class="fas fa-paper-plane"></i> Post Now
                </button>
            </div>
        </form>
    </div>
</main>

<script>
$(document).ready(function() {
    // 10 IMAGE BOXES HANDLING
    $('.img-input').change(function() {
        const file = this.files[0];
        const container = $(this).closest('.relative');
        const preview = container.find('.img-preview');
        const placeholder = container.find('.img-placeholder');
        const removeBtn = container.find('.remove-img');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.attr('src', e.target.result).show();
                placeholder.hide();
                removeBtn.css('display', 'flex');
            }
            reader.readAsDataURL(file);
        }
    });

    $('.remove-img').click(function(e) {
        e.preventDefault();
        const container = $(this).closest('.relative');
        container.find('.img-input').val('');
        container.find('.img-preview').hide().attr('src', '');
        container.find('.img-placeholder').show();
        $(this).hide();
    });

    // NOMINATIM ADDRESS AUTOCOMPLETE
    let debounceTimer;
    $('#locationInput').on('input', function() {
        clearTimeout(debounceTimer);
        const query = $(this).val();
        
        if (query.length < 3) {
            $('#addressSuggestions').hide();
            return;
        }

        debounceTimer = setTimeout(() => {
            $.get(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=pk`, function(data) {
                let html = '';
                if (data.length > 0) {
                    data.forEach(item => {
                        html += `<div class="suggestion-item p-3 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-0 text-sm" data-name="${item.display_name}">
                            <i class="fas fa-map-marker-alt text-brand mr-2"></i> ${item.display_name}
                        </div>`;
                    });
                    $('#addressSuggestions').html(html).show();
                } else {
                    $('#addressSuggestions').hide();
                }
            });
        }, 500);
    });

    $(document).on('click', '.suggestion-item', function() {
        const name = $(this).data('name');
        $('#locationInput').val(name);
        $('#validLocation').val(name);
        $('#addressSuggestions').hide();
    });

    // Close suggestions on click outside
    $(document).click(function(e) {
        if (!$(e.target).closest('#addressSuggestions, #locationInput').length) {
            $('#addressSuggestions').hide();
        }
    });

    // Final Form Validation
    $('#postAdForm').submit(function(e) {
        if ($('#validLocation').val() === "") {
            e.preventDefault();
            alert("Please pick a valid address from the suggestions list.");
            $('#locationInput').focus();
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
