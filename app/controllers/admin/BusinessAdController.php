<?php
namespace App\Controllers\Admin;

use App\Core\{Controller, Auth, CSRF, Helper, Database};
use App\Models\{BusinessAdModel, LocationModel, CategoryModel, AdModel, NotificationModel, AdPackageModel};

class BusinessAdController extends Controller
{
    private BusinessAdModel $ads;
    private LocationModel   $locations;
    private \PDO            $db;

    protected function layout(): string
    {
        $role = \App\Core\Auth::role();
        if ($role === 'admin')        return 'admin';
        if ($role === 'chief_editor') return 'editor_portal';
        return 'portal';
    }

    private function portalBase(): string
    {
        return \App\Core\Auth::role() === 'admin' ? '/admin/business-ads' : '/portal/ads';
    }

    public function __construct()
    {
        $this->requireRole('admin','chief_editor','editor','district_editor','category_editor','reporter');
        $this->ads       = new BusinessAdModel();
        $this->locations = new LocationModel();
        $this->db        = Database::getInstance();
    }

    // ── List all ads (role-filtered) ─────────────────────────

    public function index(): void
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $status  = $_GET['status'] ?? '';
        $filters = [];

        // Non-admins/chief-editors see only their own ads
        if (!in_array(Auth::role(), ['admin','chief_editor'])) {
            $filters['submitted_by'] = Auth::id();
        }
        if ($status) $filters['status'] = $status;

        try {
            $result = $this->ads->listPaginated($filters, $page, 15);
        } catch (\Exception $e) {
            // Log the real error for debugging
            error_log('BusinessAd error: ' . $e->getMessage());
            $this->flash('warning', 'Could not load ads: ' . htmlspecialchars($e->getMessage()));
            $result = ['data'=>[], 'total'=>0, 'page'=>1, 'per_page'=>15];
        }

        $upgradeRequests = [];
        if (in_array(Auth::role(), ['admin','chief_editor'])) {
            try {
                $upgradeRequests = (new \App\Models\AdPackageModel())->pendingUpgradeRequests();
            } catch (\Exception $e) {}
        }

