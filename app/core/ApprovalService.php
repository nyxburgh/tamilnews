<?php
namespace App\Core;

use App\Models\{ArticleModel, NotificationModel, UserModel};

/**
 * ApprovalService
 *
 * Central place for all approval flow logic.
 *
 * Flow:
 *  Reporter submits
 *    └── auto_approve ON  → publish instantly, notify chief editor
 *    └── auto_approve OFF
 *          └── district editor assigned for that district/category?
 *                └── YES → route to district editor queue (stage: district_editor)
 *                └── NO  → route to chief editor queue   (stage: chief_editor)
 *
 *  District editor approves
 *    └── can_publish ON  → publish, notify chief editor
 *    └── can_publish OFF → escalate to chief editor (stage: chief_editor)
 *
 *  District editor rejects → back to reporter (stage: reporter, status: rejected)
 *
 *  Chief editor can approve/reject/publish at any stage regardless
 */
class ApprovalService
{
    private ArticleModel     $articles;
    private NotificationModel $notif;
    private Database         $db;

    public function __construct()
    {
        $this->articles = new ArticleModel();
        $this->notif    = new NotificationModel();
        $this->db       = Database::getInstance();
    }

    // ── Called when reporter submits article ─────────────────

    public function onSubmit(int $articleId, int $reporterId): void
    {
        $article  = $this->articles->find($articleId);
        if (!$article) return;

        $reporter = $this->db->prepare(
            "SELECT u.*, r.slug AS role_slug FROM tn_users u JOIN tn_roles r ON r.id=u.role_id WHERE u.id=?"
        );
        $reporter->execute([$reporterId]);
        $reporter = $reporter->fetch(\PDO::FETCH_ASSOC);

        // ── AUTO APPROVE ──────────────────────────────────────
        if (!empty($reporter['auto_approve'])) {
            $this->publish($articleId, $reporterId, isAutoApprove: true);

            // Notify all chief editors
            $this->notif->notifyChiefEditors(
                'auto_published',
                "Auto-published: \"{$article['title']}\" by {$reporter['name']}",
                $articleId,
                $reporterId
            );
            return;
        }

        // ── FIND DISTRICT EDITOR ──────────────────────────────
        $districtEditorId = $this->findDistrictEditor($article);

        if ($districtEditorId) {
            // Route to district editor
            $this->db->prepare(
                "UPDATE tn_articles SET status='review', approval_stage='district_editor' WHERE id=?"
            )->execute([$articleId]);

            $this->notif->send(
                $districtEditorId,
                'article_submitted',
                "New article for review: \"{$article['title']}\"",
                $articleId,
                $reporterId
            );
        } else {
            // No district editor → go directly to chief editor
            $this->db->prepare(
                "UPDATE tn_articles SET status='review', approval_stage='chief_editor' WHERE id=?"
            )->execute([$articleId]);

            $this->notif->notifyChiefEditors(
                'article_submitted',
                "New article needs review: \"{$article['title']}\" by {$reporter['name']}",
                $articleId,
                $reporterId
            );
        }
    }

    // ── District editor approves ─────────────────────────────

    public function districtApprove(int $articleId, int $editorId): void
    {
        $article = $this->articles->find($articleId);
        if (!$article) return;

        // Check if this editor can publish directly
        $scope = (new UserModel())->getEditorScope($editorId);

        $this->db->prepare(
            "UPDATE tn_articles
             SET district_approved_by=?, district_approved_at=NOW()
             WHERE id=?"
        )->execute([$editorId, $articleId]);

        if ($scope['canPublish']) {
            $this->publish($articleId, $editorId);
            $this->notif->notifyChiefEditors(
                'article_approved',
                "District editor published: \"{$article['title']}\"",
                $articleId,
                $editorId
            );
        } else {
            // Escalate to chief editor
            $this->db->prepare(
                "UPDATE tn_articles SET approval_stage='chief_editor' WHERE id=?"
            )->execute([$articleId]);

            $editor = $this->db->prepare("SELECT name FROM tn_users WHERE id=?");
            $editor->execute([$editorId]);
            $editorName = $editor->fetchColumn();

            $this->notif->notifyChiefEditors(
                'escalated',
                "District editor approved, awaiting your publish: \"{$article['title']}\" (by {$editorName})",
                $articleId,
                $editorId
            );
        }
    }

    // ── Chief editor / Admin approves ─────────────────────────

    public function chiefApprove(int $articleId, int $editorId): void
    {
        $article = $this->articles->find($articleId);
        if (!$article) return;

        $this->publish($articleId, $editorId);

        // Notify reporter
        if (!empty($article['user_id'])) {
            $this->notif->send(
                $article['user_id'],
                'article_published',
                "Your article was published: \"{$article['title']}\"",
                $articleId,
                $editorId
            );
        }
    }

