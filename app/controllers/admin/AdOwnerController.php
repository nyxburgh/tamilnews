<?php
namespace App\Controllers\Admin;

use App\Core\{Controller, CSRF, Auth};
use App\Models\{AdPackageModel, BusinessAdModel, AdModel, ArticleModel, FrontendArticleModel, NotificationModel};
use App\Core\Helper;

class AdOwnerController extends Controller
{
    protected function layout(): string { return 'portal'; }

    private AdPackageModel $pkg;
    private BusinessAdModel $ads;

    public function __construct()
    {
        $this->pkg = new AdPackageModel();
        $this->ads = new BusinessAdModel();
    }

    public function middleware(): void
    {
        if (!Auth::check()) $this->redirect('/login');
        if (Auth::role() !== 'ad_owner') $this->redirect('/login');
    }

    // ── Dashboard ────────────────────────────────────────────

    public function dashboard(): void
    {
        // Load ads directly owned by this user
        $db   = \App\Core\Database::getInstance();
        $stmt = $db->prepare(
            "SELECT b.*, s.name AS slot_name, s.type AS slot_type,
                    p.name AS package_name, p.name_tamil AS package_name_tamil
             FROM tn_business_ads b
             LEFT JOIN tn_ad_slots s    ON s.id = b.slot_id
             LEFT JOIN tn_ad_packages p ON p.id = b.package_id
             WHERE b.owner_user_id = ?
             ORDER BY b.created_at DESC"
        );
        $stmt->execute([Auth::id()]);
        $ads = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $totalViews  = 0;
        $totalClicks = 0;
        foreach ($ads as $a) {
            $totalViews  += (int)($a['impression_count'] ?? 0);
            $totalClicks += (int)($a['click_count'] ?? 0);
        }

        $this->view('admin.ad_owner.dashboard', [
            'ads'         => $ads,
            'totalViews'  => $totalViews,
            'totalClicks' => $totalClicks,
            'pageTitle'   => 'My Ads',
        ], $this->layout());
    }

    // ── Single ad detail ─────────────────────────────────────

    public function subscription(string $adId): void
    {
        // $adId here is the business ad id, not subscription id
        $db   = \App\Core\Database::getInstance();
        $stmt = $db->prepare(
            "SELECT b.*, s.name AS slot_name, s.type AS slot_type,
                    p.name AS package_name, p.allow_images, p.max_images,
                    p.image_change_days, p.allow_news, p.news_quota,
                    p.news_interval_days, p.rate_per_day
             FROM tn_business_ads b
             LEFT JOIN tn_ad_slots s    ON s.id = b.slot_id
             LEFT JOIN tn_ad_packages p ON p.id = b.package_id
             WHERE b.id = ? AND b.owner_user_id = ?"
        );
        $stmt->execute([(int)$adId, Auth::id()]);
        $ad = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$ad) {
            $this->flash('danger', 'Ad not found or access denied.');
            $this->redirect('/portal/my-ads');
        }

        $ad['images'] = $this->ads->images((int)$adId);

        // Load sponsored news for this ad
        $db   = \App\Core\Database::getInstance();
        $stmt = $db->prepare(
            "SELECT sn.*, a.title, a.created_at
             FROM tn_sponsored_news sn
             JOIN tn_articles a ON a.id = sn.article_id
             WHERE sn.ad_id = ?
             ORDER BY sn.created_at DESC"
        );
        $stmt->execute([(int)$adId]);
        $news = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $newsCheck = $this->checkNewsQuota($ad);
        if (!empty($ad['allow_images'])) {
            if (empty($ad['image_last_changed'])) {
                $canChangeImage = true;
            } else {
                $daysSince = (int)floor((time() - strtotime($ad['image_last_changed'])) / 86400);
                $canChangeImage = $daysSince >= (int)($ad['image_change_days'] ?? 30);
            }
        }

