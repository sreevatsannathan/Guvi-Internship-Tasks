$(document).ready(function() {
    var token = localStorage.getItem('token');
    if (!token) {
        window.location.href = 'login.html';
        return;
    }

    // Load profile data
    $.ajax({
        url: 'php/profile.php',
        method: 'GET',
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                $('#age').val(response.profile.age);
                $('#dob').val(response.profile.dob);
                $('#contact').val(response.profile.contact);
            } else {
                alert('Failed to load profile: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
        }
    });

    $('#profileForm').submit(function(e) {
        e.preventDefault();
        
        var age = $('#age').val();
        var dob = $('#dob').val();
        var contact = $('#contact').val();

        $.ajax({
            url: 'php/profile.php',
            method: 'POST',
            dataType: 'json',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            data: {
                age: age,
                dob: dob,
                contact: contact
            },
            success: function(response) {
                if (response.success) {
                    alert('Profile updated successfully.');
                } else {
                    alert('Failed to update profile: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });

    $('#logoutBtn').click(function() {
        localStorage.removeItem('token');
        window.location.href = 'login.html';
    });
});