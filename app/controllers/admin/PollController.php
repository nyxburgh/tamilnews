<?php
namespace App\Controllers\Admin;
use App\Core\{Controller, Auth, CSRF, Helper};
use App\Models\PollModel;

class PollController extends Controller
{
    private PollModel $model;
    public function middleware(): void { $this->requireCan('manage_polls'); }
    protected function layout(): string
    {
        $role = \App\Core\Auth::role();
        if ($role === 'admin')        return 'admin';
        if ($role === 'chief_editor') return 'editor_portal';
        return 'portal';
    }

    public function __construct() { $this->model = new PollModel(); }

    public function index(): void
    {
        $page = max(1,(int)($this->get('page',1)));
        $res  = $this->model->allForAdmin($page, 15);
        $this->view('admin.polls.index', [
            'pageTitle' => 'Polls',
            'polls'     => $res['data'],
            'total'     => $res['total'],
            'page'      => $res['page'],
            'per_page'  => $res['per_page'],
        ], $this->layout());
    }

    public function create(): void
    {
        $this->view('admin.polls.form', [
            'pageTitle' => 'New Poll',
            'poll'      => [],
            'options'   => [],
            'isEdit'    => false,
        ], $this->layout());
    }

    public function store(): void
    {
        CSRF::validate();
        $question = Helper::sanitize($this->post('question',''));
        if (!$question) {
            $this->flash('danger','Question is required.');
            $this->redirect('/admin/polls/create');
        }
        $options = [];
        $texts   = $_POST['option_text']    ?? [];
        $texts_ta= $_POST['option_text_ta'] ?? [];
        foreach ($texts as $i => $t) {
            if (trim($t)) $options[] = ['text'=>trim($t),'text_ta'=>trim($texts_ta[$i]??'')];
        }
        if (count($options) < 2) {
            $this->flash('danger','At least 2 options required.');
            $this->redirect('/admin/polls/create');
        }
        $expires = $this->post('expires_at','');
        $id = $this->model->createWithOptions([
            'created_by'  => Auth::id(),
            'question'    => $question,
            'question_ta' => Helper::sanitize($this->post('question_ta','')),
            'expires_at'  => $expires ?: null,
            'is_active'   => 1,
        ], $options);
        $this->flash('success','Poll created.');
        $this->redirect('/admin/polls');
    }

    public function toggle(string $id): void
    {
        CSRF::validate();
        $this->model->toggle((int)$id);
        $this->flash('success','Poll status updated.');
        $this->redirect('/admin/polls');
    }

    public function delete(string $id): void
    {
        CSRF::validate();
        $this->model->delete((int)$id);
        $this->flash('success','Poll deleted.');
        $this->redirect('/admin/polls');
    }
}
