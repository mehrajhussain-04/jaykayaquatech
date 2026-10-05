    </div><!-- /.admin-content -->
  </div><!-- /.admin-main -->
</div><!-- /.admin-shell -->

<!-- Shared delete/confirm modal, driven by data-confirm attributes -->
<div class="modal-scrim" id="confirmScrim">
  <div class="modal-box">
    <h3 id="confirmTitle">Please confirm</h3>
    <p id="confirmMessage">Are you sure?</p>
    <form id="confirmForm" method="POST">
      <?= csrf_field() ?>
      <div class="modal-actions">
        <button type="button" class="btn btn-secondary btn-sm" id="confirmCancel">Cancel</button>
        <button type="submit" class="btn btn-danger btn-sm">Yes, continue</button>
      </div>
    </form>
  </div>
</div>

<script src="assets/admin-script.js"></script>
</body>
</html>
