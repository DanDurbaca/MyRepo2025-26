// Edit station modal
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.edit-station').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('editSerial').value = this.dataset.serial;
            document.getElementById('editName').value = this.dataset.name;
            document.getElementById('editDescription').value = this.dataset.description;
            
            const editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        });
    });
});