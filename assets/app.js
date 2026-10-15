// 1) Client-side validation: Bootstrap's built-in HTML5 validation styles.
document.querySelectorAll('form.needs-validation').forEach(function (form) {
  form.addEventListener('submit', function (event) {
    // Extra check: password and confirm password must match.
    var pw = form.querySelector('[name="password"]');
    var again = form.querySelector('[name="confirm"]');
    if (pw && again) {
      again.setCustomValidity(pw.value === again.value ? '' : 'Passwords do not match');
    }
    if (!form.checkValidity()) {
      event.preventDefault();
    }
    form.classList.add('was-validated');
  });
});

// 2) Quick stock in/out on the products page without reloading (asynchronous request).
document.querySelectorAll('form.stock-form').forEach(function (form) {
  form.addEventListener('submit', function (event) {
    event.preventDefault();
    var row = form.closest('tr');
    fetch('stock.php', {
      method: 'POST',
      body: new FormData(form, event.submitter),   // event.submitter tells us which button was pressed (In or Out)
      headers: { 'X-Requested-With': 'fetch' }
    })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data.ok) {
          row.querySelector('.qty-cell').textContent = data.quantity;
          row.querySelector('.status-cell').innerHTML = data.status_html;
          form.querySelector('[name="qty"]').value = 1;
        } else {
          alert(data.message);
        }
      })
      .catch(function () { alert('Could not update the stock. Please try again.'); });
  });
});

// 3) Ask before deleting.
document.querySelectorAll('form.confirm-delete').forEach(function (form) {
  form.addEventListener('submit', function (event) {
    if (!window.confirm(form.dataset.message || 'Are you sure?')) {
      event.preventDefault();
    }
  });
});
