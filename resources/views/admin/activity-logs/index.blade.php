@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Activity Logs</h1>
        <p class="mt-2 text-sm text-gray-600">Track all user activities and system events</p>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Filters</h3>
        </div>
        <div class="p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div>
                    <label for="log_name" class="block text-sm font-medium text-gray-700 mb-1">Log Type</label>
                    <select name="log_name" id="log_name" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Types</option>
                        @foreach($logNames as $logName)
                            <option value="{{ $logName }}" {{ request('log_name') === $logName ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $logName)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="event" class="block text-sm font-medium text-gray-700 mb-1">Event</label>
                    <select name="event" id="event" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Events</option>
                        @foreach($events as $event)
                            <option value="{{ $event }}" {{ request('event') === $event ? 'selected' : '' }}>
                                {{ ucfirst($event) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">User</label>
                    <select name="user_id" id="user_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" 
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                           placeholder="Search description..."
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div class="md:col-span-3 lg:col-span-6 flex items-end space-x-3">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Apply Filters
                    </button>
                    <a href="{{ route('activity-logs.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Clear
                    </a>
                    <a href="{{ route('activity-logs.analytics') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        View Analytics
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Activity Logs List -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Activity Logs ({{ number_format($activities->total()) }} total)
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Recent system and user activities</p>
            </div>
        </div>
        
        @if($activities->count() > 0)
            <ul class="divide-y divide-gray-200">
                @foreach($activities as $activity)
                    <li class="px-6 py-4 hover:bg-gray-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3 mb-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $activity->getLogNameColorClass() }}">
                                        {{ ucfirst(str_replace('_', ' ', $activity->log_name ?? 'system')) }}
                                    </span>
                                    @if($activity->event)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $activity->getEventColorClass() }}">
                                            {{ ucfirst($activity->event) }}
                                        </span>
                                    @endif
                                    <span class="text-sm text-gray-500">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                
                                <p class="text-sm font-medium text-gray-900 mb-1">
                                    {{ $activity->getFormattedDescriptionAttribute() }}
                                </p>
                                
                                <div class="flex items-center space-x-4 text-sm text-gray-500">
                                    @if($activity->causer)
                                        <span class="flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            {{ $activity->causer->name }}
                                        </span>
                                    @endif
                                    
                                    @if($activity->ip_address)
                                        <span class="flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9" />
                                            </svg>
                                            {{ $activity->ip_address }}
                                        </span>
                                    @endif
                                    
                                    <span class="flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $activity->created_at->format('M d, Y g:i:s A') }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex-shrink-0">
                                <a href="{{ route('activity-logs.show', $activity) }}" 
                                   class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No activity logs found</h3>
                <p class="mt-1 text-sm text-gray-500">Try adjusting your filters to see more results.</p>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if($activities->hasPages())
        <div class="mt-6">
            {{ $activities->links() }}
        </div>
    @endif

    <!-- Cleanup Section (Admin Only) -->
    @if(auth()->user()->isAdmin())
        <div class="mt-8 bg-red-50 border border-red-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-red-900 mb-4">Activity Log Cleanup</h3>
            <p class="text-sm text-red-700 mb-4">
                Remove old activity logs to keep the database clean. This action cannot be undone.
            </p>
            <form method="POST" action="{{ route('activity-logs.cleanup') }}" 
                  onsubmit="return confirm('Are you sure you want to delete old activity logs? This cannot be undone.')">
                @csrf
                <div class="flex items-end space-x-4">
                    <div>
                        <label for="older_than_days" class="block text-sm font-medium text-red-700">Delete logs older than (days)</label>
                        <input type="number" name="older_than_days" id="older_than_days" 
                               value="90" min="7" max="365" required
                               class="mt-1 block w-32 rounded-md border-red-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                    </div>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Delete Old Logs
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection
