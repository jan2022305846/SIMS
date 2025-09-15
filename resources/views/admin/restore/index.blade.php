{{-- resources/views/admin/restore/index.blade.php --}}
@extends('layouts.app')

@section('title', 'System Restore')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">System Restore</h1>
            <p class="text-muted">Restore data from backup files</p>
        </div>
        <div>
            <button type="button" class="btn btn-warning" onclick="createSafetyBackup()">
                <i class="fas fa-shield-alt"></i> Create Safety Backup
            </button>
            <a href="{{ route('admin.backup.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Backups
            </a>
        </div>
    </div>

    <!-- Safety Warning -->
    <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Warning:</strong> Restoring data will modify your current database. It's recommended to create a safety backup before proceeding.
    </div>

    <!-- Restore Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Upload and Restore Backup</h5>
        </div>
        <div class="card-body">
            <form id="restoreForm" enctype="multipart/form-data">
                @csrf
                
                <!-- Step 1: File Upload -->
                <div class="restore-step" id="step1">
                    <h6>Step 1: Select Backup File</h6>
                    <div class="form-group">
                        <label for="backup_file">Backup File (.zip)</label>
                        <input type="file" 
                               class="form-control-file" 
                               id="backup_file" 
                               name="backup_file" 
                               accept=".zip" 
                               required>
                        <small class="form-text text-muted">Select a ZIP backup file to restore from</small>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="analyzeBackup()">
                        <i class="fas fa-search"></i> Analyze Backup
                    </button>
                </div>

                <!-- Step 2: Backup Analysis -->
                <div class="restore-step d-none" id="step2">
                    <hr>
                    <h6>Step 2: Backup Analysis</h6>
                    <div id="analysisResults"></div>
                    
                    <!-- Restore Options -->
                    <div class="mt-4">
                        <h6>Restore Options</h6>
                        
                        <div class="form-group">
                            <label>Restore Mode</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="restore_mode" id="mode_replace" value="replace">
                                <label class="form-check-label" for="mode_replace">
                                    <strong>Replace</strong> - Clear existing data and restore from backup
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="restore_mode" id="mode_merge" value="merge" checked>
                                <label class="form-check-label" for="mode_merge">
                                    <strong>Merge</strong> - Add backup data to existing data (skip duplicates)
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Select Tables to Restore</label>
                            <div id="tableSelection"></div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="create_safety_backup" checked>
                                <label class="form-check-label" for="create_safety_backup">
                                    Create safety backup before restore
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-success" onclick="performRestore()">
                        <i class="fas fa-download"></i> Start Restore
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Existing Backups -->
    @if(count($backups) > 0)
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Available Backups</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Size</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($backups as $backup)
                        <tr>
                            <td>{{ $backup['filename'] }}</td>
                            <td>{{ number_format($backup['size'] / 1024, 2) }} KB</td>
                            <td>{{ $backup['created_at']->format('M d, Y H:i') }}</td>
                            <td>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary"
                                        onclick="loadExistingBackup('{{ $backup['filename'] }}')">
                                    <i class="fas fa-upload"></i> Use This Backup
                                </button>
                                <a href="{{ $backup['download_url'] }}" 
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Progress Modal -->
    <div class="modal fade" id="progressModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Restore Progress</h5>
                </div>
                <div class="modal-body">
                    <div class="progress mb-3">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div id="progressText">Preparing restore...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Modal -->
    <div class="modal fade" id="resultsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Restore Results</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="restoreResults">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentAnalysis = null;

function analyzeBackup() {
    const fileInput = document.getElementById('backup_file');
    const file = fileInput.files[0];
    
    if (!file) {
        toastr.error('Please select a backup file first');
        return;
    }

    const formData = new FormData();
    formData.append('backup_file', file);
    formData.append('_token', '{{ csrf_token() }}');

    // Show loading
    document.getElementById('step2').classList.add('d-none');
    toastr.info('Analyzing backup file...');

    fetch('{{ route("admin.restore.analyze") }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentAnalysis = data.analysis;
            displayAnalysis(data.analysis);
            document.getElementById('step2').classList.remove('d-none');
            toastr.success('Backup analysis completed');
        } else {
            toastr.error(data.message || 'Failed to analyze backup');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Error analyzing backup file');
    });
}

