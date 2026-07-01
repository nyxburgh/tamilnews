<?php
namespace App\Controllers\Admin;

use App\Core\{Controller, CSRF, Auth, Database};
use App\Models\{AdPackageModel, BusinessAdModel, NotificationModel};
use App\Core\Helper;

class AdSubscriptionController extends Controller
{
    protected function layout(): string
    {
        $role = \App\Core\Auth::role();
        if ($role === 'admin')        return 'admin';
        if ($role === 'chief_editor') return 'editor_portal';
        return 'portal';
    }

    private function adBase(): string
    {
        return \App\Core\Auth::role() === 'admin' ? '/admin/business-ads' : '/portal/ads';
    }

    private function subBase(string $subId = ''): string
    {
        $base = $this->adBase();
        return $subId ? $base . '/subscription/' . $subId : $base;
    }

    private AdPackageModel $pkg;
    private BusinessAdModel $ads;
    private \PDO $db;

    public function __construct()
    {
        $this->pkg = new AdPackageModel();
        $this->ads = new BusinessAdModel();
        $this->db  = Database::getInstance();
    }

    public function middleware(): void { $this->requireCan('manage_ads'); }

    // ── Assign a package to an ad ────────────────────────────

    public function assign(string $adId): void
    {
        $ad = $this->ads->findWithDetails((int)$adId);
        if (!$ad) { $this->flash('danger','Ad not found.'); $this->redirect(\$this->adBase()); }

        $packages = $this->pkg->active();
        $subs     = $this->pkg->subscriptionsByAd((int)$adId);

        $this->view('admin.ad_subscriptions.assign', [
            'ad'       => $ad,
            'packages' => $packages,
            'subs'     => $subs,
            'pageTitle'=> 'Assign Package — ' . $ad['business_name'],
        ], $this->layout());
    }

    public function storeAssign(string $adId): void
    {
        CSRF::validate();
        $ad = $this->ads->findWithDetails((int)$adId);
        if (!$ad) { $this->flash('danger','Ad not found.'); $this->redirect(\$this->adBase()); }

        $pkgId       = (int)$this->post('package_id', 0);
        $validFrom   = $this->post('valid_from', date('Y-m-d'));
        $selectedDays= (int)$this->post('selected_days', 0);
        $pkg         = $this->pkg->find($pkgId);

        if (!$pkg) { $this->flash('danger','Invalid package.'); $this->redirect(\$this->adBase() . '/' . \$adId . '/assign'); }

        // Calculate valid_until
        if ($pkg['is_trial']) {
            $validUntil = date('Y-m-d', strtotime($validFrom . ' +7 days'));
        } elseif ($pkg['slot_type'] === 'vertical') {
            $days = max($pkg['min_days'], min($pkg['max_days'] ?? 30, $selectedDays));
            $validUntil = date('Y-m-d', strtotime($validFrom . " +{$days} days"));
        } else {
            $validUntil = date('Y-m-d', strtotime($validFrom . " +{$pkg['min_days']} days"));
        }

        // Calculate amount
        $amount = $pkg['is_trial'] ? 0 :
                  ($pkg['slot_type'] === 'vertical' ? $pkg['rate_per_day'] * $selectedDays : $pkg['amount']);

        $subId = $this->pkg->subscribe([
            'ad_id'        => (int)$adId,
            'package_id'   => $pkgId,
            'assigned_by'  => Auth::id(),
            'status'       => 'pending',
            'amount_paid'  => $this->post('amount_paid', $amount),
            'valid_from'   => $validFrom,
            'valid_until'  => $validUntil,
            'selected_days'=> $selectedDays ?: null,
            'notes'        => Helper::sanitize($this->post('notes', '')),
        ]);

        $this->flash('success', "Package \"{$pkg['name']}\" assigned. Activate when payment confirmed.");
        $this->redirect(\$this->subBase(\$subId));
    }

    // ── View / manage one subscription ──────────────────────

    public function show(string $subId): void
    {
        $sub = $this->pkg->findSubscription((int)$subId);
        if (!$sub) { $this->flash('danger','Subscription not found.'); $this->redirect(\$this->adBase()); }

        $news = $this->pkg->sponsoredNewsBySubscription((int)$subId);

        $this->view('admin.ad_subscriptions.show', [
            'sub'       => $sub,
            'news'      => $news,
            'pageTitle' => 'Subscription — ' . $sub['business_name'],
        ], $this->layout());
    }

    public function activate(string $subId): void
    {
        CSRF::validate();
        $this->pkg->updateSubscription((int)$subId, [
            'status' => 'active',
        ]);
        $this->flash('success', 'Subscription activated.');
        $this->redirect(\$this->subBase(\$subId));
    }

