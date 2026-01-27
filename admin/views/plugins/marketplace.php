<?php
/**
 * Plugin Marketplace View
 */
?><div class="plugin-marketplace">
    <h1>Plugin Marketplace</h1>
    
    <div class="marketplace-filters">
        <select class="form-control" id="category-filter">
            <option value="">All Categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo htmlspecialchars($category['id']); ?>">
                    <?php echo htmlspecialchars($category['name']);  ?>
                </option>
            <?php endforeach;  ?>
        </select>
        
        <select class="form-control" id="sort-filter">
            <option value="popular">Most Popular</option>
            <option value="newest">Newest</option>
            <option value="rating">Highest Rated</option>
        </select>
    </div>

    <div class="plugin-grid">
        <?php foreach ($plugins as $plugin): ?>
            <div class="plugin-card" data-categories="<?php echo htmlspecialchars(implode(',', $plugin['categories'])); ?>">
                <h3><?php echo htmlspecialchars($plugin['name']); ?></h3>
                <div class="plugin-meta">
                    <span class="version">v<?php echo htmlspecialchars($plugin['version']); ?></span>
                    <span class="author">by <?php echo htmlspecialchars($plugin['author']); ?></span>
                </div>
                
                <p class="description"><?php echo htmlspecialchars($plugin['description']); ?></p>
                
                <?php if (!empty($plugin['compatibility_errors'])): ?>
                    <div class="compatibility-warning">
                        <strong>Compatibility Issues:</strong>
                        <ul>
                            <?php foreach ($plugin['compatibility_errors'] as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach;  ?>
                        </ul>
                    </div>
                <?php endif;  ?>
                <div class="plugin-actions">
                    <button class="btn btn-primary install-btn" 
                            data-plugin-id="<?php echo htmlspecialchars($plugin['id']); ?>">
                        Install
                    </button>
                </div>
            </div>
        <?php endforeach;  ?>
    </div>
</div>

<script>
// Client-side filtering and sorting
document.addEventListener('DOMContentLoaded', function() {
    const categoryFilter = document.getElementById('category-filter');
    const sortFilter = document.getElementById('sort-filter');
    const pluginCards = document.querySelectorAll('.plugin-card');
    
    function updateFilters() {
        const selectedCategory = categoryFilter.value;
        const sortBy = sortFilter.value;
        
        // Filter by category
        pluginCards.forEach(card => {
            const cardCategories = card.dataset.categories.split(',');
            const showCard = !selectedCategory || cardCategories.includes(selectedCategory);
            card.style.display = showCard ? 'block' : 'none';
        });
        
        // TODO: Implement sorting
    }
    
    categoryFilter.addEventListener('change', updateFilters);
    sortFilter.addEventListener('change', updateFilters);
});
</script>