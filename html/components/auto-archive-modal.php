<?php
/**
 * Auto-Archive Modal - Settings for automatically archiving completed tasks
 */
?>

<div class="modal fade" id="auto-archive-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-clock-history me-2"></i>Auto-Archive Completed Tasks
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="auto-archive-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Archive completed tasks that are older than a specified number of days.
                    </div>

                    <div class="mb-3">
                        <label for="auto-archive-days" class="form-label">
                            Archive tasks completed more than
                        </label>
                        <div class="input-group">
                            <input type="number"
                                   class="form-control"
                                   id="auto-archive-days"
                                   name="days_old"
                                   value="30"
                                   min="1"
                                   max="365"
                                   required>
                            <span class="input-group-text">days ago</span>
                        </div>
                        <div class="form-text">
                            Default: 30 days. Completed tasks older than this will be archived.
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Note:</strong> This will archive all completed tasks older than the specified days.
                        You can restore them from the archive anytime.
                    </div>

                    <div id="auto-archive-response"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="runAutoArchive()">
                    <i class="bi bi-archive me-2"></i>Archive Now
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function runAutoArchive() {
    const days = document.getElementById('auto-archive-days').value;
    const csrfToken = document.querySelector('#auto-archive-form input[name="csrf_token"]').value;
    const responseDiv = document.getElementById('auto-archive-response');

    // Show loading
    responseDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split me-2"></i>Archiving tasks...</div>';

    // Disable button
    event.target.disabled = true;

    fetch('/api/archive/auto-archive.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            days_old: days,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            responseDiv.innerHTML = `<div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>${data.message || 'Tasks archived successfully!'}
                <br><strong>${data.count} task(s) archived.</strong>
            </div>`;

            // Reload page after delay
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            responseDiv.innerHTML = `<div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>${data.message || 'Failed to archive tasks'}
            </div>`;
            event.target.disabled = false;
        }
    })
    .catch(error => {
        responseDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>An error occurred</div>';
        event.target.disabled = false;
    });
}
</script>
