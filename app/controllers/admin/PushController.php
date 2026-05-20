<?php
namespace App\Controllers\Admin;
use App\Core\{Controller, CSRF, Auth};
use App\Models\{PushModel, SettingModel};

class PushController extends Controller
{
    protected function layout(): string
    {
        return \App\Core\Auth::role() === 'admin' ? 'admin' :
               (\App\Core\Auth::role() === 'chief_editor' ? 'editor_portal' : 'portal');
    }

    private PushModel $push;
    public function middleware(): void { $this->requireRole('admin'); }
    public function __construct() { $this->push = new PushModel(); }

    public function index(): void
    {
        $this->view('admin.push.index', ['pageTitle'=>'Push Notifications','topics'=>$this->push->allTopics(),'history'=>$this->push->history(10)], $this->layout());
    }

    public function send(): void
    {
        CSRF::validate();
        $settings  = new SettingModel();
        $serverKey = $settings->getValue('fcm_server_key','');
        $title     = trim($this->post('title',''));
        $body      = trim($this->post('body',''));
        $topicId   = (int)$this->post('topic_id',0) ?: null;
        $topicSlug = $topicId ? $this->push->fetchOne("SELECT slug FROM tn_fcm_topics WHERE id=?",[$topicId])['slug'] ?? 'general' : 'general';

        $id = $this->push->store(['user_id'=>Auth::id(),'topic_id'=>$topicId,'title'=>$title,'body'=>$body,'status'=>'pending']);

        if ($serverKey) {
            $payload = json_encode(['to'=>'/topics/'.$topicSlug,'notification'=>['title'=>$title,'body'=>$body]]);
            $ch = curl_init('https://fcm.googleapis.com/fcm/send');
            curl_setopt_array($ch, [CURLOPT_POST=>true,CURLOPT_HTTPHEADER=>['Authorization: key='.$serverKey,'Content-Type: application/json'],CURLOPT_POSTFIELDS=>$payload,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $httpCode === 200 ? $this->push->markSent($id) : $this->push->markFailed($id);
        }

        $this->flash('success','Notification queued.'); $this->redirect('/admin/push');
    }

    public function history(): void
    {
        $this->view('admin.push.index', ['pageTitle'=>'Push History','topics'=>$this->push->allTopics(),'history'=>$this->push->history(50)], $this->layout());
    }
}
