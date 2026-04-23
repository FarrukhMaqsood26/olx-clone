<?php
require_once '../includes/config.php';
require_once 'includes/header.php';

$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM ads WHERE category_id = c.id) as ad_count FROM categories c ORDER BY c.name ASC")->fetchAll();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Categories</h2>
    <p class="text-slate-500 mt-1">Manage marketplace structure.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-[1fr_350px] gap-6 items-start">
    <!-- Categories List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 w-16 text-center">Icon</th>
                        <th class="px-6 py-4">Name</th>
                        <th class="px-6 py-4">Slug</th>
                        <th class="px-6 py-4 text-center">Ads</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($categories as $cat): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 text-center">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg mx-auto">
                                <i class="fas <?= htmlspecialchars($cat['icon']) ?>"></i>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-800"><?= htmlspecialchars($cat['name']) ?></td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-400 bg-slate-50/50 rounded"><?= htmlspecialchars($cat['slug']) ?></td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex min-w-[2rem] items-center justify-center px-2 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-bold"><?= $cat['ad_count'] ?></span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <form action="../api/admin_actions.php?action=delete_category" method="POST" onsubmit="return confirm('Delete this category? Cannot be undone. Fails if ads exist.');" class="inline">
                                <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                <button class="w-8 h-8 rounded-full bg-slate-100 hover:bg-red-500 text-slate-500 hover:text-white transition inline-flex items-center justify-center shadow-sm" title="Delete Category">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Category Form -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden lg:sticky lg:top-6">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="font-bold text-slate-800">Add Category</h3>
        </div>
        <div class="p-6">
            <form action="../api/admin_actions.php?action=add_category" method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Category Name</label>
                    <input type="text" name="name" required placeholder="e.g. Laptops" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition text-sm">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">URL Slug (Unique)</label>
                    <input type="text" name="slug" required placeholder="e.g. laptops" pattern="^[a-z0-9-]+$" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition text-sm font-mono text-slate-600">
                    <p class="text-xs text-slate-400 mt-1.5"><i class="fas fa-info-circle"></i> Lowercase letters, numbers, and hyphens only.</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">FontAwesome Icon Class</label>
                    <input type="text" name="icon" placeholder="e.g. fa-laptop" value="fa-cube" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-accent/20 focus:border-accent outline-none transition text-sm font-mono text-slate-600">
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-4 rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                        <i class="fas fa-plus"></i> Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
