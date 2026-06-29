<?php
// Partial: render daftar comment cards.
// Caller wajib sudah set variabel $comments (array hasil fetchAll) sebelum include file ini.

if (empty($comments)) {
    echo '<div style="text-align:center;padding:60px 0;color:var(--text-dim)"><div style="font-size:48px;margin-bottom:12px">💬</div><p>Belum ada komentar. Jadilah yang pertama!</p></div>';
} else {
    foreach ($comments as $c) {
        echo '<div class="comment-card">';
        echo '<div class="comment-header">';
        echo '<div class="comment-avatar">' . strtoupper(mb_substr($c['name'], 0, 1)) . '</div>';
        echo '<div><div class="comment-name">' . clean($c['name']) . '</div>';
        echo '<div class="comment-date">' . date('d M Y · H:i', strtotime($c['created_at'])) . '</div></div>';
        echo '<div class="comment-stars">';
        for ($i = 1; $i <= 5; $i++) {
            echo '<span style="color:' . ($i <= $c['rating'] ? '#f5c518' : 'var(--border)') . '">★</span>';
        }
        echo '</div></div>';
        echo '<div class="comment-message">' . nl2br(clean($c['message'])) . '</div>';
        echo '</div>';
    }
}
