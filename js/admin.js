document.addEventListener('DOMContentLoaded', function() {
    // Silme butonlarına tıklama olayı ekle
    const deleteButtons = document.querySelectorAll('.btn-danger.btn-action');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')) {
                e.preventDefault();
            }
        });
    });
});

document.getElementById('selectAll').addEventListener('change', function() {
    let checkboxes = document.querySelectorAll('input[name="selected_users[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});