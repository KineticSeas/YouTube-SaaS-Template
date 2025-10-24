<?php
/**
 * Confirm Permanent Delete Modal - Extra confirmation for permanent task deletion
 */
?>

<div class="modal fade" id="confirm-permanent-delete-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Permanently Delete Task
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning!</strong> This action cannot be undone!
                </div>

                <p class="mb-3">You are about to permanently delete:</p>
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <strong id="delete-task-title"></strong>
                    </div>
                </div>

                <p>To confirm, type <strong>DELETE</strong> below:</p>
                <input type="text"
                       class="form-control"
                       id="confirm-delete-input"
                       placeholder="Type DELETE to confirm"
                       autocomplete="off">

                <input type="hidden" id="confirm-delete-task-id">

                <div id="permanent-delete-response" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button"
                        class="btn btn-danger"
                        id="confirm-permanent-delete-btn"
                        onclick="executePermanentDelete()"
                        disabled>
                    <i class="bi bi-trash3 me-2"></i>Delete Permanently
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Enable delete button only when DELETE is typed
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('confirm-delete-input');
    const btn = document.getElementById('confirm-permanent-delete-btn');

    if (input) {
        input.addEventListener('input', function() {
            btn.disabled = (this.value !== 'DELETE');
        });

        // Reset on modal hide
        const modal = document.getElementById('confirm-permanent-delete-modal');
        modal.addEventListener('hidden.bs.modal', function() {
            input.value = '';
            btn.disabled = true;
            document.getElementById('permanent-delete-response').innerHTML = '';
        });
    }
});

async function executePermanentDelete() {
    const taskId = document.getElementById('confirm-delete-task-id').value;
    const csrfToken = document.getElementById('csrf-token').value;
    const responseDiv = document.getElementById('permanent-delete-response');
    const btn = document.getElementById('confirm-permanent-delete-btn');

    btn.disabled = true;
    responseDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split me-2"></i>Deleting...</div>';

    try {
        const response = await fetch('/api/trash/permanent-delete.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({task_id: taskId, csrf_token: csrfToken})
        });

        const data = await response.json();

        if (data.success) {
            responseDiv.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Task deleted permanently</div>';
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('confirm-permanent-delete-modal')).hide();
                window.location.reload();
            }, 1500);
        } else {
            responseDiv.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>${data.message || 'Failed to delete'}</div>`;
            btn.disabled = false;
        }
    } catch (error) {
        responseDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>An error occurred</div>';
        btn.disabled = false;
    }
}
</script>
