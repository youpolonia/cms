<?php
/**
 * Plugin Marketplace Core Class
 * Handles product listing, cart management, purchases and license generation
 */
class PluginMarketplace {
    private static $instance = null;
    private $products = [];
    private $cart = [];

    private function __construct() {
        $this->initializeProducts();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeProducts() {
        $this->products = [
            [
                'id' => 'plugin1',
                'name' => 'SEO Optimizer',
                'description' => 'Advanced SEO tools for content optimization',
                'price' => 49.99,
                'version' => '1.0.0'
            ],
            [
                'id' => 'plugin2',
                'name' => 'Form Builder',
                'description' => 'Drag & drop form builder with submissions',
                'price' => 29.99,
                'version' => '2.1.3'
            ],
            [
                'id' => 'plugin3',
                'name' => 'Analytics Dashboard',
                'description' => 'Enhanced analytics and reporting',
                'price' => 79.99,
                'version' => '1.5.2'
            ]
        ];
    }

    public function getProducts() {
        return $this->products;
    }

    public function addToCart($productId) {
        if (!isset($_SESSION['marketplace_cart'])) {
            $_SESSION['marketplace_cart'] = [];
        }

        $product = $this->findProductById($productId);
        if ($product) {
            $_SESSION['marketplace_cart'][] = $product;
            return true;
        }
        return false;
    }

    public function getCart() {
        return $_SESSION['marketplace_cart'] ?? [];
    }

    public function clearCart() {
        unset($_SESSION['marketplace_cart']);
    }

    public function processPurchase() {
        $cart = $this->getCart();
        if (empty($cart)) {
            return false;
        }

        // Simulate payment processing
        $total = array_sum(array_column($cart, 'price'));
        
        // Generate license keys
        $licenses = [];
        foreach ($cart as $item) {
            $licenses[] = $this->generateLicenseKey($item);
        }

        $this->clearCart();
        return [
            'success' => true,
            'total' => $total,
            'licenses' => $licenses
        ];
    }

    private function generateLicenseKey($product) {
        $data = $product['id'] . $product['version'] . time();
        return 'LIC-' . md5($data);
    }

    private function findProductById($id) {
        foreach ($this->products as $product) {
            if ($product['id'] === $id) {
                return $product;
            }
        }
        return null;
    }
}