    public function extend(string $subId): void
    {
        CSRF::validate();
        $sub     = $this->pkg->findSubscription((int)$subId);
        if (!$sub) { $this->flash('danger','Not found.'); $this->redirect(\$this->adBase()); }
        $days    = max(1, (int)$this->post('extend_days', 30));
        $newUntil= date('Y-m-d', strtotime($sub['valid_until'] . " +{$days} days"));
        $this->pkg->updateSubscription((int)$subId, [
            'valid_until' => $newUntil,
            'status'      => 'active',
        ]);
        $this->flash('success', "Package extended by {$days} days until {$newUntil}.");
        $this->redirect(\$this->subBase(\$subId));
    }

    public function suspend(string $subId): void
    {
        CSRF::validate();
        $this->pkg->updateSubscription((int)$subId, ['status' => 'suspended']);
        $this->flash('warning', 'Subscription suspended.');
        $this->redirect(\$this->subBase(\$subId));
    }

    // ── Create ad owner login ────────────────────────────────

    public function createOwnerLogin(string $subId): void
    {
        $sub = $this->pkg->findSubscription((int)$subId);
        if (!$sub) { $this->flash('danger','Not found.'); $this->redirect(\$this->adBase()); }

        $this->view('admin.ad_subscriptions.create_login', [
            'sub'       => $sub,
            'pageTitle' => 'Create Owner Login — ' . $sub['business_name'],
        ], $this->layout());
    }

    public function storeOwnerLogin(string $subId): void
    {
        CSRF::validate();
        $sub = $this->pkg->findSubscription((int)$subId);
        if (!$sub) { $this->flash('danger','Not found.'); $this->redirect(\$this->adBase()); }

        $name     = Helper::sanitize($this->post('name', ''));
        $email    = filter_var($this->post('email', ''), FILTER_SANITIZE_EMAIL);
        $password = $this->post('password', '');

        if (!$name || !$email || strlen($password) < 8) {
            $this->flash('danger', 'Name, email and password (min 8 chars) required.');
            $this->redirect(\$this->subBase(\$subId) . '/create-login');
        }

        // Check email not already taken
        $existing = $this->db->prepare("SELECT id FROM tn_users WHERE email = ?");
        $existing->execute([$email]);
        if ($existing->fetch()) {
            $this->flash('danger', 'Email already in use. Use a different email.');
            $this->redirect(\$this->subBase(\$subId) . '/create-login');
        }

        // Get ad_owner role_id
        $roleRow = $this->db->prepare("SELECT id FROM tn_roles WHERE slug='ad_owner' LIMIT 1");
        $roleRow->execute();
        $role = $roleRow->fetch(\PDO::FETCH_ASSOC);
        if (!$role) {
            $this->flash('danger', 'ad_owner role not found. Run the migration SQL first.');
            $this->redirect(\$this->subBase(\$subId));
        }

        // Create user
        $stmt = $this->db->prepare(
            "INSERT INTO tn_users (role_id, name, email, password, is_active, created_at)
             VALUES (?, ?, ?, ?, 1, NOW())"
        );
        $stmt->execute([$role['id'], $name, $email, password_hash($password, PASSWORD_BCRYPT)]);
        $userId = (int)$this->db->lastInsertId();

        // Link to subscription
        $this->pkg->assignOwner((int)$subId, $userId);

        // Notify
        try {
            (new NotificationModel())->notifyChiefEditors(
                'ad_owner_created',
                "Ad owner login created for \"{$sub['business_name']}\" — {$email}",
                (int)$sub['ad_id'],
                Auth::id()
            );
        } catch (\Exception $e) {}

        $this->flash('success', "Login created for {$name} ({$email}). Share credentials with the client.");
        $this->redirect(\$this->subBase(\$subId));
    }

    // ── Reset owner password ──

    public function resetOwnerPassword(string $adId): void
    {
        CSRF::validate();
        $userId      = (int)$this->post('user_id', 0);
        $newPassword = $this->post('new_password', '');
        if (!$userId || strlen($newPassword) < 8) {
            $this->flash('danger', 'Password must be at least 8 characters.');
            $this->redirect(\$this->adBase() . '/show/' . \$adId . '#owner-profile');
        }
        $this->db->prepare(
            "UPDATE tn_users SET password=? WHERE id=?"
        )->execute([password_hash($newPassword, PASSWORD_BCRYPT), $userId]);
        $this->flash('success', 'Password reset successfully.');
        $this->redirect(\$this->adBase() . '/show/' . \$adId . '#owner-profile');
    }

    public function approveSponsoredNews(string $newsId): void
    {
        CSRF::validate();
        $this->pkg->updateSponsoredNews((int)$newsId, [
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);
        // Publish the linked article
        $row = $this->db->prepare(
            "SELECT sn.subscription_id, sn.article_id FROM tn_sponsored_news sn WHERE sn.id=?"
        );
        $row->execute([(int)$newsId]);
        $sn = $row->fetch(\PDO::FETCH_ASSOC);
        if ($sn) {
            $this->db->prepare(
                "UPDATE tn_articles SET status='published', published_at=NOW() WHERE id=?"
            )->execute([$sn['article_id']]);
        }
        $this->flash('success', 'Sponsored news approved and published.');
        $this->redirect(\$this->adBase());
    }
}
