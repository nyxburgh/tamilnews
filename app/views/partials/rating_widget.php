<?php
/**
 * Rating Widget Partial
 * Usage in article detail view:
 *   $readerId   = \App\Core\Session::get('reader_id', 0);
 *   $ratingModel = new \App\Models\RatingModel();
 *   $ratingStats = $ratingModel->forArticle($article['id']);
 *   $userRating  = $readerId ? ($ratingModel->readerRating($article['id'], $readerId)['rating'] ?? 0) : 0;
 *   include VIEW_PATH . '/partials/rating_widget.php';
 */

use App\Core\{Helper, CSRF, Session};

$readerId   = $readerId   ?? Session::get('reader_id', 0);
$ratingStats = $ratingStats ?? ['total' => 0, 'average' => 0];
$userRating  = $userRating  ?? 0;
?>

<div class="tn-card mt-4">
  <div class="tn-card-header">
    <span><i class="bi bi-star me-2"></i>Rate this Article</span>
    <?php if ($readerId): ?>
    <small class="text-muted">Logged in as reader</small>
    <?php else: ?>
    <a href="<?= $r ?>/auth/reader/login?return=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
       class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-google me-1"></i>Sign in to rate
    </a>
    <?php endif; ?>
  </div>

  <div class="tn-card-body">
    <!-- CURRENT STATS -->
    <?php if ($ratingStats['total'] > 0): ?>
    <div class="tn-rating-display mb-3">
      <span class="tn-rating-avg"><?= number_format((float)$ratingStats['average'], 1) ?></span>
      <div>
        <div class="tn-rating-stars-sm">
          <?php
          $avg = (float)$ratingStats['average'];
          for ($i = 1; $i <= 5; $i++) {
              echo $i <= round($avg) ? '★' : '☆';
          }
          ?>
        </div>
        <div class="tn-rating-count"><?= number_format($ratingStats['total']) ?> rating<?= $ratingStats['total'] != 1 ? 's' : '' ?></div>
      </div>
    </div>
    <?php endif; ?>

    <!-- INTERACTIVE WIDGET -->
    <div class="tn-rating-widget"
         data-article-id="<?= (int)($article['id'] ?? 0) ?>"
         data-reader-id="<?= (int)$readerId ?>"
         data-user-rating="<?= (int)$userRating ?>">

      <?php if (!$readerId): ?>
      <p class="text-muted small mb-2">Sign in with Google to rate and review this article.</p>
      <a href="<?= $r ?>/auth/reader/login?return=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn-google d-inline-flex">
        <svg width="16" height="16" viewBox="0 0 48 48">
          <path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9 3.2l6.7-6.7C35.8 2.5 30.3 0 24 0 14.6 0 6.6 5.5 2.7 13.5l7.8 6C12.4 13.3 17.8 9.5 24 9.5z"/>
          <path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v8.5h12.7c-.6 3-2.3 5.5-4.8 7.2l7.5 5.8c4.4-4 7.1-10 7.1-17z"/>
          <path fill="#FBBC05" d="M10.5 28.5c-.5-1.5-.8-3-.8-4.5s.3-3 .8-4.5l-7.8-6C1 16.5 0 20.1 0 24s1 7.5 2.7 10.5l7.8-6z"/>
          <path fill="#34A853" d="M24 48c6.3 0 11.6-2.1 15.5-5.7l-7.5-5.8c-2.1 1.4-4.8 2.2-8 2.2-6.2 0-11.5-3.8-13.5-9.2l-7.8 6C6.6 42.5 14.6 48 24 48z"/>
        </svg>
        Sign in with Google
      </a>
      <?php else: ?>
      <p class="text-muted small mb-2">
        <?= $userRating ? 'Your rating: — click to change' : 'Click a star to rate' ?>
      </p>
      <div class="tn-star-rating mb-2"></div>

      <div class="tn-review-form mt-3" style="<?= $userRating ? '' : 'display:none' ?>">
        <textarea class="form-control tn-review-textarea" rows="3"
                  placeholder="Share your thoughts about this article… (optional)"
                  style="font-size:13px"></textarea>
        <button class="btn btn-sm btn-primary mt-2 tn-rating-submit">
          <?= CSRF::field() ?>
          Submit Rating
        </button>
      </div>

      <div class="tn-rating-result"></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- REVIEWS LIST -->
<?php
$ratingModel = $ratingModel ?? new \App\Models\RatingModel();
$reviews     = $ratingModel->recentReviews((int)($article['id'] ?? 0), 5);
if (!empty($reviews)):
?>
<div class="tn-card mt-3">
  <div class="tn-card-header"><span><i class="bi bi-chat-square-text me-2"></i>Reader Reviews</span></div>
  <div class="tn-card-body p-0">
    <?php foreach ($reviews as $rv): ?>
    <div class="tn-review-item px-4">
      <div class="d-flex align-items-center gap-2 mb-1">
        <?php if ($rv['reader_avatar']): ?>
        <img src="<?= Helper::e($rv['reader_avatar']) ?>" class="tn-reviewer-avatar" alt="">
        <?php endif; ?>
        <strong class="small"><?= Helper::e($rv['reader_name']) ?></strong>
        <span class="tn-rating-stars-sm" style="font-size:12px">
          <?= str_repeat('★', $rv['rating']) . str_repeat('☆', 5 - $rv['rating']) ?>
        </span>
        <span class="text-muted small ms-auto"><?= Helper::timeAgo($rv['created_at']) ?></span>
      </div>
      <p class="small text-muted mb-0"><?= Helper::e($rv['review']) ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<script src="/assets/js/rating.js"></script>
