<?php
namespace App\Controllers\Frontend;

use App\Core\{Controller, Session, Helper};
use App\Models\{ReaderModel, RatingModel, ArticleModel};

class ReaderProfileController extends Controller
{
    // ── T&C Agreement page ─────────────────────────────────

    public function agree(): void
    {
        $readerId = \App\Core\Session::get('reader_id');
        if (!$readerId) { Helper::redirect('/auth/reader/login'); }

        $reader = \App\Core\Session::get('reader') ?? [];
        if (!empty($reader['has_agreed_terms'])) {
            Helper::redirect('/reader/profile');
        }

        $districts = \App\Core\Database::getInstance()
            ->query("SELECT id, name FROM tn_districts ORDER BY name")
            ->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('frontend.reader.agree', [
            'pageTitle' => 'Complete Setup',
            'reader'    => $reader,
            'districts' => $districts,
            'noSidebar' => true,
        ], 'frontend');
    }

    public function saveAgree(): void
    {
        \App\Core\CSRF::validate();
        $readerId   = \App\Core\Session::get('reader_id');
        if (!$readerId) { Helper::redirect('/auth/reader/login'); }

        $districtId = (int)($_POST['district_id'] ?? 0);
        $agreed     = !empty($_POST['agreed']);

        if (!$agreed || !$districtId) {
            \App\Core\Session::flash('alert_type', 'danger');
            \App\Core\Session::flash('alert_msg', 'Please select your district and agree to terms.');
            Helper::redirect('/reader/agree');
        }

        $db = \App\Core\Database::getInstance();
        $db->prepare(
            "UPDATE tn_readers SET district_id=?, has_agreed_terms=1, agreed_at=NOW() WHERE id=?"
        )->execute([$districtId, $readerId]);

        // Refresh session
        $reader = $db->prepare("SELECT * FROM tn_readers WHERE id=? LIMIT 1");
        $reader->execute([$readerId]);
        $row = $reader->fetch(\PDO::FETCH_ASSOC);
        \App\Core\Session::set('reader', $row);

        Helper::redirect('/reader/profile');
    }
    public function index(): void
    {
        $readerId = Session::get('reader_id');
        if (!$readerId) {
            Helper::redirect('/auth/reader/login?return=/reader/profile');
        }

        $reader = Session::get('reader') ?? (new ReaderModel())->find((int)$readerId);

        // Recent ratings by this reader
        $ratings = [];
        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare(
                "SELECT r.rating, r.review, r.created_at,
                        a.title, a.slug, a.published_at, a.image_url
                 FROM tn_ratings r
                 JOIN tn_articles a ON a.id = r.article_id
                 WHERE r.reader_id = ?
                 ORDER BY r.created_at DESC LIMIT 10"
            );
            $stmt->execute([(int)$readerId]);
            $ratings = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        $this->view('frontend.reader.profile', [
            'pageTitle' => 'My Profile',
            'reader'    => $reader,
            'ratings'   => $ratings,
            'noSidebar' => true,
        ], 'frontend');
    }
}