function displayAnalysis(analysis) {
    const resultsDiv = document.getElementById('analysisResults');
    const tableSelectionDiv = document.getElementById('tableSelection');
    
    let html = '<div class="row">';
    
    // Backup metadata
    if (analysis.metadata) {
        html += '<div class="col-md-6">';
        html += '<h6>Backup Information</h6>';
        html += '<table class="table table-sm">';
        html += `<tr><td>Created:</td><td>${analysis.metadata.created_at || 'Unknown'}</td></tr>`;
        html += `<tr><td>Type:</td><td>${analysis.metadata.backup_type || 'Full'}</td></tr>`;
        html += `<tr><td>Version:</td><td>${analysis.metadata.app_version || 'Unknown'}</td></tr>`;
        html += '</table>';
        html += '</div>';
    }
    
    // Available tables
    html += '<div class="col-md-6">';
    html += '<h6>Available Data</h6>';
    html += '<table class="table table-sm">';
    html += '<tr><th>Table</th><th>Records</th><th>Size</th></tr>';
    
    let tableCheckboxes = '';
    for (const [table, info] of Object.entries(analysis.available_tables)) {
        html += `<tr>`;
        html += `<td>${table.replace('_', ' ').toUpperCase()}</td>`;
        html += `<td>${info.record_count}</td>`;
        html += `<td>${(info.file_size / 1024).toFixed(2)} KB</td>`;
        html += `</tr>`;
        
        tableCheckboxes += `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="tables[]" value="${table}" id="table_${table}" checked>
                <label class="form-check-label" for="table_${table}">
                    ${table.replace('_', ' ').toUpperCase()} (${info.record_count} records)
                </label>
            </div>`;
    }
    
    html += '</table>';
    html += '</div>';
    html += '</div>';
    
    resultsDiv.innerHTML = html;
    tableSelectionDiv.innerHTML = tableCheckboxes;
}

function createSafetyBackup() {
    toastr.info('Creating safety backup...');
    
    fetch('{{ route("admin.restore.safety-backup") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(`Safety backup created: ${data.backup_name}`);
        } else {
            toastr.error(data.message || 'Failed to create safety backup');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Error creating safety backup');
    });
}

function performRestore() {
    const fileInput = document.getElementById('backup_file');
    const file = fileInput.files[0];
    
    if (!file || !currentAnalysis) {
        toastr.error('Please analyze the backup file first');
        return;
    }

    // Get selected options
    const restoreMode = document.querySelector('input[name="restore_mode"]:checked').value;
    const selectedTables = Array.from(document.querySelectorAll('input[name="tables[]"]:checked')).map(cb => cb.value);
    const createSafety = document.getElementById('create_safety_backup').checked;
    
    if (selectedTables.length === 0) {
        toastr.error('Please select at least one table to restore');
        return;
    }

    // Confirm action
    if (!confirm(`Are you sure you want to ${restoreMode} the selected data? This action cannot be undone.`)) {
        return;
    }

    // Show progress modal
    $('#progressModal').modal('show');
    updateProgress(0, 'Starting restore process...');

    // Create safety backup first if requested
    if (createSafety) {
        updateProgress(10, 'Creating safety backup...');
        // We'll continue with the actual restore after safety backup
        fetch('{{ route("admin.restore.safety-backup") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateProgress(30, 'Safety backup created. Starting restore...');
                performActualRestore(file, restoreMode, selectedTables);
            } else {
                throw new Error(data.message || 'Failed to create safety backup');
            }
        })
        .catch(error => {
            $('#progressModal').modal('hide');
            toastr.error('Error: ' + error.message);
        });
    } else {
        updateProgress(30, 'Starting restore...');
        performActualRestore(file, restoreMode, selectedTables);
    }
}

function performActualRestore(file, restoreMode, selectedTables) {
    const formData = new FormData();
    formData.append('backup_file', file);
    formData.append('restore_mode', restoreMode);
    formData.append('tables', JSON.stringify(selectedTables));
    formData.append('restore_options', JSON.stringify({}));
    formData.append('_token', '{{ csrf_token() }}');

    updateProgress(50, 'Restoring data...');

    fetch('{{ route("admin.restore.execute") }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        updateProgress(100, 'Restore completed!');
        
        setTimeout(() => {
            $('#progressModal').modal('hide');
            
            if (data.success) {
                showRestoreResults(data);
                toastr.success('Restore completed successfully');
            } else {
                toastr.error(data.message || 'Restore failed');
            }
        }, 1000);
    })
    .catch(error => {
        $('#progressModal').modal('hide');
        console.error('Error:', error);
        toastr.error('Error during restore process');
    });
}

function updateProgress(percentage, text) {
    document.querySelector('.progress-bar').style.width = percentage + '%';
    document.getElementById('progressText').textContent = text;
}

function showRestoreResults(data) {
    let html = '<div class="alert alert-success">Restore completed successfully!</div>';
    
    html += '<h6>Restored Records:</h6>';
    html += '<table class="table table-sm">';
    html += '<tr><th>Table</th><th>Records Restored</th></tr>';
    
    for (const [table, count] of Object.entries(data.restored_counts)) {
        html += `<tr><td>${table.replace('_', ' ').toUpperCase()}</td><td>${count}</td></tr>`;
    }
    
    html += '</table>';
    
    document.getElementById('restoreResults').innerHTML = html;
    $('#resultsModal').modal('show');
}

function resetForm() {
    document.getElementById('restoreForm').reset();
    document.getElementById('step2').classList.add('d-none');
    currentAnalysis = null;
}

function loadExistingBackup(filename) {
    // This would typically involve downloading the backup and setting it in the file input
    // For now, just show a message
    toastr.info(`Selected backup: ${filename}. Please download and upload it using the form above.`);
}
</script>
@endsection