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

<style>
.post-ad-container {
    max-width: 800px;
    margin: 30px auto;
    padding: 40px;
    border-radius: var(--radius-xl);
    animation: fadeInUp 0.5s ease;
}

.form-title {
    color: var(--primary-teal);
    margin-bottom: 8px;
    font-size: 26px;
    font-weight: 800;
}

.form-subtitle {
    color: var(--text-secondary);
    font-size: 14px;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--glass-border);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.full-width { grid-column: span 2; }

.input-box { margin-bottom: 6px; }

.input-box label {
    display: block;
    margin-bottom: 8px;
    color: var(--primary-teal);
    font-weight: 600;
    font-size: 14px;
}

.input-box label .required {
    color: var(--danger);
}

.input-box input,
.input-box select,
.input-box textarea {
    width: 100%;
    padding: 14px 18px;
    border-radius: var(--radius-md);
    border: 1.5px solid var(--glass-border);
    background: rgba(255, 255, 255, 0.5);
    outline: none;
    font-size: 15px;
    font-family: inherit;
    color: var(--text-primary);
    transition: all var(--transition-fast);
}

.input-box input:focus,
.input-box select:focus,
.input-box textarea:focus {
    border-color: var(--accent-cyan);
    box-shadow: 0 0 0 3px rgba(35, 229, 219, 0.15);
    background: rgba(255, 255, 255, 0.8);
}

.input-box textarea { resize: vertical; min-height: 120px; }

.photo-upload {
    border: 2px dashed rgba(0, 47, 52, 0.2);
    background: rgba(255,255,255,0.3);
    border-radius: var(--radius-lg);
    padding: 35px;
    text-align: center;
    cursor: pointer;
    color: var(--text-secondary);
    transition: all var(--transition-smooth);
    position: relative;
}

.photo-upload:hover {
    border-color: var(--accent-cyan);
    background: rgba(35, 229, 219, 0.05);
}

.photo-upload i { font-size: 36px; margin-bottom: 10px; color: var(--accent-cyan); display: block; }
.photo-upload span { font-weight: 500; }
.photo-upload small { display: block; margin-top: 6px; font-size: 12px; color: #94a3b8; }

.submit-wrap {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
    border-top: 1px solid var(--glass-border);
    padding-top: 20px;
}

.submit-wrap .tip {
    font-size: 13px;
    color: var(--text-secondary);
    max-width: 300px;
}

.submit-wrap .tip i { color: var(--accent-cyan); margin-right: 5px; }

@media (max-width: 768px) {
    .post-ad-container { margin: 20px 10px; padding: 25px 20px; }
    .form-grid { grid-template-columns: 1fr; }
    .full-width { grid-column: span 1; }
    .submit-wrap { flex-direction: column; gap: 15px; }
    .submit-wrap .tip { max-width: none; text-align: center; }
}
</style>

<main>
    <div class="post-ad-container glass-panel">
        <h2 class="form-title"><i class="fas fa-plus-circle" style="color:var(--accent-cyan); margin-right:8px;"></i>POST YOUR AD</h2>
        <p class="form-subtitle">Fill in the details below to create your listing. Ads with photos get 5x more views!</p>
        
        <form action="api/ads.php?action=create" method="POST" enctype="multipart/form-data" id="postAdForm">
            <div class="form-grid">
                
                <div class="input-box full-width">
                    <label>Ad Title <span class="required">*</span></label>
                    <input type="text" name="title" placeholder="e.g. iPhone 14 Pro Max 256GB" required maxlength="150" id="ad-title">
                </div>

                <div class="input-box full-width">
                    <label>Description <span class="required">*</span></label>
                    <textarea name="description" placeholder="Describe what you are selling, include condition, features, and any details buyers should know..." required id="ad-description"></textarea>
                </div>

                <div class="input-box">
                    <label>Category <span class="required">*</span></label>
                    <select name="category_id" required id="ad-category">
                        <option value="">Select Category</option>
                        <?php foreach($all_categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="input-box">
                    <label>Condition <span class="required">*</span></label>
                    <select name="condition_type" required id="ad-condition">
                        <option value="">Select Condition</option>
                        <option value="new">Brand New</option>
                        <option value="used">Used</option>
                    </select>
                </div>

                <div class="input-box">
                    <label>Price (PKR) <span class="required">*</span></label>
                    <input type="number" name="price" placeholder="e.g. 50000" required min="1" id="ad-price">
                </div>
                
                <div class="input-box">
                    <label>Location <span class="required">*</span></label>
                    <input type="text" name="location" placeholder="e.g. DHA Phase 5, Lahore" required id="ad-location">
                </div>

                <div class="input-box full-width">
                    <label>Upload Photos (up to 5)</label>
                    <div class="photo-upload">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Click to upload photos</span>
                        <small>JPG, PNG or WEBP • Max 5MB each</small>
                        <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" style="position:absolute; inset:0; opacity:0; cursor:pointer;" id="ad-images">
                    </div>
                </div>

            </div>

            <div class="submit-wrap">
                <div class="tip">
                    <i class="fas fa-lightbulb"></i> Ads with clear photos and detailed descriptions sell faster!
                </div>
                <button type="submit" class="btn-sell" style="padding: 14px 35px; font-size:15px;" id="ad-submit">
                    <i class="fas fa-paper-plane"></i> Post Now
                </button>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
