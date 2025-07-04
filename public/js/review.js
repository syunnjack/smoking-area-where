document.addEventListener("DOMContentLoaded", () => {
  if (typeof window.spotId === 'undefined') {
    console.error("❌ spotId が未定義です。Bladeの <script> で定義されていますか？");
    return;
  }

  const token = document.querySelector('meta[name="csrf-token"]').content;
  const spotId = window.spotId;

  // ⭐ 星評価
  document.querySelectorAll('.rating-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const rating = btn.dataset.rating;

      fetch(`/spots/${spotId}/reviews`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': token,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ rating })
      })
      .then(res => res.json())
      .then(data => {
        document.getElementById('rating-status').textContent = '評価を送信しました！';

        const ul = document.getElementById('comment-list');
        const li = document.createElement('li');
        li.className = 'list-group-item';
        li.innerHTML = `
          ${data.rating ? '<span class="text-warning me-1">' + '★'.repeat(data.rating) + '</span>' : ''}
          ${data.comment ?? ''}
          <span class="text-muted float-end small">${data.created_at}</span>
        `;
        ul.prepend(li);
      })
      .catch(err => console.error("❌ fetch失敗:", err));
    });
  });

  // 💬 コメント
  const form = document.getElementById('comment-form');
  form?.addEventListener('submit', function(e) {
    e.preventDefault();
    const textarea = this.querySelector('textarea');
    const comment = textarea.value;

    fetch(`/spots/${spotId}/reviews`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ comment })
    })
    .then(res => res.json())
    .then(data => {
      document.getElementById('rating-status').textContent = '評価を送信しました！';

      const ul = document.getElementById('comment-list');
      const li = document.createElement('li');
      li.className = 'list-group-item';
      li.innerHTML = `
        ${data.rating ? '<span class="text-warning me-1">' + '★'.repeat(data.rating) + '</span>' : ''}
        ${data.comment ?? ''}
        <span class="text-muted float-end small">${data.created_at}</span>
      `;
      ul.prepend(li);
      textarea.value = '';
    })
    .catch(err => console.error("❌ fetch失敗:", err));
  });
});