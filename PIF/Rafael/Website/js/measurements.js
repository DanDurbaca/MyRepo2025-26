// View measurement details
function viewMeasurement(id) {
    fetch('api.php?action=get_measurement&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const details = `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Station:</strong> ${data.data.station_name}</p>
                            <p><strong>Temperature:</strong> ${data.data.temperature}°C</p>
                            <p><strong>Humidity:</strong> ${data.data.humidity}%</p>
                            <p><strong>Pressure:</strong> ${data.data.pressure} hPa</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Light:</strong> ${data.data.light} lux</p>
                            <p><strong>Gas:</strong> ${data.data.gas}</p>
                            <p><strong>Timestamp:</strong> ${new Date(data.data.timestamp).toLocaleString()}</p>
                            <p><strong>Record ID:</strong> ${data.data.pk_measurement}</p>
                        </div>
                    </div>
                `;
                document.getElementById('measurementDetails').innerHTML = details;
                const modal = new bootstrap.Modal(document.getElementById('viewModal'));
                modal.show();
            }
        });
}

// Confirm delete single measurement
function confirmDelete(id) {
    document.getElementById('deleteMeasurementId').value = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Export to CSV
document.addEventListener('DOMContentLoaded', function() {
    const exportBtn = document.getElementById('exportCsv');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            const table = document.getElementById('measurementsTable');
            let csv = [];
            
            // Get headers
            const headers = [];
            table.querySelectorAll('thead th').forEach(th => {
                if (!th.querySelector('.btn-group')) { // Skip action column
                    headers.push(th.textContent);
                }
            });
            csv.push(headers.join(','));
            
            // Get rows
            table.querySelectorAll('tbody tr').forEach(row => {
                const rowData = [];
                row.querySelectorAll('td').forEach((cell, index) => {
                    // Skip action column
                    if (!cell.querySelector('.btn-group')) {
                        rowData.push(cell.textContent.trim());
                    }
                });
                csv.push(rowData.join(','));
            });
            
            // Download CSV
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'measurements_<?php echo date('Y-m-d'); ?>.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        });
    }
});