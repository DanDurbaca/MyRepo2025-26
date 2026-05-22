// Add friend from suggestions
function addFriend(username) {
    document.querySelector('[name="friend_username"]').value = username;
    const modal = new bootstrap.Modal(document.getElementById('addFriendModal'));
    modal.show();
}

// Remove friend
function removeFriend(username) {
    if (confirm('Are you sure you want to remove this friend? All shared collections will be unshared.')) {
        document.getElementById('removeFriendUsername').value = username;
        document.getElementById('removeFriendForm').submit();
    }
}

// Auto-focus on username field when modal opens
document.addEventListener('DOMContentLoaded', function() {
    const addFriendModal = document.getElementById('addFriendModal');
    if (addFriendModal) {
        addFriendModal.addEventListener('shown.bs.modal', function () {
            document.querySelector('[name="friend_username"]').focus();
        });
    }
});