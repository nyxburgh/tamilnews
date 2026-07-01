<?php
namespace App\Controllers\Admin;
use App\Core\{Controller, CSRF};
use App\Models\SettingModel;

class SettingController extends Controller
{
    protected function layout(): string
    {
        return \App\Core\Auth::role() === 'admin' ? 'admin' :
               (\App\Core\Auth::role() === 'chief_editor' ? 'editor_portal' : 'portal');
    }

    private SettingModel $settings;
    public function middleware(): void { $this->requireCan('manage_settings'); }
    public function __construct() { $this->settings = new SettingModel(); }

    public function index(): void
    {
        $this->view('admin.settings.index', ['pageTitle'=>'Settings','settings'=>$this->settings->all(),'group'=>'general'], $this->layout());
    }

    public function update(): void
    {
        CSRF::validate();
        foreach ($_POST as $key => $value) {
            if ($key === '_token') continue;
            $this->settings->updateKey($key, $value);
        }
        $this->flash('success','Settings saved.'); $this->redirect('/admin/settings');
    }

    public function group(string $group): void
    {
        $this->view('admin.settings.index', ['pageTitle'=>'Settings — '.ucfirst($group),'settings'=>$this->settings->all(),'group'=>$group], $this->layout());
    }

    public function updateGroup(string $group): void
    {
        CSRF::validate();
        $data = $_POST; unset($data['_token']);
        $this->settings->updateGroup($group, $data);
        $this->flash('success','Settings saved.'); $this->redirect('/admin/settings/'.$group);
    }
}
