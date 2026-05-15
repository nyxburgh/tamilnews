/* Tamil News Portal — Rating Widget
 * Include on any article page that needs star ratings + reviews
 * Requires: reader login via Google OAuth
 */

(function () {
  'use strict';

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

  // ── STAR RATING WIDGET ────────────────────────────
  function initRatingWidget(container) {
    if (!container) return;

    const articleId  = container.dataset.articleId;
    const readerId   = container.dataset.readerId;   // 0 if not logged in
    const userRating = parseInt(container.dataset.userRating || '0');

    const starsWrap  = container.querySelector('.tn-star-rating');
    const reviewBox  = container.querySelector('.tn-review-form');
    const submitBtn  = container.querySelector('.tn-rating-submit');
    const resultArea = container.querySelector('.tn-rating-result');

    if (!starsWrap) return;

    let selected = userRating;

    // Build stars
    starsWrap.innerHTML = '';
    for (let i = 1; i <= 5; i++) {
      const star = document.createElement('span');
      star.className = 'tn-star' + (i <= selected ? ' active' : '');
      star.innerHTML = '★';
      star.dataset.value = i;

      star.addEventListener('mouseenter', () => highlightStars(starsWrap, i));
      star.addEventListener('mouseleave', () => highlightStars(starsWrap, selected));
      star.addEventListener('click',      () => {
        if (!readerId || readerId === '0') {
          const returnUrl = encodeURIComponent(window.location.href);
          window.location.href = '/auth/reader/login?return=' + returnUrl;
          return;
        }
        selected = i;
        highlightStars(starsWrap, selected);
        if (reviewBox) reviewBox.style.display = 'block';
      });

      starsWrap.appendChild(star);
    }

    // Submit rating
    submitBtn?.addEventListener('click', async () => {
      if (!selected) return;
      const review = container.querySelector('.tn-review-textarea')?.value || '';

      submitBtn.disabled = true;
      submitBtn.textContent = 'Submitting…';

      try {
        const fd = new FormData();
        fd.append('article_id', articleId);
        fd.append('rating',     selected);
        fd.append('review',     review);
        fd.append('_token',     csrf);

        const res  = await fetch('/api/rate', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
          const stats = data.stats;
          if (resultArea) {
            resultArea.innerHTML = `
              <div class="tn-rating-display mt-2">
                <span class="tn-rating-avg">${stats.average}</span>
                <span class="tn-rating-stars-sm">${'★'.repeat(Math.round(stats.average))}${'☆'.repeat(5 - Math.round(stats.average))}</span>
                <span class="tn-rating-count">${stats.total} rating${stats.total !== 1 ? 's' : ''}</span>
              </div>`;
          }
          if (reviewBox) reviewBox.style.display = 'none';
          submitBtn.textContent = '✓ Saved';
        } else {
          submitBtn.disabled   = false;
          submitBtn.textContent = 'Submit Rating';
          alert(data.error || 'Something went wrong.');
        }
      } catch {
        submitBtn.disabled    = false;
        submitBtn.textContent = 'Submit Rating';
      }
    });
  }

  function highlightStars(wrap, value) {
    wrap.querySelectorAll('.tn-star').forEach((s, i) => {
      s.classList.toggle('active', i < value);
    });
  }

  // ── INIT ALL WIDGETS ON PAGE ──────────────────────
  document.querySelectorAll('.tn-rating-widget').forEach(initRatingWidget);

})();
