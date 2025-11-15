<?php
/**
 * Shared Pagination Component
 * Renders pagination controls
 */

function render_pagination($currentPage, $totalPages) {
    if ($totalPages <= 1) return '';

    $html = '<div class="pagination">';

    // Previous link
    if ($currentPage > 1) {
        $html .= '<a href="?page='.($currentPage-1).'" class="page-link prev">Previous</a>';
    }

    // Page numbers
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i == $currentPage ? ' active' : '';
        $html .= '<a href="?page='.$i.'" class="page-link'.$active.'">'.$i.'</a>';
    }

    // Next link
    if ($currentPage < $totalPages) {
        $html .= '<a href="?page='.($currentPage+1).'" class="page-link next">Next</a>';
    }

    $html .= '</div>';
    return $html;
}
