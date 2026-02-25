<?php
namespace Plugins\JessieSeoWriter;

/**
 * SEO Writer — Rule-based Live SEO Scoring
 * All calculations are deterministic (no AI), suitable for real-time frontend scoring.
 */
class SeoWriterEditor {

    /**
     * Score content for SEO quality
     * @return array{score:int, checks:array, summary:string}
     */
    public function score(string $title, string $metaDesc, string $keyword, string $body): array {
        $keyword = mb_strtolower(trim($keyword));
        $title   = trim($title);
        $metaDesc = trim($metaDesc);
        $bodyLower = mb_strtolower($body);
        $titleLower = mb_strtolower($title);
        $metaLower = mb_strtolower($metaDesc);

        $checks = [];
        $totalPoints = 0;
        $maxPoints = 0;

        // ── 1. Title length (max 10pts) ──
        $titleLen = mb_strlen($title);
        $titleScore = 0;
        if ($titleLen >= 30 && $titleLen <= 60) {
            $titleScore = 10;
            $titleMsg = "Title length is optimal ($titleLen chars)";
            $titleStatus = 'good';
        } elseif ($titleLen >= 20 && $titleLen <= 70) {
            $titleScore = 6;
            $titleMsg = "Title length acceptable ($titleLen chars), aim for 30-60";
            $titleStatus = 'warning';
        } elseif ($titleLen > 0) {
            $titleScore = 3;
            $titleMsg = "Title length poor ($titleLen chars), should be 30-60";
            $titleStatus = 'bad';
        } else {
            $titleMsg = 'Missing title';
            $titleStatus = 'bad';
        }
        $checks[] = ['name' => 'Title Length', 'score' => $titleScore, 'max' => 10, 'status' => $titleStatus, 'message' => $titleMsg];
        $totalPoints += $titleScore;
        $maxPoints += 10;

        // ── 2. Keyword in title (max 10pts) ──
        if ($keyword !== '') {
            $kwInTitle = str_contains($titleLower, $keyword);
            $kwTitleScore = $kwInTitle ? 10 : 0;
            $checks[] = ['name' => 'Keyword in Title', 'score' => $kwTitleScore, 'max' => 10, 'status' => $kwInTitle ? 'good' : 'bad', 'message' => $kwInTitle ? 'Keyword found in title' : 'Add your keyword to the title'];
            $totalPoints += $kwTitleScore;
        } else {
            $checks[] = ['name' => 'Keyword in Title', 'score' => 0, 'max' => 10, 'status' => 'bad', 'message' => 'No target keyword set'];
        }
        $maxPoints += 10;

        // ── 3. Meta description (max 10pts) ──
        $metaLen = mb_strlen($metaDesc);
        $metaScore = 0;
        if ($metaLen >= 120 && $metaLen <= 160) {
            $metaScore = 10;
            $metaMsg = "Meta description optimal ($metaLen chars)";
            $metaSt = 'good';
        } elseif ($metaLen >= 70 && $metaLen <= 170) {
            $metaScore = 6;
            $metaMsg = "Meta description acceptable ($metaLen chars), aim for 120-160";
            $metaSt = 'warning';
        } elseif ($metaLen > 0) {
            $metaScore = 3;
            $metaMsg = "Meta description too " . ($metaLen < 70 ? 'short' : 'long') . " ($metaLen chars)";
            $metaSt = 'bad';
        } else {
            $metaMsg = 'Missing meta description';
            $metaSt = 'bad';
        }
        $checks[] = ['name' => 'Meta Description', 'score' => $metaScore, 'max' => 10, 'status' => $metaSt, 'message' => $metaMsg];
        $totalPoints += $metaScore;
        $maxPoints += 10;

        // ── 4. Keyword in meta description (max 5pts) ──
        if ($keyword !== '' && $metaLen > 0) {
            $kwInMeta = str_contains($metaLower, $keyword);
            $kwMetaScore = $kwInMeta ? 5 : 0;
            $checks[] = ['name' => 'Keyword in Meta', 'score' => $kwMetaScore, 'max' => 5, 'status' => $kwInMeta ? 'good' : 'warning', 'message' => $kwInMeta ? 'Keyword found in meta description' : 'Add keyword to meta description'];
            $totalPoints += $kwMetaScore;
        } else {
            $checks[] = ['name' => 'Keyword in Meta', 'score' => 0, 'max' => 5, 'status' => 'bad', 'message' => 'No keyword or meta description'];
        }
        $maxPoints += 5;

        // ── 5. Content length (max 15pts) ──
        $wordCount = str_word_count(strip_tags($body));
        if ($wordCount >= 1500) {
            $contentScore = 15;
            $contentMsg = "Excellent content length ($wordCount words)";
            $contentSt = 'good';
        } elseif ($wordCount >= 800) {
            $contentScore = 10;
            $contentMsg = "Good content length ($wordCount words), 1500+ recommended";
            $contentSt = 'warning';
        } elseif ($wordCount >= 300) {
            $contentScore = 5;
            $contentMsg = "Content is short ($wordCount words), aim for 800+";
            $contentSt = 'warning';
        } else {
            $contentScore = max(0, (int)($wordCount / 60));
            $contentMsg = "Content too short ($wordCount words), minimum 300 recommended";
            $contentSt = 'bad';
        }
        $checks[] = ['name' => 'Content Length', 'score' => $contentScore, 'max' => 15, 'status' => $contentSt, 'message' => $contentMsg];
        $totalPoints += $contentScore;
        $maxPoints += 15;

        // ── 6. Keyword density in body (max 10pts) ──
        if ($keyword !== '' && $wordCount > 0) {
            $kwCount = substr_count($bodyLower, $keyword);
            $density = ($kwCount * str_word_count($keyword)) / $wordCount * 100;
            if ($density >= 0.5 && $density <= 2.5) {
                $densityScore = 10;
                $densityMsg = sprintf('Keyword density %.1f%% (optimal)', $density);
                $densitySt = 'good';
            } elseif ($density > 0 && $density < 0.5) {
                $densityScore = 5;
                $densityMsg = sprintf('Keyword density %.1f%% (too low, aim for 0.5-2.5%%)', $density);
                $densitySt = 'warning';
            } elseif ($density > 2.5 && $density <= 4) {
                $densityScore = 5;
                $densityMsg = sprintf('Keyword density %.1f%% (slightly high)', $density);
                $densitySt = 'warning';
            } elseif ($density > 4) {
                $densityScore = 2;
                $densityMsg = sprintf('Keyword stuffing detected (%.1f%%)', $density);
                $densitySt = 'bad';
            } else {
                $densityScore = 0;
                $densityMsg = 'Keyword not found in content';
                $densitySt = 'bad';
            }
        } else {
            $densityScore = 0;
            $densityMsg = $keyword === '' ? 'No target keyword set' : 'Content too short to measure';
            $densitySt = 'bad';
        }
        $checks[] = ['name' => 'Keyword Density', 'score' => $densityScore, 'max' => 10, 'status' => $densitySt, 'message' => $densityMsg];
        $totalPoints += $densityScore;
        $maxPoints += 10;

        // ── 7. Keyword in headings (max 10pts) ──
        if ($keyword !== '') {
            preg_match_all('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/si', $body, $headingMatches);
            $headings = $headingMatches[1] ?? [];
            $kwInH = false;
            foreach ($headings as $h) {
                if (str_contains(mb_strtolower(strip_tags($h)), $keyword)) {
                    $kwInH = true;
                    break;
                }
            }
            $hScore = $kwInH ? 10 : 0;
            $hCount = count($headings);
            $checks[] = ['name' => 'Keyword in Headings', 'score' => $hScore, 'max' => 10, 'status' => $kwInH ? 'good' : ($hCount > 0 ? 'warning' : 'bad'), 'message' => $kwInH ? 'Keyword found in headings' : ($hCount > 0 ? "Found $hCount headings but keyword missing" : 'No headings found — add H2/H3 tags')];
            $totalPoints += $hScore;
        } else {
            $checks[] = ['name' => 'Keyword in Headings', 'score' => 0, 'max' => 10, 'status' => 'bad', 'message' => 'No target keyword set'];
        }
        $maxPoints += 10;

        // ── 8. Heading structure (max 10pts) ──
        preg_match_all('/<h([1-6])/i', $body, $hLevels);
        $hNums = array_map('intval', $hLevels[1] ?? []);
        $hasH2 = in_array(2, $hNums);
        $structScore = 0;
        if (count($hNums) >= 3 && $hasH2) {
            $structScore = 10;
            $structMsg = count($hNums) . ' headings with proper hierarchy';
            $structSt = 'good';
        } elseif (count($hNums) >= 1) {
            $structScore = 5;
            $structMsg = count($hNums) . ' heading(s) found, add more H2/H3 for structure';
            $structSt = 'warning';
        } else {
            $structMsg = 'No headings found — use H2/H3 to structure content';
            $structSt = 'bad';
        }
        $checks[] = ['name' => 'Heading Structure', 'score' => $structScore, 'max' => 10, 'status' => $structSt, 'message' => $structMsg];
        $totalPoints += $structScore;
        $maxPoints += 10;

        // ── 9. Readability (avg sentence length, max 10pts) ──
        $plainText = strip_tags($body);
        $sentences = preg_split('/[.!?]+/', $plainText, -1, PREG_SPLIT_NO_EMPTY);
        $sentCount = count($sentences);
        if ($sentCount > 0) {
            $avgSentLen = $wordCount / $sentCount;
            if ($avgSentLen >= 10 && $avgSentLen <= 20) {
                $readScore = 10;
                $readMsg = sprintf('Avg sentence length %.0f words (excellent readability)', $avgSentLen);
                $readSt = 'good';
            } elseif ($avgSentLen >= 7 && $avgSentLen <= 25) {
                $readScore = 7;
                $readMsg = sprintf('Avg sentence length %.0f words (good)', $avgSentLen);
                $readSt = 'warning';
            } else {
                $readScore = 3;
                $readMsg = sprintf('Avg sentence length %.0f words (aim for 10-20)', $avgSentLen);
                $readSt = 'bad';
            }
        } else {
            $readScore = 0;
            $readMsg = 'Not enough content to measure readability';
            $readSt = 'bad';
        }
        $checks[] = ['name' => 'Readability', 'score' => $readScore, 'max' => 10, 'status' => $readSt, 'message' => $readMsg];
        $totalPoints += $readScore;
        $maxPoints += 10;

        // ── 10. Internal/external links (max 5pts) ──
        preg_match_all('/<a\s[^>]*href\s*=\s*["\']([^"\']+)/i', $body, $linkMatches);
        $linkCount = count($linkMatches[1] ?? []);
        if ($linkCount >= 3) {
            $linkScore = 5;
            $linkMsg = "$linkCount links found (good)";
            $linkSt = 'good';
        } elseif ($linkCount >= 1) {
            $linkScore = 3;
            $linkMsg = "$linkCount link(s) found, add more for SEO";
            $linkSt = 'warning';
        } else {
            $linkScore = 0;
            $linkMsg = 'No links found — add internal and external links';
            $linkSt = 'bad';
        }
        $checks[] = ['name' => 'Links', 'score' => $linkScore, 'max' => 5, 'status' => $linkSt, 'message' => $linkMsg];
        $totalPoints += $linkScore;
        $maxPoints += 5;

        // ── 11. Image alt tags (max 5pts) ──
        preg_match_all('/<img\s[^>]*/i', $body, $imgMatches);
        $imgCount = count($imgMatches[0] ?? []);
        $imgsWithAlt = 0;
        foreach (($imgMatches[0] ?? []) as $img) {
            if (preg_match('/alt\s*=\s*["\'][^"\']+/i', $img)) $imgsWithAlt++;
        }
        if ($imgCount === 0) {
            $imgScore = 2;
            $imgMsg = 'No images — consider adding relevant images';
            $imgSt = 'warning';
        } elseif ($imgsWithAlt === $imgCount) {
            $imgScore = 5;
            $imgMsg = "All $imgCount images have alt text";
            $imgSt = 'good';
        } else {
            $imgScore = 2;
            $imgMsg = "$imgsWithAlt of $imgCount images have alt text";
            $imgSt = 'warning';
        }
        $checks[] = ['name' => 'Image Alt Tags', 'score' => $imgScore, 'max' => 5, 'status' => $imgSt, 'message' => $imgMsg];
        $totalPoints += $imgScore;
        $maxPoints += 5;

        // ── Calculate final score 0-100 ──
        $finalScore = $maxPoints > 0 ? (int)round(($totalPoints / $maxPoints) * 100) : 0;

        // Summary
        if ($finalScore >= 80) $summary = 'Excellent SEO optimization';
        elseif ($finalScore >= 60) $summary = 'Good SEO, some improvements needed';
        elseif ($finalScore >= 40) $summary = 'Average SEO, significant improvements recommended';
        else $summary = 'Poor SEO, major optimization needed';

        return [
            'score'      => $finalScore,
            'checks'     => $checks,
            'summary'    => $summary,
            'word_count' => $wordCount,
            'points'     => $totalPoints,
            'max_points' => $maxPoints,
        ];
    }
}
