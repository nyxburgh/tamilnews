<?php
namespace App\Controllers\Frontend;

use App\Core\{Controller, Database};

class PhotoNewsController extends Controller
{
    public function index(): void
    {
        $db  = Database::getInstance();
        $per = 24;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $off  = ($page - 1) * $per;

        $total = (int)$db->query(
            "SELECT COUNT(*) FROM tn_photo_news
             WHERE status='published' AND approval_status='approved'
               AND image_path IS NOT NULL AND image_path != ''"
        )->fetchColumn();

        $stmt = $db->prepare(
            "SELECT pn.id, pn.title, pn.slug, pn.image_path, pn.created_at,
                    a.slug AS article_slug
             FROM tn_photo_news pn
             LEFT JOIN tn_articles a ON a.id = pn.article_id AND a.status = 'published'
             WHERE pn.status='published' AND pn.approval_status='approved'
               AND pn.image_path IS NOT NULL AND pn.image_path != ''
             ORDER BY pn.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$per, $off]);
        $cards = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('frontend.photo_news.index', [
            'pageTitle' => 'பட செய்திகள் | Photo News — Thinathulir',
            'metaDesc'  => 'தினத்துளிர் பட செய்திகள் — Visual news stories.',
            'cards'     => $cards,
            'total'     => $total,
            'page'      => $page,
            'per'       => $per,
            'noSidebar' => true,
        ], 'frontend');
    }
}