    // ── Reject ───────────────────────────────────────────────

    public function reject(int $articleId, int $editorId, string $reason = ''): void
    {
        $article = $this->articles->find($articleId);
        if (!$article) return;

        $this->db->prepare(
            "UPDATE tn_articles SET status='rejected', approval_stage='reporter' WHERE id=?"
        )->execute([$articleId]);

        if (!empty($article['user_id'])) {
            $msg = "Your article was rejected: \"{$article['title']}\"";
            if ($reason) $msg .= " — Reason: {$reason}";
            $this->notif->send($article['user_id'], 'article_rejected', $msg, $articleId, $editorId);
        }
    }

    // ── Internal: publish article ─────────────────────────────

    private function publish(int $articleId, int $byUserId, bool $isAutoApprove = false): void
    {
        $this->db->prepare(
            "UPDATE tn_articles
             SET status='published',
                 approval_stage='published',
                 approved_by=?,
                 approved_at=NOW(),
                 published_at=COALESCE(published_at, NOW())
             WHERE id=?"
        )->execute([$byUserId, $articleId]);
    }

    // ── Find district editor for article ─────────────────────

    private function findDistrictEditor(array $article): ?int
    {
        // Match by district (via city) OR by category
        $cityId     = $article['city_id']     ?? null;
        $categoryId = $article['category_id'] ?? null;

        // Try district first
        if ($cityId) {
            $districtId = $this->db->prepare(
                "SELECT district_id FROM tn_cities WHERE id=?"
            );
            $districtId->execute([$cityId]);
            $districtId = $districtId->fetchColumn();

            if ($districtId) {
                $editor = $this->db->prepare(
                    "SELECT ep.user_id FROM tn_editor_permissions ep
                     JOIN tn_users u ON u.id = ep.user_id
                     WHERE ep.perm_type='district' AND ep.ref_id=?
                     AND u.is_active=1 AND u.is_blocked=0
                     ORDER BY ep.can_publish DESC LIMIT 1"
                );
                $editor->execute([$districtId]);
                $editorId = $editor->fetchColumn();
                if ($editorId) return (int)$editorId;
            }
        }

        // Try category
        if ($categoryId) {
            $editor = $this->db->prepare(
                "SELECT ep.user_id FROM tn_editor_permissions ep
                 JOIN tn_users u ON u.id = ep.user_id
                 WHERE ep.perm_type='category' AND ep.ref_id=?
                 AND u.is_active=1 AND u.is_blocked=0
                 ORDER BY ep.can_publish DESC LIMIT 1"
            );
            $editor->execute([$categoryId]);
            $editorId = $editor->fetchColumn();
            if ($editorId) return (int)$editorId;
        }

        return null;
    }

    public function editorApprove(int $articleId, int $editorId): void
    {
        $article = $this->articles->find($articleId);
        if (!$article) return;
        $this->db->prepare("UPDATE tn_articles SET status='published', approval_stage='published', approved_by=?, approved_at=NOW(), published_at=COALESCE(published_at,NOW()) WHERE id=?")->execute([$editorId,$articleId]);
        if (!empty($article['user_id'])) {
            $this->notif->send($article['user_id'],'article_published',"Your article was published: \"{$article['title']}\"", $articleId,$editorId);
        }
    }

    public function escalateToChief(int $articleId, int $editorId, string $note = ''): void
    {
        $article = $this->articles->find($articleId);
        if (!$article) return;
        $this->db->prepare("UPDATE tn_articles SET approval_stage='chief_editor' WHERE id=?")->execute([$articleId]);
        $stmt = $this->db->prepare("SELECT name FROM tn_users WHERE id=?");
        $stmt->execute([$editorId]);
        $editorName = $stmt->fetchColumn();
        $msg = "Escalated by {$editorName}: \"{$article['title']}\"";
        if ($note) $msg .= " — {$note}";
        $this->notif->notifyChiefEditors('escalated',$msg,$articleId,$editorId);
    }

    public function chiefApprove(int $articleId, int $chiefId): void
    {
        $article = $this->articles->find($articleId);
        if (!$article) return;
        $this->db->prepare("UPDATE tn_articles SET status='published', approval_stage='published', approved_by=?, approved_at=NOW(), published_at=COALESCE(published_at,NOW()) WHERE id=?")->execute([$chiefId,$articleId]);
        if (!empty($article['user_id'])) {
            $this->notif->send($article['user_id'],'article_published',"Your article was published by Chief Editor: \"{$article['title']}\"", $articleId,$chiefId);
        }
    }
}