<?php
/**
 * Model Tests
 * Tests for BaseModel and specific model classes
 */

require_once __DIR__ . '/TestRunner.php';
require_once __DIR__ . '/../models/BaseModel.php';
require_once __DIR__ . '/../models/ArticleModel.php';
require_once __DIR__ . '/../models/PageModel.php';
require_once __DIR__ . '/../models/MediaModel.php';
require_once __DIR__ . '/../models/MenuModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';

$runner = new TestRunner();

// Test BaseModel via ArticleModel
$runner->addTest('BaseModel create returns ID', function () {
    $id = ArticleModel::create([
        'title' => '_test_model_create',
        'slug' => '_test_model_create_slug',
        'content' => 'Test content',
        'status' => 'draft',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    TestRunner::assert(is_int($id) && $id > 0, 'Create should return positive integer ID');
    
    // Cleanup
    ArticleModel::delete($id);
});

$runner->addTest('BaseModel find returns record', function () {
    $id = ArticleModel::create([
        'title' => '_test_model_find',
        'slug' => '_test_model_find_slug',
        'content' => 'Test content for find',
        'status' => 'draft',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $article = ArticleModel::find($id);
    
    TestRunner::assert(is_array($article), 'Find should return array');
    TestRunner::assertEquals('_test_model_find', $article['title'], 'Title should match');
    TestRunner::assertEquals($id, $article['id'], 'ID should match');
    
    // Cleanup
    ArticleModel::delete($id);
});

$runner->addTest('BaseModel find returns null for non-existent ID', function () {
    $article = ArticleModel::find(999999999);
    TestRunner::assert($article === null, 'Find should return null for non-existent ID');
});

$runner->addTest('BaseModel findBy returns record', function () {
    $id = ArticleModel::create([
        'title' => '_test_model_findby',
        'slug' => '_test_model_findby_unique_slug',
        'content' => 'Test content for findBy',
        'status' => 'draft',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $article = ArticleModel::findBy('slug', '_test_model_findby_unique_slug');
    
    TestRunner::assert(is_array($article), 'FindBy should return array');
    TestRunner::assertEquals('_test_model_findby', $article['title'], 'Title should match');
    
    // Cleanup
    ArticleModel::delete($id);
});

$runner->addTest('BaseModel update modifies record', function () {
    $id = ArticleModel::create([
        'title' => '_test_model_update_original',
        'slug' => '_test_model_update_slug',
        'content' => 'Original content',
        'status' => 'draft',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $result = ArticleModel::update($id, [
        'title' => '_test_model_update_modified',
        'content' => 'Modified content'
    ]);
    
    TestRunner::assert($result === true, 'Update should return true');
    
    $article = ArticleModel::find($id);
    TestRunner::assertEquals('_test_model_update_modified', $article['title'], 'Title should be updated');
    TestRunner::assertEquals('Modified content', $article['content'], 'Content should be updated');
    
    // Cleanup
    ArticleModel::delete($id);
});

$runner->addTest('BaseModel delete removes record', function () {
    $id = ArticleModel::create([
        'title' => '_test_model_delete',
        'slug' => '_test_model_delete_slug',
        'content' => 'Test content for delete',
        'status' => 'draft',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $result = ArticleModel::delete($id);
    TestRunner::assert($result === true, 'Delete should return true');
    
    $article = ArticleModel::find($id);
    TestRunner::assert($article === null, 'Record should be deleted');
});

$runner->addTest('BaseModel all returns array', function () {
    // Create test records
    $id1 = ArticleModel::create([
        'title' => '_test_model_all_1',
        'slug' => '_test_model_all_slug_1',
        'content' => 'Test content 1',
        'status' => 'published',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $id2 = ArticleModel::create([
        'title' => '_test_model_all_2',
        'slug' => '_test_model_all_slug_2',
        'content' => 'Test content 2',
        'status' => 'draft',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $all = ArticleModel::all(['status' => 'published']);
    TestRunner::assert(is_array($all), 'All should return array');
    
    $found = false;
    foreach ($all as $article) {
        if ($article['title'] === '_test_model_all_1') {
            $found = true;
            break;
        }
    }
    TestRunner::assert($found, 'Should find published test article');
    
    // Cleanup
    ArticleModel::delete($id1);
    ArticleModel::delete($id2);
});

$runner->addTest('BaseModel count returns integer', function () {
    // Create test records
    $id1 = ArticleModel::create([
        'title' => '_test_model_count_1',
        'slug' => '_test_model_count_slug_1',
        'content' => 'Test content 1',
        'status' => 'published',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $id2 = ArticleModel::create([
        'title' => '_test_model_count_2',
        'slug' => '_test_model_count_slug_2',
        'content' => 'Test content 2',
        'status' => 'published',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $count = ArticleModel::count(['status' => 'published']);
    TestRunner::assert(is_int($count), 'Count should return integer');
    TestRunner::assert($count >= 2, 'Should count at least our test records');
    
    // Cleanup
    ArticleModel::delete($id1);
    ArticleModel::delete($id2);
});

// Test ArticleModel specific methods
$runner->addTest('ArticleModel findBySlug works', function () {
    $id = ArticleModel::create([
        'title' => '_test_article_slug',
        'slug' => '_test_article_unique_slug_123',
        'content' => 'Test content for slug',
        'status' => 'published',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $article = ArticleModel::findBySlug('_test_article_unique_slug_123');
    TestRunner::assert(is_array($article), 'FindBySlug should return array');
    TestRunner::assertEquals('_test_article_slug', $article['title'], 'Should find correct article');
    
    // Cleanup
    ArticleModel::delete($id);
});

$runner->addTest('ArticleModel published returns published articles', function () {
    // Create test records
    $id1 = ArticleModel::create([
        'title' => '_test_article_published',
        'slug' => '_test_article_published_slug',
        'content' => 'Published content',
        'status' => 'published',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $id2 = ArticleModel::create([
        'title' => '_test_article_draft',
        'slug' => '_test_article_draft_slug',
        'content' => 'Draft content',
        'status' => 'draft',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $published = ArticleModel::published();
    TestRunner::assert(is_array($published), 'Published should return array');
    
    // Check that all returned articles are published
    foreach ($published as $article) {
        if (strpos($article['title'], '_test_article_') === 0) {
            TestRunner::assertEquals('published', $article['status'], 'All returned articles should be published');
        }
    }
    
    // Cleanup
    ArticleModel::delete($id1);
    ArticleModel::delete($id2);
});

// Test PageModel specific methods
$runner->addTest('PageModel findBySlug works', function () {
    $id = PageModel::create([
        'title' => '_test_page_slug',
        'slug' => '_test_page_unique_slug_123',
        'content' => 'Test page content',
        'status' => 'published',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $page = PageModel::findBySlug('_test_page_unique_slug_123');
    TestRunner::assert(is_array($page), 'PageModel findBySlug should return array');
    TestRunner::assertEquals('_test_page_slug', $page['title'], 'Should find correct page');
    
    // Cleanup
    PageModel::delete($id);
});

$runner->addTest('PageModel published returns published pages', function () {
    // Create test records
    $id1 = PageModel::create([
        'title' => '_test_page_published',
        'slug' => '_test_page_published_slug',
        'content' => 'Published page content',
        'status' => 'published',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $id2 = PageModel::create([
        'title' => '_test_page_draft',
        'slug' => '_test_page_draft_slug',
        'content' => 'Draft page content',
        'status' => 'draft',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $published = PageModel::published();
    TestRunner::assert(is_array($published), 'Published pages should return array');
    
    // Check that all returned pages are published
    foreach ($published as $page) {
        if (strpos($page['title'], '_test_page_') === 0) {
            TestRunner::assertEquals('published', $page['status'], 'All returned pages should be published');
        }
    }
    
    // Cleanup
    PageModel::delete($id1);
    PageModel::delete($id2);
});

// Test MediaModel specific methods
$runner->addTest('MediaModel findByFilename works', function () {
    // Skip if media table doesn't exist
    try {
        $id = MediaModel::create([
            'filename' => '_test_media_file_123.jpg',
            'original_name' => 'test_image.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 12345,
            'width' => 100,
            'height' => 100,
            'size' => 12345,
            'path' => '/uploads/_test_media_file_123.jpg',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $media = MediaModel::findByFilename('_test_media_file_123.jpg');
        TestRunner::assert(is_array($media), 'MediaModel findByFilename should return array');
        TestRunner::assertEquals('test_image.jpg', $media['original_name'], 'Should find correct media');
        
        // Cleanup
        MediaModel::delete($id);
    } catch (Exception $e) {
        if (strpos($e->getMessage(), "doesn't exist") !== false) {
            TestRunner::assert(true, 'Media table does not exist - skipping test');
        } else {
            throw $e;
        }
    }
});

$runner->run();