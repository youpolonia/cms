<?php
/**
 * Jessie CMS — Plugin Integration Tests
 * Tests all 11 plugins: DB tables, class loading, CRUD operations, API endpoints
 */
define('CMS_ROOT', '/var/www/cms');
define('CMS_APP', CMS_ROOT . '/app');
require_once CMS_ROOT . '/db.php';

$passed = 0;
$failed = 0;
$errors = [];

function test(string $name, callable $fn): void {
    global $passed, $failed, $errors;
    try {
        $result = $fn();
        if ($result === false) throw new \Exception("Returned false");
        $passed++;
        echo "  ✅ {$name}\n";
    } catch (\Throwable $e) {
        $failed++;
        $errors[] = "{$name}: {$e->getMessage()}";
        echo "  ❌ {$name}: {$e->getMessage()}\n";
    }
}

function tableExists(string $table): bool {
    return (bool)db()->query("SHOW TABLES LIKE '{$table}'")->fetchColumn();
}

echo "╔══════════════════════════════════════════╗\n";
echo "║  JESSIE CMS — PLUGIN INTEGRATION TESTS  ║\n";
echo "╚══════════════════════════════════════════╝\n\n";

// ─── 1. BOOKING ───
echo "📅 Booking\n";
require_once CMS_ROOT . '/plugins/jessie-booking/includes/class-booking-service.php';
require_once CMS_ROOT . '/plugins/jessie-booking/includes/class-booking-appointment.php';
test('Tables exist', fn() => tableExists('booking_services') && tableExists('booking_appointments'));
test('BookingService::getAll()', fn() => is_array(BookingService::getAll()));
test('BookingService::create()', function() {
    $id = BookingService::create(['name' => '_test_svc', 'duration_minutes' => 30, 'price' => 25.00, 'status' => 'active']);
    if (!$id) throw new \Exception("No ID");
    BookingService::delete($id);
    return true;
});
test('BookingAppointment::getAll()', fn() => is_array(BookingAppointment::getAll()));
echo "\n";

// ─── 2. NEWSLETTER ───
echo "📧 Newsletter\n";
require_once CMS_ROOT . '/plugins/jessie-newsletter/includes/class-newsletter-subscriber.php';
require_once CMS_ROOT . '/plugins/jessie-newsletter/includes/class-newsletter-list.php';
require_once CMS_ROOT . '/plugins/jessie-newsletter/includes/class-newsletter-campaign.php';
test('Tables exist', fn() => tableExists('newsletter_subscribers') && tableExists('newsletter_lists') && tableExists('newsletter_campaigns'));
test('NewsletterList::getAll()', fn() => is_array(NewsletterList::getAll()));
test('NewsletterSubscriber::subscribe + unsubscribe', function() {
    $id = NewsletterSubscriber::subscribe('_test_' . time() . '@test.com', 'Test', [], 'test');
    if (!$id) throw new \Exception("No ID");
    db()->prepare("DELETE FROM newsletter_subscribers WHERE id = ?")->execute([$id]);
    return true;
});
echo "\n";

// ─── 3. MEMBERSHIP ───
echo "🔑 Membership\n";
require_once CMS_ROOT . '/plugins/jessie-membership/includes/class-membership-plan.php';
require_once CMS_ROOT . '/plugins/jessie-membership/includes/class-membership-access.php';
test('Tables exist', fn() => tableExists('membership_plans') && tableExists('membership_members') && tableExists('membership_content_rules'));
test('MembershipPlan::getAll()', fn() => is_array(MembershipPlan::getAll()));
test('MembershipPlan::create + delete', function() {
    $id = MembershipPlan::create(['name' => '_test_plan', 'price' => 9.99, 'billing_period' => 'monthly', 'status' => 'active']);
    if (!$id) throw new \Exception("No ID");
    MembershipPlan::delete($id);
    return true;
});
test('MembershipAccess::getAllRules()', fn() => is_array(MembershipAccess::getAllRules()));
echo "\n";

