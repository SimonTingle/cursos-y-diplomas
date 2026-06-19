<?php

namespace App\Http\Controllers;

use App\Services\UserImportService;
use Illuminate\Http\Request;

class UserImportController extends Controller
{
    public function showForm(Request $request)
    {
        if (!$request->user()->can('import_users')) {
            abort(403, __('Unauthorized to import users'));
        }

        return view('admin.import-users');
    }

    public function import(Request $request)
    {
        if (!$request->user()->can('import_users')) {
            abort(403, __('Unauthorized to import users'));
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,json', 'max:10240'], // 10 MB max
        ]);

        try {
            $service = new UserImportService();
            $result = $service->import($request->file('file'));

            // Log the import
            \App\Models\AuditLog::create([
                'user_id'   => auth()->id(),
                'action'    => 'imported',
                'model_type' => 'User',
                'model_id'  => 0, // Bulk action
                'new_values' => [
                    'total_rows' => $result->totalRows,
                    'success' => $result->successCount,
                    'failed' => $result->failureCount,
                    'filename' => $result->filename,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $createdUsersData = array_map(fn ($item) => [
                'name' => $item['user']->name,
                'email' => $item['user']->email,
                'temp_password' => $item['password'],
            ], $result->createdUsers);

            if ($result->hasErrors()) {
                return back()
                    ->with('import_status', 'warning')
                    ->with('import_message', "Imported {$result->successCount} users with {$result->failureCount} errors")
                    ->with('import_errors', $result->errors)
                    ->with('created_users', $createdUsersData)
                    ->withInput();
            }

            return back()
                ->with('import_status', 'success')
                ->with('import_message', "Successfully imported {$result->successCount} users")
                ->with('created_users', $createdUsersData);

        } catch (\Exception $e) {
            return back()
                ->with('import_status', 'error')
                ->with('import_message', "Import failed: {$e->getMessage()}")
                ->withInput();
        }
    }

    public function downloadTemplate()
    {
        $csv = "name,email,phone,title,bio,role\n";
        $csv .= "John Doe,john@example.com,+1234567890,Senior Instructor,Expert in CPR,instructor\n";
        $csv .= "Jane Smith,jane@example.com,+0987654321,Admin,System Administrator,admin\n";

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="users_template.csv"');
    }
}
