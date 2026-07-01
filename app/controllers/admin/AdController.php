<?php
namespace App\Controllers\Admin;
use App\Core\{Controller, CSRF};
use App\Models\AdModel;

class AdController extends Controller
{
    protected function layout(): string
    {
        return \App\Core\Auth::role() === 'admin' ? 'admin' :
               (\App\Core\Auth::role() === 'chief_editor' ? 'editor_portal' : 'portal');
    }

    private AdModel $ads;
    public function middleware(): void { $this->requireCan('manage_ads'); }
    public function __construct() { $this->ads = new AdModel(); }

    public function index(): void
    {
        $this->view('admin.ads.index', ['pageTitle'=>'Ad Slots','slots'=>$this->ads->allSlots()], $this->layout());
    }

    public function update(string $id): void
    {
        CSRF::validate();
        $this->ads->updateSlot((int)$id, ['ad_code'=>$this->post('ad_code',''),'is_active'=>(int)(bool)$this->post('is_active',0)]);
        $this->flash('success','Ad slot updated.'); $this->redirect('/admin/ads');
    }
}
