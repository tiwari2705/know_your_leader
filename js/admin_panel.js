$(document).ready(function() {

    // --- 1. Citizen Search Functionality ---
    function fetchCitizens(search_term = '') {
        $('#citizen-table-body').html('<tr><td colspan="4" class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Loading...</td></tr>');
        
        $.ajax({
            url: 'api/fetch_citizens.php',
            type: 'GET',
            data: { search: search_term },
            dataType: 'json',
            success: function(citizens) {
                let html = '';
                if (citizens.length > 0) {
                    citizens.forEach(citizen => {
                        html += `
                            <tr>
                                <td>${citizen.id}</td>
                                <td>${citizen.full_name}</td>
                                <td>${citizen.email}</td>
                                <td>${citizen.created_at.substring(0, 10)}</td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="4" class="text-center text-muted">No citizens found.</td></tr>';
                }
                $('#citizen-table-body').html(html);
            },
            error: function() {
                $('#citizen-table-body').html('<tr><td colspan="4" class="text-center text-danger">Error loading citizen data.</td></tr>');
            }
        });
    }

    // Initial load and live search
    fetchCitizens(); 
    $('#citizen-search-input').on('keyup', function() {
        const term = $(this).val();
        clearTimeout($.data(this, 'timer'));
        $(this).data('timer', setTimeout(function() {
            fetchCitizens(term);
        }, 300));
    });

    // --- 2. Leader CRUD Handlers ---

    $(document).on('click', '.delete-leader-btn', function() {
        const leaderId = $(this).data('id');
        if (confirm('Are you absolutely sure you want to delete this leader profile? This action cannot be undone.')) {
            $.ajax({
                url: 'api/leader_crud.php',
                type: 'POST',
                data: { action: 'delete', leader_id: leaderId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Leader deleted successfully!');
                        window.location.reload(); 
                    } else {
                        alert('Error deleting leader: ' + response.error);
                    }
                },
                error: function(xhr) {
                    alert('Server error: Failed to delete leader. Status: ' + xhr.status);
                }
            });
        }
    });

    // Handle Edit Modal Population 
    $('#leaderModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const modal = $(this);
        const mode = button.data('mode'); 
        const form = modal.find('#leader-form');
        
        form.trigger('reset');
        
        if (mode === 'add') {
            modal.find('.modal-title').text('Add New Leader');
            modal.find('#action').val('add');
            modal.find('#leader_id').val('');
        } else {
            const leaderId = button.data('id');
            modal.find('.modal-title').text('Edit Leader Profile');
            modal.find('#action').val('edit');
            modal.find('#leader_id').val(leaderId);
            
            $.ajax({
                url: 'api/get_leader.php', 
                type: 'GET',
                data: { id: leaderId },
                dataType: 'json',
                success: function(response) {
                    if (response.details && response.details.id) {
                        const leader = response.details;
                        
                        for (const key in leader) {
                            const $el = form.find(`[name="${key}"]`);
                            if ($el.length && key !== 'photo') {
                                $el.val(leader[key]);
                            }
                        }
                    } else {
                        let errorMsg = response.error || 'Could not fetch leader data for editing.';
                        alert(errorMsg);
                        modal.modal('hide');
                    }
                },
                error: function(xhr) {
                    alert('Error fetching leader data. Status: ' + xhr.status);
                    console.error("AJAX Error:", xhr.responseText);
                    modal.modal('hide');
                }
            });
        }
    });

    $('#leader-form').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this); 

        $.ajax({
            url: 'api/leader_crud.php',
            type: 'POST',
            data: formData,
            contentType: false, 
            processData: false, 
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Leader profile saved successfully!');
                    $('#leaderModal').modal('hide');
                    window.location.reload(); 
                } else {
                    alert('Error saving leader: ' + response.error);
                }
            },
            error: function(xhr, status, error) {
                alert('An AJAX error occurred. Check server logs. Response: ' + xhr.responseText);
            }
        });
    });
});

