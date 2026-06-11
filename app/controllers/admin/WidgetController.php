<?php
namespace App\Controllers\Admin;
use App\Core\{Controller, Auth, CSRF};
use App\Models\WidgetModel;

class WidgetController extends Controller
{
    private WidgetModel $model;
    public function middleware(): void { $this->requireCan('manage_widgets'); }
    protected function layout(): string
    {
        $role = \App\Core\Auth::role();
        if ($role === 'admin')        return 'admin';
        if ($role === 'chief_editor') return 'editor_portal';
        return 'portal';
    }

    public function __construct() { $this->model = new WidgetModel(); }

    public function index(): void
    {
        $this->view('admin.widgets.index', [
            'pageTitle' => 'Sidebar Widgets',
            'widgets'   => $this->model->allWidgets(),
        ], $this->layout());
    }

    public function toggle(string $id): void
    {
        CSRF::validate();
        $this->model->toggle((int)$id);
        $this->flash('success', 'Widget status updated.');
        $this->redirect('/admin/widgets');
    }

    public function reorder(): void
    {
        CSRF::validate();
        $ids = json_decode($this->post('ids','[]'), true);
        if (is_array($ids)) $this->model->reorder($ids);
        $this->json(['success' => true]);
    }

    public function update(string $id): void
    {
        CSRF::validate();
        $config = [
            'title'        => $this->post('title',''),
            'title_tamil'  => $this->post('title_tamil',''),
            'show_mobile'  => (int)$this->post('show_mobile',0),
            'category_id'  => $this->post('category_id',''),
            'district_id'  => $this->post('district_id',''),
            'count'        => (int)$this->post('count',5),
        ];
        $this->model->updateConfig((int)$id, $config);
        $this->flash('success', 'Widget updated.');
        $this->redirect('/admin/widgets');
    }

    public function create(): void
    {
        CSRF::validate();
        $type = $this->post('type','custom_html');
        $this->model->insert([
            'name'       => $this->post('name','New Widget'),
            'type'       => $type,
            'title'      => $this->post('title',''),
            'position'   => $this->post('position','sidebar'),
            'sort_order' => 99,
            'is_active'  => 1,
            'show_desktop'=> 1,
            'show_mobile' => 0,
            'config'     => json_encode(['content' => $this->post('content','')]),
        ]);
        $this->flash('success', 'Widget added.');
        $this->redirect('/admin/widgets');
    }

    public function delete(string $id): void
    {
        CSRF::validate();
        $this->model->delete((int)$id);
        $this->flash('success', 'Widget deleted.');
        $this->redirect('/admin/widgets');
    }
}
