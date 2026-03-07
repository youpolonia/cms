<?php
/**
 * Comments Module
 * Display post/page comments
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Comments extends JTB_Element
{
    public string $icon = 'comments';
    public string $category = 'content';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'comments';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'header_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-comments-header'
        ],
        'comment_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-comment-form'
        ],
        'author_color' => [
            'property' => 'color',
            'selector' => '.jtb-comment-author'
        ],
        'date_color' => [
            'property' => 'color',
            'selector' => '.jtb-comment-date'
        ],
        'text_color' => [
            'property' => 'color',
            'selector' => '.jtb-comment-content'
        ],
        'reply_color' => [
            'property' => 'color',
            'selector' => '.jtb-comment-reply-link'
        ]
    ];

    public function getSlug(): string
    {
        return 'comments';
    }

    public function getName(): string
    {
        return 'Comments';
    }

    public function getFields(): array
    {
        return [
            'show_count' => [
                'label' => 'Show Comment Count',
                'type' => 'toggle',
                'default' => true
            ],
            'show_avatar' => [
                'label' => 'Show Avatar',
                'type' => 'toggle',
                'default' => true
            ],
            'avatar_size' => [
                'label' => 'Avatar Size',
                'type' => 'range',
                'min' => 30,
                'max' => 100,
                'unit' => 'px',
                'default' => 60,
                'show_if' => ['show_avatar' => true]
            ],
            'show_reply' => [
                'label' => 'Show Reply Button',
                'type' => 'toggle',
                'default' => true
            ],
            'date_format' => [
                'label' => 'Date Format',
                'type' => 'select',
                'options' => [
                    'relative' => 'Relative (2 days ago)',
                    'full' => 'Full Date'
                ],
                'default' => 'relative'
            ],
            // Colors
            'header_bg_color' => [
                'label' => 'Header Background',
                'type' => 'color',
                'default' => '#f9f9f9'
            ],
            'comment_bg_color' => [
                'label' => 'Comment Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'author_color' => [
                'label' => 'Author Name Color',
                'type' => 'color',
                'default' => '#333333'
            ],
            'date_color' => [
                'label' => 'Date Color',
                'type' => 'color',
                'default' => '#999999'
            ],
            'text_color' => [
                'label' => 'Comment Text Color',
                'type' => 'color',
                'default' => '#666666'
            ],
            'reply_color' => [
                'label' => 'Reply Button Color',
                'type' => 'color',
                'default' => '#2ea3f2'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $showCount = $attrs['show_count'] ?? true;
        $showAvatar = $attrs['show_avatar'] ?? true;
        $showReply = $attrs['show_reply'] ?? true;
        $dateFormat = $attrs['date_format'] ?? 'relative';

        // Fetch real comments from DB
        $postId = JTB_Dynamic_Context::get('post_id') ?? 0;
        [$comments, $totalComments] = $this->fetchComments((int)$postId);

        $innerHtml = '<div class="jtb-comments-container">';

        // Header
        if ($showCount) {
            $innerHtml .= '<div class="jtb-comments-header">';
            $innerHtml .= '<h3 class="jtb-comments-title">' . $totalComments . ' Comments</h3>';
            $innerHtml .= '</div>';
        }

        // Comments list
        $innerHtml .= '<div class="jtb-comments-list">';

        foreach ($comments as $comment) {
            $innerHtml .= $this->renderComment($comment, $showAvatar, $showReply, $dateFormat);

            // Render replies
            if (!empty($comment['replies'])) {
                $innerHtml .= '<div class="jtb-comment-replies">';
                foreach ($comment['replies'] as $reply) {
                    $innerHtml .= $this->renderComment($reply, $showAvatar, $showReply, $dateFormat, true);
                }
                $innerHtml .= '</div>';
            }
        }

        $innerHtml .= '</div>';

        // Comment form
        $csrfToken = function_exists('csrf_token') ? csrf_token() : '';
        $innerHtml .= '<div class="jtb-comment-form">';
        $innerHtml .= '<h4>Leave a Comment</h4>';
        $innerHtml .= '<form method="post" action="/comment" class="jtb-comment-submit-form">';
        $innerHtml .= '<input type="hidden" name="csrf_token" value="' . $this->esc($csrfToken) . '">';
        $innerHtml .= '<input type="hidden" name="article_id" value="' . (int)$postId . '">';
        $innerHtml .= '<div class="jtb-form-row">';
        $innerHtml .= '<input type="text" name="author_name" placeholder="Name *" required>';
        $innerHtml .= '<input type="email" name="author_email" placeholder="Email *" required>';
        $innerHtml .= '</div>';
        $innerHtml .= '<textarea name="content" placeholder="Your comment..." rows="5" required></textarea>';
        $innerHtml .= '<button type="submit" class="jtb-button">Submit Comment</button>';
        $innerHtml .= '</form>';
        $innerHtml .= '<div class="jtb-comment-pending" style="display:none">Your comment is awaiting moderation. Thank you!</div>';
        $innerHtml .= '</div>';

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    private function renderComment(array $comment, bool $showAvatar, bool $showReply, string $dateFormat, bool $isReply = false): string
    {
        // Support both DB fields and legacy array keys
        $author  = $comment['author_name'] ?? $comment['author'] ?? 'Anonymous';
        $created = $comment['created_at'] ?? null;

        if ($dateFormat === 'relative' && $created) {
            $diff = time() - strtotime($created);
            if ($diff < 60)            $date = 'just now';
            elseif ($diff < 3600)      $date = floor($diff / 60) . ' min ago';
            elseif ($diff < 86400)     $date = floor($diff / 3600) . ' hours ago';
            elseif ($diff < 2592000)   $date = floor($diff / 86400) . ' days ago';
            else                       $date = date('F j, Y', strtotime($created));
        } else {
            $date = $created ? date('F j, Y', strtotime($created)) : '';
        }

        $replyClass = $isReply ? ' jtb-comment-reply' : '';
        $html = '<div class="jtb-comment' . $replyClass . '" id="comment-' . (int)($comment['id'] ?? 0) . '">';

        if ($showAvatar) {
            $initial = strtoupper(mb_substr($author, 0, 1));
            $html .= '<div class="jtb-comment-avatar">';
            $html .= '<div class="jtb-avatar-placeholder">' . $this->esc($initial) . '</div>';
            $html .= '</div>';
        }

        $html .= '<div class="jtb-comment-body">';
        $html .= '<div class="jtb-comment-meta">';
        $html .= '<span class="jtb-comment-author">' . $this->esc($author) . '</span>';
        $html .= '<span class="jtb-comment-date">' . $this->esc($date) . '</span>';
        $html .= '</div>';
        $html .= '<div class="jtb-comment-content">' . nl2br($this->esc($comment['content'] ?? '')) . '</div>';

        if ($showReply && !$isReply) {
            $commentId = (int)($comment['id'] ?? 0);
            $html .= '<a href="#jtb-comment-form" class="jtb-comment-reply-link" data-reply-to="' . $commentId . '" data-reply-author="' . $this->esc($author) . '">Reply</a>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Fetch approved comments from DB, grouped by parent
     * Returns [comments_with_replies[], total_count]
     */
    private function fetchComments(int $postId): array
    {
        if ($postId <= 0) return [[], 0];

        try {
            $pdo  = db();
            $stmt = $pdo->prepare("
                SELECT id, parent_id, author_name, content, created_at
                FROM comments
                WHERE article_id = ? AND status = 'approved'
                ORDER BY created_at ASC
            ");
            $stmt->execute([$postId]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Group: top-level + replies
            $top     = [];
            $replies = [];
            foreach ($rows as $row) {
                if ($row['parent_id']) {
                    $replies[(int)$row['parent_id']][] = $row;
                } else {
                    $top[] = $row;
                }
            }

            // Attach replies
            foreach ($top as &$comment) {
                $comment['replies'] = $replies[(int)$comment['id']] ?? [];
            }

            $total = count($rows);
            return [$top, $total];

        } catch (\Throwable $e) {
            return [[], 0];
        }
    }

    /**
     * Generate CSS for Comments module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $avatarSize = $attrs['avatar_size'] ?? 60;

        // Header
        $css .= $selector . ' .jtb-comments-header { padding: 20px; margin-bottom: 20px; }' . "\n";
        $css .= $selector . ' .jtb-comments-title { margin: 0; font-size: 20px; }' . "\n";

        // Comment
        $css .= $selector . ' .jtb-comment { display: flex; gap: 15px; padding: 20px 0; border-bottom: 1px solid #eee; }' . "\n";

        // Avatar
        $css .= $selector . ' .jtb-comment-avatar { flex-shrink: 0; }' . "\n";
        $css .= $selector . ' .jtb-avatar-placeholder { '
            . 'width: ' . $avatarSize . 'px; height: ' . $avatarSize . 'px; '
            . 'border-radius: 50%; background: #ddd; display: flex; '
            . 'align-items: center; justify-content: center; '
            . 'font-size: ' . ($avatarSize / 2) . 'px; color: #666; font-weight: bold; }' . "\n";

        // Body
        $css .= $selector . ' .jtb-comment-body { flex: 1; }' . "\n";
        $css .= $selector . ' .jtb-comment-meta { margin-bottom: 10px; }' . "\n";
        $css .= $selector . ' .jtb-comment-author { font-weight: bold; margin-right: 15px; }' . "\n";
        $css .= $selector . ' .jtb-comment-date { font-size: 14px; }' . "\n";
        $css .= $selector . ' .jtb-comment-content { line-height: 1.6; margin-bottom: 10px; }' . "\n";
        $css .= $selector . ' .jtb-comment-reply-link { text-decoration: none; font-size: 14px; }' . "\n";

        // Replies
        $css .= $selector . ' .jtb-comment-replies { margin-left: 60px; }' . "\n";
        $css .= $selector . ' .jtb-comment-reply { padding-left: 20px; border-left: 3px solid #eee; }' . "\n";

        // Form
        $css .= $selector . ' .jtb-comment-form { margin-top: 40px; padding: 30px; border: 1px solid #eee; }' . "\n";
        $css .= $selector . ' .jtb-comment-form h4 { margin-top: 0; margin-bottom: 20px; }' . "\n";
        $css .= $selector . ' .jtb-form-row { display: flex; gap: 15px; margin-bottom: 15px; }' . "\n";
        $css .= $selector . ' .jtb-form-row input { flex: 1; padding: 12px; border: 1px solid #ddd; }' . "\n";
        $css .= $selector . ' .jtb-comment-form textarea { width: 100%; padding: 12px; border: 1px solid #ddd; margin-bottom: 15px; box-sizing: border-box; }' . "\n";

        // Responsive
        $css .= '@media (max-width: 767px) {' . "\n";
        $css .= '  ' . $selector . ' .jtb-form-row { flex-direction: column; }' . "\n";
        $css .= '  ' . $selector . ' .jtb-comment-replies { margin-left: 30px; }' . "\n";
        $css .= '}' . "\n";

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('comments', JTB_Module_Comments::class);
