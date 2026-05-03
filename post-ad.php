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

<!-- Full Screen Loader Overlay -->
<div id="postingLoader" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-[100] hidden flex-col items-center justify-center transition-all duration-500">
    <div class="relative">
        <!-- Main Outer Pulse -->
        <div class="w-24 h-24 rounded-full border-4 border-brand/20 animate-ping absolute inset-0"></div>
        <!-- Spinning Ring -->
        <div class="w-24 h-24 rounded-full border-4 border-t-brand border-r-transparent border-b-brand/40 border-l-transparent animate-spin relative z-10"></div>
        <!-- Center Icon -->
        <div class="absolute inset-0 flex items-center justify-center z-20">
            <i class="fas fa-paper-plane text-brand text-2xl animate-bounce"></i>
        </div>
    </div>
    <div class="mt-8 text-center">
        <h3 class="text-white font-bold text-xl tracking-tight">Creating Your Listing</h3>
        <p class="text-slate-300 text-sm mt-2 font-medium">Please wait, we're making your ad go live...</p>
    </div>
</div>

<style>
    @keyframes ping {
        0% { transform: scale(0.8); opacity: 0.8; }
        100% { transform: scale(1.5); opacity: 0; }
    }
</style>


<div class="max-w-3xl mx-auto bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-10">

    <div class="mb-8 border-b border-slate-100 pb-6">
        <h2 class="text-3xl font-extrabold text-slate-900 flex items-center gap-3">
            <i class="fas fa-plus-circle text-brand hidden sm:block"></i> POST YOUR AD
        </h2>
        <p class="text-slate-500 mt-2">Fill in the details below to create your listing. Ads with photos get 5x more
            views!</p>
    </div>

    <form action="api/ads.php?action=create" method="POST" enctype="multipart/form-data" id="postAdForm"
        class="space-y-6">

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Ad Title <span
                    class="text-red-500">*</span></label>
            <input type="text" name="title" placeholder="e.g. iPhone 14 Pro Max 256GB" required maxlength="150"
                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 transition">
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Description <span
                    class="text-red-500">*</span></label>
            <textarea name="description"
                placeholder="Describe what you are selling, include condition, features, and any details buyers should know..."
                required
                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 transition min-h-[150px] resize-y"></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Category <span
                        class="text-red-500">*</span></label>
                <select name="category_id" required
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 bg-white transition">
                    <option value="">Select Category</option>
                    <?php foreach ($all_categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Condition <span
                        class="text-red-500">*</span></label>
                <select name="condition_type" required
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 bg-white transition">
                    <option value="">Select Condition</option>
                    <option value="new">Brand New</option>
                    <option value="used">Used</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Price (PKR) <span
                        class="text-red-500">*</span></label>
                <input type="number" name="price" placeholder="e.g. 50000" required min="1"
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 transition">
            </div>

            <!-- Detailed Location Selection -->
            <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Province <span class="text-red-500">*</span></label>
                    <select id="provinceSelect" required class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 bg-white transition">
                        <option value="">Select Province</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">City <span class="text-red-500">*</span></label>
                    <select id="citySelect" required disabled class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 bg-white transition disabled:bg-slate-50 disabled:cursor-not-allowed">
                        <option value="">Select City</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Sub Area <span class="text-red-500">*</span></label>
                    <select id="areaSelect" required disabled class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 bg-white transition disabled:bg-slate-50 disabled:cursor-not-allowed">
                        <option value="">Select Area</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">House / Block / Street</label>
                    <input type="text" id="streetInput" placeholder="e.g. House #123, Block A, Street 5"
                        class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 transition">
                </div>
                <!-- Hidden input to store the combined location string for the backend -->
                <input type="hidden" name="location" id="finalLocation">
                <input type="hidden" name="valid_location" id="validLocation" value="true">
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Upload Photos (Max 10)</label>
            <div id="image-upload-container" class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                <!-- Combined Add/Paste Box -->
                <label id="add-more-box"
                    class="relative aspect-square border-2 border-dashed border-slate-300 hover:border-brand bg-slate-50 rounded-xl flex flex-col items-center justify-center p-2 transition cursor-pointer group overflow-hidden"
                    title="Click to upload or press Ctrl+V to paste">
                    <input type="file" id="file-upload-input" multiple accept="image/*" class="hidden">
                    <div class="text-center group-hover:text-brand transition-colors">
                        <i class="fas fa-plus text-2xl text-slate-400 mb-1"></i>
                        <p class="text-[10px] font-bold text-slate-500">Add images</p>
                    </div>
                </label>

                <!-- Second Box -->
                <label id="add-another-box" onclick="document.getElementById('file-upload-input').click()"
                    class="relative aspect-square border-2 border-dashed border-slate-300 hover:border-brand bg-slate-50 rounded-xl flex flex-col items-center justify-center p-2 transition cursor-pointer group overflow-hidden">
                    <div class="text-center group-hover:text-brand transition-colors">
                        <i class="fas fa-plus text-2xl text-slate-400 mb-1"></i>
                        <p class="text-[10px] font-bold text-slate-500">Add another image</p>
                    </div>
                </label>
            </div>
            <p class="text-[10px] text-slate-500 mt-3"><i class="fas fa-info-circle mr-1"></i> Drag and drop, click to
                upload, or <b>press Ctrl+V to paste</b>. Max 10.</p>
        </div>

        <div class="pt-6 mt-8 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="text-sm text-slate-500 flex items-center gap-2 max-w-sm text-center sm:text-left">
                <i class="fas fa-lightbulb text-brand text-lg hidden sm:block"></i>
                <span>Ads with clear photos and detailed descriptions sell faster!</span>
            </div>
            <button type="submit"
                class="w-full sm:w-auto bg-brand hover:bg-brand-light text-white font-bold py-3.5 px-8 rounded-lg shadow-sm hover:shadow transition flex items-center justify-center gap-2">
                <i class="fas fa-paper-plane"></i> Post Now
            </button>
        </div>
    </form>
</div>
</main>

<script>
    $(document).ready(function () {
        const MAX_IMAGES = 10;
        let uploadedFiles = []; // Array of File objects

        const $container = $('#image-upload-container');
        const $fileInput = $('#file-upload-input');
        const $addMoreBox = $('#add-more-box');
        const $addAnotherBox = $('#add-another-box');
        const $form = $('#postAdForm');

        // --- Dynamic Image Upload Logic ---

        function renderPreviews() {
            // Remove existing previews except the boxes
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

                reader.onload = function (e) {
                    $card.find('img').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);

                // Insert before the boxes
                $addMoreBox.before($card);
            });

            // Hide/Show boxes based on limit
            if (uploadedFiles.length >= MAX_IMAGES) {
                $addMoreBox.hide();
                $addAnotherBox.hide();
            } else {
                $addMoreBox.show();
                $addAnotherBox.show();
            }
        }

        // --- Clipboard Paste Logic ---
        window.addEventListener('paste', function (e) {
            const items = e.clipboardData.items;
            let files = [];
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const blob = items[i].getAsFile();
                    files.push(blob);
                }
            }
            if (files.length > 0) {
                addFiles(files);
                // Visual feedback on the box
                $addMoreBox.addClass('border-brand bg-brand/5').delay(300).queue(function (next) {
                    $(this).removeClass('border-brand bg-brand/5');
                    next();
                });
            }
        });

        $fileInput.on('change', function (e) {
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
        $addMoreBox.on('dragover', function (e) {
            e.preventDefault();
            $(this).addClass('border-brand bg-brand/5');
        });

        $addMoreBox.on('dragleave', function (e) {
            e.preventDefault();
            $(this).removeClass('border-brand bg-brand/5');
        });

        $addMoreBox.on('drop', function (e) {
            e.preventDefault();
            $(this).removeClass('border-brand bg-brand/5');
            const files = Array.from(e.originalEvent.dataTransfer.files);
            addFiles(files);
        });

        // Remove Image
        $container.on('click', '.remove-btn', function () {
            const index = $(this).data('index');
            uploadedFiles.splice(index, 1);
            renderPreviews();
        });

        // --- Triple-Cell Location Logic (Expanded Pakistan Data) ---
        const locationData = {
            "Punjab": {
                "Lahore": ["Gulberg", "DHA Phase 1-9", "Johar Town", "Model Town", "Bahria Town", "Samanabad", "Walled City", "Cantt", "Wapda Town", "Valencia", "Iqbal Town", "Garden Town"],
                "Faisalabad": ["People's Colony", "Madina Town", "Jinnah Colony", "Samanabad", "Gulberg", "Civil Lines", "Kohinoor City", "Batala Colony"],
                "Rawalpindi": ["Satellite Town", "Bahria Town", "Saddar", "Chaklala Scheme", "PWD Colony", "Adiala Road", "Gulrez Colony", "Westridge"],
                "Multan": ["Gulgasht Colony", "Bosan Road", "Cantt", "Shah Rukn-e-Alam", "Wapda Town", "Model Town", "Multan Public School Road"],
                "Gujranwala": ["Satellite Town", "DC Colony", "Wapda Town", "Garden Town", "Model Town", "G.T Road"],
                "Sialkot": ["Model Town", "Cantt", "Sialkot City", "Pasrur Road", "Shahabpura"],
                "Sargodha": ["Satellite Town", "University Road", "Sargodha Cantt", "Model Town"],
                "Bahawalpur": ["Satellite Town", "Model Town", "Noor Mahal Road", "Cantt"],
                "Sheikhupura": ["Housing Colony", "Civil Lines", "Bhikhi Road"]
            },
            "Sindh": {
                "Karachi": ["Clifton", "DHA Phase 1-8", "Gulshan-e-Iqbal", "North Nazimabad", "Malir", "Korangi", "PECHS", "Bahria Town Karachi", "Federal B Area", "Liyari", "Orangi Town", "Gulistan-e-Jauhar", "Garden East", "Saddar", "Defense"],
                "Hyderabad": ["Latifabad", "Qasimabad", "Saddar", "Citizen Colony", "Hala Naka", "Autobhan Road"],
                "Sukkur": ["Military Road", "Barrage Colony", "Sukkur City", "New Sukkur", "Shikarpur Road"],
                "Larkana": ["Civil Lines", "Lahori Mohalla", "Larkana City"],
                "Nawabshah": ["Society", "Civil Lines", "Housing Society"]
            },
            "Islamabad": {
                "Islamabad": ["F-6", "F-7", "F-8", "F-10", "F-11", "G-6", "G-7", "G-8", "G-9", "G-10", "G-11", "G-13", "E-7", "E-11", "D-12", "I-8", "I-9", "I-10", "Bani Gala", "Bahria Town", "DHA", "CBR Town", "Gulberg Greens", "Soan Garden"]
            },
            "Khyber Pakhtunkhwa": {
                "Peshawar": ["Hayatabad", "University Road", "Saddar", "Gulbahar", "Warsak Road", "Ring Road", "Dalazak Road", "Regi Model Town"],
                "Abbottabad": ["Mandian", "Jinnahabad", "Supply", "Cantt", "Kakul", "Murree Road"],
                "Mardan": ["Mardan City", "Sheikh Maltoon Town", "Baghdada", "Hoti"],
                "Swat": ["Mingora", "Saidu Sharif", "Kalam Road"]
            },
            "Balochistan": {
                "Quetta": ["Cantt", "Model Town", "Satellite Town", "Jinnah Town", "Zarghoon Road", "Samungli Road", "Airport Road"],
                "Gwadar": ["New Town", "Sangar Housing Scheme", "Marine Drive", "Zero Point"]
            },
            "Gilgit-Baltistan": {
                "Gilgit": ["Gilgit City", "Jutial", "Danyore"],
                "Skardu": ["Skardu City", "Manthal", "Hassanabad"]
            },
            "Azad Kashmir": {
                "Muzaffarabad": ["Muzaffarabad City", "Neelum Valley Road", "Garhi Habibullah", "Chehla"],
                "Mirpur": ["Mirpur City", "New City", "Sector F/1", "Sector C/1"]
            }
        };

        const $province = $('#provinceSelect');
        const $city = $('#citySelect');
        const $area = $('#areaSelect');
        const $street = $('#streetInput');
        const $finalLocation = $('#finalLocation');

        function updateFinalLocation() {
            const p = $province.val();
            const c = $city.val();
            const a = $area.val();
            const s = $street.val().trim();

            if (p && c && a) {
                const streetPart = s ? `${s}, ` : '';
                const combined = `${streetPart}${a}, ${c}, ${p}, Pakistan`;
                $finalLocation.val(combined);
            } else {
                $finalLocation.val('');
            }
        }

        // Populate Provinces
        Object.keys(locationData).forEach(p => {
            $province.append(`<option value="${p}">${p}</option>`);
        });

        $province.on('change', function () {
            const p = $(this).val();
            $city.empty().append('<option value="">Select City</option>').prop('disabled', true);
            $area.empty().append('<option value="">Select Area</option>').prop('disabled', true);
            updateFinalLocation();

            if (p && locationData[p]) {
                Object.keys(locationData[p]).forEach(c => {
                    $city.append(`<option value="${c}">${c}</option>`);
                });
                $city.prop('disabled', false);
            }
        });

        $city.on('change', function () {
            const p = $province.val();
            const c = $(this).val();
            $area.empty().append('<option value="">Select Area</option>').prop('disabled', true);
            updateFinalLocation();

            if (c && locationData[p][c]) {
                locationData[p][c].forEach(a => {
                    $area.append(`<option value="${a}">${a}</option>`);
                });
                $area.prop('disabled', false);
            }
        });

        $area.on('change', updateFinalLocation);
        $street.on('input', updateFinalLocation);

        // Final Form Submission
        $form.submit(function (e) {
            if ($('#finalLocation').val() === "") {
                e.preventDefault();
                alert("Please select Province, City, and Sub Area.");
                return;
            }

            if (uploadedFiles.length === 0) {
                e.preventDefault();
                alert("Please upload at least one photo for your ad. Ads with photos sell much faster!");
                return;
            }

            // Attach files to FormData manually since we use a custom array
            e.preventDefault();
            const $btn = $(this).find('button[type="submit"]');
            const originalBtnHtml = $btn.html();

            // Show Premium Loader
            $('#postingLoader').removeClass('hidden').addClass('flex');
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Posting...');

            const formData = new FormData(this);
            
            // Clean up and append images correctly
            formData.delete('images');
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
                success: function (response) {
                    if (response.success) {
                        window.location.href = 'profile.php?success=ad_posted';
                    } else {
                        // Hide Loader on error
                        $('#postingLoader').addClass('hidden').removeClass('flex');
                        alert('Error: ' + (response.message || 'Unknown error occurred'));
                        $btn.prop('disabled', false).html(originalBtnHtml);
                    }
                },
                error: function (xhr) {
                    // Hide Loader on error
                    $('#postingLoader').addClass('hidden').removeClass('flex');
                    console.error(xhr.responseText);
                    alert('An error occurred while communicating with the server.');
                    $btn.prop('disabled', false).html(originalBtnHtml);
                }
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>