@extends('layouts.portal')

@section('title', __('Audit Logs'))

@section('content')

    <section class="glass p-6 sm:p-8">
        <div class="mb-6 flex items-center gap-3">
            <span class="rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-cyan-300">Admin</span>
            <h2 class="text-lg font-semibold text-white">{{ __('Audit Logs') }}</h2>
        </div>

        {{-- Filters --}}
        <form method="get" action="{{ route('admin.audit-logs.index') }}" class="mb-6 grid gap-4 sm:grid-cols-4">
            <div>
                <label class="glass-label">{{ __('Action') }}</label>
                <select name="filter_action" class="glass-input">
                    <option value="">{{ __('All Actions') }}</option>
                    <option value="created" @selected(request('filter_action') === 'created')>{{ __('Created') }}</option>
                    <option value="updated" @selected(request('filter_action') === 'updated')>{{ __('Updated') }}</option>
                    <option value="deleted" @selected(request('filter_action') === 'deleted')>{{ __('Deleted') }}</option>
                    <option value="imported" @selected(request('filter_action') === 'imported')>{{ __('Imported') }}</option>
                </select>
            </div>
            <div>
                <label class="glass-label">{{ __('From Date') }}</label>
                <input type="date" name="filter_date_from" value="{{ request('filter_date_from') }}" class="glass-input">
            </div>
            <div>
                <label class="glass-label">{{ __('To Date') }}</label>
                <input type="date" name="filter_date_to" value="{{ request('filter_date_to') }}" class="glass-input">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary w-full">{{ __('Filter') }}</button>
                @if (request()->hasAny(['filter_action', 'filter_date_from', 'filter_date_to']))
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn-ghost">{{ __('Clear') }}</a>
                @endif
            </div>
        </form>

        {{-- Logs Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-white/10">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-400">
                        <th class="px-4 py-3">{{ __('Timestamp') }}</th>
                        <th class="px-4 py-3">{{ __('Action') }}</th>
                        <th class="px-4 py-3">{{ __('Performed By') }}</th>
                        <th class="px-4 py-3">{{ __('Details') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-white/5">
                            <td class="px-4 py-3 text-sm text-slate-300">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    @if ($log->action === 'created')
                                        border border-emerald-400/30 text-emerald-300
                                    @elseif ($log->action === 'updated')
                                        border border-amber-400/30 text-amber-300
                                    @elseif ($log->action === 'deleted')
                                        border border-rose-400/30 text-rose-300
                                    @elseif ($log->action === 'imported')
                                        border border-cyan-400/30 text-cyan-300
                                    @endif
                                ">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-400">
                                {{ $log->user?->name ?? 'System' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-400">
                                @if ($log->new_values)
                                    <code class="block max-w-xs truncate rounded bg-slate-900/50 px-2 py-1">{{ json_encode($log->new_values, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-400">
                                {{ __('No audit logs found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    </section>

@endsection
