<?php
declare(strict_types=1);

/**
 * ShopInvoice — Pure PHP PDF invoice generation
 * No external libraries. Uses raw PDF specification with built-in Helvetica font.
 */
class ShopInvoice
{
    private static array $objects = [];
    private static int $objectCount = 0;

    /**
     * Generate PDF binary string for an order
     */
    public static function generate(int $orderId): string
    {
        require_once CMS_ROOT . '/core/shop.php';
        $order = \Shop::getOrder($orderId);
        if (!$order) {
            throw new \RuntimeException("Order #{$orderId} not found.");
        }

        $items = json_decode($order['items'] ?? '[]', true);
        if (!is_array($items)) $items = [];

        $billing = json_decode($order['billing_address'] ?? '{}', true);
        if (!is_array($billing)) $billing = [];

        // Company info from settings
        $companyName = get_setting('company_name', get_setting('shop_name', 'Our Company'));
        $companyAddress = get_setting('company_address', '');
        $companyTaxId = get_setting('company_tax_id', '');
        $companyEmail = get_setting('company_email', get_setting('shop_notification_email', ''));
        $companyPhone = get_setting('company_phone', '');

        $currency = $order['currency'] ?? get_setting('shop_currency', 'USD');
        $symbols = ['USD' => '$', 'EUR' => "\xe2\x82\xac", 'GBP' => "\xc2\xa3", 'PLN' => 'PLN '];
        $sym = $symbols[$currency] ?? $currency . ' ';

        // Reset state
        self::$objects = [];
        self::$objectCount = 0;

        // Build content stream
        $content = self::buildInvoiceContent($order, $items, $billing, $companyName, $companyAddress, $companyTaxId, $companyEmail, $companyPhone, $sym);

        return self::buildPdf($content);
    }

