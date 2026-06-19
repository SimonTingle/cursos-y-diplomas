@extends('layouts.portal')

@section('title', __('Import Users'))

@section('content')

    <section class="glass p-6 sm:p-8">
        <div class="mb-5 flex items-center gap-3">
            <span class="rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-cyan-300">Admin</span>
            <h2 class="text-lg font-semibold text-white">{{ __('Import Users') }}</h2>
        </div>

        <p class="mb-6 text-sm text-slate-300">{{ __('Bulk import users from a CSV or JSON file. Each user will receive an email with a temporary password.') }}</p>

        @if (session('import_status') === 'success')
            <div class="mb-4 rounded-lg border border-emerald-400/30 bg-emerald-400/10 p-4 text-sm text-emerald-200">
                ✓ {{ session('import_message') }}
            </div>
        @elseif (session('import_status') === 'warning')
            <div class="mb-4 rounded-lg border border-amber-400/30 bg-amber-400/10 p-4 text-sm text-amber-200">
                ⚠ {{ session('import_message') }}
                @if (session('import_errors'))
                    <div class="mt-3 space-y-1 text-xs">
                        @foreach (session('import_errors') as $error)
                            <div class="text-amber-100">Row {{ $error['row'] }}: {{ implode(', ', array_merge(...array_values($error['errors']))) }}</div>
                        @endforeach
                    </div>
                @endif
            </div>
        @elseif (session('import_status') === 'error')
            <div class="mb-4 rounded-lg border border-rose-400/30 bg-rose-400/10 p-4 text-sm text-rose-200">
                ✗ {{ session('import_message') }}
            </div>
        @endif

        @if (session('created_users') && count(session('created_users')) > 0)
            <div class="mb-6 rounded-lg border border-emerald-400/30 bg-emerald-400/10 p-4">
                <h3 class="mb-4 text-sm font-semibold text-emerald-300">{{ count(session('created_users')) }} User(s) Created</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-emerald-400/20">
                            <tr class="text-left text-xs font-semibold text-emerald-300">
                                <th class="px-3 py-2">Name</th>
                                <th class="px-3 py-2">Email</th>
                                <th class="px-3 py-2">Temporary Password</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (session('created_users') as $user)
                                <tr class="border-t border-emerald-400/10">
                                    <td class="px-3 py-2 text-slate-300">{{ $user['name'] }}</td>
                                    <td class="px-3 py-2 text-slate-400">{{ $user['email'] }}</td>
                                    <td class="px-3 py-2">
                                        <div class="flex items-center gap-2">
                                            <code class="rounded bg-slate-900 px-2 py-1 text-xs font-mono text-slate-300">{{ $user['temp_password'] }}</code>
                                            <button type="button" @click="navigator.clipboard.writeText('{{ addslashes($user['temp_password']) }}'); $el.textContent = '✓ Copied'" class="text-xs text-indigo-300 hover:text-indigo-200" title="Copy to clipboard">
                                                📋 Copy
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-4 text-xs text-slate-400">Share these passwords securely with the users. Users must change their password on first login.</p>
            </div>
        @endif

        <div class="mb-6 rounded-lg border border-slate-600/50 bg-slate-900/30 p-4">
            <h3 class="mb-3 font-medium text-slate-200">{{ __('File Format') }}</h3>
            <p class="mb-3 text-xs text-slate-400">{{ __('CSV or JSON file with the following fields (only name and email are required):') }}</p>
            <ul class="space-y-1 text-xs text-slate-400">
                <li>• <strong>name</strong> - User name (required)</li>
                <li>• <strong>email</strong> - User email (required, must be unique)</li>
                <li>• <strong>phone</strong> - Phone number (optional)</li>
                <li>• <strong>title</strong> - User title/role (optional, e.g., "Senior Instructor")</li>
                <li>• <strong>bio</strong> - Biography (optional)</li>
                <li>• <strong>role</strong> - Role (optional, default: "instructor", values: "admin" or "instructor")</li>
            </ul>
            <div class="mt-4">
                <a href="{{ route('admin.import-users.template') }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-700 px-3 py-1.5 text-xs font-medium text-slate-200 transition hover:bg-slate-600">
                    📥 {{ __('Download CSV Template') }}
                </a>
            </div>
        </div>

        <form method="post" action="{{ route('admin.import-users.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label class="glass-label">{{ __('Select File') }}</label>
                <input type="file" name="file" accept=".csv,.json" class="glass-input text-slate-300 file:mr-3 file:rounded-md file:border-0 file:bg-indigo-500 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-white" required>
                <p class="mt-1 text-xs text-slate-500">{{ __('Supported formats: CSV, JSON. Max 10 MB.') }}</p>
                <x-input-error class="mt-1 text-xs text-rose-400" :messages="$errors->get('file')" />
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="btn-primary">{{ __('Import Users') }}</button>
                <a href="{{ route('profile.edit') }}" class="btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </form>
    </section>

    <section class="glass p-6 sm:p-8">
        <h3 class="mb-4 font-medium text-white">{{ __('CSV Example') }}</h3>
        <pre class="overflow-x-auto rounded bg-slate-900/50 p-3 text-xs text-slate-300"><code>name,email,phone,title,bio,role
John Doe,john@example.com,+1234567890,Senior Instructor,Expert in CPR,instructor
Jane Smith,jane@example.com,+0987654321,Admin,System Administrator,admin
Bob Johnson,bob@example.com,,Instructor,,instructor</code></pre>
    </section>

    <section class="glass p-6 sm:p-8">
        <h3 class="mb-4 font-medium text-white">{{ __('JSON Example') }}</h3>
        <pre class="overflow-x-auto rounded bg-slate-900/50 p-3 text-xs text-slate-300"><code>[
  {
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "title": "Senior Instructor",
    "bio": "Expert in CPR",
    "role": "instructor"
  },
  {
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "+0987654321",
    "title": "Admin",
    "bio": "System Administrator",
    "role": "admin"
  }
]</code></pre>
    </section>

@endsection