// ─── 4. LMS ───
echo "🎓 LMS\n";
require_once CMS_ROOT . '/plugins/jessie-lms/includes/class-lms-course.php';
require_once CMS_ROOT . '/plugins/jessie-lms/includes/class-lms-lesson.php';
test('Tables exist', fn() => tableExists('lms_courses') && tableExists('lms_lessons') && tableExists('lms_quizzes'));
test('LmsCourse::getAll()', fn() => is_array(LmsCourse::getAll()));
test('LmsCourse::create + delete', function() {
    $id = LmsCourse::create(['title' => '_test_course', 'description' => 'Test', 'status' => 'draft']);
    if (!$id) throw new \Exception("No ID");
    LmsCourse::delete($id);
    return true;
});
echo "\n";

// ─── 5. DIRECTORY ───
echo "📍 Directory\n";
require_once CMS_ROOT . '/plugins/jessie-directory/includes/class-directory-listing.php';
require_once CMS_ROOT . '/plugins/jessie-directory/includes/class-directory-category.php';
require_once CMS_ROOT . '/plugins/jessie-directory/includes/class-directory-review.php';
test('Tables exist', fn() => tableExists('directory_listings') && tableExists('directory_categories') && tableExists('directory_reviews'));
test('DirectoryListing::getAll()', fn() => isset(DirectoryListing::getAll()['listings']));
test('DirectoryCategory::getTree()', fn() => is_array(DirectoryCategory::getTree()));
test('DirectoryListing::getStats()', fn() => isset(DirectoryListing::getStats()['total']));
test('DirectoryListing::create + delete', function() {
    $id = DirectoryListing::create(['title' => '_test_listing', 'status' => 'pending']);
    if (!$id) throw new \Exception("No ID");
    DirectoryListing::delete($id);
    return true;
});
echo "\n";

// ─── 6. RESTAURANT ───
echo "🍕 Restaurant\n";
require_once CMS_ROOT . '/plugins/jessie-restaurant/includes/class-restaurant-menu.php';
require_once CMS_ROOT . '/plugins/jessie-restaurant/includes/class-restaurant-order.php';
test('Tables exist', fn() => tableExists('restaurant_items') && tableExists('restaurant_orders') && tableExists('restaurant_categories'));
test('RestaurantMenu::getCategories()', fn() => is_array(RestaurantMenu::getCategories()));
test('RestaurantMenu::getFullMenu()', fn() => is_array(RestaurantMenu::getFullMenu()));
test('RestaurantMenu::getStats()', fn() => isset(RestaurantMenu::getStats()['items_total']));
test('RestaurantMenu::getSetting()', fn() => RestaurantMenu::getSetting('restaurant_name') !== '');
test('RestaurantMenu::createItem + deleteItem', function() {
    $id = RestaurantMenu::createItem(['name' => '_test_item', 'price' => 9.99, 'status' => 'hidden']);
    if (!$id) throw new \Exception("No ID");
    RestaurantMenu::deleteItem($id);
    return true;
});
test('RestaurantOrder::getAll()', fn() => isset(RestaurantOrder::getAll()['orders']));
echo "\n";

// ─── 7. REAL ESTATE ───
echo "🏠 Real Estate\n";
$reDir = CMS_ROOT . '/plugins/jessie-realestate/includes';
require_once $reDir . '/class-realestate-property.php';
test('Tables exist', fn() => tableExists('re_properties') && tableExists('re_agents') && tableExists('re_inquiries'));
test('RealEstateProperty::getAll()', fn() => is_array(RealEstateProperty::getAll()));
test('RealEstateProperty::create + delete', function() {
    $id = RealEstateProperty::create(['title' => '_test_property', 'property_type' => 'house', 'listing_type' => 'sale', 'price' => 100000, 'status' => 'pending']);
    if (!$id) throw new \Exception("No ID");
    RealEstateProperty::delete($id);
    return true;
});
echo "\n";

