@extends('layouts.portal')

@section('title', __('Audit Log Entry'))

@section('content')

    <section class="glass p-6 sm:p-8">
        <div class="mb-6 flex items-center gap-3">
            <a href="{{ route('admin.audit-logs.index') }}" class="text-slate-400 hover:text-white text-sm">← {{ __('Audit Logs') }}</a>
            <span class="text-slate-600">/</span>
            <span class="text-sm text-white">#{{ $log->id }}</span>
        </div>

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Action') }}</p>
                    <p class="mt-1 text-sm text-white">{{ ucfirst($log->action) }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Model') }}</p>
                    <p class="mt-1 text-sm text-white">{{ $log->model_type }} #{{ $log->model_id }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Performed By') }}</p>
                    <p class="mt-1 text-sm text-white">{{ $log->user?->name ?? __('System') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Timestamp') }}</p>
                    <p class="mt-1 text-sm text-white">{{ $log->created_at->format('Y-m-d H:i:s') }}</p>
                </div>
            </div>

            @if ($log->old_values)
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Before') }}</p>
                    <pre class="overflow-x-auto rounded-lg bg-slate-900/70 p-4 text-xs text-slate-300">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            @endif

            @if ($log->new_values)
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('After') }}</p>
                    <pre class="overflow-x-auto rounded-lg bg-slate-900/70 p-4 text-xs text-slate-300">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4 text-xs text-slate-500">
                <div>{{ __('IP') }}: {{ $log->ip_address ?? '—' }}</div>
                <div>{{ __('User Agent') }}: {{ $log->user_agent ?? '—' }}</div>
            </div>
        </div>
    </section>

@endsection
