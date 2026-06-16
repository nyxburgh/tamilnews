<?php
namespace App\Controllers\Admin;

use App\Core\{Controller, Auth, CSRF, Helper};
use App\Models\{BusinessAdModel, LocationModel, CategoryModel, AdModel, NotificationModel};

class BusinessAdController extends Controller
{
    private BusinessAdModel $ads;
    private LocationModel   $locations;

    protected function layout(): string
    {
        $role = \App\Core\Auth::role();
        if ($role === 'admin')        return 'admin';
        if ($role === 'chief_editor') return 'editor_portal';
        return 'portal';
    }

    public function __construct()
    {
        $this->requireRole('admin','chief_editor','editor','district_editor','category_editor','reporter');
        $this->ads       = new BusinessAdModel();
        $this->locations = new LocationModel();
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

        $this->view('admin.business_ads.index', [
            'pageTitle' => 'Business Ads',
            'result'    => $result,
            'ads'       => $result['data'],
            'total'     => $result['total'],
            'page'      => $result['page'],
            'per_page'  => $result['per_page'],
            'status'    => $status,
            'canApprove'=> Auth::can('approve_escalated'), // chief_editor + admin
        ], $this->layout());
    }

    // ── Create form ──────────────────────────────────────────

    public function create(): void
    {
        $this->view('admin.business_ads.form', [
            'pageTitle' => 'New Business Ad',
            'ad'        => [],
            'districts' => $this->locations->allDistricts(),
            'cities'    => [],
            'categories'=> (new CategoryModel())->allWithParent(),
            'slots'     => (new AdModel())->allSlots(),
            'isEdit'    => false,
        ], $this->layout());
    }

    // ── Store new ad ─────────────────────────────────────────