    /**
     * Stream PDF to browser with proper headers
     */
    public static function stream(int $orderId): void
    {
        require_once CMS_ROOT . '/core/shop.php';
        $order = \Shop::getOrder($orderId);
        $pdf = self::generate($orderId);

        $orderNumber = $order['order_number'] ?? 'unknown';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="invoice-' . $orderNumber . '.pdf"');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: private, max-age=0, must-revalidate');
        echo $pdf;
    }

    // ─── PDF BUILDING ───

    private static function buildPdf(string $contentStream): string
    {
        self::$objects = [];
        self::$objectCount = 0;

        // Object 1: Catalog
        $catalogId = self::addObject("<< /Type /Catalog /Pages 2 0 R >>");

        // Object 2: Pages
        $pagesId = self::addObject("<< /Type /Pages /Kids [3 0 R] /Count 1 >>");

        // Object 3: Page
        $pageId = self::addObject("<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 5 0 R /Resources << /Font << /F1 4 0 R /F2 6 0 R >> >> >>");

        // Object 4: Helvetica font (regular)
        $fontId = self::addObject("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>");

        // Object 5: Content stream
        $streamData = $contentStream;
        $streamLen = strlen($streamData);
        $contentId = self::addObject("<< /Length {$streamLen} >>\nstream\n{$streamData}\nendstream");

        // Object 6: Helvetica-Bold font
        $fontBoldId = self::addObject("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>");

        // Build PDF file
        $pdf = "%PDF-1.4\n%\xe2\xe3\xcf\xd3\n";
        $offsets = [];

        foreach (self::$objects as $idx => $obj) {
            $offsets[$idx] = strlen($pdf);
            $objNum = $idx + 1;
            $pdf .= "{$objNum} 0 obj\n{$obj}\nendobj\n";
        }

        // Cross-reference table
        $xrefOffset = strlen($pdf);
        $numObjects = count(self::$objects) + 1; // +1 for free entry
        $pdf .= "xref\n0 {$numObjects}\n";
        $pdf .= "0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        // Trailer
        $pdf .= "trailer\n<< /Size {$numObjects} /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private static function addObject(string $content): int
    {
        self::$objectCount++;
        self::$objects[] = $content;
        return self::$objectCount;
    }

    private static function buildInvoiceContent(
        array $order,
        array $items,
        array $billing,
        string $companyName,
        string $companyAddress,
        string $companyTaxId,
        string $companyEmail,
        string $companyPhone,
        string $sym
    ): string {
        $s = '';
        $pageW = 595;
        $pageH = 842;
        $marginL = 50;
        $marginR = 50;
        $contentW = $pageW - $marginL - $marginR;

        $y = $pageH - 60;

        // === HEADER ===
        // "INVOICE" title
        $s .= self::pdfText($marginL, $y, 'INVOICE', 24, true);

        // Company info (right-aligned)
        $companyLines = array_filter([$companyName, $companyAddress, $companyTaxId ? 'Tax ID: ' . $companyTaxId : '', $companyEmail, $companyPhone]);
        $rightX = $pageW - $marginR;
        $cy = $y;
        foreach ($companyLines as $line) {
            // Split multi-line address
            foreach (explode("\n", $line) as $subLine) {
                $subLine = trim($subLine);
                if ($subLine === '') continue;
                $textWidth = self::estimateWidth($subLine, 9);
                $s .= self::pdfText($rightX - $textWidth, $cy, $subLine, 9);
                $cy -= 13;
            }
        }

        $y -= 40;

        // Invoice number and date
        $s .= self::pdfLine($marginL, $y, $pageW - $marginR, $y);
        $y -= 18;
        $s .= self::pdfText($marginL, $y, 'Invoice #: ' . self::safe($order['order_number'] ?? ''), 10, true);
        $s .= self::pdfText(300, $y, 'Date: ' . date('M j, Y', strtotime($order['created_at'] ?? 'now')), 10);
        $y -= 14;
        $s .= self::pdfText($marginL, $y, 'Payment: ' . ucfirst(self::safe($order['payment_status'] ?? 'unpaid')), 10);
        $s .= self::pdfText(300, $y, 'Method: ' . ucfirst(self::safe($order['payment_method'] ?? 'N/A')), 10);

        $y -= 30;

        // === CUSTOMER INFO ===
        $s .= self::pdfText($marginL, $y, 'Bill To:', 11, true);
        $y -= 16;

        $customerName = $order['customer_name'] ?? 'N/A';
        $customerEmail = $order['customer_email'] ?? '';
        $customerPhone = $order['customer_phone'] ?? '';
        $addressParts = array_filter([
            $billing['line1'] ?? '',
            $billing['line2'] ?? '',
            implode(', ', array_filter([$billing['city'] ?? '', $billing['state'] ?? '', $billing['zip'] ?? ''])),
            $billing['country'] ?? '',
        ]);

        $s .= self::pdfText($marginL, $y, self::safe($customerName), 10);
        $y -= 14;
        if ($customerEmail) {
            $s .= self::pdfText($marginL, $y, self::safe($customerEmail), 9);
            $y -= 13;
        }
        if ($customerPhone) {
            $s .= self::pdfText($marginL, $y, self::safe($customerPhone), 9);
            $y -= 13;
        }
        foreach ($addressParts as $part) {
            if ($part === '') continue;
            $s .= self::pdfText($marginL, $y, self::safe($part), 9);
            $y -= 13;
        }

        $y -= 20;

        // === ITEMS TABLE ===
        // Table header
        $colX = [$marginL, $marginL + 30, $marginL + 280, $marginL + 330, $marginL + 400];
        $tableRight = $pageW - $marginR;

        // Header background
        $s .= self::pdfRect($marginL, $y - 4, $contentW, 18, true);
        $s .= "1 1 1 rg\n"; // white text for header
        $s .= self::pdfText($colX[0], $y, '#', 9, true);
        $s .= self::pdfText($colX[1], $y, 'Product', 9, true);
        $s .= self::pdfText($colX[2], $y, 'Qty', 9, true);
        $s .= self::pdfText($colX[3], $y, 'Unit Price', 9, true);
        $s .= self::pdfText($colX[4], $y, 'Total', 9, true);
        $s .= "0 0 0 rg\n"; // back to black

        $y -= 22;

        // Table rows
        $num = 0;
        foreach ($items as $item) {
            $num++;
            if ($y < 100) break; // prevent overflow

            // Zebra stripe
            if ($num % 2 === 0) {
                $s .= "0.95 0.95 0.95 rg\n";
                $s .= self::pdfRect($marginL, $y - 4, $contentW, 16, true);
                $s .= "0 0 0 rg\n";
            }

            $itemName = self::safe($item['name'] ?? 'Unknown');
            // Truncate long names
            if (strlen($itemName) > 40) {
                $itemName = substr($itemName, 0, 37) . '...';
            }

            $qty = (int)($item['quantity'] ?? 1);
            $price = (float)($item['price'] ?? 0);
            $lineTotal = (float)($item['line_total'] ?? 0);

            $s .= self::pdfText($colX[0], $y, (string)$num, 9);
            $s .= self::pdfText($colX[1], $y, $itemName, 9);
            $s .= self::pdfText($colX[2], $y, (string)$qty, 9);
            $s .= self::pdfText($colX[3], $y, self::safe($sym) . number_format($price, 2), 9);
            $s .= self::pdfText($colX[4], $y, self::safe($sym) . number_format($lineTotal, 2), 9);

            $y -= 18;
        }

        // Line under items
        $s .= self::pdfLine($marginL, $y + 4, $pageW - $marginR, $y + 4);
        $y -= 20;

        // === TOTALS ===
        $totalsX = 350;
        $totalsValX = $colX[4];

        $s .= self::pdfText($totalsX, $y, 'Subtotal:', 10);
        $s .= self::pdfText($totalsValX, $y, self::safe($sym) . number_format((float)($order['subtotal'] ?? 0), 2), 10);
        $y -= 16;

        if ((float)($order['tax'] ?? 0) > 0) {
            $s .= self::pdfText($totalsX, $y, 'Tax:', 10);
            $s .= self::pdfText($totalsValX, $y, self::safe($sym) . number_format((float)$order['tax'], 2), 10);
            $y -= 16;
        }

        if ((float)($order['shipping'] ?? 0) > 0) {
            $s .= self::pdfText($totalsX, $y, 'Shipping:', 10);
            $s .= self::pdfText($totalsValX, $y, self::safe($sym) . number_format((float)$order['shipping'], 2), 10);
            $y -= 16;
        }

        if ((float)($order['discount'] ?? 0) > 0) {
            $s .= self::pdfText($totalsX, $y, 'Discount:', 10);
            $s .= self::pdfText($totalsValX, $y, '-' . self::safe($sym) . number_format((float)$order['discount'], 2), 10);
            $y -= 16;
        }

        // Total line
        $s .= self::pdfLine($totalsX, $y + 4, $pageW - $marginR, $y + 4);
        $y -= 4;
        $s .= self::pdfText($totalsX, $y, 'TOTAL:', 13, true);
        $s .= self::pdfText($totalsValX, $y, self::safe($sym) . number_format((float)($order['total'] ?? 0), 2), 13, true);

        // === FOOTER ===
        $y -= 50;
        if ($y < 80) $y = 80;
        $s .= self::pdfLine($marginL, $y, $pageW - $marginR, $y);
        $y -= 18;
        $s .= self::pdfText($marginL, $y, 'Thank you for your business!', 11);
        $y -= 14;
        $s .= self::pdfText($marginL, $y, 'Generated on ' . date('M j, Y'), 8);

        return $s;
    }

    // ─── PDF PRIMITIVES ───

    /**
     * Generate PDF text drawing commands
     */
    private static function pdfText(float $x, float $y, string $text, float $size = 10, bool $bold = false): string
    {
        $font = $bold ? '/F2' : '/F1';
        $escapedText = self::pdfEscapeString($text);
        return "BT {$font} {$size} Tf {$x} {$y} Td ({$escapedText}) Tj ET\n";
    }

    /**
     * Draw a line
     */
    private static function pdfLine(float $x1, float $y1, float $x2, float $y2): string
    {
        return "0.8 0.8 0.8 RG 0.5 w {$x1} {$y1} m {$x2} {$y2} l S 0 0 0 RG\n";
    }

    /**
     * Draw a rectangle (optionally filled)
     */
    private static function pdfRect(float $x, float $y, float $w, float $h, bool $fill = false): string
    {
        if ($fill) {
            return "0.2 0.24 0.35 rg {$x} {$y} {$w} {$h} re f 0 0 0 rg\n";
        }
        return "{$x} {$y} {$w} {$h} re S\n";
    }

    /**
     * Escape special characters for PDF string
     */
    private static function pdfEscapeString(string $text): string
    {
        // Convert UTF-8 to ASCII approximation for PDF Type1 fonts
        $text = self::utf8ToAscii($text);
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\\(', $text);
        $text = str_replace(')', '\\)', $text);
        $text = str_replace("\r", '', $text);
        $text = str_replace("\n", ' ', $text);
        return $text;
    }

    /**
     * Simple UTF-8 to ASCII conversion for PDF compatibility
     */
    private static function utf8ToAscii(string $text): string
    {
        $map = [
            "\xc2\xa3" => 'GBP ',  // £
            "\xe2\x82\xac" => 'EUR ', // €
            "\xc5\xbc" => 'z',
            "\xc5\xbb" => 'Z',
            "\xc4\x85" => 'a',
            "\xc4\x84" => 'A',
            "\xc5\x9b" => 's',
            "\xc5\x9a" => 'S',
            "\xc4\x87" => 'c',
            "\xc4\x86" => 'C',
            "\xc4\x99" => 'e',
            "\xc4\x98" => 'E',
            "\xc5\x82" => 'l',
            "\xc5\x81" => 'L',
            "\xc3\xb3" => 'o',
            "\xc3\x93" => 'O',
            "\xc5\xba" => 'z',
            "\xc5\xb9" => 'Z',
            "\xc5\x84" => 'n',
            "\xc5\x83" => 'N',
            "\xc3\xa9" => 'e',
            "\xc3\xa8" => 'e',
            "\xc3\xbc" => 'u',
            "\xc3\xb6" => 'o',
            "\xc3\xa4" => 'a',
            "\xc3\x9f" => 'ss',
        ];
        $text = strtr($text, $map);
        // Remove remaining non-ASCII
        $text = preg_replace('/[^\x20-\x7E]/', '', $text);
        return $text;
    }

    /**
     * Estimate text width in points (rough approximation for Helvetica)
     */
    private static function estimateWidth(string $text, float $size): float
    {
        // Helvetica average character width is ~0.5 * font size
        return strlen($text) * $size * 0.52;
    }

    /**
     * Make string safe for PDF
     */
    private static function safe(string $s): string
    {
        return htmlspecialchars_decode($s, ENT_QUOTES);
    }
}
