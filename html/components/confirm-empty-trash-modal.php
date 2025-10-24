<?php
/**
 * Confirm Empty Trash Modal - Extra strong confirmation for emptying trash
 */
?>

<div class="modal fade" id="empty-trash-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Empty Trash - DANGER!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h5><i class="bi bi-exclamation-octagon me-2"></i>CRITICAL WARNING!</h5>
                    <p class="mb-0"><strong>This action CANNOT be undone!</strong></p>
                </div>

                <p class="fw-bold">You are about to permanently delete:</p>
                <div class="card bg-danger text-white mb-3">
                    <div class="card-body">
                        <h4 class="mb-0">
                            <span id="bulk-delete-count"><?php echo $totalTasks; ?></span> task(s)
                        </h4>
                    </div>
                </div>

                <p>All data will be lost forever, including:</p>
                <ul class="text-danger">
                    <li>Task titles and descriptions</li>
                    <li>All task metadata (priority, status, dates)</li>
                    <li>Category associations</li>
                    <li>Task history</li>
                </ul>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="understand-permanent-checkbox">
                    <label class="form-check-label fw-bold" for="understand-permanent-checkbox">
                        I understand this is permanent and cannot be recovered
                    </label>
                </div>

                <p class="fw-bold">To confirm, type <strong class="text-danger">EMPTY TRASH</strong> below:</p>
                <input type="text"
                       class="form-control"
                       id="empty-trash-confirm-input"
                       placeholder="Type EMPTY TRASH to confirm"
                       autocomplete="off">

                <input type="hidden" id="empty-trash-mode" value="all">

                <div id="empty-trash-response" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button"
                        class="btn btn-danger"
                        id="confirm-empty-trash-btn"
                        onclick="executeEmptyTrash()"
                        disabled>
                    <i class="bi bi-trash3 me-2"></i>Empty Trash Permanently
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Enable empty trash button only when conditions met
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('empty-trash-confirm-input');
    const checkbox = document.getElementById('understand-permanent-checkbox');
    const btn = document.getElementById('confirm-empty-trash-btn');

    function checkEmptyTrashConditions() {
        if (input && checkbox && btn) {
            btn.disabled = !(input.value === 'EMPTY TRASH' && checkbox.checked);
        }
    }

    if (input) {
        input.addEventListener('input', checkEmptyTrashConditions);
    }

    if (checkbox) {
        checkbox.addEventListener('change', checkEmptyTrashConditions);
    }

    // Reset on modal hide
    const modal = document.getElementById('empty-trash-modal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            if (input) input.value = '';
            if (checkbox) checkbox.checked = false;
            if (btn) btn.disabled = true;
            const response = document.getElementById('empty-trash-response');
            if (response) response.innerHTML = '';
        });
    }
});

async function executeEmptyTrash() {
    const mode = document.getElementById('empty-trash-mode').value;
    const csrfToken = document.getElementById('csrf-token').value;
    const responseDiv = document.getElementById('empty-trash-response');
    const btn = document.getElementById('confirm-empty-trash-btn');

    btn.disabled = true;
    responseDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split me-2"></i>Deleting all tasks...</div>';

    try {
        let response;

        if (mode === 'bulk' && typeof selectedTasks !== 'undefined') {
            // Bulk delete selected tasks
            let deleted = 0;
            for (const taskId of selectedTasks) {
                const res = await fetch('/api/trash/permanent-delete.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({task_id: taskId, csrf_token: csrfToken})
                });
                if (res.ok) deleted++;
            }

            if (deleted > 0) {
                responseDiv.innerHTML = `<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>${deleted} task(s) deleted permanently</div>`;
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('empty-trash-modal')).hide();
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error('Failed to delete tasks');
            }
        } else {
            // Empty entire trash
            response = await fetch('/api/trash/empty-trash.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({csrf_token: csrfToken})
            });

            const data = await response.json();

            if (data.success) {
                responseDiv.innerHTML = `<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>${data.count} task(s) deleted permanently</div>`;
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('empty-trash-modal')).hide();
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || 'Failed to empty trash');
            }
        }
    } catch (error) {
        responseDiv.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>${error.message}</div>`;
        btn.disabled = false;
    }
}
</script>
