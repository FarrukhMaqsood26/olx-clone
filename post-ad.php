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
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Location <span class="text-red-500">*</span></label>
                    <input type="text" name="location" placeholder="e.g. DHA Phase 5, Lahore" required 
                        class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 transition">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Upload Photos (up to 5)</label>
                <div class="relative border-2 border-dashed border-slate-300 hover:border-brand bg-slate-50 hover:bg-brand/5 rounded-xl p-8 text-center transition cursor-pointer group">
                    <i class="fas fa-cloud-upload-alt text-4xl text-slate-400 group-hover:text-brand mb-3 transition"></i>
                    <p class="font-bold text-slate-700 mb-1">Click to upload photos</p>
                    <p class="text-xs text-slate-500">JPG, PNG or WEBP • Max 5MB each</p>
                    <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                </div>
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

<?php include 'includes/footer.php'; ?>
