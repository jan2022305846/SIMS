@extends('layouts.app')

@section('content')
<div class="container-fluid h-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h2 class="h3 fw-semibold text-dark mb-0">
                        <i class="fas fa-users me-2 text-warning"></i>
                        User Management
                    </h2>
                    <a href="{{ route('users.create') }}" 
                       class="btn btn-warning fw-bold">
                        <i class="fas fa-plus me-1"></i>
                        Add New User
                    </a>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                <!-- Search and Filters -->
                <div class="mb-4">
                    <form method="GET" action="{{ route('users.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Search users..." 
                                   class="form-control">
                        </div>
                        <div class="col-md-3">
                            <select name="office_id" class="form-select">
                                <option value="">All Offices</option>
                                @foreach(\App\Models\Office::orderBy('name')->get() as $office)
                                    <option value="{{ $office->id }}" {{ request('office_id') == $office->id ? 'selected' : '' }}>
                                        {{ $office->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>
                                Search
                            </button>
                        </div>
                        @if(request()->hasAny(['search', 'office_id']))
                            <div class="col-md-1">
                                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-times me-1"></i>
                                    Clear
                                </a>
                            </div>
                        @endif
                    </form>
                    @if(request()->hasAny(['search', 'office_id']))
                        <div class="row mt-2">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted small">
                                        @if(request('search'))
                                            Search: "{{ request('search') }}"
                                        @endif
                                        @if(request('office_id'))
                                            @if(request('search')) | @endif
                                            Office: {{ \App\Models\Office::find(request('office_id'))?->name ?? 'Unknown' }}
                                        @endif
                                    </div>
                                    @if($users->total() > 0)
                                        <span class="text-muted small">
                                            {{ $users->firstItem() }}-{{ $users->lastItem() }} of {{ $users->total() }} users
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Users Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Type</th>
                                <th scope="col">Office</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $user->name }}</div>
                                        <div class="text-muted small">{{ $user->username }}</div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge {{ $user->isAdmin() ? 'bg-danger' : 'bg-primary' }}">
                                            {{ $user->isAdmin() ? 'Admin' : 'Faculty' }}
                                        </span>
                                    </td>
                                    <td>{{ $user->office?->name ?: 'N/A' }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('users.show', $user) }}" 
                                               class="btn btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('users.edit', $user) }}" 
                                               class="btn btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($user->id !== auth()->id())
                                                <form method="POST" action="{{ route('users.destroy', $user) }}" 
                                                      class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-2x mb-2"></i>
                                        <div>No users found.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($users->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $users->links() }}
                    </div>
                @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
