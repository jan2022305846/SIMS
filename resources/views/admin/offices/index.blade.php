@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h2 class="h3 fw-semibold text-dark mb-0">
                        <i class="fas fa-building me-2 text-primary"></i>
                        Office Management
                    </h2>
                    <a href="{{ route('admin.offices.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Add Office
                    </a>
                </div>

                <!-- Offices Grid -->
                <div class="row g-4">
                    @forelse($offices as $office)
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="card-title mb-1">{{ $office->name }}</h5>
                                            <small class="text-muted">{{ $office->code }}</small>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="{{ route('admin.offices.show', $office) }}">
                                                    <i class="fas fa-eye me-2"></i>View Details
                                                </a></li>
                                                <li><a class="dropdown-item" href="{{ route('admin.offices.edit', $office) }}">
                                                    <i class="fas fa-edit me-2"></i>Edit Office
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="confirmDelete({{ $office->id }}, '{{ $office->name }}')">
                                                    <i class="fas fa-trash me-2"></i>Delete Office
                                                </a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($office->description)
                                        <p class="text-muted small mb-3">{{ Str::limit($office->description, 100) }}</p>
                                    @endif

                                    <div class="row g-2 text-center">
                                        <div class="col-6">
                                            <div class="p-2 border rounded">
                                                <div class="h5 mb-0 text-primary">{{ $office->users->count() }}</div>
                                                <small class="text-muted">Users</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 border rounded">
                                                <div class="h5 mb-0 text-info">{{ $office->items->count() }}</div>
                                                <small class="text-muted">Items</small>
                                            </div>
                                        </div>
                                    </div>

                                    @if($office->officeHead)
                                        <div class="mt-3 pt-3 border-top">
                                            <small class="text-muted">Office Head</small>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; font-size: 12px;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <small>{{ $office->officeHead->name }}</small>
                                            </div>
                                        </div>
                                    @endif

                                    @if($office->location)
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ $office->location }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-footer bg-transparent">
                                    <a href="{{ route('admin.offices.show', $office) }}" class="btn btn-outline-primary btn-sm w-100">
                                        <i class="fas fa-eye me-1"></i>
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Offices Found</h5>
                                <p class="text-muted">Get started by creating your first office.</p>
                                <a href="{{ route('admin.offices.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>
                                    Create First Office
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Office</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteOfficeName"></strong>?</p>
                <div class="alert alert-warning">
                    <small>This action cannot be undone. All associated data will be permanently removed.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Office</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(officeId, officeName) {
    document.getElementById('deleteOfficeName').textContent = officeName;
    document.getElementById('deleteForm').action = `/admin/offices/${officeId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endsection