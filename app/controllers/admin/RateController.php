<?php
namespace App\Controllers\Admin;
use App\Core\{Controller, Auth, CSRF};
use App\Models\RateModel;

class RateController extends Controller
{
    private RateModel $model;
    public function middleware(): void { $this->requireCan('manage_rates'); }
    protected function layout(): string
    {
        $role = \App\Core\Auth::role();
        if ($role === 'admin')        return 'admin';
        if ($role === 'chief_editor') return 'editor_portal';
        return 'portal';
    }

    public function __construct() { $this->model = new RateModel(); }

    public function index(): void
    {
        $this->view('admin.rates.index', [
            'pageTitle' => 'Live Rates',
            'rates'     => $this->model->allRates(),
            'types'     => ['gold'=>'Gold (per gram)','silver'=>'Silver (per gram)',
                            'petrol'=>'Petrol (per litre)','diesel'=>'Diesel (per litre)',
                            'currency_usd'=>'USD/INR','currency_gbp'=>'GBP/INR','currency_eur'=>'EUR/INR'],
        ], $this->layout());
    }

    public function store(): void
    {
        CSRF::validate();
        $type   = $this->post('type','gold');
        $value  = (float)$this->post('value',0);
        $city   = trim($this->post('city','')) ?: null;
        $change = $this->post('change','') !== '' ? (float)$this->post('change',0) : null;

        if ($value <= 0) {
            $this->flash('danger','Enter a valid rate value.');
            $this->redirect('/admin/rates');
        }
        $this->model->upsert($type, $value, $city, $change);
        $this->flash('success','Rate updated successfully.');
        $this->redirect('/admin/rates');
    }

    /** API: Return all rates as JSON for frontend floating icons */
    public function api(): void
    {
        try {
            $rates = $this->model->allForWidget();
            $this->json(['success'=>true,'rates'=>$rates]);
        } catch (\Exception $e) {
            $this->json(['success'=>false,'rates'=>[]]);
        }
    }
}
