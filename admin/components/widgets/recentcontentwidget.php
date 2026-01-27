<?php
class RecentContentWidget {
    public static function render() {
        // Get recent content from database
        $items = []; // TODO: Replace with actual DB query
        
        return render_widget_template(
            'recent_content',
            [
                'items' => $items,
                'title' => 'Recent Content',
                'empty_message' => 'No recent content available'
            ]
        );
    }
}
