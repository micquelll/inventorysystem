<?php if (empty($_SESSION['user'])): ?>
  </div>
</div>
<?php else: ?>
    </main>
  </div>
</div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base ?? '' ?>assets/app.js"></script>
</body>
</html>