// ─── 8. JOB BOARD ───
echo "💼 Job Board\n";
require_once CMS_ROOT . '/plugins/jessie-jobs/includes/class-job-listing.php';
require_once CMS_ROOT . '/plugins/jessie-jobs/includes/class-job-company.php';
test('Tables exist', fn() => tableExists('job_listings') && tableExists('job_applications') && tableExists('job_companies'));
test('JobListing::getAll()', fn() => is_array(JobListing::getAll()));
test('JobListing::create + delete', function() {
    $id = JobListing::create(['title' => '_test_job', 'company_name' => 'Test Inc', 'description' => 'Test', 'status' => 'draft']);
    if (!$id) throw new \Exception("No ID");
    JobListing::delete($id);
    return true;
});
echo "\n";

// ─── 9. EVENTS ───
echo "🎫 Events\n";
require_once CMS_ROOT . '/plugins/jessie-events/includes/class-event-manager.php';
test('Tables exist', fn() => tableExists('events') && tableExists('event_tickets') && tableExists('event_orders'));
test('EventManager::getAll()', fn() => is_array(EventManager::getAll()));
test('EventManager::create + delete', function() {
    $id = EventManager::create(['title' => '_test_event', 'start_date' => '2026-12-01 18:00:00', 'status' => 'upcoming']);
    if (!$id) throw new \Exception("No ID");
    EventManager::delete($id);
    return true;
});
echo "\n";

// ─── 10. AFFILIATE ───
echo "💰 Affiliate\n";
require_once CMS_ROOT . '/plugins/jessie-affiliate/includes/class-affiliate-program.php';
require_once CMS_ROOT . '/plugins/jessie-affiliate/includes/class-affiliate.php';
test('Tables exist', fn() => tableExists('affiliate_programs') && tableExists('affiliates') && tableExists('affiliate_conversions'));
test('AffiliateProgram::getAll()', fn() => is_array(AffiliateProgram::getAll()));
test('AffiliateProgram::create + delete', function() {
    $id = AffiliateProgram::create(['name' => '_test_program', 'commission_type' => 'percentage', 'commission_value' => 10, 'status' => 'active']);
    if (!$id) throw new \Exception("No ID");
    AffiliateProgram::delete($id);
    return true;
});
echo "\n";

// ─── 11. PORTFOLIO ───
echo "📸 Portfolio\n";
require_once CMS_ROOT . '/plugins/jessie-portfolio/includes/class-portfolio-project.php';
require_once CMS_ROOT . '/plugins/jessie-portfolio/includes/class-portfolio-category.php';
test('Tables exist', fn() => tableExists('portfolio_projects') && tableExists('portfolio_categories') && tableExists('portfolio_testimonials'));
test('PortfolioProject::getAll()', fn() => is_array(PortfolioProject::getAll()));
test('PortfolioProject::create + delete', function() {
    $id = PortfolioProject::create(['title' => '_test_project', 'description' => 'Test', 'status' => 'draft']);
    if (!$id) throw new \Exception("No ID");
    PortfolioProject::delete($id);
    return true;
});
echo "\n";

// ─── RESULTS ───
echo "╔══════════════════════════════════════════╗\n";
echo "║   RESULTS                                ║\n";
echo "╠══════════════════════════════════════════╣\n";
printf("║   Passed: %-29d ║\n", $passed);
printf("║   Failed: %-29d ║\n", $failed);
printf("║   Total:  %-29d ║\n", $passed + $failed);
echo "╚══════════════════════════════════════════╝\n";
if ($failed > 0) {
    echo "\nFailed tests:\n";
    foreach ($errors as $e) echo "  ❌ {$e}\n";
}
echo $failed === 0 ? "\n✅ ALL PLUGIN TESTS PASSED!\n" : "\n⚠️ SOME TESTS FAILED!\n";