        $this->view('admin.business_ads.index', [
            'pageTitle'       => 'Business Ads',
            'result'          => $result,
            'ads'             => $result['data'],
            'total'           => $result['total'],
            'page'            => $result['page'],
            'per_page'        => $result['per_page'],
            'status'          => $status,
            'canApprove'      => Auth::can('approve_escalated'),
            'upgradeRequests' => $upgradeRequests,
        ], $this->layout());
    }

    // ── Create form ──────────────────────────────────────────

    public function create(): void
    {
        $this->view('admin.business_ads.form', [
            'pageTitle' => 'New Business Ad',
            'ad'        => [],
            'districts' => $this->locations->allDistricts(),
            'categories'=> (new CategoryModel())->allWithParent(),
            'slots'     => (new AdModel())->allSlots(),
            'packages'  => (new AdPackageModel())->active(),
            'isEdit'    => false,
        ], $this->layout());
    }

    // ── Store new ad ─────────────────────────────────────────

    public function store(): void
    {
        CSRF::validate();

        $packageId = (int)$this->post('package_id', 0) ?: null;
        $slotId    = (int)$this->post('slot_id', 0);
        // Client JS sets slot_id via data-slot-id on the package option, but
        // tn_ad_packages has no slot_id column (only slot_type) — that value
        // is always 0. Resolve authoritatively from the package's slot_type.
        if (!$slotId && $packageId) {
            $slotId = $this->resolveSlotId($packageId);
        }

        $data = [
            'business_name'  => Helper::sanitize($this->post('business_name','')),
            'contact_phone'  => Helper::sanitize($this->post('contact_phone','')),
            'contact_email'  => Helper::sanitize($this->post('contact_email','')),
            'district_id'    => (int)$this->post('district_id',0) ?: null,
            'package_id'      => $packageId,
            'slot_id'        => $slotId,
            'display_type'   => $this->post('display_type','global'),
            'category_id'    => (int)$this->post('category_id',0) ?: null,
            'valid_from'     => $this->post('valid_from', date('Y-m-d')),
            'valid_until'    => $this->post('valid_until', date('Y-m-d', strtotime('+30 days'))),
            'payment_amount' => (float)$this->post('payment_amount',0) ?: null,
            'payment_status' => $this->post('payment_status','pending'),
            'payment_note'   => Helper::sanitize($this->post('payment_note','')),
            'notes'          => Helper::sanitize($this->post('notes','')),
        ];

        if (!$data['business_name'] || !$data['slot_id']) {
            $this->flash('danger','Business name and ad slot (package) are required.');
            $this->redirect($this->portalBase().'/create');
        }

        if (empty($data['contact_phone']) && empty($data['contact_email'])) {
            $this->flash('danger','At least one contact method (phone or email) is required.');
            $this->redirect($this->portalBase().'/create');
        }

        $adId = $this->ads->submit($data, Auth::id());

        if (!$adId) {
            $this->flash('danger','Failed to create ad. Please try again.');
            $this->redirect($this->portalBase().'/create');
        }

        // Upload images if provided
        if (!empty($_FILES['images']['name'][0])) {
            $slotType = (new AdModel())->slotType((int)$data['slot_id']);
            foreach ($_FILES['images']['name'] as $i => $name) {
                if (!$name) continue;
                $file = [
                    'name'     => $_FILES['images']['name'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error'    => $_FILES['images']['error'][$i],
                    'size'     => $_FILES['images']['size'][$i],
                ];
                $linkUrl = $this->post('link_url_'.$i, '');
                $this->ads->uploadImage($adId, $file, $linkUrl, '', $slotType);
            }
        }

        // Attach images picked from the media library
        $existingMediaJson = $this->post('existing_media', '');
        if ($existingMediaJson) {
            $paths = json_decode($existingMediaJson, true);
            if (is_array($paths)) {
                $this->ads->attachExistingImages($adId, $paths);
            }
        }

        // Notify chief editors for approval
        try {
            (new NotificationModel())->notifyChiefEditors(
                'business_ad_submitted',
                'New business ad submitted: "' . $data['business_name'] . '" — needs approval',
                $adId,
                Auth::id()
            );
        } catch (\Exception $e) {}

        // Auto-approve for admin and chief_editor
        $role = Auth::role();
        if (in_array($role, ['admin', 'chief_editor'])) {
            $this->ads->approve($adId, Auth::id());
            $this->flash('success', 'Ad created and approved. Add package and owner login below.');
        } else {
            $this->flash('success', 'Ad submitted. Chief editor will review and confirm payment.');
        }
        $this->redirect($this->portalBase().'/show/' . $adId);
    }

    // ── Show detail ──────────────────────────────────────────

    public function show(string $id): void
    {
        $ad = $this->ads->findWithDetails((int)$id);
        if (!$ad) { $this->flash('danger','Ad not found.'); $this->redirect($this->portalBase()); }

        // Own or admin/chief
        if (!in_array(Auth::role(),['admin','chief_editor']) && $ad['submitted_by'] != Auth::id()) {
            $this->flash('danger','Access denied.'); $this->redirect($this->portalBase());
        }

        $this->view('admin.business_ads.show', [
            'pageTitle'  => 'Ad Details — ' . $ad['business_name'],
            'ad'         => $ad,
            'canApprove' => Auth::can('approve_escalated'),
            'canEdit'    => in_array(Auth::role(),['admin','chief_editor'])
                            || $ad['submitted_by'] == Auth::id(),
        ], $this->layout());
    }

    // ── Edit form ────────────────────────────────────────────

    public function edit(string $id): void
    {
        $ad = $this->ads->findWithDetails((int)$id);
        if (!$ad) { $this->flash('danger','Not found.'); $this->redirect($this->portalBase()); }
        if (!in_array(Auth::role(),['admin','chief_editor']) && $ad['submitted_by'] != Auth::id()) {
            $this->flash('danger','Access denied.'); $this->redirect($this->portalBase());
        }

        $this->view('admin.business_ads.form', [
            'pageTitle'  => 'Edit Ad — ' . $ad['business_name'],
            'ad'         => $ad,
            'districts'  => $this->locations->allDistricts(),
            'categories' => (new CategoryModel())->allWithParent(),
            'slots'      => (new AdModel())->allSlots(),
            'packages'   => (new AdPackageModel())->active(),
            'isEdit'     => true,
        ], $this->layout());
    }

    // ── Update ───────────────────────────────────────────────

    public function update(string $id): void
    {
        CSRF::validate();
        $ad = $this->ads->find((int)$id);
        if (!$ad) { $this->flash('danger','Not found.'); $this->redirect($this->portalBase()); }

        $data = $this->buildAdData();
        $this->ads->update((int)$id, $data);

        // Upload new images if provided
        if (!empty($_FILES['images']['name'][0])) {
            $slotType = (new AdModel())->slotType((int)$data['slot_id']);
            foreach ($_FILES['images']['name'] as $i => $name) {
                if (!$name) continue;
                $file = [
                    'name'     => $_FILES['images']['name'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error'    => $_FILES['images']['error'][$i],
                    'size'     => $_FILES['images']['size'][$i],
                ];
                $this->ads->uploadImage((int)$id, $file, '', '', $slotType);
            }
        }

        // Single-step activation
        if ($this->post('activate_now') && Auth::can('approve_escalated')) {
            $this->ads->approve((int)$id, Auth::id());
            $this->ads->confirmPayment((int)$id, Auth::id(), $data['payment_note'] ?? '');
            $this->flash('success', 'Ad updated, payment confirmed and ad is now live.');
        } else {
            $this->flash('success', 'Ad updated successfully.');
        }

        $this->redirect($this->portalBase() . '/show/' . $id);
    }

    // ── Shared: build ad data array from POST ────────────────

    private function buildAdData(): array
    {
        $packageId = (int)$this->post('package_id', 0) ?: null;
        $slotId    = (int)$this->post('slot_id', 0);
        if (!$slotId && $packageId) {
            $slotId = $this->resolveSlotId($packageId);
        }

        return [
            'business_name'  => Helper::sanitize($this->post('business_name','')),
            'contact_phone'  => Helper::sanitize($this->post('contact_phone','')),
            'contact_email'  => Helper::sanitize($this->post('contact_email','')),
            'district_id'    => (int)$this->post('district_id',0) ?: null,
            'package_id'     => $packageId,
            'slot_id'        => $slotId,
            'display_type'   => $this->post('display_type','global'),
            'category_id'    => (int)$this->post('category_id',0) ?: null,
            'valid_from'     => $this->post('valid_from'),
            'valid_until'    => $this->post('valid_until'),
            'payment_amount' => (float)$this->post('payment_amount',0) ?: null,
            'payment_note'   => Helper::sanitize($this->post('payment_note','')),
            'notes'          => Helper::sanitize($this->post('notes','')),
        ];
    }

    // ── Resolve slot_id from a package's slot_type ────────────
    // tn_ad_packages has no slot_id column (only slot_type ENUM).
    // Look up a matching active slot in tn_ad_slots by type.

    private function resolveSlotId(int $packageId): int
    {
        $pkg = (new AdPackageModel())->find($packageId);
        if (!$pkg) return 0;

        $slotType = $pkg['slot_type'] ?? 'any';
        $db = $this->db;

        if ($slotType !== 'any') {
            $stmt = $db->prepare(
                "SELECT id FROM tn_ad_slots WHERE type = ? AND is_active = 1 ORDER BY id LIMIT 1"
            );
            $stmt->execute([$slotType]);
            $id = (int)$stmt->fetchColumn();
            if ($id) return $id;
        }

        // 'any' or no match for the specific type — fall back to any active slot
        return (int)$db->query(
            "SELECT id FROM tn_ad_slots WHERE is_active = 1 ORDER BY id LIMIT 1"
        )->fetchColumn();
    }

    // ── Approve (chief editor / admin) ───────────────────────

    public function approve(string $id): void
    {
        CSRF::validate();
        $this->requireCan('approve_escalated');
        $ad = $this->ads->find((int)$id);
        if (!$ad) { $this->flash('danger','Not found.'); $this->redirect($this->portalBase()); }

        $this->ads->approve((int)$id, Auth::id());

        // Notify submitter
        try {
            (new NotificationModel())->send(
                $ad['submitted_by'],
                'business_ad_approved',
                'Your business ad has been approved!',
                (int)$id, Auth::id()
            );
        } catch (\Exception $e) {}

        $this->flash('success','Ad approved successfully.');
        $this->redirect($this->portalBase().'/show/' . $id);
    }

    // ── Reject ───────────────────────────────────────────────

    public function reject(string $id): void
    {
        CSRF::validate();
        $this->requireCan('approve_escalated');
        $reason = Helper::sanitize($this->post('reason',''));
        $ad     = $this->ads->find((int)$id);
        if (!$ad) { $this->flash('danger','Not found.'); $this->redirect($this->portalBase()); }

        $this->ads->reject((int)$id, Auth::id(), $reason);

        try {
            (new NotificationModel())->send(
                $ad['submitted_by'],
                'business_ad_rejected',
                'Your business ad was rejected. ' . ($reason ? 'Reason: '.$reason : ''),
                (int)$id, Auth::id()
            );
        } catch (\Exception $e) {}

        $this->flash('success','Ad rejected.');
        $this->redirect($this->portalBase());
    }

    // ── Confirm payment ──────────────────────────────────────

    public function confirmPayment(string $id): void
    {
        CSRF::validate();
        $this->requireCan('approve_escalated');
        $note   = Helper::sanitize($this->post('payment_note',''));
        $amount = (float)$this->post('payment_amount', 0);
        $ad     = $this->ads->findWithDetails((int)$id);
        if ($ad && $ad['status'] === 'pending') {
            $this->ads->approve((int)$id, Auth::id());
        }
        if ($amount > 0) {
            $this->ads->update((int)$id, ['payment_amount' => $amount]);
        }
        $this->ads->confirmPayment((int)$id, Auth::id(), $note);
        $this->flash('success', 'Payment confirmed. Ad is now active.');
        $this->redirect($this->portalBase().'/show/' . $id);
    }


    // ── Delete entire ad + files ─────────────────────────────
    public function delete(string $adId): void
    {
        \App\Core\CSRF::validate();
        $id = (int)$adId;
        $ad = $this->ads->find($id);
        if (!$ad) {
            $this->flash('danger', 'Ad not found.');
            $this->redirect($this->portalBase());
        }
        if (!in_array(\App\Core\Auth::role(), ['admin','chief_editor'])
            && (int)$ad['submitted_by'] !== \App\Core\Auth::id()) {
            $this->flash('danger', 'Access denied.');
            $this->redirect($this->portalBase());
        }
        $this->ads->deleteWithFiles($id);
        $this->flash('success', 'Ad deleted. All images removed.');
        $this->redirect($this->portalBase());
    }

    // ── Delete image ─────────────────────────────────────────

    public function deleteImage(string $id): void
    {
        CSRF::validate();
        $adId    = (int)$this->post('ad_id',0);
        $imageId = (int)$id;
        $ad      = $this->ads->find($adId);

        if (!$ad) { $this->json(['error'=>'Not found']); return; }
        // Allow: admin/chief OR owner OR auto_approve staff
        $user = Auth::user();
        $isOwner   = (int)$ad['submitted_by'] === Auth::id();
        $isManager = in_array(Auth::role(), ['admin','chief_editor','editor']);
        $canSelfManage = !empty($user['auto_approve']);
        if (!$isManager && !$isOwner && !$canSelfManage) {
            $this->json(['error'=>'Access denied']); return;
        }

        $this->ads->deleteImage($imageId, $adId);
        $this->json(['success' => true]);
    }

    // ── Ad owner login — direct on ad (no subscription required) ────

    public function createOwnerLogin(string $id): void
    {
        $ad = $this->ads->findWithDetails((int)$id);
        if (!$ad) { $this->flash('danger','Ad not found.'); $this->redirect($this->portalBase()); }
        // Load existing owner if any
        $ownerUser = null;
        if (!empty($ad['owner_user_id'])) {
            try {
                $r = $this->db->prepare("SELECT id,name,email FROM tn_users WHERE id=?");
                $r->execute([$ad['owner_user_id']]);
                $ownerUser = $r->fetch(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {}
        }
        $this->view('admin.business_ads.owner_login', [
            'ad'        => $ad,
            'ownerUser' => $ownerUser,
            'pageTitle' => 'Owner Login — ' . $ad['business_name'],
        ], $this->layout());
    }

    public function storeOwnerLogin(string $id): void
    {
        CSRF::validate();
        $name     = Helper::sanitize($this->post('name',''));
        $email    = filter_var($this->post('email',''), FILTER_SANITIZE_EMAIL);
        $password = $this->post('password','');

        if (!$name || !$email || strlen($password) < 8) {
            $this->flash('danger','Name, valid email and password (min 8 chars) required.');
            $this->redirect($this->portalBase().'/show/'.$id.'#acc-owner');
        }

        $chk = $this->db->prepare("SELECT id FROM tn_users WHERE email=? LIMIT 1");
        $chk->execute([$email]);
        if ($chk->fetch()) {
            $this->flash('danger','Email already in use.');
            $this->redirect($this->portalBase().'/show/'.$id.'#acc-owner');
        }

        $roleRow = $this->db->prepare("SELECT id FROM tn_roles WHERE slug='ad_owner' LIMIT 1");
        $roleRow->execute();
        $role = $roleRow->fetch(\PDO::FETCH_ASSOC);
        if (!$role) {
            $this->flash('danger','ad_owner role missing. Run ad_owner_column.sql migration.');
            $this->redirect($this->portalBase().'/show/'.$id.'#acc-owner');
        }

        $stmt = $this->db->prepare(
            "INSERT INTO tn_users (role_id,name,email,password,is_active,created_at) VALUES (?,?,?,?,1,NOW())"
        );
        $stmt->execute([$role['id'], $name, $email, password_hash($password, PASSWORD_BCRYPT)]);
        $userId = (int)$this->db->lastInsertId();

        // Link owner directly to the ad
        $this->ads->update((int)$id, ['owner_user_id' => $userId]);

        $this->flash('success','Owner login created. Share credentials: email='.$email);
        $this->redirect($this->portalBase().'/show/'.$id.'#acc-owner');
    }

    public function resetOwnerPasswordAd(string $id): void
    {
        CSRF::validate();
        $userId   = (int)$this->post('user_id',0);
        $newPass  = $this->post('new_password','');
        if (!$userId || strlen($newPass) < 8) {
            $this->flash('danger','Password must be at least 8 characters.');
            $this->redirect($this->portalBase().'/show/'.$id.'#acc-owner');
        }
        $this->db->prepare("UPDATE tn_users SET password=? WHERE id=?")
                 ->execute([password_hash($newPass, PASSWORD_BCRYPT), $userId]);
        $this->flash('success','Password reset successfully.');
        $this->redirect($this->portalBase().'/show/'.$id.'#acc-owner');
    }

    // ── Sponsored news approval queue ────────────────────────

    public function sponsoredNewsQueue(): void
    {
        $queue   = (new AdPackageModel())->allPendingSponsoredNews();
        $adsBase = $this->portalBase();
        $this->view('admin.business_ads.sponsored_queue', [
            'queue'     => $queue,
            'adsBase'   => $adsBase,
            'pageTitle' => 'Sponsored News Queue',
        ], $this->layout());
    }
        public function approveSponsoredNews(string $newsId): void
    {
        CSRF::validate();
        $this->requireCan('approve_escalated');
        $db = \App\Core\Database::getInstance();
        $db->prepare(
            "UPDATE tn_sponsored_news SET status='approved', approved_by=?, approved_at=NOW() WHERE id=?"
        )->execute([Auth::id(), (int)$newsId]);
        $row = $db->prepare("SELECT article_id FROM tn_sponsored_news WHERE id=?");
        $row->execute([(int)$newsId]);
        $r = $row->fetch(\PDO::FETCH_ASSOC);
        if ($r) {
            $db->prepare("UPDATE tn_articles SET status='published', published_at=NOW() WHERE id=?")
               ->execute([$r['article_id']]);
        }
        $this->flash('success', 'Sponsored article approved and published.');
        $this->redirect($this->portalBase() . '/sponsored-news');
    }

    public function rejectSponsoredNews(string $newsId): void
    {
        CSRF::validate();
        $this->requireCan('approve_escalated');
        $reason = Helper::sanitize($this->post('reason',''));
        $db = \App\Core\Database::getInstance();
        $row = $db->prepare("SELECT article_id FROM tn_sponsored_news WHERE id=?");
        $row->execute([(int)$newsId]);
        $r = $row->fetch(\PDO::FETCH_ASSOC);
        $db->prepare(
            "UPDATE tn_sponsored_news SET status='rejected', rejection_reason=?, approved_by=?, approved_at=NOW() WHERE id=?"
        )->execute([$reason, Auth::id(), (int)$newsId]);
        if ($r) {
            $db->prepare("UPDATE tn_articles SET status='rejected' WHERE id=?")->execute([$r['article_id']]);
        }
        $this->flash('info', 'Sponsored article rejected.');
        $this->redirect($this->portalBase() . '/sponsored-news');
    }

    public function approveUpgradeRequest(string $reqId): void
    {
        $this->requireCan('manage_ads');
        CSRF::validate();
        $pkg = new \App\Models\AdPackageModel();
        $req = $pkg->findRequest((int)$reqId);
        if (!$req) { $this->flash('danger','Request not found.'); $this->redirect($this->portalBase()); }

        // Update the ad's package
        $this->ads->update($req['ad_id'], ['package_id' => $req['requested_pkg_id']]);

        // Mark request approved
        $pkg->updateRequest((int)$reqId, [
            'status'      => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);

        $this->flash('success', 'Package upgraded and confirmed.');
        $this->redirect($this->portalBase());
    }

    public function rejectUpgradeRequest(string $reqId): void
    {
        $this->requireCan('manage_ads');
        CSRF::validate();
        $pkg = new \App\Models\AdPackageModel();
        $pkg->updateRequest((int)$reqId, [
            'status'      => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);
        $this->flash('info', 'Upgrade request rejected.');
        $this->redirect($this->portalBase());
    }

    // ── AJAX: cities by district ─────────────────────────────

    public function citiesByDistrict(string $districtId): void
    {
        $cities = $this->locations->allCities((int)$districtId);
        $this->json($cities);
    }
}
