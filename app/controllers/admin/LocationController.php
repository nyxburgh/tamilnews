<?php
namespace App\Controllers\Admin;
use App\Core\{Controller, CSRF, Helper};
use App\Models\LocationModel;

class LocationController extends Controller
{
    protected function layout(): string
    {
        return \App\Core\Auth::role() === 'admin' ? 'admin' :
               (\App\Core\Auth::role() === 'chief_editor' ? 'editor_portal' : 'portal');
    }

    private LocationModel $locs;
    public function middleware(): void { $this->requireRole('admin'); }
    public function __construct() { $this->locs = new LocationModel(); }

    public function index(): void
    {
        $this->view('admin.locations.index', [
            'pageTitle' => 'Locations',
            'states'    => $this->locs->allStates(),
            'districts' => $this->locs->allDistricts(),
            'cities'    => $this->locs->allCities(),
        ], $this->layout());
    }

    public function citiesByDistrict(string $districtId): void
    {
        $cities = $this->locs->allCities((int)$districtId);
        $this->json(array_map(fn($c) => ['id' => $c['id'], 'name' => $c['name']], $cities));
    }

    public function storeState(): void
    {
        CSRF::validate();
        $name = Helper::sanitize($this->post('name',''));
        $this->locs->addState(['name'=>$name,'slug'=>Helper::uniqueSlug('tn_states', Helper::slug($name))]);
        $this->flash('success','State added.'); $this->redirect('/admin/locations');
    }

    public function storeDistrict(): void
    {
        CSRF::validate();
        $name = Helper::sanitize($this->post('name',''));
        $this->locs->addDistrict(['state_id'=>(int)$this->post('state_id'),'name'=>$name,'slug'=>Helper::uniqueSlug('tn_districts', Helper::slug($name))]);
        $this->flash('success','District added.'); $this->redirect('/admin/locations');
    }

    public function storeCity(): void
    {
        CSRF::validate();
        $name = Helper::sanitize($this->post('name',''));
        $this->locs->addCity(['district_id'=>(int)$this->post('district_id'),'name'=>$name,'slug'=>Helper::uniqueSlug('tn_cities', Helper::slug($name))]);
        $this->flash('success','City added.'); $this->redirect('/admin/locations');
    }

    public function delete(string $type, string $id): void
    {
        CSRF::validate();
        match($type) {
            'state'    => $this->locs->deleteState((int)$id),
            'district' => $this->locs->deleteDistrict((int)$id),
            'city'     => $this->locs->deleteCity((int)$id),
            default    => null,
        };
        $this->flash('success', ucfirst($type) . ' deleted.'); $this->redirect('/admin/locations');
    }
}