    public function store(): void
    {
        CSRF::validate();

        $data = [
            'business_name'  => Helper::sanitize($this->post('business_name','')),
            'contact_phone'  => Helper::sanitize($this->post('contact_phone','')),
            'contact_email'  => Helper::sanitize($this->post('contact_email','')),
            'district_id'    => (int)$this->post('district_id',0) ?: null,
            'city_id'        => (int)$this->post('city_id',0)     ?: null,
            'slot_id'        => (int)$this->post('slot_id',0),
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
            $this->flash('danger','Business name and ad slot are required.');
            $this->redirect('/admin/business-ads/create');
        }

        $adId = $this->ads->submit($data, Auth::id());

        if (!$adId) {
            $this->flash('danger','Failed to create ad. Please try again.');
            $this->redirect('/admin/business-ads/create');
        }

        // Upload images if provided
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $i => $name) {
                if (!$name) continue;
                $file = [
                    'name'     => $_FILES['images']['name'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error'    => $_FILES['images']['error'][$i],
                    'size'     => $_FILES['images']['size'][$i],
                ];
                $linkUrl = $this->post('link_url_'.$i, '');
                $this->ads->uploadImage($adId, $file, $linkUrl);
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

        $this->flash('success','Ad submitted successfully. Awaiting chief editor approval.');
        $this->redirect('/admin/business-ads/show/' . $adId);
    }

    // ── Show detail ──────────────────────────────────────────

    public function show(string $id): void
    {
        $ad = $this->ads->findWithDetails((int)$id);
        if (!$ad) { $this->flash('danger','Ad not found.'); $this->redirect('/admin/business-ads'); }

        // Own or admin/chief
        if (!in_array(Auth::role(),['admin','chief_editor']) && $ad['submitted_by'] != Auth::id()) {
            $this->flash('danger','Access denied.'); $this->redirect('/admin/business-ads');
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
        if (!$ad) { $this->flash('danger','Not found.'); $this->redirect('/admin/business-ads'); }
        if (!in_array(Auth::role(),['admin','chief_editor']) && $ad['submitted_by'] != Auth::id()) {
            $this->flash('danger','Access denied.'); $this->redirect('/admin/business-ads');
        }

        $this->view('admin.business_ads.form', [
            'pageTitle'  => 'Edit Ad — ' . $ad['business_name'],
            'ad'         => $ad,
            'districts'  => $this->locations->allDistricts(),
            'cities'     => $ad['district_id']
                            ? $this->locations->allCities($ad['district_id'])
                            : [],
            'categories' => (new CategoryModel())->allWithParent(),
            'slots'      => (new AdModel())->allSlots(),
            'isEdit'     => true,
        ], $this->layout());
    }

    // ── Update ───────────────────────────────────────────────

    public function update(string $id): void
    {
        CSRF::validate();
        $ad = $this->ads->find((int)$id);
        if (!$ad) { $this->flash('danger','Not found.'); $this->redirect('/admin/business-ads'); }
        if (!in_array(Auth::role(),['admin','chief_editor']) && $ad['submitted_by'] != Auth::id()) {
            $this->flash('danger','Access denied.'); $this->redirect('/admin/business-ads');
        }

        $data = [
            'business_name' => Helper::sanitize($this->post('business_name','')),
            'contact_phone' => Helper::sanitize($this->post('contact_phone','')),
            'contact_email' => Helper::sanitize($this->post('contact_email','')),
            'district_id'   => (int)$this->post('district_id',0) ?: null,
            'city_id'       => (int)$this->post('city_id',0)     ?: null,
            'slot_id'       => (int)$this->post('slot_id',0),
            'display_type'  => $this->post('display_type','global'),
            'category_id'   => (int)$this->post('category_id',0) ?: null,
            'valid_from'    => $this->post('valid_from'),
            'valid_until'   => $this->post('valid_until'),
            'payment_amount'=> (float)$this->post('payment_amount',0) ?: null,
            'payment_note'  => Helper::sanitize($this->post('payment_note','')),
            'notes'         => Helper::sanitize($this->post('notes','')),
        ];

        $this->ads->update((int)$id, $data);

        // Upload new images
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $i => $name) {
                if (!$name) continue;
                $file = [
                    'name'     => $_FILES['images']['name'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error'    => $_FILES['images']['error'][$i],
                    'size'     => $_FILES['images']['size'][$i],
                ];
                $this->ads->uploadImage((int)$id, $file, $this->post('link_url_'.$i,''));
            }
        }

        $this->flash('success','Ad updated successfully.');
        $this->redirect('/admin/business-ads/show/' . $id);
    }

    // ── Approve (chief editor / admin) ───────────────────────

    public function approve(string $id): void
    {
        CSRF::validate();
        $this->requireCan('approve_escalated');
        $ad = $this->ads->find((int)$id);
        if (!$ad) { $this->flash('danger','Not found.'); $this->redirect('/admin/business-ads'); }

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
        $this->redirect('/admin/business-ads/show/' . $id);
    }

    // ── Reject ───────────────────────────────────────────────

    public function reject(string $id): void
    {
        CSRF::validate();
        $this->requireCan('approve_escalated');
        $reason = Helper::sanitize($this->post('reason',''));
        $ad     = $this->ads->find((int)$id);
        if (!$ad) { $this->flash('danger','Not found.'); $this->redirect('/admin/business-ads'); }

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
        $this->redirect('/admin/business-ads');
    }

    // ── Confirm payment ──────────────────────────────────────

    public function confirmPayment(string $id): void
    {
        CSRF::validate();
        $this->requireCan('approve_escalated');
        $note = Helper::sanitize($this->post('payment_note',''));
        $this->ads->confirmPayment((int)$id, Auth::id(), $note);
        $this->flash('success','Payment confirmed. Ad is now active.');
        $this->redirect('/admin/business-ads/show/' . $id);
    }


    // ── Delete entire ad + files ─────────────────────────────
    public function delete(string $adId): void
    {
        \App\Core\CSRF::validate();
        $id = (int)$adId;
        $ad = $this->ads->find($id);
        if (!$ad) {
            $this->flash('danger', 'Ad not found.');
            $this->redirect('/admin/business-ads');
        }
        if (!in_array(\App\Core\Auth::role(), ['admin','chief_editor'])
            && (int)$ad['submitted_by'] !== \App\Core\Auth::id()) {
            $this->flash('danger', 'Access denied.');
            $this->redirect('/admin/business-ads');
        }
        $this->ads->deleteWithFiles($id);
        $this->flash('success', 'Ad deleted. All images removed.');
        $this->redirect('/admin/business-ads');
    }

    // ── Delete image ─────────────────────────────────────────

    public function deleteImage(string $id): void
    {
        CSRF::validate();
        $adId    = (int)$this->post('ad_id',0);
        $imageId = (int)$id;
        $ad      = $this->ads->find($adId);

        if (!$ad) { $this->json(['error'=>'Not found']); return; }
        if (!in_array(Auth::role(),['admin','chief_editor']) && $ad['submitted_by'] != Auth::id()) {
            $this->json(['error'=>'Access denied']); return;
        }

        $this->ads->deleteImage($imageId, $adId);
        $this->json(['success' => true]);
    }

    // ── AJAX: cities by district ─────────────────────────────

    public function citiesByDistrict(string $districtId): void
    {
        $cities = $this->locations->allCities((int)$districtId);
        $this->json($cities);
    }
}
