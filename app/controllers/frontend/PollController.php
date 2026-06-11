<?php
namespace App\Controllers\Frontend;
use App\Core\{Controller, CSRF, Session};
use App\Models\PollModel;

class PollController extends Controller
{
    public function vote(string $pollId): void
    {
        CSRF::validate();
        $optionId = (int)$this->post('option_id', 0);
        if (!$optionId) { $this->json(['error'=>'Select an option']); return; }

        $model    = new PollModel();
        $reader   = Session::get('reader');
        $readerId = $reader ? (int)$reader['id'] : null;
        $ipHash   = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');

        if ($model->hasVoted((int)$pollId, $readerId, $ipHash)) {
            $this->json(['error' => 'Already voted']);
            return;
        }

        $ok   = $model->vote((int)$pollId, $optionId, $readerId, $ipHash);
        $poll = $model->findWithOptions((int)$pollId);

        $this->json([
            'success' => $ok,
            'options' => $poll['options'] ?? [],
            'total'   => $poll['total_votes'] ?? 0,
        ]);
    }

    public function widget(string $pollId): void
    {
        $model   = new PollModel();
        $poll    = $model->findWithOptions((int)$pollId);
        if (!$poll) { $this->json(['error'=>'Not found']); return; }

        $reader   = Session::get('reader');
        $readerId = $reader ? (int)$reader['id'] : null;
        $ipHash   = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');

        $this->json([
            'poll'     => $poll,
            'voted'    => $model->hasVoted((int)$pollId, $readerId, $ipHash),
        ]);
    }
}
