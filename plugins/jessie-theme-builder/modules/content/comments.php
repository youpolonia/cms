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
        $showCount = $attrs['show_count'] ?? true;
        $showAvatar = $attrs['show_avatar'] ?? true;
        $showReply = $attrs['show_reply'] ?? true;
        $dateFormat = $attrs['date_format'] ?? 'relative';

        // Sample comments
        $comments = [
            [
                'author' => 'John Doe',
                'date' => '2 days ago',
                'fullDate' => 'January 13, 2025',
                'content' => 'Great article! This really helped me understand the concept better. Keep up the good work!',
                'replies' => [
                    [
                        'author' => 'Jane Smith',
                        'date' => '1 day ago',
                        'fullDate' => 'January 14, 2025',
                        'content' => 'I agree, this was very informative!'
                    ]
                ]
            ],
            [
                'author' => 'Mike Johnson',
                'date' => '3 days ago',
                'fullDate' => 'January 12, 2025',
                'content' => 'Would love to see more content like this. Very well explained.',
                'replies' => []
            ]
        ];

        $totalComments = count($comments) + 1; // Including replies

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
        $innerHtml .= '<div class="jtb-comment-form">';
        $innerHtml .= '<h4>Leave a Comment</h4>';
        $innerHtml .= '<form>';
        $innerHtml .= '<div class="jtb-form-row">';
        $innerHtml .= '<input type="text" placeholder="Name *" required>';
        $innerHtml .= '<input type="email" placeholder="Email *" required>';
        $innerHtml .= '</div>';
        $innerHtml .= '<textarea placeholder="Your comment..." rows="5" required></textarea>';
        $innerHtml .= '<button type="submit" class="jtb-button">Submit Comment</button>';
        $innerHtml .= '</form>';
        $innerHtml .= '</div>';

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    private function renderComment(array $comment, bool $showAvatar, bool $showReply, string $dateFormat, bool $isReply = false): string
    {
        $date = $dateFormat === 'relative' ? $comment['date'] : ($comment['fullDate'] ?? $comment['date']);
        $replyClass = $isReply ? ' jtb-comment-reply' : '';

        $html = '<div class="jtb-comment' . $replyClass . '">';

        if ($showAvatar) {
            $html .= '<div class="jtb-comment-avatar">';
            $html .= '<div class="jtb-avatar-placeholder">' . strtoupper(substr($comment['author'], 0, 1)) . '</div>';
            $html .= '</div>';
        }

        $html .= '<div class="jtb-comment-body">';
        $html .= '<div class="jtb-comment-meta">';
        $html .= '<span class="jtb-comment-author">' . $this->esc($comment['author']) . '</span>';
        $html .= '<span class="jtb-comment-date">' . $this->esc($date) . '</span>';
        $html .= '</div>';
        $html .= '<div class="jtb-comment-content">' . $this->esc($comment['content']) . '</div>';

        if ($showReply && !$isReply) {
            $html .= '<a href="#" class="jtb-comment-reply-link">Reply</a>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        $avatarSize = $attrs['avatar_size'] ?? 60;
        $headerBg = $attrs['header_bg_color'] ?? '#f9f9f9';
        $commentBg = $attrs['comment_bg_color'] ?? '#ffffff';
        $authorColor = $attrs['author_color'] ?? '#333333';
        $dateColor = $attrs['date_color'] ?? '#999999';
        $textColor = $attrs['text_color'] ?? '#666666';
        $replyColor = $attrs['reply_color'] ?? '#2ea3f2';

        // Header
        $css .= $selector . ' .jtb-comments-header { background: ' . $headerBg . '; padding: 20px; margin-bottom: 20px; }' . "\n";
        $css .= $selector . ' .jtb-comments-title { margin: 0; font-size: 20px; }' . "\n";

        // Comment
        $css .= $selector . ' .jtb-comment { display: flex; gap: 15px; padding: 20px 0; border-bottom: 1px solid #eee; }' . "\n";

        // Avatar
        $css .= $selector . ' .jtb-comment-avatar { flex-shrink: 0; }' . "\n";
        $css .= $selector . ' .jtb-avatar-placeholder { '
            . 'width: ' . $avatarSize . 'px; '
            . 'height: ' . $avatarSize . 'px; '
            . 'border-radius: 50%; '
            . 'background: #ddd; '
            . 'display: flex; '
            . 'align-items: center; '
            . 'justify-content: center; '
            . 'font-size: ' . ($avatarSize / 2) . 'px; '
            . 'color: #666; '
            . 'font-weight: bold; '
            . '}' . "\n";

        // Body
        $css .= $selector . ' .jtb-comment-body { flex: 1; }' . "\n";
        $css .= $selector . ' .jtb-comment-meta { margin-bottom: 10px; }' . "\n";
        $css .= $selector . ' .jtb-comment-author { color: ' . $authorColor . '; font-weight: bold; margin-right: 15px; }' . "\n";
        $css .= $selector . ' .jtb-comment-date { color: ' . $dateColor . '; font-size: 14px; }' . "\n";
        $css .= $selector . ' .jtb-comment-content { color: ' . $textColor . '; line-height: 1.6; margin-bottom: 10px; }' . "\n";
        $css .= $selector . ' .jtb-comment-reply-link { color: ' . $replyColor . '; text-decoration: none; font-size: 14px; }' . "\n";

        // Replies
        $css .= $selector . ' .jtb-comment-replies { margin-left: 60px; }' . "\n";
        $css .= $selector . ' .jtb-comment-reply { padding-left: 20px; border-left: 3px solid #eee; }' . "\n";

        // Form
        $css .= $selector . ' .jtb-comment-form { margin-top: 40px; padding: 30px; background: ' . $commentBg . '; border: 1px solid #eee; }' . "\n";
        $css .= $selector . ' .jtb-comment-form h4 { margin-top: 0; margin-bottom: 20px; }' . "\n";
        $css .= $selector . ' .jtb-form-row { display: flex; gap: 15px; margin-bottom: 15px; }' . "\n";
        $css .= $selector . ' .jtb-form-row input { flex: 1; padding: 12px; border: 1px solid #ddd; }' . "\n";
        $css .= $selector . ' .jtb-comment-form textarea { width: 100%; padding: 12px; border: 1px solid #ddd; margin-bottom: 15px; box-sizing: border-box; }' . "\n";

        // Responsive
        $css .= '@media (max-width: 767px) {' . "\n";
        $css .= '  ' . $selector . ' .jtb-form-row { flex-direction: column; }' . "\n";
        $css .= '  ' . $selector . ' .jtb-comment-replies { margin-left: 30px; }' . "\n";
        $css .= '}' . "\n";

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('comments', JTB_Module_Comments::class);