        $this->view('admin.ad_owner.subscription', [
            'ad'             => $ad,
            'sub'            => $ad,
            'canChangeImage' => $canChangeImage,
            'canPostNews'    => $newsCheck === true,
            'newsBlockReason'=> is_string($newsCheck) ? $newsCheck : null,
            'news'           => $news,
            'pageTitle'      => $ad['business_name'],
        ], $this->layout());
    }

    // ── Image management ─────────────────────────────────────

    public function uploadImage(string $adId): void
    {
        CSRF::validate();
        $db   = \App\Core\Database::getInstance();
        $stmt = $db->prepare(
            "SELECT b.*, p.allow_images, p.image_change_days, p.max_images
             FROM tn_business_ads b
             LEFT JOIN tn_ad_packages p ON p.id = b.package_id
             WHERE b.id = ? AND b.owner_user_id = ?"
        );
        $stmt->execute([(int)$adId, Auth::id()]);
        $ad = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$ad) { $this->flash('danger','Access denied.'); $this->redirect('/portal/my-ads'); }

        if (empty($ad['allow_images'])) {
            $this->flash('danger', 'Image upload not included in your package.');
            $this->redirect('/portal/my-ads/'.$adId);
        }

        // Check monthly limit
        if (!empty($ad['image_last_changed'])) {
            $daysSince = (int)floor((time() - strtotime($ad['image_last_changed'])) / 86400);
            if ($daysSince < (int)($ad['image_change_days'] ?? 30)) {
                $this->flash('danger', 'Image change not available yet (monthly limit).');
                $this->redirect('/portal/my-ads/'.$adId);
            }
        }

        if (empty($_FILES['image']['name'])) {
            $this->flash('danger', 'Please select an image.');
            $this->redirect('/portal/my-ads/'.$adId);
        }

        $slotType = $db->prepare("SELECT type FROM tn_ad_slots WHERE id=?");
        $slotType->execute([$ad['slot_id']]);
        $slot = $slotType->fetchColumn();

        $result = $this->ads->uploadImage((int)$adId, $_FILES['image'], '', '', $slot ?: 'square');
        if (!$result) {
            $this->flash('danger', 'Upload failed. Check file type (JPG/PNG/WebP) and size (max 5MB).');
            $this->redirect('/portal/my-ads/'.$adId);
        }

        // Update image_last_changed on the ad
        $db->prepare("UPDATE tn_business_ads SET image_last_changed=NOW() WHERE id=?")
           ->execute([(int)$adId]);

        $db->prepare("UPDATE tn_business_ads SET status='pending_image_change' WHERE id=?")
           ->execute([(int)$adId]);

        $this->flash('success', 'Image uploaded. Will go live after editorial approval.');
        $this->redirect('/portal/my-ads/'.$adId);
    }
    // ── Sponsored news ───────────────────────────────────────

    // ── Helpers ──────────────────────────────────────────────

    private function getOwnedAd(int $adId): array
    {
        $db   = \App\Core\Database::getInstance();
        $stmt = $db->prepare(
            "SELECT b.*, s.type AS slot_type,
                    p.name AS package_name, p.allow_images, p.max_images,
                    p.image_change_days, p.allow_news, p.news_quota,
                    p.news_interval_days, p.is_trial, p.rate_per_day, p.slot_type AS pkg_slot
             FROM tn_business_ads b
             LEFT JOIN tn_ad_slots s    ON s.id = b.slot_id
             LEFT JOIN tn_ad_packages p ON p.id = b.package_id
             WHERE b.id = ? AND b.owner_user_id = ?"
        );
        $stmt->execute([$adId, Auth::id()]);
        $ad = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$ad) {
            $this->flash('danger', 'Access denied.');
            $this->redirect('/portal/my-ads');
        }
        return $ad;
    }

    private function checkNewsQuota(array $ad): bool|string
    {
        if (empty($ad['allow_news'])) return 'not_allowed';

        // Vertical: quota = selected_days (from ad or package max_days)
        $isVertical = ($ad['pkg_slot'] ?? $ad['slot_type'] ?? '') === 'vertical';
        $quota      = $isVertical
            ? (int)($ad['selected_days'] ?? $ad['news_quota'] ?? 0)
            : (int)($ad['news_quota'] ?? 0);

        if ((int)($ad['news_used'] ?? 0) >= $quota) return 'quota_exhausted';

        // Interval check
        $interval = (int)($ad['news_interval_days'] ?? 0);
        if ($interval > 0) {
            $db   = \App\Core\Database::getInstance();
            $stmt = $db->prepare(
                "SELECT MAX(created_at) FROM tn_sponsored_news
                 WHERE ad_id = ? AND status NOT IN ('rejected')"
            );
            $stmt->execute([$ad['id']]);
            $last = $stmt->fetchColumn();
            if ($last) {
                $daysSince = (int)floor((time() - strtotime($last)) / 86400);
                if ($daysSince < $interval) return 'too_soon';
            }
        }
        return true;
    }

    // ── Sponsored news ───────────────────────────────────────

    public function writeNews(string $adId): void
    {
        $db   = \App\Core\Database::getInstance();
        $stmt = $db->prepare(
            "SELECT b.*, p.allow_news, p.news_quota, p.news_interval_days
             FROM tn_business_ads b
             LEFT JOIN tn_ad_packages p ON p.id = b.package_id
             WHERE b.id = ? AND b.owner_user_id = ?"
        );
        $stmt->execute([(int)$adId, Auth::id()]);
        $ad = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$ad) { $this->flash('danger','Access denied.'); $this->redirect('/portal/my-ads'); }

        $quota = $this->pkg->canPostSponsoredNews((int)$adId);
        if (!$quota['allowed']) {
            $this->flash('danger', $quota['reason']);
            $this->redirect('/portal/my-ads/'.$adId);
        }

        $this->view('admin.ad_owner.write_news', [
            'ad'        => $ad,
            'quota'     => $quota,
            'pageTitle' => 'Write Sponsored Article',
        ], $this->layout());
    }

    public function submitNews(string $adId): void
    {
        CSRF::validate();
        $db   = \App\Core\Database::getInstance();
        $stmt = $db->prepare(
            "SELECT b.*, p.allow_news FROM tn_business_ads b
             LEFT JOIN tn_ad_packages p ON p.id = b.package_id
             WHERE b.id = ? AND b.owner_user_id = ?"
        );
        $stmt->execute([(int)$adId, Auth::id()]);
        $ad = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$ad || empty($ad['allow_news'])) {
            $this->flash('danger','Access denied.'); $this->redirect('/portal/my-ads');
        }

        $quota = $this->pkg->canPostSponsoredNews((int)$adId);
        if (!$quota['allowed']) {
            $this->flash('danger', $quota['reason']);
            $this->redirect('/portal/my-ads/'.$adId);
        }

        $title   = Helper::sanitize($this->post('title',''));
        $content = $this->post('content','');
        if (strlen($title) < 5 || strlen(strip_tags($content)) < 50) {
            $this->flash('danger','Title (min 5 chars) and content (min 50 chars) required.');
            $this->redirect('/portal/my-ads/'.$adId.'/write-news');
        }

        $slug = \App\Core\Helper::slug($title) . '-' . time();

        $categoryId = (int)($ad['category_id'] ?? 0) ?: 1;

        $articleId = (new ArticleModel())->store([
            'user_id'      => Auth::id(),
            'category_id'  => $categoryId,
            'title'        => $title,
            'slug'         => $slug,
            'content'      => $content,
            'excerpt'      => mb_substr(strip_tags($content), 0, 200),
            'status'       => 'pending',
            'content_type' => 'sponsored',
            'meta_title'   => $title,
            'meta_desc'    => mb_substr(strip_tags($content), 0, 160),
        ]);

        $this->pkg->addSponsoredNews([
            'ad_id'      => (int)$adId,
            'article_id' => $articleId,
            'status'     => 'pending_approval',
        ]);

        $db->prepare("UPDATE tn_business_ads SET news_used = COALESCE(news_used,0)+1 WHERE id=?")
           ->execute([(int)$adId]);

        try {
            (new NotificationModel())->notifyChiefEditors(
                'sponsored_news',
                'Sponsored article submitted: "' . $title . '" by ' . ($ad['business_name'] ?? ''),
                $articleId, Auth::id()
            );
        } catch (\Exception $e) {}

        $this->flash('success','Sponsored article submitted for editorial review.');
        $this->redirect('/portal/my-ads/'.$adId);
    }
}
