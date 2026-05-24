// User functions
function editUser(username, email, firstName, lastName, role) {
    document.getElementById('editUserUsername').value = username;
    document.getElementById('editUserEmail').value = email;
    document.getElementById('editUserFirstName').value = firstName;
    document.getElementById('editUserLastName').value = lastName;
    document.getElementById('editUserRole').value = role;
    
    const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    modal.show();
}

function deleteUser(username) {
    if (confirm('Delete user "' + username + '"? This will also delete all their stations, measurements, and collections.')) {
        document.getElementById('deleteUserUsername').value = username;
        document.getElementById('deleteUserForm').submit();
    }
}

// Station functions
function editStation(serial, name, description, owner) {
    document.getElementById('editStationSerial').value = serial;
    document.getElementById('editStationName').value = name;
    document.getElementById('editStationDescription').value = description;
    document.getElementById('editStationOwner').value = owner || '';
    
    const modal = new bootstrap.Modal(document.getElementById('editStationModal'));
    modal.show();
}

function deleteStation(serial) {
    if (confirm('Delete station "' + serial + '"? This will also delete all measurements from this station.')) {
        document.getElementById('deleteStationSerial').value = serial;
        document.getElementById('deleteStationForm').submit();
    }
}

// Measurement functions
function deleteMeasurement(id) {
    if (confirm('Delete measurement #' + id + '?')) {
        document.getElementById('deleteMeasurementId').value = id;
        document.getElementById('deleteMeasurementForm').submit();
    }
}

// Collection functions
function deleteCollection(id) {
    if (confirm('Delete collection #' + id + '?')) {
        document.getElementById('deleteCollectionId').value = id;
        document.getElementById('deleteCollectionForm').submit();
    }
}

// Friendship functions
function removeFriendship(user1, user2) {
    if (confirm('Remove friendship between ' + user1 + ' and ' + user2 + '?')) {
        document.getElementById('removeUser1').value = user1;
        document.getElementById('removeUser2').value = user2;
        document.getElementById('removeFriendshipForm').submit();
    }
}

// Auto-focus on first input when modals open
document.addEventListener('DOMContentLoaded', function() {
    const createUserModal = document.getElementById('createUserModal');
    if (createUserModal) {
        createUserModal.addEventListener('shown.bs.modal', function () {
            document.querySelector('#createUserModal [name="username"]').focus();
        });
    }
    
    const createStationModal = document.getElementById('createStationModal');
    if (createStationModal) {
        createStationModal.addEventListener('shown.bs.modal', function () {
            document.querySelector('#createStationModal [name="serial_number"]').focus();
        });
    }
});