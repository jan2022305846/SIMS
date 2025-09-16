{{-- resources/views/admin/backup/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Backup Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Backup Management</h1>
            <p class="text-muted">Create and manage system backups</p>
        </div>
        <div>
            <button type="button" class="btn btn-success" onclick="createFullBackup()">
                <i class="fas fa-download"></i> Create Full Backup
            </button>
            <button type="button" class="btn btn-outline-success" onclick="showSelectiveBackup()">
                <i class="fas fa-check-square"></i> Selective Backup
            </button>
            <a href="{{ route('admin.restore.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-upload"></i> Restore
            </a>
        </div>
    </div>

    <!-- System Status -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalUsers">{{ $stats['users'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalItems">{{ $stats['items'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Categories</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalCategories">{{ $stats['categories'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Requests</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalRequests">{{ $stats['requests'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Existing Backups -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Existing Backups</h5>
        </div>
        <div class="card-body">
            @if(count($backups) > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Filename</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Created</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($backups as $backup)
                            <tr>
                                <td>
                                    <i class="fas fa-file-archive text-muted mr-2"></i>
                                    {{ $backup['filename'] }}
                                </td>
                                <td>
                                    <span class="badge badge-{{ $backup['type'] === 'full' ? 'primary' : 'info' }}">
                                        {{ ucfirst($backup['type']) }}
                                    </span>
                                </td>
                                <td>{{ number_format($backup['size'] / 1024, 2) }} KB</td>
                                <td>{{ $backup['created_at']->format('M d, Y g:i A') }}</td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i> Valid
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ $backup['download_url'] }}" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-info"
                                                onclick="viewBackupDetails('{{ $backup['filename'] }}')"
                                                title="View Details">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="deleteBackup('{{ $backup['filename'] }}')"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-archive fa-3x text-muted mb-3"></i>
                    <h5>No Backups Found</h5>
                    <p class="text-muted">Create your first backup to protect your data</p>
                    <button type="button" class="btn btn-primary" onclick="createFullBackup()">
                        <i class="fas fa-download"></i> Create First Backup
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Selective Backup Modal -->
    <div class="modal fade" id="selectiveBackupModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Selective Backup</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="selectiveBackupForm">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Select Data to Backup:</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="tables[]" value="users" id="backup_users" checked>
                                    <label class="form-check-label" for="backup_users">
                                        <i class="fas fa-users text-primary"></i> Users ({{ $stats['users'] ?? 0 }} records)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="tables[]" value="categories" id="backup_categories" checked>
                                    <label class="form-check-label" for="backup_categories">
                                        <i class="fas fa-tags text-info"></i> Categories ({{ $stats['categories'] ?? 0 }} records)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="tables[]" value="items" id="backup_items" checked>
                                    <label class="form-check-label" for="backup_items">
                                        <i class="fas fa-boxes text-success"></i> Items ({{ $stats['items'] ?? 0 }} records)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="tables[]" value="requests" id="backup_requests" checked>
                                    <label class="form-check-label" for="backup_requests">
                                        <i class="fas fa-clipboard-list text-warning"></i> Requests ({{ $stats['requests'] ?? 0 }} records)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Additional Data:</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="tables[]" value="activity_logs" id="backup_logs">
                                    <label class="form-check-label" for="backup_logs">
                                        <i class="fas fa-history text-secondary"></i> Activity Logs ({{ $stats['activity_logs'] ?? 0 }} records)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="tables[]" value="acknowledgments" id="backup_acks">
                                    <label class="form-check-label" for="backup_acks">
                                        <i class="fas fa-signature text-dark"></i> Acknowledgments ({{ $stats['acknowledgments'] ?? 0 }} records)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="tables[]" value="scan_logs" id="backup_scans">
                                    <label class="form-check-label" for="backup_scans">
                                        <i class="fas fa-qrcode text-muted"></i> Scan Logs ({{ $stats['scan_logs'] ?? 0 }} records)
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="form-group">
                            <label for="backup_name">Backup Name (Optional)</label>
                            <input type="text" class="form-control" name="backup_name" id="backup_name" 
                                   placeholder="Leave empty for auto-generated name">
                            <small class="form-text text-muted">Custom name for this backup</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="createSelectiveBackup()">
                        <i class="fas fa-download"></i> Create Backup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Modal -->
    <div class="modal fade" id="progressModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Creating Backup</h5>
                </div>
                <div class="modal-body">
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%"></div>
                    </div>
                    <div id="progressText">Preparing backup...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function createFullBackup() {
    if (!confirm('This will create a full backup of all your data. Continue?')) {
        return;
    }

    showProgress('Creating full backup...');

    fetch('{{ route("admin.backup.create") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ type: 'full' })
    })
    .then(response => response.json())
    .then(data => {
        hideProgress();
        
        if (data.success) {
            toastr.success('Full backup created successfully');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            toastr.error(data.message || 'Backup creation failed');
        }
    })
    .catch(error => {
        hideProgress();
        console.error('Error:', error);
        toastr.error('Error creating backup');
    });
}

function showSelectiveBackup() {
    $('#selectiveBackupModal').modal('show');
}

function createSelectiveBackup() {
    const formData = new FormData(document.getElementById('selectiveBackupForm'));
    const tables = formData.getAll('tables[]');
    
    if (tables.length === 0) {
        toastr.error('Please select at least one table to backup');
        return;
    }

    $('#selectiveBackupModal').modal('hide');
    showProgress('Creating selective backup...');

    const backupData = {
        type: 'selective',
        tables: tables,
        backup_name: formData.get('backup_name') || null
    };

    fetch('{{ route("admin.backup.create-selective") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(backupData)
    })
    .then(response => response.json())
    .then(data => {
        hideProgress();
        
        if (data.success) {
            toastr.success('Selective backup created successfully');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            toastr.error(data.message || 'Backup creation failed');
        }
    })
    .catch(error => {
        hideProgress();
        console.error('Error:', error);
        toastr.error('Error creating backup');
    });
}

function deleteBackup(filename) {
    if (!confirm(`Are you sure you want to delete backup: ${filename}?`)) {
        return;
    }

    fetch(`{{ url('admin/backup') }}/${filename}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success('Backup deleted successfully');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            toastr.error(data.message || 'Failed to delete backup');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Error deleting backup');
    });
}

function viewBackupDetails(filename) {
    // Implementation for viewing backup details
    toastr.info(`Viewing details for: ${filename}`);
}

function showProgress(message) {
    document.getElementById('progressText').textContent = message;
    $('#progressModal').modal('show');
    
    // Simulate progress animation
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 20;
        if (progress > 90) progress = 90;
        
        document.querySelector('.progress-bar').style.width = progress + '%';
        
        if (progress >= 90) {
            clearInterval(interval);
        }
    }, 500);
}

function hideProgress() {
    document.querySelector('.progress-bar').style.width = '100%';
    setTimeout(() => {
        $('#progressModal').modal('hide');
        document.querySelector('.progress-bar').style.width = '0%';
    }, 500);
}
</script>
@endsection