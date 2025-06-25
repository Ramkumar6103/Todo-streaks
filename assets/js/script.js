document.addEventListener('DOMContentLoaded', function() {
    // Toggle task completion
    document.querySelectorAll('.task-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const taskId = this.dataset.taskId;
            const completed = this.checked ? 1 : 0;
            
            fetch('process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_task_status&task_id=${taskId}&completed=${completed}`
            })
            .then(response => {
                if (response.ok) {
                    const taskText = this.nextElementSibling;
                    taskText.classList.toggle('completed', completed);
                    location.reload(); // Refresh to update streaks
                }
            });
        });
    });
    
    // Delete task
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this task?')) {
                const taskId = this.dataset.taskId;
                
                fetch('process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_task&task_id=${taskId}`
                })
                .then(response => {
                    if (response.ok) {
                        this.parentElement.remove();
                    }
                });
            }
        });
    });
});