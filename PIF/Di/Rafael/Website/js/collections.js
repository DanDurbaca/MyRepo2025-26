// Rename collection
function renameCollection(id, currentName) {
    document.getElementById('renameCollectionId').value = id;
    document.getElementById('renameCollectionName').value = currentName;
    const modal = new bootstrap.Modal(document.getElementById('renameModal'));
    modal.show();
}

// Share collection
function shareCollection(id) {
    document.getElementById('shareCollectionId').value = id;
    const modal = new bootstrap.Modal(document.getElementById('shareModal'));
    modal.show();
}

// Unshare collection
function unshareCollection(collectionId, username) {
    if (confirm('Remove shared access for this user?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'collections.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'unshare_collection';
        form.appendChild(actionInput);
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = document.querySelector('[name="csrf_token"]').value;
        form.appendChild(csrfInput);
        
        const collectionInput = document.createElement('input');
        collectionInput.type = 'hidden';
        collectionInput.name = 'collection_id';
        collectionInput.value = collectionId;
        form.appendChild(collectionInput);
        
        const friendInput = document.createElement('input');
        friendInput.type = 'hidden';
        friendInput.name = 'friend_username';
        friendInput.value = username;
        form.appendChild(friendInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Confirm delete
function confirmDelete(id) {
    document.getElementById('deleteCollectionId').value = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// View collection details
function viewCollection(id) {
    fetch('api.php?action=get_collection&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let details = `
                    <div class="row">
                        <div class="col-md-4">
                            <h6>Collection Info</h6>
                            <p><strong>Name:</strong> ${data.collection.name}</p>
                            <p><strong>Description:</strong> ${data.collection.description}</p>
                            <p><strong>Created by:</strong> ${data.collection.creator}</p>
                            <p><strong>Measurements:</strong> ${data.measurements.length}</p>
                        </div>
                        <div class="col-md-8">
                            <div class="chart-container">
                                <canvas id="measurementChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h6>Measurements</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Temp (°C)</th>
                                    <th>Humidity (%)</th>
                                    <th>Pressure (hPa)</th>
                                    <th>Light (lux)</th>
                                    <th>Gas</th>
                                </tr>
                            </thead>
                            <tbody>`;
                
                data.measurements.forEach(m => {
                    details += `
                        <tr>
                            <td>${new Date(m.timestamp).toLocaleString()}</td>
                            <td>${m.temperature}</td>
                            <td>${m.humidity}</td>
                            <td>${m.pressure}</td>
                            <td>${m.light}</td>
                            <td>${m.gas}</td>
                        </tr>`;
                });
                
                details += `</tbody></table></div>`;
                
                document.getElementById('collectionDetails').innerHTML = details;
                
                // Create chart if Chart.js is available
                if (typeof Chart !== 'undefined') {
                    const ctx = document.getElementById('measurementChart').getContext('2d');
                    const timestamps = data.measurements.map(m => new Date(m.timestamp).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}));
                    const temps = data.measurements.map(m => m.temperature);
                    const humidities = data.measurements.map(m => m.humidity);
                    
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: timestamps,
                            datasets: [
                                {
                                    label: 'Temperature (°C)',
                                    data: temps,
                                    borderColor: 'rgb(255, 99, 132)',
                                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                    yAxisID: 'y'
                                },
                                {
                                    label: 'Humidity (%)',
                                    data: humidities,
                                    borderColor: 'rgb(54, 162, 235)',
                                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                    yAxisID: 'y1'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            scales: {
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    title: {
                                        display: true,
                                        text: 'Temperature (°C)'
                                    }
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    title: {
                                        display: true,
                                        text: 'Humidity (%)'
                                    },
                                    grid: {
                                        drawOnChartArea: false
                                    }
                                }
                            }
                        }
                    });
                }
                
                const modal = new bootstrap.Modal(document.getElementById('viewModal'));
                modal.show();
            }
        });
}