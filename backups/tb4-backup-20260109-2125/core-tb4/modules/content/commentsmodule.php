<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Comments Module
 * Displays post comments with reply form
 */
class CommentsModule extends Module
{
    public function __construct()
    {
        $this->name = 'Comments';
        $this->slug = 'comments';
        $this->icon = 'message-circle';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-comments-preview',
            'title' => '.tb4-comments-title',
            'list' => '.tb4-comments-list',
            'comment' => '.tb4-comment',
            'avatar' => '.tb4-comment-avatar',
            'author' => '.tb4-comment-author',
            'date' => '.tb4-comment-date',
            'content' => '.tb4-comment-content',
            'reply' => '.tb4-comment-reply',
            'form' => '.tb4-comments-form',
            'submit' => '.tb4-comments-form-submit'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'section_title' => [
                'label' => 'Section Title',
                'type' => 'text',
                'default' => 'Comments'
            ],
            'show_count_in_title' => [
                'label' => 'Show Count in Title',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_avatars' => [
                'label' => 'Show Avatars',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'avatar_style' => [
                'label' => 'Avatar Style',
                'type' => 'select',
                'options' => [
                    'circle' => 'Circle',
                    'rounded' => 'Rounded Square',
                    'square' => 'Square'
                ],
                'default' => 'circle'
            ],
            'show_date' => [
                'label' => 'Show Date',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'date_format' => [
                'label' => 'Date Format',
                'type' => 'select',
                'options' => [
                    'relative' => 'Relative (2 days ago)',
                    'full' => 'Full (January 5, 2026)',
                    'short' => 'Short (Jan 5, 2026)'
                ],
                'default' => 'relative'
            ],
            'show_reply_button' => [
                'label' => 'Show Reply Button',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_form' => [
                'label' => 'Show Comment Form',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'form_title' => [
                'label' => 'Form Title',
                'type' => 'text',
                'default' => 'Leave a Comment'
            ],
            'form_description' => [
                'label' => 'Form Description',
                'type' => 'text',
                'default' => 'Your email address will not be published.'
            ],
            'submit_text' => [
                'label' => 'Submit Button Text',
                'type' => 'text',
                'default' => 'Post Comment'
            ],
            'require_name_email' => [
                'label' => 'Require Name & Email',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_website_field' => [
                'label' => 'Show Website Field',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'no'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'text',
                'default' => '24px'
            ],
            'comment_bg_color' => [
                'label' => 'Comment Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'comment_border_color' => [
                'label' => 'Comment Border',
                'type' => 'color',
                'default' => '#e5e7eb'
            ],
            'comment_border_radius' => [
                'label' => 'Comment Border Radius',
                'type' => 'text',
                'default' => '8px'
            ],
            'comment_padding' => [
                'label' => 'Comment Padding',
                'type' => 'text',
                'default' => '20px'
            ],
            'comment_gap' => [
                'label' => 'Gap Between Comments',
                'type' => 'text',
                'default' => '20px'
            ],
            'avatar_size' => [
                'label' => 'Avatar Size',
                'type' => 'text',
                'default' => '48px'
            ],
            'author_color' => [
                'label' => 'Author Name Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'author_font_size' => [
                'label' => 'Author Font Size',
                'type' => 'text',
                'default' => '16px'
            ],
            'date_color' => [
                'label' => 'Date Color',
                'type' => 'color',
                'default' => '#9ca3af'
            ],
            'content_color' => [
                'label' => 'Content Color',
                'type' => 'color',
                'default' => '#4b5563'
            ],
            'content_font_size' => [
                'label' => 'Content Font Size',
                'type' => 'text',
                'default' => '14px'
            ],
            'reply_color' => [
                'label' => 'Reply Button Color',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'nested_indent' => [
                'label' => 'Reply Indent',
                'type' => 'text',
                'default' => '48px'
            ],
            'form_bg_color' => [
                'label' => 'Form Background',
                'type' => 'color',
                'default' => '#f9fafb'
            ],
            'form_border_radius' => [
                'label' => 'Form Border Radius',
                'type' => 'text',
                'default' => '12px'
            ],
            'input_bg_color' => [
                'label' => 'Input Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'input_border_color' => [
                'label' => 'Input Border',
                'type' => 'color',
                'default' => '#d1d5db'
            ],
            'input_border_radius' => [
                'label' => 'Input Border Radius',
                'type' => 'text',
                'default' => '8px'
            ],
            'button_bg_color' => [
                'label' => 'Button Background',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'button_text_color' => [
                'label' => 'Button Text Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'button_border_radius' => [
                'label' => 'Button Border Radius',
                'type' => 'text',
                'default' => '8px'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return [
            'css_id' => [
                'label' => 'CSS ID',
                'type' => 'text',
                'default' => ''
            ],
            'css_class' => [
                'label' => 'CSS Class',
                'type' => 'text',
                'default' => ''
            ],
            'custom_css' => [
                'label' => 'Custom CSS',
                'type' => 'textarea',
                'default' => ''
            ]
        ];
    }

    /**
     * Get sample comments for preview
     */
    private function getSampleComments(): array
    {
        return [
            [
                'name' => 'Sarah Johnson',
                'initials' => 'SJ',
                'color' => '#667eea',
                'date_relative' => '2 days ago',
                'date_full' => 'January 3, 2026',
                'date_short' => 'Jan 3, 2026',
                'content' => 'This is a fantastic article! I learned so much about web design principles. Looking forward to implementing these tips in my own projects.',
                'replies' => [
                    [
                        'name' => 'John Doe',
                        'initials' => 'JD',
                        'color' => '#10b981',
                        'date_relative' => '1 day ago',
                        'date_full' => 'January 4, 2026',
                        'date_short' => 'Jan 4, 2026',
                        'content' => 'Thanks Sarah! Glad you found it helpful. Feel free to reach out if you have any questions.'
                    ]
                ]
            ],
            [
                'name' => 'Mike Chen',
                'initials' => 'MC',
                'color' => '#f59e0b',
                'date_relative' => '5 days ago',
                'date_full' => 'December 31, 2025',
                'date_short' => 'Dec 31, 2025',
                'content' => 'Great insights on modern design trends. The section about responsive design was particularly useful for my current project.',
                'replies' => []
            ]
        ];
    }

    /**
     * Get avatar border radius based on style
     */
    private function getAvatarBorderRadius(string $style): string
    {
        return match($style) {
            'circle' => '50%',
            'rounded' => '8px',
            'square' => '0',
            default => '50%'
        };
    }

    /**
     * Get date based on format
     */
    private function getFormattedDate(array $comment, string $format): string
    {
        return match($format) {
            'relative' => $comment['date_relative'],
            'full' => $comment['date_full'],
            'short' => $comment['date_short'],
            default => $comment['date_relative']
        };
    }

    public function render(array $settings): string
    {
        // Content fields
        $sectionTitle = $settings['section_title'] ?? 'Comments';
        $showCountInTitle = ($settings['show_count_in_title'] ?? 'yes') === 'yes';
        $showAvatars = ($settings['show_avatars'] ?? 'yes') === 'yes';
        $avatarStyle = $settings['avatar_style'] ?? 'circle';
        $showDate = ($settings['show_date'] ?? 'yes') === 'yes';
        $dateFormat = $settings['date_format'] ?? 'relative';
        $showReplyButton = ($settings['show_reply_button'] ?? 'yes') === 'yes';
        $showForm = ($settings['show_form'] ?? 'yes') === 'yes';
        $formTitle = $settings['form_title'] ?? 'Leave a Comment';
        $formDescription = $settings['form_description'] ?? 'Your email address will not be published.';
        $submitText = $settings['submit_text'] ?? 'Post Comment';
        $requireNameEmail = ($settings['require_name_email'] ?? 'yes') === 'yes';
        $showWebsiteField = ($settings['show_website_field'] ?? 'no') === 'yes';

        // Design fields
        $titleColor = $settings['title_color'] ?? '#111827';
        $titleFontSize = $settings['title_font_size'] ?? '24px';
        $commentBgColor = $settings['comment_bg_color'] ?? '#ffffff';
        $commentBorderColor = $settings['comment_border_color'] ?? '#e5e7eb';
        $commentBorderRadius = $settings['comment_border_radius'] ?? '8px';
        $commentPadding = $settings['comment_padding'] ?? '20px';
        $commentGap = $settings['comment_gap'] ?? '20px';
        $avatarSize = $settings['avatar_size'] ?? '48px';
        $authorColor = $settings['author_color'] ?? '#111827';
        $authorFontSize = $settings['author_font_size'] ?? '16px';
        $dateColor = $settings['date_color'] ?? '#9ca3af';
        $contentColor = $settings['content_color'] ?? '#4b5563';
        $contentFontSize = $settings['content_font_size'] ?? '14px';
        $replyColor = $settings['reply_color'] ?? '#2563eb';
        $nestedIndent = $settings['nested_indent'] ?? '48px';
        $formBgColor = $settings['form_bg_color'] ?? '#f9fafb';
        $formBorderRadius = $settings['form_border_radius'] ?? '12px';
        $inputBgColor = $settings['input_bg_color'] ?? '#ffffff';
        $inputBorderColor = $settings['input_border_color'] ?? '#d1d5db';
        $inputBorderRadius = $settings['input_border_radius'] ?? '8px';
        $buttonBgColor = $settings['button_bg_color'] ?? '#2563eb';
        $buttonTextColor = $settings['button_text_color'] ?? '#ffffff';
        $buttonBorderRadius = $settings['button_border_radius'] ?? '8px';

        // Get avatar border radius
        $avatarBorderRadius = $this->getAvatarBorderRadius($avatarStyle);

        // Get sample comments
        $comments = $this->getSampleComments();
        $commentCount = 3; // Total including reply

        // Build unique ID for scoped styles
        $uniqueId = 'tb4-comments-' . uniqid();

        // Build title text
        $titleText = $showCountInTitle ? $sectionTitle . ' (' . $commentCount . ')' : $sectionTitle;

        // Build HTML
        $html = '<div class="tb4-comments-preview" id="' . esc_attr($uniqueId) . '">';

        // Title
        $html .= '<h3 class="tb4-comments-title" style="font-size:' . esc_attr($titleFontSize) . ';font-weight:700;color:' . esc_attr($titleColor) . ';margin:0 0 24px 0;">' . esc_html($titleText) . '</h3>';

        // Comments list
        $html .= '<div class="tb4-comments-list" style="display:flex;flex-direction:column;gap:' . esc_attr($commentGap) . ';">';

        foreach ($comments as $comment) {
            $html .= $this->renderComment(
                $comment,
                $showAvatars,
                $avatarSize,
                $avatarBorderRadius,
                $showDate,
                $dateFormat,
                $showReplyButton,
                $commentBgColor,
                $commentBorderColor,
                $commentBorderRadius,
                $commentPadding,
                $authorColor,
                $authorFontSize,
                $dateColor,
                $contentColor,
                $contentFontSize,
                $replyColor
            );

            // Nested replies
            if (!empty($comment['replies'])) {
                foreach ($comment['replies'] as $reply) {
                    $html .= '<div class="tb4-comment-nested" style="margin-left:' . esc_attr($nestedIndent) . ';">';
                    $html .= $this->renderComment(
                        $reply,
                        $showAvatars,
                        $avatarSize,
                        $avatarBorderRadius,
                        $showDate,
                        $dateFormat,
                        $showReplyButton,
                        $commentBgColor,
                        $commentBorderColor,
                        $commentBorderRadius,
                        $commentPadding,
                        $authorColor,
                        $authorFontSize,
                        $dateColor,
                        $contentColor,
                        $contentFontSize,
                        $replyColor
                    );
                    $html .= '</div>';
                }
            }
        }

        $html .= '</div>'; // Close comments list

        // Comment form
        if ($showForm) {
            $html .= '<div class="tb4-comments-form" style="margin-top:40px;padding:24px;background:' . esc_attr($formBgColor) . ';border-radius:' . esc_attr($formBorderRadius) . ';">';
            $html .= '<h4 class="tb4-comments-form-title" style="font-size:20px;font-weight:600;color:' . esc_attr($titleColor) . ';margin:0 0 8px 0;">' . esc_html($formTitle) . '</h4>';
            $html .= '<p class="tb4-comments-form-desc" style="font-size:14px;color:#6b7280;margin:0 0 20px 0;">' . esc_html($formDescription) . '</p>';

            $html .= '<div class="tb4-comments-form-fields" style="display:flex;flex-direction:column;gap:16px;">';

            // Name & Email row
            $requiredMark = $requireNameEmail ? ' <span style="color:#ef4444;">*</span>' : '';
            $html .= '<div class="tb4-comments-form-row" style="display:flex;gap:16px;">';
            $html .= '<div class="tb4-comments-form-field" style="flex:1;display:flex;flex-direction:column;gap:6px;">';
            $html .= '<label class="tb4-comments-form-label" style="font-size:14px;font-weight:500;color:#374151;">Name' . $requiredMark . '</label>';
            $html .= '<input type="text" class="tb4-comments-form-input" placeholder="Your name" disabled style="padding:12px 16px;background:' . esc_attr($inputBgColor) . ';border:1px solid ' . esc_attr($inputBorderColor) . ';border-radius:' . esc_attr($inputBorderRadius) . ';font-size:14px;">';
            $html .= '</div>';
            $html .= '<div class="tb4-comments-form-field" style="flex:1;display:flex;flex-direction:column;gap:6px;">';
            $html .= '<label class="tb4-comments-form-label" style="font-size:14px;font-weight:500;color:#374151;">Email' . $requiredMark . '</label>';
            $html .= '<input type="email" class="tb4-comments-form-input" placeholder="your@email.com" disabled style="padding:12px 16px;background:' . esc_attr($inputBgColor) . ';border:1px solid ' . esc_attr($inputBorderColor) . ';border-radius:' . esc_attr($inputBorderRadius) . ';font-size:14px;">';
            $html .= '</div>';
            $html .= '</div>';

            // Website field
            if ($showWebsiteField) {
                $html .= '<div class="tb4-comments-form-field" style="display:flex;flex-direction:column;gap:6px;">';
                $html .= '<label class="tb4-comments-form-label" style="font-size:14px;font-weight:500;color:#374151;">Website</label>';
                $html .= '<input type="url" class="tb4-comments-form-input" placeholder="https://yourwebsite.com" disabled style="padding:12px 16px;background:' . esc_attr($inputBgColor) . ';border:1px solid ' . esc_attr($inputBorderColor) . ';border-radius:' . esc_attr($inputBorderRadius) . ';font-size:14px;">';
                $html .= '</div>';
            }

            // Comment textarea
            $html .= '<div class="tb4-comments-form-field" style="display:flex;flex-direction:column;gap:6px;">';
            $html .= '<label class="tb4-comments-form-label" style="font-size:14px;font-weight:500;color:#374151;">Comment <span style="color:#ef4444;">*</span></label>';
            $html .= '<textarea class="tb4-comments-form-textarea" placeholder="Write your comment..." disabled style="padding:12px 16px;background:' . esc_attr($inputBgColor) . ';border:1px solid ' . esc_attr($inputBorderColor) . ';border-radius:' . esc_attr($inputBorderRadius) . ';font-size:14px;min-height:120px;resize:vertical;"></textarea>';
            $html .= '</div>';

            // Submit button
            $html .= '<button type="button" class="tb4-comments-form-submit" style="padding:12px 24px;background:' . esc_attr($buttonBgColor) . ';color:' . esc_attr($buttonTextColor) . ';border:none;border-radius:' . esc_attr($buttonBorderRadius) . ';font-size:14px;font-weight:600;cursor:pointer;align-self:flex-start;">' . esc_html($submitText) . '</button>';

            $html .= '</div></div>'; // Close form fields and form
        }

        $html .= '</div>'; // Close preview

        return $html;
    }

    /**
     * Render a single comment
     */
    private function renderComment(
        array $comment,
        bool $showAvatars,
        string $avatarSize,
        string $avatarBorderRadius,
        bool $showDate,
        string $dateFormat,
        bool $showReplyButton,
        string $commentBgColor,
        string $commentBorderColor,
        string $commentBorderRadius,
        string $commentPadding,
        string $authorColor,
        string $authorFontSize,
        string $dateColor,
        string $contentColor,
        string $contentFontSize,
        string $replyColor
    ): string {
        $html = '<div class="tb4-comment" style="display:flex;gap:16px;padding:' . esc_attr($commentPadding) . ';background:' . esc_attr($commentBgColor) . ';border:1px solid ' . esc_attr($commentBorderColor) . ';border-radius:' . esc_attr($commentBorderRadius) . ';">';

        // Avatar
        if ($showAvatars) {
            $html .= '<div class="tb4-comment-avatar" style="flex-shrink:0;width:' . esc_attr($avatarSize) . ';height:' . esc_attr($avatarSize) . ';background:linear-gradient(135deg,' . esc_attr($comment['color']) . ' 0%,' . esc_attr($comment['color']) . '99 100%);border-radius:' . esc_attr($avatarBorderRadius) . ';display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;font-size:14px;">' . esc_html($comment['initials']) . '</div>';
        }

        $html .= '<div class="tb4-comment-body" style="flex:1;min-width:0;">';

        // Header
        $html .= '<div class="tb4-comment-header" style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">';
        $html .= '<span class="tb4-comment-author" style="font-size:' . esc_attr($authorFontSize) . ';font-weight:600;color:' . esc_attr($authorColor) . ';">' . esc_html($comment['name']) . '</span>';
        if ($showDate) {
            $dateText = $this->getFormattedDate($comment, $dateFormat);
            $html .= '<span class="tb4-comment-date" style="font-size:13px;color:' . esc_attr($dateColor) . ';">' . esc_html($dateText) . '</span>';
        }
        $html .= '</div>';

        // Content
        $html .= '<p class="tb4-comment-content" style="font-size:' . esc_attr($contentFontSize) . ';color:' . esc_attr($contentColor) . ';line-height:1.6;margin:0 0 12px 0;">' . esc_html($comment['content']) . '</p>';

        // Reply button
        if ($showReplyButton) {
            $html .= '<span class="tb4-comment-reply" style="font-size:13px;color:' . esc_attr($replyColor) . ';font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:4px;">';
            $html .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg> Reply';
            $html .= '</span>';
        }

        $html .= '</div></div>'; // Close body and comment

        return $html;
    }
}
