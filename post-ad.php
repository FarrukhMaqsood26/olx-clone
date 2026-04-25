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
                <div id="image-upload-container" class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <!-- Main Drop/Click Zone -->
                    <label id="add-more-box" class="relative aspect-square border-2 border-dashed border-slate-300 hover:border-brand bg-slate-50 rounded-xl flex flex-col items-center justify-center p-2 transition cursor-pointer group overflow-hidden">
                        <input type="file" id="file-upload-input" multiple accept="image/*" class="hidden">
                        <div class="text-center group-hover:text-brand transition-colors">
                            <i class="fas fa-camera text-2xl text-slate-400 mb-1"></i>
                            <p class="text-[10px] font-bold text-slate-500">Add Photo</p>
                        </div>
                    </label>
                </div>
                <p class="text-[10px] text-slate-500 mt-3"><i class="fas fa-info-circle mr-1"></i> Drag and drop photos. First photo is your main image. Max 10.</p>
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
    const MAX_IMAGES = 10;
    let uploadedFiles = []; // Array of File objects
    
    const $container = $('#image-upload-container');
    const $fileInput = $('#file-upload-input');
    const $addMoreBox = $('#add-more-box');
    const $form = $('#postAdForm');

    // --- Dynamic Image Upload Logic ---

    function renderPreviews() {
        // Remove existing previews except the "add more" box
        $container.find('.preview-card').remove();
        
        uploadedFiles.forEach((file, index) => {
            const reader = new FileReader();
            const $card = $(`
                <div class="preview-card relative aspect-square border border-slate-200 rounded-xl overflow-hidden shadow-sm group">
                    <img src="" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                         <button type="button" class="remove-btn bg-red-500 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-600 transition" data-index="${index}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                    ${index === 0 ? '<span class="absolute bottom-1 left-1 bg-brand text-white text-[8px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wider">Main</span>' : ''}
                </div>
            `);
            
            reader.onload = function(e) {
                $card.find('img').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
            
            // Insert before the "add more" box
            $addMoreBox.before($card);
        });

        // Hide/Show "add more" box based on limit
        if (uploadedFiles.length >= MAX_IMAGES) {
            $addMoreBox.hide();
        } else {
            $addMoreBox.show();
        }
    }

    $fileInput.on('change', function(e) {
        const files = Array.from(e.target.files);
        addFiles(files);
        $(this).val(''); // Reset input
    });

    function addFiles(files) {
        const remaining = MAX_IMAGES - uploadedFiles.length;
        files.slice(0, remaining).forEach(file => {
            if (file.type.match('image.*')) {
                uploadedFiles.push(file);
            }
        });
        renderPreviews();
    }

    // Drag and Drop
    $addMoreBox.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('border-brand bg-brand/5');
    });

    $addMoreBox.on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('border-brand bg-brand/5');
    });

    $addMoreBox.on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('border-brand bg-brand/5');
        const files = Array.from(e.originalEvent.dataTransfer.files);
        addFiles(files);
    });

    // Remove Image
    $container.on('click', '.remove-btn', function() {
        const index = $(this).data('index');
        uploadedFiles.splice(index, 1);
        renderPreviews();
    });

    // --- OpenStreetMap (Nominatim) Geocoding Logic ---
    let nominatimDebounce;
    
    $('#locationInput').on('input', function() {
        const query = $(this).val();
        clearTimeout(nominatimDebounce);
        
        if (query.length < 3) {
            $('#addressSuggestions').hide();
            return;
        }

        nominatimDebounce = setTimeout(() => {
            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=pk&addressdetails=1&limit=5`;
            
            $.get(url, function(data) {
                let html = '';
                if (data && data.length > 0) {
                    data.forEach(item => {
                        // Better parsing of Nominatim results for a premium feel
                        const parts = item.display_name.split(', ');
                        const mainTitle = parts[0];
                        const subTitle = parts.slice(1).join(', ');
                        
                        html += `
                            <div class="suggestion-item p-3 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-0 flex items-start gap-3" data-name="${item.display_name}">
                                <div class="mt-1 w-8 h-8 rounded-full bg-brand/10 flex items-center justify-center text-brand flex-shrink-0">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <div class="font-bold text-slate-900 text-sm truncate">${mainTitle}</div>
                                    <div class="text-[11px] text-slate-500 truncate">${subTitle}</div>
                                </div>
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

    $(document).click(function(e) {
        if (!$(e.target).closest('#addressSuggestions, #locationInput').length) {
            $('#addressSuggestions').hide();
        }
    });

    // Final Form Submission
    $form.submit(function(e) {
        if ($('#validLocation').val() === "") {
            e.preventDefault();
            alert("Please select a location from the dropdown suggestions.");
            return;
        }

        // Attach files to FormData manually since we use a custom array
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const originalBtnHtml = $btn.html();
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Posting...');
        
        const formData = new FormData(this);
        
        // Remove empty images[] entries if any
        formData.delete('images[]');
        
        uploadedFiles.forEach(file => {
            formData.append('images[]', file);
        });

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json', // Expect JSON
            success: function(response) {
                if (response.success) {
                    window.location.href = 'profile.php?success=ad_posted';
                } else {
                    alert('Error: ' + (response.message || 'Unknown error occurred'));
                    $btn.prop('disabled', false).html(originalBtnHtml);
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                alert('An error occurred while communicating with the server.');
                $btn.prop('disabled', false).html(originalBtnHtml);
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
